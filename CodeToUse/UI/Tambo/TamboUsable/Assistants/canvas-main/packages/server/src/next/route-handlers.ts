/**
 * Next.js Route Handlers
 *
 * Complete set of route handlers for Next.js App Router.
 * Provides thin wrapper API routes for Canvas functionality.
 */

import type { EngineConfig, CanvasSettings } from '@memvid/canvas-core';
import {
  createMemoryClient,
  loadSettings,
  saveSettings,
  resolvePath,
  getEmbeddingApiKey,
  // Ingest functions
  extractText,
  chunkText,
  isExtensionSupported,
  isBinaryFormat,
  SUPPORTED_EXTENSIONS,
} from '@memvid/canvas-core';
import { loadCanvasConfig } from '@memvid/canvas-core/config';
import { createServer, type CanvasServer } from '../handlers.js';
import type { HandlerOptions, ChatRequest, RecallRequest } from '../types.js';
import * as fs from 'fs';
import * as path from 'path';

/**
 * Request type compatible with Next.js and standard Web APIs
 * Using Request (Web API standard) ensures compatibility with Next.js route handlers
 */
type NextRequest = Request;
type NextResponse = Response;

/**
 * Route handler creator options
 */
export interface RouteHandlerOptions extends HandlerOptions {
  /** Canvas config (can be function for dynamic config) */
  config?: EngineConfig | (() => EngineConfig | Promise<EngineConfig>);
  /** Base path for settings file */
  basePath?: string;
}

/**
 * Memory singleton cache
 */
interface MemoryInstance {
  client: ReturnType<typeof createMemoryClient>;
  path: string;
}

let memoryInstance: MemoryInstance | null = null;

/**
 * Server singleton cache
 */
const serverCache = new Map<string, CanvasServer>();

/**
 * Get memory path from settings or config
 */
function getMemoryPath(options: RouteHandlerOptions): string {
  const settings = loadSettings(options.basePath);
  if (settings?.memoryPath) {
    return settings.memoryPath;
  }

  if (options.config) {
    const config =
      typeof options.config === 'function' ? null : options.config;
    if (config?.memory) {
      return typeof config.memory === 'string'
        ? config.memory
        : config.memory.path;
    }
  }

  return './data/memory.mv2';
}

/**
 * Get or create memory client
 */
async function getMemoryClient(
  options: RouteHandlerOptions
): Promise<ReturnType<typeof createMemoryClient>> {
  const memoryPath = getMemoryPath(options);
  const resolvedPath = resolvePath(memoryPath, options.basePath);

  // Reset client if path changed
  if (memoryInstance && memoryInstance.path !== resolvedPath) {
    memoryInstance = null;
  }

  if (!memoryInstance) {
    const client = createMemoryClient({
      path: resolvedPath,
      autoCreate: true,
      memvidApiKey: getEmbeddingApiKey(),
    });
    await client.initialize();
    memoryInstance = { client, path: resolvedPath };
  }

  return memoryInstance.client;
}

/**
 * Get or create server instance
 */
async function getServer(options: RouteHandlerOptions): Promise<CanvasServer> {
  const config =
    typeof options.config === 'function'
      ? await options.config()
      : options.config;

  if (!config) {
    throw new Error('Canvas config is required for chat handlers');
  }

  const cacheKey = JSON.stringify({
    memory: config.memory,
    defaultAgent: config.defaultAgent,
  });

  let server = serverCache.get(cacheKey);

  if (!server) {
    server = createServer(config, options);
    serverCache.set(cacheKey, server);
  }

  return server;
}

/**
 * JSON response helper
 */
function jsonResponse(data: unknown, status = 200): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: {
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type',
    },
  });
}

/**
 * Error response helper
 */
function errorResponse(message: string, status = 500): Response {
  return jsonResponse({ error: message }, status);
}

/**
 * OPTIONS response helper
 */
function optionsResponse(): Response {
  return new Response(null, {
    status: 204,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type',
    },
  });
}

// ============================================================================
// Chat Handler
// ============================================================================

