<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
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
        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('mortuary_transaction_date'); ?>  : <span class="required">*</span></label>
            <div class=" col-lg-7">
                <div class="input-group date" id="datetimepicker" >
                    <?php $posting_date = (isset ($value) ? date("d-m-Y",strtotime($value->posting_date)) : set_value('posting_date'));?>
                    <input type="text" name="posting_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo $posting_date; ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
                    <span class="input-group-addon">
                        <span class="fa fa-calendar "></span>
                    </span>
                </div>
                <?php echo form_error('posting_date'); ?>
            </div>
        </div>

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

        <div class="form-group"><label class="col-lg-4 control-label">Ref. No.  : </label>
            <div class="col-lg-7">
                <input type="text" name="refno" value="<?php echo set_value('refno'); ?>" class="form-control"/> 
                <?php echo form_error('refno'); ?>
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
<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script type="text/javascript">
    (function() {
        function initScripts() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initScripts, 50);
                return;
            }
            
            // jQuery UI is loaded in template and also defines autocomplete
            // We need to load our custom autocomplete plugin AFTER jQuery UI
            // and ensure it overwrites jQuery UI's autocomplete
            var autocompleteScriptLoaded = false;
            
            // Check if script is already in the DOM
            var existingScript = document.querySelector('script[src*="jquery.autocomplete_saving.js"]');
            if (existingScript) {
                // Script already exists, just wait a bit and continue
                setTimeout(function() {
                    loadDatePicker();
                }, 200);
            } else {
                var autocompleteScript = document.createElement('script');
                autocompleteScript.src = '<?php echo base_url(); ?>media/js/jquery.autocomplete_saving.js';
                autocompleteScript.onload = function() {
                    autocompleteScriptLoaded = true;
                    // Wait longer to ensure the plugin fully registers and overwrites jQuery UI's autocomplete
                    setTimeout(function() {
                        loadDatePicker();
                    }, 300);
                };
                autocompleteScript.onerror = function() {
                    console.error('Failed to load autocomplete plugin');
                };
                document.head.appendChild(autocompleteScript);
            }
            
            function loadDatePicker() {
                // Load bootstrap-datepicker after jQuery and autocomplete are available
                if (typeof $.fn.datetimepicker === 'undefined') {
                    var datepickerScript = document.createElement('script');
                    datepickerScript.src = '<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
                    datepickerScript.onload = function() {
                        initMainScript();
                    };
                    document.head.appendChild(datepickerScript);
                } else {
                    initMainScript();
                }
            }
            
            function initMainScript() {
                $(function () {
                    $('#datetimepicker').datetimepicker({
                        pickTime: true
                    });
                });
                
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
        
        
        // Destroy any existing jQuery UI autocomplete instances
        try {
            if ($("#pid").data('ui-autocomplete')) {
                $("#pid").autocomplete('destroy');
            }
        } catch(e) {
            // Ignore errors
        }
        
        // Wait a bit to ensure cleanup is complete before initializing
        setTimeout(function() {
            try {
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
            } catch(e) {
                console.error('Autocomplete initialization error:', e);
                // Retry once more after a longer delay
                setTimeout(function() {
                    try {
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
                    } catch(e2) {
                        console.error('Autocomplete retry failed:', e2);
                    }
                }, 300);
            }
        }, 150); 
        
       
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
                    // Trim whitespace that might cause JSON parse errors
                    data = data.trim();
                    
                    // Check if response is empty or not valid JSON
                    if (!data || data.length === 0) {
                        $('#member_info').html('<div style="color:red;">Error: Invalid response from server. Please try again.</div>');
                        return;
                    }
                    
                    // Try to parse JSON, handle errors gracefully
                    var json;
                    try {
                        json = JSON.parse(data);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response data:', data);
                        $('#member_info').html('<div style="color:red;">Error: Invalid response from server. Please try again.</div>');
                        return;
                    }
                    
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
                    console.error('AJAX Error:', textStatus, errorThrown);
                    console.error('Response:', xhr.responseText);
                    $('#member_info').html('<div style="color:red;">Error: Unable to connect to server. Please try again.</div>');
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
                        // Trim whitespace that might cause JSON parse errors
                        data = data.trim();
                        
                        // Check if response is empty or not valid JSON
                        if (!data || data.length === 0) {
                            $('#member_info').html('<div style="color:red;">Error: Invalid response from server. Please try again.</div>');
                            return;
                        }
                        
                        // Try to parse JSON, handle errors gracefully
                        var json;
                        try {
                            json = JSON.parse(data);
                        } catch (e) {
                            console.error('JSON Parse Error:', e);
                            console.error('Response data:', data);
                            $('#member_info').html('<div style="color:red;">Error: Invalid response from server. Please try again.</div>');
                            return;
                        }
                        
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
                        console.error('AJAX Error:', textStatus, errorThrown);
                        console.error('Response:', xhr.responseText);
                        $('#member_info').html('<div style="color:red;">Error: Unable to connect to server. Please try again.</div>');
                    }
                });
                
                
                
            }else{
                alert('<?php echo lang("alert_pid"); ?>');
            }
        });
        
        
        
        
        
                });
            }
        }
        initScripts();
    })();
</script>