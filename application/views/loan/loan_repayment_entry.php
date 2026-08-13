<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">
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

$loan_product = $loaninfo ? $this->setting_model->loanproduct($loaninfo->product_type)->row() : null;
$loan_interval = ($loaninfo && !empty($loaninfo->interval)) ? $this->setting_model->intervalinfo($loaninfo->interval)->row() : null;
$contribution = $loaninfo ? $this->contribution_model->contribution_balance($loaninfo->PID, $loaninfo->member_id) : null;
$share_data = $loaninfo ? $this->share_model->share_member_info($loaninfo->PID, $loaninfo->member_id) : null;
$saving = $loaninfo ? $this->finance_model->saving_account_balance_PID($loaninfo->PID, $loaninfo->member_id) : null;

$interval_label = '';
if ($loan_interval) {
    $interval_label = isset($loan_interval->description) ? $loan_interval->description : (isset($loan_interval->name) ? $loan_interval->name : '');
}

$disburse_row = null;
if ($loaninfo && $this->db->table_exists('loan_contract_disburse')) {
    $this->db->where('LID', $loaninfo->LID);
    $this->db->where('PIN', $loaninfo->PIN);
    $this->db->order_by('disbursedate', 'ASC');
    $this->db->limit(1);
    $disburse_row = $this->db->get('loan_contract_disburse')->row();
}

$first_due_row = null;
if ($loaninfo) {
    $first_due_row = $this->db->select('repaydate')
        ->where('LID', $loaninfo->LID)
        ->where('PIN', $loaninfo->PIN)
        ->order_by('installment_number', 'ASC')
        ->limit(1)
        ->get('loan_contract_repayment_schedule')
        ->row();
}

$member_display = '';
if ($basicinfo) {
    $member_display = trim($basicinfo->member_id . ' : ' . $full_name);
} else if ($loaninfo) {
    $member_display = trim(
        (isset($loaninfo->firstname) ? $loaninfo->firstname : '') . ' ' .
        (isset($loaninfo->middlename) ? $loaninfo->middlename : '') . ' ' .
        (isset($loaninfo->lastname) ? $loaninfo->lastname : '')
    );
}

$default_debit = isset($default_debit_account) ? $default_debit_account : '';
$loan_credit = isset($loan_credit_account) ? $loan_credit_account : '';
$account_list = isset($account_list) ? $account_list : array();
$payment_methods = isset($payment_methods) ? $payment_methods : array();
$default_payment_method_id = isset($default_payment_method_id) ? $default_payment_method_id : '';

$due = isset($repayment_due) ? $repayment_due : null;
$suggested = $due && isset($due->suggested_amount) ? (float) $due->suggested_amount : (isset($loaninfo->installment_amount) ? (float) $loaninfo->installment_amount : 0);
?>

