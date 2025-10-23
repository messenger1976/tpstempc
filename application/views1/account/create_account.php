
<?php 
echo form_open_multipart(current_lang().'/account/client_account_create/' . $id, 'role="form" class="form-horizontal"'); ?>
<?php
if (isset($message)) {
    echo '<div style="margin-bottom:20px;" class="btn btn-success btn-block">' . $message . '</div><br/>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div style="margin-bottom:20px;" class="btn btn-success btn-block">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning)) {
    echo '<div style="margin-bottom:20px;" class="btn btn-danger btn-block">' . $warning . '</div>';
}
?>


<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('client_account_name'); ?> <span class="required">*</span> </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" value="<?php echo (isset($accountinfo) ? $accountinfo->name:set_value('name')); ?>"  name="name"/>
        <?php echo form_error('name'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('client_account_address'); ?> <span class="required">*</span> </label>
    <div class="col-lg-8">
        <textarea name="address" class="form-control"><?php echo (isset($accountinfo) ? $accountinfo->address : set_value('address')) ?></textarea>
        <?php echo form_error('address'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('client_account_landline'); ?> </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" value="<?php echo (isset($accountinfo) ? $accountinfo->landline : set_value('landline')) ?>"  name="landline" />

        <?php echo form_error('landline'); ?>
    </div>
</div>


<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('client_account_mobile'); ?> : <span class="required">*</span> </label>
    <div class="col-lg-8">
        <select style="width: 15%; display: inline-block" name="code" class="form-control">
            <?php
            $wholephone = (isset($accountinfo) ? $accountinfo->mobile : (set_value('code') . str_replace(' ', '', set_value('mobile'))));
            $code = substr($wholephone, 0, (strlen($wholephone) - 9));
            $phone = substr($wholephone, -9);
            foreach (mobile_code() as $key => $value) {
                ?>
                <option <?php echo ($value->name == $code ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name ?></option>
            <?php }
            ?>
        </select> <input type="text" value="<?php echo (isset($accountinfo) ? $phone : set_value('mobile')); ?>" class="form-control"  style="width: 84%; display: inline-block" name="mobile" placeholder="712765538"/>

        <?php echo form_error('mobile'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('create_user_email_label'); ?> </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" value="<?php echo (isset($accountinfo) ? $accountinfo->email : set_value('email')) ?>"  name="email"/>
        <?php echo form_error('email'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('client_account_website'); ?> </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" value="<?php echo (isset($accountinfo) ? $accountinfo->website : set_value('website')) ?>"  name="website"/>
        <?php echo form_error('website'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('client_account_currency'); ?> <span class="required">*</span>  </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" value="<?php echo (isset($accountinfo) ? $accountinfo->currency : 'TZS') ?>"  name="currency"/>
        <?php echo form_error('currency'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('client_account_sms_token'); ?>  </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" value="<?php echo (isset($accountinfo) ? $accountinfo->sms_token : set_value('sms_token')) ?>"  name="sms_token"/>
        <?php echo form_error('sms_token'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-2 control-label"><?php echo lang('client_account_logo'); ?>  :  </label>
    <div class="col-lg-8">
        <input type="file" name="file"  class="form-control"> 
        <?php
        if (isset($logo_error)) {
            echo '<div class="required">' . $logo_error . '</div>';
        }
        ?>
    </div>
</div>

<?php if (is_null($id)) { ?>
    <h3 style="color: brown; margin-left: 20px; padding-top: 10px; border-bottom: 1px solid #ccc;"><?php echo lang('client_account_login_info'); ?></h3>

    <div class="form-group">
        <label class="col-lg-2 control-label"><?php echo lang('create_user_fname_label'); ?> <span class="required">*</span> </label>
        <div class="col-lg-8">
            <input type="text" class="form-control" value="<?php echo (isset($accountinfo) ? $accountinfo->firstname : set_value('fname')) ?>"  name="fname"/>
    <?php echo form_error('fname'); ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-2 control-label"><?php echo lang('create_user_lname_label'); ?> <span class="required">*</span> </label>
        <div class="col-lg-8">
            <input type="text" class="form-control" value="<?php echo (isset($accountinfo) ? $accountinfo->lastname : set_value('lname')) ?>"  name="lname" />
    <?php echo form_error('lname'); ?>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-2 control-label"><?php echo lang('create_user_email_label'); ?> <span class="required">*</span> </label>
        <div class="col-lg-8">
            <input type="text" class="form-control" value="<?php echo (isset($accountinfo) ? $accountinfo->admin_email : set_value('admin_email')) ?>"  name="admin_email" />
    <?php echo form_error('admin_email'); ?>
        </div>
    </div>
<?php } ?>
<div class="form-group">
    <label class="col-lg-2 control-label">&nbsp;</label>
    <div class="col-lg-8">
        <input type="submit" class="btn btn-primary" value="<?php echo (is_null($id) ? lang('create_account') : lang('edit_account')) ?>" name="account"/>
    </div>
</div>

<?php echo form_close();


?>
                


