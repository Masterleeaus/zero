/**
 * Brand Logo Component
 *
 * Dynamic logo component with light/dark mode support.
 */

'use client';

import React, { useMemo } from 'react';
import { useCanvasConfig } from '../hooks/use-canvas-config.js';

/**
 * Default logo icon (used when no logo is configured)
 */
function DefaultLogoIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
      <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
      <line x1="12" y1="22.08" x2="12" y2="12" />
    </svg>
  );
}

export interface BrandLogoProps {
  /** Additional CSS class */
  className?: string;
  /** Logo size (width) */
  size?: number | string;
  /** Force a specific theme mode */
  mode?: 'light' | 'dark';
  /** Show only icon (no text) */
  iconOnly?: boolean;
  /** Alt text override */
  alt?: string;
}

/**
 * Brand Logo Component
 *
 * Renders the configured logo with light/dark mode support.
 *
 * @example
 * ```tsx
 * <BrandLogo size={40} />
 * ```
 *
 * @example
 * ```tsx
 * <BrandLogo iconOnly mode="dark" />
 * ```
 */
export function BrandLogo({
  className,
  size = 32,
  mode,
  iconOnly = false,
  alt,
}: BrandLogoProps) {
  const config = useCanvasConfig();
  const { brand, theme } = config;

  // Determine which logo to use based on mode
  const logoSrc = useMemo(() => {
    if (!brand.logo) {
      return null;
    }

    if (typeof brand.logo === 'string') {
      return brand.logo;
    }

    // Object with light/dark variants
    const effectiveMode = mode || theme.mode;
    if (effectiveMode === 'dark') {
      return brand.logo.dark || brand.logo.light;
    }
    return brand.logo.light || brand.logo.dark;
  }, [brand.logo, mode, theme.mode]);

  const sizeStyle = typeof size === 'number' ? `${size}px` : size;
  const altText = alt || brand.name || 'Logo';

  // If no logo configured, show default icon or brand name
  if (!logoSrc) {
    if (iconOnly) {
      return (
        <div
          className={className}
          style={{
            width: sizeStyle,
            height: sizeStyle,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            background: 'linear-gradient(135deg, var(--canvas-primary) 0%, var(--canvas-accent) 100%)',
            borderRadius: 'var(--canvas-radius, 8px)',
            color: 'white',
          }}
        >
          <DefaultLogoIcon className="w-1/2 h-1/2" />
        </div>
      );
    }

    return (
      <div className={className} style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
        <div
          style={{
            width: sizeStyle,
            height: sizeStyle,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            background: 'linear-gradient(135deg, var(--canvas-primary) 0%, var(--canvas-accent) 100%)',
            borderRadius: 'var(--canvas-radius, 8px)',
            color: 'white',
          }}
        >
          <DefaultLogoIcon />
        </div>
        <span
          style={{
            fontFamily: 'var(--canvas-font-display, inherit)',
            fontSize: '22px',
            fontWeight: 400,
            color: 'var(--canvas-text)',
          }}
        >
          {brand.name}
        </span>
      </div>
    );
  }

  // Render the configured logo
  if (iconOnly) {
    return (
      <img
        src={logoSrc}
        alt={altText}
        className={className}
        style={{
          width: sizeStyle,
          height: sizeStyle,
          objectFit: 'contain',
        }}
      />
    );
  }

  return (
    <div className={className} style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
      <img
        src={logoSrc}
        alt={altText}
        style={{
          height: sizeStyle,
          width: 'auto',
          objectFit: 'contain',
        }}
      />
      {!iconOnly && (
        <span
          style={{
            fontFamily: 'var(--canvas-font-display, inherit)',
            fontSize: '22px',
            fontWeight: 400,
            color: 'var(--canvas-text)',
          }}
        >
          {brand.name}
        </span>
      )}
    </div>
  );
}

