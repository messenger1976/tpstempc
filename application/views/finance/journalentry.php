<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/finance/journalentry/", 'class="form-horizontal"'); ?>

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

<div class="alert alert-info" style="margin-bottom: 20px;">
    <i class="fa fa-info-circle"></i> <strong>Note:</strong> Journal entries require approval before being posted to General Ledger. 
    After creating an entry, review and approve it from <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_review'); ?>" style="text-decoration: underline; font-weight: bold;"><strong>Journal Entry Review & Approval</strong></a>.
    <?php if (isset($unposted_count) && $unposted_count > 0): ?>
        <br><strong><?php echo $unposted_count; ?> entry/entries</strong> pending approval.
    <?php endif; ?>
    <br><small><?php echo lang('journalentry_link_help'); ?></small>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('journalentry_date'); ?>  : <span class="required">*</span></label>

    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker">
            <input type="text" name="issue_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('issue_date'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>

        <?php echo form_error('issue_date'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('journalentry_reference_no'); ?> :</label>
    <div class="col-lg-6">
        <input type="text" name="reference_no" id="reference_no" class="form-control" readonly="readonly"
               value="<?php echo isset($next_reference_no) ? htmlspecialchars($next_reference_no) : ''; ?>"
               title="<?php echo lang('journalentry_reference_no_hint'); ?>"/>
        <span class="help-block" style="margin-bottom:0;"><?php echo lang('journalentry_reference_no_hint'); ?></span>
        <?php echo form_error('reference_no'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('journalentry_document_no'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="document_no" class="form-control" maxlength="100"
               placeholder="<?php echo lang('journalentry_document_no_hint'); ?>"
               value="<?php echo set_value('document_no'); ?>"/>
        <?php echo form_error('document_no'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('journalentry_description'); ?> : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <textarea  name="description11"  class="form-control" ><?php echo set_value('description11'); ?></textarea>

        <?php echo form_error('description11'); ?>
    </div>
</div>
<div class="table-responsive">
    <table id="quotetable" class="table table-bordered ">
        <thead>
            <tr>
                <th style="width: 160px;"><?php echo lang('journalentry_account'); ?></th>
                <th style="width: 110px;"><?php echo lang('journalentry_link_type'); ?></th>
                <th style="width: 180px;"><?php echo lang('journalentry_link_entity'); ?></th>
                <th style="width: 140px;"><?php echo lang('journalentry_account_description'); ?></th>
                <th style="width: 90px;"><?php echo lang('journalentry_debit'); ?></th>
                <th style="width: 90px;"><?php echo lang('journalentry_credit'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $customerlist = isset($customerlist) ? $customerlist : array();
            $supplierlist = isset($supplierlist) ? $supplierlist : array();
            $loanlist = isset($loanlist) ? $loanlist : array();
            for ($row_n = 1; $row_n <= 2; $row_n++) {
            ?>
            <tr>
                <td>
                    <select class="form-control" name="account[]">
                        <option value=""><?php echo lang('select_default_text'); ?></option>
                        <?php foreach ($account_list as $key1 => $value1) { ?>
                            <optgroup label="<?php echo htmlspecialchars($value1['info']->name); ?>">
                                <?php foreach ($value1['data'] as $key => $value) { ?>
                                    <option value="<?php echo $value->account; ?>"><?php echo htmlspecialchars($value->name); ?></option>
                                <?php } ?>
                            </optgroup>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <select class="form-control link-type" name="link_type[]">
                        <option value=""><?php echo lang('journalentry_link_none'); ?></option>
                        <option value="customer"><?php echo lang('journalentry_link_customer'); ?></option>
                        <option value="supplier"><?php echo lang('journalentry_link_supplier'); ?></option>
                        <option value="loan"><?php echo lang('journalentry_link_loan'); ?></option>
                    </select>
                </td>
                <td>
                    <select class="form-control link-entity" name="link_entity[]" disabled="disabled">
                        <option value=""><?php echo lang('journalentry_link_select'); ?></option>
                    </select>
                </td>
                <td><input type="text" name="description[]" class="form-control"/></td>
                <td><input onchange="debit_sum(this, <?php echo $row_n; ?>)" onkeyup="debit_sum(this, <?php echo $row_n; ?>)" type="text" name="debit[]" class="form-control amountformat debit"/></td>
                <td><input onchange="credit_sum(this, <?php echo $row_n; ?>)" onkeyup="credit_sum(this, <?php echo $row_n; ?>)" type="text" name="credit[]" class="form-control amountformat credit"/></td>
            </tr>
            <?php } ?>
            <tr>
                <td><button onclick="return false;" class="btn btn-warning" id="addrow"><?php echo lang('add_row') ?></button></td>
                <td colspan="2"></td>
                <td id="diff" style="color: red; text-align: right;"></td>
                <td>
                    <input type="text" disabled="disabled" id="open_debit" class="form-control amountformat thisistotal_debit"/>
                    <input type="hidden" id="hidden_debit" name="summation_debit" class="form-control thisistotal_debit"/>
                </td>
                <td>
                    <input type="text" disabled="disabled" id="open_credit" class="form-control amountformat thisistotal_credit"/>
                    <input type="hidden" id="hidden_credit" name="summation_credit" class="form-control thisistotal_credit"/>
                </td>
            </tr>
        </tbody>
    </table>
</div>



<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" id="submitdata" value="<?php echo lang('record_addbtn'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>

<?php
// Build account options HTML for JavaScript
$account_options_html = '<option value="">' . htmlspecialchars(lang('select_default_text'), ENT_QUOTES) . '</option>';
foreach ($account_list as $key1 => $value1) {
    $account_options_html .= '<optgroup label="' . htmlspecialchars($value1['info']->name, ENT_QUOTES) . '">';
    foreach ($value1['data'] as $key => $value) {
        $account_options_html .= '<option value="' . htmlspecialchars($value->account, ENT_QUOTES) . '">' . htmlspecialchars($value->name, ENT_QUOTES) . '</option>';
    }
    $account_options_html .= '</optgroup>';
}

$link_type_options_html = '<option value="">' . htmlspecialchars(lang('journalentry_link_none'), ENT_QUOTES) . '</option>'
    . '<option value="customer">' . htmlspecialchars(lang('journalentry_link_customer'), ENT_QUOTES) . '</option>'
    . '<option value="supplier">' . htmlspecialchars(lang('journalentry_link_supplier'), ENT_QUOTES) . '</option>'
    . '<option value="loan">' . htmlspecialchars(lang('journalentry_link_loan'), ENT_QUOTES) . '</option>';

$empty_entity_html = '<option value="">' . htmlspecialchars(lang('journalentry_link_select'), ENT_QUOTES) . '</option>';

$customer_options_html = $empty_entity_html;
if (!empty($customerlist)) {
    foreach ($customerlist as $c) {
        $customer_options_html .= '<option value="' . htmlspecialchars($c->customerid, ENT_QUOTES) . '">'
            . htmlspecialchars($c->customerid . ' : ' . $c->name, ENT_QUOTES) . '</option>';
    }
}
$supplier_options_html = $empty_entity_html;
if (!empty($supplierlist)) {
    foreach ($supplierlist as $s) {
        $supplier_options_html .= '<option value="' . htmlspecialchars($s->supplierid, ENT_QUOTES) . '">'
            . htmlspecialchars($s->supplierid . ' : ' . $s->name, ENT_QUOTES) . '</option>';
    }
}
$loan_options_html = $empty_entity_html;
if (!empty($loanlist)) {
    foreach ($loanlist as $loan) {
        $loan_label = $loan->LID . ' : ' . (isset($loan->member_id) ? $loan->member_id . ' - ' : '')
            . trim((isset($loan->firstname) ? $loan->firstname : '') . ' ' . (isset($loan->lastname) ? $loan->lastname : ''));
        $loan_options_html .= '<option value="' . htmlspecialchars($loan->LID, ENT_QUOTES) . '">'
            . htmlspecialchars($loan_label, ENT_QUOTES) . '</option>';
    }
}
?>

<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script type="text/javascript">
    (function() {
        function initScripts() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initScripts, 50);
                return;
            }
            
            if (typeof $.fn.chosen === 'undefined') {
                var chosenScript = document.createElement('script');
                chosenScript.src = '<?php echo base_url() ?>media/js/chosen.jquery.js';
                chosenScript.onload = function() {
                    loadDatePicker();
                };
                document.head.appendChild(chosenScript);
            } else {
                loadDatePicker();
            }
            
            function loadDatePicker() {
                if (typeof $.fn.datetimepicker === 'undefined') {
                    var script = document.createElement('script');
                    script.src = '<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
                    script.onload = function() {
                        initMainScript();
                    };
                    document.head.appendChild(script);
                } else {
                    initMainScript();
                }
            }
            
            function initMainScript() {
                var diff = 0;
                var entityOptions = {
                    '': <?php echo json_encode($empty_entity_html); ?>,
                    customer: <?php echo json_encode($customer_options_html); ?>,
                    supplier: <?php echo json_encode($supplier_options_html); ?>,
                    loan: <?php echo json_encode($loan_options_html); ?>
                };

                window.refreshLinkEntity = function($row) {
                    var type = $row.find('select.link-type').val() || '';
                    var $entity = $row.find('select.link-entity');
                    $entity.html(entityOptions[type] || entityOptions['']);
                    if (type === '') {
                        $entity.prop('disabled', true).val('');
                    } else {
                        $entity.prop('disabled', false);
                    }
                };

                window.credit_sum = function(val, index) {
                    var sum = 0;
                    $("input.credit").each(function() {
                        var va = this.value.replace(/,/g, '');
                        this.value = va;
                        if (!isNaN(this.value) && this.value.length != 0) {
                            sum += parseFloat(this.value);
                        }
                    });
                    if (sum > 0) {
                        $("input.thisistotal_credit").val(sum.toFixed(2));
                    } else {
                        $("input.thisistotal_credit").val('');
                    }
                    if (!isNaN($(val).val()) && $(val).val().length != 0) {
                        $("#quotetable tbody tr:eq(" + (index - 1) + ") td:eq(4) input").hide();
                    } else {
                        $("#quotetable tbody tr:eq(" + (index - 1) + ") td:eq(4) input").show();
                    }
                    difference();
                };

                window.debit_sum = function(val, index) {
                    var sum = 0;
                    $("input.debit").each(function() {
                        var va = this.value.replace(/,/g, '');
                        this.value = va;
                        if (!isNaN(this.value) && this.value.length != 0) {
                            sum += parseFloat(this.value);
                        }
                    });
                    if (sum > 0) {
                        $("input.thisistotal_debit").val(sum.toFixed(2));
                    } else {
                        $("input.thisistotal_debit").val('');
                    }
                    if (!isNaN($(val).val()) && $(val).val().length != 0) {
                        $("#quotetable tbody tr:eq(" + (index - 1) + ") td:eq(5) input").hide();
                    } else {
                        $("#quotetable tbody tr:eq(" + (index - 1) + ") td:eq(5) input").show();
                    }
                    difference();
                };

                function difference() {
                    var debit1 = $("#open_debit").val();
                    var credit1 = $("#open_credit").val();
                    var debit = 0;
                    var credit = 0;
                    if (!isNaN(debit1) && debit1.length != 0) {
                        debit = parseFloat(debit1);
                    }
                    if (!isNaN(credit1) && credit1.length != 0) {
                        credit = parseFloat(credit1);
                    }
                    var dif = credit - debit;
                    if (dif != 0) {
                        diff = dif;
                        $("#diff").html(diff);
                    } else {
                        diff = 0;
                        $("#diff").html(0);
                    }
                }

                $(function() {
                    $('#datetimepicker').datetimepicker({
                        pickTime: false
                    });

                    $(document).on('change', 'select.link-type', function() {
                        window.refreshLinkEntity($(this).closest('tr'));
                    });

                    $("#submitdata").click(function() {
                        var debit1 = $("#open_debit").val();
                        var credit1 = $("#open_credit").val();
                        if (!isNaN(credit1) && credit1.length !== 0 && !isNaN(debit1) && debit1.length !== 0) {
                            if (diff == 0) {
                                // Re-enable disabled entity selects so empty values still post as arrays aligned by index
                                $('#quotetable select.link-entity:disabled').prop('disabled', false);
                                return true;
                            }
                            alert('Journal not balanced');
                            return false;
                        }
                        alert('Please fill form first');
                        return false;
                    });

                    $("#addrow").click(function() {
                        var rowindex = ($('#quotetable tbody tr').eq(-1).index() + 1);
                        var accountOptionsHtml = <?php echo json_encode($account_options_html); ?>;
                        var linkTypeOptionsHtml = <?php echo json_encode($link_type_options_html); ?>;
                        var emptyEntityHtml = <?php echo json_encode($empty_entity_html); ?>;
                        var newRow = '<tr><td><select class="form-control" name="account[]">';
                        newRow += accountOptionsHtml;
                        newRow += '</select></td>';
                        newRow += '<td><select class="form-control link-type" name="link_type[]">' + linkTypeOptionsHtml + '</select></td>';
                        newRow += '<td><select class="form-control link-entity" name="link_entity[]" disabled="disabled">' + emptyEntityHtml + '</select></td>';
                        newRow += '<td><input type="text" name="description[]" class="form-control" /></td>';
                        newRow += '<td><input onchange="debit_sum(this,' + rowindex + ')" onkeyup="debit_sum(this, ' + rowindex + ')" type="text" name="debit[]" class="form-control amountformat debit" /></td>';
                        newRow += '<td><input onchange="credit_sum(this,' + rowindex + ')" onkeyup="credit_sum(this, ' + rowindex + ')" type="text" name="credit[]" class="form-control amountformat credit" /></td>';
                        newRow += '</tr>';

                        $('#quotetable tr:last').before(newRow);
                        return false;
                    });
                });
            }
        }
        initScripts();
    })();
</script>
