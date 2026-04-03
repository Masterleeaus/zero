/**
 * Canvas Templates
 *
 * Pre-built, production-ready templates for common use cases.
 * Each template is fully customizable via brand.json configuration.
 */

export { App } from './app.js';
export { Dashboard } from './dashboard.js';
export { Search } from './search.js';
export { Support } from './support.js';

// Re-export types for convenience
export type {
  AppSlots,
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
} from '../types/brand.js';
