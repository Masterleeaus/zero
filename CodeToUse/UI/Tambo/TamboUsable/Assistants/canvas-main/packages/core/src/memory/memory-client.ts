/**
 * Memory Client
 *
 * Client for .mv2 file operations using @memvid/sdk.
 */

import { use, create, type Memvid, type AskInput, type PutInput, type PutManyInput, MemvidError, OpenAIEmbeddings } from '@memvid/sdk';
import * as fs from 'fs';

function normalizeTextForChunking(text: string): string {
  return text
    .replace(/\r\n/g, '\n')
    .replace(/\u0000/g, '')
    .replace(/[ \t]+\n/g, '\n')
    .replace(/\n{3,}/g, '\n\n')
    .trim();
}

function splitLongText(text: string, maxChars: number): string[] {
  const parts: string[] = [];
  let remaining = text;

  while (remaining.length > maxChars) {
    const window = remaining.slice(0, maxChars + 1);
    const breakpoints = [
      window.lastIndexOf('\n\n'),
      window.lastIndexOf('\n'),
      window.lastIndexOf('. '),
      window.lastIndexOf(' '),
    ].filter((n) => n > Math.floor(maxChars * 0.5));
    const cut = breakpoints.length > 0 ? Math.max(...breakpoints) : maxChars;
    parts.push(remaining.slice(0, cut).trim());
    remaining = remaining.slice(cut).trim();
  }

  if (remaining) parts.push(remaining);
  return parts.filter(Boolean);
}

function chunkTextForEmbeddings(
  text: string,
  opts?: { chunkChars?: number; overlapChars?: number; maxChunks?: number; maxTotalChars?: number }
): string[] {
  const chunkChars = opts?.chunkChars ?? 1800;
  const overlapChars = opts?.overlapChars ?? 200;
  const maxChunks = opts?.maxChunks ?? 120;
  const maxTotalChars = opts?.maxTotalChars ?? 160_000;

  const normalized = normalizeTextForChunking(text);
  if (!normalized) return [];

  const paragraphs = normalized.split(/\n\s*\n/).flatMap((p) => splitLongText(p, chunkChars));
  const baseChunks: string[] = [];

  let current = '';
  let total = 0;

  const pushChunk = (chunk: string) => {
    const trimmed = chunk.trim();
    if (!trimmed) return;
    if (baseChunks.length >= maxChunks) return;
    if (total + trimmed.length > maxTotalChars) return;
    baseChunks.push(trimmed);
    total += trimmed.length;
  };

  for (const para of paragraphs) {
    if (!para) continue;
    if (!current) {
      current = para;
      continue;
    }

    if ((current.length + 2 + para.length) <= chunkChars) {
      current = `${current}\n\n${para}`;
    } else {
      pushChunk(current);
      current = para;
      if (baseChunks.length >= maxChunks || total >= maxTotalChars) break;
    }
  }
  pushChunk(current);

  if (overlapChars <= 0) return baseChunks;

  const withOverlap: string[] = [];
  for (let i = 0; i < baseChunks.length; i++) {
    const prevTail = i > 0 ? baseChunks[i - 1]!.slice(-overlapChars) : '';
    const chunk = i > 0 ? `${prevTail}\n\n${baseChunks[i]!}` : baseChunks[i]!;
    withOverlap.push(chunk.trim());
  }
  return withOverlap;
}

// Extract text from PDF file using unpdf
async function extractPdfText(filePath: string): Promise<{ text: string; numpages: number }> {
  const { extractText } = await import('unpdf');
  const buffer = fs.readFileSync(filePath);
  const { text, totalPages } = await extractText(new Uint8Array(buffer), { mergePages: true });
  return { text, numpages: totalPages };
}
import type {
  MemoryConfig,
  Frame,
  FrameType,
  FrameInfo,
  RecallOptions,
  RecallResult,
  CaptureOptions,
  MemoryStats,
  MemoryContext,
  BuildContextOptions,
  Preference,
  Correction,
} from '../types/memory.js';
import type { Logger } from '../types/config.js';
import {
  MemoryNotFoundError,
  MemoryReadError,
  MemoryWriteError,
  MemorySearchError,
} from '../errors/memory.js';

