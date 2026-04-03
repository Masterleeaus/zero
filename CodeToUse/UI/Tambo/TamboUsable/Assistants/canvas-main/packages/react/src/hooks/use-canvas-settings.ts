'use client';

/**
 * Canvas Settings Hook
 *
 * Manages runtime settings with localStorage persistence.
 */

import { useState, useEffect, useCallback } from 'react';
import type { RuntimeSettings, CanvasUIConfig } from '@memvid/canvas-core/types-only';

/**
 * Default UI configuration (inlined to avoid importing Node.js dependencies)
 */
const DEFAULT_UI_CONFIG: CanvasUIConfig = {
  app: {
    name: 'Knowledge Base',
    description: 'AI-powered search and chat',
  },
  theme: {
    mode: 'dark',
    radius: 'md',
    colors: {
      primary: '#818cf8',
      accent: '#22c55e',
      background: '#09090b',
      surface: '#18181b',
      border: '#27272a',
      text: '#fafafa',
      muted: '#a1a1aa',
    },
  },
  features: {
    search: { enabled: true, modes: ['semantic', 'lexical', 'hybrid'], defaultMode: 'hybrid', showScores: true, limit: 20 },
    chat: { enabled: true, showSources: true, streamResponses: true },
    dashboard: { enabled: true, showStats: true, showTimeline: true },
    pdfViewer: { enabled: true, showThumbnails: true },
  },
};

/**
 * Default runtime settings (inlined to avoid importing Node.js dependencies)
 */
const DEFAULT_RUNTIME_SETTINGS: RuntimeSettings = {
  llmProvider: 'openai',
  llmModel: 'gpt-4o-mini',
  themeMode: 'dark',
  searchMode: 'hybrid',
};

const SETTINGS_KEY = 'canvas-settings';
const CONFIG_KEY = 'canvas-config';
const SETUP_COMPLETE_KEY = 'canvas-setup-complete';

export interface UseCanvasSettingsReturn {
  settings: RuntimeSettings;
  config: Partial<CanvasUIConfig>;
  isSetupComplete: boolean;
  updateSettings: (updates: Partial<RuntimeSettings>) => void;
  updateConfig: (updates: Partial<CanvasUIConfig>) => void;
  completeSetup: () => void;
  resetSettings: () => void;
  isLoading: boolean;
}

export function useCanvasSettings(): UseCanvasSettingsReturn {
  const [settings, setSettings] = useState<RuntimeSettings>(DEFAULT_RUNTIME_SETTINGS);
  const [config, setConfig] = useState<Partial<CanvasUIConfig>>(DEFAULT_UI_CONFIG);
  const [isSetupComplete, setIsSetupComplete] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  // Load from localStorage and server on mount
  useEffect(() => {
    if (typeof window === 'undefined') {
      setIsLoading(false);
      return;
    }

    const loadAllSettings = async () => {
      try {
        // First check localStorage
        const storedSettings = localStorage.getItem(SETTINGS_KEY);
        if (storedSettings) {
          setSettings((prev: RuntimeSettings) => ({ ...prev, ...JSON.parse(storedSettings) }));
        }

        const storedConfig = localStorage.getItem(CONFIG_KEY);
        if (storedConfig) {
          const parsed = JSON.parse(storedConfig);
          // Deep merge the config to preserve nested structure
          setConfig((prev) => ({
            ...prev,
            ...parsed,
            app: { ...prev?.app, ...parsed.app },
            theme: { ...prev?.theme, ...parsed.theme, colors: { ...prev?.theme?.colors, ...parsed.theme?.colors } },
            features: { ...prev?.features, ...parsed.features },
          }));
        }

        // Check localStorage for setup completion first
        const localSetupComplete = localStorage.getItem(SETUP_COMPLETE_KEY);
        if (localSetupComplete === 'true') {
          setIsSetupComplete(true);
        } else {
          // If not in localStorage, check server-side settings
          try {
            const response = await fetch('/api/canvas/settings');
            if (response.ok) {
              const serverSettings = await response.json();
              // If server has setupCompleted or has a valid memory path, consider setup complete
              if (serverSettings.setupCompleted || serverSettings.memoryPath) {
                setIsSetupComplete(true);
                localStorage.setItem(SETUP_COMPLETE_KEY, 'true');
                // Also sync other settings from server
                if (serverSettings.llmProvider) {
                  setSettings((prev: RuntimeSettings) => ({
                    ...prev,
                    llmProvider: serverSettings.llmProvider,
                    llmModel: serverSettings.llmModel,
                  }));
                }
              }
            }
          } catch {
            // Server settings not available, use localStorage only
          }
        }
      } catch (e) {
        console.error('Failed to load settings:', e);
      }

      setIsLoading(false);
    };

    loadAllSettings();
  }, []);

  const updateSettings = useCallback((updates: Partial<RuntimeSettings>) => {
    setSettings((prev: RuntimeSettings) => {
      const newSettings = { ...prev, ...updates };
      if (typeof window !== 'undefined') {
        localStorage.setItem(SETTINGS_KEY, JSON.stringify(newSettings));
      }
      return newSettings;
    });
  }, []);

  const updateConfig = useCallback((updates: Partial<CanvasUIConfig>) => {
    setConfig((prev) => {
      const newConfig = { ...prev, ...updates };
      if (typeof window !== 'undefined') {
        localStorage.setItem(CONFIG_KEY, JSON.stringify(newConfig));
      }
      return newConfig;
    });
  }, []);

  const completeSetup = useCallback(() => {
    if (typeof window !== 'undefined') {
      localStorage.setItem(SETUP_COMPLETE_KEY, 'true');
    }
    setIsSetupComplete(true);
  }, []);

  const resetSettings = useCallback(() => {
    setSettings(DEFAULT_RUNTIME_SETTINGS);
    setConfig(DEFAULT_UI_CONFIG);
    if (typeof window !== 'undefined') {
      localStorage.removeItem(SETTINGS_KEY);
      localStorage.removeItem(CONFIG_KEY);
      localStorage.removeItem(SETUP_COMPLETE_KEY);
    }
    setIsSetupComplete(false);
  }, []);

  return {
    settings,
    config,
    isSetupComplete,
    updateSettings,
    updateConfig,
    completeSetup,
    resetSettings,
    isLoading,
  };
}

/**
 * Get settings server-side (from cookies or defaults)
 */
export function getServerSettings(): RuntimeSettings {
  return DEFAULT_RUNTIME_SETTINGS;
}

/**
 * Apply theme to document
 */
export function applyTheme(config: Partial<CanvasUIConfig>) {
  if (typeof document === 'undefined') return;

  const colors = config.theme?.colors;
  if (!colors) return;

  const root = document.documentElement;
  root.style.setProperty('--canvas-primary', colors.primary);
  if (colors.accent) root.style.setProperty('--canvas-accent', colors.accent);
  root.style.setProperty('--canvas-background', colors.background);
  root.style.setProperty('--canvas-surface', colors.surface);
  root.style.setProperty('--canvas-border', colors.border);
  root.style.setProperty('--canvas-text', colors.text);
  root.style.setProperty('--canvas-muted', colors.muted);

  // Apply mode
  if (config.theme?.mode === 'light') {
    root.classList.remove('dark');
    root.classList.add('light');
  } else {
    root.classList.remove('light');
    root.classList.add('dark');
  }
}
