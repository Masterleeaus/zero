/**
 * Chat Component
 *
 * Complete chat interface component.
 */

import { type ReactNode } from 'react';
import clsx from 'clsx';
import { useChat, type UseChatOptions } from '../hooks/use-chat.js';
import { ChatMessages, type ChatMessagesProps } from './chat-messages.js';
import { ChatInput, type ChatInputProps } from './chat-input.js';

/**
 * Chat props
 */
export interface ChatProps extends UseChatOptions {
  /** Custom class name */
  className?: string;

  /** Header content */
  header?: ReactNode;

  /** Footer content (below input) */
  footer?: ReactNode;

  /** Empty state content */
  emptyState?: ReactNode;

  /** Custom avatar for user */
  userAvatar?: ReactNode;

  /** Custom avatar for assistant */
  assistantAvatar?: ReactNode;

  /** Custom message renderer */
  renderContent?: ChatMessagesProps['renderContent'];

  /** Custom message component */
  renderMessage?: ChatMessagesProps['renderMessage'];

  /** Input placeholder */
  placeholder?: string;

  /** Submit button content */
  submitButton?: ChatInputProps['submitButton'];

  /** Stop button content */
  stopButton?: ChatInputProps['stopButton'];

  /** Whether to use streaming */
  streaming?: boolean;
}

/**
 * Chat component
 *
 * Complete chat interface with messages and input.
 *
 * @example
 * ```tsx
 * <Canvas.Provider llm={...} memory="./app.mv2">
 *   <Chat />
 * </Canvas.Provider>
 * ```
 *
 * @example
 * ```tsx
 * <Chat
 *   header={<h1>AI Assistant</h1>}
 *   placeholder="Ask me anything..."
 *   streaming={true}
 *   onSend={(msg) => console.log('Sent:', msg)}
 *   onResponse={(msg) => console.log('Response:', msg)}
 * />
 * ```
 */
export function Chat({
  // UseChatOptions
  agent,
  conversationId,
  includeContext,
  onSend,
  onResponse,
  onError,
  onStreamStart,
  onStreamEnd,
  // Chat-specific props
  className,
  header,
  footer,
  emptyState,
  userAvatar,
  assistantAvatar,
  renderContent,
  renderMessage,
  placeholder,
  submitButton,
  stopButton,
  streaming = true,
}: ChatProps) {
  const {
    messages,
    isLoading,
    isStreaming,
    error,
    input,
    setInput,
    send,
    sendStream,
    stop,
  } = useChat({
    agent,
    conversationId,
    includeContext,
    onSend,
    onResponse,
    onError,
    onStreamStart,
    onStreamEnd,
  });

  /**
   * Handle submit based on streaming preference
   */
  const handleSubmit = () => {
    if (streaming) {
      sendStream();
    } else {
      send();
    }
  };

  return (
    <div className={clsx('canvas-chat', className)}>
      {header && <div className="canvas-chat__header">{header}</div>}

      <ChatMessages
        messages={messages}
        isStreaming={isStreaming}
        userAvatar={userAvatar}
        assistantAvatar={assistantAvatar}
        renderContent={renderContent}
        renderMessage={renderMessage}
        emptyState={emptyState}
        className="canvas-chat__messages"
      />

      {error && (
        <div className="canvas-chat__error" role="alert">
          {error}
        </div>
      )}

      <ChatInput
        value={input}
        onChange={setInput}
        onSubmit={handleSubmit}
        onStop={stop}
        isLoading={isLoading}
        isStreaming={isStreaming}
        placeholder={placeholder}
        submitButton={submitButton}
        stopButton={stopButton}
        className="canvas-chat__input"
      />

      {footer && <div className="canvas-chat__footer">{footer}</div>}
    </div>
  );
}
