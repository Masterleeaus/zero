/**
 * Embedding Provider
 *
 * Uses Vercel AI SDK for unified embedding access.
 * Supports OpenAI, Voyage, and other providers.
 */

import { embed, embedMany } from 'ai';
import { createOpenAI } from '@ai-sdk/openai';
import type { EmbeddingConfig, EmbeddingProvider } from '../types/provider.js';
import type { Logger } from '../types/config.js';
import {
  ProviderAuthError,
  ProviderRateLimitError,
  ProviderResponseError,
  wrapError,
} from '../errors/index.js';

/**
 * Embedding request options
 */
export interface EmbeddingRequestOptions {
  /** Text(s) to embed */
  input: string | string[];

  /** Model override */
  model?: string;

  /** Abort signal */
  abortSignal?: AbortSignal;
}

/**
 * Embedding response
 */
export interface EmbeddingResponse {
  /** Embedding vectors */
  embeddings: number[][];

  /** Token usage */
  usage: {
    totalTokens: number;
  };
}

/**
 * Embedding Client using Vercel AI SDK
 */
export class EmbeddingClient {
  private readonly config: EmbeddingConfig;
  private readonly logger?: Logger;

  constructor(config: EmbeddingConfig, logger?: Logger) {
    this.config = config;
    this.logger = logger;
  }

  /**
   * Get the embedding model
   */
  private getModel(modelOverride?: string) {
    const modelId = modelOverride ?? this.config.model ?? this.getDefaultModel();

    switch (this.config.provider) {
      case 'openai': {
        const openai = createOpenAI({
          apiKey: this.config.apiKey,
          baseURL: this.config.baseUrl,
        });
        return openai.textEmbeddingModel(modelId);
      }

      case 'voyage': {
        // Voyage uses OpenAI-compatible API
        const voyage = createOpenAI({
          apiKey: this.config.apiKey,
          baseURL: this.config.baseUrl ?? 'https://api.voyageai.com/v1',
        });
        return voyage.textEmbeddingModel(modelId);
      }

      default:
        throw new ProviderResponseError(
          this.config.provider,
          `Unsupported embedding provider: ${this.config.provider}`
        );
    }
  }

  /**
   * Get default model for provider
   */
  private getDefaultModel(): string {
    switch (this.config.provider) {
      case 'openai':
        return 'text-embedding-3-small';
      case 'voyage':
        return 'voyage-2';
      default:
        return 'default';
    }
  }

  /**
   * Generate embedding for a single text
   */
  async embedOne(text: string, options?: { model?: string }): Promise<number[]> {
    this.log('debug', 'Generating single embedding', {
      provider: this.config.provider,
      textLength: text.length,
    });

    try {
      const result = await embed({
        model: this.getModel(options?.model),
        value: text,
      });

      return result.embedding;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  /**
   * Generate embeddings for multiple texts
   */
  async embedMany(texts: string[], options?: { model?: string }): Promise<EmbeddingResponse> {
    this.log('debug', 'Generating batch embeddings', {
      provider: this.config.provider,
      count: texts.length,
    });

    try {
      const result = await embedMany({
        model: this.getModel(options?.model),
        values: texts,
      });

      return {
        embeddings: result.embeddings,
        usage: {
          totalTokens: result.usage?.tokens ?? 0,
        },
      };
    } catch (error) {
      throw this.handleError(error);
    }
  }

  /**
   * Generate embeddings (unified interface)
   */
  async embed(options: EmbeddingRequestOptions): Promise<EmbeddingResponse> {
    const { input, model } = options;

    if (Array.isArray(input)) {
      return this.embedMany(input, { model });
    }

    const embedding = await this.embedOne(input, { model });
    return {
      embeddings: [embedding],
      usage: { totalTokens: 0 }, // Single embed doesn't return usage
    };
  }

  /**
   * Handle and transform errors
   */
  private handleError(error: unknown): never {
    if (error instanceof Error) {
      const message = error.message.toLowerCase();

      if (message.includes('401') || message.includes('unauthorized') || message.includes('invalid api key')) {
        throw new ProviderAuthError(this.config.provider, error);
      }

      if (message.includes('429') || message.includes('rate limit')) {
        throw new ProviderRateLimitError(this.config.provider, { cause: error });
      }
    }

    throw wrapError(error, `Embedding request failed: ${error}`);
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
      this.logger[level](`[Embedding:${this.config.provider}] ${message}`, data);
    }
  }

  /**
   * Get provider name
   */
  get providerName(): EmbeddingProvider {
    return this.config.provider;
  }
}

/**
 * Create an embedding client
 */
export function createEmbeddingClient(
  config: EmbeddingConfig,
  logger?: Logger
): EmbeddingClient {
  return new EmbeddingClient(config, logger);
}
