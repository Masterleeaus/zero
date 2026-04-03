/**
 * Base error class for all Canvas errors.
 * Provides consistent error structure with codes, context, and retry hints.
 */
export abstract class CanvasError extends Error {
  /** Unique error code for programmatic handling */
  abstract readonly code: string;

  /** Error category for grouping related errors */
  abstract readonly category: ErrorCategory;

  /** Whether this error is retryable */
  abstract readonly retryable: boolean;

  /** Additional context about the error */
  readonly context: Record<string, unknown>;

  /** Original error if this wraps another error */
  readonly cause?: Error;

  /** Timestamp when error occurred */
  readonly timestamp: Date;

  constructor(
    message: string,
    options: {
      context?: Record<string, unknown>;
      cause?: Error;
    } = {}
  ) {
    super(message);
    this.name = this.constructor.name;
    this.context = options.context ?? {};
    this.cause = options.cause;
    this.timestamp = new Date();

    // Maintains proper stack trace in V8 environments
    if (Error.captureStackTrace) {
      Error.captureStackTrace(this, this.constructor);
    }
  }

  /**
   * Returns a user-friendly error message
   */
  toUserMessage(): string {
    return this.message;
  }

  /**
   * Returns full error details for logging
   */
  toJSON(): ErrorJSON {
    return {
      name: this.name,
      code: this.code,
      category: this.category,
      message: this.message,
      context: this.context,
      retryable: this.retryable,
      timestamp: this.timestamp.toISOString(),
      stack: this.stack,
      cause: this.cause
        ? {
            name: this.cause.name,
            message: this.cause.message,
            stack: this.cause.stack,
          }
        : undefined,
    };
  }
}

/**
 * Error categories for grouping
 */
export type ErrorCategory =
  | 'configuration'
  | 'authentication'
  | 'network'
  | 'provider'
  | 'memory'
  | 'validation'
  | 'runtime'
  | 'unknown';

/**
 * JSON representation of an error
 */
export interface ErrorJSON {
  name: string;
  code: string;
  category: ErrorCategory;
  message: string;
  context: Record<string, unknown>;
  retryable: boolean;
  timestamp: string;
  stack?: string;
  cause?: {
    name: string;
    message: string;
    stack?: string;
  };
}
