'use client';

/**
 * Canvas Setup Wizard
 *
 * A beautiful multi-step onboarding experience for configuring Canvas.
 * Designed with an editorial software aesthetic - clean, spacious, refined.
 */

import React, { useState, useCallback, useEffect } from 'react';
import type { SetupStep, CanvasUIConfig, RuntimeSettings } from '@memvid/canvas-core/types-only';

// ============================================================================
// Types
// ============================================================================

export interface SetupWizardProps {
  onComplete: (config: Partial<CanvasUIConfig>, settings: RuntimeSettings) => void;
  onSkip?: () => void;
  initialConfig?: Partial<CanvasUIConfig>;
  /** If true, show "Reconfigure" instead of "Setup" messaging */
  isReconfigure?: boolean;
  /** API endpoint for creating memory files */
  createMemoryEndpoint?: string;
}

interface StepProps {
  onNext: () => void;
  onBack?: () => void;
  config: Partial<CanvasUIConfig>;
  settings: RuntimeSettings;
  updateConfig: (updates: Partial<CanvasUIConfig>) => void;
  updateSettings: (updates: Partial<RuntimeSettings>) => void;
  isReconfigure?: boolean;
  createMemoryEndpoint?: string;
}

// ============================================================================
// Styles (CSS-in-JS for portability)
// ============================================================================

