<?php $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $this->load->view('memberapp/layout/head'); ?>
</head>
<body class="ma-auth-body">
<div class="ma-auth">
    <aside class="ma-auth-hero" aria-hidden="true">
        <div class="ma-auth-hero-inner">
            <img src="<?php echo base_url('media/memberapp/icons/icon-192.png'); ?>" alt="" class="ma-auth-hero-logo" width="96" height="96">
            <h2><?php echo $e($company_name); ?></h2>
            <p>Your loans, in your pocket.</p>
            <ul class="ma-auth-points">
                <li><span class="ma-dot"></span>See balances, schedules and every payment</li>
                <li><span class="ma-dot"></span>Pay any time with QR Ph (GCash, Maya, banks)</li>
                <li><span class="ma-dot"></span>Apply for a new loan or a reloan</li>
                <li><span class="ma-dot"></span>Works offline once you have signed in</li>
            </ul>
        </div>
    </aside>

    <main class="ma-auth-main">
        <div class="ma-auth-card">
            <div class="ma-auth-brand">
                <img src="<?php echo base_url('media/memberapp/icons/icon-192.png'); ?>" alt="" width="56" height="56">
                <div>
                    <strong><?php echo $e($app_name); ?></strong>
                    <span>Member portal</span>
                </div>
            </div>
            <?php $this->load->view($content_view); ?>
        </div>
        <p class="ma-auth-foot">&copy; <?php echo date('Y'); ?> <?php echo $e($company_name); ?> &middot; Members only</p>
    </main>
</div>

<div class="ma-install-banner" id="maInstall" hidden>
    <img src="<?php echo base_url('media/memberapp/icons/icon-192.png'); ?>" alt="" width="40" height="40">
    <div><strong>Install TAP Loans</strong><span>Open it from your home screen, even offline.</span></div>
    <button type="button" class="ma-btn ma-btn-sm" data-install>Install</button>
    <button type="button" class="ma-icon-btn" data-install-dismiss aria-label="Close">&times;</button>
</div>

<script>
window.MA = {
    base: <?php echo json_encode(rtrim(base_url(), '/') . '/'); ?>,
    page: 'auth',
    clearLocal: <?php echo !empty($clear_local) ? 'true' : 'false'; ?>,
    version: <?php echo json_encode($asset_version); ?>
};
</script>
<script src="<?php echo base_url('media/memberapp/js/offline-store.js'); ?>?v=<?php echo $e($asset_version); ?>"></script>
<script src="<?php echo base_url('media/memberapp/js/auth.js'); ?>?v=<?php echo $e($asset_version); ?>"></script>
</body>
</html>
