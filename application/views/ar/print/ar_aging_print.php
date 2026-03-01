<div style="font-family: Arial, sans-serif;">
    <div style="text-align: center;">
        <h3><strong><?php echo company_info()->name; ?></strong></h3>
        <h2><strong>AR Aging Report</strong></h2>
        <h4><strong>As of <?php echo format_date($as_of, false); ?></strong></h4>
    </div>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid #000; padding: 6px;">#</th>
                <th style="border: 1px solid #000; padding: 6px;">Aging Bucket</th>
                <th style="border: 1px solid #000; padding: 6px;">Customer #</th>
                <th style="border: 1px solid #000; padding: 6px;">Customer Name</th>
                <th style="border: 1px solid #000; padding: 6px;">Invoice #</th>
                <th style="border: 1px solid #000; padding: 6px;">Issue Date</th>
                <th style="border: 1px solid #000; padding: 6px;">Due Date</th>
                <th style="border: 1px solid #000; padding: 6px; text-align: right;">Days Overdue</th>
                <th style="border: 1px solid #000; padding: 6px; text-align: right;">Balance</th>
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
                        <tr style="background-color: #e8e8e8; font-weight: bold;">
                            <td colspan="9" style="border: 1px solid #000; padding: 6px;"><?php echo $bucket['label']; ?></td>
                        </tr>
                        <?php
                        foreach ($bucket['invoices'] as $inv) {
                            $grand_total += $inv['balance'];
                            ?>
                            <tr>
                                <td style="border: 1px solid #000; padding: 5px;"><?php echo $sno++; ?></td>
                                <td style="border: 1px solid #000; padding: 5px;"></td>
                                <td style="border: 1px solid #000; padding: 5px;"><?php echo htmlspecialchars($inv['customer_number']); ?></td>
                                <td style="border: 1px solid #000; padding: 5px;"><?php echo htmlspecialchars($inv['customer_name']); ?></td>
                                <td style="border: 1px solid #000; padding: 5px;">#<?php echo $inv['invoice_id']; ?></td>
                                <td style="border: 1px solid #000; padding: 5px;"><?php echo $inv['issue_date'] ? format_date($inv['issue_date'], false) : '-'; ?></td>
                                <td style="border: 1px solid #000; padding: 5px;"><?php echo $inv['due_date'] ? format_date($inv['due_date'], false) : '-'; ?></td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: right;"><?php echo $inv['days_overdue']; ?></td>
                                <td style="border: 1px solid #000; padding: 5px; text-align: right;"><?php echo number_format($inv['balance'], 2); ?></td>
                            </tr>
                        <?php } ?>
                        <tr style="font-weight: bold;">
                            <td colspan="8" style="border: 1px solid #000; padding: 5px; text-align: right;">Subtotal:</td>
                            <td style="border: 1px solid #000; padding: 5px; text-align: right;"><?php echo number_format($bucket['total'], 2); ?></td>
                        </tr>
                    <?php }
                }
                if ($grand_total > 0) { ?>
                    <tr style="background-color: #d0d0d0; font-weight: bold;">
                        <td colspan="8" style="border: 1px solid #000; padding: 8px; text-align: right;">GRAND TOTAL:</td>
                        <td style="border: 1px solid #000; padding: 8px; text-align: right;"><?php echo number_format($grand_total, 2); ?></td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="9" style="border: 1px solid #000; padding: 8px;">No data found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
