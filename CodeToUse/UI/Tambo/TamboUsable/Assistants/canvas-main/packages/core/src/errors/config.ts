import { CanvasError, type ErrorCategory } from './base.js';

/**
 * Configuration errors - thrown when Canvas is misconfigured
 */
export class ConfigError extends CanvasError {
  readonly code: string = 'CONFIG_ERROR';
  readonly category: ErrorCategory = 'configuration';
  readonly retryable = false;

  constructor(
    message: string,
    options: {
      context?: Record<string, unknown>;
      cause?: Error;
    } = {}
  ) {
    super(message, options);
  }

  toUserMessage(): string {
    return `Configuration error: ${this.message}`;
  }
}

/**
 * Missing API key error
 */
export class MissingApiKeyError extends ConfigError {
  override readonly code = 'MISSING_API_KEY';

  constructor(provider: string) {
    super(`API key is required for ${provider} provider`, {
      context: { provider },
    });
  }

  toUserMessage(): string {
    const provider = this.context.provider as string;
    return `Please provide an API key for ${provider}. Check your configuration.`;
  }
}

/**
 * Invalid provider error
 */
export class InvalidProviderError extends ConfigError {
  override readonly code = 'INVALID_PROVIDER';

  constructor(provider: string, validProviders: string[]) {
    super(`Invalid provider: ${provider}`, {
      context: { provider, validProviders },
    });
  }

  toUserMessage(): string {
    const valid = (this.context.validProviders as string[]).join(', ');
    return `Invalid provider "${this.context.provider}". Valid options: ${valid}`;
  }
}

/**
 * Missing memory path error
 */
export class MissingMemoryPathError extends ConfigError {
  override readonly code = 'MISSING_MEMORY_PATH';

  constructor() {
    super('Memory path is required');
  }

  toUserMessage(): string {
    return 'Please provide a memory path (e.g., "./app.mv2")';
  }
}

/**
 * Invalid configuration value error
 */
export class InvalidConfigValueError extends ConfigError {
  override readonly code = 'INVALID_CONFIG_VALUE';

  constructor(field: string, value: unknown, expectedType: string) {
    super(`Invalid value for ${field}: expected ${expectedType}`, {
      context: { field, value, expectedType },
    });
  }
}
