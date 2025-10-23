<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/report_contribution/create_contribution_report_title/' . $link_cat); ?>"><?php echo 'New Report'; ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('index_action_th'); ?></th>
                <th><?php echo ($link_cat == 1 ? 'Member Joined From' : 'From'); ?></th>
                <th><?php echo 'Until'; ?></th>
                <th><?php echo 'Description'; ?></th>
                <th><?php echo 'Page Orientation'; ?></th>

            </tr>

        </thead>
        <tbody>
            <?php foreach ($reportlist as $key => $value) { ?>
                <tr>
                    <td style="width: 300px;"><?php echo anchor(current_lang() . "/report_contribution/create_contribution_report_title/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?>   &nbsp; | &nbsp;
                        <?php echo anchor(current_lang() . "/report_contribution/delete_report_contribution/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-times"></i> ' . lang('button_delete')); ?>   &nbsp; | &nbsp; 
                        <?php
                        if ($link_cat == 1) {
                            echo anchor(current_lang() . "/report_contribution/contribution_balance_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));
                        }  else if($link_cat == 2) {
                        echo anchor(current_lang() . "/report_contribution/contribution_statement_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        } else if($link_cat == 3) {
                        echo anchor(current_lang() . "/report_contribution/contribution_transaction_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        }else if($link_cat == 4) {
                        echo anchor(current_lang() . "/report_contribution/contribution_transaction_summary_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));    
                        }
                        ?></td>
                    <td><?php echo format_date($value->fromdate, false); ?></td>
                    <td><?php echo format_date($value->todate, false); ?></td>
                    <td><?php echo ($link_cat != 2 ? $value->description: $value->description.' :: '.$this->member_model->member_name($value->description)); ?></td>
                    <td><?php echo ($value->page == 'A4' ? 'Portait' : 'Landscape'); ?></td>
                </tr>
<?php } ?>
        </tbody>

    </table>
</div>