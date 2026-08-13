<?php $this->load->view('loan/list_page_styles'); ?>
<style>
.member-list-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.member-list-page .head-actions .btn {
    border-radius: 6px;
    font-weight: 600;
}
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
.member-list-page .member-table > tbody > tr.coa-type-row > td {
    background: linear-gradient(180deg, #f7fcfa 0%, #eef8f5 100%) !important;
    color: #0e7c69;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .03em;
    text-transform: uppercase;
}
.member-list-page .member-table > tbody > tr.coa-type-row:hover { background: transparent !important; }
.member-list-page .coa-type-row .type-code {
    display: inline-block;
    margin-left: 8px;
    padding: 3px 8px;
    border-radius: 10px;
    background: #fff;
    color: #1ab394;
    font-size: 11px;
    font-weight: 700;
    text-transform: none;
    letter-spacing: 0;
}
.member-list-page .member-table > tbody > tr.coa-subtype-row > td {
    background: #fafbfc !important;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
}
.member-list-page .member-table > tbody > tr.coa-subtype-row:hover { background: transparent !important; }
.member-list-page .coa-name.level-0 { font-weight: 700; }
.member-list-page .coa-name.level-1 { padding-left: 28px; }
.member-list-page .coa-name.level-2 { padding-left: 44px; }
.member-list-page .coa-name.level-3 { padding-left: 60px; }
.member-list-page .coa-name.level-4 { padding-left: 76px; }
.member-list-page .coa-type-chip {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    background: #eef1f2;
    color: #676a6c;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
</style>

<?php
$account_chart_by_type = isset($account_chart_by_type) ? $account_chart_by_type : array();
$account_count = 0;
if (!empty($account_chart_by_type)) {
    foreach ($account_chart_by_type as $type_data) {
        if (!empty($type_data['data']) && is_array($type_data['data'])) {
            $account_count += count($type_data['data']);
        }
    }
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

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-sitemap icon-badge"></i>
                <h4><?php echo lang('finance_account_list'); ?></h4>
            </div>
            <div class="head-actions">
                <span class="result-meta">
                    <strong><?php echo number_format($account_count); ?></strong> accounts
                </span>
                <a class="btn btn-default btn-sm" href="<?php echo site_url(current_lang() . '/finance/finance_account_list_print'); ?>" target="_blank">
                    <i class="fa fa-print"></i> Print
                </a>
                <a class="btn btn-success btn-sm" href="<?php echo site_url(current_lang() . '/finance/finance_account_list_export'); ?>">
                    <i class="fa fa-file-excel-o"></i> Export to Excel
                </a>
                <a class="btn btn-primary btn-sm" href="<?php echo site_url(current_lang() . '/finance/finance_account_create'); ?>">
                    <i class="fa fa-plus"></i> <?php echo lang('finance_account_create'); ?>
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered member-table">
                <thead>
                    <tr>
                        <th><?php echo lang('finance_account_code'); ?></th>
                        <th><?php echo lang('finance_account_name'); ?></th>
                        <th><?php echo lang('finance_account_type'); ?></th>
                        <th><?php echo lang('actioncolumn'); ?></th>
                    </tr>
                </thead>
                <tbody>
            <?php
            if (!empty($account_chart_by_type)) {
                foreach ($account_chart_by_type as $type_id => $type_data) {
                    $account_type_info = $type_data['info'];
                    $accounts = $type_data['data'];

                    if (count($accounts) > 0) {
                        ?>
                        <tr class="coa-type-row">
                            <td colspan="4">
                                <?php echo htmlspecialchars(strtoupper($account_type_info->name), ENT_QUOTES, 'UTF-8'); ?>
                                <span class="type-code">Type: <?php echo htmlspecialchars($account_type_info->account, ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                        </tr>
                        <?php
                        $accounts_by_subtype = array();
                        $sub_type_info_map = array();
                        foreach ($accounts as $account) {
                            $sub_type_key = isset($account->sub_account_type) && !empty($account->sub_account_type) ? $account->sub_account_type : 'no_subtype';
                            if (!isset($accounts_by_subtype[$sub_type_key])) {
                                $accounts_by_subtype[$sub_type_key] = array();
                                if ($sub_type_key != 'no_subtype') {
                                    $sub_type_result = $this->finance_model->account_type_sub(null, $account_type_info->account, $sub_type_key);
                                    if ($sub_type_result && $sub_type_result->num_rows() > 0) {
                                        $sub_type_info_map[$sub_type_key] = $sub_type_result->row();
                                    }
                                }
                            }
                            $accounts_by_subtype[$sub_type_key][] = $account;
                        }

                        uksort($accounts_by_subtype, function ($a, $b) use ($sub_type_info_map) {
                            if ($a == 'no_subtype') return 1;
                            if ($b == 'no_subtype') return -1;
                            $sub_account_a = isset($sub_type_info_map[$a]->sub_account) ? (int) $sub_type_info_map[$a]->sub_account : 0;
                            $sub_account_b = isset($sub_type_info_map[$b]->sub_account) ? (int) $sub_type_info_map[$b]->sub_account : 0;
                            return $sub_account_a - $sub_account_b;
                        });

                        foreach ($accounts_by_subtype as $sub_type_key => $subtype_accounts) {
                            if ($sub_type_key != 'no_subtype') {
                                $sub_type_result = $this->finance_model->account_type_sub(null, $account_type_info->account, $sub_type_key);
                                if ($sub_type_result && $sub_type_result->num_rows() > 0) {
                                    $sub_type = $sub_type_result->row();
                                    ?>
                                    <tr class="coa-subtype-row">
                                        <td colspan="4">
                                            <?php echo htmlspecialchars($sub_type->name, ENT_QUOTES, 'UTF-8'); ?>
                                            (Sub Type: <?php echo htmlspecialchars($sub_type->sub_account, ENT_QUOTES, 'UTF-8'); ?>)
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }

                            foreach ($subtype_accounts as $account) {
                                $account_str = (string) $account->account;
                                $level = 0;

                                if (strlen($account_str) >= 4) {
                                    $last_4 = substr($account_str, -4);
                                    $last_2 = substr($account_str, -2);
                                    $last_1 = substr($account_str, -1);

                                    if ($last_4 == '0000') {
                                        $level = 0;
                                    } else if ($last_2 == '00') {
                                        $level = 1;
                                    } else if ($last_1 == '0') {
                                        $level = 2;
                                    } else {
                                        $level = 3;
                                    }
                                }

                                if ($sub_type_key != 'no_subtype') {
                                    $level = max(1, $level + 1);
                                }
                                ?>
                                <tr>
                                    <td><span class="member-id-chip"><?php echo htmlspecialchars($account->account, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td class="coa-name level-<?php echo (int) $level; ?>"><?php echo htmlspecialchars($account->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="coa-type-chip"><?php echo htmlspecialchars($account_type_info->name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td>
                                        <div class="action-btns">
                                            <?php if ($account->edit == 1) { ?>
                                                <a class="btn btn-primary btn-xs" href="<?php echo site_url(current_lang() . '/finance/finance_account_edit/' . encode_id($account->id)); ?>">
                                                    <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-danger btn-xs btn-delete-account" data-id="<?php echo encode_id($account->id); ?>" data-name="<?php echo htmlspecialchars($account->name, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    }
                }
            } else {
                ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="fa fa-sitemap"></i>
                                <?php echo lang('data_not_found'); ?>
                            </div>
                        </td>
                    </tr>
                <?php
            }
            ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }

        $(document).ready(function() {
            $('.btn-delete-account').click(function() {
                var accountId = $(this).data('id');
                var accountName = $(this).data('name');
                var deleteUrl = '<?php echo site_url(current_lang() . '/finance/finance_account_delete/'); ?>/' + accountId;

                swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover the chart of account: " + accountName + "!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(isConfirm) {
                    if (isConfirm) {
                        window.location.href = deleteUrl;
                    }
                });
            });
        });
    }
    initScripts();
})();
</script>
