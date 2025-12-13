<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>

<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet"/>
<?php echo form_open(current_lang() . "/contribution/contribution_transaction", 'class="form-horizontal"'); ?>

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

$sp = $jxy;
$_GET['from'] = isset($jxy['from']) && !empty($jxy['from']) ? format_date($jxy['from'],FALSE) : (($this->session->userdata('contribution_transaction_from')) ? $this->session->userdata('contribution_transaction_from') : '');
$_GET['upto'] = isset($jxy['upto']) && !empty($jxy['upto']) ? format_date($jxy['upto'],FALSE) : (($this->session->userdata('contribution_transaction_upto')) ? $this->session->userdata('contribution_transaction_upto') : '');
$_GET['key'] = isset($jxy['key']) ? $jxy['key'] : (($this->session->userdata('contribution_transaction_key')) ? $this->session->userdata('contribution_transaction_key') : '');


?>

<div class="form-group col-lg-12">

    <div class="col-lg-4">
        <input type="text" class="form-control" name="key" id="accountno" placeholder="<?php echo lang('member_member_id').'/'.  lang('customer_name'); ?>" value="<?php echo (isset($_GET['key']) && $_GET['key'] != '' ? $_GET['key'] : ($this->session->userdata('contribution_transaction_key') ? $this->session->userdata('contribution_transaction_key') : '')); ?>"/> 
    </div>
    <div class="col-lg-3">
        <input type="text" class="form-control" id="from" data-date-format="DD-MM-YYYY" placeholder="<?php echo lang('hint_date'); ?>" name="from" value="<?php echo (isset($_GET['from']) && $_GET['from'] != '' ? $_GET['from'] : ($this->session->userdata('contribution_transaction_from') ? $this->session->userdata('contribution_transaction_from') : '')); ?>"/> 
    </div>
    <div class="col-lg-3">
        <input type="text" class="form-control" id="upto" data-date-format="DD-MM-YYYY" placeholder="<?php echo lang('hint_date'); ?>" name="upto" value="<?php echo (isset($_GET['upto']) && $_GET['upto'] != '' ? $_GET['upto'] : ($this->session->userdata('contribution_transaction_upto') ? $this->session->userdata('contribution_transaction_upto') : '')); ?>"/> 
    </div>
    <div class="col-lg-2" style="text-align-last: right;">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>

</div>


<?php echo form_close(); ?>
</div>
<div class="table-responsive" style="overflow: auto;">
    <table class="table table-bordered table-striped" style="width: 100%;">
        <thead>
           
            <tr>
                <th><?php echo lang('sno'); ?></th>
                <th><?php echo lang('index_receipt'); ?></th>
                <th><?php echo lang('member_pid'); ?></th>
                <th><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('index_name'); ?></th>
                <th><?php echo lang('index_transtype'); ?></th>
                <th><?php echo lang('index_transmethod'); ?></th>
                <th><?php echo lang('index_chequeno'); ?></th>
                <th><?php echo lang('index_amount'); ?></th>
                <th><?php echo lang('index_trans_date'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php 
           $index = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
           $index++;
            foreach ($transactionlist as $key => $value) {
                
                ?>
                <tr>                  
                    <td><?php echo $index++; ?></td>
                    <td><?php echo $value->receipt; ?></td>
                    <td><?php echo $value->PID; ?></td>
                    <td><?php echo $value->member_id; ?></td>
                    <td><?php
                    $account_name = $this->member_model->member_basic_info(null,$value->PID,$value->member_id)->row();
                    echo $account_name->firstname.' '.$account_name->middlename.' '.$account_name->lastname;; ?></td>
                    <td><?php echo $value->trans_type; ?></td>
                    <td><?php echo $value->paymethod; ?></td>
                    <td><?php echo $value->cheque_num; ?></td>
                    <td style="text-align: right;"><?php echo number_format($value->amount,2); ?></td>
                    <td><?php echo $value->createdon; ?></td>
                    

                    <td>
                        <?php echo anchor(current_lang() . "/contribution/receipt_view/" . $value->receipt, ' <i class="fa fa-edit"></i> ' . lang('view_link'), 'class="btn btn-primary btn-xs"'); ?>
                        <?php echo anchor(current_lang() . "/contribution/delete_transaction/" . $value->receipt, ' <i class="fa fa-trash"></i> Delete', 'class="btn btn-danger btn-xs delete-transaction" data-receipt="' . $value->receipt . '"'); ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>

    </table>
   
    <div style="margin-top: 20px; margin-bottom: 20px;">
        <?php if (isset($links) && !empty($links)): ?>
            <div style="text-align: center; margin-bottom: 15px;">
                <?php echo $links; ?>
            </div>
        <?php endif; ?>
        <div style="margin-right: 20px; text-align: right;">
            <?php page_selector(); ?>
        </div>
    </div>
    
</div>
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
            var existingScript = document.querySelector('script[src*="jquery.autocomplete_origin.js"]');
            if (existingScript) {
                // Script already exists, just wait a bit and continue
                setTimeout(function() {
                    loadDatePicker();
                }, 200);
            } else {
                var autocompleteScript = document.createElement('script');
                autocompleteScript.src = '<?php echo base_url(); ?>media/js/jquery.autocomplete_origin.js';
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
                $(document).ready(function(){
                    // Destroy any existing jQuery UI autocomplete instances
                    try {
                        if ($("#accountno").data('ui-autocomplete')) {
                            $("#accountno").autocomplete('destroy');
                        }
                    } catch(e) {
                        // Ignore errors
                    }
                    
                    // Wait a bit to ensure cleanup is complete before initializing
                    setTimeout(function() {
                        try {
                            $("#accountno").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_member_id_all/'); ?>",{
                                matchContains:true
                            });
                        } catch(e) {
                            console.error('Autocomplete initialization error:', e);
                            // Retry once more after a longer delay
                            setTimeout(function() {
                                try {
                                    $("#accountno").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_member_id_all/'); ?>",{
                                        matchContains:true
                                    });
                                } catch(e2) {
                                    console.error('Autocomplete retry failed:', e2);
                                }
                            }, 300);
                        }
                    }, 150);
                    
                    $('#from').datetimepicker({
                        todayBtn: "linked",
                        keyboardNavigation: false,
                        forceParse: false,
                        calendarWeeks: true,
                        autoclose: true,
                        pickTime: false
                    });
                });
                    
                $(function(){
                    /*
                    $('#from').datetimepicker({
                        pickTime: false
                    });*/
                    $('#upto').datetimepicker({
                        pickTime: false
                    });
                });
                
                // SweetAlert delete confirmation
                $(document).on('click', '.delete-transaction', function(e) {
                    e.preventDefault();
                    var deleteUrl = $(this).attr('href');
                    var receipt = $(this).data('receipt');
                    
                    swal({
                        title: "Are you sure?",
                        text: "You are about to delete transaction receipt #" + receipt + ". This action cannot be undone!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel",
                        closeOnConfirm: false
                    }, function(isConfirm) {
                        if (isConfirm) {
                            window.location.href = deleteUrl;
                        }
                    });
                });
            }
        }
        initScripts();
    })();
</script>
