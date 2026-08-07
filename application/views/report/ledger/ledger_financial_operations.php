<?php
if (!function_exists('fo_format_amount')) {
    function fo_format_amount($amount) {
        if ($amount === null) {
            return '';
        }
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

$fo_rows = isset($fo_data['rows']) ? $fo_data['rows'] : array();
$periods = isset($fo_data['periods']) ? $fo_data['periods'] : array();
$company = company_info();
$indent_px = array(0 => 0, 1 => 18, 2 => 36, 3 => 54);
$col_w = '110px';
?>
<div class="row">
    <div class="col-lg-12">
        <div style="padding: 20px 8px; margin: auto; max-width: 1100px; overflow-x: auto;">
            <table style="width:100%; margin-bottom: 8px;">
                <tr>
                    <td style="width:70px; vertical-align:top;">
                        <?php if (!empty($company->logo)) { ?>
                            <img src="<?php echo base_url() . 'logo/' . $company->logo; ?>" style="height:60px;" alt="logo"/>
                        <?php } ?>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <div style="font-weight:bold; font-size:13px; text-transform:uppercase;">
                            <?php echo htmlspecialchars($company->name); ?>
                        </div>
                        <div style="font-size:12px;">
                            <?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?>
                        </div>
                        <div style="font-weight:bold; font-size:14px; margin-top:8px;">
                            COMPARATIVE STATEMENT OF FINANCIAL OPERATIONS - LENDING
                        </div>
                    </td>
                    <td style="width:70px;"></td>
                </tr>
            </table>

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:900px;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:4px; border-bottom:1px solid #000;"></th>
                        <th style="text-align:center; padding:4px; border-bottom:1px solid #000; width:<?php echo $col_w; ?>; font-size:10px;">
                            <?php echo isset($periods['ytd_label']) ? htmlspecialchars($periods['ytd_label']) : 'YTD Total'; ?>
                        </th>
                        <th style="text-align:center; padding:4px; border-bottom:1px solid #000; width:<?php echo $col_w; ?>; font-size:10px;">
                            <?php echo isset($periods['month_label']) ? htmlspecialchars($periods['month_label']) : 'Current Month'; ?>
                        </th>
                        <th style="text-align:center; padding:4px; border-bottom:1px solid #000; width:<?php echo $col_w; ?>; font-size:10px;">
                            <?php echo isset($periods['prior_label']) ? htmlspecialchars($periods['prior_label']) : 'Previous Mo.'; ?>
                        </th>
                    </tr>
                    <tr>
                        <th></th>
                        <th style="text-align:right; padding:2px 4px;">P</th>
                        <th style="text-align:right; padding:2px 4px;">P</th>
                        <th style="text-align:right; padding:2px 4px;">P</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($fo_rows as $row) {
                    $type = $row['type'];
                    if ($type === 'spacer') {
                        echo '<tr><td colspan="4" style="height:10px;"></td></tr>';
                        continue;
                    }
                    $indent = isset($indent_px[$row['indent']]) ? $indent_px[$row['indent']] : ($row['indent'] * 18);
                    $amounts = isset($row['amounts']) ? $row['amounts'] : null;
                    $has_amt = is_array($amounts);
                    $show = !empty($row['always_show']) || $type === 'section' || $type === 'group' || $type === 'subtotal' || $type === 'total'
                        || ($has_amt && (abs($amounts['ytd']) >= 0.005 || abs($amounts['month']) >= 0.005 || abs($amounts['prior']) >= 0.005));
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
                    $amt_style = 'text-align:right; white-space:nowrap; padding:2px 4px; width:' . $col_w . ';';
                    if (!empty($row['bold'])) {
                        $amt_style .= 'font-weight:bold;';
                    }
                    if (!empty($row['line'])) {
                        $amt_style .= 'border-top:1px solid #000;';
                    }
                    $cell = function ($v) use ($row, $has_amt) {
                        if (!$has_amt) {
                            return '';
                        }
                        $formatted = fo_format_amount($v);
                        if (!empty($row['peso']) && $formatted !== '-') {
                            $formatted = 'P ' . $formatted;
                        }
                        if (!empty($row['line']) && $row['line'] === 'double') {
                            return '<div style="border-bottom:3px double #000; display:inline-block; min-width:90px; text-align:right;">' . $formatted . '</div>';
                        }
                        return $formatted;
                    };
                    ?>
                    <tr>
                        <td style="<?php echo $label_style; ?>"><?php echo htmlspecialchars($row['label']); ?></td>
                        <td style="<?php echo $amt_style; ?>"><?php echo $has_amt ? $cell($amounts['ytd']) : ''; ?></td>
                        <td style="<?php echo $amt_style; ?>"><?php echo $has_amt ? $cell($amounts['month']) : ''; ?></td>
                        <td style="<?php echo $amt_style; ?>"><?php echo $has_amt ? $cell($amounts['prior']) : ''; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <table style="width:100%; margin-top:36px; font-size:12px;">
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
            <a href="<?php echo site_url(current_lang() . '/report/ledger_financial_operations_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/create_ledger_trans_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/general_leger_transaction/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>
