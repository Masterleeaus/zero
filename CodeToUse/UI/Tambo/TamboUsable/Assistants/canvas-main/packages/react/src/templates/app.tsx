/**
 * Canvas.App - Main Application Shell
 *
 * A complete, production-ready application template that combines:
 * - Navigation (sidebar or top nav)
 * - Search, Dashboard, and Support templates
 * - Full branding customization
 * - Responsive design for mobile/desktop
 *
 * Designed to scale for millions of users with:
 * - Lazy loading of templates
 * - Efficient re-renders via React.memo
 * - CSS variable-based theming (no runtime style computation)
 * - Accessibility built-in (WCAG 2.1 AA)
 *
 * @example
 * ```tsx
 * import { Canvas } from '@memvid/canvas-react';
 * import brand from './brand.json';
 *
 * export default function App() {
 *   return <Canvas.App brand={brand} memoryEndpoint="/api/memory" />;
 * }
 * ```
 */

'use client';

import React, { useState, useCallback, useMemo, useEffect, useRef, Suspense, createContext, useContext } from 'react';
import type { CanvasProps, BrandConfig, NavItem } from '../types/brand.js';
import { CanvasConfigProvider } from '../context/canvas-config-context.js';
import { applyBrand } from '../utils/brand.js';
import { DEFAULT_CANVAS_CONFIG } from '@memvid/canvas-core/config/client';
import type { CanvasConfig as UnifiedCanvasConfig } from '@memvid/canvas-core/config/client';

// ============================================================================
// Preferences Context - For persisting user settings
// ============================================================================

interface UserPreferences {
  theme: 'light' | 'dark' | 'system';
  searchMode?: 'semantic' | 'lexical' | 'hybrid';
  searchFilters?: Record<string, string>;
  navCollapsed?: boolean;
}

interface PreferencesContextType {
  preferences: UserPreferences;
  updatePreference: <K extends keyof UserPreferences>(key: K, value: UserPreferences[K]) => void;
}

const PreferencesContext = createContext<PreferencesContextType | null>(null);

const PREFERENCES_KEY = 'canvas-preferences';

function loadPreferences(): UserPreferences {
  if (typeof window === 'undefined') return { theme: 'system' };
  try {
    const stored = localStorage.getItem(PREFERENCES_KEY);
    return stored ? JSON.parse(stored) : { theme: 'system' };
  } catch {
    return { theme: 'system' };
  }
}

function savePreferences(prefs: UserPreferences): void {
  if (typeof window === 'undefined') return;
  try {
    localStorage.setItem(PREFERENCES_KEY, JSON.stringify(prefs));
  } catch {
    // Ignore localStorage errors
  }
}

export function usePreferences() {
  const ctx = useContext(PreferencesContext);
  if (!ctx) throw new Error('usePreferences must be used within PreferencesProvider');
  return ctx;
}

// ============================================================================
// Settings/Configuration Types
// ============================================================================

interface CanvasSettings {
  memvidApiKey?: string;
  llmProvider?: 'anthropic' | 'openai' | 'google';
  llmApiKey?: string;
  llmModel?: string;
  embeddingProvider?: 'openai' | 'voyage' | 'cohere';
  embeddingApiKey?: string;
  embeddingModel?: string;
  memoryPath?: string;
}

const SETTINGS_KEY = 'canvas-settings';

function loadSettings(): CanvasSettings {
  if (typeof window === 'undefined') return {};
  try {
    const stored = localStorage.getItem(SETTINGS_KEY);
    return stored ? JSON.parse(stored) : {};
  } catch {
    return {};
  }
}

function saveSettings(settings: CanvasSettings): void {
  if (typeof window === 'undefined') return;
  try {
    localStorage.setItem(SETTINGS_KEY, JSON.stringify(settings));
  } catch {
    // Ignore localStorage errors
  }
}

// Lazy load templates for code splitting
const SearchTemplate = React.lazy(() => import('./search.js').then(m => ({ default: m.Search })));
const DashboardTemplate = React.lazy(() => import('./dashboard.js').then(m => ({ default: m.Dashboard })));
const SupportTemplate = React.lazy(() => import('./support.js').then(m => ({ default: m.Support })));

/**
 * Default navigation items
 */
const DEFAULT_NAV: NavItem[] = [
  { id: 'search', label: 'Search', icon: 'search', path: '/', default: true },
  { id: 'dashboard', label: 'Dashboard', icon: 'chart', path: '/dashboard' },
  { id: 'support', label: 'Support', icon: 'chat', path: '/support' },
];

/**
 * Get initials from company name
 */
