/**
 * Retry Utilities
 *
 * Exponential backoff retry logic.
 */

import type { RetryConfig } from '../types/config.js';

/**
 * Default retry configuration
 */
export const DEFAULT_RETRY_CONFIG: Required<RetryConfig> = {
  maxRetries: 3,
  initialDelay: 1000,
  maxDelay: 30000,
  backoffMultiplier: 2,
  retryOn: [],
};

/**
 * Sleep for a given number of milliseconds
 */
export function sleep(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Calculate delay for exponential backoff
 */
export function calculateBackoff(
  attempt: number,
  config: Required<RetryConfig>
): number {
  const delay = config.initialDelay * Math.pow(config.backoffMultiplier, attempt);
  // Add jitter (±10%)
  const jitter = delay * 0.1 * (Math.random() * 2 - 1);
  return Math.min(delay + jitter, config.maxDelay);
}

/**
 * Retry options
 */
export interface RetryOptions {
  /** Retry configuration */
  config?: Partial<RetryConfig>;

  /** Function to determine if error is retryable */
  isRetryable?: (error: unknown) => boolean;

  /** Callback on each retry */
  onRetry?: (error: unknown, attempt: number) => void;

  /** Abort signal */
  abortSignal?: AbortSignal;
}

/**
 * Execute a function with retry logic
 */
export async function withRetry<T>(
  fn: () => Promise<T>,
  options: RetryOptions = {}
): Promise<T> {
  const config: Required<RetryConfig> = {
    ...DEFAULT_RETRY_CONFIG,
    ...options.config,
  };

  const isRetryable = options.isRetryable ?? (() => true);

  let lastError: unknown;

  for (let attempt = 0; attempt <= config.maxRetries; attempt++) {
    // Check abort signal
    if (options.abortSignal?.aborted) {
      throw new Error('Operation aborted');
    }

    try {
      return await fn();
    } catch (error) {
      lastError = error;

      // Check if we should retry
      if (attempt >= config.maxRetries || !isRetryable(error)) {
        throw error;
      }

      // Calculate delay
      const delay = calculateBackoff(attempt, config);

      // Notify of retry
      options.onRetry?.(error, attempt + 1);

      // Wait before retry
      await sleep(delay);
    }
  }

  throw lastError;
}

/**
 * Create a retryable function
 */
export function retryable<T extends (...args: unknown[]) => Promise<unknown>>(
  fn: T,
  options: RetryOptions = {}
): T {
  return ((...args: unknown[]) => withRetry(() => fn(...args), options)) as T;
}
