'use client';

/**
 * Canvas Shell
 *
 * The main application shell with editorial aesthetic matching the setup wizard.
 * Features a sophisticated sidebar navigation, content area, and feature-aware UI.
 * Supports component slots for full customization.
 */

import React, { useState, useEffect, useCallback, type ReactNode, type ComponentType } from 'react';
import type { RuntimeSettings, CanvasUIConfig } from '@memvid/canvas-core/types-only';
import type { CanvasShellSlots } from '../slots/types.js';
import { isSlotComponent } from '../slots/types.js';
import { BrandLogo } from './brand-logo.js';
import { useText } from '../hooks/use-text.js';

function normalizeNewlines(input: string): string {
  return input.replace(/\r\n/g, '\n');
}

function parseInlineMarkdown(text: string): Array<{ kind: 'text' | 'strong' | 'code'; value: string }> {
  const tokens: Array<{ kind: 'text' | 'strong' | 'code'; value: string }> = [];
  let i = 0;

  const pushText = (value: string) => {
    if (value) tokens.push({ kind: 'text', value });
  };

  while (i < text.length) {
    const nextCode = text.indexOf('`', i);
    const nextStrong = text.indexOf('**', i);
    const next = Math.min(nextCode === -1 ? Infinity : nextCode, nextStrong === -1 ? Infinity : nextStrong);

    if (next === Infinity) {
      pushText(text.slice(i));
      break;
    }

    pushText(text.slice(i, next));

    if (next === nextCode) {
      const end = text.indexOf('`', next + 1);
      if (end === -1) {
        pushText(text.slice(next));
        break;
      }
      tokens.push({ kind: 'code', value: text.slice(next + 1, end) });
      i = end + 1;
      continue;
    }

    const end = text.indexOf('**', next + 2);
    if (end === -1) {
      pushText(text.slice(next));
      break;
    }
    tokens.push({ kind: 'strong', value: text.slice(next + 2, end) });
    i = end + 2;
  }

  return tokens;
}

function MarkdownLite({ text }: { text: string }) {
  const content = normalizeNewlines(text);
  const lines = content.split('\n');

  const blocks: Array<
    | { type: 'p'; lines: string[] }
    | { type: 'ul'; items: string[] }
    | { type: 'code'; code: string; lang?: string }
  > = [];

  let paragraphLines: string[] = [];
  let listItems: string[] = [];
  let inCode = false;
  let codeLang: string | undefined;
  let codeLines: string[] = [];

  const flushParagraph = () => {
    if (paragraphLines.length === 0) return;
    blocks.push({ type: 'p', lines: paragraphLines });
    paragraphLines = [];
  };

  const flushList = () => {
    if (listItems.length === 0) return;
    blocks.push({ type: 'ul', items: listItems });
    listItems = [];
  };

  for (const rawLine of lines) {
    const trimmedRight = rawLine.replace(/\s+$/, '');

    if (trimmedRight.startsWith('```')) {
      if (!inCode) {
        flushParagraph();
        flushList();
        inCode = true;
        codeLang = trimmedRight.slice(3).trim() || undefined;
        codeLines = [];
      } else {
        blocks.push({ type: 'code', code: codeLines.join('\n'), lang: codeLang });
        inCode = false;
        codeLang = undefined;
        codeLines = [];
      }
      continue;
    }

    if (inCode) {
      codeLines.push(rawLine);
      continue;
    }

    const isBlank = trimmedRight.trim().length === 0;
    const isListItem = trimmedRight.trimStart().startsWith('- ');

    if (isBlank) {
      flushParagraph();
      flushList();
      continue;
    }

    if (isListItem) {
      flushParagraph();
      listItems.push(trimmedRight.trimStart().slice(2));
      continue;
    }

    flushList();
    paragraphLines.push(trimmedRight);
  }

  flushParagraph();
  flushList();

  const renderInline = (value: string) =>
    parseInlineMarkdown(value).map((t, idx) => {
      if (t.kind === 'strong') return <strong key={idx}>{t.value}</strong>;
      if (t.kind === 'code') return <code key={idx}>{t.value}</code>;
      return <span key={idx}>{t.value}</span>;
    });

  return (
    <div className="cs-markdown">
      {blocks.map((b, idx) => {
        if (b.type === 'code') {
          return (
            <pre key={idx} className="cs-code">
              <code>{b.code}</code>
            </pre>
          );
        }
        if (b.type === 'ul') {
          return (
            <ul key={idx}>
              {b.items.map((item, i) => (
                <li key={i}>{renderInline(item)}</li>
              ))}
            </ul>
          );
        }
        return (
          <p key={idx}>
            {b.lines.map((line, i) => (
              <React.Fragment key={i}>
                {renderInline(line)}
                {i < b.lines.length - 1 ? <br /> : null}
              </React.Fragment>
            ))}
          </p>
        );
      })}
    </div>
  );
}

// ============================================================================
// Types
// ============================================================================

/**
 * Text customization interface for CanvasShell
 */
export interface CanvasShellTexts {
  /** Search title */
  searchTitle?: string;
  /** Search subtitle */
  searchSubtitle?: string;
  /** Search placeholder */
  searchPlaceholder?: string;
  /** Chat empty state title */
  chatEmptyTitle?: string;
  /** Chat empty state description */
  chatEmptyDescription?: string;
  /** Chat input placeholder */
  chatPlaceholder?: string;
}

