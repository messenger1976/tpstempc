/*
 * TAPSTEMCO Member Loan App - offline storage.
 * Keeps the last snapshot from /customer_login/api/snapshot on the phone
 * (IndexedDB, falling back to localStorage) so loans can be viewed offline.
 * Everything is removed on sign-out.
 */
(function (w) {
  'use strict';

  var DB_NAME = 'tap-loans';
  var STORE = 'snapshots';
  var LS_PREFIX = 'tap_member_';
  var PREFS_KEY = 'tap_prefs';

  function openDb() {
    return new Promise(function (resolve, reject) {
      if (!w.indexedDB) {
        reject(new Error('no-idb'));
        return;
      }
      var req = w.indexedDB.open(DB_NAME, 1);
      req.onupgradeneeded = function () {
        req.result.createObjectStore(STORE);
      };
      req.onsuccess = function () { resolve(req.result); };
      req.onerror = function () { reject(req.error); };
    });
  }

  function idb(mode, fn) {
    return openDb().then(function (db) {
      return new Promise(function (resolve, reject) {
        var tx = db.transaction(STORE, mode);
        var out = fn(tx.objectStore(STORE));
        tx.oncomplete = function () { db.close(); resolve(out && 'result' in out ? out.result : undefined); };
        tx.onerror = function () { db.close(); reject(tx.error); };
      });
    });
  }

  function lsGet(key) {
    try {
      var raw = w.localStorage.getItem(LS_PREFIX + key);
      return raw ? JSON.parse(raw) : null;
    } catch (e) {
      return null;
    }
  }

  function lsSet(key, value) {
    try {
      w.localStorage.setItem(LS_PREFIX + key, JSON.stringify(value));
    } catch (e) { /* storage full or blocked */ }
  }

  var MAStore = {
    load: function (key) {
      return idb('readonly', function (s) { return s.get(key); })
        .then(function (v) { return v || lsGet(key); })
        .catch(function () { return lsGet(key); });
    },

    save: function (key, snapshot) {
      var record = { saved_at: new Date().toISOString(), snapshot: snapshot };
      return idb('readwrite', function (s) { return s.put(record, key); })
        .then(function () {
          // A small copy in localStorage so a failed IndexedDB still has data.
          try { w.localStorage.removeItem(LS_PREFIX + key); } catch (e) { /* ignore */ }
        })
        .catch(function () { lsSet(key, record); });
    },

    prefs: function () {
      try {
        return JSON.parse(w.localStorage.getItem(PREFS_KEY) || '{}') || {};
      } catch (e) {
        return {};
      }
    },

    setPrefs: function (patch) {
      var p = MAStore.prefs();
      Object.keys(patch).forEach(function (k) {
        if (patch[k] === null) {
          delete p[k];
        } else {
          p[k] = patch[k];
        }
      });
      try { w.localStorage.setItem(PREFS_KEY, JSON.stringify(p)); } catch (e) { /* ignore */ }
      return p;
    },

    /** Remove every trace of the member from this phone. */
    clearAll: function () {
      try {
        Object.keys(w.localStorage).forEach(function (k) {
          if (k.indexOf('tap_') === 0) {
            w.localStorage.removeItem(k);
          }
        });
        w.sessionStorage.clear();
      } catch (e) { /* ignore */ }
      if (navigator.serviceWorker && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_PAGES' });
      }
      if (w.caches) {
        w.caches.delete('tap-loans-pages');
      }
      return new Promise(function (resolve) {
        if (!w.indexedDB) {
          resolve();
          return;
        }
        var req = w.indexedDB.deleteDatabase(DB_NAME);
        req.onsuccess = req.onerror = req.onblocked = function () { resolve(); };
      });
    },

    /** SHA-256 hex (for the optional app PIN; the PIN itself is never stored). */
    hash: function (text) {
      if (!w.crypto || !w.crypto.subtle) {
        return Promise.resolve(null);
      }
      var data = new TextEncoder().encode(text);
      return w.crypto.subtle.digest('SHA-256', data).then(function (buf) {
        return Array.prototype.map.call(new Uint8Array(buf), function (b) {
          return ('0' + b.toString(16)).slice(-2);
        }).join('');
      });
    },

    registerServiceWorker: function (base, onUpdate) {
      if (!('serviceWorker' in navigator)) {
        return;
      }
      var scope = new URL(base).pathname + 'customer_login/';
      navigator.serviceWorker.register(scope + 'sw.js', { scope: scope }).then(function (reg) {
        function watch(worker) {
          worker.addEventListener('statechange', function () {
            if (worker.state === 'installed' && navigator.serviceWorker.controller && onUpdate) {
              onUpdate(worker);
            }
          });
        }
        if (reg.waiting && navigator.serviceWorker.controller && onUpdate) {
          onUpdate(reg.waiting);
        }
        reg.addEventListener('updatefound', function () {
          if (reg.installing) {
            watch(reg.installing);
          }
        });
      }).catch(function () { /* http on a LAN IP: no service worker */ });

      var reloading = false;
      navigator.serviceWorker.addEventListener('controllerchange', function () {
        if (!reloading && w.MA_RELOAD_ON_UPDATE) {
          reloading = true;
          w.location.reload();
        }
      });
    }
  };

  w.MAStore = MAStore;
})(window);
