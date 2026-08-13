<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php
$product = isset($product) ? $product : null;
$id = isset($id) ? $id : '';
$account_list = isset($account_list) ? $account_list : array();
$interval_list = isset($interval_list) ? $interval_list : array();
$interest_method_list = isset($interest_method_list) ? $interest_method_list : array();
$penalt_method_list = isset($penalt_method_list) ? $penalt_method_list : array();
$is_edit = !empty($product);
$list_url = site_url(current_lang() . '/setting/loan_product_list');
$page_title = $is_edit ? lang('loanproduct_edit') : lang('loanproduct_add');

$val = function ($field, $fallback = '') use ($product) {
    if ($product && isset($product->$field)) {
        return $product->$field;
    }
    return set_value($field, $fallback);
};

$grace_val = '';
if ($product && isset($product->penalt_grace_days) && $product->penalt_grace_days !== null && $product->penalt_grace_days !== '') {
    $grace_val = $product->penalt_grace_days;
} else {
    $grace_val = set_value('penalt_grace_days');
}
$system_grace = defined('MAX_NUMBER_DAYS_OVERDUE_PENALT') ? (int) MAX_NUMBER_DAYS_OVERDUE_PENALT : 3;

$selected_principle = $product ? $product->loan_principle_account : set_value('loan_principle_account');
$selected_interest = $product ? $product->loan_interest_account : set_value('loan_interest_account');
$selected_penalt = $product ? $product->loan_penalt_account : set_value('loan_penalt_account');
$selected_interval = $product ? $product->interval : set_value('interval');
$selected_interest_method = $product ? $product->interest_method : set_value('interest_method');
$selected_penalt_method = $product ? $product->penalt_method : set_value('penalt_method');
$contribution_times = $product ? $product->loan_security_contribution_times : set_value('loanproduct_contribution_times');
?>

