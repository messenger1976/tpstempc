<?php
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$this->load->view('member_app/_flash');
$statuses = array(
    '' => 'All',
    'awaiting_posting' => 'Paid - awaiting posting',
    'needs_review' => 'Paid - needs review',
    'paid' => 'Paid',
    'pending' => 'Pending',
    'expired' => 'Expired',
    'cancelled' => 'Cancelled',
    'failed' => 'Failed',
);
$needs_receipt = function ($r) {
    return $r->status === 'paid' && (!$r->cash_receipt_id || ((float) $r->fee_amount > 0 && empty($r->fee_receipt_id)));
};
$totals = array('count' => 0, 'paid' => 0.0, 'fees' => 0.0);
foreach ($rows as $r) {
    if ($r->status === 'paid') {
        $totals['count']++;
        $totals['paid'] += (float) $r->amount;
        $totals['fees'] += (float) $r->fee_amount;
    }
}
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>Online Loan Payments (Member App &middot; PayMongo QR Ph)</h5>
            <div class="ibox-tools">
                <?php if (has_role(9, 'Paymongo_settings') || $this->ion_auth->is_admin()) { ?>
                    <a class="btn btn-xs btn-default" href="<?php echo site_url(current_lang() . '/member_app/settings'); ?>"><i class="fa fa-cog"></i> Settings</a>
                <?php } ?>
            </div>
        </div>
        <div class="ibox-content">
            <?php if (!$readiness['ok']) { ?>
                <div class="alert alert-warning"><strong>Online payments are off for members:</strong> <?php echo $e($readiness['message']); ?></div>
            <?php } ?>
            <p class="text-muted small">
                A paid QR creates an <strong>unposted</strong> Cash Receipt (loan repayment). Review and post it in Cash Receipts;
                the loan balance changes only after posting. Fees are kept out of the loan.
            </p>

            <form method="get" class="form-inline" style="margin-bottom:12px">
                <select name="status" class="form-control input-sm">
                    <?php foreach ($statuses as $k => $label) { ?>
                        <option value="<?php echo $e($k); ?>" <?php echo $filters['status'] === $k ? 'selected' : ''; ?>><?php echo $e($label); ?></option>
                    <?php } ?>
                </select>
                <input type="date" name="from" value="<?php echo $e($filters['from']); ?>" class="form-control input-sm">
                <input type="date" name="to" value="<?php echo $e($filters['to']); ?>" class="form-control input-sm">
                <input type="text" name="key" value="<?php echo $e($filters['key']); ?>" class="form-control input-sm" placeholder="Reference, loan, member, receipt">
                <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-search"></i> Filter</button>
            </form>

            <div class="row" style="margin-bottom:10px">
                <div class="col-sm-4"><div class="well well-sm"><small>Paid payments shown</small><h3 style="margin:4px 0"><?php echo (int) $totals['count']; ?></h3></div></div>
                <div class="col-sm-4"><div class="well well-sm"><small>Applied to loans</small><h3 style="margin:4px 0"><?php echo number_format($totals['paid'], 2); ?></h3></div></div>
                <div class="col-sm-4"><div class="well well-sm"><small>Convenience fees</small><h3 style="margin:4px 0"><?php echo number_format($totals['fees'], 2); ?></h3></div></div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Member</th>
                        <th>Loan</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Fee</th>
                        <th>Status</th>
                        <th>Cash Receipt</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)) { ?>
                        <tr><td colspan="9" class="text-center text-muted">No online payments found.</td></tr>
                    <?php } ?>
                    <?php foreach ($rows as $r) {
                        $posted = !empty($r->loan_repayment_applied);
                        $label = array('paid' => 'success', 'pending' => 'warning', 'expired' => 'default', 'cancelled' => 'default', 'failed' => 'danger');
                        ?>
                        <tr>
                            <td><?php echo $e(date('d-m-Y H:i', strtotime($r->paid_at ? $r->paid_at : $r->create_date_time))); ?></td>
                            <td><code><?php echo $e($r->reference_no); ?></code><?php if ($r->payment_id) { ?><br><small class="text-muted"><?php echo $e($r->payment_id); ?></small><?php } ?></td>
                            <td><?php echo $e(trim($r->firstname . ' ' . $r->lastname)); ?><br><small class="text-muted"><?php echo $e($r->member_id); ?></small></td>
                            <td><?php echo $e($r->LID); ?></td>
                            <td class="text-right"><?php echo number_format((float) $r->amount, 2); ?></td>
                            <td class="text-right"><?php echo number_format((float) $r->fee_amount, 2); ?></td>
                            <td>
                                <span class="label label-<?php echo isset($label[$r->status]) ? $label[$r->status] : 'default'; ?>"><?php echo $e(ucfirst($r->status)); ?></span>
                                <?php if ($r->settle_error) { ?><div class="text-danger small" style="max-width:260px"><?php echo $e($r->settle_error); ?></div><?php } ?>
                            </td>
                            <td>
                                <?php if ($r->cash_receipt_id) { ?>
                                    <?php echo $e($r->receipt_no); ?>
                                    <?php if (!empty($r->cr_cancelled)) { ?><span class="label label-default">Cancelled</span>
                                    <?php } elseif ($posted) { ?><span class="label label-primary">Posted</span>
                                    <?php } else { ?><span class="label label-warning">Unposted</span><?php } ?>
                                <?php } else { ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php } ?>
                                <?php if (!empty($r->fee_receipt_no)) { ?>
                                    <br><small class="text-muted">Fee: <?php echo $e($r->fee_receipt_no); ?></small>
                                <?php } elseif ((float) $r->fee_amount > 0 && $r->status === 'paid') { ?>
                                    <br><small class="text-danger">Fee receipt missing</small>
                                <?php } ?>
                            </td>
                            <td class="text-nowrap">
                                <?php if (in_array($r->status, array('pending', 'expired', 'cancelled'), true)) { ?>
                                    <form method="post" action="<?php echo site_url(current_lang() . '/member_app/recheck/' . (int) $r->id); ?>" style="display:inline">
                                        <?php echo $csrf_field; ?>
                                        <button class="btn btn-xs btn-default" title="Ask PayMongo for the latest status">Re-check</button>
                                    </form>
                                <?php } ?>
                                <?php if ($needs_receipt($r)) { ?>
                                    <form method="post" action="<?php echo site_url(current_lang() . '/member_app/retry_receipt/' . (int) $r->id); ?>" style="display:inline" onsubmit="return confirm('Create the unposted Cash Receipt for <?php echo $e($r->reference_no); ?> now?');">
                                        <?php echo $csrf_field; ?>
                                        <button class="btn btn-xs btn-warning">Create receipt</button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
