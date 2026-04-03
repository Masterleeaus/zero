/**
 * Brand Configuration Types
 *
 * Defines the shape of brand.json for complete customization.
 * Designed for scalability - supports millions of deployments with unique branding.
 */

/**
 * Color palette configuration
 */
export interface BrandColors {
  /** Primary brand color (buttons, links, accents) */
  primary: string;
  /** Secondary accent color */
  accent?: string;
  /** Success state color */
  success?: string;
  /** Warning state color */
  warning?: string;
  /** Error/danger state color */
  error?: string;
  /** Background colors */
  background?: {
    primary?: string;
    secondary?: string;
    tertiary?: string;
  };
  /** Text colors */
  text?: {
    primary?: string;
    secondary?: string;
    muted?: string;
  };
}

/**
 * Typography configuration
 */
export interface BrandTypography {
  /** Primary font family */
  fontFamily?: string;
  /** Monospace font for code */
  fontFamilyMono?: string;
  /** Base font size */
  fontSize?: string;
  /** Font weights */
  fontWeight?: {
    normal?: number;
    medium?: number;
    semibold?: number;
    bold?: number;
  };
}

/**
 * Support template configuration
 */
export interface SupportConfig {
  /** Welcome message shown to users */
  welcomeMessage?: string;
  /** Quick action suggestions */
  suggestions?: string[];
  /** Enable ticket creation */
  enableTickets?: boolean;
  /** Enable live chat handoff */
  enableLiveChat?: boolean;
  /** Custom agent name */
  agentName?: string;
  /** Agent avatar URL */
  agentAvatar?: string;
  /** Placeholder text for input */
  inputPlaceholder?: string;
  /** Show source citations */
  showSources?: boolean;
  /** Enable feedback buttons */
  enableFeedback?: boolean;
  /** Custom escalation message */
  escalationMessage?: string;
  /** Business hours (for availability display) */
  businessHours?: {
    timezone: string;
    hours: { day: number; open: string; close: string }[];
  };
}

/**
 * Dashboard widget configuration
 */
export interface DashboardWidget {
  /** Widget identifier */
  id: string;
  /** Widget type */
  type: 'stats' | 'chart' | 'table' | 'list' | 'custom';
  /** Widget title */
  title: string;
  /** Grid position (for layout) */
  position?: { x: number; y: number; w: number; h: number };
  /** Widget-specific config */
  config?: Record<string, unknown>;
}

/**
 * Dashboard template configuration
 */
export interface DashboardConfig {
  /** Dashboard title */
  title?: string;
  /** Refresh interval in ms (0 = no auto-refresh) */
  refreshInterval?: number;
  /** Widgets to display */
  widgets?: (string | DashboardWidget)[];
  /** Enable date range picker */
  enableDateRange?: boolean;
  /** Enable export functionality */
  enableExport?: boolean;
  /** Default time range */
  defaultTimeRange?: '24h' | '7d' | '30d' | '90d' | 'all';
}

/**
 * Search template configuration
 */
export interface SearchConfig {
  /** Placeholder text */
  placeholder?: string;
  /** Max results to show */
  limit?: number;
  /** Show relevance scores */
  showScores?: boolean;
  /** Search modes available */
  modes?: ('semantic' | 'lexical' | 'hybrid')[];
  /** Default search mode */
  defaultMode?: 'semantic' | 'lexical' | 'hybrid';
  /** Enable filters */
  enableFilters?: boolean;
  /** Filter options */
  filters?: {
    id: string;
    label: string;
    type: 'select' | 'date' | 'tag';
    options?: string[];
  }[];
}

/**
 * Navigation item configuration
 */
export interface NavItem {
  /** Unique identifier */
  id: string;
  /** Display label */
  label: string;
  /** Icon name or URL */
  icon?: string;
  /** Route path */
  path?: string;
  /** Whether this is the default/home route */
  default?: boolean;
  /** Hide from navigation */
  hidden?: boolean;
  /** Badge count (for notifications) */
  badge?: number;
}

/**
 * Feature flags for enabling/disabling functionality
 */
export interface FeatureFlags {
  /** Enable search template */
  search?: boolean;
  /** Enable dashboard template */
  dashboard?: boolean;
  /** Enable support template */
  support?: boolean;
  /** Enable knowledge base */
  knowledgeBase?: boolean;
  /** Enable memory inspector (dev tool) */
  memoryInspector?: boolean;
  /** Enable settings panel */
  settings?: boolean;
  /** Enable dark mode toggle */
  darkMode?: boolean;
  /** Enable multi-language support */
  i18n?: boolean;
}

