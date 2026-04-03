/**
 * Agent Executor
 *
 * Executes agent logic with tool calling capabilities.
 */

import type {
  AgentConfig,
  AgentExecuteOptions,
  AgentResponse,
  AgentStreamChunk,
  AgentToolCall,
  AgentSource,
  ToolDefinition,
  ToolHandler,
  ToolContext,
} from '../types/index.js';
import type { LLMClient } from '../providers/llm.js';
import type { MemoryClient } from '../memory/memory-client.js';
import type { Logger } from '../types/config.js';
import { generateId } from '../utils/id.js';

/**
 * Registered tool
 */
interface RegisteredTool {
  definition: ToolDefinition;
  handler: ToolHandler;
}

/**
 * Agent Executor
 *
 * Handles the execution loop for an agent, including tool calls.
 */
export class AgentExecutor {
  private readonly config: AgentConfig;
  private readonly llm: LLMClient;
  private readonly memory: MemoryClient;
  private readonly registeredTools: Map<string, RegisteredTool>;
  private readonly logger?: Logger;

  constructor(
    config: AgentConfig,
    llm: LLMClient,
    memory: MemoryClient,
    logger?: Logger
  ) {
    this.config = config;
    this.llm = llm;
    this.memory = memory;
    this.logger = logger;
    this.registeredTools = new Map();
  }

  /**
   * Register a tool for use by the agent
   */
  registerTool(definition: ToolDefinition): void {
    this.registeredTools.set(definition.name, {
      definition,
      handler: definition.handler,
    });
  }

  /**
   * Execute the agent
   */
  async execute(options: AgentExecuteOptions): Promise<AgentResponse> {
    const startTime = Date.now();
    const { input, conversationId = generateId('conv'), context } = options;

    this.log('debug', 'Executing agent', {
      agent: this.config.name,
      input: input.slice(0, 100),
    });

    // Get context from memory if enabled
    const sources: AgentSource[] = [];
    if (this.config.memory?.autoContext !== false) {
      try {
        const recalled = await this.memory.recall({
          query: input,
          limit: 5,
          minScore: 0.5,
        });
        for (const r of recalled) {
          sources.push({
            id: r.id,
            content: r.content,
            relevance: r.score,
          });
        }
      } catch {
        // Memory recall failed, continue without context
      }
    }

    // Build system prompt with context
    const systemPrompt = this.buildSystemPrompt(context, sources);

    // Conversation messages
    const messages: Array<{ role: 'user' | 'assistant' | 'system'; content: string }> = [
      { role: 'system', content: systemPrompt },
      { role: 'user', content: input },
    ];

    const toolCalls: AgentToolCall[] = [];
    const maxIterations = 10;
    let iterations = 0;
    let totalInputTokens = 0;
    let totalOutputTokens = 0;

    while (iterations < maxIterations) {
      iterations++;

      // Call LLM
      const response = await this.llm.chat({
        messages,
        model: this.config.model,
        temperature: this.config.temperature,
        maxTokens: this.config.maxTokens,
      });

      totalInputTokens += response.usage.inputTokens;
      totalOutputTokens += response.usage.outputTokens;

      // Check for tool calls in response
      const parsedToolCall = this.parseToolCall(response.content);

      if (!parsedToolCall) {
        // No tool call, return final response
        return {
          messageId: generateId('msg'),
          content: response.content,
          toolCalls,
          sources,
          usage: {
            inputTokens: totalInputTokens,
            outputTokens: totalOutputTokens,
            totalTokens: totalInputTokens + totalOutputTokens,
          },
          latencyMs: Date.now() - startTime,
        };
      }

      // Execute tool
      const tool = this.registeredTools.get(parsedToolCall.name);
      if (!tool) {
        messages.push({ role: 'assistant', content: response.content });
        messages.push({
          role: 'user',
          content: `Error: Unknown tool "${parsedToolCall.name}". Available tools: ${Array.from(this.registeredTools.keys()).join(', ')}`,
        });
        continue;
      }

      // Execute tool handler
      const toolContext: ToolContext = {
        conversationId,
        messageId: generateId('msg'),
        agentName: this.config.name,
        memory: this.memory,
        abortSignal: options.abortSignal,
      };

      const toolCallId = generateId('tool');

      try {
        const output = await tool.handler(
          parsedToolCall.input as Record<string, unknown>,
          toolContext
        );

        toolCalls.push({
          id: toolCallId,
          name: parsedToolCall.name,
          arguments: parsedToolCall.input as Record<string, unknown>,
          result: output,
        });

        messages.push({ role: 'assistant', content: response.content });
        messages.push({
          role: 'user',
          content: `Tool "${parsedToolCall.name}" returned: ${JSON.stringify(output)}`,
        });
      } catch (error) {
        toolCalls.push({
          id: toolCallId,
          name: parsedToolCall.name,
          arguments: parsedToolCall.input as Record<string, unknown>,
          result: { error: error instanceof Error ? error.message : 'Unknown error' },
        });

        messages.push({ role: 'assistant', content: response.content });
        messages.push({
          role: 'user',
          content: `Tool "${parsedToolCall.name}" failed: ${error instanceof Error ? error.message : 'Unknown error'}`,
        });
      }
    }

    // Max iterations reached
    return {
      messageId: generateId('msg'),
      content: 'Max iterations reached without completing the task.',
      toolCalls,
      sources,
      usage: {
        inputTokens: totalInputTokens,
        outputTokens: totalOutputTokens,
        totalTokens: totalInputTokens + totalOutputTokens,
      },
      latencyMs: Date.now() - startTime,
    };
  }

