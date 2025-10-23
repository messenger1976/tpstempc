<script type="text/javascript" src="<?php echo base_url(); ?>media/js/jquery.autocomplete_saving.js" ></script>
<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/saving/credit_debit", 'class="form-horizontal"'); ?>

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
        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('account_no'); ?>  : <span class="required">*</span></label>
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
        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('transaction_type'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <select name="trans_type" class="form-control">
                    <option value=""><?php echo lang('select_default_text'); ?></option>
                    <?php
                    $selected = set_value('trans_type');
                    $transtype = lang('saving_transaction_type_option');
                    foreach ($transtype as $key => $value) {
                        ?>
                        <option <?php echo ($key == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php } ?>
                </select>
                <?php echo form_error('trans_type'); ?>
            </div>
        </div>

        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('amount'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <input type="text"  name="amount" value="<?php echo set_value('amount'); ?>"  class="form-control amountformat"/> 
                <?php echo form_error('amount'); ?>
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
        <div  class="form-group"><label class="col-lg-4 control-label"><?php echo lang('customer_name'); ?>  : </label>
            <div class="col-lg-7">
                <input name="customer_name" id="customer_name" class="form-control" value="<?php echo set_value('customer_name'); ?>"/> 
                <?php echo form_error('customer_name'); ?>
            </div>
        </div> 
        <div  class="form-group"><label class="col-lg-4 control-label"><?php echo lang('comment'); ?>  : </label>
            <div class="col-lg-7">
                <textarea name="comment" class="form-control" ><?php echo set_value('comment'); ?></textarea> 
                <?php echo form_error('comment'); ?>
            </div>
        </div> 


        <div class="form-group">
            <label class="col-lg-3 control-label">&nbsp;</label>
            <div class="col-lg-6">
                <input class="btn btn-primary" value="<?php echo lang('record_btn'); ?>" type="submit"/>
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
        
        
        $("#pid").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_account/pid'); ?>",
        {
            pleasewait:'<?php echo lang("please_wait"); ?>',
            serverURLq:'<?php echo site_url(current_lang() . '/saving/search_account/'); ?>',
            secondID: 'member_id',
            Name: '<?php echo lang('member_fullname'); ?>',
            gender: '<?php echo lang('member_gender'); ?>',
            dob: '<?php echo lang('member_dob'); ?>',
            joindate: '<?php echo lang('member_join_date'); ?>',
            phone1: '<?php echo lang('member_contact_phone1'); ?> ',
            phone2: '<?php echo lang('member_contact_phone2'); ?>',
            email: '<?php echo lang('member_contact_email'); ?>',
            photourl: '<?php echo base_url(); ?>uploads/memberphoto/',
            column: 'PID',
            matchContains:true,
            customerNameID: 'customer_name',
            balance: '<?php echo lang('balance'); ?>'
        }); 
        
       
        function addCommas(nStr)
        {
            nStr += '';
            x = nStr.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            return x1 + x2;
        }

        
        var pid = '<?php echo set_value('pid'); ?>';
            
        if(pid.length > 0){
            $('#member_info').html('<?php echo lang("please_wait"); ?>');
            $.ajax({
                url:'<?php echo site_url(current_lang() . '/saving/search_account/'); ?>',
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
                        var account_balance = json['accountinfo'];
                        var customername = userdata["firstname"]+' '+userdata["middlename"]+' '+userdata["lastname"];
                        $("#customer_name").val(customername);
                        var output = '<div style="border:1px solid  #ccc;font-size:15px;"><table style="width:100%;"><tr><td style="width:70%;">';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_fullname'); ?> : </strong> '+userdata["firstname"]+' '+userdata["middlename"]+' '+userdata["lastname"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_gender'); ?> : </strong> '+userdata["gender"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_dob'); ?> : </strong> '+userdata["dob"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_join_date'); ?> : </strong> '+userdata["joiningdate"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone1'); ?> : </strong> '+contact["phone1"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone2'); ?> : </strong> '+contact["phone2"]+'</div>';
                        output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_email'); ?> : </strong> '+contact["email"]+'</div>';
                        output +='</td><td>  <img style=" height:120px;" src="<?php echo base_url(); ?>uploads/memberphoto/'+userdata["photo"].toString()+'"/></td></tr></table>       </div>'
                        output += '<div style="font-size:30px;"><strong><?php echo lang('balance'); ?> : </strong> '+  addCommas(parseFloat(account_balance["balance"]).toFixed(2))+'</div>';
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
                    url:'<?php echo site_url(current_lang() . '/saving/search_account/'); ?>',
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
                            var account_balance = json['accountinfo'];
                            var customername = userdata["firstname"]+' '+userdata["middlename"]+' '+userdata["lastname"];
                            $("#customer_name").val(customername);
                        
                            var output = '<div style="border:1px solid  #ccc;font-size:15px;"><table style="width:100%;"><tr><td style="width:70%;">';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_fullname'); ?> : </strong> '+userdata["firstname"]+' '+userdata["middlename"]+' '+userdata["lastname"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_gender'); ?> : </strong> '+userdata["gender"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_dob'); ?> : </strong> '+userdata["dob"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_join_date'); ?> : </strong> '+userdata["joiningdate"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone1'); ?> : </strong> '+contact["phone1"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone2'); ?> : </strong> '+contact["phone2"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_email'); ?> : </strong> '+contact["email"]+'</div>';
                            output +='</td><td>  <img style=" height:120px;" src="<?php echo base_url(); ?>uploads/memberphoto/'+userdata["photo"].toString()+'"/></td></tr></table>       </div>'
                            output += '<div style="font-size:30px;"><strong><?php echo lang('balance'); ?>  : </strong> '+  addCommas(parseFloat(account_balance["balance"]).toFixed(2))+'</div>';
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
        
        
        
        
        
    });
</script>