<div class="row">
    <div class="col-lg-12">
        <div style="padding: 30px 10px; margin: auto;">
            <div class="btn-group" style="margin-bottom: 15px;">
                <a href="<?php echo site_url(current_lang() . '/ar/ar_balances'); ?>" class="btn btn-default"><?php echo lang('ar_balances_title'); ?></a>
                <a href="<?php echo site_url(current_lang() . '/ar/ar_ledger'); ?>" class="btn btn-default"><?php echo lang('ar_ledger_title'); ?></a>
                <a href="<?php echo site_url(current_lang() . '/ar/ar_aging'); ?>" class="btn btn-primary"><?php echo lang('ar_aging_title'); ?></a>
            </div>
            <div style="text-align: center;">
                <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong><?php echo lang('ar_aging_title'); ?></strong></h1>
                <h4><strong><?php echo lang('ar_as_of'); ?>: <?php echo format_date($as_of, false); ?></strong></h4>
            </div>

            <form method="post" action="<?php echo site_url(current_lang() . '/ar/ar_aging'); ?>" class="form-inline" style="margin: 15px 0;">
                <label><?php echo lang('ar_as_of'); ?>:</label>
                <input type="text" name="as_of" value="<?php echo htmlspecialchars($as_of); ?>" class="form-control" data-date-format="YYYY-MM-DD" style="width: 140px;" />
                <button type="submit" class="btn btn-primary"><?php echo lang('ar_run_report'); ?></button>
            </form>

            <style type="text/css">
                .ar-aging-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .ar-aging-table th { background-color: #f0f0f0; font-weight: bold; padding: 10px; text-align: left; border: 1px solid #ddd; }
                .ar-aging-table td { padding: 8px; border: 1px solid #ddd; }
                .ar-aging-table .text-right { text-align: right; }
                .ar-aging-table .bucket-header { background-color: #e8e8e8; font-weight: bold; }
                .ar-aging-table .bucket-total { background-color: #f5f5f5; font-weight: bold; }
            </style>

            <div class="table-responsive">
                <table class="table table-bordered table-striped ar-aging-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Aging Bucket</th>
                            <th><?php echo lang('customer_id'); ?></th>
                            <th><?php echo lang('customer_name'); ?></th>
                            <th><?php echo lang('ar_invoice'); ?></th>
                            <th><?php echo lang('ar_issue_date'); ?></th>
                            <th><?php echo lang('ar_due_date'); ?></th>
                            <th class="text-right"><?php echo lang('ar_days_overdue'); ?></th>
                            <th class="text-right"><?php echo lang('ar_balance'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sno = 1;
                        $grand_total = 0;
                        if (!empty($aging_data)) {
                            foreach ($aging_data as $bucket_key => $bucket) {
                                if (count($bucket['invoices']) > 0) {
                                    ?>
                                    <tr class="bucket-header">
                                        <td colspan="9"><?php echo $bucket['label']; ?> (<?php echo count($bucket['invoices']); ?> invoice(s))</td>
                                    </tr>
                                    <?php
                                    foreach ($bucket['invoices'] as $inv) {
                                        $grand_total += $inv['balance'];
                                        ?>
                                        <tr>
                                            <td><?php echo $sno++; ?></td>
                                            <td></td>
                                            <td><?php echo htmlspecialchars($inv['customer_number']); ?></td>
                                            <td><?php echo htmlspecialchars($inv['customer_name']); ?></td>
                                            <td>#<?php echo $inv['invoice_id']; ?></td>
                                            <td><?php echo $inv['issue_date'] ? format_date($inv['issue_date'], false) : '-'; ?></td>
                                            <td><?php echo $inv['due_date'] ? format_date($inv['due_date'], false) : '-'; ?></td>
                                            <td class="text-right"><?php echo $inv['days_overdue']; ?></td>
                                            <td class="text-right"><?php echo number_format($inv['balance'], 2); ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr class="bucket-total">
                                        <td colspan="8" class="text-right"><strong>Subtotal <?php echo $bucket['label']; ?>:</strong></td>
                                        <td class="text-right"><strong><?php echo number_format($bucket['total'], 2); ?></strong></td>
                                    </tr>
                                <?php }
                            }
                            if ($grand_total > 0) { ?>
                                <tr style="background-color: #d0d0d0; font-weight: bold;">
                                    <td colspan="8" class="text-right"><strong>GRAND TOTAL:</strong></td>
                                    <td class="text-right"><strong><?php echo number_format($grand_total, 2); ?></strong></td>
                                </tr>
                            <?php }
                        }
                        if ($grand_total == 0) { ?>
                            <tr>
                                <td colspan="9"><?php echo lang('ar_no_data'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; padding-top: 20px; border-top: 1px solid #ddd;">
                <a href="<?php echo site_url(current_lang() . '/ar/ar_aging_print?as_of=' . urlencode($as_of)); ?>" class="btn btn-primary" target="_blank"><?php echo lang('ar_print'); ?></a>
                &nbsp;
                <a href="<?php echo site_url(current_lang() . '/ar/ar_aging_export?as_of=' . urlencode($as_of)); ?>" class="btn btn-success"><i class="fa fa-file-excel-o"></i> <?php echo lang('ar_export_excel'); ?></a>
            </div>
        </div>
    </div>
</div>
