<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$transaction = isset($transaction) ? $transaction : array();
$account_names = array();

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
                <div style="font-weight:bold; font-size:13px; margin-top:4px;">SAVINGS ACCOUNT TRANSACTIONS</div>
                <div style="font-size:10px;">For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?></div>
            </td>
            <td style="width:70px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:10px;">LENDING</td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:9px;">
        <thead>
            <tr>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:35px;">S/No</th>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:65px;">Date</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px; width:80px;">Account No</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Account Name</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Particulars</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px; width:65px;">Method</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:80px;">Debit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:80px;">Credit</th>
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
            foreach ($transaction as $value) {
                $credit += floatval($value->credit);
                $debit += floatval($value->debit);
                $dt = explode(' ', $value->trans_date);
                $acct = $value->account;
                if (!isset($account_names[$acct])) {
                    $account_names[$acct] = $this->finance_model->saving_account_name($acct);
                }
                $display_acct = !empty($value->display_account) ? $value->display_account : $acct;
                $particulars = trim(
                    (isset($value->system_comment) ? $value->system_comment : '')
                    . (isset($value->comment) && $value->comment !== '' ? ' — ' . $value->comment : '')
                );
                ?>
                <tr>
                    <td style="text-align:center; padding:2px 3px;"><?php echo $i++; ?>.</td>
                    <td style="text-align:center; padding:2px 3px; white-space:nowrap;"><?php echo format_date($dt[0], false); ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($display_acct); ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($account_names[$acct]); ?></td>
                    <td style="padding:2px 3px;"><?php echo $particulars !== '' ? htmlspecialchars($particulars) : '&mdash;'; ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($value->paymethod); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo (floatval($value->debit) > 0 ? number_format($value->debit, 2) : ''); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo (floatval($value->credit) > 0 ? number_format($value->credit, 2) : ''); ?></td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="6"></td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($debit, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($credit, 2); ?>
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
                <span style="font-weight:bold; text-decoration:underline;">ANTONINA P. PATUNGAN</span><br/>Bookkeeper
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                Checked by:<br/>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                <span style="font-weight:bold; text-decoration:underline;">ANA MARIE F. VALMORIA</span><br/>AICOM
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                Noted by:<br/>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                <span style="font-weight:bold; text-decoration:underline;">REMEDIOS T. AUXTERO</span><br/>Manager
            </td>
        </tr>
    </table>
</div>
