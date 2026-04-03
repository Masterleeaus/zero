/**
 * useChat Hook
 *
 * Hook for managing chat state and sending messages.
 */

import { useCallback, useMemo } from 'react';
import { useStore as useZustand } from 'zustand';
import { useEngine, useStore } from '../context/index.js';
import {
  selectMessages,
  selectIsLoading,
  selectIsStreaming,
  selectError,
  selectSources,
  selectInputValue,
} from '../context/canvas-store.js';
import type { Message, RecallResult } from '@memvid/canvas-core/types-only';
import { createMessage, isCanvasError, getUserMessage } from '@memvid/canvas-core';

/**
 * useChat options
 */
export interface UseChatOptions {
  /** Agent to use */
  agent?: string;

  /** Conversation ID */
  conversationId?: string;

  /** Include memory context */
  includeContext?: boolean;

  /** Callback when message is sent */
  onSend?: (message: Message) => void;

  /** Callback when response is received */
  onResponse?: (message: Message) => void;

  /** Callback on error */
  onError?: (error: Error) => void;

  /** Callback when streaming starts */
  onStreamStart?: () => void;

  /** Callback when streaming ends */
  onStreamEnd?: () => void;
}

/**
 * useChat return type
 */
export interface UseChatReturn {
  /** Messages in the conversation */
  messages: Message[];

  /** Whether a response is loading */
  isLoading: boolean;

  /** Whether currently streaming */
  isStreaming: boolean;

  /** Current error message */
  error: string | null;

  /** Sources from last response */
  sources: RecallResult[];

  /** Current input value */
  input: string;

  /** Set input value */
  setInput: (value: string) => void;

  /** Send a message */
  send: (content?: string) => Promise<void>;

  /** Send a message with streaming */
  sendStream: (content?: string) => Promise<void>;

  /** Stop current stream */
  stop: () => void;

  /** Clear chat */
  clear: () => void;

  /** Reload last message */
  reload: () => Promise<void>;
}

/**
 * Hook for chat functionality
 *
 * @example
 * ```tsx
 * function ChatComponent() {
 *   const { messages, input, setInput, send, isLoading } = useChat();
 *
 *   return (
 *     <div>
 *       {messages.map(msg => (
 *         <div key={msg.id}>{msg.content}</div>
 *       ))}
 *       <input value={input} onChange={e => setInput(e.target.value)} />
 *       <button onClick={() => send()} disabled={isLoading}>
 *         Send
 *       </button>
 *     </div>
 *   );
 * }
 * ```
 */
