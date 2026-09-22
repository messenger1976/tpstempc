<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Count Sheet</title>
    <?php
    /*
     * Two renderers use this file:
     *   - a headless browser printing this page (preferred), which honours the
     *     @page rule below and reproduces the sheet exactly;
     *   - TCPDF, which cannot parse a full HTML document or a stylesheet, so it
     *     receives the <body> markup only (see Cashiering::_html_body()).
     *
     * Everything inside <body> is therefore table based with INLINE styles only.
     * TCPDF has no layout engine: no flexbox, no grid, no nested tables and no px
     * units (px are rescaled by TCPDF's imgscale * k and come out
     * unpredictable). Sizes are pt / mm.
     */
    $company = function_exists('company_info') ? company_info() : NULL;
    $company_name = !empty($company->name) ? strtoupper($company->name) : 'COOPERATIVE';
    $report_date = !empty($report->report_date) ? $report->report_date : date('Y-m-d');
    $notes = trim((string) (isset($report->notes) ? $report->notes : ''));
    $autoprint = !empty($autoprint);

    /* A voided sheet is still printable - it just has to be impossible to mistake for
       a live one, so the cancellation is printed at the top of the page. */
    $is_void = isset($report->status) && $report->status === 'void';
    $void_reason = isset($report->void_reason) ? trim((string) $report->void_reason) : '';
    $voided_on = !empty($report->voided_at) ? date('d-m-Y H:i', strtotime($report->voided_at)) : '';
    $voided_by_name = isset($voided_by_name) ? trim((string) $voided_by_name) : '';

    /* Prepared by the controller, so screen, preview and PDF share one structure. */
    $breakdown = (isset($breakdown) && is_array($breakdown))
        ? $breakdown
        : array('bills' => array(), 'coins' => array(), 'other' => array(), 'meta' => array());
    $totals = (isset($totals) && is_array($totals)) ? $totals : array();

    $denoms = function_exists('cashiering_denominations')
        ? cashiering_denominations()
        : array('bills' => array(), 'coins' => array(), 'pieces_per_unit' => 100);
    $pieces = (int) $denoms['pieces_per_unit'];
    $other = isset($breakdown['other']) ? $breakdown['other'] : array();
    $meta = isset($breakdown['meta']) ? $breakdown['meta'] : array();

    $accountable = isset($meta['accountable_person']) ? trim((string) $meta['accountable_person']) : '';
    if ($accountable === '') {
        $accountable = trim((string) (isset($report->cashier_name) ? $report->cashier_name : ''));
    }
    $fund_name = isset($meta['fund_name']) ? trim((string) $meta['fund_name']) : '';
    $counted_by = isset($meta['counted_by']) ? trim((string) $meta['counted_by']) : '';
    if ($counted_by === '') {
        $counted_by = $accountable;
    }
    $other_funds = isset($meta['other_funds']) ? trim((string) $meta['other_funds']) : '';
    $others_specify = isset($meta['others_specify']) ? trim((string) $meta['others_specify']) : '';

    $amount = function ($value) {
        return number_format((float) $value, 2);
    };
    $total = function ($key) use ($totals) {
        return isset($totals[$key]) ? (float) $totals[$key] : 0;
    };

    /*
     * Two seven column grids: In Bundles + Loose Bills, In Rolls + Loose Coins.
     * Flat tables only - TCPDF renders nested tables unreliably - and pairing the
     * rows keeps the sheet to one page.
     */
    $bill_rows = '';
    foreach ($denoms['bills'] as $denom) {
        $key = $denom['key'];
        $value = (float) $denom['value'];
        $bundles = isset($breakdown['bills'][$key]['bundles']) ? (float) $breakdown['bills'][$key]['bundles'] : 0;
        $loose = isset($breakdown['bills'][$key]['loose']) ? (float) $breakdown['bills'][$key]['loose'] : 0;
        $bill_rows .= '<tr>'
            . '<td style="text-align:center;">' . $amount($value) . '</td>'
            . '<td style="text-align:center;">' . $amount($value * $pieces) . '</td>'
            . '<td style="text-align:center;">' . number_format($bundles, 0) . '</td>'
            . '<td style="text-align:right;">' . $amount($bundles * $value * $pieces) . '</td>'
            . '<td style="text-align:center;">' . $amount($value) . '</td>'
            . '<td style="text-align:center;">' . number_format($loose, 0) . '</td>'
            . '<td style="text-align:right;">' . $amount($loose * $value) . '</td>'
            . '</tr>';
    }
    $coin_rows = '';
    foreach ($denoms['coins'] as $denom) {
        $key = $denom['key'];
        $value = (float) $denom['value'];
        $rolls = isset($breakdown['coins'][$key]['rolls']) ? (float) $breakdown['coins'][$key]['rolls'] : 0;
        $loose = isset($breakdown['coins'][$key]['loose']) ? (float) $breakdown['coins'][$key]['loose'] : 0;
        $coin_rows .= '<tr>'
            . '<td style="text-align:center;">' . $amount($value) . '</td>'
            . '<td style="text-align:center;">' . $amount($value * $pieces) . '</td>'
            . '<td style="text-align:center;">' . number_format($rolls, 0) . '</td>'
            . '<td style="text-align:right;">' . $amount($rolls * $value * $pieces) . '</td>'
            . '<td style="text-align:center;">' . $amount($value) . '</td>'
            . '<td style="text-align:center;">' . number_format($loose, 0) . '</td>'
            . '<td style="text-align:right;">' . $amount($loose * $value) . '</td>'
            . '</tr>';
    }
    ?>
    <style>
        /* Browser / headless printing only - TCPDF never sees this block. */
        @page { size: A4 portrait; margin: 10mm; }
        html, body { margin: 0; padding: 0; background: #fff; color: #000;
            font-family: Arial, Helvetica, sans-serif; }
        body { width: 190mm; margin: 0 auto; }
        @media print { body { width: auto; margin: 0; } }
    </style>
