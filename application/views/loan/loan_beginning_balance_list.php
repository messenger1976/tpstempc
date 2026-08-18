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
.member-list-page .member-table-panel {
    position: relative;
}
.member-list-page .member-table-panel.is-loading:after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.55);
    z-index: 5;
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
                        <?php $this->load->view('loan/loan_beginning_balance_table_body'); ?>
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
            var $page = $('.member-list-page');
            var $search = $('#bb-table-search');
            var $count = $('#bb-visible-count');
            var $label = $('#bb-record-label');
            var postUrlBase = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_post'); ?>';
            var tableDataUrl = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_table_data'); ?>';
            var deleteUrlBase = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_delete'); ?>';
            var activateUrlBase = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_activate'); ?>';
            var deactivateUrlBase = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_deactivate'); ?>';
            var voidUrlBase = '<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_void'); ?>';
            var actionInProgress = false;

            function tableRows() {
                return $('#bb-balance-table tbody tr').filter(function() {
                    return $(this).find('.empty-state').length === 0;
                });
            }

            function updateVisibleCount(visible) {
                $count.text(visible.toLocaleString());
                $label.text(visible === 1 ? 'record' : 'records');
            }

            function applySearchFilter() {
                var $rows = tableRows();
                var q = $.trim($search.val()).toLowerCase();
                if (!q) {
                    $rows.show();
                    updateVisibleCount($rows.length);
                    return;
                }
                var visible = 0;
                $rows.each(function() {
                    var match = $(this).text().toLowerCase().indexOf(q) !== -1;
                    $(this).toggle(match);
                    if (match) {
                        visible++;
                    }
                });
                updateVisibleCount(visible);
            }

            function currentFilters() {
                return {
                    fiscal_year_id: $('#fiscal_year_id').val() || '',
                    loan_product_id: $('#loan_product_id').val() || 'all'
                };
            }

            function showPageAlert(type, message) {
                var cls = type === 'success' ? 'success' : 'danger';
                var $alert = $('<div class="member-alert displaymessage"></div>')
                    .addClass(cls)
                    .text(message || '');
                var $existing = $page.children('.member-alert');
                if ($existing.length) {
                    $existing.replaceWith($alert);
                } else {
                    $page.prepend($alert);
                }
            }

            function reloadBalanceTable(filters) {
                var deferred = $.Deferred();
                var $panel = $('.member-table-panel').first();
                filters = filters || currentFilters();
                if (!filters.fiscal_year_id) {
                    deferred.resolve({ success: false, count: 0 });
                    return deferred.promise();
                }

                $panel.addClass('is-loading');
                $.ajax({
                    url: tableDataUrl,
                    type: 'GET',
                    dataType: 'json',
                    cache: false,
                    data: filters
                }).done(function(resp) {
                    if (resp && resp.success === 'Y') {
                        $('#bb-balance-table tbody').html(resp.html || '');
                        applySearchFilter();
                        deferred.resolve(resp);
                    } else {
                        deferred.reject(resp);
                    }
                }).fail(function(xhr) {
                    deferred.reject(xhr);
                }).always(function() {
                    $panel.removeClass('is-loading');
                });

                return deferred.promise();
            }

            function finishAction(success, title, message) {
                actionInProgress = false;
                showPageAlert(success ? 'success' : 'danger', message);
                swal(title, message, success ? 'success' : 'error');
            }

            function runRowAjax(url, progressTitle, progressText, successTitle, failTitle) {
                if (actionInProgress) {
                    return;
                }
                actionInProgress = true;
                swal({
                    title: progressTitle,
                    text: progressText,
                    type: 'info',
                    showConfirmButton: false
                });
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: currentFilters()
                }).done(function(json) {
                    if (json && json.success === 'Y') {
                        var filters = currentFilters();
                        if (!filters.fiscal_year_id && json.fiscal_year_id) {
                            filters.fiscal_year_id = json.fiscal_year_id;
                            $('#fiscal_year_id').val(json.fiscal_year_id);
                        }
                        reloadBalanceTable(filters).always(function() {
                            finishAction(true, successTitle, json.message);
                        });
                    } else {
                        var failMsg = (json && json.message) ? json.message : failTitle;
                        finishAction(false, failTitle, failMsg);
                    }
                }).fail(function() {
                    finishAction(false, failTitle, failTitle);
                });
            }

            $search.on('keyup input', applySearchFilter);

            $page.off('click.bbActions');

            $page.on('click.bbActions', '.btn-delete-balance', function() {
                var balanceId = $(this).data('id');
                var member = $(this).data('member');
                if (actionInProgress || !balanceId) {
                    return;
                }

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
                    if (!isConfirm) {
                        return;
                    }
                    runRowAjax(
                        deleteUrlBase + '/' + balanceId,
                        "<?php echo lang('button_delete'); ?>",
                        "<?php echo lang('loan_beginning_balance_deleting'); ?>",
                        "<?php echo lang('loan_beginning_balance_deleted'); ?>",
                        "<?php echo lang('loan_beginning_balance_delete_fail'); ?>"
                    );
                });
            });

            $page.on('click.bbActions', '.btn-post-balance', function() {
                var balanceId = $(this).data('id');
                var member = $(this).data('member');
                if (actionInProgress || !balanceId) {
                    return;
                }

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
                    if (!isConfirm) {
                        return;
                    }
                    runRowAjax(
                        postUrlBase + '/' + balanceId,
                        "<?php echo lang('loan_beginning_balance_post'); ?>",
                        "<?php echo lang('loan_beginning_balance_posting'); ?>",
                        "<?php echo lang('loan_beginning_balance_posted'); ?>",
                        "<?php echo lang('loan_beginning_balance_post_fail'); ?>"
                    );
                });
            });

            $page.on('click.bbActions', '.btn-activate-balance', function() {
                var balanceId = $(this).data('id');
                var member = $(this).data('member');
                if (actionInProgress || !balanceId) {
                    return;
                }

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
                    if (!isConfirm) {
                        return;
                    }
                    runRowAjax(
                        activateUrlBase + '/' + balanceId,
                        "<?php echo lang('loan_beginning_balance_activate'); ?>",
                        "<?php echo lang('loan_beginning_balance_activating'); ?>",
                        "<?php echo lang('loan_beginning_balance_activated'); ?>",
                        "<?php echo lang('loan_beginning_balance_activate_fail'); ?>"
                    );
                });
            });

            $page.on('click.bbActions', '.btn-void-balance', function() {
                var balanceId = $(this).data('id');
                var member = $(this).data('member');
                if (actionInProgress || !balanceId) {
                    return;
                }

                swal({
                    title: "<?php echo lang('are_you_sure'); ?>",
                    text: "<?php echo lang('loan_beginning_balance_void_confirm'); ?>" + (member ? " (Member: " + member + ")" : ""),
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d9534f",
                    confirmButtonText: "Void",
                    cancelButtonText: "<?php echo lang('cancel'); ?>",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(isConfirm) {
                    if (!isConfirm) {
                        return;
                    }
                    runRowAjax(
                        voidUrlBase + '/' + balanceId,
                        "Void",
                        "<?php echo lang('loan_beginning_balance_voiding'); ?>",
                        "Void",
                        "<?php echo lang('loan_beginning_balance_void_fail'); ?>"
                    );
                });
            });

            $page.on('click.bbActions', '.btn-deactivate-balance', function() {
                var balanceId = $(this).data('id');
                var member = $(this).data('member');
                if (actionInProgress || !balanceId) {
                    return;
                }

                swal({
                    title: "<?php echo lang('are_you_sure'); ?>",
                    text: "<?php echo lang('loan_void_bb_activation_confirm'); ?>" + (member ? " (Member: " + member + ")" : ""),
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d9534f",
                    confirmButtonText: "<?php echo lang('loan_void_bb_activation'); ?>",
                    cancelButtonText: "<?php echo lang('cancel'); ?>",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(isConfirm) {
                    if (!isConfirm) {
                        return;
                    }
                    runRowAjax(
                        deactivateUrlBase + '/' + balanceId,
                        "<?php echo lang('loan_void_bb_activation'); ?>",
                        "<?php echo lang('loan_beginning_balance_deactivating'); ?>",
                        "<?php echo lang('loan_void_bb_activation'); ?>",
                        "<?php echo lang('loan_beginning_balance_deactivate_fail'); ?>"
                    );
                });
            });
        });
    }
    initScripts();
})();
</script>
