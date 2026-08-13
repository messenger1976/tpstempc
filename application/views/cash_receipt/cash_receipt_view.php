<?php
$receipt = isset($receipt) ? $receipt : null;
$id = isset($id) ? $id : '';
$is_popup = !empty($is_popup);
$list_url = site_url(current_lang() . '/cash_receipt/cash_receipt_list');
$line_items = isset($line_items) ? $line_items : array();

$payment_method_display = '';
if ($receipt) {
    $payment_method_display = isset($receipt->payment_method_display) ? $receipt->payment_method_display : $receipt->payment_method;
}
$show_cheque = $receipt && $payment_method_display
    && (stripos($payment_method_display, 'cheque') !== false || stripos($payment_method_display, 'check') !== false)
    && !empty($receipt->cheque_no);

$created_by_name = 'N/A';
if ($receipt && !empty($receipt->createdby)) {
    $user = $this->ion_auth->user($receipt->createdby)->row();
    if ($user) {
        $created_by_name = trim($user->first_name . ' ' . $user->last_name);
    }
}

$ae = isset($accounting_entries) ? $accounting_entries : array('journal' => null, 'items' => array());
$journal = isset($ae['journal']) ? $ae['journal'] : null;
$journal_items = isset($ae['items']) ? $ae['items'] : array();
$journal_source_label = (function_exists('journal_source_label'))
    ? journal_source_label($journal ? (isset($journal->reference_type) ? $journal->reference_type : 'cash_receipt') : 'cash_receipt')
    : lang('journal_source_cash_receipt');

$li_total_debit = 0;
$li_total_credit = 0;
foreach ($line_items as $item) {
    $li_total_debit += isset($item->debit) ? (float) $item->debit : 0;
    $li_total_credit += isset($item->credit) ? (float) $item->credit : (isset($item->amount) ? (float) $item->amount : 0);
}

$je_total_debit = 0;
$je_total_credit = 0;
foreach ($journal_items as $entry) {
    $je_total_debit += isset($entry->debit) ? (float) $entry->debit : 0;
    $je_total_credit += isset($entry->credit) ? (float) $entry->credit : 0;
}
$je_balanced = abs($je_total_debit - $je_total_credit) <= 0.001;
?>

