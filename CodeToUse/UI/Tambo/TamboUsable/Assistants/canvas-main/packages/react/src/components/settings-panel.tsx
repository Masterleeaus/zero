'use client';

/**
 * Settings Panel
 *
 * A slide-out panel for runtime configuration.
 * Can be triggered from a settings icon in the header.
 */

import { useState, useEffect, useCallback, useRef } from 'react';
import type { RuntimeSettings } from '@memvid/canvas-core/types-only';

export interface SettingsPanelProps {
  isOpen: boolean;
  onClose: () => void;
  onSave: (settings: RuntimeSettings) => void;
  initialSettings?: RuntimeSettings;
  /** Callback to navigate to setup wizard */
  onGoToSetup?: () => void;
  /** Callback to reset all settings */
  onReset?: () => void;
}

const styles = `
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');

  .settings-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 100;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
  }

  .settings-overlay.open {
    opacity: 1;
    visibility: visible;
  }

  .settings-panel {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    max-width: 420px;
    background: #18181b;
    border-left: 1px solid #27272a;
    z-index: 101;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-family: 'Outfit', system-ui, sans-serif;
  }

  .settings-panel.open {
    transform: translateX(0);
  }

  .settings-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #27272a;
  }

  .settings-title {
    font-size: 18px;
    font-weight: 600;
    color: #fafafa;
    margin: 0;
  }

  .settings-close {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: #a1a1aa;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .settings-close:hover {
    background: #27272a;
    color: #fafafa;
  }

  .settings-content {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
  }

  .settings-section {
    margin-bottom: 32px;
  }

  .settings-section-title {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #71717a;
    margin-bottom: 16px;
  }

  .settings-field {
    margin-bottom: 20px;
  }

  .settings-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #fafafa;
    margin-bottom: 8px;
  }

  .settings-input {
    width: 100%;
    padding: 12px 14px;
    background: #09090b;
    border: 1px solid #27272a;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    color: #fafafa;
    transition: all 0.2s ease;
  }

  .settings-input:focus {
    outline: none;
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.1);
  }

  .settings-input-mono {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
  }

  .settings-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23a1a1aa' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 40px;
  }

  .settings-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    background: #09090b;
    border: 1px solid #27272a;
    border-radius: 8px;
    margin-bottom: 12px;
  }

  .settings-toggle-label {
    font-size: 14px;
    color: #fafafa;
  }

  .settings-toggle {
    position: relative;
    width: 44px;
    height: 24px;
    background: #27272a;
    border-radius: 100px;
    cursor: pointer;
    transition: background 0.2s ease;
  }

  .settings-toggle.active {
    background: #818cf8;
  }

  .settings-toggle::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s ease;
  }

  .settings-toggle.active::after {
    transform: translateX(20px);
  }

  .settings-provider-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
  }

  .settings-provider-card {
    padding: 16px;
    background: #09090b;
    border: 2px solid #27272a;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
  }

  .settings-provider-card:hover {
    border-color: #3f3f46;
  }

  .settings-provider-card.selected {
    border-color: #818cf8;
    background: rgba(129, 140, 248, 0.05);
  }

  .settings-provider-icon {
    width: 40px;
    height: 40px;
    margin: 0 auto 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a1a1aa;
  }

  .settings-provider-card.selected .settings-provider-icon {
    color: #818cf8;
  }

  .settings-provider-name {
    font-size: 14px;
    font-weight: 500;
    color: #fafafa;
  }

  .settings-footer {
    padding: 20px 24px;
    border-top: 1px solid #27272a;
    display: flex;
    gap: 12px;
  }

  .settings-btn {
    flex: 1;
    padding: 12px 20px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .settings-btn-primary {
    background: #818cf8;
    border: none;
    color: white;
  }

  .settings-btn-primary:hover {
    background: #a5b4fc;
  }

  .settings-btn-secondary {
    background: transparent;
    border: 1px solid #27272a;
    color: #a1a1aa;
  }

  .settings-btn-secondary:hover {
    background: #27272a;
    color: #fafafa;
  }

  .settings-hint {
    font-size: 12px;
    color: #71717a;
    margin-top: 6px;
  }

  .settings-saved-toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    padding: 12px 24px;
    background: #22c55e;
    color: white;
    font-size: 14px;
    font-weight: 500;
    border-radius: 8px;
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 200;
  }

  .settings-saved-toast.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
  }

  .settings-action-btn {
    width: 100%;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #09090b;
    border: 1px solid #27272a;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    color: #fafafa;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 12px;
  }

  .settings-action-btn:hover {
    background: #18181b;
    border-color: #3f3f46;
  }

  .settings-action-btn-icon {
    width: 20px;
    height: 20px;
    color: #a1a1aa;
  }

  .settings-action-btn-content {
    flex: 1;
    text-align: left;
  }

  .settings-action-btn-title {
    font-weight: 500;
    margin-bottom: 2px;
  }

  .settings-action-btn-desc {
    font-size: 12px;
    color: #71717a;
  }

  .settings-action-btn-danger {
    border-color: rgba(248, 113, 113, 0.3);
  }

  .settings-action-btn-danger:hover {
    background: rgba(248, 113, 113, 0.1);
    border-color: rgba(248, 113, 113, 0.5);
  }

  .settings-action-btn-danger .settings-action-btn-icon {
    color: #f87171;
  }

  .settings-dropzone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 32px 24px;
    background: #09090b;
    border: 2px dashed #27272a;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
  }

  .settings-dropzone:hover {
    border-color: #3f3f46;
    background: #18181b;
  }

  .settings-dropzone--active {
    border-color: #818cf8;
    background: rgba(129, 140, 248, 0.05);
  }

  .settings-dropzone--uploading {
    pointer-events: none;
    opacity: 0.7;
  }

  .settings-dropzone-icon {
    width: 40px;
    height: 40px;
    color: #71717a;
  }

  .settings-dropzone--active .settings-dropzone-icon {
    color: #818cf8;
  }

  .settings-dropzone-text {
    font-size: 14px;
    color: #a1a1aa;
  }

  .settings-dropzone-text--success {
    color: #22c55e;
  }

  .settings-dropzone-text--error {
    color: #f87171;
  }

  .settings-dropzone-hint {
    font-size: 12px;
    color: #52525b;
  }

  .settings-spinner {
    width: 24px;
    height: 24px;
    border: 2px solid #27272a;
    border-top-color: #818cf8;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }
`;

