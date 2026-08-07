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
$indent_px = array(0 => 0, 1 => 12, 2 => 24, 3 => 36);
$col_w = '95px';
?>
<div style="padding:0; margin:auto;">
    <div style="text-align:center; margin-bottom:6px;">
        <div style="font-weight:bold; font-size:13px;">COMPARATIVE STATEMENT OF FINANCIAL OPERATIONS - LENDING</div>
    </div>

    <table style="width:100%; border-collapse:collapse; font-size:10px;">
        <thead>
            <tr>
                <th style="text-align:left; padding:2px; border-bottom:1px solid #000;"></th>
                <th style="text-align:center; padding:2px; border-bottom:1px solid #000; width:<?php echo $col_w; ?>; font-size:8px;">
                    <?php echo isset($periods['ytd_label']) ? htmlspecialchars($periods['ytd_label']) : 'YTD Total'; ?>
                </th>
                <th style="text-align:center; padding:2px; border-bottom:1px solid #000; width:<?php echo $col_w; ?>; font-size:8px;">
                    <?php echo isset($periods['month_label']) ? htmlspecialchars($periods['month_label']) : 'Current Month'; ?>
                </th>
                <th style="text-align:center; padding:2px; border-bottom:1px solid #000; width:<?php echo $col_w; ?>; font-size:8px;">
                    <?php echo isset($periods['prior_label']) ? htmlspecialchars($periods['prior_label']) : 'Previous Mo.'; ?>
                </th>
            </tr>
            <tr>
                <th></th>
                <th style="text-align:right; padding:1px 2px;">P</th>
                <th style="text-align:right; padding:1px 2px;">P</th>
                <th style="text-align:right; padding:1px 2px;">P</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($fo_rows as $row) {
            $type = $row['type'];
            if ($type === 'spacer') {
                echo '<tr><td colspan="4" style="height:8px;"></td></tr>';
                continue;
            }
            $indent = isset($indent_px[$row['indent']]) ? $indent_px[$row['indent']] : ($row['indent'] * 12);
            $amounts = isset($row['amounts']) ? $row['amounts'] : null;
            $has_amt = is_array($amounts);
            $show = !empty($row['always_show']) || $type === 'section' || $type === 'group' || $type === 'subtotal' || $type === 'total'
                || ($has_amt && (abs($amounts['ytd']) >= 0.005 || abs($amounts['month']) >= 0.005 || abs($amounts['prior']) >= 0.005));
            if (!$show) {
                continue;
            }
            $label_style = 'padding:1px 2px 1px ' . $indent . 'px;';
            if (!empty($row['bold'])) {
                $label_style .= 'font-weight:bold;';
            }
            if ($type === 'section') {
                $label_style .= 'text-transform:uppercase; padding-top:5px;';
            }
            $amt_style = 'text-align:right; white-space:nowrap; padding:1px 2px; width:' . $col_w . ';';
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
                    return '<div style="border-bottom:3px double #000;">' . $formatted . '</div>';
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

    <table style="width:100%; margin-top:24px; font-size:10px;">
        <tr>
            <td style="width:33%; vertical-align:top; text-align:center;">
                <div>Certified Correct:</div>
                <div style="height:28px;"></div>
                <div style="font-weight:bold; text-decoration:underline;">ANTONINA P. PATUNGAN</div>
                <div>Bookkeeper</div>
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                <div>Checked by:</div>
                <div style="height:28px;"></div>
                <div style="font-weight:bold; text-decoration:underline;">ANA MARIE F. VALMORIA</div>
                <div>AICOM</div>
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                <div>Noted by:</div>
                <div style="height:28px;"></div>
                <div style="font-weight:bold; text-decoration:underline;">REMEDIOS T. AUXTERO</div>
                <div>Manager</div>
            </td>
        </tr>
    </table>
</div>