const styles = `
  @import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

  .setup-wizard {
    --sw-bg: #09090b;
    --sw-surface: #18181b;
    --sw-surface-elevated: #27272a;
    --sw-border: #3f3f46;
    --sw-border-subtle: #27272a;
    --sw-text: #fafafa;
    --sw-text-muted: #a1a1aa;
    --sw-text-subtle: #71717a;
    --sw-primary: #818cf8;
    --sw-primary-hover: #a5b4fc;
    --sw-primary-muted: rgba(129, 140, 248, 0.1);
    --sw-accent: #34d399;
    --sw-error: #f87171;
    --sw-success: #4ade80;
    --sw-radius: 12px;
    --sw-radius-sm: 8px;
    --sw-radius-lg: 16px;
    --sw-font-display: 'Instrument Serif', Georgia, serif;
    --sw-font-body: 'Outfit', system-ui, sans-serif;
    --sw-font-mono: 'JetBrains Mono', monospace;

    position: fixed;
    inset: 0;
    background: var(--sw-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--sw-font-body);
    color: var(--sw-text);
    overflow: hidden;
  }

  .setup-wizard * {
    box-sizing: border-box;
  }

  .sw-background {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
  }

  .sw-background::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background:
      radial-gradient(ellipse at 20% 20%, rgba(129, 140, 248, 0.08) 0%, transparent 50%),
      radial-gradient(ellipse at 80% 80%, rgba(52, 211, 153, 0.05) 0%, transparent 50%);
    animation: sw-bg-drift 30s ease-in-out infinite;
  }

  @keyframes sw-bg-drift {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(-2%, 2%) rotate(1deg); }
  }

  .sw-grid {
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(var(--sw-border-subtle) 1px, transparent 1px),
      linear-gradient(90deg, var(--sw-border-subtle) 1px, transparent 1px);
    background-size: 60px 60px;
    opacity: 0.3;
    mask-image: radial-gradient(ellipse at center, black 0%, transparent 70%);
  }

  .sw-container {
    position: relative;
    width: 100%;
    max-width: 720px;
    padding: 24px;
    z-index: 1;
  }

  .sw-card {
    background: var(--sw-surface);
    border: 1px solid var(--sw-border-subtle);
    border-radius: var(--sw-radius-lg);
    box-shadow:
      0 0 0 1px rgba(255, 255, 255, 0.02),
      0 20px 50px -12px rgba(0, 0, 0, 0.5);
    overflow: hidden;
  }

  .sw-progress {
    height: 3px;
    background: var(--sw-border-subtle);
    position: relative;
    overflow: hidden;
  }

  .sw-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--sw-primary), var(--sw-accent));
    transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .sw-header {
    padding: 48px 48px 32px;
    text-align: center;
  }

  .sw-step-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: var(--sw-primary-muted);
    border: 1px solid rgba(129, 140, 248, 0.2);
    border-radius: 100px;
    font-size: 12px;
    font-weight: 500;
    color: var(--sw-primary);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 24px;
  }

  .sw-title {
    font-family: var(--sw-font-display);
    font-size: 36px;
    font-weight: 400;
    line-height: 1.2;
    margin: 0 0 12px;
    background: linear-gradient(135deg, var(--sw-text) 0%, var(--sw-text-muted) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .sw-subtitle {
    font-size: 16px;
    color: var(--sw-text-muted);
    line-height: 1.6;
    max-width: 480px;
    margin: 0 auto;
  }

  .sw-content {
    padding: 0 48px 48px;
  }

  .sw-field {
    margin-bottom: 24px;
  }

  .sw-label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--sw-text);
    margin-bottom: 8px;
    letter-spacing: 0.3px;
  }

  .sw-label-hint {
    font-weight: 400;
    color: var(--sw-text-subtle);
    margin-left: 8px;
  }

  .sw-input {
    width: 100%;
    padding: 14px 16px;
    background: var(--sw-bg);
    border: 1px solid var(--sw-border-subtle);
    border-radius: var(--sw-radius-sm);
    font-family: var(--sw-font-body);
    font-size: 15px;
    color: var(--sw-text);
    transition: all 0.2s ease;
  }

  .sw-input:focus {
    outline: none;
    border-color: var(--sw-primary);
    box-shadow: 0 0 0 3px var(--sw-primary-muted);
  }

  .sw-input::placeholder {
    color: var(--sw-text-subtle);
  }

  .sw-input-mono {
    font-family: var(--sw-font-mono);
    font-size: 14px;
  }

  .sw-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23a1a1aa' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 44px;
  }

  .sw-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .sw-option {
    position: relative;
    padding: 20px;
    background: var(--sw-bg);
    border: 2px solid var(--sw-border-subtle);
    border-radius: var(--sw-radius);
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .sw-option:hover {
    border-color: var(--sw-border);
    background: var(--sw-surface-elevated);
  }

  .sw-option.selected {
    border-color: var(--sw-primary);
    background: var(--sw-primary-muted);
  }

  .sw-option-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--sw-surface-elevated);
    border-radius: var(--sw-radius-sm);
    margin-bottom: 12px;
    color: var(--sw-text-muted);
  }

  .sw-option.selected .sw-option-icon {
    background: rgba(129, 140, 248, 0.2);
    color: var(--sw-primary);
  }

  .sw-option-title {
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 4px;
  }

  .sw-option-desc {
    font-size: 13px;
    color: var(--sw-text-muted);
    line-height: 1.5;
  }

  .sw-option-check {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid var(--sw-border);
    transition: all 0.2s ease;
  }

  .sw-option.selected .sw-option-check {
    background: var(--sw-primary);
    border-color: var(--sw-primary);
  }

  .sw-option.selected .sw-option-check::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 6px;
    height: 6px;
    background: white;
    border-radius: 50%;
    transform: translate(-50%, -50%);
  }

  .sw-color-grid {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .sw-color-swatch {
    width: 40px;
    height: 40px;
    border-radius: var(--sw-radius-sm);
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
  }

  .sw-color-swatch:hover {
    transform: scale(1.1);
  }

  .sw-color-swatch.selected {
    border-color: white;
    box-shadow: 0 0 0 2px var(--sw-bg), 0 0 0 4px var(--sw-primary);
  }

  .sw-toggle-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .sw-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: var(--sw-bg);
    border: 1px solid var(--sw-border-subtle);
    border-radius: var(--sw-radius);
  }

  .sw-toggle-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .sw-toggle-label {
    font-size: 14px;
    font-weight: 500;
  }

  .sw-toggle-desc {
    font-size: 12px;
    color: var(--sw-text-subtle);
  }

  .sw-toggle-switch {
    position: relative;
    width: 48px;
    height: 28px;
    background: var(--sw-surface-elevated);
    border-radius: 100px;
    cursor: pointer;
    transition: background 0.2s ease;
  }

  .sw-toggle-switch.active {
    background: var(--sw-primary);
  }

  .sw-toggle-switch::after {
    content: '';
    position: absolute;
    top: 4px;
    left: 4px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s ease;
  }

  .sw-toggle-switch.active::after {
    transform: translateX(20px);
  }

  .sw-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 48px;
    border-top: 1px solid var(--sw-border-subtle);
    background: rgba(0, 0, 0, 0.2);
  }

  .sw-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    font-family: var(--sw-font-body);
    font-size: 14px;
    font-weight: 500;
    border-radius: var(--sw-radius-sm);
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .sw-btn-primary {
    background: var(--sw-primary);
    color: white;
  }

  .sw-btn-primary:hover {
    background: var(--sw-primary-hover);
    transform: translateY(-1px);
  }

  .sw-btn-secondary {
    background: transparent;
    color: var(--sw-text-muted);
    border: 1px solid var(--sw-border);
  }

  .sw-btn-secondary:hover {
    background: var(--sw-surface-elevated);
    color: var(--sw-text);
  }

  .sw-btn-ghost {
    background: transparent;
    color: var(--sw-text-subtle);
  }

  .sw-btn-ghost:hover {
    color: var(--sw-text);
  }

  .sw-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .sw-complete {
    text-align: center;
    padding: 32px 0;
  }

  .sw-complete-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(52, 211, 153, 0.1);
    border: 2px solid rgba(52, 211, 153, 0.2);
    border-radius: 50%;
    color: var(--sw-accent);
  }

  .sw-complete-title {
    font-family: var(--sw-font-display);
    font-size: 32px;
    margin-bottom: 12px;
  }

  .sw-complete-desc {
    color: var(--sw-text-muted);
    margin-bottom: 32px;
  }

  .sw-config-preview {
    background: var(--sw-bg);
    border: 1px solid var(--sw-border-subtle);
    border-radius: var(--sw-radius);
    padding: 20px;
    margin-bottom: 24px;
    text-align: left;
    font-family: var(--sw-font-mono);
    font-size: 12px;
    max-height: 200px;
    overflow: auto;
  }

  .sw-fade-in {
    animation: sw-fade-in 0.4s ease;
  }

  @keyframes sw-fade-in {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @media (max-width: 640px) {
    .sw-container {
      padding: 16px;
    }

    .sw-header,
    .sw-content,
    .sw-footer {
      padding-left: 24px;
      padding-right: 24px;
    }

    .sw-title {
      font-size: 28px;
    }

    .sw-options {
      grid-template-columns: 1fr;
    }
  }
`;

