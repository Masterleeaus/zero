/**
 * Slot Component
 *
 * Helper component for rendering slots with fallback content.
 */

'use client';

import { type ReactNode, type ComponentType } from 'react';
import { useSlot } from './context.js';
import type { CanvasShellSlots } from './types.js';

// ============================================================================
// Slot Component
// ============================================================================

export interface SlotProps {
  /** Name of the slot */
  name: keyof CanvasShellSlots;
  /** Props to pass to the slot component (if it's a component) */
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  props?: Record<string, any>;
  /** Default content to show if slot is not provided */
  children?: ReactNode;
  /** Render function for default content (receives slot props) */
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  render?: (props: any) => ReactNode;
}

/**
 * Renders a slot with fallback to default content
 *
 * @example
 * ```tsx
 * // Simple usage with static fallback
 * <Slot name="logo">
 *   <DefaultLogo />
 * </Slot>
 *
 * // With props for slot component
 * <Slot name="searchResult" props={{ result, index, onClick }}>
 *   <DefaultSearchResult result={result} />
 * </Slot>
 *
 * // With render function for dynamic fallback
 * <Slot
 *   name="chatMessage"
 *   props={{ message, index }}
 *   render={(props) => <DefaultMessage {...props} />}
 * />
 * ```
 */
export function Slot({
  name,
  props,
  children,
  render,
}: SlotProps): ReactNode {
  const slot = useSlot(name);

  // If slot is explicitly null, hide the content
  if (slot === null) {
    return null;
  }

  // If slot is undefined, render fallback
  if (slot === undefined) {
    if (render && props) {
      return render(props);
    }
    return children ?? null;
  }

  // If slot is a component (function), render it with props
  if (typeof slot === 'function') {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const SlotComponent = slot as ComponentType<any>;
    return <SlotComponent {...(props || {})} />;
  }

  // If slot is a ReactNode, render it directly
  return <>{slot}</>;
}

// ============================================================================
// Conditional Slot Wrapper
// ============================================================================

export interface SlotWrapperProps {
  /** Slot name to check */
  name: keyof CanvasShellSlots;
  /** Content to render if slot is not null */
  children: ReactNode;
}

/**
 * Conditionally renders children based on slot value
 * If slot is null, hides children. Otherwise renders them.
 *
 * @example
 * ```tsx
 * <SlotWrapper name="sidebarFooter">
 *   <div className="sidebar-footer">
 *     <DefaultFooterContent />
 *   </div>
 * </SlotWrapper>
 * ```
 */
export function SlotWrapper({ name, children }: SlotWrapperProps): ReactNode {
  const slot = useSlot(name);

  // If slot is explicitly null, hide the wrapper
  if (slot === null) {
    return null;
  }

  // If slot is provided (not undefined), it will be handled by <Slot />
  // This wrapper is for conditional rendering of the container

  return <>{children}</>;
}
