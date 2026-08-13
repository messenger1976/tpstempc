<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">
<style type="text/css">
.select2-container { width: 100% !important; }
.ac-create-page { margin-top: 4px; }
.ac-create-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.ac-create-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.ac-create-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.ac-create-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.ac-create-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.ac-create-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ac-create-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ac-create-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.ac-create-page .cbu-panel .panel-head .btn {
    border-radius: 6px;
    font-weight: 600;
}
.ac-create-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.ac-create-page .form-horizontal .form-group {
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f2f3;
}
.ac-create-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.ac-create-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.ac-create-page textarea.form-control { height: auto; min-height: 80px; }
.ac-create-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.ac-create-page .required { color: #ed5565; }
.ac-create-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.ac-create-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.ac-create-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.ac-create-page .form-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
}
.ac-create-page .form-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
.ac-create-page .form-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.ac-create-page .form-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.ac-create-page .parent-banner {
    margin: 0 0 18px;
    padding: 10px 14px;
    background: #e8f8f5;
    border: 1px solid #c9ebe3;
    border-radius: 8px;
    color: #0e7c69;
    font-size: 13px;
    font-weight: 700;
}
.ac-create-page .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.ac-create-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.ac-create-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.ac-create-page .select2-container--default.select2-container--focus .select2-selection--single,
.ac-create-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}
</style>

<?php
$list_url = site_url(current_lang() . '/finance/finance_account_list');
$selected_type = set_value('account_type');
$parent_suffix = isset($parent) ? $parent : '';
?>

<?php echo form_open_multipart(current_lang() . "/finance/finance_account_create/" . $parent_suffix, 'class="form-horizontal"'); ?>

<div class="col-lg-12 ac-create-page">
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
                <i class="fa fa-plus icon-badge"></i>
                <h4><?php echo lang('finance_account_create'); ?></h4>
            </div>
            <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> <?php echo lang('button_cancel'); ?>
            </a>
        </div>
        <div class="panel-body">
            <?php if (isset($parent_info) && $parent_info) { ?>
                <div class="parent-banner">
                    <?php echo htmlspecialchars($parent_info->account . ' : ' . $parent_info->name, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php } ?>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('finance_account_type'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <select class="form-control" name="account_type" id="account_type">
                        <option value=""><?php echo lang('select_default_text'); ?></option>
                        <?php foreach ($account_typelist as $value) { ?>
                            <optgroup label="<?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php
                                $sub_account = $this->finance_model->account_type_sub(null, $value->account)->result();
                                foreach ($sub_account as $xv) {
                                    $opt_val = $xv->accounttype . '_' . $xv->sub_account;
                                    ?>
                                    <option <?php echo ((string) $selected_type === (string) $opt_val ? 'selected="selected"' : ''); ?> value="<?php echo htmlspecialchars($opt_val, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($xv->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </optgroup>
                        <?php } ?>
                    </select>
                    <?php echo form_error('account_type'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('finance_account_code'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <input type="text" name="accountcode" value="<?php echo htmlspecialchars(set_value('accountcode'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                    <?php echo form_error('accountcode'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('finance_account_name'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <input type="text" name="accountname" value="<?php echo htmlspecialchars(set_value('accountname'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                    <?php echo form_error('accountname'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('finance_account_description'); ?> :</label>
                <div class="col-lg-6">
                    <textarea name="accountdescription" class="form-control"><?php echo htmlspecialchars(set_value('accountdescription'), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php echo form_error('accountdescription'); ?>
                </div>
            </div>

            <?php if (is_null($parent)) { ?>
                <div class="form-group" style="display: none;">
                    <label class="col-lg-3 control-label"><?php echo lang('finance_account_is_header'); ?> :</label>
                    <div class="col-lg-6">
                        <input type="checkbox" name="is_header" value="1" class="checkbox"/>
                        <?php echo form_error('is_header'); ?>
                    </div>
                </div>
            <?php } ?>

            <div class="form-actions">
                <a href="<?php echo $list_url; ?>" class="btn btn-default">
                    <i class="fa fa-undo"></i> <?php echo lang('button_cancel'); ?>
                </a>
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo lang('finance_account_btncreate'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
(function() {
    function boot() {
        if (!window.jQuery || !jQuery.fn.select2) {
            setTimeout(boot, 50);
            return;
        }
        jQuery('#account_type').select2({
            width: '100%',
            placeholder: <?php echo json_encode(lang('select_default_text')); ?>
        });
    }
    boot();
})();
</script>