const Icons = {
  close: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M18 6 6 18" />
      <path d="m6 6 12 12" />
    </svg>
  ),
  upload: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
      <polyline points="17 8 12 3 7 8" />
      <line x1="12" y1="3" x2="12" y2="15" />
    </svg>
  ),
  check: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <polyline points="20 6 9 17 4 12" />
    </svg>
  ),
  alert: (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="12" r="10" />
      <line x1="12" y1="8" x2="12" y2="12" />
      <line x1="12" y1="16" x2="12.01" y2="16" />
    </svg>
  ),
  wizard: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m21.64 3.64-1.28-1.28a1.21 1.21 0 0 0-1.72 0L2.36 18.64a1.21 1.21 0 0 0 0 1.72l1.28 1.28a1.2 1.2 0 0 0 1.72 0L21.64 5.36a1.2 1.2 0 0 0 0-1.72Z" />
      <path d="m14 7 3 3" />
      <path d="M5 6v4" />
      <path d="M19 14v4" />
      <path d="M10 2v2" />
      <path d="M7 8H3" />
      <path d="M21 16h-4" />
      <path d="M11 3H9" />
    </svg>
  ),
  reset: (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
      <path d="M3 3v5h5" />
    </svg>
  ),
  anthropic: (
    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
      <path d="M13.827 3.52h3.603L24 20.48h-3.603l-6.57-16.96zm-7.258 0h3.767L16.906 20.48h-3.674l-1.343-3.461H5.017l-1.344 3.46H0L6.57 3.522zm3.63 10.508L7.903 7.677l-2.295 6.35h4.59z" />
    </svg>
  ),
  openai: (
    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
      <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08-4.778 2.758a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z" />
    </svg>
  ),
};