export interface CanvasShellProps {
  children?: React.ReactNode;
  activeView?: 'search' | 'chat' | 'dashboard';
  onViewChange?: (view: 'search' | 'chat' | 'dashboard') => void;
  /** Settings from the canvas context */
  settings: RuntimeSettings;
  /** Config from the canvas context */
  config: Partial<CanvasUIConfig>;
  /** Navigation handler - called when settings button is clicked */
  onNavigate?: (path: string) => void;
  /** API base path for memory endpoints (default: '/api/canvas') */
  apiBasePath?: string;
  /** Text customization */
  texts?: CanvasShellTexts;
  /** Component slots for customization */
  slots?: CanvasShellSlots;
}

// ============================================================================
// Styles (CSS-in-JS for portability - matching setup wizard aesthetic)
// ============================================================================

const styles = `
  @import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

  .canvas-shell {
    --cs-bg: #09090b;
    --cs-surface: #18181b;
    --cs-surface-elevated: #27272a;
    --cs-border: #3f3f46;
    --cs-border-subtle: #27272a;
    --cs-text: #fafafa;
    --cs-text-muted: #a1a1aa;
    --cs-text-subtle: #71717a;
    --cs-primary: #818cf8;
    --cs-primary-hover: #a5b4fc;
    --cs-primary-muted: rgba(129, 140, 248, 0.1);
    --cs-accent: #34d399;
    --cs-error: #f87171;
    --cs-success: #4ade80;
    --cs-radius: 12px;
    --cs-radius-sm: 8px;
    --cs-radius-lg: 16px;
    --cs-font-display: 'Instrument Serif', Georgia, serif;
    --cs-font-body: 'Outfit', system-ui, sans-serif;
    --cs-font-mono: 'JetBrains Mono', monospace;

    position: fixed;
    inset: 0;
    background: var(--cs-bg);
    font-family: var(--cs-font-body);
    color: var(--cs-text);
    display: flex;
    overflow: hidden;
  }

  .canvas-shell * {
    box-sizing: border-box;
  }

  /* Background Pattern */
  .cs-background {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
  }

  .cs-background::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background:
      radial-gradient(ellipse at 10% 10%, rgba(129, 140, 248, 0.06) 0%, transparent 50%),
      radial-gradient(ellipse at 90% 90%, rgba(52, 211, 153, 0.04) 0%, transparent 50%);
    animation: cs-bg-drift 40s ease-in-out infinite;
  }

  @keyframes cs-bg-drift {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(-1%, 1%) rotate(0.5deg); }
  }

  .cs-grid {
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(var(--cs-border-subtle) 1px, transparent 1px),
      linear-gradient(90deg, var(--cs-border-subtle) 1px, transparent 1px);
    background-size: 80px 80px;
    opacity: 0.15;
    mask-image: radial-gradient(ellipse at center, black 0%, transparent 80%);
  }

  /* Sidebar */
  .cs-sidebar {
    position: relative;
    width: var(--canvas-sidebar-width, 280px);
    background: var(--cs-surface);
    border-right: 1px solid var(--cs-border-subtle);
    display: flex;
    flex-direction: column;
    z-index: 10;
    flex-shrink: 0;
  }

  .cs-sidebar-header {
    padding: 24px;
    border-bottom: 1px solid var(--cs-border-subtle);
  }

  .cs-logo {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .cs-logo-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--cs-primary) 0%, var(--cs-accent) 100%);
    border-radius: var(--cs-radius-sm);
    color: white;
  }

  .cs-logo-text {
    font-family: var(--cs-font-display);
    font-size: 22px;
    font-weight: 400;
    color: var(--cs-text);
  }

  .cs-nav {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
  }

  .cs-nav-section {
    margin-bottom: 24px;
  }

  .cs-nav-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--cs-text-subtle);
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0 12px;
    margin-bottom: 8px;
  }

  .cs-nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: var(--cs-radius-sm);
    color: var(--cs-text-muted);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    font-weight: 500;
  }

  .cs-nav-item:hover {
    background: var(--cs-surface-elevated);
    color: var(--cs-text);
  }

  .cs-nav-item.active {
    background: var(--cs-primary-muted);
    color: var(--cs-primary);
  }

  .cs-nav-item.active .cs-nav-icon {
    color: var(--cs-primary);
  }

  .cs-nav-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--cs-text-subtle);
    transition: color 0.2s ease;
  }

  .cs-nav-item:hover .cs-nav-icon {
    color: var(--cs-text-muted);
  }

  .cs-nav-item.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    pointer-events: none;
  }

  .cs-sidebar-footer {
    padding: 16px;
    border-top: 1px solid var(--cs-border-subtle);
  }

  .cs-settings-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 12px 16px;
    background: transparent;
    border: 1px solid var(--cs-border-subtle);
    border-radius: var(--cs-radius-sm);
    color: var(--cs-text-muted);
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: var(--cs-font-body);
    font-size: 14px;
  }

  .cs-settings-btn:hover {
    background: var(--cs-surface-elevated);
    border-color: var(--cs-border);
    color: var(--cs-text);
  }

  /* Main Content */
  .cs-main {
    position: relative;
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 1;
  }

  .cs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 32px;
    border-bottom: 1px solid var(--cs-border-subtle);
    background: rgba(24, 24, 27, 0.8);
    backdrop-filter: blur(12px);
  }

  .cs-header-title {
    font-family: var(--cs-font-display);
    font-size: 28px;
    font-weight: 400;
    color: var(--cs-text);
    margin: 0;
  }

  .cs-header-subtitle {
    font-size: 14px;
    color: var(--cs-text-muted);
    margin-top: 4px;
  }

  .cs-header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .cs-content {
    flex: 1;
    overflow-y: auto;
    padding: 32px;
  }

  /* Search View */
  .cs-search-container {
    max-width: 800px;
    margin: 0 auto;
  }

  .cs-search-hero {
    text-align: center;
    padding: 48px 0 40px;
  }

  .cs-search-title {
    font-family: var(--cs-font-display);
    font-size: 42px;
    font-weight: 400;
    margin: 0 0 12px;
    background: linear-gradient(135deg, var(--cs-text) 0%, var(--cs-text-muted) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .cs-search-subtitle {
    font-size: 16px;
    color: var(--cs-text-muted);
    max-width: 480px;
    margin: 0 auto;
    line-height: 1.6;
  }

  .cs-search-box {
    position: relative;
    margin-bottom: 32px;
  }

  .cs-search-input {
    width: 100%;
    padding: 18px 24px 18px 56px;
    background: var(--cs-surface);
    border: 2px solid var(--cs-border-subtle);
    border-radius: var(--cs-radius-lg);
    font-family: var(--cs-font-body);
    font-size: 16px;
    color: var(--cs-text);
    transition: all 0.3s ease;
    outline: none;
  }

  .cs-search-input:focus {
    border-color: var(--cs-primary);
    box-shadow: 0 0 0 4px var(--cs-primary-muted), 0 8px 32px rgba(0, 0, 0, 0.3);
  }

  .cs-search-input::placeholder {
    color: var(--cs-text-subtle);
  }

  .cs-search-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--cs-text-subtle);
    pointer-events: none;
  }

  .cs-search-modes {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 40px;
  }

  .cs-mode-btn {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid var(--cs-border-subtle);
    border-radius: 100px;
    font-family: var(--cs-font-body);
    font-size: 13px;
    font-weight: 500;
    color: var(--cs-text-muted);
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .cs-mode-btn:hover {
    background: var(--cs-surface-elevated);
    border-color: var(--cs-border);
  }

  .cs-mode-btn.active {
    background: var(--cs-primary-muted);
    border-color: var(--cs-primary);
    color: var(--cs-primary);
  }

  /* Results */
  .cs-results {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .cs-result-card {
    background: var(--cs-surface);
    border: 1px solid var(--cs-border-subtle);
    border-radius: var(--cs-radius);
    padding: 20px 24px;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .cs-result-card:hover {
    border-color: var(--cs-border);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  }

  .cs-result-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
  }

  .cs-result-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--cs-text);
    margin: 0;
  }

  .cs-result-score {
    font-family: var(--cs-font-mono);
    font-size: 12px;
    color: var(--cs-accent);
    background: rgba(52, 211, 153, 0.1);
    padding: 4px 8px;
    border-radius: 4px;
  }

  .cs-result-content {
    font-size: 14px;
    color: var(--cs-text-muted);
    line-height: 1.6;
    margin-bottom: 12px;
  }

  .cs-result-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: var(--cs-text-subtle);
  }

  .cs-result-meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  /* Chat View */
  .cs-chat-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    max-width: var(--canvas-content-max-width, 900px);
    margin: 0 auto;
    width: 100%;
  }

  .cs-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 24px 0;
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .cs-message {
    display: flex;
    gap: 16px;
    max-width: 85%;
  }

  .cs-message.user {
    flex-direction: row-reverse;
    margin-left: auto;
  }

  .cs-message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .cs-message.assistant .cs-message-avatar {
    background: linear-gradient(135deg, var(--cs-primary) 0%, var(--cs-accent) 100%);
    color: white;
  }

  .cs-message.user .cs-message-avatar {
    background: var(--cs-surface-elevated);
    color: var(--cs-text-muted);
  }

  .cs-message-content {
    padding: 16px 20px;
    border-radius: var(--cs-radius);
    font-size: 15px;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
  }

  .cs-message.assistant .cs-message-content {
    background: var(--cs-surface);
    border: 1px solid var(--cs-border-subtle);
    color: var(--cs-text);
  }

  .cs-message.user .cs-message-content {
    background: var(--cs-primary);
    color: white;
  }

  .cs-markdown p {
    margin: 0 0 10px 0;
  }

  .cs-markdown p:last-child {
    margin-bottom: 0;
  }

  .cs-markdown ul {
    margin: 0 0 10px 18px;
    padding: 0;
  }

  .cs-markdown li {
    margin: 4px 0;
  }

  .cs-markdown code {
    font-family: var(--cs-font-mono);
    font-size: 0.92em;
    background: rgba(39, 39, 42, 0.7);
    border: 1px solid rgba(63, 63, 70, 0.6);
    padding: 1px 6px;
    border-radius: 8px;
  }

  .cs-code {
    margin: 10px 0;
    padding: 12px 14px;
    border-radius: 12px;
    background: rgba(9, 9, 11, 0.6);
    border: 1px solid rgba(63, 63, 70, 0.6);
    overflow: auto;
  }

  .cs-code code {
    background: transparent;
    border: 0;
    padding: 0;
  }

  .cs-sources {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--cs-border-subtle);
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 12px;
    color: var(--cs-text-subtle);
  }

  .cs-source {
    display: flex;
    gap: 8px;
    align-items: baseline;
  }

  .cs-source a {
    color: var(--cs-text-muted);
    text-decoration: none;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
  }

  .cs-source a:hover {
    text-decoration: underline;
    color: var(--cs-text);
  }

  .cs-source-score {
    font-family: var(--cs-font-mono);
    color: var(--cs-accent);
  }

  .cs-typing {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 0;
  }

  .cs-typing-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: rgba(250, 250, 250, 0.7);
    animation: cs-typing 1.2s infinite ease-in-out;
  }

  .cs-typing-dot:nth-child(2) { animation-delay: 0.15s; }
  .cs-typing-dot:nth-child(3) { animation-delay: 0.3s; }

  @keyframes cs-typing {
    0%, 80%, 100% { transform: translateY(0); opacity: 0.55; }
    40% { transform: translateY(-4px); opacity: 1; }
  }

  .cs-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 9999;
  }

  .cs-modal-panel {
    width: min(var(--canvas-content-max-width, 900px), 100%);
    max-height: min(80vh, var(--canvas-content-max-width, 900px));
    background: var(--cs-surface);
    border: 1px solid var(--cs-border);
    border-radius: var(--cs-radius-lg);
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.45);
    display: flex;
    flex-direction: column;
  }

  .cs-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--cs-border-subtle);
  }

  .cs-modal-title {
    font-weight: 600;
    color: var(--cs-text);
    font-size: 14px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding-right: 12px;
  }

  .cs-modal-close {
    border: 0;
    background: transparent;
    color: var(--cs-text-muted);
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 10px;
  }

  .cs-modal-close:hover {
    background: var(--cs-surface-elevated);
    color: var(--cs-text);
  }

  .cs-modal-body {
    padding: 16px;
    overflow: auto;
  }

  .cs-chat-input-container {
    padding: 24px 0;
    border-top: 1px solid var(--cs-border-subtle);
  }

  .cs-chat-input-box {
    display: flex;
    gap: 12px;
    background: var(--cs-surface);
    border: 2px solid var(--cs-border-subtle);
    border-radius: var(--cs-radius-lg);
    padding: 8px;
    transition: all 0.3s ease;
  }

  .cs-chat-input-box:focus-within {
    border-color: var(--cs-primary);
    box-shadow: 0 0 0 4px var(--cs-primary-muted);
  }

  .cs-chat-input {
    flex: 1;
    padding: 12px 16px;
    background: transparent;
    border: none;
    font-family: var(--cs-font-body);
    font-size: 15px;
    color: var(--cs-text);
    outline: none;
    resize: none;
  }

  .cs-chat-input::placeholder {
    color: var(--cs-text-subtle);
  }

  .cs-chat-send {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cs-primary);
    border: none;
    border-radius: var(--cs-radius-sm);
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .cs-chat-send:hover {
    background: var(--cs-primary-hover);
    transform: scale(1.05);
  }

  .cs-chat-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
  }

  /* Empty State */
  .cs-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
    padding: 48px;
  }

  .cs-empty-icon {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cs-surface);
    border: 2px solid var(--cs-border-subtle);
    border-radius: var(--cs-radius-lg);
    color: var(--cs-text-subtle);
    margin-bottom: 24px;
  }

  .cs-empty-title {
    font-family: var(--cs-font-display);
    font-size: 24px;
    color: var(--cs-text);
    margin: 0 0 8px;
  }

  .cs-empty-desc {
    font-size: 15px;
    color: var(--cs-text-muted);
    max-width: 320px;
  }

  /* Dashboard View */
  .cs-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
  }

  .cs-stat-card {
    background: var(--cs-surface);
    border: 1px solid var(--cs-border-subtle);
    border-radius: var(--cs-radius);
    padding: 24px;
    transition: all 0.2s ease;
  }

  .cs-stat-card:hover {
    border-color: var(--cs-border);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  }

  .cs-stat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
  }

  .cs-stat-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cs-primary-muted);
    border-radius: var(--cs-radius-sm);
    color: var(--cs-primary);
  }

  .cs-stat-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--cs-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .cs-stat-value {
    font-family: var(--cs-font-display);
    font-size: 36px;
    font-weight: 400;
    color: var(--cs-text);
    margin-bottom: 4px;
  }

  .cs-stat-change {
    font-size: 13px;
    color: var(--cs-accent);
  }

  /* Animations */
  .cs-fade-in {
    animation: cs-fade-in 0.4s ease;
  }

  @keyframes cs-fade-in {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Responsive */
  @media (max-width: 768px) {
    .cs-sidebar {
      position: fixed;
      left: calc(-1 * var(--canvas-sidebar-width, 280px));
      height: 100%;
      z-index: 100;
      transition: left 0.3s ease;
    }

    .cs-sidebar.open {
      left: 0;
    }

    .cs-content {
      padding: 24px 16px;
    }

    .cs-search-title {
      font-size: 32px;
    }
  }
`;

