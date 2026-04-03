/**
 * Canvas API - Catch-All Route
 *
 * Single route file that handles all Canvas API endpoints.
 * This is the simplest possible integration!
 *
 * Endpoints:
 * - /api/canvas/memory - Memory info
 * - /api/canvas/search - Search
 * - /api/canvas/chat - Chat with memory
 * - /api/canvas/stats - Statistics
 * - /api/canvas/asset - Asset extraction
 * - /api/canvas/ingest - File ingestion
 * - /api/canvas/settings - Settings
 * - /api/canvas/create-memory - Create new memory
 */

import { createCanvasCatchAll } from '@memvid/canvas-server/next';
import { loadSettings, getApiKey, resolvePath } from '@memvid/canvas-core';

const basePath = process.cwd();

export const { GET, POST, OPTIONS } = createCanvasCatchAll({
  basePath,
  config: () => {
    const settings = loadSettings(basePath);
    const provider = settings?.llmProvider || 'openai';
    const memoryPath = settings?.memoryPath || './data/memory.mv2';
    // Resolve to absolute path so all components use the same memory file
    const resolvedMemoryPath = resolvePath(memoryPath, basePath);

    return {
      llm: {
        provider,
        apiKey: getApiKey(provider) || '',
        model: settings?.llmModel,
      },
      memory: resolvedMemoryPath,
    };
  },
});
