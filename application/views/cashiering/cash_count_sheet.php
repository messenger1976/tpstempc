<?php
/**
 * Cash Count Sheet - data entry, laid out after the printed TAPSTEMCO blank form.
 *
 * Styling is fully self contained: every rule lives in the scoped <style> block at
 * the bottom of this file and nothing is loaded from a CDN. That matters because
 * this view renders inside the admin shell (Bootstrap 3 + Inspinia) - an earlier
 * version pulled Tailwind from a CDN and its global reset/utilities interfered with
 * the sidebar, leaving the Cashiering submenu rendered but invisible.
 *
 * Field names match the flat keys the model rebuilds into cash_breakdown:
 * bundles_<key> / loose_<key> for bills, rolls_<key> / loose_<key> for coins.
 */
$report = isset($report) ? $report : NULL;
$summary = isset($summary) ? $summary : array();
$breakdown = isset($breakdown) ? $breakdown : array('bills' => array(), 'coins' => array(), 'other' => array(), 'meta' => array());
$totals = isset($totals) ? $totals : array();
$report_date = !empty($report->report_date) ? $report->report_date : $report_date;

$meta = isset($breakdown['meta']) ? $breakdown['meta'] : array();
$other = isset($breakdown['other']) ? $breakdown['other'] : array();
$company = function_exists('company_info') ? company_info() : NULL;
$denoms = function_exists('cashiering_denominations')
    ? cashiering_denominations()
    : array('bills' => array(), 'coins' => array(), 'pieces_per_unit' => 100);
$pieces = (int) $denoms['pieces_per_unit'];

$company_name = !empty($company->name) ? strtoupper($company->name) : 'TALIBON PUBLIC SCHOOL TEACHERS & EMPLOYEES';
$company_address = !empty($company->address) ? $company->address : 'Purok 1 North Road San Jose, Talibon, Bohol';

$accountable = !empty($meta['accountable_person'])
    ? $meta['accountable_person']
    : (!empty($report->cashier_name) ? $report->cashier_name : current_user()->first_name . ' ' . current_user()->last_name);
$fund_name = isset($meta['fund_name']) ? $meta['fund_name'] : '';
$counted_by = !empty($meta['counted_by']) ? $meta['counted_by'] : $accountable;
$other_funds = isset($meta['other_funds']) ? $meta['other_funds'] : '';
$others_specify = isset($meta['others_specify']) ? $meta['others_specify'] : '';
$notes = isset($report->notes) ? $report->notes : '';

/* Before the first submission the book figure is the system expectation. */
$cash_on_hand = !empty($report->id)
    ? (float) $report->expected_cash
    : (float) (isset($summary['expected_cash']) ? $summary['expected_cash'] : 0);

/* "P 1,234.00" - one place, so server output and the JS agree. */
$peso = function ($amount) {
    return 'P ' . number_format((float) $amount, 2);
};

/* Void state, set by the controller. A voided sheet is shown as a cancelled record:
   it can still be printed, but it cannot be submitted again. */