// ============================================================================
// Icons
// ============================================================================

const Icons = {
  search: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="11" cy="11" r="8" />
      <path d="m21 21-4.3-4.3" />
    </svg>
  ),
  message: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
    </svg>
  ),
  grid: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <rect width="7" height="7" x="3" y="3" rx="1" />
      <rect width="7" height="7" x="14" y="3" rx="1" />
      <rect width="7" height="7" x="14" y="14" rx="1" />
      <rect width="7" height="7" x="3" y="14" rx="1" />
    </svg>
  ),
  settings: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
      <circle cx="12" cy="12" r="3" />
    </svg>
  ),
  sparkles: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z" />
    </svg>
  ),
  send: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m22 2-7 20-4-9-9-4Z" />
      <path d="M22 2 11 13" />
    </svg>
  ),
  database: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <ellipse cx="12" cy="5" rx="9" ry="3" />
      <path d="M3 5V19A9 3 0 0 0 21 19V5" />
      <path d="M3 12A9 3 0 0 0 21 12" />
    </svg>
  ),
  file: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
      <path d="M14 2v4a2 2 0 0 0 2 2h4" />
    </svg>
  ),
  user: (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="8" r="5" />
      <path d="M20 21a8 8 0 0 0-16 0" />
    </svg>
  ),
  bot: (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12 8V4H8" />
      <rect width="16" height="12" x="4" y="8" rx="2" />
      <path d="M2 14h2" />
      <path d="M20 14h2" />
      <path d="M15 13v2" />
      <path d="M9 13v2" />
    </svg>
  ),
};

