<?php 
$form_url = is_null($id) ? current_lang() . '/finance/chart_sub_type_create' : current_lang() . '/finance/chart_sub_type_edit/' . $id;
echo form_open_multipart($form_url, 'class="form-horizontal"'); 
?>

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
?>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('chart_type'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select class="form-control" name="accounttype" required>
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php foreach ($chart_types as $key => $value) { ?>
                <option value="<?php echo $value->account; ?>" <?php echo (isset($chart_sub_type) && $chart_sub_type->accounttype == $value->account ? 'selected="selected"' : ''); ?>>
                    <?php echo $value->name . ' (' . $value->account . ')'; ?>
                </option>
            <?php } ?>
        </select>
        <?php echo form_error('accounttype'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('chart_sub_type_account'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="sub_account" value="<?php echo (isset($chart_sub_type) ? $chart_sub_type->sub_account : set_value('sub_account')); ?>" class="form-control" required/> 
        <?php echo form_error('sub_account'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('chart_sub_type_name'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="name" value="<?php echo (isset($chart_sub_type) ? $chart_sub_type->name : set_value('name')); ?>" class="form-control" required/> 
        <?php echo form_error('name'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo (is_null($id) ? lang('button_create') : lang('button_update')); ?>" type="submit"/>
        <a href="<?php echo site_url(current_lang() . '/finance/chart_sub_type_list'); ?>" class="btn btn-default"><?php echo lang('button_cancel'); ?></a>
    </div>
</div>

<?php echo form_close(); ?>