// ============================================================================
// Icons
// ============================================================================

const Icons = {
  database: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <ellipse cx="12" cy="5" rx="9" ry="3" />
      <path d="M3 5V19A9 3 0 0 0 21 19V5" />
      <path d="M3 12A9 3 0 0 0 21 12" />
    </svg>
  ),
  key: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4" />
    </svg>
  ),
  palette: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="13.5" cy="6.5" r=".5" fill="currentColor" />
      <circle cx="17.5" cy="10.5" r=".5" fill="currentColor" />
      <circle cx="8.5" cy="7.5" r=".5" fill="currentColor" />
      <circle cx="6.5" cy="12.5" r=".5" fill="currentColor" />
      <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.555C21.965 6.012 17.461 2 12 2z" />
    </svg>
  ),
  settings: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
      <circle cx="12" cy="12" r="3" />
    </svg>
  ),
  checkCircle: (
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="12" r="10" />
      <path d="m9 12 2 2 4-4" />
    </svg>
  ),
  arrowRight: (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 12h14" />
      <path d="m12 5 7 7-7 7" />
    </svg>
  ),
  arrowLeft: (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m12 19-7-7 7-7" />
      <path d="M19 12H5" />
    </svg>
  ),
  sparkles: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z" />
    </svg>
  ),
  anthropic: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
      <path d="M13.827 3.52h3.603L24 20.48h-3.603l-6.57-16.96zm-7.258 0h3.767L16.906 20.48h-3.674l-1.343-3.461H5.017l-1.344 3.46H0L6.57 3.522zm3.63 10.508L7.903 7.677l-2.295 6.35h4.59z" />
    </svg>
  ),
  openai: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
      <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08-4.778 2.758a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z" />
    </svg>
  ),
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
};

// ============================================================================
// Step Components
// ============================================================================

