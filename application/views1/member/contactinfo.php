<?php
$this->load->view('member/topmenu');
?>

<div style="margin-top: 20px;" class="col-lg-12">
    <div class="col-lg-3">
        <img src="<?php echo base_url() ?>uploads/memberphoto/<?php echo $basicinfo->photo; ?>" style="width: 150px; height: 170px; border: 1px solid #ccc;"/>
        <div style="display: block;  margin-top: 20px; font-size: 15px;">
            <?php echo lang('member_pid') ?> : <?php echo $basicinfo->PID; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_member_id') ?> : <?php echo $basicinfo->member_id; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_firstname') ?> : <?php echo $basicinfo->firstname; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_middlename') ?> : <?php echo $basicinfo->middlename; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_lastname') ?> : <?php echo $basicinfo->lastname; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_gender') ?> : <?php echo $basicinfo->gender; ?>
        </div>
    </div>

    <div class="col-lg-8">



        <?php echo form_open(current_lang() . "/member/membercontact/" . encode_id($basicinfo->id), 'class="form-horizontal"'); ?>

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


        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_contact_phone1'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-6">
                <div class="input-group"><span class="input-group-addon" style="border: 0px; padding: 0px 5px 0px 0px; margin: 0px"> <select name="pre_phone1" style="background: transparent; padding: 7px;  border:  1px solid #E5E6E7">
                            <?php
                            $select = substr($contactinfo->phone1, 0, -9);
                            foreach (mobile_code() as $key => $value) {
                                ?>
                                <option <?php echo ($select == $value->name ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                            <?php } ?>
                        </select> </span><input type="text" name="phone1" value="<?php echo substr($contactinfo->phone1, -9); ?>"  class="form-control"/> </div>
                <?php echo form_error('phone1'); ?>
            </div>
        </div>
        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_contact_phone2'); ?>  : </label>
            <div class="col-lg-6">
                <div class="input-group"><span class="input-group-addon" style="border: 0px; padding: 0px 5px 0px 0px; margin: 0px"> <select name="pre_phone2" style="background: transparent; padding: 7px;  border:  1px solid #E5E6E7">
                            <?php
                            $select1 = substr($contactinfo->phone2, 0, -9);
                            foreach (mobile_code() as $key => $value) {
                                ?>
                                <option <?php echo ($select1 == $value->name ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                            <?php } ?>
                        </select> </span><input type="text" name="phone2" value="<?php echo substr($contactinfo->phone2, -9); ?>"  class="form-control"/> </div>
                <?php echo form_error('phone2'); ?>
            </div>
        </div>
        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_contact_email'); ?>  : </label>
            <div class="col-lg-6">
                <input type="text" name="email" value="<?php echo $contactinfo->email; ?>"  class="form-control"/> 
                <?php echo form_error('email'); ?>
            </div>
        </div>
        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_contact_box'); ?>  : </label>
            <div class="col-lg-6">
                <input type="text" name="box" value="<?php echo $contactinfo->postaladdress; ?>"  class="form-control"/> 
                <?php echo form_error('box'); ?>
            </div>
        </div>

        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_contact_physical'); ?>  : </label>
            <div class="col-lg-6">
                <input type="text" name="physical" value="<?php echo $contactinfo->physicaladdress; ?>"  class="form-control"/> 
                <?php echo form_error('physical'); ?>
            </div>
        </div>


        <div class="form-group">
            <label class="col-lg-3 control-label">&nbsp;</label>
            <div class="col-lg-6">
                <input class="btn btn-primary" value="<?php echo lang('member_contactbtn'); ?>" type="submit"/>
            </div>
        </div>

        <?php echo form_close(); ?>



    </div>
</div>