<?php
$this->load->view('member/topmenu');
?>

<div style="margin-top: 20px;" class="col-lg-12">
    <div class="col-lg-3">
        <img src="<?php echo base_url() ?>uploads/memberphoto/<?php echo $basicinfo->photo; ?>" style="width: 150px; height: 170px; border: 1px solid #ccc;"/>
        <div style="display: block;  margin-top: 20px; font-size: 15px;">
            <?php echo lang('member_pid')  ?> : <?php echo $basicinfo->PID; ?>
        </div>
    </div>
    
    <div class="col-lg-8">
        
        
        <!-- Gritter -->
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/member/memberinfo/".  encode_id($basicinfo->id), 'class="form-horizontal"'); ?>

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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_member_id'); ?>  : </label>
    <div class="col-lg-6">
        <input type="text" disabled="disabled"  name="memberid" value="<?php echo $basicinfo->member_id; ?>"  class="form-control"/> 
        <?php echo form_error('memberid'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_firstname'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="firstname" value="<?php echo $basicinfo->firstname; ?>"  class="form-control"/> 
        <?php echo form_error('firstname'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_middlename'); ?>  :</label>
    <div class="col-lg-6">
        <input type="text" name="middlename" value="<?php echo $basicinfo->middlename; ?>"  class="form-control"/> 
        <?php echo form_error('middlename'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_lastname'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="lastname" value="<?php echo $basicinfo->lastname; ?>"  class="form-control"/> 
        <?php echo form_error('lastname'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_gender'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="gender" class="form-control">
            <option value=""> <?php echo lang('select_default_text'); ?></option>
            <?php
            $loop = lang('member_genderoption');
            $selected = $basicinfo->gender;
            foreach ($loop as $key => $value) {
                ?>
                <option <?php echo ($selected ? ($selected == $key ? 'selected="selected' : '') : ''); ?> value="<?php echo $key; ?>"> <?php echo $value; ?></option>
            <?php }
            ?>
        </select>
        <?php echo form_error('gender'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_maritalstatus'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="maritalstatus" class="form-control">
            <option value=""> <?php echo lang('select_default_text'); ?></option>
            <?php
            $loop = lang('member_maritalstatus_option');
            $selected = $basicinfo->maritalstatus;
            foreach ($loop as $key => $value) {
                ?>
                <option <?php echo ($selected ? ($selected == $key ? 'selected="selected' : '') : ''); ?> value="<?php echo $key; ?>"> <?php echo $value; ?></option>
            <?php } ?>
        </select>
        <?php echo form_error('maritalstatus'); ?>
    </div>
</div>


<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_dob'); ?>  : <span class="required">*</span></label>
    <div class=" col-lg-6">
        <div class="input-group date" id="datetimepicker" >
            <input type="text" name="dob" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo format_date($basicinfo->dob, false); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>
        <?php echo form_error('dob'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_join_date'); ?>  : <span class="required">*</span></label>
    <div class=" col-lg-6">
        <div class="input-group date" id="datetimepicker2" >
            <input type="text" name="joindate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo format_date($basicinfo->joiningdate, false); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>
        <?php echo form_error('joindate'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_photo'); ?>  :  </label>
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
        <input class="btn btn-primary" value="<?php echo lang('member_edit_btn'); ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script src="<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
    $(function () {
        $('#datetimepicker').datetimepicker({
            pickTime: false
        });
        $('#datetimepicker2').datetimepicker({
            pickTime: false
        });
    });
</script>
        
        
        
        
    </div>
</div>