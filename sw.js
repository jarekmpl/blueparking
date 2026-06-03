const CACHE_NAME = 'blueparking-v1';

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                './',
                './index.html',
                './css/style.css',
                './js/app.js',
                './favicon.png'
            ]);
        })
    );
});

self.addEventListener('fetch', (e) => {
    // Ignoruj żądania do API - chcemy, żeby zawsze szły do sieci
    if (e.request.url.includes('/api/')) {
        return;
    }
    
    // Prosta strategia: Network first, fallback to cache
    e.respondWith(
        fetch(e.request).catch(() => caches.match(e.request))
    );
});
