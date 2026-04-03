/**
 * Canvas Templates - Client-only exports
 *
 * These templates are designed for browser use only.
 * They don't import any Node.js native modules.
 */

'use client';

// Individual exports
export { App, usePreferences } from './templates/app.js';
export { Dashboard, BarChart } from './templates/dashboard.js';
export { Search, useDebounce } from './templates/search.js';
export { Support } from './templates/support.js';

// Namespace export for Canvas.App, Canvas.Search, etc.
import { App, usePreferences } from './templates/app.js';
import { Dashboard, BarChart } from './templates/dashboard.js';
import { Search, useDebounce } from './templates/search.js';
import { Support } from './templates/support.js';

export const Canvas = {
  App,
  Dashboard,
  Search,
  Support,
  BarChart,
  usePreferences,
  useDebounce,
};

// Types
export type {
  BrandConfig,
  BrandColors,
  BrandTypography,
  CanvasConfig,
  CanvasProps,
  DashboardConfig,
  DashboardWidget,
  FeatureFlags,
  I18nConfig,
  NavItem,
  SearchConfig,
  SupportConfig,
} from './types/brand.js';

// Styles are imported via CSS
// Import '@memvid/canvas-react/styles/canvas.css' in your app
