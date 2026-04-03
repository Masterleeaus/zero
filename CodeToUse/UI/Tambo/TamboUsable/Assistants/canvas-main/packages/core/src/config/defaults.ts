/**
 * Default Canvas Configuration
 *
 * Single source of truth for all default configuration values.
 */

import type { CanvasConfig } from './schema.js';

/**
 * Default Canvas configuration
 */
export const DEFAULT_CANVAS_CONFIG: CanvasConfig = {
  // === BRAND ===
  brand: {
    name: 'Knowledge Base',
    tagline: 'AI-powered search and chat',
  },

  // === THEME ===
  theme: {
    mode: 'dark',
    colors: {
      primary: '#818cf8',
      accent: '#22c55e',
      background: '#09090b',
      surface: '#18181b',
      border: '#27272a',
      text: '#fafafa',
      textMuted: '#a1a1aa',
      success: '#22c55e',
      error: '#ef4444',
      warning: '#f59e0b',
    },
    fonts: {
      display: 'Inter',
      body: 'Inter',
      mono: 'JetBrains Mono',
    },
    radius: 'md',
  },

  // === LAYOUT ===
  layout: {
    sidebar: {
      width: 280,
      position: 'left',
      collapsible: true,
      defaultCollapsed: false,
    },
    content: {
      maxWidth: 900,
      padding: 24,
    },
    header: {
      enabled: false,
      sticky: false,
      height: 64,
    },
  },

  // === FEATURES ===
  features: {
    search: {
      enabled: true,
      modes: ['semantic', 'lexical', 'hybrid'],
      defaultMode: 'hybrid',
      placeholder: 'Search documents, conversations...',
      resultsLimit: 20,
      showScores: true,
    },
    chat: {
      enabled: true,
      welcomeMessage: 'How can I help you today?',
      placeholder: 'Type a message...',
      showSources: true,
      streaming: true,
    },
    dashboard: {
      enabled: true,
      widgets: ['stats', 'recent', 'popular'],
      refreshInterval: 30000,
    },
    settings: {
      enabled: true,
      allowProviderSwitch: true,
      allowModelSwitch: true,
      allowThemeSwitch: true,
    },
    setupWizard: {
      enabled: true,
      steps: ['memory', 'llm', 'brand', 'features'],
    },
  },

  // === NAVIGATION ===
  navigation: {
    items: [
      {
        id: 'search',
        label: 'Search',
        icon: 'search',
        href: '/search',
      },
      {
        id: 'chat',
        label: 'Chat',
        icon: 'message-circle',
        href: '/chat',
      },
      {
        id: 'dashboard',
        label: 'Dashboard',
        icon: 'layout-dashboard',
        href: '/dashboard',
      },
      {
        id: 'settings',
        label: 'Settings',
        icon: 'settings',
        href: '/settings',
      },
    ],
  },

  // === TEXT/I18N ===
  text: {
    locale: 'en',
    overrides: {},
  },

  // === LLM ===
  llm: {
    provider: 'openai',
    model: 'gpt-4o-mini',
    temperature: 0.7,
    maxTokens: 2000,
  },

  // === MEMORY ===
  memory: {
    path: './data/memory.mv2',
    autoCreate: true,
  },

  // === API ===
  api: {
    basePath: '/api/canvas',
    timeout: 30000,
    cors: true,
  },

  // === ADVANCED ===
  advanced: {
    debug: false,
  },
};

/**
 * Deep merge utility for Canvas configs
 */
export function mergeCanvasConfig(
  defaults: CanvasConfig,
  overrides: Partial<CanvasConfig>
): CanvasConfig {
  const result = { ...defaults };

  // Deep merge nested objects
  if (overrides.brand) {
    result.brand = { ...defaults.brand, ...overrides.brand };
  }

  if (overrides.theme) {
    result.theme = {
      ...defaults.theme,
      ...overrides.theme,
      colors: {
        ...defaults.theme.colors,
        ...overrides.theme.colors,
      },
      fonts: {
        ...defaults.theme.fonts,
        ...overrides.theme.fonts,
      },
    };
  }

  if (overrides.layout) {
    result.layout = {
      ...defaults.layout,
      ...overrides.layout,
      sidebar: {
        ...defaults.layout.sidebar,
        ...overrides.layout.sidebar,
      },
      content: {
        ...defaults.layout.content,
        ...overrides.layout.content,
      },
      header: {
        ...defaults.layout.header,
        ...overrides.layout.header,
      },
    };
  }

  if (overrides.features) {
    result.features = {
      ...defaults.features,
      ...overrides.features,
      search: {
        ...defaults.features.search,
        ...overrides.features.search,
      },
      chat: {
        ...defaults.features.chat,
        ...overrides.features.chat,
      },
      dashboard: {
        ...defaults.features.dashboard,
        ...overrides.features.dashboard,
      },
      settings: {
        ...defaults.features.settings,
        ...overrides.features.settings,
      },
      setupWizard: {
        ...defaults.features.setupWizard,
        ...overrides.features.setupWizard,
      },
    };
  }

  if (overrides.navigation) {
    result.navigation = {
      ...defaults.navigation,
      ...overrides.navigation,
    };
  }

  if (overrides.text) {
    result.text = {
      ...defaults.text,
      ...overrides.text,
      overrides: {
        ...defaults.text.overrides,
        ...overrides.text.overrides,
      },
    };
  }

  if (overrides.llm) {
    result.llm = { ...defaults.llm, ...overrides.llm };
  }

  if (overrides.memory) {
    result.memory = { ...defaults.memory, ...overrides.memory };
  }

  if (overrides.api) {
    result.api = { ...defaults.api, ...overrides.api };
  }

  if (overrides.advanced) {
    result.advanced = { ...defaults.advanced, ...overrides.advanced };
  }

  // Merge top-level legacy fields
  if (overrides.embedding) {
    result.embedding = overrides.embedding;
  }
  if (overrides.agents) {
    result.agents = overrides.agents;
  }
  if (overrides.defaultAgent) {
    result.defaultAgent = overrides.defaultAgent;
  }
  if (overrides.memvidApiKey) {
    result.memvidApiKey = overrides.memvidApiKey;
  }
  if (overrides.logger) {
    result.logger = overrides.logger;
  }
  if (overrides.timeout !== undefined) {
    result.timeout = overrides.timeout;
  }
  if (overrides.retry) {
    result.retry = { ...defaults.retry, ...overrides.retry };
  }

  return result;
}

/**
 * Get default retry config
 */
export function getDefaultRetryConfig(): CanvasConfig['retry'] {
  return {
    maxRetries: 3,
    initialDelay: 1000,
    maxDelay: 30000,
    backoffMultiplier: 2,
  };
}

