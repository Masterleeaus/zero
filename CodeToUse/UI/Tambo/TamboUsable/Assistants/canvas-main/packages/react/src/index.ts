/**
 * @memvid/canvas-react
 *
 * React components and hooks for Canvas AI UI Kit.
 */

// Context
export {
  CanvasProvider,
  useCanvasContext,
  useEngine,
  useStore,
  useTheme,
  type CanvasProviderProps,
  type CanvasContextValue,
} from './context/index.js';

// Store
export {
  createCanvasStore,
  selectChat,
  selectMessages,
  selectIsLoading,
  selectIsStreaming,
  selectError,
  selectSources,
  selectInputValue,
  selectConversations,
  selectUI,
  type CanvasStore,
  type CanvasState,
  type CanvasActions,
  type ChatState,
} from './context/canvas-store.js';

// Hooks
export {
  useChat,
  useMemory,
  useAgent,
  useConversations,
  useText,
  useCanvasConfig,
  type UseChatOptions,
  type UseChatReturn,
  type UseMemoryOptions,
  type UseMemoryReturn,
  type UseAgentReturn,
  type UseConversationsReturn,
} from './hooks/index.js';

// Components
export {
  Chat,
  ChatInput,
  ChatMessage,
  ChatMessages,
  Sources,
  type ChatProps,
  type ChatInputProps,
  type ChatMessageProps,
  type ChatMessagesProps,
  type SourcesProps,
} from './components/index.js';

// Re-export core types for convenience (from types-only to avoid native deps)
export type {
  Message,
  MessageRole,
  Conversation,
  RecallResult,
  AgentConfig,
  LLMConfig,
  EmbeddingConfig,
} from '@memvid/canvas-core/types-only';

// Templates
export {
  App,
  Dashboard,
  Search,
  Support,
  type BrandConfig,
  type BrandColors,
  type BrandTypography,
  type CanvasConfig,
  type CanvasProps,
  type DashboardConfig,
  type DashboardWidget,
  type FeatureFlags,
  type I18nConfig,
  type NavItem,
  type SearchConfig,
  type SupportConfig,
} from './templates/index.js';

// Slots System
export {
  SlotsProvider,
  useSlots,
  useSlot,
  Slot,
  SlotWrapper,
  isSlotComponent,
  type SlotsProviderProps,
  type SlotProps,
  type SlotWrapperProps,
  type SlotValue,
  type LogoSlotProps,
  type SidebarSlotProps,
  type NavItemSlotProps,
  type SearchResultSlotProps,
  type ChatMessageSlotProps,
  type EmptyStateSlotProps,
  type HeaderSlotProps,
  type CanvasShellSlots,
  type AppTemplateSlots,
  type SearchTemplateSlots,
  type SupportTemplateSlots,
  type DashboardTemplateSlots,
} from './slots/index.js';

// Styles
import './styles/canvas.css';

/**
 * Canvas namespace for convenient imports
 *
 * @example
 * ```tsx
 * import { Canvas } from '@memvid/canvas-react';
 *
 * <Canvas.Provider llm={...} memory="./app.mv2">
 *   <Canvas.Chat />
 * </Canvas.Provider>
 * ```
 */
export const Canvas = {
  // Re-exports for namespace pattern
  Provider: undefined as unknown as typeof import('./context/index.js').CanvasProvider,
  Chat: undefined as unknown as typeof import('./components/index.js').Chat,
  ChatInput: undefined as unknown as typeof import('./components/index.js').ChatInput,
  ChatMessage: undefined as unknown as typeof import('./components/index.js').ChatMessage,
  ChatMessages: undefined as unknown as typeof import('./components/index.js').ChatMessages,
  Sources: undefined as unknown as typeof import('./components/index.js').Sources,
  // Templates
  App: undefined as unknown as typeof import('./templates/index.js').App,
  Dashboard: undefined as unknown as typeof import('./templates/index.js').Dashboard,
  Search: undefined as unknown as typeof import('./templates/index.js').Search,
  Support: undefined as unknown as typeof import('./templates/index.js').Support,
};

// Populate Canvas namespace
import { CanvasProvider } from './context/index.js';
import { Chat, ChatInput, ChatMessage, ChatMessages, Sources } from './components/index.js';
import { App, Dashboard, Search, Support } from './templates/index.js';

Canvas.Provider = CanvasProvider;
Canvas.Chat = Chat;
Canvas.ChatInput = ChatInput;
Canvas.ChatMessage = ChatMessage;
Canvas.ChatMessages = ChatMessages;
Canvas.Sources = Sources;
// Templates
Canvas.App = App;
Canvas.Dashboard = Dashboard;
Canvas.Search = Search;
Canvas.Support = Support;
