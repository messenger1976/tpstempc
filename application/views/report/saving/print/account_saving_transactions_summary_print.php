<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$transaction = isset($transaction) ? $transaction : array();
$account_names = array();

$format_cr_dr = function ($amount) {
    $amount = floatval($amount);
    if ($amount > 0) {
        return number_format($amount, 2) . ' Cr';
    }
    if ($amount < 0) {
        return number_format(abs($amount), 2) . ' Dr';
    }
    return number_format(0, 2);
};

$logo_src = '';
$logo_w = 50;
$logo_h = 50;
if (!empty($company->logo)) {
    $logo_file = FCPATH . 'logo' . DIRECTORY_SEPARATOR . $company->logo;
    if (is_file($logo_file)) {
        $logo_src = str_replace('\\', '/', $logo_file);
        $size = @getimagesize($logo_file);
        if ($size && !empty($size[0]) && !empty($size[1])) {
            $max = 50;
            if ($size[0] >= $size[1]) {
                $logo_w = $max;
                $logo_h = max(1, (int) round($max * ($size[1] / $size[0])));
            } else {
                $logo_h = $max;
                $logo_w = max(1, (int) round($max * ($size[0] / $size[1])));
            }
        }
    } else {
        $logo_src = base_url() . 'logo/' . $company->logo;
    }
}
?>
<div style="padding:0; margin:0;">
    <table style="width:100%; margin:0 0 6px 0; border-collapse:collapse;">
        <tr>
            <td style="width:<?php echo ($logo_w + 10); ?>px; vertical-align:middle; padding:0;">
                <?php if ($logo_src !== '') { ?>
                    <img src="<?php echo $logo_src; ?>" width="<?php echo (int) $logo_w; ?>" height="<?php echo (int) $logo_h; ?>" alt="logo"/>
                <?php } ?>
            </td>
            <td style="text-align:center; vertical-align:middle; padding:0 6px;">
                <div style="font-weight:bold; font-size:12px; text-transform:uppercase;"><?php echo htmlspecialchars($company->name); ?></div>
                <div style="font-size:10px;"><?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?></div>
                <div style="font-weight:bold; font-size:13px; margin-top:4px;">SAVINGS ACCOUNT TRANSACTIONS SUMMARY</div>
                <div style="font-size:10px;">For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?></div>
            </td>
            <td style="width:70px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:10px;">LENDING</td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:9px;">
        <thead>
            <tr>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:35px;">S/No</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px; width:80px;">Account No</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px; width:70px;">Member ID</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Account Name</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:90px;">Opening Balance</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:80px;">Debit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:80px;">Credit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:90px;">Closing Balance</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (empty($transaction)) {
            ?>
            <tr>
                <td colspan="8" style="text-align:center; padding:12px; font-style:italic;">
                    No savings transactions found for
                    <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?>.
                </td>
            </tr>
            <?php
        } else {
            $i = 1;
            $credit = 0;
            $debit = 0;
            $open_total = 0;
            $close_total = 0;
            foreach ($transaction as $value) {
                $acct = $value->account;
                if (!isset($account_names[$acct])) {
                    $account_names[$acct] = $this->finance_model->saving_account_name($acct);
                }
                $display_acct = !empty($value->display_account) ? $value->display_account : $acct;
                $member_id = !empty($value->member_id) ? $value->member_id : '';

                $balance_open = $this->report_model->account_saving_transactions_summary_previous($reportinfo->fromdate, $acct);
                $open_credit = $balance_open ? floatval($balance_open->credit) : 0;
                $open_debit = $balance_open ? floatval($balance_open->debit) : 0;
                $balance_tmp = $open_credit - $open_debit;

                $row_debit = floatval($value->debit);
                $row_credit = floatval($value->credit);
                $debit += $row_debit;
                $credit += $row_credit;
                $open_total += $balance_tmp;

                $close_balance = $balance_tmp - $row_debit + $row_credit;
                $close_total += $close_balance;
                ?>
                <tr>
                    <td style="text-align:center; padding:2px 3px;"><?php echo $i++; ?>.</td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($display_acct); ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($member_id); ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($account_names[$acct]); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo $format_cr_dr($balance_tmp); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo ($row_debit > 0 ? number_format($row_debit, 2) : ''); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo ($row_credit > 0 ? number_format($row_credit, 2) : ''); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo $format_cr_dr($close_balance); ?></td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="4"></td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <?php echo $format_cr_dr($open_total); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($debit, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($credit, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <?php echo $format_cr_dr($close_total); ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <table style="width:100%; margin-top:28px; font-size:10px;">
        <tr>
            <td style="width:33%; vertical-align:top; text-align:center;">
                Certified Correct:<br/>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                <span style="font-weight:bold; text-decoration:underline;">ANTONINA P. PATUNGAN</span><br/>
                Bookkeeper
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                Checked by:<br/>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                <span style="font-weight:bold; text-decoration:underline;">ANA MARIE F. VALMORIA</span><br/>
                AICOM
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                Noted by:<br/>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                <span style="font-weight:bold; text-decoration:underline;">REMEDIOS T. AUXTERO</span><br/>
                Manager
            </td>
        </tr>
    </table>
</div>
