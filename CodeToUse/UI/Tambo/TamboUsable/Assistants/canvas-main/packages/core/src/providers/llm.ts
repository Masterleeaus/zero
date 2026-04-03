/**
 * LLM Provider
 *
 * Uses Vercel AI SDK for unified LLM access.
 * Supports Anthropic, OpenAI, and other providers.
 */

import { generateText, streamText, type CoreMessage } from 'ai';
import { createAnthropic } from '@ai-sdk/anthropic';
import { createOpenAI } from '@ai-sdk/openai';
import type { LLMConfig, LLMProvider } from '../types/provider.js';
import type { Logger } from '../types/config.js';
import {
  ProviderAuthError,
  ProviderRateLimitError,
  ProviderUnavailableError,
  ProviderResponseError,
  wrapError,
} from '../errors/index.js';

/**
 * Message format for LLM
 */
export interface LLMMessage {
  role: 'user' | 'assistant' | 'system';
  content: string;
}

/**
 * LLM request options
 */
export interface LLMRequestOptions {
  messages: LLMMessage[];
  model?: string;
  temperature?: number;
  maxTokens?: number;
  abortSignal?: AbortSignal;
}

/**
 * LLM response
 */
export interface LLMResponse {
  content: string;
  usage: {
    inputTokens: number;
    outputTokens: number;
    totalTokens: number;
  };
  finishReason: 'stop' | 'length' | 'tool-calls' | 'content-filter' | 'other';
}

/**
 * Stream chunk
 */
export interface LLMStreamChunk {
  type: 'text' | 'done';
  text?: string;
  usage?: {
    inputTokens: number;
    outputTokens: number;
    totalTokens: number;
  };
}

/**
 * LLM Client using Vercel AI SDK
 */
export class LLMClient {
  private readonly config: LLMConfig;
  private readonly logger?: Logger;
  private readonly provider: ReturnType<typeof createAnthropic> | ReturnType<typeof createOpenAI>;

  constructor(config: LLMConfig, logger?: Logger) {
    this.config = config;
    this.logger = logger;
    this.provider = this.createProvider();
  }

  /**
   * Create the appropriate provider instance
   */
  private createProvider() {
    switch (this.config.provider) {
      case 'anthropic':
        return createAnthropic({
          apiKey: this.config.apiKey,
          baseURL: this.config.baseUrl,
        });

      case 'openai':
        return createOpenAI({
          apiKey: this.config.apiKey,
          baseURL: this.config.baseUrl,
        });

      default:
        throw new ProviderResponseError(
          this.config.provider,
          `Unsupported provider: ${this.config.provider}`
        );
    }
  }

  /**
   * Get the model ID for the provider
   */
  private getModel(modelOverride?: string) {
    const modelId = modelOverride ?? this.config.model ?? this.getDefaultModel();
    return this.provider(modelId);
  }

  /**
   * Get default model for provider
   */
  private getDefaultModel(): string {
    switch (this.config.provider) {
      case 'anthropic':
        return 'claude-sonnet-4-20250514';
      case 'openai':
        return 'gpt-4o';
      default:
        return 'default';
    }
  }

  /**
   * Send a chat completion request
   */
  async chat(options: LLMRequestOptions): Promise<LLMResponse> {
    this.log('debug', 'Sending chat request', {
      provider: this.config.provider,
      messageCount: options.messages.length,
    });

    try {
      const result = await generateText({
        model: this.getModel(options.model),
        messages: options.messages as CoreMessage[],
        temperature: options.temperature ?? this.config.temperature,
        maxOutputTokens: options.maxTokens ?? this.config.maxTokens,
        abortSignal: options.abortSignal,
      });

      return {
        content: result.text,
        usage: {
          inputTokens: result.usage.inputTokens ?? 0,
          outputTokens: result.usage.outputTokens ?? 0,
          totalTokens: result.usage.totalTokens ?? 0,
        },
        finishReason: result.finishReason as 'stop' | 'length' | 'tool-calls' | 'content-filter' | 'other',
      };
    } catch (error) {
      throw this.handleError(error);
    }
  }

  /**
   * Stream a chat completion request
   */
  async *stream(
    options: LLMRequestOptions
  ): AsyncGenerator<LLMStreamChunk, void, unknown> {
    this.log('debug', 'Starting stream request', {
      provider: this.config.provider,
      messageCount: options.messages.length,
    });

    try {
      const result = streamText({
        model: this.getModel(options.model),
        messages: options.messages as CoreMessage[],
        temperature: options.temperature ?? this.config.temperature,
        maxOutputTokens: options.maxTokens ?? this.config.maxTokens,
        abortSignal: options.abortSignal,
      });

      for await (const chunk of result.textStream) {
        yield { type: 'text', text: chunk };
      }

      // Get final usage
      const usage = await result.totalUsage;
      yield {
        type: 'done',
        usage: {
          inputTokens: usage.inputTokens ?? 0,
          outputTokens: usage.outputTokens ?? 0,
          totalTokens: usage.totalTokens ?? 0,
        },
      };
    } catch (error) {
      throw this.handleError(error);
    }
  }

  /**
   * Handle and transform errors
   */
  private handleError(error: unknown): never {
    // Check for specific error types
    if (error instanceof Error) {
      const message = error.message.toLowerCase();

      if (message.includes('401') || message.includes('unauthorized') || message.includes('invalid api key')) {
        throw new ProviderAuthError(this.config.provider, error);
      }

      if (message.includes('429') || message.includes('rate limit')) {
        throw new ProviderRateLimitError(this.config.provider, { cause: error });
      }

      if (message.includes('503') || message.includes('502') || message.includes('unavailable')) {
        throw new ProviderUnavailableError(this.config.provider, error);
      }
    }

    throw wrapError(error, `LLM request failed: ${error}`);
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
      this.logger[level](`[LLM:${this.config.provider}] ${message}`, data);
    }
  }

  /**
   * Get provider name
   */
  get providerName(): LLMProvider {
    return this.config.provider;
  }
}

/**
 * Create an LLM client
 */
export function createLLMClient(config: LLMConfig, logger?: Logger): LLMClient {
  return new LLMClient(config, logger);
}
