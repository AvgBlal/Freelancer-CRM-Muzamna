/**
 * Service Worker — FCM CRM
 * Cache-first for static assets, network-first for pages
 */

const CACHE_NAME = 'fcm-v1';
const PRECACHE_URLS = [
    '/assets/css/main.css',
    '/assets/icons/icon.svg',
];

// Install — pre-cache static assets
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(PRECACHE_URLS);
        })
    );
    self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (names) {
            return Promise.all(
                names.filter(function (name) { return name !== CACHE_NAME; })
                     .map(function (name) { return caches.delete(name); })
            );
        })
    );
    self.clients.claim();
});

// Fetch strategy
self.addEventListener('fetch', function (event) {
    var url = new URL(event.request.url);

    // Skip cross-origin requests (CDNs handle their own caching)
    if (url.origin !== self.location.origin) return;

    // Skip POST requests
    if (event.request.method !== 'GET') return;

    // Static assets (/assets/) — cache-first
    if (url.pathname.startsWith('/assets/')) {
        event.respondWith(
            caches.match(event.request).then(function (cached) {
                if (cached) {
                    // Update cache in background
                    fetch(event.request).then(function (response) {
                        if (response.ok) {
                            caches.open(CACHE_NAME).then(function (cache) {
                                cache.put(event.request, response);
                            });
                        }
                    }).catch(function () {});
                    return cached;
                }
                return fetch(event.request).then(function (response) {
                    if (response.ok) {
                        var clone = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) {
                            cache.put(event.request, clone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // HTML navigation — network-first
    if (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(event.request).then(function (response) {
                if (response.ok) {
                    var clone = response.clone();
                    caches.open(CACHE_NAME).then(function (cache) {
                        cache.put(event.request, clone);
                    });
                }
                return response;
            }).catch(function () {
                return caches.match(event.request).then(function (cached) {
                    return cached || new Response(
                        '<div dir="rtl" style="text-align:center;padding:3rem;font-family:sans-serif;">' +
                        '<h1 style="color:#2563eb;font-size:2rem;">غير متصل</h1>' +
                        '<p style="color:#6b7280;margin:1rem 0;">لا يوجد اتصال بالإنترنت</p>' +
                        '<a href="/dashboard" style="color:#2563eb;">حاول مرة أخرى</a></div>',
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                });
            })
        );
        return;
    }
});
