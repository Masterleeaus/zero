/**
 * Layout Components
 *
 * Page, Header, Main, Sidebar, and Panel components for building Canvas layouts.
 */

import { type ReactNode } from 'react';
import clsx from 'clsx';

/**
 * Page Props
 */
export interface PageProps {
  /** Page content */
  children: ReactNode;

  /** Layout direction */
  layout?: 'vertical' | 'horizontal' | 'split';

  /** Theme */
  theme?: 'light' | 'dark' | 'system';

  /** Custom class name */
  className?: string;

  /** Custom styles (for CSS variable overrides) */
  style?: React.CSSProperties;
}

/**
 * Page component
 *
 * Root layout container for Canvas pages. Includes canvas-root for CSS variables.
 *
 * @example
 * ```tsx
 * <Canvas.Page>
 *   <Canvas.Header>My App</Canvas.Header>
 *   <Canvas.Main>
 *     <Canvas.Chat />
 *   </Canvas.Main>
 * </Canvas.Page>
 * ```
 */
export function Page({ children, layout = 'vertical', theme = 'light', className, style }: PageProps) {
  // Resolve system theme
  const resolvedTheme = theme === 'system'
    ? (typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    : theme;

  return (
    <div
      className={clsx(
        'canvas-root',
        'canvas-page',
        `canvas-page--${layout}`,
        className
      )}
      data-canvas-theme={resolvedTheme}
      style={style}
    >
      {children}
    </div>
  );
}

/**
 * Header Props
 */
export interface HeaderProps {
  /** Header content */
  children: ReactNode;

  /** Custom class name */
  className?: string;
}

/**
 * Header component
 *
 * Page header with title and navigation.
 *
 * @example
 * ```tsx
 * <Canvas.Header>
 *   <h1>My App</h1>
 * </Canvas.Header>
 * ```
 */
export function Header({ children, className }: HeaderProps) {
  return (
    <header className={clsx('canvas-header', className)}>
      {children}
    </header>
  );
}

/**
 * Main Props
 */
export interface MainProps {
  /** Main content */
  children: ReactNode;

  /** Whether to center content */
  centered?: boolean;

  /** Max width constraint */
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | 'full';

  /** Custom class name */
  className?: string;
}

/**
 * Main component
 *
 * Main content area of the page.
 *
 * @example
 * ```tsx
 * <Canvas.Main centered maxWidth="lg">
 *   <Canvas.Chat />
 * </Canvas.Main>
 * ```
 */
export function Main({
  children,
  centered = false,
  maxWidth = 'full',
  className,
}: MainProps) {
  return (
    <main
      className={clsx(
        'canvas-main',
        centered && 'canvas-main--centered',
        maxWidth !== 'full' && `canvas-main--max-${maxWidth}`,
        className
      )}
    >
      {children}
    </main>
  );
}

/**
 * Sidebar Props
 */
export interface SidebarProps {
  /** Sidebar content */
  children: ReactNode;

  /** Which side */
  side?: 'left' | 'right';

  /** Width */
  width?: string | number;

  /** Whether collapsible */
  collapsible?: boolean;

  /** Whether collapsed */
  collapsed?: boolean;

  /** Collapse callback */
  onCollapse?: (collapsed: boolean) => void;

  /** Custom class name */
  className?: string;
}

/**
 * Sidebar component
 *
 * Side panel for navigation or secondary content.
 *
 * @example
 * ```tsx
 * <Canvas.Sidebar side="left" collapsible>
 *   <nav>...</nav>
 * </Canvas.Sidebar>
 * ```
 */
export function Sidebar({
  children,
  side = 'left',
  width,
  collapsible = false,
  collapsed = false,
  onCollapse,
  className,
}: SidebarProps) {
  const style = width ? { width: typeof width === 'number' ? `${width}px` : width } : undefined;

  return (
    <aside
      className={clsx(
        'canvas-sidebar',
        `canvas-sidebar--${side}`,
        collapsible && 'canvas-sidebar--collapsible',
        collapsed && 'canvas-sidebar--collapsed',
        className
      )}
      style={style}
    >
      {collapsible && (
        <button
          className="canvas-sidebar__toggle"
          onClick={() => onCollapse?.(!collapsed)}
          aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
        >
          <svg
            width="16"
            height="16"
            viewBox="0 0 16 16"
            fill="currentColor"
            style={{ transform: collapsed ? 'rotate(180deg)' : undefined }}
          >
            <path d="M10 12L6 8L10 4" />
          </svg>
        </button>
      )}
      <div className="canvas-sidebar__content">{children}</div>
    </aside>
  );
}

/**
 * Panel Props
 */
export interface PanelProps {
  /** Panel content */
  children: ReactNode;

  /** Default size (percentage or pixels) */
  defaultSize?: number | string;

  /** Min size */
  minSize?: number | string;

  /** Max size */
  maxSize?: number | string;

  /** Whether resizable */
  resizable?: boolean;

  /** Custom class name */
  className?: string;
}

/**
 * Panel component
 *
 * Resizable content panel.
 *
 * @example
 * ```tsx
 * <Canvas.Panel defaultSize={60} resizable>
 *   <CodeEditor />
 * </Canvas.Panel>
 * ```
 */
export function Panel({
  children,
  defaultSize,
  minSize,
  maxSize,
  resizable = false,
  className,
}: PanelProps) {
  const style: React.CSSProperties = {};
  if (defaultSize) {
    style.flexBasis = typeof defaultSize === 'number' ? `${defaultSize}%` : defaultSize;
  }
  if (minSize) {
    style.minWidth = typeof minSize === 'number' ? `${minSize}px` : minSize;
  }
  if (maxSize) {
    style.maxWidth = typeof maxSize === 'number' ? `${maxSize}px` : maxSize;
  }

  return (
    <div
      className={clsx(
        'canvas-panel',
        resizable && 'canvas-panel--resizable',
        className
      )}
      style={style}
    >
      {children}
    </div>
  );
}

/**
 * Container Props
 */
export interface ContainerProps {
  /** Container content */
  children: ReactNode;

  /** Max width */
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';

  /** Padding */
  padding?: 'none' | 'sm' | 'md' | 'lg';

  /** Custom class name */
  className?: string;
}

/**
 * Container component
 *
 * Centered container with max width.
 *
 * @example
 * ```tsx
 * <Canvas.Container maxWidth="lg" padding="md">
 *   <Canvas.Chat />
 * </Canvas.Container>
 * ```
 */
export function Container({
  children,
  maxWidth = 'lg',
  padding = 'md',
  className,
}: ContainerProps) {
  return (
    <div
      className={clsx(
        'canvas-container',
        `canvas-container--${maxWidth}`,
        padding !== 'none' && `canvas-container--padding-${padding}`,
        className
      )}
    >
      {children}
    </div>
  );
}

/**
 * Title Props
 */
export interface TitleProps {
  /** Title content */
  children: ReactNode;

  /** Subtitle text */
  subtitle?: ReactNode;

  /** Custom class name */
  className?: string;
}

/**
 * Title component
 *
 * Page or section title with optional subtitle.
 *
 * @example
 * ```tsx
 * <Canvas.Title subtitle="Powered by @memvid/canvas-react">
 *   Memory Search
 * </Canvas.Title>
 * ```
 */
export function Title({ children, subtitle, className }: TitleProps) {
  return (
    <div className={clsx('canvas-title', className)}>
      <h1 className="canvas-title__text">{children}</h1>
      {subtitle && <p className="canvas-title__subtitle">{subtitle}</p>}
    </div>
  );
}

/**
 * Text Props
 */
export interface TextProps {
  /** Text content */
  children: ReactNode;

  /** Text variant */
  variant?: 'body' | 'caption' | 'label';

  /** Text color */
  color?: 'primary' | 'secondary' | 'muted';

  /** Custom class name */
  className?: string;
}

/**
 * Text component
 *
 * Typography component for consistent text styling.
 *
 * @example
 * ```tsx
 * <Canvas.Text color="secondary">
 *   Some helper text
 * </Canvas.Text>
 * ```
 */
export function Text({
  children,
  variant = 'body',
  color = 'primary',
  className,
}: TextProps) {
  return (
    <span
      className={clsx(
        'canvas-text',
        `canvas-text--${variant}`,
        `canvas-text--${color}`,
        className
      )}
    >
      {children}
    </span>
  );
}

/**
 * Code Props
 */
export interface CodeProps {
  /** Code content */
  children: ReactNode;

  /** Custom class name */
  className?: string;
}

/**
 * Code component
 *
 * Inline code styling.
 *
 * @example
 * ```tsx
 * <Canvas.Text>
 *   Install with <Canvas.Code>npm install @memvid/canvas-react</Canvas.Code>
 * </Canvas.Text>
 * ```
 */
export function Code({ children, className }: CodeProps) {
  return (
    <code className={clsx('canvas-code', className)}>{children}</code>
  );
}
