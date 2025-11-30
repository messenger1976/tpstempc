<?php echo form_open_multipart(current_lang() . "/setting/saving_account_typecreate/".$id, 'class="form-horizontal"'); ?>

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
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_name'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="account_name" value="<?php echo (isset ($account) ? $account->name: set_value('account_name')); ?>"  class="form-control"/> 
        <?php echo form_error('account_name'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_description'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <textarea name="account_description"   class="form-control"/> <?php echo (isset ($account) ?  $account->description: set_value('account_description') ); ?> </textarea>
        <?php echo form_error('account_description'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_min_amount'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="minimum_amount" value="<?php echo (isset ($account) ? $account->min_amount: set_value('minimum_amount') ); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('minimum_amount'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_max_withdrawal'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="max_withdrawal" value="<?php echo (isset ($account) ? $account->max_withdrawal : set_value('max_withdrawal')); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('max_withdrawal'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_interest_rate'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="interest_rate" value="<?php echo (isset ($account) ? $account->interest_rate : set_value('interest_rate')); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('monthly_amount'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_min_deposit'); ?> :</label>
    <div class="col-lg-6">
        <input type="text" name="min_deposit" value="<?php echo (isset ($account) ? $account->min_deposit: set_value('min_deposit')); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('min_deposit'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('save_info_btn'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>
