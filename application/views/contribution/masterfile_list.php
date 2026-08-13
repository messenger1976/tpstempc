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
.member-list-page .status-pill {
    display: inline-block;
    padding: 5px 11px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 700;
    color: #fff !important;
}
.member-list-page .status-pill.active { background: #1ab394; }
.member-list-page .status-pill.inactive { background: #f8ac59; }
.member-list-page .action-btns .btn {
    border-radius: 5px;
    font-weight: 600;
    padding: 4px 10px;
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
#cbuLedgerOverlay .modal-header {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
#cbuLedgerOverlay .modal-title {
    font-size: 16px;
    font-weight: 700;
    color: #2f4050;
}
#cbuLedgerOverlay .modal-title .modal-icon {
    color: #1ab394;
    margin-right: 6px;
}
@media (max-width: 767px) {
    .member-list-page .filter-actions { width: 100%; }
    .member-list-page .filter-actions .btn { flex: 1; }
}
</style>

<!-- CBU Ledger popup: plain overlay so it never depends on the Bootstrap modal plugin -->
<div id="cbuLedgerOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10500; overflow:auto;">
    <div style="background:#fff; max-width:900px; margin:40px auto; border-radius:10px; box-shadow:0 8px 28px rgba(0,0,0,0.25); overflow:hidden;">
        <div class="modal-content" style="box-shadow:none; border:0;">
            <div class="modal-header">
                <button type="button" class="close cbu-ledger-close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title"><i class="fa fa-book modal-icon"></i> <?php echo lang('cbu_ledger_title'); ?></h2>
            </div>
            <div class="modal-body">
                <div id="cbu-ledger-loading" style="text-align: center; padding: 20px;">
                    <i class="fa fa-spinner fa-spin fa-3x" style="color:#1ab394;"></i>
                    <p><?php echo lang('cbu_ledger_loading'); ?></p>
                </div>
                <div id="cbu-ledger-content" style="display: none;">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-6">
                            <p><strong><?php echo lang('member_pid'); ?>:</strong> <span id="cbu-ledger-pid"></span></p>
                            <p><strong><?php echo lang('member_member_id'); ?>:</strong> <span id="cbu-ledger-member-id"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><?php echo lang('contribution_member_name'); ?>:</strong> <span id="cbu-ledger-name"></span></p>
                            <p><strong><?php echo lang('balance'); ?>:</strong> <span id="cbu-ledger-balance"></span></p>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 420px; overflow: auto;">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 110px;"><?php echo lang('index_trans_date'); ?></th>
                                    <th><?php echo lang('comment'); ?></th>
                                    <th style="text-align: right; width: 120px;"><?php echo lang('debit'); ?></th>
                                    <th style="text-align: right; width: 120px;"><?php echo lang('credit'); ?></th>
                                    <th style="text-align: right; width: 130px;"><?php echo lang('balance'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="cbu-ledger-rows">
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold;">
                                    <td colspan="2" style="text-align: right;"><?php echo lang('total'); ?></td>
                                    <td style="text-align: right;" id="cbu-ledger-total-debit">0.00</td>
                                    <td style="text-align: right;" id="cbu-ledger-total-credit">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="cbu-ledger-empty" style="display: none; padding: 20px; text-align: center;">
                        <p class="text-muted"><?php echo lang('cbu_ledger_empty'); ?></p>
                    </div>
                </div>
                <div id="cbu-ledger-error" style="display: none; padding: 20px; text-align: center;">
                    <p class="text-danger"><i class="fa fa-exclamation-triangle"></i> <span id="cbu-ledger-error-message"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default cbu-ledger-close"><?php echo lang('button_close'); ?></button>
            </div>
        </div>
    </div>
</div>

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
                <h4>Find CBU Masterfile</h4>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?php echo site_url(current_lang() . "/contribution/masterfile_list"); ?>" method="get" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field search-field">
                        <label>Search Member</label>
                        <input type="text" class="form-control" id="accountno" name="key" value="<?php echo htmlspecialchars(isset($key) ? $key : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="PID, Member ID or Name..." autocomplete="off"/>
                        <div id="masterfile-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                    </div>
                    <div class="filter-field">
                        <label><?php echo lang('member_status'); ?></label>
                        <select name="status" class="form-control">
                            <option value=""><?php echo lang('all'); ?> <?php echo lang('member_status'); ?></option>
                            <option value="1" <?php echo (isset($status) && $status === '1') ? 'selected' : ''; ?>><?php echo lang('member_active'); ?></option>
                            <option value="0" <?php echo (isset($status) && $status === '0') ? 'selected' : ''; ?>><?php echo lang('member_inactive'); ?></option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo site_url(current_lang() . '/contribution/masterfile_list'); ?>" class="btn btn-default btn-clear-filter">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> <?php echo lang('button_search'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-book icon-badge"></i>
                <h4><?php echo lang('contribution_masterfile_list'); ?></h4>
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
            <table class="table table-striped table-bordered member-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 60px;"><?php echo lang('sno'); ?></th>
                        <th><?php echo lang('member_pid'); ?></th>
                        <th><?php echo lang('member_member_id'); ?></th>
                        <th><?php echo lang('contribution_member_name'); ?></th>
                        <th style="text-align: right;"><?php echo lang('balance'); ?></th>
                        <th style="text-align: center;"><?php echo lang('member_status'); ?></th>
                        <th><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($masterfile_list)) { ?>
                        <?php
                        $index = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
                        $index++;
                        foreach ($masterfile_list as $value) {
                            $name = trim($value->firstname . ' ' . $value->middlename . ' ' . $value->lastname);
                            $is_active = ((string) $value->member_status === '1');
                            ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $index++; ?></td>
                                <td><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format((float) $value->balance, 2); ?></td>
                                <td style="text-align: center;">
                                    <?php if ($is_active) { ?>
                                        <span class="status-pill active"><?php echo lang('member_active'); ?></span>
                                    <?php } else { ?>
                                        <span class="status-pill inactive"><?php echo lang('member_inactive'); ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button type="button" class="btn btn-primary btn-xs btn-cbu-ledger"
                                           data-pid="<?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?>"
                                           data-memberid="<?php echo htmlspecialchars(trim($value->member_id), ENT_QUOTES, 'UTF-8'); ?>"
                                           data-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-th-list"></i> <?php echo lang('cbu_ledger'); ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fa fa-book"></i>
                                    No masterfile records found for the current filters.
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

<script type="text/javascript">
(function() {
    var LEDGER_URL = '<?php echo site_url(current_lang() . '/contribution/cbu_ledger_ajax'); ?>';
    var SUGGEST_URL = '<?php echo site_url(current_lang() . '/member/autosuggest_member_list'); ?>';

    function el(id) {
        return document.getElementById(id);
    }

    function show(id, visible) {
        var node = el(id);
        if (node) {
            node.style.display = visible ? 'block' : 'none';
        }
    }

    function setText(id, text) {
        var node = el(id);
        if (node) {
            node.textContent = text;
        }
    }

    function escapeHtml(text) {
        return String(text === null || typeof text === 'undefined' ? '' : text)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function closeLedger() {
        show('cbuLedgerOverlay', false);
    }

    function findLedgerButton(node) {
        while (node && node !== document) {
            if (node.className && String(node.className).indexOf('btn-cbu-ledger') !== -1) {
                return node;
            }
            node = node.parentNode;
        }
        return null;
    }

    function renderLedger(response, pid, memberId, name) {
        show('cbu-ledger-loading', false);

        if (!response || !response.success) {
            show('cbu-ledger-error', true);
            setText('cbu-ledger-error-message', (response && response.message) ? response.message : 'Failed to load ledger.');
            return;
        }

        setText('cbu-ledger-pid', response.pid || pid);
        setText('cbu-ledger-member-id', response.member_id || memberId);
        setText('cbu-ledger-name', response.member_name || name);
        setText('cbu-ledger-balance', response.current_balance || '0.00');
        setText('cbu-ledger-total-debit', response.total_debit || '0.00');
        setText('cbu-ledger-total-credit', response.total_credit || '0.00');

        var rows = response.rows || [];
        if (rows.length > 0) {
            var html = '';
            for (var i = 0; i < rows.length; i++) {
                html += '<tr>' +
                    '<td style="text-align:center;">' + escapeHtml(rows[i].date) + '</td>' +
                    '<td>' + escapeHtml(rows[i].description) + '</td>' +
                    '<td style="text-align:right;">' + escapeHtml(rows[i].debit) + '</td>' +
                    '<td style="text-align:right;">' + escapeHtml(rows[i].credit) + '</td>' +
                    '<td style="text-align:right;">' + escapeHtml(rows[i].balance) + '</td>' +
                    '</tr>';
            }
            el('cbu-ledger-rows').innerHTML = html;
        } else {
            show('cbu-ledger-empty', true);
        }
        show('cbu-ledger-content', true);
    }

    function openLedger(button) {
        if (!el('cbuLedgerOverlay') || !el('cbu-ledger-rows')) {
            alert('CBU ledger popup could not be loaded on this page.');
            return;
        }

        var pid = button.getAttribute('data-pid') || '';
        var memberId = button.getAttribute('data-memberid') || '';
        var name = button.getAttribute('data-name') || '';

        show('cbuLedgerOverlay', true);
        show('cbu-ledger-loading', true);
        show('cbu-ledger-content', false);
        show('cbu-ledger-error', false);
        show('cbu-ledger-empty', false);
        el('cbu-ledger-rows').innerHTML = '';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', LEDGER_URL, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) {
                return;
            }
            if (xhr.status !== 200) {
                show('cbu-ledger-loading', false);
                show('cbu-ledger-error', true);
                setText('cbu-ledger-error-message', 'Failed to load ledger (HTTP ' + xhr.status + ').');
                return;
            }
            var response = null;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (err) {
                show('cbu-ledger-loading', false);
                show('cbu-ledger-error', true);
                setText('cbu-ledger-error-message', 'Server returned an unexpected response.');
                return;
            }
            renderLedger(response, pid, memberId, name);
        };
        xhr.send('pid=' + encodeURIComponent(pid) + '&member_id=' + encodeURIComponent(memberId));
    }

    // The overlay must sit directly under <body>, otherwise the ibox wrapper clips it.
    var overlay = el('cbuLedgerOverlay');
    if (overlay && overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }

    document.addEventListener('click', function(e) {
        var target = e.target || e.srcElement;

        if (findLedgerButton(target)) {
            e.preventDefault();
            openLedger(findLedgerButton(target));
            return;
        }

        if (target.id === 'cbuLedgerOverlay') {
            closeLedger();
            return;
        }

        var node = target;
        while (node && node !== document) {
            if (node.className && String(node.className).indexOf('cbu-ledger-close') !== -1) {
                e.preventDefault();
                closeLedger();
                return;
            }
            node = node.parentNode;
        }
    }, false);

    document.addEventListener('keydown', function(e) {
        if (e.keyCode === 27) {
            closeLedger();
        }
    }, false);

    function initMemberSuggest() {
        var input = el('accountno');
        var box = el('masterfile-suggest-box');
        var form = input ? input.form : null;
        if (!input || !box || !form) {
            return;
        }

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
            if (!item) return;
            // Masterfile filters by PID or member_id.
            input.value = item.member_id || item.pid || '';
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
            var status = form.querySelector('[name="status"]');
            var url = SUGGEST_URL +
                '?q=' + encodeURIComponent(q) +
                '&searchstatus=' + encodeURIComponent(status ? status.value : '');

            if (window.jQuery) {
                xhr = jQuery.getJSON(url)
                    .done(function(data) { render(data || []); })
                    .fail(function(jqXHR, textStatus) {
                        if (textStatus !== 'abort') hideBox();
                    });
                return;
            }

            xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) return;
                if (xhr.status >= 200 && xhr.status < 300) {
                    try { render(JSON.parse(xhr.responseText || '[]')); }
                    catch (err) { hideBox(); }
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
            if (!box.classList.contains('open') || !items.length) return;
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
            if (!btn || !btn.classList.contains('member-suggest-item')) return;
            choose(items[parseInt(btn.getAttribute('data-index'), 10)]);
        });

        form.addEventListener('change', function(e) {
            if (e.target && e.target.name === 'status' && input.value.replace(/^\s+|\s+$/g, '').length) {
                fetchSuggestions();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMemberSuggest);
    } else {
        initMemberSuggest();
    }
})();
</script>
