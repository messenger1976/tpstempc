<?php echo form_open_multipart(current_lang() . "/member/member_group_edit/".$id, 'class="form-horizontal"'); ?>

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
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_group_name'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="gpname" value="<?php echo $grouplist->name; ?>"  class="form-control"/> 
        <?php echo form_error('gpname'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_group_description'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <textarea name="gpdescription" class="form-control"><?php echo $grouplist->description; ?></textarea>
        <?php echo form_error('gpdescription'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('member_group_btn'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>
