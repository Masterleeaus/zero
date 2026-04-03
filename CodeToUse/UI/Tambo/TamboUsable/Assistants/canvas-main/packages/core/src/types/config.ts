/**
 * Configuration Types
 *
 * Types for Canvas engine configuration.
 * Note: For UI/branding config, see ../config/schema.ts (CanvasConfig)
 */

import type { LLMConfig, EmbeddingConfig } from './provider.js';
import type { MemoryConfig } from './memory.js';
import type { AgentConfig } from './agent.js';

/**
 * Canvas Engine configuration (runtime/backend settings)
 * @deprecated Use CanvasConfig from config/schema.ts for unified configuration
 */
export interface EngineConfig {
  /** LLM provider configuration */
  llm: LLMConfig;

  /** Embedding provider configuration (defaults to OpenAI) */
  embedding?: EmbeddingConfig;

  /** Memory configuration */
  memory: MemoryConfig | string;

  /** Agent configurations */
  agents?: AgentConfig[];

  /** Default agent name */
  defaultAgent?: string;

  /** Memvid API key (for cloud sync) */
  memvidApiKey?: string;

  /** Enable debug logging */
  debug?: boolean;

  /** Custom logger */
  logger?: Logger;

  /** Request timeout in ms (default: 30000) */
  timeout?: number;

  /** Retry configuration */
  retry?: RetryConfig;
}

/**
 * Retry configuration
 */
export interface RetryConfig {
  /** Maximum number of retries (default: 3) */
  maxRetries?: number;

  /** Initial delay in ms (default: 1000) */
  initialDelay?: number;

  /** Maximum delay in ms (default: 30000) */
  maxDelay?: number;

  /** Backoff multiplier (default: 2) */
  backoffMultiplier?: number;

  /** Only retry these error codes */
  retryOn?: string[];
}

/**
 * Logger interface
 */
export interface Logger {
  debug(message: string, data?: Record<string, unknown>): void;
  info(message: string, data?: Record<string, unknown>): void;
  warn(message: string, data?: Record<string, unknown>): void;
  error(message: string, data?: Record<string, unknown>): void;
}

/**
 * Default configuration values
 */
export const DEFAULT_ENGINE_CONFIG: Partial<EngineConfig> = {
  defaultAgent: 'assistant',
  debug: false,
  timeout: 30000,
  retry: {
    maxRetries: 3,
    initialDelay: 1000,
    maxDelay: 30000,
    backoffMultiplier: 2,
  },
};

/**
 * Normalize memory config (string -> object)
 */
export function normalizeMemoryConfig(
  config: MemoryConfig | string
): MemoryConfig {
  if (typeof config === 'string') {
    return {
      path: config,
      autoCreate: true,
      autoSave: true,
      autoSaveInterval: 5000,
    };
  }
  return config;
}

/**
 * Merge engine config with defaults
 */
export function mergeEngineConfig(config: EngineConfig): Required<EngineConfig> {
  return {
    ...DEFAULT_ENGINE_CONFIG,
    ...config,
    memory: normalizeMemoryConfig(config.memory),
    agents: config.agents ?? [{ name: 'assistant' }],
    embedding: config.embedding ?? {
      provider: 'openai',
      apiKey: config.llm.apiKey, // Default to same key if OpenAI
    },
    retry: {
      ...DEFAULT_ENGINE_CONFIG.retry,
      ...config.retry,
    },
  } as Required<EngineConfig>;
}
