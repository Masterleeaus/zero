/**
 * Types-Only Exports
 *
 * This file exports ONLY TypeScript types with no runtime code.
 * Safe to import in client-side code without pulling in native dependencies.
 *
 * @example
 * ```typescript
 * import type { RecallResult, Message, LLMConfig } from '@memvid/canvas-core/types-only';
 * ```
 */

// ============================================================================
// Provider Types
// ============================================================================

export type {
  LLMProvider,
  EmbeddingProvider,
  LLMConfig,
  EmbeddingConfig,
  ProviderCapabilities,
} from './types/provider.js';

// ============================================================================
// Message Types
// ============================================================================

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
} from './types/message.js';

// ============================================================================
// Memory Types
// ============================================================================

export type {
  MemoryConfig,
  FrameType,
  Frame,
  SearchMode,
  RecallOptions,
  RecallResult,
  CaptureOptions,
  Correction,
  Preference,
  MemoryStats,
  MemoryContext,
  BuildContextOptions,
} from './types/memory.js';

// ============================================================================
// Agent Types
// ============================================================================

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
} from './types/agent.js';

// ============================================================================
// Config Types
// ============================================================================

export type {
  EngineConfig,
  RetryConfig,
  Logger,
} from './types/config.js';

// ============================================================================
// Theme Types
// ============================================================================

export type { Theme, ThemeColors } from './themes/theme.js';

// ============================================================================
// Engine Types
// ============================================================================

export type { SendOptions, EngineStreamChunk } from './engine/canvas-engine.js';

// ============================================================================
// Provider Client Types
// ============================================================================

export type {
  LLMMessage,
  LLMRequestOptions,
  LLMResponse,
  LLMStreamChunk,
} from './providers/llm.js';

export type {
  EmbeddingRequestOptions,
  EmbeddingResponse,
} from './providers/embedding.js';

// ============================================================================
// UI Config Types (client-safe)
// ============================================================================

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
} from './types/ui-config.js';