  /**
   * Stream agent execution
   */
  async *stream(options: AgentExecuteOptions): AsyncGenerator<AgentStreamChunk> {
    const startTime = Date.now();
    const { input, context } = options;

    // Get context from memory
    const sources: AgentSource[] = [];
    if (this.config.memory?.autoContext !== false) {
      try {
        const recalled = await this.memory.recall({
          query: input,
          limit: 5,
          minScore: 0.5,
        });
        for (const r of recalled) {
          sources.push({
            id: r.id,
            content: r.content,
            relevance: r.score,
          });
        }
      } catch {
        // Continue without context
      }
    }

    // Build system prompt
    const systemPrompt = this.buildSystemPrompt(context, sources);

    // Stream LLM response
    let fullContent = '';
    let totalInputTokens = 0;
    let totalOutputTokens = 0;

    for await (const chunk of this.llm.stream({
      messages: [
        { role: 'system', content: systemPrompt },
        { role: 'user', content: input },
      ],
      model: this.config.model,
      temperature: this.config.temperature,
      maxTokens: this.config.maxTokens,
      abortSignal: options.abortSignal,
    })) {
      if (chunk.type === 'text' && chunk.text) {
        fullContent += chunk.text;
        yield { type: 'text', text: chunk.text };
      }
    }

    // Yield done with final response
    yield {
      type: 'done',
      response: {
        messageId: generateId('msg'),
        content: fullContent,
        toolCalls: [],
        sources,
        usage: {
          inputTokens: totalInputTokens,
          outputTokens: totalOutputTokens,
          totalTokens: totalInputTokens + totalOutputTokens,
        },
        latencyMs: Date.now() - startTime,
      },
    };
  }

  /**
   * Build system prompt with memory context
   */
  private buildSystemPrompt(
    additionalContext: string | undefined,
    sources: AgentSource[]
  ): string {
    let prompt = this.config.systemPrompt ?? 'You are a helpful AI assistant.';

    // Add available tools
    if (this.registeredTools.size > 0) {
      prompt += '\n\n## Available Tools\n\n';
      for (const [name, { definition }] of this.registeredTools) {
        prompt += `### ${name}\n`;
        prompt += `${definition.description}\n`;
        if (definition.parameters) {
          prompt += `Parameters: ${JSON.stringify(definition.parameters, null, 2)}\n`;
        }
        prompt += '\n';
      }
      prompt += 'To use a tool, respond with: TOOL: <tool_name> INPUT: <json_input>\n';
    }

    // Add memory context
    if (sources.length > 0) {
      prompt += '\n\n## Relevant Context from Memory\n\n';
      for (const source of sources) {
        prompt += `- ${source.content}\n`;
      }
    }

    // Add additional context
    if (additionalContext) {
      prompt += `\n\n## Additional Context\n\n${additionalContext}`;
    }

    return prompt;
  }

  /**
   * Parse tool call from LLM response
   */
  private parseToolCall(content: string): { name: string; input: unknown } | null {
    // Simple pattern matching for tool calls
    // Format: TOOL: <name> INPUT: <json>
    const match = content.match(/TOOL:\s*(\w+)\s*INPUT:\s*(\{[\s\S]*\})/);
    if (!match || !match[1] || !match[2]) return null;

    try {
      return {
        name: match[1],
        input: JSON.parse(match[2]),
      };
    } catch {
      return null;
    }
  }

  /**
   * Log message
   */
  private log(
    level: 'debug' | 'info' | 'warn' | 'error',
    message: string,
    data?: Record<string, unknown>
  ): void {
    if (this.logger) {
      this.logger[level](`[AgentExecutor:${this.config.name}] ${message}`, data);
    }
  }
}

/**
 * Create an agent executor
 */
export function createAgentExecutor(
  config: AgentConfig,
  llm: LLMClient,
  memory: MemoryClient,
  logger?: Logger
): AgentExecutor {
  return new AgentExecutor(config, llm, memory, logger);
}
