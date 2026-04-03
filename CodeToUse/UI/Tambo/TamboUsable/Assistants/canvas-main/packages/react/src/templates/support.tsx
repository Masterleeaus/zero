/**
 * Canvas.Support - Customer Support Template
 *
 * A production-ready customer support interface with:
 * - AI-powered chat with memory-based responses
 * - Knowledge base search
 * - Suggested responses
 * - Ticket creation and escalation
 * - Feedback collection
 *
 * Features:
 * - Streaming responses
 * - Source citations from memory
 * - Quick action suggestions
 * - Typing indicators
 * - Message history
 * - Human handoff support
 *
 * @example
 * ```tsx
 * <Canvas.Support brand={brand} memoryEndpoint="/api/memory" />
 * ```
 */

'use client';

import React, { useState, useRef, useEffect, useCallback } from 'react';
import Markdown from 'react-markdown';
import type { BrandConfig } from '../types/brand.js';

interface SupportProps {
  brand?: BrandConfig;
  config?: Record<string, unknown>;
  memoryEndpoint?: string;
  onEvent?: (event: string, data?: Record<string, unknown>) => void;
}

interface Message {
  id: string;
  role: 'user' | 'assistant' | 'system';
  content: string;
  timestamp: Date;
  sources?: { title: string; uri: string; score?: number }[];
  isStreaming?: boolean;
}

interface Suggestion {
  id: string;
  text: string;
}

// Storage key for conversation persistence
const CONVERSATION_KEY = 'canvas-conversation';

/**
 * Load conversation from localStorage
 */
function loadConversation(): Message[] {
  if (typeof window === 'undefined') return [];
  try {
    const stored = localStorage.getItem(CONVERSATION_KEY);
    if (stored) {
      const parsed = JSON.parse(stored);
      // Convert timestamp strings back to Date objects
      return parsed.map((msg: Message & { timestamp: string }) => ({
        ...msg,
        timestamp: new Date(msg.timestamp),
      }));
    }
  } catch {
    // Ignore errors
  }
  return [];
}

/**
 * Save conversation to localStorage
 */
function saveConversation(messages: Message[]): void {
  if (typeof window === 'undefined') return;
  try {
    // Only save the last 50 messages to avoid localStorage limits
    const toSave = messages.slice(-50);
    localStorage.setItem(CONVERSATION_KEY, JSON.stringify(toSave));
  } catch {
    // Ignore errors
  }
}

/**
 * Avatar Component
 */
const Avatar = React.memo(function Avatar({
  role,
  src,
  name,
}: {
  role: 'user' | 'assistant';
  src?: string;
  name?: string;
}) {
  if (src) {
    return <img src={src} alt={name || role} className="canvas-avatar" />;
  }

  return (
    <div className={`canvas-avatar canvas-avatar--${role}`}>
      {role === 'assistant' ? (
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
          <path d="M10 2C5.58 2 2 5.58 2 10s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z" />
          <circle cx="7" cy="9" r="1.5" />
          <circle cx="13" cy="9" r="1.5" />
          <path d="M10 14c2 0 3.5-1.5 3.5-3h-7c0 1.5 1.5 3 3.5 3z" />
        </svg>
      ) : (
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
          <circle cx="10" cy="6" r="4" />
          <path d="M10 12c-4 0-7 2-7 4v2h14v-2c0-2-3-4-7-4z" />
        </svg>
      )}
    </div>
  );
});

/**
 * Source Detail Modal Component
 */
