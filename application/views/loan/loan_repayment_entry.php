<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
</style>

<?php echo form_open(current_lang() . '/loan/loan_repayment_process', 'class="form-horizontal" id="loanRepaymentForm"'); ?>

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

<input type="hidden" name="loanid" value="<?php echo htmlspecialchars(isset($loaninfo) ? $loaninfo->LID : ''); ?>"/>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo lang('loan_repayment'); ?> - <?php echo lang('loan_repay_btn'); ?></h5>
                    <div class="ibox-tools">
                        <a href="<?php echo site_url(current_lang() . '/loan/loan_repayment'); ?>" class="btn btn-white btn-xs">
                            <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">

                    <?php
                    if (isset($loaninfo) && $loaninfo):
                        $loan_product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
                        $loan_interval = $this->setting_model->intervalinfo($loaninfo->interval)->row();
                        $loan_member = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
                        $loan_member_name = '';
                        if ($loan_member) {
                            $loan_member_name = trim($loan_member->member_id . ' - ' . $loan_member->firstname . ' ' . $loan_member->middlename . ' ' . $loan_member->lastname);
                        } else {
                            $loan_member_name = trim((isset($loaninfo->firstname) ? $loaninfo->firstname : '') . ' ' . (isset($loaninfo->middlename) ? $loaninfo->middlename : '') . ' ' . (isset($loaninfo->lastname) ? $loaninfo->lastname : ''));
                        }
                        $disburse_row = null;
                        if ($this->db->table_exists('loan_contract_disburse')) {
                            $this->db->where('LID', $loaninfo->LID);
                            $this->db->where('PIN', $loaninfo->PIN);
                            $this->db->order_by('disbursedate', 'ASC');
                            $this->db->limit(1);
                            $disburse_row = $this->db->get('loan_contract_disburse')->row();
                        }
                        // First installment due date from schedule (true repayment start), not disbursement date
                        $first_due_row = $this->db->select('repaydate')
                            ->where('LID', $loaninfo->LID)
                            ->where('PIN', $loaninfo->PIN)
                            ->order_by('installment_number', 'ASC')
                            ->limit(1)
                            ->get('loan_contract_repayment_schedule')
                            ->row();
                        $interval_label = '';
                        if ($loan_interval) {
                            $interval_label = isset($loan_interval->description) ? $loan_interval->description : (isset($loan_interval->name) ? $loan_interval->name : '');
                        }
                    ?>
                    <div class="panel panel-default" style="margin-bottom: 20px;">
                        <div class="panel-heading">
                            <h4 style="margin:0;"><?php echo lang('loan_info'); ?></h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-condensed" style="margin-bottom: 0;">
                                <tr>
                                    <td><strong><?php echo lang('loan_LID'); ?>:</strong> <?php echo htmlspecialchars($loaninfo->LID); ?></td>
                                    <td><strong><?php echo lang('member_name'); ?>:</strong> <?php echo htmlspecialchars($loan_member_name); ?></td>
                                    <td><strong><?php echo lang('loan_product'); ?>:</strong> <?php echo htmlspecialchars($loan_product ? $loan_product->name : '—'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo lang('loan_applicationdate'); ?>:</strong> <?php echo !empty($loaninfo->applicationdate) ? htmlspecialchars(format_date($loaninfo->applicationdate, FALSE)) : '—'; ?></td>
                                    <td><strong><?php echo lang('loan_disburse_date'); ?>:</strong> <?php echo ($disburse_row && !empty($disburse_row->disbursedate)) ? htmlspecialchars(format_date($disburse_row->disbursedate, FALSE)) : '—'; ?></td>
                                    <td><strong><?php echo lang('loan_applied_amount'); ?>:</strong> <?php echo number_format((float) $loaninfo->basic_amount, 2); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo lang('loan_installment_amount'); ?>:</strong> <?php echo number_format((float) $loaninfo->installment_amount, 2); ?></td>
                                    <td><strong><?php echo lang('loan_total'); ?>:</strong> <?php echo number_format((float) $loaninfo->total_loan, 2); ?></td>
                                    <td><strong><?php echo lang('loanproduct_interest'); ?>:</strong> <?php echo (isset($loaninfo->rate) && $loaninfo->rate !== '' && $loaninfo->rate !== null) ? htmlspecialchars($loaninfo->rate) . '%' : '—'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo lang('loan_installment'); ?>:</strong> <?php echo (int) $loaninfo->number_istallment . ($interval_label !== '' ? ' ' . htmlspecialchars($interval_label) : ''); ?></td>
                                    <td><strong><?php echo lang('loanproduct_penalt_percentage'); ?>:</strong> <?php echo ($loan_product && $loan_product->penalt_percentage !== '' && $loan_product->penalt_percentage !== null) ? htmlspecialchars($loan_product->penalt_percentage) . '%' : '—'; ?></td>
                                    <td><strong><?php echo lang('loan_startrepay_date'); ?>:</strong> <?php
                                        echo ($first_due_row && !empty($first_due_row->repaydate))
                                            ? htmlspecialchars(format_date($first_due_row->repaydate, FALSE))
                                            : '—';
                                    ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Receipt Header Information (same order as cash receipt create) -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_no'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" name="receipt_no" value="<?php echo set_value('receipt_no', isset($next_receipt_no) ? $next_receipt_no : 'CR-00001'); ?>" class="form-control" required/>
                                    <?php echo form_error('receipt_no'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('loan_repay_date'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
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
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_received_from'); ?> :</label>
                                <div class="col-lg-8">
                                    <?php
                                    $member_display = '';
                                    if (isset($loaninfo) && !empty($loaninfo->PID)) {
                                        $info = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
                                        $member_display = $info ? ($info->member_id . ' : ' . $info->firstname . ' ' . $info->middlename . ' ' . $info->lastname) : ($loaninfo->firstname . ' ' . $loaninfo->middlename . ' ' . $loaninfo->lastname);
                                    }
                                    ?>
                                    <p class="form-control-static"><strong><?php echo htmlspecialchars($member_display); ?></strong></p>
                                    <input type="hidden" name="received_from" value="<?php echo htmlspecialchars($member_display); ?>"/>
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
                                        $selected_pm = set_value('payment_method', isset($default_payment_method_id) ? $default_payment_method_id : '');
                                        if (isset($payment_methods) && is_array($payment_methods)) {
                                            foreach ($payment_methods as $pm_id => $pm_name) {
                                                $sel = ((string)$selected_pm !== '' && (string)$selected_pm === (string)$pm_id) ? 'selected="selected"' : '';
                                                $is_cheque = (strtolower(trim($pm_name)) === 'cheque') ? ' data-is-cheque="1"' : '';
                                                echo '<option value="' . htmlspecialchars($pm_id) . '" ' . $sel . $is_cheque . '>' . htmlspecialchars($pm_name) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <?php echo form_error('payment_method'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="cheque_details" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_cheque_no'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="cheque_no" value="<?php echo set_value('cheque_no'); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_bank_name'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="bank_name" value="<?php echo set_value('bank_name'); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="col-lg-2 control-label"><?php echo lang('cash_receipt_description'); ?> :</label>
                                <div class="col-lg-10">
                                    <textarea name="description" class="form-control" rows="3"><?php echo set_value('description', 'Loan Repayment - ' . (isset($loaninfo) ? $loaninfo->LID : '')); ?></textarea>
                                    <?php echo form_error('description'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <!-- Amount due preview (recalculated when repayment date changes) -->
                    <?php
                    $due = isset($repayment_due) ? $repayment_due : null;
                    $suggested = $due && isset($due->suggested_amount) ? (float) $due->suggested_amount : (isset($loaninfo->installment_amount) ? (float) $loaninfo->installment_amount : 0);
                    ?>
                    <div id="repaymentDuePanel" class="panel panel-info" style="margin-bottom: 20px;">
                        <div class="panel-heading">
                            <strong><?php echo lang('loan_repay_due_title'); ?></strong>
                        </div>
                        <div class="panel-body">
                            <p class="text-muted small" id="repaymentDueExplanation">
                                <?php
                                echo sprintf(
                                    lang('loan_repay_due_explanation'),
                                    ($due && isset($due->grace_days)) ? (int) $due->grace_days : (defined('MAX_NUMBER_DAYS_OVERDUE_PENALT') ? (int) MAX_NUMBER_DAYS_OVERDUE_PENALT : 0),
                                    ($due && isset($due->penalt_percentage)) ? rtrim(rtrim(number_format((float) $due->penalt_percentage, 2), '0'), '.') : '0'
                                );
                                ?>
                            </p>
                            <div class="table-responsive">
                                <table class="table table-condensed table-bordered" id="repaymentDueTable" style="margin-bottom: 10px;">
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
                                        <tr class="info">
                                            <th colspan="6" class="text-right"><?php echo lang('loan_repay_net_due'); ?> / <?php echo lang('loan_repay_suggested'); ?></th>
                                            <th class="text-right" id="dueNetDue"><?php echo number_format($suggested, 2); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <button type="button" class="btn btn-white btn-sm" id="btnUseSuggestedAmount">
                                <i class="fa fa-magic"></i> <?php echo lang('loan_repay_use_suggested'); ?>
                            </button>
                            <a href="#" class="btn btn-primary btn-sm" id="btnPrintCollectionNotice" target="_blank" rel="noopener">
                                <i class="fa fa-print"></i> <?php echo lang('loan_collection_notice_print'); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Line Items Table (same as cash receipt create: Account | Description | Debit | Credit) -->
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
                                $default_debit = isset($default_debit_account) ? $default_debit_account : '';
                                $loan_credit = isset($loan_credit_account) ? $loan_credit_account : '';
                                ?>
                                <tr class="line-item loan-repay-debit-row" data-row-type="debit">
                                    <td>
                                        <select class="form-control account-select" name="account[]">
                                            <option value=""><?php echo lang('select_default_text'); ?></option>
                                            <?php if (isset($account_list) && is_array($account_list)) { foreach ($account_list as $key1 => $value1) { ?>
                                                <optgroup label="<?php echo htmlspecialchars($value1['info']->account . ' - ' . $value1['info']->name); ?>">
                                                    <?php foreach ($value1['data'] as $key => $value) {
                                                        $sel = ($default_debit !== '' && (string)$value->account === (string)$default_debit) ? ' selected="selected"' : '';
                                                    ?>
                                                        <option value="<?php echo $value->account; ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($value->account . ' - ' . $value->name); ?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            <?php } } ?>
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
                                            <?php if (isset($account_list) && is_array($account_list)) { foreach ($account_list as $key1 => $value1) { ?>
                                                <optgroup label="<?php echo htmlspecialchars($value1['info']->account . ' - ' . $value1['info']->name); ?>">
                                                    <?php foreach ($value1['data'] as $key => $value) {
                                                        $sel = ($loan_credit !== '' && (string)$value->account === (string)$loan_credit) ? ' selected="selected"' : '';
                                                    ?>
                                                        <option value="<?php echo $value->account; ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($value->account . ' - ' . $value->name); ?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            <?php } } ?>
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
                        <button type="button" class="btn btn-primary" id="addLineItem">
                            <i class="fa fa-plus"></i> <?php echo lang('add_row'); ?>
                        </button>
                    </div>

                    <input type="hidden" name="amount" id="amount_from_lines" value=""/>

                    <hr/>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo lang('save'); ?>
                            </button>
                            <a href="<?php echo site_url(current_lang() . '/loan/loan_repayment'); ?>" class="btn btn-white">
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
                        $('#lineItemsTable tbody .loan-repay-debit-row').first().find('.account-select').val(String(account));
                    }
                }
            });

            // Add line item
            $('#addLineItem').on('click', function(){
                var newRow = $('.line-item:first').clone();
                newRow.find('input, select').val('');
                $('#lineItemsTable tbody').append(newRow);
                updateRemoveButtons();
                calculateTotals();
            });

            $(document).on('click', '.remove-line', function(){
                if ($('.line-item').length > 1) {
                    $(this).closest('tr').remove();
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
