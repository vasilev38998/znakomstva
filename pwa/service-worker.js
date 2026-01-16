const CACHE_NAME = 'znak-cache-v1';
const OFFLINE_URL = '/offline';
const ASSETS = [
    '/',
    '/offline',
    '/assets/css/app.css',
    '/assets/js/app.js',
    '/pwa/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    event.respondWith(
        caches.match(event.request).then((cached) =>
            cached ||
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        )
    );
});

self.addEventListener('push', (event) => {
    const payload = event.data ? event.data.json() : { title: 'Новый сигнал', body: 'Откройте Znakomstva' };
    const options = {
        body: payload.body,
        icon: '/assets/icons/icon.svg',
        data: payload.data || {}
    };
    event.waitUntil(self.registration.showNotification(payload.title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
