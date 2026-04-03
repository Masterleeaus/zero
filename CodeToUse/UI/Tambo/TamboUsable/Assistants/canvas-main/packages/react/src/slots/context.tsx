/**
 * Slots Context
 *
 * Provides slot customization to all Canvas components.
 */

'use client';

import {
  createContext,
  useContext,
  useMemo,
  type ReactNode,
} from 'react';
import type { CanvasShellSlots } from './types.js';

// ============================================================================
// Context
// ============================================================================

/**
 * Context value for slots
 */
interface SlotsContextValue {
  slots: CanvasShellSlots;
}

const SlotsContext = createContext<SlotsContextValue | null>(null);

// ============================================================================
// Provider
// ============================================================================

export interface SlotsProviderProps {
  /** Slot overrides */
  slots?: CanvasShellSlots;
  /** Children */
  children: ReactNode;
}

/**
 * Provider for slot customization
 *
 * @example
 * ```tsx
 * <SlotsProvider slots={{
 *   logo: <MyCustomLogo />,
 *   searchResult: MySearchResultComponent,
 *   sidebarFooter: null, // Hide sidebar footer
 * }}>
 *   <CanvasShell />
 * </SlotsProvider>
 * ```
 */
export function SlotsProvider({ slots = {}, children }: SlotsProviderProps) {
  const value = useMemo(() => ({ slots }), [slots]);

  return (
    <SlotsContext.Provider value={value}>
      {children}
    </SlotsContext.Provider>
  );
}

// ============================================================================
// Hooks
// ============================================================================

/**
 * Hook to access all slots
 */
export function useSlots(): CanvasShellSlots {
  const context = useContext(SlotsContext);
  return context?.slots ?? {};
}

/**
 * Hook to access a specific slot
 *
 * @param name - Slot name
 * @returns Slot value or undefined
 */
export function useSlot<K extends keyof CanvasShellSlots>(
  name: K
): CanvasShellSlots[K] | undefined {
  const slots = useSlots();
  return slots[name];
}