const WelcomeStep: React.FC<StepProps> = ({ onNext, isReconfigure }) => (
  <div className="sw-fade-in">
    <div className="sw-header">
      <div className="sw-step-indicator">
        {Icons.sparkles}
        <span>{isReconfigure ? 'Reconfigure' : 'Setup Wizard'}</span>
      </div>
      <h1 className="sw-title">{isReconfigure ? 'Reconfigure Canvas' : 'Welcome to Canvas'}</h1>
      <p className="sw-subtitle">
        {isReconfigure
          ? 'Update your settings and preferences. Changes will take effect immediately.'
          : "Let's configure your AI-powered knowledge base in just a few steps. You'll connect your memory, add API keys, and customize your brand."}
      </p>
    </div>
    <div className="sw-content">
      <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
        {[
          { icon: Icons.database, title: 'Connect Memory', desc: 'Link your .mv2 file or create a new one' },
          { icon: Icons.key, title: 'Add API Keys', desc: 'Configure your LLM provider' },
          { icon: Icons.palette, title: 'Brand Your App', desc: 'Customize colors, name, and logo' },
          { icon: Icons.settings, title: 'Enable Features', desc: 'Choose search, chat, and more' },
        ].map((item, i) => (
          <div key={i} style={{ display: 'flex', gap: '16px', alignItems: 'center', padding: '16px', background: 'var(--sw-bg)', borderRadius: 'var(--sw-radius)', border: '1px solid var(--sw-border-subtle)' }}>
            <div style={{ width: '44px', height: '44px', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'var(--sw-surface-elevated)', borderRadius: 'var(--sw-radius-sm)', color: 'var(--sw-primary)' }}>
              {item.icon}
            </div>
            <div>
              <div style={{ fontWeight: 500, marginBottom: '2px' }}>{item.title}</div>
              <div style={{ fontSize: '13px', color: 'var(--sw-text-muted)' }}>{item.desc}</div>
            </div>
          </div>
        ))}
      </div>
    </div>
    <div className="sw-footer">
      <div></div>
      <button className="sw-btn sw-btn-primary" onClick={onNext}>
        Get Started {Icons.arrowRight}
      </button>
    </div>
  </div>
);

const MemoryStep: React.FC<StepProps> = ({ onNext, onBack, config, updateConfig, createMemoryEndpoint = '/api/canvas/create-memory' }) => {
  const [memoryPath, setMemoryPath] = useState('./data/memory.mv2');
  const [createNew, setCreateNew] = useState(true);
  const [isCreating, setIsCreating] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleNext = async () => {
    setError(null);

    if (createNew) {
      setIsCreating(true);
      try {
        const response = await fetch(createMemoryEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ memoryPath }),
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.error || 'Failed to create memory file');
        }
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to create memory file');
        setIsCreating(false);
        return;
      }
      setIsCreating(false);
    }

    updateConfig({
      ...config,
    });
    onNext();
  };

  return (
    <div className="sw-fade-in">
      <div className="sw-header">
        <div className="sw-step-indicator">Step 1 of 4</div>
        <h1 className="sw-title">Connect Memory</h1>
        <p className="sw-subtitle">
          Your memory file stores all your documents, conversations, and knowledge.
        </p>
      </div>
      <div className="sw-content">
        <div className="sw-options" style={{ marginBottom: '24px' }}>
          <div className={`sw-option ${createNew ? 'selected' : ''}`} onClick={() => setCreateNew(true)}>
            <div className="sw-option-check" />
            <div className="sw-option-icon">{Icons.sparkles}</div>
            <div className="sw-option-title">Create New</div>
            <div className="sw-option-desc">Start fresh with a new memory file</div>
          </div>
          <div className={`sw-option ${!createNew ? 'selected' : ''}`} onClick={() => setCreateNew(false)}>
            <div className="sw-option-check" />
            <div className="sw-option-icon">{Icons.database}</div>
            <div className="sw-option-title">Use Existing</div>
            <div className="sw-option-desc">Connect an existing .mv2 file</div>
          </div>
        </div>

        <div className="sw-field">
          <label className="sw-label">
            Memory File Path
            <span className="sw-label-hint">relative to project root</span>
          </label>
          <input
            type="text"
            className="sw-input sw-input-mono"
            value={memoryPath}
            onChange={(e) => setMemoryPath(e.target.value)}
            placeholder="./data/memory.mv2"
          />
        </div>

        {error && (
          <div style={{ padding: '12px 16px', background: 'rgba(248, 113, 113, 0.1)', border: '1px solid rgba(248, 113, 113, 0.3)', borderRadius: 'var(--sw-radius-sm)', color: '#f87171', fontSize: '13px', marginTop: '16px' }}>
            {error}
          </div>
        )}
      </div>
      <div className="sw-footer">
        <button className="sw-btn sw-btn-ghost" onClick={onBack} disabled={isCreating}>
          {Icons.arrowLeft} Back
        </button>
        <button className="sw-btn sw-btn-primary" onClick={handleNext} disabled={isCreating}>
          {isCreating ? 'Creating memory...' : <>Continue {Icons.arrowRight}</>}
        </button>
      </div>
    </div>
  );
};

