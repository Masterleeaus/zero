/**
 * Documentation Site Canvas Configuration
 *
 * Configuration optimized for documentation sites.
 */

import { defineConfig } from '@memvid/canvas-core';

export default defineConfig({
  brand: {
    name: 'Docs',
    tagline: 'Search our documentation',
    logo: '/docs-logo.svg',
    favicon: '/docs-favicon.ico',
  },

  theme: {
    mode: 'system',
    colors: {
      primary: '#6366f1',
      accent: '#8b5cf6',
      background: '#09090b',
      surface: '#18181b',
      border: '#27272a',
      text: '#fafafa',
      textMuted: '#a1a1aa',
    },
    fonts: {
      display: 'Inter',
      body: 'Inter',
      mono: 'Fira Code',
    },
    radius: 'lg',
  },

  layout: {
    sidebar: {
      width: 260,
      collapsible: true,
      defaultCollapsed: false,
    },
    content: {
      maxWidth: 800,
      padding: 24,
    },
  },

  features: {
    search: {
      enabled: true,
      modes: ['semantic', 'lexical'],
      defaultMode: 'semantic',
      placeholder: 'Search docs...',
      resultsLimit: 15,
      showScores: false,
    },
    chat: {
      enabled: true,
      welcomeMessage: 'Ask me anything about the documentation.',
      placeholder: 'Ask a question...',
      showSources: true,
      streaming: true,
    },
    dashboard: {
      enabled: false,
    },
    settings: {
      enabled: true,
      allowThemeSwitch: true,
    },
    setupWizard: {
      enabled: false,
    },
  },

  navigation: {
    items: [
      { id: 'search', label: 'Search', icon: 'search', href: '/' },
      { id: 'chat', label: 'Ask AI', icon: 'sparkles', href: '/ask' },
    ],
    footer: [
      { label: 'GitHub', href: 'https://github.com/example/repo', external: true },
      { label: 'Discord', href: 'https://discord.gg/example', external: true },
    ],
  },

  text: {
    locale: 'en',
    overrides: {
      'search.title': 'Search Documentation',
      'search.emptyStateTitle': 'Search the docs',
      'search.emptyState': 'Find guides, tutorials, and API references.',
      'chat.emptyState': 'Ask AI',
      'chat.emptyStateDescription': 'Get instant answers from our documentation.',
    },
  },

  llm: {
    provider: 'anthropic',
    model: 'claude-sonnet-4-20250514',
    systemPrompt: `You are a helpful documentation assistant. Answer questions based on the provided documentation context. Be concise and accurate. If you don't know the answer, say so.`,
    temperature: 0.3,
  },

  memory: {
    path: './data/docs.mv2',
  },

  advanced: {
    debug: false,
  },
});

