/**
 * Agent Types
 *
 * Types for AI agents and their configuration.
 */

/**
 * Agent configuration
 */
export interface AgentConfig {
  /** Unique agent name */
  name: string;

  /** Human-readable description */
  description?: string;

  /** Model to use (overrides default) */
  model?: string;

  /** System prompt */
  systemPrompt?: string;

  /** Available tools */
  tools?: string[];

  /** Memory configuration */
  memory?: AgentMemoryConfig;

  /** Temperature (0-1) */
  temperature?: number;

  /** Maximum tokens in response */
  maxTokens?: number;
}

/**
 * Agent memory configuration
 */
export interface AgentMemoryConfig {
  /** Enable memory for this agent */
  enabled?: boolean;

  /** Automatically include context from memory */
  autoContext?: boolean;

  /** Maximum context tokens from memory */
  maxContextTokens?: number;

  /** Include user preferences */
  includePreferences?: boolean;

  /** Include corrections */
  includeCorrections?: boolean;
}

/**
 * Tool definition
 */
export interface ToolDefinition {
  /** Tool name */
  name: string;

  /** Tool description (shown to LLM) */
  description: string;

  /** Parameter schema (JSON Schema) */
  parameters: ToolParameters;

  /** Tool handler function */
  handler: ToolHandler;
}

/**
 * Tool parameters (JSON Schema subset)
 */
export interface ToolParameters {
  type: 'object';
  properties: Record<string, ToolParameterProperty>;
  required?: string[];
}

/**
 * Single tool parameter property
 */
export interface ToolParameterProperty {
  type: 'string' | 'number' | 'boolean' | 'array' | 'object';
  description?: string;
  enum?: string[];
  items?: ToolParameterProperty;
  default?: unknown;
}

/**
 * Tool handler function
 */
export type ToolHandler = (
  args: Record<string, unknown>,
  context: ToolContext
) => Promise<unknown>;

/**
 * Context passed to tool handlers
 */
export interface ToolContext {
  /** Current conversation ID */
  conversationId: string;

  /** Current message ID */
  messageId: string;

  /** Agent name */
  agentName: string;

  /** Memory client (if available) */
  memory?: unknown;

  /** Abort signal for cancellation */
  abortSignal?: AbortSignal;
}

/**
 * Agent execution options
 */
export interface AgentExecuteOptions {
  /** Input message */
  input: string;

  /** Conversation ID */
  conversationId?: string;

  /** Override system prompt */
  systemPrompt?: string;

  /** Additional context */
  context?: string;

  /** Stream the response */
  stream?: boolean;

  /** Abort signal */
  abortSignal?: AbortSignal;
}

/**
 * Agent response (non-streaming)
 */
export interface AgentResponse {
  /** Response message ID */
  messageId: string;

  /** Response content */
  content: string;

  /** Tool calls made */
  toolCalls: AgentToolCall[];

  /** Sources used */
  sources: AgentSource[];

  /** Usage statistics */
  usage: {
    inputTokens: number;
    outputTokens: number;
    totalTokens: number;
  };

  /** Response latency in ms */
  latencyMs: number;
}

/**
 * Tool call in agent response
 */
export interface AgentToolCall {
  id: string;
  name: string;
  arguments: Record<string, unknown>;
  result: unknown;
}

/**
 * Source in agent response
 */
export interface AgentSource {
  id: string;
  content: string;
  relevance: number;
}

/**
 * Streaming chunk from agent
 */
export interface AgentStreamChunk {
  type: 'text' | 'tool_start' | 'tool_end' | 'done' | 'error';

  /** Text content (for type: 'text') */
  text?: string;

  /** Tool call info (for type: 'tool_start') */
  toolCall?: {
    id: string;
    name: string;
    arguments: Record<string, unknown>;
  };

  /** Tool result (for type: 'tool_end') */
  toolResult?: {
    id: string;
    result: unknown;
  };

  /** Error info (for type: 'error') */
  error?: {
    code: string;
    message: string;
  };

  /** Final response (for type: 'done') */
  response?: AgentResponse;
}

/**
 * Built-in agent templates
 */
export type AgentTemplate =
  | 'assistant'
  | 'coder'
  | 'researcher'
  | 'support'
  | 'custom';

/**
 * Get default config for agent template
 */
export function getTemplateConfig(template: AgentTemplate): Partial<AgentConfig> {
  const templates: Record<AgentTemplate, Partial<AgentConfig>> = {
    assistant: {
      description: 'General-purpose AI assistant',
      systemPrompt: 'You are a helpful AI assistant.',
      temperature: 0.7,
    },
    coder: {
      description: 'Coding assistant with memory',
      systemPrompt:
        'You are an expert programmer. Write clean, well-documented code.',
      temperature: 0.3,
      tools: ['memory_search', 'run_code'],
    },
    researcher: {
      description: 'Research assistant',
      systemPrompt:
        'You are a research assistant. Provide accurate, well-sourced information.',
      temperature: 0.5,
      tools: ['memory_search', 'web_search'],
    },
    support: {
      description: 'Customer support agent',
      systemPrompt:
        'You are a helpful customer support agent. Be friendly and resolve issues.',
      temperature: 0.5,
      tools: ['memory_search', 'create_ticket'],
    },
    custom: {},
  };

  return templates[template];
}