const LLMStep: React.FC<StepProps> = ({ onNext, onBack, settings, updateSettings }) => {
  const [provider, setProvider] = useState<'anthropic' | 'openai'>(settings.llmProvider || 'openai');
  const [apiKey, setApiKey] = useState(settings.llmApiKey || '');
  const [model, setModel] = useState(settings.llmModel || '');

  const models = {
    anthropic: [
      { id: 'claude-sonnet-4-20250514', name: 'Claude Sonnet 4' },
      { id: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
      { id: 'claude-3-haiku-20240307', name: 'Claude 3 Haiku (Fast)' },
    ],
    openai: [
      { id: 'gpt-4o', name: 'GPT-4o' },
      { id: 'gpt-4o-mini', name: 'GPT-4o Mini (Fast)' },
      { id: 'gpt-4-turbo', name: 'GPT-4 Turbo' },
    ],
  };

  useEffect(() => {
    const providerModels = models[provider];
    const firstModel = providerModels?.[0];
    if (providerModels && firstModel && (!model || !providerModels.find(m => m.id === model))) {
      setModel(firstModel.id);
    }
  }, [provider, model]);

  const handleNext = () => {
    updateSettings({
      llmProvider: provider,
      llmModel: model,
      llmApiKey: apiKey,
    });
    onNext();
  };

  return (
    <div className="sw-fade-in">
      <div className="sw-header">
        <div className="sw-step-indicator">Step 2 of 4</div>
        <h1 className="sw-title">Configure LLM</h1>
        <p className="sw-subtitle">
          Choose your AI provider and add your API key for chat and RAG features.
        </p>
      </div>
      <div className="sw-content">
        <div className="sw-options" style={{ marginBottom: '24px' }}>
          <div className={`sw-option ${provider === 'openai' ? 'selected' : ''}`} onClick={() => setProvider('openai')}>
            <div className="sw-option-check" />
            <div className="sw-option-icon">{Icons.openai}</div>
            <div className="sw-option-title">OpenAI</div>
            <div className="sw-option-desc">GPT-4o, GPT-4 Turbo</div>
          </div>
          <div className={`sw-option ${provider === 'anthropic' ? 'selected' : ''}`} onClick={() => setProvider('anthropic')}>
            <div className="sw-option-check" />
            <div className="sw-option-icon">{Icons.anthropic}</div>
            <div className="sw-option-title">Anthropic</div>
            <div className="sw-option-desc">Claude Sonnet 4, Claude 3.5</div>
          </div>
        </div>

        <div className="sw-field">
          <label className="sw-label">API Key</label>
          <input
            type="password"
            className="sw-input sw-input-mono"
            value={apiKey}
            onChange={(e) => setApiKey(e.target.value)}
            placeholder={provider === 'openai' ? 'sk-...' : 'sk-ant-...'}
          />
        </div>

        <div className="sw-field">
          <label className="sw-label">Model</label>
          <select className="sw-input sw-select" value={model} onChange={(e) => setModel(e.target.value)}>
            {models[provider].map((m) => (
              <option key={m.id} value={m.id}>{m.name}</option>
            ))}
          </select>
        </div>

        <div style={{ padding: '16px', background: 'var(--sw-primary-muted)', borderRadius: 'var(--sw-radius)', border: '1px solid rgba(129, 140, 248, 0.2)' }}>
          <div style={{ fontSize: '13px', color: 'var(--sw-text-muted)' }}>
            <strong style={{ color: 'var(--sw-primary)' }}>Tip:</strong> You can also set API keys via environment variables: <code style={{ fontFamily: 'var(--sw-font-mono)', background: 'var(--sw-bg)', padding: '2px 6px', borderRadius: '4px' }}>OPENAI_API_KEY</code> or <code style={{ fontFamily: 'var(--sw-font-mono)', background: 'var(--sw-bg)', padding: '2px 6px', borderRadius: '4px' }}>ANTHROPIC_API_KEY</code>
          </div>
        </div>
      </div>
      <div className="sw-footer">
        <button className="sw-btn sw-btn-ghost" onClick={onBack}>{Icons.arrowLeft} Back</button>
        <button className="sw-btn sw-btn-primary" onClick={handleNext}>Continue {Icons.arrowRight}</button>
      </div>
    </div>
  );
};

const BrandStep: React.FC<StepProps> = ({ onNext, onBack, config, updateConfig }) => {
  const [appName, setAppName] = useState(config.app?.name || 'Knowledge Base');
  const [primaryColor, setPrimaryColor] = useState(config.theme?.colors?.primary || '#6366f1');
  const [themeMode, setThemeMode] = useState<'dark' | 'light'>(config.theme?.mode === 'light' ? 'light' : 'dark');

  const colorPresets = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f97316',
    '#eab308', '#22c55e', '#14b8a6', '#0ea5e9', '#3b82f6',
  ];

  const handleNext = () => {
    updateConfig({
      app: { ...config.app, name: appName },
      theme: {
        mode: themeMode,
        radius: 'md',
        colors: {
          primary: primaryColor,
          accent: '#22c55e',
          background: themeMode === 'dark' ? '#09090b' : '#ffffff',
          surface: themeMode === 'dark' ? '#18181b' : '#f4f4f5',
          border: themeMode === 'dark' ? '#27272a' : '#e4e4e7',
          text: themeMode === 'dark' ? '#fafafa' : '#18181b',
          muted: themeMode === 'dark' ? '#a1a1aa' : '#71717a',
        },
      },
    });
    onNext();
  };

  return (
    <div className="sw-fade-in">
      <div className="sw-header">
        <div className="sw-step-indicator">Step 3 of 4</div>
        <h1 className="sw-title">Brand Your App</h1>
        <p className="sw-subtitle">Customize the look and feel to match your brand identity.</p>
      </div>
      <div className="sw-content">
        <div className="sw-field">
          <label className="sw-label">Application Name</label>
          <input type="text" className="sw-input" value={appName} onChange={(e) => setAppName(e.target.value)} placeholder="My Knowledge Base" />
        </div>

        <div className="sw-field">
          <label className="sw-label">Theme Mode</label>
          <div className="sw-options">
            <div className={`sw-option ${themeMode === 'dark' ? 'selected' : ''}`} onClick={() => setThemeMode('dark')} style={{ padding: '16px' }}>
              <div className="sw-option-check" />
              <div className="sw-option-title">Dark</div>
              <div className="sw-option-desc">Easy on the eyes</div>
            </div>
            <div className={`sw-option ${themeMode === 'light' ? 'selected' : ''}`} onClick={() => setThemeMode('light')} style={{ padding: '16px' }}>
              <div className="sw-option-check" />
              <div className="sw-option-title">Light</div>
              <div className="sw-option-desc">Bright and clean</div>
            </div>
          </div>
        </div>

        <div className="sw-field">
          <label className="sw-label">Primary Color</label>
          <div className="sw-color-grid">
            {colorPresets.map((color) => (
              <div key={color} className={`sw-color-swatch ${primaryColor === color ? 'selected' : ''}`} style={{ background: color }} onClick={() => setPrimaryColor(color)} />
            ))}
          </div>
        </div>
      </div>
      <div className="sw-footer">
        <button className="sw-btn sw-btn-ghost" onClick={onBack}>{Icons.arrowLeft} Back</button>
        <button className="sw-btn sw-btn-primary" onClick={handleNext}>Continue {Icons.arrowRight}</button>
      </div>
    </div>
  );
};