const SourceDetailModal = React.memo(function SourceDetailModal({
  source,
  onClose,
}: {
  source: { title: string; uri: string; score?: number; content?: string } | null;
  onClose: () => void;
}) {
  if (!source) return null;

  return (
    <div className="canvas-modal-overlay" onClick={onClose}>
      <div className="canvas-source-detail" onClick={e => e.stopPropagation()}>
        <header className="canvas-source-detail__header">
          <div className="canvas-source-detail__title-row">
            <h2 className="canvas-source-detail__title">{source.title}</h2>
            {source.score && (
              <span className="canvas-source-detail__score">
                {Math.round(source.score * 100)}% relevance
              </span>
            )}
          </div>
          <button className="canvas-source-detail__close" onClick={onClose} aria-label="Close">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M18 6L6 18M6 6l12 12" />
            </svg>
          </button>
        </header>

        <div className="canvas-source-detail__content">
          {source.content ? (
            <div className="canvas-source-detail__text">{source.content}</div>
          ) : (
            <p className="canvas-source-detail__placeholder">
              This source was used to generate the response. The full content is stored in the memory file.
            </p>
          )}
        </div>

        <footer className="canvas-source-detail__footer">
          <button className="canvas-btn canvas-btn--secondary" onClick={onClose}>
            Close
          </button>
        </footer>
      </div>
    </div>
  );
});

/**
 * Message Component
 */
const ChatMessage = React.memo(function ChatMessage({
  message,
  agentName,
  agentAvatar,
  showSources,
  onFeedback,
  onSourceClick,
}: {
  message: Message;
  agentName?: string;
  agentAvatar?: string;
  showSources?: boolean;
  onFeedback?: (messageId: string, feedback: 'positive' | 'negative') => void;
  onSourceClick?: (source: { title: string; uri: string; score?: number }) => void;
}) {
  const [feedbackGiven, setFeedbackGiven] = useState<'positive' | 'negative' | null>(null);

  const handleFeedback = (feedback: 'positive' | 'negative') => {
    setFeedbackGiven(feedback);
    onFeedback?.(message.id, feedback);
  };

  const handleSourceClick = (e: React.MouseEvent, source: { title: string; uri: string; score?: number }) => {
    e.preventDefault();
    onSourceClick?.(source);
  };

  return (
    <div className={`canvas-message canvas-message--${message.role}`}>
      <Avatar
        role={message.role === 'assistant' ? 'assistant' : 'user'}
        src={message.role === 'assistant' ? agentAvatar : undefined}
        name={message.role === 'assistant' ? agentName : 'You'}
      />

      <div className="canvas-message__content">
        <div className="canvas-message__header">
          <span className="canvas-message__name">
            {message.role === 'assistant' ? (agentName || 'Assistant') : 'You'}
          </span>
          <span className="canvas-message__time">
            {message.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
          </span>
        </div>

        <div className="canvas-message__text">
          {message.role === 'assistant' ? (
            <Markdown>{message.content}</Markdown>
          ) : (
            message.content
          )}
          {message.isStreaming && <span className="canvas-message__cursor">▋</span>}
        </div>

        {/* Sources */}
        {showSources && message.sources && message.sources.length > 0 && (
          <div className="canvas-message__sources">
            <span className="canvas-message__sources-label">Sources:</span>
            <ul className="canvas-message__sources-list">
              {message.sources.map((source, index) => (
                <li key={index} className="canvas-message__source">
                  <button
                    className="canvas-message__source-btn"
                    onClick={(e) => handleSourceClick(e, source)}
                  >
                    {source.title}
                  </button>
                  {source.score && (
                    <span className="canvas-message__source-score">
                      {Math.round(source.score * 100)}%
                    </span>
                  )}
                </li>
              ))}
            </ul>
          </div>
        )}

        {/* Feedback buttons for assistant messages */}
        {message.role === 'assistant' && !message.isStreaming && onFeedback && (
          <div className="canvas-message__feedback">
            <button
              className={`canvas-message__feedback-btn ${feedbackGiven === 'positive' ? 'canvas-message__feedback-btn--active' : ''}`}
              onClick={() => handleFeedback('positive')}
              disabled={feedbackGiven !== null}
              aria-label="Helpful"
            >
              👍
            </button>
            <button
              className={`canvas-message__feedback-btn ${feedbackGiven === 'negative' ? 'canvas-message__feedback-btn--active' : ''}`}
              onClick={() => handleFeedback('negative')}
              disabled={feedbackGiven !== null}
              aria-label="Not helpful"
            >
              👎
            </button>
          </div>
        )}
      </div>
    </div>
  );
});

/**
 * Suggestions Component
 */
const Suggestions = React.memo(function Suggestions({
  suggestions,
  onSelect,
}: {
  suggestions: Suggestion[];
  onSelect: (text: string) => void;
}) {
  if (suggestions.length === 0) return null;

  return (
    <div className="canvas-suggestions">
      {suggestions.map(suggestion => (
        <button
          key={suggestion.id}
          className="canvas-suggestion"
          onClick={() => onSelect(suggestion.text)}
        >
          {suggestion.text}
        </button>
      ))}
    </div>
  );
});

/**
 * Typing Indicator Component
 */
const TypingIndicator = React.memo(function TypingIndicator({ name }: { name?: string }) {
  return (
    <div className="canvas-typing">
      <span className="canvas-typing__name">{name || 'Assistant'}</span>
      <span className="canvas-typing__dots">
        <span className="canvas-typing__dot" />
        <span className="canvas-typing__dot" />
        <span className="canvas-typing__dot" />
      </span>
    </div>
  );
});

/**
 * Create Ticket Modal Component
 */
const TicketModal = React.memo(function TicketModal({
  isOpen,
  onClose,
  onSubmit,
  conversationSummary,
}: {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: { subject: string; description: string; priority: string }) => void;
  conversationSummary?: string;
}) {
  const [subject, setSubject] = useState('');
  const [description, setDescription] = useState(conversationSummary || '');
  const [priority, setPriority] = useState('medium');

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({ subject, description, priority });
    onClose();
  };

  return (
    <div className="canvas-modal-overlay" onClick={onClose}>
      <div className="canvas-modal" onClick={e => e.stopPropagation()}>
        <header className="canvas-modal__header">
          <h2>Create Support Ticket</h2>
          <button className="canvas-modal__close" onClick={onClose}>×</button>
        </header>

        <form onSubmit={handleSubmit} className="canvas-modal__body">
          <div className="canvas-form-group">
            <label htmlFor="ticket-subject">Subject</label>
            <input
              id="ticket-subject"
              type="text"
              value={subject}
              onChange={e => setSubject(e.target.value)}
              placeholder="Brief description of the issue"
              required
            />
          </div>

          <div className="canvas-form-group">
            <label htmlFor="ticket-description">Description</label>
            <textarea
              id="ticket-description"
              value={description}
              onChange={e => setDescription(e.target.value)}
              placeholder="Detailed description..."
              rows={5}
              required
            />
          </div>

          <div className="canvas-form-group">
            <label htmlFor="ticket-priority">Priority</label>
            <select
              id="ticket-priority"
              value={priority}
              onChange={e => setPriority(e.target.value)}
            >
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>

          <div className="canvas-modal__footer">
            <button type="button" onClick={onClose} className="canvas-btn canvas-btn--secondary">
              Cancel
            </button>
            <button type="submit" className="canvas-btn canvas-btn--primary">
              Create Ticket
            </button>
          </div>
        </form>
      </div>
    </div>
  );
});

