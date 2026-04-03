/**
 * Client-safe Components
 *
 * These components can be safely imported in browser/client-side code
 * without pulling in native Node.js dependencies.
 *
 * @example
 * ```tsx
 * // Client-safe import (no native deps)
 * import { Canvas, Sources } from '@memvid/canvas-react/components';
 * import type { RecallResult } from '@memvid/canvas-react/components';
 * ```
 */

// Components that don't depend on the engine/context
export { Sources, type SourcesProps } from './components/sources.js';
export { ChatMessage, type ChatMessageProps } from './components/chat-message.js';
export { ChatMessages, type ChatMessagesProps } from './components/chat-messages.js';
export { ChatInput, type ChatInputProps } from './components/chat-input.js';

// Canvas App Components (client-safe)
export { SetupWizard, type SetupWizardProps } from './components/setup-wizard.js';
export { SettingsPanel, type SettingsPanelProps } from './components/settings-panel.js';
export { CanvasShell, type CanvasShellProps } from './components/canvas-shell.js';
export { CanvasProvider, useCanvas, type CanvasProviderProps } from './components/canvas-provider.js';

// Hooks (client-safe)
export {
  useCanvasSettings,
  applyTheme,
  getServerSettings,
  type UseCanvasSettingsReturn,
} from './hooks/use-canvas-settings.js';
export {
  ConfigPanel,
  ConfigField,
  MODEL_OPTIONS,
  type ConfigPanelProps,
  type ConfigFieldProps,
  type ConfigValues,
  type LLMProvider,
} from './components/config-panel.js';

// Layout components
export {
  Page,
  Header,
  Main,
  Sidebar,
  Panel,
  Container,
  Title,
  Text,
  Code,
  type PageProps,
  type HeaderProps,
  type MainProps,
  type SidebarProps,
  type PanelProps,
  type ContainerProps,
  type TitleProps,
  type TextProps,
  type CodeProps,
} from './components/layout.js';

// Re-export types needed by these components (from types-only to avoid native deps)
export type {
  RecallResult,
  Message,
  MessageRole,
  // UI Config types
  CanvasUIConfig,
  RuntimeSettings,
  SetupStep,
  ThemeConfig,
  FeaturesConfig,
} from '@memvid/canvas-core/types-only';

/**
 * Canvas namespace for convenient imports (client-safe)
 *
 * @example
 * ```tsx
 * import { Canvas } from '@memvid/canvas-react/components';
 *
 * <Canvas.Page>
 *   <Canvas.Header>
 *     <Canvas.Title subtitle="Powered by @memvid/canvas-react">
 *       Memory Search
 *     </Canvas.Title>
 *   </Canvas.Header>
 *   <Canvas.Main maxWidth="lg">
 *     <Canvas.ChatInput ... />
 *     <Canvas.Sources ... />
 *   </Canvas.Main>
 * </Canvas.Page>
 * ```
 */
export const Canvas = {
  // Layout
  Page: undefined as unknown as typeof import('./components/layout.js').Page,
  Header: undefined as unknown as typeof import('./components/layout.js').Header,
  Main: undefined as unknown as typeof import('./components/layout.js').Main,
  Sidebar: undefined as unknown as typeof import('./components/layout.js').Sidebar,
  Panel: undefined as unknown as typeof import('./components/layout.js').Panel,
  Container: undefined as unknown as typeof import('./components/layout.js').Container,
  Title: undefined as unknown as typeof import('./components/layout.js').Title,
  Text: undefined as unknown as typeof import('./components/layout.js').Text,
  Code: undefined as unknown as typeof import('./components/layout.js').Code,
  // Chat
  ChatInput: undefined as unknown as typeof import('./components/chat-input.js').ChatInput,
  ChatMessage: undefined as unknown as typeof import('./components/chat-message.js').ChatMessage,
  ChatMessages: undefined as unknown as typeof import('./components/chat-messages.js').ChatMessages,
  Sources: undefined as unknown as typeof import('./components/sources.js').Sources,
  // Config
  ConfigPanel: undefined as unknown as typeof import('./components/config-panel.js').ConfigPanel,
  ConfigField: undefined as unknown as typeof import('./components/config-panel.js').ConfigField,
};

// Populate Canvas namespace
import { Page, Header, Main, Sidebar, Panel, Container, Title, Text, Code } from './components/layout.js';
import { ChatInput } from './components/chat-input.js';
import { ChatMessage } from './components/chat-message.js';
import { ChatMessages } from './components/chat-messages.js';
import { Sources } from './components/sources.js';
import { ConfigPanel, ConfigField } from './components/config-panel.js';

Canvas.Page = Page;
Canvas.Header = Header;
Canvas.Main = Main;
Canvas.Sidebar = Sidebar;
Canvas.Panel = Panel;
Canvas.Container = Container;
Canvas.Title = Title;
Canvas.Text = Text;
Canvas.Code = Code;
Canvas.ChatInput = ChatInput;
Canvas.ChatMessage = ChatMessage;
Canvas.ChatMessages = ChatMessages;
Canvas.Sources = Sources;
Canvas.ConfigPanel = ConfigPanel;
Canvas.ConfigField = ConfigField;
