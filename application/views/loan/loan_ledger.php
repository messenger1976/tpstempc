<?php
$loaninfo = isset($loaninfo) ? $loaninfo : null;
$ledger_transactions = isset($ledger_transactions) ? $ledger_transactions : array();
$basicinfo = isset($basicinfo) ? $basicinfo : ($loaninfo ? $this->member_model->member_basic_info(null, $loaninfo->PID)->row() : null);
$contactinfo = isset($contactinfo) ? $contactinfo : ($loaninfo ? $this->member_model->member_contact($loaninfo->PID) : null);

$gender_options = lang('member_genderoption');
$gender_label = ($basicinfo && isset($gender_options[$basicinfo->gender])) ? $gender_options[$basicinfo->gender] : ($basicinfo ? $basicinfo->gender : '');
$full_name = $basicinfo ? trim($basicinfo->firstname . ' ' . $basicinfo->middlename . ' ' . $basicinfo->lastname) : '';
$photo_url = $basicinfo ? member_avatar_url(isset($basicinfo->photo) ? $basicinfo->photo : '', isset($basicinfo->gender) ? $basicinfo->gender : '') : '';
$status_code = ($basicinfo && isset($basicinfo->status)) ? (string) $basicinfo->status : '';
$status_label = ($status_code === '1') ? lang('member_active') : (($status_code === '0') ? lang('member_inactive') : (($status_code === '2') ? 'Deleted' : lang('member_inactive')));
$status_active = ($status_code === '1');
$address = '';
if ($contactinfo) {
    if (!empty($contactinfo->physicaladdress)) {
        $address = $contactinfo->physicaladdress;
    } else if (!empty($contactinfo->postaladdress)) {
        $address = $contactinfo->postaladdress;
    } else if (!empty($contactinfo->officeaddress)) {
        $address = $contactinfo->officeaddress;
    }
}
$position = ($contactinfo && !empty($contactinfo->occupation)) ? $contactinfo->occupation : '';
$salary_grade = ($contactinfo && isset($contactinfo->salary_grade)) ? $contactinfo->salary_grade : '';

$product = $loaninfo ? $this->setting_model->loanproduct($loaninfo->product_type)->row() : null;
$interval = ($loaninfo && !empty($loaninfo->interval)) ? $this->setting_model->intervalinfo($loaninfo->interval)->row() : null;
$contribution = $loaninfo ? $this->contribution_model->contribution_balance($loaninfo->PID, $loaninfo->member_id) : null;
$share_data = $loaninfo ? $this->share_model->share_member_info($loaninfo->PID, $loaninfo->member_id) : null;
$saving = $loaninfo ? $this->finance_model->saving_account_balance_PID($loaninfo->PID, $loaninfo->member_id) : null;

$interval_label = '';
if ($interval) {
    $interval_label = isset($interval->description) ? $interval->description : (isset($interval->name) ? $interval->name : '');
}

$loan_lid = $loaninfo ? $loaninfo->LID : '';
$sum_paid = 0;
$total_debit = 0;
$total_credit = 0;
foreach ($ledger_transactions as $r) {
    $total_debit += (float) $r->debit;
    $total_credit += (float) $r->credit;
    if (isset($r->type) && $r->type === 'repayment' && !empty($r->amount_paid)) {
        $sum_paid += (float) $r->amount_paid;
    }
}
$closing_balance = $total_credit - $total_debit;
?>