/**
 * Internal memory config with required fields
 */
interface InternalMemoryConfig {
  path: string;
  memvidApiKey?: string;
  autoCreate: boolean;
  autoSave: boolean;
  autoSaveInterval: number;
}

/**
 * Memory Client
 *
 * Handles all operations on .mv2 memory files using the memvid SDK.
 */
export class MemoryClient {
  private readonly config: InternalMemoryConfig;
  private readonly logger?: Logger;
  private mv: Memvid | null = null;
  private isInitialized = false;

  // In-memory cache for preferences and corrections
  private preferences: Map<string, Preference> = new Map();
  private corrections: Correction[] = [];

  constructor(config: MemoryConfig, logger?: Logger) {
    this.config = {
      path: config.path,
      memvidApiKey: config.memvidApiKey,
      autoCreate: config.autoCreate ?? true,
      autoSave: config.autoSave ?? true,
      autoSaveInterval: config.autoSaveInterval ?? 60000,
    };
    this.logger = logger;
  }

  /**
   * Initialize memory (load from file or create new)
   */
  async initialize(): Promise<void> {
    if (this.isInitialized && this.mv) {
      return;
    }

    try {
      // Only use memvidApiKey if it's an actual Memvid API key (mv2_*)
      // OpenAI/Anthropic keys should not be passed here - they're for embeddings
      const memvidKey = this.config.memvidApiKey?.startsWith('mv2_')
        ? this.config.memvidApiKey
        : undefined;

      console.log('[MemoryClient] Initializing memory', {
        path: this.config.path,
        hasMemvidKey: !!memvidKey,
      });

      // Use 'auto' mode to open existing or create new
      const mode = this.config.autoCreate ? 'auto' : 'open';

      this.mv = await use(
        'basic',
        this.config.path,
        memvidKey,
        { mode }
      );

      this.isInitialized = true;
      this.log('info', 'Memory initialized');
    } catch (error) {
      if (error instanceof MemvidError) {
        if (error.code === 'MV012') {
          throw new MemoryReadError(`Corrupt memory file: ${this.config.path}`);
        }
        if (error.code === 'MV007') {
          throw new MemoryReadError(`Memory file is locked: ${this.config.path}`);
        }
      }

      if (!this.config.autoCreate) {
        throw new MemoryNotFoundError(this.config.path);
      }

      // Try to create new file
      try {
        const memvidKey = this.config.memvidApiKey?.startsWith('mv2_')
          ? this.config.memvidApiKey
          : undefined;
        this.log('info', 'Memory file not found, creating new');
        this.mv = await create(this.config.path, 'basic', memvidKey);
        this.isInitialized = true;
      } catch (createError) {
        throw new MemoryWriteError(
          createError instanceof Error ? createError.message : 'Failed to create memory file'
        );
      }
    }
  }

  /**
   * Ensure memory is initialized
   */
  private async ensureInitialized(): Promise<Memvid> {
    if (!this.isInitialized || !this.mv) {
      await this.initialize();
    }
    if (!this.mv) {
      throw new MemoryReadError('Memory not initialized');
    }
    return this.mv;
  }

