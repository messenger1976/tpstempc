<div style="margin-left: 20px;">
<p style="margin-left: 30px;"><?php echo lang('edit_user_subheading'); ?></p>
<div id="infoMessage"><?php echo $message;?></div>

<?php echo form_open(uri_string(), 'class="form-horizontal"');?>
<div class="form-group">
     <label class="col-lg-3 control-label"><?php echo lang('edit_user_fname_label'); ?>  : <span class="required">*</span></label> 
     <div class="col-lg-6">
     	<input type="text" name="first_name" value="<?php echo $first_name['value']; ?>"  class="form-control"/> 
        <?php echo form_error('first_name'); ?>
     </div>
</div>      
<div class="form-group">
     <label class="col-lg-3 control-label"><?php echo lang('edit_user_lname_label'); ?>  : <span class="required">*</span></label> 
     <div class="col-lg-6">
         <input type="text" name="last_name" value="<?php echo $last_name['value']; ?>"  class="form-control"/> 
        <?php echo form_error('last_name'); ?>
     </div>
</div>      
<div class="form-group">
     <label class="col-lg-3 control-label"><?php echo lang('edit_user_company_label'); ?>  : <span class="required">*</span></label> 
     <div class="col-lg-6">
         <input type="text" name="company" value="<?php echo $company['value']; ?>"  class="form-control"/> 
        <?php echo form_error('company'); ?>
     </div>
</div> 
<div class="form-group">
     <label class="col-lg-3 control-label"><?php echo lang('edit_user_phone_label'); ?>  : <span class="required">*</span></label> 
     <div class="col-lg-6">
         <input type="text" name="phone" value="<?php echo $phone['value']; ?>"  class="form-control"/> 
        <?php echo form_error('phone'); ?>
     </div>
</div> 

<!--      <p>
            <?php echo lang('edit_user_company_label', 'company');?> <br />
            <?php echo form_input($company);?>
      </p>

      <p>
            <?php echo lang('edit_user_phone_label', 'phone');?> <br />
            <?php echo form_input($phone);?>
      </p>

      <p>
            <?php echo lang('edit_user_password_label', 'password');?> <br />
            <?php echo form_input($password);?>
      </p>

      <p>
            <?php echo lang('edit_user_password_confirm_label', 'password_confirm');?><br />
            <?php echo form_input($password_confirm);?>
      </p>-->

      <?php if ($this->ion_auth->is_admin()): ?>

          <h3><?php echo lang('edit_user_groups_heading');?></h3>
          <?php foreach ($groups as $group):?>
              <label class="checkbox">
              <?php
                  $gID=$group['id'];
                  $checked = null;
                  $item = null;
                  foreach($currentGroups as $grp) {
                      if ($gID == $grp->id) {
                          $checked= ' checked="checked"';
                      break;
                      }
                  }
              ?>
              <input type="checkbox" name="groups[]" value="<?php echo $group['id'];?>"<?php echo $checked;?>>
              <?php echo htmlspecialchars($group['name'],ENT_QUOTES,'UTF-8');?>
              </label>
          <?php endforeach?>

      <?php endif ?>

      <?php echo form_hidden('id', $user->id);?>
      <?php echo form_hidden($csrf); ?>

      <p><?php echo form_submit('submit', lang('edit_user_submit_btn'));?></p>

<?php echo form_close();?>
</div>
