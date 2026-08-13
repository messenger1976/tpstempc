<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet"/>
<style>
.member-list-page { margin-top: 4px; }
.member-list-page .member-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.member-list-page .member-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.member-list-page .member-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.member-list-page .member-filter-panel,
.member-list-page .member-table-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.member-list-page .member-table-panel { overflow: hidden; }
.member-list-page .member-filter-panel .panel-body { overflow: visible; }
.member-list-page .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 18px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.member-list-page .panel-head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.member-list-page .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.member-list-page .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.member-list-page .result-meta {
    color: #888;
    font-size: 12px;
    font-weight: 600;
}
.member-list-page .result-meta strong { color: #1ab394; }
.member-list-page .panel-body { padding: 18px; }
.member-list-page .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.member-list-page .filter-field {
    flex: 1 1 150px;
    min-width: 140px;
}
.member-list-page .filter-field.search-field { flex: 2 1 240px; position: relative; }
.member-list-page .filter-field.date-field { flex: 0 1 190px; min-width: 170px; }
.member-list-page .date-field .input-group { width: 100%; }
.member-list-page .date-field .input-group-addon {
    background: #f8fafb;
    cursor: pointer;
}
.member-list-page .date-field .input-group-addon:hover { color: #1ab394; }
.member-list-page #date_from,
.member-list-page #date_upto { position: relative; z-index: 3; }
.member-list-page .filter-field label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #888;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.member-list-page .filter-field .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.member-list-page .filter-field .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.member-list-page .filter-actions {
    flex: 0 0 auto;
    display: flex;
    gap: 8px;
    align-items: center;
}
.member-list-page .filter-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 16px;
    height: 36px;
}
.member-list-page .filter-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
    box-shadow: none;
}
.member-list-page .filter-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.member-list-page .filter-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.member-list-page .member-suggest-box {
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
.member-list-page .member-suggest-box.open { display: block; }
.member-list-page .member-suggest-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 12px;
    border: 0;
    border-bottom: 1px solid #f0f2f3;
    background: #fff;
    text-align: left;
    cursor: pointer;
}
.member-list-page .member-suggest-item:last-child { border-bottom: 0; }
.member-list-page .member-suggest-item:hover,
.member-list-page .member-suggest-item.active { background: #e8f8f5; }
.member-list-page .member-suggest-item .suggest-id {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 11px;
    white-space: nowrap;
}
.member-list-page .member-suggest-item .suggest-name {
    flex: 1;
    font-weight: 600;
    color: #2f4050;
    font-size: 13px;
}
.member-list-page .member-suggest-item .suggest-status {
    color: #999;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.member-list-page .member-suggest-empty {
    padding: 12px;
    color: #999;
    font-size: 12px;
}
.member-list-page .member-table {
    margin: 0;
    background: #fff;
    border-collapse: separate;
    border-spacing: 0;
}
.member-list-page .table-responsive {
    max-height: 68vh;
    overflow: auto;
}
.member-list-page .member-table > thead > tr > th {
    background: linear-gradient(180deg, #fbfcfd 0%, #f4f7f8 100%);
    border: 1px solid #e7eaec !important;
    border-top: 0 !important;
    color: #5a5e63;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    vertical-align: middle;
    white-space: nowrap;
    padding: 13px 14px;
    position: sticky;
    top: 0;
    z-index: 2;
}
.member-list-page .member-table > tbody > tr > td {
    vertical-align: middle;
    border: 1px solid #e7eaec !important;
    padding: 12px 14px;
    color: #2f4050;
    font-size: 13px;
}
.member-list-page .member-table > thead > tr > th:not(:first-child),
.member-list-page .member-table > tbody > tr > td:not(:first-child) {
    border-left: 0 !important;
}
.member-list-page .member-table > tbody > tr:nth-child(even) { background: #fcfdfd; }
.member-list-page .member-table > tbody > tr:hover { background: #f3fbf8 !important; }
.member-list-page .member-id-chip {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 12px;
}
.member-list-page .amount-cell {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    white-space: nowrap;
}
.member-list-page .trans-pill,
.member-list-page .gl-pill,
.member-list-page .void-pill {
    display: inline-block;
    padding: 5px 11px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 700;
    color: #fff !important;
    margin: 2px 4px 2px 0;
}
.member-list-page .trans-pill.deposit { background: #1ab394; }
.member-list-page .trans-pill.withdrawal { background: #ed5565; }
.member-list-page .trans-pill.interest { background: #23c6c8; }
.member-list-page .trans-pill.other { background: #676a6c; }
.member-list-page .gl-pill.yes { background: #1ab394; }
.member-list-page .gl-pill.no { background: #f8ac59; }
.member-list-page .void-pill { background: #f8ac59; }
.member-list-page .void-pill.done { background: #ed5565; }
.member-list-page .action-btns {
    display: flex;
    flex-wrap: nowrap;
    gap: 6px;
    align-items: center;
}
.member-list-page .action-btns .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    min-width: 30px;
    height: 30px;
    padding: 0;
    border-radius: 6px;
    font-size: 0;
    line-height: 1;
    overflow: hidden;
}
.member-list-page .action-btns .btn i,
.member-list-page .action-btns .btn .fa {
    font-size: 13px;
    line-height: 1;
    margin: 0;
    width: auto;
}
.member-list-page .list-footer {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border-top: 1px solid #eef1f2;
    background: linear-gradient(180deg, #fbfcfd 0%, #f6f8f9 100%);
}
.member-list-page .list-footer .pagination,
.member-list-page .member-pagination {
    display: flex !important;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    border: 0 !important;
    border-top: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    background: transparent !important;
}
.member-list-page .member-pagination .link-pagination {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    margin: 0 !important;
    border: 1px solid #e1e5e8 !important;
    border-radius: 8px !important;
    background: #fff;
    overflow: hidden;
    transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
}
.member-list-page .member-pagination .link-pagination a {
    display: block;
    padding: 8px 12px !important;
    color: #676a6c !important;
    text-decoration: none !important;
    font-weight: 600;
    font-size: 13px;
    line-height: 1.2;
}
.member-list-page .member-pagination .link-pagination:hover {
    border-color: #1ab394 !important;
    box-shadow: 0 2px 8px rgba(26,179,148,0.15);
}
.member-list-page .member-pagination .link-pagination:hover a {
    color: #1ab394 !important;
    background: #e8f8f5;
}
.member-list-page .member-pagination .link-pagination.current {
    background: #1ab394 !important;
    border-color: #1ab394 !important;
    color: #fff !important;
    padding: 8px 12px !important;
    font-weight: 700;
    font-size: 13px;
    min-width: 36px;
    box-shadow: 0 2px 8px rgba(26,179,148,0.28);
}
.member-list-page .member-pagination .link-pagination.nav-btn a {
    color: #2f4050 !important;
}
.member-list-page .page-size-wrap {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    background: #fff;
    color: #676a6c;
    font-size: 12px;
    font-weight: 600;
}
.member-list-page .page-size-wrap #per_pg {
    width: 78px !important;
    height: 32px;
    padding: 4px 8px !important;
    border: 1px solid #e5e6e7;
    border-radius: 6px;
    background: #fafbfc !important;
    color: #2f4050;
    font-weight: 700;
    outline: none;
}
.member-list-page .page-size-wrap #per_pg:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.member-list-page .empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #999;
}
.member-list-page .empty-state i {
    font-size: 40px;
    color: #c9ebe3;
    margin-bottom: 12px;
    display: block;
}
.member-list-page .bootstrap-datetimepicker-widget { z-index: 1060 !important; }
@media (max-width: 767px) {
    .member-list-page .filter-actions { width: 100%; }
    .member-list-page .filter-actions .btn { flex: 1; }
}
</style>
<?php $this->load->view('loan/list_action_icon_script'); ?>

<?php
$sp = isset($jxy) && is_array($jxy) ? $jxy : array();
$key_val = (isset($sp['key']) && $sp['key'] !== '') ? $sp['key'] : '';
$from_val = (isset($sp['from']) && $sp['from'] !== '') ? format_date($sp['from'], FALSE) : '';
$upto_val = (isset($sp['upto']) && $sp['upto'] !== '') ? format_date($sp['upto'], FALSE) : '';
$selected_trans_type = isset($selected_trans_type) ? strtoupper($selected_trans_type) : 'ALL';
$account_type_filter = isset($account_type_filter) && $account_type_filter !== '' && $account_type_filter !== null
    ? $account_type_filter
    : 'all';
?>

<div class="col-lg-12 member-list-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="member-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="member-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="member-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="member-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="member-filter-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-search icon-badge"></i>
                <h4><?php echo lang('saving_transaction_search'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <?php echo form_open(current_lang() . "/saving/transaction_search", 'class="form-horizontal"'); ?>
                <div class="filter-row">
                    <div class="filter-field search-field">
                        <label><?php echo lang('account_no'); ?> / <?php echo lang('finance_account_name'); ?></label>
                        <input type="text" class="form-control" name="key" id="accountno" placeholder="<?php echo lang('account_no') . ' / ' . lang('finance_account_name'); ?>" value="<?php echo htmlspecialchars($key_val, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                        <div id="txn-suggest-box" class="member-suggest-box" role="listbox" aria-label="Account suggestions"></div>
                    </div>
                    <div class="filter-field date-field">
                        <label><?php echo lang('date_from'); ?></label>
                        <div class="input-group date" id="date_from">
                            <input type="text" class="form-control" id="from" data-date-format="DD-MM-YYYY" placeholder="<?php echo lang('hint_date'); ?>" name="from" value="<?php echo htmlspecialchars($from_val, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                            <span class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <div class="filter-field date-field">
                        <label><?php echo lang('date_to'); ?></label>
                        <div class="input-group date" id="date_upto">
                            <input type="text" class="form-control" id="upto" data-date-format="DD-MM-YYYY" placeholder="<?php echo lang('hint_date'); ?>" name="upto" value="<?php echo htmlspecialchars($upto_val, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                            <span class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <div class="filter-field">
                        <label><?php echo lang('account_type'); ?></label>
                        <select name="account_type_filter" class="form-control">
                            <option value="all" <?php echo ($account_type_filter == 'all' ? 'selected="selected"' : ''); ?>>All</option>
                            <option value="special" <?php echo ($account_type_filter == 'special' ? 'selected="selected"' : ''); ?>>Special</option>
                            <option value="mso" <?php echo ($account_type_filter == 'mso' ? 'selected="selected"' : ''); ?>>MSO</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label><?php echo lang('transaction_type'); ?></label>
                        <select name="trans_type" class="form-control">
                            <option value="ALL" <?php echo ($selected_trans_type == 'ALL' ? 'selected="selected"' : ''); ?>>ALL</option>
                            <option value="DEPOSIT" <?php echo ($selected_trans_type == 'DEPOSIT' ? 'selected="selected"' : ''); ?>>DEPOSIT</option>
                            <option value="WITHDRAWAL" <?php echo ($selected_trans_type == 'WITHDRAWAL' ? 'selected="selected"' : ''); ?>>WITHDRAWAL</option>
                            <option value="INTEREST" <?php echo ($selected_trans_type == 'INTEREST' ? 'selected="selected"' : ''); ?>>INTEREST</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo site_url(current_lang() . '/saving/transaction_reset'); ?>" class="btn btn-default btn-clear-filter">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> <?php echo lang('button_search'); ?>
                        </button>
                    </div>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-list icon-badge"></i>
                <h4><?php echo lang('saving_transaction_search'); ?></h4>
            </div>
            <?php
            $total_rows = isset($total_rows) ? (int) $total_rows : 0;
            $page_start = isset($page_start) ? (int) $page_start : 0;
            $per_page = isset($per_page) ? (int) $per_page : 0;
            $from_n = $total_rows > 0 ? ($page_start + 1) : 0;
            $to_n = $total_rows > 0 ? min($page_start + $per_page, $total_rows) : 0;
            ?>
            <div class="result-meta">
                Showing <strong><?php echo number_format($from_n); ?>-<?php echo number_format($to_n); ?></strong>
                of <strong><?php echo number_format($total_rows); ?></strong>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered member-table">
                <thead>
                    <tr>
                        <th style="text-align:center; width:60px;"><?php echo lang('sno'); ?></th>
                        <th><?php echo lang('index_receipt'); ?></th>
                        <th><?php echo lang('index_account'); ?></th>
                        <th><?php echo lang('index_name'); ?></th>
                        <th><?php echo lang('index_transtype'); ?></th>
                        <th><?php echo lang('index_source'); ?></th>
                        <th><?php echo lang('index_transmethod'); ?></th>
                        <th style="text-align:right;"><?php echo lang('index_amount'); ?></th>
                        <th><?php echo lang('index_trans_date'); ?></th>
                        <th><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactionlist)) { ?>
                        <?php
                        $index = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
                        $index++;
                        foreach ($transactionlist as $value) {
                            $trans_type_label = isset($value->trans_type_display) ? strtoupper(trim($value->trans_type_display)) : strtoupper(trim($value->trans_type));
                            $trans_pill_class = 'other';
                            if ($trans_type_label === 'DEPOSIT' || $trans_type_label === 'CR') {
                                $trans_pill_class = 'deposit';
                            } else if ($trans_type_label === 'WITHDRAWAL' || $trans_type_label === 'DR') {
                                $trans_pill_class = 'withdrawal';
                            } else if ($trans_type_label === 'INTEREST') {
                                $trans_pill_class = 'interest';
                            }
                            $account_no = !empty($value->account_no_display) ? $value->account_no_display : $value->account;
                            $holder_name = $this->finance_model->saving_account_name(isset($value->account) ? $value->account : '');
                            ?>
                            <tr>
                                <td style="text-align:center;"><?php echo $index++; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($value->receipt, ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if (isset($value->is_void_entry) && $value->is_void_entry) { ?>
                                        <br/>
                                        <span class="void-pill" title="This is a reversing entry">
                                            <i class="fa fa-reply"></i> Void of <?php echo htmlspecialchars($value->voided_receipt, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php } ?>
                                </td>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($account_no, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($holder_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="trans-pill <?php echo $trans_pill_class; ?>"><?php echo htmlspecialchars($trans_type_label, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars(isset($value->transaction_source) ? $value->transaction_source : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars(isset($value->paymethod) ? $value->paymethod : '', ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if (isset($value->is_void_entry) && $value->is_void_entry && !empty($value->void_original_method)) { ?>
                                        <br/>
                                        <span class="void-pill" title="Original method before void">
                                            <i class="fa fa-history"></i> Original: <?php echo htmlspecialchars($value->void_original_method, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php } ?>
                                </td>
                                <td class="amount-cell"><?php echo number_format(isset($value->amount) ? $value->amount : 0, 2); ?></td>
                                <td><?php echo (!empty($value->trans_date) ? date('m/d/Y', strtotime($value->trans_date)) : ''); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <?php echo anchor(current_lang() . "/saving/receipt_view/" . $value->receipt, ' <i class="fa fa-eye"></i> ' . lang('view_link'), 'class="btn btn-primary btn-xs"'); ?>
                                        <?php if (isset($value->is_gl_posted) && $value->is_gl_posted) { ?>
                                            <span class="gl-pill yes" title="Posted to General Ledger"><i class="fa fa-book"></i> GL Posted</span>
                                        <?php } else { ?>
                                            <span class="gl-pill no" title="Not yet posted to General Ledger"><i class="fa fa-book"></i> GL Not Posted</span>
                                            <?php if (has_role(3, 'saving_account_list')) { ?>
                                                <a href="<?php echo site_url(current_lang() . '/saving/post_receipt_to_gl/' . $value->receipt); ?>" class="btn btn-warning btn-xs post-transaction" data-receipt="<?php echo htmlspecialchars($value->receipt, ENT_QUOTES, 'UTF-8'); ?>" title="Post this transaction to GL">
                                                    <i class="fa fa-book"></i> Post to GL
                                                </a>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if (has_role(3, 'void_transaction') && !$value->is_void_entry) { ?>
                                            <?php if (isset($value->is_voided) && $value->is_voided) { ?>
                                                <span class="void-pill done" title="<?php echo lang('saving_void_transaction'); ?>">
                                                    <i class="fa fa-check-circle"></i> VOIDED
                                                </span>
                                            <?php } else { ?>
                                                <a href="<?php echo site_url(current_lang() . '/saving/void_transaction/' . $value->receipt); ?>" class="btn btn-danger btn-xs void-transaction" data-receipt="<?php echo htmlspecialchars($value->receipt, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo lang('saving_void_transaction'); ?>">
                                                    <i class="fa fa-ban"></i> <?php echo lang('void_link'); ?>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="fa fa-list"></i>
                                    <?php echo lang('no_records_found'); ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="list-footer">
            <div class="pagination-wrap"><?php echo $links; ?></div>
            <div class="page-size-wrap"><?php page_selector(); ?></div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>media/js/script/moment.js"></script>
<script type="text/javascript">
(function() {
    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function initDatePickers() {
        if (typeof jQuery === 'undefined' || typeof moment === 'undefined' || typeof jQuery.fn.datetimepicker === 'undefined') {
            setTimeout(initDatePickers, 50);
            return;
        }
        var $from = jQuery('#date_from');
        var $upto = jQuery('#date_upto');
        if ($from.length && !$from.data('DateTimePicker')) {
            $from.datetimepicker({ pickTime: false, format: 'DD-MM-YYYY' });
        }
        if ($upto.length && !$upto.data('DateTimePicker')) {
            $upto.datetimepicker({ pickTime: false, format: 'DD-MM-YYYY' });
        }
    }

    function initAccountSuggest() {
        var input = document.getElementById('accountno');
        var box = document.getElementById('txn-suggest-box');
        var form = input ? input.form : null;
        if (!input || !box || !form) {
            return;
        }

        var suggestUrl = '<?php echo site_url(current_lang() . '/saving/autosuggest_account_list'); ?>';
        var timer = null;
        var xhr = null;
        var items = [];
        var activeIndex = -1;
        var suppressBlur = false;

        function hideBox() {
            box.classList.remove('open');
            box.innerHTML = '';
            items = [];
            activeIndex = -1;
        }

        function setActive(index) {
            var nodes = box.querySelectorAll('.member-suggest-item');
            activeIndex = index;
            for (var i = 0; i < nodes.length; i++) {
                if (i === activeIndex) {
                    nodes[i].classList.add('active');
                    if (nodes[i].scrollIntoView) {
                        nodes[i].scrollIntoView({ block: 'nearest' });
                    }
                } else {
                    nodes[i].classList.remove('active');
                }
            }
        }

        function choose(item) {
            if (!item) {
                return;
            }
            input.value = item.account || item.display_account || '';
            hideBox();
            form.submit();
        }

        function render(list) {
            items = list || [];
            activeIndex = items.length ? 0 : -1;
            if (!items.length) {
                box.innerHTML = '<div class="member-suggest-empty">No matching accounts</div>';
                box.classList.add('open');
                return;
            }

            var html = '';
            for (var i = 0; i < items.length; i++) {
                var row = items[i];
                html += '<button type="button" class="member-suggest-item' + (i === 0 ? ' active' : '') + '" data-index="' + i + '" role="option">' +
                    '<span class="suggest-id">' + escapeHtml(row.display_account || row.account) + '</span>' +
                    '<span class="suggest-name">' + escapeHtml(row.name) + '</span>' +
                    '<span class="suggest-status">' + escapeHtml(row.type_label || '') + '</span>' +
                    '</button>';
            }
            box.innerHTML = html;
            box.classList.add('open');
        }

        function fetchSuggestions() {
            var q = input.value.replace(/^\s+|\s+$/g, '');
            if (q.length < 1) {
                hideBox();
                return;
            }
            if (xhr && typeof xhr.abort === 'function') {
                xhr.abort();
            }
            xhr = jQuery.getJSON(suggestUrl + '?q=' + encodeURIComponent(q))
                .done(function(data) { render(data || []); })
                .fail(function(jqXHR, textStatus) {
                    if (textStatus !== 'abort') hideBox();
                });
        }

        input.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(fetchSuggestions, 220);
        });

        input.addEventListener('keydown', function(e) {
            if (!box.classList.contains('open') || !items.length) {
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActive(Math.min(activeIndex + 1, items.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                choose(items[activeIndex]);
            } else if (e.key === 'Escape') {
                hideBox();
            }
        });

        input.addEventListener('blur', function() {
            setTimeout(function() {
                if (!suppressBlur) hideBox();
                suppressBlur = false;
            }, 150);
        });

        box.addEventListener('mousedown', function() { suppressBlur = true; });
        box.addEventListener('click', function(e) {
            var btn = e.target;
            while (btn && btn !== box && !btn.classList.contains('member-suggest-item')) {
                btn = btn.parentNode;
            }
            if (!btn || !btn.classList.contains('member-suggest-item')) {
                return;
            }
            choose(items[parseInt(btn.getAttribute('data-index'), 10)]);
        });
    }

    function initAlerts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initAlerts, 50);
            return;
        }
        jQuery(document).ready(function($) {
            $(document).on('click', '.void-transaction', function(e) {
                e.preventDefault();
                var voidUrl = $(this).attr('href');
                var receipt = $(this).data('receipt');
                var warning = <?php echo json_encode(lang('saving_void_transaction_warning')); ?>;
                if (typeof swal !== 'function') {
                    if (window.confirm(warning)) {
                        window.location.href = voidUrl;
                    }
                    return;
                }
                swal({
                    title: <?php echo json_encode(lang('saving_void_transaction')); ?>,
                    text: warning + (receipt ? ' (#' + receipt + ')' : ''),
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, void it!",
                    cancelButtonText: "Cancel",
                    closeOnConfirm: false
                }, function(isConfirm) {
                    if (isConfirm) {
                        window.location.href = voidUrl;
                    }
                });
            });

            $(document).on('click', '.post-transaction', function(e) {
                e.preventDefault();
                var postUrl = $(this).attr('href');
                var receipt = $(this).data('receipt');
                var confirmText = 'Post this transaction to GL now?';
                if (typeof swal !== 'function') {
                    if (window.confirm(confirmText)) {
                        window.location.href = postUrl;
                    }
                    return;
                }
                swal({
                    title: "Post to GL",
                    text: confirmText + (receipt ? ' (#' + receipt + ')' : ''),
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#1ab394",
                    confirmButtonText: "Yes, post it!",
                    cancelButtonText: "Cancel",
                    closeOnConfirm: false
                }, function(isConfirm) {
                    if (isConfirm) {
                        window.location.href = postUrl;
                    }
                });
            });
        });
    }

    if (document.readyState === 'complete') {
        initDatePickers();
    } else {
        window.addEventListener('load', initDatePickers);
        setTimeout(initDatePickers, 300);
    }
    initAccountSuggest();
    initAlerts();
})();
</script>
