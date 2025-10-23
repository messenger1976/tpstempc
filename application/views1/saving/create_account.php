<script type="text/javascript" src="<?php echo base_url(); ?>media/js/jquery.autocomplete.js" ></script>
<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/saving/create_saving_account", 'class="form-horizontal"'); ?>

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

<div class="col-lg-12">
    <div class="col-lg-7">
        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('member_pid'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <div class="input-group">
                    <input type="text" id="pid" name="pid" value="<?php echo set_value('pid'); ?>"  class="form-control"/> 
                    <span class="input-group-addon" id="search_pid" style="cursor: pointer;">
                        <span class="fa fa-search"  ></span>
                    </span>
                </div>
                <?php echo form_error('pid'); ?>
            </div>
        </div>
        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('member_member_id'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <div class="input-group">
                    <input type="text" id="member_id" name="member_id" value="<?php echo set_value('member_id'); ?>"  class="form-control"/> 
                    <span class="input-group-addon" id="search_mid" style="cursor: pointer;">
                        <span class="fa fa-search"  ></span>
                    </span>
                </div>
                <?php echo form_error('member_id'); ?>
            </div>
        </div>

        <div style="color: brown;margin: 20px; font-weight: bold; font-size: 13px; border-bottom: 1px solid #ccc;">
            <?php echo lang('member_saccos_saving_account_title'); ?>
        </div>     


        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('member_saccos_saving_account_type'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <select name="saving_account" class="form-control">
                    <option value=""><?php echo lang('select_default_text'); ?></option>
                    <?php
                    $selected = set_value('saving_account');
                    foreach ($account_list as $key => $value) {
                        ?>
                        <option <?php echo ($value->account == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->account; ?>"><?php echo $value->account . ' - ' . $value->name; ?></option>
                    <?php } ?>
                </select>
                <?php echo form_error('saving_account'); ?>
            </div>
        </div>

        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('account_balance_open'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <input type="text"  name="open_balance" value="<?php echo set_value('open_balance'); ?>"  class="form-control amountformat"/> 
                <?php echo form_error('open_balance'); ?>
            </div>
        </div>        

        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('paymentmethod'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <select name="paymenthod" id="paymenthod" class="form-control">
                    <?php
                    $selected = set_value('paymenthod');
                    foreach ($paymenthod as $key => $value) {
                        ?>
                        <option <?php echo ($value->name == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                    <?php } ?>
                </select>
                <?php echo form_error('paymenthod'); ?>
            </div>
        </div>

        <div id="chequenumber" class="form-group"><label class="col-lg-4 control-label"><?php echo lang('cheque_no'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <input type="text"  name="cheque" value="<?php echo set_value('cheque'); ?>"  class="form-control "/> 
                <?php echo form_error('cheque'); ?>
            </div>
        </div> 
        <div  class="form-group"><label class="col-lg-4 control-label"><?php echo lang('comment'); ?>  : </label>
            <div class="col-lg-7">
                <textarea name="comment" class="form-control" ><?php echo set_value('cheque'); ?></textarea> 
                <?php echo form_error('comment'); ?>
            </div>
        </div> 


        <div class="form-group">
            <label class="col-lg-3 control-label">&nbsp;</label>
            <div class="col-lg-6">
                <input class="btn btn-primary" value="<?php echo lang('member_group_btn'); ?>" type="submit"/>
            </div>
        </div>

    </div>

    <div class="col-lg-5" id="member_info">

    </div>

</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function(){
        $("#chequenumber").hide(); 
        
        var paymenthod = '<?php echo set_value("paymenthod"); ?>';
        if(paymenthod == 'CHEQUE'){
                $("#chequenumber").show(); 
            }else{
                $("#chequenumber").hide();       
            }
            
            
        $("#paymenthod").change(function(){
            var val = $(this).val();
            if(val == 'CHEQUE'){
                $("#chequenumber").show(); 
            }else{
                $("#chequenumber").hide();       
            }
        });
        
        $(window).keydown(function(event){
            if(event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });
        
        
        $("#pid").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest/pid'); ?>",
        {
            pleasewait:'<?php echo lang("please_wait"); ?>',
            serverURLq:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
            secondID: 'member_id',
            Name: '<?php echo lang('member_fullname'); ?>',
            gender: '<?php echo lang('member_gender'); ?>',
            dob: '<?php echo lang('member_dob'); ?>',
            joindate: '<?php echo lang('member_join_date'); ?>',
            phone1: '<?php echo lang('member_contact_phone1'); ?> ',
            phone2: '<?php echo lang('member_contact_phone2'); ?>',
            email: '<?php echo lang('member_contact_email'); ?>',
            photourl: '<?php echo base_url(); ?>uploads/memberphoto/',
            matchContains:true,
            column: 'PID'
        }); 
        
        $("#member_id").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest/mid'); ?>",{
            pleasewait:'<?php echo lang("please_wait"); ?>',
            serverURLq:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
            secondID: 'pid',
            Name: '<?php echo lang('member_fullname'); ?>',
            gender: '<?php echo lang('member_gender'); ?>',
            dob: '<?php echo lang('member_dob'); ?>',
            joindate: '<?php echo lang('member_join_date'); ?>',
            phone1: '<?php echo lang('member_contact_phone1'); ?> ',
            phone2: '<?php echo lang('member_contact_phone2'); ?>',
            email: '<?php echo lang('member_contact_email'); ?>',
            photourl: '<?php echo base_url(); ?>uploads/memberphoto/',
            matchContains:true,
            column: 'MID'
        });   
     
     
        
        var pid = '<?php echo set_value('pid'); ?>';
            
        if(pid.length > 0){
            $('#member_info').html('<?php echo lang("please_wait"); ?>');
            $.ajax({
                url:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
                type:'POST',
                data:{
                    value:pid,
                    column :'PID'
                },                              
                success: function(data){
                    var json = JSON.parse(data);
                    if(json['success'].toString() == 'N'){
                        $('#member_info').html('<div style="color:red;">'+json['error'].toString()+'</div>');
                    }else{
                        var userdata = json['data'];
                        var contact = json['contact'];
                        $("#member_id").val(userdata["member_id"]);
                        var output = '<div style="border:1px solid  #ccc;font-size:15px;"><table style="width:100%;"><tr><td style="width:70%;">';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_fullname'); ?> : </strong> '+userdata["firstname"]+' '+userdata["middlename"]+' '+userdata["lastname"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_gender'); ?> : </strong> '+userdata["gender"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_dob'); ?> : </strong> '+userdata["dob"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_join_date'); ?> : </strong> '+userdata["joiningdate"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone1'); ?> : </strong> '+contact["phone1"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone2'); ?> : </strong> '+contact["phone2"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_email'); ?> : </strong> '+contact["email"]+'</div>';
                        output +='</td><td>  <img style=" height:120px;" src="<?php echo base_url(); ?>uploads/memberphoto/'+userdata["photo"].toString()+'"/></td></tr></table>       </div>'
                        $('#member_info').html(output);   
                    }
                        
                        
                },
                error:function(xhr,textStatus,errorThrown){
                    alert(errorThrown); 
                }
            });
                
                
                
        }
        
        
        $("#search_pid").click(function(){
            
            var pid = $("#pid").val();
            
            if(pid.length > 0){
                $('#member_info').html('<?php echo lang("please_wait"); ?>');
                $.ajax({
                    url:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
                    type:'POST',
                    data:{
                        value:pid,
                        column :'PID'
                    },                              
                    success: function(data){
                        var json = JSON.parse(data);
                        if(json['success'].toString() == 'N'){
                            $('#member_info').html('<div style="color:red;">'+json['error'].toString()+'</div>');
                        }else{
                            var userdata = json['data'];
                            var contact = json['contact'];
                            $("#member_id").val(userdata["member_id"]);
                            var output = '<div style="border:1px solid  #ccc;font-size:15px;"><table style="width:100%;"><tr><td style="width:70%;">';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_fullname'); ?> : </strong> '+userdata["firstname"]+' '+userdata["middlename"]+' '+userdata["lastname"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_gender'); ?> : </strong> '+userdata["gender"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_dob'); ?> : </strong> '+userdata["dob"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_join_date'); ?> : </strong> '+userdata["joiningdate"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone1'); ?> : </strong> '+contact["phone1"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone2'); ?> : </strong> '+contact["phone2"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_email'); ?> : </strong> '+contact["email"]+'</div>';
                            output +='</td><td>  <img style=" height:120px;" src="<?php echo base_url(); ?>uploads/memberphoto/'+userdata["photo"].toString()+'"/></td></tr></table>       </div>'
                            $('#member_info').html(output);   
                        }
                        
                        
                    },
                    error:function(xhr,textStatus,errorThrown){
                        alert(errorThrown); 
                    }
                });
                
                
                
            }else{
                alert('<?php echo lang("alert_pid"); ?>');
            }
        });
        
        
        $("#search_mid").click(function(){
            var pid = $("#member_id").val();
            if(pid.length > 0){
                $('#member_info').html('<?php echo lang("please_wait"); ?>');
                $.ajax({
                    url:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
                    type:'POST',
                    data:{
                        value:pid,
                        column :'MID'
                    },                              
                    success: function(data){
                        var json = JSON.parse(data);
                        if(json['success'].toString() == 'N'){
                            $('#member_info').html('<div style="color:red;">'+json['error'].toString()+'</div>');
                        }else{
                            var userdata = json['data'];
                            var contact = json['contact'];
                            $("#pid").val(userdata["PID"]);
                            var output = '<div style="border:1px solid  #ccc; font-size:15px;"><table style="width:100%;"><tr><td style="width:70%;">';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_fullname'); ?> : </strong> '+userdata["firstname"]+' '+userdata["middlename"]+' '+userdata["lastname"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_gender'); ?> : </strong> '+userdata["gender"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_dob'); ?> : </strong> '+userdata["dob"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_join_date'); ?> : </strong> '+userdata["joiningdate"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone1'); ?> : </strong> '+contact["phone1"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone2'); ?> : </strong> '+contact["phone2"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_email'); ?> : </strong> '+contact["email"]+'</div>';
                            output +='</td><td>  <img style=" height:120px;" src="<?php echo base_url(); ?>uploads/memberphoto/'+userdata["photo"].toString()+'"/></td></tr></table>       </div>'
                            $('#member_info').html(output);   
                        }
                        
                        
                    },
                    error:function(xhr,textStatus,errorThrown){
                        alert(errorThrown); 
                    }
                });
                
               
               
            }else{
                alert('<?php echo lang("alert_member_id"); ?>');
            }
        });
        
        
        
        
        
        
        
        
        
        
        
    });
</script>