/**
 * Main Support Component
 */
export function Support({
  brand,
  config,
  memoryEndpoint = '/api/canvas',
  onEvent,
}: SupportProps) {
  const supportConfig = { ...(brand?.support || {}), ...(config?.support || {}) };
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLTextAreaElement>(null);

  const [messages, setMessages] = useState<Message[]>([]);
  const [input, setInput] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [showTicketModal, setShowTicketModal] = useState(false);
  const [selectedSource, setSelectedSource] = useState<{ title: string; uri: string; score?: number; content?: string } | null>(null);
  const [suggestions, setSuggestions] = useState<Suggestion[]>(() => {
    return (supportConfig.suggestions || []).map((text: string, index: number) => ({
      id: `suggestion-${index}`,
      text,
    }));
  });

  // Load conversation from localStorage on mount, or show welcome message
  useEffect(() => {
    const saved = loadConversation();
    if (saved.length > 0) {
      setMessages(saved);
      setSuggestions([]); // Hide suggestions if there's history
    } else if (supportConfig.welcomeMessage) {
      setMessages([{
        id: 'welcome',
        role: 'assistant',
        content: supportConfig.welcomeMessage,
        timestamp: new Date(),
      }]);
    }
  }, [supportConfig.welcomeMessage]);

  // Save conversation to localStorage when messages change
  // Save if there are user messages (not just the welcome message)
  useEffect(() => {
    const hasUserMessages = messages.some(msg => msg.role === 'user');
    if (hasUserMessages) {
      saveConversation(messages);
    }
  }, [messages]);

  // Scroll to bottom when messages change
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  // Handle sending a message
  const handleSend = useCallback(async (text?: string) => {
    const messageText = text || input.trim();
    if (!messageText || isLoading) return;

    const userMessage: Message = {
      id: `user-${Date.now()}`,
      role: 'user',
      content: messageText,
      timestamp: new Date(),
    };

    setMessages(prev => [...prev, userMessage]);
    setInput('');
    setSuggestions([]); // Clear suggestions after first message
    setIsLoading(true);

    onEvent?.('message_sent', { content: messageText });

    try {
      // Create streaming assistant message
      const assistantMessage: Message = {
        id: `assistant-${Date.now()}`,
        role: 'assistant',
        content: '',
        timestamp: new Date(),
        isStreaming: true,
      };
      setMessages(prev => [...prev, assistantMessage]);

      // Get API keys from localStorage settings
      let llmApiKey: string | undefined;
      let llmProvider: string | undefined;
      let llmModel: string | undefined;
      try {
        const settings = localStorage.getItem('canvas-settings');
        if (settings) {
          const parsed = JSON.parse(settings);
          llmApiKey = parsed.llmApiKey;
          llmProvider = parsed.llmProvider;
          llmModel = parsed.llmModel;
        }
      } catch {
        // Ignore localStorage errors
      }

      // Call the chat API
      const response = await fetch(`${memoryEndpoint}/chat`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: messageText,
          conversationId: messages[0]?.id || 'new',
          llmApiKey,
          llmProvider,
          llmModel,
        }),
      });

      if (!response.ok) throw new Error('Failed to get response');

      const data = await response.json();

      // Update the assistant message with the response
      setMessages(prev => prev.map(msg =>
        msg.id === assistantMessage.id
          ? {
              ...msg,
              content: data.response || data.content || 'I apologize, but I could not generate a response.',
              sources: data.sources,
              isStreaming: false,
            }
          : msg
      ));

      onEvent?.('response_received', { sources: data.sources?.length || 0 });
    } catch (error) {
      // Update with error message
      setMessages(prev => prev.map(msg =>
        msg.isStreaming
          ? {
              ...msg,
              content: 'I apologize, but I encountered an error. Please try again or create a support ticket.',
              isStreaming: false,
            }
          : msg
      ));

      onEvent?.('error', { error: error instanceof Error ? error.message : 'Unknown error' });
    } finally {
      setIsLoading(false);
    }
  }, [input, isLoading, messages, memoryEndpoint, onEvent]);

  // Handle feedback
  const handleFeedback = useCallback((messageId: string, feedback: 'positive' | 'negative') => {
    onEvent?.('feedback', { messageId, feedback });

    // Optionally send to backend
    fetch(`${memoryEndpoint}/feedback`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ messageId, feedback }),
    }).catch(() => {}); // Fire and forget
  }, [memoryEndpoint, onEvent]);

  // Handle source click - show detail modal
  const handleSourceClick = useCallback((source: { title: string; uri: string; score?: number }) => {
    setSelectedSource(source);
    onEvent?.('source_clicked', { title: source.title, uri: source.uri });
  }, [onEvent]);

  // Handle ticket creation
  const handleCreateTicket = useCallback((data: { subject: string; description: string; priority: string }) => {
    onEvent?.('ticket_created', data);

    // Send to backend
    fetch(`${memoryEndpoint}/ticket`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ...data,
        conversationId: messages[0]?.id,
      }),
    }).then(() => {
      setMessages(prev => [...prev, {
        id: `system-${Date.now()}`,
        role: 'system',
        content: `Support ticket created: ${data.subject}. Our team will get back to you soon.`,
        timestamp: new Date(),
      }]);
    }).catch(() => {});
  }, [messages, memoryEndpoint, onEvent]);

  // Clear conversation
  const handleClearConversation = useCallback(() => {
    localStorage.removeItem(CONVERSATION_KEY);
    setMessages(supportConfig.welcomeMessage ? [{
      id: 'welcome',
      role: 'assistant',
      content: supportConfig.welcomeMessage,
      timestamp: new Date(),
    }] : []);
    setSuggestions((supportConfig.suggestions || []).map((text: string, index: number) => ({
      id: `suggestion-${index}`,
      text,
    })));
    onEvent?.('conversation_cleared', {});
  }, [supportConfig.welcomeMessage, supportConfig.suggestions, onEvent]);

  // Handle key press
  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  return (
    <div className="canvas-support canvas-support--modern">
      {/* Messages Area - takes most of the space */}
      <div className="canvas-support__messages-area">
        {/* Header - fixed at top */}
        <header className="canvas-support__header">
          <div className="canvas-support__header-left">
            <Avatar role="assistant" src={supportConfig.agentAvatar} name={supportConfig.agentName} />
            <div className="canvas-support__header-info">
              <h2 className="canvas-support__header-name">{supportConfig.agentName || 'Canvas Assistant'}</h2>
              <span className="canvas-support__header-status">
                <span className="canvas-support__status-dot" />
                Online
              </span>
            </div>
          </div>
          <div className="canvas-support__header-actions">
            {messages.length > 1 && (
              <button
                className="canvas-support__clear-btn"
                onClick={handleClearConversation}
                aria-label="Clear conversation"
              >
                Clear
              </button>
            )}
            {supportConfig.enableTickets !== false && (
              <button
                className="canvas-support__ticket-btn"
                onClick={() => setShowTicketModal(true)}
              >
                Create Ticket
              </button>
            )}
          </div>
        </header>

        {/* Messages - scrollable area */}
        <div className="canvas-support__messages">
          <div className="canvas-support__messages-inner">
            {messages.map(message => (
              <ChatMessage
                key={message.id}
                message={message}
                agentName={supportConfig.agentName}
                agentAvatar={supportConfig.agentAvatar}
                showSources={supportConfig.showSources !== false}
                onFeedback={supportConfig.enableFeedback !== false ? handleFeedback : undefined}
                onSourceClick={handleSourceClick}
              />
            ))}

            {isLoading && <TypingIndicator name={supportConfig.agentName} />}

            <div ref={messagesEndRef} />
          </div>
        </div>
      </div>

      {/* Bottom Input Area - fixed at bottom, centered */}
      <div className="canvas-support__bottom">
        {/* Suggestions - shown above input */}
        {suggestions.length > 0 && (
          <div className="canvas-support__suggestions-wrapper">
            <Suggestions suggestions={suggestions} onSelect={handleSend} />
          </div>
        )}

        {/* Input Container - centered with max-width */}
        <div className="canvas-support__input-wrapper">
          <div className="canvas-support__input-container">
            <textarea
              ref={inputRef}
              className="canvas-support__input"
              value={input}
              onChange={e => setInput(e.target.value)}
              onKeyPress={handleKeyPress}
              placeholder={supportConfig.inputPlaceholder || 'Ask me anything...'}
              rows={1}
              disabled={isLoading}
            />
            <button
              className="canvas-support__send-btn"
              onClick={() => handleSend()}
              disabled={!input.trim() || isLoading}
              aria-label="Send message"
            >
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M18 2L9 11M18 2L12 18L9 11M18 2L2 8L9 11" />
              </svg>
            </button>
          </div>
          <p className="canvas-support__disclaimer">
            Powered by Memvid memory search
          </p>
        </div>
      </div>

      {/* Ticket Modal */}
      <TicketModal
        isOpen={showTicketModal}
        onClose={() => setShowTicketModal(false)}
        onSubmit={handleCreateTicket}
        conversationSummary={messages.slice(-5).map(m => m.content).join('\n')}
      />

      {/* Source Detail Modal */}
      <SourceDetailModal
        source={selectedSource}
        onClose={() => setSelectedSource(null)}
      />
    </div>
  );
}

export default Support;
