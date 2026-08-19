<?php $this->load->view('loan/list_page_styles'); ?>
<style>
.member-list-page .status-pill.posted { background: #1ab394; }
.member-list-page .status-pill.not-posted { background: #f8ac59; }
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
.member-list-page .panel-head .btn {
    border-radius: 6px;
    font-weight: 600;
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
$fiscal_years = isset($fiscal_years) ? $fiscal_years : array();
$beginning_balances = isset($beginning_balances) ? $beginning_balances : array();
$row_count = is_array($beginning_balances) ? count($beginning_balances) : 0;
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
    if (function_exists('gl_books_close_alert_html')) {
        echo gl_books_close_alert_html();
    }
    ?>

    <div class="member-filter-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-balance-scale icon-badge"></i>
                <h4><?php echo lang('beginning_balance_list'); ?></h4>
            </div>
            <a class="btn btn-primary btn-sm" href="<?php echo site_url(current_lang() . '/finance/beginning_balance_create'); ?>">
                <i class="fa fa-plus"></i> <?php echo lang('beginning_balance_create'); ?>
            </a>
        </div>
        <div class="panel-body">
            <form method="post" action="<?php echo site_url(current_lang() . '/finance/beginning_balance_list'); ?>" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field wide-select">
                        <label><?php echo lang('fiscal_year'); ?></label>
                        <select name="fiscal_year_id" id="fiscal_year_id" class="form-control">
                            <option value=""><?php echo lang('select_default_text'); ?></option>
                            <?php foreach ($fiscal_years as $fy) {
                                $fy_id = (int) $fy->id;
                                $is_selected = (isset($selected_fiscal_year_id) && (int) $selected_fiscal_year_id === $fy_id);
                                ?>
                                <option value="<?php echo $fy_id; ?>" <?php echo $is_selected ? 'selected="selected"' : ''; ?>>
                                    <?php echo htmlspecialchars($fy->name . ' (' . date('M d, Y', strtotime($fy->start_date)) . ' - ' . date('M d, Y', strtotime($fy->end_date)) . ')', ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo site_url(current_lang() . '/finance/beginning_balance_list'); ?>" class="btn btn-default btn-clear-filter">
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
            </div>
        <?php } ?>

        <div class="member-table-panel">
            <div class="panel-head">
                <div class="panel-head-left">
                    <i class="fa fa-list icon-badge"></i>
                    <h4><?php echo lang('beginning_balance_list'); ?></h4>
                </div>
                <div class="result-meta">
                    Showing <strong><?php echo number_format($row_count); ?></strong>
                    <?php echo $row_count === 1 ? 'record' : 'records'; ?>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered member-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width:60px;"><?php echo lang('sno'); ?></th>
                            <th><?php echo lang('account_no'); ?></th>
                            <th><?php echo lang('finance_account_name'); ?></th>
                            <th style="text-align:right;"><?php echo lang('beginning_balance_debit'); ?></th>
                            <th style="text-align:right;"><?php echo lang('beginning_balance_credit'); ?></th>
                            <th><?php echo lang('description'); ?></th>
                            <th><?php echo lang('status'); ?></th>
                            <th><?php echo lang('actioncolumn'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($row_count > 0) {
                            $i = 1;
                            foreach ($beginning_balances as $balance) {
                                $account_info = account_row_info($balance->account);
                                $account_name = $account_info ? $account_info->name : '-';
                                ?>
                                <tr>
                                    <td style="text-align:center;"><?php echo $i++; ?></td>
                                    <td><span class="member-id-chip"><?php echo htmlspecialchars($balance->account, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><?php echo htmlspecialchars($account_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="amount-cell"><?php echo number_format($balance->debit, 2); ?></td>
                                    <td class="amount-cell"><?php echo number_format($balance->credit, 2); ?></td>
                                    <td><?php echo $balance->description ? htmlspecialchars($balance->description, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                    <td>
                                        <?php if ((int) $balance->posted === 1) { ?>
                                            <span class="status-pill posted"><?php echo lang('beginning_balance_posted'); ?></span>
                                            <?php if ($balance->posted_date) { ?>
                                                <span class="status-sub"><?php echo date('M d, Y H:i', strtotime($balance->posted_date)); ?></span>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span class="status-pill not-posted"><?php echo lang('beginning_balance_not_posted'); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <?php if ((int) $balance->posted === 0) { ?>
                                                <a class="btn btn-primary btn-xs" href="<?php echo site_url(current_lang() . '/finance/beginning_balance_edit/' . encode_id($balance->id)); ?>">
                                                    <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-danger btn-xs btn-delete-balance" data-id="<?php echo encode_id($balance->id); ?>" data-account="<?php echo htmlspecialchars($balance->account, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-success btn-xs btn-post-balance" data-id="<?php echo encode_id($balance->id); ?>" data-account="<?php echo htmlspecialchars($balance->account, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa fa-check"></i> <?php echo lang('beginning_balance_post'); ?>
                                                </a>
                                            <?php } else { ?>
                                                <?php if (has_role(6, 'Void_transactions')) { ?>
                                                <a class="btn btn-danger btn-xs" href="<?php echo site_url(current_lang() . '/finance/beginning_balance_void/' . encode_id($balance->id)); ?>" onclick="return confirm('Void this beginning balance with a reversing GL entry? Original GL lines are kept for audit.');">
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
                                <td colspan="8">
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
                    <?php echo lang('beginning_balance_select_fiscal_year'); ?>
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
            $('.btn-delete-balance').click(function() {
                var balanceId = $(this).data('id');
                var account = $(this).data('account');
                var deleteUrl = '<?php echo site_url(current_lang() . '/finance/beginning_balance_delete/'); ?>/' + balanceId;

                swal({
                    title: "<?php echo lang('are_you_sure'); ?>",
                    text: "<?php echo lang('beginning_balance_delete_confirm'); ?>: " + account,
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
                var account = $(this).data('account');
                var postUrl = '<?php echo site_url(current_lang() . '/finance/beginning_balance_post/'); ?>/' + balanceId;

                swal({
                    title: "<?php echo lang('are_you_sure'); ?>",
                    text: "<?php echo lang('beginning_balance_post_confirm'); ?>" + (account ? " (" + account + ")" : ""),
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#5cb85c",
                    confirmButtonText: "<?php echo lang('beginning_balance_post'); ?>",
                    cancelButtonText: "<?php echo lang('cancel'); ?>",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(isConfirm) {
                    if (isConfirm) {
                        window.location.href = postUrl;
                    }
                });
            });
        });
    }
    initScripts();
})();
</script>
