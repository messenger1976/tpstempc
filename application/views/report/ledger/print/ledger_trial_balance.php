<?php
if (!function_exists('tb_format_amt')) {
    function tb_format_amt($amount) {
        $v = floatval($amount);
        if ($v > 0) {
            return number_format($v, 2);
        }
        return '-';
    }
}

$company = company_info();
$as_at = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';

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
$total_credit = 0;
$total_debit = 0;
$net_prfit_credit = 0;
$net_prfit_debit = 0;
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
                <div style="font-weight:bold; font-size:14px; margin-top:4px; line-height:1.2;">TRIAL BALANCE</div>
                <div style="font-size:11px; line-height:1.2;">As at <?php echo $as_at; ?></div>
            </td>
            <td style="width:80px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:11px; padding:0;">
                LENDING
            </td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:11px;">
        <thead>
            <tr>
                <th style="text-align:left; border-bottom:1px solid #000; padding:4px;"></th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:120px;">Debit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:120px;">Credit</th>
            </tr>
        </thead>
        <tbody>
        <?php if (array_key_exists(4, $transaction)) {
            $check_exp_inc = 1;
            ?>
            <tr>
                <td style="padding:8px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="3">Income</td>
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
                ?>
                <tr>
                    <td style="padding:2px 4px 2px 28px;"><?php echo htmlspecialchars($account_info->name); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo tb_format_amt($debit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo tb_format_amt($credit); ?></td>
                </tr>
            <?php }
            unset($transaction[4]);
        }

        if (array_key_exists(5, $transaction)) {
            $check_exp_inc = 1;
            ?>
            <tr>
                <td style="padding:12px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="3">Expenses</td>
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
                ?>
                <tr>
                    <td style="padding:2px 4px 2px 28px;"><?php echo htmlspecialchars($account_info->name); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo tb_format_amt($debit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo tb_format_amt($credit); ?></td>
                </tr>
            <?php }
            unset($transaction[5]);
        }

        $close_balance = $net_prfit_debit - $net_prfit_credit;
        $balance_credit = 0;
        $balance_debit = 0;
        if ($close_balance > 0) {
            $balance_credit += $close_balance;
            $total_credit += $close_balance;
        } else if ($close_balance < 0) {
            $balance_debit += (-1 * $close_balance);
            $total_debit += (-1 * $close_balance);
        }
        if ($check_exp_inc == 1) {
            echo '<tr><td colspan="3" style="height:8px;"></td></tr>';
        }
        ?>
            <tr>
                <td style="padding:4px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Net Surplus (Loss)</td>
                <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                    <?php echo number_format($balance_debit, 2); ?>
                </td>
                <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                    <?php echo number_format($balance_credit, 2); ?>
                </td>
            </tr>
            <tr><td colspan="3" style="height:10px;"></td></tr>

        <?php foreach ($transaction as $key => $value) {
            $type_account = $this->finance_model->account_typelist($key)->row();
            if (!$type_account) {
                continue;
            }
            ?>
            <tr>
                <td style="padding:8px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="3">
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
                if ($open_balance > 0) {
                    $sub_debit += $open_balance;
                    $total_debit += $open_balance;
                } else if ($open_balance < 0) {
                    $sub_credit += (-1 * $open_balance);
                    $total_credit += (-1 * $open_balance);
                }
                if (!empty($value1['current']) && is_object($value1['current'])) {
                    $sub_credit += floatval($value1['current']->credit);
                    $sub_debit += floatval($value1['current']->debit);
                    $total_debit += floatval($value1['current']->debit);
                    $total_credit += floatval($value1['current']->credit);
                }
                ?>
                <tr>
                    <td style="padding:2px 4px 2px 28px;"><?php echo htmlspecialchars($account_info->name); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo tb_format_amt($sub_debit); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo tb_format_amt($sub_credit); ?></td>
                </tr>
            <?php }
        } ?>

            <tr>
                <td style="padding:6px 4px; font-weight:bold; border-top:1px solid #000;">Totals</td>
                <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                    <span style="float:left;">P</span><?php echo number_format($total_debit, 2); ?>
                </td>
                <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                    <span style="float:left;">P</span><?php echo number_format($total_credit, 2); ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align:right; padding:0 4px;">
                    <div style="border-bottom:3px double #000; display:inline-block; min-width:110px;">&nbsp;</div>
                </td>
                <td style="text-align:right; padding:0 4px;">
                    <div style="border-bottom:3px double #000; display:inline-block; min-width:110px;">&nbsp;</div>
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
