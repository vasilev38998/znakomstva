const CACHE_NAME = 'znak-cache-v2';
const scopeUrl = new URL(self.registration.scope);
const withBasePath = (path) => new URL(path.replace(/^\//, ''), scopeUrl).toString();
const OFFLINE_URL = withBasePath('offline');
const ASSETS = [
    withBasePath(''),
    withBasePath('offline'),
    withBasePath('login'),
    withBasePath('register'),
    withBasePath('assets/css/app.css'),
    withBasePath('assets/js/app.js'),
    withBasePath('assets/icons/icon.svg'),
    withBasePath('pwa/manifest.json')
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
        icon: withBasePath('assets/icons/icon.svg'),
        data: payload.data || {}
    };
    event.waitUntil(self.registration.showNotification(payload.title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url || withBasePath('');
    const resolvedTarget = targetUrl.startsWith('http')
        ? targetUrl
        : targetUrl.startsWith('/')
            ? new URL(targetUrl, scopeUrl.origin).toString()
            : withBasePath(targetUrl);
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === resolvedTarget && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(resolvedTarget);
            }
        })
    );
});
