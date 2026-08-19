<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php
$account_list = isset($account_list) ? $account_list : array();
$payment_methods = isset($payment_methods) ? $payment_methods : array();
$next_receipt_no = isset($next_receipt_no) ? $next_receipt_no : '';
$selected_loan_repayment_lid = isset($selected_loan_repayment_lid) ? $selected_loan_repayment_lid : set_value('loan_repayment_lid');
$selected_received_from_type = isset($selected_received_from_type) ? $selected_received_from_type : set_value('received_from_type');
$list_url = site_url(current_lang() . '/cash_receipt/cash_receipt_list');
$received_from_types = array(
    'loan_repayment' => lang('cash_receipt_from_loan_repayment'),
    'member' => lang('cash_receipt_from_member'),
    'supplier' => lang('cash_receipt_from_supplier'),
    'customer' => lang('cash_receipt_from_customer'),
    'miscellaneous' => lang('cash_receipt_from_miscellaneous'),
);
?>

<style type="text/css">
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
.select2-container--default .select2-results__option[aria-disabled=true]{color:#222;cursor:default;}
.select2-container--default .select2-results__option .coa-bold{font-weight:bold;color:#111;}
.select2-container{width:100%!important;}

.cr-create-page { margin-top: 4px; }
.cr-create-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.cr-create-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.cr-create-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.cr-create-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.cr-create-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.cr-create-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cr-create-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cr-create-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.cr-create-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.cr-create-page .form-horizontal .form-group { margin-bottom: 16px; }
.cr-create-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.cr-create-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.cr-create-page textarea.form-control { height: auto; min-height: 80px; }
.cr-create-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.cr-create-page .form-control[readonly] {
    background: #f8fafb;
    font-weight: 700;
}
.cr-create-page .help-block {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
    margin-bottom: 0;
}
.cr-create-page .required { color: #ed5565; }
.cr-create-page .input-group-addon {
    background: #f8fafb;
    cursor: pointer;
    color: #1ab394;
}
.cr-create-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.cr-create-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.cr-create-page .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.cr-create-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.cr-create-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.cr-create-page .select2-container--default.select2-container--focus .select2-selection--single,
.cr-create-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}
.cr-create-page .lines-table {
    margin: 0;
    background: #fff;
}
.cr-create-page .lines-table > thead > tr > th {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
    vertical-align: middle;
}
.cr-create-page .lines-table > tbody > tr > td,
.cr-create-page .lines-table > tfoot > tr > td {
    vertical-align: middle;
}
.cr-create-page .lines-table .amount-cell input {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-weight: 600;
}
.cr-create-page .balance-diff {
    font-weight: 700;
    font-size: 13px;
}
.cr-create-page .line-actions { margin-top: 12px; }
.cr-create-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.cr-create-page .cancelled-box {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 12px 14px;
}
.cr-create-page .cancelled-box .checkbox { margin: 0; }
.cr-create-page .loan-repayment-box {
    display: none;
    background: #f0faf7;
    border: 1px solid #c9ebe3;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 4px;
    font-size: 13px;
    color: #2f4050;
}
.cr-create-page .loan-repayment-box strong { color: #0e7c69; }
.cr-create-page .loan-repayment-meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px 16px;
    margin-top: 8px;
}
@media (max-width: 767px) {
    .cr-create-page .loan-repayment-meta { grid-template-columns: 1fr; }
}
.cr-create-page .received-from-detail {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 12px 14px;
    margin-top: 4px;
}
.cr-create-page .received-from-selected {
    margin-top: 8px;
    font-size: 13px;
    color: #0e7c69;
    font-weight: 600;
}
.cr-create-page .received-from-results {
    margin-top: 10px;
    max-height: 260px;
    overflow-y: auto;
}
.cr-create-page .received-from-results .table {
    margin-bottom: 0;
    background: #fff;
}
</style>

<select id="coaOptionsSource" style="display:none;">
    <option value=""><?php echo lang('select_default_text'); ?></option>
    <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => '')); ?>
</select>

<?php echo form_open_multipart(current_lang() . "/cash_receipt/cash_receipt_create/", 'class="form-horizontal" id="cashReceiptForm"'); ?>

