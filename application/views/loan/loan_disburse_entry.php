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

$product = $loaninfo ? $this->setting_model->loanproduct($loaninfo->product_type)->row() : null;
$interval = ($loaninfo && $product) ? $this->setting_model->intervalinfo($loaninfo->interval)->row() : null;
$contribution = $loaninfo ? $this->contribution_model->contribution_balance($loaninfo->PID, $loaninfo->member_id) : null;
$share_data = $loaninfo ? $this->share_model->share_member_info($loaninfo->PID, $loaninfo->member_id) : null;
$saving = $loaninfo ? $this->finance_model->saving_account_balance_PID($loaninfo->PID, $loaninfo->member_id) : null;

$basic_amount = isset($loaninfo->basic_amount) ? $loaninfo->basic_amount : 0;
$loan_principle_account = isset($loan_principle_account) ? $loan_principle_account : '';
$default_credit_account = isset($default_credit_account) ? $default_credit_account : '';
$default_payment_method_id = isset($default_payment_method_id) ? $default_payment_method_id : '';
$payment_method_credit_accounts = isset($payment_method_credit_accounts) ? $payment_method_credit_accounts : array();
$payment_methods = isset($payment_methods) ? $payment_methods : array();
$account_list = isset($account_list) ? $account_list : array();
$offsetable_loans = isset($offsetable_loans) ? $offsetable_loans : array();
$selected_offset_loans = isset($selected_offset_loans) ? $selected_offset_loans : array();
$existing_release = isset($existing_release) ? $existing_release : null;
$existing_gl_items = isset($existing_gl_items) ? $existing_gl_items : array();
$disburse_deductions = isset($disburse_deductions) ? $disburse_deductions : array();
?>

