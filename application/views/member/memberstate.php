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
.member-list-page .result-meta strong {
    color: #1ab394;
}
.member-list-page .panel-body { padding: 18px; }
.member-list-page .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.member-list-page .filter-field {
    flex: 1 1 180px;
    min-width: 160px;
}
.member-list-page .filter-field.search-field { flex: 2 1 260px; position: relative; }
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
    width: 100%;
    table-layout: fixed;
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
    border-bottom: 1px solid #e7eaec !important;
    border-top: 0 !important;
    color: #5a5e63;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
    vertical-align: middle;
    white-space: normal;
    line-height: 1.25;
    padding: 8px 6px;
    position: sticky;
    top: 0;
    z-index: 2;
}
.member-list-page .member-table > tbody > tr > td {
    vertical-align: middle;
    padding: 6px 6px;
    color: #2f4050;
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
}
.member-list-page .member-table > thead > tr > th,
.member-list-page .member-table > tbody > tr > td,
.member-list-page .member-table > tfoot > tr > th,
.member-list-page .member-table > tfoot > tr > td {
    border: 1px solid #e7eaec !important;
}
.member-list-page .member-table > thead > tr > th {
    border-top: 0 !important;
}
.member-list-page .member-table > thead > tr > th:not(:first-child),
.member-list-page .member-table > tbody > tr > td:not(:first-child),
.member-list-page .member-table > tfoot > tr > th:not(:first-child),
.member-list-page .member-table > tfoot > tr > td:not(:first-child) {
    border-left: 0 !important;
}
.member-list-page .member-table col.col-pid { width: 11%; }
.member-list-page .member-table col.col-mid { width: 12%; }
.member-list-page .member-table col.col-name { width: 20%; }
.member-list-page .member-table col.col-amt { width: 11.4%; }
.member-list-page .name-cell {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.member-list-page .member-table > tbody > tr:nth-child(even):not(.totals-row) {
    background: #fcfdfd;
}
.member-list-page .member-table > tbody > tr:hover:not(.totals-row) {
    background: #f3fbf8 !important;
}
.member-list-page .member-table > tbody > tr.totals-row {
    background: linear-gradient(180deg, #f7fcfa 0%, #eef8f5 100%);
}
.member-list-page .member-table > tbody > tr.totals-row:hover {
    background: linear-gradient(180deg, #f7fcfa 0%, #eef8f5 100%);
}
.member-list-page .member-table > tbody > tr.totals-row td {
    border-top: 2px solid #c9ebe3;
    font-weight: 700;
    color: #2f4050;
    padding-top: 8px;
    padding-bottom: 8px;
}
.member-list-page .member-id-chip {
    display: inline-block;
    max-width: 100%;
    padding: 2px 6px;
    border-radius: 10px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 11px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
}
.member-list-page .amount-cell {
    text-align: right;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    font-weight: 600;
    font-size: 12px;
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
@media (max-width: 767px) {
    .member-list-page .filter-actions { width: 100%; }
    .member-list-page .filter-actions .btn { flex: 1; }
}
</style>

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
                <h4>Find Members</h4>
            </div>
        </div>
        <div class="panel-body">
            <?php echo form_open(current_lang() . "/member/member_current_state", 'class="form-horizontal" method="GET"'); ?>
            <div class="filter-row">
                <div class="filter-field search-field">
                    <label>Search</label>
                    <input type="text" id="member-state-search" class="form-control" name="key" value="<?php echo htmlspecialchars(isset($key) ? $key : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Name, Member ID..." autocomplete="off"/>
                    <div id="member-state-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                </div>
                <div class="filter-actions">
                    <a href="<?php echo site_url(current_lang() . '/member/member_current_state'); ?>" class="btn btn-default btn-clear-filter">
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
                <i class="fa fa-line-chart icon-badge"></i>
                <h4><?php echo lang('member_current_state'); ?></h4>
            </div>
            <?php
            $total_rows = isset($total_rows) ? (int) $total_rows : 0;
            $page_start = isset($page_start) ? (int) $page_start : 0;
            $per_page = isset($per_page) ? (int) $per_page : 0;
            $from = $total_rows > 0 ? ($page_start + 1) : 0;
            $to = $total_rows > 0 ? min($page_start + $per_page, $total_rows) : 0;
            ?>
            <div class="result-meta">
                Showing <strong><?php echo number_format($from); ?>-<?php echo number_format($to); ?></strong>
                of <strong><?php echo number_format($total_rows); ?></strong>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped member-table">
                <colgroup>
                    <col class="col-pid">
                    <col class="col-mid">
                    <col class="col-name">
                    <col class="col-amt">
                    <col class="col-amt">
                    <col class="col-amt">
                    <col class="col-amt">
                    <col class="col-amt">
                </colgroup>
                <thead>
                    <tr>
                        <th><?php echo lang('member_pid'); ?></th>
                        <th><?php echo lang('member_member_id'); ?></th>
                        <th><?php echo lang('contribution_member_name'); ?></th>
                        <th style="text-align:right;"><?php echo lang('member_current_contribution'); ?></th>
                        <th style="text-align:right;"><?php echo lang('member_current_share'); ?></th>
                        <th style="text-align:right;"><?php echo lang('member_current_loan'); ?></th>
                        <th style="text-align:right;"><?php echo lang('member_current_loan_payment'); ?></th>
                        <th style="text-align:right;"><?php echo lang('member_current_savings'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_loan = 0;
                    $total_share = 0;
                    $total_contribution = 0;
                    $total_loan_paid = 0;
                    $total_savings = 0;
                    $has_rows = !empty($member_state);
                    if ($has_rows) {
                        foreach ($member_state as $value) {
                            $share_row = $this->member_model->member_share_balance_by_member($value->PID, $value->member_id);
                            $share = 0;
                            if ($share_row && isset($share_row->totalshare) && is_numeric($share_row->totalshare)) {
                                $share = floatval($share_row->totalshare);
                            }
                            if (is_numeric($share)) {
                                $total_share += $share;
                                if ($share < 0) {
                                    $share_label = '(' . number_format((-1 * $share), 0) . ')';
                                } else {
                                    $share_label = number_format($share, 0);
                                }
                            } else {
                                $share_label = number_format(0, 0);
                            }

                            $contribution = $this->member_model->member_contribution_balance($value->PID)->balance;
                            if (is_numeric($contribution)) {
                                $total_contribution += $contribution;
                                if ($contribution < 0) {
                                    $contribution_label = '(' . number_format((-1 * $contribution), 2) . ')';
                                } else {
                                    $contribution_label = number_format($contribution, 2);
                                }
                            } else {
                                $contribution_label = number_format(0, 2);
                            }

                            $loan_row = $this->member_model->member_current_total_loan($value->PID);
                            $loan = isset($loan_row->total_loan) ? $loan_row->total_loan : null;
                            $loan_id = isset($loan_row->LID) ? $loan_row->LID : null;

                            $loan_bb_sum = $this->member_model->member_loan_beginning_balances_sum($value->member_id);
                            if (is_numeric($loan)) {
                                $loan = floatval($loan) + floatval($loan_bb_sum);
                            } else {
                                $loan = $loan_bb_sum;
                            }

                            if ($loan_id !== null && $loan_id !== '') {
                                $loan_paid_row = $this->member_model->member_current_loan_payment($loan_id);
                                $loan_paid = isset($loan_paid_row->total_paid_amount) ? $loan_paid_row->total_paid_amount : null;
                            } else {
                                $loan_paid = null;
                            }

                            if (is_numeric($loan)) {
                                $total_loan += $loan;
                                if ($loan < 0) {
                                    $loan_label = '(' . number_format((-1 * $loan), 2) . ')';
                                } else {
                                    $loan_label = number_format($loan, 2);
                                }
                            } else {
                                $loan_label = number_format(0, 2);
                            }

                            if (is_numeric($loan_paid)) {
                                $total_loan_paid += $loan_paid;
                                if ($loan_paid < 0) {
                                    $loan_paid_label = '(' . number_format((-1 * $loan_paid), 2) . ')';
                                } else {
                                    $loan_paid_label = number_format($loan_paid, 2);
                                }
                            } else {
                                $loan_paid_label = number_format(0, 2);
                            }

                            $saving = $this->finance_model->saving_account_balance_PID($value->PID, $value->member_id);
                            $savings_balance = ($saving && is_numeric($saving->balance)) ? $saving->balance : 0;
                            $total_savings += $savings_balance;
                            if ($savings_balance < 0) {
                                $savings_label = '(' . number_format((-1 * $savings_balance), 2) . ')';
                            } else {
                                $savings_label = number_format($savings_balance, 2);
                            }
                            ?>
                            <tr>
                                <td title="<?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td title="<?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?>"><span class="member-id-chip"><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <?php $full_name = trim($value->firstname . ' ' . $value->middlename . ' ' . $value->lastname); ?>
                                <td class="name-cell" title="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo $contribution_label; ?></td>
                                <td class="amount-cell"><?php echo $share_label; ?></td>
                                <td class="amount-cell"><?php echo $loan_label; ?></td>
                                <td class="amount-cell"><?php echo $loan_paid_label; ?></td>
                                <td class="amount-cell"><?php echo $savings_label; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr class="totals-row">
                            <td colspan="3" style="text-align: right;"><?php echo lang('total'); ?>:</td>
                            <td class="amount-cell"><?php echo number_format($total_contribution, 2); ?></td>
                            <td class="amount-cell"><?php echo number_format($total_share, 0); ?></td>
                            <td class="amount-cell"><?php echo number_format($total_loan, 2); ?></td>
                            <td class="amount-cell"><?php echo number_format($total_loan_paid, 2); ?></td>
                            <td class="amount-cell"><?php echo number_format($total_savings, 2); ?></td>
                        </tr>
                    <?php } else { ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fa fa-line-chart"></i>
                                    No members found for the current search.
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

<script>
(function() {
    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function initMemberStateSearchSuggest() {
        var input = document.getElementById('member-state-search');
        var box = document.getElementById('member-state-suggest-box');
        var form = input ? input.form : null;
        if (!input || !box || !form) {
            return;
        }

        // This page lists active members only (status=1, member type=1).
        var suggestUrl = '<?php echo site_url(current_lang() . '/member/autosuggest_member_list'); ?>';
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
            input.value = item.member_id || '';
            hideBox();
            form.submit();
        }

        function render(list) {
            items = list || [];
            activeIndex = items.length ? 0 : -1;
            if (!items.length) {
                box.innerHTML = '<div class="member-suggest-empty">No matching members</div>';
                box.classList.add('open');
                return;
            }

            var html = '';
            for (var i = 0; i < items.length; i++) {
                var row = items[i];
                html += '<button type="button" class="member-suggest-item' + (i === 0 ? ' active' : '') + '" data-index="' + i + '" role="option">' +
                    '<span class="suggest-id">' + escapeHtml(row.member_id) + '</span>' +
                    '<span class="suggest-name">' + escapeHtml(row.name) + '</span>' +
                    '<span class="suggest-status">' + escapeHtml(row.status) + '</span>' +
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

            var url = suggestUrl +
                '?q=' + encodeURIComponent(q) +
                '&searchstatus=1' +
                '&searchmember=1';

            if (window.jQuery) {
                xhr = jQuery.getJSON(url)
                    .done(function(data) {
                        render(data || []);
                    })
                    .fail(function(jqXHR, textStatus) {
                        if (textStatus !== 'abort') {
                            hideBox();
                        }
                    });
                return;
            }

            xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) {
                    return;
                }
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        render(JSON.parse(xhr.responseText || '[]'));
                    } catch (e) {
                        hideBox();
                    }
                } else if (xhr.status !== 0) {
                    hideBox();
                }
            };
            xhr.send();
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
                if (!suppressBlur) {
                    hideBox();
                }
                suppressBlur = false;
            }, 150);
        });

        box.addEventListener('mousedown', function() {
            suppressBlur = true;
        });

        box.addEventListener('click', function(e) {
            var btn = e.target;
            while (btn && btn !== box && !btn.classList.contains('member-suggest-item')) {
                btn = btn.parentNode;
            }
            if (!btn || !btn.classList.contains('member-suggest-item')) {
                return;
            }
            var idx = parseInt(btn.getAttribute('data-index'), 10);
            choose(items[idx]);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMemberStateSearchSuggest);
    } else {
        initMemberStateSearchSuggest();
    }
})();
</script>
