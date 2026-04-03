/**
 * Favicon Utilities
 *
 * Dynamic favicon injection based on config.
 */

import type { CanvasConfig } from '@memvid/canvas-core/config/client';

/**
 * Inject or update favicon based on config
 *
 * @param config - Canvas configuration
 */
export function injectFavicon(config: CanvasConfig): void {
  if (typeof document === 'undefined') {
    return; // Server-side rendering
  }

  const faviconUrl = config.brand.favicon;
  if (!faviconUrl) {
    return;
  }

  // Find existing favicon link or create new one
  let link = document.querySelector("link[rel='icon']") as HTMLLinkElement | null;
  
  if (!link) {
    link = document.createElement('link');
    link.rel = 'icon';
    document.head.appendChild(link);
  }

  // Update favicon URL
  link.href = faviconUrl;

  // Also update apple-touch-icon if needed
  let appleLink = document.querySelector("link[rel='apple-touch-icon']") as HTMLLinkElement | null;
  if (!appleLink) {
    appleLink = document.createElement('link');
    appleLink.rel = 'apple-touch-icon';
    document.head.appendChild(appleLink);
  }
  appleLink.href = faviconUrl;
}

/**
 * Update page title based on config
 *
 * @param config - Canvas configuration
 * @param pageName - Optional page name to append
 */
export function updatePageTitle(config: CanvasConfig, pageName?: string): void {
  if (typeof document === 'undefined') {
    return;
  }

  const brandName = config.brand.name;
  document.title = pageName ? `${pageName} | ${brandName}` : brandName;
}

/**
 * Inject custom head content
 *
 * @param config - Canvas configuration
 */
export function injectCustomHead(config: CanvasConfig): void {
  if (typeof document === 'undefined') {
    return;
  }

  const customHead = config.advanced?.customHead;
  if (!customHead) {
    return;
  }

  // Create a container for custom head content
  const containerId = 'canvas-custom-head';
  let container = document.getElementById(containerId);
  
  if (!container) {
    container = document.createElement('div');
    container.id = containerId;
    container.style.display = 'none';
    document.head.appendChild(container);
  }

  // Parse and inject custom head content
  container.innerHTML = customHead;
  
  // Move actual elements to head
  Array.from(container.children).forEach((child) => {
    document.head.appendChild(child.cloneNode(true));
  });
}

