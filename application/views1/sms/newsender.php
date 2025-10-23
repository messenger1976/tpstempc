<?php echo form_open_multipart(current_lang() . "/sms/create_senderid/" . $id, 'class="form-horizontal"'); ?>
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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Sender ID'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="sender" class="form-control" value="<?php echo (isset($senderinfo) ? $senderinfo->name : set_value('sender')); ?>"/>
        <?php echo form_error('sender'); ?>
    </div>
</div>



<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo 'Save'; ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>