<?php echo form_open_multipart(current_lang() . "/setting/companyinfo_edit/".$PIN, 'class="form-horizontal"'); ?>
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


<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('clientaccount_label_name'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="companyname" value="<?php echo $account->name; ?>"  class="form-control"> 
        <?php echo form_error('companyname'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('clientaccount_label_postal_address'); ?>  : </label>
    <div class="col-lg-6">
        <input type="text" name="postaladdress" value="<?php echo $account->box; ?>"  class="form-control"> 
        <?php echo form_error('postaladdress'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('clientaccount_label_physical_address'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <input type="text" name="physicaladdress" value="<?php echo $account->address; ?>"   class="form-control"> 
        <?php echo form_error('physicaladdress'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('clientaccount_label_phone'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <div class="input-group"><span class="input-group-addon" style="border: 0px; padding: 0px 5px 0px 0px; margin: 0px"> <select name="pre_phone" style="background: transparent; padding: 7px;  border:  1px solid #E5E6E7">
                    <?php
                    $select = substr($account->mobile, 0, -9);
                    foreach (mobile_code() as $key => $value) {
                        ?>
                        <option <?php echo ($select == $value->name ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                    <?php } ?>
                </select> </span> <input type="text" name="phone" value="<?php echo substr($account->mobile, -9); ?>"  class="form-control"/> </div>

        <?php echo form_error('phone'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('clientaccount_label_fax'); ?>  :</label>
    <div class="col-lg-6">
        <input type="text" name="fax" value="<?php echo $account->fax; ?>"  class="form-control"> 
        <?php echo form_error('fax'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('clientaccount_label_email'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <input type="text" name="email" value="<?php echo $account->email; ?>"  class="form-control"> 
        <?php echo form_error('email'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('clientaccount_label_logo'); ?>  : </label>
    <div class="col-lg-6">
        <input type="file" name="file"  class="form-control"> 
        <?php if (isset($logo_error)) {
            echo '<div class="error_message">' . $logo_error . '</div>';
        } ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('clientaccount_label_btnedit'); ?>" type="submit"/>
    </div>
</div>
<?php echo form_close(); ?>