</head>
<body>
    <?php if ($is_void): ?>
    <table border="1" cellpadding="6" style="width:100%; font-size:11pt;">
        <tr>
            <td style="text-align:center;">
                <b style="font-size:14pt;">*** VOID ***</b><br/>
                This cash count sheet was cancelled<?php echo $voided_on !== '' ? ' on ' . htmlspecialchars($voided_on, ENT_QUOTES, 'UTF-8') : ''; ?><?php echo $voided_by_name !== '' ? ' by ' . htmlspecialchars($voided_by_name, ENT_QUOTES, 'UTF-8') : ''; ?>.
                <?php if ($void_reason !== ''): ?><br/>Reason: <?php echo htmlspecialchars($void_reason, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
            </td>
        </tr>
    </table>
    <br/>
    <?php endif; ?>
    <table style="width:100%;">
        <tr>
            <td style="text-align:center; border-bottom:1px solid #000; padding-bottom:4px;">
                <div style="font-size:13pt; font-weight:bold;"><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></div>
                <div style="font-size:11pt; font-weight:bold;">CASH COUNT SHEET</div>
            </td>
        </tr>
    </table>
    <br/>
    <table style="width:100%;">
        <tr>
            <td style="width:50%; font-size:9pt;">Name of Accountable Person: <b><?php echo htmlspecialchars($accountable, ENT_QUOTES, 'UTF-8'); ?></b></td>
            <td style="width:50%; font-size:9pt; text-align:right;">Date: <b><?php echo date('d-m-Y', strtotime($report_date)); ?></b></td>
        </tr>
        <tr>
            <td style="font-size:9pt;">Name of Fund: <b><?php echo htmlspecialchars($fund_name, ENT_QUOTES, 'UTF-8'); ?></b></td>
            <td style="font-size:9pt; text-align:right;">Report #<?php echo (int) $report->id; ?></td>
        </tr>
    </table>
    <br/>
    <table border="1" cellpadding="3" style="width:100%; font-size:8.5pt;">
        <tr style="background-color:#f2f2f2;">
            <td colspan="4" style="text-align:center;"><b>I. BILLS - IN BUNDLES</b></td>
            <td colspan="3" style="text-align:center;"><b>LOOSE BILLS</b></td>
        </tr>
        <tr>
            <td style="text-align:center;"><b>Denomination</b></td>
            <td style="text-align:center;"><b>Per Bundle</b></td>
            <td style="text-align:center;"><b>No. of Bundles</b></td>
            <td style="text-align:center;"><b>Amount</b></td>
            <td style="text-align:center;"><b>Denomination</b></td>
            <td style="text-align:center;"><b>Qty</b></td>
            <td style="text-align:center;"><b>Amount</b></td>
        </tr>
        <?php echo $bill_rows; ?>
        <tr style="background-color:#f2f2f2;">
            <td colspan="3" style="text-align:right;"><b>Sub-Total (a)</b></td>
            <td style="text-align:right;"><b><?php echo $amount($total('bundles')); ?></b></td>
            <td colspan="2" style="text-align:right;"><b>Sub-Total (b)</b></td>
            <td style="text-align:right;"><b><?php echo $amount($total('loose_bills')); ?></b></td>
        </tr>
    </table>
    <br/>
    <table border="1" cellpadding="3" style="width:100%; font-size:8.5pt;">
        <tr style="background-color:#f2f2f2;">
            <td colspan="4" style="text-align:center;"><b>II. COINS - IN ROLLS</b></td>
            <td colspan="3" style="text-align:center;"><b>LOOSE COINS</b></td>
        </tr>
        <tr>
            <td style="text-align:center;"><b>Denomination</b></td>
            <td style="text-align:center;"><b>Per Roll</b></td>
            <td style="text-align:center;"><b>No. of Rolls</b></td>
            <td style="text-align:center;"><b>Amount</b></td>
            <td style="text-align:center;"><b>Denomination</b></td>
            <td style="text-align:center;"><b>Qty</b></td>
            <td style="text-align:center;"><b>Amount</b></td>
        </tr>
        <?php echo $coin_rows; ?>
        <tr style="background-color:#f2f2f2;">
            <td colspan="3" style="text-align:right;"><b>Sub-Total (c)</b></td>
            <td style="text-align:right;"><b><?php echo $amount($total('rolls')); ?></b></td>
            <td colspan="2" style="text-align:right;"><b>Sub-Total (d)</b></td>
            <td style="text-align:right;"><b><?php echo $amount($total('loose_coins')); ?></b></td>
        </tr>
    </table>
    <br/>
    <table border="1" cellpadding="4" style="width:100%; font-size:9pt;">
        <tr style="background-color:#f2f2f2;">
            <td style="width:60%;"><b>TOTAL CURRENCY COUNTED (a to d)</b></td>
            <td style="width:40%; text-align:right;"><b><?php echo $amount($total('currency_total')); ?></b></td>
        </tr>
    </table>
    <br/>
    <table border="1" cellpadding="4" style="width:100%; font-size:9pt;">
        <tr style="background-color:#f2f2f2;"><td colspan="2"><b>OTHER ITEMS</b></td></tr>
        <tr><td style="width:70%;">Checks</td><td style="width:30%; text-align:right;"><?php echo $amount(isset($other['checks']) ? $other['checks'] : 0); ?></td></tr>
        <tr><td>Advances of IOU's</td><td style="text-align:right;"><?php echo $amount(isset($other['advances']) ? $other['advances'] : 0); ?></td></tr>
        <tr><td>Others<?php echo $others_specify !== '' ? ': ' . htmlspecialchars($others_specify, ENT_QUOTES, 'UTF-8') : ''; ?></td><td style="text-align:right;"><?php echo $amount(isset($other['others']) ? $other['others'] : 0); ?></td></tr>
        <tr style="background-color:#f2f2f2;"><td><b>Total CASH ITEMS COUNTED</b></td><td style="text-align:right;"><b><?php echo $amount($total('cash_items')); ?></b></td></tr>
    </table>
    <br/>
    <table border="1" cellpadding="4" style="width:100%; font-size:9pt;">
        <tr><td style="width:60%; text-align:right;">TOTAL CURRENCY COUNTED</td><td style="width:40%; text-align:right;"><?php echo $amount($total('currency_total')); ?></td></tr>
        <tr><td style="text-align:right;">ADD: TOTAL CASH ITEMS COUNTED</td><td style="text-align:right;"><?php echo $amount($total('cash_items')); ?></td></tr>
        <tr style="background-color:#f2f2f2;"><td style="text-align:right;"><b>GRAND TOTAL</b></td><td style="text-align:right;"><b><?php echo $amount($total('grand_total')); ?></b></td></tr>
        <tr><td style="text-align:right;">CASH ON HAND PER BOOKS</td><td style="text-align:right;"><?php echo $amount((float) $report->expected_cash); ?></td></tr>
        <tr style="background-color:#f2f2f2;"><td style="text-align:right;"><b>OVERAGES/(SHORTAGES)</b></td><td style="text-align:right;"><b><?php echo $amount((float) $report->over_short); ?></b></td></tr>
    </table>
    <br/>
    <table style="width:100%;">
        <tr>
            <td style="font-size:8.5pt;">
                I hereby certify that said fund of <b><?php echo $amount($total('grand_total')); ?></b>
                was counted by the TAPSTEMCO Internal Auditor in my presence on
                <b><?php echo date('d-m-Y', strtotime($report_date)); ?></b> and was surrendered to me intact on the
                same date. There are other funds in my possession for which I am accountable to the
                above-mentioned until, except as noted below:
            </td>
        </tr>
        <tr>
            <td style="font-size:8.5pt;">OTHER FUNDS: <u>&nbsp;<?php echo htmlspecialchars($other_funds, ENT_QUOTES, 'UTF-8'); ?>&nbsp;</u></td>
        </tr>
    </table>
    <br/>
    <?php if ($notes !== ''): ?>
    <table border="1" cellpadding="6" style="width:100%; font-size:8.5pt;">
        <tr><td><b>Notes:</b> <?php echo nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')); ?></td></tr>
    </table>
    <br/>
    <?php endif; ?>
    <table style="width:100%;">
        <tr>
            <td width="46%" style="height:16mm; border-bottom:1px solid #000; text-align:center; font-size:9pt;" valign="bottom"><b><?php echo htmlspecialchars($counted_by, ENT_QUOTES, 'UTF-8'); ?></b></td>
            <td width="8%">&nbsp;</td>
            <td width="46%" style="height:16mm; border-bottom:1px solid #000; text-align:center; font-size:9pt;" valign="bottom"><b><?php echo htmlspecialchars($accountable, ENT_QUOTES, 'UTF-8'); ?></b></td>
        </tr>
        <tr>
            <td style="text-align:center; font-size:8pt;">Counted by</td>
            <td>&nbsp;</td>
            <td style="text-align:center; font-size:8pt;">Accountable Employee</td>
        </tr>
    </table>
    <?php if ($autoprint): ?>
    <script>window.print();</script>
    <?php endif; ?>
</body>
</html>
