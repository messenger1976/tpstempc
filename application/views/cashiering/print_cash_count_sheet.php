<?php
/**
 * Printable Cash Count Sheet - the Print tab and the exported PDF.
 *
 * This is a STANDALONE Tailwind page, which is why the Tailwind CDN is safe here:
 * views/cashiering/cash_count_sheet.php must stay CDN-free because it renders inside
 * the admin shell and a Tailwind global reset once left the Inspinia sidebar
 * rendered-but-invisible. Nothing on this page shares a document with that shell, so
 * that hazard does not apply.
 *
 * The markup mirrors the on-screen sheet element for element, so the exported PDF is
 * a copy of the sheet the cashier filled in rather than a second design. Same
 * technique as the printed loan forms (views/loan/print/partials/loan_form_open.php
 * + loan_form_close.php):
 *
 *   - paper size and margins live in the :root variables below, which the fit
 *     routine reads, so the preview, the print dialog and the PDF share one sheet;
 *   - ?autoprint=1 opens the print dialog once the crest and the Tailwind CDN pass
 *     have settled.
 *
 * WHY EVERY ARBITRARY SIZE BELOW IS IN rem, NEVER px - this is not cosmetic.
 * The fit routine scales the sheet onto one page by shrinking the root font size,
 * which only works on rem-based sizes. Tailwind turns an arbitrary px value into a
 * hard px, immune to that scaling, and the sheet then spills onto a second page no
 * matter how small the root gets. So text-[0.6875rem] (11px at the default 16px
 * root) and p-[0.25rem_0.375rem] (4px/6px) instead of text-[11px] / p-[4px_6px].
 * At the default root size the two are identical; at a reduced root only the rem
 * form keeps the sheet on one page. Same reason the named scale (text-sm, p-4) is
 * safe: it is rem based too.
 *
 * Counted values are printed as text on the form's lines - this sheet is evidence of
 * a count, not a form to re-type - so no <input> is rendered anywhere.
 *
 * TCPDF cannot read any of this, so hosts with no headless browser fall back to
 * print_cash_count_sheet_tcpdf.php (see Cashiering::_cash_count_pdf_output()).
 */
$h = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$company = function_exists('company_info') ? company_info() : NULL;

$company_name = !empty($company->name) ? strtoupper($company->name) : 'TALIBON PUBLIC SCHOOL TEACHERS & EMPLOYEES';
$company_address = !empty($company->address) ? $company->address : 'Purok 1 North Road San Jose, Talibon, Bohol';

/* Official cooperative crest, inlined by loan_form_logo_src() so the headless
   renderer - which loads this page from a file:// temp path - never has to fetch the
   image over HTTP from the site itself. */
$logo_file = (defined('TAPSTEMCO_FORM_LOGO') && TAPSTEMCO_FORM_LOGO !== '')
    ? TAPSTEMCO_FORM_LOGO
    : (!empty($company->logo) ? $company->logo : '');
$logo_src = function_exists('loan_form_logo_src') ? loan_form_logo_src($logo_file) : '';

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

/*
 * $report is a row from cashiering_reports for a submitted sheet, or a stdClass the
 * exporter builds from the posted form for a draft. Both carry report_date, notes,
 * expected_cash and over_short, so the two paths render through one code path.
 */
$report_date = !empty($report->report_date) ? $report->report_date : date('Y-m-d');

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
$notes = trim((string) (isset($report->notes) ? $report->notes : ''));

$expected_cash = isset($report->expected_cash) ? (float) $report->expected_cash : 0.0;
$over_short = isset($report->over_short) ? (float) $report->over_short : 0.0;

/* "P 1,234.00" - the same formatting the on-screen sheet uses. */
$amount = function ($value) {
    return 'P ' . number_format((float) $value, 2);
};
$total = function ($key) use ($totals) {
    return isset($totals[$key]) ? (float) $totals[$key] : 0;
};

/* A voided sheet stays printable, but must be impossible to mistake for a live one. */
$is_void = isset($report->status) && $report->status === 'void';
$void_reason = isset($report->void_reason) ? trim((string) $report->void_reason) : '';
$voided_on = !empty($report->voided_at) ? date('d-m-Y H:i', strtotime($report->voided_at)) : '';
$voided_by_name = isset($voided_by_name) ? trim((string) $voided_by_name) : '';