  /**
   * Search memory for relevant context
   */
  async recall(options: RecallOptions): Promise<RecallResult[]> {
    const mv = await this.ensureInitialized();

    try {
      const {
        query,
        mode = 'hybrid',
        limit = 10,
        minScore = 0.1, // Lowered from 0.5 to include more results
        types,
        since,
        until,
      } = options;

      console.log('[MemoryClient] Recall', { query, mode, limit, minScore });

      // Use 'ask' with contextOnly to get semantic search results
      // The 'find' method only does lexical search, but 'ask' supports mode selection
      const askOptions: AskInput = {
        k: limit,
        snippetChars: 1200,
        mode: mode, // 'semantic', 'lexical', or 'hybrid'
        contextOnly: true, // Don't generate an answer, just return search results
      };

      // Execute search using ask() which supports semantic search
      const response = await mv.ask(query, askOptions);
      console.log('[MemoryClient] Ask response', {
        hasHits: !!response.hits,
        hitCount: response.hits?.length || 0,
        firstHit: response.hits?.[0] ? { score: response.hits[0].score, snippet: response.hits[0].snippet?.slice(0, 100) } : null
      });

      // Transform results to RecallResult format
      const results: RecallResult[] = [];

      if (response.hits && Array.isArray(response.hits)) {
        for (const hit of response.hits) {
          // Filter by score
          if (hit.score < minScore) {
            continue;
          }

          // Filter by type if specified
          if (types && types.length > 0) {
            const frameType = hit.labels?.[0] as FrameType;
            if (!types.includes(frameType)) {
              continue;
            }
          }

          // Filter by time range - use created_at (ISO string) from new SDK
          const timestamp = hit.created_at ? new Date(hit.created_at) : new Date();
          if (since && timestamp < since) {
            continue;
          }
          if (until && timestamp > until) {
            continue;
          }

          results.push({
            id: String(hit.frame_id),
            type: (hit.labels?.[0] || 'custom') as FrameType,
            content: hit.snippet || '',
            score: hit.score,
            timestamp,
            metadata: {
              // Include top-level fields from hit that are useful
              uri: hit.uri,
              title: hit.title,
              tags: hit.tags,
              labels: hit.labels,
              track: hit.track,
              content_dates: hit.content_dates,
              mime: hit.uri?.endsWith('.pdf') ? 'application/pdf' : undefined,
            },
          });
        }
      }

      return results;
    } catch (error) {
      if (error instanceof MemvidError) {
        throw new MemorySearchError(`Search failed: ${error.message}`);
      }
      throw new MemorySearchError(
        error instanceof Error ? error.message : 'Search failed'
      );
    }
  }