// ============================================================================
// Main Component
// ============================================================================

type ChatSource = { title: string; uri: string; score: number };
type ChatMessage = { role: 'user' | 'assistant'; content: string; sources?: ChatSource[] };

export function CanvasShell({
  children: _children,
  activeView = 'search',
  onViewChange,
  settings,
  config,
  onNavigate,
  apiBasePath = '/api/canvas',
  texts = {},
  slots = {},
}: CanvasShellProps) {
  // Use i18n hook for text, with fallback to props
  const text = useText();
  const t = {
    searchTitle: texts.searchTitle ?? text('search.title'),
    searchSubtitle: texts.searchSubtitle ?? text('search.subtitle'),
    searchPlaceholder: texts.searchPlaceholder ?? text('search.placeholder'),
    chatEmptyTitle: texts.chatEmptyTitle ?? text('chat.empty.title'),
    chatEmptyDescription: texts.chatEmptyDescription ?? text('chat.empty.description'),
    chatPlaceholder: texts.chatPlaceholder ?? text('chat.input.placeholder'),
  };

  // Helper to render a slot (uses any to support various slot prop types)
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const renderSlot = (
    slotValue: ReactNode | ComponentType<any> | null | undefined,
    props: Record<string, unknown>,
    fallback: ReactNode
  ): ReactNode => {
    if (slotValue === null) return null;
    if (slotValue === undefined) return fallback;
    if (isSlotComponent(slotValue)) {
      const SlotComponent = slotValue as ComponentType<any>;
      return <SlotComponent {...props} />;
    }
    return slotValue;
  };
  // _children reserved for future use (e.g., custom content slots)
  void _children;
  const [view, setView] = useState<'search' | 'chat' | 'dashboard'>(activeView);
  const [searchQuery, setSearchQuery] = useState('');
  const [searchMode, setSearchMode] = useState<'semantic' | 'lexical' | 'hybrid'>('hybrid');
  const [searchResults, setSearchResults] = useState<any[]>([]);
  const [isSearching, setIsSearching] = useState(false);
  const [chatMessages, setChatMessages] = useState<ChatMessage[]>([]);
  const [chatInput, setChatInput] = useState('');
  const [isChatting, setIsChatting] = useState(false);
  const [stats, setStats] = useState<any>(null);
  const [selectedResult, setSelectedResult] = useState<any | null>(null);

  const closeSelectedResult = useCallback(() => setSelectedResult(null), []);

  const openSearchResult = useCallback((result: any) => {
    const uri = result?.uri || result?.metadata?.uri;
    if (uri && typeof window !== 'undefined') {
      const url = `${apiBasePath}/asset?uri=${encodeURIComponent(uri)}`;
      window.open(url, '_blank', 'noopener,noreferrer');
      return;
    }
    setSelectedResult(result);
  }, [apiBasePath]);

  // Get enabled features from config
  // If features or feature.enabled is explicitly false, disable it
  // Otherwise default to enabled
  const isFeatureEnabled = (feature: { enabled?: boolean } | undefined): boolean => {
    if (feature === undefined) return true; // No config = enabled by default
    if (feature.enabled === undefined) return true; // No enabled prop = enabled by default
    return feature.enabled; // Use explicit value
  };

  const features = {
    search: { enabled: isFeatureEnabled(config.features?.search) },
    chat: { enabled: isFeatureEnabled(config.features?.chat) },
    dashboard: { enabled: isFeatureEnabled(config.features?.dashboard) },
    pdfViewer: { enabled: isFeatureEnabled(config.features?.pdfViewer) },
  };

  // Debug log to help trace issues
  useEffect(() => {
    console.log('[CanvasShell] Config features:', JSON.stringify(config.features, null, 2));
    console.log('[CanvasShell] Resolved features:', features);
  }, [config.features]);

  // Set initial view to first enabled feature
  useEffect(() => {
    const enabledViews: ('search' | 'chat' | 'dashboard')[] = [];
    if (features.search.enabled) enabledViews.push('search');
    if (features.chat.enabled) enabledViews.push('chat');
    if (features.dashboard.enabled) enabledViews.push('dashboard');

    // If current view is disabled, switch to first enabled
    const firstEnabled = enabledViews[0];
    if (!enabledViews.includes(view) && firstEnabled) {
      setView(firstEnabled);
    }
  }, [config.features]);

  const handleViewChange = (newView: 'search' | 'chat' | 'dashboard') => {
    setView(newView);
    onViewChange?.(newView);
  };

  // Search handler
  const handleSearch = useCallback(async () => {
    if (!searchQuery.trim()) return;

    setIsSearching(true);
    try {
      const response = await fetch(`${apiBasePath}/search`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          query: searchQuery,
          mode: searchMode,
          limit: 20,
        }),
      });

      const data = await response.json();
      setSearchResults(data.results || []);
    } catch (error) {
      console.error('Search error:', error);
    }
    setIsSearching(false);
  }, [searchQuery, searchMode, apiBasePath]);

  // Chat handler
  const handleChat = useCallback(async () => {
    if (!chatInput.trim()) return;

    const userMessage = chatInput;
    setChatInput('');
    setChatMessages(prev => [...prev, { role: 'user', content: userMessage }]);
    setIsChatting(true);

    try {
      const response = await fetch(`${apiBasePath}/chat`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          content: userMessage,
          conversationId: 'default',
          llmProvider: settings.llmProvider,
          llmModel: settings.llmModel,
          llmApiKey: settings.llmApiKey,
        }),
      });

      const data = await response.json().catch(() => ({}));
      // Server returns { message: { content: string, role: string }, sources: [] }
      const messageContent = data.message?.content || data.message || data.response || data.error || 'Sorry, I could not process your request.';
      setChatMessages(prev => [...prev, { role: 'assistant', content: messageContent, sources: data.sources || [] }]);
    } catch (error) {
      console.error('Chat error:', error);
      setChatMessages(prev => [...prev, { role: 'assistant', content: 'Sorry, an error occurred. Please try again.' }]);
    }
    setIsChatting(false);
  }, [chatInput, settings.llmApiKey, settings.llmModel, settings.llmProvider, apiBasePath]);

  // Load stats for dashboard
  useEffect(() => {
    if (view === 'dashboard') {
      fetch(`${apiBasePath}/stats`)
        .then(res => res.json())
        .then(data => setStats(data))
        .catch(console.error);
    }
  }, [view, apiBasePath]);

  // Get app name from config
  const appName = config.app?.name || 'Canvas';

  return (
    <>
      <style dangerouslySetInnerHTML={{ __html: styles }} />
      <div className="canvas-shell">
        <div className="cs-background">
          <div className="cs-grid" />
        </div>

        {/* Sidebar - supports slots.sidebar for full replacement */}
        {slots.sidebar !== null && (
          slots.sidebar !== undefined ? (
            renderSlot(slots.sidebar, { isCollapsed: false }, null)
          ) : (
            <aside className="cs-sidebar">
              {/* Sidebar Header - supports slots.sidebarHeader and slots.logo */}
              <div className="cs-sidebar-header">
                {renderSlot(
                  slots.sidebarHeader,
                  { isCollapsed: false },
                  <div className="cs-logo">
                    {renderSlot(
                      slots.logo,
                      { brandName: appName, size: 40 },
                      <BrandLogo size={40} />
                    )}
                  </div>
                )}
              </div>

              <nav className="cs-nav">
                <div className="cs-nav-section">
                  <div className="cs-nav-label">{text('nav.main')}</div>

                  {features.search.enabled && (
                    <div
                      className={`cs-nav-item ${view === 'search' ? 'active' : ''}`}
                      onClick={() => handleViewChange('search')}
                    >
                      <span className="cs-nav-icon">{Icons.search}</span>
                      {text('nav.search')}
                    </div>
                  )}

                  {features.chat.enabled && (
                    <div
                      className={`cs-nav-item ${view === 'chat' ? 'active' : ''}`}
                      onClick={() => handleViewChange('chat')}
                    >
                      <span className="cs-nav-icon">{Icons.message}</span>
                      {text('nav.chat')}
                    </div>
                  )}

                  {features.dashboard.enabled && (
                    <div
                      className={`cs-nav-item ${view === 'dashboard' ? 'active' : ''}`}
                      onClick={() => handleViewChange('dashboard')}
                    >
                      <span className="cs-nav-icon">{Icons.grid}</span>
                      {text('nav.dashboard')}
                    </div>
                  )}
                </div>
              </nav>

              {/* Sidebar Footer - supports slots.sidebarFooter */}
              {slots.sidebarFooter !== null && (
                <div className="cs-sidebar-footer">
                  {renderSlot(
                    slots.sidebarFooter,
                    { isCollapsed: false },
                    <button className="cs-settings-btn" onClick={() => onNavigate?.('/settings')}>
                      {Icons.settings}
                      {text('nav.settings')}
                    </button>
                  )}
                </div>
              )}
            </aside>
          )
        )}

        {/* Main Content */}
        <main className="cs-main">
          {/* Before Content Slot */}
          {slots.beforeContent && renderSlot(slots.beforeContent, {}, null)}

          {/* Search View - supports slots.searchView for full replacement */}
          {view === 'search' && (
            slots.searchView !== undefined ? (
              renderSlot(slots.searchView, {}, null)
            ) : (
            <div className="cs-content cs-fade-in">
              <div className="cs-search-container">
                {/* Search Header - supports slots.searchHeader */}
                {renderSlot(
                  slots.searchHeader,
                  { title: t.searchTitle, subtitle: t.searchSubtitle },
                  <div className="cs-search-hero">
                    <h1 className="cs-search-title">{t.searchTitle}</h1>
                    <p className="cs-search-subtitle">
                      {t.searchSubtitle}
                    </p>
                  </div>
                )}

                <div className="cs-search-box">
                  <span className="cs-search-icon">{Icons.search}</span>
                  <input
                    type="text"
                    className="cs-search-input"
                    placeholder={t.searchPlaceholder}
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                  />
                </div>

                <div className="cs-search-modes">
                  {(['semantic', 'lexical', 'hybrid'] as const).map((mode) => (
                    <button
                      key={mode}
                      className={`cs-mode-btn ${searchMode === mode ? 'active' : ''}`}
                      onClick={() => setSearchMode(mode)}
                    >
                      {mode.charAt(0).toUpperCase() + mode.slice(1)}
                    </button>
                  ))}
                </div>

                {searchResults.length > 0 && (
                  <div className="cs-results">
                    {searchResults.map((result, i) => (
                      <div
                        key={i}
                        className="cs-result-card"
                        role="button"
                        tabIndex={0}
                        onClick={() => openSearchResult(result)}
                        onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && openSearchResult(result)}
                      >
                        <div className="cs-result-header">
                          <h3 className="cs-result-title">
                            {result.metadata?.filename || result.metadata?.title || result.title || `Result ${i + 1}`}
                          </h3>
                          <span className="cs-result-score">
                            {(result.score * 100).toFixed(0)}%
                          </span>
                        </div>
                        <p className="cs-result-content">
                          {(() => {
                            const preview = String(result.content ?? result.text ?? '');
                            if (!preview) return 'No preview available.';
                            return preview.length > 220 ? `${preview.slice(0, 220)}...` : preview;
                          })()}
                        </p>
                        <div className="cs-result-meta">
                          <span className="cs-result-meta-item">
                            {Icons.file}
                            {result.metadata?.mime || result.metadata?.type || 'Document'}
                          </span>
                          {result.metadata?.page && (
                            <span className="cs-result-meta-item">
                              Page {result.metadata.page}
                            </span>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                )}

                {searchQuery && searchResults.length === 0 && !isSearching && (
                  <div className="cs-empty">
                    <div className="cs-empty-icon">{Icons.search}</div>
                    <h3 className="cs-empty-title">No results found</h3>
                    <p className="cs-empty-desc">
                      Try adjusting your search query or try a different search mode.
                    </p>
                  </div>
                )}
              </div>
            </div>
            )
          )}

          {/* Chat View - supports slots.chatView for full replacement */}
          {view === 'chat' && (
            slots.chatView !== undefined ? (
              renderSlot(slots.chatView, {}, null)
            ) : (
            <div className="cs-content cs-fade-in">
              <div className="cs-chat-container">
                <div className="cs-chat-messages">
                  {chatMessages.length === 0 ? (
                    /* Chat Empty State - supports slots.chatEmpty */
                    renderSlot(
                      slots.chatEmpty,
                      { view: 'chat' },
                      <div className="cs-empty">
                        <div className="cs-empty-icon">{Icons.message}</div>
                        <h3 className="cs-empty-title">{t.chatEmptyTitle}</h3>
                        <p className="cs-empty-desc">
                          {t.chatEmptyDescription}
                        </p>
                      </div>
                    )
                  ) : (
                    <>
                      {chatMessages.map((msg, i) => (
                        <div key={i} className={`cs-message ${msg.role}`}>
                          <div className="cs-message-avatar">
                            {msg.role === 'user' ? Icons.user : Icons.bot}
                          </div>
                          <div className="cs-message-content">
                            {msg.role === 'assistant' ? (
                              <>
                                <MarkdownLite text={msg.content} />
                                {msg.sources && msg.sources.length > 0 && (
                                  <div className="cs-sources">
                                    {msg.sources.slice(0, 3).map((s, idx) => {
                                      // Handle both flattened and nested metadata structures
                                      const meta = (s as Record<string, unknown>).metadata as Record<string, string> | undefined;
                                      const title = s.title || meta?.title || `Source ${idx + 1}`;
                                      const uri = s.uri || meta?.uri;
                                      const isMv2 = typeof uri === 'string' && uri.startsWith('mv2://');
                                      const href = isMv2 ? `${apiBasePath}/asset?uri=${encodeURIComponent(uri)}` : undefined;
                                      return (
                                        <div className="cs-source" key={idx}>
                                          {href ? (
                                            <a href={href} target="_blank" rel="noreferrer noopener">
                                              {title}
                                            </a>
                                          ) : (
                                            <span>{title}</span>
                                          )}
                                          <span className="cs-source-score">{Math.round((s.score ?? 0) * 100)}%</span>
                                        </div>
                                      );
                                    })}
                                  </div>
                                )}
                              </>
                            ) : (
                              msg.content
                            )}
                          </div>
                        </div>
                      ))}
                      {isChatting && (
                        <div className="cs-message assistant">
                          <div className="cs-message-avatar">{Icons.bot}</div>
                          <div className="cs-message-content">
                            <div className="cs-typing" aria-label="Generating response">
                              <span className="cs-typing-dot" />
                              <span className="cs-typing-dot" />
                              <span className="cs-typing-dot" />
                            </div>
                          </div>
                        </div>
                      )}
                    </>
                  )}
                </div>

                <div className="cs-chat-input-container">
                  <div className="cs-chat-input-box">
                    <input
                      type="text"
                      className="cs-chat-input"
                      placeholder={t.chatPlaceholder}
                      value={chatInput}
                      onChange={(e) => setChatInput(e.target.value)}
                      onKeyDown={(e) => e.key === 'Enter' && !e.shiftKey && handleChat()}
                      disabled={isChatting}
                    />
                    <button
                      className="cs-chat-send"
                      onClick={handleChat}
                      disabled={!chatInput.trim() || isChatting}
                    >
                      {Icons.send}
                    </button>
                  </div>
                </div>
              </div>
            </div>
            )
          )}

          {/* Dashboard View - supports slots.dashboardView for full replacement */}
          {view === 'dashboard' && (
            slots.dashboardView !== undefined ? (
              renderSlot(slots.dashboardView, {}, null)
            ) : (
            <div className="cs-content cs-fade-in">
              {/* Dashboard Header - supports slots.dashboardHeader */}
              {renderSlot(
                slots.dashboardHeader,
                { title: 'Dashboard', subtitle: 'Overview of your memory and usage statistics' },
                <div className="cs-header" style={{ margin: '-32px -32px 32px', padding: '24px 32px' }}>
                  <div>
                    <h1 className="cs-header-title">Dashboard</h1>
                    <p className="cs-header-subtitle">Overview of your memory and usage statistics</p>
                  </div>
                </div>
              )}

              <div className="cs-dashboard">
                <div className="cs-stat-card">
                  <div className="cs-stat-header">
                    <div className="cs-stat-icon">{Icons.database}</div>
                    <span className="cs-stat-label">Total Frames</span>
                  </div>
                  <div className="cs-stat-value">{stats?.totalFrames || '-'}</div>
                  <div className="cs-stat-change">In memory</div>
                </div>

                <div className="cs-stat-card">
                  <div className="cs-stat-header">
                    <div className="cs-stat-icon">{Icons.file}</div>
                    <span className="cs-stat-label">Documents</span>
                  </div>
                  <div className="cs-stat-value">{stats?.documentCount || '-'}</div>
                  <div className="cs-stat-change">Indexed</div>
                </div>

                <div className="cs-stat-card">
                  <div className="cs-stat-header">
                    <div className="cs-stat-icon">{Icons.message}</div>
                    <span className="cs-stat-label">Conversations</span>
                  </div>
                  <div className="cs-stat-value">{stats?.conversationCount || '-'}</div>
                  <div className="cs-stat-change">Stored</div>
                </div>

                <div className="cs-stat-card">
                  <div className="cs-stat-header">
                    <div className="cs-stat-icon">{Icons.sparkles}</div>
                    <span className="cs-stat-label">Memory Size</span>
                  </div>
                  <div className="cs-stat-value">{stats?.size || '-'}</div>
                  <div className="cs-stat-change">Total size</div>
                </div>
              </div>
            </div>
            )
          )}

          {/* After Content Slot */}
          {slots.afterContent && renderSlot(slots.afterContent, {}, null)}

          {/* Result Modal */}
          {selectedResult && (
            <div className="cs-modal" role="dialog" aria-modal="true" onClick={closeSelectedResult}>
              <div className="cs-modal-panel" onClick={(e) => e.stopPropagation()}>
                <div className="cs-modal-header">
                  <div className="cs-modal-title">
                    {selectedResult?.metadata?.filename || selectedResult?.metadata?.title || selectedResult?.title || 'Result'}
                  </div>
                  <button className="cs-modal-close" onClick={closeSelectedResult} aria-label="Close">
                    ×
                  </button>
                </div>
                <div className="cs-modal-body">
                  <MarkdownLite text={String(selectedResult?.content ?? selectedResult?.text ?? '')} />
                </div>
              </div>
            </div>
          )}
        </main>
      </div>
    </>
  );
}

export default CanvasShell;