export function createChatHandler(options: RouteHandlerOptions) {
  return async function handler(request: NextRequest): Promise<NextResponse> {
    if (request.method === 'OPTIONS') {
      return optionsResponse();
    }

    try {
      // Parse request body first to get API key from client
      const body = (await request.json()) as ChatRequest;
      
      // Get base config
      const baseConfig =
        typeof options.config === 'function'
          ? await options.config()
          : options.config;
      
      if (!baseConfig) {
        throw new Error('Canvas config is required for chat handlers');
      }
      
      // Create dynamic config with API key from request if provided
      // Otherwise use base config (which should have API key from env/config)
      const dynamicConfig: EngineConfig = body.llmApiKey && body.llmProvider
        ? {
            ...baseConfig,
            llm: {
              provider: body.llmProvider as 'openai' | 'anthropic' | 'google',
              apiKey: body.llmApiKey.trim(),
              model: body.llmModel || baseConfig.llm.model,
            },
          }
        : baseConfig;
      
      // Create server with dynamic config (don't cache when using request API key)
      const server = body.llmApiKey && body.llmProvider
        ? createServer(dynamicConfig, options)
        : await getServer(options);
      
      return server.handleChat(body);
    } catch (error) {
      console.error('[Canvas] Chat error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Chat failed'
      );
    }
  };
}

// ============================================================================
// Recall/Search Handler
// ============================================================================

export function createRecallHandler(options: RouteHandlerOptions) {
  return async function handler(request: NextRequest): Promise<NextResponse> {
    if (request.method === 'OPTIONS') {
      return optionsResponse();
    }

    try {
      const server = await getServer(options);
      const body = (await request.json()) as RecallRequest;
      return server.handleRecall(body);
    } catch (error) {
      console.error('[Canvas] Recall error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Recall failed'
      );
    }
  };
}

// ============================================================================
// Memory Info Handler
// ============================================================================

export function createMemoryHandler(options: RouteHandlerOptions) {
  async function handleGet(): Promise<NextResponse> {
    try {
      const client = await getMemoryClient(options);
      const stats = await client.getStats();
      const memoryPath = getMemoryPath(options);

      return jsonResponse({
        status: 'ok',
        memoryPath,
        frames: stats.totalFrames,
        size: stats.sizeBytes,
      });
    } catch (error) {
      console.error('[Canvas] Memory info error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Failed to get memory info'
      );
    }
  }

  async function handlePost(request: NextRequest): Promise<NextResponse> {
    try {
      const { query, limit = 10 } = (await request.json()) as {
        query: string;
        limit?: number;
      };

      if (!query) {
        return errorResponse('Query is required', 400);
      }

      const client = await getMemoryClient(options);
      const results = await client.recall({
        query,
        limit,
        minScore: 0.3,
      });

      // Normalize scores
      const maxScore =
        results.length > 0 ? Math.max(...results.map((r) => r.score)) : 1;
      const normalizedResults = results.map((result) => ({
        ...result,
        score: maxScore > 0 ? result.score / maxScore : 0,
      }));

      return jsonResponse({ results: normalizedResults });
    } catch (error) {
      console.error('[Canvas] Memory search error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Search failed'
      );
    }
  }

  return async function handler(request: NextRequest): Promise<NextResponse> {
    if (request.method === 'OPTIONS') {
      return optionsResponse();
    }
    if (request.method === 'GET') {
      return handleGet();
    }
    return handlePost(request);
  };
}

// ============================================================================
// Search Handler
// ============================================================================

export function createSearchHandler(options: RouteHandlerOptions) {
  return async function handler(request: NextRequest): Promise<NextResponse> {
    if (request.method === 'OPTIONS') {
      return optionsResponse();
    }

    try {
      const { query, limit = 20, mode = 'hybrid', minScore = 0.1 } =
        (await request.json()) as {
          query: string;
          limit?: number;
          mode?: 'semantic' | 'lexical' | 'hybrid';
          minScore?: number;
        };

      if (!query) {
        return errorResponse('Query is required', 400);
      }

      const client = await getMemoryClient(options);
      const results = await client.recall({
        query,
        limit,
        mode,
        minScore,
      });

      return jsonResponse({
        results,
        query,
        mode,
        count: results.length,
      });
    } catch (error) {
      console.error('[Canvas] Search error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Search failed'
      );
    }
  };
}