<style type="text/css">
.cr-view-page { margin-top: 4px; }
.cr-view-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.cr-view-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.cr-view-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.cr-view-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.cr-view-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    flex-wrap: wrap;
}
.cr-view-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cr-view-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cr-view-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.cr-view-page .cbu-panel .panel-body { padding: 18px 20px; }
.cr-view-page .action-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.cr-view-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.cr-view-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.cr-view-page .status-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    margin-left: 8px;
}
.cr-view-page .status-pill.cancelled {
    background: #eef1f2;
    color: #676a6c;
}
.cr-view-page .status-pill.posted {
    background: #e8f8f5;
    color: #1ab394;
}
.cr-view-page .status-pill.source {
    background: #eef3fb;
    color: #3c6eae;
}
.cr-view-page .info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0;
}
.cr-view-page .info-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.cr-view-page .info-row:last-child { border-bottom: 0; }
.cr-view-page .info-row .lbl { color: #999; font-weight: 500; }
.cr-view-page .info-row .val {
    color: #2f4050;
    font-weight: 700;
    text-align: right;
    word-break: break-word;
}
.cr-view-page .amount-highlight {
    color: #1ab394 !important;
    font-size: 15px;
}
.cr-view-page .desc-box {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 13px;
    color: #2f4050;
    line-height: 1.5;
    white-space: pre-wrap;
}
.cr-view-page .view-table {
    margin: 0;
    background: #fff;
}
.cr-view-page .view-table > thead > tr > th {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.cr-view-page .view-table > tbody > tr > td {
    vertical-align: middle;
    font-size: 13px;
}
.cr-view-page .view-table .amount-cell {
    text-align: right;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #2f4050;
}
.cr-view-page .view-table .debit-cell { color: #c0392b; }
.cr-view-page .view-table .credit-cell { color: #1ab394; }
.cr-view-page .view-table > tfoot > tr > th,
.cr-view-page .view-table > tbody > tr.totals-row > td {
    background: #f8fafb;
    border-top: 2px solid #e7eaec;
    font-weight: 700;
}
.cr-view-page .balance-banner {
    margin-top: 12px;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
}
.cr-view-page .balance-banner.ok {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.cr-view-page .balance-banner.warn {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.cr-view-page .empty-note {
    color: #999;
    font-size: 13px;
    padding: 8px 0;
    text-align: center;
}
.cr-view-page .journal-note {
    color: #888;
    font-size: 12px;
    margin-top: 4px;
}
</style>

<div class="col-lg-12 cr-view-page">
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
                    <?php echo lang('cash_receipt_view'); ?>
                    <?php if ($receipt) { ?>
                        — <?php echo htmlspecialchars($receipt->receipt_no, ENT_QUOTES, 'UTF-8'); ?>
                    <?php } ?>
                    <?php if ($receipt && !empty($receipt->cancelled)) { ?>
                        <span class="status-pill cancelled"><?php echo lang('cancelled'); ?></span>
                    <?php } ?>
                    <?php if ($receipt && !empty($receipt->is_posted_to_gl)) { ?>
                        <span class="status-pill posted">Posted to GL</span>
                    <?php } ?>
                </h4>
            </div>
            <div class="action-btns">
                <?php if ($is_popup) { ?>
                    <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . $id); ?>" class="btn btn-default btn-sm" onclick="window.parent && window.parent.$ ? window.parent.$('#receiptModal').modal('hide') : window.parent.location.reload(); return false;">
                        <i class="fa fa-times"></i> <?php echo lang('close'); ?>
                    </a>
                <?php } else { ?>
                    <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                    </a>
                <?php } ?>
                <?php if ($receipt && has_role(6, 'Edit_cash_receipt')) { ?>
                    <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_edit/' . $id); ?>" class="btn btn-warning btn-sm"<?php echo $is_popup ? ' target="_top"' : ''; ?>>
                        <i class="fa fa-edit"></i> <?php echo lang('edit'); ?>
                    </a>
                <?php } ?>
                <?php if ($receipt) { ?>
                    <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_print/' . $id); ?>" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fa fa-print"></i> <?php echo lang('print'); ?>
                    </a>
                <?php } ?>
                <?php if (!$is_popup && $receipt && has_role(6, 'Journal_entry') && !empty($receipt->is_posted_to_gl) && !empty($receipt->journal_entry_id) && empty($receipt->cancelled)) { ?>
                    <a href="<?php echo site_url(current_lang() . '/finance/void_gl_posting_journal_entry/' . encode_id($receipt->journal_entry_id)); ?>"
                       onclick="return confirm('Void the GL posting only? The receipt and journal entry will stay; you can repost to GL from Journal Entry Review.');"
                       class="btn btn-warning btn-sm">
                        <i class="fa fa-undo"></i> Void GL Posting
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="panel-body">
            <?php if ($receipt) { ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-grid">
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_no'); ?></span><span class="val"><?php echo htmlspecialchars($receipt->receipt_no, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_date'); ?></span><span class="val"><?php echo htmlspecialchars(date('d-m-Y', strtotime($receipt->receipt_date)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_received_from'); ?></span><span class="val"><?php echo htmlspecialchars($receipt->received_from, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php if (!empty($receipt->loan_repayment_lid)) { ?>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_repayment'); ?></span><span class="val"><?php echo htmlspecialchars($receipt->loan_repayment_lid, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php } ?>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_payment_method'); ?></span><span class="val"><?php echo htmlspecialchars($payment_method_display, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php if ($show_cheque) { ?>
                                <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_cheque_no'); ?></span><span class="val"><?php echo htmlspecialchars($receipt->cheque_no, ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_bank_name'); ?></span><span class="val"><?php echo htmlspecialchars($receipt->bank_name, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php } ?>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_total_amount'); ?></span><span class="val amount-highlight"><?php echo number_format($receipt->total_amount, 2); ?></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-grid">
                            <div class="info-row"><span class="lbl"><?php echo lang('created_by'); ?></span><span class="val"><?php echo htmlspecialchars($created_by_name, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('created_at'); ?></span><span class="val"><?php echo htmlspecialchars(date('d-m-Y H:i:s', strtotime($receipt->created_at)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php if (!empty($receipt->updated_at)) { ?>
                                <div class="info-row"><span class="lbl"><?php echo lang('updated_at'); ?></span><span class="val"><?php echo htmlspecialchars(date('d-m-Y H:i:s', strtotime($receipt->updated_at)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <?php } ?>
                            <?php if (!empty($receipt->cancelled)) { ?>
                                <div class="info-row"><span class="lbl"><?php echo lang('status'); ?></span><span class="val"><span class="status-pill cancelled"><?php echo lang('cancelled'); ?></span></span></div>
                            <?php } ?>
                        </div>
                        <div style="margin-top: 14px;">
                            <div class="lbl" style="color:#999;font-weight:500;font-size:13px;margin-bottom:6px;"><?php echo lang('cash_receipt_description'); ?></div>
                            <div class="desc-box"><?php echo nl2br(htmlspecialchars($receipt->description, ENT_QUOTES, 'UTF-8')); ?></div>
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
                <i class="fa fa-list-alt icon-badge"></i>
                <h4><?php echo lang('cash_receipt_line_items'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <?php if (!empty($line_items)) { ?>
                <div class="table-responsive">
                    <table class="table table-striped view-table">
                        <thead>
                            <tr>
                                <th width="8%">#</th>
                                <th width="32%"><?php echo lang('cash_receipt_account'); ?></th>
                                <th width="30%"><?php echo lang('cash_receipt_line_description'); ?></th>
                                <th width="15%" class="text-right"><?php echo lang('journalentry_debit'); ?></th>
                                <th width="15%" class="text-right"><?php echo lang('journalentry_credit'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index = 1; foreach ($line_items as $item) {
                                $item_debit = isset($item->debit) ? (float) $item->debit : 0;
                                $item_credit = isset($item->credit) ? (float) $item->credit : (isset($item->amount) ? (float) $item->amount : 0);
                            ?>
                                <tr>
                                    <td><?php echo $index++; ?></td>
                                    <td><?php echo htmlspecialchars((isset($item->account_name) ? $item->account_name : '') . ' (' . (isset($item->account) ? $item->account : '') . ')', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(isset($item->description) ? $item->description : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="amount-cell debit-cell"><?php echo number_format($item_debit, 2); ?></td>
                                    <td class="amount-cell credit-cell"><?php echo number_format($item_credit, 2); ?></td>
                                </tr>
                            <?php } ?>
                            <tr class="totals-row">
                                <td colspan="3" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                                <td class="amount-cell debit-cell"><strong><?php echo number_format($li_total_debit, 2); ?></strong></td>
                                <td class="amount-cell credit-cell"><strong><?php echo number_format($li_total_credit, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
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
