<?php echo form_open_multipart(current_lang() . "/setting/share_setup", 'class="form-horizontal"'); ?>

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
$share = $this->setting_model->share_setting_info();
?>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('share_current_value'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="share_value" value="<?php echo (set_value('share_value') ? set_value('share_value') : number_format($share->amount)); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('share_value'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('share_minimum'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
         <input type="text" name="share_minimum" value="<?php echo (set_value('share_minimum') ? set_value('share_minimum') : $share->min_share); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('share_minimum'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('share_maximum'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
         <input type="text" name="share_maximum" value="<?php echo (set_value('share_maximum') ? set_value('share_maximum'): $share->max_share); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('share_maximum'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('save_info_btn'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>