  /**
   * Capture content to memory
   */
  async capture(options: CaptureOptions): Promise<Frame> {
    const mv = await this.ensureInitialized();

    try {
      const { type, content, filePath, metadata = {}, embed = true } = options;
      const title = metadata.title as string || `${type}_${Date.now()}`;

      // For files (PDFs), ingest first then extract text and generate embeddings
      if (filePath) {
        const putInput: PutInput = {
          title,
          label: type,
          labels: [type],
          tags: metadata.tags as string[] || [],
          metadata: {
            ...metadata,
            canvasType: type,
            capturedAt: new Date().toISOString(),
          },
          file: filePath,
          enableEmbedding: false, // We'll handle embeddings manually
        };

        console.log('[MemoryClient] Ingesting file', { title, hasFile: true });
        const frameId = await mv.put(putInput);

        // Generate embeddings for PDF content if OpenAI key is available
        if (embed && process.env.OPENAI_API_KEY) {
          try {
            // Extract text from PDF using pdf-parse
            // Note: mv.view() returns raw PDF bytes, not extracted text
            console.log('[MemoryClient] Extracting text from PDF using pdf-parse...', { filePath });

            const pdfData = await extractPdfText(filePath);
            const extractedText = pdfData.text;

            if (extractedText && extractedText.length > 0) {
              const chunks = chunkTextForEmbeddings(extractedText, {
                chunkChars: 1800,
                overlapChars: 200,
                maxChunks: 120,
                maxTotalChars: 160_000,
              });

              if (chunks.length === 0) {
                console.warn('[MemoryClient] No chunkable text extracted from PDF');
              } else {
                console.log('[MemoryClient] Generating embeddings for PDF text chunks...', {
                  originalLength: extractedText.length,
                  chunkCount: chunks.length,
                  pdfPages: pdfData.numpages,
                });

                const embedder = new OpenAIEmbeddings({ model: 'text-embedding-3-small' });
                const batchSize = 16;
                const allEmbeddings: number[][] = [];

                for (let i = 0; i < chunks.length; i += batchSize) {
                  const batch = chunks.slice(i, i + batchSize);
                  const batchEmbeddings = await embedder.embedDocuments(batch);
                  allEmbeddings.push(...batchEmbeddings);
                }

                const nowIso = new Date().toISOString();
                const tags = Array.isArray(metadata.tags) ? (metadata.tags as string[]) : [];
                const putManyInputs: PutManyInput[] = [];

                for (let i = 0; i < chunks.length; i++) {
                  const chunk = chunks[i]!;
                  const embedding = allEmbeddings[i];
                  if (!embedding || embedding.length === 0) continue;

                  putManyInputs.push({
                    title: `${title} [embedded] (chunk ${i + 1}/${chunks.length})`,
                    text: chunk,
                    labels: [type, 'embedded'],
                    tags,
                    metadata: {
                      ...metadata,
                      canvasType: type,
                      capturedAt: nowIso,
                      sourceFrameId: frameId,
                      embeddedAt: nowIso,
                      pdfPages: pdfData.numpages,
                      chunkIndex: i,
                      chunkCount: chunks.length,
                    },
                    embedding,
                  });
                }

                if (putManyInputs.length > 0) {
                  console.log('[MemoryClient] Storing PDF text chunks with embeddings', {
                    title,
                    chunks: putManyInputs.length,
                    embeddingDimension: putManyInputs[0]?.embedding?.length ?? 0,
                  });
                  await mv.putMany(putManyInputs);
                }
              }
            } else {
              console.warn('[MemoryClient] No text extracted from PDF');
            }
          } catch (embedError) {
            console.warn('[MemoryClient] PDF embedding failed (document still stored):', embedError);
            // Document is still stored, just without embedding
          }
        }

        return {
          id: frameId,
          type,
          content,
          timestamp: new Date(),
          metadata,
        };
      }

      // For text content, use putMany() with embeddings if OpenAI key is available
      if (embed && process.env.OPENAI_API_KEY) {
        try {
          console.log('[MemoryClient] Generating embedding with OpenAI...');
          const embedder = new OpenAIEmbeddings({ model: 'text-embedding-3-small' });
          const embeddings = await embedder.embedDocuments([content]);

          const putManyInput: PutManyInput = {
            title,
            text: content,
            labels: [type],
            tags: metadata.tags as string[] || [],
            metadata: {
              ...metadata,
              canvasType: type,
              capturedAt: new Date().toISOString(),
            },
            embedding: embeddings[0],
          };

          const embedding = embeddings[0];
          console.log('[MemoryClient] Calling putMany with embedding', {
            title,
            embeddingDimension: embedding?.length ?? 0
          });

          const frameIds = await mv.putMany([putManyInput]);
          const frameId = frameIds[0] || `frame_${Date.now()}`;

          return {
            id: frameId,
            type,
            content,
            timestamp: new Date(),
            metadata,
          };
        } catch (embedError) {
          console.warn('[MemoryClient] Embedding failed, falling back to put without embedding:', embedError);
          // Fall through to regular put
        }
      }

      // Fallback: use put() without embedding
      const putInput: PutInput = {
        title,
        label: type,
        labels: [type],
        tags: metadata.tags as string[] || [],
        metadata: {
          ...metadata,
          canvasType: type,
          capturedAt: new Date().toISOString(),
        },
        text: content,
        enableEmbedding: embed,
      };

      console.log('[MemoryClient] Calling put (no embedding)', { title });
      const frameId = await mv.put(putInput);

      return {
        id: frameId,
        type,
        content,
        timestamp: new Date(),
        metadata,
      };
    } catch (error) {
      if (error instanceof MemvidError) {
        if (error.code === 'MV001') {
          throw new MemoryWriteError('Memory capacity exceeded');
        }
      }
      throw new MemoryWriteError(
        error instanceof Error ? error.message : 'Capture failed'
      );
    }
  }

  /**
   * Capture a conversation message
   */
  async captureMessage(
    role: 'user' | 'assistant',
    content: string,
    metadata: Record<string, unknown> = {}
  ): Promise<Frame> {
    return this.capture({
      type: 'message',
      content: `[${role}]: ${content}`,
      metadata: { role, ...metadata },
    });
  }

  /**
   * Capture a correction
   */
  async captureCorrection(correction: Correction): Promise<Frame> {
    this.corrections.push(correction);

    return this.capture({
      type: 'correction',
      content: `Original: ${correction.whatAiDid}\nCorrect: ${correction.correctApproach}`,
      metadata: {
        originalMessageId: correction.originalMessageId,
        tags: correction.tags,
      },
    });
  }

