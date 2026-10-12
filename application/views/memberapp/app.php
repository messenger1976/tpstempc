<?php
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$v = $e($asset_version);
$nav = array(
    array('home', 'Home', 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z'),
    array('loans', 'Loans', 'M4 5h16v14H4zM4 9h16M8 13h4'),
    array('pay', 'Pay', 'M3 7h18v10H3zM3 11h18M7 15h3'),
    array('apply', 'Apply', 'M12 5v14M5 12h14'),
    array('menu', 'Menu', 'M4 7h16M4 12h16M4 17h16'),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $this->load->view('memberapp/layout/head'); ?>
</head>
<body class="ma-app-body">
<div class="ma-app" id="maApp">
    <aside class="ma-sidebar" aria-label="Main">
        <div class="ma-sidebar-brand">
            <img src="<?php echo base_url('media/memberapp/icons/icon-192.png'); ?>" alt="" width="40" height="40">
            <div><strong>TAP Loans</strong><span><?php echo $e($company_name); ?></span></div>
        </div>
        <nav class="ma-sidebar-nav">
            <?php foreach ($nav as $n) { ?>
            <a href="#/<?php echo $n[0]; ?>" data-nav="<?php echo $n[0]; ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?php echo $n[2]; ?>"/></svg><span><?php echo $n[0] === 'menu' ? 'More' : $n[1]; ?></span>
            </a>
            <?php } ?>
            <a href="#/payments" data-nav="payments"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14v16l-3-2-2 2-2-2-2 2-2-2-3 2z"/></svg><span>Payments</span></a>
            <a href="#/applications" data-nav="applications"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l3 3v15H6zM9 11h6M9 15h6"/></svg><span>Applications</span></a>
        </nav>
        <a class="ma-sidebar-out" href="<?php echo site_url('customer_login/logout'); ?>" data-logout>Sign out</a>
    </aside>

    <div class="ma-main">
        <header class="ma-topbar">
            <button type="button" class="ma-icon-btn ma-topbar-back" data-back hidden aria-label="Back">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
            </button>
            <div class="ma-topbar-title">
                <span class="ma-topbar-kicker" id="maKicker">Hello, <?php echo $e($first_name); ?></span>
                <strong id="maTitle">Home</strong>
            </div>
            <button type="button" class="ma-sync" id="maSync" aria-live="polite" title="Refresh">
                <i class="ma-sync-dot"></i><span>Syncing…</span>
            </button>
            <a href="#/profile" class="ma-avatar" id="maAvatar" aria-label="My profile"></a>
        </header>

        <div class="ma-offline-bar" id="maOffline" hidden>You are offline &mdash; showing data saved on this phone.</div>

        <main class="ma-view" id="maView" tabindex="-1">
            <div class="ma-skeleton">
                <div class="ma-sk ma-sk-hero"></div>
                <div class="ma-sk-row"><div class="ma-sk"></div><div class="ma-sk"></div></div>
                <div class="ma-sk ma-sk-card"></div>
                <div class="ma-sk ma-sk-card"></div>
            </div>
        </main>
    </div>

    <nav class="ma-bottomnav" aria-label="Main">
        <?php foreach ($nav as $n) { ?>
        <a href="#/<?php echo $n[0]; ?>" data-nav="<?php echo $n[0]; ?>" class="<?php echo $n[0] === 'pay' ? 'ma-bottomnav-pay' : ''; ?>">
            <span class="ma-bn-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?php echo $n[2]; ?>"/></svg></span>
            <span class="ma-bn-label"><?php echo $n[1]; ?></span>
        </a>
        <?php } ?>
    </nav>
</div>

<div class="ma-sheet-backdrop" id="maSheetBackdrop" hidden></div>
<section class="ma-sheet" id="maSheet" role="dialog" aria-modal="true" aria-labelledby="maSheetTitle" hidden>
    <div class="ma-sheet-grip"></div>
    <header class="ma-sheet-head">
        <h2 id="maSheetTitle"></h2>
        <button type="button" class="ma-icon-btn" data-sheet-close aria-label="Close">&times;</button>
    </header>
    <div class="ma-sheet-body" id="maSheetBody"></div>
</section>

<div class="ma-toasts" id="maToasts" aria-live="polite"></div>

<div class="ma-update" id="maUpdate" hidden>
    <span>A new version of the app is ready.</span>
    <button type="button" class="ma-btn ma-btn-sm" data-update>Update</button>
</div>

<div class="ma-lock" id="maLock" hidden>
    <div class="ma-lock-inner">
        <img src="<?php echo base_url('media/memberapp/icons/icon-192.png'); ?>" alt="" width="72" height="72">
        <h2>Enter your app PIN</h2>
        <div class="ma-pin-dots" id="maPinDots"><i></i><i></i><i></i><i></i></div>
        <p class="ma-lock-error" id="maLockError"></p>
        <div class="ma-keypad" id="maKeypad">
            <?php foreach (array(1, 2, 3, 4, 5, 6, 7, 8, 9) as $k) { ?><button type="button" data-key="<?php echo $k; ?>"><?php echo $k; ?></button><?php } ?>
            <button type="button" data-key="forgot" class="ma-keypad-text">Forgot</button>
            <button type="button" data-key="0">0</button>
            <button type="button" data-key="del" class="ma-keypad-text" aria-label="Delete">&#9003;</button>
        </div>
    </div>
</div>

<noscript><div class="ma-noscript">Please turn on JavaScript to use the app.</div></noscript>

<script>
window.MA = {
    base: <?php echo json_encode(rtrim(base_url(), '/') . '/'); ?>,
    page: 'app',
    csrf: <?php echo json_encode($csrf_token); ?>,
    memberKey: <?php echo json_encode($member_key); ?>,
    firstName: <?php echo json_encode($first_name); ?>,
    version: <?php echo json_encode($asset_version); ?>
};
</script>
<script src="<?php echo base_url('media/memberapp/vendor/chart.umd.min.js'); ?>?v=<?php echo $v; ?>" defer></script>
<script src="<?php echo base_url('media/memberapp/js/offline-store.js'); ?>?v=<?php echo $v; ?>" defer></script>
<script src="<?php echo base_url('media/memberapp/js/app.js'); ?>?v=<?php echo $v; ?>" defer></script>
</body>
</html>
