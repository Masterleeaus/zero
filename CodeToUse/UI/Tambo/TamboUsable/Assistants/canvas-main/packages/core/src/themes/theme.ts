/**
 * Theme System
 *
 * CSS variable-based theming for Canvas components.
 */

/**
 * Theme color palette
 */
export interface ThemeColors {
  /** Background color */
  background: string;
  /** Secondary background */
  backgroundSecondary: string;
  /** Primary text color */
  text: string;
  /** Secondary text color */
  textSecondary: string;
  /** Border color */
  border: string;
  /** Primary accent color */
  primary: string;
  /** Primary hover state */
  primaryHover: string;
  /** Error color */
  error: string;
  /** Success color */
  success: string;
  /** User message background */
  userBg: string;
  /** Assistant message background */
  assistantBg: string;
  /** User avatar color */
  avatarUser: string;
  /** Assistant avatar color */
  avatarAssistant: string;
}

/**
 * Theme spacing scale
 */
export interface ThemeSpacing {
  xs: string;
  sm: string;
  md: string;
  lg: string;
  xl: string;
}

/**
 * Theme typography
 */
export interface ThemeTypography {
  fontFamily: string;
  fontSize: string;
  lineHeight: string;
}

/**
 * Theme border radius
 */
export interface ThemeBorderRadius {
  sm: string;
  md: string;
  lg: string;
}

/**
 * Complete theme definition
 */
export interface Theme {
  name: string;
  colors: ThemeColors;
  spacing: ThemeSpacing;
  typography: ThemeTypography;
  borderRadius: ThemeBorderRadius;
}

/**
 * Light theme
 */
export const lightTheme: Theme = {
  name: 'light',
  colors: {
    background: '#ffffff',
    backgroundSecondary: '#f9fafb',
    text: '#111827',
    textSecondary: '#6b7280',
    border: '#e5e7eb',
    primary: '#3b82f6',
    primaryHover: '#2563eb',
    error: '#ef4444',
    success: '#22c55e',
    userBg: '#eff6ff',
    assistantBg: '#f9fafb',
    avatarUser: '#3b82f6',
    avatarAssistant: '#6b7280',
  },
  spacing: {
    xs: '0.25rem',
    sm: '0.5rem',
    md: '1rem',
    lg: '1.5rem',
    xl: '2rem',
  },
  typography: {
    fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
    fontSize: '0.9375rem',
    lineHeight: '1.5',
  },
  borderRadius: {
    sm: '0.375rem',
    md: '0.5rem',
    lg: '0.75rem',
  },
};

/**
 * Dark theme
 */
export const darkTheme: Theme = {
  name: 'dark',
  colors: {
    background: '#111827',
    backgroundSecondary: '#1f2937',
    text: '#f9fafb',
    textSecondary: '#9ca3af',
    border: '#374151',
    primary: '#60a5fa',
    primaryHover: '#3b82f6',
    error: '#f87171',
    success: '#4ade80',
    userBg: '#1e3a5f',
    assistantBg: '#1f2937',
    avatarUser: '#60a5fa',
    avatarAssistant: '#9ca3af',
  },
  spacing: lightTheme.spacing,
  typography: lightTheme.typography,
  borderRadius: lightTheme.borderRadius,
};

/**
 * Built-in themes
 */
export const themes: Record<string, Theme> = {
  light: lightTheme,
  dark: darkTheme,
};

/**
 * Convert theme to CSS variables
 */
export function themeToCSSVariables(theme: Theme): Record<string, string> {
  return {
    '--canvas-bg': theme.colors.background,
    '--canvas-bg-secondary': theme.colors.backgroundSecondary,
    '--canvas-text': theme.colors.text,
    '--canvas-text-secondary': theme.colors.textSecondary,
    '--canvas-border': theme.colors.border,
    '--canvas-primary': theme.colors.primary,
    '--canvas-primary-hover': theme.colors.primaryHover,
    '--canvas-error': theme.colors.error,
    '--canvas-success': theme.colors.success,
    '--canvas-user-bg': theme.colors.userBg,
    '--canvas-assistant-bg': theme.colors.assistantBg,
    '--canvas-avatar-user': theme.colors.avatarUser,
    '--canvas-avatar-assistant': theme.colors.avatarAssistant,
    '--canvas-space-xs': theme.spacing.xs,
    '--canvas-space-sm': theme.spacing.sm,
    '--canvas-space-md': theme.spacing.md,
    '--canvas-space-lg': theme.spacing.lg,
    '--canvas-space-xl': theme.spacing.xl,
    '--canvas-font-family': theme.typography.fontFamily,
    '--canvas-font-size': theme.typography.fontSize,
    '--canvas-line-height': theme.typography.lineHeight,
    '--canvas-radius-sm': theme.borderRadius.sm,
    '--canvas-radius-md': theme.borderRadius.md,
    '--canvas-radius-lg': theme.borderRadius.lg,
  };
}

/**
 * Apply theme to a DOM element
 */
export function applyTheme(element: HTMLElement, theme: Theme | string): void {
  const resolvedTheme = typeof theme === 'string' ? themes[theme] : theme;
  if (!resolvedTheme) return;

  const variables = themeToCSSVariables(resolvedTheme);
  for (const [key, value] of Object.entries(variables)) {
    element.style.setProperty(key, value);
  }
  element.setAttribute('data-canvas-theme', resolvedTheme.name);
}

/**
 * Create a custom theme by merging with a base theme
 */
export function createTheme(
  name: string,
  overrides: Partial<Theme>,
  base: Theme = lightTheme
): Theme {
  return {
    name,
    colors: { ...base.colors, ...overrides.colors },
    spacing: { ...base.spacing, ...overrides.spacing },
    typography: { ...base.typography, ...overrides.typography },
    borderRadius: { ...base.borderRadius, ...overrides.borderRadius },
  };
}
