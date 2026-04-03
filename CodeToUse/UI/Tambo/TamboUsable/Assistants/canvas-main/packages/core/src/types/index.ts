/**
 * Canvas Core Types
 *
 * @example
 * ```typescript
 * import type { Message, CanvasConfig, AgentConfig } from '@memvid/canvas-core/types';
 * ```
 */

// Provider types
export type {
  LLMProvider,
  EmbeddingProvider,
  LLMConfig,
  EmbeddingConfig,
  ProviderCapabilities,
} from './provider.js';

export {
  DEFAULT_MODELS,
  DEFAULT_EMBEDDING_MODELS,
  PROVIDER_CAPABILITIES,
} from './provider.js';

// Message types
export type {
  MessageRole,
  MessageStatus,
  Message,
  MessageMetadata,
  MessageSource,
  ToolCall,
  Conversation,
  ConversationMetadata,
  SendMessageInput,
  Attachment,
} from './message.js';

export { createMessage } from './message.js';

// Memory types
export type {
  MemoryConfig,
  FrameType,
  Frame,
  FrameInfo,
  SearchMode,
  RecallOptions,
  RecallResult,
  CaptureOptions,
  Correction,
  Preference,
  MemoryStats,
  MemoryContext,
  BuildContextOptions,
} from './memory.js';

// Agent types
export type {
  AgentConfig,
  AgentMemoryConfig,
  ToolDefinition,
  ToolParameters,
  ToolParameterProperty,
  ToolHandler,
  ToolContext,
  AgentExecuteOptions,
  AgentResponse,
  AgentToolCall,
  AgentSource,
  AgentStreamChunk,
  AgentTemplate,
} from './agent.js';

export { getTemplateConfig } from './agent.js';

// Config types (EngineConfig is the engine-level config)
export type {
  EngineConfig,
  RetryConfig,
  Logger,
} from './config.js';

export {
  DEFAULT_ENGINE_CONFIG,
  normalizeMemoryConfig,
  mergeEngineConfig,
} from './config.js';

// UI Config types
export type {
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
} from './ui-config.js';

export {
  DEFAULT_UI_CONFIG,
  DEFAULT_RUNTIME_SETTINGS,
} from './ui-config.js';
