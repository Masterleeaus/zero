/**
 * Theme Application System
 *
 * Maps unified CanvasConfig to CSS variables for dynamic theming.
 */

import type { CanvasConfig } from '@memvid/canvas-core/config/client';

/**
 * CSS variable mapping from config paths to CSS variable names
 */
const CSS_VAR_MAP: Record<string, string> = {
  // Colors
  'colors.primary': '--canvas-primary',
  'colors.accent': '--canvas-accent',
  'colors.background': '--canvas-bg',
  'colors.surface': '--canvas-surface',
  'colors.border': '--canvas-border',
  'colors.text': '--canvas-text',
  'colors.textMuted': '--canvas-text-muted',
  'colors.success': '--canvas-success',
  'colors.error': '--canvas-error',
  'colors.warning': '--canvas-warning',

  // Fonts
  'fonts.display': '--canvas-font-display',
  'fonts.body': '--canvas-font-body',
  'fonts.mono': '--canvas-font-mono',

  // Layout
  'layout.sidebar.width': '--canvas-sidebar-width',
  'layout.content.maxWidth': '--canvas-content-max-width',
  'layout.content.padding': '--canvas-content-padding',
  'layout.header.height': '--canvas-header-height',

  // Radius
  'radius': '--canvas-radius',
};

/**
 * Border radius preset mapping
 */
const RADIUS_MAP: Record<string, string> = {
  none: '0',
  sm: '4px',
  md: '8px',
  lg: '16px',
  full: '9999px',
};

/**
 * Apply theme from config to document root
 *
 * @param config - Canvas configuration
 */
export function applyTheme(config: CanvasConfig): void {
  if (typeof document === 'undefined') {
    return; // Server-side rendering
  }

  const root = document.documentElement;

  // Apply color palette
  const colors = config.theme.colors;
  if (colors.primary) {
    root.style.setProperty('--canvas-primary', colors.primary);
  }
  if (colors.accent) {
    root.style.setProperty('--canvas-accent', colors.accent);
  }
  if (colors.background) {
    root.style.setProperty('--canvas-bg', colors.background);
  }
  if (colors.surface) {
    root.style.setProperty('--canvas-surface', colors.surface);
  }
  if (colors.border) {
    root.style.setProperty('--canvas-border', colors.border);
  }
  if (colors.text) {
    root.style.setProperty('--canvas-text', colors.text);
  }
  if (colors.textMuted) {
    root.style.setProperty('--canvas-text-muted', colors.textMuted);
  }
  if (colors.success) {
    root.style.setProperty('--canvas-success', colors.success);
  }
  if (colors.error) {
    root.style.setProperty('--canvas-error', colors.error);
  }
  if (colors.warning) {
    root.style.setProperty('--canvas-warning', colors.warning);
  }

  // Apply fonts
  const fonts = config.theme.fonts;
  if (fonts?.display) {
    root.style.setProperty('--canvas-font-display', `'${fonts.display}', sans-serif`);
  }
  if (fonts?.body) {
    root.style.setProperty('--canvas-font-body', `'${fonts.body}', sans-serif`);
  }
  if (fonts?.mono) {
    root.style.setProperty('--canvas-font-mono', `'${fonts.mono}', monospace`);
  }

  // Apply layout
  if (config.layout.sidebar.width) {
    root.style.setProperty('--canvas-sidebar-width', `${config.layout.sidebar.width}px`);
  }
  if (config.layout.content.maxWidth) {
    const maxWidth = config.layout.content.maxWidth === 'full' 
      ? '100%' 
      : `${config.layout.content.maxWidth}px`;
    root.style.setProperty('--canvas-content-max-width', maxWidth);
  }
  if (config.layout.content.padding) {
    root.style.setProperty('--canvas-content-padding', `${config.layout.content.padding}px`);
  }
  if (config.layout.header?.height) {
    root.style.setProperty('--canvas-header-height', `${config.layout.header.height}px`);
  }

  // Apply radius preset
  if (config.theme.radius) {
    const radiusValue = RADIUS_MAP[config.theme.radius] || RADIUS_MAP.md;
    root.style.setProperty('--canvas-radius', radiusValue);
  }

  // Apply custom CSS if provided
  if (config.advanced?.customCss) {
    injectCustomCSS(config.advanced.customCss);
  }
}

/**
 * Inject custom CSS into the document
 */
function injectCustomCSS(css: string): void {
  if (typeof document === 'undefined') {
    return;
  }

  const styleId = 'canvas-custom-css';
  let styleElement = document.getElementById(styleId) as HTMLStyleElement;

  if (!styleElement) {
    styleElement = document.createElement('style');
    styleElement.id = styleId;
    document.head.appendChild(styleElement);
  }

  styleElement.textContent = css;
}

/**
 * React hook to apply theme from config
 */
export function useApplyTheme(config: CanvasConfig): void {
  if (typeof window === 'undefined') {
    return;
  }

  // Apply theme on mount and when config changes
  if (typeof window !== 'undefined') {
    applyTheme(config);
  }
}

