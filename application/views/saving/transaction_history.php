<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>

<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet"/>
<?php echo form_open(current_lang() . "/saving/transaction_search", 'class="form-horizontal"'); ?>

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
$_GET['from'] = format_date($jxy['from'],FALSE);
$_GET['upto'] = format_date($jxy['upto'],FALSE);
$account_type_filter = isset($account_type_filter) ? $account_type_filter : (isset($_GET['account_type_filter']) ? $_GET['account_type_filter'] : 'all');

?>

<div class="form-group col-lg-12">

    <?php $selected_trans_type = isset($selected_trans_type) ? strtoupper($selected_trans_type) : 'ALL'; ?>

    <div class="col-lg-2">
        <input type="text" class="form-control" name="key" id="accountno" placeholder="<?php echo lang('account_no').' / '.  lang('finance_account_name'); ?>" value="<?php echo (isset($_GET['key']) ? $_GET['key'] : ''); ?>"/> 
    </div>
    <div class="col-lg-2">
        <input type="text" class="form-control" id="from" data-date-format="DD-MM-YYYY" placeholder="<?php echo lang('hint_date'); ?>" name="from" value="<?php echo (isset($_GET['from']) ? $_GET['from'] : ''); ?>"/> 
    </div>
    <div class="col-lg-2">
        <input type="text" class="form-control" id="upto" data-date-format="DD-MM-YYYY" placeholder="<?php echo lang('hint_date'); ?>" name="upto" value="<?php echo (isset($_GET['upto']) ? $_GET['upto'] : ''); ?>"/> 
    </div>
    <div class="col-lg-2">
        <select name="account_type_filter" class="form-control">
            <option value="all" <?php echo ($account_type_filter == 'all' ? 'selected="selected"' : ''); ?>>All</option>
            <option value="special" <?php echo ($account_type_filter == 'special' ? 'selected="selected"' : ''); ?>>Special</option>
            <option value="mso" <?php echo ($account_type_filter == 'mso' ? 'selected="selected"' : ''); ?>>MSO</option>
        </select>
    </div>
    <div class="col-lg-2">
        <select name="trans_type" class="form-control">
            <option value="ALL" <?php echo ($selected_trans_type == 'ALL' ? 'selected="selected"' : ''); ?>>ALL</option>
            <option value="DEPOSIT" <?php echo ($selected_trans_type == 'DEPOSIT' ? 'selected="selected"' : ''); ?>>DEPOSIT</option>
            <option value="WITHDRAWAL" <?php echo ($selected_trans_type == 'WITHDRAWAL' ? 'selected="selected"' : ''); ?>>WITHDRAWAL</option>
            <option value="INTEREST" <?php echo ($selected_trans_type == 'INTEREST' ? 'selected="selected"' : ''); ?>>INTEREST</option>
        </select>
    </div>
    <div class="col-lg-2">
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
                <th><?php echo lang('index_account'); ?></th>
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
                    <td>
                        <?php echo $value->receipt; ?>
                        <?php if (isset($value->is_void_entry) && $value->is_void_entry): ?>
                            <br/>
                            <span class="label label-warning" title="This is a reversing entry">
                                <i class="fa fa-reply"></i> Void of <?php echo $value->voided_receipt; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo !empty($value->account_no_display) ? $value->account_no_display : $value->account; ?></td>
                    <td><?php echo $this->finance_model->saving_account_name($value->account); ?></td>
                    <td>
                        <?php
                        $trans_type_label = isset($value->trans_type_display) ? strtoupper(trim($value->trans_type_display)) : strtoupper(trim($value->trans_type));
                        $is_interest = ($trans_type_label === 'INTEREST');
                        echo $trans_type_label;
                        ?>
                    </td>
                    <td>
                        <?php echo $value->paymethod; ?>
                        <?php if (isset($value->is_void_entry) && $value->is_void_entry && !empty($value->void_original_method)): ?>
                            <br/>
                            <span class="label label-info" title="Original method before void">
                                <i class="fa fa-history"></i> Original: <?php echo $value->void_original_method; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $value->cheque_num; ?></td>
                    <td><?php echo number_format($value->amount,2); ?></td>
                    <td><?php echo $value->trans_date; ?></td>
                    

                    <td>
                        <?php echo anchor(current_lang() . "/saving/receipt_view/" . $value->receipt, ' <i class="fa fa-edit"></i> ' . lang('view_link')); ?>
                        &nbsp;&nbsp;
                        <?php if (isset($value->is_gl_posted) && $value->is_gl_posted): ?>
                            <span class="label label-success" title="Posted to General Ledger">
                                <i class="fa fa-book"></i> GL Posted
                            </span>
                        <?php else: ?>
                            <span class="label label-default" title="Not yet posted to General Ledger">
                                <i class="fa fa-book"></i> GL Not Posted
                            </span>
                            <?php if (has_role(3, 'saving_account_list')): ?>
                                &nbsp;
                                <a href="<?php echo site_url(current_lang() . '/saving/post_receipt_to_gl/' . $value->receipt); ?>" class="btn btn-warning btn-xs" onclick="return confirm('Post this transaction to GL now?');" title="Post this transaction to GL">
                                    <i class="fa fa-book"></i> Post to GL
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (has_role(3, 'void_transaction') && !$value->is_void_entry): ?>
                            &nbsp;&nbsp;
                            <?php if (isset($value->is_voided) && $value->is_voided): ?>
                                <span class="label label-danger" title="<?php echo lang('saving_void_transaction'); ?>">
                                    <i class="fa fa-check-circle"></i> VOIDED
                                </span>
                            <?php else: ?>
                                <a href="#" onclick="confirmVoid('<?php echo $value->receipt; ?>', '<?php echo site_url(current_lang() . "/saving/void_transaction/" . $value->receipt); ?>'); return false;" title="<?php echo lang('saving_void_transaction'); ?>">
                                    <i class="fa fa-ban" style="color: red;"></i> <?php echo lang('void_link'); ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>

    </table>
   
    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
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
                            $("#accountno").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_account_all/'); ?>",{
                                matchContains:true
                            });
                        } catch(e) {
                            console.error('Autocomplete initialization error:', e);
                            // Retry once more after a longer delay
                            setTimeout(function() {
                                try {
                                    $("#accountno").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_account_all/'); ?>",{
                                        matchContains:true
                                    });
                                } catch(e2) {
                                    console.error('Autocomplete retry failed:', e2);
                                }
                            }, 300);
                        }
                    }, 150);
                });
                    
                $(function () {
                    $('#from').datetimepicker({
                        pickTime: false
                    });
                    $('#upto').datetimepicker({
                        pickTime: false
                    });
                });
            }
        }
        initScripts();
    })();
    
    function confirmVoid(receipt, url) {
        if (confirm('<?php echo lang('saving_void_transaction_warning'); ?>')) {
            window.location.href = url;
        }
    }
</script>
