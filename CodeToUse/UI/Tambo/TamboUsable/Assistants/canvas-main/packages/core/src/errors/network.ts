import { CanvasError, type ErrorCategory } from './base.js';

/**
 * Network errors - thrown when network operations fail
 */
export class NetworkError extends CanvasError {
  readonly code: string = 'NETWORK_ERROR';
  readonly category: ErrorCategory = 'network';
  readonly retryable: boolean = true;

  constructor(
    message: string,
    options: {
      url?: string;
      statusCode?: number;
      context?: Record<string, unknown>;
      cause?: Error;
    } = {}
  ) {
    super(message, {
      context: {
        url: options.url,
        statusCode: options.statusCode,
        ...options.context,
      },
      cause: options.cause,
    });
  }

  toUserMessage(): string {
    return 'Network error. Please check your connection and try again.';
  }
}

/**
 * Connection failed
 */
export class ConnectionError extends NetworkError {
  override readonly code = 'CONNECTION_FAILED';

  constructor(url: string, cause?: Error) {
    super(`Failed to connect to ${url}`, { url, cause });
  }
}

/**
 * Request timeout
 */
export class TimeoutError extends NetworkError {
  override readonly code = 'TIMEOUT';

  constructor(timeoutMs: number, url?: string) {
    super(`Request timed out after ${timeoutMs}ms`, {
      url,
      context: { timeoutMs },
    });
  }

  toUserMessage(): string {
    return 'Request timed out. Please try again.';
  }
}

/**
 * HTTP error response
 */
export class HttpError extends NetworkError {
  override readonly code = 'HTTP_ERROR';
  override readonly retryable: boolean;

  constructor(
    statusCode: number,
    message: string,
    options: {
      url?: string;
      body?: unknown;
      cause?: Error;
    } = {}
  ) {
    super(message, {
      url: options.url,
      statusCode,
      context: { body: options.body },
      cause: options.cause,
    });

    // 5xx errors are retryable, 4xx are not
    this.retryable = statusCode >= 500;
  }

  toUserMessage(): string {
    const status = this.context.statusCode as number;

    if (status >= 500) {
      return 'Server error. Please try again later.';
    }

    if (status === 429) {
      return 'Too many requests. Please wait a moment.';
    }

    if (status === 401 || status === 403) {
      return 'Authentication error. Please check your API key.';
    }

    return this.message;
  }
}

/**
 * Offline error
 */
export class OfflineError extends NetworkError {
  override readonly code = 'OFFLINE';
  override readonly retryable = true;

  constructor() {
    super('No internet connection');
  }

  toUserMessage(): string {
    return 'You appear to be offline. Please check your connection.';
  }
}
