/**
 * Font Loading Utilities
 *
 * Dynamic font loading based on config.
 */

import type { CanvasConfig } from '@memvid/canvas-core/config/client';

/**
 * Known Google Fonts that can be loaded
 */
const GOOGLE_FONTS = [
  'Inter',
  'Roboto',
  'Open Sans',
  'Lato',
  'Montserrat',
  'Poppins',
  'Source Sans Pro',
  'Noto Sans',
  'Raleway',
  'Ubuntu',
  'Playfair Display',
  'Merriweather',
  'PT Sans',
  'Nunito',
  'Work Sans',
  'DM Sans',
  'Space Grotesk',
  'Outfit',
  'Instrument Serif',
  'JetBrains Mono',
  'Fira Code',
  'Source Code Pro',
];

/**
 * Check if a font is a known Google Font
 */
function isGoogleFont(fontName: string): boolean {
  return GOOGLE_FONTS.some(
    (f) => f.toLowerCase() === fontName.toLowerCase()
  );
}

/**
 * Format font name for Google Fonts URL
 */
function formatFontName(fontName: string): string {
  return fontName.replace(/\s+/g, '+');
}

/**
 * Load fonts from Google Fonts based on config
 *
 * @param config - Canvas configuration
 */
export function loadFonts(config: CanvasConfig): void {
  if (typeof document === 'undefined') {
    return; // Server-side rendering
  }

  const fonts = config.theme.fonts;
  if (!fonts) {
    return;
  }

  const fontsToLoad: string[] = [];

  // Check each font
  if (fonts.display && isGoogleFont(fonts.display)) {
    fontsToLoad.push(fonts.display);
  }
  if (fonts.body && isGoogleFont(fonts.body) && fonts.body !== fonts.display) {
    fontsToLoad.push(fonts.body);
  }
  if (fonts.mono && isGoogleFont(fonts.mono)) {
    fontsToLoad.push(fonts.mono);
  }

  if (fontsToLoad.length === 0) {
    return;
  }

  // Check if fonts are already loaded
  const linkId = 'canvas-google-fonts';
  let existingLink = document.getElementById(linkId) as HTMLLinkElement | null;

  // Build Google Fonts URL
  const fontFamilies = fontsToLoad
    .map((font) => `family=${formatFontName(font)}:wght@300;400;500;600;700`)
    .join('&');
  const fontUrl = `https://fonts.googleapis.com/css2?${fontFamilies}&display=swap`;

  if (existingLink) {
    // Update existing link
    if (existingLink.href !== fontUrl) {
      existingLink.href = fontUrl;
    }
  } else {
    // Create new link
    const link = document.createElement('link');
    link.id = linkId;
    link.rel = 'stylesheet';
    link.href = fontUrl;
    document.head.appendChild(link);
  }
}

/**
 * Load custom font from URL
 *
 * @param fontFamily - Font family name
 * @param fontUrl - URL to font file
 * @param options - Font options
 */
export async function loadCustomFont(
  fontFamily: string,
  fontUrl: string,
  options: {
    weight?: string;
    style?: string;
  } = {}
): Promise<void> {
  if (typeof document === 'undefined' || !('FontFace' in window)) {
    return;
  }

  const { weight = '400', style = 'normal' } = options;

  try {
    const font = new FontFace(fontFamily, `url(${fontUrl})`, {
      weight,
      style,
    });

    await font.load();
    document.fonts.add(font);
  } catch (error) {
    console.warn(`Failed to load custom font "${fontFamily}":`, error);
  }
}

/**
 * Preload fonts for better performance
 *
 * @param fontUrls - Array of font URLs to preload
 */
export function preloadFonts(fontUrls: string[]): void {
  if (typeof document === 'undefined') {
    return;
  }

  fontUrls.forEach((url) => {
    const link = document.createElement('link');
    link.rel = 'preload';
    link.as = 'font';
    link.type = 'font/woff2';
    link.crossOrigin = 'anonymous';
    link.href = url;
    document.head.appendChild(link);
  });
}

