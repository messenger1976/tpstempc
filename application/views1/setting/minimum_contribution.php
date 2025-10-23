<?php echo form_open_multipart(current_lang() . "/setting/contribution_minimum", 'class="form-horizontal"'); ?>

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
$contribution = $this->setting_model->global_contribution_info();
?>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('contribution_minimum_amount'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="amount" value="<?php echo (set_value('amount') ? set_value('amount') : $contribution->amount); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('amount'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('contribution_minimum_overdueamount'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="charge" value="<?php echo (set_value('charge') ? set_value('charge') : number_format($contribution->overdue_amount)); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('charge'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('save_info_btn'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>
