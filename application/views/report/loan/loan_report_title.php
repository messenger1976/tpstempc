<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/report_loan/create_loan_report_title/' . $link_cat); ?>"><?php echo 'New Report'; ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('index_action_th'); ?></th>
                <th><?php echo ($link_cat == 1 ? 'Member Joined From' : (($link_cat == 2 || $link_cat ==3) ? 'Disbursed From' : 'From')); ?></th>
                <th><?php echo 'Until'; ?></th>
                <th><?php echo 'Description'; ?></th>
                <?php if($link_cat == 1){ ?>
                <th>Loan Status</th>
                <?php } ?>
                <th><?php echo 'Page Orientation'; ?></th>

            </tr>

        </thead>
        <tbody>
            <?php foreach ($reportlist as $key => $value) { ?>
                <tr>
                    <td style="width: 300px;"><?php echo anchor(current_lang() . "/report_loan/create_loan_report_title/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?>   &nbsp; | &nbsp;
                        <?php echo anchor(current_lang() . "/report_loan/delete_report_loan/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-times"></i> ' . lang('button_delete')); ?>   &nbsp; | &nbsp; 
                        <?php
                        if ($link_cat == 1) {
                            echo anchor(current_lang() . "/report_loan/loan_list_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));
                        }  else if($link_cat == 2) {
                        echo anchor(current_lang() . "/report_loan/loan_balance_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        } else if($link_cat == 3) {
                        echo anchor(current_lang() . "/report_loan/loan_interest_penalty_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        }else if($link_cat == 4) {
                        echo anchor(current_lang() . "/report_loan/loan_transaction_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        }else if($link_cat == 5) {
                        echo anchor(current_lang() . "/report_loan/loan_transaction_summary_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        }else if($link_cat == 6) {
                        echo anchor(current_lang() . "/report_loan/loan_processing_fee_collection_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        }
                        ?></td>
                    <td><?php echo format_date($value->fromdate, false); ?></td>
                    <td><?php echo format_date($value->todate, false); ?></td>
                    <td><?php echo $value->description; ?></td>
                     <?php if($link_cat == 1){ ?>
                    <td><?php echo loan_status($value->custom); ?></td>
                <?php } ?>
                    <td><?php echo ($value->page == 'A4' ? 'Portait' : 'Landscape'); ?></td>
                </tr>
<?php } ?>
        </tbody>

    </table>
</div>