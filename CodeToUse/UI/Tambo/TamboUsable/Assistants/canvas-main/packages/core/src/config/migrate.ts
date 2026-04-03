/**
 * Legacy Configuration Migration
 *
 * Migrates existing brand.json and canvas.config.ts to unified config format.
 */

import * as fs from 'fs';
import * as path from 'path';
import { pathToFileURL } from 'url';
import type { PartialCanvasConfig } from './schema.js';

// Import BrandConfig type - we'll define it here to avoid cross-package dependency
interface BrandConfig {
  name: string;
  logo?: string;
  logoDark?: string;
  favicon?: string;
  tagline?: string;
  theme?: 'light' | 'dark' | 'system';
  colors?: {
    primary?: string;
    accent?: string;
    success?: string;
    warning?: string;
    error?: string;
    background?: {
      primary?: string;
      secondary?: string;
      tertiary?: string;
    };
    text?: {
      primary?: string;
      secondary?: string;
      muted?: string;
    };
  };
  typography?: {
    fontFamily?: string;
    fontFamilyMono?: string;
    fontSize?: string;
  };
  features?: {
    search?: boolean;
    dashboard?: boolean;
    support?: boolean;
    settings?: boolean;
  };
  navigation?: Array<{
    id: string;
    label: string;
    icon?: string;
    path?: string;
  }>;
  search?: {
    placeholder?: string;
    limit?: number;
    showScores?: boolean;
    modes?: ('semantic' | 'lexical' | 'hybrid')[];
    defaultMode?: 'semantic' | 'lexical' | 'hybrid';
  };
  support?: {
    welcomeMessage?: string;
    inputPlaceholder?: string;
    showSources?: boolean;
  };
  dashboard?: {
    refreshInterval?: number;
  };
}

/**
 * Migrate brand.json to unified config format
 */
export function migrateBrandJson(
  brandConfig: BrandConfig
): PartialCanvasConfig {
  const config: PartialCanvasConfig = {};

  // Brand
  if (brandConfig.name) {
    config.brand = {
      name: brandConfig.name,
      tagline: brandConfig.tagline,
      logo: brandConfig.logoDark
        ? { light: brandConfig.logo || '', dark: brandConfig.logoDark }
        : brandConfig.logo,
      favicon: brandConfig.favicon,
    };
  }

  // Theme
  if (brandConfig.theme || brandConfig.colors || brandConfig.typography) {
    config.theme = {
      mode: brandConfig.theme || 'system',
      colors: {
        primary: brandConfig.colors?.primary || '#818cf8',
        accent: brandConfig.colors?.accent,
        background: brandConfig.colors?.background?.primary,
        surface: brandConfig.colors?.background?.secondary,
        text: brandConfig.colors?.text?.primary,
        textMuted: brandConfig.colors?.text?.muted,
        success: brandConfig.colors?.success,
        warning: brandConfig.colors?.warning,
        error: brandConfig.colors?.error,
      },
      fonts: {
        display: brandConfig.typography?.fontFamily,
        body: brandConfig.typography?.fontFamily,
        mono: brandConfig.typography?.fontFamilyMono,
      },
    };
  }

  // Features
  if (brandConfig.features) {
    config.features = {
      search: {
        enabled: brandConfig.features.search ?? true,
        placeholder: brandConfig.search?.placeholder,
        resultsLimit: brandConfig.search?.limit,
        showScores: brandConfig.search?.showScores,
        modes: brandConfig.search?.modes,
        defaultMode: brandConfig.search?.defaultMode,
      },
      chat: {
        enabled: brandConfig.features.support ?? true,
        welcomeMessage: brandConfig.support?.welcomeMessage,
        placeholder: brandConfig.support?.inputPlaceholder,
        showSources: brandConfig.support?.showSources,
      },
      dashboard: {
        enabled: brandConfig.features.dashboard ?? true,
        refreshInterval: brandConfig.dashboard?.refreshInterval,
      },
      settings: {
        enabled: brandConfig.features.settings ?? true,
      },
      setupWizard: {
        enabled: true,
      },
    };
  }

  // Navigation
  if (brandConfig.navigation) {
    config.navigation = {
      items: brandConfig.navigation.map((item) => ({
        id: item.id,
        label: item.label,
        icon: item.icon || 'circle',
        href: item.path || `/${item.id}`,
      })),
    };
  }

  // Search config
  if (brandConfig.search) {
    config.features = config.features || ({} as any);
    const features = config.features!;
    features.search = features.search || ({ enabled: true } as any);
    features.search!.placeholder = brandConfig.search.placeholder;
    features.search!.resultsLimit = brandConfig.search.limit;
    features.search!.showScores = brandConfig.search.showScores;
    features.search!.modes = brandConfig.search.modes;
    features.search!.defaultMode = brandConfig.search.defaultMode;
  }

  // Support config
  if (brandConfig.support) {
    config.features = config.features || ({} as any);
    const features = config.features!;
    features.chat = features.chat || ({ enabled: true } as any);
    features.chat!.welcomeMessage = brandConfig.support.welcomeMessage;
    features.chat!.placeholder = brandConfig.support.inputPlaceholder;
    features.chat!.showSources = brandConfig.support.showSources;
  }

  // Dashboard config
  if (brandConfig.dashboard) {
    config.features = config.features || ({} as any);
    const features = config.features!;
    features.dashboard = features.dashboard || ({ enabled: true } as any);
    features.dashboard!.refreshInterval = brandConfig.dashboard.refreshInterval;
  }

  return config;
}