  /**
   * Capture a user preference
   */
  async capturePreference(preference: Omit<Preference, 'id'>): Promise<Frame> {
    const id = this.generateId();
    const fullPreference: Preference = { ...preference, id };
    this.preferences.set(id, fullPreference);

    return this.capture({
      type: 'preference',
      content: preference.content,
      metadata: {
        category: preference.category,
        confidence: preference.confidence,
        evidence: preference.evidence,
      },
    });
  }

  /**
   * Build context for LLM from memory
   */
  async buildContext(options: BuildContextOptions): Promise<MemoryContext> {
    const {
      query,
      maxTokens = 4000,
      includePreferences = true,
      includeCorrections = true,
      recencyBoost = 0.1,
    } = options;

    // Get relevant frames
    let frames = await this.recall({
      query,
      limit: 20,
      minScore: 0.3,
    });

    // Apply recency boost
    if (recencyBoost > 0) {
      const now = Date.now();
      const dayMs = 24 * 60 * 60 * 1000;

      frames = frames.map((frame) => {
        const ageInDays = (now - frame.timestamp.getTime()) / dayMs;
        const boost = Math.max(0, recencyBoost * (1 - ageInDays / 30));
        return { ...frame, score: Math.min(1, frame.score + boost) };
      });

      frames.sort((a, b) => b.score - a.score);
    }

    // Get preferences
    const preferences = includePreferences
      ? Array.from(this.preferences.values())
      : [];

    // Get recent corrections
    const corrections = includeCorrections
      ? this.corrections.slice(-10)
      : [];

    // Estimate tokens (rough: 4 chars per token)
    let tokenCount = 0;
    const includedFrames: RecallResult[] = [];

    for (const frame of frames) {
      const frameTokens = Math.ceil(frame.content.length / 4);
      if (tokenCount + frameTokens > maxTokens) break;
      tokenCount += frameTokens;
      includedFrames.push(frame);
    }

    return {
      frames: includedFrames,
      preferences,
      corrections,
      tokenCount,
    };
  }

  /**
   * Ask a question using RAG
   */
  async ask(question: string, options?: AskInput): Promise<string> {
    const mv = await this.ensureInitialized();

    try {
      const response = await mv.ask(question, {
        k: options?.k ?? 5,
        snippetChars: options?.snippetChars ?? 1200,
        model: options?.model,
        contextOnly: options?.contextOnly,
      });

      return response.answer || '';
    } catch (error) {
      throw new MemorySearchError(
        error instanceof Error ? error.message : 'Ask failed'
      );
    }
  }

  /**
   * Get memory statistics
   */
  async getStats(): Promise<MemoryStats> {
    const mv = await this.ensureInitialized();

    try {
      const stats = await mv.stats();

      const framesByType: Record<FrameType, number> = {
        message: 0,
        correction: 0,
        feedback: 0,
        decision: 0,
        preference: 0,
        pattern: 0,
        document: 0,
        custom: 0,
      };

      return {
        totalFrames: (stats.frame_count as number) || 0,
        framesByType,
        sizeBytes: (stats.size_bytes as number) || 0,
        firstFrame: undefined,
        lastFrame: undefined,
        indexedFrames: (stats.has_vec_index as boolean) ? (stats.frame_count as number) || 0 : 0,
      };
    } catch (error) {
      throw new MemoryReadError(
        error instanceof Error ? error.message : 'Failed to get stats'
      );
    }
  }

  /**
   * Save/seal memory file
   */
  async save(): Promise<void> {
    const mv = await this.ensureInitialized();

    try {
      this.log('debug', 'Sealing memory', { path: this.config.path });
      await mv.seal();
      this.log('info', 'Memory saved');
    } catch (error) {
      throw new MemoryWriteError(
        error instanceof Error ? error.message : 'Save failed'
      );
    }
  }

