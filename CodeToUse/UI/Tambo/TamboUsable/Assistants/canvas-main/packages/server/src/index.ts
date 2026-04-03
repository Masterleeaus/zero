/**
 * @memvid/canvas-server
 *
 * Server utilities for Canvas AI UI Kit.
 */

// Handlers
export { CanvasServer, createServer } from './handlers.js';

// Streaming
export {
  createStream,
  createSSEStream,
  createSSEResponse,
  encodeSSE,
  parseSSEStream,
  SSE_HEADERS,
} from './stream.js';

// Types
export type {
  ChatRequest,
  ChatResponse,
  RecallRequest,
  RecallResponse,
  StreamEvent,
  HandlerOptions,
} from './types.js';

// Re-export core types
export type {
  EngineConfig,
  LLMConfig,
  EmbeddingConfig,
  AgentConfig,
  Message,
  RecallResult,
} from '@memvid/canvas-core';
