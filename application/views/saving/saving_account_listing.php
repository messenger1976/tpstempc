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
.member-list-page .panel-head-right {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
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
    flex: 1 1 160px;
    min-width: 140px;
}
.member-list-page .filter-field.search-field { flex: 2 1 240px; position: relative; }
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
.member-list-page .filter-actions .btn,
.member-list-page .panel-head-right .btn {
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
.member-list-page .filter-actions .btn-primary,
.member-list-page .panel-head-right .btn-primary {
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
.member-list-page .total-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0 0 18px;
    padding: 14px 18px;
    border-radius: 10px;
    background: linear-gradient(165deg, #e8f8f5 0%, #f7fcfa 100%);
    border: 1px solid #c9ebe3;
    color: #0e7c69;
    font-weight: 700;
}
.member-list-page .total-banner .lbl { font-size: 12px; letter-spacing: .04em; text-transform: uppercase; opacity: .85; }
.member-list-page .total-banner .val { font-size: 20px; font-variant-numeric: tabular-nums; }
.member-list-page .bulk-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    padding: 12px 18px;
    border-bottom: 1px solid #eef1f2;
    background: #fbfcfd;
}
.member-list-page .bulk-toolbar .btn {
    border-radius: 6px;
    font-weight: 600;
}
.member-list-page .bulk-hint { color: #888; font-size: 12px; font-weight: 600; }
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
.member-list-page .status-pill.inactive { background: #ed5565; }
.member-list-page .gl-pill {
    display: inline-block;
    padding: 5px 11px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 700;
    color: #fff !important;
}
.member-list-page .gl-pill.yes { background: #1ab394; }
.member-list-page .gl-pill.no { background: #f8ac59; }
.member-list-page .action-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
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
@media (max-width: 767px) {
    .member-list-page .filter-actions,
    .member-list-page .panel-head-right { width: 100%; }
    .member-list-page .filter-actions .btn,
    .member-list-page .panel-head-right .btn { flex: 1; }
}
</style>

<?php
$search_key = isset($jxy['key']) ? $jxy['key'] : (isset($_GET['key']) ? $_GET['key'] : '');
$account_type_filter = isset($account_type_filter) && $account_type_filter !== '' && $account_type_filter !== null ? $account_type_filter : (isset($_GET['account_type_filter']) ? $_GET['account_type_filter'] : 'all');
$gl_posted_filter = isset($gl_posted_filter) && $gl_posted_filter !== '' && $gl_posted_filter !== null ? $gl_posted_filter : (isset($_GET['gl_posted_filter']) ? $_GET['gl_posted_filter'] : 'all');
$status_filter = isset($status_filter) ? $status_filter : (isset($_GET['status_filter']) ? $_GET['status_filter'] : '1');

$export_url = current_lang() . '/saving/saving_account_list_export';
$export_params = array();
if (!empty($search_key)) {
    $export_params['key'] = $search_key;
}
if (!empty($account_type_filter) && $account_type_filter != 'all') {
    $export_params['account_type_filter'] = $account_type_filter;
}
if (!empty($gl_posted_filter) && $gl_posted_filter != 'all') {
    $export_params['gl_posted_filter'] = $gl_posted_filter;
}
if (isset($status_filter) && $status_filter != '') {
    $export_params['status_filter'] = $status_filter;
} else {
    $export_params['status_filter'] = '1';
}
if (!empty($export_params)) {
    $export_url .= '?' . http_build_query($export_params);
}
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
                <h4><?php echo lang('saving_account_list'); ?></h4>
            </div>
            <div class="panel-head-right">
                <?php echo anchor($export_url, '<i class="fa fa-file-excel-o"></i> Export to Excel', 'class="btn btn-success btn-sm"'); ?>
                <?php echo anchor(current_lang() . '/saving/create_saving_account/', '<i class="fa fa-plus"></i> ' . lang('create_saving_account'), 'class="btn btn-primary btn-sm"'); ?>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?php echo site_url(current_lang() . "/saving/saving_account_listing"); ?>" method="get" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field search-field">
                        <label>Search Member</label>
                        <input type="text" class="form-control" id="accountno" name="key" placeholder="Account, Member ID or Name..." value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                        <div id="saving-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                    </div>
                    <div class="filter-field">
                        <label><?php echo lang('account_type_name'); ?></label>
                        <select name="account_type_filter" class="form-control">
                            <option value="all" <?php echo ($account_type_filter == 'all' ? 'selected="selected"' : ''); ?>>All</option>
                            <option value="special" <?php echo ($account_type_filter == 'special' ? 'selected="selected"' : ''); ?>>Special</option>
                            <option value="mso" <?php echo ($account_type_filter == 'mso' ? 'selected="selected"' : ''); ?>>MSO</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label><?php echo lang('saving_account_gl_status'); ?></label>
                        <select name="gl_posted_filter" class="form-control">
                            <option value="all" <?php echo ($gl_posted_filter == 'all' ? 'selected="selected"' : ''); ?>><?php echo lang('saving_account_gl_filter_all'); ?></option>
                            <option value="posted" <?php echo ($gl_posted_filter == 'posted' ? 'selected="selected"' : ''); ?>><?php echo lang('saving_account_gl_posted'); ?></option>
                            <option value="not_posted" <?php echo ($gl_posted_filter == 'not_posted' ? 'selected="selected"' : ''); ?>><?php echo lang('saving_account_gl_not_posted'); ?></option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label><?php echo lang('account_status'); ?></label>
                        <select name="status_filter" class="form-control">
                            <option value="all" <?php echo ($status_filter == 'all' ? 'selected="selected"' : ''); ?>><?php echo lang('all_status'); ?></option>
                            <option value="1" <?php echo ($status_filter == '1' ? 'selected="selected"' : ''); ?>><?php echo lang('account_status_active'); ?></option>
                            <option value="0" <?php echo ($status_filter == '0' ? 'selected="selected"' : ''); ?>><?php echo lang('account_status_inactive'); ?></option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo site_url(current_lang() . '/saving/saving_account_listing'); ?>" class="btn btn-default btn-clear-filter">
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

    <div class="total-banner">
        <span class="lbl"><?php echo lang('total_savings_amount'); ?></span>
        <span class="val"><?php echo number_format(isset($total_savings_amount) ? $total_savings_amount : 0, 2, '.', ','); ?></span>
    </div>

    <?php echo form_open('', array('id' => 'form_bulk_gl', 'method' => 'post')); ?>
    <?php if (!empty($search_key)) { ?><input type="hidden" name="redirect_key" value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>"/><?php } ?>
    <?php if (!empty($account_type_filter) && $account_type_filter != 'all') { ?><input type="hidden" name="redirect_account_type_filter" value="<?php echo htmlspecialchars($account_type_filter, ENT_QUOTES, 'UTF-8'); ?>"/><?php } ?>
    <?php if (!empty($gl_posted_filter) && $gl_posted_filter != 'all') { ?><input type="hidden" name="redirect_gl_posted_filter" value="<?php echo htmlspecialchars($gl_posted_filter, ENT_QUOTES, 'UTF-8'); ?>"/><?php } ?>
    <?php if (isset($status_filter) && $status_filter !== '') { ?><input type="hidden" name="redirect_status_filter" value="<?php echo htmlspecialchars($status_filter, ENT_QUOTES, 'UTF-8'); ?>"/><?php } ?>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-bank icon-badge"></i>
                <h4><?php echo lang('saving_account_list'); ?></h4>
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

        <div class="bulk-toolbar">
            <button type="submit" name="post_selected" id="btn_post_selected_gl" class="btn btn-warning btn-sm" disabled="disabled" formaction="<?php echo base_url() . current_lang(); ?>/saving/post_selected_to_gl">
                <i class="fa fa-book"></i> <?php echo lang('saving_account_post_selected_to_gl'); ?>
            </button>
            <span id="post_selected_hint" class="bulk-hint"></span>
            <button type="submit" name="void_selected" id="btn_void_selected_gl" class="btn btn-danger btn-sm" disabled="disabled" formaction="<?php echo base_url() . current_lang(); ?>/saving/void_selected_gl">
                <i class="fa fa-undo"></i> <?php echo lang('saving_account_void_selected_gl'); ?>
            </button>
            <span id="void_selected_hint" class="bulk-hint"></span>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered member-table" id="saving_account_list_table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select_all_post_gl" title="<?php echo htmlspecialchars(lang('saving_account_select_all_post_gl'), ENT_QUOTES, 'UTF-8'); ?>"/>
                        </th>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select_all_void_gl" title="<?php echo htmlspecialchars(lang('saving_account_select_all_void_gl'), ENT_QUOTES, 'UTF-8'); ?>"/>
                        </th>
                        <th><?php echo lang('account_number'); ?></th>
                        <th><?php echo lang('member_member_id'); ?></th>
                        <th><?php echo lang('member_fullname'); ?></th>
                        <th><?php echo lang('member_old_account_no'); ?></th>
                        <th><?php echo lang('account_type_name'); ?></th>
                        <th style="text-align: right;"><?php echo lang('balance'); ?></th>
                        <th style="text-align: right;"><?php echo lang('virtual_balance'); ?></th>
                        <th style="text-align: center;"><?php echo lang('account_status'); ?></th>
                        <th style="text-align: center;"><?php echo lang('saving_account_gl_status'); ?></th>
                        <th><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($saving_accounts) && count($saving_accounts) > 0) { ?>
                        <?php foreach ($saving_accounts as $key => $value) {
                            $unposted_count = isset($value->unposted_count) ? intval($value->unposted_count) : 0;
                            $gl_posted_count = isset($value->gl_posted_count) ? intval($value->gl_posted_count) : 0;
                            $can_post = $unposted_count > 0;
                            $can_void = $gl_posted_count > 0;
                            $status_value = isset($value->status) ? $value->status : '1';
                            $is_active = ($status_value == '1' || $status_value === 1);
                            if ($value->tablename == 'members_grouplist' && $value->group_name) {
                                $display_name = $value->group_name;
                            } else if (($value->firstname || $value->lastname)) {
                                $display_name = trim($value->lastname . ', ' . $value->firstname . ' ' . $value->middlename);
                            } else {
                                $display_name = '-';
                            }
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php if ($can_post) { ?>
                                        <input type="checkbox" name="ids[]" value="<?php echo htmlspecialchars(encode_id($value->id), ENT_QUOTES, 'UTF-8'); ?>" class="cb_post_gl"/>
                                    <?php } else { ?>
                                        <input type="checkbox" disabled="disabled" title="<?php echo htmlspecialchars(lang('saving_account_no_unposted'), ENT_QUOTES, 'UTF-8'); ?>"/>
                                    <?php } ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($can_void) { ?>
                                        <input type="checkbox" name="void_ids[]" value="<?php echo htmlspecialchars(encode_id($value->id), ENT_QUOTES, 'UTF-8'); ?>" class="cb_void_gl"/>
                                    <?php } else { ?>
                                        <input type="checkbox" disabled="disabled" title="<?php echo htmlspecialchars(lang('saving_account_no_posted_to_void'), ENT_QUOTES, 'UTF-8'); ?>"/>
                                    <?php } ?>
                                </td>
                                <td><?php echo htmlspecialchars($value->account, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($value->old_members_acct ? $value->old_members_acct : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($value->account_type_name_display ? $value->account_type_name_display : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->balance, 2, '.', ','); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->virtual_balance, 2, '.', ','); ?></td>
                                <td style="text-align: center;">
                                    <span class="status-pill <?php echo $is_active ? 'active' : 'inactive'; ?>">
                                        <?php echo htmlspecialchars($is_active ? lang('account_status_active') : lang('account_status_inactive'), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="gl-pill <?php echo $gl_posted_count > 0 ? 'yes' : 'no'; ?>">
                                        <?php echo htmlspecialchars($gl_posted_count > 0 ? lang('saving_account_gl_posted') : lang('saving_account_gl_not_posted'), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php
                                        $this->db->where('PIN', current_user()->PIN);
                                        $this->db->where('link', 1);
                                        if (!empty($value->account_cat)) {
                                            $this->db->where('account_type', $value->account_cat);
                                        }
                                        $this->db->order_by('id', 'DESC');
                                        $this->db->limit(1);
                                        $report = $this->db->get('report_table_saving')->row();

                                        if ($report && !empty($value->account)) {
                                            $ledger_url = current_lang() . "/report_saving/new_saving_account_statement_view/1/" . encode_id($report->id) . "/" . encode_id($value->account);
                                            echo anchor($ledger_url, ' <i class="fa fa-th-list"></i> Ledger', 'class="btn btn-info btn-xs" target="_blank"');
                                        }
                                        if ($can_post) {
                                            echo anchor(current_lang() . "/saving/post_to_gl/" . encode_id($value->id), ' <i class="fa fa-book"></i> ' . lang('saving_account_post_to_gl'), 'class="btn btn-warning btn-xs post-account-gl" data-confirm="' . htmlspecialchars(lang('saving_account_post_to_gl_confirm'), ENT_QUOTES, 'UTF-8') . '"');
                                        }
                                        echo anchor(current_lang() . "/saving/edit_saving_account/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'), 'class="btn btn-primary btn-xs"');
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="12">
                                <div class="empty-state">
                                    <i class="fa fa-bank"></i>
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
    <?php echo form_close(); ?>
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

    function initMemberSuggest() {
        var input = document.getElementById('accountno');
        var box = document.getElementById('saving-suggest-box');
        var form = input ? input.form : null;
        if (!input || !box || !form) {
            return;
        }

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
                    '<span class="suggest-status">' + escapeHtml(row.pid) + '</span>' +
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
            var url = suggestUrl + '?q=' + encodeURIComponent(q);
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
                    catch (e) { hideBox(); }
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
    }

    function confirmAction(title, text, confirmText, confirmColor, onYes) {
        if (typeof swal !== 'function') {
            if (window.confirm(text)) {
                onYes();
            }
            return;
        }
        swal({
            title: title,
            text: text,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmColor || '#1ab394',
            confirmButtonText: confirmText || 'Yes',
            cancelButtonText: 'Cancel',
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                onYes();
            }
        });
    }

    var selectAllPost = document.getElementById('select_all_post_gl');
    var postCheckboxes = document.querySelectorAll('.cb_post_gl');
    var postBtn = document.getElementById('btn_post_selected_gl');
    var postHint = document.getElementById('post_selected_hint');

    function updatePostButton() {
        var n = 0;
        for (var i = 0; i < postCheckboxes.length; i++) {
            if (postCheckboxes[i].checked) n++;
        }
        if (postBtn) postBtn.disabled = n === 0;
        if (postHint) {
            postHint.textContent = n > 0 ? (n === 1 ? <?php echo json_encode(lang('saving_account_1_selected')); ?> : n + ' ' + <?php echo json_encode(lang('saving_account_n_selected')); ?>) : '';
        }
    }

    if (selectAllPost) {
        selectAllPost.onclick = function() {
            var checked = this.checked;
            for (var i = 0; i < postCheckboxes.length; i++) postCheckboxes[i].checked = checked;
            updatePostButton();
        };
    }
    for (var i = 0; i < postCheckboxes.length; i++) {
        postCheckboxes[i].onclick = updatePostButton;
    }
    updatePostButton();

    if (postBtn) {
        postBtn.onclick = function(e) {
            if (postBtn.disabled) {
                e.preventDefault();
                return false;
            }
            e.preventDefault();
            confirmAction(
                'Post to GL',
                <?php echo json_encode(lang('saving_account_post_selected_to_gl_confirm')); ?>,
                'Yes, post it!',
                '#f8ac59',
                function() {
                    if (postBtn.getAttribute('formaction')) {
                        postBtn.form.action = postBtn.getAttribute('formaction');
                    }
                    postBtn.form.submit();
                }
            );
            return false;
        };
    }

    var selectAllVoid = document.getElementById('select_all_void_gl');
    var voidCheckboxes = document.querySelectorAll('.cb_void_gl');
    var voidBtn = document.getElementById('btn_void_selected_gl');
    var voidHint = document.getElementById('void_selected_hint');

    function updateVoidButton() {
        var n = 0;
        for (var i = 0; i < voidCheckboxes.length; i++) {
            if (voidCheckboxes[i].checked) n++;
        }
        if (voidBtn) voidBtn.disabled = n === 0;
        if (voidHint) {
            voidHint.textContent = n > 0 ? (n === 1 ? <?php echo json_encode(lang('saving_account_1_selected_void')); ?> : n + ' ' + <?php echo json_encode(lang('saving_account_n_selected_void')); ?>) : '';
        }
    }

    if (selectAllVoid) {
        selectAllVoid.onclick = function() {
            var checked = this.checked;
            for (var i = 0; i < voidCheckboxes.length; i++) voidCheckboxes[i].checked = checked;
            updateVoidButton();
        };
    }
    for (var i = 0; i < voidCheckboxes.length; i++) {
        voidCheckboxes[i].onclick = updateVoidButton;
    }
    updateVoidButton();

    if (voidBtn) {
        voidBtn.onclick = function(e) {
            if (voidBtn.disabled) {
                e.preventDefault();
                return false;
            }
            e.preventDefault();
            confirmAction(
                'Void GL Postings',
                <?php echo json_encode(lang('saving_account_void_selected_gl_confirm')); ?>,
                'Yes, void it!',
                '#d33',
                function() {
                    if (voidBtn.getAttribute('formaction')) {
                        voidBtn.form.action = voidBtn.getAttribute('formaction');
                    }
                    voidBtn.form.submit();
                }
            );
            return false;
        };
    }

    document.addEventListener('click', function(e) {
        var link = e.target;
        while (link && link !== document && !(link.className && String(link.className).indexOf('post-account-gl') !== -1)) {
            link = link.parentNode;
        }
        if (!link || link === document) {
            return;
        }
        e.preventDefault();
        var href = link.getAttribute('href');
        var text = link.getAttribute('data-confirm') || 'Post to GL?';
        confirmAction('Post to GL', text, 'Yes, post it!', '#f8ac59', function() {
            window.location.href = href;
        });
    });

    initMemberSuggest();
})();
</script>
