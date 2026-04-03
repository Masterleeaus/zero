/**
 * Titan Zero Service Worker — v2
 * Strategies:
 *   - Navigation requests : network-first → offline fallback
 *   - Same-origin GET     : stale-while-revalidate
 *   - Manifest / icons    : cache-first
 *   - Cross-origin / non-GET : network-only
 *
 * Phase 2 additions:
 *   - Background Sync API registration (graceful fallback)
 *   - Update-available notification to client
 *   - Safe cache version rotation
 *   - Skip-waiting via postMessage
 */

const CACHE_VERSION = 'titan-zero-v2';
const CACHE_NAME    = CACHE_VERSION;
const OFFLINE_URL   = '/offline';
const BG_SYNC_TAG   = 'titan-signal-sync';

// Core shell assets — only stable URLs that won't change with Vite hashes
const SHELL_ASSETS = [
  '/offline',
  '/manifest.webmanifest',
  '/pwa-runtime/db.js',
  '/pwa-runtime/signalQueue.js',
  '/pwa-runtime/sync.js',
  '/pwa-runtime/runtime.js',
  '/pwa-runtime/ui.js',
];

// ── Install ────────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(
        SHELL_ASSETS.map((url) => new Request(url, { credentials: 'same-origin' }))
      );
    })
  );
  self.skipWaiting();
});

// ── Activate ───────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
  event.waitUntil(
    Promise.all([
      // Evict all caches not matching current version
      caches.keys().then((keys) =>
        Promise.all(
          keys
            .filter((key) => key !== CACHE_NAME)
            .map((key) => caches.delete(key))
        )
      ),
      self.clients.claim(),
    ])
  );
});

// ── Message ────────────────────────────────────────────────────────────────
self.addEventListener('message', (event) => {
  if (!event.data) return;

  switch (event.data.type) {
    case 'SKIP_WAITING':
      self.skipWaiting();
      break;

    case 'TRIGGER_SYNC':
      // Client requested an immediate sync trigger
      if ('SyncManager' in self) {
        event.waitUntil(
          self.registration.sync.register(BG_SYNC_TAG).catch(() => {
            // Background sync not available — notify client to fallback
            notifyClients({ type: 'SYNC_FALLBACK' });
          })
        );
      } else {
        notifyClients({ type: 'SYNC_FALLBACK' });
      }
      break;

    default:
      break;
  }
});

// ── Background Sync ────────────────────────────────────────────────────────
self.addEventListener('sync', (event) => {
  if (event.tag === BG_SYNC_TAG) {
    event.waitUntil(
      notifyClients({ type: 'BG_SYNC_TRIGGERED', tag: BG_SYNC_TAG })
    );
  }
});

// ── Online/connection change (via fetch success heuristic) ─────────────────
// Browsers don't fire a 'connection' event in SW, but after a successful
// navigation fetch we broadcast an online-restore hint to the runtime.
let _wasOffline = false;

// ── Fetch ──────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Never intercept non-GET requests (POST/PUT/PATCH/DELETE carry CSRF tokens)
  if (request.method !== 'GET') return;

  // Never intercept cross-origin requests
  if (url.origin !== self.location.origin) return;

  // Cache-first for manifest and icon assets
  if (
    url.pathname === '/manifest.webmanifest' ||
    url.pathname.startsWith('/images/pwa-icon')
  ) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Network-first for navigation (HTML pages), fallback to offline page
  if (request.mode === 'navigate') {
    event.respondWith(networkFirstWithOfflineFallback(request));
    return;
  }

  // Stale-while-revalidate for all other same-origin GET requests
  event.respondWith(staleWhileRevalidate(request));
});

// ── Strategy helpers ───────────────────────────────────────────────────────

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    return new Response('Asset unavailable offline', { status: 503 });
  }
}

async function networkFirstWithOfflineFallback(request) {
  try {
    const response = await fetch(request);
    // Cache successful navigation responses for offline use
    if (response.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, response.clone());

      // Restore after offline
      if (_wasOffline) {
        _wasOffline = false;
        notifyClients({ type: 'CONNECTION_RESTORED' });
      }
    }
    return response;
  } catch {
    _wasOffline = true;
    const cached = await caches.match(request);
    if (cached) return cached;
    // Return the pre-cached offline page
    const offlinePage = await caches.match(OFFLINE_URL);
    return offlinePage || new Response('Offline', { status: 503 });
  }
}

async function staleWhileRevalidate(request) {
  const cache  = await caches.open(CACHE_NAME);
  const cached = await cache.match(request);

  const networkFetch = fetch(request).then((response) => {
    if (response.ok) cache.put(request, response.clone());
    return response;
  }).catch(() => null);

  return cached || networkFetch;
}

// ── Helpers ────────────────────────────────────────────────────────────────

async function notifyClients(message) {
  const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
  clients.forEach((client) => client.postMessage(message));
}
