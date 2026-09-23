<?php
$summary = isset($summary) ? $summary : array();
$recent_reports = isset($recent_reports) ? $recent_reports : array();
?>
<div class="cashiering-dashboard">
    <div class="ibox">
        <div class="ibox-title">
            <h5><?php echo lang('cashiering_dashboard'); ?></h5>
        </div>
        <div class="ibox-content">
            <div class="row m-b-md">
                <div class="col-md-6">
                    <form method="get" action="<?php echo site_url(current_lang() . '/cashiering/dashboard'); ?>">
                        <label for="report_date">Report Date</label>
                        <div class="input-group">
                            <input type="date" id="report_date" name="date" value="<?php echo htmlspecialchars($report_date); ?>" class="form-control">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit">Load</button>
                            </span>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <a href="<?php echo site_url(current_lang() . '/cashiering/cash_count_sheet?date=' . urlencode($report_date)); ?>" class="btn btn-success">
                        <i class="fa fa-calculator"></i> Cash Count Sheet
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="label">Beginning Cash</div>
                        <div class="value"><?php echo number_format(floatval($summary['beginning_cash'] ?? 0), 2); ?></div>
                        <?php if (!empty($summary['beginning_cash_carried_from'])): ?>
                            <div class="note">Carried from <?php echo date('d-m-Y', strtotime($summary['beginning_cash_carried_from'])); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box box-blue">
                        <div class="label">Cash In</div>
                        <div class="value"><?php echo number_format(floatval($summary['cash_in'] ?? 0), 2); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box box-red">
                        <div class="label">Cash Out</div>
                        <div class="value"><?php echo number_format(floatval($summary['cash_out'] ?? 0), 2); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box box-green">
                        <div class="label">Expected Cash</div>
                        <div class="value"><?php echo number_format(floatval($summary['expected_cash'] ?? 0), 2); ?></div>
                        <?php
                        /*
                         * A submitted sheet carries its own reconciled figure. When the two
                         * disagree, say so here rather than showing a number that silently
                         * contradicts the filed sheet - this is the tile the cashier
                         * reconciles against, so the gap has to be visible.
                         */
                        $filed_expected_cash = isset($summary['filed_expected_cash']) ? $summary['filed_expected_cash'] : NULL;
                        $unreconciled = isset($summary['unreconciled']) ? $summary['unreconciled'] : NULL;
                        ?>
                        <?php if ($filed_expected_cash !== NULL && $unreconciled !== NULL && abs($unreconciled) >= 0.005): ?>
                            <div class="note">
                                Filed sheet: <?php echo number_format($filed_expected_cash, 2); ?> &mdash;
                                <?php echo number_format(abs($unreconciled), 2); ?> unreconciled
                            </div>
                        <?php elseif ($filed_expected_cash !== NULL): ?>
                            <div class="note">Agrees with the filed sheet</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row m-t-lg">
                <div class="col-md-8">
                    <div class="ibox-content light-bg">
                        <h4>Recent Cash Count Reports</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Cashier</th>
                                        <th>Cash In</th>
                                        <th>Cash Out</th>
                                        <th>Variance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_reports)): ?>
                                        <?php foreach ($recent_reports as $row): ?>
                                            <tr>
                                                <td><?php echo date('d-m-Y', strtotime($row->report_date)); ?></td>
                                                <td><?php echo htmlspecialchars($row->cashier_name ?: '—'); ?></td>
                                                <td><?php echo number_format(floatval($row->cash_in), 2); ?></td>
                                                <td><?php echo number_format(floatval($row->cash_out), 2); ?></td>
                                                <td><?php echo number_format(floatval($row->over_short), 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No cash count reports yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ibox-content light-bg">
                        <h4>Operational Summary</h4>
                        <ul class="summary-list">
                            <li><span>Net Cash</span><strong><?php echo number_format(floatval($summary['net_cash'] ?? 0), 2); ?></strong></li>
                            <li><span>Receipts</span><strong><?php echo number_format(floatval($summary['cash_in'] ?? 0), 2); ?></strong></li>
                            <li><span>Disbursements</span><strong><?php echo number_format(floatval($summary['cash_out'] ?? 0), 2); ?></strong></li>
                            <li><span>Remaining Cash</span><strong><?php echo number_format(floatval($summary['expected_cash'] ?? 0), 2); ?></strong></li>
                            <?php if (isset($summary['filed_expected_cash']) && $summary['filed_expected_cash'] !== NULL): ?>
                                <li><span>Filed Expected Cash</span><strong><?php echo number_format(floatval($summary['filed_expected_cash']), 2); ?></strong></li>
                                <li><span>Unreconciled</span><strong><?php echo number_format(floatval($summary['unreconciled'] ?? 0), 2); ?></strong></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cashiering-dashboard .stat-box {
    background: #f6f7fb;
    border: 1px solid #e5e7ec;
    border-radius: 6px;
    padding: 18px 12px;
    margin-bottom: 20px;
    text-align: center;
}
.cashiering-dashboard .stat-box .label {
    color: #6b7280;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 600;
}
.cashiering-dashboard .stat-box .value {
    font-size: 26px;
    font-weight: 700;
    color: #1f2937;
    margin-top: 8px;
}
.cashiering-dashboard .stat-box .note { font-size: 11px; color: #6b7280; margin-top: 6px; }
.cashiering-dashboard .box-blue .value { color: #1d4ed8; }
.cashiering-dashboard .box-red .value { color: #dc2626; }
.cashiering-dashboard .box-green .value { color: #15803d; }
.cashiering-dashboard .light-bg { background: #fafafa; }
.cashiering-dashboard .summary-list { list-style: none; padding: 0; margin: 0; }
.cashiering-dashboard .summary-list li {
    display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;
}
</style>
