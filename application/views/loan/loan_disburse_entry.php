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
                            <input type="text" name="disbursedate" value="<?php echo set_value('disbursedate', date('d-m-Y')); ?>" data-date-format="dd-mm-yyyy" class="form-control" placeholder="dd-mm-yyyy" required/>
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
                <textarea name="comment" class="form-control" rows="2" required><?php echo set_value('comment'); ?></textarea>
                <?php echo form_error('comment'); ?>
            </div>

            <?php if (!empty($offsetable_loans)) { ?>
            <div class="offset-panel">
                <div class="offset-head"><?php echo lang('loan_offset_section'); ?></div>
                <div class="offset-body">
                    <p class="section-note"><?php echo lang('loan_offset_help'); ?></p>
                    <div class="docs-table table-responsive">
                        <table class="table table-striped" id="offsetLoansTable">
                            <thead>
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th><?php echo lang('loan_LID'); ?></th>
                                    <th><?php echo lang('loan_product'); ?></th>
                                    <th class="text-right"><?php echo lang('loan_offset_principal'); ?></th>
                                    <th class="text-right"><?php echo lang('loan_offset_interest'); ?></th>
                                    <th class="text-right"><?php echo lang('loan_offset_total'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($offsetable_loans as $ol) {
                                    $checked = in_array($ol->LID, $selected_offset_loans, true);
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="offset-loan-cb" name="offset_loans[]" value="<?php echo htmlspecialchars($ol->LID); ?>"
                                               data-principal="<?php echo htmlspecialchars($ol->principal_outstanding); ?>"
                                               data-interest="<?php echo htmlspecialchars($ol->interest_outstanding); ?>"
                                               data-total="<?php echo htmlspecialchars($ol->total_outstanding); ?>"
                                               data-principle-account="<?php echo htmlspecialchars($ol->principle_account); ?>"
                                               data-interest-account="<?php echo htmlspecialchars($ol->interest_account); ?>"
                                               <?php echo $checked ? 'checked="checked"' : ''; ?> />
                                    </td>
                                    <td><?php echo htmlspecialchars($ol->LID); ?></td>
                                    <td><?php echo htmlspecialchars($ol->product_name); ?></td>
                                    <td class="text-right"><?php echo number_format($ol->principal_outstanding, 2); ?></td>
                                    <td class="text-right"><?php echo number_format($ol->interest_outstanding, 2); ?></td>
                                    <td class="text-right"><strong><?php echo number_format($ol->total_outstanding, 2); ?></strong></td>
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
                        <strong><?php echo lang('loan_offset_net_proceeds'); ?>:</strong> <span id="offsetNetProceeds"><?php echo number_format($basic_amount, 2); ?></span>
                        <div id="offsetWarning" class="text-danger" style="display:none; margin-top:6px;"></div>
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
                        foreach ($default_lines as $line):
                        ?>
                        <tr class="line-item">
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
    var deductionDefs = <?php echo json_encode(isset($disburse_deductions) ? $disburse_deductions : array()); ?>;

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

        function addRow(account, debit, credit, desc) {
            var tbody = $('#lineItemsTable tbody');
            var html = '<tr class="line-item">' +
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
            $('.offset-loan-cb:checked').each(function() {
                var $cb = $(this);
                rows.push({
                    LID: $cb.val(),
                    principal: parseFloat($cb.data('principal')) || 0,
                    interest: parseFloat($cb.data('interest')) || 0,
                    total: parseFloat($cb.data('total')) || 0,
                    principle_account: String($cb.data('principle-account') || ''),
                    interest_account: String($cb.data('interest-account') || '')
                });
            });
            return rows;
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

        function rebuildGlLinesFromOffset() {
            var offsets = getSelectedOffsets();
            var deductions = getDeductions();
            var offsetTotal = 0;
            offsets.forEach(function(o) { offsetTotal += o.total; });
            offsetTotal = Math.round(offsetTotal * 100) / 100;
            var deductionTotal = 0;
            deductions.forEach(function(d) { deductionTotal += d.amount; });
            deductionTotal = Math.round(deductionTotal * 100) / 100;
            var net = Math.round((newLoanAmount - offsetTotal - deductionTotal) * 100) / 100;

            $('#offsetTotalAmt').text(offsetTotal.toFixed(2));
            $('#offsetNetProceeds').text(net.toFixed(2));
            if ((offsetTotal + deductionTotal) > newLoanAmount + 0.009) {
                $('#offsetWarning').text(deductionsExceedMsg).show();
            } else {
                $('#offsetWarning').hide().text('');
            }

            var cashAccount = firstCreditAccount || '';
            var pmId = $('#payment_method').val();
            if (pmId && paymentMethodAccounts && paymentMethodAccounts[pmId]) {
                cashAccount = paymentMethodAccounts[pmId];
            }

            $('#lineItemsTable tbody tr.line-item').each(function() {
                destroyAccountSelect($(this).find('.account-select'));
            });
            $('#lineItemsTable tbody').empty();
            addRow(newPrincipleAccount, newLoanAmount.toFixed(2), '', 'Loan principal');

            deductions.forEach(function(d) {
                if (d.account) {
                    addRow(d.account, '', d.amount > 0.009 ? d.amount.toFixed(2) : '', d.description);
                }
            });

            offsets.forEach(function(o) {
                if (o.principal > 0.009 && o.principle_account) {
                    addRow(o.principle_account, '', o.principal.toFixed(2), 'Offset principal ' + o.LID);
                }
                if (o.interest > 0.009 && o.interest_account) {
                    addRow(o.interest_account, '', o.interest.toFixed(2), 'Offset interest ' + o.LID);
                }
            });

            if (net > 0.009) {
                addRow(cashAccount, '', net.toFixed(2), 'Net cash to member');
            } else if (offsets.length === 0 && deductions.length === 0) {
                addRow(cashAccount, '', newLoanAmount.toFixed(2), 'Disbursement source');
            }
            updateTotals();
            updateRemoveButtons();
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

        $('#addLineItem').on('click', function() {
            addRow('', '', '', '');
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
            var deductions = getDeductions();
            var offsetTotal = 0;
            offsets.forEach(function(o) { offsetTotal += o.total; });
            var deductionTotal = 0;
            deductions.forEach(function(d) { deductionTotal += d.amount; });
            if ((offsetTotal + deductionTotal) > newLoanAmount + 0.009) {
                e.preventDefault();
                alert(deductionsExceedMsg);
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
