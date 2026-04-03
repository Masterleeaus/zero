/**
 * Canvas Slots Example
 *
 * Demonstrates how to use the slot system to customize Canvas components.
 */

'use client';

import React from 'react';
import { Canvas, type AppSlots, type LogoSlotProps, type EmptyStateSlotProps } from '@memvid/canvas-react';

// ============================================================================
// Custom Components for Slots
// ============================================================================

/**
 * Custom logo component with animation
 */
function CustomLogo({ brandName }: LogoSlotProps) {
  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      gap: '12px',
      padding: '8px',
    }}>
      <div style={{
        width: '40px',
        height: '40px',
        background: 'linear-gradient(135deg, #6366f1 0%, #a855f7 100%)',
        borderRadius: '10px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: '20px',
        fontWeight: 'bold',
        color: 'white',
        animation: 'pulse 2s infinite',
      }}>
        {brandName.charAt(0).toUpperCase()}
      </div>
      <span style={{
        fontSize: '18px',
        fontWeight: 600,
        color: 'inherit',
      }}>
        {brandName}
      </span>
      <style>{`
        @keyframes pulse {
          0%, 100% { transform: scale(1); }
          50% { transform: scale(1.05); }
        }
      `}</style>
    </div>
  );
}

/**
 * Custom empty state for chat
 */
function CustomChatEmpty({ view }: EmptyStateSlotProps) {
  return (
    <div style={{
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      justifyContent: 'center',
      height: '100%',
      padding: '48px',
      textAlign: 'center',
    }}>
      <div style={{
        fontSize: '64px',
        marginBottom: '24px',
        animation: 'float 3s ease-in-out infinite',
      }}>
        💬
      </div>
      <h3 style={{
        fontSize: '24px',
        fontWeight: 600,
        marginBottom: '12px',
        color: 'inherit',
      }}>
        Your AI Assistant is Ready
      </h3>
      <p style={{
        fontSize: '16px',
        opacity: 0.7,
        maxWidth: '400px',
      }}>
        Start a conversation about your documents, code, or any topic.
        I'll help you find what you need.
      </p>
      <style>{`
        @keyframes float {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-10px); }
        }
      `}</style>
    </div>
  );
}

/**
 * Custom sidebar footer with version info
 */
function CustomSidebarFooter() {
  return (
    <div style={{
      padding: '16px',
      borderTop: '1px solid rgba(255,255,255,0.1)',
      fontSize: '12px',
      opacity: 0.5,
    }}>
      <div>Canvas Pro v2.0</div>
      <div>© 2024 Memvid Inc.</div>
    </div>
  );
}

/**
 * Custom announcement banner (beforeContent slot)
 */
function AnnouncementBanner() {
  return (
    <div style={{
      background: 'linear-gradient(90deg, #6366f1 0%, #a855f7 100%)',
      color: 'white',
      padding: '12px 24px',
      textAlign: 'center',
      fontSize: '14px',
      fontWeight: 500,
    }}>
      🎉 New Feature: AI-powered search is now available!{' '}
      <a href="#" style={{ color: 'white', textDecoration: 'underline' }}>
        Learn more →
      </a>
    </div>
  );
}

// ============================================================================
// Example App with Slots
// ============================================================================

// Brand configuration
const brand = {
  name: 'My Custom App',
  tagline: 'AI-powered knowledge base',
  theme: 'dark' as const,
  colors: {
    primary: '#6366f1',
    accent: '#a855f7',
  },
};

// Slots configuration
const slots: AppSlots = {
  // Use custom logo component
  logo: CustomLogo,

  // Use custom chat empty state
  // chatEmpty: CustomChatEmpty, // Uncomment when using CanvasShell directly

  // Use custom sidebar footer
  sidebarFooter: CustomSidebarFooter,

  // Add announcement banner before content
  beforeContent: <AnnouncementBanner />,

  // You can also pass null to hide elements
  // sidebarFooter: null,  // This would hide the sidebar footer entirely
};

/**
 * Example App using slots
 */
export default function SlotsExampleApp() {
  return (
    <Canvas.App
      brand={brand}
      slots={slots}
      memoryEndpoint="/api/canvas"
    />
  );
}

// ============================================================================
// Slot Usage Patterns
// ============================================================================

/**
 * Pattern 1: Static Content
 *
 * Pass a React node directly
 *
 * slots={{
 *   beforeContent: <div>Welcome Banner</div>
 * }}
 */

/**
 * Pattern 2: Component with Props
 *
 * Pass a component that receives slot props
 *
 * slots={{
 *   logo: ({ brandName, logoUrl }) => <MyLogo name={brandName} />
 * }}
 */

/**
 * Pattern 3: Hide Element
 *
 * Pass null to completely hide a slot
 *
 * slots={{
 *   sidebarFooter: null  // Hides footer
 * }}
 */

/**
 * Pattern 4: Replace Entire Template
 *
 * Replace an entire view with custom content
 *
 * slots={{
 *   search: <MyCustomSearchPage />,
 *   dashboard: MyDashboardComponent,
 * }}
 */

