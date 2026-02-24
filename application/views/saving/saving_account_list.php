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
$status_filter = isset($status_filter) ? $status_filter : (isset($_GET['status_filter']) ? $_GET['status_filter'] : '1');
?>

<div class="form-group col-lg-12">
    <div class="col-lg-3">
        <input type="text" class="form-control" id="accountno" name="key" placeholder="<?php echo lang('search_account_member'); ?>" value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>"/> 
    </div>
    <div class="col-lg-2">
        <select name="account_type_filter" class="form-control">
            <option value="all" <?php echo ($account_type_filter == 'all' ? 'selected="selected"' : ''); ?>>All</option>
            <option value="special" <?php echo ($account_type_filter == 'special' ? 'selected="selected"' : ''); ?>>Special</option>
            <option value="mso" <?php echo ($account_type_filter == 'mso' ? 'selected="selected"' : ''); ?>>MSO</option>
        </select>
    </div>
    <div class="col-lg-2">
        <select name="status_filter" class="form-control">
            <option value="all" <?php echo ($status_filter == 'all' ? 'selected="selected"' : ''); ?>><?php echo lang('all_status'); ?></option>
            <option value="1" <?php echo ($status_filter == '1' ? 'selected="selected"' : ''); ?>><?php echo lang('account_status_active'); ?></option>
            <option value="0" <?php echo ($status_filter == '0' ? 'selected="selected"' : ''); ?>><?php echo lang('account_status_inactive'); ?></option>
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
        // Always pass status_filter to export (including 'all' or default '1')
        if (isset($status_filter) && $status_filter != '') {
            $export_params['status_filter'] = $status_filter;
        } else {
            // If not set, default to '1' (Active) for export
            $export_params['status_filter'] = '1';
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

<?php echo form_open(current_lang() . '/saving/post_selected_to_gl', array('id' => 'form_post_selected_gl', 'onsubmit' => 'return confirm(\'' . addslashes(lang('saving_account_post_selected_to_gl_confirm')) . '\');')); ?>
<?php if (!empty($search_key)) { ?><input type="hidden" name="redirect_key" value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>"/><?php } ?>
<?php if (!empty($account_type_filter) && $account_type_filter != 'all') { ?><input type="hidden" name="redirect_account_type_filter" value="<?php echo htmlspecialchars($account_type_filter, ENT_QUOTES, 'UTF-8'); ?>"/><?php } ?>
<?php if (isset($status_filter) && $status_filter !== '') { ?><input type="hidden" name="redirect_status_filter" value="<?php echo htmlspecialchars($status_filter, ENT_QUOTES, 'UTF-8'); ?>"/><?php } ?>
<div class="form-group col-lg-12" style="margin-bottom: 10px;">
    <button type="submit" name="post_selected" id="btn_post_selected_gl" class="btn btn-warning" disabled="disabled">
        <i class="fa fa-book"></i> <?php echo lang('saving_account_post_selected_to_gl'); ?>
    </button>
    <span id="post_selected_hint" class="text-muted" style="margin-left: 10px;"></span>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped" id="saving_account_list_table">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">
                    <input type="checkbox" id="select_all_post_gl" title="<?php echo htmlspecialchars(lang('saving_account_select_all_post_gl'), ENT_QUOTES, 'UTF-8'); ?>"/>
                </th>
                <th><?php echo lang('account_number'); ?></th>
                <th><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('member_fullname'); ?></th>
                <th><?php echo lang('member_old_account_no'); ?></th>
                <th><?php echo lang('account_type_name'); ?></th>
                <th style="text-align: right; width: 120px;"><?php echo lang('balance'); ?></th>
                <th style="text-align: right; width: 120px;"><?php echo lang('virtual_balance'); ?></th>
                <th style="text-align: center; width: 100px;"><?php echo lang('account_status'); ?></th>
                <th style="text-align: center; width: 100px;"><?php echo lang('saving_account_gl_status'); ?></th>
                <th style="text-align: center; width: 200px;"><?php echo lang('index_action_th'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($saving_accounts) && count($saving_accounts) > 0) { ?>
                <?php foreach ($saving_accounts as $key => $value) {
                    $unposted_count = isset($value->unposted_count) ? intval($value->unposted_count) : 0;
                    $can_post = $unposted_count > 0;
                ?>
                    <tr>
                        <td style="text-align: center;">
                            <?php if ($can_post) { ?>
                                <input type="checkbox" name="ids[]" value="<?php echo htmlspecialchars(encode_id($value->id), ENT_QUOTES, 'UTF-8'); ?>" class="cb_post_gl"/>
                            <?php } else { ?>
                                <input type="checkbox" disabled="disabled" title="<?php echo htmlspecialchars(lang('saving_account_gl_not_posted'), ENT_QUOTES, 'UTF-8'); ?>"/>
                            <?php } ?>
                        </td>
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
                        <td style="text-align: center;">
                            <?php 
                            $status_value = isset($value->status) ? $value->status : '1';
                            $status_class = ($status_value == '1' || $status_value === 1) ? 'btn-success' : 'btn-danger';
                            $status_text = ($status_value == '1' || $status_value === 1) ? lang('account_status_active') : lang('account_status_inactive');
                            ?>
                            <span class="btn <?php echo $status_class; ?> btn-xs" style="cursor: default;">
                                <?php echo htmlspecialchars($status_text, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <?php
                            $gl_posted_count = isset($value->gl_posted_count) ? intval($value->gl_posted_count) : 0;
                            $gl_class = $gl_posted_count > 0 ? 'btn-success' : 'btn-default';
                            $gl_text = $gl_posted_count > 0 ? lang('saving_account_gl_posted') : lang('saving_account_gl_not_posted');
                            ?>
                            <span class="btn <?php echo $gl_class; ?> btn-xs" style="cursor: default;">
                                <?php echo htmlspecialchars($gl_text, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
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
                            if ($can_post) {
                                echo anchor(current_lang() . "/saving/post_to_gl/" . encode_id($value->id), ' <i class="fa fa-book"></i> ' . lang('saving_account_post_to_gl'), 'class="btn btn-warning btn-xs btn-outline" style="margin-right: 5px;" onclick="return confirm(\'' . htmlspecialchars(lang('saving_account_post_to_gl_confirm'), ENT_QUOTES, 'UTF-8') . '\');"');
                            }
                            echo anchor(current_lang() . "/saving/edit_saving_account/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'), 'class="btn btn-success btn-xs btn-outline"'); 
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="11" style="text-align: center;"><?php echo lang('no_records_found'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div>
</div>
<?php echo form_close(); ?>

<script>
(function() {
    var selectAll = document.getElementById('select_all_post_gl');
    var checkboxes = document.querySelectorAll('.cb_post_gl');
    var btn = document.getElementById('btn_post_selected_gl');
    var hint = document.getElementById('post_selected_hint');

    function updateButton() {
        var n = 0;
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) n++;
        }
        btn.disabled = n === 0;
        hint.textContent = n > 0 ? (n === 1 ? '<?php echo addslashes(lang('saving_account_1_selected')); ?>' : n + ' <?php echo addslashes(lang('saving_account_n_selected')); ?>') : '';
    }

    if (selectAll) {
        selectAll.onclick = function() {
            var checked = this.checked;
            for (var i = 0; i < checkboxes.length; i++) checkboxes[i].checked = checked;
            updateButton();
        };
    }
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].onclick = updateButton;
    }
    updateButton();
})();
</script>
