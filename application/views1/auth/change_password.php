<?php echo form_open(current_lang() . "/auth/change_password", 'class="form-horizontal"'); ?>

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
     <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('change_password_old_password_label', 'old_password');?> : <span class="required">*</span></label>
         <div class="col-lg-6">
            <?php echo form_input($old_password);?>
             <?php echo form_error('old'); ?>
         </div>
     </div>

           <div class="form-group"><label class="col-lg-3 control-label"><?php echo sprintf(lang('change_password_new_password_label'), $min_password_length);?>: <span class="required">*</span></label>
            <div class="col-lg-6">
            <?php echo form_input($new_password);?>
                 <?php echo form_error('new'); ?>
       </div>
     </div>

           <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('change_password_new_password_confirm_label', 'new_password_confirm');?> : <span class="required">*</span></label>
          <div class="col-lg-6">
            <?php echo form_input($new_password_confirm);?>
               <?php echo form_error('new_confirm'); ?>
      </div>
     </div>

      <?php echo form_input($user_id);?>
<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
      <?php echo form_submit('submit', lang('change_password_submit_btn'),'class="btn btn-primary"');?>
           </div>
</div>

<?php echo form_close();?>
