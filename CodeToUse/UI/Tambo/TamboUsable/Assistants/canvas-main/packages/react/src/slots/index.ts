/**
 * Canvas Slots System
 *
 * Provides a flexible way to customize Canvas components.
 */

// Types
export type {
  SlotValue,
  LogoSlotProps,
  SidebarSlotProps,
  NavItemSlotProps,
  SearchResultSlotProps,
  ChatMessageSlotProps,
  EmptyStateSlotProps,
  HeaderSlotProps,
  CanvasShellSlots,
  AppTemplateSlots,
  SearchTemplateSlots,
  SupportTemplateSlots,
  DashboardTemplateSlots,
} from './types.js';

export { isSlotComponent } from './types.js';

// Context & Provider
export {
  SlotsProvider,
  useSlots,
  useSlot,
  type SlotsProviderProps,
} from './context.js';

// Components
export { Slot, SlotWrapper } from './Slot.js';
export type { SlotProps, SlotWrapperProps } from './Slot.js';

