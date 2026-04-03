/**
 * Canvas Engine
 *
 * Main engine that orchestrates LLM calls, memory, and agents.
 */

import type {
  EngineConfig,
  Message,
  Conversation,
  RecallOptions,
  RecallResult,
  AgentConfig,
  CaptureOptions,
  MemoryStats,
  MemoryContext,
  BuildContextOptions,
} from '../types/index.js';
import { mergeEngineConfig } from '../types/config.js';
import { createMessage } from '../types/message.js';
import { createLLMClient, type LLMClient } from '../providers/llm.js';
import { createMemoryClient, type MemoryClient } from '../memory/memory-client.js';
import {
  MissingApiKeyError,
  MissingMemoryPathError,
  InvalidAgentError,
  isCanvasError,
  wrapError,
} from '../errors/index.js';

/**
 * Engine state
 */
interface EngineState {
  conversations: Map<string, Conversation>;
  isInitialized: boolean;
}

/**
 * Send message options
 */
export interface SendOptions {
  /** Conversation ID (creates new if not provided) */
  conversationId?: string;

  /** Agent to use */
  agent?: string;

  /** Stream the response */
  stream?: boolean;

  /** Abort signal */
  abortSignal?: AbortSignal;

  /** Include memory context */
  includeContext?: boolean;
}

/**
 * Stream chunk from engine
 */
export interface EngineStreamChunk {
  type: 'text' | 'sources' | 'done' | 'error';
  text?: string;
  sources?: RecallResult[];
  message?: Message;
  error?: {
    code: string;
    message: string;
  };
}

/**
 * Canvas Engine
 *
 * @example
 * ```typescript
 * const engine = new CanvasEngine({
 *   llm: { provider: 'anthropic', apiKey: '...' },
 *   memory: './app.mv2',
 * });
 *
 * // Send message
 * const response = await engine.send('Hello!');
 *
 * // Stream response
 * for await (const chunk of engine.stream('Hello!')) {
 *   if (chunk.type === 'text') {
 *     process.stdout.write(chunk.text);
 *   }
 * }
 * ```
 */
export class CanvasEngine {
  readonly config: Required<EngineConfig>;
  private readonly llm: LLMClient;
  private readonly memory: MemoryClient;
  private readonly state: EngineState;
  private readonly agents: Map<string, AgentConfig>;

  constructor(config: EngineConfig) {
    this.validateConfig(config);
    this.config = mergeEngineConfig(config);

    // Initialize providers
    this.llm = createLLMClient(this.config.llm, this.config.logger);

    // Initialize memory (uses @memvid/sdk which handles its own embeddings)
    const memoryPath = typeof this.config.memory === 'string'
      ? this.config.memory
      : this.config.memory.path;
    this.memory = createMemoryClient(
      {
        path: memoryPath,
        memvidApiKey: this.config.memvidApiKey,
        autoCreate: true,
        autoSave: true,
      },
      this.config.logger
    );

    // Initialize state
    this.state = {
      conversations: new Map(),
      isInitialized: false,
    };

    // Initialize agents
    this.agents = new Map();
    for (const agent of this.config.agents) {
      this.agents.set(agent.name, agent);
    }

    this.log('info', 'Canvas Engine initialized', {
      llmProvider: this.config.llm.provider,
      embeddingProvider: this.config.embedding.provider,
      memory: this.config.memory,
      agents: Array.from(this.agents.keys()),
    });
  }

  /**
   * Validate configuration
   */
  private validateConfig(config: EngineConfig): void {
    if (!config.llm?.apiKey) {
      throw new MissingApiKeyError(config.llm?.provider ?? 'llm');
    }

    if (!config.memory) {
      throw new MissingMemoryPathError();
    }
  }

