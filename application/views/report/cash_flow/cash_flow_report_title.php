<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a class="btn btn-primary" href="<?php echo site_url(current_lang() . '/report/create_cash_flow_report'); ?>"><?php echo 'New Report'; ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('index_action_th'); ?></th>
                <th><?php echo 'From'; ?></th>
                <th><?php echo 'Until'; ?></th>
                <th><?php echo 'Description'; ?></th>
                <th><?php echo 'Page Orientation'; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reportlist)) { ?>
                <?php foreach ($reportlist as $value) { ?>
                    <tr>
                        <td style="width: 350px;">
                            <?php echo anchor(current_lang() . '/report/create_cash_flow_report/' . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?>
                            &nbsp; | &nbsp;
                            <?php echo anchor(current_lang() . '/report/delete_cash_flow_report/' . encode_id($value->id), ' <i class="fa fa-times"></i> ' . lang('button_delete')); ?>
                            &nbsp; | &nbsp;
                            <?php echo anchor(current_lang() . '/report/cash_flow_report_view/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view')); ?>
                        </td>
                        <td><?php echo format_date($value->fromdate, false); ?></td>
                        <td><?php echo format_date($value->todate, false); ?></td>
                        <td><?php echo htmlspecialchars($value->description); ?></td>
                        <td><?php echo ($value->page == 'A4' ? 'Portrait' : 'Landscape'); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" style="text-align: center;">
                        No cash flow reports found.
                        <a href="<?php echo site_url(current_lang() . '/report/create_cash_flow_report'); ?>">Create a new one</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
