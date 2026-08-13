<style type="text/css">
.ss-page { margin-top: 4px; }
.ss-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.ss-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.ss-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.ss-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.ss-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.ss-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ss-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ss-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.ss-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.ss-page .form-horizontal .form-group {
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f2f3;
}
.ss-page .form-horizontal .form-group:last-of-type {
    border-bottom: 0;
    margin-bottom: 8px;
    padding-bottom: 0;
}
.ss-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.ss-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.ss-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.ss-page .required { color: #ed5565; }
.ss-page .help-block {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
    margin-bottom: 0;
}
.ss-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.ss-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.ss-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.ss-page .form-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
}
.ss-page .form-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
.ss-page .form-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.ss-page .form-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
</style>

<?php
$share = $this->setting_model->share_setting_info();
$share_value = set_value('share_value');
$share_minimum = set_value('share_minimum');
$share_maximum = set_value('share_maximum');
if ($share_value === '') {
    $share_value = ($share && isset($share->amount)) ? number_format($share->amount) : '';
}
if ($share_minimum === '') {
    $share_minimum = ($share && isset($share->min_share)) ? $share->min_share : '';
}
if ($share_maximum === '') {
    $share_maximum = ($share && isset($share->max_share)) ? $share->max_share : '';
}
?>

<?php echo form_open_multipart(current_lang() . "/setting/share_setup", 'class="form-horizontal"'); ?>

<div class="col-lg-12 ss-page">
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
                <i class="fa fa-certificate icon-badge"></i>
                <h4><?php echo lang('setting_share_setup'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('share_current_value'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <input type="text" name="share_value" value="<?php echo htmlspecialchars($share_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control amountformat"/>
                    <?php echo form_error('share_value'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('share_minimum'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <input type="text" name="share_minimum" value="<?php echo htmlspecialchars($share_minimum, ENT_QUOTES, 'UTF-8'); ?>" class="form-control amountformat"/>
                    <?php echo form_error('share_minimum'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('share_maximum'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <input type="text" name="share_maximum" value="<?php echo htmlspecialchars($share_maximum, ENT_QUOTES, 'UTF-8'); ?>" class="form-control amountformat"/>
                    <?php echo form_error('share_maximum'); ?>
                    <p class="help-block"><?php echo lang('share_max_less_min'); ?></p>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?php echo site_url(current_lang() . '/setting/share_setup'); ?>" class="btn btn-default">
                    <i class="fa fa-undo"></i> Clear
                </a>
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo lang('save_info_btn'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