  /**
   * Send a message and get a response
   */
  async send(
    content: string,
    options: SendOptions = {}
  ): Promise<{ message: Message; sources: RecallResult[] }> {
    const {
      conversationId = this.generateId('conv'),
      agent = this.config.defaultAgent,
      includeContext = true,
      abortSignal,
    } = options;

    // Validate agent
    const agentConfig = this.getAgent(agent);

    // Get or create conversation
    const conversation = this.getOrCreateConversation(conversationId);

    // Create user message
    const userMessage = createMessage('user', content);
    conversation.messages.push(userMessage);

    // Build context from memory
    let sources: RecallResult[] = [];
    if (includeContext) {
      sources = await this.recall({ query: content, limit: 5 });
    }

    // Build messages for LLM
    const messages = this.buildMessages(conversation, agentConfig, sources);

    try {
      // Call LLM
      const response = await this.llm.chat({
        messages,
        model: agentConfig.model,
        temperature: agentConfig.temperature,
        maxTokens: agentConfig.maxTokens,
        abortSignal,
      });

      // Create assistant message
      const assistantMessage = createMessage('assistant', response.content, {
        metadata: {
          agent,
          model: agentConfig.model ?? this.config.llm.model,
          sources: sources.map((s) => ({
            id: s.id,
            content: s.content,
            relevance: s.score,
          })),
          tokens: {
            input: response.usage.inputTokens,
            output: response.usage.outputTokens,
          },
        },
      });

      conversation.messages.push(assistantMessage);
      conversation.updatedAt = new Date();

      return { message: assistantMessage, sources };
    } catch (error) {
      // Create error message
      const errorMessage = createMessage('assistant', '', {
        status: 'error',
        metadata: { agent },
      });
      conversation.messages.push(errorMessage);

      if (isCanvasError(error)) {
        throw error;
      }
      throw wrapError(error, 'Failed to send message');
    }
  }

  /**
   * Stream a message response
   */
  async *stream(
    content: string,
    options: SendOptions = {}
  ): AsyncGenerator<EngineStreamChunk, void, unknown> {
    const {
      conversationId = this.generateId('conv'),
      agent = this.config.defaultAgent,
      includeContext = true,
      abortSignal,
    } = options;

    // Validate agent
    const agentConfig = this.getAgent(agent);

    // Get or create conversation
    const conversation = this.getOrCreateConversation(conversationId);

    // Create user message
    const userMessage = createMessage('user', content);
    conversation.messages.push(userMessage);

    // Build context from memory
    let sources: RecallResult[] = [];
    if (includeContext) {
      sources = await this.recall({ query: content, limit: 5 });
      if (sources.length > 0) {
        yield { type: 'sources', sources };
      }
    }

    // Build messages for LLM
    const messages = this.buildMessages(conversation, agentConfig, sources);

    try {
      let fullContent = '';

      // Stream from LLM
      for await (const chunk of this.llm.stream({
        messages,
        model: agentConfig.model,
        temperature: agentConfig.temperature,
        maxTokens: agentConfig.maxTokens,
        abortSignal,
      })) {
        if (chunk.type === 'text' && chunk.text) {
          fullContent += chunk.text;
          yield { type: 'text', text: chunk.text };
        }
      }

      // Create assistant message
      const assistantMessage = createMessage('assistant', fullContent, {
        metadata: {
          agent,
          model: agentConfig.model ?? this.config.llm.model,
          sources: sources.map((s) => ({
            id: s.id,
            content: s.content,
            relevance: s.score,
          })),
        },
      });

      conversation.messages.push(assistantMessage);
      conversation.updatedAt = new Date();

      yield { type: 'done', message: assistantMessage };
    } catch (error) {
      const errorMessage = isCanvasError(error)
        ? error.toUserMessage()
        : 'An error occurred';
      const errorCode = isCanvasError(error) ? error.code : 'UNKNOWN_ERROR';

      yield {
        type: 'error',
        error: { code: errorCode, message: errorMessage },
      };
    }
  }

