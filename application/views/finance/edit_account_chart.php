<?php echo form_open_multipart(current_lang() . "/finance/finance_account_edit/".$id, 'class="form-horizontal"'); ?>

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

if(isset($parent_info)){ ?>
<div class="form-group"><label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
    <?php echo '<br/><strong>'.$parent_info->account.' : '.$parent_info->name.'</strong>'; ?>
    </div>
</div>
<?php }
?>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('finance_account_type'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select class="form-control" name="account_type">
            <option value=""><?php echo lang('select_default_text') ?></option>
            <?php
            
            $selected = $accountinfo->account_type. '_' . $accountinfo->sub_account_type;
         foreach ($account_typelist as $key => $value) { ?>
                <optgroup label="<?php echo $value->name; ?>">
                    <?php
                    $sub_account = $this->finance_model->account_type_sub(null, $value->account)->result();
                    foreach ($sub_account as $xk => $xv) {
                        ?>
                        <option <?php echo ($selected == $xv->accounttype . '_' . $xv->sub_account ? 'selected="selected"' : ''); ?>  value="<?php echo $xv->accounttype . '_' . $xv->sub_account; ?>"><?php echo $xv->name; ?></option> 
                    <?php }
                    ?>
                </optgroup>
            <?php } ?>
            
        </select>
        <?php echo form_error('account_type'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('finance_account_name'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="accountname" value="<?php echo $accountinfo->name; ?>"  class="form-control"/> 
        <?php echo form_error('accountname'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('finance_account_description'); ?>  : </label>
    <div class="col-lg-6">
        <textarea name="accountdescription" class="form-control"><?php echo $accountinfo->description; ?></textarea> 
        <?php echo form_error('accountdescription'); ?>
    </div>
</div>
<?php if(is_null($parent)){ ?>
<div class="form-group" style="display: none;"><label class="col-lg-3 control-label"><?php echo lang('finance_account_is_header'); ?>  : </label>
    <div class="col-lg-6">
        <input type="radio" name="is_header" <?php echo ($accountinfo->is_header == 1 ? 'checked="checked"':''); ?> value="1" class="radio-inline"/> Yes
        <input type="radio" name="is_header" <?php echo ($accountinfo->is_header == 0 ? 'checked="checked"':''); ?> value="0" class="radio-inline"/> No
        <?php echo form_error('is_header'); ?>
    </div>
</div>
<?php } ?>
<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('finance_account_btncreate'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>
