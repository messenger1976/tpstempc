<?php
$disburse = isset($disburse) ? $disburse : null;
$id = isset($id) ? $id : '';
$list_url = site_url(current_lang() . '/cash_disbursement/cash_disbursement_list');

$payment_method_display = '';
if ($disburse) {
    $payment_method_display = isset($disburse->payment_method_display) ? $disburse->payment_method_display : $disburse->payment_method;
}
$show_cheque = $disburse && $payment_method_display
    && (stripos($payment_method_display, 'cheque') !== false || stripos($payment_method_display, 'check') !== false)
    && !empty($disburse->cheque_no);

$created_by_name = 'N/A';
if ($disburse && !empty($disburse->createdby)) {
    $user = $this->ion_auth->user($disburse->createdby)->row();
    if ($user) {
        $created_by_name = trim($user->first_name . ' ' . $user->last_name);
    }
}

$ae = isset($accounting_entries) ? $accounting_entries : array('journal' => null, 'items' => array());
$journal = isset($ae['journal']) ? $ae['journal'] : null;
$journal_items = isset($ae['items']) ? $ae['items'] : array();
$journal_source_label = (function_exists('journal_source_label'))
    ? journal_source_label($journal ? (isset($journal->reference_type) ? $journal->reference_type : 'cash_disbursement') : 'cash_disbursement')
    : lang('journal_source_cash_disbursement');

$je_total_debit = 0;
$je_total_credit = 0;
foreach ($journal_items as $entry) {
    $je_total_debit += isset($entry->debit) ? (float) $entry->debit : 0;
    $je_total_credit += isset($entry->credit) ? (float) $entry->credit : 0;
}
$je_balanced = abs($je_total_debit - $je_total_credit) <= 0.001;
?>

