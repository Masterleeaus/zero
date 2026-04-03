/**
 * ChatMessages Component
 *
 * Renders a list of messages with auto-scroll.
 */

import { useRef, useEffect, type ReactNode } from 'react';
import clsx from 'clsx';
import type { Message } from '@memvid/canvas-core/types-only';
import { ChatMessage, type ChatMessageProps } from './chat-message.js';

/**
 * ChatMessages props
 */
export interface ChatMessagesProps {
  /** Messages to render */
  messages: Message[];

  /** Whether currently streaming */
  isStreaming?: boolean;

  /** Custom class name */
  className?: string;

  /** Custom avatar for user */
  userAvatar?: ReactNode;

  /** Custom avatar for assistant */
  assistantAvatar?: ReactNode;

  /** Custom renderer for message content */
  renderContent?: ChatMessageProps['renderContent'];

  /** Custom message component */
  renderMessage?: (message: Message, index: number) => ReactNode;

  /** Empty state content */
  emptyState?: ReactNode;

  /** Whether to auto-scroll on new messages */
  autoScroll?: boolean;
}

/**
 * Default empty state
 */
function DefaultEmptyState() {
  return (
    <div className="canvas-messages__empty">
      <p>Start a conversation</p>
    </div>
  );
}

/**
 * ChatMessages component
 *
 * @example
 * ```tsx
 * const { messages, isStreaming } = useChat();
 *
 * <ChatMessages
 *   messages={messages}
 *   isStreaming={isStreaming}
 * />
 * ```
 */
export function ChatMessages({
  messages,
  isStreaming = false,
  className,
  userAvatar,
  assistantAvatar,
  renderContent,
  renderMessage,
  emptyState,
  autoScroll = true,
}: ChatMessagesProps) {
  const containerRef = useRef<HTMLDivElement>(null);
  const bottomRef = useRef<HTMLDivElement>(null);

  /**
   * Auto-scroll to bottom on new messages
   */
  useEffect(() => {
    if (autoScroll && bottomRef.current) {
      bottomRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [messages, autoScroll]);

  /**
   * Render empty state
   */
  if (messages.length === 0) {
    return (
      <div className={clsx('canvas-messages', 'canvas-messages--empty', className)}>
        {emptyState ?? <DefaultEmptyState />}
      </div>
    );
  }

  return (
    <div
      ref={containerRef}
      className={clsx('canvas-messages', className)}
      role="log"
      aria-label="Chat messages"
      aria-live="polite"
    >
      {messages.map((message, index) => {
        const isLast = index === messages.length - 1;
        const isLastAssistant = isLast && message.role === 'assistant';

        if (renderMessage) {
          return renderMessage(message, index);
        }

        return (
          <ChatMessage
            key={message.id}
            message={message}
            userAvatar={userAvatar}
            assistantAvatar={assistantAvatar}
            renderContent={renderContent}
            isStreaming={isLastAssistant && isStreaming}
          />
        );
      })}

      <div ref={bottomRef} className="canvas-messages__bottom" aria-hidden="true" />
    </div>
  );
}
