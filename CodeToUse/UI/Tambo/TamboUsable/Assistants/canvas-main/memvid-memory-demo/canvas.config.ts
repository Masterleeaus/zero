import type { CanvasConfig } from '@memvid/canvas-react/templates';

/**
 * Canvas Runtime Configuration
 *
 * This configures the runtime behavior of your Canvas app.
 * For visual branding (logo, colors, name), edit brand.json
 */
const config: CanvasConfig = {
  // Memory file path (relative to project root)
  memoryPath: './sp.mv2',

  // Default template to show on load
  defaultTemplate: 'search',

  // LLM settings (API keys come from .env.local)
  llm: {
    provider: 'anthropic',
    model: 'claude-sonnet-4-20250514',
  },

  // Embedding settings
  embedding: {
    provider: 'openai',
    model: 'text-embedding-3-small',
  },

  // API endpoints (for server-side operations)
  endpoints: {
    memory: '/api/memory',
    chat: '/api/memory/chat',
    search: '/api/memory/search',
  },
};

export default config;
