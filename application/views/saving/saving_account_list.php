<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>
<?php echo form_open(current_lang() . "/saving/saving_account_list", 'class="form-horizontal"'); ?>

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

$sp = isset($jxy) ? $jxy : array();
$search_key = isset($jxy['key']) ? $jxy['key'] : (isset($_GET['key']) ? $_GET['key'] : '');
$account_type_filter = isset($account_type_filter) ? $account_type_filter : (isset($_GET['account_type_filter']) ? $_GET['account_type_filter'] : 'all');
?>

<div class="form-group col-lg-12">
    <div class="col-lg-3">
        <input type="text" class="form-control" id="accountno" name="key" placeholder="<?php echo lang('search_account_member'); ?>" value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>"/> 
    </div>
    <div class="col-lg-3">
        <select name="account_type_filter" class="form-control">
            <option value="all" <?php echo ($account_type_filter == 'all' ? 'selected="selected"' : ''); ?>>All</option>
            <option value="special" <?php echo ($account_type_filter == 'special' ? 'selected="selected"' : ''); ?>>Special</option>
            <option value="mso" <?php echo ($account_type_filter == 'mso' ? 'selected="selected"' : ''); ?>>MSO</option>
        </select>
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>
    <div class="col-lg-4" style="text-align: right;">
        <?php 
        // Build export URL with current search parameters
        $export_url = current_lang().'/saving/saving_account_list_export';
        $export_params = array();
        if (!empty($search_key)) {
            $export_params['key'] = $search_key;
        }
        if (!empty($account_type_filter) && $account_type_filter != 'all') {
            $export_params['account_type_filter'] = $account_type_filter;
        }
        if (!empty($export_params)) {
            $export_url .= '?' . http_build_query($export_params);
        }
        echo anchor($export_url, 'Export to Excel', 'class="btn btn-success" style="margin-right: 10px;"');
        echo anchor(current_lang().'/saving/create_saving_account/', lang('create_saving_account'), 'class="btn btn-primary"'); 
        ?>
    </div>
</div>

<?php echo form_close(); ?>

