<?php
if (!function_exists('cf_format_amount')) {
    function cf_format_amount($amount) {
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

$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$cf_rows = isset($cash_flow_data['rows']) ? $cash_flow_data['rows'] : array();
$cf_totals = isset($cash_flow_data['totals']) ? $cash_flow_data['totals'] : array();
$indent_px = array(0 => 0, 1 => 14, 2 => 28, 3 => 42, 4 => 56);

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
                <div style="font-weight:bold; font-size:14px; margin-top:4px; line-height:1.2;">STATEMENT OF CASH FLOWS</div>
                <div style="font-size:10px; font-style:italic; line-height:1.2;">Indirect Method</div>
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
        <?php foreach ($cf_rows as $row) {
            $type = $row['type'];
            if ($type === 'spacer') {
                echo '<tr><td colspan="2" style="height:10px;"></td></tr>';
                continue;
            }
            $indent = isset($indent_px[$row['indent']]) ? $indent_px[$row['indent']] : ($row['indent'] * 14);
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
                $label_style .= 'padding-top:8px;';
            }
            $amt_style = 'text-align:right; white-space:nowrap; padding:2px 4px; width:120px;';
            if (!empty($row['bold'])) {
                $amt_style .= 'font-weight:bold;';
            }
            if (!empty($row['line'])) {
                $amt_style .= 'border-top:1px solid #000;';
            }

            $amount_html = '';
            if ($has_amount) {
                $formatted = cf_format_amount($row['amount']);
                if (!empty($row['peso']) && $formatted !== '-') {
                    $amount_html = '<span style="float:left;">P</span>' . $formatted;
                } else {
                    $amount_html = $formatted;
                }
                if (!empty($row['line']) && $row['line'] === 'double') {
                    $amount_html = '<div style="border-bottom:3px double #000; display:inline-block; min-width:110px; text-align:right;">' . $amount_html . '</div>';
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

    <?php
    $end = isset($cf_totals['ending_cash']) ? floatval($cf_totals['ending_cash']) : 0;
    $recon = isset($cf_totals['ending_cash_reconciled']) ? floatval($cf_totals['ending_cash_reconciled']) : 0;
    if (abs($end - $recon) >= 0.05) {
        ?>
        <div style="margin-top:8px; font-size:10px;">
            Note: Cash end and beginning + net change differ by <?php echo number_format(abs($end - $recon), 2); ?>.
        </div>
    <?php } ?>

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
