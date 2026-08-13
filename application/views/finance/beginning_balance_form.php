<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">
<style type="text/css">
.select2-container { width: 100% !important; }
.bb-form-page { margin-top: 4px; }
.bb-form-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.bb-form-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.bb-form-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.bb-form-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.bb-form-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.bb-form-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.bb-form-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.bb-form-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.bb-form-page .cbu-panel .panel-head .btn {
    border-radius: 6px;
    font-weight: 600;
}
.bb-form-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.bb-form-page .form-horizontal .form-group {
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f2f3;
}
.bb-form-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.bb-form-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.bb-form-page textarea.form-control { height: auto; min-height: 80px; }
.bb-form-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.bb-form-page .required { color: #ed5565; }
.bb-form-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.bb-form-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.bb-form-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.bb-form-page .form-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
}
.bb-form-page .form-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
.bb-form-page .form-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.bb-form-page .form-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.bb-form-page .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.bb-form-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.bb-form-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.bb-form-page .select2-container--default.select2-container--focus .select2-selection--single,
.bb-form-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}
</style>

<?php
$is_edit = isset($balance) && $balance;
$id_value = isset($id) ? $id : '';
$form_url = current_lang() . '/finance/beginning_balance_create/' . $id_value;
$list_url = site_url(current_lang() . '/finance/beginning_balance_list' . ($is_edit && !empty($balance->fiscal_year_id) ? '?fiscal_year_id=' . (int) $balance->fiscal_year_id : ''));
$fiscal_years = isset($fiscal_years) ? $fiscal_years : array();
$account_list = isset($account_list) ? $account_list : array();
$fiscal_year_value = set_value('fiscal_year_id', $is_edit ? $balance->fiscal_year_id : '');
$account_value = set_value('account', $is_edit ? $balance->account : '');
$debit_value = set_value('debit', $is_edit ? number_format($balance->debit, 2) : '0.00');
$credit_value = set_value('credit', $is_edit ? number_format($balance->credit, 2) : '0.00');
$description_value = set_value('description', $is_edit ? $balance->description : '');
?>

<?php echo form_open_multipart($form_url, 'class="form-horizontal"'); ?>

<div class="col-lg-12 bb-form-page">
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
                <i class="fa fa-<?php echo $is_edit ? 'edit' : 'plus'; ?> icon-badge"></i>
                <h4><?php echo $is_edit ? lang('beginning_balance_edit') : lang('beginning_balance_create'); ?></h4>
            </div>
            <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> <?php echo lang('button_cancel'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('fiscal_year'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <select class="form-control" name="fiscal_year_id" id="fiscal_year_id" required>
                        <option value=""><?php echo lang('select_default_text'); ?></option>
                        <?php foreach ($fiscal_years as $fy) { ?>
                            <option value="<?php echo (int) $fy->id; ?>"<?php echo ((string) $fiscal_year_value === (string) $fy->id) ? ' selected="selected"' : ''; ?>>
                                <?php echo htmlspecialchars($fy->name . ' (' . date('M d, Y', strtotime($fy->start_date)) . ' - ' . date('M d, Y', strtotime($fy->end_date)) . ')', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php echo form_error('fiscal_year_id'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('finance_account_code'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <select class="form-control" name="account" id="account" required>
                        <option value=""><?php echo lang('select_default_text'); ?></option>
                        <?php foreach ($account_list as $type_data) {
                            if (isset($type_data['data']) && count($type_data['data']) > 0) {
                                $type_info = $type_data['info'];
                                ?>
                                <optgroup label="<?php echo htmlspecialchars($type_info->name, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php foreach ($type_data['data'] as $account) { ?>
                                        <option value="<?php echo htmlspecialchars($account->account, ENT_QUOTES, 'UTF-8'); ?>"<?php echo ((string) $account_value === (string) $account->account) ? ' selected="selected"' : ''; ?>>
                                            <?php echo htmlspecialchars($account->account . ' - ' . $account->name, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php } ?>
                                </optgroup>
                            <?php }
                        } ?>
                    </select>
                    <?php echo form_error('account'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('beginning_balance_debit'); ?> :</label>
                <div class="col-lg-6">
                    <input type="text" name="debit" id="debit" value="<?php echo htmlspecialchars($debit_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" onkeyup="formatNumber(this);"/>
                    <?php echo form_error('debit'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('beginning_balance_credit'); ?> :</label>
                <div class="col-lg-6">
                    <input type="text" name="credit" id="credit" value="<?php echo htmlspecialchars($credit_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" onkeyup="formatNumber(this);"/>
                    <?php echo form_error('credit'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('description'); ?> :</label>
                <div class="col-lg-6">
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description_value, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php echo form_error('description'); ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?php echo $list_url; ?>" class="btn btn-default">
                    <i class="fa fa-undo"></i> <?php echo lang('button_cancel'); ?>
                </a>
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo $is_edit ? lang('button_update') : lang('beginning_balance_btncreate'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
function formatNumber(input) {
    var value = input.value.replace(/[^\d.]/g, '');
    var parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    input.value = value;
}

(function() {
    function boot() {
        if (!window.jQuery) {
            setTimeout(boot, 50);
            return;
        }
        jQuery(function($) {
            if ($.fn.select2) {
                $('#account').select2({
                    width: '100%',
                    placeholder: <?php echo json_encode(lang('select_default_text')); ?>
                });
            }
            $('#debit, #credit').on('blur', function() {
                var value = parseFloat($(this).val().replace(/,/g, ''));
                if (!isNaN(value)) {
                    $(this).val(value.toFixed(2));
                }
            });
        });
    }
    boot();
})();
</script>
