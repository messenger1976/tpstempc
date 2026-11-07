<style type="text/css">
.smtp-page { margin-top: 4px; }
.smtp-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.smtp-page .cbu-alert.success { background: #e8f8f5; color: #0e7c69; border: 1px solid #c9ebe3; }
.smtp-page .cbu-alert.danger { background: #fdeceb; color: #c0392b; border: 1px solid #f5c6cb; }
.smtp-page .cbu-alert.info { background: #f4f8fb; color: #2f4050; border: 1px solid #e7eaec; font-weight: 500; }
.smtp-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.smtp-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.smtp-page .cbu-panel .panel-head h4 { margin: 0; font-size: 15px; font-weight: 700; color: #2f4050; }
.smtp-page .cbu-panel .panel-body { padding: 20px; }
.smtp-page .form-horizontal .form-group {
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f2f3;
}
.smtp-page .form-horizontal .control-label { color: #676a6c; font-weight: 600; padding-top: 9px; }
.smtp-page .form-control { width: 100%; box-sizing: border-box; border-radius: 6px; border-color: #e5e6e7; box-shadow: none; height: 36px; }
.smtp-page select.form-control { height: 36px; }
.smtp-page .help-block { font-size: 12px; color: #888; margin-top: 6px; margin-bottom: 0; }
.smtp-page .required { color: #ed5565; }
.smtp-page .btn-primary { background: #1ab394; border-color: #1ab394; }
.smtp-page .btn { border-radius: 6px; font-weight: 600; }
.smtp-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
}
.smtp-page .status-pill {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .02em;
}
.smtp-page .status-pill.off { background: #f3f3f4; color: #676a6c; }
.smtp-page .status-pill.on { background: #e8f8f5; color: #0e7c69; }
</style>
<?php
$e = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$smtp = isset($smtp) && is_array($smtp) ? $smtp : array();
$val = function ($key, $default = '') use ($smtp) {
    return isset($smtp[$key]) ? $smtp[$key] : $default;
};
$crypto = (string) $val('smtp_crypto', 'tls');
$errors = isset($smtp_errors) && is_array($smtp_errors) ? $smtp_errors : array();
?>
<div class="col-lg-12 smtp-page">
    <?php if (isset($message) && $message !== '') { ?>
        <div class="cbu-alert success"><?php echo $e($message); ?></div>
    <?php } elseif ($this->session->flashdata('message')) { ?>
        <div class="cbu-alert success"><?php echo $e($this->session->flashdata('message')); ?></div>
    <?php } ?>
    <?php if (isset($warning) && $warning !== '') { ?>
        <div class="cbu-alert danger"><?php echo $e($warning); ?></div>
    <?php } elseif ($this->session->flashdata('warning')) { ?>
        <div class="cbu-alert danger"><?php echo $e($this->session->flashdata('warning')); ?></div>
    <?php } ?>
    <?php if ($errors) { ?>
        <div class="cbu-alert danger">
            <?php foreach ($errors as $error) { ?>
                <div><?php echo $e($error); ?></div>
            <?php } ?>
        </div>
    <?php } ?>

    <div class="cbu-panel">
        <div class="panel-head">
            <h4><i class="fa fa-envelope"></i> Email / SMTP</h4>
            <span class="status-pill <?php echo !empty($val('is_active')) ? 'on' : 'off'; ?>">
                <?php echo !empty($val('is_active')) ? 'Enabled' : 'Not enabled'; ?>
            </span>
        </div>
        <div class="panel-body">
            <p class="help-block" style="margin-bottom:16px"><?php echo $e($val('status_label')); ?></p>
            <?php if (empty($val('key_ready'))) { ?>
                <div class="cbu-alert info"><?php echo $e($val('key_message')); ?></div>
            <?php } ?>

            <?php echo form_open(current_lang() . '/setting/smtp_settings', 'class="form-horizontal" id="smtpSettingsForm" autocomplete="off"'); ?>
                <input type="hidden" name="smtp_token" value="<?php echo $e(isset($csrf_token) ? $csrf_token : ''); ?>">

                <div class="form-group">
                    <label class="col-lg-3 control-label" for="smtp_host">SMTP host <?php echo !empty($val('is_active')) ? '<span class="required">*</span>' : ''; ?></label>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" maxlength="255" value="<?php echo $e($val('smtp_host')); ?>" placeholder="smtp.example.com" autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="smtp_port">SMTP port <?php echo !empty($val('is_active')) ? '<span class="required">*</span>' : ''; ?></label>
                    <div class="col-lg-3">
                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" min="1" max="65535" value="<?php echo $e($val('smtp_port')); ?>" placeholder="587">
                        <p class="help-block">TLS usually uses 587. SSL usually uses 465.</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="smtp_crypto">Encryption</label>
                    <div class="col-lg-4">
                        <select class="form-control" id="smtp_crypto" name="smtp_crypto">
                            <option value="tls" <?php echo $crypto === 'tls' ? 'selected' : ''; ?>>TLS (STARTTLS, usually port 587)</option>
                            <option value="ssl" <?php echo $crypto === 'ssl' ? 'selected' : ''; ?>>SSL (usually port 465)</option>
                            <option value="" <?php echo $crypto === '' ? 'selected' : ''; ?>>None (not encrypted)</option>
                        </select>
                        <p class="help-block">Certificate checks stay at the server default. This page cannot turn them off.</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="smtp_timeout">Timeout (seconds)</label>
                    <div class="col-lg-3">
                        <input type="number" class="form-control" id="smtp_timeout" name="smtp_timeout" min="1" max="120" value="<?php echo $e($val('smtp_timeout', '20')); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="smtp_user">SMTP username</label>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" id="smtp_user" name="smtp_user" maxlength="255" value="<?php echo $e($val('smtp_user')); ?>" autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="smtp_pass">SMTP password <?php echo !empty($val('has_password')) ? '' : ''; ?></label>
                    <div class="col-lg-6">
                        <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" maxlength="512" value="" autocomplete="new-password" placeholder="<?php echo !empty($val('has_password')) ? 'Leave blank to keep saved password' : 'No password saved'; ?>">
                        <?php if (!empty($val('has_password'))) { ?>
                            <p class="help-block">An encrypted password is saved. It is not shown here.</p>
                            <label class="small text-muted"><input type="checkbox" name="clear_password" value="1"> Remove saved password</label>
                        <?php } else { ?>
                            <p class="help-block">Leave this blank if the mail server does not use a password. A password is encrypted before it is stored.</p>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="from_email">From address <?php echo !empty($val('is_active')) ? '<span class="required">*</span>' : ''; ?></label>
                    <div class="col-lg-6">
                        <input type="email" class="form-control" id="from_email" name="from_email" maxlength="255" value="<?php echo $e($val('from_email')); ?>" autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="from_name">From name <?php echo !empty($val('is_active')) ? '<span class="required">*</span>' : ''; ?></label>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" id="from_name" name="from_name" maxlength="255" value="<?php echo $e($val('from_name')); ?>" autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="is_active">SMTP sending</label>
                    <div class="col-lg-6">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo !empty($val('is_active')) ? 'checked' : ''; ?>>
                                Enable SMTP for outgoing mail
                            </label>
                        </div>
                        <p class="help-block">Leave this unchecked to keep the current mail transport. Saving host and sender details does not turn SMTP on. When SMTP is enabled and the server rejects a message, that message is not sent by another method.</p>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Email / SMTP</button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head"><h4>Before the first password can be saved</h4></div>
        <div class="panel-body">
            <ol>
                <li>On the server, create a key that is not committed to source control. Either export <code>TAPSTEMCO_SMTP_KEY</code>, or copy <code>application/config/smtp.key.example</code> to <code>application/config/smtp.key</code> and replace the placeholder. The example file is not a working key.</li>
                <li>Generate the key on the server: <code>php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"</code></li>
                <li>Open this page again and type the mailbox host, port, encryption, username, password, From address, and From name. Nothing is prefilled from another system.</li>
                <li>Save once with SMTP still unchecked, confirm the password line says an encrypted password is saved, then check <strong>Enable SMTP</strong> and save again.</li>
            </ol>
            <p class="help-block">These settings are used for member-app email codes and for the other messages sent through the shared mailer (quotations, sales invoices, purchase orders, purchase invoices, and account emails). The password is not written to the page, to JSON, or to the error log.</p>
        </div>
    </div>
</div>
