import { CanvasError, type ErrorCategory } from './base.js';

/**
 * Memory errors - thrown when .mv2 file operations fail
 */
export class MemoryError extends CanvasError {
  readonly code: string = 'MEMORY_ERROR';
  readonly category: ErrorCategory = 'memory';
  readonly retryable: boolean;

  constructor(
    message: string,
    options: {
      path?: string;
      retryable?: boolean;
      context?: Record<string, unknown>;
      cause?: Error;
    } = {}
  ) {
    super(message, {
      context: { path: options.path, ...options.context },
      cause: options.cause,
    });
    this.retryable = options.retryable ?? false;
  }
}

/**
 * Memory file not found
 */
export class MemoryNotFoundError extends MemoryError {
  override readonly code = 'MEMORY_NOT_FOUND';
  override readonly retryable = false;

  constructor(path: string) {
    super(`Memory file not found: ${path}`, { path });
  }

  toUserMessage(): string {
    return `Memory file not found. A new one will be created at ${this.context.path}.`;
  }
}

/**
 * Memory file corrupted
 */
export class MemoryCorruptedError extends MemoryError {
  override readonly code = 'MEMORY_CORRUPTED';
  override readonly retryable = false;

  constructor(path: string, cause?: Error) {
    super(`Memory file is corrupted: ${path}`, {
      path,
      cause,
    });
  }

  toUserMessage(): string {
    return 'The memory file appears to be corrupted. Please restore from backup or create a new one.';
  }
}

/**
 * Memory read error
 */
export class MemoryReadError extends MemoryError {
  override readonly code = 'MEMORY_READ_ERROR';
  override readonly retryable = true;

  constructor(path: string, cause?: Error) {
    super(`Failed to read memory file: ${path}`, {
      path,
      retryable: true,
      cause,
    });
  }
}

/**
 * Memory write error
 */
export class MemoryWriteError extends MemoryError {
  override readonly code = 'MEMORY_WRITE_ERROR';
  override readonly retryable = true;

  constructor(path: string, cause?: Error) {
    super(`Failed to write to memory file: ${path}`, {
      path,
      retryable: true,
      cause,
    });
  }
}

/**
 * Memory search/recall error
 */
export class MemorySearchError extends MemoryError {
  override readonly code = 'MEMORY_SEARCH_ERROR';
  override readonly retryable = true;

  constructor(
    message: string,
    options: {
      query?: string;
      cause?: Error;
    } = {}
  ) {
    super(message, {
      retryable: true,
      context: { query: options.query },
      cause: options.cause,
    });
  }
}

/**
 * Memory capacity exceeded
 */
export class MemoryCapacityError extends MemoryError {
  override readonly code = 'MEMORY_CAPACITY_EXCEEDED';
  override readonly retryable = false;

  constructor(
    path: string,
    options: {
      currentSize: number;
      maxSize: number;
    }
  ) {
    super(`Memory file capacity exceeded: ${path}`, {
      path,
      context: options,
    });
  }

  toUserMessage(): string {
    return 'Memory storage is full. Please archive old data or upgrade your plan.';
  }
}

/**
 * Memory lock error (file is in use)
 */
export class MemoryLockError extends MemoryError {
  override readonly code = 'MEMORY_LOCKED';
  override readonly retryable = true;

  constructor(path: string) {
    super(`Memory file is locked by another process: ${path}`, {
      path,
      retryable: true,
    });
  }

  toUserMessage(): string {
    return 'Memory file is currently in use. Please try again.';
  }
}