function getInitials(name: string): string {
  return name
    .split(/\s+/)
    .map(word => word[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase();
}

/**
 * Avatar component - shows logo or initials
 */
const Avatar = React.memo(function Avatar({
  logo,
  name,
  size = 32,
}: {
  logo?: string;
  name: string;
  size?: number;
}) {
  if (logo) {
    return (
      <img
        src={logo}
        alt={name}
        className="canvas-nav__logo"
        style={{ width: size, height: size }}
      />
    );
  }

  const initials = getInitials(name);
  return (
    <div
      className="canvas-nav__avatar"
      style={{ width: size, height: size }}
      aria-label={name}
    >
      {initials}
    </div>
  );
});

/**
 * Icon component for navigation
 */
const NavIcon = React.memo(function NavIcon({ name }: { name: string }) {
  const icons: Record<string, React.ReactNode> = {
    search: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <circle cx="8" cy="8" r="6" />
        <path d="M18 18L12.5 12.5" />
      </svg>
    ),
    chart: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <path d="M2 18V8L6 12L10 4L14 10L18 6V18H2Z" />
      </svg>
    ),
    chat: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <path d="M2 14V4C2 3 3 2 4 2H16C17 2 18 3 18 4V12C18 13 17 14 16 14H6L2 18V14Z" />
      </svg>
    ),
    settings: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <circle cx="10" cy="10" r="3" />
        <path d="M10 2V4M10 16V18M2 10H4M16 10H18M4.22 4.22L5.64 5.64M14.36 14.36L15.78 15.78M4.22 15.78L5.64 14.36M14.36 5.64L15.78 4.22" />
      </svg>
    ),
    sun: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <circle cx="10" cy="10" r="4" />
        <path d="M10 2V4M10 16V18M2 10H4M16 10H18M4.93 4.93L6.34 6.34M13.66 13.66L15.07 15.07M4.93 15.07L6.34 13.66M13.66 6.34L15.07 4.93" />
      </svg>
    ),
    moon: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <path d="M17 10A7 7 0 1 1 7 3.5 5.5 5.5 0 0 0 17 10Z" />
      </svg>
    ),
    close: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <path d="M4 4L16 16M16 4L4 16" />
      </svg>
    ),
    key: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <circle cx="7" cy="13" r="3" />
        <path d="M10 10L18 2M18 2V6M18 2H14" />
      </svg>
    ),
    folder: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <path d="M2 5C2 4 3 3 4 3H8L10 5H16C17 5 18 6 18 7V15C18 16 17 17 16 17H4C3 17 2 16 2 15V5Z" />
      </svg>
    ),
    cpu: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <rect x="4" y="4" width="12" height="12" rx="2" />
        <rect x="7" y="7" width="6" height="6" />
        <path d="M7 1V4M13 1V4M7 16V19M13 16V19M1 7H4M16 7H19M1 13H4M16 13H19" />
      </svg>
    ),
    eye: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <path d="M1 10s3-6 9-6 9 6 9 6-3 6-9 6-9-6-9-6z" />
        <circle cx="10" cy="10" r="3" />
      </svg>
    ),
    eyeOff: (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
        <path d="M8.5 4.2A8.5 8.5 0 0 1 10 4c6 0 9 6 9 6a15.3 15.3 0 0 1-2.3 3.2M6.5 6.5A8.5 8.5 0 0 0 1 10s3 6 9 6c1.2 0 2.3-.2 3.3-.5" />
        <path d="M2 2l16 16" />
        <path d="M8 8a3 3 0 0 0 4.2 4.2" />
      </svg>
    ),
  };
  return <span className="canvas-nav__icon">{icons[name] || icons.search}</span>;
});

/**
 * Password Input with visibility toggle
 */
const PasswordInput = React.memo(function PasswordInput({
  id,
  value,
  onChange,
  placeholder,
  className,
}: {
  id: string;
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  className?: string;
}) {
  const [visible, setVisible] = useState(false);

  return (
    <div className="canvas-settings__password-wrapper">
      <input
        id={id}
        type={visible ? 'text' : 'password'}
        className={className || 'canvas-settings__input'}
        value={value}
        onChange={e => onChange(e.target.value)}
        placeholder={placeholder}
      />
      <button
        type="button"
        className="canvas-settings__eye-btn"
        onClick={() => setVisible(v => !v)}
        aria-label={visible ? 'Hide password' : 'Show password'}
      >
        <NavIcon name={visible ? 'eyeOff' : 'eye'} />
      </button>
    </div>
  );
});

