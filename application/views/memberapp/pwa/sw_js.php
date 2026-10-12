/* TAPSTEMCO Member Loan App service worker - v<?php echo $version; ?> */
'use strict';

const VERSION = '<?php echo $version; ?>';
const BASE = <?php echo json_encode($base); ?>;
const SCOPE = BASE + 'customer_login/';
const APP_URL = SCOPE + 'app';
const OFFLINE_URL = SCOPE + 'offline';
const STATIC_CACHE = 'tap-loans-static-' + VERSION;
const PAGE_CACHE = 'tap-loans-pages';

const PRECACHE = [
  OFFLINE_URL,
  BASE + 'media/memberapp/css/app.css?v=' + VERSION,
  BASE + 'media/memberapp/js/offline-store.js?v=' + VERSION,
  BASE + 'media/memberapp/js/app.js?v=' + VERSION,
  BASE + 'media/memberapp/vendor/chart.umd.min.js?v=' + VERSION,
  BASE + 'media/memberapp/icons/icon-192.png',
  BASE + 'media/memberapp/icons/maskable-192.png',
  BASE + 'media/memberapp/icons/apple-touch-icon.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => cache.addAll(PRECACHE))
      .catch(() => null)
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter((k) => k.startsWith('tap-loans-static-') && k !== STATIC_CACHE).map((k) => caches.delete(k))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('message', (event) => {
  const msg = event.data || {};
  if (msg.type === 'SKIP_WAITING') {
    self.skipWaiting();
  } else if (msg.type === 'CLEAR_PAGES') {
    // Sign-out: the cached app page carries the member's first name.
    event.waitUntil(caches.delete(PAGE_CACHE));
  }
});

function isNetworkOnly(url) {
  const p = url.pathname;
  return p.startsWith(SCOPE + 'api') || p.startsWith(SCOPE + 'pay') || p.startsWith(SCOPE + 'paymongo')
    || p.startsWith(SCOPE + 'logout') || p === SCOPE + 'sw.js';
}

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') {
    return;
  }
  const url = new URL(req.url);
  if (url.origin !== self.location.origin || isNetworkOnly(url)) {
    return;
  }

  if (req.mode === 'navigate') {
    if (url.pathname === APP_URL || url.pathname === APP_URL + '/') {
      event.respondWith(appPage(req));
    } else if (url.pathname.startsWith(SCOPE)) {
      event.respondWith(fetch(req).catch(() => caches.match(OFFLINE_URL)));
    }
    return;
  }

  if (url.pathname.startsWith(BASE + 'media/memberapp/')) {
    event.respondWith(staleWhileRevalidate(req));
  }
});

/** Network first; when offline, the last app page this phone loaded. */
async function appPage(req) {
  const cache = await caches.open(PAGE_CACHE);
  try {
    const res = await fetch(req);
    if (res.ok && !res.redirected) {
      cache.put(APP_URL, res.clone());
    } else if (res.redirected) {
      // Signed out on the server: do not keep showing the old page.
      cache.delete(APP_URL);
    }
    return res;
  } catch (e) {
    const cached = await cache.match(APP_URL);
    return cached || caches.match(OFFLINE_URL);
  }
}

async function staleWhileRevalidate(req) {
  const cache = await caches.open(STATIC_CACHE);
  const cached = await cache.match(req);
  const network = fetch(req).then((res) => {
    if (res.ok) {
      cache.put(req, res.clone());
    }
    return res;
  }).catch(() => cached);
  return cached || network;
}
