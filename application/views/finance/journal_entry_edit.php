<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<?php
if (!function_exists('journal_edit_link_entity')) {
    function journal_edit_link_entity($item) {
        $type = isset($item->link_type) ? strtolower(trim($item->link_type)) : '';
        if ($type === 'customer' || !empty($item->customerid)) {
            return !empty($item->customerid) ? $item->customerid : '';
        }
        if ($type === 'supplier' || !empty($item->supplierid)) {
            return !empty($item->supplierid) ? $item->supplierid : '';
        }
        if ($type === 'loan' || !empty($item->LID)) {
            return !empty($item->LID) ? $item->LID : '';
        }
        if ($type === 'savings' || !empty($item->savings_account)) {
            return !empty($item->savings_account) ? $item->savings_account : '';
        }
        if ($type === 'cbu' || (!empty($item->PID) && empty($item->LID) && empty($item->customerid) && empty($item->supplierid))) {
            return !empty($item->PID) ? $item->PID : '';
        }
        return '';
    }
}

if (!function_exists('journal_edit_link_type')) {
    function journal_edit_link_type($item) {
        if (!empty($item->link_type)) {
            return strtolower(trim($item->link_type));
        }
        if (!empty($item->customerid)) {
            return 'customer';
        }
        if (!empty($item->supplierid)) {
            return 'supplier';
        }
        if (!empty($item->LID)) {
            return 'loan';
        }
        if (!empty($item->savings_account)) {
            return 'savings';
        }
        if (!empty($item->PID)) {
            return 'cbu';
        }
        return '';
    }
}

$line_items = !empty($entry->line_items) ? $entry->line_items : array();
if (count($line_items) < 2) {
    while (count($line_items) < 2) {
        $line_items[] = (object) array(
            'account' => '',
            'description' => '',
            'debit' => '',
            'credit' => '',
            'link_type' => '',
            'customerid' => '',
            'supplierid' => '',
            'LID' => '',
        );
    }
}

$issue_date_value = set_value('issue_date', date('d-m-Y', strtotime($entry->entrydate)));
$document_no_value = set_value('document_no', isset($entry->document_no) ? $entry->document_no : '');
$description_value = set_value('description11', $entry->description);
?>
<?php echo form_open_multipart(current_lang() . '/finance/journal_entry_edit/' . $id, 'class="form-horizontal"'); ?>

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

