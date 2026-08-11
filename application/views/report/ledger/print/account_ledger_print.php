<?php
if (!function_exists('al_fmt')) {
    function al_fmt($amount, $show_zero = false) {
        $v = floatval($amount);
        if (!$show_zero && abs($v) < 0.005) {
            return '';
        }
        if (abs($v) < 0.005) {
            return number_format(0, 2);
        }
        if ($v < 0) {
            return '(' . number_format(abs($v), 2) . ')';
        }
        return number_format($v, 2);
    }
}
$acc = $ledger['account'];
$transactions = $ledger['transactions'];
?>
<div style="padding:0; margin:auto;">
    <div style="text-align:center; margin-bottom:8px;">
        <h3 style="margin:0; font-size:14px;"><strong>Account Ledger</strong></h3>
        <div style="font-size:12px; font-weight:bold;"><?php echo htmlspecialchars($acc->account . ' — ' . $acc->name); ?></div>
        <div style="font-size:11px;">
            For the period from <?php echo format_date($fromdate, false); ?>
            to <?php echo format_date($todate, false); ?>
        </div>
    </div>

    <table style="width:100%; font-size:11px; margin-bottom:8px;">
        <tr>
            <td style="width:33%;"><strong>Balance Forwarded:</strong> <?php echo al_fmt($ledger['opening_balance'], true); ?></td>
            <td style="width:34%; text-align:center;">
                <strong>Period Debit:</strong> <?php echo number_format($ledger['period_debit'], 2); ?>
                &nbsp;|&nbsp;
                <strong>Credit:</strong> <?php echo number_format($ledger['period_credit'], 2); ?>
            </td>
            <td style="width:33%; text-align:right;"><strong>Ending Balance:</strong> <?php echo al_fmt($ledger['ending_balance'], true); ?></td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:10px;">
        <thead>
            <tr>
                <th style="border-bottom:1px solid #000; text-align:left; padding:3px;">Date</th>
                <th style="border-bottom:1px solid #000; text-align:left; padding:3px;">Type</th>
                <th style="border-bottom:1px solid #000; text-align:left; padding:3px;">Ref #</th>
                <th style="border-bottom:1px solid #000; text-align:left; padding:3px;">Description</th>
                <th style="border-bottom:1px solid #000; text-align:right; padding:3px;">Debit</th>
                <th style="border-bottom:1px solid #000; text-align:right; padding:3px;">Credit</th>
                <th style="border-bottom:1px solid #000; text-align:right; padding:3px;">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" style="padding:3px; font-weight:bold;">Balance Forwarded</td>
                <td style="text-align:right; padding:3px;"><?php echo ($ledger['opening_debit'] > 0 ? number_format($ledger['opening_debit'], 2) : ''); ?></td>
                <td style="text-align:right; padding:3px;"><?php echo ($ledger['opening_credit'] > 0 ? number_format($ledger['opening_credit'], 2) : ''); ?></td>
                <td style="text-align:right; padding:3px; font-weight:bold;"><?php echo al_fmt($ledger['opening_balance'], true); ?></td>
            </tr>
            <?php foreach ($transactions as $t) {
                $journal_type = isset($t->trans_comment) ? $t->trans_comment : '';
                $ref_no = (isset($t->invoiceid) && $t->invoiceid > 0) ? $t->invoiceid : (isset($t->refferenceID) ? $t->refferenceID : '');
                $desc = isset($t->description) ? $t->description : '';
                if (!empty($t->related_entity_name)) {
                    $desc = trim($desc . ($desc !== '' ? ' — ' : '') . $t->related_entity_name);
                }
                ?>
                <tr>
                    <td style="padding:2px 3px; border-bottom:1px solid #eee;"><?php echo format_date($t->date, false); ?></td>
                    <td style="padding:2px 3px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($journal_type); ?></td>
                    <td style="padding:2px 3px; border-bottom:1px solid #eee;"><?php echo ($ref_no !== '' && $ref_no !== null) ? '#' . htmlspecialchars($ref_no) : ''; ?></td>
                    <td style="padding:2px 3px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($desc); ?></td>
                    <td style="text-align:right; padding:2px 3px; border-bottom:1px solid #eee;"><?php echo ($t->debit > 0 ? number_format($t->debit, 2) : ''); ?></td>
                    <td style="text-align:right; padding:2px 3px; border-bottom:1px solid #eee;"><?php echo ($t->credit > 0 ? number_format($t->credit, 2) : ''); ?></td>
                    <td style="text-align:right; padding:2px 3px; border-bottom:1px solid #eee;"><?php echo al_fmt($t->running_balance, true); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="4" style="padding:4px 3px; border-top:1px solid #000; font-weight:bold; text-align:right;">Period Totals</td>
                <td style="text-align:right; padding:4px 3px; border-top:1px solid #000; font-weight:bold;"><?php echo number_format($ledger['period_debit'], 2); ?></td>
                <td style="text-align:right; padding:4px 3px; border-top:1px solid #000; font-weight:bold;"><?php echo number_format($ledger['period_credit'], 2); ?></td>
                <td style="border-top:1px solid #000;"></td>
            </tr>
            <tr>
                <td colspan="6" style="padding:4px 3px; border-bottom:3px double #000; font-weight:bold; text-align:right;">Ending Balance</td>
                <td style="text-align:right; padding:4px 3px; border-bottom:3px double #000; font-weight:bold;"><?php echo al_fmt($ledger['ending_balance'], true); ?></td>
            </tr>
        </tbody>
    </table>
</div>
