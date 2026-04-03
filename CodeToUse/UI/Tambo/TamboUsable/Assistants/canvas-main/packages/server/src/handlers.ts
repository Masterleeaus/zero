/**
 * Request Handlers
 *
 * Framework-agnostic handlers for chat and recall endpoints.
 */

import {
  createEngine,
  type EngineConfig,
  type CanvasEngine,
  isCanvasError,
  getUserMessage,
} from '@memvid/canvas-core';
import type {
  ChatRequest,
  ChatResponse,
  RecallRequest,
  RecallResponse,
  StreamEvent,
  HandlerOptions,
} from './types.js';
import { createSSEResponse } from './stream.js';

/**
 * Canvas server instance
 */
export class CanvasServer {
  private engine: CanvasEngine;
  private options: HandlerOptions;

  constructor(config: EngineConfig, options: HandlerOptions = {}) {
    this.engine = createEngine(config);
    this.options = options;
  }

  /**
   * Handle chat request
   */
  async handleChat(request: ChatRequest): Promise<Response> {
    try {
      // Support both 'content' and 'message' fields for backward compatibility
      const content = request.content || request.message;
      const { agent, conversationId, includeContext, stream, llmProvider, llmModel, llmApiKey } = request;

      if (!content?.trim()) {
        return this.errorResponse('Message content is required', 400);
      }

      // If API key is provided in request, create a new engine with it
      let engine = this.engine;
      if (llmApiKey && llmProvider && llmApiKey.trim()) {
        const dynamicConfig: EngineConfig = {
          ...this.engine.config,
          llm: {
            provider: llmProvider as 'openai' | 'anthropic' | 'google',
            apiKey: llmApiKey.trim(),
            model: llmModel || this.engine.config.llm.model,
          },
        };
        engine = createEngine(dynamicConfig);
      } else if (!llmApiKey || !llmApiKey.trim()) {
        // If no API key provided in request, check if base engine has one
        if (!this.engine.config.llm.apiKey || !this.engine.config.llm.apiKey.trim()) {
          return this.errorResponse('API key is required. Please provide an API key in settings.', 400);
        }
      }

      if (stream) {
        return this.handleChatStream(content, { agent, conversationId, includeContext }, engine);
      }

      const { message, sources } = await engine.send(content, {
        agent,
        conversationId,
        includeContext,
      });

      const response: ChatResponse = { message, sources };

      return new Response(JSON.stringify(response), {
        headers: { 'Content-Type': 'application/json' },
      });
    } catch (error) {
      return this.handleError(error);
    }
  }

  /**
   * Handle streaming chat request
   */
  private handleChatStream(
    content: string,
    options: { agent?: string; conversationId?: string; includeContext?: boolean },
    engine?: CanvasEngine
  ): Response {
    const generator = this.streamToEvents(content, options, engine);
    return createSSEResponse(generator, this.getCORSHeaders());
  }

  /**
   * Convert engine stream to stream events
   */
  private async *streamToEvents(
    content: string,
    options: { agent?: string; conversationId?: string; includeContext?: boolean },
    engine?: CanvasEngine
  ): AsyncGenerator<StreamEvent> {
    const activeEngine = engine || this.engine;
    try {
      for await (const chunk of activeEngine.stream(content, options)) {
        if (chunk.type === 'text' && chunk.text) {
          yield { type: 'text', text: chunk.text };
        } else if (chunk.type === 'sources' && chunk.sources) {
          yield { type: 'sources', sources: chunk.sources };
        } else if (chunk.type === 'done' && chunk.message) {
          yield { type: 'done', message: chunk.message };
        } else if (chunk.type === 'error' && chunk.error) {
          yield {
            type: 'error',
            error: {
              message: chunk.error.message,
              code: isCanvasError(chunk.error) ? chunk.error.code : undefined,
            },
          };
        }
      }
    } catch (error) {
      const message = isCanvasError(error)
        ? getUserMessage(error)
        : 'An error occurred';

      yield {
        type: 'error',
        error: {
          message,
          code: isCanvasError(error) ? error.code : 'UNKNOWN',
        },
      };
    }
  }

  /**
   * Handle recall request
   */
  async handleRecall(request: RecallRequest): Promise<Response> {
    try {
      const { query, maxResults, minScore, types } = request;

      if (!query?.trim()) {
        return this.errorResponse('Query is required', 400);
      }

      const results = await this.engine.recall({
        query,
        limit: maxResults,
        minScore,
        types,
      });

      const response: RecallResponse = { results };

      return new Response(JSON.stringify(response), {
        headers: {
          'Content-Type': 'application/json',
          ...this.getCORSHeaders(),
        },
      });
    } catch (error) {
      return this.handleError(error);
    }
  }

  /**
   * Handle CORS preflight
   */
  handleOptions(): Response {
    return new Response(null, {
      status: 204,
      headers: {
        ...this.getCORSHeaders(),
        'Access-Control-Max-Age': '86400',
      },
    });
  }

  /**
   * Get CORS headers
   */
  private getCORSHeaders(): Record<string, string> {
    if (!this.options.cors) {
      return {};
    }

    const origin = this.options.allowedOrigins?.[0] ?? '*';

    return {
      'Access-Control-Allow-Origin': origin,
      'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type, Authorization',
    };
  }

  /**
   * Create error response
   */
  private errorResponse(message: string, status: number): Response {
    return new Response(
      JSON.stringify({ error: { message, code: 'BAD_REQUEST' } }),
      {
        status,
        headers: {
          'Content-Type': 'application/json',
          ...this.getCORSHeaders(),
        },
      }
    );
  }

  /**
   * Handle and format errors
   */
  private handleError(error: unknown): Response {
    // Call error handler if provided
    if (this.options.onError && error instanceof Error) {
      this.options.onError(error);
    }

    if (isCanvasError(error)) {
      const status = this.getStatusFromError(error);
      return new Response(
        JSON.stringify({
          error: {
            message: getUserMessage(error),
            code: error.code,
          },
        }),
        {
          status,
          headers: {
            'Content-Type': 'application/json',
            ...this.getCORSHeaders(),
          },
        }
      );
    }

    // Generic error
    return new Response(
      JSON.stringify({
        error: {
          message: 'An unexpected error occurred',
          code: 'INTERNAL_ERROR',
        },
      }),
      {
        status: 500,
        headers: {
          'Content-Type': 'application/json',
          ...this.getCORSHeaders(),
        },
      }
    );
  }

  /**
   * Map error category to HTTP status
   */
  private getStatusFromError(error: { category: string }): number {
    switch (error.category) {
      case 'config':
      case 'validation':
        return 400;
      case 'provider':
        return 502;
      case 'network':
        return 503;
      case 'memory':
        return 500;
      default:
        return 500;
    }
  }

  /**
   * Get the underlying engine
   */
  getEngine(): CanvasEngine {
    return this.engine;
  }
}

/**
 * Create a Canvas server instance
 */
export function createServer(
  config: EngineConfig,
  options?: HandlerOptions
): CanvasServer {
  return new CanvasServer(config, options);
}
