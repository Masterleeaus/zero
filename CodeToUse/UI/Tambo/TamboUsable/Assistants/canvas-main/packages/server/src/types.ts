/**
 * Server Types
 */

import type { Message, RecallResult, FrameType } from '@memvid/canvas-core';

/**
 * Chat request body
 */
export interface ChatRequest {
  /** Message content */
  content: string;

  /** Agent to use */
  agent?: string;

  /** Conversation ID */
  conversationId?: string;

  /** Whether to include memory context */
  includeContext?: boolean;

  /** Whether to stream response */
  stream?: boolean;

  /** LLM provider override (from client settings) */
  llmProvider?: string;

  /** LLM model override (from client settings) */
  llmModel?: string;

  /** LLM API key override (from client settings) */
  llmApiKey?: string;

  /** Legacy field names for backward compatibility */
  message?: string;
}

/**
 * Chat response (non-streaming)
 */
export interface ChatResponse {
  /** Response message */
  message: Message;

  /** Memory sources used */
  sources: RecallResult[];
}

/**
 * Recall request body
 */
export interface RecallRequest {
  /** Search query */
  query: string;

  /** Maximum results */
  maxResults?: number;

  /** Minimum similarity score */
  minScore?: number;

  /** Filter by frame types */
  types?: FrameType[];
}

/**
 * Recall response
 */
export interface RecallResponse {
  /** Search results */
  results: RecallResult[];
}

/**
 * Stream event types
 */
export type StreamEvent =
  | { type: 'text'; text: string }
  | { type: 'sources'; sources: RecallResult[] }
  | { type: 'done'; message: Message }
  | { type: 'error'; error: { message: string; code?: string } };

/**
 * Server handler options
 */
export interface HandlerOptions {
  /** Enable CORS */
  cors?: boolean;

  /** Allowed origins for CORS */
  allowedOrigins?: string[];

  /** Rate limiting (requests per minute) */
  rateLimit?: number;

  /** Custom error handler */
  onError?: (error: Error) => void;
}
