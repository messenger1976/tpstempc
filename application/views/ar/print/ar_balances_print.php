<div style="font-family: Arial, sans-serif;">
    <div style="text-align: center;">
        <h3><strong><?php echo company_info()->name; ?></strong></h3>
        <h2><strong>AR Balances</strong></h2>
        <h4><strong>As of <?php echo format_date($as_of, false); ?></strong></h4>
    </div>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid #000; padding: 8px; text-align: left;">#</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: left;">Customer #</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: left;">Customer Name</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right;">Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            if (!empty($balances)) {
                foreach ($balances as $row) {
                    ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px;"><?php echo $i++; ?></td>
                        <td style="border: 1px solid #000; padding: 6px;"><?php echo htmlspecialchars($row->customer_number ?: $row->customerid); ?></td>
                        <td style="border: 1px solid #000; padding: 6px;"><?php echo htmlspecialchars($row->customer_name ?: $row->customerid); ?></td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: right;"><?php echo number_format($row->balance, 2); ?></td>
                    </tr>
                <?php } ?>
                <tr style="font-weight: bold;">
                    <td colspan="3" style="border: 1px solid #000; padding: 8px; text-align: right;">TOTAL:</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: right;"><?php echo number_format($total_balance, 2); ?></td>
                </tr>
            <?php } else { ?>
                <tr>
                    <td colspan="4" style="border: 1px solid #000; padding: 8px;">No data found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
