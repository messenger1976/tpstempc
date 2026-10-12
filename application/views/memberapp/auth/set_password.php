<?php $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }; ?>
<div class="ma-steps"><span class="is-done">1 Verify</span><span class="is-done">2 Code</span><span class="is-active">3 Password</span></div>
<h1 class="ma-auth-title">Choose a password</h1>
<p class="ma-auth-sub">Member ID <strong><?php echo $e($flow['member_id']); ?></strong> will be your username.</p>

<?php if ($error !== '') { ?><div class="ma-alert ma-alert-error" role="alert"><?php echo $e($error); ?></div><?php } ?>

<form method="post" action="<?php echo site_url('customer_login/set_password'); ?>" class="ma-form" novalidate data-busy-form>
    <?php echo $csrf_field; ?>
    <input type="text" name="username" value="<?php echo $e($flow['member_id']); ?>" autocomplete="username" hidden>
    <label class="ma-field">
        <span>New password</span>
        <div class="ma-input-group">
            <input type="password" name="password" autocomplete="new-password" minlength="8" required data-strength>
            <button type="button" class="ma-input-addon" data-toggle-password aria-label="Show password">Show</button>
        </div>
        <div class="ma-strength" aria-hidden="true"><i></i></div>
        <small class="ma-hint">At least 8 characters, with letters and numbers.</small>
    </label>
    <label class="ma-field">
        <span>Confirm password</span>
        <input type="password" name="password_confirm" autocomplete="new-password" required>
    </label>
    <button type="submit" class="ma-btn ma-btn-block ma-btn-lg">Save and sign in</button>
</form>
