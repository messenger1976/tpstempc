<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}
?>

<div class="col-lg-12">
    <!-- Loan Information header -->
    <?php
    $loaninfo = isset($loaninfo) ? $loaninfo : null;
    if ($loaninfo):
        $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
        $interval = $this->setting_model->intervalinfo($loaninfo->interval)->row();
        $memberinfo = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><?php echo lang('loan_info'); ?></h4>
        </div>
        <div class="panel-body">
            <table class="table table-condensed" style="margin-bottom: 0;">
                <tr>
                    <td><strong><?php echo lang('loan_LID'); ?>:</strong> <?php echo htmlspecialchars($loaninfo->LID); ?></td>
                    <td><strong><?php echo lang('member_name'); ?>:</strong> <?php echo htmlspecialchars($memberinfo->member_id . ' - ' . trim($memberinfo->firstname . ' ' . $memberinfo->middlename . ' ' . $memberinfo->lastname)); ?></td>
                    <td><strong><?php echo lang('loan_product'); ?>:</strong> <?php echo htmlspecialchars($product->name); ?></td>
                </tr>
                <tr>
                    <td><strong><?php echo lang('loan_applied_amount'); ?>:</strong> <?php echo number_format($loaninfo->basic_amount, 2); ?></td>
                    <td><strong><?php echo lang('loan_installment_amount'); ?>:</strong> <?php echo number_format($loaninfo->installment_amount, 2); ?></td>
                    <td><strong><?php echo lang('loan_total'); ?>:</strong> <?php echo number_format($loaninfo->total_loan, 2); ?></td>
                </tr>
                <tr>
                    <td><strong><?php echo lang('loanproduct_interest'); ?>:</strong> <?php echo isset($loaninfo->rate) ? htmlspecialchars($loaninfo->rate) . '%' : '—'; ?></td>
                    <td><strong><?php echo lang('loan_installment'); ?>:</strong> <?php echo $loaninfo->number_istallment . ' ' . (isset($interval->description) ? $interval->description : (isset($interval->name) ? $interval->name : '')); ?></td>
                    <td><strong><?php echo lang('loanproduct_penalt_percentage'); ?>:</strong> <?php echo (isset($product->penalt_percentage) && $product->penalt_percentage !== '' && $product->penalt_percentage !== null) ? htmlspecialchars($product->penalt_percentage) . '%' : '—'; ?></td>
                </tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Loan Ledger transactions -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><?php echo lang('loan_ledger'); ?></h4>
        </div>
        <div class="panel-body">
            <div class="alert alert-info small" style="margin-bottom: 15px;">
                <strong><?php echo lang('loan_ledger_advancement_lock_note_title'); ?></strong>
                <?php echo lang('loan_ledger_advancement_lock_note'); ?>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover dataTables-example" id="loanLedgerTable">
                    <thead>
                        <tr>
                            <th><?php echo lang('loan_ledger_date'); ?></th>
                            <th><?php echo lang('loan_ledger_description'); ?></th>
                            <th><?php echo lang('loan_ledger_schedule'); ?></th>
                            <th style="text-align: right;"><?php echo lang('loan_ledger_interest'); ?></th>
                            <th style="text-align: right;"><?php echo lang('loan_ledger_penalty'); ?></th>
                            <th style="text-align: right;"><?php echo lang('loan_ledger_amount_paid'); ?></th>
                            <th style="text-align: right;"><?php echo lang('loan_ledger_debit'); ?></th>
                            <th style="text-align: right;"><?php echo lang('loan_ledger_credit'); ?></th>
                            <th style="text-align: right;"><?php echo lang('loan_ledger_balance'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ledger_transactions = isset($ledger_transactions) ? $ledger_transactions : array();
                        $running_balance = 0;
                        foreach ($ledger_transactions as $row) {
                            $running_balance += (float)$row->credit - (float)$row->debit;
                            $is_repayment = isset($row->type) && $row->type === 'repayment';
                            $schedule_text = '—';
                            if ($is_repayment && (isset($row->schedule_installment) || isset($row->duedate))) {
                                $p = array();
                                if (!empty($row->schedule_installment)) $p[] = lang('loan_installment') . ' ' . $row->schedule_installment;
                                if (!empty($row->duedate)) $p[] = format_date($row->duedate, FALSE);
                                $schedule_text = implode(' / ', $p);
                            }
                            ?>
                        <tr>
                            <td><?php echo format_date($row->date, FALSE); ?></td>
                            <td><?php echo htmlspecialchars($row->description); ?></td>
                            <td><?php echo $schedule_text; ?></td>
                            <td style="text-align: right;"><?php echo $is_repayment && isset($row->interest) && $row->interest > 0 ? number_format($row->interest, 2) : '—'; ?></td>
                            <td style="text-align: right;"><?php echo $is_repayment && isset($row->penalt) && $row->penalt > 0 ? number_format($row->penalt, 2) : '—'; ?></td>
                            <td style="text-align: right;"><?php echo $is_repayment && isset($row->amount_paid) && $row->amount_paid > 0 ? number_format($row->amount_paid, 2) : '—'; ?></td>
                            <td style="text-align: right;"><?php echo $row->debit > 0 ? number_format($row->debit, 2) : ''; ?></td>
                            <td style="text-align: right;"><?php echo $row->credit > 0 ? number_format($row->credit, 2) : ''; ?></td>
                            <td style="text-align: right;"><?php echo number_format($running_balance, 2); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr class="info">
                            <th colspan="5" style="text-align: right;"><?php echo lang('loan_ledger_total'); ?></th>
                            <th style="text-align: right;"><?php
                                $sum_paid = 0;
                                foreach ($ledger_transactions as $r) {
                                    if (isset($r->type) && $r->type === 'repayment' && !empty($r->amount_paid)) $sum_paid += (float)$r->amount_paid;
                                }
                                echo number_format($sum_paid, 2);
                            ?></th>
                            <th style="text-align: right;"><?php
                                $total_debit = 0;
                                $total_credit = 0;
                                foreach ($ledger_transactions as $r) {
                                    $total_debit += (float)$r->debit;
                                    $total_credit += (float)$r->credit;
                                }
                                echo number_format($total_debit, 2);
                            ?></th>
                            <th style="text-align: right;"><?php echo number_format($total_credit, 2); ?></th>
                            <th style="text-align: right;"><?php echo number_format($total_credit - $total_debit, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php if (empty($ledger_transactions)): ?>
            <p class="text-muted"><?php echo lang('loan_ledger_no_transactions'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">
<script src="<?php echo base_url(); ?>assets/js/plugins/dataTables/datatables.min.js"></script>
<script>
(function() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
        jQuery(document).ready(function() {
            var table = jQuery('#loanLedgerTable');
            if (table.length && table.find('tbody tr').length > 0) {
                table.DataTable({
                    order: [[0, 'asc']],
                    pageLength: 25,
                    responsive: true
                });
            }
        });
    }
})();
</script>
