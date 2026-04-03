/**
 * Next.js Integration
 *
 * Route handlers and utilities for Next.js App Router.
 *
 * @example
 * ```ts
 * // lib/canvas-handlers.ts
 * import { createCanvasHandlers } from '@memvid/canvas-server/next';
 *
 * export const handlers = createCanvasHandlers();
 *
 * // app/api/memory/route.ts (3 lines!)
 * import { handlers } from '@/lib/canvas-handlers';
 * export const { GET, POST, OPTIONS } = handlers.memory;
 * ```
 */

export {
  // Catch-all handler (simplest - just one route file!)
  createCanvasCatchAll,

  // Combined handlers factory
  createCanvasHandlers,

  // Individual handlers
  createChatHandler,
  createRecallHandler,
  createMemoryHandler,
  createSearchHandler,
  createStatsHandler,
  createAssetHandler,
  createIngestHandler,
  createSettingsHandler,
  createCreateMemoryHandler,

  // Types
  type RouteHandlerOptions,
} from './route-handlers.js';