/**
 * Migrate old canvas.config.ts to unified format
 */
export function migrateCanvasConfig(
  oldConfig: any
): PartialCanvasConfig {
  const config: PartialCanvasConfig = {};

  // Memory
  if (oldConfig.memoryPath) {
    config.memory = {
      path: oldConfig.memoryPath,
      autoCreate: true,
    };
  }

  // LLM
  if (oldConfig.llm) {
    config.llm = {
      provider: oldConfig.llm.provider || 'openai',
      model: oldConfig.llm.model,
      apiKey: oldConfig.llm.apiKey,
    };
  }

  // Embedding (legacy support)
  if (oldConfig.embedding) {
    config.embedding = oldConfig.embedding;
  }

  // API endpoints
  if (oldConfig.endpoints) {
    config.api = {
      basePath: oldConfig.endpoints.memory?.replace('/memory', '') || '/api/canvas',
    };
  }

  // Search overrides
  if (oldConfig.search) {
    config.features = config.features || ({} as any);
    config.features!.search = {
      enabled: true,
      ...oldConfig.search,
    } as any;
  }

  // Dashboard overrides
  if (oldConfig.dashboard) {
    config.features = config.features || ({} as any);
    config.features!.dashboard = {
      enabled: true,
      ...oldConfig.dashboard,
    } as any;
  }

  // Support overrides
  if (oldConfig.support) {
    config.features = config.features || ({} as any);
    config.features!.chat = {
      enabled: true,
      ...oldConfig.support,
    } as any;
  }

  return config;
}

/**
 * Migrate legacy config files to unified format
 *
 * @param cwd - Working directory
 * @returns Migrated config or null if no legacy files found
 */
export async function migrateLegacyConfig(
  cwd: string = process.cwd()
): Promise<PartialCanvasConfig | null> {
  const brandJsonPath = path.join(cwd, 'brand.json');
  const canvasConfigPath = path.join(cwd, 'canvas.config.ts');
  const canvasConfigJsPath = path.join(cwd, 'canvas.config.js');

  let migrated: PartialCanvasConfig = {};

  // Migrate brand.json
  if (fs.existsSync(brandJsonPath)) {
    try {
      const brandContent = fs.readFileSync(brandJsonPath, 'utf-8');
      const brandConfig: BrandConfig = JSON.parse(brandContent);
      const brandMigrated = migrateBrandJson(brandConfig);
      migrated = { ...migrated, ...brandMigrated };
    } catch (error) {
      console.warn(`Failed to migrate brand.json: ${error}`);
    }
  }

  // Migrate canvas.config.ts/js
  let canvasConfig: any = null;
  if (fs.existsSync(canvasConfigPath)) {
    try {
      // For .ts files, we'd need to compile or use ts-node
      // For now, we'll just note that it exists
      console.warn(
        'TypeScript config files need manual migration. Please update canvas.config.ts to use the new unified format.'
      );
    } catch (error) {
      console.warn(`Failed to migrate canvas.config.ts: ${error}`);
    }
  } else if (fs.existsSync(canvasConfigJsPath)) {
    try {
      // Tell bundlers (Next/Webpack) not to try to resolve `file://` at build time.
      const module = await import(/* webpackIgnore: true */ pathToFileURL(canvasConfigJsPath).href);
      canvasConfig = module.default || module;
      const canvasMigrated = migrateCanvasConfig(canvasConfig);
      migrated = { ...migrated, ...canvasMigrated };
    } catch (error) {
      console.warn(`Failed to migrate canvas.config.js: ${error}`);
    }
  }

  if (Object.keys(migrated).length === 0) {
    return null;
  }

  return migrated;
}

