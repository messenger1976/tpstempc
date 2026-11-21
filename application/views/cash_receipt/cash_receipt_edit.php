<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">
<style>
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
.select2-container--default .select2-results__option[aria-disabled=true]{color:#222;cursor:default;}
.select2-container--default .select2-results__option .coa-bold{font-weight:bold;color:#111;}
.select2-container{width:100%!important;}
.loan-repayment-box {
    display: none;
    background: #f0faf7;
    border: 1px solid #c9ebe3;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 12px;
    font-size: 13px;
    color: #2f4050;
}
.loan-repayment-box strong { color: #0e7c69; }
.loan-repayment-meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px 16px;
    margin-top: 8px;
}
.loan-repayment-terms {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 6px 16px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed #c9ebe3;
}
.loan-repayment-terms .terms-head {
    grid-column: 1 / -1;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: #0e7c69;
}
@media (max-width: 991px) {
    .loan-repayment-terms { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767px) {
    .loan-repayment-meta { grid-template-columns: 1fr; }
    .loan-repayment-terms { grid-template-columns: 1fr; }
}
.received-from-detail {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 12px 14px;
    margin-top: 4px;
}
.received-from-selected {
    margin-top: 8px;
    font-size: 13px;
    color: #0e7c69;
    font-weight: 600;
}
.received-from-results {
    margin-top: 10px;
    max-height: 260px;
    overflow-y: auto;
}
.received-from-results .table { margin-bottom: 0; background: #fff; }
.payer-suggest-wrap { position: relative; }
.payer-suggest-box {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    margin-top: 4px;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    max-height: 300px;
    overflow-y: auto;
    z-index: 10050;
}
.payer-suggest-box.open { display: block; }
.payer-suggest-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 12px;
    border: 0;
    border-bottom: 1px solid #f0f2f3;
    background: #fff;
    text-align: left;
    cursor: pointer;
}
.payer-suggest-item:last-child { border-bottom: 0; }
.payer-suggest-item:hover,
.payer-suggest-item.active { background: #e8f8f5; }
.payer-suggest-item .suggest-id {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 11px;
    white-space: nowrap;
}
.payer-suggest-item .suggest-name {
    flex: 1;
    font-weight: 600;
    color: #2f4050;
    font-size: 13px;
}
.payer-suggest-item .suggest-sub {
    color: #999;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.payer-suggest-empty {
    padding: 12px;
    color: #999;
    font-size: 12px;
}
</style>

<?php
$selected_loan_repayment_lid = isset($selected_loan_repayment_lid) ? $selected_loan_repayment_lid : set_value('loan_repayment_lid');
$selected_loan_allocations = isset($selected_loan_allocations) && is_array($selected_loan_allocations) ? $selected_loan_allocations : array();
$selected_member_pid = isset($selected_member_pid) ? $selected_member_pid : set_value('member_pid');
$selected_received_from_type = isset($selected_received_from_type) ? $selected_received_from_type : set_value('received_from_type', 'miscellaneous');
$received_from_value = set_value('received_from', isset($receipt->received_from) ? $receipt->received_from : '');
$received_from_types = array(
    'loan_repayment' => lang('cash_receipt_from_loan_repayment'),
    'member' => lang('cash_receipt_from_member'),
    'supplier' => lang('cash_receipt_from_supplier'),
    'customer' => lang('cash_receipt_from_customer'),
    'miscellaneous' => lang('cash_receipt_from_miscellaneous'),
);
?>

<?php echo form_open_multipart(current_lang() . "/cash_receipt/cash_receipt_edit/" . $id, 'class="form-horizontal" id="cashReceiptForm"'); ?>

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

<input type="hidden" name="id" value="<?php echo $receipt->id; ?>"/>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo lang('cash_receipt_edit'); ?> - <?php echo $receipt->receipt_no; ?></h5>
                    <div class="ibox-tools">
                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_list'); ?>" class="btn btn-white btn-xs">
                            <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    
                    <!-- Receipt Header Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_no'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" name="receipt_no" value="<?php echo set_value('receipt_no', $receipt->receipt_no); ?>" class="form-control" required/>
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
                                                 value="<?php echo set_value('receipt_date', date('d-m-Y', strtotime($receipt->receipt_date))); ?>" 
                                                 data-date-format="dd-mm-yyyy" class="form-control" required/> 
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar"></span>
                                        </span>
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
                                            <div class="payer-suggest-wrap">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="receivedFromSearchKey" autocomplete="off" placeholder=""/>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-primary" id="doReceivedFromSearch">
                                                            <i class="fa fa-search"></i> <?php echo lang('search'); ?>
                                                        </button>
                                                    </span>
                                                </div>
                                                <div id="receivedFromSuggestBox" class="payer-suggest-box" role="listbox"></div>
                                            </div>
                                            <div class="received-from-selected" id="receivedFromSelected" style="display:none;"></div>
                                            <div class="received-from-results" id="receivedFromSearchResults"></div>
                                        </div>
                                        <div id="receivedFromMiscWrap" style="display:none;">
                                            <label class="control-label" style="padding-top:0;margin-bottom:6px;display:block;"><?php echo lang('cash_receipt_received_from_name'); ?> : <span class="required">*</span></label>
                                            <input type="text" id="received_from_name" class="form-control" value="<?php echo htmlspecialchars($received_from_value, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                                        </div>
                                    </div>
                                    <input type="hidden" name="received_from" id="received_from" value="<?php echo htmlspecialchars($received_from_value, ENT_QUOTES, 'UTF-8'); ?>"/>
                                    <input type="hidden" name="member_pid" id="member_pid" value="<?php echo htmlspecialchars(isset($selected_member_pid) ? $selected_member_pid : set_value('member_pid'), ENT_QUOTES, 'UTF-8'); ?>"/>
                                    <input type="hidden" name="member_id" id="member_id" value=""/>
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
                                        $selected_payment_id = set_value('payment_method');
                                        // Handle saved payment_method: if it's numeric (ID), use it directly; if it's a name, lookup the ID
                                        if (empty($selected_payment_id) && !empty($receipt->payment_method)) {
                                            if (is_numeric($receipt->payment_method)) {
                                                // Saved value is an ID
                                                $selected_payment_id = (int)$receipt->payment_method;
                                            } else {
                                                // Saved value is a name, lookup the ID
                                                $saved_method_lower = strtolower(trim($receipt->payment_method));
                                                if (isset($payment_method_id_by_name[$saved_method_lower])) {
                                                    $selected_payment_id = $payment_method_id_by_name[$saved_method_lower];
                                                }
                                            }
                                        }
                                        foreach ($payment_methods as $id => $name): 
                                            $is_selected = (!empty($selected_payment_id) && $id == $selected_payment_id);
                                        ?>
                                            <option value="<?php echo $id; ?>" <?php echo $is_selected ? 'selected="selected"' : ''; ?>><?php echo $name; ?></option>
                                        <?php endforeach; ?>
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
                                <p class="help-block" style="margin:6px 0 0;"><?php echo lang('cash_receipt_loan_multi_hint'); ?></p>
                                <div id="loanRepaymentPanelBody" style="margin-top:8px;"></div>
                                <div id="loanAllocHiddenInputs"></div>
                                <div id="loanRepaymentDueWrap" style="display:none;margin-top:12px;">
                                    <p class="help-block" id="loanRepaymentDueExplanation" style="margin-bottom:8px;"></p>
                                    <div class="table-responsive">
                                        <table class="table table-condensed" id="loanRepaymentDueTable" style="margin-bottom:8px;background:#fff;">
                                            <thead>
                                                <tr>
                                                    <th><?php echo lang('loan_installment'); ?></th>
                                                    <th><?php echo lang('due_date'); ?></th>
                                                    <th><?php echo lang('index_status_th'); ?></th>
                                                    <th class="text-right"><?php echo lang('loan_repay_principal_amount'); ?></th>
                                                    <th class="text-right"><?php echo lang('loan_ledger_interest'); ?></th>
                                                    <th class="text-right"><?php echo lang('loan_ledger_penalty'); ?></th>
                                                    <th class="text-right"><?php echo lang('loan_repay_penalty_months'); ?></th>
                                                    <th class="text-right"><?php echo lang('total'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="loanRepaymentDueBody"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3" class="text-right"><?php echo lang('loan_repay_total_due'); ?></th>
                                                    <th class="text-right" id="crDueTotalPrinciple">0.00</th>
                                                    <th class="text-right" id="crDueTotalInterest">0.00</th>
                                                    <th class="text-right" id="crDueTotalPenalty">0.00</th>
                                                    <th></th>
                                                    <th class="text-right" id="crDueTotalDue">0.00</th>
                                                </tr>
                                                <tr>
                                                    <td colspan="7" class="text-right"><?php echo lang('loan_repay_carry_balance'); ?></td>
                                                    <td class="text-right" id="crDueCarry">0.00</td>
                                                </tr>
                                                <tr>
                                                    <th colspan="7" class="text-right"><?php echo lang('loan_repay_net_due'); ?> / <?php echo lang('loan_repay_suggested'); ?></th>
                                                    <th class="text-right" id="crDueNetDue">0.00</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div style="margin-top:8px;">
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

                    <div class="row" id="cheque_details" style="display: <?php echo ($receipt->payment_method == 'Cheque') ? 'block' : 'none'; ?>;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_cheque_no'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="cheque_no" value="<?php echo set_value('cheque_no', $receipt->cheque_no); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_bank_name'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="bank_name" value="<?php echo set_value('bank_name', $receipt->bank_name); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="col-lg-2 control-label"><?php echo lang('cash_receipt_description'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-10">
                                    <textarea name="description" class="form-control" rows="3" required><?php echo set_value('description', $receipt->description); ?></textarea>
                                    <?php echo form_error('description'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="cancelled" id="cancelled" value="1" <?php echo set_checkbox('cancelled', '1', !empty($receipt->cancelled)); ?>/>
                                            <?php echo lang('cancelled'); ?>
                                        </label>
                                    </div>
                                    <p class="help-block text-muted" style="margin-left: 0;"><?php echo lang('cash_receipt_cancelled_help'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <!-- Line Items Table (journal-entry style: Account | Description | Debit | Credit) -->
                    <h4><?php echo lang('cash_receipt_line_items'); ?></h4>
                    <div class="table-responsive">
                        <table id="lineItemsTable" class="table table-bordered">
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
                                <?php 
                                $edit_total_debit = 0; $edit_total_credit = 0;
                                foreach ($line_items as $index => $item): 
                                    $item_debit = isset($item->debit) ? floatval($item->debit) : 0;
                                    $item_credit = isset($item->credit) ? floatval($item->credit) : (isset($item->amount) ? floatval($item->amount) : 0);
                                    $edit_total_debit += $item_debit;
                                    $edit_total_credit += $item_credit;
                                ?>
                                <tr class="line-item">
                                    <td>
                                        <select class="form-control account-select" name="account[]">
                                            <option value=""><?php echo lang('select_default_text'); ?></option>
                                            <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => $item->account)); ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="line_description[]" class="form-control" value="<?php echo htmlspecialchars($item->description); ?>"/>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input" value="<?php echo $item_debit; ?>" placeholder="0.00"/>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input" value="<?php echo $item_credit; ?>" placeholder="0.00"/>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-xs remove-line" <?php echo (count($line_items) <= 1) ? 'disabled' : ''; ?> title="<?php echo lang('delete'); ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                                    <td>
                                        <input type="text" id="total_debit" class="form-control" readonly value="<?php echo number_format($edit_total_debit, 2); ?>"/>
                                    </td>
                                    <td>
                                        <input type="text" id="total_credit" class="form-control" readonly value="<?php echo number_format($edit_total_credit, 2); ?>"/>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="2" id="balance_diff" class="text-right" style="color: red; font-weight: bold;"></td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="button" class="btn btn-primary" id="addLineItem">
                            <i class="fa fa-plus"></i> <?php echo lang('add_row'); ?>
                        </button>
                    </div>

                    <hr/>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo lang('update'); ?>
                            </button>
                            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . $id); ?>" class="btn btn-white">
                                <?php echo lang('cancel'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script src="<?php echo base_url(); ?>media/js/cash_receipt_payer_suggest.js?v=20261002"></script>
<script>
(function(){
    function loadScript(src, cb, fallback){ var s=document.createElement('script'); s.src=src; s.onload=cb; if(fallback){ s.onerror=function(){ loadScript(fallback, cb); }; } document.head.appendChild(s); }
    function initOnceReady(){
        if(!window.jQuery){ setTimeout(initOnceReady, 50); return; }
        var $=window.jQuery;
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
                    templateResult: formatCoaOption,
                    templateSelection: function(data) { return data.text; }
                });
            }

            function destroyAccountSelect($el) {
                if ($el && $el.length && $el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
            }

            function cloneLineItem(prefillAccount, prefillDescription) {
                var $first = $('.line-item:first');
                destroyAccountSelect($first.find('.account-select'));
                var newRow = $first.clone();
                newRow.find('.select2-container').remove();
                newRow.find('input').val('');
                newRow.find('select').val('');
                if (prefillAccount) {
                    newRow.find('.account-select').val(prefillAccount);
                }
                if (prefillDescription) {
                    newRow.find('input[name="line_description[]"]').val(prefillDescription);
                }
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

                if(!($.fn.datepicker && $.fn.datepicker.DPGlobal)){
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
                    todayBtn:'linked', keyboardNavigation:false, forceParse:false,
                    calendarWeeks:true, autoclose:true, format:'dd-mm-yyyy',
                    orientation:'bottom auto', todayHighlight:true, container:'body'
                });
            }

            ensureBootstrapDP(initPicker);

            updateRemoveButtons();
            $('.account-select').each(function(){ initAccountSelect($(this)); });

            $('#payment_method').on('change', function(){
                var $opt = $('#payment_method option:selected');
                var txt = ($opt.text() || '').toLowerCase();
                if ($opt.data('is-cheque') == 1 || $opt.data('is-cheque') === '1' || txt.indexOf('cheque') >= 0 || txt.indexOf('check') >= 0) {
                    $('#cheque_details').show();
                } else {
                    $('#cheque_details').hide();
                }
                if ($('#received_from_type').val() === 'loan_repayment' && getSelectedLoanAllocations().length) {
                    rebuildMergedLoanWorksheets();
                } else {
                    syncPaymentMethodAccount();
                }
            });

            $('#addLineItem').on('click', function(){
                cloneLineItem();
                updateRemoveButtons();
                calculateTotals();
            });

            $(document).on('click', '.remove-line', function(){
                if($('.line-item').length>1){
                    var $row=$(this).closest('tr');
                    destroyAccountSelect($row.find('.account-select'));
                    $row.remove();
                    updateRemoveButtons();
                    calculateTotals();
                }
            });

            function updateRemoveButtons(){
                var count=$('.line-item').length;
                $('.remove-line').prop('disabled', count<=1);
            }

            $(document).on('keyup change', '.debit-input, .credit-input', function(){ calculateTotals(); });

            function calculateTotals(){
                var totalDebit=0, totalCredit=0;
                $('.debit-input').each(function(){ totalDebit += parseFloat($(this).val()) || 0; });
                $('.credit-input').each(function(){ totalCredit += parseFloat($(this).val()) || 0; });
                $('#total_debit').val(totalDebit.toFixed(2));
                $('#total_credit').val(totalCredit.toFixed(2));
                var diff=totalDebit-totalCredit;
                if(Math.abs(diff)<0.01){ $('#balance_diff').text('').css('color','green'); }
                else{ $('#balance_diff').text('Diff: '+diff.toFixed(2)).css('color','red'); }
            }

            // Form validation - debits must equal credits (skip when cancelled)
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
                if (type === 'loan_repayment') {
                    syncLoanAllocationHiddenInputs();
                    var selectedAllocs = getSelectedLoanAllocations();
                    if (!selectedAllocs.length) {
                        alert(i18n.loanRequired);
                        e.preventDefault();
                        return false;
                    }
                }
                if ($('#cancelled').is(':checked')) {
                    return true; // No line items validation when cancelled
                }
                var totalDebit=0, totalCredit=0, hasItems=false;
                $('.debit-input').each(function(){ totalDebit += parseFloat($(this).val()) || 0; });
                $('.credit-input').each(function(){ var v=parseFloat($(this).val())||0; totalCredit+=v; if(v>0)hasItems=true; });
                if(!hasItems){ alert('<?php echo lang('cash_receipt_no_items'); ?>'); e.preventDefault(); return false; }
                if(Math.abs(totalDebit-totalCredit)>0.01){ alert('<?php echo lang('debits_credits_not_balanced'); ?>'); e.preventDefault(); return false; }
                return true;
            });

            // Toggle line items required indicator when cancelled changes
            $('#cancelled').on('change', function(){
                if ($(this).is(':checked')) {
                    $('#lineItemsTable th .required').hide();
                } else {
                    $('#lineItemsTable th .required').show();
                }
            });
            
            // Initialize on page load
            if ($('#cancelled').is(':checked')) {
                $('#lineItemsTable th .required').hide();
            }

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
            var focusedLoanId = '';
            var loanWorksheetSeq = 0;
            var initialLoanAllocations = <?php echo json_encode(array_values($selected_loan_allocations)); ?>;
            var receiptExcludeId = <?php echo isset($receipt->id) ? (int) $receipt->id : 0; ?>;

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
                selectedTotal: <?php echo json_encode(lang('cash_receipt_loan_selected_total')); ?>,
                paymentAmount: <?php echo json_encode(lang('cash_receipt_loan_payment_amount')); ?>,
                overrideToggle: <?php echo json_encode(lang('cash_receipt_loan_override_toggle')); ?>,
                overrideHelp: <?php echo json_encode(lang('cash_receipt_loan_override_help')); ?>,
                overrideNote: <?php echo json_encode(lang('cash_receipt_loan_override_note')); ?>,
                overridePrincipal: <?php echo json_encode(lang('cash_receipt_loan_override_principal')); ?>,
                overrideInterest: <?php echo json_encode(lang('cash_receipt_loan_override_interest')); ?>,
                overridePenalty: <?php echo json_encode(lang('cash_receipt_loan_override_penalty')); ?>,
                dueTitle: <?php echo json_encode(lang('loan_repay_due_title')); ?>,
                statusDue: <?php echo json_encode(lang('loan_repay_status_due')); ?>,
                statusOverdue: <?php echo json_encode(lang('loan_repay_status_overdue')); ?>,
                nothingDue: <?php echo json_encode(lang('loan_repay_nothing_due')); ?>,
                dueExplanation: <?php echo json_encode(lang('loan_repay_due_explanation')); ?>,
                minApply: <?php echo json_encode(lang('loan_repay_amount_insufficient')); ?>,
                loanTerms: <?php echo json_encode(lang('cash_receipt_loan_terms')); ?>,
                loanTerm: <?php echo json_encode(lang('cash_receipt_loan_term')); ?>,
                loanAmount: <?php echo json_encode(lang('cash_receipt_loan_amount')); ?>,
                loanInterestRate: <?php echo json_encode(lang('cash_receipt_loan_interest_rate')); ?>,
                loanMonthly: <?php echo json_encode(lang('cash_receipt_loan_monthly_installment')); ?>,
                loanFirstInstallment: <?php echo json_encode(lang('cash_receipt_loan_first_installment')); ?>,
                loanTotalObligation: <?php echo json_encode(lang('cash_receipt_loan_total_obligation')); ?>
            };
            var loanDueState = { suggested: 0, minimum: 0 };

            function clearPayerSelection(keepMiscText) {
                $('#received_from').val('');
                $('#member_pid').val('');
                $('#member_id').val('');
                $('#loan_repayment_lid').val('');
                selectedMemberName = '';
                loanCache = {};
                focusedLoanId = '';
                $('#loanRepaymentPanelBody').html('');
                $('#loanAllocHiddenInputs').empty();
                $('#loanRepaymentPanel').hide();
                $('#loanRepaymentDueWrap').hide();
                $('#receivedFromSelected').hide().text('');
                $('#receivedFromSearchResults').html('');
                $('#receivedFromSearchKey').val('');
                if (!keepMiscText) {
                    $('#received_from_name').val('');
                }
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
            if (window.CashReceiptPayerSuggest) {
                CashReceiptPayerSuggest.init({
                    urls: {
                        member: memberSearchUrl,
                        loan_repayment: memberSearchUrl,
                        supplier: supplierSearchUrl,
                        customer: customerSearchUrl
                    },
                    emptyText: {
                        member: i18n.noMembers,
                        loan_repayment: i18n.noMembers,
                        supplier: i18n.noSuppliers,
                        customer: i18n.noCustomers
                    }
                });
            }

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

            function initialAmountForLid(lid) {
                var amt = null;
                $.each(initialLoanAllocations || [], function(_, row){
                    if (String(row.LID) === String(lid)) {
                        amt = parseFloat(row.amount);
                        return false;
                    }
                });
                return amt;
            }

            function initialOverrideForLid(lid) {
                var found = null;
                $.each(initialLoanAllocations || [], function(_, row){
                    if (String(row.LID) === String(lid) && row.override_enabled) {
                        found = {
                            enabled: true,
                            principal: parseFloat(row.override_principal) || 0,
                            interest: parseFloat(row.override_interest) || 0,
                            penalty: parseFloat(row.override_penalty) || 0,
                            note: row.override_note || ''
                        };
                        return false;
                    }
                });
                return found;
            }

            function getRowOverride($row) {
                if (!$row || !$row.length) {
                    return null;
                }
                if (!$row.find('.loan-override-enabled').is(':checked')) {
                    return null;
                }
                return {
                    enabled: true,
                    principal: parseFloat(String($row.find('.loan-override-principal').val() || '').replace(/,/g, '')) || 0,
                    interest: parseFloat(String($row.find('.loan-override-interest').val() || '').replace(/,/g, '')) || 0,
                    penalty: parseFloat(String($row.find('.loan-override-penalty').val() || '').replace(/,/g, '')) || 0,
                    note: $.trim($row.find('.loan-override-note').val() || '')
                };
            }

            function syncOverrideAmountFromComponents($row) {
                var ov = getRowOverride($row);
                if (!ov) {
                    return;
                }
                var sum = roundMoney(ov.principal + ov.interest + ov.penalty);
                $row.find('.loan-alloc-amount').val(formatMoney(sum));
            }

            function roundMoney(n) {
                return Math.round((parseFloat(n) || 0) * 100) / 100;
            }

            function loadMemberLoans(pid, preselect) {
                $('#receivedFromSearchResults').html('');
                $('#loanRepaymentPanelBody').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading loans...</p>');
                $('#loanRepaymentPanel').show();
                $.ajax({
                    url: memberLoansUrl,
                    type: 'GET',
                    data: { pid: pid, exclude_id: receiptExcludeId || 0 },
                    dataType: 'json',
                    success: function(response){
                        if (response.success === 'Y' && response.data && response.data.length > 0) {
                            renderLoanAllocationTable(response.data, preselect || {});
                        } else {
                            $('#loanRepaymentPanelBody').html('<p class="text-danger">' + (response.error || i18n.noLoans) + '</p>');
                            $('#loanRepaymentDueWrap').hide();
                            syncLoanAllocationHiddenInputs();
                        }
                    },
                    error: function(){
                        $('#loanRepaymentPanelBody').html('<p class="text-danger">Error loading loans. Please try again.</p>');
                    }
                });
            }

            function renderLoanAllocationTable(rows, preselect) {
                preselect = preselect || {};
                var html = '<div class="table-responsive"><table class="table table-bordered table-condensed" id="loanAllocTable" style="margin-bottom:8px;background:#fff;">';
                html += '<thead><tr><th style="width:36px;"></th><th>Loan</th><th>Product</th><th>Status</th>';
                html += '<th class="text-right">Outstanding</th><th class="text-right">Amount Due</th>';
                html += '<th class="text-right" style="min-width:120px;">' + i18n.paymentAmount + '</th></tr></thead><tbody>';
                $.each(rows || [], function(i, row){
                    loanCache[row.LID] = row;
                    var lid = row.LID || '';
                    var due = parseFloat(row.amount_due || 0) || 0;
                    var checked = !!preselect[lid] || (initialAmountForLid(lid) != null);
                    var amountVal = preselect[lid] != null ? preselect[lid] : initialAmountForLid(lid);
                    var initOv = initialOverrideForLid(lid);
                    if (initOv) {
                        amountVal = initOv.principal + initOv.interest + initOv.penalty;
                        checked = true;
                    }
                    if (amountVal == null || isNaN(amountVal)) {
                        amountVal = due > 0 ? due : '';
                    } else {
                        amountVal = formatMoney(amountVal);
                    }
                    var overdue = parseInt(row.has_overdue, 10) === 1 ? ' <span class="label label-danger">overdue</span>' : '';
                    html += '<tr class="loan-alloc-row' + (checked ? ' success' : '') + '" data-loan-id="' + lid + '">';
                    html += '<td class="text-center"><input type="checkbox" class="loan-alloc-cb"' + (checked ? ' checked="checked"' : '') + ' /></td>';
                    html += '<td>' + lid + '</td><td>' + (row.product_name || '') + '</td><td>' + (row.status_name || '') + overdue + '</td>';
                    html += '<td class="text-right">' + formatMoney(row.outstanding) + '</td>';
                    html += '<td class="text-right">' + formatMoney(due) + '</td>';
                    html += '<td><input type="number" step="0.01" min="0" class="form-control input-sm text-right loan-alloc-amount" value="' + amountVal + '" ' + (checked ? '' : 'disabled="disabled"') + (initOv ? ' readonly="readonly"' : '') + '/></td>';
                    html += '</tr>';
                    html += '<tr class="loan-override-row" data-loan-id="' + lid + '" style="' + (checked ? '' : 'display:none;') + '">';
                    html += '<td></td><td colspan="6" style="background:#fffdf5;">';
                    html += '<label style="font-weight:600;margin-right:10px;"><input type="checkbox" class="loan-override-enabled"' + (initOv ? ' checked="checked"' : '') + '/> ' + i18n.overrideToggle + '</label>';
                    html += '<span class="text-muted" style="font-size:12px;">' + i18n.overrideHelp + '</span>';
                    html += '<div class="loan-override-fields" style="margin-top:8px;' + (initOv ? '' : 'display:none;') + '">';
                    html += '<div class="row">';
                    html += '<div class="col-sm-3"><label>' + i18n.overridePrincipal + '</label><input type="number" step="0.01" min="0" class="form-control input-sm loan-override-principal" value="' + (initOv ? formatMoney(initOv.principal) : '') + '"/></div>';
                    html += '<div class="col-sm-3"><label>' + i18n.overrideInterest + '</label><input type="number" step="0.01" min="0" class="form-control input-sm loan-override-interest" value="' + (initOv ? formatMoney(initOv.interest) : '') + '"/></div>';
                    html += '<div class="col-sm-3"><label>' + i18n.overridePenalty + '</label><input type="number" step="0.01" min="0" class="form-control input-sm loan-override-penalty" value="' + (initOv ? formatMoney(initOv.penalty) : '') + '"/></div>';
                    html += '<div class="col-sm-3"><label>' + i18n.overrideNote + '</label><input type="text" maxlength="255" class="form-control input-sm loan-override-note" value="' + (initOv && initOv.note ? String(initOv.note).replace(/"/g, '&quot;') : '') + '"/></div>';
                    html += '</div></div></td></tr>';
                });
                html += '</tbody><tfoot><tr><th colspan="6" class="text-right">' + i18n.selectedTotal + '</th>';
                html += '<th class="text-right" id="loanAllocSelectedTotal">0.00</th></tr></tfoot></table></div>';
                html += '<div id="loanAllocFocusMeta"></div>';
                $('#loanRepaymentPanelBody').html(html);
                $('#loanRepaymentPanel').show();
                $('#receivedFromSearchResults').html('');
                updateLoanAllocationSummary();
                syncLoanAllocationHiddenInputs();
                var firstChecked = $('#loanAllocTable .loan-alloc-cb:checked').first().closest('tr').data('loanId');
                if (firstChecked) {
                    focusLoanAllocation(firstChecked, true);
                } else {
                    $('#loanRepaymentDueWrap').hide();
                }
                rebuildMergedLoanWorksheets();
            }

            function getSelectedLoanAllocations() {
                var out = [];
                $('#loanAllocTable tbody tr.loan-alloc-row').each(function(){
                    var $row = $(this);
                    if (!$row.find('.loan-alloc-cb').is(':checked')) {
                        return;
                    }
                    var lid = String($row.data('loanId') || '');
                    var $ovRow = $('#loanAllocTable tr.loan-override-row[data-loan-id="' + lid + '"]');
                    var override = getRowOverride($ovRow);
                    if (override) {
                        var ovAmount = roundMoney(override.principal + override.interest + override.penalty);
                        $row.find('.loan-alloc-amount').val(formatMoney(ovAmount));
                        out.push({ LID: lid, amount: ovAmount, override: override });
                        return;
                    }
                    var amount = parseFloat(String($row.find('.loan-alloc-amount').val() || '').replace(/,/g, '')) || 0;
                    if (lid && amount > 0) {
                        out.push({ LID: lid, amount: amount });
                    }
                });
                return out;
            }

            function syncLoanAllocationHiddenInputs() {
                var allocs = getSelectedLoanAllocations();
                var html = '';
                $.each(allocs, function(_, row){
                    html += '<input type="hidden" name="loan_alloc_lid[]" value="' + String(row.LID).replace(/"/g, '&quot;') + '"/>';
                    html += '<input type="hidden" name="loan_alloc_amount[]" value="' + formatMoney(row.amount) + '"/>';
                    if (row.override && row.override.enabled) {
                        html += '<input type="hidden" name="loan_override_enabled[]" value="1"/>';
                        html += '<input type="hidden" name="loan_override_principal[]" value="' + formatMoney(row.override.principal) + '"/>';
                        html += '<input type="hidden" name="loan_override_interest[]" value="' + formatMoney(row.override.interest) + '"/>';
                        html += '<input type="hidden" name="loan_override_penalty[]" value="' + formatMoney(row.override.penalty) + '"/>';
                        html += '<input type="hidden" name="loan_override_note[]" value="' + String(row.override.note || '').replace(/"/g, '&quot;') + '"/>';
                    } else {
                        html += '<input type="hidden" name="loan_override_enabled[]" value="0"/>';
                        html += '<input type="hidden" name="loan_override_principal[]" value="0"/>';
                        html += '<input type="hidden" name="loan_override_interest[]" value="0"/>';
                        html += '<input type="hidden" name="loan_override_penalty[]" value="0"/>';
                        html += '<input type="hidden" name="loan_override_note[]" value=""/>';
                    }
                });
                $('#loanAllocHiddenInputs').html(html);
                $('#loan_repayment_lid').val(allocs.length ? allocs[0].LID : '');
                var total = 0;
                $.each(allocs, function(_, row){ total += row.amount; });
                $('#loanAllocSelectedTotal').text(formatMoney(total));
                var lids = $.map(allocs, function(r){ return r.LID; });
                var label = selectedMemberName || $('#received_from').val() || '';
                if (lids.length === 1) {
                    label = (label ? label + ' — ' : '') + 'Loan ' + lids[0];
                } else if (lids.length > 1) {
                    label = (label ? label + ' — ' : '') + lids.length + ' loans';
                }
                if (label) {
                    setSelectedPayerLabel(label);
                }
                var desc = $('textarea[name="description"]').val() || '';
                if (!desc || desc.indexOf('Loan Repayment') === 0) {
                    if (lids.length) {
                        $('textarea[name="description"]').val('Loan Repayment ' + lids.join(', '));
                    }
                }
                return allocs;
            }

            function updateLoanAllocationSummary() {
                var total = 0;
                $('#loanAllocTable tbody tr.loan-alloc-row').each(function(){
                    var $row = $(this);
                    if ($row.find('.loan-alloc-cb').is(':checked')) {
                        total += parseFloat(String($row.find('.loan-alloc-amount').val() || '').replace(/,/g, '')) || 0;
                    }
                });
                $('#loanAllocSelectedTotal').text(formatMoney(total));
            }

            function focusLoanAllocation(lid, loadDue) {
                focusedLoanId = lid || '';
                var loan = loanCache[lid];
                if (!loan) {
                    $('#loanAllocFocusMeta').html('');
                    $('#loanRepaymentDueWrap').hide();
                    return;
                }
                var html = '<div class="loan-repayment-meta">';
                html += '<div><strong>Loan #:</strong> ' + (loan.LID || '') + '</div>';
                html += '<div><strong>Member:</strong> ' + ($('#received_from').val() || selectedMemberName || '') + '</div>';
                html += '<div><strong>Product:</strong> ' + (loan.product_name || '') + '</div>';
                html += '<div><strong>Status:</strong> ' + (loan.status_name || '') + '</div>';
                html += '<div><strong>Outstanding:</strong> ' + formatMoney(loan.outstanding) + '</div>';
                html += '<div><strong>Amount Due:</strong> ' + formatMoney(loan.amount_due || loan.suggested_amount) + '</div>';
                html += '</div>';
                html += loanTermsHtml(loan);
                $('#loanAllocFocusMeta').html(html);
                if (loadDue) {
                    loadFocusedLoanDue(lid);
                }
            }

            function loadFocusedLoanDue(lid) {
                var $row = $('#loanAllocTable tbody tr.loan-alloc-row').filter(function(){
                    return String($(this).data('loanId')) === String(lid);
                });
                var amount = $row.find('.loan-alloc-amount').val() || '';
                $.ajax({
                    url: loanWorksheetUrl,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        lid: lid,
                        payment_method: $('#payment_method').val() || '',
                        receipt_date: $('input[name="receipt_date"]').val() || '',
                        amount: amount
                    },
                    success: function(response){
                        if (response.success === 'Y' && response.data) {
                            if (response.data.due) {
                                renderDuePanel(response.data.due, response.data);
                            }
                            if (loanCache[lid]) {
                                $.extend(loanCache[lid], response.data);
                            }
                            focusLoanAllocation(lid, false);
                        }
                    }
                });
            }

            function rebuildMergedLoanWorksheets() {
                var allocs = syncLoanAllocationHiddenInputs();
                if (!allocs.length) {
                    if (linesFromLoan) {
                        resetLineItems();
                        linesFromLoan = false;
                    }
                    return;
                }
                var seq = ++loanWorksheetSeq;
                var pending = allocs.length;
                var sheets = [];
                $.each(allocs, function(i, alloc){
                    var data = {
                        lid: alloc.LID,
                        payment_method: $('#payment_method').val() || '',
                        receipt_date: $('input[name="receipt_date"]').val() || '',
                        amount: alloc.amount
                    };
                    if (alloc.override && alloc.override.enabled) {
                        data.override_enabled = 1;
                        data.override_principal = alloc.override.principal;
                        data.override_interest = alloc.override.interest;
                        data.override_penalty = alloc.override.penalty;
                        data.override_note = alloc.override.note || '';
                    }
                    $.ajax({
                        url: loanWorksheetUrl,
                        type: 'GET',
                        dataType: 'json',
                        data: data,
                        success: function(response){
                            if (seq !== loanWorksheetSeq) {
                                return;
                            }
                            if (response.success === 'Y' && response.data && response.data.line_items) {
                                sheets[i] = response.data.line_items;
                                if (loanCache[alloc.LID]) {
                                    $.extend(loanCache[alloc.LID], response.data);
                                }
                            } else {
                                sheets[i] = [];
                                if (response.error) {
                                    $('#loanRepaymentPreviewMsg').text(response.error).show();
                                }
                            }
                        },
                        complete: function(){
                            if (seq !== loanWorksheetSeq) {
                                return;
                            }
                            pending -= 1;
                            if (pending <= 0) {
                                populateLoanLines(mergeLoanWorksheetLines(sheets));
                                linesFromLoan = true;
                                if (focusedLoanId) {
                                    loadFocusedLoanDue(focusedLoanId);
                                } else if (allocs[0]) {
                                    focusLoanAllocation(allocs[0].LID, true);
                                }
                            }
                        }
                    });
                });
            }

            function mergeLoanWorksheetLines(sheets) {
                var cashAccount = '';
                var cashDebit = 0;
                var cashDescParts = [];
                var credits = [];
                $.each(sheets || [], function(_, items){
                    $.each(items || [], function(_, item){
                        var role = item.role || '';
                        var debit = parseFloat(item.debit) || 0;
                        var credit = parseFloat(item.credit) || 0;
                        if (role === 'cash' || (debit > 0 && credit <= 0 && !cashAccount)) {
                            if (!cashAccount && item.account) {
                                cashAccount = item.account;
                            }
                            cashDebit += debit;
                            if (item.description) {
                                cashDescParts.push(item.description);
                            }
                        } else if (credit > 0 || role === 'principal' || role === 'interest' || role === 'penalty') {
                            credits.push({
                                account: item.account || '',
                                debit: '',
                                credit: credit > 0 ? credit : (item.credit || ''),
                                description: item.description || '',
                                role: role || ''
                            });
                        } else if (debit > 0) {
                            cashDebit += debit;
                            if (!cashAccount && item.account) {
                                cashAccount = item.account;
                            }
                        }
                    });
                });
                var merged = [];
                if (cashAccount || cashDebit > 0) {
                    var lids = $.map(getSelectedLoanAllocations(), function(r){ return r.LID; });
                    merged.push({
                        account: cashAccount,
                        debit: cashDebit > 0 ? formatMoney(cashDebit) : '',
                        credit: '',
                        description: lids.length ? ('Loan Repayment ' + lids.join(', ')) : 'Loan Repayment',
                        role: 'cash'
                    });
                }
                return merged.concat(credits);
            }

            $(document).on('click', '.select-payer-member', function(){
                var name = $(this).data('fullname') || '';
                $('#received_from').val(name);
                $('#member_pid').val($(this).data('pid') || '');
                $('#member_id').val($(this).data('member-id') || '');
                $('#loan_repayment_lid').val('');
                focusedLoanId = '';
                $('#loanRepaymentPanel').hide();
                $('#loanRepaymentDueWrap').hide();
                setSelectedPayerLabel(name);
                $('#receivedFromSearchResults').html('');
            });

            $(document).on('click', '.select-loan-member', function(){
                var name = $(this).data('fullname') || '';
                var pid = $(this).data('pid') || '';
                selectedMemberName = name;
                $('#member_pid').val(pid);
                $('#member_id').val($(this).data('member-id') || '');
                $('#received_from').val(name);
                $('#loan_repayment_lid').val('');
                focusedLoanId = '';
                setSelectedPayerLabel(name);
                loadMemberLoans(pid, {});
            });

            $(document).on('click', '.select-payer-name', function(){
                var name = $(this).data('fullname') || '';
                var kind = $(this).data('kind') || '';
                $('#received_from').val(name);
                $('#member_pid').val('');
                $('#member_id').val('');
                $('#loan_repayment_lid').val('');
                focusedLoanId = '';
                $('#loanRepaymentPanel').hide();
                $('#loanRepaymentDueWrap').hide();
                setSelectedPayerLabel(name);
                $('#receivedFromSearchResults').html('');
                if (kind === 'customer') {
                    addLinkedAccountLine(arAccountUrl, 'AR - ' + name);
                } else if (kind === 'supplier') {
                    addLinkedAccountLine(apAccountUrl, 'AP - ' + name);
                }
            });

            $(document).on('change', '.loan-alloc-cb', function(){
                var $row = $(this).closest('tr');
                var lid = $row.data('loanId');
                var checked = $(this).is(':checked');
                $row.toggleClass('success', checked);
                $row.find('.loan-alloc-amount').prop('disabled', !checked);
                var $ovRow = $('#loanAllocTable tr.loan-override-row[data-loan-id="' + lid + '"]');
                $ovRow.toggle(checked);
                if (!checked) {
                    $ovRow.find('.loan-override-enabled').prop('checked', false);
                    $ovRow.find('.loan-override-fields').hide();
                    $row.find('.loan-alloc-amount').prop('readonly', false);
                }
                if (checked) {
                    var amt = parseFloat(String($row.find('.loan-alloc-amount').val() || '').replace(/,/g, '')) || 0;
                    if (amt <= 0 && loanCache[lid] && loanCache[lid].amount_due > 0) {
                        $row.find('.loan-alloc-amount').val(formatMoney(loanCache[lid].amount_due));
                    }
                    focusLoanAllocation(lid, true);
                } else if (focusedLoanId === lid) {
                    var next = $('#loanAllocTable .loan-alloc-cb:checked').first().closest('tr').data('loanId');
                    if (next) {
                        focusLoanAllocation(next, true);
                    } else {
                        focusedLoanId = '';
                        $('#loanAllocFocusMeta').html('');
                        $('#loanRepaymentDueWrap').hide();
                    }
                }
                updateLoanAllocationSummary();
                rebuildMergedLoanWorksheets();
            });

            $(document).on('change', '.loan-alloc-amount', function(){
                var $row = $(this).closest('tr');
                if ($row.find('.loan-alloc-cb').is(':checked')) {
                    focusLoanAllocation($row.data('loanId'), true);
                    updateLoanAllocationSummary();
                    rebuildMergedLoanWorksheets();
                }
            });

            $(document).on('change', '.loan-override-enabled', function(){
                var $ovRow = $(this).closest('tr');
                var lid = $ovRow.data('loanId');
                var $row = $('#loanAllocTable tr.loan-alloc-row[data-loan-id="' + lid + '"]');
                var on = $(this).is(':checked');
                $ovRow.find('.loan-override-fields').toggle(on);
                $row.find('.loan-alloc-amount').prop('readonly', on);
                if (on) {
                    var loan = loanCache[lid] || {};
                    var sys = loan.system_suggested || {};
                    if (!$ovRow.find('.loan-override-principal').val()) {
                        $ovRow.find('.loan-override-principal').val(formatMoney(sys.principal != null ? sys.principal : (loan.due && loan.due.total_principle) || 0));
                    }
                    if (!$ovRow.find('.loan-override-interest').val()) {
                        $ovRow.find('.loan-override-interest').val(formatMoney(sys.interest != null ? sys.interest : (loan.due && loan.due.total_interest) || 0));
                    }
                    if (!$ovRow.find('.loan-override-penalty').val()) {
                        $ovRow.find('.loan-override-penalty').val(formatMoney(sys.penalty != null ? sys.penalty : (loan.due && loan.due.total_penalty) || 0));
                    }
                    syncOverrideAmountFromComponents($ovRow);
                    $row.find('.loan-alloc-amount').val(formatMoney(
                        (parseFloat($ovRow.find('.loan-override-principal').val()) || 0) +
                        (parseFloat($ovRow.find('.loan-override-interest').val()) || 0) +
                        (parseFloat($ovRow.find('.loan-override-penalty').val()) || 0)
                    ));
                }
                updateLoanAllocationSummary();
                rebuildMergedLoanWorksheets();
            });

            $(document).on('change', '.loan-override-principal, .loan-override-interest, .loan-override-penalty, .loan-override-note', function(){
                var $ovRow = $(this).closest('tr');
                var lid = $ovRow.data('loanId');
                var $row = $('#loanAllocTable tr.loan-alloc-row[data-loan-id="' + lid + '"]');
                if ($ovRow.find('.loan-override-enabled').is(':checked')) {
                    var sum = roundMoney(
                        (parseFloat(String($ovRow.find('.loan-override-principal').val() || '').replace(/,/g, '')) || 0) +
                        (parseFloat(String($ovRow.find('.loan-override-interest').val() || '').replace(/,/g, '')) || 0) +
                        (parseFloat(String($ovRow.find('.loan-override-penalty').val() || '').replace(/,/g, '')) || 0)
                    );
                    $row.find('.loan-alloc-amount').val(formatMoney(sum));
                    updateLoanAllocationSummary();
                    rebuildMergedLoanWorksheets();
                }
            });

            $(document).on('click', '#loanAllocTable tbody tr.loan-alloc-row', function(e){
                if ($(e.target).is('input') || $(e.target).closest('input').length) {
                    return;
                }
                var lid = $(this).data('loanId');
                if (lid) {
                    focusLoanAllocation(lid, true);
                }
            });

            function formatMoney(n) {
                var v = parseFloat(n);
                if (isNaN(v)) v = 0;
                return v.toFixed(2);
            }

            // Amount that means nothing when the contract has no value yet.
            function moneyOrDash(v) {
                var n = parseFloat(v);
                return (isNaN(n) || n <= 0) ? '&mdash;' : formatMoney(n);
            }

            // Schedule dates arrive as Y-m-d; the rest of the form shows d-m-Y.
            function formatDateDmy(value) {
                var raw = String(value || '').substr(0, 10);
                var parts = raw.split('-');
                if (parts.length !== 3 || parts[0].length !== 4) {
                    return raw || '&mdash;';
                }
                return parts[2] + '-' + parts[1] + '-' + parts[0];
            }

            // Footer total for a component: prefer the server total, fall back to the rows.
            function dueTotalOf(due, itemField, totalField) {
                if (due && due[totalField] != null) {
                    return parseFloat(due[totalField]) || 0;
                }
                var t = 0;
                $.each((due && due.items) ? due.items : [], function(_, item){
                    t += parseFloat(item[itemField]) || 0;
                });
                return t;
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
                    body = '<tr><td colspan="8" class="text-muted">' + i18n.nothingDue + '</td></tr>';
                } else {
                    $.each(items, function(i, item){
                        var overdue = item.status === 'overdue';
                        body += '<tr class="' + (overdue ? 'warning' : '') + '">';
                        body += '<td>' + (item.installment || '') + '</td>';
                        body += '<td>' + (item.due_date || '') + '</td>';
                        body += '<td>' + (overdue ? i18n.statusOverdue : i18n.statusDue) + '</td>';
                        body += '<td class="text-right">' + formatMoney(item.principle) + '</td>';
                        body += '<td class="text-right">' + formatMoney(item.interest) + '</td>';
                        body += '<td class="text-right">' + formatMoney(item.penalty) + '</td>';
                        body += '<td class="text-right">' + (item.penalty_months || 0) + '</td>';
                        body += '<td class="text-right"><strong>' + formatMoney(item.total) + '</strong></td>';
                        body += '</tr>';
                    });
                }
                $('#loanRepaymentDueBody').html(body);
                $('#crDueTotalPrinciple').text(formatMoney(dueTotalOf(due, 'principle', 'total_principle')));
                $('#crDueTotalInterest').text(formatMoney(dueTotalOf(due, 'interest', 'total_interest')));
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
                if (extra.preview_ok === false && extra.preview_message) {
                    $('#loanRepaymentPreviewMsg').text(extra.preview_message).show();
                } else {
                    $('#loanRepaymentPreviewMsg').hide().text('');
                }
                $('#loanRepaymentDueWrap').show();
            }

            // Contract terms behind the balance, so the cashier can answer the
            // member without leaving the receipt (term, amount, rate, installment,
            // first due date, total obligation).
            function loanTermsHtml(loan) {
                var interval = loan.interval_label || '';
                var term = loan.term ? String(loan.term) : '';
                if (term && interval) { term += ' ' + interval; }
                var rate = parseFloat(loan.interest_rate);
                var html = '<div class="loan-repayment-terms">';
                html += '<div class="terms-head">' + i18n.loanTerms + '</div>';
                html += '<div><strong>' + i18n.loanTerm + ':</strong> ' + (term || '&mdash;') + '</div>';
                html += '<div><strong>' + i18n.loanAmount + ':</strong> ' + moneyOrDash(loan.loan_amount) + '</div>';
                html += '<div><strong>' + i18n.loanInterestRate + ':</strong> ' + ((!isNaN(rate) && rate > 0) ? formatMoney(rate) + '%' : '&mdash;') + '</div>';
                html += '<div><strong>' + i18n.loanMonthly + ':</strong> ' + moneyOrDash(loan.monthly_installment) + '</div>';
                html += '<div><strong>' + i18n.loanFirstInstallment + ':</strong> ' + formatDateDmy(loan.first_installment_date) + '</div>';
                html += '<div><strong>' + i18n.loanTotalObligation + ':</strong> ' + moneyOrDash(loan.total_loan) + '</div>';
                html += '</div>';
                return html;
            }

            function renderLinkedLoan(loan){
                if (!loan) {
                    $('#loanRepaymentDueWrap').hide();
                    return;
                }
                focusLoanAllocation(loan.LID || focusedLoanId, false);
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
                                cloneLineItem(response.account, desc);
                                updateRemoveButtons();
                                calculateTotals();
                            }
                        }
                    }
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

            function resetLineItems() {
                var $first = $('.line-item:first');
                $('.line-item').not($first).each(function(){
                    destroyAccountSelect($(this).find('.account-select'));
                    $(this).remove();
                });
                $first.removeAttr('data-line-role');
                $first.find('input').val('');
                $first.find('.account-select').val('').trigger('change');
                updateRemoveButtons();
                calculateTotals();
            }

            function applyLineValues($row, item) {
                $row.attr('data-line-role', item.role || '');
                setRowAccount($row, item.account || '');
                $row.find('input[name="line_description[]"]').val(item.description || '');
                $row.find('.debit-input').val(item.debit || '');
                $row.find('.credit-input').val(item.credit || '');
            }

            function populateLoanLines(items) {
                if (!items || !items.length) {
                    if (linesFromLoan) {
                        resetLineItems();
                    }
                    return;
                }
                var $first = $('.line-item:first');
                $('.line-item').not($first).each(function(){
                    destroyAccountSelect($(this).find('.account-select'));
                    $(this).remove();
                });
                applyLineValues($first, items[0]);
                for (var i = 1; i < items.length; i++) {
                    var $row = cloneLineItem(items[i].account || '', items[i].description || '');
                    applyLineValues($row, items[i]);
                }
                updateRemoveButtons();
                calculateTotals();
            }

            function syncPaymentMethodAccount() {
                if ($('#received_from_type').val() !== 'loan_repayment') {
                    return;
                }
                if (getSelectedLoanAllocations().length) {
                    rebuildMergedLoanWorksheets();
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
                        }
                    }
                });
            }

            $('#btnUseSuggestedLoanAmount').on('click', function(){
                if (!focusedLoanId || loanDueState.suggested <= 0) {
                    return;
                }
                var $row = $('#loanAllocTable tbody tr.loan-alloc-row').filter(function(){
                    return String($(this).data('loanId')) === String(focusedLoanId);
                });
                if (!$row.length) {
                    return;
                }
                if (!$row.find('.loan-alloc-cb').is(':checked')) {
                    $row.find('.loan-alloc-cb').prop('checked', true).trigger('change');
                }
                $row.find('.loan-alloc-amount').prop('disabled', false).val(formatMoney(loanDueState.suggested));
                updateLoanAllocationSummary();
                rebuildMergedLoanWorksheets();
            });

            // The date field is driven by bootstrap-datepicker, which fires only
            // changeDate (on the #datetimepicker wrapper) and never 'change' on the
            // input, so picking a date used to leave the loan line items stale.
            var loanWorksheetDateTimer = null;
            function refreshLoanWorksheetForDate() {
                if ($('#received_from_type').val() !== 'loan_repayment') {
                    return;
                }
                if (!getSelectedLoanAllocations().length) {
                    return;
                }
                if (loanWorksheetDateTimer) {
                    clearTimeout(loanWorksheetDateTimer);
                }
                loanWorksheetDateTimer = setTimeout(function(){
                    loanWorksheetDateTimer = null;
                    rebuildMergedLoanWorksheets();
                }, 120);
            }
            $('input[name="receipt_date"]').on('change blur', refreshLoanWorksheetForDate);
            $('#datetimepicker').on('changeDate', refreshLoanWorksheetForDate);

            updateReceivedFromUI(false);
            if ($('#received_from_type').val() === 'loan_repayment') {
                selectedMemberName = $('#received_from').val() || '';
                var pid = $('#member_pid').val() || '';
                if (pid) {
                    var preselect = {};
                    $.each(initialLoanAllocations || [], function(_, row){
                        if (row.LID) {
                            preselect[row.LID] = parseFloat(row.amount) || 0;
                        }
                    });
                    loadMemberLoans(pid, preselect);
                } else if ($('#loan_repayment_lid').val()) {
                    setSelectedPayerLabel(($('#received_from').val() || '') + ' — Loan ' + $('#loan_repayment_lid').val());
                }
            }
        }
        $(boot);
    }
    initOnceReady();
})();
</script>