<style type="text/css">
.cd-view-page { margin-top: 4px; }
.cd-view-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.cd-view-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.cd-view-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.cd-view-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.cd-view-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    flex-wrap: wrap;
}
.cd-view-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cd-view-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cd-view-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.cd-view-page .cbu-panel .panel-body { padding: 18px 20px; }
.cd-view-page .action-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.cd-view-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.cd-view-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.cd-view-page .status-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    margin-left: 8px;
}
.cd-view-page .status-pill.cancelled {
    background: #eef1f2;
    color: #676a6c;
}
.cd-view-page .status-pill.posted {
    background: #e8f8f5;
    color: #1ab394;
}
.cd-view-page .status-pill.source {
    background: #eef3fb;
    color: #3c6eae;
}
.cd-view-page .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 18px;
}
@media (max-width: 767px) {
    .cd-view-page .info-grid { grid-template-columns: 1fr; }
}
.cd-view-page .info-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.cd-view-page .info-row:last-child { border-bottom: 0; }
.cd-view-page .info-row .lbl { color: #999; font-weight: 500; }
.cd-view-page .info-row .val {
    color: #2f4050;
    font-weight: 700;
    text-align: right;
    word-break: break-word;
}
.cd-view-page .amount-highlight {
    color: #1ab394 !important;
    font-size: 15px;
}
.cd-view-page .desc-box {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 13px;
    color: #2f4050;
    line-height: 1.5;
    white-space: pre-wrap;
}
.cd-view-page .view-table {
    margin: 0;
    background: #fff;
}
.cd-view-page .view-table > thead > tr > th {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.cd-view-page .view-table > tbody > tr > td {
    vertical-align: middle;
    font-size: 13px;
}
.cd-view-page .view-table .amount-cell {
    text-align: right;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #2f4050;
}
.cd-view-page .view-table .debit-cell { color: #c0392b; }
.cd-view-page .view-table .credit-cell { color: #1ab394; }
.cd-view-page .view-table > tfoot > tr > th,
.cd-view-page .view-table > tbody > tr.totals-row > td {
    background: #f8fafb;
    border-top: 2px solid #e7eaec;
    font-weight: 700;
}
.cd-view-page .balance-banner {
    margin-top: 12px;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
}
.cd-view-page .balance-banner.ok {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.cd-view-page .balance-banner.warn {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.cd-view-page .empty-note {
    color: #999;
    font-size: 13px;
    padding: 8px 0;
    text-align: center;
}
.cd-view-page .journal-note {
    color: #888;
    font-size: 12px;
    margin-top: 4px;
}
</style>

<div class="col-lg-12 cd-view-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="cbu-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="cbu-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="cbu-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="cbu-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-file-text-o icon-badge"></i>
                <h4>
                    <?php echo lang('cash_disbursement_view'); ?>
                    <?php if ($disburse) { ?>
                        — <?php echo htmlspecialchars($disburse->disburse_no, ENT_QUOTES, 'UTF-8'); ?>
                    <?php } ?>
                    <?php if ($disburse && !empty($disburse->cancelled)) { ?>
                        <span class="status-pill cancelled"><?php echo lang('cancelled'); ?></span>
                    <?php } ?>
                    <?php if ($disburse && !empty($disburse->is_posted_to_gl)) { ?>
                        <span class="status-pill posted">Posted to GL</span>
                    <?php } ?>
                </h4>
            </div>
            <div class="action-btns">
                <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                </a>
                <?php if ($disburse && has_role(6, 'Edit_cash_disbursement') && empty($disburse->is_posted_to_gl)) { ?>
                    <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_edit/' . $id); ?>" class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i> <?php echo lang('edit'); ?>
                    </a>
                <?php } ?>
                <?php if ($disburse) { ?>
                    <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_print/' . $id); ?>" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fa fa-print"></i> <?php echo lang('print'); ?>
                    </a>
                <?php } ?>
                <?php if ($disburse && has_role(6, 'Journal_entry') && !empty($disburse->is_posted_to_gl) && !empty($disburse->journal_entry_id) && empty($disburse->cancelled)) { ?>
                    <a href="<?php echo site_url(current_lang() . '/finance/void_gl_posting_journal_entry/' . encode_id($disburse->journal_entry_id)); ?>"
                       onclick="return confirm('Void the GL posting only? The disbursement and journal entry will stay; you can repost to GL from Journal Entry Review.');"
                       class="btn btn-warning btn-sm">
                        <i class="fa fa-undo"></i> Void GL Posting
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="panel-body">
            <?php if ($disburse) { ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-grid" style="grid-template-columns: 1fr;">
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_disbursement_no'); ?></span><span class="val"><?php echo htmlspecialchars($disburse->disburse_no, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_disbursement_date'); ?></span><span class="val"><?php echo htmlspecialchars(date('d-m-Y', strtotime($disburse->disburse_date)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_disbursement_paid_to'); ?></span><span class="val"><?php echo htmlspecialchars($disburse->paid_to, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php if (!empty($disburse->loan_release_lid)) { ?>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_release_loan'); ?></span><span class="val"><?php echo htmlspecialchars($disburse->loan_release_lid, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php } ?>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_disbursement_payment_method'); ?></span><span class="val"><?php echo htmlspecialchars($payment_method_display, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php if ($show_cheque) { ?>
                                <div class="info-row"><span class="lbl"><?php echo lang('cash_disbursement_cheque_no'); ?></span><span class="val"><?php echo htmlspecialchars($disburse->cheque_no, ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('cash_disbursement_bank_name'); ?></span><span class="val"><?php echo htmlspecialchars($disburse->bank_name, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php } ?>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_disbursement_total_amount'); ?></span><span class="val amount-highlight"><?php echo number_format($disburse->total_amount, 2); ?></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-grid" style="grid-template-columns: 1fr;">
                            <div class="info-row"><span class="lbl"><?php echo lang('created_by'); ?></span><span class="val"><?php echo htmlspecialchars($created_by_name, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('created_at'); ?></span><span class="val"><?php echo htmlspecialchars(date('d-m-Y H:i:s', strtotime($disburse->created_at)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php if (!empty($disburse->updated_at)) { ?>
                                <div class="info-row"><span class="lbl"><?php echo lang('updated_at'); ?></span><span class="val"><?php echo htmlspecialchars(date('d-m-Y H:i:s', strtotime($disburse->updated_at)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php } ?>
                            <?php if (!empty($disburse->cancelled)) { ?>
                                <div class="info-row"><span class="lbl"><?php echo lang('status'); ?></span><span class="val"><span class="status-pill cancelled"><?php echo lang('cancelled'); ?></span></span></div>
                            <?php } ?>
                        </div>
                        <div style="margin-top: 14px;">
                            <div class="lbl" style="color:#999;font-weight:500;font-size:13px;margin-bottom:6px;"><?php echo lang('cash_disbursement_description'); ?></div>
                            <div class="desc-box"><?php echo nl2br(htmlspecialchars($disburse->description, ENT_QUOTES, 'UTF-8')); ?></div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="empty-note"><?php echo lang('no_records_found'); ?></div>
            <?php } ?>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-book icon-badge"></i>
                <h4>
                    <?php echo lang('accounting_entries'); ?>
                    <?php if ($journal_source_label) { ?>
                        <span class="status-pill source"><?php echo htmlspecialchars($journal_source_label, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php } ?>
                </h4>
            </div>
        </div>
        <div class="panel-body">
            <?php if ($journal && !empty($journal->description)) { ?>
                <div class="journal-note"><?php echo htmlspecialchars($journal->description, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php } ?>

            <?php if (!empty($journal_items)) { ?>
                <div class="table-responsive" style="margin-top: 10px;">
                    <table class="table table-striped view-table">
                        <thead>
                            <tr>
                                <th width="30%"><?php echo lang('account'); ?></th>
                                <th width="35%"><?php echo lang('description'); ?></th>
                                <th width="17.5%" class="text-right"><?php echo lang('journalentry_debit'); ?></th>
                                <th width="17.5%" class="text-right"><?php echo lang('journalentry_credit'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($journal_items as $entry) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((isset($entry->account_name) ? $entry->account_name : '') . ' (' . (isset($entry->account) ? $entry->account : '') . ')', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(isset($entry->description) ? $entry->description : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="amount-cell debit-cell"><?php echo number_format(isset($entry->debit) ? (float) $entry->debit : 0, 2); ?></td>
                                    <td class="amount-cell credit-cell"><?php echo number_format(isset($entry->credit) ? (float) $entry->credit : 0, 2); ?></td>
                                </tr>
                            <?php } ?>
                            <tr class="totals-row">
                                <td colspan="2" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                                <td class="amount-cell debit-cell"><strong><?php echo number_format($je_total_debit, 2); ?></strong></td>
                                <td class="amount-cell credit-cell"><strong><?php echo number_format($je_total_credit, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php if ($je_balanced) { ?>
                    <div class="balance-banner ok">
                        <i class="fa fa-check-circle"></i> <?php echo lang('debits_credits_balanced'); ?>
                    </div>
                <?php } else { ?>
                    <div class="balance-banner warn">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong><?php echo lang('warning'); ?>:</strong> <?php echo lang('debits_credits_not_balanced'); ?>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="empty-note"><?php echo lang('no_accounting_entries'); ?></div>
            <?php } ?>
        </div>
    </div>
</div>
