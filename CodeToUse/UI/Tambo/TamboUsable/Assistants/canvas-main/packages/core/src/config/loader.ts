/**
 * Canvas Configuration Loader
 *
 * Loads configuration from multiple file formats:
 * - canvas.config.ts (recommended, type-safe)
 * - canvas.config.js
 * - canvas.config.json
 * - .canvasrc (JSON)
 */

import * as fs from 'fs';
import * as path from 'path';
import { pathToFileURL } from 'url';
import type { CanvasConfig, PartialCanvasConfig } from './schema.js';
import { DEFAULT_CANVAS_CONFIG, mergeCanvasConfig } from './defaults.js';
import { validateConfig as validateConfigImpl } from './validator.js';

/**
 * Configuration file paths to check (in order of priority)
 */
const CONFIG_PATHS = [
  'canvas.config.ts',
  'canvas.config.js',
  'canvas.config.json',
  '.canvasrc',
];

/**
 * Check if a file exists
 */
function fileExists(filePath: string): boolean {
  try {
    return fs.existsSync(filePath) && fs.statSync(filePath).isFile();
  } catch {
    return false;
  }
}

/**
 * Resolve config file path relative to a directory
 */
function resolveConfigPath(dir: string, configPath: string): string {
  return path.resolve(dir, configPath);
}

/**
 * Load JSON config file
 */
function loadJsonConfig(filePath: string): PartialCanvasConfig {
  try {
    const content = fs.readFileSync(filePath, 'utf-8');
    return JSON.parse(content);
  } catch (error) {
    throw new Error(`Failed to parse JSON config at ${filePath}: ${error}`);
  }
}

/**
 * Load JavaScript/TypeScript config file
 *
 * Note: This is a simplified version. In a real implementation,
 * you might want to use dynamic imports or a bundler to handle .ts files.
 * For now, we'll focus on .js and .json files.
 */
async function loadJsConfig(filePath: string): Promise<PartialCanvasConfig> {
  try {
    // For .ts files, we'd need to compile them first or use a bundler
    // For now, we'll support .js files via dynamic import
    if (filePath.endsWith('.ts')) {
      // TypeScript files need to be compiled first
      // In a real implementation, you might use ts-node or similar
      throw new Error(
        'TypeScript config files (.ts) need to be compiled. Use .js or .json instead, or compile your .ts file first.'
      );
    }

    // Dynamic import for .js files
    // Tell bundlers (Next/Webpack) not to try to resolve `file://` at build time.
    const module = await import(/* webpackIgnore: true */ pathToFileURL(filePath).href);
    return module.default || module;
  } catch (error) {
    throw new Error(`Failed to load JS config at ${filePath}: ${error}`);
  }
}

/**
 * Find and load configuration file
 *
 * @param cwd - Working directory to search from (defaults to process.cwd())
 * @returns Loaded and merged configuration
 */
export async function loadCanvasConfig(
  cwd: string = process.cwd()
): Promise<CanvasConfig> {
  // Try each config path
  for (const configPath of CONFIG_PATHS) {
    const fullPath = resolveConfigPath(cwd, configPath);

    if (!fileExists(fullPath)) {
      continue;
    }

    try {
      let partialConfig: PartialCanvasConfig;

      if (configPath.endsWith('.json') || configPath === '.canvasrc') {
        // Load JSON config
        partialConfig = loadJsonConfig(fullPath);
      } else if (configPath.endsWith('.js') || configPath.endsWith('.ts')) {
        // Load JS/TS config
        partialConfig = await loadJsConfig(fullPath);
      } else {
        continue;
      }

      // Merge with defaults
      return mergeCanvasConfig(DEFAULT_CANVAS_CONFIG, partialConfig);
    } catch (error) {
      console.warn(
        `Failed to load config from ${configPath}:`,
        error instanceof Error ? error.message : error
      );
      // Continue to next config file
      continue;
    }
  }

  // No config file found, return defaults
  return DEFAULT_CANVAS_CONFIG;
}

/**
 * Load configuration synchronously (for JSON files only)
 *
 * @param cwd - Working directory to search from
 * @returns Loaded and merged configuration
 */
export function loadCanvasConfigSync(
  cwd: string = process.cwd()
): CanvasConfig {
  // Try each config path
  for (const configPath of CONFIG_PATHS) {
    const fullPath = resolveConfigPath(cwd, configPath);

    if (!fileExists(fullPath)) {
      continue;
    }

    try {
      let partialConfig: PartialCanvasConfig;

      if (configPath.endsWith('.json') || configPath === '.canvasrc') {
        // Load JSON config
        partialConfig = loadJsonConfig(fullPath);
      } else {
        // Skip .js/.ts files in sync mode
        continue;
      }

      // Merge with defaults
      return mergeCanvasConfig(DEFAULT_CANVAS_CONFIG, partialConfig);
    } catch (error) {
      console.warn(
        `Failed to load config from ${configPath}:`,
        error instanceof Error ? error.message : error
      );
      // Continue to next config file
      continue;
    }
  }

  // No config file found, return defaults
  return DEFAULT_CANVAS_CONFIG;
}

/**
 * Validate configuration
 *
 * @param config - Configuration to validate
 * @returns Validation result with errors and warnings
 */
export function validateConfig(config: PartialCanvasConfig): {
  valid: boolean;
  errors: string[];
  warnings: string[];
} {
  return validateConfigImpl(config);
}

/**
 * Helper to create a config file from a partial config
 */
export function defineConfig(
  config: PartialCanvasConfig
): PartialCanvasConfig {
  return config;
}

