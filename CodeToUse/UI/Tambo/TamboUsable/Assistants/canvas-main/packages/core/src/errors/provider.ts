import { CanvasError, type ErrorCategory } from './base.js';

/**
 * Provider errors - thrown when LLM/embedding providers fail
 */
export class ProviderError extends CanvasError {
  readonly code: string = 'PROVIDER_ERROR';
  readonly category: ErrorCategory = 'provider';
  readonly retryable: boolean;

  constructor(
    message: string,
    options: {
      provider: string;
      retryable?: boolean;
      context?: Record<string, unknown>;
      cause?: Error;
    }
  ) {
    super(message, {
      context: { provider: options.provider, ...options.context },
      cause: options.cause,
    });
    this.retryable = options.retryable ?? false;
  }
}

/**
 * Authentication error with provider
 */
export class ProviderAuthError extends ProviderError {
  override readonly code = 'PROVIDER_AUTH_ERROR';
  override readonly category: ErrorCategory = 'authentication';
  override readonly retryable = false;

  constructor(provider: string, cause?: Error) {
    super(`Authentication failed with ${provider}`, {
      provider,
      cause,
    });
  }

  toUserMessage(): string {
    return `Authentication failed. Please check your API key for ${this.context.provider}.`;
  }
}

/**
 * Rate limit error from provider
 */
export class ProviderRateLimitError extends ProviderError {
  override readonly code = 'PROVIDER_RATE_LIMIT';
  override readonly retryable = true;

  /** Seconds to wait before retrying */
  readonly retryAfter?: number;

  constructor(
    provider: string,
    options: {
      retryAfter?: number;
      cause?: Error;
    } = {}
  ) {
    super(`Rate limit exceeded for ${provider}`, {
      provider,
      retryable: true,
      context: { retryAfter: options.retryAfter },
      cause: options.cause,
    });
    this.retryAfter = options.retryAfter;
  }

  toUserMessage(): string {
    if (this.retryAfter) {
      return `Rate limit reached. Please wait ${this.retryAfter} seconds.`;
    }
    return 'Rate limit reached. Please wait a moment and try again.';
  }
}

/**
 * Provider service unavailable
 */
export class ProviderUnavailableError extends ProviderError {
  override readonly code = 'PROVIDER_UNAVAILABLE';
  override readonly retryable = true;

  constructor(provider: string, cause?: Error) {
    super(`${provider} service is temporarily unavailable`, {
      provider,
      retryable: true,
      cause,
    });
  }

  toUserMessage(): string {
    return `The AI service is temporarily unavailable. Please try again in a moment.`;
  }
}

/**
 * Invalid response from provider
 */
export class ProviderResponseError extends ProviderError {
  override readonly code = 'PROVIDER_RESPONSE_ERROR';
  override readonly retryable = false;

  constructor(
    provider: string,
    message: string,
    options: {
      response?: unknown;
      cause?: Error;
    } = {}
  ) {
    super(message, {
      provider,
      context: { response: options.response },
      cause: options.cause,
    });
  }
}

/**
 * Provider timeout error
 */
export class ProviderTimeoutError extends ProviderError {
  override readonly code = 'PROVIDER_TIMEOUT';
  override readonly retryable = true;

  constructor(provider: string, timeoutMs: number) {
    super(`Request to ${provider} timed out after ${timeoutMs}ms`, {
      provider,
      retryable: true,
      context: { timeoutMs },
    });
  }

  toUserMessage(): string {
    return 'The request took too long. Please try again.';
  }
}

/**
 * Content filtered by provider
 */
export class ProviderContentFilterError extends ProviderError {
  override readonly code = 'PROVIDER_CONTENT_FILTER';
  override readonly retryable = false;

  constructor(provider: string) {
    super(`Content was filtered by ${provider}`, {
      provider,
    });
  }

  toUserMessage(): string {
    return 'Your request could not be processed due to content restrictions.';
  }
}