<div class="col-lg-12 cr-create-page">
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
    if (function_exists('gl_books_close_alert_html')) {
        echo gl_books_close_alert_html();
    }
    ?>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-file-text-o icon-badge"></i>
                <h4><?php echo lang('cash_receipt_create'); ?></h4>
            </div>
            <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_no'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="receipt_no" value="<?php echo htmlspecialchars(set_value('receipt_no', $next_receipt_no), ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required/>
                            <?php echo form_error('receipt_no'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_date'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <div class="input-group date" id="datetimepicker">
                                <input type="text" name="receipt_date" placeholder="<?php echo lang('hint_date'); ?>"
                                       value="<?php echo htmlspecialchars(set_value('receipt_date', date('d-m-Y')), ENT_QUOTES, 'UTF-8'); ?>"
                                       data-date-format="dd-mm-yyyy" class="form-control" required autocomplete="off"/>
                                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                            </div>
                            <?php echo form_error('receipt_date'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_received_from'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <select name="received_from_type" id="received_from_type" class="form-control" required>
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php foreach ($received_from_types as $type_key => $type_label) { ?>
                                    <option value="<?php echo htmlspecialchars($type_key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((string) $selected_received_from_type === (string) $type_key) ? 'selected="selected"' : ''; ?>>
                                        <?php echo htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('received_from_type'); ?>
                            <div class="received-from-detail" id="receivedFromDetail" style="display:none;">
                                <div id="receivedFromSearchWrap" style="display:none;">
                                    <label id="receivedFromSearchLabel" class="control-label" style="padding-top:0;margin-bottom:6px;display:block;"></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="receivedFromSearchKey" autocomplete="off" placeholder=""/>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" id="doReceivedFromSearch">
                                                <i class="fa fa-search"></i> <?php echo lang('search'); ?>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="received-from-selected" id="receivedFromSelected" style="display:none;"></div>
                                    <div class="received-from-results" id="receivedFromSearchResults"></div>
                                </div>
                                <div id="receivedFromMiscWrap" style="display:none;">
                                    <label class="control-label" style="padding-top:0;margin-bottom:6px;display:block;"><?php echo lang('cash_receipt_received_from_name'); ?> : <span class="required">*</span></label>
                                    <input type="text" id="received_from_name" class="form-control" value="<?php echo htmlspecialchars(set_value('received_from'), ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                                </div>
                            </div>
                            <input type="hidden" name="received_from" id="received_from" value="<?php echo htmlspecialchars(set_value('received_from'), ENT_QUOTES, 'UTF-8'); ?>"/>
                            <input type="hidden" name="member_pid" id="member_pid" value="<?php echo htmlspecialchars(set_value('member_pid'), ENT_QUOTES, 'UTF-8'); ?>"/>
                            <input type="hidden" name="member_id" id="member_id" value="<?php echo htmlspecialchars(set_value('member_id'), ENT_QUOTES, 'UTF-8'); ?>"/>
                            <input type="hidden" name="loan_repayment_lid" id="loan_repayment_lid" value="<?php echo htmlspecialchars((string) $selected_loan_repayment_lid, ENT_QUOTES, 'UTF-8'); ?>"/>
                            <?php echo form_error('received_from'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_payment_method'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php
                                $selected_payment = set_value('payment_method');
                                $default_to_cash = empty($selected_payment) && isset($default_cash_id);
                                foreach ($payment_methods as $id => $name) {
                                    $is_selected = false;
                                    if ($default_to_cash && $id == $default_cash_id) {
                                        $is_selected = true;
                                    } else if (!empty($selected_payment) && (string) $id === (string) $selected_payment) {
                                        $is_selected = true;
                                    }
                                    $is_cheque = (stripos(trim($name), 'cheque') !== false || stripos(trim($name), 'check') !== false) ? ' data-is-cheque="1"' : '';
                                    ?>
                                    <option value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $is_selected ? ' selected="selected"' : ''; ?><?php echo $is_cheque; ?>>
                                        <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('payment_method'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="loan-repayment-box" id="loanRepaymentPanel">
                        <strong><?php echo lang('cash_receipt_loan_panel'); ?></strong>
                        <div id="loanRepaymentPanelBody" style="margin-top:8px;"></div>
                        <div id="loanRepaymentDueWrap" style="display:none;margin-top:12px;">
                            <p class="help-block" id="loanRepaymentDueExplanation" style="margin-bottom:8px;"></p>
                            <div class="table-responsive">
                                <table class="table table-condensed" id="loanRepaymentDueTable" style="margin-bottom:8px;background:#fff;">
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
                                    <tbody id="loanRepaymentDueBody"></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right"><?php echo lang('loan_repay_total_due'); ?></th>
                                            <th class="text-right" id="crDueTotalInstallments">0.00</th>
                                            <th class="text-right" id="crDueTotalPenalty">0.00</th>
                                            <th></th>
                                            <th class="text-right" id="crDueTotalDue">0.00</th>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-right"><?php echo lang('loan_repay_carry_balance'); ?></td>
                                            <td class="text-right" id="crDueCarry">0.00</td>
                                        </tr>
                                        <tr>
                                            <th colspan="6" class="text-right"><?php echo lang('loan_repay_net_due'); ?> / <?php echo lang('loan_repay_suggested'); ?></th>
                                            <th class="text-right" id="crDueNetDue">0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div style="margin-top:8px;">
                                <label for="loan_repayment_amount"><?php echo lang('cash_receipt_loan_payment_amount'); ?></label>
                                <input type="number" step="0.01" min="0" id="loan_repayment_amount" class="form-control" style="max-width:180px;display:inline-block;margin:0 8px;"/>
                                <button type="button" class="btn btn-white btn-sm" id="btnUseSuggestedLoanAmount">
                                    <i class="fa fa-magic"></i> <?php echo lang('loan_repay_use_suggested'); ?>
                                </button>
                                <span class="help-block" style="display:inline;margin-left:8px;" id="crDueMinHint"></span>
                            </div>
                            <p class="text-danger" id="loanRepaymentPreviewMsg" style="display:none;margin-top:8px;"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="cheque_details" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_cheque_no'); ?> :</label>
                        <div class="col-lg-8">
                            <input type="text" name="cheque_no" value="<?php echo htmlspecialchars(set_value('cheque_no'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_bank_name'); ?> :</label>
                        <div class="col-lg-8">
                            <input type="text" name="bank_name" value="<?php echo htmlspecialchars(set_value('bank_name'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-lg-2 control-label"><?php echo lang('cash_receipt_description'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-10">
                            <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars(set_value('description'), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <?php echo form_error('description'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <div class="cancelled-box">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="cancelled" id="cancelled" value="1" <?php echo set_checkbox('cancelled', '1'); ?>/>
                                        <?php echo lang('cancelled'); ?>
                                    </label>
                                </div>
                                <p class="help-block"><?php echo lang('cash_receipt_cancelled_help'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            <div class="table-responsive">
                <table id="lineItemsTable" class="table table-striped lines-table">
                    <thead>
                        <tr>
                            <th style="width: 32%;"><?php echo lang('cash_receipt_account'); ?> <span class="required">*</span></th>
                            <th style="width: 28%;"><?php echo lang('cash_receipt_line_description'); ?></th>
                            <th style="width: 15%;"><?php echo lang('journalentry_debit'); ?></th>
                            <th style="width: 15%;"><?php echo lang('journalentry_credit'); ?></th>
                            <th style="width: 10%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="line-item">
                            <td>
                                <select class="form-control account-select" name="account[]">
                                    <option value=""><?php echo lang('select_default_text'); ?></option>
                                    <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => '')); ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="line_description[]" class="form-control"/>
                            </td>
                            <td class="amount-cell">
                                <input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input" placeholder="0.00"/>
                            </td>
                            <td class="amount-cell">
                                <input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input" placeholder="0.00"/>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo htmlspecialchars(lang('delete'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                            <td class="amount-cell">
                                <input type="text" id="total_debit" class="form-control" readonly value="0.00"/>
                            </td>
                            <td class="amount-cell">
                                <input type="text" id="total_credit" class="form-control" readonly value="0.00"/>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2" id="balance_diff" class="text-right balance-diff"></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="line-actions">
                <button type="button" class="btn btn-primary" id="addLineItem">
                    <i class="fa fa-plus"></i> <?php echo lang('add_row'); ?>
                </button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> <?php echo lang('save'); ?>
                </button>
                <a href="<?php echo $list_url; ?>" class="btn btn-default">
                    <i class="fa fa-undo"></i> <?php echo lang('cancel'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
(function(){
    function loadScript(src, cb, fallback){
        var s = document.createElement('script');
        s.src = src; s.onload = cb;
        if (fallback) { s.onerror = function(){ loadScript(fallback, cb); }; }
        document.head.appendChild(s);
    }
    function initOnceReady(){
        if (!window.jQuery) { setTimeout(initOnceReady, 50); return; }
        var $ = window.jQuery;
        function boot(){
            function formatCoaOption(data) {
                if (!data.element) { return data.text; }
                var $opt = $(data.element);
                var isParent = $opt.data('is-parent') == 1 || $opt.hasClass('coa-parent-account');
                var isHeader = $opt.data('coa-header') == 1;
                if (isParent || isHeader) {
                    return $('<span class="coa-bold"></span>').text(data.text);
                }
                return data.text;
            }

            function initAccountSelect($el) {
                if (!$el || !$el.length || !$.fn.select2) { return; }
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

            function makeAccountSelectHtml(selectedAccount) {
                var $tmp = $('<select/>').html($('#coaOptionsSource').html());
                if (selectedAccount) {
                    $tmp.find('option').each(function() {
                        var $opt = $(this);
                        if ($opt.prop('disabled')) { return; }
                        if (String($opt.attr('value') || '') === String(selectedAccount)) {
                            $opt.attr('selected', 'selected');
                        } else {
                            $opt.removeAttr('selected');
                        }
                    });
                }
                return $tmp.html();
            }

            function addLineRow(account, debit, credit, desc, role) {
                var roleAttr = role ? ' data-line-role="' + String(role).replace(/"/g, '') + '"' : '';
                var html = '<tr class="line-item"' + roleAttr + '>' +
                    '<td><select class="form-control account-select" name="account[]">' + makeAccountSelectHtml(account || '') + '</select></td>' +
                    '<td><input type="text" name="line_description[]" class="form-control" value="' + String(desc || '').replace(/"/g, '&quot;') + '"/></td>' +
                    '<td class="amount-cell"><input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input" value="' + (debit || '') + '" placeholder="0.00"/></td>' +
                    '<td class="amount-cell"><input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input" value="' + (credit || '') + '" placeholder="0.00"/></td>' +
                    '<td><button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo addslashes(lang('delete')); ?>"><i class="fa fa-trash"></i></button></td></tr>';
                var $row = $(html);
                $('#lineItemsTable tbody').append($row);
                initAccountSelect($row.find('.account-select'));
                updateRemoveButtons();
                calculateTotals();
                return $row;
            }

            function resetLineItems() {
                $('#lineItemsTable tbody tr.line-item').each(function(){
                    destroyAccountSelect($(this).find('.account-select'));
                });
                $('#lineItemsTable tbody').empty();
                addLineRow('', '', '', '');
            }

            function populateLoanLines(items) {
                $('#lineItemsTable tbody tr.line-item').each(function(){
                    destroyAccountSelect($(this).find('.account-select'));
                });
                $('#lineItemsTable tbody').empty();
                if (!items || !items.length) {
                    addLineRow('', '', '', '');
                    return;
                }
                $.each(items, function(i, item){
                    addLineRow(item.account || '', item.debit || '', item.credit || '', item.description || '', item.role || '');
                });
            }

            function setRowAccount($row, account) {
                if (!$row || !$row.length || !account) return;
                var $sel = $row.find('.account-select');
                $sel.val(account);
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.trigger('change');
                } else {
                    initAccountSelect($sel);
                }
            }

            function ensureBootstrapDP(cb){
                function wrapBootstrapDP(){
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
            function initPicker(){
                var picker = $.fn.bootstrapDP || $.fn.datepicker;
                if (!picker) return;
                picker.call($('#datetimepicker'), {
                    todayBtn: 'linked', keyboardNavigation: false, forceParse: false,
                    calendarWeeks: true, autoclose: true, format: 'dd-mm-yyyy',
                    orientation: 'bottom auto', todayHighlight: true, container: 'body'
                });
            }

            ensureBootstrapDP(initPicker);
            $('.account-select').each(function(){ initAccountSelect($(this)); });
            updateRemoveButtons();
            calculateTotals();

            function toggleChequeDetails(){
                var $opt = $('#payment_method option:selected');
                var txt = ($opt.text() || '').toLowerCase();
                if ($opt.data('is-cheque') == 1 || $opt.data('is-cheque') === '1' || txt.indexOf('cheque') >= 0 || txt.indexOf('check') >= 0) {
                    $('#cheque_details').show();
                } else {
                    $('#cheque_details').hide();
                }
            }
            $('#payment_method').on('change', function(){
                toggleChequeDetails();
                if ($('#received_from_type').val() === 'loan_repayment' && $('#loan_repayment_lid').val()) {
                    loadLoanWorksheet($('#loan_repayment_lid').val());
                } else {
                    syncPaymentMethodAccount();
                }
            });
            toggleChequeDetails();

            $('#addLineItem').on('click', function(){
                addLineRow('', '', '', '');
            });

            $(document).on('click', '.remove-line', function(){
                if ($('#lineItemsTable tbody tr.line-item').length > 1) {
                    var $row = $(this).closest('tr');
                    destroyAccountSelect($row.find('.account-select'));
                    $row.remove();
                    updateRemoveButtons();
                    calculateTotals();
                }
            });

            function updateRemoveButtons(){
                var count = $('#lineItemsTable tbody tr.line-item').length;
                $('.remove-line').prop('disabled', count <= 1);
            }

            $(document).on('keyup change', '.debit-input, .credit-input', function(){
                calculateTotals();
            });

            function calculateTotals(){
                var totalDebit = 0, totalCredit = 0;
                $('.debit-input').each(function(){ totalDebit += parseFloat($(this).val()) || 0; });
                $('.credit-input').each(function(){ totalCredit += parseFloat($(this).val()) || 0; });
                $('#total_debit').val(totalDebit.toFixed(2));
                $('#total_credit').val(totalCredit.toFixed(2));
                var diff = totalDebit - totalCredit;
                if (Math.abs(diff) < 0.01) { $('#balance_diff').text('').css('color', 'green'); }
                else { $('#balance_diff').text('Diff: ' + diff.toFixed(2)).css('color', 'red'); }
            }

            $('#cashReceiptForm').on('submit', function(e){
                var type = $('#received_from_type').val();
                if (type === 'miscellaneous') {
                    syncMiscReceivedFrom();
                }
                if (!type || !$.trim($('#received_from').val() || '')) {
                    alert(i18n.payerRequired);
                    e.preventDefault();
                    return false;
                }
                if (type === 'loan_repayment' && !$.trim($('#loan_repayment_lid').val() || '')) {
                    alert(i18n.loanRequired);
                    e.preventDefault();
                    return false;
                }
                if (type === 'loan_repayment' && loanDueState.minimum > 0) {
                    var cashDebit = 0;
                    $('#lineItemsTable tbody tr.line-item').each(function(){
                        cashDebit += parseFloat($(this).find('.debit-input').val() || 0) || 0;
                    });
                    if (cashDebit + 0.00001 < loanDueState.minimum) {
                        alert((i18n.minApply || '').replace('%s', formatMoney(loanDueState.minimum)));
                        e.preventDefault();
                        return false;
                    }
                }
                if ($('#cancelled').is(':checked')) {
                    return true;
                }
                var totalDebit = 0, totalCredit = 0, hasItems = false;
                $('.debit-input').each(function(){ totalDebit += parseFloat($(this).val()) || 0; });
                $('.credit-input').each(function(){
                    var v = parseFloat($(this).val()) || 0;
                    totalCredit += v;
                    if (v > 0) hasItems = true;
                });
                if (!hasItems) { $('.debit-input').each(function(){ if (parseFloat($(this).val()) > 0) hasItems = true; }); }
                if (!hasItems) {
                    alert('<?php echo lang('cash_receipt_no_items'); ?>');
                    e.preventDefault();
                    return false;
                }
                if (Math.abs(totalDebit - totalCredit) > 0.01) {
                    alert('<?php echo lang('debits_credits_not_balanced'); ?>');
                    e.preventDefault();
                    return false;
                }
                return true;
            });

            $('#cancelled').on('change', function(){
                if ($(this).is(':checked')) {
                    $('#lineItemsTable th .required').hide();
                } else {
                    $('#lineItemsTable th .required').show();
                }
            });

            var memberSearchUrl = '<?php echo site_url(current_lang() . '/cash_receipt/search_member'); ?>';
            var supplierSearchUrl = '<?php echo site_url(current_lang() . '/cash_receipt/search_supplier'); ?>';
            var customerSearchUrl = '<?php echo site_url(current_lang() . '/cash_receipt/search_customer'); ?>';
            var memberLoansUrl = '<?php echo site_url(current_lang() . '/cash_receipt/member_repayable_loans'); ?>';
            var loanWorksheetUrl = '<?php echo site_url(current_lang() . '/cash_receipt/loan_repayment_worksheet'); ?>';
            var paymentMethodGlUrl = '<?php echo site_url(current_lang() . '/cash_receipt/payment_method_gl_account'); ?>';
            var arAccountUrl = '<?php echo site_url(current_lang() . '/cash_receipt/get_ar_account'); ?>';
            var apAccountUrl = '<?php echo site_url(current_lang() . '/cash_receipt/get_ap_account'); ?>';
            var loanCache = {};
            var selectedMemberName = '';
            var linesFromLoan = false;

            var i18n = {
                searchLoan: <?php echo json_encode(lang('cash_receipt_search_loan_repayment')); ?>,
                searchMember: <?php echo json_encode(lang('cash_receipt_search_member')); ?>,
                searchSupplier: <?php echo json_encode(lang('cash_receipt_search_supplier')); ?>,
                searchCustomer: <?php echo json_encode(lang('cash_receipt_search_customer')); ?>,
                phMember: <?php echo json_encode(lang('cash_receipt_search_placeholder_member')); ?>,
                phSupplier: <?php echo json_encode(lang('cash_receipt_search_placeholder_supplier')); ?>,
                phCustomer: <?php echo json_encode(lang('cash_receipt_search_placeholder_customer')); ?>,
                selected: <?php echo json_encode(lang('cash_receipt_selected_payer')); ?>,
                minChars: <?php echo json_encode(lang('cash_receipt_search_min_chars')); ?>,
                payerRequired: <?php echo json_encode(lang('cash_receipt_payer_required')); ?>,
                loanRequired: <?php echo json_encode(lang('cash_receipt_loan_repayment_required')); ?>,
                noMembers: <?php echo json_encode(lang('cash_receipt_no_members_found')); ?>,
                noSuppliers: <?php echo json_encode(lang('cash_receipt_no_suppliers_found')); ?>,
                noCustomers: <?php echo json_encode(lang('cash_receipt_no_customers_found')); ?>,
                noLoans: <?php echo json_encode(lang('cash_receipt_no_repayable_loans')); ?>,
                loanHint: <?php echo json_encode(lang('cash_receipt_loan_list_hint')); ?>,
                dueTitle: <?php echo json_encode(lang('loan_repay_due_title')); ?>,
                statusDue: <?php echo json_encode(lang('loan_repay_status_due')); ?>,
                statusOverdue: <?php echo json_encode(lang('loan_repay_status_overdue')); ?>,
                nothingDue: <?php echo json_encode(lang('loan_repay_nothing_due')); ?>,
                dueExplanation: <?php echo json_encode(lang('loan_repay_due_explanation')); ?>,
                minApply: <?php echo json_encode(lang('loan_repay_amount_insufficient')); ?>
            };
            var loanDueState = { suggested: 0, minimum: 0 };

            function clearPayerSelection(keepMiscText) {
                $('#received_from').val('');
                $('#member_pid').val('');
                $('#member_id').val('');
                $('#loan_repayment_lid').val('');
                selectedMemberName = '';
                loanCache = {};
                $('#receivedFromSelected').hide().text('');
                $('#receivedFromSearchResults').html('');
                $('#receivedFromSearchKey').val('');
                if (!keepMiscText) {
                    $('#received_from_name').val('');
                }
                renderLinkedLoan(null);
                if (linesFromLoan) {
                    resetLineItems();
                    linesFromLoan = false;
                }
            }

            function setSelectedPayerLabel(label) {
                if (!label) {
                    $('#receivedFromSelected').hide().text('');
                    return;
                }
                $('#receivedFromSelected').text(i18n.selected + ': ' + label).show();
            }

            function syncMiscReceivedFrom() {
                $('#received_from').val($.trim($('#received_from_name').val() || ''));
            }

            function updateReceivedFromUI(resetSelection) {
                var type = $('#received_from_type').val();
                if (resetSelection) {
                    clearPayerSelection(type === 'miscellaneous');
                }
                if (!type) {
                    $('#receivedFromDetail').hide();
                    $('#receivedFromSearchWrap').hide();
                    $('#receivedFromMiscWrap').hide();
                    renderLinkedLoan(null);
                    return;
                }
                $('#receivedFromDetail').show();
                if (type === 'miscellaneous') {
                    $('#receivedFromSearchWrap').hide();
                    $('#receivedFromMiscWrap').show();
                    if (!$('#received_from_name').val() && $('#received_from').val()) {
                        $('#received_from_name').val($('#received_from').val());
                    }
                    syncMiscReceivedFrom();
                    renderLinkedLoan(null);
                    return;
                }
                $('#receivedFromMiscWrap').hide();
                $('#receivedFromSearchWrap').show();
                var label = i18n.searchMember;
                var placeholder = i18n.phMember;
                if (type === 'loan_repayment') {
                    label = i18n.searchLoan;
                } else if (type === 'supplier') {
                    label = i18n.searchSupplier;
                    placeholder = i18n.phSupplier;
                } else if (type === 'customer') {
                    label = i18n.searchCustomer;
                    placeholder = i18n.phCustomer;
                }
                $('#receivedFromSearchLabel').text(label);
                $('#receivedFromSearchKey').attr('placeholder', placeholder);
                if ($('#received_from').val()) {
                    setSelectedPayerLabel($('#received_from').val());
                }
            }

            $('#received_from_type').on('change', function(){
                updateReceivedFromUI(true);
            });
            $('#received_from_name').on('keyup change', syncMiscReceivedFrom);

            $('#doReceivedFromSearch').on('click', function(){
                var key = $.trim($('#receivedFromSearchKey').val() || '');
                if (key.length < 2) {
                    alert(i18n.minChars);
                    return;
                }
                runReceivedFromSearch(key);
            });
            $('#receivedFromSearchKey').on('keypress', function(e){
                if (e.which === 13) { e.preventDefault(); $('#doReceivedFromSearch').click(); }
            });

            function runReceivedFromSearch(key) {
                var type = $('#received_from_type').val();
                var url = memberSearchUrl;
                if (type === 'supplier') url = supplierSearchUrl;
                else if (type === 'customer') url = customerSearchUrl;
                else if (type === 'member' || type === 'loan_repayment') url = memberSearchUrl;
                else return;

                $('#receivedFromSearchResults').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</p>');
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: { key: key || '' },
                    dataType: 'json',
                    success: function(response){
                        if (response.success === 'Y' && response.data && response.data.length > 0) {
                            if (type === 'supplier') {
                                renderSupplierSearchResults(response.data);
                            } else if (type === 'customer') {
                                renderCustomerSearchResults(response.data);
                            } else {
                                renderMemberSearchResults(response.data, type === 'loan_repayment');
                            }
                        } else {
                            var err = response.error || i18n.noMembers;
                            if (type === 'supplier') err = response.error || i18n.noSuppliers;
                            if (type === 'customer') err = response.error || i18n.noCustomers;
                            $('#receivedFromSearchResults').html('<p class="text-danger text-center">' + err + '</p>');
                        }
                    },
                    error: function(){
                        $('#receivedFromSearchResults').html('<p class="text-danger text-center">Error searching. Please try again.</p>');
                    }
                });
            }

            function renderMemberSearchResults(rows, forLoan) {
                var html = '<table class="table table-bordered table-hover"><thead><tr><th>Member ID</th><th>PID</th><th>Full Name</th><th></th></tr></thead><tbody>';
                $.each(rows, function(i, member){
                    var btnClass = forLoan ? 'select-loan-member' : 'select-payer-member';
                    html += '<tr><td>' + (member.member_id || '') + '</td><td>' + (member.PID || '') + '</td><td>' + (member.fullname || '') + '</td>';
                    html += '<td><button type="button" class="btn btn-sm btn-primary ' + btnClass + '" data-pid="' + (member.PID || '') + '" data-member-id="' + (member.member_id || '') + '" data-fullname="' + String(member.fullname || '').replace(/"/g, '&quot;') + '"><i class="fa fa-check"></i> Select</button></td></tr>';
                });
                html += '</tbody></table>';
                $('#receivedFromSearchResults').html(html);
            }

            function renderSupplierSearchResults(rows) {
                var html = '<table class="table table-bordered table-hover"><thead><tr><th>Supplier No</th><th>Name</th><th></th></tr></thead><tbody>';
                $.each(rows, function(i, row){
                    html += '<tr><td>' + (row.supplierid || '') + '</td><td>' + (row.name || '') + '</td>';
                    html += '<td><button type="button" class="btn btn-sm btn-primary select-payer-name" data-kind="supplier" data-fullname="' + String(row.name || '').replace(/"/g, '&quot;') + '"><i class="fa fa-check"></i> Select</button></td></tr>';
                });
                html += '</tbody></table>';
                $('#receivedFromSearchResults').html(html);
            }

            function renderCustomerSearchResults(rows) {
                var html = '<table class="table table-bordered table-hover"><thead><tr><th>Customer No</th><th>Name</th><th></th></tr></thead><tbody>';
                $.each(rows, function(i, row){
                    html += '<tr><td>' + (row.customerid || '') + '</td><td>' + (row.name || '') + '</td>';
                    html += '<td><button type="button" class="btn btn-sm btn-primary select-payer-name" data-kind="customer" data-fullname="' + String(row.name || '').replace(/"/g, '&quot;') + '"><i class="fa fa-check"></i> Select</button></td></tr>';
                });
                html += '</tbody></table>';
                $('#receivedFromSearchResults').html(html);
            }

            function loadMemberLoans(pid) {
                $('#receivedFromSearchResults').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading loans...</p>');
                $.ajax({
                    url: memberLoansUrl,
                    type: 'GET',
                    data: { pid: pid },
                    dataType: 'json',
                    success: function(response){
                        if (response.success === 'Y' && response.data && response.data.length > 0) {
                            renderLoanSearchResults(response.data);
                        } else {
                            $('#receivedFromSearchResults').html('<p class="text-danger text-center">' + (response.error || i18n.noLoans) + '</p>');
                        }
                    },
                    error: function(){
                        $('#receivedFromSearchResults').html('<p class="text-danger text-center">Error loading loans. Please try again.</p>');
                    }
                });
            }

            function renderLoanSearchResults(rows) {
                var html = '<p class="help-block" style="margin-top:0;">' + i18n.loanHint + '</p>';
                html += '<table class="table table-bordered table-hover"><thead><tr><th>Loan</th><th>Product</th><th>Status</th><th>Outstanding</th><th>Amount Due</th><th></th></tr></thead><tbody>';
                $.each(rows || [], function(i, row){
                    loanCache[row.LID] = row;
                    var overdue = parseInt(row.has_overdue, 10) === 1 ? ' <span class="label label-danger">overdue</span>' : '';
                    html += '<tr><td>' + (row.LID || '') + '</td><td>' + (row.product_name || '') + '</td><td>' + (row.status_name || '') + overdue + '</td>';
                    html += '<td>' + (parseFloat(row.outstanding || 0).toFixed(2)) + '</td><td>' + (parseFloat(row.amount_due || 0).toFixed(2)) + '</td>';
                    html += '<td><button type="button" class="btn btn-sm btn-primary select-member-loan" data-loan-id="' + (row.LID || '') + '"><i class="fa fa-check"></i> Select</button></td></tr>';
                });
                html += '</tbody></table>';
                $('#receivedFromSearchResults').html(html);
            }

            $(document).on('click', '.select-payer-member', function(){
                var name = $(this).data('fullname') || '';
                $('#received_from').val(name);
                $('#member_pid').val($(this).data('pid') || '');
                $('#member_id').val($(this).data('member-id') || '');
                $('#loan_repayment_lid').val('');
                renderLinkedLoan(null);
                setSelectedPayerLabel(name);
                $('#receivedFromSearchResults').html('');
            });

            $(document).on('click', '.select-loan-member', function(){
                var name = $(this).data('fullname') || '';
                var pid = $(this).data('pid') || '';
                selectedMemberName = name;
                $('#member_pid').val(pid);
                $('#member_id').val($(this).data('member-id') || '');
                $('#received_from').val('');
                $('#loan_repayment_lid').val('');
                renderLinkedLoan(null);
                setSelectedPayerLabel(name);
                loadMemberLoans(pid);
            });

            $(document).on('click', '.select-payer-name', function(){
                var name = $(this).data('fullname') || '';
                var kind = $(this).data('kind') || '';
                $('#received_from').val(name);
                $('#member_pid').val('');
                $('#member_id').val('');
                $('#loan_repayment_lid').val('');
                renderLinkedLoan(null);
                setSelectedPayerLabel(name);
                $('#receivedFromSearchResults').html('');
                if (kind === 'customer') {
                    addLinkedAccountLine(arAccountUrl, 'AR - ' + name);
                } else if (kind === 'supplier') {
                    addLinkedAccountLine(apAccountUrl, 'AP - ' + name);
                }
            });

            $(document).on('click', '.select-member-loan', function(){
                var loan = loanCache[$(this).data('loanId')];
                if (!loan) return;
                $('#loan_repayment_lid').val(loan.LID || '');
                $('#loan_repayment_amount').val('');
                $('#received_from').val(selectedMemberName || $('#received_from').val());
                if (loan.PID) $('#member_pid').val(loan.PID);
                if (loan.member_id) $('#member_id').val(loan.member_id);
                setSelectedPayerLabel(($('#received_from').val() || '') + ' — Loan ' + (loan.LID || ''));
                $('#receivedFromSearchResults').html('');
                if (!$('textarea[name="description"]').val()) {
                    $('textarea[name="description"]').val('Loan Repayment ' + (loan.LID || ''));
                }
                renderLinkedLoan(loan);
                loadLoanWorksheet(loan.LID);
            });

            function formatMoney(n) {
                var v = parseFloat(n);
                if (isNaN(v)) v = 0;
                return v.toFixed(2);
            }

            function renderDuePanel(due, extra) {
                extra = extra || {};
                if (!due) {
                    $('#loanRepaymentDueWrap').hide();
                    return;
                }
                var grace = due.grace_days || 0;
                var pct = due.penalt_percentage || 0;
                $('#loanRepaymentDueExplanation').text(
                    (i18n.dueExplanation || '').replace('%s', grace).replace('%s', pct)
                );
                var items = due.items || [];
                var body = '';
                if (!items.length) {
                    body = '<tr><td colspan="7" class="text-muted">' + i18n.nothingDue + '</td></tr>';
                } else {
                    $.each(items, function(i, item){
                        var overdue = item.status === 'overdue';
                        body += '<tr class="' + (overdue ? 'warning' : '') + '">';
                        body += '<td>' + (item.installment || '') + '</td>';
                        body += '<td>' + (item.due_date || '') + '</td>';
                        body += '<td>' + (overdue ? i18n.statusOverdue : i18n.statusDue) + '</td>';
                        body += '<td class="text-right">' + formatMoney(item.installment_amount) + '</td>';
                        body += '<td class="text-right">' + formatMoney(item.penalty) + '</td>';
                        body += '<td class="text-right">' + (item.penalty_months || 0) + '</td>';
                        body += '<td class="text-right"><strong>' + formatMoney(item.total) + '</strong></td>';
                        body += '</tr>';
                    });
                }
                $('#loanRepaymentDueBody').html(body);
                $('#crDueTotalInstallments').text(formatMoney(due.total_installments));
                $('#crDueTotalPenalty').text(formatMoney(due.total_penalty));
                $('#crDueTotalDue').text(formatMoney(due.total_due));
                $('#crDueCarry').text(formatMoney(due.carry_balance));
                $('#crDueNetDue').text(formatMoney(due.suggested_amount || due.net_due));
                loanDueState.suggested = parseFloat(extra.suggested_amount != null ? extra.suggested_amount : due.suggested_amount) || 0;
                loanDueState.minimum = parseFloat(extra.minimum_to_apply != null ? extra.minimum_to_apply : due.minimum_to_apply) || 0;
                if (loanDueState.minimum > 0) {
                    $('#crDueMinHint').text('Min: ' + formatMoney(loanDueState.minimum));
                } else {
                    $('#crDueMinHint').text('');
                }
                var amt = parseFloat($('#loan_repayment_amount').val() || 0);
                if (!amt && loanDueState.suggested > 0) {
                    $('#loan_repayment_amount').val(formatMoney(loanDueState.suggested));
                } else if (extra.payment_amount != null && extra.payment_amount !== '') {
                    var pay = parseFloat(extra.payment_amount);
                    if (!isNaN(pay) && Math.abs(pay - amt) > 0.009) {
                        $('#loan_repayment_amount').val(formatMoney(pay));
                    }
                }
                if (extra.preview_ok === false && extra.preview_message) {
                    $('#loanRepaymentPreviewMsg').text(extra.preview_message).show();
                } else {
                    $('#loanRepaymentPreviewMsg').hide().text('');
                }
                $('#loanRepaymentDueWrap').show();
            }

            function renderLinkedLoan(loan){
                if (!loan) {
                    $('#loanRepaymentPanel').hide();
                    $('#loanRepaymentPanelBody').html('');
                    $('#loanRepaymentDueWrap').hide();
                    return;
                }
                var html = '<div class="loan-repayment-meta">';
                html += '<div><strong>Loan #:</strong> ' + (loan.LID || '') + '</div>';
                html += '<div><strong>Member:</strong> ' + ($('#received_from').val() || '') + '</div>';
                html += '<div><strong>Product:</strong> ' + (loan.product_name || '') + '</div>';
                html += '<div><strong>Status:</strong> ' + (loan.status_name || '') + '</div>';
                html += '<div><strong>Outstanding:</strong> ' + formatMoney(loan.outstanding) + '</div>';
                html += '<div><strong>Amount Due:</strong> ' + formatMoney(loan.amount_due || loan.suggested_amount) + '</div>';
                html += '</div>';
                $('#loanRepaymentPanelBody').html(html);
                $('#loanRepaymentPanel').show();
                if (loan.due) {
                    renderDuePanel(loan.due, loan);
                }
            }

            function addLinkedAccountLine(url, desc) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response){
                        if (response.success === 'Y' && response.account) {
                            var exists = false;
                            $('.account-select').each(function(){
                                if ($(this).val() === response.account) {
                                    exists = true;
                                    return false;
                                }
                            });
                            if (!exists) {
                                addLineRow(response.account, '', '', desc);
                            }
                        }
                    }
                });
            }

            function loadLoanWorksheet(lid) {
                if (!lid) return;
                $.ajax({
                    url: loanWorksheetUrl,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        lid: lid,
                        payment_method: $('#payment_method').val() || '',
                        receipt_date: $('input[name="receipt_date"]').val() || '',
                        amount: $('#loan_repayment_amount').val() || ''
                    },
                    success: function(response){
                        if (response.success === 'Y' && response.data) {
                            if (response.data.line_items && response.data.line_items.length) {
                                populateLoanLines(response.data.line_items);
                                linesFromLoan = true;
                            }
                            renderLinkedLoan(response.data);
                        }
                    }
                });
            }

            function syncPaymentMethodAccount() {
                if ($('#received_from_type').val() !== 'loan_repayment') {
                    return;
                }
                var pmId = $('#payment_method').val();
                if (!pmId) {
                    return;
                }
                $.ajax({
                    url: paymentMethodGlUrl,
                    type: 'GET',
                    dataType: 'json',
                    data: { id: pmId },
                    success: function(response){
                        if (response.success !== 'Y' || !response.account) {
                            return;
                        }
                        var $cash = $('#lineItemsTable tbody tr.line-item[data-line-role="cash"]');
                        if ($cash.length) {
                            setRowAccount($cash, response.account);
                            return;
                        }
                        if ($('#loan_repayment_lid').val()) {
                            var $first = $('#lineItemsTable tbody tr.line-item').first();
                            var firstEmpty = $first.length && !$first.find('.account-select').val()
                                && !parseFloat($first.find('.debit-input').val() || 0)
                                && !parseFloat($first.find('.credit-input').val() || 0);
                            if (firstEmpty) {
                                $first.attr('data-line-role', 'cash');
                                $first.find('input[name="line_description[]"]').val('Loan Repayment ' + $('#loan_repayment_lid').val());
                                setRowAccount($first, response.account);
                            } else {
                                addLineRow(response.account, '', '', 'Loan Repayment ' + $('#loan_repayment_lid').val(), 'cash');
                                var $added = $('#lineItemsTable tbody tr.line-item').last();
                                $('#lineItemsTable tbody').prepend($added);
                            }
                            linesFromLoan = true;
                        }
                    }
                });
            }

            $('#btnUseSuggestedLoanAmount').on('click', function(){
                if (loanDueState.suggested > 0) {
                    $('#loan_repayment_amount').val(formatMoney(loanDueState.suggested));
                    if ($('#loan_repayment_lid').val()) {
                        loadLoanWorksheet($('#loan_repayment_lid').val());
                    }
                }
            });
            $('#loan_repayment_amount').on('change', function(){
                if ($('#loan_repayment_lid').val()) {
                    loadLoanWorksheet($('#loan_repayment_lid').val());
                }
            });
            $('input[name="receipt_date"]').on('change', function(){
                if ($('#received_from_type').val() === 'loan_repayment' && $('#loan_repayment_lid').val()) {
                    loadLoanWorksheet($('#loan_repayment_lid').val());
                }
            });

            updateReceivedFromUI(false);
            if ($('#received_from_type').val() === 'loan_repayment' && $('#loan_repayment_lid').val()) {
                selectedMemberName = $('#received_from').val() || '';
                loadLoanWorksheet($('#loan_repayment_lid').val());
                setSelectedPayerLabel(($('#received_from').val() || '') + ' — Loan ' + $('#loan_repayment_lid').val());
            }
        }
        $(document).ready(boot);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOnceReady);
    } else {
        initOnceReady();
    }
})();
</script>
