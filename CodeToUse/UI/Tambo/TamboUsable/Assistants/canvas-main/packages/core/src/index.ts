/**
 * @memvid/canvas-core
 *
 * Core engine for Canvas - AI UI Kit powered by Memvid.
 *
 * @example
 * ```typescript
 * import { createEngine } from '@memvid/canvas-core';
 *
 * const engine = createEngine({
 *   llm: { provider: 'anthropic', apiKey: process.env.ANTHROPIC_API_KEY },
 *   embedding: { provider: 'openai', apiKey: process.env.OPENAI_API_KEY },
 *   memory: './app.mv2',
 * });
 *
 * // Send a message
 * const { message, sources } = await engine.send('Hello!');
 * console.log(message.content);
 *
 * // Stream a response
 * for await (const chunk of engine.stream('Tell me a story')) {
 *   if (chunk.type === 'text') {
 *     process.stdout.write(chunk.text);
 *   }
 * }
 * ```
 *
 * @packageDocumentation
 */

// ============================================================================
// Engine
// ============================================================================

export {
  CanvasEngine,
  createEngine,
  type SendOptions,
  type EngineStreamChunk,
} from './engine/index.js';

// ============================================================================
// Providers
// ============================================================================

export {
  LLMClient,
  createLLMClient,
  EmbeddingClient,
  createEmbeddingClient,
  type LLMMessage,
  type LLMRequestOptions,
  type LLMResponse,
  type LLMStreamChunk,
  type EmbeddingRequestOptions,
  type EmbeddingResponse,
} from './providers/index.js';

// ============================================================================
// Memory
// ============================================================================

export { MemoryClient, createMemoryClient } from './memory/index.js';

// ============================================================================
// Types (re-export commonly used types)
// ============================================================================

export type {
  // Config (EngineConfig is the old CanvasConfig, now renamed to avoid conflict with unified CanvasConfig)
  EngineConfig,
  LLMConfig,
  EmbeddingConfig,
  MemoryConfig,
  AgentConfig,
  Logger,

  // Messages
  Message,
  MessageRole,
  Conversation,
  SendMessageInput,

  // Memory
  Frame,
  FrameType,
  RecallOptions,
  RecallResult,
  Preference,
  Correction,
  CaptureOptions,
  MemoryStats,
  MemoryContext,
  BuildContextOptions,

  // Providers
  LLMProvider,
  EmbeddingProvider,

  // Agents
  AgentStreamChunk,
  AgentResponse,
  AgentExecuteOptions,

  // UI Config
  CanvasUIConfig,
  AppConfig,
  ThemeConfig,
  ColorPalette,
  FontConfig,
  FeaturesConfig,
  SearchFeatureConfig,
  ChatFeatureConfig,
  DashboardFeatureConfig,
  PDFViewerConfig,
  NavigationItem,
  AdvancedConfig,
  SetupState,
  SetupStep,
  RuntimeSettings,
} from './types/index.js';

// UI Config defaults
export {
  DEFAULT_UI_CONFIG,
  DEFAULT_RUNTIME_SETTINGS,
} from './types/index.js';

// Message utilities
export { createMessage } from './types/index.js';

// ============================================================================
// Errors (re-export commonly used errors)
// ============================================================================

export {
  CanvasError,
  isCanvasError,
  isRetryable,
  getUserMessage,
  wrapError,

  // Specific errors
  ConfigError,
  MissingApiKeyError,
  ProviderError,
  ProviderAuthError,
  ProviderRateLimitError,
  MemoryError,
  ValidationError,
  NetworkError,
} from './errors/index.js';

// ============================================================================
// Agents
// ============================================================================

export { AgentExecutor, createAgentExecutor } from './agents/index.js';

// ============================================================================
// Themes
// ============================================================================

export {
  lightTheme,
  darkTheme,
  themes,
  themeToCSSVariables,
  applyTheme,
  createTheme,
  type Theme,
  type ThemeColors,
} from './themes/index.js';

// ============================================================================
// Utils
// ============================================================================

export {
  generateId,
  uuid,
  shortId,
  withRetry,
  retryable,
  sleep,
  estimateTokens,
  estimateMessageTokens,
  truncateToTokenLimit,
  chunkByTokens,
} from './utils/index.js';

// ============================================================================
// Settings
// ============================================================================

export {
  loadSettings,
  saveSettings,
  isSetupCompleted,
  getEnvConfig,
  getApiKey,
  getEmbeddingApiKey,
  validateEnv,
  mergeSettings,
  createDefaultSettings,
  resolvePath,
  getSettingsPath,
  type CanvasSettings,
  type CanvasEnv,
} from './settings/index.js';

// ============================================================================
// Configuration
// ============================================================================

export {
  DEFAULT_CANVAS_CONFIG,
  loadCanvasConfig,
  loadCanvasConfigSync,
  validateConfig,
  defineConfig,
  migrateLegacyConfig,
  mergeCanvasConfig,
  type CanvasConfig,
  type PartialCanvasConfig,
} from './config/index.js';

// ============================================================================
// Ingest (File Ingestion)
// ============================================================================

export {
  // Text extraction
  extractText,
  extractPdfText,
  extractDocxText,
  extractXlsxText,
  extractOfficeText,

  // Chunking
  chunkText,

  // Ingestion
  ingestFile,
  ingestFiles,

  // Utilities
  parseFileContent,
  getFileInfo,
  isExtensionSupported,
  isBinaryFormat,

  // Constants
  SUPPORTED_EXTENSIONS,
  BINARY_MIME_TYPES,

  // Types
  type FileInput,
  type IngestOptions,
  type IngestResult,
} from './ingest/index.js';
