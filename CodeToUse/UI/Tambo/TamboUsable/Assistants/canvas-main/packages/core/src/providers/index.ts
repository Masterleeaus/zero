/**
 * Providers
 *
 * LLM and embedding providers powered by Vercel AI SDK.
 *
 * @example
 * ```typescript
 * import { createLLMClient, createEmbeddingClient } from '@memvid/canvas-core';
 *
 * // Create LLM client
 * const llm = createLLMClient({
 *   provider: 'anthropic',
 *   apiKey: process.env.ANTHROPIC_API_KEY,
 * });
 *
 * // Chat
 * const response = await llm.chat({
 *   messages: [{ role: 'user', content: 'Hello!' }],
 * });
 *
 * // Stream
 * for await (const chunk of llm.stream({ messages })) {
 *   if (chunk.type === 'text') {
 *     process.stdout.write(chunk.text);
 *   }
 * }
 *
 * // Embeddings
 * const embedding = createEmbeddingClient({
 *   provider: 'openai',
 *   apiKey: process.env.OPENAI_API_KEY,
 * });
 *
 * const vector = await embedding.embedOne('Hello world');
 * ```
 */

export {
  LLMClient,
  createLLMClient,
  type LLMMessage,
  type LLMRequestOptions,
  type LLMResponse,
  type LLMStreamChunk,
} from './llm.js';

export {
  EmbeddingClient,
  createEmbeddingClient,
  type EmbeddingRequestOptions,
  type EmbeddingResponse,
} from './embedding.js';