// Default models per provider
const DEFAULT_LLM_MODELS: Record<string, string> = {
  anthropic: 'claude-sonnet-4-20250514',
  openai: 'gpt-4o',
  google: 'gemini-pro',
};

/**
 * Settings Panel Component
 */
const SettingsPanel = React.memo(function SettingsPanel({
  isOpen,
  onClose,
  config,
  onEvent,
}: {
  isOpen: boolean;
  onClose: () => void;
  config?: UnifiedCanvasConfig;
  onEvent?: (event: string, data?: Record<string, unknown>) => void;
}) {
  const [settings, setSettings] = useState<CanvasSettings>(loadSettings);
  const [hasChanges, setHasChanges] = useState(false);
  const [uploadStatus, setUploadStatus] = useState<'idle' | 'uploading' | 'success' | 'error'>('idle');
  const [uploadMessage, setUploadMessage] = useState('');
  const [isDragging, setIsDragging] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const updateSetting = useCallback(<K extends keyof CanvasSettings>(
    key: K,
    value: CanvasSettings[K]
  ) => {
    setSettings(prev => ({ ...prev, [key]: value }));
    setHasChanges(true);
  }, []);

  // Handle provider change - also update the model to the default for that provider
  const handleProviderChange = useCallback((provider: CanvasSettings['llmProvider']) => {
    setSettings(prev => ({
      ...prev,
      llmProvider: provider,
      llmModel: DEFAULT_LLM_MODELS[provider || 'openai'] || '',
    }));
    setHasChanges(true);
  }, []);

  const handleSave = useCallback(() => {
    saveSettings(settings);
    setHasChanges(false);
    onEvent?.('settings:save', settings as unknown as Record<string, unknown>);
    onClose(); // Close modal after saving
  }, [settings, onEvent, onClose]);

  const handleReset = useCallback(() => {
    setSettings({});
    setHasChanges(true);
  }, []);

  // File upload handlers
  const handleFileUpload = useCallback(async (files: FileList | null) => {
    if (!files || files.length === 0) return;

    setUploadStatus('uploading');
    setUploadMessage(`Uploading ${files.length} file(s)...`);

    let successCount = 0;
    let errorCount = 0;

    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      if (!file) continue;

      try {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('enableEmbeddings', 'true');

        const response = await fetch('/api/canvas/ingest', {
          method: 'POST',
          body: formData,
        });

        if (response.ok) {
          successCount++;
          setUploadMessage(`Uploaded ${successCount}/${files.length}: ${file.name}`);
        } else {
          const error = await response.json();
          errorCount++;
          console.error(`Failed to upload ${file.name}:`, error);
        }
      } catch (error) {
        errorCount++;
        console.error(`Error uploading ${file.name}:`, error);
      }
    }

    if (errorCount === 0) {
      setUploadStatus('success');
      setUploadMessage(`Successfully uploaded ${successCount} file(s)`);
      onEvent?.('files:uploaded', { count: successCount });
    } else {
      setUploadStatus('error');
      setUploadMessage(`Uploaded ${successCount}, failed ${errorCount}`);
    }

    // Reset status after a delay
    setTimeout(() => {
      setUploadStatus('idle');
      setUploadMessage('');
    }, 3000);
  }, [onEvent]);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(true);
  }, []);

  const handleDragLeave = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
  }, []);

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
    handleFileUpload(e.dataTransfer.files);
  }, [handleFileUpload]);

  const handleFileInputChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    handleFileUpload(e.target.files);
    // Reset input so same file can be selected again
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  }, [handleFileUpload]);

  // Reload settings when panel opens
  useEffect(() => {
    if (isOpen) {
      setSettings(loadSettings());
      setHasChanges(false);
    }
  }, [isOpen]);

  if (!isOpen) return null;

  return (
    <div className="canvas-settings-overlay" onClick={onClose}>
      <div className="canvas-settings" onClick={e => e.stopPropagation()}>
        <div className="canvas-settings__header">
          <h2 className="canvas-settings__title">Settings</h2>
          <button className="canvas-settings__close" onClick={onClose} aria-label="Close settings">
            <NavIcon name="close" />
          </button>
        </div>

        <div className="canvas-settings__content">
          {/* API Keys Section */}
          <section className="canvas-settings__section">
            <h3 className="canvas-settings__section-title">
              <NavIcon name="key" />
              API Keys
            </h3>

            <div className="canvas-settings__field">
              <label className="canvas-settings__label" htmlFor="memvid-api-key">
                Memvid API Key
              </label>
              <PasswordInput
                id="memvid-api-key"
                value={settings.memvidApiKey || ''}
                onChange={v => updateSetting('memvidApiKey', v)}
                placeholder="mv-..."
              />
            </div>

            <div className="canvas-settings__field">
              <label className="canvas-settings__label" htmlFor="llm-api-key">
                LLM API Key
              </label>
              <div className="canvas-settings__input-group">
                <select
                  id="llm-provider"
                  className="canvas-settings__select"
                  value={settings.llmProvider || config?.llm?.provider || 'openai'}
                  onChange={e => handleProviderChange(e.target.value as CanvasSettings['llmProvider'])}
                >
                  <option value="openai">OpenAI</option>
                  <option value="anthropic">Anthropic</option>
                  <option value="google">Google</option>
                </select>
                <PasswordInput
                  id="llm-api-key"
                  className="canvas-settings__input canvas-settings__input--flex"
                  value={settings.llmApiKey || ''}
                  onChange={v => updateSetting('llmApiKey', v)}
                  placeholder="sk-..."
                />
              </div>
            </div>

            <div className="canvas-settings__field">
              <label className="canvas-settings__label" htmlFor="llm-model">
                LLM Model
              </label>
              <input
                id="llm-model"
                type="text"
                className="canvas-settings__input"
                value={settings.llmModel || DEFAULT_LLM_MODELS[settings.llmProvider || 'openai'] || ''}
                onChange={e => updateSetting('llmModel', e.target.value)}
                placeholder={DEFAULT_LLM_MODELS[settings.llmProvider || 'openai'] || 'gpt-4o'}
              />
            </div>
          </section>

          {/* Embeddings Section */}
          <section className="canvas-settings__section">
            <h3 className="canvas-settings__section-title">
              <NavIcon name="cpu" />
              Embeddings
            </h3>

            <div className="canvas-settings__field">
              <label className="canvas-settings__label" htmlFor="embedding-provider">
                Embedding Provider
              </label>
              <div className="canvas-settings__input-group">
                <select
                  id="embedding-provider"
                  className="canvas-settings__select"
                  value={settings.embeddingProvider || config?.embedding?.provider || 'openai'}
                  onChange={e => updateSetting('embeddingProvider', e.target.value as CanvasSettings['embeddingProvider'])}
                >
                  <option value="openai">OpenAI</option>
                  <option value="voyage">Voyage</option>
                  <option value="cohere">Cohere</option>
                </select>
                <PasswordInput
                  id="embedding-api-key"
                  className="canvas-settings__input canvas-settings__input--flex"
                  value={settings.embeddingApiKey || ''}
                  onChange={v => updateSetting('embeddingApiKey', v)}
                  placeholder="API Key"
                />
              </div>
            </div>

            <div className="canvas-settings__field">
              <label className="canvas-settings__label" htmlFor="embedding-model">
                Embedding Model
              </label>
              <input
                id="embedding-model"
                type="text"
                className="canvas-settings__input"
                value={settings.embeddingModel || config?.embedding?.model || ''}
                onChange={e => updateSetting('embeddingModel', e.target.value)}
                placeholder="text-embedding-3-small"
              />
            </div>
          </section>

          {/* Memory File Section */}
          <section className="canvas-settings__section">
            <h3 className="canvas-settings__section-title">
              <NavIcon name="folder" />
              Memory File
            </h3>

            <div className="canvas-settings__field">
              <label className="canvas-settings__label" htmlFor="memory-path">
                Memory File Path (.mv2)
              </label>
              <input
                id="memory-path"
                type="text"
                className="canvas-settings__input"
                value={settings.memoryPath || (typeof config?.memory === 'string' ? config.memory : config?.memory?.path) || ''}
                onChange={e => updateSetting('memoryPath', e.target.value)}
                placeholder="./data/memory.mv2"
              />
              <span className="canvas-settings__hint">
                Relative to project root or absolute path
              </span>
            </div>
          </section>

          {/* File Upload Section */}
          <section className="canvas-settings__section">
            <h3 className="canvas-settings__section-title">
              <NavIcon name="upload" />
              Import Documents
            </h3>

            <div className="canvas-settings__field">
              <div
                className={`canvas-settings__dropzone ${isDragging ? 'canvas-settings__dropzone--active' : ''} ${uploadStatus === 'uploading' ? 'canvas-settings__dropzone--uploading' : ''}`}
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
                    <NavIcon name="upload" />
                    <span className="canvas-settings__dropzone-text">
                      Drop files here or click to upload
                    </span>
                    <span className="canvas-settings__dropzone-hint">
                      PDF, DOCX, XLSX, PPTX, TXT, MD, JSON, CSV
                    </span>
                  </>
                )}
                {uploadStatus === 'uploading' && (
                  <>
                    <div className="canvas-settings__spinner" />
                    <span className="canvas-settings__dropzone-text">{uploadMessage}</span>
                  </>
                )}
                {uploadStatus === 'success' && (
                  <>
                    <NavIcon name="check" />
                    <span className="canvas-settings__dropzone-text canvas-settings__dropzone-text--success">
                      {uploadMessage}
                    </span>
                  </>
                )}
                {uploadStatus === 'error' && (
                  <>
                    <NavIcon name="alert" />
                    <span className="canvas-settings__dropzone-text canvas-settings__dropzone-text--error">
                      {uploadMessage}
                    </span>
                  </>
                )}
              </div>
            </div>
          </section>
        </div>

        <div className="canvas-settings__footer">
          <button
            className="canvas-settings__btn canvas-settings__btn--secondary"
            onClick={handleReset}
          >
            Reset
          </button>
          <button
            className="canvas-settings__btn canvas-settings__btn--primary"
            onClick={handleSave}
            disabled={!hasChanges}
          >
            Save Changes
          </button>
        </div>
      </div>
    </div>
  );
});