<style type="text/css">
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
.select2-container--default .select2-results__option[aria-disabled=true]{color:#222;cursor:default;}
.select2-container--default .select2-results__option .coa-bold{font-weight:bold;color:#111;}
.select2-container{width:100%!important;}
.loan-repay-page .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.loan-repay-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.loan-repay-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.loan-repay-page .select2-container--default.select2-container--focus .select2-selection--single,
.loan-repay-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}

.loan-repay-page { margin-top: 4px; }
.loan-repay-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.loan-repay-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.loan-repay-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.loan-repay-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.loan-repay-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.loan-repay-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.loan-repay-page .cbu-panel .panel-head i.panel-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.loan-repay-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.loan-repay-page .cbu-panel .panel-body { padding: 18px 20px; }
.loan-repay-page .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 18px;
}
@media (max-width: 767px) {
    .loan-repay-page .info-grid { grid-template-columns: 1fr; }
}
.loan-repay-page .info-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-repay-page .info-row:last-child { border-bottom: 0; }
.loan-repay-page .info-row .lbl { color: #999; font-weight: 500; }
.loan-repay-page .info-row .val {
    color: #2f4050;
    font-weight: 700;
    text-align: right;
    word-break: break-word;
}
.loan-repay-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    margin-bottom: 20px;
}
.loan-repay-page .cbu-member-card { text-align: center; }
.loan-repay-page .cbu-member-photo {
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
.loan-repay-page .cbu-member-photo img {
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    object-fit: cover;
    object-position: center top;
    border-radius: 50%;
    display: block;
}
.loan-repay-page .cbu-member-photo.avatar-fallback img {
    object-fit: contain;
    object-position: center center;
    background: #e8f8f5;
}
.loan-repay-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.loan-repay-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.loan-repay-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.loan-repay-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.loan-repay-page .cbu-member-details {
    text-align: left;
    margin: 0;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.loan-repay-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-repay-page .cbu-member-details li:last-child { border-bottom: 0; }
.loan-repay-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.loan-repay-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.loan-repay-page .balance-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 14px;
}
@media (max-width: 767px) {
    .loan-repay-page .balance-strip { grid-template-columns: 1fr; }
}
.loan-repay-page .balance-item {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 10px 12px;
    text-align: center;
}
.loan-repay-page .balance-item .lbl {
    display: block;
    color: #999;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 4px;
}
.loan-repay-page .balance-item .val {
    display: block;
    color: #2f4050;
    font-size: 14px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.loan-repay-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.loan-repay-page textarea.form-control { height: auto; min-height: 70px; }
.loan-repay-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.loan-repay-page .control-label,
.loan-repay-page label {
    color: #676a6c;
    font-weight: 600;
    margin-bottom: 6px;
}
.loan-repay-page .input-group-addon {
    background: #fafbfc;
    border-color: #e5e6e7;
    color: #1ab394;
    cursor: pointer;
}
.loan-repay-page .section-note {
    color: #888;
    font-size: 13px;
    margin: 0 0 12px;
}
.loan-repay-page .section-title {
    margin: 4px 0 10px;
    font-size: 14px;
    font-weight: 700;
    color: #2f4050;
}
.loan-repay-page .docs-table {
    border: 1px solid #e7eaec;
    border-radius: 8px;
    overflow: hidden;
}
.loan-repay-page .docs-table .table {
    margin: 0;
    background: #fff;
}
.loan-repay-page .docs-table .table > thead > tr > th {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
}
.loan-repay-page .docs-table .table > tbody > tr > td,
.loan-repay-page .docs-table .table > tfoot > tr > td,
.loan-repay-page .docs-table .table > tfoot > tr > th {
    vertical-align: middle;
    font-size: 13px;
}
.loan-repay-page .form-actions {
    margin-top: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.loan-repay-page .form-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 18px;
}
.loan-repay-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.loan-repay-page .btn-primary:hover,
.loan-repay-page .btn-primary:focus {
    background: #18a689;
    border-color: #18a689;
}
.loan-repay-page .empty-note {
    color: #999;
    font-size: 13px;
    padding: 12px 0;
}
.loan-repay-page .due-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}
.loan-repay-page #repaymentDuePanel .table > tbody > tr.warning > td {
    background-color: #fcf8e3;
}
</style>

