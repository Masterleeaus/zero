/**
 * E-commerce Product Search Canvas Configuration
 *
 * Configuration for product search and support in e-commerce applications.
 */

import { defineConfig } from '@memvid/canvas-core';

export default defineConfig({
  brand: {
    name: 'Shop Assistant',
    tagline: 'Find products and get help',
    logo: {
      light: '/logo-dark.svg',
      dark: '/logo-light.svg',
    },
    favicon: '/shop-favicon.ico',
    supportEmail: 'support@shop.com',
  },

  theme: {
    mode: 'light',
    colors: {
      primary: '#ea580c',
      accent: '#f97316',
      background: '#ffffff',
      surface: '#f5f5f5',
      border: '#e5e5e5',
      text: '#171717',
      textMuted: '#737373',
      success: '#16a34a',
      error: '#dc2626',
      warning: '#ca8a04',
    },
    fonts: {
      display: 'Poppins',
      body: 'Inter',
      mono: 'JetBrains Mono',
    },
    radius: 'lg',
  },

  layout: {
    sidebar: {
      width: 300,
      position: 'left',
      collapsible: true,
    },
    content: {
      maxWidth: 1100,
      padding: 24,
    },
  },

  features: {
    search: {
      enabled: true,
      modes: ['semantic', 'hybrid'],
      defaultMode: 'hybrid',
      placeholder: 'Search products, guides, FAQs...',
      resultsLimit: 20,
      showScores: false,
    },
    chat: {
      enabled: true,
      welcomeMessage: 'Hi! I\'m your shopping assistant. How can I help you today?',
      placeholder: 'Ask about products, orders, returns...',
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
      { id: 'chat', label: 'Help', icon: 'message-circle', href: '/help' },
    ],
    footer: [
      { label: 'FAQs', href: '/faq' },
      { label: 'Contact Us', href: '/contact' },
      { label: 'Return Policy', href: '/returns' },
    ],
  },

  text: {
    locale: 'en',
    overrides: {
      'search.title': 'Find What You Need',
      'search.emptyStateTitle': 'Search our catalog',
      'search.emptyState': 'Find products, guides, and answers to common questions.',
      'chat.emptyState': 'Need help?',
      'chat.emptyStateDescription': 'Ask me about products, orders, shipping, or returns.',
      'support.welcome': 'Hi! I\'m your shopping assistant. How can I help you today?',
    },
  },

  llm: {
    provider: 'openai',
    model: 'gpt-4o-mini',
    systemPrompt: `You are a helpful e-commerce support assistant. Help customers find products, answer questions about orders, shipping, returns, and provide product recommendations. Be friendly, helpful, and concise. Always cite sources when providing information.`,
    temperature: 0.7,
    maxTokens: 1000,
  },

  memory: {
    path: './data/products.mv2',
  },

  api: {
    basePath: '/api/support',
    timeout: 30000,
  },

  advanced: {
    analytics: {
      enabled: true,
      provider: 'custom',
    },
  },
});

