/**
 * Streaming Utilities
 *
 * Helpers for creating SSE streams.
 */

import type { StreamEvent } from './types.js';

/**
 * Create a readable stream from an async generator
 */
export function createStream<T>(
  generator: AsyncGenerator<T>,
  encoder: (chunk: T) => string
): ReadableStream<Uint8Array> {
  const textEncoder = new TextEncoder();

  return new ReadableStream({
    async start(controller) {
      try {
        for await (const chunk of generator) {
          const encoded = encoder(chunk);
          controller.enqueue(textEncoder.encode(encoded));
        }
        controller.close();
      } catch (error) {
        controller.error(error);
      }
    },
  });
}

/**
 * Encode a stream event as SSE format
 */
export function encodeSSE(event: StreamEvent): string {
  const data = JSON.stringify(event);
  return `data: ${data}\n\n`;
}

/**
 * Create SSE stream from async generator
 */
export function createSSEStream(
  generator: AsyncGenerator<StreamEvent>
): ReadableStream<Uint8Array> {
  return createStream(generator, encodeSSE);
}

/**
 * SSE response headers
 */
export const SSE_HEADERS = {
  'Content-Type': 'text/event-stream',
  'Cache-Control': 'no-cache, no-transform',
  Connection: 'keep-alive',
} as const;

/**
 * Create a complete SSE Response
 */
export function createSSEResponse(
  generator: AsyncGenerator<StreamEvent>,
  headers?: Record<string, string>
): Response {
  return new Response(createSSEStream(generator), {
    headers: {
      ...SSE_HEADERS,
      ...headers,
    },
  });
}

/**
 * Parse SSE events from a ReadableStream
 */
export async function* parseSSEStream(
  stream: ReadableStream<Uint8Array>
): AsyncGenerator<StreamEvent> {
  const reader = stream.getReader();
  const decoder = new TextDecoder();
  let buffer = '';

  try {
    while (true) {
      const { done, value } = await reader.read();

      if (done) break;

      buffer += decoder.decode(value, { stream: true });

      // Process complete events
      const lines = buffer.split('\n\n');
      buffer = lines.pop() ?? '';

      for (const line of lines) {
        if (line.startsWith('data: ')) {
          const data = line.slice(6);
          try {
            const event = JSON.parse(data) as StreamEvent;
            yield event;
          } catch {
            // Skip invalid JSON
          }
        }
      }
    }
  } finally {
    reader.releaseLock();
  }
}
