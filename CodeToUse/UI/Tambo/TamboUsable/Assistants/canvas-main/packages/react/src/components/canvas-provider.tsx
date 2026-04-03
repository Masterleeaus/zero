'use client';

/**
 * Canvas Provider
 *
 * Client-side wrapper that handles:
 * - Setup detection and redirect
 * - Settings management
 * - Theme application
 * - Settings panel integration
 */

import { useEffect, useState, createContext, useContext, type ReactNode } from 'react';
import { useCanvasSettings, applyTheme } from '../hooks/use-canvas-settings.js';
import { SettingsPanel } from './settings-panel.js';
import { SetupWizard } from './setup-wizard.js';
import type { RuntimeSettings, CanvasUIConfig } from '@memvid/canvas-core/types-only';

interface CanvasContextValue {
  settings: RuntimeSettings;
  config: Partial<CanvasUIConfig>;
  updateSettings: (updates: Partial<RuntimeSettings>) => void;
  updateConfig: (updates: Partial<CanvasUIConfig>) => void;
  openSettings: () => void;
  closeSettings: () => void;
  isSettingsOpen: boolean;
  isSetupComplete: boolean;
  completeSetup: () => void;
  resetSettings: () => void;
  /** Navigate to setup wizard for reconfiguration */
  goToSetup: () => void;
  /** Current pathname */
  pathname?: string;
  isLoading: boolean;
}

const CanvasContext = createContext<CanvasContextValue | null>(null);

export function useCanvas() {
  const ctx = useContext(CanvasContext);
  if (!ctx) {
    throw new Error('useCanvas must be used within CanvasProvider');
  }
  return ctx;
}

export interface CanvasProviderProps {
  children: ReactNode;
  /** Skip setup check and always show children */
  skipSetupCheck?: boolean;
  /** Current pathname (for route-based UI) */
  pathname?: string;
  /** Navigation function */
  onNavigate?: (path: string) => void;
  /** Setup page path (default: '/setup') */
  setupPath?: string;
  /** Settings page path (default: '/settings') */
  settingsPath?: string;
}

export function CanvasProvider({
  children,
  skipSetupCheck = false,
  pathname,
  onNavigate,
  setupPath = '/setup',
  settingsPath = '/settings',
}: CanvasProviderProps) {
  const {
    settings,
    config,
    isSetupComplete,
    updateSettings,
    updateConfig,
    completeSetup,
    resetSettings,
    isLoading,
  } = useCanvasSettings();

  const [isSettingsOpen, setIsSettingsOpen] = useState(false);
  const [mounted, setMounted] = useState(false);

  // Handle mounting
  useEffect(() => {
    setMounted(true);
  }, []);

  // Apply theme when config changes
  useEffect(() => {
    if (mounted && config) {
      applyTheme(config);
    }
  }, [config, mounted]);

  // Determine if we should show setup or settings
  const isSetupPage = pathname === setupPath;
  const isSettingsPage = pathname === settingsPath;
  const shouldShowSetup = !skipSetupCheck && (!isSetupComplete || isSetupPage);

  // Auto-open settings when on settings path
  useEffect(() => {
    if (mounted && isSettingsPage && !isSettingsOpen) {
      setIsSettingsOpen(true);
    }
  }, [mounted, isSettingsPage, isSettingsOpen]);

  // Show loading state
  if (!mounted || isLoading) {
    return (
      <div style={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: '#09090b',
        color: '#fafafa',
      }}>
        <div style={{
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          gap: '1rem',
        }}>
          <div style={{
            width: '40px',
            height: '40px',
            border: '3px solid #27272a',
            borderTopColor: '#818cf8',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite',
          }} />
          <style>{`
            @keyframes spin {
              to { transform: rotate(360deg); }
            }
          `}</style>
        </div>
      </div>
    );
  }

  // Navigate to setup wizard
  const goToSetup = () => {
    if (onNavigate) {
      onNavigate(setupPath);
    }
  };

  const contextValue: CanvasContextValue = {
    settings,
    config,
    updateSettings,
    updateConfig,
    openSettings: () => setIsSettingsOpen(true),
    closeSettings: () => setIsSettingsOpen(false),
    isSettingsOpen,
    isSetupComplete,
    completeSetup,
    resetSettings,
    goToSetup,
    pathname,
    isLoading,
  };

  // Handle setup completion
  const handleSetupComplete = () => {
    completeSetup();
    if (onNavigate && pathname === setupPath) {
      onNavigate('/');
    }
  };

  // Handle going back from setup (when reconfiguring)
  const handleSetupBack = () => {
    if (onNavigate) {
      onNavigate('/');
    }
  };

  // Show SetupWizard when needed
  if (shouldShowSetup) {
    // If setup is complete but user is on /setup, they're reconfiguring
    const isReconfiguring = isSetupComplete && isSetupPage;

    return (
      <CanvasContext.Provider value={contextValue}>
        <SetupWizard
          onComplete={handleSetupComplete}
          onSkip={isReconfiguring ? handleSetupBack : handleSetupComplete}
          isReconfigure={isReconfiguring}
          initialConfig={isReconfiguring ? config : undefined}
        />
      </CanvasContext.Provider>
    );
  }

  // Handle settings panel close
  const handleSettingsClose = () => {
    setIsSettingsOpen(false);
    if (isSettingsPage && onNavigate) {
      onNavigate('/');
    }
  };

  // Handle going to setup from settings
  const handleGoToSetup = () => {
    setIsSettingsOpen(false);
    goToSetup();
  };

  // Handle reset from settings
  const handleReset = () => {
    resetSettings();
    setIsSettingsOpen(false);
    // Navigate to setup after reset
    if (onNavigate) {
      onNavigate(setupPath);
    }
  };

  return (
    <CanvasContext.Provider value={contextValue}>
      {children}
      <SettingsPanel
        isOpen={isSettingsOpen}
        onClose={handleSettingsClose}
        onSave={(newSettings) => {
          updateSettings(newSettings);
          handleSettingsClose();
        }}
        initialSettings={settings}
        onGoToSetup={handleGoToSetup}
        onReset={handleReset}
      />
    </CanvasContext.Provider>
  );
}

export default CanvasProvider;
