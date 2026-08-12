<?php
$loaninfo = isset($loaninfo) ? $loaninfo : null;
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
$can_approve = ($loaninfo && (string) $loaninfo->status !== '4');

$product = $loaninfo ? $this->setting_model->loanproduct($loaninfo->product_type)->row() : null;
$interval = ($loaninfo && $product) ? $this->setting_model->intervalinfo($loaninfo->interval)->row() : null;
$contribution = $loaninfo ? $this->contribution_model->contribution_balance($loaninfo->PID, $loaninfo->member_id) : null;
$share_data = $loaninfo ? $this->share_model->share_member_info($loaninfo->PID, $loaninfo->member_id) : null;
$saving = $loaninfo ? $this->finance_model->saving_account_balance_PID($loaninfo->PID, $loaninfo->member_id) : null;
$declaration = $loaninfo ? $this->loan_model->get_declaration($loaninfo->LID) : null;
$supporting_doc = $loaninfo ? $this->loan_model->get_supporting_doc($loaninfo->LID) : array();
$guarantor_list = $loaninfo ? $this->loan_model->get_guarantor(null, $loaninfo->LID)->result() : array();
$evaluation_histry = $loaninfo ? $this->loan_model->loan_evaluation_history($loaninfo->LID)->result() : array();
$approval_histry = $loaninfo ? $this->loan_model->loan_approval_history($loaninfo->LID)->result() : array();

$max_loan = 0;
$open_principle = 0;
$max_allowed = 0;
if ($product) {
    $max_loan = $product->loan_security_contribution_times * (isset($contribution->balance) ? $contribution->balance : 0);
    $open_loan = $this->db->query("SELECT * FROM loan_contract WHERE PID='" . $this->db->escape_str($loaninfo->PID) . "' AND approval=4")->result();
    $principles = 0;
    $amount_paid = 0;
    foreach ($open_loan as $value) {
        $paid_row = $this->db->query("SELECT SUM(amount) as amount FROM loan_repayment_receipt WHERE LID='" . $this->db->escape_str($value->LID) . "'")->row();
        $amount_paid += ($paid_row && isset($paid_row->amount)) ? (float) $paid_row->amount : 0;
        $principles += (float) $value->basic_amount;
    }
    $open_principle = $principles - $amount_paid;
    $max_allowed = $max_loan - $open_principle;
}

$status_options = array(
    '4' => 'Approved & Accepted',
    '2' => 'Approved & Rejected',
);
?>