$is_void = !empty($is_void);
$can_void = !empty($can_void);
$voided_by_name = isset($voided_by_name) ? trim((string) $voided_by_name) : '';
$void_reason = isset($report->void_reason) ? trim((string) $report->void_reason) : '';
$voided_on = !empty($report->voided_at) ? date('d-m-Y H:i', strtotime($report->voided_at)) : '';
?>
<div class="cash-count-sheet">

    <div class="cc-bar">
        <h1 class="cc-bar-title"><span class="cc-dot"></span>Cash Count Sheet</h1>
        <div class="cc-bar-actions">
            <a class="cc-btn cc-btn-light" href="<?php echo site_url(current_lang() . '/cashiering/dashboard'); ?>">Back</a>
            <?php if (!empty($report->id)): ?>
                <a class="cc-btn cc-btn-dark" target="_blank" href="<?php echo site_url(current_lang() . '/cashiering/cash_count_sheet_print/' . $report->id . '/1'); ?>">Print</a>
                <a class="cc-btn cc-btn-light" href="<?php echo site_url(current_lang() . '/cashiering/export_cash_count_pdf/' . $report->id); ?>">Export PDF</a>
            <?php else: ?>
                <button type="button" class="cc-btn cc-btn-dark" onclick="window.print();">Print</button>
            <?php endif; ?>
            <?php if ($is_void): ?>
                <a class="cc-btn cc-btn-green" href="<?php echo site_url(current_lang() . '/cashiering/cash_count_sheet'); ?>">Start a New Count</a>
            <?php else: ?>
                <?php if ($can_void): ?>
                    <a class="cc-btn cc-btn-danger" href="<?php echo site_url(current_lang() . '/cashiering/void_cash_count/' . $report->id); ?>">Void</a>
                <?php endif; ?>
                <button type="submit" form="ccSheetForm" class="cc-btn cc-btn-green">Submit Cash Report</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_void): ?>
        <div class="cc-void-banner">
            <b>VOID</b> - this cash count sheet was cancelled<?php echo $voided_on !== '' ? ' on ' . htmlspecialchars($voided_on, ENT_QUOTES, 'UTF-8') : ''; ?><?php echo $voided_by_name !== '' ? ' by ' . htmlspecialchars($voided_by_name, ENT_QUOTES, 'UTF-8') : ''; ?>.
            <?php if ($void_reason !== ''): ?><br/>Reason: <?php echo htmlspecialchars($void_reason, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
            <br/>It no longer counts towards the daily totals. Start a new count to replace it.
        </div>
    <?php endif; ?>

    <form method="post" id="ccSheetForm" action="">
        <input type="hidden" name="cashier_name" value="<?php echo htmlspecialchars($accountable, ENT_QUOTES, 'UTF-8'); ?>" />

        <div class="cc-sheet">

            <div class="cc-head">
                <div class="cc-emblem">
                    <div><b>TAPSTEMCO</b><span>Cooperative</span></div>
                </div>
                <h2 class="cc-coop"><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></h2>
                <h3 class="cc-coop-sub">Multipurpose Cooperative</h3>
                <p class="cc-coop-addr"><?php echo htmlspecialchars($company_address, ENT_QUOTES, 'UTF-8'); ?></p>
                <h1 class="cc-title">CASH COUNT SHEET</h1>
            </div>

            <div class="cc-meta">
                <div>
                    <input type="text" name="accountable_person" class="cc-line cc-name" value="<?php echo htmlspecialchars($accountable, ENT_QUOTES, 'UTF-8'); ?>" />
                    <div class="cc-caption">Name of Accountable Person</div>
                </div>
                <div class="cc-meta-right">
                    <div class="cc-meta-row">
                        <span class="cc-label">Name of Fund:</span>
                        <input type="text" name="fund_name" class="cc-line ccw-45" value="<?php echo htmlspecialchars($fund_name, ENT_QUOTES, 'UTF-8'); ?>" />
                    </div>
                    <div class="cc-meta-row">
                        <span class="cc-label">Date/Time:</span>
                        <input type="date" name="report_date" class="cc-line ccw-45" value="<?php echo htmlspecialchars($report_date, ENT_QUOTES, 'UTF-8'); ?>" />
                    </div>
                </div>
            </div>

            <div class="cc-band">CURRENCY</div>

            <div class="cc-subhead">I. BILLS</div>
            <div class="cc-grid">
                <div class="cc-span-7">
                    <table class="cc-table">
                        <thead>
                            <tr><th colspan="4" class="cc-group">In Bundles</th></tr>
                            <tr>
                                <th class="ccw-25">Denomination</th>
                                <th class="ccw-40">Per Bundle<br>(100 pcs. each)</th>
                                <th class="ccw-16">No. of<br>Bundles</th>
                                <th class="ccw-20">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($denoms['bills'] as $denom) {
                                $key = $denom['key'];
                                $value = (float) $denom['value'];
                                $qty = isset($breakdown['bills'][$key]['bundles']) ? (float) $breakdown['bills'][$key]['bundles'] : 0;
                                echo '<tr>'
                                    . '<td>' . $peso($value) . '</td>'
                                    . '<td>' . $peso($value * $pieces) . '</td>'
                                    . '<td><input type="number" min="0" step="1" class="cc-cell cc-qty" data-unit="' . $value . '" data-factor="' . $pieces . '" data-bucket="bundles" name="bundles_' . $key . '" value="' . htmlspecialchars($qty, ENT_QUOTES, 'UTF-8') . '" /></td>'
                                    . '<td class="cc-right"><span id="amt_bundles_' . $key . '">' . $peso($qty * $value * $pieces) . '</span></td>'
                                    . '</tr>';
                            }
                            ?>
                            <tr class="cc-total">
                                <td colspan="3" class="cc-right">Sub-Total (a)</td>
                                <td class="cc-right"><span id="sub_bundles"><?php echo $peso(isset($totals['bundles']) ? $totals['bundles'] : 0); ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="cc-span-5">
                    <table class="cc-table">
                        <thead>
                            <tr><th colspan="3" class="cc-group">Loose Bills</th></tr>
                            <tr>
                                <th class="ccw-40">Denomination</th>
                                <th class="ccw-25">Quantity</th>
                                <th class="ccw-35">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($denoms['bills'] as $denom) {
                                $key = $denom['key'];
                                $value = (float) $denom['value'];
                                $qty = isset($breakdown['bills'][$key]['loose']) ? (float) $breakdown['bills'][$key]['loose'] : 0;
                                echo '<tr>'
                                    . '<td>' . $peso($value) . '</td>'
                                    . '<td><input type="number" min="0" step="1" class="cc-cell cc-qty" data-unit="' . $value . '" data-factor="1" data-bucket="loose_bills" name="loose_' . $key . '" value="' . htmlspecialchars($qty, ENT_QUOTES, 'UTF-8') . '" /></td>'
                                    . '<td class="cc-right"><span id="amt_loose_' . $key . '">' . $peso($qty * $value) . '</span></td>'
                                    . '</tr>';
                            }
                            ?>
                            <tr class="cc-total">
                                <td colspan="2" class="cc-right">Sub-Total (b)</td>
                                <td class="cc-right"><span id="sub_loose_bills"><?php echo $peso(isset($totals['loose_bills']) ? $totals['loose_bills'] : 0); ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cc-subhead">II. COINS</div>
            <div class="cc-grid">
                <div class="cc-span-7">
                    <table class="cc-table">
                        <thead>
                            <tr><th colspan="4" class="cc-group">In Rolls</th></tr>
                            <tr>
                                <th class="ccw-25">Denomination</th>
                                <th class="ccw-40">Per Roll<br>(100 pcs each)</th>
                                <th class="ccw-16">No. of<br>Rolls</th>
                                <th class="ccw-20">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($denoms['coins'] as $denom) {
                                $key = $denom['key'];
                                $value = (float) $denom['value'];
                                $qty = isset($breakdown['coins'][$key]['rolls']) ? (float) $breakdown['coins'][$key]['rolls'] : 0;
                                echo '<tr>'
                                    . '<td>' . $peso($value) . '</td>'
                                    . '<td>' . $peso($value * $pieces) . '</td>'
                                    . '<td><input type="number" min="0" step="1" class="cc-cell cc-qty" data-unit="' . $value . '" data-factor="' . $pieces . '" data-bucket="rolls" name="rolls_' . $key . '" value="' . htmlspecialchars($qty, ENT_QUOTES, 'UTF-8') . '" /></td>'
                                    . '<td class="cc-right"><span id="amt_rolls_' . $key . '">' . $peso($qty * $value * $pieces) . '</span></td>'
                                    . '</tr>';
                            }
                            ?>
                            <tr class="cc-total">
                                <td colspan="3" class="cc-right">Sub-Total (c)</td>
                                <td class="cc-right"><span id="sub_rolls"><?php echo $peso(isset($totals['rolls']) ? $totals['rolls'] : 0); ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="cc-span-5">
                    <table class="cc-table">
                        <thead>
                            <tr><th colspan="3" class="cc-group">Loose Coins</th></tr>
                            <tr>
                                <th class="ccw-40">Denomination</th>
                                <th class="ccw-25">Quantity</th>
                                <th class="ccw-35">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($denoms['coins'] as $denom) {
                                $key = $denom['key'];
                                $value = (float) $denom['value'];
                                $qty = isset($breakdown['coins'][$key]['loose']) ? (float) $breakdown['coins'][$key]['loose'] : 0;
                                echo '<tr>'
                                    . '<td>' . $peso($value) . '</td>'
                                    . '<td><input type="number" min="0" step="1" class="cc-cell cc-qty" data-unit="' . $value . '" data-factor="1" data-bucket="loose_coins" name="loose_' . $key . '" value="' . htmlspecialchars($qty, ENT_QUOTES, 'UTF-8') . '" /></td>'
                                    . '<td class="cc-right"><span id="amt_loose_' . $key . '">' . $peso($qty * $value) . '</span></td>'
                                    . '</tr>';
                            }
                            ?>
                            <tr class="cc-total">
                                <td colspan="2" class="cc-right">Sub-Total (d)</td>
                                <td class="cc-right"><span id="sub_loose_coins"><?php echo $peso(isset($totals['loose_coins']) ? $totals['loose_coins'] : 0); ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <table class="cc-table cc-table-total">
                <tr>
                    <td class="cc-left ccw-55">TOTAL CURRENCY COUNTED</td>
                    <td class="cc-right ccw-20">(a to d)</td>
                    <td class="cc-left ccw-25"><span class="js-currency"><?php echo $peso(isset($totals['currency_total']) ? $totals['currency_total'] : 0); ?></span></td>
                </tr>
            </table>

            <div class="cc-subhead">OTHER ITEMS</div>
            <table class="cc-table cc-table-other">
                <tbody>
                    <tr>
                        <td class="cc-left ccw-70">Checks (See schedule ____)</td>
                        <td class="ccw-30"><input type="number" min="0" step="0.01" id="checks" name="checks" class="cc-cell cc-cell-right" value="<?php echo htmlspecialchars(isset($other['checks']) ? $other['checks'] : 0, ENT_QUOTES, 'UTF-8'); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="cc-left">Advances of IOU's (See schedule ____)</td>
                        <td><input type="number" min="0" step="0.01" id="advances" name="advances" class="cc-cell cc-cell-right" value="<?php echo htmlspecialchars(isset($other['advances']) ? $other['advances'] : 0, ENT_QUOTES, 'UTF-8'); ?>" /></td>
                    </tr>
                    <tr>
                        <td class="cc-left">Others: (Specify) <input type="text" name="others_specify" class="cc-line ccw-40" value="<?php echo htmlspecialchars($others_specify, ENT_QUOTES, 'UTF-8'); ?>" /></td>
                        <td><input type="number" min="0" step="0.01" id="others" name="others" class="cc-cell cc-cell-right" value="<?php echo htmlspecialchars(isset($other['others']) ? $other['others'] : 0, ENT_QUOTES, 'UTF-8'); ?>" /></td>
                    </tr>
                    <tr class="cc-total">
                        <td class="cc-left">Total CASH ITEMS COUNTED</td>
                        <td><span class="js-cash-items"><?php echo $peso(isset($totals['cash_items']) ? $totals['cash_items'] : 0); ?></span></td>
                    </tr>
                </tbody>
            </table>

            <div class="cc-summary-wrap">
                <div class="cc-summary">
                    <div class="cc-sum">
                        <span>TOTAL CURRENCY COUNTED</span>
                        <span class="cc-sum-value"><span class="js-currency"><?php echo $peso(isset($totals['currency_total']) ? $totals['currency_total'] : 0); ?></span></span>
                    </div>
                    <div class="cc-sum">
                        <span>ADD: TOTAL CASH ITEMS COUNTED</span>
                        <span class="cc-sum-value"><span class="js-cash-items"><?php echo $peso(isset($totals['cash_items']) ? $totals['cash_items'] : 0); ?></span></span>
                    </div>
                    <div class="cc-sum">
                        <span>GRAND TOTAL</span>
                        <span class="cc-sum-value"><span class="js-grand"><?php echo $peso(isset($totals['grand_total']) ? $totals['grand_total'] : 0); ?></span></span>
                    </div>
                    <div class="cc-sum">
                        <span>CASH ON HAND PER BOOKS</span>
                        <span class="cc-sum-value"><input type="number" step="0.01" id="cash_on_hand_books" name="cash_on_hand_books" class="cc-border-cell" value="<?php echo htmlspecialchars($cash_on_hand, ENT_QUOTES, 'UTF-8'); ?>" /></span>
                    </div>
                    <div class="cc-sum">
                        <span>OVERAGES/(SHORTAGES)</span>
                        <span class="cc-sum-value"><span class="js-over"><?php echo $peso((isset($totals['grand_total']) ? $totals['grand_total'] : 0) - $cash_on_hand); ?></span></span>
                    </div>
                </div>
            </div>

            <div class="cc-sign">
                <div class="cc-sign-4">
                    <span class="cc-label">Counted by:</span>
                    <div class="cc-gap"></div>
                    <input type="text" name="counted_by" class="cc-line cc-sign-input" value="<?php echo htmlspecialchars($counted_by, ENT_QUOTES, 'UTF-8'); ?>" />
                    <div class="cc-sign-caption">(signature over printed name)</div>
                </div>
                <div class="cc-sign-8">
                    <p class="cc-cert">
                        I hereby certify that said fund of <b><span class="js-grand"><?php echo $peso(isset($totals['grand_total']) ? $totals['grand_total'] : 0); ?></span></b>
                        was counted by the TAPSTEMCO Internal Auditor in my presence on
                        <b><?php echo date('d-m-Y', strtotime($report_date)); ?></b> and was surrendered to me intact on the same date.
                        There are other funds in my possession for which I am accountable to the above-mentioned until, except as noted below:
                    </p>
                    <div class="cc-block">
                        <span class="cc-label">OTHER FUNDS:</span>
                        <input type="text" name="other_funds" class="cc-line ccw-75" value="<?php echo htmlspecialchars($other_funds, ENT_QUOTES, 'UTF-8'); ?>" />
                    </div>
                    <div class="cc-block">
                        <span class="cc-label">Notes:</span>
                        <textarea name="notes" rows="2" class="cc-line ccw-75 cc-textarea"><?php echo htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="cc-sign-right">
                        <div class="cc-gap-sm"></div>
                        <div class="cc-sign-name"><?php echo htmlspecialchars($accountable, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="cc-sign-caption">Accountable Employee</div>
                        <div class="cc-sign-caption">(Signature over printed name)</div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<style>
    /*
     * Every rule is scoped under .cash-count-sheet and no external stylesheet is
     * loaded, so the admin shell (Bootstrap 3 + Inspinia) and its sidebar menu are
     * untouched. Keep it that way: nothing here may be written unscoped.
     */
    .cash-count-sheet { font-size: 12px; line-height: 1.35; color: #1f2937; }

    /* ---------- action bar ---------- */
    .cash-count-sheet .cc-bar { max-width: 1024px; margin: 0 auto 16px; display: flex; flex-wrap: wrap;
        align-items: center; justify-content: space-between; gap: 12px;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .08); }
    .cash-count-sheet .cc-bar-title { margin: 0; font-size: 14px; font-weight: 600; color: #334155;
        display: flex; align-items: center; gap: 8px; }
    .cash-count-sheet .cc-dot { width: 10px; height: 10px; border-radius: 50%; background: #10b981; }
    .cash-count-sheet .cc-bar-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .cash-count-sheet .cc-btn { display: inline-block; padding: 8px 16px; border-radius: 8px;
        border: 1px solid transparent; font-size: 13px; font-weight: 500; line-height: 1.2;
        text-decoration: none; cursor: pointer; }
    .cash-count-sheet .cc-btn:hover, .cash-count-sheet .cc-btn:focus { text-decoration: none; }
    .cash-count-sheet .cc-btn-dark { background: #1e293b; color: #fff; }
    .cash-count-sheet .cc-btn-dark:hover, .cash-count-sheet .cc-btn-dark:focus { background: #0f172a; color: #fff; }
    .cash-count-sheet .cc-btn-green { background: #047857; color: #fff; }
    .cash-count-sheet .cc-btn-green:hover, .cash-count-sheet .cc-btn-green:focus { background: #065f46; color: #fff; }
    .cash-count-sheet .cc-btn-light { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
    .cash-count-sheet .cc-btn-light:hover, .cash-count-sheet .cc-btn-light:focus { background: #e2e8f0; color: #334155; }
    .cash-count-sheet .cc-btn-danger { background: #b91c1c; color: #fff; }
    .cash-count-sheet .cc-btn-danger:hover, .cash-count-sheet .cc-btn-danger:focus { background: #991b1b; color: #fff; }

    /* Voided sheet: a cancelled record that is kept for audit, not hidden. */
    .cash-count-sheet .cc-void-banner { max-width: 1024px; margin: 0 auto 16px; padding: 14px 16px;
        border: 2px solid #b91c1c; background: #fef2f2; color: #7f1d1d;
        border-radius: 10px; font-size: 13px; line-height: 1.5; }
    .cash-count-sheet .cc-void-banner b:first-child { letter-spacing: .12em; }

    /* ---------- the sheet ---------- */
    .cash-count-sheet .cc-sheet { max-width: 1024px; margin: 0 auto; background: #fff;
        border: 1px solid #cbd5e1; border-radius: 10px; padding: 32px 40px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .10); }

    /* ---------- letterhead ---------- */
    .cash-count-sheet .cc-head { position: relative; text-align: center; padding-bottom: 14px;
        margin-bottom: 18px; border-bottom: 1px solid #cbd5e1; }
    .cash-count-sheet .cc-emblem { position: absolute; left: 0; top: 0; width: 74px; height: 74px;
        border-radius: 50%; border: 2px solid #047857; background: #ecfdf5; display: none;
        align-items: center; justify-content: center; text-align: center; line-height: 1.15;
        color: #065f46; font-weight: 700; box-shadow: inset 0 0 0 3px #fff; }
    .cash-count-sheet .cc-emblem b { display: block; font-size: 9px; color: #0f172a; }
    .cash-count-sheet .cc-emblem span { font-size: 7px; font-weight: 400; }
    @media (min-width: 640px) { .cash-count-sheet .cc-emblem { display: flex; } }
    .cash-count-sheet .cc-coop { margin: 0; font-size: 14px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: #0f172a; }
    .cash-count-sheet .cc-coop-sub { margin: 2px 0 0; font-size: 12px; font-weight: 600; color: #1e293b; }
    .cash-count-sheet .cc-coop-addr { margin: 2px 0 0; font-size: 11px; color: #475569; }
    .cash-count-sheet .cc-title { margin: 16px 0 0; font-size: 16px; font-weight: 800;
        text-transform: uppercase; letter-spacing: .08em; color: #0f172a; text-decoration: underline; }

    /* ---------- meta ---------- */
    .cash-count-sheet .cc-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px; margin-bottom: 16px; }
    .cash-count-sheet .cc-meta-right { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
    .cash-count-sheet .cc-meta-row { display: flex; align-items: baseline; gap: 8px; }
    .cash-count-sheet .cc-caption { font-size: 10px; color: #475569; font-weight: 500; }
    .cash-count-sheet .cc-label { font-weight: 600; color: #334155; }
    .cash-count-sheet .cc-name { width: 100%; font-weight: 700; font-size: 14px; color: #0f172a; }

    /* ---------- headings ---------- */
    .cash-count-sheet .cc-band { font-size: 12px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #1e293b; margin: 0 0 8px; }
    .cash-count-sheet .cc-subhead { font-size: 12px; font-weight: 700; margin: 0 0 4px; }

    /* ---------- two column grids ---------- */
    .cash-count-sheet .cc-grid { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 12px; margin-bottom: 16px; }
    .cash-count-sheet .cc-span-7 { grid-column: span 7 / span 7; }
    .cash-count-sheet .cc-span-5 { grid-column: span 5 / span 5; }

    /* ---------- tables ---------- */
    .cash-count-sheet .cc-table { width: 100%; border-collapse: collapse; }
    .cash-count-sheet .cc-table th,
    .cash-count-sheet .cc-table td { border: 1px solid #1f2937; padding: 4px 6px;
        text-align: center; vertical-align: middle; }
    .cash-count-sheet .cc-table th { font-size: 11px; font-weight: 700; background: #f8fafc; }
    .cash-count-sheet .cc-group { background: #f1f5f9; font-size: 12px; }
    .cash-count-sheet .cc-table tbody td { font-size: 11px; }
    .cash-count-sheet .cc-table-total tr td { font-size: 12px; font-weight: 700; background: #f8fafc; }
    .cash-count-sheet .cc-table-other tbody td { font-size: 12px; }
    .cash-count-sheet .cc-total td { font-weight: 700; background: #f8fafc; }
    .cash-count-sheet .cc-left { text-align: left; padding-left: 6px; }
    .cash-count-sheet .cc-right { text-align: right; padding-right: 6px; }

    /* Column widths - scoped, so the names can stay short. */
    .cash-count-sheet .ccw-16 { width: 16%; }
    .cash-count-sheet .ccw-20 { width: 20%; }
    .cash-count-sheet .ccw-25 { width: 25%; }
    .cash-count-sheet .ccw-30 { width: 30%; }
    .cash-count-sheet .ccw-35 { width: 35%; }
    .cash-count-sheet .ccw-40 { width: 40%; }
    .cash-count-sheet .ccw-45 { width: 45%; }
    .cash-count-sheet .ccw-55 { width: 55%; }
    .cash-count-sheet .ccw-70 { width: 70%; }
    .cash-count-sheet .ccw-75 { width: 75%; }

    /* ---------- inputs ---------- */
    .cash-count-sheet .cc-cell { width: 100%; border: 0; background: transparent; text-align: center;
        font: inherit; color: inherit; padding: 2px; }
    .cash-count-sheet .cc-cell-right { text-align: right; }
    .cash-count-sheet .cc-line { border: 0; border-bottom: 1px solid #000; background: transparent;
        font: inherit; color: inherit; padding: 1px 2px; }
    .cash-count-sheet .cc-border-cell { width: 100%; border: 0; border-bottom: 1px solid #000;
        background: transparent; text-align: right; font: inherit; color: inherit; padding: 1px 2px; }
    .cash-count-sheet .cc-cell:focus,
    .cash-count-sheet .cc-line:focus,
    .cash-count-sheet .cc-border-cell:focus { outline: 2px solid #059669; outline-offset: -1px; }
    .cash-count-sheet .cc-textarea { resize: vertical; min-height: 44px; }

    /* Bare paper: no number spinners. */
    .cash-count-sheet input[type=number]::-webkit-outer-spin-button,
    .cash-count-sheet input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .cash-count-sheet input[type=number] { -moz-appearance: textfield; }

    /* ---------- summary ---------- */
    .cash-count-sheet .cc-summary-wrap { display: flex; justify-content: flex-end; margin-bottom: 28px; }
    .cash-count-sheet .cc-summary { width: 100%; max-width: 420px; }
    .cash-count-sheet .cc-sum { display: flex; justify-content: space-between; align-items: baseline;
        gap: 12px; font-weight: 700; margin-bottom: 8px; }
    .cash-count-sheet .cc-sum-value { display: inline-block; width: 160px; padding: 0 4px;
        border-bottom: 1px solid #000; text-align: right; }
    .cash-count-sheet .cc-sum-value input { text-align: right; }

    /* ---------- signatures ---------- */
    .cash-count-sheet .cc-sign { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
    .cash-count-sheet .cc-sign-4 { grid-column: span 4 / span 4; }
    .cash-count-sheet .cc-sign-8 { grid-column: span 8 / span 8; }
    .cash-count-sheet .cc-gap { height: 64px; }
    .cash-count-sheet .cc-gap-sm { height: 48px; }
    .cash-count-sheet .cc-sign-input { width: 100%; text-align: center; font-weight: 700; }
    .cash-count-sheet .cc-sign-caption { font-size: 10px; text-align: center; color: #475569; }
    .cash-count-sheet .cc-sign-name { text-align: center; font-weight: 700; text-transform: uppercase;
        border-bottom: 1px solid #000; min-height: 16px; }
    .cash-count-sheet .cc-sign-right { width: 66%; margin-left: auto; padding-top: 24px; text-align: center; }
    .cash-count-sheet .cc-cert { font-size: 11px; line-height: 1.6; text-align: justify; margin: 0 0 14px; }
    .cash-count-sheet .cc-block { margin-bottom: 14px; }

    /* ---------- narrow screens ---------- */
    @media (max-width: 767px) {
        .cash-count-sheet .cc-sheet { padding: 20px 16px; }
        .cash-count-sheet .cc-grid,
        .cash-count-sheet .cc-sign { grid-template-columns: 1fr; }
        .cash-count-sheet .cc-span-7,
        .cash-count-sheet .cc-span-5,
        .cash-count-sheet .cc-sign-4,
        .cash-count-sheet .cc-sign-8 { grid-column: auto; }
        .cash-count-sheet .cc-summary { max-width: none; }
    }

    /* ---------- print ---------- */
    @media print {
        .cash-count-sheet .cc-bar { display: none !important; }
        .cash-count-sheet .cc-sheet { box-shadow: none !important; border: 0 !important;
            padding: 0 !important; max-width: 100% !important; }
        .cash-count-sheet .cc-cell { border: 0 !important; }
    }
</style>

<script>
    /*
     * Live totals. The server recomputes everything on submit, so this is only a
     * convenience - with JS off the sheet still saves the same numbers.
     */
    (function () {
        var peso = function (n) {
            return 'P ' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        var num = function (value) {
            var parsed = parseFloat(String(value === null || value === undefined ? '' : value).replace(/[^0-9.\-]/g, ''));
            return isNaN(parsed) ? 0 : parsed;
        };
        var byId = function (id) { return document.getElementById(id); };
        var val = function (id) { var el = byId(id); return el ? num(el.value) : 0; };
        var setAll = function (selector, text) {
            Array.prototype.forEach.call(document.querySelectorAll(selector), function (el) { el.textContent = text; });
        };

        function recalc() {
            var buckets = { bundles: 0, loose_bills: 0, rolls: 0, loose_coins: 0 };

            Array.prototype.forEach.call(document.querySelectorAll('.cc-qty'), function (el) {
                var amount = num(el.dataset.unit) * num(el.dataset.factor) * num(el.value);
                buckets[el.dataset.bucket] += amount;
                var target = byId('amt_' + el.name);
                if (target) { target.textContent = peso(amount); }
            });

            ['bundles', 'loose_bills', 'rolls', 'loose_coins'].forEach(function (key) {
                var target = byId('sub_' + key);
                if (target) { target.textContent = peso(buckets[key]); }
            });

            var currency = buckets.bundles + buckets.loose_bills + buckets.rolls + buckets.loose_coins;
            var items = val('checks') + val('advances') + val('others');
            var grand = currency + items;
            var onHand = val('cash_on_hand_books');

            setAll('.js-currency', peso(currency));
            setAll('.js-cash-items', peso(items));
            setAll('.js-grand', peso(grand));
            setAll('.js-over', peso(grand - onHand));
        }

        document.addEventListener('DOMContentLoaded', function () {
            Array.prototype.forEach.call(
                document.querySelectorAll('.cc-qty, #checks, #advances, #others, #cash_on_hand_books'),
                function (el) {
                    el.addEventListener('input', recalc);
                    el.addEventListener('change', recalc);
                }
            );
            recalc();
        });
    })();
</script>