  /**
   * Search memory
   */
  async recall(options: RecallOptions): Promise<RecallResult[]> {
    console.log('[CanvasEngine] Recall called', { query: options.query, limit: options.limit });
    const results = await this.memory.recall(options);
    console.log('[CanvasEngine] Recall results', { count: results.length, results: results.slice(0, 2) });
    return results;
  }

  /**
   * Capture content to memory
   */
  async capture(options: CaptureOptions): Promise<void> {
    await this.memory.capture(options);
  }

  /**
   * Build context from memory for LLM
   */
  async buildContext(options: BuildContextOptions): Promise<MemoryContext> {
    return this.memory.buildContext(options);
  }

  /**
   * Get memory statistics
   */
  async getMemoryStats(): Promise<MemoryStats> {
    return this.memory.getStats();
  }

  /**
   * Save memory to file
   */
  async saveMemory(): Promise<void> {
    await this.memory.save();
  }

  /**
   * Sync memory with cloud
   */
  async syncMemory(): Promise<void> {
    await this.memory.sync();
  }

  /**
   * Get agent configuration
   */
  private getAgent(name: string): AgentConfig {
    const agent = this.agents.get(name);
    if (!agent) {
      throw new InvalidAgentError(name, Array.from(this.agents.keys()));
    }
    return agent;
  }

  /**
   * Get or create a conversation
   */
  private getOrCreateConversation(id: string): Conversation {
    let conversation = this.state.conversations.get(id);

    if (!conversation) {
      conversation = {
        id,
        messages: [],
        createdAt: new Date(),
        updatedAt: new Date(),
      };
      this.state.conversations.set(id, conversation);
    }

    return conversation;
  }

  /**
   * Build messages array for LLM
   */
  private buildMessages(
    conversation: Conversation,
    agent: AgentConfig,
    sources: RecallResult[]
  ) {
    const messages: Array<{ role: 'user' | 'assistant' | 'system'; content: string }> = [];

    // Add system prompt
    const systemPrompt = this.buildSystemPrompt(agent, sources);
    if (systemPrompt) {
      messages.push({ role: 'system', content: systemPrompt });
    }

    // Add conversation history (exclude the current message as it's already in)
    for (const msg of conversation.messages) {
      if (msg.role === 'user' || msg.role === 'assistant') {
        messages.push({ role: msg.role, content: msg.content });
      }
    }

    return messages;
  }

  /**
   * Build system prompt with context
   */
  private buildSystemPrompt(agent: AgentConfig, sources: RecallResult[]): string {
    let prompt = agent.systemPrompt ?? 'You are a helpful AI assistant.';

    // Add context from memory
    if (sources.length > 0) {
      prompt += '\n\n## Relevant Context from Memory\n\n';
      for (const source of sources) {
        prompt += `- ${source.content}\n`;
      }
    }

    return prompt;
  }

  /**
   * Generate unique ID
   */
  private generateId(prefix: string): string {
    return `${prefix}_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
  }

  /**
   * Log message
   */
  private log(
    level: 'debug' | 'info' | 'warn' | 'error',
    message: string,
    data?: Record<string, unknown>
  ): void {
    if (this.config.logger) {
      this.config.logger[level](`[CanvasEngine] ${message}`, data);
    }
  }

  /**
   * Get conversation by ID
   */
  getConversation(id: string): Conversation | undefined {
    return this.state.conversations.get(id);
  }

  /**
   * List all conversations
   */
  listConversations(): Conversation[] {
    return Array.from(this.state.conversations.values());
  }

  /**
   * Delete a conversation
   */
  deleteConversation(id: string): boolean {
    return this.state.conversations.delete(id);
  }

  /**
   * Clear all conversations
   */
  clearConversations(): void {
    this.state.conversations.clear();
  }
}

/**
 * Create a Canvas Engine instance
 */
export function createEngine(config: EngineConfig): CanvasEngine {
  return new CanvasEngine(config);
}
