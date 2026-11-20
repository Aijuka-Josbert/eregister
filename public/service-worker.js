/**
 * LMO Digital Records Management System - Secure Service Worker
 *
 * SECURITY RULE:
 * - Only caches non-sensitive static UI shell assets (CSS, JS, SVG icons).
 * - NEVER caches authenticated HTML pages, API responses, or private document previews/downloads.
 */

const CACHE_NAME = 'lmo-drms-static-v3';
const STATIC_ASSET_SUFFIXES = [
    '/css/civic-institutional.css',
    '/js/drms-app.js',
    '/images/lmo-icon.svg',
    '/manifest.json'
];

self.addEventListener('install', () => {
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

    if (req.method !== 'GET') {
        return;
    }

    const isStaticAsset = STATIC_ASSET_SUFFIXES.some((suffix) => url.pathname.endsWith(suffix));

    // Cache-first ONLY for explicitly whitelisted static UI shell assets
    if (isStaticAsset) {
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) =>
                cache.match(req).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    return fetch(req).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            cache.put(req, networkResponse.clone());
                        }
                        return networkResponse;
                    });
                })
            )
        );
    }
});