export function useChat(options: UseChatOptions = {}): UseChatReturn {
  const {
    agent,
    conversationId,
    includeContext = true,
    onSend,
    onResponse,
    onError,
    onStreamStart,
    onStreamEnd,
  } = options;

  const engine = useEngine();
  const store = useStore();

  // Subscribe to store state
  const messages = useZustand(store, selectMessages);
  const isLoading = useZustand(store, selectIsLoading);
  const isStreaming = useZustand(store, selectIsStreaming);
  const error = useZustand(store, selectError);
  const sources = useZustand(store, selectSources);
  const input = useZustand(store, selectInputValue);

  // Abort controller for stopping streams
  const abortControllerRef = useMemo(() => ({ current: null as AbortController | null }), []);

  /**
   * Set input value
   */
  const setInput = useCallback(
    (value: string) => {
      store.getState().setInputValue(value);
    },
    [store]
  );

  /**
   * Send a message (non-streaming)
   */
  const send = useCallback(
    async (content?: string) => {
      const messageContent = content ?? input;
      if (!messageContent.trim()) return;

      const state = store.getState();

      // Create user message
      const userMessage = createMessage('user', messageContent);
      state.addMessage(userMessage);
      state.setInputValue('');
      state.setLoading(true);
      state.setError(null);

      onSend?.(userMessage);

      try {
        const { message, sources: resultSources } = await engine.send(messageContent, {
          agent,
          conversationId,
          includeContext,
        });

        state.addMessage(message);
        state.setSources(resultSources);
        state.setLoading(false);

        onResponse?.(message);
      } catch (err) {
        const errorMessage = isCanvasError(err)
          ? getUserMessage(err)
          : 'An error occurred';

        state.setError(errorMessage);
        state.setLoading(false);

        if (err instanceof Error) {
          onError?.(err);
        }
      }
    },
    [engine, store, input, agent, conversationId, includeContext, onSend, onResponse, onError]
  );

  /**
   * Send a message with streaming
   */
  const sendStream = useCallback(
    async (content?: string) => {
      const messageContent = content ?? input;
      if (!messageContent.trim()) return;

      const state = store.getState();

      // Create abort controller
      abortControllerRef.current = new AbortController();

      // Create user message
      const userMessage = createMessage('user', messageContent);
      state.addMessage(userMessage);
      state.setInputValue('');
      state.setLoading(true);
      state.setStreaming(true);
      state.setError(null);

      onSend?.(userMessage);
      onStreamStart?.();

      // Create placeholder assistant message
      const assistantMessage = createMessage('assistant', '', {
        status: 'streaming',
      });
      state.addMessage(assistantMessage);

      let fullContent = '';

      try {
        for await (const chunk of engine.stream(messageContent, {
          agent,
          conversationId,
          includeContext,
          abortSignal: abortControllerRef.current.signal,
        })) {
          if (chunk.type === 'text' && chunk.text) {
            fullContent += chunk.text;
            state.updateMessage(assistantMessage.id, {
              content: fullContent,
            });
          } else if (chunk.type === 'sources' && chunk.sources) {
            state.setSources(chunk.sources);
          } else if (chunk.type === 'done' && chunk.message) {
            state.updateMessage(assistantMessage.id, {
              content: chunk.message.content,
              status: 'complete',
              metadata: chunk.message.metadata,
            });
            onResponse?.(chunk.message);
          } else if (chunk.type === 'error' && chunk.error) {
            state.setError(chunk.error.message);
            state.updateMessage(assistantMessage.id, {
              status: 'error',
            });
          }
        }
      } catch (err) {
        // Handle abort
        if (err instanceof Error && err.name === 'AbortError') {
          state.updateMessage(assistantMessage.id, {
            content: fullContent,
            status: 'complete',
          });
        } else {
          const errorMessage = isCanvasError(err)
            ? getUserMessage(err)
            : 'An error occurred';

          state.setError(errorMessage);
          state.updateMessage(assistantMessage.id, {
            status: 'error',
          });

          if (err instanceof Error) {
            onError?.(err);
          }
        }
      } finally {
        state.setLoading(false);
        state.setStreaming(false);
        abortControllerRef.current = null;
        onStreamEnd?.();
      }
    },
    [
      engine,
      store,
      input,
      agent,
      conversationId,
      includeContext,
      abortControllerRef,
      onSend,
      onResponse,
      onError,
      onStreamStart,
      onStreamEnd,
    ]
  );

  /**
   * Stop current stream
   */
  const stop = useCallback(() => {
    if (abortControllerRef.current) {
      abortControllerRef.current.abort();
    }
  }, [abortControllerRef]);

  /**
   * Clear chat
   */
  const clear = useCallback(() => {
    store.getState().clearChat();
  }, [store]);

  /**
   * Reload last message
   */
  const reload = useCallback(async () => {
    const currentMessages = store.getState().chat.messages;
    if (currentMessages.length < 2) return;

    // Find last user message
    const lastUserMessage = [...currentMessages]
      .reverse()
      .find((m) => m.role === 'user');

    if (!lastUserMessage) return;

    // Remove last assistant message
    const newMessages = currentMessages.slice(0, -1);
    store.getState().setMessages(newMessages);

    // Resend
    await sendStream(lastUserMessage.content);
  }, [store, sendStream]);

  return {
    messages,
    isLoading,
    isStreaming,
    error,
    sources,
    input,
    setInput,
    send,
    sendStream,
    stop,
    clear,
    reload,
  };
}