<style type="text/css">
.loan-ledger-page { margin-top: 4px; }
.loan-ledger-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.loan-ledger-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.loan-ledger-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.loan-ledger-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.loan-ledger-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.loan-ledger-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.loan-ledger-page .cbu-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.loan-ledger-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.loan-ledger-page .cbu-panel .panel-body { padding: 18px 20px; }
.loan-ledger-page .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 18px;
}
@media (max-width: 767px) {
    .loan-ledger-page .info-grid { grid-template-columns: 1fr; }
}
.loan-ledger-page .info-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-ledger-page .info-row:last-child { border-bottom: 0; }
.loan-ledger-page .info-row .lbl { color: #999; font-weight: 500; }
.loan-ledger-page .info-row .val {
    color: #2f4050;
    font-weight: 700;
    text-align: right;
    word-break: break-word;
}
.loan-ledger-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    margin-bottom: 20px;
}
.loan-ledger-page .cbu-member-card { text-align: center; }
.loan-ledger-page .cbu-member-photo {
    width: 120px;
    height: 120px;
    margin: 0 auto 14px;
    border-radius: 50%;
    padding: 4px;
    background: #fff;
    border: 3px solid #1ab394;
    box-shadow: 0 4px 14px rgba(26,179,148,0.18);
    overflow: hidden;
}
.loan-ledger-page .cbu-member-photo img {
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    object-fit: cover;
    object-position: center top;
    border-radius: 50%;
    display: block;
}
.loan-ledger-page .cbu-member-photo.avatar-fallback img {
    object-fit: contain;
    object-position: center center;
    background: #e8f8f5;
}
.loan-ledger-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.loan-ledger-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.loan-ledger-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.loan-ledger-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.loan-ledger-page .cbu-member-details {
    text-align: left;
    margin: 0;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.loan-ledger-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-ledger-page .cbu-member-details li:last-child { border-bottom: 0; }
.loan-ledger-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.loan-ledger-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.loan-ledger-page .balance-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 14px;
}
@media (max-width: 767px) {
    .loan-ledger-page .balance-strip { grid-template-columns: 1fr; }
}
.loan-ledger-page .balance-item {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 10px 12px;
    text-align: center;
}
.loan-ledger-page .balance-item .lbl {
    display: block;
    color: #999;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 4px;
}
.loan-ledger-page .balance-item .val {
    display: block;
    color: #2f4050;
    font-size: 14px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.loan-ledger-page .ledger-note {
    background: #f0faf7;
    border: 1px solid #c9ebe3;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 16px;
    font-size: 13px;
    color: #2f4050;
    line-height: 1.45;
}
.loan-ledger-page .ledger-note strong {
    display: block;
    color: #0e7c69;
    margin-bottom: 4px;
}
.loan-ledger-page .ledger-table {
    margin: 0;
    background: #fff;
}
.loan-ledger-page .ledger-table > thead > tr > th {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.loan-ledger-page .ledger-table > tbody > tr > td,
.loan-ledger-page .ledger-table > tfoot > tr > th {
    vertical-align: middle;
    font-size: 13px;
}
.loan-ledger-page .ledger-table .amount-cell {
    text-align: right;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #2f4050;
}
.loan-ledger-page .ledger-table .debit-cell { color: #c0392b; }
.loan-ledger-page .ledger-table .credit-cell { color: #1ab394; }
.loan-ledger-page .ledger-table > tfoot > tr > th {
    background: #f8fafb;
    border-top: 2px solid #e7eaec;
    font-weight: 700;
}
.loan-ledger-page .type-pill {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    background: #eef1f2;
    color: #676a6c;
}
.loan-ledger-page .type-pill.disbursement {
    background: #e8f8f5;
    color: #1ab394;
}
.loan-ledger-page .type-pill.repayment {
    background: #eef3fb;
    color: #3c6eae;
}
.loan-ledger-page .type-pill.beginning {
    background: #fff8e6;
    color: #b8860b;
}
.loan-ledger-page .empty-note {
    color: #999;
    font-size: 13px;
    padding: 18px 0;
    text-align: center;
}
.loan-ledger-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.loan-ledger-page .dataTables_wrapper .dataTables_filter input,
.loan-ledger-page .dataTables_wrapper .dataTables_length select {
    border: 1px solid #e5e6e7;
    border-radius: 6px;
    height: 32px;
    padding: 4px 8px;
}
.loan-ledger-page .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #1ab394 !important;
    border-color: #1ab394 !important;
    color: #fff !important;
}
</style>

<div class="col-lg-12 loan-ledger-page">
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

    <div class="row">
        <div class="col-lg-4">
            <div class="cbu-preview" id="member_info">
                <?php if ($basicinfo) { ?>
                    <div class="cbu-member-card">
                        <?php
                        $has_real_photo = !empty($basicinfo->photo) && $basicinfo->photo !== '0' && strtolower($basicinfo->photo) !== 'avatar.gif';
                        ?>
                        <div class="cbu-member-photo<?php echo $has_real_photo ? '' : ' avatar-fallback'; ?>">
                            <img src="<?php echo htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars(member_avatar_url('', isset($basicinfo->gender) ? $basicinfo->gender : ''), ENT_QUOTES, 'UTF-8'); ?>';"/>
                        </div>
                        <h3 class="cbu-member-name"><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="cbu-member-badges">
                            <?php if (!empty($basicinfo->member_id)) { ?>
                                <span class="cbu-badge"><?php echo htmlspecialchars($basicinfo->member_id, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php } ?>
                            <span class="cbu-badge"><?php echo lang('member_pid'); ?> <?php echo htmlspecialchars($basicinfo->PID, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if ($gender_label !== '') { ?>
                                <span class="cbu-badge"><?php echo htmlspecialchars($gender_label, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php } ?>
                            <span class="cbu-badge<?php echo $status_active ? '' : ' inactive'; ?>"><?php echo lang('member_status'); ?>: <?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <ul class="cbu-member-details">
                            <li><span class="lbl"><?php echo lang('member_contact_address'); ?></span><span class="val"><?php echo $address !== '' ? htmlspecialchars($address, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_dob'); ?></span><span class="val"><?php echo !empty($basicinfo->dob) ? htmlspecialchars(format_date($basicinfo->dob, FALSE), ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_join_date'); ?></span><span class="val"><?php echo !empty($basicinfo->joiningdate) ? htmlspecialchars(format_date($basicinfo->joiningdate, FALSE), ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_phone1'); ?></span><span class="val"><?php echo ($contactinfo && !empty($contactinfo->phone1)) ? htmlspecialchars($contactinfo->phone1, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_position'); ?></span><span class="val"><?php echo $position !== '' ? htmlspecialchars($position, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_email'); ?></span><span class="val"><?php echo ($contactinfo && !empty($contactinfo->email)) ? htmlspecialchars($contactinfo->email, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_salary_grade'); ?></span><span class="val"><?php echo $salary_grade !== '' ? htmlspecialchars($salary_grade, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                        </ul>
                        <div class="balance-strip">
                            <div class="balance-item">
                                <span class="lbl"><?php echo lang('contribution_balance'); ?></span>
                                <span class="val"><?php echo $contribution ? number_format($contribution->balance, 2) : '0.00'; ?></span>
                            </div>
                            <div class="balance-item">
                                <span class="lbl"><?php echo lang('share_balance'); ?></span>
                                <span class="val"><?php echo $share_data ? number_format(($share_data->amount + $share_data->remainbalance), 2) : '0.00'; ?></span>
                            </div>
                            <div class="balance-item">
                                <span class="lbl"><?php echo lang('saving_balance'); ?></span>
                                <span class="val"><?php echo $saving ? number_format($saving->balance, 2) : '0.00'; ?></span>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="empty-note"><?php echo lang('no_records_found'); ?></div>
                <?php } ?>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="cbu-panel">
                <div class="panel-head">
                    <div class="head-left">
                        <i class="fa fa-file-text-o"></i>
                        <h4><?php echo lang('loan_info'); ?></h4>
                    </div>
                </div>
                <div class="panel-body">
                    <?php if ($loaninfo) { ?>
                        <div class="info-grid">
                            <div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_LID'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->LID, ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_product'); ?></span><span class="val"><?php echo $product ? htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loanproduct_interest'); ?></span><span class="val"><?php echo isset($loaninfo->rate) && $loaninfo->rate !== '' ? htmlspecialchars($loaninfo->rate, ENT_QUOTES, 'UTF-8') . '%' : '&mdash;'; ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_installment'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->number_istallment . ($interval_label !== '' ? ' ' . $interval_label : ''), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loanproduct_penalt_percentage'); ?></span><span class="val"><?php echo ($product && isset($product->penalt_percentage) && $product->penalt_percentage !== '' && $product->penalt_percentage !== null) ? htmlspecialchars($product->penalt_percentage, ENT_QUOTES, 'UTF-8') . '%' : '&mdash;'; ?></span></div>
                            </div>
                            <div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_applicationdate'); ?></span><span class="val"><?php echo !empty($loaninfo->applicationdate) ? htmlspecialchars(format_date($loaninfo->applicationdate, FALSE), ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_applied_amount'); ?></span><span class="val"><?php echo number_format($loaninfo->basic_amount, 2); ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_installment_amount'); ?></span><span class="val"><?php echo number_format($loaninfo->installment_amount, 2); ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_total_interest'); ?></span><span class="val"><?php echo number_format($loaninfo->total_interest_amount, 2); ?></span></div>
                                <div class="info-row"><span class="lbl"><?php echo lang('loan_total'); ?></span><span class="val"><?php echo number_format($loaninfo->total_loan, 2); ?></span></div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="empty-note"><?php echo lang('no_records_found'); ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-book"></i>
                <h4><?php echo lang('loan_ledger'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="ledger-note">
                <strong><?php echo lang('loan_ledger_advancement_lock_note_title'); ?></strong>
                <?php echo lang('loan_ledger_advancement_lock_note'); ?>
            </div>

            <?php if (!empty($ledger_transactions)) { ?>
                <div class="table-responsive">
                    <table class="table table-striped ledger-table dataTables-example" id="loanLedgerTable">
                        <thead>
                            <tr>
                                <th><?php echo lang('loan_ledger_date'); ?></th>
                                <th><?php echo lang('loan_ledger_description'); ?></th>
                                <th><?php echo lang('loan_ledger_schedule'); ?></th>
                                <th style="text-align:right;"><?php echo lang('loan_ledger_interest'); ?></th>
                                <th style="text-align:right;"><?php echo lang('loan_ledger_penalty'); ?></th>
                                <th style="text-align:right;"><?php echo lang('loan_ledger_amount_paid'); ?></th>
                                <th style="text-align:right;"><?php echo lang('loan_ledger_debit'); ?></th>
                                <th style="text-align:right;"><?php echo lang('loan_ledger_credit'); ?></th>
                                <th style="text-align:right;"><?php echo lang('loan_ledger_balance'); ?></th>
                                <th><?php echo lang('index_action_th'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $running_balance = 0;
                            foreach ($ledger_transactions as $row) {
                                $running_balance += (float) $row->credit - (float) $row->debit;
                                $is_repayment = isset($row->type) && $row->type === 'repayment';
                                $is_disbursement = isset($row->type) && $row->type === 'disbursement';
                                $desc = isset($row->description) ? $row->description : '';
                                $is_beginning = (stripos($desc, 'Beginning') !== FALSE);

                                $schedule_text = '&mdash;';
                                if ($is_repayment && (isset($row->schedule_installment) || isset($row->duedate))) {
                                    $parts = array();
                                    if (!empty($row->schedule_installment)) {
                                        $parts[] = lang('loan_installment') . ' ' . $row->schedule_installment;
                                    }
                                    if (!empty($row->duedate)) {
                                        $parts[] = format_date($row->duedate, FALSE);
                                    }
                                    $schedule_text = htmlspecialchars(implode(' / ', $parts), ENT_QUOTES, 'UTF-8');
                                }

                                $pill_class = $is_repayment ? 'repayment' : ($is_beginning ? 'beginning' : ($is_disbursement ? 'disbursement' : ''));
                                ?>
                                <tr>
                                    <td><?php echo format_date($row->date, FALSE); ?></td>
                                    <td>
                                        <span class="type-pill <?php echo $pill_class; ?>"><?php echo htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td><?php echo $schedule_text; ?></td>
                                    <td class="amount-cell"><?php echo $is_repayment && isset($row->interest) && $row->interest > 0 ? number_format($row->interest, 2) : '&mdash;'; ?></td>
                                    <td class="amount-cell"><?php echo $is_repayment && isset($row->penalt) && $row->penalt > 0 ? number_format($row->penalt, 2) : '&mdash;'; ?></td>
                                    <td class="amount-cell"><?php echo $is_repayment && isset($row->amount_paid) && $row->amount_paid > 0 ? number_format($row->amount_paid, 2) : '&mdash;'; ?></td>
                                    <td class="amount-cell debit-cell"><?php echo $row->debit > 0 ? number_format($row->debit, 2) : ''; ?></td>
                                    <td class="amount-cell credit-cell"><?php echo $row->credit > 0 ? number_format($row->credit, 2) : ''; ?></td>
                                    <td class="amount-cell"><?php echo number_format($running_balance, 2); ?></td>
                                    <td>
                                        <?php if ($is_repayment && !empty($row->receipt) && $loan_lid !== '') { ?>
                                            <a class="btn btn-warning btn-xs"
                                               href="<?php echo site_url(current_lang() . '/loan/void_loan_repayment/' . rawurlencode($row->receipt) . '?LID=' . encode_id($loan_lid)); ?>"
                                               onclick="return confirm('Void this repayment with a reversing GL entry? Schedule will reopen.');">
                                                <i class="fa fa-undo"></i> Void
                                            </a>
                                        <?php } else { echo '&mdash;'; } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" style="text-align:right;"><?php echo lang('loan_ledger_total'); ?></th>
                                <th class="amount-cell"><?php echo number_format($sum_paid, 2); ?></th>
                                <th class="amount-cell debit-cell"><?php echo number_format($total_debit, 2); ?></th>
                                <th class="amount-cell credit-cell"><?php echo number_format($total_credit, 2); ?></th>
                                <th class="amount-cell"><?php echo number_format($closing_balance, 2); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php } else { ?>
                <div class="empty-note"><?php echo lang('loan_ledger_no_transactions'); ?></div>
            <?php } ?>
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
