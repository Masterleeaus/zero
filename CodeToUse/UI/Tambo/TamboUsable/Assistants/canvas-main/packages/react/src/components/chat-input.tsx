/**
 * ChatInput Component
 *
 * Input field for sending messages.
 */

import {
  useRef,
  useCallback,
  type FormEvent,
  type KeyboardEvent,
  type ChangeEvent,
  type ReactNode,
} from 'react';
import clsx from 'clsx';

/**
 * ChatInput props
 */
export interface ChatInputProps {
  /** Current input value */
  value: string;

  /** Value change handler */
  onChange: (value: string) => void;

  /** Submit handler */
  onSubmit: () => void;

  /** Stop handler (when streaming) */
  onStop?: () => void;

  /** Whether currently loading/streaming */
  isLoading?: boolean;

  /** Whether currently streaming */
  isStreaming?: boolean;

  /** Placeholder text */
  placeholder?: string;

  /** Whether input is disabled */
  disabled?: boolean;

  /** Custom class name */
  className?: string;

  /** Submit button content */
  submitButton?: ReactNode;

  /** Stop button content */
  stopButton?: ReactNode;

  /** Whether to auto-focus */
  autoFocus?: boolean;

  /** Max rows for textarea */
  maxRows?: number;
}

/**
 * ChatInput component
 *
 * @example
 * ```tsx
 * const { input, setInput, send, isLoading, stop, isStreaming } = useChat();
 *
 * <ChatInput
 *   value={input}
 *   onChange={setInput}
 *   onSubmit={send}
 *   onStop={stop}
 *   isLoading={isLoading}
 *   isStreaming={isStreaming}
 * />
 * ```
 */
export function ChatInput({
  value,
  onChange,
  onSubmit,
  onStop,
  isLoading = false,
  isStreaming = false,
  placeholder = 'Type a message...',
  disabled = false,
  className,
  submitButton,
  stopButton,
  autoFocus = true,
  maxRows = 5,
}: ChatInputProps) {
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  /**
   * Handle form submit
   */
  const handleSubmit = useCallback(
    (e: FormEvent) => {
      e.preventDefault();
      if (value.trim() && !isLoading && !disabled) {
        onSubmit();
      }
    },
    [value, isLoading, disabled, onSubmit]
  );

  /**
   * Handle textarea change
   */
  const handleChange = useCallback(
    (e: ChangeEvent<HTMLTextAreaElement>) => {
      onChange(e.target.value);

      // Auto-resize textarea
      const textarea = e.target;
      textarea.style.height = 'auto';
      const lineHeight = parseInt(getComputedStyle(textarea).lineHeight) || 20;
      const maxHeight = lineHeight * maxRows;
      textarea.style.height = `${Math.min(textarea.scrollHeight, maxHeight)}px`;
    },
    [onChange, maxRows]
  );

  /**
   * Handle keyboard events
   */
  const handleKeyDown = useCallback(
    (e: KeyboardEvent<HTMLTextAreaElement>) => {
      // Submit on Enter (without Shift)
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (value.trim() && !isLoading && !disabled) {
          onSubmit();
        }
      }
    },
    [value, isLoading, disabled, onSubmit]
  );

  /**
   * Handle stop click
   */
  const handleStop = useCallback(() => {
    if (onStop && isStreaming) {
      onStop();
    }
  }, [onStop, isStreaming]);

  /**
   * Determine which button to show
   */
  const showStopButton = isStreaming && onStop;
  const isDisabled = disabled || (!showStopButton && (isLoading || !value.trim()));

  return (
    <form
      className={clsx('canvas-input', className)}
      onSubmit={handleSubmit}
    >
      <textarea
        ref={textareaRef}
        className="canvas-input__textarea"
        value={value}
        onChange={handleChange}
        onKeyDown={handleKeyDown}
        placeholder={placeholder}
        disabled={disabled || isLoading}
        autoFocus={autoFocus}
        rows={1}
        aria-label="Message input"
      />

      {showStopButton ? (
        <button
          type="button"
          className="canvas-input__button canvas-input__button--stop"
          onClick={handleStop}
          aria-label="Stop generating"
        >
          {stopButton ?? (
            <svg
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="currentColor"
              aria-hidden="true"
            >
              <rect x="3" y="3" width="10" height="10" rx="1" />
            </svg>
          )}
        </button>
      ) : (
        <button
          type="submit"
          className="canvas-input__button canvas-input__button--submit"
          disabled={isDisabled}
          aria-label="Send message"
        >
          {submitButton ?? (
            <svg
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="currentColor"
              aria-hidden="true"
            >
              <path d="M1.5 1.5L14.5 8L1.5 14.5V9L10.5 8L1.5 7V1.5Z" />
            </svg>
          )}
        </button>
      )}
    </form>
  );
}
