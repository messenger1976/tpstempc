<?php
$account = isset($account) ? $account : null;
$PIN = isset($PIN) ? $PIN : '';
$view_url = site_url(current_lang() . '/setting/companyinfo_view');
$logo_file = ($account && !empty($account->logo)) ? $account->logo : '';
$logo_url = $logo_file !== '' ? base_url('logo/' . $logo_file) : '';

$phone_full = ($account && isset($account->mobile)) ? (string) $account->mobile : '';
$phone_select = ($phone_full !== '') ? substr($phone_full, 0, -10) : '';
$phone_number = ($phone_full !== '') ? substr($phone_full, -10) : '';
if (set_value('phone') !== '') {
    $phone_number = set_value('phone');
}
if (set_value('pre_phone') !== '') {
    $phone_select = set_value('pre_phone');
}
?>

<style type="text/css">
.ci-edit-page { margin-top: 4px; }
.ci-edit-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.ci-edit-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.ci-edit-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.ci-edit-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.ci-edit-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.ci-edit-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ci-edit-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ci-edit-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.ci-edit-page .cbu-panel .panel-body { padding: 20px; }
.ci-edit-page .form-horizontal .form-group { margin-bottom: 16px; }
.ci-edit-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.ci-edit-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.ci-edit-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.ci-edit-page .required { color: #ed5565; }
.ci-edit-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.ci-edit-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.ci-edit-page .phone-addon {
    background: #f8fafb;
    border: 1px solid #e5e6e7;
    border-right: 0;
    border-radius: 6px 0 0 6px;
    padding: 0;
}
.ci-edit-page .phone-addon select {
    background: transparent;
    border: 0;
    height: 34px;
    padding: 0 10px;
    color: #2f4050;
    font-weight: 600;
}
.ci-edit-page .input-group .form-control {
    border-radius: 0 6px 6px 0;
}
.ci-edit-page .logo-preview {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 12px;
    padding: 12px;
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
}
.ci-edit-page .logo-preview img {
    width: 72px;
    height: 72px;
    object-fit: contain;
    border-radius: 8px;
    background: #fff;
    border: 1px solid #eef1f2;
}
.ci-edit-page .logo-preview .meta {
    font-size: 12px;
    color: #888;
}
.ci-edit-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.ci-edit-page .error_message {
    color: #c0392b;
    margin-top: 6px;
    font-size: 12px;
    font-weight: 600;
}
</style>

<?php echo form_open_multipart(current_lang() . '/setting/companyinfo_edit/' . $PIN, 'class="form-horizontal" id="companyInfoEditForm"'); ?>

<div class="col-lg-12 ci-edit-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="cbu-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="cbu-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="cbu-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="cbu-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-building icon-badge"></i>
                <h4><?php echo lang('setting_create_clientaccount_edit'); ?></h4>
            </div>
            <a href="<?php echo $view_url; ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
            </a>
        </div>
        <div class="panel-body">
            <?php if ($account) { ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-lg-4 control-label"><?php echo lang('clientaccount_label_name'); ?> : <span class="required">*</span></label>
                            <div class="col-lg-8">
                                <input type="text" name="companyname"
                                       value="<?php echo htmlspecialchars(set_value('companyname', $account->name), ENT_QUOTES, 'UTF-8'); ?>"
                                       class="form-control"/>
                                <?php echo form_error('companyname'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-lg-4 control-label"><?php echo lang('clientaccount_label_email'); ?> : <span class="required">*</span></label>
                            <div class="col-lg-8">
                                <input type="text" name="email"
                                       value="<?php echo htmlspecialchars(set_value('email', $account->email), ENT_QUOTES, 'UTF-8'); ?>"
                                       class="form-control"/>
                                <?php echo form_error('email'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-lg-4 control-label"><?php echo lang('clientaccount_label_postal_address'); ?> :</label>
                            <div class="col-lg-8">
                                <input type="text" name="postaladdress"
                                       value="<?php echo htmlspecialchars(set_value('postaladdress', $account->box), ENT_QUOTES, 'UTF-8'); ?>"
                                       class="form-control"/>
                                <?php echo form_error('postaladdress'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-lg-4 control-label"><?php echo lang('clientaccount_label_physical_address'); ?> : <span class="required">*</span></label>
                            <div class="col-lg-8">
                                <input type="text" name="physicaladdress"
                                       value="<?php echo htmlspecialchars(set_value('physicaladdress', $account->address), ENT_QUOTES, 'UTF-8'); ?>"
                                       class="form-control"/>
                                <?php echo form_error('physicaladdress'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-lg-4 control-label"><?php echo lang('clientaccount_label_phone'); ?> : <span class="required">*</span></label>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <span class="input-group-addon phone-addon">
                                        <select name="pre_phone">
                                            <?php foreach (mobile_code() as $value) { ?>
                                                <option value="<?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?php echo ((string) $phone_select === (string) $value->name) ? 'selected="selected"' : ''; ?>>
                                                    <?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </span>
                                    <input type="text" name="phone"
                                           value="<?php echo htmlspecialchars($phone_number, ENT_QUOTES, 'UTF-8'); ?>"
                                           class="form-control"/>
                                </div>
                                <?php echo form_error('phone'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-lg-4 control-label"><?php echo lang('clientaccount_label_fax'); ?> :</label>
                            <div class="col-lg-8">
                                <input type="text" name="fax"
                                       value="<?php echo htmlspecialchars(set_value('fax', $account->fax), ENT_QUOTES, 'UTF-8'); ?>"
                                       class="form-control"/>
                                <?php echo form_error('fax'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-lg-2 control-label"><?php echo lang('clientaccount_label_logo'); ?> :</label>
                            <div class="col-lg-10">
                                <?php if ($logo_url !== '') { ?>
                                    <div class="logo-preview">
                                        <img src="<?php echo htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Current logo"/>
                                        <div class="meta">
                                            Current logo. Upload a new file to replace it.<br>
                                            Allowed: JPG, JPEG, PNG, GIF
                                        </div>
                                    </div>
                                <?php } ?>
                                <input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.gif,image/*"/>
                                <?php if (isset($logo_error)) {
                                    echo '<div class="error_message">' . $logo_error . '</div>';
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?php echo lang('clientaccount_label_btnedit'); ?>
                    </button>
                    <a href="<?php echo $view_url; ?>" class="btn btn-default">
                        <i class="fa fa-undo"></i> <?php echo lang('cancel'); ?>
                    </a>
                </div>
            <?php } else { ?>
                <p class="text-muted">Company information could not be loaded.</p>
            <?php } ?>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
