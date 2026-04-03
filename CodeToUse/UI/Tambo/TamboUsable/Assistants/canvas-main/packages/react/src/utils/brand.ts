/**
 * Brand Utilities
 *
 * Utilities for applying brand configuration to the app.
 */

import type { CanvasConfig } from '@memvid/canvas-core/config/client';
import { injectFavicon, updatePageTitle, injectCustomHead } from './favicon.js';
import { loadFonts } from './fonts.js';
import { applyTheme } from '../theme/apply-theme.js';

/**
 * Apply all brand configuration to the document
 *
 * This function applies:
 * - Theme (colors, fonts, layout)
 * - Favicon
 * - Page title
 * - Custom fonts
 * - Custom head content
 *
 * @param config - Canvas configuration
 */
export function applyBrand(config: CanvasConfig): void {
  if (typeof document === 'undefined') {
    return; // Server-side rendering
  }

  // Apply theme (CSS variables)
  applyTheme(config);

  // Load custom fonts
  loadFonts(config);

  // Inject favicon
  injectFavicon(config);

  // Update page title
  updatePageTitle(config);

  // Inject custom head content
  injectCustomHead(config);
}

/**
 * React hook to apply brand on mount
 */
export function useApplyBrand(config: CanvasConfig): void {
  if (typeof window === 'undefined') {
    return;
  }

  // Apply brand on mount
  applyBrand(config);
}

