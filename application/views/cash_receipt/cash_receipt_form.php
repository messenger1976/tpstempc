<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php
$account_list = isset($account_list) ? $account_list : array();
$payment_methods = isset($payment_methods) ? $payment_methods : array();
$next_receipt_no = isset($next_receipt_no) ? $next_receipt_no : '';
$list_url = site_url(current_lang() . '/cash_receipt/cash_receipt_list');
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
#memberSearchModal .modal-header {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
#memberSearchModal .modal-title { font-weight: 700; color: #2f4050; }
#memberSearchModal .table > thead > tr > th {
    background: #fafbfc;
    font-size: 12px;
    text-transform: uppercase;
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
                            <div class="input-group">
                                <input type="text" name="received_from" id="received_from" value="<?php echo htmlspecialchars(set_value('received_from'), ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required/>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary" id="searchMemberBtn" data-toggle="modal" data-target="#memberSearchModal">
                                        <i class="fa fa-search"></i> Search Member
                                    </button>
                                </span>
                            </div>
                            <input type="hidden" name="member_pid" id="member_pid" value=""/>
                            <input type="hidden" name="member_id" id="member_id" value=""/>
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

<div class="modal fade" id="memberSearchModal" tabindex="-1" role="dialog" aria-labelledby="memberSearchModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="memberSearchModalLabel">Search Member</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Search by Member ID, PID, or Name:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="memberSearchKey" placeholder="Enter member ID, PID, or name...">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="button" id="doMemberSearch">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </span>
                    </div>
                </div>
                <div id="memberSearchResults" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-muted text-center">Enter search keyword and click Search</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

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

            function addLineRow(account, debit, credit, desc) {
                var html = '<tr class="line-item">' +
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
            $('#payment_method').on('change', toggleChequeDetails);
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
            var arAccountUrl = '<?php echo site_url(current_lang() . '/cash_receipt/get_ar_account'); ?>';

            $('#doMemberSearch').on('click', function(){
                var key = $('#memberSearchKey').val().trim();
                if (key.length < 2) {
                    alert('Please enter at least 2 characters to search');
                    return;
                }
                searchMembers(key);
            });

            $('#memberSearchKey').on('keypress', function(e){
                if (e.which === 13) { e.preventDefault(); $('#doMemberSearch').click(); }
            });

            function searchMembers(key){
                $('#memberSearchResults').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Searching...</p>');
                $.ajax({
                    url: memberSearchUrl,
                    type: 'GET',
                    data: { key: key },
                    dataType: 'json',
                    success: function(response){
                        if (response.success === 'Y' && response.data && response.data.length > 0) {
                            var html = '<table class="table table-bordered table-hover">';
                            html += '<thead><tr><th>Member ID</th><th>PID</th><th>Full Name</th><th>Action</th></tr></thead><tbody>';
                            $.each(response.data, function(i, member){
                                html += '<tr><td>' + (member.member_id || '') + '</td><td>' + (member.PID || '') + '</td><td>' + (member.fullname || '') + '</td>';
                                html += '<td><button type="button" class="btn btn-sm btn-primary select-member" data-pid="' + (member.PID || '') + '" data-member-id="' + (member.member_id || '') + '" data-fullname="' + (member.fullname || '') + '"><i class="fa fa-check"></i> Select</button></td></tr>';
                            });
                            html += '</tbody></table>';
                            $('#memberSearchResults').html(html);
                        } else {
                            $('#memberSearchResults').html('<p class="text-danger text-center">' + (response.error || 'No members found') + '</p>');
                        }
                    },
                    error: function(){
                        $('#memberSearchResults').html('<p class="text-danger text-center">Error searching members. Please try again.</p>');
                    }
                });
            }

            $(document).on('click', '.select-member', function(){
                var pid = $(this).data('pid');
                var memberId = $(this).data('member-id');
                var fullname = $(this).data('fullname');
                $('#received_from').val(fullname);
                $('#member_pid').val(pid);
                $('#member_id').val(memberId);
                $('#memberSearchModal').modal('hide');
                addARAccountEntry(fullname);
            });

            function addARAccountEntry(memberName){
                $.ajax({
                    url: arAccountUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response){
                        if (response.success === 'Y' && response.account) {
                            var arExists = false;
                            $('.account-select').each(function(){
                                if ($(this).val() === response.account) {
                                    arExists = true;
                                    return false;
                                }
                            });
                            if (!arExists) {
                                addLineRow(response.account, '', '', 'AR - ' + memberName);
                                alert('Accounts Receivable account (' + response.name + ') has been added. Please enter the credit amount.');
                            } else {
                                alert('Accounts Receivable account already exists in line items.');
                            }
                        } else {
                            console.log('AR account not found: ' + (response.error || ''));
                        }
                    },
                    error: function(){
                        console.log('Error fetching AR account');
                    }
                });
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
