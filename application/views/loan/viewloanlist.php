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
    flex: 1 1 160px;
    min-width: 140px;
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
    white-space: nowrap;
}
.member-list-page .status-pill.new { background: #23c6c8; }
.member-list-page .status-pill.eval { background: #1c84c6; }
.member-list-page .status-pill.rejected { background: #ed5565; }
.member-list-page .status-pill.accepted { background: #1ab394; }
.member-list-page .status-pill.closed { background: #676a6c; }
.member-list-page .status-pill.disburse { background: #f8ac59; }
.member-list-page .status-pill.bb { background: #f8ac59; }
.member-list-page .status-pill.mixed { background: #f8ac59; }
.member-list-page .status-pill.pending-release { background: #23c6c8; }
.member-list-page .status-pill.released-unposted { background: #f8ac59; }
.member-list-page .status-pill.active { background: #1ab394; }
.member-list-page .status-pill.past-due { background: #ed5565; }
.member-list-page .status-pill.other { background: #676a6c; }
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
    overflow: visible;
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
@media (max-width: 767px) {
    .member-list-page .filter-actions { width: 100%; }
    .member-list-page .filter-actions .btn { flex: 1; }
}
</style>
<?php $this->load->view('loan/list_action_icon_script'); ?>

<?php
$search_key = isset($search_key) ? $search_key : (isset($_GET['key']) ? $_GET['key'] : (isset($_POST['key']) ? $_POST['key'] : ''));
$status_list = isset($status_list) ? $status_list : array();
$current_status = isset($status_filter) ? $status_filter : '';
$loan_products = isset($loan_products) ? $loan_products : array();
$current_product_id = isset($product_id) ? $product_id : 'all';
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
                <h4><?php echo lang('loan_viewlist'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?php echo site_url(current_lang() . "/loan/loan_viewlist"); ?>" method="get" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field search-field">
                        <label><?php echo lang('loan_LID'); ?> / <?php echo lang('member_name'); ?></label>
                        <input type="text" class="form-control" name="key" id="loan_search_key" placeholder="Loan No, Member ID or Name..." value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                        <div id="loan-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                    </div>
                    <div class="filter-field">
                        <label><?php echo lang('loan_status'); ?></label>
                        <select name="status_filter" class="form-control">
                            <?php foreach ($status_list as $code => $label) {
                                $sel = ($current_status !== null && $current_status !== '' && (string) $code === (string) $current_status) ? ' selected="selected"' : '';
                                ?>
                                <option value="<?php echo htmlspecialchars($code); ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($label); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label><?php echo lang('loan_product'); ?></label>
                        <select name="product_id" class="form-control">
                            <option value="all"<?php echo ($current_product_id === 'all' || $current_product_id === '' || $current_product_id === null) ? ' selected="selected"' : ''; ?>><?php echo lang('loan_products_all'); ?></option>
                            <?php foreach ($loan_products as $product) { ?>
                                <option value="<?php echo (int) $product->id; ?>"<?php echo ((string) $current_product_id === (string) $product->id) ? ' selected="selected"' : ''; ?>>
                                    <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo site_url(current_lang() . '/loan/loan_viewlist'); ?>" class="btn btn-default btn-clear-filter">
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
                <i class="fa fa-list icon-badge"></i>
                <h4><?php echo lang('loan_viewlist'); ?></h4>
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
                        <th><?php echo lang('loan_LID'); ?></th>
                        <th><?php echo lang('member_name'); ?></th>
                        <th><?php echo lang('loan_product'); ?></th>
                        <th><?php echo lang('loan_applicationdate'); ?></th>
                        <th><?php echo lang('loan_encoded_date'); ?></th>
                        <th><?php echo lang('loan_encoded_by'); ?></th>
                        <th style="text-align:right;"><?php echo lang('loan_applied_amount'); ?></th>
                        <th style="text-align:center;"><?php echo lang('loan_installment'); ?></th>
                        <th style="text-align:right;"><?php echo lang('loan_installment_amount'); ?></th>
                        <th style="text-align:right;"><?php echo lang('loan_total_interest'); ?></th>
                        <th style="text-align:right;"><?php echo lang('loan_total'); ?></th>
                        <th><?php echo lang('loan_status'); ?></th>
                        <th><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($loan_list)) { ?>
                        <?php foreach ($loan_list as $value) {
                            $info = (!empty($value->PID)) ? $this->member_model->member_basic_info(null, $value->PID)->row() : null;
                            if ($info) {
                                $member_id_disp = $info->member_id;
                                $full_name = trim($info->firstname . ' ' . $info->middlename . ' ' . $info->lastname);
                            } else {
                                $member_id_disp = isset($value->member_id) ? $value->member_id : '';
                                $full_name = '';
                            }
                            $interval = $this->setting_model->intervalinfo($value->interval)->row();
                            $interval_desc = $interval ? $interval->description : '';
                            $status_code = isset($value->status) ? (string) $value->status : '';
                            $status_name = isset($value->lifecycle_name) && $value->lifecycle_name !== ''
                                ? $value->lifecycle_name
                                : (isset($value->name) ? $value->name : '');
                            $pill = isset($value->lifecycle_pill) && $value->lifecycle_pill !== ''
                                ? $value->lifecycle_pill
                                : 'other';
                            if ($pill === 'other') {
                                if ($status_name === 'Beginning Balance' || $status_code === 'bb') {
                                    $pill = 'bb';
                                } else if ($status_code === '0') {
                                    $pill = 'new';
                                } else if ($status_code === '1') {
                                    $pill = 'eval';
                                } else if ($status_code === '2') {
                                    $pill = 'rejected';
                                } else if ($status_code === '4' || $status_code === '9') {
                                    $pill = 'accepted';
                                } else if ($status_code === '5') {
                                    $pill = 'closed';
                                } else if ($status_code === '6') {
                                    $pill = 'disburse';
                                } else if ($status_code === '7' || $status_code === '8') {
                                    $pill = 'mixed';
                                }
                            }
                            $app_date = !empty($value->applicationdate) ? format_date($value->applicationdate, false) : '';
                            $product_name = !empty($value->product_name) ? $value->product_name : '-';
                            $encoded_on = '';
                            $encoded_raw = '';
                            if (!empty($value->encoded_on) && $value->encoded_on !== '0000-00-00 00:00:00') {
                                $encoded_raw = $value->encoded_on;
                            } else if (!empty($value->createdon) && $value->createdon !== '0000-00-00 00:00:00') {
                                $encoded_raw = $value->createdon;
                            } else if (!empty($value->created_at) && $value->created_at !== '0000-00-00 00:00:00') {
                                $encoded_raw = $value->created_at;
                            }
                            if ($encoded_raw !== '') {
                                $encoded_on = date('d-m-Y H:i', strtotime($encoded_raw));
                            }
                            $encoded_by = isset($value->encoded_by_name) ? trim((string) $value->encoded_by_name) : '';
                            if ($encoded_by === '' && !empty($value->createdby)) {
                                $enc_user = $this->db->get_where('users', array('id' => $value->createdby))->row();
                                if ($enc_user) {
                                    $encoded_by = trim($enc_user->first_name . ' ' . $enc_user->last_name);
                                }
                            }
                            ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($value->LID, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td>
                                    <?php if ($member_id_disp !== '') { ?>
                                        <span class="member-id-chip"><?php echo htmlspecialchars($member_id_disp, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php } ?>
                                    <?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($app_date, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $encoded_on !== '' ? htmlspecialchars($encoded_on, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                <td><?php echo $encoded_by !== '' ? htmlspecialchars($encoded_by, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                <td class="amount-cell"><?php echo number_format($value->basic_amount, 2); ?></td>
                                <td style="text-align:center;"><?php echo htmlspecialchars($value->number_istallment . ($interval_desc !== '' ? ' ' . $interval_desc : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->installment_amount, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->total_interest_amount, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->total_loan, 2); ?></td>
                                <td><span class="status-pill <?php echo htmlspecialchars($pill, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td>
                                    <div class="action-btns">
                                        <?php
                                        $is_bb_row = !empty($value->is_beginning_balance)
                                            || $status_name === 'Beginning Balance'
                                            || $status_code === 'bb';
                                        if ($is_bb_row) {
                                            $bb_id = isset($value->bb_id) ? (int) $value->bb_id : 0;
                                            $bb_posted = isset($value->bb_posted) ? (int) $value->bb_posted : 0;
                                            $bb_fy = isset($value->bb_fiscal_year_id) ? (int) $value->bb_fiscal_year_id : 0;
                                            $bb_list_url = site_url(current_lang() . '/loan/loan_beginning_balance_list' . ($bb_fy ? ('?fiscal_year_id=' . $bb_fy) : ''));
                                            echo '<a href="' . htmlspecialchars($bb_list_url) . '" class="btn btn-primary btn-xs" title="' . htmlspecialchars(lang('loan_beginning_balance_manage'), ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-folder-open"></i> ' . lang('loan_beginning_balance_manage') . '</a>';
                                            if ($bb_id > 0 && $bb_posted === 0) {
                                                echo anchor(
                                                    current_lang() . '/loan/loan_beginning_balance_create/' . encode_id($bb_id),
                                                    ' <i class="fa fa-edit"></i> ' . lang('button_edit'),
                                                    'class="btn btn-default btn-xs" title="' . htmlspecialchars(lang('button_edit'), ENT_QUOTES, 'UTF-8') . '"'
                                                );
                                            } else if ($bb_id > 0 && $bb_posted === 1) {
                                                echo '<a href="javascript:void(0);" class="btn btn-info btn-xs btn-activate-balance-list" data-id="' . htmlspecialchars(encode_id($bb_id), ENT_QUOTES, 'UTF-8') . '" data-member="' . htmlspecialchars(isset($value->member_id) ? $value->member_id : '', ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars(lang('loan_beginning_balance_activate'), ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-play-circle"></i> ' . lang('loan_beginning_balance_activate') . '</a>';
                                            }
                                        } else {
                                            echo anchor(current_lang() . "/loan/view_indetail/" . encode_id($value->LID), ' <i class="fa fa-folder-open"></i> ' . lang('loan_view_detail'), 'class="btn btn-primary btn-xs" title="' . htmlspecialchars(lang('loan_view_detail'), ENT_QUOTES, 'UTF-8') . '"');
                                            if ($value->edit == 0) {
                                                echo anchor(current_lang() . '/loan/loan_editing/' . encode_id($value->LID), ' <i class="fa fa-edit"></i> ' . lang('button_edit'), 'class="btn btn-default btn-xs" title="' . htmlspecialchars(lang('button_edit'), ENT_QUOTES, 'UTF-8') . '"');
                                            }
                                            $lifecycle_code = isset($value->lifecycle_code) ? (string) $value->lifecycle_code : '';
                                            if (has_role(5, 'void_transaction') && in_array($lifecycle_code, array('pending_release', 'released_unposted'), true)) {
                                                $cancel_url = site_url(current_lang() . '/loan/view_indetail/' . encode_id($value->LID) . '#loan-cancel-panel');
                                                echo '<a href="' . htmlspecialchars($cancel_url, ENT_QUOTES, 'UTF-8') . '" class="btn btn-danger btn-xs" title="' . htmlspecialchars(lang('loan_cancel'), ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-ban"></i> ' . lang('loan_cancel') . '</a>';
                                            }
                                            if (isset($value->status) && ((string) $value->status === '4' || (string) $value->status === '5')) {
                                                $schedule_url = site_url(current_lang() . '/loan/view_repayment_schedule_popup/' . encode_id($value->LID));
                                                echo '<a href="' . htmlspecialchars($schedule_url) . '" class="btn btn-info btn-xs repayment-schedule-popup" data-schedule-url="' . htmlspecialchars($schedule_url) . '" title="' . htmlspecialchars(lang('loan_view_repayment_schedule'), ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-calendar-check-o"></i> ' . lang('loan_view_repayment_schedule') . '</a>';
                                                if (!empty($value->disburse)) {
                                                    $print_disburse_url = site_url(current_lang() . '/loan/loan_disbursement_print/' . encode_id($value->LID));
                                                    $is_bb_print = (isset($value->evaluated) && (string) $value->evaluated === 'BEGINNING_BALANCE');
                                                    $print_disburse_label = $is_bb_print ? lang('loan_print_beginning_balance_journal') : lang('loan_print_disbursement');
                                                    echo '<a href="' . htmlspecialchars($print_disburse_url) . '" class="btn btn-default btn-xs" target="_blank" title="' . htmlspecialchars($print_disburse_label, ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-print"></i> ' . $print_disburse_label . '</a>';
                                                }
                                                $ledger_url = site_url(current_lang() . '/loan/loan_ledger/' . encode_id($value->LID));
                                                echo '<a href="' . htmlspecialchars($ledger_url) . '" class="btn btn-warning btn-xs" title="' . htmlspecialchars(lang('loan_ledger'), ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-book"></i> ' . lang('loan_ledger') . '</a>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="11">
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

<div class="modal fade" id="repaymentScheduleModal" tabindex="-1" role="dialog" aria-labelledby="repaymentScheduleModalLabel">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="repaymentScheduleModalLabel"><?php echo lang('loan_view_repayment_schedule'); ?></h4>
            </div>
            <div class="modal-body" style="padding: 0; min-height: 400px;">
                <iframe id="repaymentScheduleFrame" style="width: 100%; height: 70vh; min-height: 400px; border: none;"></iframe>
            </div>
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

    function findPopupLink(el) {
        while (el) {
            if (el.nodeName === 'A' && el.className && el.className.indexOf('repayment-schedule-popup') !== -1) return el;
            el = el.parentNode;
        }
        return null;
    }
    document.addEventListener('click', function(e) {
        var link = (e.target && e.target.closest) ? e.target.closest('a.repayment-schedule-popup') : findPopupLink(e.target);
        if (!link) return;
        e.preventDefault();
        var url = link.getAttribute('data-schedule-url') || link.getAttribute('href');
        if (!url) return;
        var frame = document.getElementById('repaymentScheduleFrame');
        var modal = document.getElementById('repaymentScheduleModal');
        if (frame) frame.src = url;
        if (modal && typeof jQuery !== 'undefined' && jQuery(modal).modal) {
            jQuery(modal).modal('show');
        } else {
            window.open(url, 'repayment_schedule_win', 'width=950,height=700,scrollbars=yes,resizable=yes');
        }
    });
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function() {
            jQuery('#repaymentScheduleModal').on('hidden.bs.modal', function() {
                var frame = document.getElementById('repaymentScheduleFrame');
                if (frame) frame.src = 'about:blank';
            });
        });
    }

    function initMemberSuggest() {
        var input = document.getElementById('loan_search_key');
        var box = document.getElementById('loan-suggest-box');
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
    }

    if (document.readyState === 'complete') {
        initMemberSuggest();
    } else {
        window.addEventListener('load', initMemberSuggest);
        setTimeout(initMemberSuggest, 300);
    }

    function initBbActivateFromList() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initBbActivateFromList, 50);
            return;
        }
        jQuery(document).off('click.bbActivateList', '.btn-activate-balance-list').on('click.bbActivateList', '.btn-activate-balance-list', function() {
            var balanceId = jQuery(this).data('id');
            var member = jQuery(this).data('member');
            var activateUrl = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_activate'); ?>/' + balanceId;
            if (typeof swal === 'function') {
                swal({
                    title: "<?php echo lang('are_you_sure'); ?>",
                    text: "<?php echo lang('loan_beginning_balance_activate_confirm'); ?>" + (member ? " (Member: " + member + ")" : ""),
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#0275d8",
                    confirmButtonText: "<?php echo lang('loan_beginning_balance_activate'); ?>",
                    cancelButtonText: "<?php echo lang('cancel'); ?>",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(isConfirm) {
                    if (isConfirm) {
                        window.location.href = activateUrl;
                    }
                });
            } else if (window.confirm("<?php echo lang('loan_beginning_balance_activate_confirm'); ?>")) {
                window.location.href = activateUrl;
            }
        });
    }
    initBbActivateFromList();
})();
</script>
