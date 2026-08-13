<?php
$is_edit = !empty($id);
$list_url = site_url(current_lang() . '/customer/customerlist');
$form_url = current_lang() . '/customer/customer_register/' . ($is_edit ? $id : '');
$page_title = $is_edit ? lang('customer_edit') : lang('customer_register');

$name_value = isset($customerinfo) ? $customerinfo->name : set_value('name');
$identity_value = isset($customerinfo) ? $customerinfo->customerid : set_value('identity');
$address_value = isset($customerinfo) ? $customerinfo->address : set_value('address');
$email_value = isset($customerinfo) ? $customerinfo->email : set_value('email');
$fax_value = isset($customerinfo) ? $customerinfo->fax : set_value('fax');
$additional_value = isset($customerinfo) ? $customerinfo->additional : set_value('additional');
$pre_phone = isset($customerinfo) ? substr($customerinfo->phone, 0, -9) : set_value('pre_phone1');
$phone_value = isset($customerinfo) ? substr($customerinfo->phone, -9) : set_value('phone');
?>

<style type="text/css">
.cust-form-page { margin-top: 4px; }
.cust-form-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.cust-form-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.cust-form-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.cust-form-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.cust-form-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.cust-form-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cust-form-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cust-form-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.cust-form-page .cbu-panel .panel-head .btn {
    border-radius: 6px;
    font-weight: 600;
}
.cust-form-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.cust-form-page .form-horizontal .form-group {
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f2f3;
}
.cust-form-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.cust-form-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.cust-form-page textarea.form-control {
    height: auto;
    min-height: 80px;
}
.cust-form-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.cust-form-page .form-control[disabled] {
    background: #f8fafb;
    font-weight: 700;
    color: #2f4050;
}
.cust-form-page .required { color: #ed5565; }
.cust-form-page .input-group-addon {
    background: #f8fafb;
    border-color: #e5e6e7;
    border-radius: 6px 0 0 6px;
    padding: 0;
}
.cust-form-page .input-group-addon select {
    background: transparent;
    border: 0;
    height: 34px;
    padding: 0 10px;
    color: #2f4050;
    font-weight: 600;
    outline: none;
}
.cust-form-page .input-group .form-control {
    border-radius: 0 6px 6px 0;
}
.cust-form-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.cust-form-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.cust-form-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.cust-form-page .form-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
}
.cust-form-page .form-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
.cust-form-page .form-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.cust-form-page .form-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
</style>

<?php echo form_open_multipart($form_url, 'class="form-horizontal"'); ?>

<div class="col-lg-12 cust-form-page">
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
                <i class="fa fa-<?php echo $is_edit ? 'edit' : 'user-plus'; ?> icon-badge"></i>
                <h4><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h4>
            </div>
            <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('customer_name'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required/>
                    <?php echo form_error('name'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('customer_id'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <input type="text" name="identity" <?php echo $is_edit ? 'disabled="disabled"' : ''; ?> value="<?php echo htmlspecialchars($identity_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" <?php echo $is_edit ? '' : 'required'; ?>/>
                    <?php echo form_error('identity'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('customer_address'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <textarea name="address" class="form-control" required><?php echo htmlspecialchars(trim((string) $address_value), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php echo form_error('address'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('customer_email'); ?> :</label>
                <div class="col-lg-6">
                    <input type="text" name="email" value="<?php echo htmlspecialchars($email_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                    <?php echo form_error('email'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('customer_phone'); ?> : <span class="required">*</span></label>
                <div class="col-lg-6">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <select name="pre_phone1">
                                <?php foreach (mobile_code() as $value) { ?>
                                    <option <?php echo ((string) $pre_phone === (string) $value->name) ? 'selected="selected"' : ''; ?> value="<?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </span>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required/>
                    </div>
                    <?php echo form_error('phone'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('customer_fax'); ?> :</label>
                <div class="col-lg-6">
                    <input type="text" name="fax" value="<?php echo htmlspecialchars($fax_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                    <?php echo form_error('fax'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('customer_additional'); ?> :</label>
                <div class="col-lg-6">
                    <textarea name="additional" class="form-control"><?php echo htmlspecialchars(trim((string) $additional_value), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php echo form_error('additional'); ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?php echo $list_url; ?>" class="btn btn-default">
                    <i class="fa fa-undo"></i> <?php echo lang('cancel'); ?>
                </a>
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo lang('customer_addbtn'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
