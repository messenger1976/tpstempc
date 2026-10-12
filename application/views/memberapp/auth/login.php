<?php $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }; ?>
<h1 class="ma-auth-title">Welcome back</h1>
<p class="ma-auth-sub">Sign in with your Member ID.</p>

<?php if (!empty($notice)) { ?><div class="ma-alert ma-alert-info"><?php echo $e($notice); ?></div><?php } ?>
<?php if (!empty($clear_local)) { ?><div class="ma-alert ma-alert-info">You are signed out. Data saved on this phone was removed.</div><?php } ?>
<?php if ($error !== '') { ?><div class="ma-alert ma-alert-error" role="alert"><?php echo $e($error); ?></div><?php } ?>

<form method="post" action="<?php echo site_url('customer_login'); ?>" class="ma-form" novalidate data-busy-form>
    <?php echo $csrf_field; ?>
    <label class="ma-field">
        <span>Member ID</span>
        <input type="text" name="member_id" value="<?php echo $e($member_id); ?>" autocomplete="username" autocapitalize="characters" spellcheck="false" required autofocus>
    </label>
    <label class="ma-field">
        <span>Password</span>
        <div class="ma-input-group">
            <input type="password" name="password" autocomplete="current-password" required>
            <button type="button" class="ma-input-addon" data-toggle-password aria-label="Show password">Show</button>
        </div>
    </label>
    <div class="ma-row-between">
        <label class="ma-check"><input type="checkbox" name="remember" value="1" checked> <span>Keep me signed in</span></label>
        <a href="<?php echo site_url('customer_login/forgot'); ?>" class="ma-link">Forgot password?</a>
    </div>
    <button type="submit" class="ma-btn ma-btn-block ma-btn-lg">Sign in</button>
</form>

<div class="ma-divider"><span>New to the app?</span></div>
<a href="<?php echo site_url('customer_login/activate'); ?>" class="ma-btn ma-btn-outline ma-btn-block">Activate my account</a>
<p class="ma-auth-help">Got a code from the cooperative office? <a href="<?php echo site_url('customer_login/office_code'); ?>" class="ma-link">Enter it here</a>.</p>