const FeaturesStep: React.FC<StepProps> = ({ onNext, onBack, config, updateConfig }) => {
  const [features, setFeatures] = useState({
    search: config.features?.search?.enabled ?? true,
    chat: config.features?.chat?.enabled ?? true,
    dashboard: config.features?.dashboard?.enabled ?? true,
    pdfViewer: config.features?.pdfViewer?.enabled ?? true,
  });

  const toggleFeature = (key: keyof typeof features) => {
    setFeatures(prev => ({ ...prev, [key]: !prev[key] }));
  };

  const handleNext = () => {
    updateConfig({
      features: {
        search: { enabled: features.search, modes: ['semantic', 'lexical', 'hybrid'], defaultMode: 'hybrid', showScores: true, limit: 20 },
        chat: { enabled: features.chat, showSources: true, streamResponses: true },
        dashboard: { enabled: features.dashboard, showStats: true, showTimeline: true },
        pdfViewer: { enabled: features.pdfViewer, showThumbnails: true },
      },
      navigation: [
        features.search && { id: 'search', label: 'Search', href: '/search', icon: 'search' },
        features.chat && { id: 'chat', label: 'Chat', href: '/chat', icon: 'message-circle' },
        features.dashboard && { id: 'dashboard', label: 'Dashboard', href: '/dashboard', icon: 'layout-grid' },
      ].filter(Boolean) as CanvasUIConfig['navigation'],
    });
    onNext();
  };

  const featureList = [
    { key: 'search' as const, icon: Icons.search, title: 'Search', desc: 'Semantic, lexical, and hybrid search across your documents' },
    { key: 'chat' as const, icon: Icons.message, title: 'Chat', desc: 'AI-powered Q&A with source citations' },
    { key: 'dashboard' as const, icon: Icons.grid, title: 'Dashboard', desc: 'Statistics, timeline, and memory overview' },
    { key: 'pdfViewer' as const, icon: Icons.database, title: 'PDF Viewer', desc: 'View source documents inline with search results' },
  ];

  return (
    <div className="sw-fade-in">
      <div className="sw-header">
        <div className="sw-step-indicator">Step 4 of 4</div>
        <h1 className="sw-title">Enable Features</h1>
        <p className="sw-subtitle">Choose which features to enable in your application.</p>
      </div>
      <div className="sw-content">
        <div className="sw-toggle-group">
          {featureList.map((feature) => (
            <div key={feature.key} className="sw-toggle">
              <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                <div style={{ width: '40px', height: '40px', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'var(--sw-surface-elevated)', borderRadius: 'var(--sw-radius-sm)', color: features[feature.key] ? 'var(--sw-primary)' : 'var(--sw-text-muted)' }}>
                  {feature.icon}
                </div>
                <div className="sw-toggle-info">
                  <div className="sw-toggle-label">{feature.title}</div>
                  <div className="sw-toggle-desc">{feature.desc}</div>
                </div>
              </div>
              <div className={`sw-toggle-switch ${features[feature.key] ? 'active' : ''}`} onClick={() => toggleFeature(feature.key)} />
            </div>
          ))}
        </div>
      </div>
      <div className="sw-footer">
        <button className="sw-btn sw-btn-ghost" onClick={onBack}>{Icons.arrowLeft} Back</button>
        <button className="sw-btn sw-btn-primary" onClick={handleNext}>Complete Setup {Icons.arrowRight}</button>
      </div>
    </div>
  );
};

