/**
 * Settings Management
 *
 * Handles reading/writing Canvas settings and environment configuration.
 */

import * as fs from 'fs';
import * as path from 'path';
import * as os from 'os';

/**
 * Runtime settings (stored in .canvas-settings.json)
 */
export interface CanvasSettings {
  /** Path to memory file */
  memoryPath: string;
  /** LLM provider */
  llmProvider?: 'openai' | 'anthropic';
  /** LLM model */
  llmModel?: string;
  /** App name */
  appName?: string;
  /** Theme mode */
  themeMode?: 'light' | 'dark';
  /** Primary color */
  primaryColor?: string;
  /** Enabled features */
  features?: {
    search?: boolean;
    chat?: boolean;
    dashboard?: boolean;
    pdfViewer?: boolean;
  };
  /** Setup completed */
  setupCompleted?: boolean;
  /** Last updated */
  updatedAt?: string;
}

/**
 * Environment configuration
 */
export interface CanvasEnv {
  /** OpenAI API key */
  openaiApiKey?: string;
  /** Anthropic API key */
  anthropicApiKey?: string;
  /** Memvid API key */
  memvidApiKey?: string;
  /** Custom OpenAI base URL */
  openaiBaseUrl?: string;
}

const DEFAULT_SETTINGS_FILE = '.canvas-settings.json';

/**
 * Resolve path with home directory expansion
 */
export function resolvePath(filePath: string, basePath?: string): string {
  if (filePath.startsWith('~/') || filePath.startsWith('~\\')) {
    return path.join(os.homedir(), filePath.slice(2));
  }
  if (path.isAbsolute(filePath)) {
    return filePath;
  }
  return path.join(basePath || process.cwd(), filePath);
}

/**
 * Get settings file path
 */
export function getSettingsPath(basePath?: string): string {
  return path.join(basePath || process.cwd(), DEFAULT_SETTINGS_FILE);
}

/**
 * Load settings from file
 */
export function loadSettings(basePath?: string): CanvasSettings | null {
  const settingsPath = getSettingsPath(basePath);

  try {
    if (fs.existsSync(settingsPath)) {
      const data = fs.readFileSync(settingsPath, 'utf-8');
      return JSON.parse(data) as CanvasSettings;
    }
  } catch (error) {
    console.error('[Canvas] Failed to load settings:', error);
  }

  return null;
}

/**
 * Save settings to file
 */
export function saveSettings(settings: CanvasSettings, basePath?: string): void {
  const settingsPath = getSettingsPath(basePath);

  try {
    const data = JSON.stringify(
      {
        ...settings,
        updatedAt: new Date().toISOString(),
      },
      null,
      2
    );
    fs.writeFileSync(settingsPath, data, 'utf-8');
  } catch (error) {
    console.error('[Canvas] Failed to save settings:', error);
    throw new Error(`Failed to save settings: ${error instanceof Error ? error.message : 'Unknown error'}`);
  }
}

/**
 * Check if setup has been completed
 */
export function isSetupCompleted(basePath?: string): boolean {
  const settings = loadSettings(basePath);
  return settings?.setupCompleted === true;
}

/**
 * Get environment configuration
 */
export function getEnvConfig(): CanvasEnv {
  return {
    openaiApiKey: process.env.OPENAI_API_KEY,
    anthropicApiKey: process.env.ANTHROPIC_API_KEY,
    memvidApiKey: process.env.MEMVID_API_KEY,
    openaiBaseUrl: process.env.OPENAI_BASE_URL,
  };
}

/**
 * Get effective API key for a provider
 */
export function getApiKey(provider: 'openai' | 'anthropic'): string | undefined {
  const env = getEnvConfig();
  return provider === 'openai' ? env.openaiApiKey : env.anthropicApiKey;
}

/**
 * Get embedding API key (defaults to OpenAI)
 */
export function getEmbeddingApiKey(): string | undefined {
  return process.env.OPENAI_API_KEY;
}

/**
 * Validate environment has required keys
 */
export function validateEnv(provider?: 'openai' | 'anthropic'): { valid: boolean; missing: string[] } {
  const missing: string[] = [];
  const env = getEnvConfig();

  // Check for at least one LLM key
  if (!env.openaiApiKey && !env.anthropicApiKey) {
    missing.push('OPENAI_API_KEY or ANTHROPIC_API_KEY');
  }

  // Check specific provider if requested
  if (provider === 'openai' && !env.openaiApiKey) {
    missing.push('OPENAI_API_KEY');
  }
  if (provider === 'anthropic' && !env.anthropicApiKey) {
    missing.push('ANTHROPIC_API_KEY');
  }

  // OpenAI key is required for embeddings
  if (!env.openaiApiKey) {
    missing.push('OPENAI_API_KEY (required for embeddings)');
  }

  return {
    valid: missing.length === 0,
    missing,
  };
}

/**
 * Merge settings with defaults
 */
export function mergeSettings(
  settings: Partial<CanvasSettings>,
  defaults: Partial<CanvasSettings>
): CanvasSettings {
  return {
    memoryPath: settings.memoryPath || defaults.memoryPath || './data/memory.mv2',
    llmProvider: settings.llmProvider || defaults.llmProvider || 'openai',
    llmModel: settings.llmModel || defaults.llmModel,
    appName: settings.appName || defaults.appName || 'Canvas',
    themeMode: settings.themeMode || defaults.themeMode || 'dark',
    primaryColor: settings.primaryColor || defaults.primaryColor || '#6366f1',
    features: {
      search: settings.features?.search ?? defaults.features?.search ?? true,
      chat: settings.features?.chat ?? defaults.features?.chat ?? true,
      dashboard: settings.features?.dashboard ?? defaults.features?.dashboard ?? true,
      pdfViewer: settings.features?.pdfViewer ?? defaults.features?.pdfViewer ?? true,
    },
    setupCompleted: settings.setupCompleted ?? defaults.setupCompleted ?? false,
  };
}

/**
 * Create default settings
 */
export function createDefaultSettings(): CanvasSettings {
  return mergeSettings({}, {});
}