// ============================================================================
// Stats Handler
// ============================================================================

export function createStatsHandler(options: RouteHandlerOptions) {
  return async function handler(request: NextRequest): Promise<NextResponse> {
    if (request.method === 'OPTIONS') {
      return optionsResponse();
    }

    try {
      const client = await getMemoryClient(options);
      const stats = await client.getStats();
      const memoryPath = getMemoryPath(options);

      return jsonResponse({
        memoryPath,
        totalFrames: stats.totalFrames,
        sizeBytes: stats.sizeBytes,
        indexedFrames: stats.indexedFrames,
        framesByType: stats.framesByType,
      });
    } catch (error) {
      console.error('[Canvas] Stats error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Failed to get stats'
      );
    }
  };
}

// ============================================================================
// Asset Handler
// ============================================================================

export function createAssetHandler(options: RouteHandlerOptions) {
  return async function handler(request: NextRequest): Promise<NextResponse> {
    if (request.method === 'OPTIONS') {
      return optionsResponse();
    }

    try {
      const url = new URL(request.url);
      const frameId = url.searchParams.get('frameId');
      const uri = url.searchParams.get('uri');

      if (!frameId && !uri) {
        return errorResponse('frameId or uri is required', 400);
      }

      const client = await getMemoryClient(options);

      const asset = uri
        ? await client.extractAssetByUri(uri)
        : await client.extractAsset(Number(frameId));

      // Convert Buffer to Uint8Array for Response compatibility
      const uint8Array = new Uint8Array(asset.data);
      return new Response(uint8Array, {
        status: 200,
        headers: {
          'Content-Type': asset.mimeType,
          'Content-Disposition': `inline; filename="${asset.filename}"`,
          'Content-Length': String(asset.data.length),
          'X-Page-Count': String(asset.pageCount),
          'Access-Control-Allow-Origin': '*',
          'Cache-Control': 'public, max-age=3600',
        },
      });
    } catch (error) {
      console.error('[Canvas] Asset error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Failed to extract asset'
      );
    }
  };
}

// ============================================================================
// Ingest Handler
// ============================================================================