  /**
   * Sync with cloud (if memvidApiKey provided)
   */
  async sync(): Promise<void> {
    if (!this.config.memvidApiKey) {
      this.log('debug', 'No memvidApiKey, skipping sync');
      return;
    }

    const mv = await this.ensureInitialized();

    try {
      this.log('info', 'Syncing memory to cloud');

      // Get memory binding info
      const binding = await mv.getMemoryBinding();

      if (binding) {
        // Already bound, sync tickets
        await mv.syncTickets(
          binding.memory_id,
          this.config.memvidApiKey
        );
        this.log('info', 'Memory synced');
      } else {
        this.log('debug', 'Memory not bound to cloud');
      }
    } catch (error) {
      this.log('error', 'Failed to sync memory', {
        error: error instanceof Error ? error.message : 'Unknown error',
      });
    }
  }

  /**
   * Close memory (cleanup)
   */
  async close(): Promise<void> {
    this.mv = null;
    this.isInitialized = false;
  }

  /**
   * View a frame by ID
   */
  async view(frameId: number): Promise<string> {
    const mv = await this.ensureInitialized();
    return mv.view(frameId);
  }

  /**
   * View a frame by URI
   */
  async viewByUri(uri: string): Promise<string> {
    const mv = await this.ensureInitialized();
    return mv.viewByUri(uri);
  }

  /**
   * Get frame metadata by ID
   */
  async getFrameInfo(frameId: number): Promise<FrameInfo> {
    const mv = await this.ensureInitialized();

    try {
      const info = await mv.getFrameInfo(frameId);
      return {
        id: info.id,
        uri: info.uri,
        title: info.title,
        mimeType: info.metadata?.mime as string | undefined,
        pageCount: info.chunk_count ?? 1,
        currentPage: info.chunk_index ? info.chunk_index + 1 : 1,
        content: undefined, // Use view() to get content if needed
        metadata: {
          ...info.metadata,
          parent_id: info.parent_id,
          role: info.role,
        },
      };
    } catch (error) {
      throw new MemoryReadError(
        error instanceof Error ? error.message : 'Failed to get frame info'
      );
    }
  }

  /**
   * Extract binary asset (PDF, image, etc.) from a frame
   * Returns the raw bytes as a Buffer
   *
   * For document chunks, automatically extracts the parent document.
   */
  async extractAsset(frameId: number): Promise<{ data: Buffer; mimeType: string; filename: string; pageCount: number }> {
    const mv = await this.ensureInitialized();

    try {
      const result = await mv.extractAsset(frameId);
      return {
        data: Buffer.from(result.data),
        mimeType: result.mimeType,
        filename: result.filename,
        pageCount: result.pageCount,
      };
    } catch (error) {
      throw new MemoryReadError(
        error instanceof Error ? error.message : 'Failed to extract asset'
      );
    }
  }

  /**
   * Extract asset by URI
   *
   * For document chunks (URIs with #page-X), automatically extracts the parent document.
   */
  async extractAssetByUri(uri: string): Promise<{ data: Buffer; mimeType: string; filename: string; pageCount: number }> {
    const mv = await this.ensureInitialized();

    try {
      const result = await mv.extractAssetByUri(uri);
      return {
        data: Buffer.from(result.data),
        mimeType: result.mimeType,
        filename: result.filename,
        pageCount: result.pageCount,
      };
    } catch (error) {
      throw new MemoryReadError(
        error instanceof Error ? error.message : 'Failed to extract asset by URI'
      );
    }
  }

  /**
   * Enable lexical search
   */
  async enableLex(): Promise<void> {
    const mv = await this.ensureInitialized();
    await mv.enableLex();
  }

  /**
   * Generate unique ID
   */
  private generateId(): string {
    return `frame_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
  }

  /**
   * Log message
   */
  private log(
    level: 'debug' | 'info' | 'warn' | 'error',
    message: string,
    data?: Record<string, unknown>
  ): void {
    if (this.logger) {
      this.logger[level](`[MemoryClient] ${message}`, data);
    }
  }
}

/**
 * Create a memory client
 */
export function createMemoryClient(
  config: MemoryConfig,
  logger?: Logger
): MemoryClient {
  return new MemoryClient(config, logger);
}
