/**
 * Minimal Canvas Configuration
 *
 * The bare minimum configuration to get Canvas running.
 */

import { defineConfig } from '@memvid/canvas-core';

export default defineConfig({
  brand: {
    name: 'My Knowledge Base',
  },

  theme: {
    mode: 'dark',
    colors: {
      primary: '#3b82f6',
    },
  },

  layout: {
    sidebar: {},
    content: {},
  },

  features: {
    search: { enabled: true },
    chat: { enabled: true },
    dashboard: { enabled: false },
    settings: { enabled: true },
    setupWizard: { enabled: true },
  },

  navigation: {
    items: [
      { id: 'search', label: 'Search', icon: 'search', href: '/search' },
      { id: 'chat', label: 'Chat', icon: 'message-circle', href: '/chat' },
    ],
  },

  text: {
    locale: 'en',
  },

  llm: {
    provider: 'openai',
  },

  memory: {
    path: './data/memory.mv2',
  },
});