<div class="col-lg-12 loan-repay-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="cbu-alert success displaymessage">' . $message . '</div>';
    } elseif ($this->session->flashdata('message') != '') {
        echo '<div class="cbu-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } elseif (isset($warning) && !empty($warning)) {
        echo '<div class="cbu-alert danger displaymessage">' . $warning . '</div>';
    } elseif ($this->session->flashdata('warning') != '') {
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
                        <i class="fa fa-file-text-o panel-icon"></i>
                        <h4><?php echo lang('loan_info'); ?></h4>
                    </div>
                    <a href="<?php echo site_url(current_lang() . '/loan/loan_repayment'); ?>" class="btn btn-white btn-xs">
                        <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                    </a>
                </div>
                <div class="panel-body">
                    <?php if ($loaninfo): ?>
                    <div class="info-grid">
                        <div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_LID'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->LID, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_product'); ?></span><span class="val"><?php echo $loan_product ? htmlspecialchars($loan_product->name, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_applicationdate'); ?></span><span class="val"><?php echo !empty($loaninfo->applicationdate) ? htmlspecialchars(format_date($loaninfo->applicationdate, FALSE), ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_disburse_date'); ?></span><span class="val"><?php echo ($disburse_row && !empty($disburse_row->disbursedate)) ? htmlspecialchars(format_date($disburse_row->disbursedate, FALSE), ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_startrepay_date'); ?></span><span class="val"><?php echo ($first_due_row && !empty($first_due_row->repaydate)) ? htmlspecialchars(format_date($first_due_row->repaydate, FALSE), ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></div>
                        </div>
                        <div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_applied_amount'); ?></span><span class="val"><?php echo number_format((float) $loaninfo->basic_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_installment_amount'); ?></span><span class="val"><?php echo number_format((float) $loaninfo->installment_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_total'); ?></span><span class="val"><?php echo number_format((float) $loaninfo->total_loan, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loanproduct_interest'); ?></span><span class="val"><?php echo (isset($loaninfo->rate) && $loaninfo->rate !== '' && $loaninfo->rate !== null) ? htmlspecialchars($loaninfo->rate, ENT_QUOTES, 'UTF-8') . '%' : '&mdash;'; ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_installment'); ?></span><span class="val"><?php echo (int) $loaninfo->number_istallment . ($interval_label !== '' ? ' ' . htmlspecialchars($interval_label, ENT_QUOTES, 'UTF-8') : ''); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loanproduct_penalt_percentage'); ?></span><span class="val"><?php echo ($loan_product && $loan_product->penalt_percentage !== '' && $loan_product->penalt_percentage !== null) ? htmlspecialchars($loan_product->penalt_percentage, ENT_QUOTES, 'UTF-8') . '%' : '&mdash;'; ?></span></div>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="empty-note"><?php echo lang('no_records_found'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo form_open(current_lang() . '/loan/loan_repayment_process', 'class="form-horizontal" id="loanRepaymentForm"'); ?>
    <input type="hidden" name="loanid" value="<?php echo htmlspecialchars(isset($loaninfo) ? $loaninfo->LID : ''); ?>"/>
    <input type="hidden" name="received_from" value="<?php echo htmlspecialchars($member_display); ?>"/>
    <input type="hidden" name="amount" id="amount_from_lines" value=""/>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-money panel-icon"></i>
                <h4><?php echo lang('loan_repayment'); ?> - <?php echo lang('loan_repay_btn'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo lang('cash_receipt_no'); ?> <span class="required">*</span></label>
                        <input type="text" name="receipt_no" value="<?php echo set_value('receipt_no', isset($next_receipt_no) ? $next_receipt_no : 'CR-00001'); ?>" class="form-control" required/>
                        <?php echo form_error('receipt_no'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo lang('loan_repay_date'); ?> <span class="required">*</span></label>
                        <div class="input-group date" id="datetimepicker">
                            <input type="text" name="repaydate" placeholder="<?php echo isset($hint_date) ? $hint_date : 'DD-MM-YYYY'; ?>"
                                value="<?php echo set_value('repaydate', date('d-m-Y')); ?>"
                                data-date-format="dd-mm-yyyy" class="form-control" required/>
                            <span class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </span>
                        </div>
                        <?php echo form_error('repaydate'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo lang('cash_receipt_payment_method'); ?> <span class="required">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value=""><?php echo lang('select_default_text'); ?></option>
                            <?php
                            $selected_pm = set_value('payment_method', $default_payment_method_id);
                            foreach ((array) $payment_methods as $pm_id => $pm_name) {
                                $sel = ((string)$selected_pm !== '' && (string)$selected_pm === (string)$pm_id) ? 'selected="selected"' : '';
                                $is_cheque = (strtolower(trim($pm_name)) === 'cheque') ? ' data-is-cheque="1"' : '';
                                echo '<option value="' . htmlspecialchars($pm_id) . '" ' . $sel . $is_cheque . '>' . htmlspecialchars($pm_name) . '</option>';
                            }
                            ?>
                        </select>
                        <?php echo form_error('payment_method'); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo lang('cash_receipt_received_from'); ?></label>
                        <p class="form-control-static" style="padding-top:7px;"><strong><?php echo htmlspecialchars($member_display); ?></strong></p>
                    </div>
                </div>
            </div>

            <div class="row" id="cheque_details" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo lang('cash_receipt_cheque_no'); ?></label>
                        <input type="text" name="cheque_no" value="<?php echo set_value('cheque_no'); ?>" class="form-control"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo lang('cash_receipt_bank_name'); ?></label>
                        <input type="text" name="bank_name" value="<?php echo set_value('bank_name'); ?>" class="form-control"/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><?php echo lang('cash_receipt_description'); ?></label>
                <textarea name="description" class="form-control" rows="3"><?php echo set_value('description', 'Loan Repayment - ' . (isset($loaninfo) ? $loaninfo->LID : '')); ?></textarea>
                <?php echo form_error('description'); ?>
            </div>
        </div>
    </div>

    <div class="cbu-panel" id="repaymentDuePanel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-calculator panel-icon"></i>
                <h4><?php echo lang('loan_repay_due_title'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <p class="section-note" id="repaymentDueExplanation">
                <?php
                echo sprintf(
                    lang('loan_repay_due_explanation'),
                    ($due && isset($due->grace_days)) ? (int) $due->grace_days : (defined('MAX_NUMBER_DAYS_OVERDUE_PENALT') ? (int) MAX_NUMBER_DAYS_OVERDUE_PENALT : 0),
                    ($due && isset($due->penalt_percentage)) ? rtrim(rtrim(number_format((float) $due->penalt_percentage, 2), '0'), '.') : '0'
                );
                ?>
            </p>
            <div class="docs-table table-responsive">
                <table class="table table-condensed" id="repaymentDueTable" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th><?php echo lang('loan_installment'); ?></th>
                            <th><?php echo lang('due_date'); ?></th>
                            <th><?php echo lang('index_status_th'); ?></th>
                            <th class="text-right"><?php echo lang('loan_installment_amount'); ?></th>
                            <th class="text-right"><?php echo lang('loan_ledger_penalty'); ?></th>
                            <th class="text-right"><?php echo lang('loan_repay_penalty_months'); ?></th>
                            <th class="text-right"><?php echo lang('total'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="repaymentDueBody">
                        <?php if ($due && !empty($due->items)): ?>
                            <?php foreach ($due->items as $item): ?>
                                <tr class="<?php echo $item->status === 'overdue' ? 'warning' : ''; ?>">
                                    <td><?php echo (int) $item->installment; ?></td>
                                    <td><?php echo htmlspecialchars(format_date($item->due_date, FALSE)); ?></td>
                                    <td><?php echo $item->status === 'overdue' ? lang('loan_repay_status_overdue') : lang('loan_repay_status_due'); ?></td>
                                    <td class="text-right"><?php echo number_format((float) $item->installment_amount, 2); ?></td>
                                    <td class="text-right"><?php echo number_format((float) $item->penalty, 2); ?></td>
                                    <td class="text-right"><?php echo (int) $item->penalty_months; ?></td>
                                    <td class="text-right"><strong><?php echo number_format((float) $item->total, 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="repaymentDueEmptyRow">
                                <td colspan="7" class="text-muted"><?php echo lang('loan_repay_nothing_due'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right"><?php echo lang('loan_repay_total_due'); ?></th>
                            <th class="text-right" id="dueTotalInstallments"><?php echo number_format($due ? (float) $due->total_installments : 0, 2); ?></th>
                            <th class="text-right" id="dueTotalPenalty"><?php echo number_format($due ? (float) $due->total_penalty : 0, 2); ?></th>
                            <th></th>
                            <th class="text-right" id="dueTotalDue"><?php echo number_format($due ? (float) $due->total_due : 0, 2); ?></th>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-right"><?php echo lang('loan_repay_carry_balance'); ?></td>
                            <td class="text-right" id="dueCarry"><?php echo number_format($due ? (float) $due->carry_balance : 0, 2); ?></td>
                        </tr>
                        <tr style="background:#e8f8f5;">
                            <th colspan="6" class="text-right"><?php echo lang('loan_repay_net_due'); ?> / <?php echo lang('loan_repay_suggested'); ?></th>
                            <th class="text-right" id="dueNetDue"><?php echo number_format($suggested, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="due-actions">
                <button type="button" class="btn btn-white btn-sm" id="btnUseSuggestedAmount">
                    <i class="fa fa-magic"></i> <?php echo lang('loan_repay_use_suggested'); ?>
                </button>
                <a href="#" class="btn btn-primary btn-sm" id="btnPrintCollectionNotice" target="_blank" rel="noopener">
                    <i class="fa fa-print"></i> <?php echo lang('loan_collection_notice_print'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-list panel-icon"></i>
                <h4><?php echo lang('cash_receipt_line_items'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="docs-table table-responsive">
                <table id="lineItemsTable" class="table table-bordered" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th style="width: 30%;"><?php echo lang('cash_receipt_account'); ?> <span class="required">*</span></th>
                            <th style="width: 30%;"><?php echo lang('cash_receipt_line_description'); ?></th>
                            <th style="width: 15%;"><?php echo lang('journalentry_debit'); ?></th>
                            <th style="width: 15%;"><?php echo lang('journalentry_credit'); ?></th>
                            <th style="width: 10%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="line-item loan-repay-debit-row" data-row-type="debit">
                            <td>
                                <select class="form-control account-select" name="account[]">
                                    <option value=""><?php echo lang('select_default_text'); ?></option>
                                    <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => $default_debit)); ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="line_description[]" class="form-control" placeholder="<?php echo htmlspecialchars(lang('cash_receipt_line_description')); ?>" value=""/>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input loan-repay-amount-debit" placeholder="0.00" title="<?php echo htmlspecialchars(lang('loan_repay_amount')); ?>"/>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input" placeholder="0.00" value="0" readonly tabindex="-1"/>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo lang('delete'); ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="line-item loan-repay-credit-row" data-row-type="credit">
                            <td>
                                <select class="form-control account-select" name="account[]">
                                    <option value=""><?php echo lang('select_default_text'); ?></option>
                                    <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => $loan_credit)); ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="line_description[]" class="form-control" value="<?php echo htmlspecialchars(lang('loan_repayment')); ?>"/>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input" placeholder="0.00" value="0" readonly tabindex="-1"/>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input loan-repay-amount-credit" placeholder="0.00" title="<?php echo htmlspecialchars(lang('loan_repay_amount')); ?>"/>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo lang('delete'); ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                            <td>
                                <input type="text" id="total_debit" class="form-control" readonly value="0.00"/>
                            </td>
                            <td>
                                <input type="text" id="total_credit" class="form-control" readonly value="0.00"/>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2" id="balance_diff" class="text-right" style="color: red; font-weight: bold;"></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" class="btn btn-primary" id="addLineItem" style="margin-top:12px;">
                <i class="fa fa-plus"></i> <?php echo lang('add_row'); ?>
            </button>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> <?php echo lang('save'); ?>
                </button>
                <a href="<?php echo site_url(current_lang() . '/loan/loan_repayment'); ?>" class="btn btn-default">
                    <?php echo lang('cancel'); ?>
                </a>
            </div>
        </div>
    </div>

    <?php echo form_close(); ?>
</div>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
var loanRepayPaymentMethodAccounts = <?php echo json_encode(isset($payment_method_gl_accounts) ? $payment_method_gl_accounts : array()); ?>;
var loanRepayDueUrl = <?php echo json_encode(isset($repayment_due_url) ? $repayment_due_url : ''); ?>;
var loanCollectionNoticeUrl = <?php echo json_encode(isset($collection_notice_url) ? $collection_notice_url : ''); ?>;
var loanRepaySuggestedAmount = <?php echo json_encode(isset($suggested) ? round((float) $suggested, 2) : 0); ?>;
var loanRepayDueLabels = {
    installment: <?php echo json_encode(lang('loan_installment')); ?>,
    due_date: <?php echo json_encode(lang('due_date')); ?>,
    status_due: <?php echo json_encode(lang('loan_repay_status_due')); ?>,
    status_overdue: <?php echo json_encode(lang('loan_repay_status_overdue')); ?>,
    nothing_due: <?php echo json_encode(lang('loan_repay_nothing_due')); ?>,
    explanation: <?php echo json_encode(lang('loan_repay_due_explanation')); ?>
};
</script>
<script>
(function(){
    function loadScript(src, cb, fallback){
        var s=document.createElement('script');
        s.src=src; s.onload=cb;
        if(fallback){ s.onerror=function(){ loadScript(fallback, cb); }; }
        document.head.appendChild(s);
    }
    function initOnceReady(){
        if(!window.jQuery){ setTimeout(initOnceReady, 50); return; }
        var $ = window.jQuery;
        function boot(){
            function formatCoaOption(data) {
                if (!data.element) {
                    return data.text;
                }
                var $opt = $(data.element);
                var isParent = $opt.data('is-parent') == 1 || $opt.hasClass('coa-parent-account');
                var isHeader = $opt.data('coa-header') == 1;
                if (isParent || isHeader) {
                    return $('<span class="coa-bold"></span>').text(data.text);
                }
                return data.text;
            }

            function initAccountSelect($el) {
                if (!$el || !$el.length || !$.fn.select2) {
                    return;
                }
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({
                    width: '100%',
                    placeholder: <?php echo json_encode(lang('select_default_text')); ?>,
                    allowClear: true,
                    templateResult: formatCoaOption,
                    templateSelection: function(data) { return data.text; }
                });
            }

            function destroyAccountSelect($el) {
                if ($el && $el.length && $el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
            }

            function setAccountValue($select, account) {
                if (!$select || !$select.length) {
                    return;
                }
                $select.val(account || '');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('change');
                }
            }

            function cloneLineItem() {
                var $first = $('#lineItemsTable tbody .line-item:first');
                destroyAccountSelect($first.find('.account-select'));
                var newRow = $first.clone();
                newRow.find('.select2-container').remove();
                newRow.removeClass('loan-repay-debit-row loan-repay-credit-row');
                newRow.removeAttr('data-row-type');
                newRow.find('input').val('').prop('readonly', false).removeAttr('tabindex');
                newRow.find('select').val('');
                newRow.find('.debit-input, .credit-input')
                    .removeClass('loan-repay-amount-debit loan-repay-amount-credit')
                    .attr('placeholder', '0.00');
                $('#lineItemsTable tbody').append(newRow);
                initAccountSelect($first.find('.account-select'));
                initAccountSelect(newRow.find('.account-select'));
                return newRow;
            }

            function ensureBootstrapDP(cb){
                function wrapBootstrapDP(){
                    if ($.fn.datepicker && $.fn.datepicker.DPGlobal){
                        var bootstrapDP = $.fn.datepicker;
                        if ($.fn.datepicker.noConflict){
                            $.fn.datepicker.noConflict();
                        }
                        $.fn.bootstrapDP = bootstrapDP;
                        cb();
                    } else {
                        cb();
                    }
                }
                if (!($.fn.datepicker && $.fn.datepicker.DPGlobal)){
                    loadScript(
                        '<?php echo base_url(); ?>assets/js/plugins/datapicker/bootstrap-datepicker.js',
                        wrapBootstrapDP,
                        'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js'
                    );
                } else {
                    wrapBootstrapDP();
                }
            }

            function initPicker(){
                var picker = $.fn.bootstrapDP || $.fn.datepicker;
                if (!picker){ return; }
                picker.call($('#datetimepicker'), {
                    todayBtn: 'linked', keyboardNavigation: false, forceParse: false,
                    calendarWeeks: true, autoclose: true, format: 'dd-mm-yyyy',
                    orientation: 'bottom auto', todayHighlight: true, container: 'body'
                }).on('changeDate change', function(){
                    refreshRepaymentDue(true);
                    updateCollectionNoticeLink();
                });
            }

            ensureBootstrapDP(initPicker);

            updateRemoveButtons();
            $('.account-select').each(function(){ initAccountSelect($(this)); });

            function formatMoney(n){
                var x = parseFloat(n);
                if (isNaN(x)) x = 0;
                return x.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            function formatDateDisplay(ymd){
                if (!ymd || ymd.indexOf('-') < 0) return ymd || '';
                var p = ymd.split('-');
                if (p.length !== 3) return ymd;
                return p[2] + '-' + p[1] + '-' + p[0];
            }
            function setLineAmounts(amount){
                var v = parseFloat(amount);
                if (isNaN(v) || v < 0) v = 0;
                var s = v.toFixed(2);
                $('#lineItemsTable tbody .loan-repay-debit-row').first().find('.loan-repay-amount-debit').val(s);
                $('#lineItemsTable tbody .loan-repay-credit-row').first().find('.loan-repay-amount-credit').val(s);
                calculateTotals();
            }
            function renderDue(due){
                if (!due) return;
                loanRepaySuggestedAmount = parseFloat(due.suggested_amount) || 0;
                var grace = due.grace_days || 0;
                var pct = due.penalt_percentage || 0;
                var expl = loanRepayDueLabels.explanation || '';
                $('#repaymentDueExplanation').text(expl.replace('%s', grace).replace('%s', pct));
                var $body = $('#repaymentDueBody').empty();
                if (!due.items || !due.items.length) {
                    $body.append('<tr><td colspan="7" class="text-muted">' + loanRepayDueLabels.nothing_due + '</td></tr>');
                } else {
                    $.each(due.items, function(_, item){
                        var status = item.status === 'overdue' ? loanRepayDueLabels.status_overdue : loanRepayDueLabels.status_due;
                        var trClass = item.status === 'overdue' ? ' class="warning"' : '';
                        $body.append(
                            '<tr' + trClass + '>' +
                            '<td>' + item.installment + '</td>' +
                            '<td>' + formatDateDisplay(item.due_date) + '</td>' +
                            '<td>' + status + '</td>' +
                            '<td class="text-right">' + formatMoney(item.installment_amount) + '</td>' +
                            '<td class="text-right">' + formatMoney(item.penalty) + '</td>' +
                            '<td class="text-right">' + (item.penalty_months || 0) + '</td>' +
                            '<td class="text-right"><strong>' + formatMoney(item.total) + '</strong></td>' +
                            '</tr>'
                        );
                    });
                }
                $('#dueTotalInstallments').text(formatMoney(due.total_installments));
                $('#dueTotalPenalty').text(formatMoney(due.total_penalty));
                $('#dueTotalDue').text(formatMoney(due.total_due));
                $('#dueCarry').text(formatMoney(due.carry_balance));
                $('#dueNetDue').text(formatMoney(due.suggested_amount));
            }
            var dueRequest = null;
            function refreshRepaymentDue(autofill){
                if (!loanRepayDueUrl) return;
                var repaydate = $('input[name="repaydate"]').val();
                if (dueRequest && dueRequest.abort) dueRequest.abort();
                dueRequest = $.getJSON(loanRepayDueUrl, { repaydate: repaydate })
                    .done(function(res){
                        if (res && res.success && res.due) {
                            renderDue(res.due);
                            if (autofill) {
                                setLineAmounts(res.due.suggested_amount);
                            }
                        }
                    });
            }

            $('#btnUseSuggestedAmount').on('click', function(){
                setLineAmounts(loanRepaySuggestedAmount);
            });

            function updateCollectionNoticeLink(){
                if (!loanCollectionNoticeUrl) return;
                var repaydate = $('input[name="repaydate"]').val() || '';
                var url = loanCollectionNoticeUrl
                    + (loanCollectionNoticeUrl.indexOf('?') >= 0 ? '&' : '?')
                    + 'repaydate=' + encodeURIComponent(repaydate)
                    + '&autoprint=1';
                $('#btnPrintCollectionNotice').attr('href', url);
            }
            updateCollectionNoticeLink();
            $('#btnPrintCollectionNotice').on('click', function(e){
                updateCollectionNoticeLink();
                var href = $(this).attr('href');
                if (!href || href === '#') {
                    e.preventDefault();
                    return false;
                }
                window.open(href, '_blank');
                e.preventDefault();
                return false;
            });

            $('input[name="repaydate"]').on('change blur', function(){
                refreshRepaymentDue(true);
                updateCollectionNoticeLink();
            });

            // Prefill line amounts with suggested due on first load
            if (loanRepaySuggestedAmount > 0) {
                setLineAmounts(loanRepaySuggestedAmount);
            }

            // Show/hide cheque details based on payment method (by option data or text)
            $('#payment_method').on('change', function(){
                var opt = $(this).find('option:selected');
                if(opt.data('is-cheque') === 1 || opt.data('is-cheque') === '1' || (opt.text() && opt.text().toLowerCase().indexOf('cheque') >= 0)){
                    $('#cheque_details').show();
                } else {
                    $('#cheque_details').hide();
                }
            });
            // Trigger once on load
            $('#payment_method').trigger('change');

            // Auto-fill: mirror amount between first debit row and first credit row only
            $(document).on('keyup change', '.loan-repay-amount-debit', function(){
                var $firstDebitRow = $('#lineItemsTable tbody .loan-repay-debit-row').first();
                if ($(this).closest('tr').get(0) !== $firstDebitRow.get(0)) return;
                var v = $(this).val();
                $('#lineItemsTable tbody .loan-repay-credit-row').first().find('.loan-repay-amount-credit').val(v);
                calculateTotals();
            });
            $(document).on('keyup change', '.loan-repay-amount-credit', function(){
                var $firstCreditRow = $('#lineItemsTable tbody .loan-repay-credit-row').first();
                if ($(this).closest('tr').get(0) !== $firstCreditRow.get(0)) return;
                var v = $(this).val();
                $('#lineItemsTable tbody .loan-repay-debit-row').first().find('.loan-repay-amount-debit').val(v);
                calculateTotals();
            });

            // When payment method changes, update the first line's account to that method's GL account
            $('#payment_method').on('change', function(){
                var pmId = $(this).val();
                if (typeof loanRepayPaymentMethodAccounts !== 'undefined' && loanRepayPaymentMethodAccounts[pmId]) {
                    var account = loanRepayPaymentMethodAccounts[pmId];
                    if (account) {
                        setAccountValue(
                            $('#lineItemsTable tbody .loan-repay-debit-row').first().find('.account-select'),
                            String(account)
                        );
                    }
                }
            });

            // Add line item
            $('#addLineItem').on('click', function(){
                cloneLineItem();
                updateRemoveButtons();
                calculateTotals();
            });

            $(document).on('click', '.remove-line', function(){
                if ($('.line-item').length > 1) {
                    var $row = $(this).closest('tr');
                    destroyAccountSelect($row.find('.account-select'));
                    $row.remove();
                    updateRemoveButtons();
                    calculateTotals();
                }
            });

            function updateRemoveButtons(){
                var count = $('.line-item').length;
                $('.remove-line').prop('disabled', count <= 1);
            }

            $(document).on('keyup change', '.debit-input, .credit-input', function(){
                calculateTotals();
            });

            function calculateTotals(){
                var totalDebit = 0, totalCredit = 0;
                $('.debit-input').each(function(){
                    totalDebit += parseFloat($(this).val()) || 0;
                });
                $('.credit-input').each(function(){
                    totalCredit += parseFloat($(this).val()) || 0;
                });
                $('#total_debit').val(totalDebit.toFixed(2));
                $('#total_credit').val(totalCredit.toFixed(2));
                var diff = totalDebit - totalCredit;
                if (Math.abs(diff) < 0.01) {
                    $('#balance_diff').text('').css('color', 'green');
                } else {
                    $('#balance_diff').text('Diff: ' + diff.toFixed(2)).css('color', 'red');
                }
            }

            // Form validation: debits must equal credits; set hidden amount from total
            $('#loanRepaymentForm').on('submit', function(e){
                var totalDebit = 0, totalCredit = 0, hasItems = false;
                $('.debit-input').each(function(){
                    totalDebit += parseFloat($(this).val()) || 0;
                });
                $('.credit-input').each(function(){
                    var v = parseFloat($(this).val()) || 0;
                    totalCredit += v;
                    if (v > 0) hasItems = true;
                });
                if (!hasItems) {
                    alert('<?php echo addslashes(lang('cash_receipt_no_items')); ?>');
                    e.preventDefault();
                    return false;
                }
                if (Math.abs(totalDebit - totalCredit) > 0.01) {
                    alert('<?php echo addslashes(lang('debits_credits_not_balanced')); ?>');
                    e.preventDefault();
                    return false;
                }
                $('#amount_from_lines').val(totalDebit.toFixed(2));
                return true;
            });

            calculateTotals();
        }
        $(boot);
    }
    initOnceReady();
})();
</script>