<style type="text/css">
.loan-eval-page { margin-top: 4px; }
.loan-eval-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.loan-eval-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.loan-eval-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.loan-eval-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.loan-eval-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.loan-eval-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.loan-eval-page .cbu-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.loan-eval-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.loan-eval-page .cbu-panel .panel-body { padding: 18px 20px; }
.loan-eval-page .btn-edit-loan {
    border-radius: 6px;
    font-weight: 600;
    background: #1ab394;
    border-color: #1ab394;
}
.loan-eval-page .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 18px;
}
@media (max-width: 767px) {
    .loan-eval-page .info-grid { grid-template-columns: 1fr; }
}
.loan-eval-page .info-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-eval-page .info-row:last-child { border-bottom: 0; }
.loan-eval-page .info-row .lbl { color: #999; font-weight: 500; }
.loan-eval-page .info-row .val {
    color: #2f4050;
    font-weight: 700;
    text-align: right;
    word-break: break-word;
}
.loan-eval-page .metric-cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}
@media (max-width: 991px) {
    .loan-eval-page .metric-cards { grid-template-columns: 1fr; }
}
.loan-eval-page .metric-card {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 70%);
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 14px 16px;
}
.loan-eval-page .metric-card .lbl {
    display: block;
    color: #888;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 6px;
}
.loan-eval-page .metric-card .val {
    display: block;
    color: #2f4050;
    font-size: 18px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.loan-eval-page .metric-card.accent .val { color: #1ab394; }
.loan-eval-page .balance-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 14px;
}
@media (max-width: 767px) {
    .loan-eval-page .balance-strip { grid-template-columns: 1fr; }
}
.loan-eval-page .balance-item {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 10px 12px;
    text-align: center;
}
.loan-eval-page .balance-item .lbl {
    display: block;
    color: #999;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 4px;
}
.loan-eval-page .balance-item .val {
    display: block;
    color: #2f4050;
    font-size: 14px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.loan-eval-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    margin-bottom: 20px;
}
.loan-eval-page .cbu-member-card { text-align: center; }
.loan-eval-page .cbu-member-photo {
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
.loan-eval-page .cbu-member-photo img {
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    object-fit: cover;
    object-position: center top;
    border-radius: 50%;
    display: block;
}
.loan-eval-page .cbu-member-photo.avatar-fallback img {
    object-fit: contain;
    object-position: center center;
    background: #e8f8f5;
}
.loan-eval-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.loan-eval-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.loan-eval-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.loan-eval-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.loan-eval-page .cbu-member-details {
    text-align: left;
    margin: 0;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.loan-eval-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-eval-page .cbu-member-details li:last-child { border-bottom: 0; }
.loan-eval-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.loan-eval-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.loan-eval-page .section-divider {
    margin: 4px 0 14px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e7eaec;
    color: #2f4050;
    font-size: 14px;
    font-weight: 700;
}
.loan-eval-page .declaration-box {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 14px 16px;
    font-size: 13px;
    color: #2f4050;
    white-space: pre-wrap;
    line-height: 1.5;
    min-height: 70px;
}
.loan-eval-page .docs-table {
    border: 1px solid #e7eaec;
    border-radius: 8px;
    overflow: hidden;
}
.loan-eval-page .docs-table .table {
    margin: 0;
    background: #fff;
}
.loan-eval-page .docs-table .table > thead > tr > th {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
}
.loan-eval-page .docs-table .table > tbody > tr > td {
    vertical-align: middle;
    font-size: 13px;
}
.loan-eval-page .btn-link-action {
    color: #1ab394;
    font-weight: 600;
}
.loan-eval-page .guarantor-grid {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -8px;
}
.loan-eval-page .guarantor-grid .guarantor-col {
    width: 50%;
    padding: 0 8px 16px;
    box-sizing: border-box;
}
@media (max-width: 991px) {
    .loan-eval-page .guarantor-grid .guarantor-col { width: 100%; }
}
.loan-eval-page .guarantor-card {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    overflow: hidden;
    height: 100%;
}
.loan-eval-page .guarantor-card .g-head {
    padding: 12px 14px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    font-size: 13px;
    font-weight: 700;
    color: #2f4050;
}
.loan-eval-page .guarantor-card .g-body { padding: 12px 14px 14px; font-size: 13px; }
.loan-eval-page .guarantor-card .g-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 6px 0;
    border-bottom: 1px dashed #eef1f2;
}
.loan-eval-page .guarantor-card .g-row:last-child { border-bottom: 0; }
.loan-eval-page .guarantor-card .g-row .lbl { color: #999; font-weight: 500; }
.loan-eval-page .guarantor-card .g-row .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.loan-eval-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.loan-eval-page textarea.form-control { height: auto; min-height: 90px; }
.loan-eval-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.loan-eval-page .control-label {
    color: #676a6c;
    font-weight: 600;
    margin-bottom: 6px;
}
.loan-eval-page .eval-actions { margin-top: 14px; }
.loan-eval-page .eval-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 18px;
}
.loan-eval-page .history-item {
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 12px;
    background: #fff;
}
.loan-eval-page .history-item:last-child { margin-bottom: 0; }
.loan-eval-page .history-item.current {
    border-color: #9fd9ce;
    background: #f3fbf9;
    box-shadow: inset 3px 0 0 #1ab394;
}
.loan-eval-page .history-item .status-pill {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    background: #1ab394;
    margin-bottom: 8px;
}
.loan-eval-page .history-item .status-pill.rejected { background: #ed5565; }
.loan-eval-page .history-item .comment {
    color: #2f4050;
    font-size: 13px;
    margin-bottom: 8px;
    line-height: 1.45;
}
.loan-eval-page .history-item .meta {
    color: #999;
    font-size: 12px;
}
.loan-eval-page .empty-note {
    color: #999;
    font-size: 13px;
    padding: 12px 0;
}
</style>

<div class="col-lg-12 loan-eval-page">
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
                    <div class="info-grid">
                        <div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_LID'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->LID, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_product'); ?></span><span class="val"><?php echo $product ? htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loanproduct_interest'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->rate, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_installment'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->number_istallment . ($interval ? ' ' . $interval->name : ''), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_paysource'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->pay_source, ENT_QUOTES, 'UTF-8'); ?></span></div>
                        </div>
                        <div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_applicationdate'); ?></span><span class="val"><?php echo htmlspecialchars(format_date($loaninfo->applicationdate, FALSE), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_applied_amount'); ?></span><span class="val"><?php echo number_format($loaninfo->basic_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_installment_amount'); ?></span><span class="val"><?php echo number_format($loaninfo->installment_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_total_interest'); ?></span><span class="val"><?php echo number_format($loaninfo->total_interest_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_total'); ?></span><span class="val"><?php echo number_format($loaninfo->total_loan, 2); ?></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cbu-panel">
                <div class="panel-head">
                    <div class="head-left">
                        <i class="fa fa-calculator"></i>
                        <h4>Amount Allowed</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="metric-cards">
                        <div class="metric-card">
                            <span class="lbl">Normal Maximum loan</span>
                            <span class="val"><?php echo number_format($max_loan, 2); ?></span>
                        </div>
                        <div class="metric-card">
                            <span class="lbl">Opening Principles</span>
                            <span class="val"><?php echo number_format($open_principle, 2); ?></span>
                        </div>
                        <div class="metric-card accent">
                            <span class="lbl">Maximum Loan allowed</span>
                            <span class="val"><?php echo number_format($max_allowed, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-shield"></i>
                <h4><?php echo lang('loan_info_header'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-5">
                    <div class="section-divider"><?php echo lang('loan_security_declaration'); ?></div>
                    <div class="declaration-box"><?php echo htmlspecialchars(($declaration && isset($declaration->declaration)) ? $declaration->declaration : '', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="col-lg-7">
                    <div class="section-divider"><?php echo lang('loan_info_sopport'); ?></div>
                    <?php if (count($supporting_doc) > 0) { ?>
                        <div class="docs-table table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo lang('loan_supporting_document_comment'); ?></th>
                                        <th><?php echo lang('loan_supporting_document_doc'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($supporting_doc as $value) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($value->comment, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo anchor(base_url() . 'uploads/document/' . $value->file, lang('loan_supporting_document_view'), 'class="btn-link-action" target="_blank"'); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="empty-note"><?php echo lang('loan_doc_not_found'); ?></div>
                    <?php } ?>
                </div>
            </div>

            <div class="section-divider" style="margin-top: 18px;"><?php echo lang('loan_info_guarantor'); ?></div>
            <?php if (count($guarantor_list) > 0) { ?>
                <div class="guarantor-grid">
                    <?php foreach ($guarantor_list as $value) {
                        $customerinfo = $this->member_model->member_basic_info(null, $value->PID)->row();
                        $g_name = $customerinfo
                            ? trim($customerinfo->firstname . ' ' . $customerinfo->middlename . ' ' . $customerinfo->lastname)
                            : $value->PID;
                        ?>
                        <div class="guarantor-col">
                            <div class="guarantor-card">
                                <div class="g-head"><?php echo htmlspecialchars($g_name, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="g-body">
                                    <div class="g-row">
                                        <span class="lbl"><?php echo lang('member_member_id'); ?></span>
                                        <span class="val"><?php echo $customerinfo ? htmlspecialchars($customerinfo->member_id, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                    </div>
                                    <div class="g-row">
                                        <span class="lbl"><?php echo lang('loan_quarantor_relationship'); ?></span>
                                        <span class="val"><?php echo htmlspecialchars($value->relationship, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <div class="g-row">
                                        <span class="lbl"><?php echo lang('loan_quarantor_asset'); ?></span>
                                        <span class="val"><?php echo htmlspecialchars($value->declaration, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <div class="g-row">
                                        <span class="lbl"><?php echo lang('loan_quarantor_attachment'); ?></span>
                                        <span class="val">
                                            <?php
                                            if (!empty($value->file)) {
                                                echo anchor(base_url() . 'uploads/document/' . $value->file, lang('loan_quarantor_attachment_view'), 'class="btn-link-action" target="_blank"');
                                            } else {
                                                echo '&mdash;';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="empty-note"><?php echo lang('loan_guarantor_not_found'); ?></div>
            <?php } ?>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-check-square-o"></i>
                <h4><?php echo lang('evaluation_comment'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <?php if (count($evaluation_histry) > 0) { ?>
                <div class="row">
                    <?php foreach ($evaluation_histry as $value) {
                        $is_current = (isset($loaninfo->evaluated) && (string) $loaninfo->evaluated === (string) $value->status);
                        ?>
                        <div class="col-lg-6">
                            <div class="history-item<?php echo $is_current ? ' current' : ''; ?>">
                                <span class="status-pill"><?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?></span>
                                <div class="comment"><strong><?php echo lang('loan_comment'); ?>:</strong> <?php echo htmlspecialchars($value->comment, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="meta">
                                    <i class="fa fa-user"></i>
                                    <?php echo htmlspecialchars($value->first_name . ' ' . $value->last_name, ENT_QUOTES, 'UTF-8'); ?>
                                    &nbsp;&nbsp;
                                    <i class="fa fa-clock-o"></i>
                                    <?php echo htmlspecialchars($value->createdon, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="empty-note"><?php echo lang('no_records_found'); ?></div>
            <?php } ?>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-thumbs-o-up"></i>
                <h4><?php echo lang('loan_approval_comment'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-6">
                    <?php echo form_open_multipart(current_lang() . "/loan/loan_approval_action/" . $loanid); ?>
                    <div class="form-group">
                        <label class="control-label"><?php echo lang('loan_status'); ?> : <span class="required">*</span></label>
                        <select name="status" class="form-control">
                            <option value=""><?php echo lang('select_default_text'); ?></option>
                            <?php
                            $selected = set_value('status');
                            foreach ($status_options as $key => $value) {
                                ?>
                                <option <?php echo ((string) $key === (string) $selected ? 'selected="selected"' : ''); ?> value="<?php echo $key; ?>"><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                        <?php echo form_error('status'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?php echo lang('loan_comment'); ?> : <span class="required">*</span></label>
                        <textarea rows="3" name="comment" class="form-control"><?php echo set_value('comment'); ?></textarea>
                        <?php echo form_error('comment'); ?>
                    </div>
                    <?php if ($can_approve) { ?>
                        <div class="eval-actions">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-check"></i> <?php echo lang('loan_evaluated_test'); ?>
                            </button>
                        </div>
                    <?php } ?>
                    <?php echo form_close(); ?>
                </div>
                <div class="col-lg-6">
                    <?php if (count($approval_histry) > 0) { ?>
                        <?php foreach ($approval_histry as $value) {
                            $pill_class = ((string) $value->status === '2') ? ' rejected' : '';
                            ?>
                            <div class="history-item">
                                <span class="status-pill<?php echo $pill_class; ?>"><?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?></span>
                                <div class="comment"><strong><?php echo lang('loan_comment'); ?>:</strong> <?php echo htmlspecialchars($value->comment, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="meta">
                                    <i class="fa fa-user"></i>
                                    <?php echo htmlspecialchars($value->first_name . ' ' . $value->last_name, ENT_QUOTES, 'UTF-8'); ?>
                                    &nbsp;&nbsp;
                                    <i class="fa fa-clock-o"></i>
                                    <?php echo htmlspecialchars($value->createdon, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="empty-note"><?php echo lang('no_records_found'); ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
