<style type="text/css"> table tr td { line-height: 20px; } </style>
<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
</style>
<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} elseif ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} elseif (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} elseif ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}
$memberinfo = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
$product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
$interval = $this->setting_model->intervalinfo($loaninfo->interval)->row();
$basic_amount = isset($loaninfo->basic_amount) ? $loaninfo->basic_amount : 0;
$loan_principle_account = isset($loan_principle_account) ? $loan_principle_account : '';
$default_credit_account = isset($default_credit_account) ? $default_credit_account : '';
$default_payment_method_id = isset($default_payment_method_id) ? $default_payment_method_id : '';
$payment_method_credit_accounts = isset($payment_method_credit_accounts) ? $payment_method_credit_accounts : array();
?>
<div class="col-lg-12">
    <div class="panel panel-default">
        <div class="panel-heading"><h4><?php echo lang('member_basic_info'); ?></h4></div>
        <div class="panel-body">
            <table>
                <tr>
                    <td><img style="width: 100px; height: 100px;" src="<?php echo base_url(); ?>uploads/memberphoto/<?php echo $memberinfo->photo; ?>"/></td>
                    <td valign="top"><div style="padding-left: 30px;">
                        <strong><?php echo lang('member_firstname'); ?> : </strong> <?php echo $memberinfo->firstname; ?><br/>
                        <strong><?php echo lang('member_middlename'); ?> : </strong> <?php echo $memberinfo->middlename; ?><br/>
                        <strong><?php echo lang('member_lastname'); ?> : </strong> <?php echo $memberinfo->lastname; ?><br/>
                        <strong><?php echo lang('member_gender'); ?> : </strong> <?php echo $memberinfo->gender; ?><br/>
                        <strong><?php echo lang('member_dob'); ?> : </strong> <?php echo format_date($memberinfo->dob, FALSE); ?>
                        </div></td>
                    <td valign="top"><div style="padding-left: 40px;">
                        <strong><?php echo lang('member_pid'); ?> : </strong> <?php echo $memberinfo->PID; ?><br/>
                        <strong><?php echo lang('member_member_id'); ?> : </strong> <?php echo $memberinfo->member_id; ?><br/>
                        <strong><?php echo lang('member_join_date'); ?> : </strong> <?php echo format_date($memberinfo->joiningdate, FALSE); ?>
                        </div></td>
                    <td valign="top"><div style="padding-left: 40px;">
                        <?php $contribution = $this->contribution_model->contribution_balance($loaninfo->PID, $loaninfo->member_id); ?>
                        <strong><?php echo lang('contribution_balance'); ?> : </strong> <?php echo ($contribution ? number_format($contribution->balance, 2) : ''); ?><br/>
                        <?php $share_data = $this->share_model->share_member_info($loaninfo->PID, $loaninfo->member_id); ?>
                        <strong><?php echo lang('share_balance'); ?> : </strong> <?php echo ($share_data ? number_format(($share_data->amount + $share_data->remainbalance), 2) : ''); ?><br/>
                        <?php $saving = $this->finance_model->saving_account_balance_PID($loaninfo->PID, $loaninfo->member_id); ?>
                        <strong><?php echo lang('saving_balance'); ?> : </strong> <?php echo ($saving ? number_format($saving->balance, 2) : ''); ?>
                        </div></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><h4><?php echo lang('loan_info'); ?></h4></div>
        <div class="panel-body">
            <table>
                <tr>
                    <td valign="top"><div style="padding-left: 30px;">
                        <strong><?php echo lang('loan_product'); ?> : </strong> <?php echo $product->name; ?><br/>
                        <strong><?php echo lang('loanproduct_interest'); ?> : </strong> <?php echo $loaninfo->rate; ?><br/>
                        <strong><?php echo lang('loan_installment'); ?> : </strong> <?php echo $loaninfo->number_istallment . ' ' . $interval->name; ?><br/>
                        <strong><?php echo lang('loan_LID'); ?> : </strong> <?php echo $loaninfo->LID; ?>
                        </div></td>
                    <td valign="top"><div style="padding-left: 40px;">
                        <strong><?php echo lang('loan_applicationdate'); ?> : </strong> <?php echo format_date($loaninfo->applicationdate, FALSE); ?><br/>
                        <strong><?php echo lang('loan_installment_amount'); ?> : </strong> <?php echo number_format($loaninfo->installment_amount, 2); ?><br/>
                        <strong><?php echo lang('loan_total_interest'); ?> : </strong> <?php echo number_format($loaninfo->total_interest_amount, 2); ?><br/>
                        <strong><?php echo lang('loan_applied_amount'); ?> : </strong> <?php echo number_format($basic_amount, 2); ?><br/>
                        <strong><?php echo lang('loan_total'); ?> : </strong> <?php echo number_format($loaninfo->total_loan, 2); ?>
                        </div></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><h4><?php echo lang('loan_disburse_info'); ?></h4></div>
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
                            <?php foreach ((array)$payment_methods as $id => $name): ?>
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

            <h5><?php echo lang('loan_disburse_line_items'); ?></h5>
            <p class="text-muted"><?php echo lang('loan_disburse_line_help'); ?></p>
            <div class="table-responsive">
                <table id="lineItemsTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width:28%;"><?php echo lang('account_code'); ?> <span class="required">*</span></th>
                            <th style="width:22%;"><?php echo lang('journalentry_account_description'); ?></th>
                            <th style="width:18%;"><?php echo lang('journalentry_debit'); ?></th>
                            <th style="width:18%;"><?php echo lang('journalentry_credit'); ?></th>
                            <th style="width:14%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $default_lines = array(
                            array('account' => $loan_principle_account, 'debit' => $basic_amount, 'credit' => 0, 'desc' => 'Loan principal'),
                            array('account' => $default_credit_account, 'debit' => 0, 'credit' => $basic_amount, 'desc' => 'Disbursement source'),
                        );
                        foreach ($default_lines as $idx => $line):
                        ?>
                        <tr class="line-item">
                            <td>
                                <select class="form-control account-select" name="account[]">
                                    <option value=""><?php echo lang('select_default_text'); ?></option>
                                    <?php foreach ((array)$account_list as $key1 => $value1) { if (!isset($value1['info']) || !isset($value1['data'])) continue; ?>
                                        <optgroup label="<?php echo htmlspecialchars($value1['info']->name); ?>">
                                            <?php foreach ($value1['data'] as $key => $value) { ?>
                                                <option value="<?php echo $value->account; ?>" <?php echo ($line['account'] !== '' && $value->account == $line['account']) ? 'selected="selected"' : ''; ?>><?php echo $value->account . ' - ' . htmlspecialchars($value->name); ?></option>
                                            <?php } ?>
                                        </optgroup>
                                    <?php } ?>
                                </select>
                            </td>
                            <td><input type="text" name="line_description[]" class="form-control" value="<?php echo htmlspecialchars($line['desc']); ?>"/></td>
                            <td><input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input" value="<?php echo $line['debit']; ?>" placeholder="0.00"/></td>
                            <td><input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input" value="<?php echo $line['credit']; ?>" placeholder="0.00"/></td>
                            <td><button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo lang('delete'); ?>"><i class="fa fa-trash"></i></button></td>
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
            </div>
            <button type="button" class="btn btn-primary btn-sm" id="addLineItem"><i class="fa fa-plus"></i> <?php echo lang('add_row'); ?></button>

            <div class="form-group" style="margin-top: 15px;">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo lang('loan_evaluated_test'); ?></button>
                <a href="<?php echo site_url(current_lang() . '/loan/loan_disbursement'); ?>" class="btn btn-white"><?php echo lang('cancel'); ?></a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
