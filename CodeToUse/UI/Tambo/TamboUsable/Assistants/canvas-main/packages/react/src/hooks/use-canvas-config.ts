/**
 * useCanvasConfig Hook
 *
 * React hook for accessing the unified Canvas configuration.
 */

import { useContext } from 'react';
import type { CanvasConfig } from '@memvid/canvas-core/config/client';
import { DEFAULT_CANVAS_CONFIG } from '@memvid/canvas-core/config/client';
import { CanvasConfigContext } from '../context/canvas-config-context.js';

/**
 * Hook to access Canvas configuration
 *
 * @example
 * ```tsx
 * function MyComponent() {
 *   const config = useCanvasConfig();
 *   return <h1>{config.brand.name}</h1>;
 * }
 * ```
 */
export function useCanvasConfig(): CanvasConfig {
  const context = useContext(CanvasConfigContext);
  return context || DEFAULT_CANVAS_CONFIG;
}

