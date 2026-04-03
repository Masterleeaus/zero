/**
 * Titan Zero Service Worker — v1
 * Strategies:
 *   - Navigation requests : network-first → offline fallback
 *   - Same-origin GET     : stale-while-revalidate
 *   - Manifest / icons    : cache-first
 *   - Cross-origin / non-GET : network-only
 */

const CACHE_NAME   = 'titan-zero-v1';
const OFFLINE_URL  = '/offline';

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
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// ── Message ────────────────────────────────────────────────────────────────
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

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
  const response = await fetch(request);
  if (response.ok) {
    const cache = await caches.open(CACHE_NAME);
    cache.put(request, response.clone());
  }
  return response;
}

async function networkFirstWithOfflineFallback(request) {
  try {
    const response = await fetch(request);
    // Cache successful navigation responses for offline use
    if (response.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    const cached = await caches.match(request);
    if (cached) return cached;
    // Return the pre-cached offline page
    return caches.match(OFFLINE_URL);
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
