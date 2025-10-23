<?php echo form_open_multipart(current_lang() . "/sms/newcontact/".$id, 'class="form-horizontal"'); ?>
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


<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Group'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="group" class="form-control">
            <?php 
             $select = isset($smscontact) ? $smscontact->group : set_value('group');
            foreach ($grouplist as $key => $value) { 
                if($value->id > 3){
                ?>
                <option <?php echo ($select == $value->id ? 'selected="selected"' : ''); ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
            <?php } }
            ?>
        </select>   
            <?php echo form_error('group'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Name'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="name" class="form-control" value="<?php echo (isset($smscontact) ? $smscontact->name : set_value('name')); ?>"/>
        <?php echo form_error('name'); ?>
    </div>
</div>
  <div class="form-group"><label class="col-lg-3 control-label">Mobile  : <span class="required">*</span></label>
            <div class="col-lg-6">
                <div class="input-group"><span class="input-group-addon" style="border: 0px; padding: 0px 5px 0px 0px; margin: 0px"> <select name="pre_phone1" style="background: transparent; padding: 7px;  border:  1px solid #E5E6E7">
                            <?php
                            $select = substr((isset($smscontact) ? $smscontact->mobile : set_value('mobile')), 0, -9);
                            foreach (mobile_code() as $key => $value) {
                                ?>
                                <option <?php echo ($select == $value->name ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                            <?php } ?>
                        </select> </span><input type="text" name="mobile" value="<?php echo substr((isset($smscontact) ? $smscontact->mobile : set_value('mobile')), -9); ?>"  class="form-control"/> </div>
                <?php echo form_error('mobile'); ?>
            </div>
        </div>



<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo 'Save'; ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>