const CompleteStep: React.FC<StepProps & { onComplete: () => void }> = ({ config, settings, onComplete }) => {
  const configPreview = JSON.stringify({
    app: config.app,
    llm: { provider: settings.llmProvider, model: settings.llmModel },
    theme: { mode: config.theme?.mode, primaryColor: config.theme?.colors?.primary },
    features: Object.entries(config.features || {}).filter(([, v]) => v?.enabled).map(([k]) => k),
  }, null, 2);

  return (
    <div className="sw-fade-in">
      <div className="sw-header">
        <div className="sw-complete">
          <div className="sw-complete-icon">{Icons.checkCircle}</div>
          <h1 className="sw-complete-title">You&apos;re All Set!</h1>
          <p className="sw-complete-desc">Your Canvas application is configured and ready to use.</p>
        </div>
      </div>
      <div className="sw-content">
        <div className="sw-config-preview">
          <pre style={{ margin: 0 }}>{configPreview}</pre>
        </div>
        <div style={{ padding: '16px', background: 'var(--sw-primary-muted)', borderRadius: 'var(--sw-radius)', border: '1px solid rgba(129, 140, 248, 0.2)', marginBottom: '8px' }}>
          <div style={{ fontSize: '13px', color: 'var(--sw-text-muted)' }}>
            <strong style={{ color: 'var(--sw-primary)' }}>Next steps:</strong> You can always change these settings later in the Settings panel.
          </div>
        </div>
      </div>
      <div className="sw-footer">
        <div></div>
        <button className="sw-btn sw-btn-primary" onClick={onComplete}>Launch App {Icons.arrowRight}</button>
      </div>
    </div>
  );
};

