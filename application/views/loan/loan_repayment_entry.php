<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
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
                                                <optgroup label="<?php echo htmlspecialchars($value1['info']->name); ?>">
                                                    <?php foreach ($value1['data'] as $key => $value) {
                                                        $sel = ($default_debit !== '' && (string)$value->account === (string)$default_debit) ? ' selected="selected"' : '';
                                                    ?>
                                                        <option value="<?php echo $value->account; ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($value->name); ?></option>
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
                                                <optgroup label="<?php echo htmlspecialchars($value1['info']->name); ?>">
                                                    <?php foreach ($value1['data'] as $key => $value) {
                                                        $sel = ($loan_credit !== '' && (string)$value->account === (string)$loan_credit) ? ' selected="selected"' : '';
                                                    ?>
                                                        <option value="<?php echo $value->account; ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($value->name); ?></option>
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
                });
            }

            ensureBootstrapDP(initPicker);

            updateRemoveButtons();

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