/* A draft export never reached the database, so it says so on its face. */
$is_draft = !empty($is_draft);
$autoprint = !empty($autoprint);
$sheet_url = isset($sheet_url) ? $sheet_url : site_url(current_lang() . '/cashiering/cash_count_sheet');
$pdf_url = isset($pdf_url) ? $pdf_url : '';

/* Tailwind class strings, so the cells below stay readable and consistent. */
$td = 'border border-slate-900 p-[0.25rem_0.375rem] text-center text-[0.6875rem] print-exact';
$td_r = 'border border-slate-900 p-[0.25rem_0.375rem] text-right text-[0.6875rem] print-exact';
$th = 'border border-slate-900 bg-slate-50 p-[0.25rem_0.375rem] text-center text-[0.6875rem] font-bold print-exact';
$th_g = 'border border-slate-900 bg-slate-100 p-[0.25rem_0.375rem] text-center text-[0.75rem] font-bold print-exact';
$td_t = 'border border-slate-900 bg-slate-50 p-[0.25rem_0.375rem] text-[0.75rem] font-bold print-exact';

/* Two side-by-side grids, exactly as on screen: In Bundles + Loose Bills, then
   In Rolls + Loose Coins. */
$bill_bundle_rows = '';
$bill_loose_rows = '';
foreach ($denoms['bills'] as $denom) {
    $key = $denom['key'];
    $value = (float) $denom['value'];
    $bundles = isset($breakdown['bills'][$key]['bundles']) ? (float) $breakdown['bills'][$key]['bundles'] : 0;
    $loose = isset($breakdown['bills'][$key]['loose']) ? (float) $breakdown['bills'][$key]['loose'] : 0;

    $bill_bundle_rows .= '<tr>'
        . '<td class="' . $td . '">' . $amount($value) . '</td>'
        . '<td class="' . $td . '">' . $amount($value * $pieces) . '</td>'
        . '<td class="' . $td . '">' . number_format($bundles, 0) . '</td>'
        . '<td class="' . $td_r . '">' . $amount($bundles * $value * $pieces) . '</td>'
        . '</tr>';

    $bill_loose_rows .= '<tr>'
        . '<td class="' . $td . '">' . $amount($value) . '</td>'
        . '<td class="' . $td . '">' . number_format($loose, 0) . '</td>'
        . '<td class="' . $td_r . '">' . $amount($loose * $value) . '</td>'
        . '</tr>';
}