<style type="text/css">
.select2-container--default .select2-results__option[aria-disabled=true]{color:#222;cursor:default;}
.select2-container--default .select2-results__option .coa-bold{font-weight:bold;color:#111;}
.select2-container{width:100%!important;}

.lp-form-page { margin-top: 4px; }
.lp-form-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.lp-form-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.lp-form-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.lp-form-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.lp-form-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.lp-form-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.lp-form-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.lp-form-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.lp-form-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.lp-form-page .form-horizontal .form-group { margin-bottom: 16px; }
.lp-form-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.lp-form-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.lp-form-page textarea.form-control { height: auto; min-height: 80px; }
.lp-form-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.lp-form-page .help-block {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
    margin-bottom: 0;
}
.lp-form-page .required { color: #ed5565; }
.lp-form-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.lp-form-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.lp-form-page .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.lp-form-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.lp-form-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.lp-form-page .select2-container--default.select2-container--focus .select2-selection--single,
.lp-form-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}
.lp-form-page .form-actions {
    margin-top: 4px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
</style>

<?php echo form_open_multipart(current_lang() . '/setting/addloan_product/' . $id, 'class="form-horizontal" id="loanProductForm"'); ?>

<div class="col-lg-12 lp-form-page">
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
                <i class="fa fa-briefcase icon-badge"></i>
                <h4><?php echo $page_title; ?></h4>
            </div>
            <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_name'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="name" value="<?php echo htmlspecialchars($val('name'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                            <?php echo form_error('name'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_interval'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <select name="interval" class="form-control">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php foreach ($interval_list as $value) { ?>
                                    <option value="<?php echo (int) $value->id; ?>" <?php echo ((string) $value->id === (string) $selected_interval) ? 'selected="selected"' : ''; ?>>
                                        <?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('interval'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-lg-2 control-label"><?php echo lang('loanproduct_description'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-10">
                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($val('description'), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <?php echo form_error('description'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_interest'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="interest_rate" value="<?php echo htmlspecialchars($val('interest_rate'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('interest_rate'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_interest_method'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <select name="interest_method" class="form-control">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php foreach ($interest_method_list as $value) { ?>
                                    <option value="<?php echo (int) $value->id; ?>" <?php echo ((string) $value->id === (string) $selected_interest_method) ? 'selected="selected"' : ''; ?>>
                                        <?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('interest_method'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_penalt_method'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <select name="penalt_method" class="form-control">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php foreach ($penalt_method_list as $value) { ?>
                                    <option value="<?php echo (int) $value->id; ?>" <?php echo ((string) $value->id === (string) $selected_penalt_method) ? 'selected="selected"' : ''; ?>>
                                        <?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('penalt_method'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_penalt_percentage'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="penalt_percentage" value="<?php echo htmlspecialchars($val('penalt_percentage'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('penalt_percentage'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_penalt_grace_days'); ?> :</label>
                        <div class="col-lg-8">
                            <input type="number" min="0" step="1" name="penalt_grace_days"
                                   value="<?php echo htmlspecialchars($grace_val, ENT_QUOTES, 'UTF-8'); ?>"
                                   class="form-control"
                                   placeholder="<?php echo htmlspecialchars(sprintf(lang('loanproduct_penalt_grace_days_placeholder'), $system_grace), ENT_QUOTES, 'UTF-8'); ?>"/>
                            <span class="help-block"><?php echo sprintf(lang('loanproduct_penalt_grace_days_help'), $system_grace); ?></span>
                            <?php echo form_error('penalt_grace_days'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_maxmum_time'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="maxmum_time" value="<?php echo htmlspecialchars($val('maxmum_time'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                            <?php echo form_error('maxmum_time'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-shield icon-badge"></i>
                <h4><?php echo lang('loanproduct_security'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_share'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="loan_security_share_min" value="<?php echo htmlspecialchars($val('loan_security_share_min'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                            <?php echo form_error('loan_security_share_min'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_contribution'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="loan_security_contribution_min" value="<?php echo htmlspecialchars($val('loan_security_contribution_min'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                            <?php echo form_error('loan_security_contribution_min'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_saving'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="loan_security_saving_minimum" value="<?php echo htmlspecialchars($val('loan_security_saving_minimum'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                            <?php echo form_error('loan_security_saving_minimum'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loanproduct_contribution_times'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="loanproduct_contribution_times" value="<?php echo htmlspecialchars($contribution_times, ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                            <?php echo form_error('loanproduct_contribution_times'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-book icon-badge"></i>
                <h4><?php echo lang('loanproduct_account'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-lg-2 control-label"><?php echo lang('loanproduct_account_principle'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-10">
                            <select name="loan_principle_account" class="form-control account-select">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => $selected_principle)); ?>
                            </select>
                            <?php echo form_error('loan_principle_account'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-lg-2 control-label"><?php echo lang('loanproduct_account_interest'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-10">
                            <select name="loan_interest_account" class="form-control account-select">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => $selected_interest)); ?>
                            </select>
                            <?php echo form_error('loan_interest_account'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-lg-2 control-label"><?php echo lang('loanproduct_account_penalt'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-10">
                            <select name="loan_penalt_account" class="form-control account-select">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => $selected_penalt)); ?>
                            </select>
                            <?php echo form_error('loan_penalt_account'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> <?php echo lang('tax_addbtn'); ?>
                </button>
                <a href="<?php echo $list_url; ?>" class="btn btn-default">
                    <i class="fa fa-undo"></i> <?php echo lang('cancel'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
(function(){
    function boot(){
        if (!window.jQuery || !jQuery.fn.select2) {
            setTimeout(boot, 50);
            return;
        }
        var $ = window.jQuery;

        function formatCoaOption(data) {
            if (!data.element) { return data.text; }
            var $opt = $(data.element);
            var isParent = $opt.data('is-parent') == 1 || $opt.hasClass('coa-parent-account');
            var isHeader = $opt.data('coa-header') == 1;
            if (isParent || isHeader) {
                return $('<span class="coa-bold"></span>').text(data.text);
            }
            return data.text;
        }

        $('.account-select').each(function(){
            var $el = $(this);
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                width: '100%',
                placeholder: <?php echo json_encode(lang('select_default_text')); ?>,
                allowClear: true,
                templateResult: formatCoaOption,
                templateSelection: function(data) { return data.text; }
            });
        });
    }
    boot();
})();
</script>
