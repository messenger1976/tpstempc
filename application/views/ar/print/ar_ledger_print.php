<div style="font-family: Arial, sans-serif;">
    <div style="text-align: center;">
        <h3><strong><?php echo company_info()->name; ?></strong></h3>
        <h2><strong>AR Ledger</strong></h2>
        <h4><strong>Customer: <?php echo htmlspecialchars($customer_name); ?></strong></h4>
        <h4><strong>From <?php echo format_date($date_from, false); ?> to <?php echo format_date($date_to, false); ?></strong></h4>
    </div>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid #000; padding: 8px;">Date</th>
                <th style="border: 1px solid #000; padding: 8px;">Reference</th>
                <th style="border: 1px solid #000; padding: 8px;">Description</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right;">Debit</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right;">Credit</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right;">Running Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($ledger)) {
                foreach ($ledger as $row) {
                    $ref = !empty($row->invoiceid) ? '#' . $row->invoiceid : (isset($row->refferenceID) ? $row->refferenceID : '');
                    ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px;"><?php echo format_date($row->date, false); ?></td>
                        <td style="border: 1px solid #000; padding: 6px;"><?php echo htmlspecialchars($ref); ?></td>
                        <td style="border: 1px solid #000; padding: 6px;"><?php echo htmlspecialchars($row->description ?: '-'); ?></td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: right;"><?php echo $row->debit > 0 ? number_format($row->debit, 2) : ''; ?></td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: right;"><?php echo $row->credit > 0 ? number_format($row->credit, 2) : ''; ?></td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: right;"><?php echo number_format($row->running_balance, 2); ?></td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="6" style="border: 1px solid #000; padding: 8px;">No data found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
