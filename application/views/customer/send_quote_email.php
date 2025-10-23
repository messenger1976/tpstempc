
<?php echo form_open_multipart(current_lang() . "/customer/sendquote/" . $quoteid, 'class="form-horizontal"'); ?>

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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('email_to'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="recipient" value="<?php echo (isset($customerinfo) ? $customerinfo->email : set_value('recipient')); ?>"  class="form-control"/> 
        <?php echo form_error('recipient'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('email_cc'); ?>  : </label>
    <div class="col-lg-6">
        <input type="text" name="copy" value="<?php echo set_value('cc'); ?>"  class="form-control"/> 
        <?php echo form_error('copy'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('email_title'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <input type="text" name="subject" value="<?php echo set_value('subject'); ?>"  class="form-control"/> 
        <?php echo form_error('subject'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label">&nbsp; </label>
    <div class="col-lg-6">
        <?php echo anchor(base_url().'uploads/'.$filename,  lang('email_attachment')); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('email_body'); ?>  : <span class="required">*</span>  </label>
    <div class="col-lg-8">
        <textarea name="body" id="body" class="form-control"><?php echo set_value('body'); ?> </textarea>
        <?php echo form_error('body'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('send_mail'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>
<script type="text/javascript" src="<?php echo base_url() . 'ckeditor/ckeditor.js' ?>"></script>
<script>
    window.onload = function() {
        CKEDITOR.replace('body');
    };

</script>
