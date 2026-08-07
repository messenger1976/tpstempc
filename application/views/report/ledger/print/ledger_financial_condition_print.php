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
$as_of = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$indent_px = array(0 => 0, 1 => 14, 2 => 28, 3 => 42, 4 => 56);
?>
<div style="padding: 0; margin: auto;">
    <div style="text-align:center; margin-bottom:8px;">
        <div style="font-weight:bold; font-size:14px;">CONSOLIDATED STATEMENT OF FINANCIAL CONDITION</div>
        <div style="font-size:12px;">As of <?php echo $as_of; ?></div>
        <div style="text-align:right; font-weight:bold; font-size:11px; margin-top:4px;">LENDING</div>
    </div>

    <table style="width:100%; border-collapse:collapse; font-size:11px;">
        <tbody>
        <?php foreach ($fc_rows as $row) {
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
            $label_style = 'padding:1px 2px 1px ' . $indent . 'px;';
            if (!empty($row['bold'])) {
                $label_style .= 'font-weight:bold;';
            }
            if (!empty($row['italic'])) {
                $label_style .= 'font-style:italic;';
            }
            if ($type === 'section') {
                $label_style .= 'text-transform:uppercase; padding-top:6px;';
            }
            $amt_style = 'text-align:right; white-space:nowrap; padding:1px 2px; width:120px;';
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
                    $amount_html = 'P ' . $formatted;
                } else {
                    $amount_html = $formatted;
                }
                if (!empty($row['line']) && $row['line'] === 'double') {
                    $amount_html = '<div style="border-bottom:3px double #000;">' . $amount_html . '</div>';
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

    <table style="width:100%; margin-top:30px; font-size:11px;">
        <tr>
            <td style="width:33%; vertical-align:top; text-align:center;">
                <div>Certified Correct:</div>
                <div style="height:30px;"></div>
                <div style="font-weight:bold; text-decoration:underline;">ANTONINA P. PATUNGAN</div>
                <div>Bookkeeper</div>
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                <div>Checked by:</div>
                <div style="height:30px;"></div>
                <div style="font-weight:bold; text-decoration:underline;">ANA MARIE F. VALMORIA</div>
                <div>AICOM</div>
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                <div>Noted by:</div>
                <div style="height:30px;"></div>
                <div style="font-weight:bold; text-decoration:underline;">REMEDIOS T. AUXTERO</div>
                <div>Manager</div>
            </td>
        </tr>
    </table>
</div>