<!-- Total Amount Display -->
<div class="form-group col-lg-12" style="margin-top: 20px;">
    <div class="col-lg-12">
        <div class="alert alert-info" style="font-size: 16px; font-weight: bold;">
            <strong><?php echo lang('total_savings_amount'); ?>: </strong>
            <?php echo number_format($total_savings_amount, 2, '.', ','); ?>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('account_number'); ?></th>
                <th><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('member_fullname'); ?></th>
                <th><?php echo lang('member_old_account_no'); ?></th>
                <th><?php echo lang('account_type_name'); ?></th>
                <th style="text-align: right; width: 120px;"><?php echo lang('balance'); ?></th>
                <th style="text-align: right; width: 120px;"><?php echo lang('virtual_balance'); ?></th>
                <th style="text-align: center; width: 200px;"><?php echo lang('index_action_th'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($saving_accounts) && count($saving_accounts) > 0) { ?>
                <?php foreach ($saving_accounts as $key => $value) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($value->account, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php 
                            if ($value->tablename == 'members_grouplist' && $value->group_name) {
                                echo htmlspecialchars($value->group_name, ENT_QUOTES, 'UTF-8');
                            } else if (($value->firstname || $value->lastname)) {
                                echo htmlspecialchars(trim($value->lastname . ', ' . $value->firstname . ' ' . $value->middlename), ENT_QUOTES, 'UTF-8');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <!--<td>
                            <?php 
                            if ($value->tablename == 'members_grouplist' && $value->group_name) {
                                echo htmlspecialchars($value->group_name, ENT_QUOTES, 'UTF-8');
                            } else if (($value->firstname || $value->lastname)) {
                                echo htmlspecialchars(trim($value->firstname . ' ' . $value->middlename . ' ' . $value->lastname), ENT_QUOTES, 'UTF-8');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>-->
                        <td><?php echo htmlspecialchars($value->old_members_acct ? $value->old_members_acct : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($value->account_type_name_display ? $value->account_type_name_display : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->balance, 2, '.', ','); ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->virtual_balance, 2, '.', ','); ?></td>
                        <td style="text-align: center; white-space: nowrap;">
                            <?php 
                            // Find the most recent report for saving account list (link=1) with matching account type
                            $this->db->where('PIN', current_user()->PIN);
                            $this->db->where('link', 1);
                            if (!empty($value->account_cat)) {
                                $this->db->where('account_type', $value->account_cat);
                            }
                            $this->db->order_by('id', 'DESC');
                            $this->db->limit(1);
                            $report = $this->db->get('report_table_saving')->row();
                            
                            if ($report && !empty($value->account)) {
                                // Use the same URL structure as ledger button in account_list_balance.php
                                // Format: /report_saving/new_saving_account_statement_view/{link_cat}/{id}/{encoded_account}
                                $ledger_url = current_lang() . "/report_saving/new_saving_account_statement_view/1/" . encode_id($report->id) . "/" . encode_id($value->account);
                                echo anchor($ledger_url, ' <i class="fa fa-th-list"></i> Ledger', 'class="btn btn-info btn-xs btn-outline" target="_blank" style="margin-right: 5px;"');
                            }
                            echo anchor(current_lang() . "/saving/edit_saving_account/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'), 'class="btn btn-success btn-xs btn-outline"'); 
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="9" style="text-align: center;"><?php echo lang('no_records_found'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div>
</div>

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
                // Script already exists, just wait a bit and initialize
                setTimeout(function() {
                    checkAndInitAutocomplete();
                }, 200);
                return;
            }
            
            var autocompleteScript = document.createElement('script');
            autocompleteScript.src = '<?php echo base_url(); ?>media/js/jquery.autocomplete_origin.js';
            autocompleteScript.onload = function() {
                autocompleteScriptLoaded = true;
                // Wait longer to ensure the plugin fully registers and overwrites jQuery UI's autocomplete
                setTimeout(function() {
                    checkAndInitAutocomplete();
                }, 300);
            };
            autocompleteScript.onerror = function() {
                console.error('Failed to load autocomplete plugin');
            };
            document.head.appendChild(autocompleteScript);
            
            function checkAndInitAutocomplete() {
                // Check that jQuery and autocomplete are available
                if (typeof jQuery === 'undefined' || typeof $.fn.autocomplete === 'undefined') {
                    setTimeout(checkAndInitAutocomplete, 50);
                    return;
                }
                
                $(document).ready(function(){
                    // Ensure the element exists
                    var $elem = $("#accountno");
                    if ($elem.length === 0) {
                        return;
                    }
                    
                    // Destroy any existing jQuery UI autocomplete
                    try {
                        if ($elem.data('ui-autocomplete')) {
                            $elem.autocomplete('destroy');
                        }
                    } catch(e) {
                        // Ignore errors
                    }
                    
                    // Wait a bit more to ensure cleanup is complete
                    setTimeout(function() {
                        try {
                            // The custom autocomplete expects (url, options) format
                            // If jQuery UI's autocomplete is still active, this will fail
                            // So we need to ensure our custom plugin has overwritten it
                            $elem.autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_member_id_all/'); ?>",{
                                matchContains:true
                            });
                        } catch(e) {
                            console.error('Autocomplete initialization error:', e);
                            // If it fails, the custom plugin might not have loaded properly
                            // Try reloading the script
                            var retryScript = document.createElement('script');
                            retryScript.src = '<?php echo base_url(); ?>media/js/jquery.autocomplete_origin.js';
                            retryScript.onload = function() {
                                setTimeout(function() {
                                    try {
                                        $elem.autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_member_id_all/'); ?>",{
                                            matchContains:true
                                        });
                                    } catch(e2) {
                                        console.error('Autocomplete retry failed:', e2);
                                    }
                                }, 200);
                            };
                            document.head.appendChild(retryScript);
                        }
                    }, 150);
                });
            }
        }
        initScripts();
    })();
</script>
