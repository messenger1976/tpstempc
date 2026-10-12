<?php $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }; ?>
<a href="<?php echo site_url('customer_login/' . ($flow['purpose'] === 'reset' ? 'forgot' : 'activate')); ?>" class="ma-back">&larr; Start over</a>
<div class="ma-steps"><span class="is-done">1 Verify</span><span class="is-active">2 Code</span><span>3 Password</span></div>
<h1 class="ma-auth-title">Enter your code</h1>

<?php if (!empty($flow['delivered'])) { ?>
    <p class="ma-auth-sub">We sent a 6-digit code to <strong><?php echo $e($flow['masked']); ?></strong>. It is valid for 10 minutes.</p>
<?php } else { ?>
    <div class="ma-alert ma-alert-warn">We could not send the code to <strong><?php echo $e($flow['masked']); ?></strong> right now. Try again, or ask the cooperative office for a one-time code.</div>
<?php } ?>
<?php if (!empty($flow['dev_code'])) { ?>
    <div class="ma-alert ma-alert-info">Development mode &mdash; your code is <strong><?php echo $e($flow['dev_code']); ?></strong></div>
<?php } ?>
<?php if ($notice !== '') { ?><div class="ma-alert ma-alert-info"><?php echo $e($notice); ?></div><?php } ?>
<?php if ($error !== '') { ?><div class="ma-alert ma-alert-error" role="alert"><?php echo $e($error); ?></div><?php } ?>

<form method="post" action="<?php echo site_url('customer_login/verify'); ?>" class="ma-form" novalidate data-busy-form>
    <?php echo $csrf_field; ?>
    <label class="ma-field">
        <span>6-digit code</span>
        <input type="text" name="code" class="ma-otp" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required autofocus>
    </label>
    <button type="submit" class="ma-btn ma-btn-block ma-btn-lg">Verify</button>
</form>
<form method="post" action="<?php echo site_url('customer_login/verify'); ?>" class="ma-resend" data-resend-form>
    <?php echo $csrf_field; ?>
    <input type="hidden" name="resend" value="1">
    <span>Didn't get it?</span>
    <button type="submit" class="ma-link-btn" data-resend data-wait="60">Send a new code</button>
</form>