(function(){
    var paymentMethodAccounts = <?php echo json_encode($payment_method_credit_accounts); ?>;
    var firstCreditAccount = <?php echo json_encode($default_credit_account); ?>;

    function loadScript(src, cb) {
        var s = document.createElement('script');
        s.src = src; s.onload = cb;
        document.head.appendChild(s);
    }
    function init() {
        if (typeof jQuery === 'undefined') {
            setTimeout(init, 50);
            return;
        }
        var $ = jQuery;

        function makeAccountSelectOptions(selectedAccount) {
            var opts = [];
            <?php foreach ((array)$account_list as $key1 => $value1) { if (!isset($value1['info']) || !isset($value1['data'])) continue; ?>
            opts.push('<optgroup label="<?php echo addslashes(htmlspecialchars($value1['info']->name)); ?>">');
            <?php foreach ($value1['data'] as $key => $value) { ?>
            opts.push('<option value="<?php echo $value->account; ?>"' + (selectedAccount === "<?php echo $value->account; ?>" ? ' selected' : '') + '><?php echo addslashes($value->account . ' - ' . $value->name); ?></option>');
            <?php } ?>
            opts.push('</optgroup>');
            <?php } ?>
            return opts.join('');
        }

        function addRow(account, debit, credit, desc) {
            var tbody = $('#lineItemsTable tbody');
            var html = '<tr class="line-item">' +
                '<td><select class="form-control account-select" name="account[]">' +
                '<option value=""><?php echo addslashes(lang('select_default_text')); ?></option>' + makeAccountSelectOptions(account || '') + '</select></td>' +
                '<td><input type="text" name="line_description[]" class="form-control" value="' + (desc || '') + '"/></td>' +
                '<td><input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input" value="' + (debit || '') + '" placeholder="0.00"/></td>' +
                '<td><input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input" value="' + (credit || '') + '" placeholder="0.00"/></td>' +
                '<td><button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo addslashes(lang('delete')); ?>"><i class="fa fa-trash"></i></button></td></tr>';
            tbody.append(html);
            updateTotals();
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

        $('#payment_method').on('change', function() {
            var id = $(this).val();
            var account = (paymentMethodAccounts && paymentMethodAccounts[id]) ? paymentMethodAccounts[id] : '';
            var $rows = $('#lineItemsTable tbody tr.line-item');
            if ($rows.length >= 2 && account) {
                var $secondRow = $rows.eq(1);
                $secondRow.find('.account-select').val(account);
            }
            updateTotals();
        });

        $('#addLineItem').on('click', function() {
            addRow('', '', '', '');
        });

        $(document).on('click', '.remove-line', function() {
            if ($('#lineItemsTable tbody tr.line-item').length > 1) {
                $(this).closest('tr').remove();
                updateTotals();
            }
        });

        $(document).on('keyup change', '.debit-input, .credit-input', function() {
            updateTotals();
        });

        $('#loanDisburseEntryForm').on('submit', function(e) {
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

        updateTotals();

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