// ============================================================================
// Main Component
// ============================================================================

export function SetupWizard({ onComplete, onSkip, initialConfig, isReconfigure, createMemoryEndpoint }: SetupWizardProps) {
  const [step, setStep] = useState<SetupStep>('welcome');
  const [config, setConfig] = useState<Partial<CanvasUIConfig>>(initialConfig || {});
  const [settings, setSettings] = useState<RuntimeSettings>({});

  const updateConfig = useCallback((updates: Partial<CanvasUIConfig>) => {
    setConfig(prev => ({ ...prev, ...updates }));
  }, []);

  const updateSettings = useCallback((updates: Partial<RuntimeSettings>) => {
    setSettings(prev => ({ ...prev, ...updates }));
  }, []);

  const steps: SetupStep[] = ['welcome', 'memory', 'llm', 'brand', 'features', 'complete'];
  const currentIndex = steps.indexOf(step);
  const progress = ((currentIndex) / (steps.length - 1)) * 100;

  const goNext = () => {
    const nextIndex = currentIndex + 1;
    const nextStep = steps[nextIndex];
    if (nextIndex < steps.length && nextStep) {
      setStep(nextStep);
    }
  };

  const goBack = () => {
    const prevIndex = currentIndex - 1;
    const prevStep = steps[prevIndex];
    if (prevIndex >= 0 && prevStep) {
      setStep(prevStep);
    }
  };

  const handleComplete = () => {
    // Save to localStorage for persistence
    if (typeof window !== 'undefined') {
      localStorage.setItem('canvas-settings', JSON.stringify(settings));
      localStorage.setItem('canvas-config', JSON.stringify(config));
      localStorage.setItem('canvas-setup-complete', 'true');
    }
    onComplete(config, settings);
  };

  const handleSkip = () => {
    // Mark setup as complete with defaults
    if (typeof window !== 'undefined') {
      localStorage.setItem('canvas-setup-complete', 'true');
    }
    onSkip?.();
  };

  const stepProps: StepProps = {
    onNext: goNext,
    onBack: goBack,
    config,
    settings,
    updateConfig,
    updateSettings,
    isReconfigure,
    createMemoryEndpoint,
  };

  return (
    <>
      <style dangerouslySetInnerHTML={{ __html: styles }} />
      <div className="setup-wizard">
        <div className="sw-background">
          <div className="sw-grid" />
        </div>
        <div className="sw-container">
          <div className="sw-card">
            <div className="sw-progress">
              <div className="sw-progress-bar" style={{ width: `${progress}%` }} />
            </div>
            {step === 'welcome' && <WelcomeStep {...stepProps} />}
            {step === 'memory' && <MemoryStep {...stepProps} />}
            {step === 'llm' && <LLMStep {...stepProps} />}
            {step === 'brand' && <BrandStep {...stepProps} />}
            {step === 'features' && <FeaturesStep {...stepProps} />}
            {step === 'complete' && <CompleteStep {...stepProps} onComplete={handleComplete} />}
          </div>
          {onSkip && step === 'welcome' && (
            <div style={{ textAlign: 'center', marginTop: '24px' }}>
              <button className="sw-btn sw-btn-ghost" onClick={handleSkip}>
                Skip setup and use defaults
              </button>
            </div>
          )}
        </div>
      </div>
    </>
  );
}

export default SetupWizard;
