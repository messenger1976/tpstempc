<?php echo form_open(current_lang() . "/saving/edit_saving_account/" . $id, 'class="form-horizontal"'); ?>

<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}

$account_info = isset($account_info) ? $account_info : null;
?>

<div class="col-lg-12">
    <div class="col-lg-7">
        <div class="form-group">
            <label class="col-lg-4 control-label"><?php echo lang('account_number'); ?>: <span class="required">*</span></label>
            <div class="col-lg-7">
                <input type="text" name="account" value="<?php echo set_value('account', $account_info ? $account_info->account : ''); ?>" class="form-control" required/>
                <?php echo form_error('account'); ?>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-lg-4 control-label"><?php echo lang('member_member_id'); ?>: <span class="required">*</span></label>
            <div class="col-lg-7">
                <input type="text" name="member_id" value="<?php echo set_value('member_id', $account_info ? $account_info->member_id : ''); ?>" class="form-control" required/>
                <?php echo form_error('member_id'); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label"><?php echo lang('account_type'); ?>: <span class="required">*</span></label>
            <div class="col-lg-7">
                <select name="account_cat" class="form-control" required>
                    <option value=""><?php echo lang('select_default_text'); ?></option>
                    <?php
                    $selected = set_value('account_cat', $account_info ? $account_info->account_cat : '');
                    foreach ($account_list as $key => $value) {
                        ?>
                        <option <?php echo ($value->account == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->account; ?>"><?php echo $value->account . ' - ' . $value->name; ?></option>
                    <?php } ?>
                </select>
                <?php echo form_error('account_cat'); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label"><?php echo lang('balance'); ?>: <span class="required">*</span></label>
            <div class="col-lg-7">
                <input type="text" name="balance" value="<?php echo set_value('balance', $account_info ? number_format($account_info->balance, 2, '.', '') : '0.00'); ?>" class="form-control amountformat" required/>
                <?php echo form_error('balance'); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label"><?php echo lang('virtual_balance'); ?>: </label>
            <div class="col-lg-7">
                <input type="text" name="virtual_balance" value="<?php echo set_value('virtual_balance', $account_info ? number_format($account_info->virtual_balance, 2, '.', '') : '0.00'); ?>" class="form-control amountformat"/>
                <?php echo form_error('virtual_balance'); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-3 control-label">&nbsp;</label>
            <div class="col-lg-6">
                <input class="btn btn-primary" value="<?php echo lang('button_update'); ?>" type="submit"/>
                <?php echo anchor(current_lang() . '/saving/saving_account_list', lang('button_cancel'), 'class="btn btn-default"'); ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <?php if ($account_info) { ?>
            <div style="border:1px solid #ccc; padding:15px; font-size:14px;">
                <h4><?php echo lang('account_information'); ?></h4>
                <div style="border-bottom:1px dashed #ccc; padding:5px 0;">
                    <strong><?php echo lang('member_name'); ?>:</strong> 
                    <?php echo htmlspecialchars(trim($account_info->lastname . ', ' . $account_info->firstname . ' ' . $account_info->middlename), ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div style="border-bottom:1px dashed #ccc; padding:5px 0;">
                    <strong><?php echo lang('account_type'); ?>:</strong> 
                    <?php echo htmlspecialchars($account_info->account_type_name ? $account_info->account_type_name : '-', ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div style="border-bottom:1px dashed #ccc; padding:5px 0;">
                    <strong><?php echo lang('current_balance'); ?>:</strong> 
                    <?php echo number_format($account_info->balance, 2, '.', ','); ?>
                </div>
                <div style="border-bottom:1px dashed #ccc; padding:5px 0;">
                    <strong><?php echo lang('virtual_balance'); ?>:</strong> 
                    <?php echo number_format($account_info->virtual_balance, 2, '.', ','); ?>
                </div>
                <?php if ($account_info->createdon) { ?>
                <div style="border-bottom:1px dashed #ccc; padding:5px 0;">
                    <strong><?php echo lang('created_date'); ?>:</strong> 
                    <?php echo date('Y-m-d', strtotime($account_info->createdon)); ?>
                </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<?php echo form_close(); ?>

