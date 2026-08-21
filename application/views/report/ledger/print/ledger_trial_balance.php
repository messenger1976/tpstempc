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

$tb_section_order = array();
if (isset($transaction[4])) {
    $tb_section_order[] = array(4, 'Income');
}
if (isset($transaction[5])) {
    $tb_section_order[] = array(5, 'Expenses');
}
foreach ($transaction as $type_id => $_unused) {
    if ($type_id == 4 || $type_id == 5) {
        continue;
    }
    $type_account = $this->finance_model->account_typelist($type_id)->row();
    if ($type_account) {
        $tb_section_order[] = array($type_id, $type_account->name);
    }
}
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
        <?php foreach ($tb_section_order as $section) {
            $type_id = $section[0];
            $section_title = $section[1];
            if (empty($transaction[$type_id])) {
                continue;
            }
            $section_rows = '';
            $has_row = false;
            foreach ($transaction[$type_id] as $key1 => $value1) {
                $account_info = $this->finance_model->account_chart(null, $key1)->row();
                if (!$account_info) {
                    continue;
                }
                $sides = $this->report_model->trial_balance_ending_sides($value1);
                if ($sides['debit'] <= 0 && $sides['credit'] <= 0) {
                    continue;
                }
                $has_row = true;
                $total_debit += $sides['debit'];
                $total_credit += $sides['credit'];
                $section_rows .= '<tr>'
                    . '<td style="padding:2px 4px 2px 28px;">' . htmlspecialchars($account_info->name) . '</td>'
                    . '<td style="text-align:right; white-space:nowrap; padding:2px 4px;">' . tb_format_amt($sides['debit']) . '</td>'
                    . '<td style="text-align:right; white-space:nowrap; padding:2px 4px;">' . tb_format_amt($sides['credit']) . '</td>'
                    . '</tr>';
            }
            if (!$has_row) {
                continue;
            }
            ?>
            <tr>
                <td style="padding:8px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="3">
                    <?php echo htmlspecialchars($section_title); ?>
                </td>
            </tr>
            <?php echo $section_rows;
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
