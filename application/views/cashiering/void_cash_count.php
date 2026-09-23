<?php
/**
 * Void confirmation for a submitted cash count sheet.
 *
 * Nothing is deleted: the sheet is marked void with a reason so the correction stays
 * visible to whoever audits the fund later. Admin may void any sheet; a cashier only
 * their own report of the current day - the controller refuses anything else before
 * this page is ever rendered.
 *
 * Rendered inside the admin template, so it uses the app's Bootstrap 3 classes.
 */
$report = isset($report) ? $report : NULL;
$warning = isset($warning) ? $warning : '';
$totals = isset($totals) ? $totals : array();

$report_date = !empty($report->report_date) ? $report->report_date : '';
$accountable = isset($report->cashier_name) ? trim((string) $report->cashier_name) : '';
$reason_value = function_exists('set_value') ? set_value('void_reason') : '';

$peso = function ($amount) {
    return 'P ' . number_format((float) $amount, 2);
};
?>
<div class="cc-void-page">

    <?php if ($warning !== ''): ?>
        <div class="alert alert-warning"><?php echo htmlspecialchars($warning, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="alert alert-danger">
        <strong>Void this cash count sheet?</strong><br/>
        The report is not deleted. It is marked VOID with your reason, stops counting towards
        the daily totals, and stops feeding the next day's beginning cash. You will be sent back
        to a blank sheet for
        <?php echo htmlspecialchars(date('d-m-Y', strtotime($report_date)), ENT_QUOTES, 'UTF-8'); ?>
        so the count can be redone.
    </div>

    <table class="table table-bordered">
        <tbody>
            <tr>
                <th style="width:45%;">Report date</th>
                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($report_date)), ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr>
                <th>Name of accountable person</th>
                <td><?php echo htmlspecialchars($accountable, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr>
                <th>Total currency counted</th>
                <td><?php echo $peso(isset($totals['currency_total']) ? $totals['currency_total'] : 0); ?></td>
            </tr>
            <tr>
                <th>Total cash items counted</th>
                <td><?php echo $peso(isset($totals['cash_items']) ? $totals['cash_items'] : 0); ?></td>
            </tr>
            <tr>
                <th>Grand total</th>
                <td><strong><?php echo $peso(isset($report->grand_total) ? $report->grand_total : 0); ?></strong></td>
            </tr>
            <tr>
                <th>Cash on hand per books</th>
                <td><?php echo $peso(isset($report->expected_cash) ? $report->expected_cash : 0); ?></td>
            </tr>
            <tr>
                <th>Overages/(Shortages)</th>
                <td><strong><?php echo $peso(isset($report->over_short) ? $report->over_short : 0); ?></strong></td>
            </tr>
            <tr>
                <th>Submitted</th>
                <td><?php echo !empty($report->created_at) ? htmlspecialchars(date('d-m-Y H:i', strtotime($report->created_at)), ENT_QUOTES, 'UTF-8') : ''; ?></td>
            </tr>
        </tbody>
    </table>

    <form method="post" action="">
        <div class="form-group">
            <label for="void_reason">Reason for voiding <span class="text-danger">*</span></label>
            <textarea id="void_reason" name="void_reason" rows="3" maxlength="255" class="form-control"
                placeholder="e.g. wrong denominations keyed in, recount performed"><?php echo htmlspecialchars($reason_value, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <p class="help-block">Recorded against the report for audit purposes.</p>
        </div>

        <button type="submit" class="btn btn-danger">Void Report</button>
        <a class="btn btn-default" href="<?php echo site_url(current_lang() . '/cashiering/cash_count_sheet/' . (int) $report->id); ?>">Cancel</a>
    </form>

</div>
