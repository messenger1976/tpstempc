<?php $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }; ?>
<a href="<?php echo site_url('customer_login'); ?>" class="ma-back">&larr; Back to sign in</a>
<h1 class="ma-auth-title">Code from the office</h1>
<p class="ma-auth-sub">Enter the one-time code that cooperative staff gave you. It is valid for 10 minutes.</p>

<?php if ($error !== '') { ?><div class="ma-alert ma-alert-error" role="alert"><?php echo $e($error); ?></div><?php } ?>

<form method="post" action="<?php echo site_url('customer_login/office_code'); ?>" class="ma-form" novalidate data-busy-form>
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
        <span>6-digit code</span>
        <input type="text" name="code" class="ma-otp" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required>
    </label>
    <button type="submit" class="ma-btn ma-btn-block ma-btn-lg">Continue</button>
</form>