/**
 * Internationalization configuration
 */
export interface I18nConfig {
  /** Default locale */
  defaultLocale?: string;
  /** Available locales */
  locales?: string[];
  /** Translation overrides */
  translations?: Record<string, Record<string, string>>;
}

/**
 * Analytics configuration
 */
export interface AnalyticsConfig {
  /** Enable analytics */
  enabled?: boolean;
  /** Analytics provider */
  provider?: 'plausible' | 'fathom' | 'google' | 'custom';
  /** Provider-specific config */
  config?: Record<string, unknown>;
  /** Events to track */
  events?: string[];
}

/**
 * Complete Brand Configuration
 *
 * This is the main interface for brand.json
 */
export interface BrandConfig {
  /** Company/app name */
  name: string;

  /** Logo URL (supports SVG, PNG, etc.) */
  logo?: string;

  /** Logo for dark mode (optional) */
  logoDark?: string;

  /** Favicon URL */
  favicon?: string;

  /** Tagline/description */
  tagline?: string;

  /** Theme mode */
  theme?: 'light' | 'dark' | 'system';

  /** Color palette */
  colors?: BrandColors;

  /** Typography settings */
  typography?: BrandTypography;

  /** Feature flags */
  features?: FeatureFlags;

  /** Navigation items */
  navigation?: NavItem[];

  /** Search configuration */
  search?: SearchConfig;

  /** Dashboard configuration */
  dashboard?: DashboardConfig;

  /** Support configuration */
  support?: SupportConfig;

  /** Internationalization */
  i18n?: I18nConfig;

  /** Analytics */
  analytics?: AnalyticsConfig;

  /** Custom CSS variables */
  customCSS?: Record<string, string>;

  /** Custom metadata */
  metadata?: Record<string, unknown>;
}

/**
 * Runtime configuration (from canvas.config.ts)
 */
export interface CanvasConfig {
  /** Memory file path */
  memoryPath: string;

  /** Default template to show */
  defaultTemplate?: 'search' | 'dashboard' | 'support';

  /** LLM configuration */
  llm?: {
    provider: 'anthropic' | 'openai' | 'google';
    model?: string;
  };

  /** Embedding configuration */
  embedding?: {
    provider: 'openai' | 'voyage' | 'cohere';
    model?: string;
  };

  /** API endpoints (for server mode) */
  endpoints?: {
    memory?: string;
    chat?: string;
    search?: string;
  };

  /** Search settings override */
  search?: Partial<SearchConfig>;

  /** Dashboard settings override */
  dashboard?: Partial<DashboardConfig>;

  /** Support settings override */
  support?: Partial<SupportConfig>;
}

/**
 * Component slots for App template customization
 * See slots/types.ts for full slot definitions
 */
export interface AppSlots {
  /** Replace the entire sidebar */
  sidebar?: React.ReactNode | React.ComponentType<{ isCollapsed: boolean }>;
  /** Content at top of sidebar (above nav) */
  sidebarHeader?: React.ReactNode | React.ComponentType<{ isCollapsed: boolean }>;
  /** Content at bottom of sidebar (below nav) */
  sidebarFooter?: React.ReactNode | React.ComponentType<{ isCollapsed: boolean }>;
  /** Custom logo component */
  logo?: React.ReactNode | React.ComponentType<{ brandName: string; logoUrl?: string }>;
  /** Replace search template */
  search?: React.ReactNode | React.ComponentType;
  /** Replace dashboard template */
  dashboard?: React.ReactNode | React.ComponentType;
  /** Replace support template */
  support?: React.ReactNode | React.ComponentType;
  /** Before main content */
  beforeContent?: React.ReactNode;
  /** After main content */
  afterContent?: React.ReactNode;
}

/**
 * Combined props passed to Canvas templates
 */
export interface CanvasProps {
  /** Brand configuration */
  brand: BrandConfig;

  /** Runtime configuration */
  config?: CanvasConfig;

  /** Memory API endpoint */
  memoryEndpoint?: string;

  /** Initial route/template */
  initialRoute?: string;

  /** Callback when route changes */
  onRouteChange?: (route: string) => void;

  /** Callback for analytics events */
  onEvent?: (event: string, data?: Record<string, unknown>) => void;

  /** Component slots for customization */
  slots?: AppSlots;

  /** Custom class name */
  className?: string;

  /** Custom styles */
  style?: React.CSSProperties;
}
