import { CanvasError, type ErrorCategory } from './base.js';

/**
 * Validation errors - thrown when input validation fails
 */
export class ValidationError extends CanvasError {
  readonly code: string = 'VALIDATION_ERROR';
  readonly category: ErrorCategory = 'validation';
  readonly retryable = false;

  /** Field that failed validation */
  readonly field?: string;

  constructor(
    message: string,
    options: {
      field?: string;
      value?: unknown;
      context?: Record<string, unknown>;
    } = {}
  ) {
    super(message, {
      context: {
        field: options.field,
        value: options.value,
        ...options.context,
      },
    });
    this.field = options.field;
  }
}

/**
 * Required field missing
 */
export class RequiredFieldError extends ValidationError {
  override readonly code = 'REQUIRED_FIELD';

  constructor(field: string) {
    super(`${field} is required`, { field });
  }
}

/**
 * Invalid message content
 */
export class InvalidMessageError extends ValidationError {
  override readonly code = 'INVALID_MESSAGE';

  constructor(reason: string) {
    super(`Invalid message: ${reason}`);
  }
}

/**
 * Message too long
 */
export class MessageTooLongError extends ValidationError {
  override readonly code = 'MESSAGE_TOO_LONG';

  constructor(length: number, maxLength: number) {
    super(`Message exceeds maximum length of ${maxLength} characters`, {
      context: { length, maxLength },
    });
  }

  toUserMessage(): string {
    const max = this.context.maxLength as number;
    return `Your message is too long. Maximum ${max.toLocaleString()} characters allowed.`;
  }
}

/**
 * Invalid agent name
 */
export class InvalidAgentError extends ValidationError {
  override readonly code = 'INVALID_AGENT';

  constructor(agentName: string, availableAgents: string[]) {
    super(`Agent "${agentName}" not found`, {
      context: { agentName, availableAgents },
    });
  }

  toUserMessage(): string {
    const available = (this.context.availableAgents as string[]).join(', ');
    return `Agent "${this.context.agentName}" not found. Available: ${available}`;
  }
}

/**
 * Schema validation error (from Zod)
 */
export class SchemaValidationError extends ValidationError {
  override readonly code = 'SCHEMA_VALIDATION_ERROR';

  /** Individual validation issues */
  readonly issues: ValidationIssue[];

  constructor(issues: ValidationIssue[]) {
    const firstIssue = issues[0];
    const message =
      issues.length === 1 && firstIssue
        ? firstIssue.message
        : `${issues.length} validation errors`;

    super(message, {
      context: { issues },
    });

    this.issues = issues;
  }

  toUserMessage(): string {
    const firstIssue = this.issues[0];
    if (this.issues.length === 1 && firstIssue) {
      return firstIssue.message;
    }
    return this.issues.map((i) => `• ${i.message}`).join('\n');
  }
}

/**
 * Single validation issue
 */
export interface ValidationIssue {
  path: string[];
  message: string;
  code: string;
}

/**
 * Create validation issues from Zod error
 */
export function fromZodError(zodError: {
  issues: Array<{
    path: (string | number)[];
    message: string;
    code: string;
  }>;
}): SchemaValidationError {
  const issues: ValidationIssue[] = zodError.issues.map((issue) => ({
    path: issue.path.map(String),
    message: issue.message,
    code: issue.code,
  }));

  return new SchemaValidationError(issues);
}
