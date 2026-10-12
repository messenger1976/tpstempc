<?php
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$is_reset = $purpose === 'reset';
?>
<a href="<?php echo site_url('customer_login'); ?>" class="ma-back">&larr; Back to sign in</a>
<div class="ma-steps"><span class="is-active">1 Verify</span><span>2 Code</span><span>3 Password</span></div>
<h1 class="ma-auth-title"><?php echo $is_reset ? 'Reset your password' : 'Activate your account'; ?></h1>
<p class="ma-auth-sub">Enter the details the cooperative has on file. We will send a 6-digit code to your registered email or mobile number.</p>

<?php if ($error !== '') { ?><div class="ma-alert ma-alert-error" role="alert"><?php echo $e($error); ?></div><?php } ?>

<form method="post" action="<?php echo site_url('customer_login/' . ($is_reset ? 'forgot' : 'activate')); ?>" class="ma-form" novalidate data-busy-form>
    <?php echo $csrf_field; ?>
    <label class="ma-field">
        <span>Member ID</span>
        <input type="text" name="member_id" value="<?php echo $e($member_id); ?>" autocomplete="username" autocapitalize="characters" spellcheck="false" required>
    </label>
    <label class="ma-field">
        <span>Birthdate</span>
        <input type="date" name="dob" value="<?php echo $e($dob); ?>" required max="<?php echo date('Y-m-d'); ?>">
    </label>
    <label class="ma-field">
        <span>Registered email or mobile number</span>
        <input type="text" name="contact" value="<?php echo $e($contact); ?>" autocomplete="email" inputmode="email" placeholder="you@email.com or 09XXXXXXXXX" required>
    </label>
    <button type="submit" class="ma-btn ma-btn-block ma-btn-lg">Send my code</button>
</form>
<p class="ma-auth-help">Details changed or no email on file? Visit the cooperative office; staff can give you a one-time code.</p>
