/**
 * Canvas Context
 *
 * Provides Canvas engine and state to all child components.
 */

import {
  createContext,
  useContext,
  useRef,
  useEffect,
  type ReactNode,
} from 'react';
import {
  createEngine,
  type CanvasEngine,
  type EngineConfig,
  type LLMConfig,
  type EmbeddingConfig,
  type AgentConfig,
} from '@memvid/canvas-core';
import { createCanvasStore, type CanvasStore } from './canvas-store.js';

/**
 * Props for Canvas.Provider
 */
export interface CanvasProviderProps {
  /** LLM provider configuration */
  llm: LLMConfig;

  /** Embedding provider configuration (optional, defaults to OpenAI) */
  embedding?: EmbeddingConfig;

  /** Memory path (local file or cloud) */
  memory: string;

  /** Memvid API key for cloud sync (optional) */
  memvidApiKey?: string;

  /** Agent configurations */
  agents?: AgentConfig[];

  /** Default agent to use */
  defaultAgent?: string;

  /** Theme name or custom theme */
  theme?: 'light' | 'dark' | 'system';

  /** Enable debug mode */
  debug?: boolean;

  /** Error handler */
  onError?: (error: Error) => void;

  /** Children */
  children: ReactNode;
}

/**
 * Canvas context value
 */
export interface CanvasContextValue {
  /** Canvas engine instance */
  engine: CanvasEngine;

  /** Zustand store */
  store: CanvasStore;

  /** Current theme */
  theme: 'light' | 'dark';

  /** Debug mode */
  debug: boolean;
}

/**
 * Canvas context
 */
const CanvasContext = createContext<CanvasContextValue | null>(null);

/**
 * Canvas Provider
 *
 * @example
 * ```tsx
 * <Canvas.Provider
 *   llm={{ provider: 'anthropic', apiKey: process.env.ANTHROPIC_API_KEY }}
 *   memory="./app.mv2"
 * >
 *   <Canvas.Chat />
 * </Canvas.Provider>
 * ```
 */
export function CanvasProvider({
  llm,
  embedding,
  memory,
  memvidApiKey,
  agents,
  defaultAgent = 'assistant',
  theme = 'system',
  debug = false,
  onError,
  children,
}: CanvasProviderProps) {
  // Create engine (once)
  const engineRef = useRef<CanvasEngine | null>(null);
  const storeRef = useRef<CanvasStore | null>(null);

  if (!engineRef.current) {
    const config: EngineConfig = {
      llm,
      embedding,
      memory,
      memvidApiKey,
      agents: agents ?? [{ name: 'assistant' }],
      defaultAgent,
      debug,
    };

    try {
      engineRef.current = createEngine(config);
    } catch (error) {
      if (onError && error instanceof Error) {
        onError(error);
      }
      throw error;
    }
  }

  if (!storeRef.current) {
    storeRef.current = createCanvasStore();
  }

  // Resolve theme
  const resolvedTheme = useResolvedTheme(theme);

  // Set up error boundary
  useEffect(() => {
    const handleError = (event: ErrorEvent) => {
      if (onError) {
        onError(event.error);
      }
    };

    window.addEventListener('error', handleError);
    return () => window.removeEventListener('error', handleError);
  }, [onError]);

  const value: CanvasContextValue = {
    engine: engineRef.current,
    store: storeRef.current,
    theme: resolvedTheme,
    debug,
  };

  return (
    <CanvasContext.Provider value={value}>
      <div data-canvas-theme={resolvedTheme} className="canvas-root">
        {children}
      </div>
    </CanvasContext.Provider>
  );
}

/**
 * Hook to resolve theme (handles 'system')
 */
function useResolvedTheme(theme: 'light' | 'dark' | 'system'): 'light' | 'dark' {
  if (theme !== 'system') {
    return theme;
  }

  // Check for system preference
  if (typeof window !== 'undefined') {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    return prefersDark ? 'dark' : 'light';
  }

  return 'light';
}

/**
 * Hook to access Canvas context
 *
 * @throws Error if used outside of CanvasProvider
 */
export function useCanvasContext(): CanvasContextValue {
  const context = useContext(CanvasContext);

  if (!context) {
    throw new Error(
      'useCanvasContext must be used within a Canvas.Provider. ' +
      'Wrap your component tree with <Canvas.Provider>.'
    );
  }

  return context;
}

/**
 * Hook to access Canvas engine
 */
export function useEngine(): CanvasEngine {
  const { engine } = useCanvasContext();
  return engine;
}

/**
 * Hook to access Canvas store
 */
export function useStore(): CanvasStore {
  const { store } = useCanvasContext();
  return store;
}

/**
 * Hook to access current theme
 */
export function useTheme(): 'light' | 'dark' {
  const { theme } = useCanvasContext();
  return theme;
}
