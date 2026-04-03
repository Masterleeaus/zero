/**
 * Client-Safe Configuration Exports
 *
 * This file exports only client-safe config utilities (types, defaults, validator).
 * It does NOT export loader functions that use Node.js fs module.
 */

import type { PartialCanvasConfig as PartialConfig } from './schema.js';

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
  validateConfig,
  type ValidationResult,
} from './validator.js';

/**
 * Client-safe defineConfig helper
 * (Just returns the config as-is, no file loading)
 */
export function defineConfig(config: PartialConfig): PartialConfig {
  return config;
}