<div class="ibox float-e-margins">
    <div class="ibox-title">
        <h5><?php echo lang('journal_entry_edit'); ?> - #<?php echo $entry->entryid; ?></h5>
        <div class="ibox-tools">
            <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_list'); ?>" class="btn btn-default btn-xs">
                <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
            </a>
        </div>
    </div>
    <div class="ibox-content">
        <div class="alert alert-info" style="margin-bottom: 20px;">
            <i class="fa fa-info-circle"></i> <?php echo lang('journal_entry_edit_note'); ?>
            <br><small><?php echo lang('journalentry_link_help'); ?></small>
        </div>

        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('journalentry_date'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-6">
                <div class="input-group date" id="datetimepicker">
                    <input type="text" name="issue_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo htmlspecialchars($issue_date_value); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
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
                       value="<?php echo isset($entry->reference_no) ? htmlspecialchars($entry->reference_no) : ''; ?>"
                       title="<?php echo lang('journalentry_reference_no_hint'); ?>"/>
                <span class="help-block" style="margin-bottom:0;"><?php echo lang('journalentry_reference_no_hint'); ?></span>
            </div>
        </div>

        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('journalentry_document_no'); ?> : <span class="required">*</span></label>
            <div class="col-lg-6">
                <input type="text" name="document_no" class="form-control" maxlength="100"
                       placeholder="<?php echo lang('journalentry_document_no_hint'); ?>"
                       value="<?php echo htmlspecialchars($document_no_value); ?>"/>
                <?php echo form_error('document_no'); ?>
            </div>
        </div>

        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('journalentry_description'); ?> : <span class="required">*</span> </label>
            <div class="col-lg-6">
                <textarea name="description11" class="form-control"><?php echo htmlspecialchars($description_value); ?></textarea>
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
                    $cbulist = isset($cbulist) ? $cbulist : array();
                    $cbu_account = isset($cbu_account) ? $cbu_account : '';
                    $savings_coa_list = isset($savings_coa_list) ? $savings_coa_list : array();
                    $savings_member_accounts = isset($savings_member_accounts) ? $savings_member_accounts : array();
                    $loan_receivable_coa_list = isset($loan_receivable_coa_list) ? $loan_receivable_coa_list : array();
                    $savings_accounts_by_coa_type = array();
                    if (!empty($savings_member_accounts)) {
                        foreach ($savings_member_accounts as $sa) {
                            $coa_key = isset($sa->coa_account) ? trim((string) $sa->coa_account) : '';
                            if ($coa_key === '') {
                                continue;
                            }
                            $type_name = !empty($sa->account_type_name) ? trim((string) $sa->account_type_name) : 'Savings';
                            if (!isset($savings_accounts_by_coa_type[$coa_key])) {
                                $savings_accounts_by_coa_type[$coa_key] = array();
                            }
                            if (!isset($savings_accounts_by_coa_type[$coa_key][$type_name])) {
                                $savings_accounts_by_coa_type[$coa_key][$type_name] = array();
                            }
                            $savings_accounts_by_coa_type[$coa_key][$type_name][] = $sa;
                        }
                    }
                    $loans_by_coa_product = array();
                    $loans_by_product = array();
                    if (!empty($loanlist)) {
                        foreach ($loanlist as $loan) {
                            $product_name = !empty($loan->product_name) ? trim((string) $loan->product_name) : 'Loan Product';
                            if (!isset($loans_by_product[$product_name])) {
                                $loans_by_product[$product_name] = array();
                            }
                            $loans_by_product[$product_name][] = $loan;
                            $coa_key = isset($loan->coa_account) ? trim((string) $loan->coa_account) : '';
                            if ($coa_key === '') {
                                continue;
                            }
                            if (!isset($loans_by_coa_product[$coa_key])) {
                                $loans_by_coa_product[$coa_key] = array();
                            }
                            if (!isset($loans_by_coa_product[$coa_key][$product_name])) {
                                $loans_by_coa_product[$coa_key][$product_name] = array();
                            }
                            $loans_by_coa_product[$coa_key][$product_name][] = $loan;
                        }
                    }
                    $row_n = 0;
                    foreach ($line_items as $item) {
                        $row_n++;
                        $lt = journal_edit_link_type($item);
                        $le = journal_edit_link_entity($item);
                        $debit_val = (isset($item->debit) && floatval($item->debit) > 0) ? number_format(floatval($item->debit), 2, '.', '') : '';
                        $credit_val = (isset($item->credit) && floatval($item->credit) > 0) ? number_format(floatval($item->credit), 2, '.', '') : '';
                        $desc_val = isset($item->description) ? $item->description : '';
                        $acct_val = isset($item->account) ? $item->account : '';
                        $show_cbu_link = ($cbu_account !== '' && (string) $acct_val === (string) $cbu_account);
                        $show_savings_link = (!$show_cbu_link && in_array((string) $acct_val, array_map('strval', $savings_coa_list), true));
                        $is_loan_receivable_row = in_array((string) $acct_val, array_map('strval', $loan_receivable_coa_list), true);
                    ?>
                    <tr>
                        <td>
                            <select class="form-control journal-account" name="account[]">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php foreach ($account_list as $key1 => $value1) { ?>
                                    <optgroup label="<?php echo htmlspecialchars($value1['info']->account . ' - ' . $value1['info']->name); ?>">
                                        <?php foreach ($value1['data'] as $key => $value) { ?>
                                            <option value="<?php echo $value->account; ?>" <?php echo ($acct_val == $value->account) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($value->account . ' - ' . $value->name); ?></option>
                                        <?php } ?>
                                    </optgroup>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <select class="form-control link-type" name="link_type[]">
                                <option value=""><?php echo lang('journalentry_link_none'); ?></option>
                                <option value="customer" <?php echo ($lt === 'customer') ? 'selected="selected"' : ''; ?>><?php echo lang('journalentry_link_customer'); ?></option>
                                <option value="supplier" <?php echo ($lt === 'supplier') ? 'selected="selected"' : ''; ?>><?php echo lang('journalentry_link_supplier'); ?></option>
                                <option value="loan" <?php echo ($lt === 'loan') ? 'selected="selected"' : ''; ?>><?php echo lang('journalentry_link_loan'); ?></option>
                                <?php if ($show_cbu_link || $lt === 'cbu') { ?>
                                <option value="cbu" <?php echo ($lt === 'cbu') ? 'selected="selected"' : ''; ?>><?php echo lang('journalentry_link_cbu'); ?></option>
                                <?php } ?>
                                <?php if ($show_savings_link || $lt === 'savings') { ?>
                                <option value="savings" <?php echo ($lt === 'savings') ? 'selected="selected"' : ''; ?>><?php echo lang('journalentry_link_savings'); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <select class="form-control link-entity" name="link_entity[]" <?php echo ($lt === '') ? 'disabled="disabled"' : ''; ?> data-selected="<?php echo htmlspecialchars($le); ?>">
                                <option value=""><?php echo lang('journalentry_link_select'); ?></option>
                                <?php if ($lt === 'customer' && !empty($customerlist)) {
                                    foreach ($customerlist as $c) { ?>
                                        <option value="<?php echo htmlspecialchars($c->customerid); ?>" <?php echo ($le == $c->customerid) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($c->customerid . ' : ' . $c->name); ?></option>
                                    <?php }
                                } elseif ($lt === 'supplier' && !empty($supplierlist)) {
                                    foreach ($supplierlist as $s) { ?>
                                        <option value="<?php echo htmlspecialchars($s->supplierid); ?>" <?php echo ($le == $s->supplierid) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($s->supplierid . ' : ' . $s->name); ?></option>
                                    <?php }
                                } elseif ($lt === 'loan' && !empty($loanlist)) {
                                    $loan_groups = ($is_loan_receivable_row && !empty($loans_by_coa_product[$acct_val]))
                                        ? $loans_by_coa_product[$acct_val]
                                        : $loans_by_product;
                                    foreach ($loan_groups as $product_name => $group_loans) { ?>
                                        <optgroup label="<?php echo htmlspecialchars($product_name); ?>">
                                        <?php foreach ($group_loans as $loan) {
                                            $loan_label = $loan->LID . ' : ' . (isset($loan->member_id) ? $loan->member_id . ' - ' : '')
                                                . trim((isset($loan->firstname) ? $loan->firstname : '') . ' ' . (isset($loan->lastname) ? $loan->lastname : ''));
                                            ?>
                                            <option value="<?php echo htmlspecialchars($loan->LID); ?>" <?php echo ($le == $loan->LID) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($loan_label); ?></option>
                                        <?php } ?>
                                        </optgroup>
                                    <?php }
                                } elseif ($lt === 'cbu' && !empty($cbulist)) {
                                    foreach ($cbulist as $cbu) {
                                        $cbu_label = (isset($cbu->member_id) ? $cbu->member_id . ' - ' : '')
                                            . trim((isset($cbu->firstname) ? $cbu->firstname : '') . ' ' . (isset($cbu->middlename) ? $cbu->middlename : '') . ' ' . (isset($cbu->lastname) ? $cbu->lastname : ''));
                                        ?>
                                        <option value="<?php echo htmlspecialchars($cbu->PID); ?>" <?php echo ((string) $le === (string) $cbu->PID) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($cbu_label); ?></option>
                                    <?php }
                                } elseif ($lt === 'savings' && !empty($savings_accounts_by_coa_type[$acct_val])) {
                                    foreach ($savings_accounts_by_coa_type[$acct_val] as $type_name => $type_accounts) { ?>
                                        <optgroup label="<?php echo htmlspecialchars($type_name); ?>">
                                        <?php foreach ($type_accounts as $sa) {
                                            $display_acct = !empty($sa->old_members_acct) ? ($sa->old_members_acct . ' / ' . $sa->account) : $sa->account;
                                            $sa_label = (isset($sa->member_id) ? $sa->member_id . ' - ' : '')
                                                . trim((isset($sa->firstname) ? $sa->firstname : '') . ' ' . (isset($sa->middlename) ? $sa->middlename : '') . ' ' . (isset($sa->lastname) ? $sa->lastname : ''))
                                                . ' [' . $display_acct . ']';
                                            ?>
                                            <option value="<?php echo htmlspecialchars($sa->account); ?>" <?php echo ((string) $le === (string) $sa->account) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($sa_label); ?></option>
                                        <?php } ?>
                                        </optgroup>
                                    <?php }
                                } ?>
                            </select>
                        </td>
                        <td><input type="text" name="description[]" class="form-control" value="<?php echo htmlspecialchars($desc_val); ?>"/></td>
                        <td><input onchange="debit_sum(this, <?php echo $row_n; ?>)" onkeyup="debit_sum(this, <?php echo $row_n; ?>)" type="text" name="debit[]" class="form-control amountformat debit" value="<?php echo htmlspecialchars($debit_val); ?>"/></td>
                        <td><input onchange="credit_sum(this, <?php echo $row_n; ?>)" onkeyup="credit_sum(this, <?php echo $row_n; ?>)" type="text" name="credit[]" class="form-control amountformat credit" value="<?php echo htmlspecialchars($credit_val); ?>"/></td>
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
                <input class="btn btn-primary" id="submitdata" value="<?php echo lang('journal_entry_update_btn'); ?>" type="submit"/>
                <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_list'); ?>" class="btn btn-default"><?php echo lang('cancel'); ?></a>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<?php
$account_options_html = '<option value="">' . htmlspecialchars(lang('select_default_text'), ENT_QUOTES) . '</option>';
foreach ($account_list as $key1 => $value1) {
    $account_options_html .= '<optgroup label="' . htmlspecialchars($value1['info']->account . ' - ' . $value1['info']->name, ENT_QUOTES) . '">';
    foreach ($value1['data'] as $key => $value) {
        $account_options_html .= '<option value="' . htmlspecialchars($value->account, ENT_QUOTES) . '">' . htmlspecialchars($value->account . ' - ' . $value->name, ENT_QUOTES) . '</option>';
    }
    $account_options_html .= '</optgroup>';
}

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
$loan_receivable_coa_list = isset($loan_receivable_coa_list) ? $loan_receivable_coa_list : array();
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

$savings_coa_list = isset($savings_coa_list) ? $savings_coa_list : array();
$savings_member_accounts = isset($savings_member_accounts) ? $savings_member_accounts : array();
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
                var cbuAccount = <?php echo json_encode((string) $cbu_account); ?>;
                var savingsCoaList = <?php echo json_encode(array_map('strval', $savings_coa_list)); ?>;
                var loanReceivableCoaList = <?php echo json_encode(array_map('strval', $loan_receivable_coa_list)); ?>;
                var cbuLinkOptionHtml = <?php echo json_encode($link_type_cbu_option_html); ?>;
                var savingsLinkOptionHtml = <?php echo json_encode($link_type_savings_option_html); ?>;
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

                window.refreshLinkEntity = function($row, selected) {
                    var type = $row.find('select.link-type').val() || '';
                    var account = $row.find('select.journal-account').val() || '';
                    var $entity = $row.find('select.link-entity');
                    var keep = (typeof selected !== 'undefined') ? selected : ($entity.data('selected') || $entity.val() || '');
                    var html = entityOptions[type] || entityOptions[''];
                    if (type === 'savings') {
                        html = savingsOptionsByCoa[String(account)] || emptyEntityHtml;
                    } else if (type === 'loan' && isLoanReceivableCoa(account)) {
                        html = loanOptionsByCoa[String(account)] || emptyEntityHtml;
                    }
                    $entity.html(html);
                    if (type === '') {
                        $entity.prop('disabled', true).val('');
                    } else {
                        $entity.prop('disabled', false);
                        if (keep) {
                            $entity.val(String(keep));
                        }
                    }
                    $entity.removeData('selected');
                };

                window.syncMemberSubledgerLink = function($row, autoSelect) {
                    if (typeof autoSelect === 'undefined') { autoSelect = true; }
                    var account = $row.find('select.journal-account').val() || '';
                    var $linkType = $row.find('select.link-type');
                    var current = $linkType.val() || '';
                    var $cbuOpt = $linkType.find('option[value="cbu"]');
                    var $savOpt = $linkType.find('option[value="savings"]');
                    var isCbuAccount = (cbuAccount !== '' && String(account) === String(cbuAccount));
                    var isSavingsAccount = isSavingsCoa(account) && !isCbuAccount;
                    var isLoanReceivable = isLoanReceivableCoa(account) && !isCbuAccount && !isSavingsAccount;
                    var keepEntity = $row.find('select.link-entity').data('selected') || $row.find('select.link-entity').val() || '';

                    if (isCbuAccount) {
                        if ($cbuOpt.length === 0) {
                            $linkType.append(cbuLinkOptionHtml);
                        }
                        if ($savOpt.length) {
                            $savOpt.remove();
                        }
                        if (autoSelect && (current === '' || current === 'savings' || current === 'loan')) {
                            $linkType.val('cbu');
                            window.refreshLinkEntity($row, keepEntity);
                            return;
                        }
                        if (current === 'cbu') {
                            window.refreshLinkEntity($row, keepEntity);
                        }
                    } else if ($cbuOpt.length) {
                        if (current === 'cbu') {
                            $linkType.val('');
                            current = '';
                            window.refreshLinkEntity($row, '');
                        }
                        $cbuOpt.remove();
                    }

                    if (isSavingsAccount) {
                        if ($linkType.find('option[value="savings"]').length === 0) {
                            $linkType.append(savingsLinkOptionHtml);
                        }
                        if (autoSelect && (current === '' || current === 'cbu' || current === 'loan')) {
                            $linkType.val('savings');
                            window.refreshLinkEntity($row, keepEntity);
                            return;
                        }
                        if ($linkType.val() === 'savings') {
                            window.refreshLinkEntity($row, keepEntity);
                        }
                    } else if ($linkType.find('option[value="savings"]').length) {
                        if ($linkType.val() === 'savings') {
                            $linkType.val('');
                            current = '';
                            window.refreshLinkEntity($row, '');
                        }
                        $linkType.find('option[value="savings"]').remove();
                    }

                    if (isLoanReceivable) {
                        if (autoSelect && (current === '' || current === 'cbu' || current === 'savings')) {
                            $linkType.val('loan');
                            window.refreshLinkEntity($row, keepEntity);
                            return;
                        }
                        if ($linkType.val() === 'loan') {
                            window.refreshLinkEntity($row, keepEntity);
                        }
                    } else if ($linkType.val() === 'loan') {
                        window.refreshLinkEntity($row, keepEntity);
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

                    $(document).on('change', 'select.journal-account', function() {
                        window.syncMemberSubledgerLink($(this).closest('tr'), true);
                    });

                    $("#submitdata").click(function() {
                        var debit1 = $("#open_debit").val();
                        var credit1 = $("#open_credit").val();
                        if (!isNaN(credit1) && credit1.length !== 0 && !isNaN(debit1) && debit1.length !== 0) {
                            if (diff == 0) {
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
                        var emptyEntityHtmlLocal = <?php echo json_encode($empty_entity_html); ?>;
                        var newRow = '<tr><td><select class="form-control journal-account" name="account[]">';
                        newRow += accountOptionsHtml;
                        newRow += '</select></td>';
                        newRow += '<td><select class="form-control link-type" name="link_type[]">' + linkTypeOptionsHtml + '</select></td>';
                        newRow += '<td><select class="form-control link-entity" name="link_entity[]" disabled="disabled">' + emptyEntityHtmlLocal + '</select></td>';
                        newRow += '<td><input type="text" name="description[]" class="form-control" /></td>';
                        newRow += '<td><input onchange="debit_sum(this,' + rowindex + ')" onkeyup="debit_sum(this, ' + rowindex + ')" type="text" name="debit[]" class="form-control amountformat debit" /></td>';
                        newRow += '<td><input onchange="credit_sum(this,' + rowindex + ')" onkeyup="credit_sum(this, ' + rowindex + ')" type="text" name="credit[]" class="form-control amountformat credit" /></td>';
                        newRow += '</tr>';

                        $('#quotetable tr:last').before(newRow);
                        return false;
                    });

                    // Initialize totals and hide opposite amount field for existing rows
                    $("input.debit").each(function(i) {
                        if (this.value && !isNaN(this.value.replace(/,/g, ''))) {
                            window.debit_sum(this, i + 1);
                        }
                    });
                    $("input.credit").each(function(i) {
                        if (this.value && !isNaN(this.value.replace(/,/g, ''))) {
                            window.credit_sum(this, i + 1);
                        }
                    });

                    $('#quotetable tbody tr').each(function() {
                        var $row = $(this);
                        if ($row.find('select.journal-account').length) {
                            window.syncMemberSubledgerLink($row, false);
                        }
                    });
                });
            }
        }
        initScripts();
    })();
</script>
