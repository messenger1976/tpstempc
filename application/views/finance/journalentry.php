<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php
$account_list = isset($account_list) ? $account_list : array();
$customerlist = isset($customerlist) ? $customerlist : array();
$supplierlist = isset($supplierlist) ? $supplierlist : array();
$loanlist = isset($loanlist) ? $loanlist : array();
$cbulist = isset($cbulist) ? $cbulist : array();
$cbu_account = isset($cbu_account) ? $cbu_account : '';
$savings_coa_list = isset($savings_coa_list) ? $savings_coa_list : array();
$savings_member_accounts = isset($savings_member_accounts) ? $savings_member_accounts : array();
$loan_receivable_coa_list = isset($loan_receivable_coa_list) ? $loan_receivable_coa_list : array();
$next_reference_no = isset($next_reference_no) ? $next_reference_no : '';
$unposted_count = isset($unposted_count) ? (int) $unposted_count : 0;
$list_url = site_url(current_lang() . '/finance/journal_entry_list');
$review_url = site_url(current_lang() . '/finance/journal_entry_review');
?>

<style type="text/css">
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
.select2-container--default .select2-results__option[aria-disabled=true]{color:#222;cursor:default;}
.select2-container--default .select2-results__option .coa-bold{font-weight:bold;color:#111;}
.select2-container{width:100%!important;}

.je-create-page { margin-top: 4px; }
.je-create-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.je-create-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.je-create-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.je-create-page .cbu-alert.info {
    background: #eef3fb;
    color: #3c6eae;
    border: 1px solid #d6e2f5;
    font-weight: 500;
}
.je-create-page .cbu-alert.info a {
    color: #1ab394;
    font-weight: 700;
    text-decoration: underline;
}
.je-create-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.je-create-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.je-create-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.je-create-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.je-create-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.je-create-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.je-create-page .form-horizontal .form-group { margin-bottom: 16px; }
.je-create-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.je-create-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.je-create-page textarea.form-control { height: auto; min-height: 80px; }
.je-create-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.je-create-page .form-control[readonly] {
    background: #f8fafb;
    font-weight: 700;
}
.je-create-page .help-block {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
    margin-bottom: 0;
}
.je-create-page .required { color: #ed5565; }
.je-create-page .input-group-addon {
    background: #f8fafb;
    cursor: pointer;
    color: #1ab394;
}
.je-create-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.je-create-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.je-create-page .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.je-create-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.je-create-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.je-create-page .select2-container--default.select2-container--focus .select2-selection--single,
.je-create-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}
.je-create-page .lines-table {
    margin: 0;
    background: #fff;
}
.je-create-page .lines-table > thead > tr > th {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
    vertical-align: middle;
}
.je-create-page .lines-table > tbody > tr > td,
.je-create-page .lines-table > tfoot > tr > td {
    vertical-align: middle;
}
.je-create-page .lines-table .amount-cell input {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-weight: 600;
}
.je-create-page .balance-diff {
    font-weight: 700;
    font-size: 13px;
    color: #ed5565;
}
.je-create-page .line-actions { margin-top: 12px; }
.je-create-page .form-actions {
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
</style>

<select id="coaOptionsSource" style="display:none;">
    <option value=""><?php echo lang('select_default_text'); ?></option>
    <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => '')); ?>
</select>

<?php echo form_open_multipart(current_lang() . "/finance/journalentry/", 'class="form-horizontal" id="journalEntryForm"'); ?>

<div class="col-lg-12 je-create-page">
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

    <?php if (function_exists('gl_books_close_alert_html')) { echo gl_books_close_alert_html(); } ?>

    <div class="cbu-alert info">
        <i class="fa fa-info-circle"></i>
        <strong>Note:</strong> Journal entries require approval before being posted to General Ledger.
        After creating an entry, review and approve it from
        <?php if (has_role(6, 'Review_journal_entry')) { ?>
            <a href="<?php echo $review_url; ?>"><?php echo lang('journal_entry_review'); ?></a>.
        <?php } else { ?>
            <strong><?php echo lang('journal_entry_review'); ?></strong>.
        <?php } ?>
        <?php if ($unposted_count > 0) { ?>
            <br><strong><?php echo $unposted_count; ?> entry/entries</strong> pending approval.
        <?php } ?>
        <br><small><?php echo lang('journalentry_link_help'); ?></small>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-book icon-badge"></i>
                <h4><?php echo lang('journalentry'); ?></h4>
            </div>
            <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('journalentry_date'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <div class="input-group date" id="datetimepicker">
                                <input type="text" name="issue_date" placeholder="<?php echo lang('hint_date'); ?>"
                                       value="<?php echo htmlspecialchars(set_value('issue_date', date('d-m-Y')), ENT_QUOTES, 'UTF-8'); ?>"
                                       data-date-format="dd-mm-yyyy" class="form-control" autocomplete="off"/>
                                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                            </div>
                            <?php echo form_error('issue_date'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('journalentry_reference_no'); ?> :</label>
                        <div class="col-lg-8">
                            <input type="text" name="reference_no" id="reference_no" class="form-control" readonly="readonly"
                                   value="<?php echo htmlspecialchars($next_reference_no, ENT_QUOTES, 'UTF-8'); ?>"
                                   title="<?php echo htmlspecialchars(lang('journalentry_reference_no_hint'), ENT_QUOTES, 'UTF-8'); ?>"/>
                            <span class="help-block"><?php echo lang('journalentry_reference_no_hint'); ?></span>
                            <?php echo form_error('reference_no'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('journalentry_document_no'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="document_no" class="form-control" maxlength="100"
                                   placeholder="<?php echo htmlspecialchars(lang('journalentry_document_no_hint'), ENT_QUOTES, 'UTF-8'); ?>"
                                   value="<?php echo htmlspecialchars(set_value('document_no'), ENT_QUOTES, 'UTF-8'); ?>"/>
                            <?php echo form_error('document_no'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('journalentry_description'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <textarea name="description11" class="form-control" rows="3"><?php echo htmlspecialchars(set_value('description11'), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <?php echo form_error('description11'); ?>
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
                <h4><?php echo lang('journalentry_account'); ?> Lines</h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="quotetable" class="table table-striped lines-table">
                    <thead>
                        <tr>
                            <th style="width: 22%;"><?php echo lang('journalentry_account'); ?></th>
                            <th style="width: 12%;"><?php echo lang('journalentry_link_type'); ?></th>
                            <th style="width: 18%;"><?php echo lang('journalentry_link_entity'); ?></th>
                            <th style="width: 18%;"><?php echo lang('journalentry_account_description'); ?></th>
                            <th style="width: 12%;"><?php echo lang('journalentry_debit'); ?></th>
                            <th style="width: 12%;"><?php echo lang('journalentry_credit'); ?></th>
                            <th style="width: 6%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($row_n = 1; $row_n <= 2; $row_n++) { ?>
                            <tr class="line-item">
                                <td>
                                    <select class="form-control journal-account account-select" name="account[]">
                                        <option value=""><?php echo lang('select_default_text'); ?></option>
                                        <?php $this->load->view('finance/partials/coa_select_options', array('account_list' => $account_list, 'selected_account' => '')); ?>
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
                                <td class="amount-cell">
                                    <input type="text" name="debit[]" class="form-control amountformat debit"/>
                                </td>
                                <td class="amount-cell">
                                    <input type="text" name="credit[]" class="form-control amountformat credit"/>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo htmlspecialchars(lang('delete'), ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                            <td class="amount-cell">
                                <input type="text" disabled="disabled" id="open_debit" class="form-control amountformat thisistotal_debit"/>
                                <input type="hidden" id="hidden_debit" name="summation_debit" class="form-control thisistotal_debit"/>
                            </td>
                            <td class="amount-cell">
                                <input type="text" disabled="disabled" id="open_credit" class="form-control amountformat thisistotal_credit"/>
                                <input type="hidden" id="hidden_credit" name="summation_credit" class="form-control thisistotal_credit"/>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4" id="diff" class="text-right balance-diff"></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="line-actions">
                <button type="button" class="btn btn-primary" id="addrow">
                    <i class="fa fa-plus"></i> <?php echo lang('add_row'); ?>
                </button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submitdata">
                    <i class="fa fa-save"></i> <?php echo lang('record_addbtn'); ?>
                </button>
                <a href="<?php echo $list_url; ?>" class="btn btn-default">
                    <i class="fa fa-undo"></i> <?php echo lang('cancel'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<?php
$link_type_options_html = '<option value="">' . htmlspecialchars(lang('journalentry_link_none'), ENT_QUOTES) . '</option>'
    . '<option value="customer">' . htmlspecialchars(lang('journalentry_link_customer'), ENT_QUOTES) . '</option>'
    . '<option value="supplier">' . htmlspecialchars(lang('journalentry_link_supplier'), ENT_QUOTES) . '</option>'
    . '<option value="loan">' . htmlspecialchars(lang('journalentry_link_loan'), ENT_QUOTES) . '</option>';
$link_type_cbu_option_html = '<option value="cbu">' . htmlspecialchars(lang('journalentry_link_cbu'), ENT_QUOTES) . '</option>';
$link_type_savings_option_html = '<option value="savings">' . htmlspecialchars(lang('journalentry_link_savings'), ENT_QUOTES) . '</option>';

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
$loan_options_by_coa = array();
$loan_options_by_product = array();
if (!empty($loanlist)) {
    foreach ($loanlist as $loan) {
        $loan_label = $loan->LID . ' : ' . (isset($loan->member_id) ? $loan->member_id . ' - ' : '')
            . trim((isset($loan->firstname) ? $loan->firstname : '') . ' ' . (isset($loan->lastname) ? $loan->lastname : ''));
        $loan_opt = '<option value="' . htmlspecialchars($loan->LID, ENT_QUOTES) . '">'
            . htmlspecialchars($loan_label, ENT_QUOTES) . '</option>';
        $product_name = !empty($loan->product_name) ? trim((string) $loan->product_name) : 'Loan Product';
        if (!isset($loan_options_by_product[$product_name])) {
            $loan_options_by_product[$product_name] = '';
        }
        $loan_options_by_product[$product_name] .= $loan_opt;

        $coa_key = isset($loan->coa_account) ? trim((string) $loan->coa_account) : '';
        if ($coa_key !== '') {
            if (!isset($loan_options_by_coa[$coa_key])) {
                $loan_options_by_coa[$coa_key] = array();
            }
            if (!isset($loan_options_by_coa[$coa_key][$product_name])) {
                $loan_options_by_coa[$coa_key][$product_name] = '';
            }
            $loan_options_by_coa[$coa_key][$product_name] .= $loan_opt;
        }
    }
    foreach ($loan_options_by_product as $product_name => $opts) {
        $loan_options_html .= '<optgroup label="' . htmlspecialchars($product_name, ENT_QUOTES) . '">' . $opts . '</optgroup>';
    }
}
foreach ($loan_options_by_coa as $coa_key => $by_product) {
    $html = $empty_entity_html;
    foreach ($by_product as $product_name => $opts) {
        $html .= '<optgroup label="' . htmlspecialchars($product_name, ENT_QUOTES) . '">' . $opts . '</optgroup>';
    }
    $loan_options_by_coa[$coa_key] = $html;
}
if (!empty($loan_receivable_coa_list)) {
    foreach ($loan_receivable_coa_list as $coa_code) {
        $coa_code = trim((string) $coa_code);
        if ($coa_code !== '' && !isset($loan_options_by_coa[$coa_code])) {
            $loan_options_by_coa[$coa_code] = $empty_entity_html;
        }
    }
}
$cbu_options_html = $empty_entity_html;
if (!empty($cbulist)) {
    foreach ($cbulist as $cbu) {
        $cbu_label = (isset($cbu->member_id) ? $cbu->member_id . ' - ' : '')
            . trim((isset($cbu->firstname) ? $cbu->firstname : '') . ' ' . (isset($cbu->middlename) ? $cbu->middlename : '') . ' ' . (isset($cbu->lastname) ? $cbu->lastname : ''));
        $cbu_options_html .= '<option value="' . htmlspecialchars($cbu->PID, ENT_QUOTES) . '">'
            . htmlspecialchars($cbu_label, ENT_QUOTES) . '</option>';
    }
}

$savings_options_by_coa = array();
$savings_grouped = array();
if (!empty($savings_member_accounts)) {
    foreach ($savings_member_accounts as $sa) {
        $coa_key = isset($sa->coa_account) ? trim((string) $sa->coa_account) : '';
        if ($coa_key === '') {
            continue;
        }
        $type_name = !empty($sa->account_type_name) ? trim((string) $sa->account_type_name) : 'Savings';
        if (!isset($savings_grouped[$coa_key])) {
            $savings_grouped[$coa_key] = array();
        }
        if (!isset($savings_grouped[$coa_key][$type_name])) {
            $savings_grouped[$coa_key][$type_name] = '';
        }
        $display_acct = !empty($sa->old_members_acct) ? ($sa->old_members_acct . ' / ' . $sa->account) : $sa->account;
        $sa_label = (isset($sa->member_id) ? $sa->member_id . ' - ' : '')
            . trim((isset($sa->firstname) ? $sa->firstname : '') . ' ' . (isset($sa->middlename) ? $sa->middlename : '') . ' ' . (isset($sa->lastname) ? $sa->lastname : ''))
            . ' [' . $display_acct . ']';
        $savings_grouped[$coa_key][$type_name] .= '<option value="' . htmlspecialchars($sa->account, ENT_QUOTES) . '">'
            . htmlspecialchars($sa_label, ENT_QUOTES) . '</option>';
    }
}
foreach ($savings_grouped as $coa_key => $by_type) {
    $html = $empty_entity_html;
    foreach ($by_type as $type_name => $opts) {
        $html .= '<optgroup label="' . htmlspecialchars($type_name, ENT_QUOTES) . '">' . $opts . '</optgroup>';
    }
    $savings_options_by_coa[$coa_key] = $html;
}
if (!empty($savings_coa_list)) {
    foreach ($savings_coa_list as $coa_code) {
        $coa_code = trim((string) $coa_code);
        if ($coa_code !== '' && !isset($savings_options_by_coa[$coa_code])) {
            $savings_options_by_coa[$coa_code] = $empty_entity_html;
        }
    }
}
?>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
(function(){
    function loadScript(src, cb, fallback){
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        if (fallback) {
            s.onerror = function(){ loadScript(fallback, cb); };
        }
        document.head.appendChild(s);
    }

    function initOnceReady(){
        if (!window.jQuery) {
            setTimeout(initOnceReady, 50);
            return;
        }
        var $ = window.jQuery;
        var diff = 0;
        var cbuAccount = <?php echo json_encode((string) $cbu_account); ?>;
        var savingsCoaList = <?php echo json_encode(array_map('strval', $savings_coa_list)); ?>;
        var loanReceivableCoaList = <?php echo json_encode(array_map('strval', $loan_receivable_coa_list)); ?>;
        var cbuLinkOptionHtml = <?php echo json_encode($link_type_cbu_option_html); ?>;
        var savingsLinkOptionHtml = <?php echo json_encode($link_type_savings_option_html); ?>;
        var linkTypeOptionsHtml = <?php echo json_encode($link_type_options_html); ?>;
        var emptyEntityHtml = <?php echo json_encode($empty_entity_html); ?>;
        var entityOptions = {
            '': <?php echo json_encode($empty_entity_html); ?>,
            customer: <?php echo json_encode($customer_options_html); ?>,
            supplier: <?php echo json_encode($supplier_options_html); ?>,
            loan: <?php echo json_encode($loan_options_html); ?>,
            cbu: <?php echo json_encode($cbu_options_html); ?>
        };
        var savingsOptionsByCoa = <?php echo json_encode($savings_options_by_coa); ?>;
        var loanOptionsByCoa = <?php echo json_encode($loan_options_by_coa); ?>;

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

        function destroyLinkEntitySelect($el) {
            if ($el && $el.length && $el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
        }

        function initLinkEntitySelect($el) {
            if (!$el || !$el.length || !$.fn.select2) { return; }
            destroyLinkEntitySelect($el);
            $el.select2({
                width: '100%',
                placeholder: <?php echo json_encode(lang('journalentry_link_select')); ?>,
                allowClear: true
            });
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

        function listIncludes(list, account) {
            var acct = String(account || '');
            if (!acct || !list || !list.length) { return false; }
            for (var i = 0; i < list.length; i++) {
                if (String(list[i]) === acct) { return true; }
            }
            return false;
        }

        function isSavingsCoa(account) {
            return listIncludes(savingsCoaList, account);
        }

        function isLoanReceivableCoa(account) {
            return listIncludes(loanReceivableCoaList, account);
        }

        function refreshLinkEntity($row, selected) {
            var type = $row.find('select.link-type').val() || '';
            var account = $row.find('select.journal-account').val() || '';
            var $entity = $row.find('select.link-entity');
            var keep = (typeof selected !== 'undefined') ? selected : ($entity.val() || '');
            var html = entityOptions[type] || entityOptions[''];
            if (type === 'savings') {
                html = savingsOptionsByCoa[String(account)] || emptyEntityHtml;
            } else if (type === 'loan' && isLoanReceivableCoa(account)) {
                html = loanOptionsByCoa[String(account)] || emptyEntityHtml;
            }
            destroyLinkEntitySelect($entity);
            $entity.html(html);
            if (type === '') {
                $entity.prop('disabled', true).val('');
            } else {
                $entity.prop('disabled', false);
                if (keep) {
                    $entity.val(String(keep));
                }
            }
            initLinkEntitySelect($entity);
        }

        function syncMemberSubledgerLink($row, autoSelect) {
            if (typeof autoSelect === 'undefined') { autoSelect = true; }
            var account = $row.find('select.journal-account').val() || '';
            var $linkType = $row.find('select.link-type');
            var current = $linkType.val() || '';
            var $cbuOpt = $linkType.find('option[value="cbu"]');
            var $savOpt = $linkType.find('option[value="savings"]');
            var isCbuAccount = (cbuAccount !== '' && String(account) === String(cbuAccount));
            var isSavingsAccount = isSavingsCoa(account) && !isCbuAccount;
            var isLoanReceivable = isLoanReceivableCoa(account) && !isCbuAccount && !isSavingsAccount;

            if (isCbuAccount) {
                if ($cbuOpt.length === 0) {
                    $linkType.append(cbuLinkOptionHtml);
                }
                if ($savOpt.length) {
                    $savOpt.remove();
                }
                if (autoSelect && (current === '' || current === 'savings' || current === 'loan')) {
                    $linkType.val('cbu');
                    refreshLinkEntity($row, '');
                    return;
                }
            } else if ($cbuOpt.length) {
                if (current === 'cbu') {
                    $linkType.val('');
                    current = '';
                    refreshLinkEntity($row, '');
                }
                $cbuOpt.remove();
            }

            if (isSavingsAccount) {
                if ($savOpt.length === 0) {
                    $linkType.append(savingsLinkOptionHtml);
                }
                if (autoSelect && (current === '' || current === 'cbu' || current === 'loan')) {
                    $linkType.val('savings');
                    refreshLinkEntity($row, '');
                    return;
                }
                if (current === 'savings') {
                    refreshLinkEntity($row);
                }
            } else if ($linkType.find('option[value="savings"]').length) {
                if ($linkType.val() === 'savings') {
                    $linkType.val('');
                    current = '';
                    refreshLinkEntity($row, '');
                }
                $linkType.find('option[value="savings"]').remove();
            }

            if (isLoanReceivable) {
                if (autoSelect && (current === '' || current === 'cbu' || current === 'savings')) {
                    $linkType.val('loan');
                    refreshLinkEntity($row, '');
                    return;
                }
                if ($linkType.val() === 'loan') {
                    refreshLinkEntity($row);
                }
            } else if ($linkType.val() === 'loan') {
                refreshLinkEntity($row);
            }
        }

        function updateRemoveButtons() {
            var count = $('#quotetable tbody tr.line-item').length;
            $('.remove-line').prop('disabled', count <= 2);
        }

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
                $("#diff").html('Diff: ' + dif.toFixed(2));
            } else {
                diff = 0;
                $("#diff").html('');
            }
        }

        function credit_sum($input) {
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
            var $row = $input.closest('tr');
            var val = $input.val();
            if (!isNaN(val) && val.length != 0) {
                $row.find('input.debit').hide();
            } else {
                $row.find('input.debit').show();
            }
            difference();
        }

        function debit_sum($input) {
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
            var $row = $input.closest('tr');
            var val = $input.val();
            if (!isNaN(val) && val.length != 0) {
                $row.find('input.credit').hide();
            } else {
                $row.find('input.credit').show();
            }
            difference();
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

        ensureBootstrapDP(initPicker);
        $('.account-select').each(function(){ initAccountSelect($(this)); });
        $('select.link-entity').each(function(){ initLinkEntitySelect($(this)); });
        updateRemoveButtons();

        $(document).on('change', 'select.link-type', function() {
            refreshLinkEntity($(this).closest('tr'));
        });

        $(document).on('change', 'select.journal-account', function() {
            syncMemberSubledgerLink($(this).closest('tr'), true);
        });

        $(document).on('keyup change', 'input.debit', function() {
            debit_sum($(this));
        });

        $(document).on('keyup change', 'input.credit', function() {
            credit_sum($(this));
        });

        $('#addrow').on('click', function() {
            var html = '<tr class="line-item">' +
                '<td><select class="form-control journal-account account-select" name="account[]">' + makeAccountSelectHtml('') + '</select></td>' +
                '<td><select class="form-control link-type" name="link_type[]">' + linkTypeOptionsHtml + '</select></td>' +
                '<td><select class="form-control link-entity" name="link_entity[]" disabled="disabled">' + emptyEntityHtml + '</select></td>' +
                '<td><input type="text" name="description[]" class="form-control"/></td>' +
                '<td class="amount-cell"><input type="text" name="debit[]" class="form-control amountformat debit"/></td>' +
                '<td class="amount-cell"><input type="text" name="credit[]" class="form-control amountformat credit"/></td>' +
                '<td><button type="button" class="btn btn-danger btn-xs remove-line" title="<?php echo addslashes(lang('delete')); ?>"><i class="fa fa-trash"></i></button></td>' +
                '</tr>';
            var $row = $(html);
            $('#quotetable tbody').append($row);
            initAccountSelect($row.find('.account-select'));
            initLinkEntitySelect($row.find('select.link-entity'));
            updateRemoveButtons();
            return false;
        });

        $(document).on('click', '.remove-line', function() {
            if ($('#quotetable tbody tr.line-item').length <= 2) {
                return;
            }
            var $row = $(this).closest('tr');
            destroyAccountSelect($row.find('.account-select'));
            destroyLinkEntitySelect($row.find('select.link-entity'));
            $row.remove();
            updateRemoveButtons();
            $("input.debit").first().trigger('change');
            $("input.credit").first().trigger('change');
        });

        $('#journalEntryForm').on('submit', function(e) {
            var debit1 = $("#open_debit").val();
            var credit1 = $("#open_credit").val();
            if (!isNaN(credit1) && credit1.length !== 0 && !isNaN(debit1) && debit1.length !== 0) {
                if (diff == 0) {
                    $('#quotetable select.link-entity').each(function() {
                        var $el = $(this);
                        destroyLinkEntitySelect($el);
                        $el.prop('disabled', false);
                    });
                    return true;
                }
                alert('Journal not balanced');
                e.preventDefault();
                return false;
            }
            alert('Please fill form first');
            e.preventDefault();
            return false;
        });
    }

    initOnceReady();
})();
</script>
