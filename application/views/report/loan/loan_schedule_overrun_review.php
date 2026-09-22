<?php
/**
 * Schedule overrun review.
 *
 * Read-only audit of loans whose stored repayment schedule does not amortise the
 * contract: rows carrying negative interest, or a schedule that repays more
 * principal than was lent. Those rows are what the Amount Due panels print as
 * Principal / Interest and what the repayment planner posts, so such a loan credits
 * principal it never had and credits interest income with a negative amount.
 *
 * Nothing here writes to the database. The repair per loan is: correct the
 * installment amount / installment count / interest rate on the contract, then
 * regenerate the schedule (Loan -> Repayment Schedule -> Generate Schedule).
 */
$review = isset($review) ? $review : array('rows' => array(), 'totals' => array(), 'count' => 0, 'shown' => 0, 'as_of' => date('Y-m-d'));
$rows = isset($review['rows']) ? $review['rows'] : array();
$totals = isset($review['totals']) ? $review['totals'] : array();
$money = function ($value) {
    return number_format((float) $value, 2);
};
?>
<style type="text/css">
.so-review .so-card { border: 1px solid #e3e8ee; border-radius: 8px; background: #fff; }
.so-review .so-head { padding: 12px 16px; border-bottom: 1px solid #e3e8ee; }
.so-review .so-head h4 { margin: 0; font-weight: 700; }
.so-review .so-summary { padding: 14px 16px; background: #f7fafc; border-bottom: 1px solid #e3e8ee; }
.so-review .so-metric { display: inline-block; margin-right: 28px; }
.so-review .so-metric .lbl { display: block; color: #6c757d; font-size: 12px; text-transform: uppercase; letter-spacing: .03em; }
.so-review .so-metric .val { font-size: 20px; font-weight: 700; font-variant-numeric: tabular-nums; }
.so-review .so-metric .val.bad { color: #c0392b; }
.so-review .so-note { padding: 10px 16px; border-bottom: 1px solid #e3e8ee; color: #6c757d; font-size: 13px; }
.so-review table.so-table th { background: #f7fafc; white-space: nowrap; }
.so-review table.so-table td.num { text-align: right; font-variant-numeric: tabular-nums; }
.so-review table.so-table tfoot td { background: #f7fafc; font-weight: 700; }
.so-review table.so-table td.neg { color: #c0392b; }
</style>

<div class="so-review">
    <div class="row">
        <div class="col-md-12">
            <div class="so-card">
                <div class="so-head">
                    <h4><i class="fa fa-exclamation-triangle"></i> Schedule overrun review &mdash; as of <?php echo htmlspecialchars($review['as_of']); ?></h4>
                </div>
                <div class="so-note">
                    These loans were generated with an installment amount that does not amortise the principal lent, so the
                    schedule repays the whole principal before the term ends and the Interest column goes negative.
                    <strong>Correct whichever figure is wrong (amount lent, installment amount, installment count or interest
                    rate), then regenerate the schedule</strong> (Loan &rarr; Repayment Schedule &rarr; Generate Schedule). New
                    releases and schedule rebuilds are now refused when the terms cannot amortise.
                    <?php if ((int) $review['shown'] < (int) $review['count']) { ?>
                    <br/><em>Showing the <?php echo (int) $review['shown']; ?> worst of <?php echo (int) $review['count']; ?> affected loans.</em>
                    <?php } ?>
                </div>
                <div class="so-summary">
                    <div class="so-metric">
                        <span class="lbl">Affected loans</span>
                        <span class="val bad"><?php echo (int) $review['count']; ?></span>
                    </div>
                    <div class="so-metric">
                        <span class="lbl">Negative-interest rows</span>
                        <span class="val bad"><?php echo (int) (isset($totals['negative_rows']) ? $totals['negative_rows'] : 0); ?></span>
                    </div>
                    <div class="so-metric">
                        <span class="lbl">Principal lent</span>
                        <span class="val"><?php echo $money(isset($totals['lent']) ? $totals['lent'] : 0); ?></span>
                    </div>
                    <div class="so-metric">
                        <span class="lbl">Principal in schedule</span>
                        <span class="val"><?php echo $money(isset($totals['schedule_principle']) ? $totals['schedule_principle'] : 0); ?></span>
                    </div>
                    <div class="so-metric">
                        <span class="lbl">Excess principal</span>
                        <span class="val bad"><?php echo $money(isset($totals['overrun']) ? $totals['overrun'] : 0); ?></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped so-table">
                        <thead>
                            <tr>
                                <th>Loan</th>
                                <th>Member</th>
                                <th>Product</th>
                                <th>Terms</th>
                                <th class="num">Installment on file</th>
                                <th class="num">Installment if terms held</th>
                                <th class="num">Difference</th>
                                <th class="num">Principal lent</th>
                                <th class="num">Principal in schedule</th>
                                <th class="num">Excess</th>
                                <th class="num">Negative rows</th>
                                <th class="num">Worst interest</th>
                                <th class="num">Open / paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)) { ?>
                            <tr>
                                <td colspan="13" class="text-center text-muted" style="padding:26px;">
                                    <i class="fa fa-check-circle"></i> No loan has a schedule that fails to amortise as of <?php echo htmlspecialchars($review['as_of']); ?>.
                                </td>
                            </tr>
                            <?php } ?>
                            <?php foreach ($rows as $entry) {
                                $loan = $entry['loan'];
                                $member_name = trim((string) $loan->firstname . ' ' . (string) $loan->middlename . ' ' . (string) $loan->lastname);
                                $terms = rtrim(rtrim(number_format((float) $loan->rate, 3, '.', ','), '0'), '.') . '%';
                                $terms .= ' / ' . (int) $loan->number_istallment . ' inst.';
                            ?>
                            <tr>
                                <td><a href="<?php echo site_url(current_lang() . '/loan/view_indetail/' . encode_id($loan->LID)); ?>" target="_blank"><?php echo htmlspecialchars($loan->LID); ?></a></td>
                                <td><?php echo htmlspecialchars(trim((string) $loan->member_id . ' ' . $member_name)); ?></td>
                                <td><?php echo htmlspecialchars((string) $loan->product_name); ?></td>
                                <td><?php echo htmlspecialchars($terms); ?></td>
                                <td class="num"><?php echo $money($loan->installment_amount); ?></td>
                                <td class="num"><?php echo $money($entry['correct_installment']); ?></td>
                                <td class="num <?php echo (abs($entry['installment_delta']) > 0.009) ? 'neg' : ''; ?>"><?php echo $money($entry['installment_delta']); ?></td>
                                <td class="num"><?php echo $money($entry['lent']); ?></td>
                                <td class="num"><?php echo $money($entry['schedule_principle']); ?></td>
                                <td class="num <?php echo ($entry['overrun'] > 0.009) ? 'neg' : ''; ?>"><?php echo $money($entry['overrun']); ?></td>
                                <td class="num <?php echo ($entry['negative_rows'] > 0) ? 'neg' : ''; ?>">
                                    <?php echo (int) $entry['negative_rows']; ?> / <?php echo (int) $loan->total_rows; ?>
                                </td>
                                <td class="num <?php echo ($entry['min_interest'] < 0) ? 'neg' : ''; ?>"><?php echo $money($entry['min_interest']); ?></td>
                                <td class="num"><?php echo (int) $entry['open_rows']; ?> / <?php echo (int) $entry['paid_rows']; ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <?php if (!empty($rows)) { ?>
                        <tfoot>
                            <tr>
                                <td colspan="7">TOTAL</td>
                                <td class="num"><?php echo $money(isset($totals['lent']) ? $totals['lent'] : 0); ?></td>
                                <td class="num"><?php echo $money(isset($totals['schedule_principle']) ? $totals['schedule_principle'] : 0); ?></td>
                                <td class="num"><?php echo $money(isset($totals['overrun']) ? $totals['overrun'] : 0); ?></td>
                                <td class="num"><?php echo (int) (isset($totals['negative_rows']) ? $totals['negative_rows'] : 0); ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
