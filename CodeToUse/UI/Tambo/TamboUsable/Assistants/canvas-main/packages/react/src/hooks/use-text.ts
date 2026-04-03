/**
 * useText Hook
 *
 * React hook for accessing i18n text with config overrides.
 */

import { useMemo } from 'react';
import { defaultTexts, getText } from '../i18n/default-texts.js';
import { useCanvasConfig } from './use-canvas-config.js';

/**
 * Hook to get text translation function
 *
 * @example
 * ```tsx
 * function MyComponent() {
 *   const t = useText();
 *   return <h1>{t('search.title')}</h1>;
 * }
 * ```
 *
 * @example
 * ```tsx
 * function ResultsCount({ count }: { count: number }) {
 *   const t = useText();
 *   return <span>{t('search.resultsCount', { count })}</span>;
 * }
 * ```
 */
export function useText() {
  const config = useCanvasConfig();

  return useMemo(() => {
    return (key: string, params?: Record<string, string | number>): string => {
      // Check config overrides first
      const override = config.text?.overrides?.[key];
      if (override) {
        let text = override;
        // Replace params in override too
        if (params) {
          Object.entries(params).forEach(([paramKey, value]) => {
            text = text.replace(`{${paramKey}}`, String(value));
          });
        }
        return text;
      }

      // Fall back to default texts
      return getText(key, params);
    };
  }, [config.text?.overrides, config.text?.locale]);
}

/**
 * Hook to get all texts (for debugging)
 */
export function useAllTexts(): Record<string, string> {
  const config = useCanvasConfig();
  const texts = useMemo(() => {
    const all = { ...defaultTexts };
    // Merge overrides
    if (config.text?.overrides) {
      Object.assign(all, config.text.overrides);
    }
    return all;
  }, [config.text?.overrides]);

  return texts;
}