/**
 * Theme Toggle Component
 */
const ThemeToggle = React.memo(function ThemeToggle({
  theme,
  onToggle,
  collapsed,
}: {
  theme: 'light' | 'dark';
  onToggle: () => void;
  collapsed: boolean;
}) {
  return (
    <button
      className="canvas-nav__theme-toggle"
      onClick={onToggle}
      aria-label={theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
    >
      <NavIcon name={theme === 'dark' ? 'sun' : 'moon'} />
      {!collapsed && (
        <span className="canvas-nav__label">
          {theme === 'dark' ? 'Light Mode' : 'Dark Mode'}
        </span>
      )}
    </button>
  );
});

/**
 * Loading fallback for lazy-loaded templates
 */
const TemplateLoader = React.memo(function TemplateLoader() {
  return (
    <div className="canvas-loader">
      <div className="canvas-loader__spinner" />
      <span className="canvas-loader__text">Loading...</span>
    </div>
  );
});

/**
 * Navigation component
 */
const Navigation = React.memo(function Navigation({
  items,
  activeId,
  onNavigate,
  brand,
  collapsed,
  onToggleCollapse,
  theme,
  onToggleTheme,
  onOpenSettings,
}: {
  items: NavItem[];
  activeId: string;
  onNavigate: (id: string) => void;
  brand: BrandConfig;
  collapsed: boolean;
  onToggleCollapse: () => void;
  theme: 'light' | 'dark';
  onToggleTheme: () => void;
  onOpenSettings: () => void;
}) {
  return (
    <nav className={`canvas-nav ${collapsed ? 'canvas-nav--collapsed' : ''}`}>
      <div className="canvas-nav__header">
        <Avatar
          logo={theme === 'dark' ? (brand.logoDark || brand.logo) : brand.logo}
          name={brand.name}
        />
        {!collapsed && <span className="canvas-nav__brand">{brand.name}</span>}
        <button
          className="canvas-nav__toggle"
          onClick={onToggleCollapse}
          aria-label={collapsed ? 'Expand navigation' : 'Collapse navigation'}
        >
          <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
            <path d={collapsed ? 'M6 4L10 8L6 12' : 'M10 4L6 8L10 12'} />
          </svg>
        </button>
      </div>

      <ul className="canvas-nav__list">
        {items.filter(item => !item.hidden).map(item => (
          <li key={item.id} className="canvas-nav__item">
            <button
              className={`canvas-nav__link ${activeId === item.id ? 'canvas-nav__link--active' : ''}`}
              onClick={() => onNavigate(item.id)}
              aria-current={activeId === item.id ? 'page' : undefined}
            >
              {item.icon && <NavIcon name={item.icon} />}
              {!collapsed && <span className="canvas-nav__label">{item.label}</span>}
              {item.badge !== undefined && item.badge > 0 && (
                <span className="canvas-nav__badge">{item.badge > 99 ? '99+' : item.badge}</span>
              )}
            </button>
          </li>
        ))}
      </ul>

      <div className="canvas-nav__actions">
        <ThemeToggle
          theme={theme}
          onToggle={onToggleTheme}
          collapsed={collapsed}
        />
        <button
          className="canvas-nav__settings-btn"
          onClick={onOpenSettings}
          aria-label="Open settings"
        >
          <NavIcon name="settings" />
          {!collapsed && <span className="canvas-nav__label">Settings</span>}
        </button>
      </div>

      {brand.tagline && !collapsed && (
        <div className="canvas-nav__footer">
          <span className="canvas-nav__tagline">{brand.tagline}</span>
        </div>
      )}
    </nav>
  );
});

/**
 * Theme provider - applies CSS variables from brand config
 */
function useThemeVariables(brand: BrandConfig): React.CSSProperties {
  return useMemo(() => {
    const vars: Record<string, string> = {};

    // Colors
    if (brand.colors?.primary) vars['--canvas-primary'] = brand.colors.primary;
    if (brand.colors?.accent) vars['--canvas-accent'] = brand.colors.accent;
    if (brand.colors?.success) vars['--canvas-success'] = brand.colors.success;
    if (brand.colors?.warning) vars['--canvas-warning'] = brand.colors.warning;
    if (brand.colors?.error) vars['--canvas-error'] = brand.colors.error;

    // Background colors
    if (brand.colors?.background?.primary) vars['--canvas-bg-primary'] = brand.colors.background.primary;
    if (brand.colors?.background?.secondary) vars['--canvas-bg-secondary'] = brand.colors.background.secondary;
    if (brand.colors?.background?.tertiary) vars['--canvas-bg-tertiary'] = brand.colors.background.tertiary;

    // Text colors
    if (brand.colors?.text?.primary) vars['--canvas-text-primary'] = brand.colors.text.primary;
    if (brand.colors?.text?.secondary) vars['--canvas-text-secondary'] = brand.colors.text.secondary;
    if (brand.colors?.text?.muted) vars['--canvas-text-muted'] = brand.colors.text.muted;

    // Typography
    if (brand.typography?.fontFamily) vars['--canvas-font-family'] = brand.typography.fontFamily;
    if (brand.typography?.fontFamilyMono) vars['--canvas-font-family-mono'] = brand.typography.fontFamilyMono;
    if (brand.typography?.fontSize) vars['--canvas-font-size'] = brand.typography.fontSize;

    // Custom CSS variables
    if (brand.customCSS) {
      Object.entries(brand.customCSS).forEach(([key, value]) => {
        vars[key.startsWith('--') ? key : `--canvas-${key}`] = value;
      });
    }

    return vars as React.CSSProperties;
  }, [brand]);
}

/**
 * Main App component
 */
export function App({
  brand,
  config,
  memoryEndpoint = '/api/canvas',
  initialRoute,
  onRouteChange,
  onEvent,
  slots = {},
  className,
  style,
}: CanvasProps) {
  // Load preferences from localStorage
  const [preferences, setPreferences] = useState<UserPreferences>(() => loadPreferences());
  const [settingsOpen, setSettingsOpen] = useState(false);

  // Merge config with defaults and convert to unified config format
  const unifiedConfig = useMemo((): UnifiedCanvasConfig => {
    // Cast to partial unified config for safe property access
    const baseConfig = (config || {}) as Partial<UnifiedCanvasConfig>;
    
    // Merge brand config into unified config
    const merged: UnifiedCanvasConfig = {
      ...DEFAULT_CANVAS_CONFIG,
      brand: {
        ...DEFAULT_CANVAS_CONFIG.brand,
        ...(baseConfig.brand || {}),
        ...(brand ? {
          name: brand.name || DEFAULT_CANVAS_CONFIG.brand.name,
          tagline: brand.tagline || DEFAULT_CANVAS_CONFIG.brand.tagline,
          logo: brand.logo || DEFAULT_CANVAS_CONFIG.brand.logo,
          favicon: brand.favicon || DEFAULT_CANVAS_CONFIG.brand.favicon,
        } : {}),
      },
      theme: {
        ...DEFAULT_CANVAS_CONFIG.theme,
        ...(baseConfig.theme || {}),
        colors: {
          ...DEFAULT_CANVAS_CONFIG.theme.colors,
          ...(baseConfig.theme?.colors || {}),
        },
        ...(brand?.theme ? {
          mode: brand.theme as 'light' | 'dark' | 'system',
        } : {}),
      },
      layout: {
        ...DEFAULT_CANVAS_CONFIG.layout,
        ...(baseConfig.layout || {}),
      },
      features: {
        ...DEFAULT_CANVAS_CONFIG.features,
        ...(baseConfig.features || {}),
        ...(brand?.features ? {
          search: { ...DEFAULT_CANVAS_CONFIG.features.search, enabled: brand.features.search !== false },
          chat: { ...DEFAULT_CANVAS_CONFIG.features.chat, enabled: (brand.features as any).chat !== false && brand.features.support !== false },
          dashboard: { ...DEFAULT_CANVAS_CONFIG.features.dashboard, enabled: brand.features.dashboard !== false },
          settings: { ...DEFAULT_CANVAS_CONFIG.features.settings },
          setupWizard: { ...DEFAULT_CANVAS_CONFIG.features.setupWizard },
        } : {}),
      },
      navigation: {
        ...DEFAULT_CANVAS_CONFIG.navigation,
        ...(baseConfig.navigation || {}),
        ...(brand?.navigation ? {
          items: brand.navigation.map((item: NavItem) => ({
            id: item.id,
            label: item.label,
            icon: item.icon || 'circle',
            href: (item as any).path || (item as any).href || `/${item.id}`,
            badge: (item as any).badge,
            external: (item as any).external || false,
          })),
        } : {}),
      },
      text: {
        ...DEFAULT_CANVAS_CONFIG.text,
        ...(baseConfig.text || {}),
      },
      llm: {
        ...DEFAULT_CANVAS_CONFIG.llm,
        ...(baseConfig.llm || {}),
      },
      memory: baseConfig.memory || DEFAULT_CANVAS_CONFIG.memory,
    };

    return merged;
  }, [config, brand]);

  // Apply brand on mount and when config changes
  useEffect(() => {
    applyBrand(unifiedConfig);
  }, [unifiedConfig]);

  // Update and persist preferences
  const updatePreference = useCallback(<K extends keyof UserPreferences>(
    key: K,
    value: UserPreferences[K]
  ) => {
    setPreferences(prev => {
      const next = { ...prev, [key]: value };
      savePreferences(next);
      return next;
    });
  }, []);

  // Get default route (without window check to avoid hydration mismatch)
  const getDefaultRoute = useCallback(() => {
    if (initialRoute) return initialRoute;
    if (unifiedConfig.defaultTemplate) return unifiedConfig.defaultTemplate;
    const defaultNav = (unifiedConfig.navigation?.items || (brand?.navigation as any) || DEFAULT_NAV).find((n: any) => n.default);
    return defaultNav?.id || 'search';
  }, [initialRoute, unifiedConfig, brand?.navigation]);

  const [activeRoute, setActiveRoute] = useState(getDefaultRoute);

  // Sync route from URL hash AFTER hydration
  useEffect(() => {
    const hash = window.location.hash.slice(1);
    if (hash) {
      const navItems = brand.navigation || DEFAULT_NAV;
      const validRoute = navItems.find(n => n.id === hash);
      if (validRoute && hash !== activeRoute) {
        setActiveRoute(hash);
      }
    }
  }, [brand.navigation, activeRoute]);
  const [navCollapsed, setNavCollapsed] = useState(() => preferences.navCollapsed ?? false);

  // Use preference theme if set, otherwise use brand theme
  const getInitialTheme = useCallback((): 'light' | 'dark' | 'system' => {
    if (preferences.theme && preferences.theme !== 'system') {
      return preferences.theme;
    }
    return brand.theme || 'system';
  }, [preferences.theme, brand.theme]);

  const [themePreference, setThemePreference] = useState<'light' | 'dark' | 'system'>(getInitialTheme);
  const [resolvedTheme, setResolvedTheme] = useState<'light' | 'dark'>('light');

  // Handle system theme preference and resolve theme
  useEffect(() => {
    if (themePreference === 'system') {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      setResolvedTheme(mediaQuery.matches ? 'dark' : 'light');

      const handler = (e: MediaQueryListEvent) => setResolvedTheme(e.matches ? 'dark' : 'light');
      mediaQuery.addEventListener('change', handler);
      return () => mediaQuery.removeEventListener('change', handler);
    } else {
      setResolvedTheme(themePreference);
    }
  }, [themePreference]);

  // Toggle theme
  const handleToggleTheme = useCallback(() => {
    const newTheme = resolvedTheme === 'dark' ? 'light' : 'dark';
    setThemePreference(newTheme);
    updatePreference('theme', newTheme);
    onEvent?.('theme:change', { theme: newTheme });
  }, [resolvedTheme, updatePreference, onEvent]);

  // Handle nav collapse and persist
  const handleToggleCollapse = useCallback(() => {
    const newCollapsed = !navCollapsed;
    setNavCollapsed(newCollapsed);
    updatePreference('navCollapsed', newCollapsed);
  }, [navCollapsed, updatePreference]);

  // Navigation items with feature flags applied
  const navItems = useMemo(() => {
    const features = unifiedConfig.features;
    // Use unified config navigation, fallback to brand navigation, then defaults
    const configNavItems = unifiedConfig.navigation?.items || [];
    const brandNavItems = brand.navigation || [];
    const items = configNavItems.length > 0 
      ? configNavItems.map((item: any) => ({
          id: item.id,
          label: item.label,
          icon: item.icon || 'circle',
          path: item.href || `/${item.id}`,
          default: item.default,
          badge: item.badge,
          external: item.external,
        }))
      : brandNavItems.length > 0
      ? brandNavItems
      : DEFAULT_NAV;

    return items.filter((item: any) => {
      if (item.id === 'search' && features?.search?.enabled === false) return false;
      if (item.id === 'dashboard' && features?.dashboard?.enabled === false) return false;
      if (item.id === 'support' && features?.chat?.enabled === false) return false;
      if (item.id === 'chat' && features?.chat?.enabled === false) return false;
      return true;
    });
  }, [unifiedConfig.navigation, unifiedConfig.features, brand.navigation]);

  // Route change handler - also update URL hash
  const handleNavigate = useCallback((route: string) => {
    setActiveRoute(route);
    // Update URL hash for browser navigation
    if (typeof window !== 'undefined') {
      window.history.pushState(null, '', `#${route}`);
    }
    onRouteChange?.(route);
    onEvent?.('navigate', { route });
  }, [onRouteChange, onEvent]);

  // Listen for browser back/forward navigation
  useEffect(() => {
    if (typeof window === 'undefined') return;

    const handlePopState = () => {
      const hash = window.location.hash.slice(1);
      if (hash) {
        const navItems = brand.navigation || DEFAULT_NAV;
        const validRoute = navItems.find(n => n.id === hash);
        if (validRoute) {
          setActiveRoute(hash);
          onRouteChange?.(hash);
        }
      }
    };

    window.addEventListener('popstate', handlePopState);
    return () => window.removeEventListener('popstate', handlePopState);
  }, [brand.navigation, onRouteChange]);

  // Theme variables
  const themeStyles = useThemeVariables(brand);

  // Combined props for templates with preferences context
  const templateProps = useMemo(() => ({
    brand,
    config: unifiedConfig as unknown as Record<string, unknown>,
    memoryEndpoint,
    onEvent,
  }), [brand, unifiedConfig, memoryEndpoint, onEvent]);

  // Helper to render slot or default content
  const renderSlot = (
    slot: React.ReactNode | React.ComponentType<any> | undefined,
    props: Record<string, any>,
    fallback: React.ReactNode
  ): React.ReactNode => {
    if (slot === null) return null;
    if (slot === undefined) return fallback;
    if (typeof slot === 'function') {
      const SlotComponent = slot as React.ComponentType<any>;
      return <SlotComponent {...props} />;
    }
    return slot;
  };

  // Render active template with slot support
  const renderTemplate = () => {
    switch (activeRoute) {
      case 'dashboard':
        // Support slots.dashboard to replace dashboard template
        return slots.dashboard !== undefined
          ? renderSlot(slots.dashboard, templateProps, null)
          : <DashboardTemplate {...templateProps} />;
      case 'support':
        // Support slots.support to replace support template
        return slots.support !== undefined
          ? renderSlot(slots.support, templateProps, null)
          : <SupportTemplate {...templateProps} />;
      case 'search':
      default:
        // Support slots.search to replace search template
        return slots.search !== undefined
          ? renderSlot(slots.search, templateProps, null)
          : <SearchTemplate {...templateProps} />;
    }
  };

  // Preferences context value
  const preferencesContextValue = useMemo(() => ({
    preferences,
    updatePreference,
  }), [preferences, updatePreference]);

  return (
    <CanvasConfigProvider config={unifiedConfig}>
      <PreferencesContext.Provider value={preferencesContextValue}>
        <div
          className={`canvas-app ${className || ''}`}
          data-theme={resolvedTheme}
          style={{ ...themeStyles, ...style }}
        >
        <Navigation
          items={navItems}
          activeId={activeRoute}
          onNavigate={handleNavigate}
          brand={brand}
          collapsed={navCollapsed}
          onToggleCollapse={handleToggleCollapse}
          theme={resolvedTheme}
          onToggleTheme={handleToggleTheme}
          onOpenSettings={() => setSettingsOpen(true)}
        />

        <main className="canvas-app__main">
          {/* Before Content Slot */}
          {slots.beforeContent}

          <Suspense fallback={<TemplateLoader />}>
            {renderTemplate()}
          </Suspense>

          {/* After Content Slot */}
          {slots.afterContent}
        </main>

        <SettingsPanel
          isOpen={settingsOpen}
          onClose={() => setSettingsOpen(false)}
          config={unifiedConfig}
          onEvent={onEvent}
        />
      </div>
    </PreferencesContext.Provider>
    </CanvasConfigProvider>
  );
}

export default App;
