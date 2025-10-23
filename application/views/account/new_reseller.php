
<?php echo form_open('account/create_resseller_account/' . $id, 'role="form" class="form-horizontal"'); ?>
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
    <label class="col-lg-2 control-label"><?php echo lang('create_user_fname_label'); ?> <span class="required">*</span> </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" value="<?php echo (isset($resellerinfo) ? $resellerinfo->firstname : set_value('fname')) ?>"  name="fname" placeholder="First Name"/>
        <?php echo form_error('fname'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('create_user_lname_label'); ?> <span class="required">*</span> </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" value="<?php echo (isset($resellerinfo) ? $resellerinfo->lastname : set_value('lname')) ?>"  name="lname" placeholder="Last Name"/>

        <?php echo form_error('lname'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('create_user_email_label'); ?> <span class="required">*</span> </label>
    <div class="col-lg-8">
        <input type="text"   <?php echo (!is_null($id) ? 'disabled="disabled"' : ''); ?> class="form-control" value="<?php echo (isset($resellerinfo) ? $resellerinfo->email : set_value('email')) ?>"  name="email" placeholder="Email"/>
        <?php echo form_error('email'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('mobile'); ?> : <span class="required">*</span> </label>
    <div class="col-lg-8">
        <select style="width: 15%; display: inline-block" name="code" class="form-control">
            <?php
            $wholephone = (isset($resellerinfo) ? $resellerinfo->mobile : (set_value('code') . str_replace(' ', '', set_value('phone'))));
            $code = substr($wholephone, 0, (strlen($wholephone) - 9));
            $phone = substr($wholephone, -9);
            foreach (mobile_code() as $key => $value) {
                ?>
                <option <?php echo ($value->name == $code ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name ?></option>
            <?php }
            ?>
        </select> <input type="text" value="<?php echo (isset($resellerinfo) ? $phone : set_value('phone')); ?>" class="form-control"  style="width: 84%; display: inline-block" name="phone" placeholder="712765538"/>

<?php echo form_error('phone'); ?>
    </div>
</div>


<?php
if (is_super_user()) {
    $super = (isset($resellerinfo) ? $resellerinfo->is_super : set_value('reseller'));
    ?>
    <div class="form-group">
        <label class="col-lg-2 control-label"><?php echo lang('is_super_user'); ?> <span class="required">*</span> </label>
        <div class="col-lg-8">
            <select name="reseller" class="form-control">
                <option <?php echo ($super == 0 ? 'selected="selected"' : ''); ?> value="0">No</option>
                <option <?php echo ($super == 1 ? 'selected="selected"' : ''); ?> value="1">Yes</option>
            </select>
        </div>

    </div>
<?php } ?>
<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('create_user_company_label'); ?>  </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" value="<?php echo (isset($resellerinfo) ? $resellerinfo->company : set_value('company')) ?>"  name="company" placeholder="Company"/>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-2 control-label"><?php echo lang('address'); ?> : </label>
    <div class="col-lg-8">
        <input type="text" class="form-control" name="address" value="<?php echo (isset($resellerinfo) ? $resellerinfo->address : set_value('address')) ?>" placeholder="Postal, Street"/>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-2 control-label">&nbsp;</label>
    <div class="col-lg-8">
        <input type="submit" class="btn btn-primary" value="<?php echo (is_null($id) ? lang('create_account') : lang('edit_account')) ?>" name="account"/>
    </div>
</div>

<?php echo form_close(); ?>
                


