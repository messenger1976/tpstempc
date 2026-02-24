<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<style>
/* Fix z-index for autocomplete dropdown in modal */
.modal .ac_results,
#createBeginningBalanceModal .ac_results,
.modal-body .ac_results {
    z-index: 1060 !important;
    position: absolute !important;
}
/* Ensure modal backdrop and modal have correct z-index */
.modal-backdrop {
    z-index: 1040 !important;
}
.modal {
    z-index: 1050 !important;
}
.modal-dialog {
    z-index: 1051 !important;
}
</style>

<?php 
// Get data - these should be passed from parent view or controller
// Default to empty arrays if not set
if (!isset($account_list)) {
    $account_list = array();
}
if (!isset($paymenthod)) {
    $paymenthod = array();
}
?>

<?php echo form_open_multipart(current_lang() . "/saving/create_savings_beginning_balance", 'class="form-horizontal" id="beginningBalanceForm"'); ?>

<div class="row">
    <div class="col-md-7">
        <div class="form-group">
            <label class="col-sm-4 control-label"><?php echo lang('member_pid'); ?>  : <span class="required">*</span></label>
            <div class="col-sm-8">
                <div class="input-group">
                    <input type="text" id="beginning_pid" name="pid" value=""  class="form-control"/> 
                    <span class="input-group-addon" id="search_beginning_pid" style="cursor: pointer;">
                        <span class="fa fa-search"></span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-4 control-label"><?php echo lang('member_member_id'); ?>  : <span class="required">*</span></label>
            <div class="col-sm-8">
                <div class="input-group">
                    <input type="text" id="beginning_member_id" name="member_id" value=""  class="form-control"/> 
                    <span class="input-group-addon" id="search_beginning_mid" style="cursor: pointer;">
                        <span class="fa fa-search"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-4 control-label"><?php echo lang('member_old_account_no'); ?>: </label>
            <div class="col-sm-8">
                <input type="text" id="beginning_old_member_id" name="old_member_id" value=""  class="form-control"/> 
            </div>
        </div>
        
        <div style="color: brown;margin: 15px 0; font-weight: bold; font-size: 13px; border-bottom: 1px solid #ccc;">
            <?php echo lang('member_saccos_saving_account_title'); ?>
        </div>     

        <div class="form-group">
            <label class="col-sm-4 control-label"><?php echo lang('mortuary_transaction_date'); ?>  : <span class="required">*</span></label>
            <div class="col-sm-8">
                <div class="input-group date" id="beginning_datepicker">
                    <input type="text" name="posting_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo date('d-m-Y'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
                    <span class="input-group-addon">
                        <span class="fa fa-calendar"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-4 control-label"><?php echo lang('member_saccos_saving_account_type'); ?>  : <span class="required">*</span></label>
            <div class="col-sm-8">
                <select name="saving_account" id="beginning_saving_account" class="form-control">
                    <option value=""><?php echo lang('select_default_text'); ?></option>
                    <?php
                    foreach ($account_list as $key => $value) {
                        ?>
                        <option value="<?php echo $value->account; ?>"><?php echo $value->account . ' - ' . $value->name; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-4 control-label"><?php echo lang('beginning_balance'); ?>  : <span class="required">*</span></label>
            <div class="col-sm-8">
                <input type="text" name="beginning_balance" value=""  class="form-control amountformat"/> 
            </div>
        </div> 
        
        <div class="form-group">
            <label class="col-sm-4 control-label"><?php echo lang('comment'); ?>  : </label>
            <div class="col-sm-8">
                <textarea name="comment" class="form-control" placeholder="Beginning Balance" rows="2">Beginning Balance</textarea> 
            </div>
        </div> 

        <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8">
                <input class="btn btn-primary" value="<?php echo lang('create_savings_beginning_balance'); ?>" type="submit"/>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('cancel'); ?></button>
            </div>
        </div>
    </div>

    <div class="col-md-5" id="beginning_member_info" style="min-height: 200px;">
        <!-- Member info will be displayed here -->
    </div>
    <!-- Hidden div for autocomplete plugin to use (it's hardcoded to use #member_info) -->
    <div id="member_info" style="display: none;"></div>
</div>
<?php echo form_close(); ?>

<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script type="text/javascript">
// Wait for jQuery to be available
(function() {
    function waitForJQuery() {
        if (typeof jQuery === 'undefined' || typeof jQuery === 'undefined') {
            setTimeout(waitForJQuery, 50);
            return;
        }
        
        // Use jQuery instead of $ to avoid conflicts
        jQuery(document).ready(function($) {
        // Initialize when modal is shown
        $('#createBeginningBalanceModal').on('shown.bs.modal', function() {
            initializeBeginningBalanceForm($);
            
            // Sync #member_info content to #beginning_member_info
            // The autocomplete plugin is hardcoded to use #member_info
            var syncMemberInfo = function() {
                var memberInfoContent = $('#member_info').html();
                if (memberInfoContent && memberInfoContent.trim() !== '') {
                    $('#beginning_member_info').html(memberInfoContent);
                }
            };
            
            // Use MutationObserver for efficient syncing
            if (typeof MutationObserver !== 'undefined') {
                var memberInfoObserver = new MutationObserver(function(mutations) {
                    syncMemberInfo();
                });
                
                var memberInfoElement = document.getElementById('member_info');
                if (memberInfoElement) {
                    memberInfoObserver.observe(memberInfoElement, {
                        childList: true,
                        subtree: true,
                        characterData: true
                    });
                }
                
                // Store observer for cleanup
                $(this).data('memberInfoObserver', memberInfoObserver);
            } else {
                // Fallback for older browsers
                var syncInterval = setInterval(syncMemberInfo, 200);
                $(this).data('syncInterval', syncInterval);
            }
            
            // Initial sync
            setTimeout(syncMemberInfo, 100);
        });
        
        // Cleanup when modal is hidden
        $('#createBeginningBalanceModal').on('hidden.bs.modal', function() {
            var observer = $(this).data('memberInfoObserver');
            if (observer) {
                observer.disconnect();
            }
            var interval = $(this).data('syncInterval');
            if (interval) {
                clearInterval(interval);
            }
            $('#member_info').html('');
            $('#beginning_member_info').html('');
        });
        
        // Fix z-index for autocomplete dropdown when it's created
        $(document).on('focus', '#beginning_pid, #beginning_member_id', function() {
            setTimeout(function() {
                $('.ac_results').css({
                    'z-index': '1060',
                    'position': 'absolute'
                });
            }, 50);
        });
        });
    }
    
    function initializeBeginningBalanceForm($) {
        // Initialize datepicker
        if (typeof $.fn.datetimepicker !== 'undefined') {
            $('#beginning_datepicker').datetimepicker({
                pickTime: true
            });
        }
        
        // Payment method removed - beginning balances are adjustments only
        
        // Load autocomplete script if not already loaded
        var existingScript = document.querySelector('script[src*="jquery.autocomplete.js"]');
        if (!existingScript) {
            var autocompleteScript = document.createElement('script');
            autocompleteScript.src = '<?php echo base_url(); ?>media/js/jquery.autocomplete.js';
            autocompleteScript.onload = function() {
                initializeAutocomplete($);
            };
            document.head.appendChild(autocompleteScript);
        } else {
            // Script already loaded, initialize after a short delay
            setTimeout(function() {
                initializeAutocomplete($);
            }, 200);
        }
        
        function initializeAutocomplete($) {
            // Destroy any existing jQuery UI autocomplete instances
            try {
                if ($("#beginning_pid").data('ui-autocomplete')) {
                    $("#beginning_pid").autocomplete('destroy');
                }
                if ($("#beginning_member_id").data('ui-autocomplete')) {
                    $("#beginning_member_id").autocomplete('destroy');
                }
            } catch(e) {
                // Ignore errors
            }
            
            // Wait a bit to ensure cleanup is complete before initializing
            setTimeout(function() {
                try {
                    $("#beginning_pid").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest/pid'); ?>",
                    {
                        pleasewait:'<?php echo lang("please_wait"); ?>',
                        serverURLq:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
                        secondID: 'beginning_member_id',
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
                    
                    $("#beginning_member_id").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest/mid'); ?>",{
                        pleasewait:'<?php echo lang("please_wait"); ?>',
                        serverURLq:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
                        secondID: 'beginning_pid',
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
                } catch(e) {
                    console.error('Autocomplete initialization error:', e);
                    // Retry once more after a longer delay
                    setTimeout(function() {
                        try {
                            $("#beginning_pid").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest/pid'); ?>",
                            {
                                pleasewait:'<?php echo lang("please_wait"); ?>',
                                serverURLq:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
                                secondID: 'beginning_member_id',
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
                            
                            $("#beginning_member_id").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest/mid'); ?>",{
                                pleasewait:'<?php echo lang("please_wait"); ?>',
                                serverURLq:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
                                secondID: 'beginning_pid',
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
                        } catch(e2) {
                            console.error('Autocomplete retry failed:', e2);
                        }
                    }, 300);
                }
            }, 150);
        }   
        
        // Search PID button
        $("#search_beginning_pid").off('click').on('click', function(){
            var pid = $("#beginning_pid").val();
            if(pid.length > 0){
                $('#beginning_member_info').html('<?php echo lang("please_wait"); ?>');
                $.ajax({
                    url:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
                    type:'POST',
                    data:{value:pid, column :'PID'},                              
                    success: function(data){
                        var json = JSON.parse(data);
                        if(json['success'].toString() == 'N'){
                            $('#beginning_member_info').html('<div style="color:red;">'+json['error'].toString()+'</div>');
                        }else{
                            var userdata = json['data'];
                            var contact = json['contact'];
                            $("#beginning_member_id").val(userdata["member_id"]);
                            var output = '<div style="border:1px solid  #ccc;font-size:15px;"><table style="width:100%;"><tr><td style="width:70%;">';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_fullname'); ?> : </strong> '+userdata["firstname"]+' '+userdata["middlename"]+' '+userdata["lastname"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_gender'); ?> : </strong> '+userdata["gender"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_dob'); ?> : </strong> '+userdata["dob"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_join_date'); ?> : </strong> '+userdata["joiningdate"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone1'); ?> : </strong> '+contact["phone1"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone2'); ?> : </strong> '+contact["phone2"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_email'); ?> : </strong> '+contact["email"]+'</div>';
                            output +='</td><td>  <img style=" height:120px;" src="<?php echo base_url(); ?>uploads/memberphoto/'+userdata["photo"].toString()+'"/></td></tr></table></div>';
                            $('#beginning_member_info').html(output);   
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
        
        // Search Member ID button
        $("#search_beginning_mid").off('click').on('click', function(){
            var pid = $("#beginning_member_id").val();
            if(pid.length > 0){
                $('#beginning_member_info').html('<?php echo lang("please_wait"); ?>');
                $.ajax({
                    url:'<?php echo site_url(current_lang() . '/saving/search_member/'); ?>',
                    type:'POST',
                    data:{value:pid, column :'MID'},                              
                    success: function(data){
                        var json = JSON.parse(data);
                        if(json['success'].toString() == 'N'){
                            $('#beginning_member_info').html('<div style="color:red;">'+json['error'].toString()+'</div>');
                        }else{
                            var userdata = json['data'];
                            var contact = json['contact'];
                            $("#beginning_pid").val(userdata["PID"]);
                            var output = '<div style="border:1px solid  #ccc; font-size:15px;"><table style="width:100%;"><tr><td style="width:70%;">';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_fullname'); ?> : </strong> '+userdata["firstname"]+' '+userdata["middlename"]+' '+userdata["lastname"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_gender'); ?> : </strong> '+userdata["gender"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_dob'); ?> : </strong> '+userdata["dob"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_join_date'); ?> : </strong> '+userdata["joiningdate"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone1'); ?> : </strong> '+contact["phone1"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone2'); ?> : </strong> '+contact["phone2"]+'</div>';
                            output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_email'); ?> : </strong> '+contact["email"]+'</div>';
                            output +='</td><td>  <img style=" height:120px;" src="<?php echo base_url(); ?>uploads/memberphoto/'+userdata["photo"].toString()+'"/></td></tr></table></div>';
                            $('#beginning_member_info').html(output);   
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
    }
    
    waitForJQuery();
})();
</script>
