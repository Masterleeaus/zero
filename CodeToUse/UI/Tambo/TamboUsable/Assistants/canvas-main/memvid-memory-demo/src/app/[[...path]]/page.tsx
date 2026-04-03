'use client';

/**
 * Canvas App - Single Page Handler
 *
 * Optional catch-all route that handles all paths:
 * - / (home)
 * - /setup (setup wizard - shown automatically if not complete)
 * - /settings (settings panel)
 * - Any other paths handled by CanvasShell
 */

import { useRouter, usePathname } from 'next/navigation';
import { CanvasProvider, CanvasShell, useCanvas } from '@memvid/canvas-react/components';

function CanvasApp() {
  const router = useRouter();
  const { settings, config } = useCanvas();

  return (
    <CanvasShell
      settings={settings}
      config={config}
      onNavigate={(path) => router.push(path)}
    />
  );
}

export default function Page() {
  const router = useRouter();
  const pathname = usePathname();

  return (
    <CanvasProvider
      pathname={pathname}
      onNavigate={(path) => router.push(path)}
    >
      <CanvasApp />
    </CanvasProvider>
  );
}
