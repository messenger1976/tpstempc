<div style="margin-left: 20px;">
<p><?php echo sprintf(lang('deactivate_subheading'), $user->username);?></p>

<?php echo form_open("auth/deactivate/".encode_id($user->id));?>

  <p>
  	<?php echo lang('deactivate_confirm_y_label', 'confirm');?>
    <input type="radio" name="confirm" value="yes" checked="checked" />
    <?php echo lang('deactivate_confirm_n_label', 'confirm');?>
    <input type="radio" name="confirm" value="no" />
  </p>

  <?php echo form_hidden($csrf); ?>
  <?php echo form_hidden(array('id'=>$user->id)); ?>

  <p><?php echo form_submit('submit', lang('deactivate_submit_btn'),'class="btn btn-primary"');?></p>

<?php echo form_close();?>
</div>