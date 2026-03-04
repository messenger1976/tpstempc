<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/report_saving/saving_account_report_title/' . $link_cat); ?>"><?php echo 'New Report'; ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('index_action_th'); ?></th>
                <th><?php echo ($link_cat == 1 ? 'Account Created From' : 'From'); ?></th>
                <th><?php echo 'Until'; ?></th>
                <th><?php echo 'Description'; ?></th>
                <?php if ($link_cat == 1 || $link_cat == 2) { ?>
                    <th><?php echo 'Saving Account Type'; ?></th>
                <?php } ?>
                <th><?php echo 'Page Orientation'; ?></th>

            </tr>

        </thead>
        <tbody>
            <?php foreach ($reportlist as $key => $value) { ?>
                <tr>
                    <td style="width: 300px;"><?php echo anchor(current_lang() . "/report_saving/saving_account_report_title/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?>   &nbsp; | &nbsp;
                        <?php echo anchor(current_lang() . "/report_saving/delete_report_saving_account/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-times"></i> ' . lang('button_delete')); ?>   &nbsp; | &nbsp; 
                        <?php
                        if ($link_cat == 1) {
                            echo anchor(current_lang() . "/report_saving/saving_account_accountlist_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));
                        }  else if($link_cat == 2) {
                        echo anchor(current_lang() . "/report_saving/saving_account_statement_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        } else if($link_cat == 3) {
                        echo anchor(current_lang() . "/report_saving/saving_account_transaction_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        }else if($link_cat == 4) {
                        echo anchor(current_lang() . "/report_saving/saving_account_transaction_summary_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        }
                        ?></td>
                    <td><?php echo format_date($value->fromdate, false); ?></td>
                    <td><?php echo format_date($value->todate, false); ?></td>
                    <td><?php 
                    if ($link_cat != 2) {
                        echo $value->description;
                    } else {
                        // Get account info for formatting
                        $account_info = $this->finance_model->saving_account_balance($value->description);
                        $acc_name = $this->finance_model->saving_account_name($value->description);
                        
                        // Format: account_cat - [old_members_acct] account_name
                        $display_text = '';
                        if ($account_info) {
                            $display_text = $account_info->account_cat;
                            if (!empty($account_info->old_members_acct)) {
                                $display_text .= ' - [' . htmlspecialchars($account_info->old_members_acct, ENT_QUOTES, 'UTF-8') . ']';
                            }
                        }
                        $display_text .= ' ' . $acc_name;
                        echo $display_text;
                    }
                    ?></td>
                    <?php
                    if ($link_cat == 1 || $link_cat == 2) {
                        if ($link_cat == 1) {
                            // For account list, use account_type field
                            $account = $this->finance_model->saving_account_list(null, $value->account_type)->row();
                            ?>
                            <td><?php echo ($account ? $account->name : ''); ?></td>
                            <?php
                        } else if ($link_cat == 2) {
                            // For account statement, get account type from account info
                            $account_info = $this->finance_model->saving_account_balance($value->description);
                            if ($account_info && !empty($account_info->account_cat)) {
                                $account_type = $this->finance_model->saving_account_list(null, $account_info->account_cat)->row();
                                ?>
                                <td><?php echo $account_type ? htmlspecialchars($account_type->name, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                <?php
                            } else {
                                ?>
                                <td>-</td>
                                <?php
                            }
                        }
                    } ?>
                    <td><?php echo ($value->page == 'A4' ? 'Portait' : 'Landscape'); ?></td>
                </tr>
<?php } ?>
        </tbody>

    </table>
</div>