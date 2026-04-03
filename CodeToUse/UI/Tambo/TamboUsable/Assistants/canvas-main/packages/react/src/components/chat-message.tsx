/**
 * ChatMessage Component
 *
 * Renders a single message in the chat.
 */

import { useMemo, type ReactNode } from 'react';
import clsx from 'clsx';
import type { Message } from '@memvid/canvas-core/types-only';

/**
 * ChatMessage props
 */
export interface ChatMessageProps {
  /** The message to render */
  message: Message;

  /** Custom class name */
  className?: string;

  /** Custom avatar for user */
  userAvatar?: ReactNode;

  /** Custom avatar for assistant */
  assistantAvatar?: ReactNode;

  /** Custom renderer for message content */
  renderContent?: (content: string, message: Message) => ReactNode;

  /** Whether message is currently streaming */
  isStreaming?: boolean;
}

/**
 * Default avatar component
 */
function DefaultAvatar({ role }: { role: 'user' | 'assistant' | 'system' }) {
  const label = role === 'user' ? 'U' : role === 'assistant' ? 'A' : 'S';

  return (
    <div
      className={clsx('canvas-avatar', `canvas-avatar--${role}`)}
      aria-label={role}
    >
      {label}
    </div>
  );
}

/**
 * Streaming indicator
 */
function StreamingIndicator() {
  return (
    <span className="canvas-streaming-indicator" aria-label="Typing">
      <span className="canvas-streaming-dot" />
      <span className="canvas-streaming-dot" />
      <span className="canvas-streaming-dot" />
    </span>
  );
}

/**
 * ChatMessage component
 *
 * @example
 * ```tsx
 * <ChatMessage
 *   message={{ id: '1', role: 'user', content: 'Hello!' }}
 * />
 * ```
 */
export function ChatMessage({
  message,
  className,
  userAvatar,
  assistantAvatar,
  renderContent,
  isStreaming = false,
}: ChatMessageProps) {
  const { role, content, status } = message;

  /**
   * Get avatar based on role
   */
  const avatar = useMemo(() => {
    if (role === 'user') {
      return userAvatar ?? <DefaultAvatar role="user" />;
    }
    if (role === 'assistant') {
      return assistantAvatar ?? <DefaultAvatar role="assistant" />;
    }
    return <DefaultAvatar role="system" />;
  }, [role, userAvatar, assistantAvatar]);

  /**
   * Render content
   */
  const renderedContent = useMemo(() => {
    if (renderContent) {
      return renderContent(content, message);
    }
    return content;
  }, [content, message, renderContent]);

  /**
   * Show streaming indicator
   */
  const showStreaming = isStreaming || status === 'streaming';
  const isEmpty = !content.trim();

  return (
    <div
      className={clsx(
        'canvas-message',
        `canvas-message--${role}`,
        {
          'canvas-message--streaming': showStreaming,
          'canvas-message--error': status === 'error',
        },
        className
      )}
      data-role={role}
      data-status={status}
    >
      <div className="canvas-message__avatar">{avatar}</div>

      <div className="canvas-message__content">
        {isEmpty && showStreaming ? (
          <StreamingIndicator />
        ) : (
          <>
            {renderedContent}
            {showStreaming && !isEmpty && <StreamingIndicator />}
          </>
        )}
      </div>
    </div>
  );
}
