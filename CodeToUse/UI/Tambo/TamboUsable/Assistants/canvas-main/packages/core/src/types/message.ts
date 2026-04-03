/**
 * Message Types
 *
 * Types for chat messages and conversations.
 */

/**
 * Message role
 */
export type MessageRole = 'user' | 'assistant' | 'system';

/**
 * Message status
 */
export type MessageStatus = 'pending' | 'streaming' | 'complete' | 'error';

/**
 * A single message in a conversation
 */
export interface Message {
  /** Unique message ID */
  id: string;

  /** Message role */
  role: MessageRole;

  /** Message content */
  content: string;

  /** Message status */
  status: MessageStatus;

  /** When the message was created */
  createdAt: Date;

  /** When the message was last updated */
  updatedAt: Date;

  /** Optional metadata */
  metadata?: MessageMetadata;
}

/**
 * Message metadata
 */
export interface MessageMetadata {
  /** Agent that generated this message */
  agent?: string;

  /** Model used to generate this message */
  model?: string;

  /** Sources used to generate this message */
  sources?: MessageSource[];

  /** Tools that were called */
  toolCalls?: ToolCall[];

  /** Token counts */
  tokens?: {
    input: number;
    output: number;
  };

  /** Latency in milliseconds */
  latencyMs?: number;

  /** User feedback */
  feedback?: {
    rating?: 'positive' | 'negative';
    comment?: string;
  };
}

/**
 * Source citation for a message
 */
export interface MessageSource {
  /** Source ID (frame ID from memory) */
  id: string;

  /** Source content snippet */
  content: string;

  /** Relevance score (0-1) */
  relevance: number;

  /** Original URI if available */
  uri?: string;
}

/**
 * Tool call made during message generation
 */
export interface ToolCall {
  /** Tool call ID */
  id: string;

  /** Tool name */
  name: string;

  /** Tool arguments */
  arguments: Record<string, unknown>;

  /** Tool result */
  result?: unknown;

  /** Tool call status */
  status: 'pending' | 'running' | 'complete' | 'error';

  /** Error if failed */
  error?: string;
}

/**
 * A conversation (list of messages)
 */
export interface Conversation {
  /** Conversation ID */
  id: string;

  /** Conversation title (usually derived from first message) */
  title?: string;

  /** Messages in the conversation */
  messages: Message[];

  /** When the conversation was created */
  createdAt: Date;

  /** When the conversation was last updated */
  updatedAt: Date;

  /** Conversation metadata */
  metadata?: ConversationMetadata;
}

/**
 * Conversation metadata
 */
export interface ConversationMetadata {
  /** Agent used for this conversation */
  agent?: string;

  /** Tags */
  tags?: string[];

  /** Whether conversation is archived */
  archived?: boolean;

  /** Whether conversation is pinned */
  pinned?: boolean;
}

/**
 * Input for sending a message
 */
export interface SendMessageInput {
  /** Message content */
  content: string;

  /** Conversation ID (creates new if not provided) */
  conversationId?: string;

  /** Agent to use */
  agent?: string;

  /** Attached files */
  attachments?: Attachment[];
}

/**
 * File attachment
 */
export interface Attachment {
  /** File name */
  name: string;

  /** MIME type */
  type: string;

  /** File size in bytes */
  size: number;

  /** File content (base64 or URL) */
  content: string;
}

/**
 * Create a new message
 */
export function createMessage(
  role: MessageRole,
  content: string,
  options: {
    id?: string;
    status?: MessageStatus;
    metadata?: MessageMetadata;
  } = {}
): Message {
  const now = new Date();

  return {
    id: options.id ?? generateId(),
    role,
    content,
    status: options.status ?? 'complete',
    createdAt: now,
    updatedAt: now,
    metadata: options.metadata,
  };
}

/**
 * Generate a unique ID
 */
function generateId(): string {
  return `msg_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
}
