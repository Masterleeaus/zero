/**
 * Unified Canvas Configuration Schema
 *
 * Single source of truth for all Canvas configuration.
 * Supports zero-friction customization via canvas.config.ts
 */

import type { EmbeddingConfig } from "../types/provider.js";
import type { AgentConfig } from "../types/agent.js";

// Note: LLMConfig and MemoryConfig are not used - schema defines inline types

/**
 * Unified Canvas Configuration
 *
 * This is the main configuration interface that controls everything:
 * - Branding (logo, colors, fonts)
 * - Layout (sidebar, content width)
 * - Features (search, chat, dashboard)
 * - Text/i18n (all UI strings)
 * - LLM & Memory settings
 * - API configuration
 */
export interface CanvasConfig {
  // === BRAND ===
  brand: {
    /** Application name */
    name: string;
    /** Tagline/description */
    tagline?: string;
    /** Logo URL or object with light/dark variants */
    logo?: string | { light: string; dark: string };
    /** Favicon URL */
    favicon?: string;
    /** Support email */
    supportEmail?: string;
  };

  // === THEME ===
  theme: {
    /** Color mode */
    mode: "light" | "dark" | "system";
    /** Color palette */
    colors: {
      /** Primary brand color */
      primary: string;
      /** Secondary accent color */
      accent?: string;
      /** Page background */
      background?: string;
      /** Card/panel background */
      surface?: string;
      /** Border color */
      border?: string;
      /** Primary text color */
      text?: string;
      /** Secondary/muted text */
      textMuted?: string;
      /** Success color */
      success?: string;
      /** Error color */
      error?: string;
      /** Warning color */
      warning?: string;
    };
    /** Font families */
    fonts?: {
      /** Display/heading font */
      display?: string;
      /** Body text font */
      body?: string;
      /** Monospace font */
      mono?: string;
    };
    /** Border radius preset */
    radius?: "none" | "sm" | "md" | "lg" | "full";
    /** Theme preset (optional, can override specific colors) */
    preset?: "default" | "ocean" | "forest" | "sunset" | "corporate";
  };

  // === LAYOUT ===
  layout: {
    /** Sidebar configuration */
    sidebar: {
      /** Sidebar width in pixels */
      width?: number;
      /** Sidebar position */
      position?: "left" | "right";
      /** Can sidebar be collapsed */
      collapsible?: boolean;
      /** Default collapsed state */
      defaultCollapsed?: boolean;
    };
    /** Content area configuration */
    content: {
      /** Max content width (pixels or 'full') */
      maxWidth?: number | "full";
      /** Content padding in pixels */
      padding?: number;
    };
    /** Header configuration */
    header?: {
      /** Show header */
      enabled?: boolean;
      /** Sticky header */
      sticky?: boolean;
      /** Header height in pixels */
      height?: number;
    };
  };

  // === FEATURES ===
  features: {
    /** Search feature */
    search: {
      enabled: boolean;
      /** Available search modes */
      modes?: ("semantic" | "lexical" | "hybrid")[];
      /** Default search mode */
      defaultMode?: "semantic" | "lexical" | "hybrid";
      /** Placeholder text */
      placeholder?: string;
      /** Results limit */
      resultsLimit?: number;
      /** Show relevance scores */
      showScores?: boolean;
    };
    /** Chat feature */
    chat: {
      enabled: boolean;
      /** Welcome message */
      welcomeMessage?: string;
      /** Input placeholder */
      placeholder?: string;
      /** Show source citations */
      showSources?: boolean;
      /** Enable streaming */
      streaming?: boolean;
    };
    /** Dashboard feature */
    dashboard: {
      enabled: boolean;
      /** Dashboard widgets */
      widgets?: ("stats" | "recent" | "popular")[];
      /** Refresh interval in ms */
      refreshInterval?: number;
    };
    /** Settings feature */
    settings: {
      enabled: boolean;
      /** Allow provider switch */
      allowProviderSwitch?: boolean;
      /** Allow model switch */
      allowModelSwitch?: boolean;
      /** Allow theme switch */
      allowThemeSwitch?: boolean;
    };
    /** Setup wizard */
    setupWizard: {
      enabled: boolean;
      /** Setup steps to show */
      steps?: ("memory" | "llm" | "brand" | "features")[];
    };
  };

  // === NAVIGATION ===
  navigation: {
    /** Navigation items for sidebar/top nav */
    items: Array<{
      /** Unique identifier for the nav item */
      id: string;
      /** Display label (can be overridden via i18n) */
      label: string;
      /** Icon name (e.g., 'search', 'chat', 'dashboard') */
      icon: string;
      /** Route path or URL */
      href: string;
      /** Optional badge (e.g., notification count) */
      badge?: string | number;
      /** Whether this is an external link */
      external?: boolean;
      /** Mark as default route */
      default?: boolean;
    }>;
    /** Footer links (shown at bottom of sidebar) */
    footer?: Array<{
      /** Link label */
      label: string;
      /** Link URL */
      href: string;
      /** Whether this is an external link */
      external?: boolean;
    }>;
  };

