/**
 * UI Configuration Types
 *
 * Types for Canvas UI configuration (app branding, theme, features).
 */

/**
 * Full UI configuration for Canvas apps
 */
export interface CanvasUIConfig {
  /** Application metadata */
  app: AppConfig;

  /** Theme and branding */
  theme: ThemeConfig;

  /** Feature toggles and settings */
  features: FeaturesConfig;

  /** Navigation items */
  navigation?: NavigationItem[];

  /** Advanced settings */
  advanced?: AdvancedConfig;
}

export interface AppConfig {
  /** Application name displayed in header/title */
  name: string;
  /** Short description for meta tags */
  description?: string;
  /** Path to logo image */
  logo?: string;
  /** Path to favicon */
  favicon?: string;
}

export interface ThemeConfig {
  /** Color mode */
  mode: 'light' | 'dark' | 'system';
  /** Border radius preset */
  radius?: 'none' | 'sm' | 'md' | 'lg' | 'full';
  /** Color palette */
  colors: ColorPalette;
  /** Custom fonts */
  fonts?: FontConfig;
}

export interface ColorPalette {
  /** Primary brand color */
  primary: string;
  /** Accent/highlight color */
  accent?: string;
  /** Background color */
  background: string;
  /** Surface/card color */
  surface: string;
  /** Border color */
  border: string;
  /** Primary text color */
  text: string;
  /** Muted/secondary text */
  muted: string;
  /** Error color */
  error?: string;
  /** Success color */
  success?: string;
  /** Warning color */
  warning?: string;
}

export interface FontConfig {
  /** Display/heading font family */
  display?: string;
  /** Body text font family */
  body?: string;
  /** Monospace font family */
  mono?: string;
}

export interface FeaturesConfig {
  /** Search feature settings */
  search?: SearchFeatureConfig;
  /** Chat feature settings */
  chat?: ChatFeatureConfig;
  /** Dashboard feature settings */
  dashboard?: DashboardFeatureConfig;
  /** PDF viewer settings */
  pdfViewer?: PDFViewerConfig;
}

export interface SearchFeatureConfig {
  enabled: boolean;
  /** Available search modes */
  modes?: ('semantic' | 'lexical' | 'hybrid')[];
  /** Default search mode */
  defaultMode?: 'semantic' | 'lexical' | 'hybrid';
  /** Show relevance scores */
  showScores?: boolean;
  /** Max results to show */
  limit?: number;
  /** Placeholder text */
  placeholder?: string;
}

export interface ChatFeatureConfig {
  enabled: boolean;
  /** Show source citations */
  showSources?: boolean;
  /** Stream responses */
  streamResponses?: boolean;
  /** System prompt for the assistant */
  systemPrompt?: string;
  /** Welcome message */
  welcomeMessage?: string;
  /** Suggested questions */
  suggestedQuestions?: string[];
}

export interface DashboardFeatureConfig {
  enabled: boolean;
  /** Show memory statistics */
  showStats?: boolean;
  /** Show timeline of recent activity */
  showTimeline?: boolean;
  /** Show recent searches */
  showRecentSearches?: boolean;
}

export interface PDFViewerConfig {
  enabled: boolean;
  /** Show page thumbnails */
  showThumbnails?: boolean;
  /** Default zoom level */
  defaultZoom?: number;
}

export interface NavigationItem {
  /** Unique identifier */
  id: string;
  /** Display label */
  label: string;
  /** Route path */
  href: string;
  /** Icon name (lucide icon) */
  icon: string;
  /** Show in navigation */
  visible?: boolean;
}

export interface AdvancedConfig {
  /** Show setup wizard for unconfigured apps */
  enableSetupWizard?: boolean;
  /** Allow runtime settings changes */
  enableSettingsUI?: boolean;
  /** Base path for API routes */
  apiBasePath?: string;
  /** Enable debug mode */
  debug?: boolean;
}

/**
 * Setup state for the wizard
 */
export interface SetupState {
  step: SetupStep;
  completed: boolean;
  config: Partial<CanvasUIConfig>;
}

export type SetupStep = 'welcome' | 'memory' | 'llm' | 'brand' | 'features' | 'complete';

/**
 * Runtime settings that can be changed without restart
 */
export interface RuntimeSettings {
  llmProvider?: 'anthropic' | 'openai';
  llmModel?: string;
  llmApiKey?: string;
  themeMode?: 'light' | 'dark' | 'system';
  searchMode?: 'semantic' | 'lexical' | 'hybrid';
}

/**
 * Default UI configuration
 */
export const DEFAULT_UI_CONFIG: CanvasUIConfig = {
  app: {
    name: 'Knowledge Base',
    description: 'AI-powered search and chat',
  },
  theme: {
    mode: 'dark',
    radius: 'md',
    colors: {
      primary: '#818cf8',
      accent: '#22c55e',
      background: '#09090b',
      surface: '#18181b',
      border: '#27272a',
      text: '#fafafa',
      muted: '#a1a1aa',
    },
  },
  features: {
    search: { enabled: true, modes: ['semantic', 'lexical', 'hybrid'], defaultMode: 'hybrid', showScores: true, limit: 20 },
    chat: { enabled: true, showSources: true, streamResponses: true },
    dashboard: { enabled: true, showStats: true, showTimeline: true },
    pdfViewer: { enabled: true, showThumbnails: true },
  },
};

/**
 * Default runtime settings
 */
export const DEFAULT_RUNTIME_SETTINGS: RuntimeSettings = {
  llmProvider: 'openai',
  llmModel: 'gpt-4o-mini',
  themeMode: 'dark',
  searchMode: 'hybrid',
};
