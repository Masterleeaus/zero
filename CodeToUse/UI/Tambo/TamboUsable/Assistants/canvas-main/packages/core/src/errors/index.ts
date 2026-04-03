/**
 * Canvas Error System
 *
 * All errors in Canvas extend CanvasError and provide:
 * - Unique error codes for programmatic handling
 * - Categories for grouping (config, auth, network, provider, memory, validation)
 * - Retry hints (whether the error is retryable)
 * - User-friendly messages
 * - Full context for debugging
 *
 * @example
 * ```typescript
 * import { isCanvasError, ProviderRateLimitError } from '@memvid/canvas-core/errors';
 *
 * try {
 *   await engine.send(message);
 * } catch (error) {
 *   if (isCanvasError(error)) {
 *     if (error.retryable) {
 *       // Retry with backoff
 *     }
 *     console.error(error.toUserMessage());
 *   }
 * }
 * ```
 */

// Base
export { CanvasError, type ErrorCategory, type ErrorJSON } from './base.js';

// Configuration errors
export {
  ConfigError,
  MissingApiKeyError,
  InvalidProviderError,
  MissingMemoryPathError,
  InvalidConfigValueError,
} from './config.js';

// Provider errors (LLM/Embedding)
export {
  ProviderError,
  ProviderAuthError,
  ProviderRateLimitError,
  ProviderUnavailableError,
  ProviderResponseError,
  ProviderTimeoutError,
  ProviderContentFilterError,
} from './provider.js';

// Memory errors (.mv2 file operations)
export {
  MemoryError,
  MemoryNotFoundError,
  MemoryCorruptedError,
  MemoryReadError,
  MemoryWriteError,
  MemorySearchError,
  MemoryCapacityError,
  MemoryLockError,
} from './memory.js';

// Validation errors
export {
  ValidationError,
  RequiredFieldError,
  InvalidMessageError,
  MessageTooLongError,
  InvalidAgentError,
  SchemaValidationError,
  fromZodError,
  type ValidationIssue,
} from './validation.js';

// Network errors
export {
  NetworkError,
  ConnectionError,
  TimeoutError,
  HttpError,
  OfflineError,
} from './network.js';

// ============================================================================
// Error utilities
// ============================================================================

import { CanvasError, type ErrorCategory } from './base.js';

/**
 * Type guard to check if an error is a CanvasError
 */
export function isCanvasError(error: unknown): error is CanvasError {
  return error instanceof CanvasError;
}

/**
 * Type guard to check if error has a specific code
 */
export function hasErrorCode<T extends CanvasError>(
  error: unknown,
  code: string
): error is T {
  return isCanvasError(error) && error.code === code;
}

/**
 * Type guard to check if error is in a category
 */
export function isErrorCategory(
  error: unknown,
  category: ErrorCategory
): error is CanvasError {
  return isCanvasError(error) && error.category === category;
}

/**
 * Check if an error is retryable
 */
export function isRetryable(error: unknown): boolean {
  if (isCanvasError(error)) {
    return error.retryable;
  }
  return false;
}

/**
 * Get user-friendly message from any error
 */
export function getUserMessage(error: unknown): string {
  if (isCanvasError(error)) {
    return error.toUserMessage();
  }

  if (error instanceof Error) {
    return error.message;
  }

  return 'An unexpected error occurred';
}

/**
 * Wrap unknown error in CanvasError
 */
export function wrapError(error: unknown, message?: string): CanvasError {
  if (isCanvasError(error)) {
    return error;
  }

  const cause = error instanceof Error ? error : undefined;
  const msg = message ?? cause?.message ?? 'Unknown error';

  return new UnknownError(msg, { cause });
}

/**
 * Unknown/unexpected error
 */
export class UnknownError extends CanvasError {
  readonly code = 'UNKNOWN_ERROR';
  readonly category: ErrorCategory = 'unknown';
  readonly retryable = false;

  constructor(
    message: string,
    options: {
      cause?: Error;
      context?: Record<string, unknown>;
    } = {}
  ) {
    super(message, options);
  }
}
