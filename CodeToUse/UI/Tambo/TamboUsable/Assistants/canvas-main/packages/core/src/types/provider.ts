/**
 * Provider Types
 *
 * Types for LLM and embedding providers.
 */

/**
 * Supported LLM providers
 */
export type LLMProvider = "anthropic" | "openai" | "google" | "custom";

/**
 * Supported embedding providers
 */
export type EmbeddingProvider = "openai" | "voyage" | "custom";

/**
 * LLM provider configuration
 */
export interface LLMConfig {
  /** Provider name */
  provider: LLMProvider;

  /** API key for the provider */
  apiKey: string;

  /** Model to use (e.g., 'claude-sonnet-4-20250514', 'gpt-4') */
  model?: string;

  /** Base URL for API (for custom providers) */
  baseUrl?: string;

  /** Request timeout in milliseconds */
  timeout?: number;

  /** Maximum tokens in response */
  maxTokens?: number;

  /** Temperature (0-1) */
  temperature?: number;
}

/**
 * Embedding provider configuration
 */
export interface EmbeddingConfig {
  /** Provider name */
  provider: EmbeddingProvider;

  /** API key for the provider */
  apiKey: string;

  /** Model to use (e.g., 'text-embedding-3-small') */
  model?: string;

  /** Base URL for API (for custom providers) */
  baseUrl?: string;

  /** Request timeout in milliseconds */
  timeout?: number;

  /** Dimensions for embedding (if provider supports) */
  dimensions?: number;
}

/**
 * Default models for each provider
 */
export const DEFAULT_MODELS: Record<LLMProvider, string> = {
  anthropic: "claude-sonnet-4-20250514",
  openai: "gpt-4o-mini",
  google: "gemini-1.5-pro",
  custom: "default",
};

/**
 * Default embedding models
 */
export const DEFAULT_EMBEDDING_MODELS: Record<EmbeddingProvider, string> = {
  openai: "text-embedding-3-small",
  voyage: "voyage-2",
  custom: "default",
};

/**
 * Provider capabilities
 */
export interface ProviderCapabilities {
  /** Supports streaming responses */
  streaming: boolean;

  /** Supports function/tool calling */
  tools: boolean;

  /** Supports vision (image input) */
  vision: boolean;

  /** Maximum context length in tokens */
  maxContextLength: number;
}

/**
 * Known provider capabilities
 */
export const PROVIDER_CAPABILITIES: Record<LLMProvider, ProviderCapabilities> =
  {
    anthropic: {
      streaming: true,
      tools: true,
      vision: true,
      maxContextLength: 200000,
    },
    openai: {
      streaming: true,
      tools: true,
      vision: true,
      maxContextLength: 128000,
    },
    google: {
      streaming: true,
      tools: true,
      vision: true,
      maxContextLength: 1000000,
    },
    custom: {
      streaming: false,
      tools: false,
      vision: false,
      maxContextLength: 4096,
    },
  };
