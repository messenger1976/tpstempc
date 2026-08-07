<?php if (isset($link_cat) && (int) $link_cat === 3): ?>
<div class="alert alert-info" style="margin-bottom: 15px;">
    <i class="fa fa-info-circle"></i>
    <strong>Cash Receipts Journal</strong> lists General Ledger lines posted from
    <strong>Cash Receipt</strong> transactions (journal ID 3), including received-from / payor details with links to the receipt and account ledger.
</div>
<?php elseif (isset($link_cat) && (int) $link_cat === 10): ?>
<div class="alert alert-info" style="margin-bottom: 15px;">
    <i class="fa fa-info-circle"></i>
    <strong>Cash Disbursement Journal</strong> lists General Ledger lines posted from
    <strong>Cash Disbursement</strong> transactions (journal ID 10).
</div>
<?php elseif (isset($link_cat) && (int) $link_cat === 5): ?>
<div class="alert alert-info" style="margin-bottom: 15px;">
    <i class="fa fa-info-circle"></i>
    This report covers <strong>Journal Entries / General Journal</strong> (manual journal vouchers).
    Cash Receipts and Cash Disbursements have their own journal reports.
</div>
<?php endif; ?>

<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/report/create_journal_trans_title/' . $link_cat); ?>"><?php echo 'New Report'; ?></a>
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
            <?php foreach ($reportlist as $key => $value) { ?>
                <tr>
                    <td style="width: 300px;"><?php echo anchor(current_lang() . "/report/create_journal_trans_title/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?> &nbsp; | &nbsp;
                         <?php echo anchor(current_lang() . "/report/delete_report_journal/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-times"></i> ' . lang('button_delete')); ?>   &nbsp; | &nbsp; 
                        <?php
                            echo anchor(current_lang() . "/report/journal_trans_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));
                        
                        ?></td>
                    <td><?php echo format_date($value->fromdate, false); ?></td>
                    <td><?php echo format_date($value->todate, false); ?></td>
                    <td><?php echo $value->description; ?></td>
                    <td><?php echo ($value->page == 'A4' ? 'Portait' : 'Landscape'); ?></td>
                </tr>
            <?php } ?>
        </tbody>

    </table>
</div>
