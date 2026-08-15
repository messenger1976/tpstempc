<?php $this->load->view('loan/list_page_styles'); ?>
<style>
.member-list-page .status-pill.posted { background: #1ab394; }
.member-list-page .status-pill.not-posted { background: #f8ac59; }
.member-list-page .status-pill.activated { background: #1c84c6; }
.member-list-page .status-sub {
    display: block;
    margin-top: 4px;
    font-size: 11px;
    color: #888;
    font-weight: 600;
}
.member-list-page .fy-banner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0 0 18px;
    padding: 12px 16px;
    background: #e8f8f5;
    border: 1px solid #c9ebe3;
    border-radius: 8px;
    color: #0e7c69;
    font-size: 13px;
    font-weight: 600;
}
.member-list-page .fy-banner .btn {
    border-radius: 6px;
    font-weight: 600;
}
.member-list-page .panel-head .btn {
    border-radius: 6px;
    font-weight: 600;
}
.member-list-page .panel-head-tools {
    display: flex;
    align-items: center;
    gap: 14px;
    flex: 1 1 auto;
    justify-content: flex-end;
    min-width: 0;
}
.member-list-page .table-search-wrap {
    position: relative;
    flex: 0 1 280px;
    max-width: 100%;
}
.member-list-page .table-search-wrap .fa-search {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #a7b1c2;
    font-size: 13px;
    pointer-events: none;
}
.member-list-page .table-search-wrap .form-control {
    height: 34px;
    padding-left: 34px;
    padding-right: 12px;
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    font-size: 13px;
}
.member-list-page .table-search-wrap .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.member-list-page .filter-field.wide-select { flex: 1.4 1 220px; }
.member-list-page .member-table > thead > tr > th,
.member-list-page .member-table > tbody > tr > td {
    border: 1px solid #e7eaec !important;
}
.member-list-page .member-table > thead > tr > th {
    border-top: 0 !important;
}
.member-list-page .member-table > thead > tr > th:not(:first-child),
.member-list-page .member-table > tbody > tr > td:not(:first-child) {
    border-left: 0 !important;
}
.member-list-page .member-table > tbody > tr:hover td {
    box-shadow: none;
}
</style>

<?php
$selected_fiscal_year_id = isset($selected_fiscal_year_id) ? $selected_fiscal_year_id : null;
$selected_loan_product_id = isset($selected_loan_product_id) ? $selected_loan_product_id : 'all';
$fiscal_years = isset($fiscal_years) ? $fiscal_years : array();
$loan_products = isset($loan_products) ? $loan_products : array();
$loan_beginning_balances = isset($loan_beginning_balances) ? $loan_beginning_balances : array();
$member_names = isset($member_names) ? $member_names : array();
$product_info = isset($product_info) ? $product_info : array();
$activated_map = isset($activated_map) ? $activated_map : array();
$row_count = is_array($loan_beginning_balances) ? count($loan_beginning_balances) : 0;
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
                <i class="fa fa-balance-scale icon-badge"></i>
                <h4><?php echo lang('loan_beginning_balance_list'); ?></h4>
            </div>
            <a class="btn btn-primary btn-sm" href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_create'); ?>">
                <i class="fa fa-plus"></i> <?php echo lang('loan_beginning_balance_create'); ?>
            </a>
        </div>
        <div class="panel-body">
            <form method="post" action="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_list'); ?>" class="form-horizontal" id="filterForm">
                <div class="filter-row">
                    <div class="filter-field wide-select">
                        <label><?php echo lang('fiscal_year'); ?></label>
                        <select name="fiscal_year_id" id="fiscal_year_id" class="form-control">
                            <option value=""><?php echo lang('select_default_text'); ?></option>
                            <?php foreach ($fiscal_years as $fy) {
                                $fy_id = (int) $fy->id;
                                $is_selected = (isset($selected_fiscal_year_id) && (int) $selected_fiscal_year_id === $fy_id);
                                ?>
                                <option value="<?php echo (int) $fy->id; ?>" <?php echo $is_selected ? 'selected="selected"' : ''; ?>>
                                    <?php echo htmlspecialchars($fy->name . ' (' . date('M d, Y', strtotime($fy->start_date)) . ' - ' . date('M d, Y', strtotime($fy->end_date)) . ')', ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label><?php echo lang('loan_beginning_balance_loan_product'); ?></label>
                        <select name="loan_product_id" id="loan_product_id" class="form-control">
                            <option value="all" <?php echo ($selected_loan_product_id == 'all' ? 'selected="selected"' : ''); ?>>All</option>
                            <?php foreach ($loan_products as $product) { ?>
                                <option value="<?php echo (int) $product->id; ?>" <?php echo ((string) $selected_loan_product_id === (string) $product->id ? 'selected="selected"' : ''); ?>>
                                    <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_list'); ?>" class="btn btn-default btn-clear-filter">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> <?php echo lang('button_view'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($selected_fiscal_year_id)) { ?>
        <?php if (isset($fiscal_year) && $fiscal_year) { ?>
            <div class="fy-banner">
                <div>
                    <strong><?php echo lang('fiscal_year'); ?>:</strong>
                    <?php echo htmlspecialchars($fiscal_year->name, ENT_QUOTES, 'UTF-8'); ?>
                    (<?php echo date('M d, Y', strtotime($fiscal_year->start_date)) . ' - ' . date('M d, Y', strtotime($fiscal_year->end_date)); ?>)
                </div>
                <?php
                $export_url = site_url(current_lang() . '/loan/loan_beginning_balance_export');
                $export_url .= '?fiscal_year_id=' . urlencode($selected_fiscal_year_id);
                if (isset($selected_loan_product_id) && $selected_loan_product_id != 'all') {
                    $export_url .= '&loan_product_id=' . urlencode($selected_loan_product_id);
                }
                ?>
                <a class="btn btn-success btn-sm" href="<?php echo $export_url; ?>">
                    <i class="fa fa-file-excel-o"></i> Export to Excel
                </a>
            </div>
        <?php } ?>

        <div class="member-table-panel">
            <div class="panel-head">
                <div class="panel-head-left">
                    <i class="fa fa-list icon-badge"></i>
                    <h4><?php echo lang('loan_beginning_balance_list'); ?></h4>
                </div>
                <div class="panel-head-tools">
                    <div class="table-search-wrap">
                        <i class="fa fa-search"></i>
                        <input type="text" id="bb-table-search" class="form-control" placeholder="Search member, loan ID, product..." autocomplete="off" />
                    </div>
                    <div class="result-meta">
                        Showing <strong id="bb-visible-count"><?php echo number_format($row_count); ?></strong>
                        <span id="bb-record-label"><?php echo $row_count === 1 ? 'record' : 'records'; ?></span>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered member-table" id="bb-balance-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width:60px;"><?php echo lang('sno'); ?></th>
                            <th><?php echo lang('loan_beginning_balance_member_id'); ?></th>
                            <th><?php echo lang('member_name'); ?></th>
                            <th><?php echo lang('loan_beginning_balance_loan_product'); ?></th>
                            <th><?php echo lang('loan_beginning_balance_loan_id'); ?></th>
                            <th style="text-align:right;"><?php echo lang('loan_beginning_balance_loan_amount'); ?></th>
                            <th style="text-align:right;"><?php echo lang('loan_beginning_balance_monthly_amort'); ?></th>
                            <th><?php echo lang('loan_beginning_balance_term'); ?></th>
                            <th><?php echo lang('loan_beginning_balance_disbursement_date'); ?></th>
                            <th><?php echo lang('loan_beginning_balance_last_date_paid'); ?></th>
                            <th style="text-align:right;"><?php echo lang('loan_beginning_balance_principal'); ?></th>
                            <th style="text-align:right;"><?php echo lang('loan_beginning_balance_interest'); ?></th>
                            <th style="text-align:right;"><?php echo lang('loan_beginning_balance_penalty'); ?></th>
                            <th style="text-align:right;"><?php echo lang('loan_beginning_balance_total'); ?></th>
                            <th><?php echo lang('status'); ?></th>
                            <th><?php echo lang('actioncolumn'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($row_count > 0) {
                            $i = 1;
                            foreach ($loan_beginning_balances as $balance) {
                                $member_info = isset($member_names[$balance->member_id]) ? $member_names[$balance->member_id] : 'Unknown';
                                $product_name = isset($product_info[$balance->loan_product_id]) ? $product_info[$balance->loan_product_id] : '-';
                                $is_activated = !empty($activated_map[$balance->id]);
                                ?>
                                <tr>
                                    <td style="text-align:center;"><?php echo $i++; ?></td>
                                    <td><span class="member-id-chip"><?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><?php echo htmlspecialchars($member_info, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $balance->loan_id ? htmlspecialchars($balance->loan_id, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                    <td class="amount-cell"><?php echo $balance->loan_amount ? number_format($balance->loan_amount, 2) : '-'; ?></td>
                                    <td class="amount-cell"><?php echo $balance->monthly_amort ? number_format($balance->monthly_amort, 2) : '-'; ?></td>
                                    <td><?php echo $balance->term ? htmlspecialchars($balance->term . ' months', ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                    <td><?php echo !empty($balance->disbursement_date) ? date('d-m-Y', strtotime($balance->disbursement_date)) : '-'; ?></td>
                                    <td><?php echo $balance->last_date_paid ? date('d-m-Y', strtotime($balance->last_date_paid)) : '-'; ?></td>
                                    <td class="amount-cell"><?php echo number_format($balance->principal_balance, 2); ?></td>
                                    <td class="amount-cell"><?php echo number_format($balance->interest_balance, 2); ?></td>
                                    <td class="amount-cell"><?php echo number_format($balance->penalty_balance, 2); ?></td>
                                    <td class="amount-cell"><?php echo number_format($balance->total_balance, 2); ?></td>
                                    <td>
                                        <?php if ($is_activated) { ?>
                                            <span class="status-pill activated"><?php echo lang('loan_beginning_balance_activated'); ?></span>
                                            <?php if ($balance->loan_id) { ?>
                                                <span class="status-sub">
                                                    <a href="<?php echo site_url(current_lang() . '/loan/view_indetail/' . encode_id($balance->loan_id)); ?>">
                                                        <?php echo htmlspecialchars($balance->loan_id, ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                </span>
                                            <?php } ?>
                                        <?php } else if ((int) $balance->posted === 1) { ?>
                                            <span class="status-pill posted"><?php echo lang('loan_beginning_balance_posted'); ?></span>
                                            <?php if ($balance->posted_date) { ?>
                                                <span class="status-sub"><?php echo date('M d, Y H:i', strtotime($balance->posted_date)); ?></span>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span class="status-pill not-posted"><?php echo lang('loan_beginning_balance_not_posted'); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <?php if ((int) $balance->posted === 0) { ?>
                                                <a class="btn btn-primary btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_create/' . encode_id($balance->id)); ?>">
                                                    <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-danger btn-xs btn-delete-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-success btn-xs btn-post-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa fa-check"></i> <?php echo lang('loan_beginning_balance_post'); ?>
                                                </a>
                                            <?php } else if ($is_activated) { ?>
                                                <a class="btn btn-primary btn-xs" href="<?php echo site_url(current_lang() . '/loan/view_indetail/' . encode_id($balance->loan_id)); ?>">
                                                    <i class="fa fa-folder-open"></i> <?php echo lang('loan_view_detail'); ?>
                                                </a>
                                                <a class="btn btn-default btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_ledger/' . encode_id($balance->loan_id)); ?>">
                                                    <i class="fa fa-book"></i> <?php echo lang('loan_ledger'); ?>
                                                </a>
                                            <?php } else { ?>
                                                <a class="btn btn-default btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_create/' . encode_id($balance->id)); ?>">
                                                    <i class="fa fa-calendar"></i> <?php echo lang('loan_beginning_balance_edit_dates'); ?>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-info btn-xs btn-activate-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa fa-play-circle"></i> <?php echo lang('loan_beginning_balance_activate'); ?>
                                                </a>
                                                <?php if (has_role(5, 'void_transaction')) { ?>
                                                <a class="btn btn-danger btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_void/' . encode_id($balance->id)); ?>" onclick="return confirm('Void this loan beginning balance with a reversing GL entry?');">
                                                    <i class="fa fa-undo"></i> Void
                                                </a>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="16">
                                    <div class="empty-state">
                                        <i class="fa fa-list"></i>
                                        <?php echo lang('data_not_found'); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } else { ?>
        <div class="member-table-panel">
            <div class="panel-body">
                <div class="empty-state">
                    <i class="fa fa-calendar"></i>
                    <?php echo lang('loan_beginning_balance_select_fiscal_year'); ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<script>
(function() {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }

        $(document).ready(function() {
            var $search = $('#bb-table-search');
            var $rows = $('#bb-balance-table tbody tr').filter(function() {
                return $(this).find('.empty-state').length === 0;
            });
            var $count = $('#bb-visible-count');
            var $label = $('#bb-record-label');
            var totalRows = $rows.length;

            function updateVisibleCount(visible) {
                $count.text(visible.toLocaleString());
                $label.text(visible === 1 ? 'record' : 'records');
            }

            $search.on('keyup input', function() {
                var q = $.trim($(this).val()).toLowerCase();
                if (!q) {
                    $rows.show();
                    updateVisibleCount(totalRows);
                    return;
                }
                var visible = 0;
                $rows.each(function() {
                    var text = $(this).text().toLowerCase();
                    var match = text.indexOf(q) !== -1;
                    $(this).toggle(match);
                    if (match) {
                        visible++;
                    }
                });
                updateVisibleCount(visible);
            });

            $('.btn-delete-balance').click(function() {
                var balanceId = $(this).data('id');
                var member = $(this).data('member');
                var deleteUrl = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_delete/'); ?>/' + balanceId;

                swal({
                    title: "<?php echo lang('are_you_sure'); ?>",
                    text: "<?php echo lang('loan_beginning_balance_delete_confirm'); ?>: " + member,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "<?php echo lang('yes_delete'); ?>",
                    cancelButtonText: "<?php echo lang('cancel'); ?>",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(isConfirm) {
                    if (isConfirm) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            $('.btn-post-balance').click(function() {
                var balanceId = $(this).data('id');
                var member = $(this).data('member');
                var postUrl = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_post/'); ?>/' + balanceId;

                swal({
                    title: "<?php echo lang('are_you_sure'); ?>",
                    text: "<?php echo lang('loan_beginning_balance_post_confirm'); ?>" + (member ? " (Member: " + member + ")" : ""),
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#5cb85c",
                    confirmButtonText: "<?php echo lang('loan_beginning_balance_post'); ?>",
                    cancelButtonText: "<?php echo lang('cancel'); ?>",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(isConfirm) {
                    if (isConfirm) {
                        window.location.href = postUrl;
                    }
                });
            });

            $('.btn-activate-balance').click(function() {
                var balanceId = $(this).data('id');
                var member = $(this).data('member');
                var activateUrl = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_activate/'); ?>/' + balanceId;

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
            });
        });
    }
    initScripts();
})();
</script>
