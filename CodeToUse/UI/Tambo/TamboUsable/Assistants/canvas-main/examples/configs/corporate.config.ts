/**
 * Corporate Canvas Configuration
 *
 * Enterprise-ready configuration with professional styling.
 */

import { defineConfig } from '@memvid/canvas-core';

export default defineConfig({
  brand: {
    name: 'Acme Knowledge Hub',
    tagline: 'Enterprise Knowledge Management',
    logo: '/logo.svg',
    favicon: '/favicon.ico',
    supportEmail: 'support@acme.com',
  },

  theme: {
    mode: 'light',
    preset: 'corporate',
    colors: {
      primary: '#1e40af',
      accent: '#3b82f6',
      background: '#ffffff',
      surface: '#f8fafc',
      border: '#e2e8f0',
      text: '#0f172a',
      textMuted: '#64748b',
      success: '#059669',
      error: '#dc2626',
      warning: '#d97706',
    },
    fonts: {
      display: 'Inter',
      body: 'Inter',
      mono: 'JetBrains Mono',
    },
    radius: 'sm',
  },

  layout: {
    sidebar: {
      width: 280,
      position: 'left',
      collapsible: true,
    },
    content: {
      maxWidth: 1000,
      padding: 32,
    },
    header: {
      enabled: true,
      sticky: true,
      height: 64,
    },
  },

  features: {
    search: {
      enabled: true,
      modes: ['semantic', 'hybrid'],
      defaultMode: 'hybrid',
      placeholder: 'Search knowledge base...',
      resultsLimit: 25,
      showScores: false,
    },
    chat: {
      enabled: true,
      welcomeMessage: 'Welcome to Acme Knowledge Hub. How can I assist you today?',
      placeholder: 'Ask a question...',
      showSources: true,
      streaming: true,
    },
    dashboard: {
      enabled: true,
      widgets: ['stats', 'recent'],
      refreshInterval: 60000,
    },
    settings: {
      enabled: true,
      allowProviderSwitch: false,
      allowModelSwitch: true,
      allowThemeSwitch: true,
    },
    setupWizard: {
      enabled: false,
    },
  },

  navigation: {
    items: [
      { id: 'search', label: 'Search', icon: 'search', href: '/search' },
      { id: 'chat', label: 'Ask AI', icon: 'message-circle', href: '/chat' },
      { id: 'dashboard', label: 'Analytics', icon: 'layout-dashboard', href: '/dashboard' },
      { id: 'settings', label: 'Settings', icon: 'settings', href: '/settings' },
    ],
    footer: [
      { label: 'Help Center', href: 'https://help.acme.com', external: true },
      { label: 'Contact Support', href: 'mailto:support@acme.com', external: true },
    ],
  },

  text: {
    locale: 'en',
    overrides: {
      'nav.search': 'Search',
      'nav.chat': 'Ask AI',
      'nav.dashboard': 'Analytics',
      'search.title': 'Search Knowledge Base',
      'chat.welcome': 'Welcome to Acme Knowledge Hub. How can I assist you today?',
    },
  },

  llm: {
    provider: 'openai',
    model: 'gpt-4o',
    temperature: 0.5,
    maxTokens: 2000,
  },

  memory: {
    path: './data/knowledge.mv2',
    autoCreate: true,
  },

  api: {
    basePath: '/api/knowledge',
    timeout: 60000,
    cors: true,
  },

  advanced: {
    debug: false,
    analytics: {
      enabled: true,
      provider: 'custom',
    },
  },
});

