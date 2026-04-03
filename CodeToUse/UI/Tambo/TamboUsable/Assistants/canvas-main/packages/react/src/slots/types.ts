/**
 * Component Slot Types
 *
 * Defines the slot system for customizing Canvas components.
 * Users can replace or extend any part of the UI without modifying code.
 */

import type { ReactNode, ComponentType } from 'react';

// ============================================================================
// Slot Value Types
// ============================================================================

/**
 * A slot can be:
 * - ReactNode: Static content
 * - Component: A React component that receives props
 * - null: Hide/remove the slot entirely
 */
export type SlotValue<P = Record<string, unknown>> = 
  | ReactNode 
  | ComponentType<P> 
  | null;

/**
 * Helper to check if a slot value is a component
 */
export function isSlotComponent<P>(
  slot: SlotValue<P>
): slot is ComponentType<P> {
  return typeof slot === 'function';
}

// ============================================================================
// Common Slot Props
// ============================================================================

/**
 * Props passed to logo slot
 */
export interface LogoSlotProps {
  brandName: string;
  logoUrl?: string;
  size?: number;
}

/**
 * Props passed to sidebar slots
 */
export interface SidebarSlotProps {
  isCollapsed: boolean;
  onToggleCollapse?: () => void;
}

/**
 * Props passed to navigation item slot
 */
export interface NavItemSlotProps {
  item: {
    id: string;
    label: string;
    icon: string;
    href: string;
    badge?: string | number;
    external?: boolean;
  };
  isActive: boolean;
  onClick: () => void;
}

/**
 * Props passed to search result slot
 */
export interface SearchResultSlotProps {
  result: {
    id: string;
    content: string;
    score: number;
    metadata?: Record<string, unknown>;
  };
  index: number;
  onClick: () => void;
}

/**
 * Props passed to chat message slot
 */
export interface ChatMessageSlotProps {
  message: {
    role: 'user' | 'assistant';
    content: string;
    sources?: Array<{
      title: string;
      uri: string;
      score: number;
    }>;
  };
  index: number;
}

/**
 * Props passed to empty state slots
 */
export interface EmptyStateSlotProps {
  view: 'search' | 'chat' | 'dashboard';
}

/**
 * Props passed to header slots
 */
export interface HeaderSlotProps {
  title: string;
  subtitle?: string;
}

// ============================================================================
// Canvas Shell Slots
// ============================================================================

/**
 * Slots available in CanvasShell component
 */
export interface CanvasShellSlots {
  // === Sidebar Slots ===
  /** Replace the entire sidebar */
  sidebar?: SlotValue<SidebarSlotProps>;
  /** Content at top of sidebar (above nav) */
  sidebarHeader?: SlotValue<SidebarSlotProps>;
  /** Content at bottom of sidebar (below nav) */
  sidebarFooter?: SlotValue<SidebarSlotProps>;
  /** Custom logo component */
  logo?: SlotValue<LogoSlotProps>;
  /** Custom navigation item renderer */
  navItem?: SlotValue<NavItemSlotProps>;

  // === Content Area Slots ===
  /** Content before main content area */
  beforeContent?: SlotValue;
  /** Content after main content area */
  afterContent?: SlotValue;
  /** Custom header for content area */
  contentHeader?: SlotValue<HeaderSlotProps>;

  // === Search Slots ===
  /** Replace entire search view */
  searchView?: SlotValue;
  /** Custom search header */
  searchHeader?: SlotValue<HeaderSlotProps>;
  /** Custom search result renderer */
  searchResult?: SlotValue<SearchResultSlotProps>;
  /** Custom search empty state */
  searchEmpty?: SlotValue<EmptyStateSlotProps>;

  // === Chat Slots ===
  /** Replace entire chat view */
  chatView?: SlotValue;
  /** Custom chat header */
  chatHeader?: SlotValue<HeaderSlotProps>;
  /** Custom message renderer */
  chatMessage?: SlotValue<ChatMessageSlotProps>;
  /** Custom chat input area */
  chatInput?: SlotValue<{ value: string; onChange: (value: string) => void; onSubmit: () => void }>;
  /** Custom chat empty state */
  chatEmpty?: SlotValue<EmptyStateSlotProps>;

  // === Dashboard Slots ===
  /** Replace entire dashboard view */
  dashboardView?: SlotValue;
  /** Custom dashboard header */
  dashboardHeader?: SlotValue<HeaderSlotProps>;

  // === Global Slots ===
  /** Custom loading indicator */
  loading?: SlotValue;
  /** Custom error display */
  error?: SlotValue<{ error: Error; retry?: () => void }>;
}

// ============================================================================
// Template Slots
// ============================================================================

/**
 * Slots available in App template
 */
export interface AppTemplateSlots extends CanvasShellSlots {
  /** Custom modal renderer */
  modal?: SlotValue<{ isOpen: boolean; onClose: () => void; children: ReactNode }>;
}

/**
 * Slots available in Search template
 */
export interface SearchTemplateSlots {
  /** Custom search header */
  header?: SlotValue<HeaderSlotProps>;
  /** Custom search input */
  input?: SlotValue<{ value: string; onChange: (value: string) => void }>;
  /** Custom result renderer */
  result?: SlotValue<SearchResultSlotProps>;
  /** Custom empty state */
  empty?: SlotValue<EmptyStateSlotProps>;
  /** Custom loading state */
  loading?: SlotValue;
}

/**
 * Slots available in Support/Chat template
 */
export interface SupportTemplateSlots {
  /** Custom header */
  header?: SlotValue<HeaderSlotProps>;
  /** Custom message renderer */
  message?: SlotValue<ChatMessageSlotProps>;
  /** Custom input area */
  input?: SlotValue<{ value: string; onChange: (value: string) => void; onSubmit: () => void }>;
  /** Custom welcome message */
  welcome?: SlotValue;
  /** Custom sources display */
  sources?: SlotValue<{ sources: Array<{ title: string; uri: string; score: number }> }>;
}

/**
 * Slots available in Dashboard template
 */
export interface DashboardTemplateSlots {
  /** Custom header */
  header?: SlotValue<HeaderSlotProps>;
  /** Custom stats widget */
  stats?: SlotValue<{ stats: Record<string, number> }>;
  /** Custom activity widget */
  activity?: SlotValue<{ items: Array<{ id: string; title: string; timestamp: Date }> }>;
}

