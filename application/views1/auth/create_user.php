<?php echo form_open(current_lang() . "/auth/create_user", 'class="form-horizontal"'); ?>

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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('create_user_fname_label'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="first_name" value="<?php echo set_value('first_name'); ?>"  class="form-control"/> 
        <?php echo form_error('first_name'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('create_user_lname_label'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="last_name" value="<?php echo set_value('last_name'); ?>"  class="form-control"/> 
        <?php echo form_error('last_name'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('create_user_email_label'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <input type="text" name="email" value="<?php echo set_value('email'); ?>"  class="form-control"/> 
        <?php echo form_error('email'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('create_user_phone_label'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <div class="input-group"><span class="input-group-addon" style="border: 0px; padding: 0px 5px 0px 0px; margin: 0px"> <select name="pre_phone" style="background: transparent; padding: 7px;  border:  1px solid #E5E6E7">
                    <?php
                    $select = set_value('pre_phone');
                    foreach (mobile_code() as $key => $value) {
                        ?>
                        <option <?php echo ($select == $value->name ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                    <?php } ?>
                </select> </span> <input type="text" name="phone" value="<?php echo set_value('phone'); ?>"  class="form-control"/> </div>

        <?php echo form_error('phone'); ?>
    </div>
</div>


<div style="height: 20px;"></div>
<div class="form-group" style="border-bottom: 1px solid #ccc; width: 90%;  margin: auto; font-size: 15px;">
    <label class="col-lg-3 control-label"><i class="fa fa-lock"></i> &nbsp; &nbsp; <?php echo lang('account_creation_dividertittle'); ?> </label>
</div>
<div style="height: 20px;"></div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('edit_group_name_label'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <select name="groupname" class="form-control"> 
            <option value=""> -- <?php echo lang('select_default_text') ?> --</option>
            <?php
            $selected = set_value('groupname');
            foreach ($grouplist as $key => $value) { ?>
            <option <?php echo ($selected == $value->id ? 'selected="selected"':''); ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
            <?php } ?>
        </select>
        <?php echo form_error('groupname'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('create_user_username_label'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <input type="text" name="username" value="<?php echo set_value('username'); ?>"  class="form-control"/> 
        <?php echo form_error('username'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('create_user_password_label'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <input type="password" name="password" value="<?php echo set_value('password'); ?>"  class="form-control"/> 
        <?php echo form_error('password'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('create_user_password_confirm_label'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <input type="password" name="password_confirm" value="<?php echo set_value('password_confirm'); ?>"  class="form-control"/> 
        <?php echo form_error('password_confirm'); ?>
    </div>
</div>



<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('create_user_submit_btn'); ?>" type="submit"/>
    </div>
</div>
<?php echo form_close(); ?>
