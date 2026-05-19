// Service Worker para UniMaps PWA
const CACHE_NAME = 'unimaps-v2';
const urlsToCache = [
  '/',
  '/manifest.json'
];

// Evento de instalación
self.addEventListener('install', (event) => {
  console.log('[SW] Instalando Service Worker...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Archivos en caché');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.error('[SW] Error al cachear:', error);
      })
  );
  self.skipWaiting();
});

// Evento de activación
self.addEventListener('activate', (event) => {
  console.log('[SW] Service Worker activado');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[SW] Eliminando caché antigua:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Evento fetch - Estrategia Network First
self.addEventListener('fetch', (event) => {
  // Solo cachear peticiones GET
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Clonar la respuesta
        const responseToCache = response.clone();
        
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, responseToCache);
        });
        
        return response;
      })
      .catch(() => {
        // Si falla la red, buscar en caché
        return caches.match(event.request);
      })
  );
});

// Escuchar mensajes desde la página para forzar activación inmediata
self.addEventListener('message', (event) => {
  if (!event.data) return;
  if (event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
