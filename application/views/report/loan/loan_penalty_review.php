<?php
/**
 * Penalty recalibration review.
 *
 * Read-only comparison of every past-due loan under the legacy penalty rule
 * (any part of a month past the grace period is charged as a whole month) and the
 * current pro-rated rule (30-day periods counted from the end of the grace
 * period). Use it to sign off TAPSTEMCO_PENALTY_PRORATE before the change affects
 * live collections. Nothing here writes to the database.
 */
$review = isset($review) ? $review : array('rows' => array(), 'totals' => array(), 'count' => 0, 'as_of' => date('Y-m-d'));
$rows = isset($review['rows']) ? $review['rows'] : array();
$totals = isset($review['totals']) ? $review['totals'] : array();
$prorate_enabled = !empty($prorate_enabled);
$money = function ($value) {
    return number_format((float) $value, 2);
};
?>
<style type="text/css">
.pen-review .pr-card { border: 1px solid #e3e8ee; border-radius: 8px; background: #fff; }
.pen-review .pr-head { padding: 12px 16px; border-bottom: 1px solid #e3e8ee; }
.pen-review .pr-head h4 { margin: 0; font-weight: 700; }
.pen-review .pr-summary { padding: 14px 16px; background: #f7fafc; border-bottom: 1px solid #e3e8ee; }
.pen-review .pr-metric { display: inline-block; margin-right: 28px; }
.pen-review .pr-metric .lbl { display: block; color: #6c757d; font-size: 12px; text-transform: uppercase; letter-spacing: .03em; }
.pen-review .pr-metric .val { font-size: 20px; font-weight: 700; font-variant-numeric: tabular-nums; }
.pen-review .pr-metric .val.down { color: #2a7ab9; }
.pen-review .pr-metric .val.up { color: #c0392b; }
.pen-review table.pr-table th { background: #f7fafc; white-space: nowrap; }
.pen-review table.pr-table td.num { text-align: right; font-variant-numeric: tabular-nums; }
.pen-review table.pr-table tfoot td { background: #f7fafc; font-weight: 700; }
.pen-review .pr-flag { padding: 10px 16px; border-bottom: 1px solid #e3e8ee; }
</style>

<div class="pen-review">
    <div class="row">
        <div class="col-md-12">
            <div class="pr-card">
                <div class="pr-head">
                    <h4><i class="fa fa-balance-scale"></i> Penalty recalibration review &mdash; as of <?php echo htmlspecialchars($review['as_of']); ?></h4>
                </div>
                <div class="pr-flag">
                    <?php if ($prorate_enabled) { ?>
                    <span class="label label-info">Pro-rated rule is ACTIVE</span>
                    &nbsp;Penalties below are charged pro-rated over 30-day periods from the end of the grace period.
                    <?php } else { ?>
                    <span class="label label-warning">Legacy rule is ACTIVE</span>
                    &nbsp;Penalties below still charge any part of a month as a whole month.
                    <?php } ?>
                </div>
                <div class="pr-summary">
                    <div class="pr-metric">
                        <span class="lbl">Past-due loans</span>
                        <span class="val"><?php echo (int) $review['count']; ?></span>
                    </div>
                    <div class="pr-metric">
                        <span class="lbl">Penalty under legacy rule</span>
                        <span class="val"><?php echo $money(isset($totals['legacy_penalty']) ? $totals['legacy_penalty'] : 0); ?></span>
                    </div>
                    <div class="pr-metric">
                        <span class="lbl">Penalty under pro-rated rule</span>
                        <span class="val"><?php echo $money(isset($totals['current_penalty']) ? $totals['current_penalty'] : 0); ?></span>
                    </div>
                    <div class="pr-metric">
                        <span class="lbl">Reduction in collectible penalty</span>
                        <span class="val down"><?php echo $money(isset($totals['penalty_delta']) ? $totals['penalty_delta'] : 0); ?></span>
                    </div>
                    <div class="pr-metric">
                        <span class="lbl">Reduction in total amount due</span>
                        <span class="val down"><?php echo $money(isset($totals['due_delta']) ? $totals['due_delta'] : 0); ?></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped pr-table">
                        <thead>
                            <tr>
                                <th>Loan</th>
                                <th>Member</th>
                                <th>Product</th>
                                <th class="num">Overdue items</th>
                                <th class="num">Penalty (legacy)</th>
                                <th class="num">Penalty (pro-rated)</th>
                                <th class="num">Penalty reduction</th>
                                <th class="num">Amount due (legacy)</th>
                                <th class="num">Amount due (pro-rated)</th>
                                <th class="num">Due reduction</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)) { ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted" style="padding:26px;">
                                    <i class="fa fa-check-circle"></i> No past-due loans as of <?php echo htmlspecialchars($review['as_of']); ?>.
                                </td>
                            </tr>
                            <?php } ?>
                            <?php foreach ($rows as $entry) {
                                $loan = $entry['loan'];
                                $member_name = trim((string) $loan->firstname . ' ' . (string) $loan->middlename . ' ' . (string) $loan->lastname);
                            ?>
                            <tr>
                                <td><a href="<?php echo site_url(current_lang() . '/loan/view_indetail/' . encode_id($loan->LID)); ?>" target="_blank"><?php echo htmlspecialchars($loan->LID); ?></a></td>
                                <td><?php echo htmlspecialchars(trim((string) $loan->member_id . ' ' . $member_name)); ?></td>
                                <td><?php echo htmlspecialchars((string) $loan->product_name); ?></td>
                                <td class="num"><?php echo (int) $entry['overdue_count']; ?></td>
                                <td class="num"><?php echo $money($entry['legacy_penalty']); ?></td>
                                <td class="num"><?php echo $money($entry['current_penalty']); ?></td>
                                <td class="num <?php echo ($entry['penalty_delta'] > 0.009) ? 'text-info' : ''; ?>"><?php echo $money($entry['penalty_delta']); ?></td>
                                <td class="num"><?php echo $money($entry['legacy_due']); ?></td>
                                <td class="num"><?php echo $money($entry['current_due']); ?></td>
                                <td class="num <?php echo ($entry['due_delta'] > 0.009) ? 'text-info' : ''; ?>"><?php echo $money($entry['due_delta']); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <?php if (!empty($rows)) { ?>
                        <tfoot>
                            <tr>
                                <td colspan="4">TOTAL</td>
                                <td class="num"><?php echo $money(isset($totals['legacy_penalty']) ? $totals['legacy_penalty'] : 0); ?></td>
                                <td class="num"><?php echo $money(isset($totals['current_penalty']) ? $totals['current_penalty'] : 0); ?></td>
                                <td class="num"><?php echo $money(isset($totals['penalty_delta']) ? $totals['penalty_delta'] : 0); ?></td>
                                <td class="num"><?php echo $money(isset($totals['legacy_due']) ? $totals['legacy_due'] : 0); ?></td>
                                <td class="num"><?php echo $money(isset($totals['current_due']) ? $totals['current_due'] : 0); ?></td>
                                <td class="num"><?php echo $money(isset($totals['due_delta']) ? $totals['due_delta'] : 0); ?></td>
                            </tr>
                        </tfoot>
                        <?php } ?>
                    </table>
                </div>
                <div style="padding:14px 16px;" class="text-muted">
                    <i class="fa fa-info-circle"></i>
                    Already-posted penalties are never rewritten. Reducing collectible penalty means the coop
                    bills the smaller pro-rated figure from the next collection onwards. Review this table before
                    relying on it, and use the waiver workflow to clear any penalty already billed at the old rate.
                </div>
            </div>
        </div>
    </div>
</div>
