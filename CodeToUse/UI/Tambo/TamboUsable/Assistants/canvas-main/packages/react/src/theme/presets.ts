/**
 * Theme Presets
 *
 * Pre-defined theme configurations for common use cases.
 */

import type { CanvasConfig } from '@memvid/canvas-core/config/client';

/**
 * Default theme preset
 */
export const defaultPreset: Partial<CanvasConfig['theme']> = {
  colors: {
    primary: '#818cf8',
    accent: '#22c55e',
    background: '#09090b',
    surface: '#18181b',
    border: '#27272a',
    text: '#fafafa',
    textMuted: '#a1a1aa',
    success: '#22c55e',
    error: '#ef4444',
    warning: '#f59e0b',
  },
  fonts: {
    display: 'Inter',
    body: 'Inter',
    mono: 'JetBrains Mono',
  },
  radius: 'md',
};

/**
 * Ocean theme preset
 */
export const oceanPreset: Partial<CanvasConfig['theme']> = {
  colors: {
    primary: '#0ea5e9',
    accent: '#06b6d4',
    background: '#0c1929',
    surface: '#1e3a5f',
    border: '#1e40af',
    text: '#e0f2fe',
    textMuted: '#7dd3fc',
    success: '#22d3ee',
    error: '#ef4444',
    warning: '#fbbf24',
  },
  fonts: {
    display: 'Inter',
    body: 'Inter',
    mono: 'JetBrains Mono',
  },
  radius: 'lg',
};

/**
 * Forest theme preset
 */
export const forestPreset: Partial<CanvasConfig['theme']> = {
  colors: {
    primary: '#10b981',
    accent: '#34d399',
    background: '#064e3b',
    surface: '#065f46',
    border: '#047857',
    text: '#d1fae5',
    textMuted: '#6ee7b7',
    success: '#34d399',
    error: '#f87171',
    warning: '#fbbf24',
  },
  fonts: {
    display: 'Inter',
    body: 'Inter',
    mono: 'JetBrains Mono',
  },
  radius: 'md',
};

/**
 * Sunset theme preset
 */
export const sunsetPreset: Partial<CanvasConfig['theme']> = {
  colors: {
    primary: '#f97316',
    accent: '#fb923c',
    background: '#7c2d12',
    surface: '#9a3412',
    border: '#c2410c',
    text: '#fed7aa',
    textMuted: '#fdba74',
    success: '#22c55e',
    error: '#ef4444',
    warning: '#fbbf24',
  },
  fonts: {
    display: 'Inter',
    body: 'Inter',
    mono: 'JetBrains Mono',
  },
  radius: 'lg',
};

/**
 * Corporate theme preset
 */
export const corporatePreset: Partial<CanvasConfig['theme']> = {
  colors: {
    primary: '#1e40af',
    accent: '#3b82f6',
    background: '#ffffff',
    surface: '#f8fafc',
    border: '#e2e8f0',
    text: '#0f172a',
    textMuted: '#64748b',
    success: '#059669',
    error: '#dc2626',
    warning: '#d97706',
  },
  fonts: {
    display: 'Inter',
    body: 'Inter',
    mono: 'JetBrains Mono',
  },
  radius: 'sm',
};

/**
 * All available theme presets
 */
export const themePresets: Record<string, Partial<CanvasConfig['theme']>> = {
  default: defaultPreset,
  ocean: oceanPreset,
  forest: forestPreset,
  sunset: sunsetPreset,
  corporate: corporatePreset,
};

/**
 * Get theme preset by name
 *
 * @param name - Preset name
 * @returns Theme preset or default if not found
 */
export function getThemePreset(name: string): Partial<CanvasConfig['theme']> {
  return themePresets[name] || defaultPreset;
}

/**
 * Apply theme preset to config
 *
 * @param config - Canvas config
 * @param presetName - Preset name
 * @returns Config with preset applied
 */
export function applyThemePreset(
  config: CanvasConfig,
  presetName: string
): CanvasConfig {
  const preset = getThemePreset(presetName);
  
  return {
    ...config,
    theme: {
      ...config.theme,
      ...preset,
      colors: {
        ...config.theme.colors,
        ...preset.colors,
      },
      fonts: {
        ...config.theme.fonts,
        ...preset.fonts,
      },
    },
  };
}

