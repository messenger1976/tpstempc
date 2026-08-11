<?php
if (!function_exists('fc_format_amount')) {
    function fc_format_amount($amount, $is_less = false) {
        if ($amount === null) {
            return '';
        }
        $v = floatval($amount);
        if (abs($v) < 0.005) {
            return '-';
        }
        if ($is_less && $v > 0) {
            $v = -$v;
        }
        if ($v < 0) {
            return '(' . number_format(abs($v), 2) . ')';
        }
        return number_format($v, 2);
    }
}

$fc_rows = isset($fc_data['rows']) ? $fc_data['rows'] : array();
$company = company_info();
$as_of = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$indent_px = array(0 => 0, 1 => 18, 2 => 36, 3 => 54, 4 => 72);
?>
<div class="row">
    <div class="col-lg-12">
        <div style="padding: 20px 10px; margin: auto; max-width: 900px;">
            <table style="width:100%; margin-bottom: 8px;">
                <tr>
                    <td style="width:70px; vertical-align:top;">
                        <?php if (!empty($company->logo)) { ?>
                            <img src="<?php echo base_url() . 'logo/' . $company->logo; ?>" style="height:60px;" alt="logo"/>
                        <?php } ?>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <div style="font-weight:bold; font-size:14px; text-transform:uppercase;">
                            <?php echo htmlspecialchars($company->name); ?>
                        </div>
                        <div style="font-size:12px;">
                            <?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?>
                        </div>
                        <div style="font-weight:bold; font-size:15px; margin-top:8px;">
                            CONSOLIDATED STATEMENT OF FINANCIAL CONDITION
                        </div>
                        <div style="font-size:13px;">As of <?php echo $as_of; ?></div>
                    </td>
                    <td style="width:90px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:12px;">
                        LENDING
                    </td>
                </tr>
            </table>

            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <tbody>
                <?php foreach ($fc_rows as $row) {
                    $type = $row['type'];
                    if ($type === 'spacer') {
                        echo '<tr><td colspan="2" style="height:14px;"></td></tr>';
                        continue;
                    }
                    $indent = isset($indent_px[$row['indent']]) ? $indent_px[$row['indent']] : ($row['indent'] * 18);
                    $has_amount = array_key_exists('amount', $row) && $row['amount'] !== null;
                    $show = !empty($row['always_show']) || $type === 'section' || $type === 'group' || $type === 'subtotal' || $type === 'total'
                        || ($has_amount && abs(floatval($row['amount'])) >= 0.005);
                    if (!$show) {
                        continue;
                    }
                    $label_style = 'padding:2px 4px 2px ' . $indent . 'px;';
                    if (!empty($row['bold'])) {
                        $label_style .= 'font-weight:bold;';
                    }
                    if (!empty($row['italic'])) {
                        $label_style .= 'font-style:italic;';
                    }
                    if ($type === 'section') {
                        $label_style .= 'text-transform:uppercase; padding-top:8px;';
                    }
                    $amt_style = 'text-align:right; white-space:nowrap; padding:2px 4px; width:140px;';
                    if (!empty($row['bold'])) {
                        $amt_style .= 'font-weight:bold;';
                    }
                    if (!empty($row['italic'])) {
                        $amt_style .= 'font-style:italic;';
                    }
                    if (!empty($row['line'])) {
                        $amt_style .= 'border-top:1px solid #000;';
                    }
                    $amount_html = '';
                    if ($has_amount) {
                        $formatted = fc_format_amount($row['amount'], !empty($row['is_less']));
                        if (!empty($row['peso']) && $formatted !== '-') {
                            $amount_html = '<span style="float:left;">P</span>' . $formatted;
                        } else {
                            $amount_html = $formatted;
                        }
                        if (!empty($row['line']) && $row['line'] === 'double') {
                            $amount_html = '<div style="border-bottom:3px double #000; display:inline-block; min-width:120px; text-align:right;">' . $amount_html . '</div>';
                        }
                    }
                    ?>
                    <tr>
                        <td style="<?php echo $label_style; ?>"><?php echo htmlspecialchars($row['label']); ?></td>
                        <td style="<?php echo $amt_style; ?>"><?php echo $amount_html; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <?php if (!empty($fc_data['totals']['difference']) && abs($fc_data['totals']['difference']) >= 0.05) { ?>
                <div style="margin-top:12px; color:#a94442; font-size:12px;">
                    Note: Assets and Liabilities &amp; Equities differ by
                    <?php echo number_format($fc_data['totals']['difference'], 2); ?>.
                    Review unposted journals or unmapped accounts.
                </div>
            <?php } ?>

            <table style="width:100%; margin-top:40px; font-size:12px;">
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
        <div style="text-align: center; margin-top: 20px;">
            <a href="<?php echo site_url(current_lang() . '/report/ledger_financial_condition_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/create_ledger_trans_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/general_leger_transaction/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>
