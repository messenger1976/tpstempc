<?php
// Fixed Beginning Balance loan notice. Expects $bb_balance from loan_model->get_bb_loan_balance().
if (empty($bb_balance) || !is_array($bb_balance)) {
    return;
}
?>
<div class="alert alert-info" style="margin-bottom: 12px;">
    <strong>Beginning Balance loan:</strong> no repayment schedule or computed penalty is used.
    The member owes the Beginning Balance amounts below, paid penalty first, then interest, then principal.
    <div style="margin-top: 6px;">
        Remaining &mdash;
        Principal <strong><?php echo number_format((float) $bb_balance['principal'], 2); ?></strong>,
        Interest <strong><?php echo number_format((float) $bb_balance['interest'], 2); ?></strong>,
        Penalty <strong><?php echo number_format((float) $bb_balance['penalty'], 2); ?></strong>,
        Total <strong><?php echo number_format((float) $bb_balance['total'], 2); ?></strong>
        <span class="text-muted">
            (Beginning Balance <?php echo number_format((float) ($bb_balance['original']['principal'] + $bb_balance['original']['interest'] + $bb_balance['original']['penalty']), 2); ?>)
        </span>
    </div>
</div>