  // === TEXT/I18N ===
  text: {
    /** Locale code (e.g., 'en', 'es', 'fr') - defaults to 'en' */
    locale?: string;
    /**
     * Text overrides (key: value pairs)
     *
     * @example
     * ```ts
     * overrides: {
     *   'search.title': 'Search Your Knowledge',
     *   'chat.welcome': 'How can I help?',
     * }
     * ```
     */
    overrides?: Record<string, string>;
  };

  // === LLM ===
  llm: {
    /** LLM provider */
    provider: "openai" | "anthropic" | "google" | "custom";
    /** Model name */
    model?: string;
    /** API key (or env var reference like '$OPENAI_API_KEY') */
    apiKey?: string;
    /** System prompt */
    systemPrompt?: string;
    /** Temperature (0-1) */
    temperature?: number;
    /** Max tokens */
    maxTokens?: number;
  };

  // === MEMORY ===
  memory: {
    /** Memory file path */
    path: string;
    /** Auto-create if missing */
    autoCreate?: boolean;
  };

  // === API ===
  api?: {
    /** Base path for API routes */
    basePath?: string;
    /** Request timeout in ms */
    timeout?: number;
    /** Enable CORS */
    cors?: boolean;
  };

  // === ADVANCED ===
  advanced?: {
    /** Enable debug mode */
    debug?: boolean;
    /** Additional CSS */
    customCss?: string;
    /** Custom <head> content */
    customHead?: string;
    /** Analytics configuration */
    analytics?: {
      enabled: boolean;
      provider?: string;
      trackingId?: string;
    };
  };

  // === LEGACY SUPPORT ===
  // These fields support backwards compatibility with existing configs
  /** Default template to show (search, dashboard, or support) */
  defaultTemplate?: 'search' | 'dashboard' | 'support';
  /** Embedding configuration (optional, defaults to OpenAI) */
  embedding?: EmbeddingConfig;
  /** Agent configurations */
  agents?: AgentConfig[];
  /** Default agent name */
  defaultAgent?: string;
  /** Memvid API key (for cloud sync) */
  memvidApiKey?: string;
  /** Custom logger */
  logger?: Logger;
  /** Request timeout in ms */
  timeout?: number;
  /** Retry configuration */
  retry?: RetryConfig;
}

/**
 * Logger interface
 */
export interface Logger {
  debug(message: string, data?: Record<string, unknown>): void;
  info(message: string, data?: Record<string, unknown>): void;
  warn(message: string, data?: Record<string, unknown>): void;
  error(message: string, data?: Record<string, unknown>): void;
}

/**
 * Retry configuration
 */
export interface RetryConfig {
  /** Maximum number of retries (default: 3) */
  maxRetries?: number;
  /** Initial delay in ms (default: 1000) */
  initialDelay?: number;
  /** Maximum delay in ms (default: 30000) */
  maxDelay?: number;
  /** Backoff multiplier (default: 2) */
  backoffMultiplier?: number;
  /** Only retry these error codes */
  retryOn?: string[];
}

/**
 * Partial config (for merging with defaults)
 */
export type PartialCanvasConfig = Partial<CanvasConfig> & {
  brand?: Partial<CanvasConfig["brand"]>;
  theme?: Partial<CanvasConfig["theme"]> & {
    colors?: Partial<CanvasConfig["theme"]["colors"]>;
  };
  layout?: Partial<CanvasConfig["layout"]> & {
    sidebar?: Partial<CanvasConfig["layout"]["sidebar"]>;
    content?: Partial<CanvasConfig["layout"]["content"]>;
  };
  features?: Partial<CanvasConfig["features"]> & {
    search?: Partial<CanvasConfig["features"]["search"]>;
    chat?: Partial<CanvasConfig["features"]["chat"]>;
    dashboard?: Partial<CanvasConfig["features"]["dashboard"]>;
    settings?: Partial<CanvasConfig["features"]["settings"]>;
    setupWizard?: Partial<CanvasConfig["features"]["setupWizard"]>;
  };
  navigation?: Partial<CanvasConfig["navigation"]>;
  text?: Partial<CanvasConfig["text"]>;
  llm?: Partial<CanvasConfig["llm"]>;
  /** Memory can be a string path or an object with path and options */
  memory?: string | Partial<CanvasConfig["memory"]>;
};
