/**
 * Canvas Configuration Validator
 *
 * Validates Canvas configuration and provides helpful error messages and warnings.
 */

import type { PartialCanvasConfig } from './schema.js';

export interface ValidationResult {
  /** Whether the config is valid */
  valid: boolean;
  /** List of validation errors */
  errors: string[];
  /** List of validation warnings */
  warnings: string[];
}

/**
 * Validate Canvas configuration
 *
 * @param config - Configuration to validate (can be partial)
 * @returns Validation result with errors and warnings
 *
 * @example
 * ```ts
 * const result = validateConfig(myConfig);
 * if (!result.valid) {
 *   console.error('Config errors:', result.errors);
 * }
 * if (result.warnings.length > 0) {
 *   console.warn('Config warnings:', result.warnings);
 * }
 * ```
 */
export function validateConfig(config: unknown): ValidationResult {
  const errors: string[] = [];
  const warnings: string[] = [];

  // Type check
  if (!config || typeof config !== 'object') {
    return {
      valid: false,
      errors: ['Configuration must be an object'],
      warnings: [],
    };
  }

  const cfg = config as PartialCanvasConfig;

  // === BRAND VALIDATION ===
  if (cfg.brand) {
    if (!cfg.brand.name || typeof cfg.brand.name !== 'string' || cfg.brand.name.trim() === '') {
      errors.push('brand.name is required and must be a non-empty string');
    }

    if (cfg.brand.logo) {
      if (typeof cfg.brand.logo === 'string') {
        // Validate URL format (basic check)
        if (!cfg.brand.logo.startsWith('/') && !cfg.brand.logo.startsWith('http')) {
          warnings.push('brand.logo should be a URL path (starting with /) or full URL');
        }
      } else if (typeof cfg.brand.logo === 'object') {
        if (!cfg.brand.logo.light && !cfg.brand.logo.dark) {
          errors.push('brand.logo object must have at least one of: light, dark');
        }
      } else {
        errors.push('brand.logo must be a string (URL) or object with light/dark properties');
      }
    } else {
      warnings.push('Consider adding brand.logo for better branding');
    }

    if (cfg.brand.favicon && typeof cfg.brand.favicon !== 'string') {
      errors.push('brand.favicon must be a string (URL)');
    }

    if (cfg.brand.supportEmail && !cfg.brand.supportEmail.includes('@')) {
      warnings.push('brand.supportEmail should be a valid email address');
    }
  } else {
    warnings.push('Consider adding brand configuration');
  }

  // === THEME VALIDATION ===
  if (cfg.theme) {
    if (cfg.theme.mode && !['light', 'dark', 'system'].includes(cfg.theme.mode)) {
      errors.push('theme.mode must be one of: light, dark, system');
    }

    if (cfg.theme.colors) {
      if (!cfg.theme.colors.primary || typeof cfg.theme.colors.primary !== 'string') {
        errors.push('theme.colors.primary is required and must be a string (hex color)');
      } else if (!/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(cfg.theme.colors.primary)) {
        warnings.push('theme.colors.primary should be a valid hex color (e.g., #818cf8)');
      }

      // Validate other colors if provided
      const colorKeys = ['accent', 'background', 'surface', 'border', 'text', 'textMuted', 'success', 'error', 'warning'] as const;
      for (const key of colorKeys) {
        const color = cfg.theme.colors[key];
        if (color && typeof color === 'string' && !/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(color)) {
          warnings.push(`theme.colors.${key} should be a valid hex color`);
        }
      }
    }

    if (cfg.theme.radius && !['none', 'sm', 'md', 'lg', 'full'].includes(cfg.theme.radius)) {
      errors.push('theme.radius must be one of: none, sm, md, lg, full');
    }

    if (cfg.theme.preset && !['default', 'ocean', 'forest', 'sunset', 'corporate'].includes(cfg.theme.preset)) {
      errors.push('theme.preset must be one of: default, ocean, forest, sunset, corporate');
    }
  }

  // === LAYOUT VALIDATION ===
  if (cfg.layout) {
    if (cfg.layout.sidebar) {
      if (cfg.layout.sidebar.width !== undefined) {
        if (typeof cfg.layout.sidebar.width !== 'number' || cfg.layout.sidebar.width < 0) {
          errors.push('layout.sidebar.width must be a positive number');
        }
      }

      if (cfg.layout.sidebar.position && !['left', 'right'].includes(cfg.layout.sidebar.position)) {
        errors.push('layout.sidebar.position must be one of: left, right');
      }
    }

    if (cfg.layout.content) {
      if (cfg.layout.content.maxWidth !== undefined) {
        if (cfg.layout.content.maxWidth !== 'full' && (typeof cfg.layout.content.maxWidth !== 'number' || cfg.layout.content.maxWidth < 0)) {
          errors.push('layout.content.maxWidth must be a positive number or "full"');
        }
      }

      if (cfg.layout.content.padding !== undefined) {
        if (typeof cfg.layout.content.padding !== 'number' || cfg.layout.content.padding < 0) {
          errors.push('layout.content.padding must be a positive number');
        }
      }
    }
  }

  // === FEATURES VALIDATION ===
  if (cfg.features) {
    const featureKeys = ['search', 'chat', 'dashboard', 'settings', 'setupWizard'] as const;
    for (const key of featureKeys) {
      const feature = cfg.features[key];
      if (feature !== undefined && typeof feature !== 'object') {
        errors.push(`features.${key} must be an object with enabled property`);
      }
    }
  }

  // === NAVIGATION VALIDATION ===
  if (cfg.navigation) {
    if (cfg.navigation.items) {
      if (!Array.isArray(cfg.navigation.items)) {
        errors.push('navigation.items must be an array');
      } else {
        cfg.navigation.items.forEach((item, index) => {
          if (!item.id || typeof item.id !== 'string') {
            errors.push(`navigation.items[${index}].id is required and must be a string`);
          }
          if (!item.label || typeof item.label !== 'string') {
            errors.push(`navigation.items[${index}].label is required and must be a string`);
          }
          if (item.href && typeof item.href !== 'string') {
            errors.push(`navigation.items[${index}].href must be a string`);
          }
          if (item.icon && typeof item.icon !== 'string') {
            errors.push(`navigation.items[${index}].icon must be a string`);
          }
        });
      }
    }
  }

  // === TEXT/I18N VALIDATION ===
  if (cfg.text) {
    if (cfg.text.locale && typeof cfg.text.locale !== 'string') {
      errors.push('text.locale must be a string (e.g., "en", "es")');
    }

    if (cfg.text.overrides && typeof cfg.text.overrides !== 'object') {
      errors.push('text.overrides must be an object with text key-value pairs');
    }
  }

  // === LLM VALIDATION ===
  if (cfg.llm) {
    if (!cfg.llm.provider || !['openai', 'anthropic', 'google'].includes(cfg.llm.provider)) {
      errors.push('llm.provider is required and must be one of: openai, anthropic, google');
    }

    if (cfg.llm.apiKey && typeof cfg.llm.apiKey !== 'string') {
      errors.push('llm.apiKey must be a string');
    } else if (!cfg.llm.apiKey) {
      warnings.push('llm.apiKey is not set - make sure to set it via environment variable or config');
    }

    if (cfg.llm.model && typeof cfg.llm.model !== 'string') {
      errors.push('llm.model must be a string');
    }

    if (cfg.llm.temperature !== undefined) {
      if (typeof cfg.llm.temperature !== 'number' || cfg.llm.temperature < 0 || cfg.llm.temperature > 2) {
        warnings.push('llm.temperature should be between 0 and 2');
      }
    }

    if (cfg.llm.maxTokens !== undefined) {
      if (typeof cfg.llm.maxTokens !== 'number' || cfg.llm.maxTokens < 1) {
        errors.push('llm.maxTokens must be a positive number');
      }
    }
  } else {
    errors.push('llm configuration is required');
  }

  // === MEMORY VALIDATION ===
  if (cfg.memory) {
    if (typeof cfg.memory === 'string') {
      // String path is valid
      if (cfg.memory.trim() === '') {
        errors.push('memory path cannot be empty');
      }
    } else if (typeof cfg.memory === 'object' && cfg.memory !== null) {
      if (!cfg.memory.path || typeof cfg.memory.path !== 'string') {
        errors.push('memory.path is required and must be a string');
      }
    } else {
      errors.push('memory must be a string (path) or object with path property');
    }
  } else {
    warnings.push('memory configuration not set - will use default path: ./data/memory.mv2');
  }

  // === API VALIDATION ===
  if (cfg.api) {
    if (cfg.api.basePath && typeof cfg.api.basePath !== 'string') {
      errors.push('api.basePath must be a string');
    }

    if (cfg.api.timeout !== undefined) {
      if (typeof cfg.api.timeout !== 'number' || cfg.api.timeout < 0) {
        errors.push('api.timeout must be a positive number');
      }
    }
  }

  return {
    valid: errors.length === 0,
    errors,
    warnings,
  };
}

