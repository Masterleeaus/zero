/**
 * Default UI Text Strings
 *
 * All UI text in Canvas is defined here for easy customization and i18n.
 * Users can override any text via config.text.overrides.
 */

export const defaultTexts: Record<string, string> = {
  // === NAVIGATION ===
  'nav.main': 'Main',
  'nav.search': 'Search',
  'nav.chat': 'Chat',
  'nav.dashboard': 'Dashboard',
  'nav.settings': 'Settings',
  'nav.home': 'Home',

  // === SEARCH ===
  'search.title': 'Search Your Memory',
  'search.subtitle': 'Find documents, conversations, and knowledge using semantic, lexical, or hybrid search.',
  'search.placeholder': 'Search documents, conversations...',
  'search.noResults': 'No results found',
  'search.resultsCount': '{count} results',
  'search.loading': 'Searching...',
  'search.emptyState': 'Enter a query to search through your documents and conversations.',
  'search.emptyStateTitle': 'Search your memory',

  // === CHAT ===
  'chat.title': 'Chat',
  'chat.placeholder': 'Type a message...',
  'chat.welcome': 'How can I help you today?',
  'chat.typing': 'Typing...',
  'chat.sources': 'Sources',
  'chat.emptyState': 'Start a conversation',
  'chat.emptyStateDescription': 'Ask me anything and I\'ll help you find answers.',
  'chat.empty.title': 'Start a conversation',
  'chat.empty.description': 'Ask me anything and I\'ll help you find answers.',
  'chat.input.placeholder': 'Type a message...',
  'chat.send': 'Send',
  'chat.clear': 'Clear',
  'chat.stop': 'Stop',

  // === DASHBOARD ===
  'dashboard.title': 'Dashboard',
  'dashboard.stats.documents': 'Documents',
  'dashboard.stats.conversations': 'Conversations',
  'dashboard.stats.totalSize': 'Total Size',
  'dashboard.stats.frames': 'Frames',
  'dashboard.recent': 'Recent Activity',
  'dashboard.popular': 'Popular Searches',
  'dashboard.loading': 'Loading dashboard...',

  // === SETTINGS ===
  'settings.title': 'Settings',
  'settings.llm.provider': 'LLM Provider',
  'settings.llm.model': 'Model',
  'settings.llm.apiKey': 'API Key',
  'settings.theme': 'Theme',
  'settings.theme.light': 'Light',
  'settings.theme.dark': 'Dark',
  'settings.theme.system': 'System',
  'settings.memory.path': 'Memory Path',
  'settings.save': 'Save',
  'settings.cancel': 'Cancel',
  'settings.reset': 'Reset',
  'settings.saved': 'Settings saved',

  // === SETUP WIZARD ===
  'setup.welcome': 'Welcome to Canvas',
  'setup.welcome.description': "Let's configure your AI-powered knowledge base in just a few steps.",
  'setup.reconfigure': 'Reconfigure Canvas',
  'setup.reconfigure.description': 'Update your settings and preferences. Changes will take effect immediately.',
  'setup.step.memory': 'Connect Memory',
  'setup.step.memory.description': 'Link your .mv2 file or create a new one',
  'setup.step.llm': 'Add API Keys',
  'setup.step.llm.description': 'Configure your LLM provider',
  'setup.step.brand': 'Brand Your App',
  'setup.step.brand.description': 'Customize colors, name, and logo',
  'setup.step.features': 'Enable Features',
  'setup.step.features.description': 'Choose search, chat, and more',
  'setup.complete': 'Setup Complete!',
  'setup.complete.description': 'Your Canvas is ready to use.',
  'setup.next': 'Next',
  'setup.back': 'Back',
  'setup.skip': 'Skip',
  'setup.finish': 'Finish',

  // === COMMON ===
  'common.loading': 'Loading...',
  'common.error': 'Something went wrong',
  'common.retry': 'Retry',
  'common.cancel': 'Cancel',
  'common.save': 'Save',
  'common.delete': 'Delete',
  'common.edit': 'Edit',
  'common.close': 'Close',
  'common.confirm': 'Confirm',
  'common.yes': 'Yes',
  'common.no': 'No',
  'common.ok': 'OK',
  'common.search': 'Search',
  'common.filter': 'Filter',
  'common.sort': 'Sort',
  'common.refresh': 'Refresh',
  'common.more': 'More',
  'common.less': 'Less',

  // === ERRORS ===
  'error.network': 'Network error. Please check your connection.',
  'error.apiKey': 'Invalid API key. Please check your settings.',
  'error.memory': 'Failed to access memory file.',
  'error.unknown': 'An unexpected error occurred.',
  'error.retry': 'Retry',

  // === SUPPORT/CHAT SPECIFIC ===
  'support.welcome': 'Hi! I\'m your AI assistant. How can I help you today?',
  'support.suggestions.search': 'Search my documents',
  'support.suggestions.recent': 'Find recent conversations',
  'support.suggestions.setup': 'Help with setup',
  'support.suggestions.contact': 'Contact support',
  'support.inputPlaceholder': 'Ask me anything...',
  'support.sources': 'Sources',
  'support.feedback.helpful': 'Helpful',
  'support.feedback.notHelpful': 'Not helpful',
  'support.ticket.create': 'Create Ticket',
  'support.ticket.created': 'Ticket created successfully',

  // === SOURCES ===
  'sources.title': 'Sources',
  'sources.relevance': 'Relevance',
  'sources.view': 'View',
  'sources.noSources': 'No sources available',
};

/**
 * Get text by key with parameter substitution
 *
 * @param key - Text key (e.g., 'search.resultsCount')
 * @param params - Parameters to substitute (e.g., { count: 5 })
 * @returns Text with parameters substituted
 */
export function getText(
  key: string,
  params?: Record<string, string | number>
): string {
  let text = defaultTexts[key] || key;

  // Replace parameters
  if (params) {
    Object.entries(params).forEach(([paramKey, value]) => {
      text = text.replace(`{${paramKey}}`, String(value));
    });
  }

  return text;
}

/**
 * Get all texts (for debugging or external use)
 */
export function getAllTexts(): Record<string, string> {
  return { ...defaultTexts };
}

