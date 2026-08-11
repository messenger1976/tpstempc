<?php
if (!function_exists('gls_format_amt')) {
    function gls_format_amt($amount) {
        $v = floatval($amount);
        if ($v > 0) {
            return number_format($v, 2);
        }
        return '-';
    }
}
if (!function_exists('gls_dr_cr_label')) {
    function gls_dr_cr_label($amount) {
        $v = floatval($amount);
        if ($v > 0) {
            return number_format($v, 2) . ' Dr';
        }
        if ($v < 0) {
            return number_format(abs($v), 2) . ' Cr';
        }
        return '-';
    }
}

$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';

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

$transaction = $this->report_model->create_ledger_trans_summary($reportinfo->fromdate, $reportinfo->todate);
$total_credit = 0;
$total_debit = 0;
$net_prfit_credit = 0;
$net_prfit_debit = 0;
$check_exp_inc = 0;
$col_w = '85px';
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
                <div style="font-weight:bold; font-size:12px; text-transform:uppercase; line-height:1.25;">
                    <?php echo htmlspecialchars($company->name); ?>
                </div>
                <div style="font-size:10px; line-height:1.2;">
                    <?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?>
                </div>
                <div style="font-weight:bold; font-size:13px; margin-top:4px; line-height:1.2;">GENERAL LEDGER SUMMARY</div>
                <div style="font-size:10px; line-height:1.2;">
                    For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?>
                </div>
            </td>
            <td style="width:70px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:10px; padding:0;">
                LENDING
            </td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:10px;">
        <thead>
            <tr>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;"></th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:<?php echo $col_w; ?>;">Opening</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:<?php echo $col_w; ?>;">Debit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:<?php echo $col_w; ?>;">Credit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:<?php echo $col_w; ?>;">Net Movement</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:<?php echo $col_w; ?>;">Closing</th>
            </tr>
        </thead>
        <tbody>
        <?php if (array_key_exists(4, $transaction)) {
            $check_exp_inc = 1;
            ?>
            <tr>
                <td style="padding:8px 3px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="6">Income</td>
            </tr>
            <?php
            foreach ($transaction[4] as $key1 => $value1) {
                $account_info = $this->finance_model->account_chart(null, $key1)->row();
                if (!$account_info) {
                    continue;
                }
                $debit = 0;
                $credit = 0;
                if (!empty($value1['current']) && is_object($value1['current'])) {
                    $debit = floatval($value1['current']->debit);
                    $credit = floatval($value1['current']->credit);
                    $net_prfit_debit += $debit;
                    $net_prfit_credit += $credit;
                    $total_debit += $debit;
                    $total_credit += $credit;
                }
                $net_move = $debit - $credit;
                ?>
                <tr>
                    <td style="padding:2px 3px 2px 20px;"><?php echo htmlspecialchars($account_info->name); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;">-</td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_format_amt($debit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_format_amt($credit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_dr_cr_label($net_move); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_dr_cr_label($net_move); ?></td>
                </tr>
            <?php }
            unset($transaction[4]);
        }

        if (array_key_exists(5, $transaction)) {
            $check_exp_inc = 1;
            ?>
            <tr>
                <td style="padding:10px 3px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="6">Expenses</td>
            </tr>
            <?php
            foreach ($transaction[5] as $key1 => $value1) {
                $account_info = $this->finance_model->account_chart(null, $key1)->row();
                if (!$account_info) {
                    continue;
                }
                $debit = 0;
                $credit = 0;
                if (!empty($value1['current']) && is_object($value1['current'])) {
                    $debit = floatval($value1['current']->debit);
                    $credit = floatval($value1['current']->credit);
                    $net_prfit_debit += $debit;
                    $net_prfit_credit += $credit;
                    $total_debit += $debit;
                    $total_credit += $credit;
                }
                $net_move = $debit - $credit;
                ?>
                <tr>
                    <td style="padding:2px 3px 2px 20px;"><?php echo htmlspecialchars($account_info->name); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;">-</td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_format_amt($debit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_format_amt($credit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_dr_cr_label($net_move); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_dr_cr_label($net_move); ?></td>
                </tr>
            <?php }
            unset($transaction[5]);
        }

        if ($check_exp_inc == 1) {
            $close_balance = $net_prfit_debit - $net_prfit_credit;
            $balance_credit = 0;
            $balance_debit = 0;
            $close_balance_label = '-';
            if ($close_balance > 0) {
                $close_balance_label = number_format($close_balance, 2) . ' Cr';
                $balance_credit = $close_balance;
                $total_credit += $close_balance;
            } else if ($close_balance < 0) {
                $close_balance_label = number_format(abs($close_balance), 2) . ' Dr';
                $balance_debit = abs($close_balance);
                $total_debit += abs($close_balance);
            }
            ?>
            <tr>
                <td style="padding:3px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Net Surplus (Loss)</td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000;"></td>
                <td style="text-align:right; white-space:nowrap; padding:2px 3px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                    <?php echo number_format($balance_debit, 2); ?>
                </td>
                <td style="text-align:right; white-space:nowrap; padding:2px 3px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                    <?php echo number_format($balance_credit, 2); ?>
                </td>
                <td style="text-align:right; white-space:nowrap; padding:2px 3px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                    <?php echo $close_balance_label; ?>
                </td>
                <td style="text-align:right; white-space:nowrap; padding:2px 3px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                    <?php echo $close_balance_label; ?>
                </td>
            </tr>
            <tr><td colspan="6" style="height:10px;"></td></tr>
        <?php }

        foreach ($transaction as $key => $value) {
            $type_account = $this->finance_model->account_typelist($key)->row();
            if (!$type_account) {
                continue;
            }
            ?>
            <tr>
                <td style="padding:8px 3px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="6">
                    <?php echo htmlspecialchars($type_account->name); ?>
                </td>
            </tr>
            <?php
            foreach ($value as $key1 => $value1) {
                $account_info = $this->finance_model->account_chart(null, $key1)->row();
                if (!$account_info) {
                    continue;
                }
                $sub_credit = 0;
                $sub_debit = 0;
                $open_balance = isset($value1['balance']) ? floatval($value1['balance']) : 0;
                $open_balance_label = gls_dr_cr_label($open_balance);
                if ($open_balance > 0) {
                    $sub_debit += $open_balance;
                } else if ($open_balance < 0) {
                    $sub_credit += abs($open_balance);
                }
                $period_debit = 0;
                $period_credit = 0;
                if (!empty($value1['current']) && is_object($value1['current'])) {
                    $period_debit = floatval($value1['current']->debit);
                    $period_credit = floatval($value1['current']->credit);
                    $sub_credit += $period_credit;
                    $sub_debit += $period_debit;
                    $total_debit += $period_debit;
                    $total_credit += $period_credit;
                }
                $close_balance = $sub_debit - $sub_credit;
                ?>
                <tr>
                    <td style="padding:2px 3px 2px 20px;"><?php echo htmlspecialchars($account_info->name); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo $open_balance_label; ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_format_amt($period_debit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_format_amt($period_credit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_dr_cr_label($period_debit - $period_credit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo gls_dr_cr_label($close_balance); ?></td>
                </tr>
            <?php }
        } ?>

            <tr>
                <td style="padding:5px 3px; font-weight:bold; border-top:1px solid #000;">Totals</td>
                <td style="border-top:1px solid #000;"></td>
                <td style="text-align:right; white-space:nowrap; padding:2px 3px; font-weight:bold; border-top:1px solid #000;">
                    <span style="float:left;">P</span><?php echo number_format($total_debit, 2); ?>
                </td>
                <td style="text-align:right; white-space:nowrap; padding:2px 3px; font-weight:bold; border-top:1px solid #000;">
                    <span style="float:left;">P</span><?php echo number_format($total_credit, 2); ?>
                </td>
                <td style="border-top:1px solid #000;"></td>
                <td style="border-top:1px solid #000;"></td>
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