$coin_roll_rows = '';
$coin_loose_rows = '';
foreach ($denoms['coins'] as $denom) {
    $key = $denom['key'];
    $value = (float) $denom['value'];
    $rolls = isset($breakdown['coins'][$key]['rolls']) ? (float) $breakdown['coins'][$key]['rolls'] : 0;
    $loose = isset($breakdown['coins'][$key]['loose']) ? (float) $breakdown['coins'][$key]['loose'] : 0;

    $coin_roll_rows .= '<tr>'
        . '<td class="' . $td . '">' . $amount($value) . '</td>'
        . '<td class="' . $td . '">' . $amount($value * $pieces) . '</td>'
        . '<td class="' . $td . '">' . number_format($rolls, 0) . '</td>'
        . '<td class="' . $td_r . '">' . $amount($rolls * $value * $pieces) . '</td>'
        . '</tr>';

    $coin_loose_rows .= '<tr>'
        . '<td class="' . $td . '">' . $amount($value) . '</td>'
        . '<td class="' . $td . '">' . number_format($loose, 0) . '</td>'
        . '<td class="' . $td_r . '">' . $amount($loose * $value) . '</td>'
        . '</tr>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Count Sheet<?php echo $report_date !== '' ? ' - ' . $h(date('d-m-Y', strtotime($report_date))) : ''; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Sheet geometry in one place: the fit routine below reads these variables,
           so the preview and the exported PDF always use the same paper. */
        :root {
            --sheet-w-in: 8.5;
            --sheet-h-in: 13;
            --sheet-mx-mm: 8;
            --sheet-my-mm: 6;
        }
        @page { size: 8.5in 13in; margin: 6mm 8mm; }
        @media print {
            .no-print { display: none !important; }
            /* !important is required: the Tailwind utilities on <body> outrank a plain
               element selector and would otherwise keep their padding. */
            body { background-color: #ffffff !important; padding: 0 !important; margin: 0 !important; }
            .form-container { box-shadow: none !important; border: 0 !important; padding: 0 !important;
                border-radius: 0 !important; width: 100% !important; max-width: 100% !important; }
            /* The crest is hidden below the sm breakpoint so it cannot sit on top of the
               centred letterhead on a phone; on paper there is always room for it. */
            .form-container .cc-emblem { display: flex !important; }
        }
        .print-exact { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 font-sans text-slate-800">

<div class="max-w-[64rem] mx-auto mb-4 flex flex-wrap items-center justify-end gap-2 no-print">
    <span class="mr-auto text-xs text-slate-500">Cash Count Sheet<?php echo $is_draft ? ' (draft)' : ''; ?></span>
    <a href="<?php echo $h($sheet_url); ?>"
       class="bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-4 rounded border border-slate-300 shadow-sm transition text-sm">
        Back to Sheet
    </a>
    <?php if ($pdf_url !== ''): ?>
        <a href="<?php echo $h($pdf_url); ?>" target="_blank"
           title="Opens this sheet in the print dialog - choose &quot;Save as PDF&quot; to keep exactly this layout."
           class="bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-4 rounded border border-slate-300 shadow-sm transition text-sm">
            Download PDF
        </a>
    <?php endif; ?>
    <button type="button" onclick="window.print()"
            class="bg-slate-800 hover:bg-slate-900 text-white font-semibold py-2 px-4 rounded shadow transition text-sm">
        Print
    </button>
</div>

<div class="form-container bg-white max-w-[64rem] mx-auto px-4 py-8 md:px-10 md:py-8 shadow-lg border border-slate-200 rounded-[0.625rem] print-exact">

    <?php if ($is_void): ?>
        <div class="border border-red-400 bg-red-50 text-red-800 text-[0.6875rem] leading-relaxed py-2 px-3 mb-4 print-exact">
            <b class="uppercase">Void</b> - this cash count sheet was cancelled<?php echo $voided_on !== '' ? ' on ' . $h($voided_on) : ''; ?><?php echo $voided_by_name !== '' ? ' by ' . $h($voided_by_name) : ''; ?>.
            <?php if ($void_reason !== ''): ?><br/>Reason: <?php echo $h($void_reason); ?><?php endif; ?>
            <br/>It no longer counts towards the daily totals.
        </div>
    <?php endif; ?>

    <?php if ($is_draft): ?>
        <div class="border border-amber-400 bg-amber-50 text-amber-900 text-[0.6875rem] font-bold uppercase tracking-[.06em] text-center py-1 mb-4 print-exact">
            Draft - not yet submitted
        </div>
    <?php endif; ?>

    <!-- ---------- letterhead ---------- -->
    <div class="relative text-center pb-[0.875rem] mb-[1.125rem] border-b border-slate-300">
        <div class="cc-emblem hidden sm:flex absolute left-0 top-0 w-[4.625rem] h-[4.625rem] items-center justify-center">
            <?php if ($logo_src !== ''): ?>
                <img src="<?php echo $h($logo_src); ?>" alt="<?php echo $h($company_name); ?>" class="block w-full h-full object-contain print-exact"/>
            <?php else: ?>
                <div class="block text-[0.5625rem] font-bold text-slate-900 text-center leading-[1.15]">
                    TAPSTEMCO<span class="block text-[0.4375rem] font-normal">Cooperative</span>
                </div>
            <?php endif; ?>
        </div>
        <h2 class="m-0 text-[0.875rem] font-bold uppercase tracking-[.04em] text-slate-900"><?php echo $h($company_name); ?></h2>
        <h3 class="mt-[0.125rem] text-[0.75rem] font-semibold text-slate-800">Multipurpose Cooperative</h3>
        <p class="mt-[0.125rem] text-[0.6875rem] text-slate-600"><?php echo $h($company_address); ?></p>
        <h1 class="mt-4 text-[1rem] font-extrabold uppercase tracking-[.08em] text-slate-900 underline">CASH COUNT SHEET</h1>
    </div>

    <!-- ---------- accountable person / fund / date ---------- -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <div class="w-full border-b border-black px-[0.125rem] pb-[0.0625rem] text-[0.875rem] font-bold text-slate-900"><?php echo $h($accountable); ?>&nbsp;</div>
            <div class="text-[0.625rem] font-medium text-slate-600">Name of Accountable Person</div>
        </div>
        <div class="flex flex-col items-end gap-2">
            <div class="flex items-baseline gap-2 w-full justify-end">
                <span class="font-semibold text-slate-700">Name of Fund:</span>
                <span class="w-[45%] border-b border-black px-[0.125rem] pb-[0.0625rem] text-right"><?php echo $h($fund_name); ?>&nbsp;</span>
            </div>
            <div class="flex items-baseline gap-2 w-full justify-end">
                <span class="font-semibold text-slate-700">Date/Time:</span>
                <span class="w-[45%] border-b border-black px-[0.125rem] pb-[0.0625rem] text-right"><?php echo $h(date('m/d/Y', strtotime($report_date))); ?></span>
            </div>
        </div>
    </div>

    <!-- ---------- currency ---------- -->
    <div class="text-[0.75rem] font-bold uppercase tracking-[.06em] text-slate-800 mb-2">CURRENCY</div>

    <div class="text-[0.75rem] font-bold mb-1">I. BILLS</div>
    <div class="grid grid-cols-12 gap-3 mb-4">
        <div class="col-span-7">
            <table class="w-full border-collapse">
                <thead>
                    <tr><th colspan="4" class="<?php echo $th_g; ?> w-full">In Bundles</th></tr>
                    <tr>
                        <th class="<?php echo $th; ?> w-[25%]">Denomination</th>
                        <th class="<?php echo $th; ?> w-[40%]">Per Bundle<br/>(100 pcs. each)</th>
                        <th class="<?php echo $th; ?> w-[16%]">No. of<br/>Bundles</th>
                        <th class="<?php echo $th; ?> w-[20%]">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $bill_bundle_rows; ?>
                    <tr>
                        <td colspan="3" class="<?php echo $td_t; ?> text-right">Sub-Total (a)</td>
                        <td class="<?php echo $td_t; ?> text-right"><?php echo $amount($total('bundles')); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-span-5">
            <table class="w-full border-collapse">
                <thead>
                    <tr><th colspan="3" class="<?php echo $th_g; ?> w-full">Loose Bills</th></tr>
                    <tr>
                        <th class="<?php echo $th; ?> w-[40%]">Denomination</th>
                        <th class="<?php echo $th; ?> w-[25%]">Quantity</th>
                        <th class="<?php echo $th; ?> w-[35%]">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $bill_loose_rows; ?>
                    <tr>
                        <td colspan="2" class="<?php echo $td_t; ?> text-right">Sub-Total (b)</td>
                        <td class="<?php echo $td_t; ?> text-right"><?php echo $amount($total('loose_bills')); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-[0.75rem] font-bold mb-1">II. COINS</div>
    <div class="grid grid-cols-12 gap-3 mb-4">
        <div class="col-span-7">
            <table class="w-full border-collapse">
                <thead>
                    <tr><th colspan="4" class="<?php echo $th_g; ?> w-full">In Rolls</th></tr>
                    <tr>
                        <th class="<?php echo $th; ?> w-[25%]">Denomination</th>
                        <th class="<?php echo $th; ?> w-[40%]">Per Roll<br/>(100 pcs each)</th>
                        <th class="<?php echo $th; ?> w-[16%]">No. of<br/>Rolls</th>
                        <th class="<?php echo $th; ?> w-[20%]">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $coin_roll_rows; ?>
                    <tr>
                        <td colspan="3" class="<?php echo $td_t; ?> text-right">Sub-Total (c)</td>
                        <td class="<?php echo $td_t; ?> text-right"><?php echo $amount($total('rolls')); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-span-5">
            <table class="w-full border-collapse">
                <thead>
                    <tr><th colspan="3" class="<?php echo $th_g; ?> w-full">Loose Coins</th></tr>
                    <tr>
                        <th class="<?php echo $th; ?> w-[40%]">Denomination</th>
                        <th class="<?php echo $th; ?> w-[25%]">Quantity</th>
                        <th class="<?php echo $th; ?> w-[35%]">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $coin_loose_rows; ?>
                    <tr>
                        <td colspan="2" class="<?php echo $td_t; ?> text-right">Sub-Total (d)</td>
                        <td class="<?php echo $td_t; ?> text-right"><?php echo $amount($total('loose_coins')); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <table class="w-full border-collapse mb-4">
        <tr>
            <td class="<?php echo $td_t; ?> w-[55%] text-left pl-[0.375rem]">TOTAL CURRENCY COUNTED</td>
            <td class="<?php echo $td_t; ?> w-[20%] text-right pr-[0.375rem]">(a to d)</td>
            <td class="<?php echo $td_t; ?> w-[25%] text-left pl-[0.375rem]"><?php echo $amount($total('currency_total')); ?></td>
        </tr>
    </table>

    <!-- ---------- other items ---------- -->
    <div class="text-[0.75rem] font-bold mb-1">OTHER ITEMS</div>
    <table class="w-full border-collapse mb-4">
        <tbody>
            <tr>
                <td class="<?php echo $td; ?> text-left pl-[0.375rem] text-[0.75rem] w-[70%]">Checks (See schedule ____)</td>
                <td class="<?php echo $td_r; ?> text-[0.75rem] w-[30%]"><?php echo $amount(isset($other['checks']) ? $other['checks'] : 0); ?></td>
            </tr>
            <tr>
                <td class="<?php echo $td; ?> text-left pl-[0.375rem] text-[0.75rem]">Advances of IOU's (See schedule ____)</td>
                <td class="<?php echo $td_r; ?> text-[0.75rem]"><?php echo $amount(isset($other['advances']) ? $other['advances'] : 0); ?></td>
            </tr>
            <tr>
                <td class="<?php echo $td; ?> text-left pl-[0.375rem] text-[0.75rem]">Others: (Specify)<?php echo $others_specify !== '' ? ' ' . $h($others_specify) : ''; ?></td>
                <td class="<?php echo $td_r; ?> text-[0.75rem]"><?php echo $amount(isset($other['others']) ? $other['others'] : 0); ?></td>
            </tr>
            <tr>
                <td class="<?php echo $td_t; ?> text-left pl-[0.375rem]">Total CASH ITEMS COUNTED</td>
                <td class="<?php echo $td_t; ?> text-right pr-[0.375rem]"><?php echo $amount($total('cash_items')); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- ---------- reconciliation ---------- -->
    <div class="flex justify-end mb-7">
        <div class="w-full max-w-[26.25rem]">
            <div class="flex justify-between items-baseline gap-3 font-bold mb-2">
                <span>TOTAL CURRENCY COUNTED</span>
                <span class="inline-block w-[10rem] px-1 border-b border-black text-right"><?php echo $amount($total('currency_total')); ?></span>
            </div>
            <div class="flex justify-between items-baseline gap-3 font-bold mb-2">
                <span>ADD: TOTAL CASH ITEMS COUNTED</span>
                <span class="inline-block w-[10rem] px-1 border-b border-black text-right"><?php echo $amount($total('cash_items')); ?></span>
            </div>
            <div class="flex justify-between items-baseline gap-3 font-bold mb-2">
                <span>GRAND TOTAL</span>
                <span class="inline-block w-[10rem] px-1 border-b border-black text-right"><?php echo $amount($total('grand_total')); ?></span>
            </div>
            <div class="flex justify-between items-baseline gap-3 font-bold mb-2">
                <span>CASH ON HAND PER BOOKS</span>
                <span class="inline-block w-[10rem] px-1 border-b border-black text-right"><?php echo $amount($expected_cash); ?></span>
            </div>
            <div class="flex justify-between items-baseline gap-3 font-bold mb-2">
                <span>OVERAGES/(SHORTAGES)</span>
                <span class="inline-block w-[10rem] px-1 border-b border-black text-right"><?php echo $amount($over_short); ?></span>
            </div>
        </div>
    </div>

    <!-- ---------- certification and signatures ---------- -->
    <div class="grid grid-cols-12 gap-6 pt-4 border-t border-slate-200">
        <div class="col-span-4">
            <span class="font-semibold text-slate-700">Counted by:</span>
            <div class="h-16"></div>
            <div class="w-full text-center font-bold border-b border-black pb-[0.0625rem]"><?php echo $h($counted_by); ?>&nbsp;</div>
            <div class="text-[0.625rem] text-center text-slate-600">(signature over printed name)</div>
        </div>
        <div class="col-span-8">
            <p class="text-[0.6875rem] leading-[1.6] text-justify m-0 mb-[0.875rem]">
                I hereby certify that said fund of <b><?php echo $amount($total('grand_total')); ?></b>
                was counted by the TAPSTEMCO Internal Auditor in my presence on
                <b><?php echo $h(date('d-m-Y', strtotime($report_date))); ?></b> and was surrendered to me intact on the same date.
                There are other funds in my possession for which I am accountable to the above-mentioned until, except as noted below:
            </p>
            <div class="mb-[0.875rem]">
                <span class="font-semibold text-slate-700">OTHER FUNDS:</span>
                <span class="inline-block w-[75%] border-b border-black px-[0.125rem] align-bottom"><?php echo $h($other_funds); ?>&nbsp;</span>
            </div>
            <div class="mb-[0.875rem]">
                <span class="font-semibold text-slate-700">Notes:</span>
                <span class="inline-block w-[75%] border-b border-black px-[0.125rem] align-bottom whitespace-pre-wrap"><?php echo $h($notes); ?>&nbsp;</span>
            </div>
            <div class="w-[66%] ml-auto pt-6 text-center">
                <div class="h-12"></div>
                <div class="text-center font-bold uppercase border-b border-black min-h-[1rem]"><?php echo $h($accountable); ?>&nbsp;</div>
                <div class="text-[0.625rem] text-center text-slate-600">Accountable Employee</div>
                <div class="text-[0.625rem] text-center text-slate-600">(Signature over printed name)</div>
            </div>
        </div>
    </div>

</div><!-- /form-container -->

<script>
/**
 * Fit the sheet on one page - the same routine the printed loan forms use.
 *
 * The sheet is never re-flowed or trimmed; only the root font size is reduced, and
 * because every size in this document is rem based (see the note at the top of the
 * file) the whole layout scales proportionally. The metrics come from the :root
 * variables in the <style> block above, and the container is pinned to the printable
 * width with its card padding removed, so the preview, the print dialog and the
 * exported PDF are the same page.
 */
function tapstemcoFitSheet() {
    var MM_PER_IN = 25.4;
    var PX_PER_IN = 96;
    var MIN_ROOT = 8;

    var root = document.documentElement;
    var container = document.querySelector('.form-container');
    if (!root || !container) {
        return false;
    }

    var styles = window.getComputedStyle(root);
    var sheetWIn = parseFloat(styles.getPropertyValue('--sheet-w-in')) || 8.5;
    var sheetHIn = parseFloat(styles.getPropertyValue('--sheet-h-in')) || 13;
    var marginXMm = parseFloat(styles.getPropertyValue('--sheet-mx-mm')) || 8;
    var marginYMm = parseFloat(styles.getPropertyValue('--sheet-my-mm')) || 6;

    var printableW = Math.floor(sheetWIn * PX_PER_IN - 2 * (marginXMm / MM_PER_IN) * PX_PER_IN);
    var printableH = Math.floor(sheetHIn * PX_PER_IN - 2 * (marginYMm / MM_PER_IN) * PX_PER_IN);
    var baseRoot = parseFloat(styles.fontSize) || 16;

    function applySheet(px) {
        root.style.fontSize = px + 'px';
        container.style.width = printableW + 'px';
        container.style.maxWidth = printableW + 'px';
        container.style.padding = '0';
        container.style.boxShadow = 'none';
        container.style.border = '0';
        return container.getBoundingClientRect().height;
    }

    var height = applySheet(baseRoot);
    if (height > printableH) {
        // Largest root size whose content still fits the sheet height.
        var lo = MIN_ROOT;
        var hi = baseRoot;
        var mid;
        for (var i = 0; i < 14; i++) {
            mid = (lo + hi) / 2;
            if (applySheet(mid) <= printableH) {
                lo = mid;
            } else {
                hi = mid;
            }
        }
        height = applySheet(Math.floor(lo * 100) / 100);
    }

    window.__sheet = { height: height, printable: printableH, fits: height <= printableH };
    return true;
}

(function () {
    var attempts = 0;

    function imagesReady() {
        var imgs = document.images;
        for (var i = 0; i < imgs.length; i++) {
            if (!imgs[i].complete) { return false; }
        }
        return true;
    }

    function runFit() {
        try {
            tapstemcoFitSheet();
        } catch (e) {
            // Never let the fit routine break the sheet.
        }
        window.__sheetFitted = true;
    }

    function fire() {
        // The document title becomes the default file name in the save dialog.
        window.print();
    }

    function waitThenPrint() {
        attempts++;
        // Wait for the crest and the Tailwind CDN styles, then fit once more so a
        // late style pass cannot undo the measurement.
        if (document.readyState === 'complete' && imagesReady() && attempts > 2) {
            runFit();
            setTimeout(fire, 150);
            return;
        }
        if (attempts > 40) {
            runFit();
            fire();
            return;
        }
        setTimeout(waitThenPrint, 150);
    }

    function start() {
        runFit();
        // Tailwind's CDN build can still be applying classes; settle, then redo.
        setTimeout(runFit, 400);
<?php if ($autoprint): ?>
        waitThenPrint();
<?php endif; ?>
    }

    if (document.readyState === 'complete') {
        start();
    } else {
        window.addEventListener('load', start);
    }
})();
</script>

</body>
</html>
