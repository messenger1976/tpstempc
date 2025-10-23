<?php echo form_open_multipart(current_lang() . "/sms/sendSMS/", 'class="form-horizontal"'); ?>
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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Sender ID'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="sender" class="form-control">
            <?php 
            $select = set_value('sender');
            foreach ($senderlist as $key => $value) { ?>
                <option <?php echo ($value->name == $select ? 'selected="selected"':'') ?> value="<?php  echo $value->name; ?>"><?php echo $value->name; ?></option>
            <?php }
            ?>
        </select>   
        <?php echo form_error('sender'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Group'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="group" class="form-control">
            <?php 
             $select = set_value('group');
            foreach ($grouplist as $key => $value) { ?>
                <option <?php echo ($value->id == $select ? 'selected="selected"':'') ?> value="<?php echo $value->id; ?>"><?php echo $value->name .' ( '.$this->sms_model->count_sms_contact($value->id).' )' ; ?></option>
            <?php }
            ?>
        </select>   
            <?php echo form_error('group'); ?>
    </div>
</div>
<div class="form-group" style="padding-bottom: 0px; margin-bottom: 0px;"><label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
 Character remain : <div style="display: inline-block; font-weight: bold;" id="remain_char">160</div> &nbsp; &nbsp; SMS : <div style="display: inline-block; font-weight: bold;" id="smscount">1</div>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Message Body'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <textarea name="sms" id="sms" class="form-control" style="height: 100px;"><?php echo set_value('sms'); ?></textarea> 
        
        <?php echo form_error('sms'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo 'Send SMS'; ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>
<script type="text/javascript">
$(document).ready(function(){
  var remin_char = $("#remain_char").text();
  var smscount = $("#smscount").text();
  var max = 160;
  
  /// onload
     
     var len = $("#sms").val().length;
     
     if (len >= max) {
         var smsCount = (Math.floor(len/max) + 1 );
         $('#smscount').text(smsCount);
        remin_char =  (max-(len%max));
           $('#remain_char').text(remin_char);
     }else{
         var remin_char = max - len;
	    $('#remain_char').text(remin_char);
	    $('#smscount').text('1');
     }
  
  
  
  
  $("#sms").keyup(function(){
     
     var len = $(this).val().length;
     
     if (len >= max) {
         var smsCount = (Math.floor(len/max) + 1 );
         $('#smscount').text(smsCount);
        remin_char =  (max-(len%max));
           $('#remain_char').text(remin_char);
     }else{
         var remin_char = max - len;
	    $('#remain_char').text(remin_char);
	    $('#smscount').text('1');
     }
  });
});
</script>