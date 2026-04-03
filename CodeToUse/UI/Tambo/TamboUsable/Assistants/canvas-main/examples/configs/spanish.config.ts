/**
 * Spanish (Español) Canvas Configuration
 *
 * Example of internationalized configuration in Spanish.
 */

import { defineConfig } from '@memvid/canvas-core';

export default defineConfig({
  brand: {
    name: 'Base de Conocimientos',
    tagline: 'Búsqueda y chat impulsados por IA',
  },

  theme: {
    mode: 'dark',
    colors: {
      primary: '#818cf8',
      accent: '#22c55e',
    },
    radius: 'md',
  },

  layout: {
    sidebar: {
      width: 300,
    },
    content: {
      maxWidth: 900,
    },
  },

  features: {
    search: {
      enabled: true,
      placeholder: 'Buscar documentos, conversaciones...',
    },
    chat: {
      enabled: true,
      welcomeMessage: '¿Cómo puedo ayudarte hoy?',
      placeholder: 'Escribe un mensaje...',
    },
    dashboard: { enabled: true },
    settings: { enabled: true },
    setupWizard: { enabled: true },
  },

  navigation: {
    items: [
      { id: 'search', label: 'Buscar', icon: 'search', href: '/search' },
      { id: 'chat', label: 'Chat', icon: 'message-circle', href: '/chat' },
      { id: 'dashboard', label: 'Panel', icon: 'layout-dashboard', href: '/dashboard' },
      { id: 'settings', label: 'Ajustes', icon: 'settings', href: '/settings' },
    ],
  },

  text: {
    locale: 'es',
    overrides: {
      // Navigation
      'nav.search': 'Buscar',
      'nav.chat': 'Chat',
      'nav.dashboard': 'Panel',
      'nav.settings': 'Ajustes',

      // Search
      'search.title': 'Busca en tu Memoria',
      'search.placeholder': 'Buscar documentos, conversaciones...',
      'search.noResults': 'No se encontraron resultados',
      'search.resultsCount': '{count} resultados',
      'search.loading': 'Buscando...',
      'search.emptyState': 'Ingresa una consulta para buscar en tus documentos.',
      'search.emptyStateTitle': 'Busca en tu memoria',

      // Chat
      'chat.title': 'Chat',
      'chat.placeholder': 'Escribe un mensaje...',
      'chat.welcome': '¿Cómo puedo ayudarte hoy?',
      'chat.typing': 'Escribiendo...',
      'chat.sources': 'Fuentes',
      'chat.emptyState': 'Inicia una conversación',
      'chat.emptyStateDescription': 'Hazme preguntas y te ayudaré a encontrar respuestas.',

      // Dashboard
      'dashboard.title': 'Panel',
      'dashboard.stats.documents': 'Documentos',
      'dashboard.stats.conversations': 'Conversaciones',
      'dashboard.stats.totalSize': 'Tamaño Total',
      'dashboard.recent': 'Actividad Reciente',

      // Settings
      'settings.title': 'Ajustes',
      'settings.llm.provider': 'Proveedor LLM',
      'settings.llm.model': 'Modelo',
      'settings.theme': 'Tema',
      'settings.theme.light': 'Claro',
      'settings.theme.dark': 'Oscuro',
      'settings.theme.system': 'Sistema',
      'settings.save': 'Guardar',
      'settings.cancel': 'Cancelar',

      // Common
      'common.loading': 'Cargando...',
      'common.error': 'Algo salió mal',
      'common.retry': 'Reintentar',
      'common.cancel': 'Cancelar',
      'common.save': 'Guardar',
      'common.delete': 'Eliminar',
      'common.close': 'Cerrar',
    },
  },

  llm: {
    provider: 'openai',
  },

  memory: {
    path: './data/memoria.mv2',
  },
});

