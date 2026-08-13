<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
.cd-edit-paid-to-detail {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 12px 14px;
    margin-top: 8px;
}
.cd-edit-paid-to-selected {
    margin-top: 8px;
    font-size: 13px;
    color: #0e7c69;
    font-weight: 600;
}
.cd-edit-paid-to-results {
    margin-top: 10px;
    max-height: 260px;
    overflow-y: auto;
}
.cd-edit-paid-to-results .table { margin-bottom: 0; background: #fff; }
</style>

<?php
$selected_paid_to_type = isset($selected_paid_to_type) ? $selected_paid_to_type : 'miscellaneous';
$paid_to_value = set_value('paid_to', isset($disburse->paid_to) ? $disburse->paid_to : '');
$paid_to_types = array(
    'loan_release' => lang('cash_disbursement_paid_to_loan_release'),
    'member' => lang('cash_disbursement_paid_to_member'),
    'supplier' => lang('cash_disbursement_paid_to_supplier'),
    'customer' => lang('cash_disbursement_paid_to_customer'),
    'miscellaneous' => lang('cash_disbursement_paid_to_miscellaneous'),
);
$cash_disbursement_numeric_id = isset($disburse->id) ? (int) $disburse->id : 0;
?>

<?php echo form_open_multipart(current_lang() . "/cash_disbursement/cash_disbursement_edit/" . $id, 'class="form-horizontal" id="cashDisbursementForm"'); ?>

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

<input type="hidden" name="id" value="<?php echo $disburse->id; ?>"/>
<input type="hidden" name="loan_release_lid" id="loan_release_lid" value="<?php echo !empty($selected_release_lid) ? htmlspecialchars($selected_release_lid) : ''; ?>"/>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo lang('cash_disbursement_edit'); ?> - <?php echo $disburse->disburse_no; ?></h5>
                    <div class="ibox-tools">
                        <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_list'); ?>" class="btn btn-white btn-xs">
                            <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    
                    <!-- Disbursement Header Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_disbursement_no'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" name="disburse_no" value="<?php echo set_value('disburse_no', $disburse->disburse_no); ?>" class="form-control" required/>
                                    <?php echo form_error('disburse_no'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_disbursement_date'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <div class="input-group date" id="datetimepicker">
                                        <input type="text" name="disburse_date" placeholder="<?php echo lang('hint_date'); ?>" 
                                               value="<?php echo set_value('disburse_date', date('d-m-Y', strtotime($disburse->disburse_date))); ?>" 
                                               data-date-format="dd-mm-yyyy" class="form-control" required/> 
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar"></span>
                                        </span>
                                    </div>
                                    <?php echo form_error('disburse_date'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="loanReleaseEditPanel" style="<?php echo !empty($selected_release_lid) ? '' : 'display:none;'; ?>">
                        <div class="col-md-12">
                            <div class="alert alert-info" style="margin-bottom:10px;">
                                <strong><?php echo lang('loan_release_loan'); ?>:</strong>
                                <span id="loanReleaseEditLabel"><?php echo htmlspecialchars((string) $selected_release_lid); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_disbursement_paid_to'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <select name="paid_to_type" id="paid_to_type" class="form-control" required>
                                        <option value=""><?php echo lang('select_default_text'); ?></option>
                                        <?php foreach ($paid_to_types as $type_key => $type_label) { ?>
                                            <option value="<?php echo htmlspecialchars($type_key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((string) $selected_paid_to_type === (string) $type_key) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <?php echo form_error('paid_to_type'); ?>
                                    <div class="cd-edit-paid-to-detail" id="paidToDetail" style="display:none;">
                                        <div id="paidToSearchWrap" style="display:none;">
                                            <label id="paidToSearchLabel" class="control-label" style="padding-top:0;margin-bottom:6px;display:block;"></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="paidToSearchKey" autocomplete="off" placeholder=""/>
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-primary" id="doPaidToSearch">
                                                        <i class="fa fa-search"></i> <?php echo lang('search'); ?>
                                                    </button>
                                                </span>
                                            </div>
                                            <div class="cd-edit-paid-to-selected" id="paidToSelected" style="display:none;"></div>
                                            <div class="cd-edit-paid-to-results" id="paidToSearchResults"></div>
                                        </div>
                                        <div id="paidToMiscWrap" style="display:none;">
                                            <label class="control-label" style="padding-top:0;margin-bottom:6px;display:block;"><?php echo lang('cash_disbursement_to_the_order_of'); ?> : <span class="required">*</span></label>
                                            <input type="text" id="to_the_order_of" class="form-control" value="<?php echo htmlspecialchars($paid_to_value, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                                        </div>
                                    </div>
                                    <input type="hidden" name="paid_to" id="paid_to" value="<?php echo htmlspecialchars($paid_to_value, ENT_QUOTES, 'UTF-8'); ?>"/>
                                    <input type="hidden" name="member_pid" id="member_pid" value=""/>
                                    <input type="hidden" name="member_id" id="member_id" value=""/>
                                    <?php echo form_error('paid_to'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_disbursement_payment_method'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                        <option value=""><?php echo lang('select_default_text'); ?></option>
                                        <?php 
                                        $selected_payment_id = set_value('payment_method');
                                        // Handle saved payment_method: if it's numeric (ID), use it directly; if it's a name, lookup the ID
                                        if (empty($selected_payment_id) && !empty($disburse->payment_method)) {
                                            if (is_numeric($disburse->payment_method)) {
                                                // Saved value is an ID
                                                $selected_payment_id = (int)$disburse->payment_method;
                                            } else {
                                                // Saved value is a name, lookup the ID
                                                $saved_method_lower = strtolower(trim((string)$disburse->payment_method));
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

                    <div class="row" id="cheque_details" style="display: <?php echo ($disburse->payment_method == 'Cheque') ? 'block' : 'none'; ?>;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_disbursement_cheque_no'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="cheque_no" value="<?php echo set_value('cheque_no', $disburse->cheque_no); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_disbursement_bank_name'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="bank_name" value="<?php echo set_value('bank_name', $disburse->bank_name); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="col-lg-2 control-label"><?php echo lang('cash_disbursement_description'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-10">
                                    <textarea name="description" class="form-control" rows="3" required><?php echo set_value('description', $disburse->description); ?></textarea>
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
                                            <input type="checkbox" name="cancelled" id="cancelled" value="1" <?php echo set_checkbox('cancelled', '1', !empty($disburse->cancelled)); ?>/>
                                            <?php echo lang('cancelled'); ?>
                                        </label>
                                    </div>
                                    <p class="help-block text-muted" style="margin-left: 0;"><?php echo lang('cash_disbursement_cancelled_help'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <!-- Line Items Table (journal-entry style: Account | Description | Debit | Credit) -->
                    <h4><?php echo lang('cash_disbursement_line_items'); ?></h4>
                    <div class="table-responsive">
                        <table id="lineItemsTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 30%;"><?php echo lang('cash_disbursement_account'); ?> <span class="required">*</span></th>
                                    <th style="width: 30%;"><?php echo lang('cash_disbursement_line_description'); ?></th>
                                    <th style="width: 15%;"><?php echo lang('journalentry_debit'); ?></th>
                                    <th style="width: 15%;"><?php echo lang('journalentry_credit'); ?></th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $edit_total_debit = 0; $edit_total_credit = 0;
                                foreach ($line_items as $index => $item): 
                                    $item_debit = isset($item->debit) ? floatval($item->debit) : (isset($item->amount) ? floatval($item->amount) : 0);
                                    $item_credit = isset($item->credit) ? floatval($item->credit) : 0;
                                    $edit_total_debit += $item_debit;
                                    $edit_total_credit += $item_credit;
                                ?>
                                <tr class="line-item">
                                    <td>
                                        <select class="form-control account-select" name="account[]">
                                            <option value=""><?php echo lang('select_default_text'); ?></option>
                                            <?php foreach ($account_list as $key1 => $value1) { ?>
                                                <optgroup label="<?php echo htmlspecialchars($value1['info']->account . ' - ' . $value1['info']->name); ?>">
                                                    <?php foreach ($value1['data'] as $key => $value) { ?>
                                                        <option value="<?php echo $value->account; ?>" <?php echo ($item->account == $value->account) ? 'selected' : ''; ?>><?php echo htmlspecialchars($value->account . ' - ' . $value->name); ?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            <?php } ?>
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
                            <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_view/' . $id); ?>" class="btn btn-white">
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
            var releaseCache = {};
            var cashDisbursementId = <?php echo (int) $cash_disbursement_numeric_id; ?>;

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

            updateRemoveButtons();
            calculateTotals();

            $('#payment_method').on('change', function(){
                var txt = ($('#payment_method option:selected').text() || '').toLowerCase();
                if (txt.indexOf('cheque') >= 0 || txt.indexOf('check') >= 0) {
                    $('#cheque_details').show();
                } else {
                    $('#cheque_details').hide();
                }
            }).trigger('change');

            var memberSearchUrl = '<?php echo site_url(current_lang() . '/cash_disbursement/search_member'); ?>';
            var pendingReleaseSearchUrl = '<?php echo site_url(current_lang() . '/cash_disbursement/search_pending_loan_releases'); ?>';
            var supplierSearchUrl = '<?php echo site_url(current_lang() . '/cash_disbursement/search_supplier'); ?>';
            var customerSearchUrl = '<?php echo site_url(current_lang() . '/cash_disbursement/search_customer'); ?>';

            var i18n = {
                searchRelease: <?php echo json_encode(lang('cash_disbursement_search_loan_release')); ?>,
                searchMember: <?php echo json_encode(lang('cash_disbursement_search_member')); ?>,
                searchSupplier: <?php echo json_encode(lang('cash_disbursement_search_supplier')); ?>,
                searchCustomer: <?php echo json_encode(lang('cash_disbursement_search_customer')); ?>,
                phRelease: <?php echo json_encode(lang('cash_disbursement_search_placeholder_release')); ?>,
                phMember: <?php echo json_encode(lang('cash_disbursement_search_placeholder_member')); ?>,
                phSupplier: <?php echo json_encode(lang('cash_disbursement_search_placeholder_supplier')); ?>,
                phCustomer: <?php echo json_encode(lang('cash_disbursement_search_placeholder_customer')); ?>,
                selected: <?php echo json_encode(lang('cash_disbursement_selected_payee')); ?>,
                minChars: <?php echo json_encode(lang('cash_disbursement_search_min_chars')); ?>,
                payeeRequired: <?php echo json_encode(lang('cash_disbursement_payee_required')); ?>,
                releaseRequired: <?php echo json_encode(lang('cash_disbursement_loan_release_required')); ?>,
                noMembers: <?php echo json_encode(lang('cash_disbursement_no_members_found')); ?>,
                noSuppliers: <?php echo json_encode(lang('cash_disbursement_no_suppliers_found')); ?>,
                noCustomers: <?php echo json_encode(lang('cash_disbursement_no_customers_found')); ?>,
                noReleases: <?php echo json_encode(lang('cash_disbursement_no_pending_releases')); ?>,
                releaseHint: <?php echo json_encode(lang('cash_disbursement_loan_release_list_hint')); ?>
            };

            function clearPayeeSelection(keepMiscText) {
                $('#paid_to').val('');
                $('#member_pid').val('');
                $('#member_id').val('');
                $('#loan_release_lid').val('');
                $('#loanReleaseEditLabel').text('');
                $('#loanReleaseEditPanel').hide();
                $('#paidToSelected').hide().text('');
                $('#paidToSearchResults').html('');
                $('#paidToSearchKey').val('');
                if (!keepMiscText) {
                    $('#to_the_order_of').val('');
                }
            }

            function setSelectedPayeeLabel(label) {
                if (!label) {
                    $('#paidToSelected').hide().text('');
                    return;
                }
                $('#paidToSelected').text(i18n.selected + ': ' + label).show();
            }

            function syncMiscPaidTo() {
                $('#paid_to').val($.trim($('#to_the_order_of').val() || ''));
            }

            function showLinkedRelease(lid, name) {
                if (!lid) {
                    $('#loanReleaseEditPanel').hide();
                    $('#loanReleaseEditLabel').text('');
                    return;
                }
                var label = lid;
                if (name) label = name + ' — Loan ' + lid;
                $('#loanReleaseEditLabel').text(label);
                $('#loanReleaseEditPanel').show();
            }

            function updatePaidToUI(resetSelection) {
                var type = $('#paid_to_type').val();
                if (resetSelection) {
                    clearPayeeSelection(type === 'miscellaneous');
                }
                if (!type) {
                    $('#paidToDetail').hide();
                    $('#paidToSearchWrap').hide();
                    $('#paidToMiscWrap').hide();
                    return;
                }
                $('#paidToDetail').show();
                if (type === 'miscellaneous') {
                    $('#paidToSearchWrap').hide();
                    $('#paidToMiscWrap').show();
                    if (!$('#to_the_order_of').val() && $('#paid_to').val()) {
                        $('#to_the_order_of').val($('#paid_to').val());
                    }
                    syncMiscPaidTo();
                    return;
                }
                $('#paidToMiscWrap').hide();
                $('#paidToSearchWrap').show();
                var label = i18n.searchMember;
                var placeholder = i18n.phMember;
                if (type === 'loan_release') {
                    label = i18n.searchRelease;
                    placeholder = i18n.phRelease;
                } else if (type === 'supplier') {
                    label = i18n.searchSupplier;
                    placeholder = i18n.phSupplier;
                } else if (type === 'customer') {
                    label = i18n.searchCustomer;
                    placeholder = i18n.phCustomer;
                }
                $('#paidToSearchLabel').text(label);
                $('#paidToSearchKey').attr('placeholder', placeholder);
                if ($('#paid_to').val()) {
                    setSelectedPayeeLabel($('#paid_to').val());
                }
                if (type === 'loan_release' && $('#loan_release_lid').val()) {
                    showLinkedRelease($('#loan_release_lid').val(), $('#paid_to').val());
                } else if (type !== 'loan_release') {
                    showLinkedRelease('', '');
                }
                if (type === 'loan_release') {
                    runPaidToSearch($.trim($('#paidToSearchKey').val() || ''));
                }
            }

            $('#paid_to_type').on('change', function(){
                updatePaidToUI(true);
            });
            $('#to_the_order_of').on('keyup change', syncMiscPaidTo);

            $('#doPaidToSearch').on('click', function(){
                var type = $('#paid_to_type').val();
                var key = $.trim($('#paidToSearchKey').val() || '');
                if (type !== 'loan_release' && key.length < 2) {
                    alert(i18n.minChars);
                    return;
                }
                runPaidToSearch(key);
            });
            $('#paidToSearchKey').on('keypress', function(e){
                if (e.which === 13) { e.preventDefault(); $('#doPaidToSearch').click(); }
            });

            function runPaidToSearch(key) {
                var type = $('#paid_to_type').val();
                var url = memberSearchUrl;
                var data = { key: key || '' };
                if (type === 'loan_release') {
                    url = pendingReleaseSearchUrl;
                    if (cashDisbursementId > 0) data.cash_disbursement_id = cashDisbursementId;
                } else if (type === 'supplier') url = supplierSearchUrl;
                else if (type === 'customer') url = customerSearchUrl;
                else if (type === 'member') url = memberSearchUrl;
                else return;

                $('#paidToSearchResults').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</p>');
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: data,
                    dataType: 'json',
                    success: function(response){
                        if (response.success === 'Y' && response.data && response.data.length > 0) {
                            if (type === 'loan_release') renderReleaseSearchResults(response.data);
                            else if (type === 'member') renderMemberSearchResults(response.data);
                            else if (type === 'supplier') renderSupplierSearchResults(response.data);
                            else if (type === 'customer') renderCustomerSearchResults(response.data);
                        } else {
                            var err = response.error || i18n.noMembers;
                            if (type === 'loan_release') err = response.error || i18n.noReleases;
                            if (type === 'supplier') err = response.error || i18n.noSuppliers;
                            if (type === 'customer') err = response.error || i18n.noCustomers;
                            $('#paidToSearchResults').html('<p class="text-danger text-center">' + err + '</p>');
                        }
                    },
                    error: function(){
                        $('#paidToSearchResults').html('<p class="text-danger text-center">Error searching. Please try again.</p>');
                    }
                });
            }

            function renderMemberSearchResults(rows) {
                var html = '<table class="table table-bordered table-hover"><thead><tr><th>Member ID</th><th>PID</th><th>Full Name</th><th></th></tr></thead><tbody>';
                $.each(rows, function(i, member){
                    html += '<tr><td>' + (member.member_id || '') + '</td><td>' + (member.PID || '') + '</td><td>' + (member.fullname || '') + '</td>';
                    html += '<td><button type="button" class="btn btn-sm btn-primary select-payee-member" data-pid="' + (member.PID || '') + '" data-member-id="' + (member.member_id || '') + '" data-fullname="' + String(member.fullname || '').replace(/"/g, '&quot;') + '"><i class="fa fa-check"></i> Select</button></td></tr>';
                });
                html += '</tbody></table>';
                $('#paidToSearchResults').html(html);
            }

            function renderSupplierSearchResults(rows) {
                var html = '<table class="table table-bordered table-hover"><thead><tr><th>Supplier No</th><th>Name</th><th></th></tr></thead><tbody>';
                $.each(rows, function(i, row){
                    html += '<tr><td>' + (row.supplierid || '') + '</td><td>' + (row.name || '') + '</td>';
                    html += '<td><button type="button" class="btn btn-sm btn-primary select-payee-name" data-fullname="' + String(row.name || '').replace(/"/g, '&quot;') + '"><i class="fa fa-check"></i> Select</button></td></tr>';
                });
                html += '</tbody></table>';
                $('#paidToSearchResults').html(html);
            }

            function renderCustomerSearchResults(rows) {
                var html = '<table class="table table-bordered table-hover"><thead><tr><th>Customer No</th><th>Name</th><th></th></tr></thead><tbody>';
                $.each(rows, function(i, row){
                    html += '<tr><td>' + (row.customerid || '') + '</td><td>' + (row.name || '') + '</td>';
                    html += '<td><button type="button" class="btn btn-sm btn-primary select-payee-name" data-fullname="' + String(row.name || '').replace(/"/g, '&quot;') + '"><i class="fa fa-check"></i> Select</button></td></tr>';
                });
                html += '</tbody></table>';
                $('#paidToSearchResults').html(html);
            }

            function normalizeRelease(row){
                row.full_name = $.trim([row.firstname || '', row.middlename || '', row.lastname || ''].join(' ').replace(/\s+/g, ' '));
                return row;
            }

            function renderReleaseSearchResults(rows) {
                var currentLid = String($('#loan_release_lid').val() || '');
                var allowed = [];
                $.each(rows || [], function(i, row){
                    var st = String(row.release_status || '').toLowerCase();
                    var lid = String(row.LID || '');
                    // Create-style pending only; on edit also keep the currently linked draft.
                    if (st === 'pending' || (st === 'draft' && currentLid && lid === currentLid)) {
                        allowed.push(row);
                    }
                });
                if (!allowed.length) {
                    $('#paidToSearchResults').html('<p class="text-danger text-center">' + i18n.noReleases + '</p>');
                    return;
                }
                var html = '<p class="help-block" style="margin-top:0;">' + i18n.releaseHint + '</p>';
                html += '<table class="table table-bordered table-hover"><thead><tr><th>Loan</th><th>Member</th><th>Release No</th><th>Product</th><th>Net Cash</th><th>Status</th><th></th></tr></thead><tbody>';
                $.each(allowed, function(i, row){
                    var release = normalizeRelease(row);
                    releaseCache[release.LID] = release;
                    var st = String(release.release_status || 'pending').toLowerCase();
                    var badge = st === 'pending' ? 'label-warning' : 'label-info';
                    html += '<tr><td>' + (release.LID || '') + '</td><td>' + (release.full_name || '') + '</td><td>' + (release.disburse_no || '') + '</td><td>' + (release.product_name || '') + '</td><td>' + (parseFloat(release.net_cash || 0).toFixed(2)) + '</td><td><span class="label ' + badge + '">' + st + '</span></td>';
                    html += '<td><button type="button" class="btn btn-sm btn-primary select-loan-release" data-release-id="' + (release.LID || '') + '"><i class="fa fa-check"></i> Select</button></td></tr>';
                });
                html += '</tbody></table>';
                $('#paidToSearchResults').html(html);
            }

            $(document).on('click', '.select-payee-member', function(){
                var name = $(this).data('fullname') || '';
                $('#paid_to').val(name);
                $('#member_pid').val($(this).data('pid') || '');
                $('#member_id').val($(this).data('member-id') || '');
                $('#loan_release_lid').val('');
                showLinkedRelease('', '');
                setSelectedPayeeLabel(name);
                $('#paidToSearchResults').html('');
            });

            $(document).on('click', '.select-payee-name', function(){
                var name = $(this).data('fullname') || '';
                $('#paid_to').val(name);
                $('#member_pid').val('');
                $('#member_id').val('');
                $('#loan_release_lid').val('');
                showLinkedRelease('', '');
                setSelectedPayeeLabel(name);
                $('#paidToSearchResults').html('');
            });

            $(document).on('click', '.select-loan-release', function(){
                var release = releaseCache[$(this).data('releaseId')];
                if (!release) return;
                $('#loan_release_lid').val(release.LID || '');
                $('#paid_to').val(release.full_name || '');
                $('#member_pid').val(release.PID || '');
                $('#member_id').val(release.member_id || '');
                setSelectedPayeeLabel((release.full_name || '') + ' — Loan ' + (release.LID || ''));
                showLinkedRelease(release.LID || '', release.full_name || '');
                $('#paidToSearchResults').html('');
            });

            updatePaidToUI(false);

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
                $('.debit-input').each(function(){ totalDebit += parseFloat($(this).val()) || 0; });
                $('.credit-input').each(function(){ totalCredit += parseFloat($(this).val()) || 0; });
                $('#total_debit').val(totalDebit.toFixed(2));
                $('#total_credit').val(totalCredit.toFixed(2));
                var diff = totalDebit - totalCredit;
                if (Math.abs(diff) < 0.01) { $('#balance_diff').text('').css('color', 'green'); }
                else { $('#balance_diff').text('Diff: ' + diff.toFixed(2)).css('color', 'red'); }
            }

            $('#cashDisbursementForm').on('submit', function(e){
                var type = $('#paid_to_type').val();
                if (type === 'miscellaneous') {
                    syncMiscPaidTo();
                }
                if (!type || !$.trim($('#paid_to').val() || '')) {
                    alert(i18n.payeeRequired);
                    e.preventDefault();
                    return false;
                }
                if (type === 'loan_release' && !$.trim($('#loan_release_lid').val() || '')) {
                    alert(i18n.releaseRequired);
                    e.preventDefault();
                    return false;
                }
                if ($('#cancelled').is(':checked')) {
                    return true;
                }
                var totalDebit = 0, totalCredit = 0, hasItems = false;
                $('.debit-input').each(function(){ totalDebit += parseFloat($(this).val()) || 0; });
                $('.credit-input').each(function(){ var v = parseFloat($(this).val()) || 0; totalCredit += v; if (v > 0) hasItems = true; });
                if (!hasItems) { $('.debit-input').each(function(){ if (parseFloat($(this).val()) > 0) hasItems = true; }); }
                if (!hasItems) {
                    alert('<?php echo lang('cash_disbursement_no_items'); ?>');
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
            
            if ($('#cancelled').is(':checked')) {
                $('#lineItemsTable th .required').hide();
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
