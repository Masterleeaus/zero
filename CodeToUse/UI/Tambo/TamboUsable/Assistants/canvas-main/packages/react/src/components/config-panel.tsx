/**
 * ConfigPanel Component
 *
 * Floating configuration panel for runtime settings.
 */

import { useState, useCallback, useEffect, type ReactNode } from 'react';
import clsx from 'clsx';

/**
 * LLM Provider types
 */
export type LLMProvider = 'anthropic' | 'openai' | 'google';

/**
 * Model options by provider
 */
export const MODEL_OPTIONS: Record<LLMProvider, { id: string; name: string }[]> = {
  anthropic: [
    { id: 'claude-sonnet-4-20250514', name: 'Claude Sonnet 4' },
    { id: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
    { id: 'claude-3-5-haiku-20241022', name: 'Claude 3.5 Haiku' },
    { id: 'claude-3-opus-20240229', name: 'Claude 3 Opus' },
  ],
  openai: [
    { id: 'gpt-4o', name: 'GPT-4o' },
    { id: 'gpt-4o-mini', name: 'GPT-4o Mini' },
    { id: 'gpt-4-turbo', name: 'GPT-4 Turbo' },
    { id: 'gpt-3.5-turbo', name: 'GPT-3.5 Turbo' },
  ],
  google: [
    { id: 'gemini-1.5-pro', name: 'Gemini 1.5 Pro' },
    { id: 'gemini-1.5-flash', name: 'Gemini 1.5 Flash' },
    { id: 'gemini-pro', name: 'Gemini Pro' },
  ],
};

/**
 * Config value types
 */
export interface ConfigValues {
  // Appearance
  theme?: 'light' | 'dark' | 'system';
  primaryColor?: string;
  // Search
  searchLimit?: number;
  showScores?: boolean;
  showBranding?: boolean;
  // API Configuration
  llmProvider?: LLMProvider;
  llmModel?: string;
  anthropicApiKey?: string;
  openaiApiKey?: string;
  googleApiKey?: string;
  // Memory
  memoryPath?: string;
  [key: string]: unknown;
}

/**
 * ConfigPanel props
 */
export interface ConfigPanelProps {
  /** Initial config values */
  initialValues?: ConfigValues;

  /** Called when config changes */
  onChange?: (values: ConfigValues) => void;

  /** Panel title */
  title?: string;

  /** Position of floating button */
  position?: 'bottom-left' | 'bottom-right' | 'top-left' | 'top-right';

  /** Custom trigger button */
  trigger?: ReactNode;

  /** Whether panel is open initially */
  defaultOpen?: boolean;

  /** Custom class name */
  className?: string;

  /** Children for custom config fields */
  children?: ReactNode;
}

/**
 * Settings icon SVG
 */
function SettingsIcon() {
  return (
    <svg
      width="20"
      height="20"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <circle cx="12" cy="12" r="3" />
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
    </svg>
  );
}

/**
 * Close icon SVG
 */
function CloseIcon() {
  return (
    <svg
      width="18"
      height="18"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <line x1="18" y1="6" x2="6" y2="18" />
      <line x1="6" y1="6" x2="18" y2="18" />
    </svg>
  );
}

/**
 * ConfigPanel component
 *
 * @example
 * ```tsx
 * <ConfigPanel
 *   initialValues={{ theme: 'light', searchLimit: 10 }}
 *   onChange={(values) => setConfig(values)}
 * />
 * ```
 */
export function ConfigPanel({
  initialValues = {},
  onChange,
  title = 'Settings',
  position = 'bottom-right',
  trigger,
  defaultOpen = false,
  className,
  children,
}: ConfigPanelProps) {
  const [isOpen, setIsOpen] = useState(defaultOpen);
  const [values, setValues] = useState<ConfigValues>(initialValues);

  // Sync with initialValues changes
  useEffect(() => {
    setValues(initialValues);
  }, [initialValues]);

  /**
   * Update a config value
   */
  const updateValue = useCallback(
    (key: string, value: unknown) => {
      const newValues = { ...values, [key]: value };
      setValues(newValues);
      onChange?.(newValues);
    },
    [values, onChange]
  );

  /**
   * Toggle panel
   */
  const togglePanel = useCallback(() => {
    setIsOpen((prev) => !prev);
  }, []);

  /**
   * Position classes
   */
  const positionClasses = {
    'bottom-left': 'canvas-config--bottom-left',
    'bottom-right': 'canvas-config--bottom-right',
    'top-left': 'canvas-config--top-left',
    'top-right': 'canvas-config--top-right',
  };

  return (
    <div className={clsx('canvas-config', positionClasses[position], className)}>
      {/* Floating trigger button */}
      <button
        className="canvas-config__trigger"
        onClick={togglePanel}
        aria-label={isOpen ? 'Close settings' : 'Open settings'}
        aria-expanded={isOpen}
      >
        {trigger ?? <SettingsIcon />}
      </button>

      {/* Panel */}
      {isOpen && (
        <div className="canvas-config__panel">
          <div className="canvas-config__header">
            <h3 className="canvas-config__title">{title}</h3>
            <button
              className="canvas-config__close"
              onClick={togglePanel}
              aria-label="Close settings"
            >
              <CloseIcon />
            </button>
          </div>

          <div className="canvas-config__content">
            {/* API Configuration Section */}
            <div className="canvas-config__section">
              <h4 className="canvas-config__section-title">API Configuration</h4>

              {/* LLM Provider */}
              <div className="canvas-config__field">
                <label className="canvas-config__label">LLM Provider</label>
                <select
                  className="canvas-config__select"
                  value={values.llmProvider || 'anthropic'}
                  onChange={(e) => {
                    const provider = e.target.value as LLMProvider;
                    updateValue('llmProvider', provider);
                    // Auto-select first model of new provider
                    const firstModel = MODEL_OPTIONS[provider]?.[0]?.id;
                    if (firstModel) {
                      updateValue('llmModel', firstModel);
                    }
                  }}
                >
                  <option value="anthropic">Anthropic (Claude)</option>
                  <option value="openai">OpenAI (GPT)</option>
                  <option value="google">Google (Gemini)</option>
                </select>
              </div>

              {/* Model Selection */}
              <div className="canvas-config__field">
                <label className="canvas-config__label">Model</label>
                <select
                  className="canvas-config__select"
                  value={values.llmModel || MODEL_OPTIONS[values.llmProvider || 'anthropic']?.[0]?.id}
                  onChange={(e) => updateValue('llmModel', e.target.value)}
                >
                  {MODEL_OPTIONS[values.llmProvider || 'anthropic']?.map((model) => (
                    <option key={model.id} value={model.id}>
                      {model.name}
                    </option>
                  ))}
                </select>
              </div>

              {/* API Key based on provider */}
              {(values.llmProvider === 'anthropic' || !values.llmProvider) && (
                <div className="canvas-config__field">
                  <label className="canvas-config__label">Anthropic API Key</label>
                  <input
                    type="password"
                    className="canvas-config__text-input"
                    value={values.anthropicApiKey || ''}
                    onChange={(e) => updateValue('anthropicApiKey', e.target.value)}
                    placeholder="sk-ant-..."
                  />
                  <p className="canvas-config__hint">Required for Claude models</p>
                </div>
              )}

              {values.llmProvider === 'openai' && (
                <div className="canvas-config__field">
                  <label className="canvas-config__label">OpenAI API Key</label>
                  <input
                    type="password"
                    className="canvas-config__text-input"
                    value={values.openaiApiKey || ''}
                    onChange={(e) => updateValue('openaiApiKey', e.target.value)}
                    placeholder="sk-..."
                  />
                  <p className="canvas-config__hint">Required for GPT models</p>
                </div>
              )}

              {values.llmProvider === 'google' && (
                <div className="canvas-config__field">
                  <label className="canvas-config__label">Google API Key</label>
                  <input
                    type="password"
                    className="canvas-config__text-input"
                    value={values.googleApiKey || ''}
                    onChange={(e) => updateValue('googleApiKey', e.target.value)}
                    placeholder="AIza..."
                  />
                  <p className="canvas-config__hint">Required for Gemini models</p>
                </div>
              )}
            </div>

            {/* Memory Configuration Section */}
            <div className="canvas-config__section">
              <h4 className="canvas-config__section-title">Memory</h4>

              <div className="canvas-config__field">
                <label className="canvas-config__label">Memory File Path</label>
                <input
                  type="text"
                  className="canvas-config__text-input"
                  value={values.memoryPath || ''}
                  onChange={(e) => updateValue('memoryPath', e.target.value)}
                  placeholder="./memory.mv2"
                />
                <p className="canvas-config__hint">Path to your .mv2 memory file</p>
              </div>

              {/* Search limit */}
              <div className="canvas-config__field">
                <label className="canvas-config__label">Search Results Limit</label>
                <input
                  type="number"
                  className="canvas-config__text-input"
                  value={values.searchLimit ?? 10}
                  onChange={(e) => updateValue('searchLimit', parseInt(e.target.value, 10))}
                  min={1}
                  max={50}
                />
              </div>
            </div>

            {/* Appearance Section */}
            <div className="canvas-config__section">
              <h4 className="canvas-config__section-title">Appearance</h4>

              {/* Theme selector */}
              <div className="canvas-config__field">
                <label className="canvas-config__label">Theme</label>
                <div className="canvas-config__button-group">
                  {(['light', 'dark', 'system'] as const).map((theme) => (
                    <button
                      key={theme}
                      className={clsx(
                        'canvas-config__button',
                        values.theme === theme && 'canvas-config__button--active'
                      )}
                      onClick={() => updateValue('theme', theme)}
                    >
                      {theme.charAt(0).toUpperCase() + theme.slice(1)}
                    </button>
                  ))}
                </div>
              </div>

              {/* Primary color */}
              <div className="canvas-config__field">
                <label className="canvas-config__label">Primary Color</label>
                <div className="canvas-config__color-row">
                  <input
                    type="color"
                    className="canvas-config__color-input"
                    value={values.primaryColor || '#3b82f6'}
                    onChange={(e) => updateValue('primaryColor', e.target.value)}
                  />
                  <input
                    type="text"
                    className="canvas-config__text-input"
                    value={values.primaryColor || '#3b82f6'}
                    onChange={(e) => updateValue('primaryColor', e.target.value)}
                    placeholder="#3b82f6"
                  />
                </div>
              </div>

              {/* Toggle options */}
              <div className="canvas-config__field">
                <label className="canvas-config__toggle">
                  <input
                    type="checkbox"
                    checked={values.showScores !== false}
                    onChange={(e) => updateValue('showScores', e.target.checked)}
                  />
                  <span>Show relevance scores</span>
                </label>
              </div>

              <div className="canvas-config__field">
                <label className="canvas-config__toggle">
                  <input
                    type="checkbox"
                    checked={values.showBranding !== false}
                    onChange={(e) => updateValue('showBranding', e.target.checked)}
                  />
                  <span>Show branding</span>
                </label>
              </div>
            </div>

            {/* Custom children */}
            {children}
          </div>
        </div>
      )}
    </div>
  );
}

/**
 * ConfigField props
 */
export interface ConfigFieldProps {
  /** Field label */
  label: string;

  /** Field content */
  children: ReactNode;

  /** Optional description */
  description?: string;
}

/**
 * ConfigField component for custom fields
 */
export function ConfigField({ label, children, description }: ConfigFieldProps) {
  return (
    <div className="canvas-config__field">
      <label className="canvas-config__label">{label}</label>
      {description && <p className="canvas-config__description">{description}</p>}
      {children}
    </div>
  );
}
