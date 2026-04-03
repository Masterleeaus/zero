/**
 * Canvas Usage Examples
 *
 * This file demonstrates various ways to use the Canvas UI Kit.
 */

import React from 'react';

// =============================================================================
// REACT COMPONENTS
// =============================================================================

/**
 * Example 1: Basic Chat
 *
 * The simplest way to add an AI chat to your app.
 */
import { Canvas, Chat } from '@memvid/canvas-react';
import '@memvid/canvas-react/styles';

export function BasicChat() {
  return (
    <Canvas.Provider
      llm={{
        provider: 'anthropic',
        apiKey: process.env.NEXT_PUBLIC_ANTHROPIC_API_KEY!,
      }}
      memory="./app.mv2"
    >
      <Chat />
    </Canvas.Provider>
  );
}

/**
 * Example 2: Customized Chat
 *
 * Chat with custom styling and callbacks.
 */
export function CustomizedChat() {
  return (
    <Canvas.Provider
      llm={{
        provider: 'openai',
        apiKey: process.env.NEXT_PUBLIC_OPENAI_API_KEY!,
        model: 'gpt-4o',
      }}
      memory="./app.mv2"
      theme="dark"
    >
      <Chat
        header={
          <div className="flex items-center gap-2">
            <span className="text-xl">🤖</span>
            <h1 className="font-semibold">AI Assistant</h1>
          </div>
        }
        placeholder="Ask me anything..."
        streaming={true}
        onSend={(msg) => console.log('User sent:', msg.content)}
        onResponse={(msg) => console.log('AI responded:', msg.content)}
        onError={(err) => console.error('Error:', err)}
      />
    </Canvas.Provider>
  );
}

/**
 * Example 3: Using Hooks Directly
 *
 * Build your own UI with Canvas hooks.
 */
import { useChat, useMemory, useAgent } from '@memvid/canvas-react/hooks';
import { CanvasProvider } from '@memvid/canvas-react';

function CustomChatInterface() {
  const {
    messages,
    input,
    setInput,
    sendStream,
    isStreaming,
    stop,
    error,
  } = useChat();

  const { search, results } = useMemory();
  const { currentAgent, agents, switchAgent } = useAgent();

  return (
    <div className="flex flex-col h-full">
      {/* Agent Selector */}
      <select
        value={currentAgent}
        onChange={(e) => switchAgent(e.target.value)}
        className="mb-4"
      >
        {agents.map((agent) => (
          <option key={agent.name} value={agent.name}>
            {agent.name}
          </option>
        ))}
      </select>

      {/* Messages */}
      <div className="flex-1 overflow-y-auto">
        {messages.map((msg) => (
          <div
            key={msg.id}
            className={`p-4 ${msg.role === 'user' ? 'bg-blue-50' : 'bg-gray-50'}`}
          >
            <strong>{msg.role}:</strong> {msg.content}
          </div>
        ))}
      </div>

      {/* Error */}
      {error && <div className="text-red-500 p-2">{error}</div>}

      {/* Input */}
      <form
        onSubmit={(e) => {
          e.preventDefault();
          sendStream();
        }}
        className="flex gap-2 p-4"
      >
        <input
          value={input}
          onChange={(e) => setInput(e.target.value)}
          placeholder="Type a message..."
          className="flex-1 border p-2 rounded"
        />
        {isStreaming ? (
          <button type="button" onClick={stop} className="px-4 py-2 bg-red-500 text-white rounded">
            Stop
          </button>
        ) : (
          <button type="submit" className="px-4 py-2 bg-blue-500 text-white rounded">
            Send
          </button>
        )}
      </form>
    </div>
  );
}

export function HooksExample() {
  return (
    <CanvasProvider
      llm={{ provider: 'anthropic', apiKey: process.env.NEXT_PUBLIC_ANTHROPIC_API_KEY! }}
      memory="./app.mv2"
    >
      <CustomChatInterface />
    </CanvasProvider>
  );
}

/**
 * Example 4: Multiple Agents
 *
 * Configure different AI personas.
 */
