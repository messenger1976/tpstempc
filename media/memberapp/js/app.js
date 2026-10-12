/*
 * TAPSTEMCO Member Loan App (PWA).
 * Every screen is drawn from one "snapshot" (GET api/snapshot) that is kept on
 * the phone, so the app opens and shows loans without a connection.
 * Paying and applying need a connection and always go through the server.
 */
(function (w, d) {
  'use strict';

  var MA = w.MA;
  var Store = w.MAStore;
  var ROOT = MA.base + 'customer_login/';
  var view = d.getElementById('maView');
  var titleEl = d.getElementById('maTitle');
  var kickerEl = d.getElementById('maKicker');
  var backBtn = d.querySelector('[data-back]');
  var syncBtn = d.getElementById('maSync');
  var offlineBar = d.getElementById('maOffline');

  var state = {
    snap: null,
    savedAt: null,
    syncing: false,
    charts: [],
    pollTimer: null,
    countdownTimer: null,
    apply: null
  };

  // ------------------------------------------------------------------
  // Helpers
  // ------------------------------------------------------------------

  function h(v) {
    return String(v === null || v === undefined ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function peso(n, noSign) {
    var v = Number(n || 0);
    var s = v.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return noSign ? s : '\u20B1' + s;
  }

  function pesoShort(n) {
    var v = Number(n || 0);
    if (Math.abs(v) >= 1e6) return '\u20B1' + (v / 1e6).toFixed(1) + 'M';
    if (Math.abs(v) >= 1e3) return '\u20B1' + (v / 1e3).toFixed(1) + 'k';
    return '\u20B1' + v.toFixed(0);
  }

  function fmtDate(s, withYear) {
    if (!s) return '—';
    var dt = new Date(String(s).slice(0, 10) + 'T00:00:00');
    if (isNaN(dt)) return h(s);
    return dt.toLocaleDateString('en-PH', withYear === false ? { month: 'short', day: 'numeric' } : { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function daysUntil(s) {
    if (!s) return null;
    var dt = new Date(String(s).slice(0, 10) + 'T00:00:00');
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    return Math.round((dt - today) / 86400000);
  }

  function ago(iso) {
    if (!iso) return 'never';
    var s = Math.round((Date.now() - new Date(iso).getTime()) / 1000);
    if (s < 60) return 'just now';
    if (s < 3600) return Math.floor(s / 60) + ' min ago';
    if (s < 86400) return Math.floor(s / 3600) + ' h ago';
    return new Date(iso).toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
  }

  function pill(kind, text) {
    return '<span class="ma-pill ma-pill-' + h(kind) + '">' + h(text) + '</span>';
  }

  function toast(msg, kind) {
    var box = d.getElementById('maToasts');
    var el = d.createElement('div');
    el.className = 'ma-toast ma-toast-' + (kind || 'info');
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(function () { el.classList.add('is-out'); }, 3800);
    setTimeout(function () { el.remove(); }, 4300);
  }

  function empty(icon, title, text, action) {
    return '<div class="ma-empty"><div class="ma-empty-icon">' + icon + '</div><h3>' + h(title) + '</h3><p>' + h(text) + '</p>' + (action || '') + '</div>';
  }

  function isOnline() {
    return navigator.onLine !== false;
  }

  function loanById(lid) {
    if (!state.snap) return null;
    for (var i = 0; i < state.snap.loans.length; i++) {
      if (state.snap.loans[i].LID === lid) return state.snap.loans[i];
    }
    return null;
  }

  function lifecyclePill(l) {
    var map = {
      bb: 'muted', closed: 'muted', active: 'ok', 'past-due': 'danger', rejected: 'danger', mixed: 'danger',
      'new': 'warn', 'eval': 'warn', 'pending-release': 'info', 'released-unposted': 'info'
    };
    var kind = l.has_overdue ? 'danger' : (map[l.pill] || 'info');
    return pill(kind, l.has_overdue ? 'Overdue' : (l.lifecycle_name || l.status_name));
  }

  // ------------------------------------------------------------------
  // Server calls
  // ------------------------------------------------------------------

  function api(path, opts) {
    opts = opts || {};
    var init = { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } };
    if (opts.body) {
      var body = new URLSearchParams();
      body.append('_token', MA.csrf);
      appendParams(body, opts.body, '');
      init.method = 'POST';
      init.body = body;
    }
    return fetch(ROOT + path, init).then(function (res) {
      return res.json().catch(function () { return { ok: false, message: 'Unexpected reply from the server.' }; }).then(function (json) {
        if (res.status === 401) {
          signedOut();
          throw new Error(json.message || 'Please sign in again.');
        }
        if (res.status === 403 && json.error === 'csrf' && !opts.retried) {
          return refreshCsrf().then(function () {
            opts.retried = true;
            return api(path, opts);
          });
        }
        return json;
      });
    });
  }

  function appendParams(body, obj, prefix) {
    Object.keys(obj).forEach(function (k) {
      var v = obj[k];
      var key = prefix ? prefix + '[' + k + ']' : k;
      if (v !== null && typeof v === 'object') {
        appendParams(body, v, key);
      } else if (v !== undefined && v !== null) {
        body.append(key, v);
      }
    });
  }

  function refreshCsrf() {
    return fetch(ROOT + 'api/snapshot', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j && j.csrf) MA.csrf = j.csrf;
        if (j && j.snapshot) setSnapshot(j.snapshot, true);
      });
  }

  function signedOut() {
    w.location.href = ROOT + '?expired=1';
  }

  // ------------------------------------------------------------------
  // Snapshot sync
  // ------------------------------------------------------------------

  function storageKey() {
    return 'm_' + MA.memberKey;
  }

  function setSnapshot(snap, persist) {
    state.snap = snap;
    state.savedAt = snap.synced_at;
    if (persist) {
      Store.save(storageKey(), snap);
    }
    renderChrome();
  }

  function sync(silent) {
    if (state.syncing) return Promise.resolve();
    if (!isOnline()) {
      updateSyncChip();
      return Promise.resolve();
    }
    state.syncing = true;
    updateSyncChip();
    return api('api/snapshot').then(function (j) {
      if (j && j.ok) {
        MA.csrf = j.csrf || MA.csrf;
        setSnapshot(j.snapshot, true);
        route();
        if (!silent) toast('Up to date', 'ok');
      } else if (!silent) {
        toast((j && j.message) || 'Could not refresh.', 'error');
      }
    }).catch(function () {
      if (!silent) toast('Could not reach the server. Showing saved data.', 'warn');
    }).then(function () {
      state.syncing = false;
      updateSyncChip();
    });
  }

  function updateSyncChip() {
    var online = isOnline();
    offlineBar.hidden = online;
    syncBtn.classList.toggle('is-offline', !online);
    syncBtn.classList.toggle('is-syncing', state.syncing);
    var label = state.syncing ? 'Syncing…' : (online ? 'Updated ' + ago(state.savedAt) : 'Offline · ' + ago(state.savedAt));
    syncBtn.querySelector('span').textContent = label;
  }

  function renderChrome() {
    var m = state.snap && state.snap.member;
    var av = d.getElementById('maAvatar');
    if (m) {
      av.innerHTML = m.photo ? '<img src="' + h(m.photo) + '" alt="" onerror="this.remove()">' + '<span>' + h(m.initials) + '</span>' : '<span>' + h(m.initials) + '</span>';
    }
    var reqs = state.snap ? state.snap.comaker_requests.filter(function (r) { return r.status === 'pending'; }).length : 0;
    d.querySelectorAll('[data-nav="menu"]').forEach(function (a) {
      a.classList.toggle('has-badge', reqs > 0);
    });
    updateSyncChip();
  }

  // ------------------------------------------------------------------
  // Router
  // ------------------------------------------------------------------

  var routes = {
    home: renderHome,
    loans: renderLoans,
    loan: renderLoan,
    payments: renderPayments,
    applications: renderApplications,
    pay: renderPay,
    apply: renderApply,
    comakers: renderComakers,
    menu: renderMenu,
    profile: renderProfile
  };

  function parseHash() {
    var raw = (w.location.hash || '#/home').replace(/^#\/?/, '');
    var q = '';
    if (raw.indexOf('?') >= 0) {
      q = raw.slice(raw.indexOf('?') + 1);
      raw = raw.slice(0, raw.indexOf('?'));
    }
    var parts = raw.split('/').filter(Boolean).map(decodeURIComponent);
    return { name: parts[0] || 'home', args: parts.slice(1), query: new URLSearchParams(q) };
  }

  function route() {
    stopTimers();
    destroyCharts();
    closeSheet();
    var r = parseHash();
    var fn = routes[r.name] || renderHome;
    var top = ['home', 'loans', 'pay', 'apply', 'menu'].indexOf(r.name) >= 0 && r.args.length === 0;
    backBtn.hidden = top;
    d.querySelectorAll('[data-nav]').forEach(function (a) {
      var nav = a.getAttribute('data-nav');
      a.classList.toggle('is-active', nav === r.name || (r.name === 'loan' && nav === 'loans'));
    });
    if (!state.snap && ['pay', 'apply'].indexOf(r.name) < 0) {
      setTitle('Loading', '');
      return;
    }
    fn(r.args, r.query);
    view.scrollTop = 0;
    w.scrollTo(0, 0);
  }

  function setTitle(title, kicker) {
    titleEl.textContent = title;
    kickerEl.textContent = kicker === undefined ? '' : kicker;
    d.title = title + ' · TAP Loans';
  }

  function stopTimers() {
    if (state.pollTimer) clearTimeout(state.pollTimer);
    if (state.countdownTimer) clearInterval(state.countdownTimer);
    state.pollTimer = state.countdownTimer = null;
  }

  function destroyCharts() {
    state.charts.forEach(function (c) { try { c.destroy(); } catch (e) { /* ignore */ } });
    state.charts = [];
  }

  // ------------------------------------------------------------------
  // Home / dashboard
  // ------------------------------------------------------------------

  function renderHome(args, query) {
    var s = state.snap;
    var k = s.kpi;
    setTitle('Home', greeting() + ', ' + (MA.firstName || s.member.name.split(' ')[0]));

    var nextDays = daysUntil(k.next_due_date);
    var dueLine = k.next_due_date
      ? 'Next due ' + fmtDate(k.next_due_date, false) + (nextDays !== null ? (nextDays < 0 ? ' · ' + Math.abs(nextDays) + ' days late' : nextDays === 0 ? ' · today' : ' · in ' + nextDays + ' days') : '')
      : (k.active_loans ? 'No upcoming installment' : 'You have no active loans');

    var html = '';
    if (query.get('welcome')) {
      html += '<div class="ma-alert ma-alert-ok">Welcome! Your account is ready. Install the app from the menu to open it from your home screen.</div>';
    }

    html += '<section class="ma-hero">'
      + '<div class="ma-hero-top"><span>Total balance</span>' + (k.has_overdue ? pill('danger', 'Overdue') : (k.active_loans ? pill('ok-light', 'Good standing') : '')) + '</div>'
      + '<div class="ma-hero-amount">' + peso(k.total_outstanding) + '</div>'
      + '<div class="ma-hero-sub">' + h(dueLine) + '</div>'
      + '<div class="ma-hero-due"><div><small>Amount due now</small><strong>' + peso(k.amount_due) + '</strong></div>'
      + (k.active_loans ? '<a class="ma-btn ma-btn-light" href="#/pay' + (k.next_due_lid ? '/' + encodeURIComponent(k.next_due_lid) : '') + '">Pay now</a>' : '<a class="ma-btn ma-btn-light" href="#/apply">Apply for a loan</a>')
      + '</div></section>';

    var pending = s.loans.filter(function (l) { return l.pending_payment; });
    pending.forEach(function (l) {
      html += '<div class="ma-alert ma-alert-info"><strong>Payment received</strong> for ' + h(l.LID) + ' (' + peso(l.pending_payment.amount) + '). It will show on your balance once the office posts it.</div>';
    });
    var reqs = s.comaker_requests.filter(function (r) { return r.status === 'pending'; });
    if (reqs.length) {
      html += '<a class="ma-alert ma-alert-warn ma-alert-link" href="#/comakers"><strong>' + reqs.length + ' co-maker request' + (reqs.length > 1 ? 's' : '') + '</strong> waiting for your answer &rsaquo;</a>';
    }

    html += '<section class="ma-kpis">'
      + kpi('Active loans', k.active_loans, 'loans')
      + kpi('Total paid', pesoShort(k.total_paid), 'payments')
      + kpi('Applications', k.pending_applications, 'applications')
      + '</section>';

    html += '<section class="ma-quick">'
      + quick('#/pay', 'Pay', 'M3 7h18v10H3zM3 11h18')
      + quick('#/apply', 'Apply', 'M12 5v14M5 12h14')
      + quick('#/loans', 'Ledger', 'M4 5h16v14H4zM4 9h16M8 13h4')
      + quick('#/payments', 'History', 'M5 4h14v16l-3-2-2 2-2-2-2 2-2-2-3 2z')
      + '</section>';

    if (s.upcoming.length) {
      html += '<section class="ma-card"><header class="ma-card-head"><h3>Upcoming dues</h3><a href="#/loans" class="ma-link">All loans</a></header><ul class="ma-list">';
      s.upcoming.slice(0, 4).forEach(function (u) {
        var dd = daysUntil(u.date);
        html += '<li class="ma-list-item"><div class="ma-date-chip' + (dd < 0 ? ' is-late' : '') + '"><b>' + new Date(u.date + 'T00:00:00').getDate() + '</b><small>' + new Date(u.date + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short' }) + '</small></div>'
          + '<div class="ma-list-main"><strong>' + h(u.product || u.LID) + '</strong><small>' + h(u.LID) + ' · Installment #' + h(u.installment) + '</small></div>'
          + '<div class="ma-list-amt">' + peso(u.amount) + '</div></li>';
      });
      html += '</ul></section>';
    }

    html += '<section class="ma-card"><header class="ma-card-head"><h3>Payments, last 12 months</h3></header><div class="ma-chart"><canvas id="chPayments"></canvas></div></section>';
    html += '<div class="ma-grid-2">'
      + '<section class="ma-card"><header class="ma-card-head"><h3>Principal balance trend</h3></header><div class="ma-chart"><canvas id="chBalance"></canvas></div></section>'
      + '<section class="ma-card"><header class="ma-card-head"><h3>Where your payments went</h3></header><div class="ma-chart ma-chart-sm"><canvas id="chComposition"></canvas></div></section>'
      + '</div>';

    if (s.charts.progress.length) {
      html += '<section class="ma-card"><header class="ma-card-head"><h3>Loan progress</h3></header>';
      s.charts.progress.forEach(function (p) {
        html += '<a class="ma-progress-row" href="#/loan/' + encodeURIComponent(p.LID) + '"><div><strong>' + h(p.product || p.LID) + '</strong><small>' + h(p.LID) + '</small></div>'
          + '<div class="ma-progress"><i style="width:' + Number(p.pct) + '%"></i></div><span>' + Number(p.pct) + '%</span></a>';
      });
      html += '</section>';
    }

    var recent = s.payments.slice(0, 5);
    html += '<section class="ma-card"><header class="ma-card-head"><h3>Recent payments</h3><a href="#/payments" class="ma-link">See all</a></header>';
    html += recent.length ? '<ul class="ma-list">' + recent.map(paymentItem).join('') + '</ul>' : '<p class="ma-muted">No payments yet.</p>';
    html += '</section>';

    view.innerHTML = html;
    drawHomeCharts();
  }

  function greeting() {
    var hr = new Date().getHours();
    return hr < 12 ? 'Good morning' : (hr < 18 ? 'Good afternoon' : 'Good evening');
  }

  function kpi(label, value, link) {
    return '<a class="ma-kpi" href="#/' + link + '"><small>' + h(label) + '</small><strong>' + h(value) + '</strong></a>';
  }

  function quick(href, label, path) {
    return '<a class="ma-quick-item" href="' + href + '"><span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="' + path + '"/></svg></span>' + h(label) + '</a>';
  }

  function whenCharts(fn) {
    if (w.Chart) {
      fn();
      return;
    }
    var tries = 0;
    var t = setInterval(function () {
      tries++;
      if (w.Chart || tries > 40) {
        clearInterval(t);
        if (w.Chart) fn();
      }
    }, 100);
  }

  function drawHomeCharts() {
    whenCharts(function () {
      var c = state.snap.charts;
      var green = '#0f6b3a';
      var gold = '#f2b705';
      Chart.defaults.font.family = getComputedStyle(d.body).fontFamily;
      Chart.defaults.color = '#64748b';
      var money = function (v) { return pesoShort(v); };

      var el = d.getElementById('chPayments');
      if (el) {
        state.charts.push(new Chart(el, {
          type: 'bar',
          data: { labels: c.labels, datasets: [{ data: c.monthly_payments, backgroundColor: green, borderRadius: 6, maxBarThickness: 26 }] },
          options: chartOpts(money)
        }));
      }
      el = d.getElementById('chBalance');
      if (el) {
        var ctx = el.getContext('2d');
        var grad = ctx.createLinearGradient(0, 0, 0, 200);
        grad.addColorStop(0, 'rgba(15,107,58,.28)');
        grad.addColorStop(1, 'rgba(15,107,58,0)');
        state.charts.push(new Chart(el, {
          type: 'line',
          data: { labels: c.labels, datasets: [{ data: c.balance_trend, borderColor: green, backgroundColor: grad, fill: true, tension: .35, pointRadius: 0, borderWidth: 2.5 }] },
          options: chartOpts(money)
        }));
      }
      el = d.getElementById('chComposition');
      if (el) {
        var comp = c.composition;
        var total = comp.principal + comp.interest + comp.penalty;
        if (total <= 0) {
          el.parentNode.innerHTML = '<p class="ma-muted ma-center">No posted payments yet.</p>';
        } else {
          state.charts.push(new Chart(el, {
            type: 'doughnut',
            data: { labels: ['Principal', 'Interest', 'Penalty'], datasets: [{ data: [comp.principal, comp.interest, comp.penalty], backgroundColor: [green, gold, '#dc2626'], borderWidth: 0 }] },
            options: { maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } }, tooltip: { callbacks: { label: function (i) { return i.label + ': ' + peso(i.raw); } } } } }
          }));
        }
      }
    });
  }

  function chartOpts(fmt) {
    return {
      maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (i) { return peso(i.raw); } } } },
      scales: {
        x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 6 } },
        y: { beginAtZero: true, grid: { color: 'rgba(100,116,139,.12)' }, ticks: { callback: fmt, maxTicksLimit: 5 } }
      }
    };
  }

  // ------------------------------------------------------------------
  // Loans
  // ------------------------------------------------------------------

  function loanGroup(l) {
    if (l.is_beginning_balance) return l.is_open ? 'active' : 'closed';
    if (l.status_code === '5') return 'closed';
    if (l.disbursed) return 'active';
    if (['2', '7', '8'].indexOf(String(l.status_code)) >= 0) return 'closed';
    return 'applications';
  }

  function renderLoans(args, query) {
    setTitle('My loans', state.snap.loans.length + ' total');
    var filter = query.get('f') || 'active';
    var loans = state.snap.loans.filter(function (l) { return filter === 'all' || loanGroup(l) === filter; });
    var tabs = [['active', 'Active'], ['applications', 'Applications'], ['closed', 'Closed'], ['all', 'All']];
    var html = '<div class="ma-segment" role="tablist">' + tabs.map(function (t) {
      return '<a role="tab" href="#/loans?f=' + t[0] + '" class="' + (t[0] === filter ? 'is-active' : '') + '">' + t[1] + '</a>';
    }).join('') + '</div>';

    if (!loans.length) {
      html += empty('&#128179;', 'Nothing here', filter === 'active' ? 'You have no active loans.' : 'No loans in this list.', '<a class="ma-btn" href="#/apply">Apply for a loan</a>');
    } else {
      html += loans.map(loanCard).join('');
    }
    view.innerHTML = html;
  }

  function loanCard(l) {
    var open = loanGroup(l) === 'active';
    var sub = open
      ? (l.next_due_date ? 'Next due ' + fmtDate(l.next_due_date, false) + ' · ' + peso(l.next_due_amount) : 'Balance')
      : (loanGroup(l) === 'applications' ? 'Applied ' + fmtDate(l.applicationdate) : 'Amount ' + peso(l.amount));
    return '<a class="ma-loan-card" href="#/loan/' + encodeURIComponent(l.LID) + '">'
      + '<div class="ma-loan-card-top"><div><strong>' + h(l.product || 'Loan') + '</strong><small>' + h(l.LID) + '</small></div>' + lifecyclePill(l) + '</div>'
      + '<div class="ma-loan-card-amt"><span>' + (open ? 'Balance' : 'Loan amount') + '</span><strong>' + peso(open ? l.outstanding : l.amount) + '</strong></div>'
      + (open && !l.is_beginning_balance ? '<div class="ma-progress"><i style="width:' + Number(l.progress_pct) + '%"></i></div>' : '')
      + '<div class="ma-loan-card-foot"><span>' + h(sub) + '</span>' + (l.pending_payment ? pill('info', 'Payment posting') : '') + '<span class="ma-chev">&rsaquo;</span></div>'
      + '</a>';
  }

  function renderLoan(args, query) {
    var l = loanById(args[0]);
    if (!l) {
      setTitle('Loan', '');
      view.innerHTML = empty('&#128269;', 'Loan not found', 'Pull to refresh or go back to your loans.', '<a class="ma-btn" href="#/loans">My loans</a>');
      return;
    }
    setTitle(l.LID, l.product);
    var tab = query.get('t') || (loanGroup(l) === 'applications' ? 'status' : 'summary');
    var tabs = [['summary', 'Summary'], ['schedule', 'Schedule'], ['ledger', 'Ledger'], ['status', 'Status']];
    if (l.is_beginning_balance) tabs = [['summary', 'Summary']];

    var html = '<section class="ma-loan-hero">'
      + '<div class="ma-loan-hero-top">' + lifecyclePill(l) + '<span>' + h(l.interval || '') + (l.term ? ' · ' + l.term + ' terms' : '') + '</span></div>'
      + '<small>' + (l.disbursed ? 'Outstanding balance' : 'Amount applied') + '</small>'
      + '<div class="ma-loan-hero-amt">' + peso(l.disbursed ? l.outstanding : l.amount) + '</div>'
      + (l.disbursed && !l.is_beginning_balance ? '<div class="ma-progress ma-progress-light"><i style="width:' + Number(l.progress_pct) + '%"></i></div><small>' + Number(l.progress_pct) + '% of principal paid</small>' : '')
      + '</section>';

    if (l.pending_payment) {
      html += '<div class="ma-alert ma-alert-info">Payment ' + h(l.pending_payment.reference_no || l.pending_payment.receipt_no || '') + ' of ' + peso(l.pending_payment.amount) + ' was received and is waiting to be posted by the office.</div>';
    }
    if (l.can_pay) {
      html += '<a class="ma-btn ma-btn-block ma-btn-lg" href="#/pay/' + encodeURIComponent(l.LID) + '">Pay this loan</a>';
    }
    if (l.is_beginning_balance) {
      html += '<div class="ma-alert ma-alert-info">This balance was carried over from the previous system. Please pay it at the cooperative office.</div>';
    }

    html += '<div class="ma-tabs">' + tabs.map(function (t) {
      return '<a href="#/loan/' + encodeURIComponent(l.LID) + '?t=' + t[0] + '" class="' + (t[0] === tab ? 'is-active' : '') + '">' + t[1] + '</a>';
    }).join('') + '</div>';

    if (tab === 'schedule') html += loanSchedule(l);
    else if (tab === 'ledger') html += loanLedger(l);
    else if (tab === 'status') html += loanTimeline(l);
    else html += loanSummary(l);

    view.innerHTML = html;
  }

  function dl(rows) {
    return '<dl class="ma-dl">' + rows.filter(Boolean).map(function (r) {
      return '<div><dt>' + h(r[0]) + '</dt><dd>' + r[1] + '</dd></div>';
    }).join('') + '</dl>';
  }

  function loanSummary(l) {
    var html = '';
    if (l.disbursed) {
      html += '<section class="ma-card"><header class="ma-card-head"><h3>Balance breakdown</h3></header>' + dl([
        ['Principal', peso(l.principal_outstanding)],
        ['Interest', peso(l.interest_outstanding)],
        ['Penalty', peso(l.penalty_outstanding)],
        ['Amount due now', '<strong>' + peso(l.amount_due) + '</strong>'],
        l.has_overdue ? ['Overdue installments', '<span class="ma-text-danger">' + h(l.overdue_count) + '</span>'] : null
      ]) + '</section>';
    }
    html += '<section class="ma-card"><header class="ma-card-head"><h3>Loan details</h3></header>' + dl([
      ['Product', h(l.product)],
      ['Loan amount', peso(l.amount)],
      l.total_interest !== undefined ? ['Total interest', peso(l.total_interest)] : null,
      l.total_loan ? ['Total payable', peso(l.total_loan)] : null,
      l.installment ? ['Installment', peso(l.installment) + ' / ' + h((l.interval || 'Monthly').toLowerCase())] : null,
      l.rate ? ['Interest rate', h(l.rate) + '% p.a.'] : null,
      ['Applied', fmtDate(l.applicationdate)],
      l.release_date ? ['Released', fmtDate(l.release_date)] : null,
      l.paid_total !== undefined ? ['Total paid', peso(l.paid_total)] : null,
      l.purpose ? ['Purpose', h(l.purpose)] : null
    ]) + '</section>';
    return html;
  }

  function loanSchedule(l) {
    if (!l.schedule || !l.schedule.length) {
      return empty('&#128197;', 'No schedule yet', 'The repayment schedule appears once the loan is released.');
    }
    var html = '<section class="ma-card ma-card-flush"><ul class="ma-list">';
    l.schedule.forEach(function (s) {
      var paid = Number(s.status) === 1;
      var late = !paid && daysUntil(s.date) < 0;
      html += '<li class="ma-list-item"><div class="ma-num' + (paid ? ' is-paid' : late ? ' is-late' : '') + '">' + (paid ? '&#10003;' : h(s.no)) + '</div>'
        + '<div class="ma-list-main"><strong>' + fmtDate(s.date) + '</strong><small>Principal ' + peso(s.principal) + ' · Interest ' + peso(s.interest) + '</small></div>'
        + '<div class="ma-list-amt">' + peso(s.amount) + '<small>' + (paid ? 'Paid' : late ? 'Overdue' : 'Bal ' + peso(s.balance)) + '</small></div></li>';
    });
    return html + '</ul></section>';
  }

  function loanLedger(l) {
    if (!l.ledger || !l.ledger.length) {
      return empty('&#128209;', 'No transactions yet', 'Releases and payments for this loan will be listed here.');
    }
    var html = '<section class="ma-card ma-card-flush"><ul class="ma-list">';
    l.ledger.forEach(function (t) {
      var credit = Number(t.credit) > 0;
      var parts = [];
      if (t.principal) parts.push('P ' + peso(t.principal));
      if (t.interest) parts.push('I ' + peso(t.interest));
      if (t.penalty) parts.push('Pen ' + peso(t.penalty));
      html += '<li class="ma-list-item"><div class="ma-ledger-icon ' + (credit ? 'is-in' : 'is-out') + '">' + (credit ? '&darr;' : '&uarr;') + '</div>'
        + '<div class="ma-list-main"><strong>' + h(t.description || t.type) + '</strong><small>' + fmtDate(t.date) + (t.receipt ? ' · ' + h(t.receipt) : '') + (parts.length ? ' · ' + parts.join(' · ') : '') + '</small></div>'
        + '<div class="ma-list-amt ' + (credit ? 'ma-text-ok' : '') + '">' + (credit ? '-' + peso(t.credit) : peso(t.debit)) + '</div></li>';
    });
    return html + '</ul></section>';
  }

  function loanTimeline(l) {
    if (!l.timeline || !l.timeline.length) {
      return empty('&#9203;', 'No status yet', 'Status updates appear here.');
    }
    var icons = { done: '&#10003;', current: '&#8226;', rejected: '&times;', changes: '!', todo: '', skipped: '' };
    return '<section class="ma-card"><ol class="ma-timeline">' + l.timeline.map(function (s) {
      return '<li class="is-' + h(s.state) + '"><span class="ma-tl-dot">' + (icons[s.state] || '') + '</span><div><strong>' + h(s.label) + '</strong>'
        + (s.date ? '<small>' + fmtDate(s.date) + '</small>' : '') + (s.note ? '<p>' + h(s.note) + '</p>' : '') + '</div></li>';
    }).join('') + '</ol></section>';
  }

  // ------------------------------------------------------------------
  // Payments history / applications
  // ------------------------------------------------------------------

  function paymentItem(p) {
    var badge = p.status === 'posted' ? '' : pill(p.status === 'pending' ? 'warn' : 'info', p.status === 'pending' ? 'Waiting for payment' : 'Posting');
    var channel = { online: 'QR Ph', counter: 'Office', offset: 'Reloan offset' }[p.channel] || '';
    var split = p.principal !== null && p.principal !== undefined ? 'P ' + peso(p.principal) + ' · I ' + peso(p.interest) + (p.penalty ? ' · Pen ' + peso(p.penalty) : '') : channel;
    return '<li class="ma-list-item"><div class="ma-ledger-icon is-in">&#8369;</div>'
      + '<div class="ma-list-main"><strong>' + h(p.LID) + ' ' + badge + '</strong><small>' + fmtDate(p.date) + ' · ' + h(p.receipt) + '</small><small>' + h(split) + '</small></div>'
      + '<div class="ma-list-amt ma-text-ok">' + peso(p.amount) + '</div></li>';
  }

  function renderPayments(args, query) {
    var all = state.snap.payments;
    var lid = query.get('loan') || '';
    var list = lid ? all.filter(function (p) { return p.LID === lid; }) : all;
    var posted = list.filter(function (p) { return p.status === 'posted'; });
    var total = posted.reduce(function (a, p) { return a + Number(p.amount); }, 0);
    setTitle('Payments', list.length + ' record' + (list.length === 1 ? '' : 's'));

    var lids = [];
    all.forEach(function (p) { if (lids.indexOf(p.LID) < 0) lids.push(p.LID); });
    var html = '<section class="ma-summary-strip"><div><small>Total posted</small><strong>' + peso(total) + '</strong></div>'
      + '<label class="ma-select"><select id="maPayFilter"><option value="">All loans</option>' + lids.map(function (x) {
        return '<option value="' + h(x) + '"' + (x === lid ? ' selected' : '') + '>' + h(x) + '</option>';
      }).join('') + '</select></label></section>';

    if (!list.length) {
      html += empty('&#129534;', 'No payments yet', 'Your posted payments will appear here.');
    } else {
      var month = '';
      html += '<section class="ma-card ma-card-flush"><ul class="ma-list">';
      list.forEach(function (p) {
        var m = p.date ? new Date(p.date + 'T00:00:00').toLocaleDateString('en-PH', { month: 'long', year: 'numeric' }) : '';
        if (m !== month) {
          month = m;
          html += '<li class="ma-list-sep">' + h(m) + '</li>';
        }
        html += paymentItem(p);
      });
      html += '</ul></section>';
    }
    view.innerHTML = html;
    var sel = d.getElementById('maPayFilter');
    if (sel) sel.addEventListener('change', function () { w.location.hash = '#/payments' + (sel.value ? '?loan=' + encodeURIComponent(sel.value) : ''); });
  }

  function renderApplications() {
    var apps = state.snap.loans.filter(function (l) { return !l.is_beginning_balance && (loanGroup(l) === 'applications' || ['2', '7', '8'].indexOf(String(l.status_code)) >= 0); });
    setTitle('Applications', apps.length + ' total');
    if (!apps.length) {
      view.innerHTML = empty('&#128221;', 'No applications', 'Loans you apply for will show their progress here.', '<a class="ma-btn" href="#/apply">Apply for a loan</a>');
      return;
    }
    view.innerHTML = apps.map(function (l) {
      return '<section class="ma-card"><a class="ma-card-head" href="#/loan/' + encodeURIComponent(l.LID) + '?t=status"><div><h3>' + h(l.product) + '</h3><small>' + h(l.LID) + ' · ' + peso(l.amount) + ' · ' + fmtDate(l.applicationdate) + '</small></div>' + lifecyclePill(l) + '</a>' + loanTimeline(l).replace('<section class="ma-card">', '').replace(/<\/section>$/, '') + '</section>';
    }).join('');
  }

  // ------------------------------------------------------------------
  // Pay (PayMongo QR Ph)
  // ------------------------------------------------------------------

  function renderPay(args) {
    setTitle('Pay a loan', 'QR Ph · GCash, Maya, banks');
    if (!args[0]) {
      var payable = state.snap ? state.snap.loans.filter(function (l) { return l.is_open && !l.is_beginning_balance; }) : [];
      if (!payable.length) {
        view.innerHTML = empty('&#9989;', 'Nothing to pay', 'You have no open loans right now.');
        return;
      }
      if (payable.length === 1) {
        w.location.replace('#/pay/' + encodeURIComponent(payable[0].LID));
        return;
      }
      view.innerHTML = '<p class="ma-muted">Choose the loan you want to pay.</p>' + payable.map(loanCard).join('').replace(/#\/loan\//g, '#/pay/');
      return;
    }
    var LID = args[0];
    if (!isOnline()) {
      view.innerHTML = empty('&#128246;', 'You are offline', 'Paying needs an internet connection. Your balance is still shown on the Loans tab.');
      return;
    }
    var active = sessionGet('tap_active_pay');
    if (active && active.LID === LID && active.id) {
      showPaymentStatus(active.id);
      return;
    }
    view.innerHTML = '<div class="ma-loading">Loading payment options…</div>';
    api('pay/options/' + encodeURIComponent(LID)).then(function (j) {
      if (!j.ok) {
        view.innerHTML = empty('&#9888;', 'Not available', j.message || 'Please try again.');
        return;
      }
      drawPayOptions(LID, j);
    }).catch(function () {
      view.innerHTML = empty('&#128246;', 'No connection', 'Please check your internet and try again.');
    });
  }

  function feeFor(amount, fee) {
    if (!fee || !fee.enabled) return 0;
    return Math.round((amount * fee.percent / 100 + fee.fixed) * 100) / 100;
  }

  function drawPayOptions(LID, j) {
    var l = loanById(LID);
    var html = '<section class="ma-card"><div class="ma-card-head"><div><h3>' + h(l ? l.product : LID) + '</h3><small>' + h(LID) + '</small></div>' + (j.has_overdue ? pill('danger', 'Overdue') : '') + '</div>';
    if (!j.available) {
      html += '<div class="ma-alert ma-alert-info">' + h(j.message) + '</div></section>';
      view.innerHTML = html;
      return;
    }
    html += '<form id="maPayForm" class="ma-form"><div class="ma-options">';
    j.options.forEach(function (o, i) {
      html += '<label class="ma-option"><input type="radio" name="option" value="' + h(o.key) + '" data-amount="' + Number(o.amount) + '"' + (i === 0 ? ' checked' : '') + '>'
        + '<span><strong>' + h(o.label) + '</strong><b>' + peso(o.amount) + '</b></span></label>';
    });
    html += '<label class="ma-option"><input type="radio" name="option" value="custom"><span><strong>Other amount</strong><small>Minimum ' + peso(j.minimum) + '</small></span></label>'
      + '</div>'
      + '<label class="ma-field" id="maCustomWrap" hidden><span>Amount to pay</span><div class="ma-input-group"><span class="ma-input-prefix">\u20B1</span><input type="text" inputmode="decimal" name="amount" id="maCustomAmount" placeholder="0.00"></div></label>'
      + '<div class="ma-pay-total"><div><small>Applied to your loan</small><strong id="maPayApplied">—</strong></div>'
      + (j.fee.enabled ? '<div><small>Convenience fee</small><strong id="maPayFee">—</strong></div>' : '')
      + '<div class="is-total"><small>You will pay</small><strong id="maPayCharged">—</strong></div></div>'
      + '<button type="submit" class="ma-btn ma-btn-block ma-btn-lg">Generate QR code</button>'
      + '<p class="ma-hint ma-center">Scan with GCash, Maya or your bank app. Your payment is posted by the office, usually within one business day.' + (j.test_mode ? ' <b>(Test mode)</b>' : '') + '</p>'
      + '</form></section>';
    view.innerHTML = html;

    var form = d.getElementById('maPayForm');
    var wrap = d.getElementById('maCustomWrap');
    var custom = d.getElementById('maCustomAmount');
    function current() {
      var sel = form.querySelector('input[name=option]:checked');
      if (!sel) return 0;
      if (sel.value === 'custom') return parseFloat(String(custom.value).replace(/,/g, '')) || 0;
      return Number(sel.getAttribute('data-amount'));
    }
    function refresh() {
      var sel = form.querySelector('input[name=option]:checked');
      wrap.hidden = !(sel && sel.value === 'custom');
      var amt = current();
      var fee = feeFor(amt, j.fee);
      d.getElementById('maPayApplied').textContent = amt ? peso(amt) : '—';
      if (j.fee.enabled) d.getElementById('maPayFee').textContent = amt ? peso(fee) : '—';
      d.getElementById('maPayCharged').textContent = amt ? peso(amt + fee) : '—';
    }
    form.addEventListener('change', refresh);
    custom.addEventListener('input', refresh);
    refresh();

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var sel = form.querySelector('input[name=option]:checked');
      var amt = current();
      if (sel.value === 'custom' && (amt < j.minimum || amt > j.payoff)) {
        toast('Enter an amount from ' + peso(j.minimum) + ' to ' + peso(j.payoff) + '.', 'error');
        return;
      }
      var btn = form.querySelector('[type=submit]');
      btn.disabled = true;
      btn.classList.add('is-busy');
      api('pay/create', { body: { LID: LID, option: sel.value, amount: sel.value === 'custom' ? amt.toFixed(2) : '' } }).then(function (r) {
        if (!r.ok) {
          toast(r.message || 'Could not create the QR code.', 'error');
          btn.disabled = false;
          btn.classList.remove('is-busy');
          return;
        }
        sessionSet('tap_active_pay', { id: r.payment.id, LID: LID });
        drawQr(r.payment);
      }).catch(function () {
        toast('No connection. Please try again.', 'error');
        btn.disabled = false;
        btn.classList.remove('is-busy');
      });
    });
  }

  function showPaymentStatus(id) {
    view.innerHTML = '<div class="ma-loading">Checking your payment…</div>';
    api('pay/status/' + id).then(function (j) {
      if (!j.ok) {
        sessionSet('tap_active_pay', null);
        route();
        return;
      }
      handlePayment(j.payment);
    }).catch(function () {
      view.innerHTML = empty('&#128246;', 'No connection', 'We will check your payment when you are back online.');
    });
  }

  function handlePayment(p) {
    if (p.status === 'pending') {
      drawQr(p);
    } else if (p.status === 'paid') {
      sessionSet('tap_active_pay', null);
      drawPaid(p);
    } else {
      sessionSet('tap_active_pay', null);
      view.innerHTML = empty('&#8987;', p.status === 'expired' ? 'QR code expired' : 'Payment not completed',
        p.status === 'expired' ? 'The QR code is only valid for a short time. Make a new one to pay.' : 'No money was taken. You can try again.',
        '<a class="ma-btn" href="#/pay/' + encodeURIComponent(p.LID) + '?r=' + Date.now() + '">Try again</a>');
    }
  }

  function drawQr(p) {
    stopTimers();
    setTitle('Scan to pay', p.LID);
    view.innerHTML = '<section class="ma-card ma-qr-card">'
      + '<div class="ma-qr-amount"><small>Amount</small><strong>' + peso(p.charged) + '</strong>' + (p.fee ? '<small>includes ' + peso(p.fee) + ' fee</small>' : '') + '</div>'
      + '<div class="ma-qr-frame">' + (p.qr_image_url ? '<img src="' + h(p.qr_image_url) + '" alt="QR Ph code">' : '<div class="ma-loading">QR code unavailable</div>') + '</div>'
      + '<div class="ma-qr-timer"><span class="ma-pulse"></span>Waiting for payment · <b id="maQrLeft">--:--</b></div>'
      + '<ol class="ma-qr-steps"><li>Open GCash, Maya or your bank app.</li><li>Choose <b>Scan QR</b> / <b>Pay QR</b>, or save this QR and upload it.</li><li>Confirm ' + peso(p.charged) + '. This screen updates by itself.</li></ol>'
      + '<div class="ma-qr-actions">' + (p.qr_image_url ? '<a class="ma-btn ma-btn-outline" download="TAP-' + h(p.reference_no) + '.png" href="' + h(p.qr_image_url) + '">Save QR</a>' : '')
      + (p.test_url ? '<a class="ma-btn ma-btn-outline" target="_blank" rel="noopener" href="' + h(p.test_url) + '">Simulate payment (test)</a>' : '')
      + '<button type="button" class="ma-btn ma-btn-ghost" id="maQrCancel">Cancel</button></div>'
      + '<p class="ma-hint ma-center">Reference ' + h(p.reference_no) + '</p></section>';

    var left = Number(p.seconds_left || 0);
    var leftEl = d.getElementById('maQrLeft');
    function tick() {
      if (left <= 0) {
        leftEl.textContent = 'expired';
        return;
      }
      leftEl.textContent = Math.floor(left / 60) + ':' + ('0' + (left % 60)).slice(-2);
      left--;
    }
    tick();
    state.countdownTimer = setInterval(tick, 1000);

    function poll() {
      state.pollTimer = setTimeout(function () {
        if (d.hidden) {
          poll();
          return;
        }
        api('pay/status/' + p.id).then(function (j) {
          if (j.ok && j.payment.status !== 'pending') {
            stopTimers();
            handlePayment(j.payment);
          } else {
            poll();
          }
        }).catch(poll);
      }, 5000);
    }
    poll();

    d.getElementById('maQrCancel').addEventListener('click', function () {
      if (!confirm('Cancel this QR code? Do not cancel if you already paid.')) return;
      api('pay/cancel/' + p.id, { body: { id: p.id } }).then(function (j) {
        stopTimers();
        if (j.ok && j.payment.status === 'paid') {
          handlePayment(j.payment);
          return;
        }
        sessionSet('tap_active_pay', null);
        w.location.hash = '#/loan/' + encodeURIComponent(p.LID);
      });
    });
  }

  function drawPaid(p) {
    setTitle('Payment received', p.LID);
    view.innerHTML = '<section class="ma-card ma-success">'
      + '<div class="ma-success-icon">&#10003;</div><h2>Thank you!</h2>'
      + '<p>We received <b>' + peso(p.charged) + '</b> for loan <b>' + h(p.LID) + '</b>.</p>'
      + dl([['Reference', h(p.reference_no)], ['Applied to loan', peso(p.amount)], p.fee ? ['Fee', peso(p.fee)] : null, p.receipt_no ? ['Receipt no.', h(p.receipt_no)] : null, ['Paid', h(p.paid_at || '')]])
      + '<div class="ma-alert ma-alert-info">Your balance will update once the office posts this payment, usually within one business day.</div>'
      + '<a class="ma-btn ma-btn-block" href="#/loan/' + encodeURIComponent(p.LID) + '">Back to my loan</a></section>';
    sync(true);
  }

  function sessionGet(k) {
    try { return JSON.parse(w.sessionStorage.getItem(k) || 'null'); } catch (e) { return null; }
  }

  function sessionSet(k, v) {
    try {
      if (v === null) w.sessionStorage.removeItem(k);
      else w.sessionStorage.setItem(k, JSON.stringify(v));
    } catch (e) { /* ignore */ }
  }

  // ------------------------------------------------------------------
  // Apply / reloan wizard
  // ------------------------------------------------------------------

  function renderApply(args, query) {
    setTitle('Apply for a loan', 'New loan or reloan');
    if (!isOnline()) {
      view.innerHTML = empty('&#128246;', 'You are offline', 'Applying needs an internet connection.');
      return;
    }
    if (!state.apply || query.get('new')) {
      state.apply = { step: 1, product: null, amount: '', term: '', quote: null, source: '', purpose: '', income: '', reloan: [], comakers: [], meta: null };
    }
    var a = state.apply;
    if (!a.meta) {
      view.innerHTML = '<div class="ma-loading">Loading loan products…</div>';
      api('api/products').then(function (j) {
        if (!j.ok) {
          view.innerHTML = empty('&#9888;', 'Not available', j.message || 'Please try again.');
          return;
        }
        a.meta = j;
        if (query.get('reloan')) a.reloan = [query.get('reloan')];
        drawApply();
      }).catch(function () {
        view.innerHTML = empty('&#128246;', 'No connection', 'Please check your internet and try again.');
      });
      return;
    }
    drawApply();
  }

  function drawApply() {
    var a = state.apply;
    var m = a.meta;
    if (m.pending_application) {
      view.innerHTML = empty('&#9203;', 'Application in progress', 'Your application ' + m.pending_application + ' is still being reviewed. You can apply again once it has been decided.', '<a class="ma-btn" href="#/loan/' + encodeURIComponent(m.pending_application) + '?t=status">See its status</a>');
      return;
    }
    if (!m.products.length) {
      view.innerHTML = empty('&#128683;', 'No products available', 'Online applications are not open right now. Please visit the cooperative office.');
      return;
    }
    var steps = ['Loan', 'Details', 'Review'];
    var html = '<div class="ma-wizard-steps">' + steps.map(function (s, i) {
      return '<span class="' + (i + 1 === a.step ? 'is-active' : i + 1 < a.step ? 'is-done' : '') + '"><b>' + (i + 1) + '</b>' + s + '</span>';
    }).join('') + '</div>';
    html += '<form id="maApplyForm" class="ma-form ma-card" novalidate>';
    if (a.step === 1) html += applyStep1(a);
    else if (a.step === 2) html += applyStep2(a);
    else html += applyStep3(a);
    html += '</form>';
    view.innerHTML = html;
    bindApply();
  }

  function productById(id) {
    var list = state.apply.meta.products;
    for (var i = 0; i < list.length; i++) if (String(list[i].id) === String(id)) return list[i];
    return null;
  }

  function applyStep1(a) {
    var html = '<h3 class="ma-form-title">Choose a loan</h3><div class="ma-product-grid">';
    a.meta.products.forEach(function (p) {
      html += '<label class="ma-product"><input type="radio" name="product" value="' + p.id + '"' + (String(a.product) === String(p.id) ? ' checked' : '') + '>'
        + '<span><strong>' + h(p.name) + '</strong><small>' + h(p.description) + '</small><em>' + h(p.rate) + '% p.a. · up to ' + h(p.max_term) + ' ' + h(String(p.interval_unit).toLowerCase()) + (p.max_amount ? ' · max ' + peso(p.max_amount) : '') + '</em></span></label>';
    });
    html += '</div>';
    var p = productById(a.product);
    html += '<div class="ma-grid-2">'
      + '<label class="ma-field"><span>Amount</span><div class="ma-input-group"><span class="ma-input-prefix">\u20B1</span><input type="text" inputmode="decimal" name="amount" value="' + h(a.amount) + '" placeholder="0.00"></div></label>'
      + '<label class="ma-field"><span>Term' + (p ? ' (' + h(String(p.interval_unit).toLowerCase()) + ', max ' + h(p.max_term) + ')' : '') + '</span><input type="number" inputmode="numeric" name="term" min="1" max="' + (p ? p.max_term : 60) + '" value="' + h(a.term) + '"></label>'
      + '</div>'
      + '<div class="ma-quote" id="maQuote">' + quoteHtml(a.quote) + '</div>'
      + '<button type="submit" class="ma-btn ma-btn-block ma-btn-lg">Continue</button>';
    return html;
  }

  function quoteHtml(q) {
    if (!q) return '<p class="ma-muted ma-center">Choose a product, amount and term to see your installment.</p>';
    if (!q.ok) return '<p class="ma-text-danger ma-center">' + h(q.message) + '</p>';
    return '<div><small>Installment</small><strong>' + peso(q.installment) + '</strong></div>'
      + '<div><small>Total interest</small><strong>' + peso(q.total_interest) + '</strong></div>'
      + '<div><small>Total payable</small><strong>' + peso(q.total) + '</strong></div>';
  }

  function applyStep2(a) {
    var html = '<h3 class="ma-form-title">About this loan</h3>'
      + '<label class="ma-field"><span>Purpose</span><textarea name="purpose" rows="2" maxlength="250" placeholder="e.g. School tuition, home repair">' + h(a.purpose) + '</textarea></label>'
      + '<div class="ma-grid-2">'
      + '<label class="ma-field"><span>How will you pay?</span><select name="source"><option value="">Choose…</option>' + a.meta.sources.map(function (s) {
        return '<option' + (s === a.source ? ' selected' : '') + '>' + h(s) + '</option>';
      }).join('') + '</select></label>'
      + '<label class="ma-field"><span>Monthly income</span><div class="ma-input-group"><span class="ma-input-prefix">\u20B1</span><input type="text" inputmode="decimal" name="income" value="' + h(a.income) + '" placeholder="0.00"></div></label>'
      + '</div>';
    if (a.meta.reloan_candidates.length) {
      html += '<fieldset class="ma-fieldset"><legend>Reloan &mdash; pay off an existing loan from this one (optional)</legend>';
      a.meta.reloan_candidates.forEach(function (r) {
        html += '<label class="ma-check ma-check-card"><input type="checkbox" name="reloan" value="' + h(r.LID) + '"' + (a.reloan.indexOf(r.LID) >= 0 ? ' checked' : '') + '>'
          + '<span><strong>' + h(r.LID) + '</strong> ' + h(r.product) + '<small>Balance ' + peso(r.outstanding) + '</small></span></label>';
      });
      html += '<p class="ma-hint">The office confirms the payoff amount when the new loan is released.</p></fieldset>';
    }
    html += '<fieldset class="ma-fieldset"><legend>Co-makers (optional, up to 3)</legend><div id="maComakers">'
      + a.comakers.map(function (c, i) {
        return '<div class="ma-comaker"><div><strong>' + h(c.name) + '</strong><small>' + h(c.member_id) + ' · ' + h(c.relationship) + '</small></div><button type="button" class="ma-icon-btn" data-remove-comaker="' + i + '" aria-label="Remove">&times;</button></div>';
      }).join('')
      + '</div>' + (a.comakers.length < 3 ? '<div class="ma-grid-2"><label class="ma-field"><span>Co-maker Member ID</span><input type="text" id="maCmId" autocapitalize="characters"></label>'
      + '<label class="ma-field"><span>Relationship</span><input type="text" id="maCmRel" placeholder="e.g. Co-teacher"></label></div><button type="button" class="ma-btn ma-btn-outline ma-btn-sm" id="maCmAdd">Add co-maker</button>' : '')
      + '<p class="ma-hint">Each co-maker gets a request in their app to accept.</p></fieldset>'
      + '<div class="ma-row-between"><button type="button" class="ma-btn ma-btn-ghost" data-step="1">Back</button><button type="submit" class="ma-btn ma-btn-lg">Review</button></div>';
    return html;
  }

  function applyStep3(a) {
    var p = productById(a.product);
    var q = a.quote || {};
    return '<h3 class="ma-form-title">Review your application</h3>'
      + dl([
        ['Product', h(p ? p.name : '')],
        ['Amount', '<strong>' + peso(a.amount) + '</strong>'],
        ['Term', h(a.term) + ' ' + h(p ? String(p.interval_unit).toLowerCase() : '')],
        ['Installment', peso(q.installment)],
        ['Total interest', peso(q.total_interest)],
        ['Total payable', peso(q.total)],
        ['Purpose', h(a.purpose)],
        ['Pay source', h(a.source)],
        ['Monthly income', peso(a.income)],
        a.reloan.length ? ['Reloan of', h(a.reloan.join(', '))] : null,
        a.comakers.length ? ['Co-makers', h(a.comakers.map(function (c) { return c.name; }).join(', '))] : null
      ])
      + '<div class="ma-alert ma-alert-info">Figures are estimates. The cooperative evaluates every application and sets the processing fee and final terms before release.</div>'
      + '<label class="ma-check"><input type="checkbox" name="agree" required> <span>I confirm the details are true and I agree to the cooperative\'s loan policies.</span></label>'
      + '<div class="ma-row-between"><button type="button" class="ma-btn ma-btn-ghost" data-step="2">Back</button><button type="submit" class="ma-btn ma-btn-lg">Submit application</button></div>';
  }

  var quoteTimer = null;

  function bindApply() {
    var a = state.apply;
    var form = d.getElementById('maApplyForm');
    form.querySelectorAll('[data-step]').forEach(function (b) {
      b.addEventListener('click', function () {
        collect(form);
        a.step = Number(b.getAttribute('data-step'));
        drawApply();
      });
    });

    if (a.step === 1) {
      form.addEventListener('change', function (e) {
        if (e.target.name === 'product') {
          collect(form);
          drawApply();
          requestQuote();
        }
      });
      form.addEventListener('input', function (e) {
        if (e.target.name === 'amount' || e.target.name === 'term') {
          collect(form);
          clearTimeout(quoteTimer);
          quoteTimer = setTimeout(requestQuote, 450);
        }
      });
    }

    if (a.step === 2) {
      var add = d.getElementById('maCmAdd');
      if (add) {
        add.addEventListener('click', function () {
          var id = d.getElementById('maCmId').value.trim();
          var rel = d.getElementById('maCmRel').value.trim();
          if (!id) return;
          if (a.comakers.some(function (c) { return c.member_id.toUpperCase() === id.toUpperCase(); })) {
            toast('Already added.', 'warn');
            return;
          }
          add.disabled = true;
          api('api/comaker_lookup?member_id=' + encodeURIComponent(id)).then(function (j) {
            add.disabled = false;
            if (!j.ok) {
              toast(j.message, 'error');
              return;
            }
            collect(form);
            a.comakers.push({ member_id: j.member_id, name: j.name, relationship: rel || 'Co-member' });
            drawApply();
          }).catch(function () { add.disabled = false; });
        });
      }
      form.querySelectorAll('[data-remove-comaker]').forEach(function (b) {
        b.addEventListener('click', function () {
          collect(form);
          a.comakers.splice(Number(b.getAttribute('data-remove-comaker')), 1);
          drawApply();
        });
      });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      collect(form);
      if (a.step === 1) {
        if (!a.product || !(parseFloat(a.amount) > 0) || !(parseInt(a.term, 10) > 0)) {
          toast('Choose a product, amount and term.', 'error');
          return;
        }
        if (!a.quote || !a.quote.ok) {
          requestQuote().then(function () {
            if (a.quote && a.quote.ok) {
              a.step = 2;
              drawApply();
            }
          });
          return;
        }
        a.step = 2;
        drawApply();
      } else if (a.step === 2) {
        if (a.purpose.length < 3 || !a.source || !(parseFloat(a.income) > 0)) {
          toast('Fill in the purpose, pay source and monthly income.', 'error');
          return;
        }
        a.step = 3;
        drawApply();
      } else {
        if (!form.querySelector('[name=agree]').checked) {
          toast('Please tick the confirmation box.', 'error');
          return;
        }
        submitApplication(form);
      }
    });
  }

  function collect(form) {
    var a = state.apply;
    var f = function (n) { var el = form.querySelector('[name=' + n + ']'); return el ? el : null; };
    var sel = form.querySelector('[name=product]:checked');
    if (sel) a.product = sel.value;
    if (f('amount')) a.amount = String(f('amount').value).replace(/,/g, '');
    if (f('term')) a.term = f('term').value;
    if (f('purpose')) a.purpose = f('purpose').value.trim();
    if (f('source')) a.source = f('source').value;
    if (f('income')) a.income = String(f('income').value).replace(/,/g, '');
    if (form.querySelector('[name=reloan]')) {
      a.reloan = Array.prototype.map.call(form.querySelectorAll('[name=reloan]:checked'), function (c) { return c.value; });
    }
  }

  function requestQuote() {
    var a = state.apply;
    var box = d.getElementById('maQuote');
    if (!a.product || !(parseFloat(a.amount) > 0) || !(parseInt(a.term, 10) > 0)) {
      a.quote = null;
      if (box) box.innerHTML = quoteHtml(null);
      return Promise.resolve();
    }
    if (box) box.classList.add('is-loading');
    return api('api/quote', { body: { product: a.product, amount: a.amount, term: a.term } }).then(function (j) {
      a.quote = j;
      var b = d.getElementById('maQuote');
      if (b) {
        b.classList.remove('is-loading');
        b.innerHTML = quoteHtml(j);
      }
    }).catch(function () {
      if (box) box.classList.remove('is-loading');
    });
  }

  function submitApplication(form) {
    var a = state.apply;
    var btn = form.querySelector('[type=submit]');
    btn.disabled = true;
    btn.classList.add('is-busy');
    var body = { product: a.product, amount: a.amount, term: a.term, source: a.source, purpose: a.purpose, income: a.income, reloan_of: a.reloan, comakers: a.comakers.map(function (c) { return { member_id: c.member_id, relationship: c.relationship }; }) };
    api('api/apply', { body: body }).then(function (j) {
      if (!j.ok) {
        toast(j.message || 'Could not submit.', 'error');
        btn.disabled = false;
        btn.classList.remove('is-busy');
        return;
      }
      state.apply = null;
      view.innerHTML = '<section class="ma-card ma-success"><div class="ma-success-icon">&#10003;</div><h2>Application sent</h2><p>' + h(j.message) + '</p>'
        + '<a class="ma-btn ma-btn-block" href="#/loan/' + encodeURIComponent(j.LID) + '?t=status">Track my application</a></section>';
      sync(true);
    }).catch(function () {
      toast('No connection. Please try again.', 'error');
      btn.disabled = false;
      btn.classList.remove('is-busy');
    });
  }

  // ------------------------------------------------------------------
  // Co-maker requests
  // ------------------------------------------------------------------

  function renderComakers() {
    var list = state.snap.comaker_requests;
    setTitle('Co-maker requests', list.filter(function (r) { return r.status === 'pending'; }).length + ' waiting');
    if (!list.length) {
      view.innerHTML = empty('&#129309;', 'No requests', 'When a member asks you to be their co-maker, it shows here.');
      return;
    }
    view.innerHTML = list.map(function (r) {
      return '<section class="ma-card"><div class="ma-card-head"><div><h3>' + h(r.borrower) + '</h3><small>' + h(r.LID) + ' · ' + h(r.product) + ' · ' + fmtDate(r.date) + '</small></div>'
        + pill(r.status === 'pending' ? 'warn' : r.status === 'accepted' ? 'ok' : 'muted', r.status) + '</div>'
        + '<p>Loan amount <b>' + peso(r.amount) + '</b></p>'
        + (r.status === 'pending' ? '<div class="ma-row-between"><button class="ma-btn ma-btn-ghost" data-cm="decline" data-id="' + r.id + '">Decline</button><button class="ma-btn" data-cm="accept" data-id="' + r.id + '">Accept</button></div>' : '')
        + '</section>';
    }).join('');

    view.querySelectorAll('[data-cm]').forEach(function (b) {
      b.addEventListener('click', function () {
        var id = b.getAttribute('data-id');
        if (b.getAttribute('data-cm') === 'decline') {
          if (!confirm('Decline this co-maker request?')) return;
          respondComaker({ id: id, action: 'decline' });
          return;
        }
        openSheet('Accept as co-maker', '<form id="maCmForm" class="ma-form">'
          + '<label class="ma-field"><span>Your relationship to the borrower</span><input type="text" name="relationship" required placeholder="e.g. Co-teacher, sibling"></label>'
          + '<label class="ma-field"><span>Declaration (optional)</span><textarea name="declaration" rows="2" placeholder="Assets or notes for the office"></textarea></label>'
          + '<p class="ma-hint">By accepting, you agree to be jointly responsible for this loan under the cooperative\'s rules.</p>'
          + '<button class="ma-btn ma-btn-block ma-btn-lg" type="submit">Accept</button></form>');
        d.getElementById('maCmForm').addEventListener('submit', function (e) {
          e.preventDefault();
          var f = e.target;
          respondComaker({ id: id, action: 'accept', relationship: f.relationship.value, declaration: f.declaration.value });
        });
      });
    });
  }

  function respondComaker(body) {
    api('api/comaker_respond', { body: body }).then(function (j) {
      toast(j.message || (j.ok ? 'Done' : 'Failed'), j.ok ? 'ok' : 'error');
      if (j.ok) {
        closeSheet();
        sync(true);
      }
    }).catch(function () { toast('No connection.', 'error'); });
  }

  // ------------------------------------------------------------------
  // Menu / profile
  // ------------------------------------------------------------------

  function renderMenu() {
    setTitle('Menu', '');
    var reqs = state.snap.comaker_requests.filter(function (r) { return r.status === 'pending'; }).length;
    var item = function (href, label, sub, badge) {
      return '<a class="ma-menu-item" href="' + href + '"><div><strong>' + h(label) + '</strong><small>' + h(sub) + '</small></div>' + (badge ? '<span class="ma-badge">' + badge + '</span>' : '') + '<span class="ma-chev">&rsaquo;</span></a>';
    };
    view.innerHTML = '<section class="ma-profile-card"><div class="ma-avatar ma-avatar-lg">' + d.getElementById('maAvatar').innerHTML + '</div><div><strong>' + h(state.snap.member.full_name) + '</strong><small>Member ID ' + h(state.snap.member.member_id) + '</small></div></section>'
      + '<section class="ma-card ma-card-flush">'
      + item('#/payments', 'Payment history', 'Every posted and pending payment')
      + item('#/applications', 'Applications', 'Status of your loan applications')
      + item('#/comakers', 'Co-maker requests', 'Loans where you are asked to co-sign', reqs)
      + item('#/profile', 'Profile & security', 'Password, app PIN, install app')
      + '</section>'
      + '<section class="ma-card ma-card-flush">'
      + '<button type="button" class="ma-menu-item" id="maInstallBtn" hidden><div><strong>Install the app</strong><small>Open it from your home screen</small></div><span class="ma-chev">&rsaquo;</span></button>'
      + '<button type="button" class="ma-menu-item" id="maRefreshBtn"><div><strong>Refresh data</strong><small>Last updated ' + h(ago(state.savedAt)) + '</small></div><span class="ma-chev">&#8635;</span></button>'
      + '<a class="ma-menu-item ma-text-danger" href="' + ROOT + 'logout" data-logout><div><strong>Sign out</strong><small>Removes your data from this phone</small></div></a>'
      + '</section><p class="ma-hint ma-center">TAP Loans v' + h(MA.version) + '</p>';
    d.getElementById('maRefreshBtn').addEventListener('click', function () { sync(false); });
    bindInstallButton(d.getElementById('maInstallBtn'));
  }

  function renderProfile() {
    var m = state.snap.member;
    var prefs = Store.prefs();
    var hasPin = prefs.pinHash && prefs.pinFor === MA.memberKey;
    setTitle('Profile & security', '');
    view.innerHTML = '<section class="ma-profile-card"><div class="ma-avatar ma-avatar-lg">' + d.getElementById('maAvatar').innerHTML + '</div><div><strong>' + h(m.full_name) + '</strong><small>Member since ' + fmtDate(m.joined) + '</small></div></section>'
      + '<section class="ma-card"><header class="ma-card-head"><h3>My details</h3></header>' + dl([
        ['Member ID', h(m.member_id)], ['Mobile', h(m.phone || '—')], ['Email', h(m.email || '—')]
      ]) + '<p class="ma-hint">To change your details, visit the cooperative office.</p></section>'
      + '<section class="ma-card"><header class="ma-card-head"><h3>App PIN</h3>' + (hasPin ? pill('ok', 'On') : pill('muted', 'Off')) + '</header>'
      + '<p class="ma-muted">Ask for a 4-digit PIN each time the app opens on this phone.</p>'
      + (hasPin ? '<button class="ma-btn ma-btn-outline" id="maPinOff">Turn off PIN</button>' : '<button class="ma-btn ma-btn-outline" id="maPinOn">Set a PIN</button>') + '</section>'
      + '<section class="ma-card"><header class="ma-card-head"><h3>Change password</h3></header><form id="maPwForm" class="ma-form">'
      + '<input type="text" name="username" value="' + h(m.member_id) + '" autocomplete="username" hidden>'
      + '<label class="ma-field"><span>Current password</span><input type="password" name="current" autocomplete="current-password" required></label>'
      + '<label class="ma-field"><span>New password</span><input type="password" name="password" autocomplete="new-password" minlength="8" required><small class="ma-hint">At least 8 characters, with letters and numbers.</small></label>'
      + '<label class="ma-field"><span>Confirm new password</span><input type="password" name="password_confirm" autocomplete="new-password" required></label>'
      + '<button class="ma-btn" type="submit">Change password</button></form></section>';

    var on = d.getElementById('maPinOn');
    if (on) on.addEventListener('click', setupPin);
    var off = d.getElementById('maPinOff');
    if (off) off.addEventListener('click', function () {
      Store.setPrefs({ pinHash: null, pinFor: null, pinSalt: null });
      toast('App PIN turned off', 'ok');
      route();
    });
    d.getElementById('maPwForm').addEventListener('submit', function (e) {
      e.preventDefault();
      var f = e.target;
      if (!isOnline()) {
        toast('You are offline.', 'error');
        return;
      }
      var btn = f.querySelector('[type=submit]');
      btn.disabled = true;
      api('api/change_password', { body: { current: f.current.value, password: f.password.value, password_confirm: f.password_confirm.value } }).then(function (j) {
        btn.disabled = false;
        toast(j.message, j.ok ? 'ok' : 'error');
        if (j.ok) f.reset();
      }).catch(function () { btn.disabled = false; });
    });
  }

  // ------------------------------------------------------------------
  // Sheet
  // ------------------------------------------------------------------

  function openSheet(title, html) {
    d.getElementById('maSheetTitle').textContent = title;
    d.getElementById('maSheetBody').innerHTML = html;
    d.getElementById('maSheet').hidden = false;
    d.getElementById('maSheetBackdrop').hidden = false;
    requestAnimationFrame(function () { d.body.classList.add('ma-sheet-open'); });
  }

  function closeSheet() {
    d.body.classList.remove('ma-sheet-open');
    d.getElementById('maSheet').hidden = true;
    d.getElementById('maSheetBackdrop').hidden = true;
  }

  // ------------------------------------------------------------------
  // App PIN (optional, stored as a salted hash on this phone only)
  // ------------------------------------------------------------------

  function setupPin() {
    askPin('Choose a 4-digit PIN', function (first) {
      askPin('Enter the PIN again', function (second) {
        if (first !== second) {
          toast('The PINs did not match.', 'error');
          hideLock();
          return;
        }
        var salt = Math.random().toString(36).slice(2);
        Store.hash(salt + ':' + first).then(function (hash) {
          hideLock();
          if (!hash) {
            toast('This browser cannot store a PIN.', 'error');
            return;
          }
          Store.setPrefs({ pinHash: hash, pinSalt: salt, pinFor: MA.memberKey });
          sessionSet('tap_unlocked', 1);
          toast('App PIN is on', 'ok');
          route();
        });
      }, true);
    }, true);
  }

  var lockHandler = null;
  var pinBuffer = '';

  function askPin(title, onComplete, cancellable) {
    var lock = d.getElementById('maLock');
    lock.querySelector('h2').textContent = title;
    d.getElementById('maLockError').textContent = '';
    lock.querySelector('[data-key="forgot"]').textContent = cancellable ? 'Cancel' : 'Forgot';
    lock.setAttribute('data-cancellable', cancellable ? '1' : '');
    pinBuffer = '';
    drawDots();
    lockHandler = onComplete;
    lock.hidden = false;
  }

  function hideLock() {
    d.getElementById('maLock').hidden = true;
    lockHandler = null;
  }

  function drawDots() {
    d.querySelectorAll('#maPinDots i').forEach(function (dot, i) {
      dot.classList.toggle('is-on', i < pinBuffer.length);
    });
  }

  d.getElementById('maKeypad').addEventListener('click', function (e) {
    var b = e.target.closest('[data-key]');
    if (!b) return;
    var key = b.getAttribute('data-key');
    if (key === 'del') {
      pinBuffer = pinBuffer.slice(0, -1);
    } else if (key === 'forgot') {
      if (d.getElementById('maLock').getAttribute('data-cancellable')) {
        hideLock();
        return;
      }
      if (confirm('Forgot your PIN? You will be signed out and need your password to sign in again.')) {
        logout();
      }
      return;
    } else if (pinBuffer.length < 4) {
      pinBuffer += key;
    }
    drawDots();
    if (pinBuffer.length === 4 && lockHandler) {
      var done = lockHandler;
      var value = pinBuffer;
      pinBuffer = '';
      setTimeout(function () { drawDots(); done(value); }, 120);
    }
  });

  function requireUnlock() {
    var prefs = Store.prefs();
    if (!prefs.pinHash || prefs.pinFor !== MA.memberKey || sessionGet('tap_unlocked')) {
      return Promise.resolve();
    }
    var tries = 0;
    return new Promise(function (resolve) {
      function attempt(pin) {
        Store.hash(prefs.pinSalt + ':' + pin).then(function (hash) {
          if (hash === prefs.pinHash) {
            sessionSet('tap_unlocked', 1);
            hideLock();
            resolve();
            return;
          }
          tries++;
          if (tries >= 5) {
            logout();
            return;
          }
          d.getElementById('maLockError').textContent = 'Wrong PIN. ' + (5 - tries) + ' tries left.';
          lockHandler = attempt;
        });
      }
      askPin('Enter your app PIN', attempt, false);
    });
  }

  // ------------------------------------------------------------------
  // Install / update / sign-out
  // ------------------------------------------------------------------

  var installEvent = null;
  w.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    installEvent = e;
    bindInstallButton(d.getElementById('maInstallBtn'));
  });

  function isIos() {
    return /iphone|ipad|ipod/i.test(navigator.userAgent) && !w.MSStream;
  }

  function isStandalone() {
    return w.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
  }

  function bindInstallButton(btn) {
    if (!btn || isStandalone()) return;
    if (!installEvent && !isIos()) return;
    btn.hidden = false;
    btn.onclick = function () {
      if (installEvent) {
        installEvent.prompt();
        installEvent.userChoice.finally(function () { installEvent = null; btn.hidden = true; });
      } else {
        openSheet('Install on iPhone', '<ol class="ma-qr-steps"><li>Tap the <b>Share</b> button in Safari.</li><li>Choose <b>Add to Home Screen</b>.</li><li>Tap <b>Add</b>. Open TAP Loans from your home screen.</li></ol>');
      }
    };
  }

  function logout() {
    Store.clearAll().then(function () {
      w.location.href = ROOT + 'logout';
    });
  }

  d.addEventListener('click', function (e) {
    var a = e.target.closest('[data-logout]');
    if (a) {
      e.preventDefault();
      if (confirm('Sign out? Loan data saved on this phone will be removed.')) logout();
    }
    if (e.target.closest('[data-sheet-close]') || e.target.id === 'maSheetBackdrop') closeSheet();
  });

  backBtn.addEventListener('click', function () {
    if (w.history.length > 1) w.history.back();
    else w.location.hash = '#/home';
  });

  syncBtn.addEventListener('click', function () { sync(false); });
  w.addEventListener('online', function () { updateSyncChip(); sync(true); });
  w.addEventListener('offline', updateSyncChip);
  w.addEventListener('hashchange', route);
  d.addEventListener('visibilitychange', function () {
    if (!d.hidden && state.savedAt && Date.now() - new Date(state.savedAt).getTime() > 5 * 60 * 1000) sync(true);
  });
  setInterval(updateSyncChip, 30000);

  // ------------------------------------------------------------------
  // Start
  // ------------------------------------------------------------------

  function start() {
    var prefs = Store.prefs();
    var switching = prefs.lastMember && prefs.lastMember !== MA.memberKey;
    var ready = switching ? Store.clearAll() : Promise.resolve();

    Store.registerServiceWorker(MA.base, function (worker) {
      var bar = d.getElementById('maUpdate');
      bar.hidden = false;
      bar.querySelector('[data-update]').onclick = function () {
        w.MA_RELOAD_ON_UPDATE = true;
        worker.postMessage({ type: 'SKIP_WAITING' });
      };
    });

    ready.then(function () {
      Store.setPrefs({ lastMember: MA.memberKey });
      return requireUnlock();
    }).then(function () {
      return Store.load(storageKey());
    }).then(function (rec) {
      if (rec && rec.snapshot) {
        state.snap = rec.snapshot;
        state.savedAt = rec.snapshot.synced_at;
        renderChrome();
      }
      route();
      sync(true);
    });
  }

  start();
})(window, document);
