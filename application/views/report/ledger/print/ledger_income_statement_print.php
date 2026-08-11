<?php
if (!function_exists('is_format_amount')) {
    function is_format_amount($amount) {
        $v = floatval($amount);
        if (abs($v) < 0.005) {
            return '-';
        }
        if ($v < 0) {
            return '(' . number_format(abs($v), 2) . ')';
        }
        return number_format($v, 2);
    }
}

$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';

$logo_src = '';
$logo_w = 55;
$logo_h = 55;
if (!empty($company->logo)) {
    $logo_file = FCPATH . 'logo' . DIRECTORY_SEPARATOR . $company->logo;
    if (is_file($logo_file)) {
        $logo_src = str_replace('\\', '/', $logo_file);
        $size = @getimagesize($logo_file);
        if ($size && !empty($size[0]) && !empty($size[1])) {
            $max = 55;
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

$transaction = $this->report_model->create_ledger_trans_summary($reportinfo->fromdate, $reportinfo->todate);
$total_income = 0;
$total_expenses = 0;
$check_exp_inc = 0;
?>
<div style="padding: 0; margin: 0;">
    <table style="width:100%; margin:0 0 6px 0; border-collapse:collapse;">
        <tr>
            <td style="width:<?php echo ($logo_w + 10); ?>px; vertical-align:middle; padding:0; text-align:left;">
                <?php if ($logo_src !== '') { ?>
                    <img src="<?php echo $logo_src; ?>" width="<?php echo (int) $logo_w; ?>" height="<?php echo (int) $logo_h; ?>" alt="logo"/>
                <?php } ?>
            </td>
            <td style="text-align:center; vertical-align:middle; padding:0 6px;">
                <div style="font-weight:bold; font-size:13px; text-transform:uppercase; line-height:1.25;">
                    <?php echo htmlspecialchars($company->name); ?>
                </div>
                <div style="font-size:11px; line-height:1.2;">
                    <?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?>
                </div>
                <div style="font-weight:bold; font-size:14px; margin-top:4px; line-height:1.2;">INCOME STATEMENT</div>
                <div style="font-size:10px; font-style:italic; line-height:1.2;">Statement of Financial Operations</div>
                <div style="font-size:11px; line-height:1.2;">
                    For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?>
                </div>
            </td>
            <td style="width:80px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:11px; padding:0;">
                LENDING
            </td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:11px;">
        <tbody>
        <?php if (array_key_exists(4, $transaction)) {
            $check_exp_inc = 1;
            ?>
            <tr>
                <td style="padding:8px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="2">
                    Revenues &amp; Gains
                </td>
            </tr>
            <?php
            foreach ($transaction[4] as $key1 => $value1) {
                $account_info = $this->finance_model->account_chart(null, $key1)->row();
                if (!$account_info) {
                    continue;
                }
                $tmp = 0;
                if (isset($value1['current']) && is_object($value1['current']) && isset($value1['current']->credit) && isset($value1['current']->debit)) {
                    $tmp = $value1['current']->credit - $value1['current']->debit;
                }
                $total_income += $tmp;
                ?>
                <tr>
                    <td style="padding:2px 4px 2px 28px;"><?php echo htmlspecialchars($account_info->name); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 4px; width:120px;">
                        <?php echo is_format_amount($tmp); ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td style="padding:4px 4px 2px 42px; font-weight:bold;">Total Revenues &amp; Gains</td>
                <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                    <span style="float:left;">P</span><?php echo is_format_amount($total_income); ?>
                </td>
            </tr>
        <?php } ?>

        <?php if (array_key_exists(5, $transaction)) {
            $check_exp_inc = 1;
            ?>
            <tr>
                <td style="padding:12px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="2">
                    Expenses &amp; Losses
                </td>
            </tr>
            <?php
            foreach ($transaction[5] as $key1 => $value1) {
                $account_info = $this->finance_model->account_chart(null, $key1)->row();
                if (!$account_info) {
                    continue;
                }
                $tmp = 0;
                if (isset($value1['current']) && is_object($value1['current']) && isset($value1['current']->credit) && isset($value1['current']->debit)) {
                    $tmp = $value1['current']->debit - $value1['current']->credit;
                }
                $total_expenses += $tmp;
                ?>
                <tr>
                    <td style="padding:2px 4px 2px 28px;"><?php echo htmlspecialchars($account_info->name); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 4px; width:120px;">
                        <?php echo is_format_amount($tmp); ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td style="padding:4px 4px 2px 42px; font-weight:bold;">Total Expenses &amp; Losses</td>
                <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                    <span style="float:left;">P</span><?php echo is_format_amount($total_expenses); ?>
                </td>
            </tr>
        <?php } ?>

        <?php
        $close_balance = $total_income - $total_expenses;
        if ($check_exp_inc == 1) {
            echo '<tr><td colspan="2" style="height:14px;"></td></tr>';
        }
        ?>
            <tr>
                <td style="padding:4px; font-weight:bold; border-top:1px solid #000;">Net Surplus (Loss)</td>
                <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                    <div style="border-bottom:3px double #000; display:inline-block; min-width:110px; text-align:right;">
                        <span style="float:left;">P</span><?php echo is_format_amount($close_balance); ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width:100%; margin-top:36px; font-size:11px; border-collapse:collapse;">
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
