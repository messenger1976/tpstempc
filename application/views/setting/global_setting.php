<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php
$default_list = isset($default_list) ? $default_list : array();
$account_list = isset($account_list) ? $account_list : array();
?>

<style type="text/css">
.select2-container{width:100%!important;}
.select2-results__option .chart-type-name {
    font-weight: 700;
    color: #2f4050;
}
.select2-results__option .account-indent {
    display: inline-block;
    margin-left: 0;
    color: #1ab394;
}

.gs-page { margin-top: 4px; }
.gs-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.gs-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.gs-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.gs-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.gs-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.gs-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.gs-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.gs-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.gs-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.gs-page .form-horizontal .form-group {
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f2f3;
}
.gs-page .form-horizontal .form-group:last-of-type {
    border-bottom: 0;
    margin-bottom: 8px;
    padding-bottom: 0;
}
.gs-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
    text-transform: capitalize;
}
.gs-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.gs-page textarea.form-control {
    height: auto;
    min-height: 80px;
}
.gs-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.gs-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.gs-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.gs-page .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.gs-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.gs-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.gs-page .select2-container--default.select2-container--focus .select2-selection--single,
.gs-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}
.gs-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.gs-page .setting-key {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #aab2bd;
    margin-top: 4px;
    text-transform: none;
    letter-spacing: .02em;
}
.gs-page .empty-state {
    text-align: center;
    padding: 36px 16px;
    color: #888;
}
</style>

<?php echo form_open_multipart(current_lang() . '/setting/global_setting/', 'class="form-horizontal" id="globalSettingForm"'); ?>

<div class="col-lg-12 gs-page">
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
                <i class="fa fa-cog icon-badge"></i>
                <h4><?php echo lang('global_setting'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <?php if (!empty($default_list)) { ?>
                <?php foreach ($default_list as $value) {
                    $label = str_replace('_', ' ', $value->key);
                    $field_name = 'field_' . $value->id;
                    ?>
                    <div class="form-group">
                        <label class="col-lg-4 control-label">
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            <span class="setting-key"><?php echo htmlspecialchars($value->key, ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                        <div class="col-lg-7">
                            <?php if ($value->key == 'RETAINED_EARNINGS_ACCOUNT') { ?>
                                <select name="<?php echo htmlspecialchars($field_name, ENT_QUOTES, 'UTF-8'); ?>"
                                        id="retained_earnings_account"
                                        class="form-control select2-account">
                                    <option value=""><?php echo lang('select_default_text'); ?></option>
                                    <?php
                                    $selected = $value->text;
                                    if (!empty($account_list)) {
                                        foreach ($account_list as $type_key => $type_data) {
                                            $optgroup_label = isset($type_data['info']->name) ? $type_data['info']->name : $type_key;
                                            ?>
                                            <optgroup label="<?php echo htmlspecialchars($optgroup_label, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php foreach ($type_data['data'] as $acc) {
                                                    $level = isset($acc->display_level) ? (int) $acc->display_level : 0;
                                                    $chart_type_name = isset($type_data['info']->name) ? strtoupper($type_data['info']->name) : '';
                                                    ?>
                                                    <option
                                                        <?php echo ((string) $acc->account === (string) $selected) ? 'selected="selected"' : ''; ?>
                                                        value="<?php echo htmlspecialchars($acc->account, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-chart-type="<?php echo htmlspecialchars($chart_type_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-account-name="<?php echo htmlspecialchars($acc->name, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-level="<?php echo $level; ?>">
                                                        <?php echo htmlspecialchars($acc->account . ' - ' . $chart_type_name . ' - ' . $acc->name, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php } ?>
                                            </optgroup>
                                        <?php }
                                    } ?>
                                </select>
                            <?php } else if ((int) $value->is_number === 0) { ?>
                                <textarea name="<?php echo htmlspecialchars($field_name, ENT_QUOTES, 'UTF-8'); ?>"
                                          class="form-control"
                                          rows="3"><?php echo htmlspecialchars($value->text, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <?php } else { ?>
                                <input type="text"
                                       name="<?php echo htmlspecialchars($field_name, ENT_QUOTES, 'UTF-8'); ?>"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($value->text, ENT_QUOTES, 'UTF-8'); ?>"/>
                            <?php } ?>
                            <?php echo form_error($field_name); ?>
                        </div>
                    </div>
                <?php } ?>

                <input type="hidden" value="1" name="SAVEDATA"/>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?php echo lang('tax_addbtn'); ?>
                    </button>
                </div>
            <?php } else { ?>
                <div class="empty-state">
                    <i class="fa fa-inbox fa-3x" style="margin-bottom: 12px; opacity: 0.35;"></i>
                    <p>No global settings found for this cooperative.</p>
                </div>
            <?php } ?>
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
        if (!$('#retained_earnings_account').length) {
            return;
        }
        $('#retained_earnings_account').select2({
            width: '100%',
            allowClear: true,
            placeholder: <?php echo json_encode(lang('select_default_text')); ?>,
            templateResult: function(data) {
                if (!data.id) {
                    return data.text;
                }
                var $result = $('<span></span>');
                var level = $(data.element).data('level') || 0;
                var chartType = $(data.element).data('chart-type') || '';
                var accountName = $(data.element).data('account-name') || '';
                var accountNumber = data.id;

                if (level > 0) {
                    var indent = '';
                    for (var i = 0; i < level; i++) {
                        indent += '&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    $result.append('<span class="account-indent">' + indent + '└ </span>');
                }

                $result.append('<span>' + accountNumber + ' - </span>');
                $result.append('<span class="chart-type-name">' + chartType + '</span>');
                $result.append('<span> - ' + accountName + '</span>');
                return $result;
            },
            templateSelection: function(data) {
                if (!data.id) {
                    return data.text;
                }
                var chartType = $(data.element).data('chart-type') || '';
                var accountName = $(data.element).data('account-name') || '';
                return data.id + ' - ' + chartType + ' - ' + accountName;
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        });
    }
    boot();
})();
</script>