export function MultiAgentChat() {
  return (
    <Canvas.Provider
      llm={{
        provider: 'anthropic',
        apiKey: process.env.NEXT_PUBLIC_ANTHROPIC_API_KEY!,
      }}
      memory="./app.mv2"
      agents={[
        {
          name: 'assistant',
          systemPrompt: 'You are a helpful AI assistant.',
        },
        {
          name: 'coder',
          systemPrompt: 'You are an expert programmer. Always provide code examples.',
          model: 'claude-sonnet-4-20250514',
        },
        {
          name: 'analyst',
          systemPrompt: 'You are a data analyst. Be precise and use numbers.',
          temperature: 0.3,
        },
      ]}
      defaultAgent="assistant"
    >
      <Chat agent="coder" />
    </Canvas.Provider>
  );
}

/**
 * Example 5: Custom Message Rendering
 *
 * Use a markdown renderer for AI responses.
 */
import ReactMarkdown from 'react-markdown';
import { Prism as SyntaxHighlighter } from 'react-syntax-highlighter';

export function MarkdownChat() {
  return (
    <Canvas.Provider
      llm={{ provider: 'anthropic', apiKey: process.env.NEXT_PUBLIC_ANTHROPIC_API_KEY! }}
      memory="./app.mv2"
    >
      <Chat
        renderContent={(content, message) => {
          if (message.role === 'user') {
            return content;
          }

          return (
            <ReactMarkdown
              components={{
                code({ className, children }) {
                  const match = /language-(\w+)/.exec(className || '');
                  return match ? (
                    <SyntaxHighlighter language={match[1]}>
                      {String(children)}
                    </SyntaxHighlighter>
                  ) : (
                    <code className={className}>{children}</code>
                  );
                },
              }}
            >
              {content}
            </ReactMarkdown>
          );
        }}
      />
    </Canvas.Provider>
  );
}

// =============================================================================
// SERVER-SIDE (Next.js API Routes)
// =============================================================================

/**
 * Example 6: Next.js API Route
 *
 * Create API endpoints for your chat.
 */

// app/api/chat/route.ts
/*
import { createChatHandler } from '@memvid/canvas-server/next';

const handler = createChatHandler({
  config: {
    llm: {
      provider: 'anthropic',
      apiKey: process.env.ANTHROPIC_API_KEY!,
    },
    memory: './data/memory.mv2',
  },
  cors: true,
});

export const POST = handler;
export const OPTIONS = handler;
*/

// =============================================================================
// PURE NODE.JS (without React)
// =============================================================================

/**
 * Example 7: Node.js Script
 *
 * Use Canvas in a Node.js script or CLI.
 */

// scripts/chat.ts
/*
import { createEngine } from '@memvid/canvas-core';

async function main() {
  const engine = createEngine({
    llm: {
      provider: 'anthropic',
      apiKey: process.env.ANTHROPIC_API_KEY!,
    },
    memory: './app.mv2',
  });

  // Non-streaming
  const { message, sources } = await engine.send('What is TypeScript?');
  console.log(message.content);
  console.log('Sources:', sources.length);

  // Streaming
  console.log('\n--- Streaming ---\n');
  for await (const chunk of engine.stream('Tell me a joke')) {
    if (chunk.type === 'text') {
      process.stdout.write(chunk.text);
    }
  }

  // Search memory
  const results = await engine.recall({
    query: 'TypeScript',
    limit: 5,
  });
  console.log('\n\nMemory search results:', results.length);

  // Capture to memory
  await engine.capture({
    type: 'document',
    content: 'TypeScript is a typed superset of JavaScript.',
    metadata: { source: 'example' },
  });

  // Save and sync
  await engine.saveMemory();
}

main().catch(console.error);
*/

// =============================================================================
// EMBEDDING-ONLY USAGE
// =============================================================================

/**
 * Example 8: Just Embeddings
 *
 * Use Canvas just for embeddings without chat.
 */

// scripts/embed.ts
/*
import { createEmbeddingClient } from '@memvid/canvas-core';

async function main() {
  const embedding = createEmbeddingClient({
    provider: 'openai',
    apiKey: process.env.OPENAI_API_KEY!,
    model: 'text-embedding-3-small',
  });

  // Embed a single text
  const vector = await embedding.embedOne('Hello, world!');
  console.log('Vector dimensions:', vector.length);

  // Embed multiple texts
  const { embeddings } = await embedding.embedMany([
    'First sentence',
    'Second sentence',
    'Third sentence',
  ]);
  console.log('Embedded', embeddings.length, 'texts');
}

main().catch(console.error);
*/
