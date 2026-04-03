/**
 * Canvas Config Context
 *
 * Provides unified Canvas configuration to all child components.
 */

import {
  createContext,
  useContext,
  type ReactNode,
} from 'react';
import type { CanvasConfig } from '@memvid/canvas-core/config/client';
import { DEFAULT_CANVAS_CONFIG } from '@memvid/canvas-core/config/client';

/**
 * Context for Canvas configuration
 */
export const CanvasConfigContext = createContext<CanvasConfig | null>(null);

/**
 * Props for CanvasConfigProvider
 */
export interface CanvasConfigProviderProps {
  /** Canvas configuration */
  config: CanvasConfig;
  /** Children */
  children: ReactNode;
}

/**
 * Provider component for Canvas configuration
 *
 * @example
 * ```tsx
 * <CanvasConfigProvider config={myConfig}>
 *   <App />
 * </CanvasConfigProvider>
 * ```
 */
export function CanvasConfigProvider({
  config,
  children,
}: CanvasConfigProviderProps) {
  return (
    <CanvasConfigContext.Provider value={config}>
      {children}
    </CanvasConfigContext.Provider>
  );
}

