/**
 * LMO Digital Records Management System - Secure Service Worker
 *
 * SECURITY RULE:
 * - Only caches non-sensitive static UI shell assets (CSS, JS, SVG icons).
 * - NEVER caches authenticated HTML pages, API responses, or private document previews/downloads.
 */

const CACHE_NAME = 'lmo-drms-static-v1';
const STATIC_ASSETS = [
    '/css/civic-institutional.css',
    '/js/drms-app.js',
    '/images/lmo-icon.svg',
    '/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    const url = new URL(req.url);

    // Never intercept non-GET requests or private document/audit/auth endpoints
    if (
        req.method !== 'GET' ||
        url.pathname.startsWith('/documents') ||
        url.pathname.startsWith('/archives') ||
        url.pathname.startsWith('/audit-logs') ||
        url.pathname.startsWith('/reports') ||
        url.pathname.startsWith('/users') ||
        url.pathname.startsWith('/dashboard') ||
        url.pathname.startsWith('/login')
    ) {
        return;
    }

    // Cache-first only for explicitly whitelisted static assets
    if (STATIC_ASSETS.includes(url.pathname)) {
        event.respondWith(
            caches.match(req).then((cachedResponse) => {
                return cachedResponse || fetch(req);
            })
        );
    }
});
