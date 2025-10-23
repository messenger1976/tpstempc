
<?php echo form_open_multipart(current_lang() . "/setting/taxcode_registration/".$id, 'class="form-horizontal"'); ?>

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


<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('taxcode'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="code"  <?php echo (isset($taxinfo) ? 'disabled="disabled"':''); ?> value="<?php echo (isset($taxinfo) ? $taxinfo->code : set_value('code')); ?>"  class="form-control"/> 
        <?php echo form_error('code'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('taxdescription'); ?>  : </label>
    <div class="col-lg-6">
        <textarea type="text" name="description" class="form-control"><?php echo (isset($taxinfo) ? $taxinfo->description : set_value('description')); ?> </textarea>
        <?php echo form_error('description'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('taxpercent'); ?>  :  <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="percent" value="<?php echo (isset($taxinfo) ? $taxinfo->rate : set_value('percent')); ?>"  class="form-control"/> 
        <?php echo form_error('percent'); ?>
    </div>
</div>


<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('tax_addbtn'); ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>