<style type="text/css">
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
.select2-container--default .select2-results__option[aria-disabled=true]{color:#222;cursor:default;}
.select2-container--default .select2-results__option .coa-bold{font-weight:bold;color:#111;}
.select2-container{width:100%!important;}
.loan-disburse-page .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.loan-disburse-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.loan-disburse-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.loan-disburse-page .select2-container--default.select2-container--focus .select2-selection--single,
.loan-disburse-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}

.loan-disburse-page { margin-top: 4px; }
.loan-disburse-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.loan-disburse-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.loan-disburse-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.loan-disburse-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.loan-disburse-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.loan-disburse-page .cbu-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.loan-disburse-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.loan-disburse-page .cbu-panel .panel-body { padding: 18px 20px; }
.loan-disburse-page .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 18px;
}
@media (max-width: 767px) {
    .loan-disburse-page .info-grid { grid-template-columns: 1fr; }
}
.loan-disburse-page .info-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-disburse-page .info-row:last-child { border-bottom: 0; }
.loan-disburse-page .info-row .lbl { color: #999; font-weight: 500; }
.loan-disburse-page .info-row .val {
    color: #2f4050;
    font-weight: 700;
    text-align: right;
    word-break: break-word;
}
.loan-disburse-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    margin-bottom: 20px;
}
.loan-disburse-page .cbu-member-card { text-align: center; }
.loan-disburse-page .cbu-member-photo {
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
.loan-disburse-page .cbu-member-photo img {
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    object-fit: cover;
    object-position: center top;
    border-radius: 50%;
    display: block;
}
.loan-disburse-page .cbu-member-photo.avatar-fallback img {
    object-fit: contain;
    object-position: center center;
    background: #e8f8f5;
}
.loan-disburse-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.loan-disburse-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.loan-disburse-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.loan-disburse-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.loan-disburse-page .cbu-member-details {
    text-align: left;
    margin: 0;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.loan-disburse-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-disburse-page .cbu-member-details li:last-child { border-bottom: 0; }
.loan-disburse-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.loan-disburse-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.loan-disburse-page .balance-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 14px;
}
@media (max-width: 767px) {
    .loan-disburse-page .balance-strip { grid-template-columns: 1fr; }
}
.loan-disburse-page .balance-item {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 10px 12px;
    text-align: center;
}
.loan-disburse-page .balance-item .lbl {
    display: block;
    color: #999;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 4px;
}
.loan-disburse-page .balance-item .val {
    display: block;
    color: #2f4050;
    font-size: 14px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.loan-disburse-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.loan-disburse-page textarea.form-control { height: auto; min-height: 70px; }
.loan-disburse-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.loan-disburse-page .control-label,
.loan-disburse-page label {
    color: #676a6c;
    font-weight: 600;
    margin-bottom: 6px;
}
.loan-disburse-page .input-group-addon {
    background: #fafbfc;
    border-color: #e5e6e7;
    color: #1ab394;
    cursor: pointer;
}
.loan-disburse-page .section-note {
    color: #888;
    font-size: 13px;
    margin: 0 0 12px;
}
.loan-disburse-page .section-title {
    margin: 4px 0 10px;
    font-size: 14px;
    font-weight: 700;
    color: #2f4050;
}
.loan-disburse-page .offset-panel {
    border: 1px solid #c9ebe3;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 16px;
}
.loan-disburse-page .offset-panel .offset-head {
    padding: 12px 14px;
    background: #e8f8f5;
    border-bottom: 1px solid #c9ebe3;
    font-weight: 700;
    color: #0e7c69;
}
.loan-disburse-page .offset-panel .offset-body { padding: 14px; }
.loan-disburse-page .offset-summary {
    display: block;
    padding: 10px 0 0;
    border-top: 1px solid #e5e9ed;
    margin-top: 10px;
}
.loan-disburse-page .offset-waive-row > td { padding-top: 0 !important; }
.loan-disburse-page .offset-waive {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 4px 0 8px;
    border-bottom: 1px dashed #e5e9ed;
}
.loan-disburse-page .offset-waive-label { font-weight: 600; color: #6c757d; }
.loan-disburse-page .offset-waive-field { font-weight: normal; margin: 0; }
.loan-disburse-page .offset-waive-field input,
.loan-disburse-page .offset-waive-field select { display: inline-block; width: 110px; }
.loan-disburse-page .offset-waive-note input { width: 220px; }
.loan-disburse-page .text-warning { color: #f0ad4e; }
    margin: 12px 0 0;
    padding: 10px 12px;
    border-radius: 6px;
    background: #f3fbf9;
    border: 1px solid #c9ebe3;
    font-size: 13px;
    color: #2f4050;
}
.loan-disburse-page .docs-table {
    border: 1px solid #e7eaec;
    border-radius: 8px;
    overflow: hidden;
}
.loan-disburse-page .docs-table .table {
    margin: 0;
    background: #fff;
}
.loan-disburse-page .docs-table .table > thead > tr > th {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
}
.loan-disburse-page .docs-table .table > tbody > tr > td,
.loan-disburse-page .docs-table .table > tfoot > tr > td {
    vertical-align: middle;
    font-size: 13px;
}
.loan-disburse-page .form-actions {
    margin-top: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.loan-disburse-page .form-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 18px;
}
.loan-disburse-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.loan-disburse-page .empty-note {
    color: #999;
    font-size: 13px;
    padding: 12px 0;
}
</style>

<div class="col-lg-12 loan-disburse-page">
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
                    <i class="fa fa-file-text-o"></i>
                    <h4><?php echo lang('loan_info'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="info-grid">
                        <div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_LID'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->LID, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_product'); ?></span><span class="val"><?php echo $product ? htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loanproduct_interest'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->rate, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_installment'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->number_istallment . ($interval ? ' ' . $interval->name : ''), ENT_QUOTES, 'UTF-8'); ?></span></div>
                        </div>
                        <div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_applicationdate'); ?></span><span class="val"><?php echo htmlspecialchars(format_date($loaninfo->applicationdate, FALSE), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_applied_amount'); ?></span><span class="val"><?php echo number_format($basic_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_installment_amount'); ?></span><span class="val"><?php echo number_format($loaninfo->installment_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_total_interest'); ?></span><span class="val"><?php echo number_format($loaninfo->total_interest_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_total'); ?></span><span class="val"><?php echo number_format($loaninfo->total_loan, 2); ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <i class="fa fa-money"></i>
            <h4><?php echo lang('loan_disburse_info'); ?></h4>
        </div>
        <div class="panel-body">
            <?php echo form_open(current_lang() . "/loan/loan_disburse_entry/" . $loanid, array('id' => 'loanDisburseEntryForm')); ?>
            <?php if (!empty($existing_release)): ?>
            <div class="alert alert-info" style="margin-bottom:14px;">
                <?php echo lang('loan_release_editing_pending'); ?>
            </div>
            <?php endif; ?>
            <div class="row">
                <?php if (!empty($show_disburse_no) && isset($next_disburse_no)): ?>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo lang('loan_disburse_no'); ?> <span class="required">*</span></label>
                        <input type="text" name="disburse_no" value="<?php echo set_value('disburse_no', $next_disburse_no); ?>" class="form-control" required/>
                        <?php echo form_error('disburse_no'); ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo lang('loan_disburse_date'); ?> <span class="required">*</span></label>
                        <div class="input-group date" id="disburseDatePicker">
                            <?php
                            $default_disburse_date = date('d-m-Y');
                            if (!empty($existing_release) && !empty($existing_release->disbursedate)) {
                                $default_disburse_date = date('d-m-Y', strtotime($existing_release->disbursedate));
                            }
                            ?>
                            <input type="text" name="disbursedate" value="<?php echo set_value('disbursedate', $default_disburse_date); ?>" data-date-format="dd-mm-yyyy" class="form-control" placeholder="dd-mm-yyyy" required/>
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        </div>
                        <?php echo form_error('disbursedate'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo lang('loan_disburse_payment_method'); ?> <span class="required">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value=""><?php echo lang('select_default_text'); ?></option>
                            <?php $selected_payment_method = set_value('payment_method', (string) $default_payment_method_id); ?>
                            <?php foreach ((array) $payment_methods as $id => $name): ?>
                                <option value="<?php echo $id; ?>" <?php echo ($selected_payment_method !== '' && (string) $id === (string) $selected_payment_method) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php echo form_error('payment_method'); ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label><?php echo lang('loan_comment'); ?> <span class="required">*</span></label>
                <textarea name="comment" class="form-control" rows="2" required><?php echo set_value('comment', (!empty($existing_release) && !empty($existing_release->comment)) ? $existing_release->comment : ''); ?></textarea>
                <?php echo form_error('comment'); ?>
            </div>

            <?php if (!empty($offsetable_loans)) { ?>
            <div class="offset-panel">
                <div class="offset-head"><?php echo lang('loan_offset_section'); ?></div>
                <div class="offset-body">
                    <p class="section-note"><?php echo lang('loan_offset_help'); ?></p>
                    <?php if (!empty($pending_waivers)) { ?>
                    <div class="alert alert-warning" style="margin-bottom:12px;">
                        <i class="fa fa-clock-o"></i>
                        <?php echo sprintf(lang('loan_waiver_release_pending'), (int) $pending_waivers); ?>
                        <a href="<?php echo site_url(current_lang() . '/loan/loan_waiver_list'); ?>" target="_blank"><?php echo lang('loan_waiver_list_title'); ?></a>
                    </div>
                    <?php } ?>
                    <div class="docs-table table-responsive">
                        <table class="table table-striped" id="offsetLoansTable">
                            <thead>
                                <tr>
                                    <th style="width:34px;"></th>
                                    <th><?php echo lang('loan_LID'); ?></th>
                                    <th><?php echo lang('loan_product'); ?></th>
                                    <th class="text-right" style="width:110px;"><?php echo lang('loan_offset_principal'); ?></th>
                                    <th class="text-right" style="width:110px;"><?php echo lang('loan_offset_interest'); ?></th>
                                    <th class="text-right" style="width:110px;"><?php echo lang('loan_offset_penalty'); ?></th>
                                    <th class="text-right" style="width:100px;"><?php echo lang('loan_offset_other'); ?></th>
                                    <th class="text-right" style="width:110px;"><?php echo lang('loan_offset_total'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($offsetable_loans as $ol) {
                                    $checked = in_array($ol->LID, $selected_offset_loans, true);
                                    $saved_row = isset($offset_breakdown[(string) $ol->LID]) ? $offset_breakdown[(string) $ol->LID] : array();
                                    $val_principal = array_key_exists('principal', $saved_row) ? $saved_row['principal'] : $ol->principal_outstanding;
                                    $val_interest = array_key_exists('interest', $saved_row) ? $saved_row['interest'] : $ol->interest_outstanding;
                                    $val_penalty = array_key_exists('penalty', $saved_row) ? $saved_row['penalty'] : $ol->penalty_outstanding;
                                    $val_other = array_key_exists('other', $saved_row) ? $saved_row['other'] : 0;
                                    $val_wpen = array_key_exists('penalty_waived', $saved_row) ? $saved_row['penalty_waived'] : 0;
                                    $val_wint = array_key_exists('interest_waived', $saved_row) ? $saved_row['interest_waived'] : 0;
                                    $val_reason = isset($saved_row['reason_code']) ? $saved_row['reason_code'] : '';
                                    $val_note = isset($saved_row['reason_note']) ? $saved_row['reason_note'] : '';
                                ?>
                                <tr class="offset-loan-row" data-lid="<?php echo htmlspecialchars($ol->LID); ?>">
                                    <td class="text-center">
                                        <input type="checkbox" class="offset-loan-cb" name="offset_loans[]" value="<?php echo htmlspecialchars($ol->LID); ?>"
                                               data-principal="<?php echo htmlspecialchars($ol->principal_outstanding); ?>"
                                               data-interest="<?php echo htmlspecialchars($ol->interest_outstanding); ?>"
                                               data-penalty="<?php echo htmlspecialchars($ol->penalty_outstanding); ?>"
                                               data-assessed="<?php echo htmlspecialchars($ol->assessed_outstanding); ?>"
                                               data-total="<?php echo htmlspecialchars($ol->total_outstanding); ?>"
                                               data-principle-account="<?php echo htmlspecialchars($ol->principle_account); ?>"
                                               data-interest-account="<?php echo htmlspecialchars($ol->interest_account); ?>"
                                               data-penalty-account="<?php echo htmlspecialchars($ol->penalty_account); ?>"
                                               data-penalty-waived-account="<?php echo htmlspecialchars($ol->penalty_waived_account); ?>"
                                               data-interest-waived-account="<?php echo htmlspecialchars($ol->interest_waived_account); ?>"
                                               data-penalty-days="<?php echo (int) $ol->penalty_days; ?>"
                                               <?php echo $checked ? 'checked="checked"' : ''; ?> />
                                    </td>
                                    <td><?php echo htmlspecialchars($ol->LID); ?></td>
                                    <td><?php echo htmlspecialchars($ol->product_name); ?></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control input-sm text-right offset-amount"
                                               name="offset_principal[<?php echo htmlspecialchars($ol->LID); ?>]"
                                               value="<?php echo number_format((float) $val_principal, 2, '.', ''); ?>"/></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control input-sm text-right offset-amount"
                                               name="offset_interest[<?php echo htmlspecialchars($ol->LID); ?>]"
                                               value="<?php echo number_format((float) $val_interest, 2, '.', ''); ?>"/></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="form-control input-sm text-right offset-amount"
                                               name="offset_penalty[<?php echo htmlspecialchars($ol->LID); ?>]"
                                               value="<?php echo number_format((float) $val_penalty, 2, '.', ''); ?>"/>
                                        <?php if ($ol->penalty_days > 0) { ?>
                                        <small class="text-muted"><?php echo sprintf(lang('loan_offset_penalty_days'), (int) $ol->penalty_days); ?></small>
                                        <?php } ?>
                                    </td>
                                    <td><input type="number" step="0.01" min="0" class="form-control input-sm text-right offset-amount"
                                               name="offset_other[<?php echo htmlspecialchars($ol->LID); ?>]"
                                               value="<?php echo number_format((float) $val_other, 2, '.', ''); ?>"/></td>
                                    <td class="text-right"><strong class="offset-row-total">0.00</strong></td>
                                </tr>
                                <tr class="offset-waive-row">
                                    <td></td>
                                    <td colspan="7">
                                        <div class="offset-waive">
                                            <span class="offset-waive-label"><?php echo lang('loan_waive_label'); ?>:</span>
                                            <label class="offset-waive-field">
                                                <?php echo lang('loan_waive_penalty'); ?>
                                                <input type="number" step="0.01" min="0" class="form-control input-sm offset-waive-input"
                                                       name="offset_penalty_waived[<?php echo htmlspecialchars($ol->LID); ?>]"
                                                       value="<?php echo number_format((float) $val_wpen, 2, '.', ''); ?>"/>
                                            </label>
                                            <label class="offset-waive-field">
                                                <?php echo lang('loan_waive_interest'); ?>
                                                <input type="number" step="0.01" min="0" class="form-control input-sm offset-waive-input"
                                                       name="offset_interest_waived[<?php echo htmlspecialchars($ol->LID); ?>]"
                                                       value="<?php echo number_format((float) $val_wint, 2, '.', ''); ?>"/>
                                            </label>
                                            <label class="offset-waive-field">
                                                <?php echo lang('loan_waiver_reason'); ?>
                                                <select class="form-control input-sm offset-waive-reason"
                                                        name="offset_reason[<?php echo htmlspecialchars($ol->LID); ?>]">
                                                    <option value=""><?php echo lang('select_default_text'); ?></option>
                                                    <?php foreach ((array) $waiver_reason_codes as $code => $reason_label) { ?>
                                                    <option value="<?php echo htmlspecialchars($code); ?>" <?php echo ($code === $val_reason) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($reason_label); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </label>
                                            <label class="offset-waive-field offset-waive-note">
                                                <?php echo lang('loan_waiver_note'); ?>
                                                <input type="text" maxlength="255" class="form-control input-sm"
                                                       name="offset_reason_note[<?php echo htmlspecialchars($ol->LID); ?>]"
                                                       value="<?php echo htmlspecialchars($val_note, ENT_QUOTES, 'UTF-8'); ?>"/>
                                            </label>
                                            <button type="button" class="btn btn-default btn-xs offset-reset"
                                                    title="<?php echo lang('loan_offset_reset_row'); ?>">
                                                <i class="fa fa-undo"></i> <?php echo lang('loan_offset_reset_row'); ?>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="offset-summary" id="offsetSummary">
                        <strong><?php echo lang('loan_applied_amount'); ?>:</strong> <span id="offsetNewAmount"><?php echo number_format($basic_amount, 2); ?></span>
                        &nbsp;|&nbsp;
                        <strong><?php echo lang('loan_offset_total'); ?>:</strong> <span id="offsetTotalAmt">0.00</span>
                        &nbsp;|&nbsp;
                        <strong><?php echo lang('loan_offset_waived_total'); ?>:</strong> <span id="offsetWaivedAmt">0.00</span>
                        &nbsp;|&nbsp;
                        <strong id="offsetNetLabel"><?php echo lang('loan_offset_net_proceeds'); ?>:</strong> <span id="offsetNetProceeds"><?php echo number_format($basic_amount, 2); ?></span>
                        <div id="offsetWarning" class="text-danger" style="display:none; margin-top:6px;"></div>
                        <div id="offsetTopupNote" class="text-warning" style="display:none; margin-top:6px;"></div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <div class="section-title"><?php echo lang('loan_disburse_line_items'); ?></div>
            <p class="section-note"><?php echo lang('loan_disburse_line_help'); ?></p>
            <select id="coaOptionsSource" style="display:none;">
                <option value=""><?php echo lang('select_default_text'); ?></option>
                <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => '')); ?>
            </select>
            <div class="table-responsive">
                <table id="lineItemsTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width:30%;"><?php echo lang('account_code'); ?> <span class="required">*</span></th>
                            <th style="width:30%;"><?php echo lang('journalentry_account_description'); ?></th>
                            <th style="width:15%;"><?php echo lang('journalentry_debit'); ?></th>
                            <th style="width:15%;"><?php echo lang('journalentry_credit'); ?></th>
                            <th style="width:10%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $default_lines = array();
                        if (!empty($existing_gl_items) && is_array($existing_gl_items)) {
                            foreach ($existing_gl_items as $gi) {
                                $default_lines[] = array(
                                    'account' => isset($gi['account']) ? $gi['account'] : '',
                                    'debit' => isset($gi['debit']) ? $gi['debit'] : 0,
                                    'credit' => isset($gi['credit']) ? $gi['credit'] : 0,
                                    'desc' => isset($gi['description']) ? $gi['description'] : '',
                                );
                            }
                        }
                        if (empty($default_lines)) {
                            $default_lines = array(
                                array('account' => $loan_principle_account, 'debit' => $basic_amount, 'credit' => 0, 'desc' => 'Loan principal'),
                            );
                            foreach ($disburse_deductions as $ded) {
                                $default_lines[] = array(
                                    'account' => $ded['account'],
                                    'debit' => 0,
                                    'credit' => isset($ded['amount']) ? $ded['amount'] : 0,
                                    'desc' => $ded['description'],
                                );
                            }
                            $default_lines[] = array(
                                'account' => $default_credit_account,
                                'debit' => 0,
                                'credit' => $basic_amount,
                                'desc' => 'Net cash to member',
                            );
                        }
                        foreach ($default_lines as $line):
                        ?>
                        <?php // data-source="sys" is what rebuildGlLinesFromOffset() removes before
                              // regenerating; without it the server rows survive the rebuild and
                              // every account ends up in the table twice. ?>
                        <tr class="line-item" data-source="sys">
                            <td>
                                <select class="form-control account-select" name="account[]">
                                    <option value=""><?php echo lang('select_default_text'); ?></option>
                                    <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => $line['account'])); ?>
                                </select>
                            </td>
                            <td><input type="text" name="line_description[]" class="form-control" value="<?php echo htmlspecialchars($line['desc']); ?>"/></td>
                            <td><input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input" value="<?php echo $line['debit']; ?>" placeholder="0.00"/></td>
                            <td><input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input" value="<?php echo $line['credit']; ?>" placeholder="0.00"/></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo lang('delete'); ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                            <td><input type="text" id="total_debit" class="form-control" readonly value="0.00"/></td>
                            <td><input type="text" id="total_credit" class="form-control" readonly value="0.00"/></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2" id="balance_diff" class="text-right" style="font-weight: bold;"></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
                <button type="button" class="btn btn-primary" id="addLineItem" style="margin-top:10px;">
                    <i class="fa fa-plus"></i> <?php echo lang('add_row'); ?>
                </button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo lang('loan_evaluated_test'); ?></button>
                <a href="<?php echo site_url(current_lang() . '/loan/loan_disbursement'); ?>" class="btn btn-default"><?php echo lang('cancel'); ?></a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
(function(){
    var paymentMethodAccounts = <?php echo json_encode($payment_method_credit_accounts); ?>;
    var firstCreditAccount = <?php echo json_encode($default_credit_account); ?>;
    var newLoanAmount = <?php echo json_encode((float) $basic_amount); ?>;
    var newPrincipleAccount = <?php echo json_encode($loan_principle_account); ?>;
    var offsetExceedsMsg = <?php echo json_encode(lang('loan_offset_exceeds_new_loan')); ?>;
    var deductionsExceedMsg = <?php echo json_encode(lang('loan_disburse_deductions_exceed')); ?>;
    var offsetAccountMissingMsg = <?php echo json_encode(lang('loan_offset_account_missing')); ?>;
    var deductionDefs = <?php echo json_encode(isset($disburse_deductions) ? $disburse_deductions : array()); ?>;
    var canApproveWaiver = <?php echo json_encode(!empty($can_approve_waiver)); ?>;

    function loadScript(src, cb, fallback) {
        var s = document.createElement('script');
        s.src = src; s.onload = cb;
        if (fallback) {
            s.onerror = function() { loadScript(fallback, cb); };
        }
        document.head.appendChild(s);
    }
    function init() {
        if (typeof jQuery === 'undefined') {
            setTimeout(init, 50);
            return;
        }
        var $ = jQuery;

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
                $select.trigger('change.select2');
            }
        }

        function makeAccountSelectHtml(selectedAccount) {
            var $source = $('#coaOptionsSource');
            var $tmp = $('<select/>').html($source.html());
            if (selectedAccount) {
                $tmp.find('option').each(function() {
                    var $opt = $(this);
                    if ($opt.prop('disabled')) {
                        return;
                    }
                    if (String($opt.attr('value') || '') === String(selectedAccount)) {
                        $opt.attr('selected', 'selected');
                    } else {
                        $opt.removeAttr('selected');
                    }
                });
            }
            return $tmp.html();
        }

        function addRow(account, debit, credit, desc, source) {
            var tbody = $('#lineItemsTable tbody');
            var html = '<tr class="line-item" data-source="' + (source || 'manual') + '">' +
                '<td><select class="form-control account-select" name="account[]">' +
                makeAccountSelectHtml(account || '') + '</select></td>' +
                '<td><input type="text" name="line_description[]" class="form-control" value="' + (desc || '').replace(/"/g, '&quot;') + '"/></td>' +
                '<td><input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input" value="' + (debit || '') + '" placeholder="0.00"/></td>' +
                '<td><input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input" value="' + (credit || '') + '" placeholder="0.00"/></td>' +
                '<td><button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo addslashes(lang('delete')); ?>"><i class="fa fa-trash"></i></button></td></tr>';
            var $row = $(html);
            tbody.append($row);
            initAccountSelect($row.find('.account-select'));
            updateTotals();
            updateRemoveButtons();
            return $row;
        }

        function updateRemoveButtons() {
            var count = $('#lineItemsTable tbody tr.line-item').length;
            $('.remove-line').prop('disabled', count <= 1);
        }

        function updateTotals() {
            var totalDebit = 0, totalCredit = 0;
            $('.debit-input').each(function() { totalDebit += parseFloat($(this).val()) || 0; });
            $('.credit-input').each(function() { totalCredit += parseFloat($(this).val()) || 0; });
            $('#total_debit').val(totalDebit.toFixed(2));
            $('#total_credit').val(totalCredit.toFixed(2));
            var diff = totalDebit - totalCredit;
            var $diffEl = $('#balance_diff');
            if (Math.abs(diff) < 0.01) {
                $diffEl.text('').css('color', 'green');
            } else {
                $diffEl.text('Difference: ' + diff.toFixed(2)).css('color', 'red');
            }
        }

        function getSelectedOffsets() {
            var rows = [];
            $('.offset-loan-row').each(function() {
                var $row = $(this);
                var $cb = $row.find('.offset-loan-cb');
                if (!$cb.is(':checked')) {
                    return;
                }
                // The waive controls live on the row immediately below.
                var $waiveRow = $row.next('.offset-waive-row');
                var amount = function (suffix) {
                    var v = parseFloat($row.find('.offset-amount[name^="offset_' + suffix + '["]').val());
                    return isNaN(v) ? 0 : v;
                };
                var waived = function (suffix) {
                    var v = parseFloat($waiveRow.find('.offset-waive-input[name^="offset_' + suffix + '["]').val());
                    return isNaN(v) ? 0 : v;
                };
                var reason = String($waiveRow.find('.offset-waive-reason').val() || '');
                var note = String($waiveRow.find('input[type="text"]').val() || '');
                var principal = amount('principal');
                var interest = amount('interest');
                var penalty = amount('penalty');
                var other = amount('other');
                var waivePenalty = Math.min(waived('penalty_waived'), penalty);
                var waiveInterest = Math.min(waived('interest_waived'), interest);
                var total = Math.round((principal + interest + penalty + other - waivePenalty - waiveInterest) * 100) / 100;
                rows.push({
                    LID: String($cb.val()),
                    principal: principal,
                    interest: interest,
                    penalty: penalty,
                    other: other,
                    waive_penalty: waivePenalty,
                    waive_interest: waiveInterest,
                    reason: reason,
                    note: note,
                    total: total,
                    principle_account: String($cb.data('principle-account') || ''),
                    interest_account: String($cb.data('interest-account') || ''),
                    penalty_account: String($cb.data('penalty-account') || ''),
                    penalty_waived_account: String($cb.data('penalty-waived-account') || ''),
                    interest_waived_account: String($cb.data('interest-waived-account') || '')
                });
            });
            return rows;
        }

        /**
         * Recompute the per-loan payoff column, the summary strip and the
         * accounting lines from what is typed in the offset panel. Only rows this
         * function created (data-source="sys") are replaced, so any extra lines
         * added by hand survive.
         */
        function toggleOffsetRowState($row, isSelected) {
            var $controls = $row.find('.offset-amount, .offset-waive-input, .offset-waive-reason, input[type="text"][name^="offset_reason_note["]');
            $controls.prop('disabled', !isSelected);
            $row.next('.offset-waive-row').find('.offset-waive-input, .offset-waive-reason, input[type="text"][name^="offset_reason_note["]').prop('disabled', !isSelected);
        }

        function rebuildGlLinesFromOffset() {
            $('.offset-loan-row').each(function() {
                var $row = $(this);
                var checked = $row.find('.offset-loan-cb').is(':checked');
                toggleOffsetRowState($row, checked);
            });

            var offsets = getSelectedOffsets();
            var deductions = getDeductions();
            var offsetTotal = 0;
            var assessedTotal = 0;
            var waivedTotal = 0;
            offsets.forEach(function (o) {
                offsetTotal += o.total;
                assessedTotal += (o.principal + o.interest + o.penalty + o.other);
                waivedTotal += (o.waive_penalty + o.waive_interest);
            });
            offsetTotal = Math.round(offsetTotal * 100) / 100;
            assessedTotal = Math.round(assessedTotal * 100) / 100;
            waivedTotal = Math.round(waivedTotal * 100) / 100;
            var deductionTotal = 0;
            deductions.forEach(function (d) { deductionTotal += d.amount; });
            deductionTotal = Math.round(deductionTotal * 100) / 100;
            var net = Math.round((newLoanAmount - offsetTotal - deductionTotal) * 100) / 100;

            // Per-loan payoff total in the offset table
            $('.offset-loan-row').each(function () {
                var $row = $(this);
                var lid = String($row.data('lid'));
                var match = null;
                offsets.forEach(function (o) { if (o.LID === lid) { match = o; } });
                $row.find('.offset-row-total').text(match ? match.total.toFixed(2) : '0.00');
            });

            $('#offsetTotalAmt').text(offsetTotal.toFixed(2));
            $('#offsetWaivedAmt').text(waivedTotal.toFixed(2));

            var $warning = $('#offsetWarning').hide().text('');
            var offsetWarnings = [];
            var $topup = $('#offsetTopupNote').hide().text('');
            var isTopup = net < -0.009;

            if (offsets.length === 0) {
                $('#offsetNetProceeds').text(newLoanAmount.toFixed(2));
                $('#offsetNetLabel').text(<?php echo json_encode(lang('loan_offset_net_proceeds')); ?>);
                $warning.hide();
                $topup.hide();
            } else {
                $('#offsetNetProceeds').text(Math.abs(net).toFixed(2));
                $('#offsetNetLabel').text(isTopup
                    ? <?php echo json_encode(lang('loan_offset_topup_label')); ?>
                    : <?php echo json_encode(lang('loan_offset_net_proceeds')); ?>);

                if (isTopup) {
                    $topup.text(<?php echo json_encode(lang('loan_offset_topup_hint')); ?>).show();
                }
                if (waivedTotal > 0.009 && !canApproveWaiver) {
                    offsetWarnings.push(<?php echo json_encode(lang('loan_waiver_pending_hint')); ?>);
                }
            }

            var cashAccount = firstCreditAccount || '';
            var pmId = $('#payment_method').val();
            if (pmId && paymentMethodAccounts && paymentMethodAccounts[pmId]) {
                cashAccount = paymentMethodAccounts[pmId];
            }

            // Drop only the auto-generated rows; keep anything typed by hand.
            // NOTE: which accounts were on the sheet is captured first, so a
            // deduction placeholder the user deleted stays deleted instead of
            // being seeded back on the next rebuild. Everything that is not a
            // hand-typed ("manual") row is regenerated here - the server-rendered
            // worksheet included - so the generated lines can never end up next
            // to a second copy of themselves.
            var accountsOnSheet = {};
            $('#lineItemsTable tbody tr.line-item').each(function () {
                var account = String($(this).find('.account-select').val() || '');
                if (account) {
                    accountsOnSheet[account] = true;
                }
            });
            $('#lineItemsTable tbody tr.line-item').not('[data-source="manual"]').each(function () {
                destroyAccountSelect($(this).find('.account-select'));
                $(this).remove();
            });

            addRow(newPrincipleAccount, newLoanAmount.toFixed(2), '', 'Loan principal', 'sys');

            deductions.forEach(function (d) {
                if (!d.account) {
                    return;
                }
                if (d.amount > 0.009 || accountsOnSheet[String(d.account)]) {
                    addRow(d.account, '', d.amount > 0.009 ? d.amount.toFixed(2) : '', d.description, 'sys');
                }
            });

            // Old loans: principal and interest receivable are credited, the
            // accrued penalty is recognised as income. A component that carries an
            // amount but has no GL account would drop its credit leg and leave the
            // worksheet unbalanced, so it is reported here instead of skipped.
            offsets.forEach(function (o) {
                var legs = [
                    { label: 'principal', account: o.principle_account, amount: o.principal, desc: 'Offset principal ' + o.LID },
                    { label: 'interest', account: o.interest_account, amount: o.interest, desc: 'Offset interest ' + o.LID },
                    { label: 'penalty', account: o.penalty_account, amount: o.penalty, desc: 'Offset penalty ' + o.LID },
                    { label: 'other', account: o.principle_account, amount: o.other, desc: 'Offset other ' + o.LID }
                ];
                legs.forEach(function (leg) {
                    if (leg.amount <= 0.009) {
                        return;
                    }
                    if (!leg.account) {
                        offsetWarnings.push(offsetAccountMissingMsg.replace('%s', o.LID + ' (' + leg.label + ')'));
                        return;
                    }
                    addRow(leg.account, '', leg.amount.toFixed(2), leg.desc, 'sys');
                });
                // Gross then waive: recognise the waived amount, debit the contra.
                if (o.waive_penalty > 0.009 && o.penalty_waived_account && o.penalty_account) {
                    addRow(o.penalty_waived_account, o.waive_penalty.toFixed(2), '', 'Penalty waived ' + o.LID, 'sys');
                    addRow(o.penalty_account, '', o.waive_penalty.toFixed(2), 'Penalty waived ' + o.LID + ' (income recognised)', 'sys');
                }
                if (o.waive_interest > 0.009 && o.interest_waived_account && o.interest_account) {
                    addRow(o.interest_waived_account, o.waive_interest.toFixed(2), '', 'Interest waived ' + o.LID, 'sys');
                    addRow(o.interest_account, '', o.waive_interest.toFixed(2), 'Interest waived ' + o.LID + ' (income recognised)', 'sys');
                }
            });

            if (net > 0.009) {
                addRow(cashAccount, '', net.toFixed(2), 'Net cash to member', 'sys');
            } else if (isTopup && cashAccount) {
                // The payoff is bigger than the new loan: the member pays the
                // difference in cash, which is a debit rather than a credit.
                addRow(cashAccount, Math.abs(net).toFixed(2), '', 'Cash from member (top-up)', 'sys');
            } else if (offsets.length === 0 && deductions.length === 0) {
                addRow(cashAccount, '', newLoanAmount.toFixed(2), 'Disbursement source', 'sys');
            }
            if (offsetWarnings.length) {
                $warning.text(offsetWarnings.join(' ')).show();
            }
            updateTotals();
            updateRemoveButtons();
        }

        function getDeductions() {
            var amountsByAccount = {};
            $('#lineItemsTable tbody tr.line-item').each(function() {
                var $row = $(this);
                var account = String($row.find('.account-select').val() || '');
                var credit = parseFloat($row.find('.credit-input').val()) || 0;
                if (account) {
                    amountsByAccount[account] = (amountsByAccount[account] || 0) + credit;
                }
            });
            var rows = [];
            (deductionDefs || []).forEach(function(d) {
                rows.push({
                    key: String(d.key || ''),
                    account: String(d.account || ''),
                    description: String(d.description || d.label || ''),
                    amount: parseFloat(amountsByAccount[String(d.account || '')]) || 0
                });
            });
            return rows;
        }

        $('#payment_method').on('change', function() {
            var id = $(this).val();
            var account = (paymentMethodAccounts && paymentMethodAccounts[id]) ? paymentMethodAccounts[id] : '';
            firstCreditAccount = account || firstCreditAccount;
            if ($('.offset-loan-cb').length || deductionDefs.length) {
                rebuildGlLinesFromOffset();
                return;
            }
            var $rows = $('#lineItemsTable tbody tr.line-item');
            if ($rows.length >= 2 && account) {
                var $secondRow = $rows.eq(1);
                setAccountValue($secondRow.find('.account-select'), account);
            }
            updateTotals();
        });

        $(document).on('change', '.offset-loan-cb', function() {
            rebuildGlLinesFromOffset();
        });

        // Typed overrides and waivers drive the accounting lines.
        $(document).on('keyup change', '.offset-amount, .offset-waive-input', function() {
            var $row = $(this).closest('.offset-loan-row');
            if (!$row.length) {
                $row = $(this).closest('tr').prev('.offset-loan-row');
            }
            var $penalty = $row.find('.offset-amount[name^="offset_penalty["]');
            var $interest = $row.find('.offset-amount[name^="offset_interest["]');
            var $wpen = $row.find('.offset-waive-input[name^="offset_penalty_waived["]');
            var $wint = $row.find('.offset-waive-input[name^="offset_interest_waived["]');
            // A waiver can never exceed what was assessed.
            if (parseFloat($wpen.val()) > parseFloat($penalty.val())) {
                $wpen.val(parseFloat($penalty.val()).toFixed(2));
            }
            if (parseFloat($wint.val()) > parseFloat($interest.val())) {
                $wint.val(parseFloat($interest.val()).toFixed(2));
            }
            rebuildGlLinesFromOffset();
        });

        $(document).on('click', '.offset-reset', function() {
            var $waiveRow = $(this).closest('.offset-waive-row');
            var $row = $waiveRow.prev('.offset-loan-row');
            var $cb = $row.find('.offset-loan-cb');
            $row.find('.offset-amount[name^="offset_principal["]').val(parseFloat($cb.data('principal') || 0).toFixed(2));
            $row.find('.offset-amount[name^="offset_interest["]').val(parseFloat($cb.data('interest') || 0).toFixed(2));
            $row.find('.offset-amount[name^="offset_penalty["]').val(parseFloat($cb.data('penalty') || 0).toFixed(2));
            $row.find('.offset-amount[name^="offset_other["]').val('0.00');
            $row.find('.offset-waive-input').val('0.00');
            $waiveRow.find('.offset-waive-reason').val('');
            $waiveRow.find('input[type="text"]').val('');
            rebuildGlLinesFromOffset();
        });

        $('#addLineItem').on('click', function() {
            addRow('', '', '', '', 'manual');
        });

        $(document).on('click', '.remove-line', function() {
            if ($('#lineItemsTable tbody tr.line-item').length > 1) {
                var $row = $(this).closest('tr');
                destroyAccountSelect($row.find('.account-select'));
                $row.remove();
                updateTotals();
                updateRemoveButtons();
            }
        });

        $(document).on('keyup change', '.debit-input, .credit-input', function() {
            updateTotals();
        });

        $(document).on('change', '#lineItemsTable .credit-input', function() {
            var account = String($(this).closest('tr').find('.account-select').val() || '');
            var isDeduction = (deductionDefs || []).some(function(d) {
                return String(d.account || '') === account;
            });
            if (isDeduction) {
                rebuildGlLinesFromOffset();
            }
        });

        $('#loanDisburseEntryForm').on('submit', function(e) {
            var offsets = getSelectedOffsets();
            // A payoff larger than the new loan is allowed - the member covers the
            // difference in cash, so the worksheet carries a top-up debit line.
            var missingReason = null;
            offsets.forEach(function (o) {
                if ((o.waive_penalty > 0.009 || o.waive_interest > 0.009) && !o.reason) {
                    missingReason = o.LID;
                }
            });
            if (missingReason) {
                e.preventDefault();
                alert(<?php echo json_encode(lang('loan_waiver_reason_required_js')); ?>.replace('%s', missingReason));
                return false;
            }
            var totalDebit = 0, totalCredit = 0, hasItems = false;
            $('.debit-input').each(function() { totalDebit += parseFloat($(this).val()) || 0; });
            $('.credit-input').each(function() {
                var v = parseFloat($(this).val()) || 0;
                totalCredit += v;
                if (v > 0) hasItems = true;
            });
            $('.debit-input').each(function() { if (parseFloat($(this).val()) > 0) hasItems = true; });
            if (!hasItems) {
                e.preventDefault();
                alert('<?php echo addslashes(lang('loan_disburse_entries_required')); ?>');
                return false;
            }
            if (Math.abs(totalDebit - totalCredit) > 0.01) {
                e.preventDefault();
                alert('<?php echo addslashes(lang('debits_credits_not_balanced')); ?>');
                return false;
            }
            return true;
        });

        $('.account-select').each(function() { initAccountSelect($(this)); });
        updateRemoveButtons();
        rebuildGlLinesFromOffset();

        function ensureBootstrapDP(cb) {
            function wrapBootstrapDP() {
                if ($.fn.datepicker && $.fn.datepicker.DPGlobal) {
                    $.fn.bootstrapDP = $.fn.datepicker;
                    if ($.fn.datepicker.noConflict) { $.fn.datepicker.noConflict(); }
                }
                cb();
            }
            if (!($.fn.datepicker && $.fn.datepicker.DPGlobal)) {
                loadScript(
                    '<?php echo base_url(); ?>assets/js/plugins/datapicker/bootstrap-datepicker.js',
                    wrapBootstrapDP,
                    'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js'
                );
            } else {
                wrapBootstrapDP();
            }
        }
        function initDatePicker() {
            var picker = $.fn.bootstrapDP || $.fn.datepicker;
            if (!picker) return;
            picker.call($('#disburseDatePicker'), {
                todayBtn: 'linked',
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
                format: 'dd-mm-yyyy',
                orientation: 'bottom auto',
                todayHighlight: true,
                container: 'body'
            });
        }
        ensureBootstrapDP(initDatePicker);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
