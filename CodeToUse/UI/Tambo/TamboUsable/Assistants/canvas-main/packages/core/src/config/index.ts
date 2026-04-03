/**
 * Canvas Configuration
 *
 * Unified configuration system for Canvas.
 */

export type {
  CanvasConfig,
  PartialCanvasConfig,
  Logger,
  RetryConfig,
} from './schema.js';

export {
  DEFAULT_CANVAS_CONFIG,
  mergeCanvasConfig,
  getDefaultRetryConfig,
} from './defaults.js';

export {
  loadCanvasConfig,
  loadCanvasConfigSync,
  defineConfig,
} from './loader.js';

export {
  validateConfig,
  type ValidationResult,
} from './validator.js';

export { migrateLegacyConfig } from './migrate.js';