export function SettingsPanel({ isOpen, onClose, onSave, initialSettings, onGoToSetup, onReset }: SettingsPanelProps) {
  const [settings, setSettings] = useState<RuntimeSettings>(initialSettings || {});
  const [showToast, setShowToast] = useState(false);

  // File upload state
  const [uploadStatus, setUploadStatus] = useState<'idle' | 'uploading' | 'success' | 'error'>('idle');
  const [uploadMessage, setUploadMessage] = useState('');
  const [isDragging, setIsDragging] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Load settings from localStorage on mount
  useEffect(() => {
    if (typeof window !== 'undefined' && !initialSettings) {
      const stored = localStorage.getItem('canvas-settings');
      if (stored) {
        try {
          setSettings(JSON.parse(stored));
        } catch {
          // Ignore parse errors
        }
      }
    }
  }, [initialSettings]);

  const handleSave = useCallback(() => {
    // Save to localStorage
    if (typeof window !== 'undefined') {
      localStorage.setItem('canvas-settings', JSON.stringify(settings));
    }
    onSave(settings);
    setShowToast(true);
    setTimeout(() => setShowToast(false), 2000);
  }, [settings, onSave]);

  const updateSetting = <K extends keyof RuntimeSettings>(key: K, value: RuntimeSettings[K]) => {
    setSettings(prev => ({ ...prev, [key]: value }));
  };

  // File upload handlers
  const handleFileUpload = useCallback(async (files: FileList | null) => {
    if (!files || files.length === 0) return;

    setUploadStatus('uploading');
    setUploadMessage(`Uploading ${files.length} file(s)...`);

    let successCount = 0;
    let errorCount = 0;

    for (const file of Array.from(files)) {
      try {
        const formData = new FormData();
        formData.append('file', file);

        const response = await fetch('/api/canvas/ingest', {
          method: 'POST',
          body: formData,
        });

        if (response.ok) {
          successCount++;
          setUploadMessage(`Uploaded ${successCount}/${files.length}: ${file.name}`);
        } else {
          errorCount++;
          const error = await response.json();
          console.error(`Failed to upload ${file.name}:`, error);
        }
      } catch (err) {
        errorCount++;
        console.error(`Error uploading ${file.name}:`, err);
      }
    }

    if (errorCount === 0) {
      setUploadStatus('success');
      setUploadMessage(`Successfully uploaded ${successCount} file(s)`);
    } else {
      setUploadStatus('error');
      setUploadMessage(`Uploaded ${successCount}, failed ${errorCount}`);
    }

    // Reset after delay
    setTimeout(() => {
      setUploadStatus('idle');
      setUploadMessage('');
    }, 3000);
  }, []);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(true);
  }, []);

  const handleDragLeave = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
  }, []);

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    handleFileUpload(e.dataTransfer.files);
  }, [handleFileUpload]);

  const handleFileInputChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    handleFileUpload(e.target.files);
    if (e.target) e.target.value = '';
  }, [handleFileUpload]);

  const models = {
    anthropic: [
      { id: 'claude-sonnet-4-20250514', name: 'Claude Sonnet 4' },
      { id: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
      { id: 'claude-3-haiku-20240307', name: 'Claude 3 Haiku' },
    ],
    openai: [
      { id: 'gpt-4o', name: 'GPT-4o' },
      { id: 'gpt-4o-mini', name: 'GPT-4o Mini' },
      { id: 'gpt-4-turbo', name: 'GPT-4 Turbo' },
    ],
  };

  const currentModels = models[settings.llmProvider || 'openai'];

  return (
    <>
      <style dangerouslySetInnerHTML={{ __html: styles }} />
      <div className={`settings-overlay ${isOpen ? 'open' : ''}`} onClick={onClose} />
      <div className={`settings-panel ${isOpen ? 'open' : ''}`}>
        <div className="settings-header">
          <h2 className="settings-title">Settings</h2>
          <button className="settings-close" onClick={onClose}>
            {Icons.close}
          </button>
        </div>

        <div className="settings-content">
          <div className="settings-section">
            <div className="settings-section-title">LLM Provider</div>
            <div className="settings-provider-cards">
              <div
                className={`settings-provider-card ${settings.llmProvider === 'openai' || !settings.llmProvider ? 'selected' : ''}`}
                onClick={() => updateSetting('llmProvider', 'openai')}
              >
                <div className="settings-provider-icon">{Icons.openai}</div>
                <div className="settings-provider-name">OpenAI</div>
              </div>
              <div
                className={`settings-provider-card ${settings.llmProvider === 'anthropic' ? 'selected' : ''}`}
                onClick={() => updateSetting('llmProvider', 'anthropic')}
              >
                <div className="settings-provider-icon">{Icons.anthropic}</div>
                <div className="settings-provider-name">Anthropic</div>
              </div>
            </div>

            <div className="settings-field">
              <label className="settings-label">API Key</label>
              <input
                type="password"
                className="settings-input settings-input-mono"
                value={settings.llmApiKey || ''}
                onChange={(e) => updateSetting('llmApiKey', e.target.value)}
                placeholder={settings.llmProvider === 'anthropic' ? 'sk-ant-...' : 'sk-...'}
              />
              <div className="settings-hint">
                Or set via environment variable: {settings.llmProvider === 'anthropic' ? 'ANTHROPIC_API_KEY' : 'OPENAI_API_KEY'}
              </div>
            </div>

            <div className="settings-field">
              <label className="settings-label">Model</label>
              <select
                className="settings-input settings-select"
                value={settings.llmModel || currentModels[0]?.id || ''}
                onChange={(e) => updateSetting('llmModel', e.target.value)}
              >
                {currentModels.map((m) => (
                  <option key={m.id} value={m.id}>{m.name}</option>
                ))}
              </select>
            </div>
          </div>

          <div className="settings-section">
            <div className="settings-section-title">Appearance</div>
            <div className="settings-toggle-row">
              <span className="settings-toggle-label">Dark Mode</span>
              <div
                className={`settings-toggle ${settings.themeMode === 'dark' || !settings.themeMode ? 'active' : ''}`}
                onClick={() => updateSetting('themeMode', settings.themeMode === 'dark' ? 'light' : 'dark')}
              />
            </div>
          </div>

          <div className="settings-section">
            <div className="settings-section-title">Search</div>
            <div className="settings-field">
              <label className="settings-label">Default Search Mode</label>
              <select
                className="settings-input settings-select"
                value={settings.searchMode || 'hybrid'}
                onChange={(e) => updateSetting('searchMode', e.target.value as RuntimeSettings['searchMode'])}
              >
                <option value="hybrid">Hybrid (Recommended)</option>
                <option value="semantic">Semantic</option>
                <option value="lexical">Keyword</option>
              </select>
            </div>
          </div>

          <div className="settings-section">
            <div className="settings-section-title">Import Documents</div>
            <div
              className={`settings-dropzone ${isDragging ? 'settings-dropzone--active' : ''} ${uploadStatus === 'uploading' ? 'settings-dropzone--uploading' : ''}`}
              onDragOver={handleDragOver}
              onDragLeave={handleDragLeave}
              onDrop={handleDrop}
              onClick={() => fileInputRef.current?.click()}
            >
              <input
                ref={fileInputRef}
                type="file"
                multiple
                accept=".pdf,.docx,.doc,.xlsx,.xls,.pptx,.ppt,.txt,.md,.json,.csv,.html,.xml,.yaml,.yml"
                onChange={handleFileInputChange}
                style={{ display: 'none' }}
              />
              {uploadStatus === 'idle' && (
                <>
                  <span className="settings-dropzone-icon">{Icons.upload}</span>
                  <span className="settings-dropzone-text">
                    Drop files here or click to upload
                  </span>
                  <span className="settings-dropzone-hint">
                    PDF, DOCX, XLSX, PPTX, TXT, MD, JSON, CSV
                  </span>
                </>
              )}
              {uploadStatus === 'uploading' && (
                <>
                  <div className="settings-spinner" />
                  <span className="settings-dropzone-text">{uploadMessage}</span>
                </>
              )}
              {uploadStatus === 'success' && (
                <>
                  <span className="settings-dropzone-icon" style={{ color: '#22c55e' }}>{Icons.check}</span>
                  <span className="settings-dropzone-text settings-dropzone-text--success">
                    {uploadMessage}
                  </span>
                </>
              )}
              {uploadStatus === 'error' && (
                <>
                  <span className="settings-dropzone-icon" style={{ color: '#f87171' }}>{Icons.alert}</span>
                  <span className="settings-dropzone-text settings-dropzone-text--error">
                    {uploadMessage}
                  </span>
                </>
              )}
            </div>
          </div>

          <div className="settings-section">
            <div className="settings-section-title">Advanced</div>
            {onGoToSetup && (
              <button className="settings-action-btn" onClick={onGoToSetup}>
                <span className="settings-action-btn-icon">{Icons.wizard}</span>
                <span className="settings-action-btn-content">
                  <div className="settings-action-btn-title">Re-run Setup Wizard</div>
                  <div className="settings-action-btn-desc">Reconfigure your Canvas settings from scratch</div>
                </span>
              </button>
            )}
            {onReset && (
              <button className="settings-action-btn settings-action-btn-danger" onClick={onReset}>
                <span className="settings-action-btn-icon">{Icons.reset}</span>
                <span className="settings-action-btn-content">
                  <div className="settings-action-btn-title">Reset All Settings</div>
                  <div className="settings-action-btn-desc">Clear all data and start fresh</div>
                </span>
              </button>
            )}
          </div>
        </div>

        <div className="settings-footer">
          <button className="settings-btn settings-btn-secondary" onClick={onClose}>
            Cancel
          </button>
          <button className="settings-btn settings-btn-primary" onClick={handleSave}>
            Save Changes
          </button>
        </div>
      </div>

      <div className={`settings-saved-toast ${showToast ? 'show' : ''}`}>
        Settings saved successfully
      </div>
    </>
  );
}

export default SettingsPanel;
