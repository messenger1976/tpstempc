<style type="text/css">
.ct-form-page { margin-top: 4px; }
.ct-form-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.ct-form-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.ct-form-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.ct-form-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.ct-form-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.ct-form-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ct-form-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ct-form-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.ct-form-page .cbu-panel .panel-head .btn {
    border-radius: 6px;
    font-weight: 600;
}
.ct-form-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.ct-form-page .form-horizontal .form-group {
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f2f3;
}
.ct-form-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.ct-form-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.ct-form-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.ct-form-page .required { color: #ed5565; }
.ct-form-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.ct-form-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.ct-form-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.ct-form-page .form-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
}
.ct-form-page .form-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
.ct-form-page .form-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.ct-form-page .form-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
</style>

<?php
$is_edit = !is_null($id);
$form_url = $is_edit ? current_lang() . '/finance/chart_sub_type_edit/' . $id : current_lang() . '/finance/chart_sub_type_create';
$list_url = site_url(current_lang() . '/finance/chart_sub_type_list');
$chart_types = isset($chart_types) ? $chart_types : array();
$accounttype_value = set_value('accounttype', isset($chart_sub_type) ? $chart_sub_type->accounttype : '');
$sub_account_value = set_value('sub_account', isset($chart_sub_type) ? $chart_sub_type->sub_account : '');
$name_value = set_value('name', isset($chart_sub_type) ? $chart_sub_type->name : '');
?>

<?php echo form_open_multipart($form_url, 'class="form-horizontal"'); ?>

<div class="col-lg-12 ct-form-page">
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
                <h4><?php echo $is_edit ? lang('chart_sub_type_edit') : lang('chart_sub_type_create'); ?></h4>
            </div>
            <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> <?php echo lang('button_cancel'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('chart_type'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <select class="form-control" name="accounttype" required>
                        <option value=""><?php echo lang('select_default_text'); ?></option>
                        <?php foreach ($chart_types as $value) { ?>
                            <option value="<?php echo htmlspecialchars($value->account, ENT_QUOTES, 'UTF-8'); ?>"<?php echo ((string) $accounttype_value === (string) $value->account) ? ' selected="selected"' : ''; ?>>
                                <?php echo htmlspecialchars($value->name . ' (' . $value->account . ')', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php echo form_error('accounttype'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('chart_sub_type_account'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <input type="text" name="sub_account" value="<?php echo htmlspecialchars($sub_account_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required/>
                    <?php echo form_error('sub_account'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('chart_sub_type_name'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required/>
                    <?php echo form_error('name'); ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?php echo $list_url; ?>" class="btn btn-default">
                    <i class="fa fa-undo"></i> <?php echo lang('button_cancel'); ?>
                </a>
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo $is_edit ? lang('button_update') : lang('button_create'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
