<?php echo form_open(current_lang() . "/auth/create_group", 'class="form-horizontal"'); ?>

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

<p style="margin-left: 30px;"><?php echo lang('create_group_subheading'); ?></p>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('create_group_name_label'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="group_name"  class="form-control"> 
        <?php echo form_error('group_name'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('create_group_desc_label'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="description"  class="form-control"> 
        <?php echo form_error('description'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('create_group_submit_btn'); ?>" type="submit"/>
    </div>
</div>
<?php echo form_close(); ?>