// Build supported formats list from core
const SUPPORTED_FORMATS = [
  { extension: 'pdf', mimeType: 'application/pdf', description: 'PDF Documents' },
  { extension: 'docx', mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', description: 'Word Documents' },
  { extension: 'doc', mimeType: 'application/msword', description: 'Word Documents (Legacy)' },
  { extension: 'xlsx', mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', description: 'Excel Spreadsheets' },
  { extension: 'xls', mimeType: 'application/vnd.ms-excel', description: 'Excel Spreadsheets (Legacy)' },
  { extension: 'pptx', mimeType: 'application/vnd.openxmlformats-officedocument.presentationml.presentation', description: 'PowerPoint Presentations' },
  { extension: 'ppt', mimeType: 'application/vnd.ms-powerpoint', description: 'PowerPoint Presentations (Legacy)' },
  { extension: 'txt', mimeType: 'text/plain', description: 'Plain Text' },
  { extension: 'md', mimeType: 'text/markdown', description: 'Markdown' },
  { extension: 'csv', mimeType: 'text/csv', description: 'CSV Data' },
  { extension: 'json', mimeType: 'application/json', description: 'JSON Data' },
  { extension: 'html', mimeType: 'text/html', description: 'HTML' },
  { extension: 'xml', mimeType: 'application/xml', description: 'XML' },
  { extension: 'yaml', mimeType: 'application/x-yaml', description: 'YAML' },
  { extension: 'yml', mimeType: 'application/x-yaml', description: 'YAML' },
];

export function createIngestHandler(options: RouteHandlerOptions) {
  async function handleGet(): Promise<NextResponse> {
    return jsonResponse({
      supportedFormats: SUPPORTED_FORMATS,
      supportedExtensions: SUPPORTED_EXTENSIONS,
      maxFileSize: '50MB',
    });
  }

  async function handlePost(request: NextRequest): Promise<NextResponse> {
    try {
      if (!request.formData) {
        return errorResponse('FormData not supported', 400);
      }

      const formData = await request.formData();
      const file = formData.get('file') as File | null;
      const enableEmbeddings = formData.get('enableEmbeddings') !== 'false';
      const chunkSize = parseInt(formData.get('chunkSize') as string) || 1200;

      if (!file) {
        return errorResponse('No file provided', 400);
      }

      // Check if file type is supported using core function
      if (!isExtensionSupported(file.name)) {
        return errorResponse(
          `Unsupported file type. Supported: ${SUPPORTED_EXTENSIONS.join(', ')}`,
          400
        );
      }

      const client = await getMemoryClient(options);
      const arrayBuffer = await file.arrayBuffer();
      const buffer = Buffer.from(arrayBuffer);
      const isBinary = isBinaryFormat(file.name);

      // Extract text using core ingest functions
      let textContent: string;
      if (isBinary) {
        textContent = await extractText(buffer, file.name);
      } else {
        textContent = buffer.toString('utf-8');
      }

      if (!textContent || textContent.trim().length === 0) {
        // Empty file, create placeholder
        await client.capture({
          type: 'document',
          content: '(Empty document)',
          metadata: {
            title: file.name,
            filename: file.name,
            mime: file.type,
            size: file.size,
            uploadedAt: new Date().toISOString(),
          },
        });
      } else {
        // Chunk the text using core function
        const chunks = chunkText(textContent, chunkSize);

        if (chunks.length === 1) {
          // Single chunk
          await client.capture({
            type: 'document',
            content: chunks[0]!,
            metadata: {
              title: file.name,
              filename: file.name,
              mime: file.type,
              size: file.size,
              uploadedAt: new Date().toISOString(),
            },
            embed: enableEmbeddings,
          });
        } else {
          // Multiple chunks - ingest each as separate frame
          for (let i = 0; i < chunks.length; i++) {
            const chunk = chunks[i];
            if (!chunk) continue;

            await client.capture({
              type: 'document',
              content: chunk,
              metadata: {
                title: i === 0 ? file.name : `${file.name} (chunk ${i + 1}/${chunks.length})`,
                filename: file.name,
                mime: file.type,
                size: file.size,
                uploadedAt: new Date().toISOString(),
                chunk: i + 1,
                totalChunks: chunks.length,
                parentTitle: file.name,
              },
              embed: enableEmbeddings,
            });
          }
        }
      }

      const stats = await client.getStats();
      const ext = file.name.split('.').pop()?.toLowerCase() || '';

      return jsonResponse({
        status: 'ok',
        message: `${file.name} ingested successfully`,
        file: {
          name: file.name,
          size: file.size,
          type: ext,
          isBinary,
        },
        chunks: chunkText(textContent, chunkSize).length,
        memory: {
          totalFrames: stats.totalFrames,
          sizeBytes: stats.sizeBytes,
        },
      });
    } catch (error) {
      console.error('[Canvas] Ingest error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Ingestion failed'
      );
    }
  }

  return async function handler(request: NextRequest): Promise<NextResponse> {
    if (request.method === 'OPTIONS') {
      return optionsResponse();
    }
    if (request.method === 'GET') {
      return handleGet();
    }
    return handlePost(request);
  };
}

// ============================================================================
// Settings Handler
// ============================================================================

export function createSettingsHandler(options: RouteHandlerOptions) {
  async function handleGet(): Promise<NextResponse> {
    try {
      const settings = loadSettings(options.basePath);
      return jsonResponse(settings || {});
    } catch (error) {
      console.error('[Canvas] Settings load error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Failed to load settings'
      );
    }
  }

  async function handlePost(request: NextRequest): Promise<NextResponse> {
    try {
      const newSettings = (await request.json()) as CanvasSettings;
      saveSettings(newSettings, options.basePath);

      // Reset memory client if path changed
      if (memoryInstance) {
        memoryInstance = null;
      }

      return jsonResponse({
        status: 'ok',
        message: 'Settings saved',
        settings: newSettings,
      });
    } catch (error) {
      console.error('[Canvas] Settings save error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Failed to save settings'
      );
    }
  }

  return async function handler(request: NextRequest): Promise<NextResponse> {
    if (request.method === 'OPTIONS') {
      return optionsResponse();
    }
    if (request.method === 'GET') {
      return handleGet();
    }
    return handlePost(request);
  };
}

// ============================================================================
// Create Memory Handler
// ============================================================================

export function createCreateMemoryHandler(options: RouteHandlerOptions) {
  return async function handler(request: NextRequest): Promise<NextResponse> {
    if (request.method === 'OPTIONS') {
      return optionsResponse();
    }

    try {
      const { memoryPath } = (await request.json()) as { memoryPath: string };

      if (!memoryPath) {
        return errorResponse('memoryPath is required', 400);
      }

      const resolvedPath = resolvePath(memoryPath, options.basePath);

      // Ensure directory exists
      const dir = path.dirname(resolvedPath);
      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }

      // Create memory client (will auto-create file)
      const client = createMemoryClient({
        path: resolvedPath,
        autoCreate: true,
        memvidApiKey: getEmbeddingApiKey(),
      });
      await client.initialize();

      // Update singleton
      memoryInstance = { client, path: resolvedPath };

      const stats = await client.getStats();

      return jsonResponse({
        status: 'ok',
        message: 'Memory file created',
        path: resolvedPath,
        frames: stats.totalFrames,
      });
    } catch (error) {
      console.error('[Canvas] Create memory error:', error);
      return errorResponse(
        error instanceof Error ? error.message : 'Failed to create memory file'
      );
    }
  };
}

// ============================================================================
// Catch-All Handler (Recommended for minimal setup)
// ============================================================================

/**
 * URL path to handler mapping
 */
type PathHandler = (request: NextRequest) => Promise<NextResponse>;

/**
 * Create a catch-all route handler for all Canvas endpoints
 *
 * This provides the simplest possible integration - just one route file!
 *
 * @example
 * ```ts
 * // app/api/canvas/[...path]/route.ts
 * import { createCanvasCatchAll } from '@memvid/canvas-server/next';
 *
 * export const { GET, POST, OPTIONS } = createCanvasCatchAll({
 *   basePath: process.cwd(),
 * });
 * ```
 *
 * Supported endpoints:
 * - /api/canvas/memory - Memory info
 * - /api/canvas/search - Search
 * - /api/canvas/chat - Chat
 * - /api/canvas/stats - Statistics
 * - /api/canvas/asset - Asset extraction
 * - /api/canvas/ingest - File ingestion
 * - /api/canvas/settings - Settings
 * - /api/canvas/create-memory - Create new memory
 */
export function createCanvasCatchAll(options: RouteHandlerOptions = {}) {
  // Auto-load unified config if not provided
  const resolvedOptions: RouteHandlerOptions = { ...options };
  if (!resolvedOptions.config) {
    resolvedOptions.config = async () => {
      try {
        const unifiedConfig = await loadCanvasConfig(options.basePath || process.cwd());
        // Convert unified config to engine config
        const engineConfig: EngineConfig = {
          llm: {
            provider: unifiedConfig.llm.provider,
            apiKey: unifiedConfig.llm.apiKey || process.env[`${unifiedConfig.llm.provider.toUpperCase()}_API_KEY`] || '',
            model: unifiedConfig.llm.model,
          },
          embedding: unifiedConfig.embedding,
          memory: typeof unifiedConfig.memory === 'string' 
            ? unifiedConfig.memory 
            : unifiedConfig.memory?.path || './data/memory.mv2',
          memvidApiKey: unifiedConfig.memvidApiKey,
          agents: unifiedConfig.agents,
          defaultAgent: unifiedConfig.defaultAgent,
          debug: unifiedConfig.advanced?.debug,
        };
        return engineConfig;
      } catch (error) {
        console.warn('[Canvas] Failed to load unified config, using defaults:', error);
        // Return minimal config
        return {
          llm: {
            provider: 'openai',
            apiKey: process.env.OPENAI_API_KEY || '',
          },
          memory: './data/memory.mv2',
        };
      }
    };
  }

  // Create all handlers
  const memoryHandler = createMemoryHandler(resolvedOptions);
  const searchHandler = createSearchHandler(resolvedOptions);
  const statsHandler = createStatsHandler(resolvedOptions);
  const assetHandler = createAssetHandler(resolvedOptions);
  const ingestHandler = createIngestHandler(resolvedOptions);
  const settingsHandler = createSettingsHandler(resolvedOptions);
  const createMemoryHandler_ = createCreateMemoryHandler(resolvedOptions);
  const chatHandler = resolvedOptions.config ? createChatHandler(resolvedOptions) : null;
  const recallHandler = resolvedOptions.config ? createRecallHandler(resolvedOptions) : null;

  // Route map
  const routes: Record<string, PathHandler | null> = {
    memory: memoryHandler,
    search: searchHandler,
    stats: statsHandler,
    asset: assetHandler,
    ingest: ingestHandler,
    settings: settingsHandler,
    'create-memory': createMemoryHandler_,
    chat: chatHandler,
    recall: recallHandler,
  };

  // Extract path from URL
  function getEndpoint(request: NextRequest): string {
    const url = new URL(request.url);
    // Match /api/canvas/[endpoint] or /api/canvas/[...path]
    const match = url.pathname.match(/\/api\/canvas\/(.+?)(?:\/|$)/);
    return match?.[1] || '';
  }

  // Main handler
  async function handler(request: NextRequest): Promise<NextResponse> {
    const endpoint = getEndpoint(request);
    const routeHandler = routes[endpoint];

    if (!routeHandler) {
      return jsonResponse(
        {
          error: 'Not found',
          message: `Unknown endpoint: ${endpoint}`,
          availableEndpoints: Object.keys(routes).filter(k => routes[k] !== null),
        },
        404
      );
    }

    return routeHandler(request);
  }

  return {
    GET: handler,
    POST: handler,
    OPTIONS: handler,
  };
}

// ============================================================================
// Combined Handlers Factory
// ============================================================================

/**
 * Create all Canvas route handlers at once
 *
 * @example
 * ```ts
 * // lib/canvas-handlers.ts
 * import { createCanvasHandlers } from '@memvid/canvas-server/next';
 *
 * export const handlers = createCanvasHandlers({
 *   basePath: process.cwd(),
 * });
 *
 * // app/api/memory/route.ts
 * import { handlers } from '@/lib/canvas-handlers';
 * export const { GET, POST, OPTIONS } = handlers.memory;
 *
 * // app/api/canvas/chat/route.ts
 * import { handlers } from '@/lib/canvas-handlers';
 * export const { POST, OPTIONS } = handlers.chat;
 * ```
 */
export function createCanvasHandlers(options: RouteHandlerOptions = {}) {
  const memoryHandler = createMemoryHandler(options);
  const searchHandler = createSearchHandler(options);
  const statsHandler = createStatsHandler(options);
  const assetHandler = createAssetHandler(options);
  const ingestHandler = createIngestHandler(options);
  const settingsHandler = createSettingsHandler(options);
  const createMemoryHandler_ = createCreateMemoryHandler(options);

  // Chat and recall require config
  const chatHandler = options.config ? createChatHandler(options) : null;
  const recallHandler = options.config ? createRecallHandler(options) : null;

  return {
    /** Memory info and quick search */
    memory: {
      GET: memoryHandler,
      POST: memoryHandler,
      OPTIONS: memoryHandler,
    },
    /** Full search with options */
    search: {
      POST: searchHandler,
      OPTIONS: searchHandler,
    },
    /** Memory statistics */
    stats: {
      GET: statsHandler,
      OPTIONS: statsHandler,
    },
    /** Asset extraction (PDFs, etc.) */
    asset: {
      GET: assetHandler,
      OPTIONS: assetHandler,
    },
    /** File ingestion */
    ingest: {
      GET: ingestHandler,
      POST: ingestHandler,
      OPTIONS: ingestHandler,
    },
    /** Settings management */
    settings: {
      GET: settingsHandler,
      POST: settingsHandler,
      OPTIONS: settingsHandler,
    },
    /** Create new memory file */
    createMemory: {
      POST: createMemoryHandler_,
      OPTIONS: createMemoryHandler_,
    },
    /** Chat (requires config) */
    chat: chatHandler
      ? {
          POST: chatHandler,
          OPTIONS: chatHandler,
        }
      : null,
    /** Recall/search with LLM context (requires config) */
    recall: recallHandler
      ? {
          POST: recallHandler,
          OPTIONS: recallHandler,
        }
      : null,
  };
}
