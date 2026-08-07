<style type="text/css">
    .coa-list-actions {
        text-align: right;
        margin-right: 20px;
        margin-bottom: 15px;
    }
    .coa-ladder-container {
        margin-top: 10px;
        background: #fff;
        border: 1px solid #e7eaec;
        padding: 15px;
    }
    .coa-account-type-group {
        margin-bottom: 15px;
    }
    .coa-account-type-header {
        font-weight: bold;
        font-size: 14px;
        padding: 8px 10px;
        border-bottom: 2px solid #000;
        margin-bottom: 5px;
        background-color: #f0f0f0;
    }
    .coa-account-sub-type-header {
        font-weight: bold;
        font-size: 12px;
        padding: 6px 12px;
        border-bottom: 1px solid #999;
        margin-bottom: 3px;
        margin-top: 5px;
        background-color: #f8f8f8;
    }
    .coa-account-item {
        padding: 8px 0;
        border-bottom: 1px solid #ddd;
        position: relative;
        min-height: 30px;
        line-height: 1.6;
    }
    .coa-account-item.level-0 {
        padding-left: 12px;
        font-weight: bold;
        background-color: #f5f5f5;
        font-size: 13px;
    }
    .coa-account-item.level-1 {
        padding-left: 24px;
    }
    .coa-account-item.level-2 {
        padding-left: 36px;
    }
    .coa-account-item.level-3 {
        padding-left: 48px;
    }
    .coa-account-item.level-4 {
        padding-left: 60px;
    }
    .coa-account-row {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    .coa-account-code-name {
        display: table-cell;
        width: auto;
        vertical-align: middle;
        padding-right: 10px;
    }
    .coa-account-code-name .account-number {
        font-weight: bold;
    }
    .coa-account-type-name {
        display: table-cell;
        width: 120px;
        vertical-align: middle;
        font-size: 11px;
        color: #666;
        padding-right: 10px;
        text-align: right;
    }
    .coa-account-actions {
        display: table-cell;
        width: 160px;
        vertical-align: middle;
        text-align: right;
        white-space: nowrap;
        padding-right: 8px;
        font-weight: normal;
        font-size: 12px;
    }
    .coa-account-actions a {
        margin-left: 10px;
    }
    .coa-account-actions a.btn-delete-account {
        color: red;
    }
    .coa-empty {
        text-align: center;
        padding: 20px;
        color: #666;
    }
</style>

<div class="coa-list-actions">
    <a class="btn btn-primary" href="<?php echo site_url(current_lang() . '/finance/finance_account_create'); ?>"><?php echo lang('finance_account_create') ?></a>
    <a class="btn btn-success" href="<?php echo site_url(current_lang() . '/finance/finance_account_list_print'); ?>" target="_blank" style="margin-left: 10px;"><i class="fa fa-print"></i> Print</a>
    <a class="btn btn-info" href="<?php echo site_url(current_lang() . '/finance/finance_account_list_export'); ?>" style="margin-left: 10px;"><i class="fa fa-file-excel-o"></i> Export to Excel</a>
</div>

<div class="coa-ladder-container">
    <?php
    if (isset($account_chart_by_type) && count($account_chart_by_type) > 0) {
        foreach ($account_chart_by_type as $type_id => $type_data) {
            $account_type_info = $type_data['info'];
            $accounts = $type_data['data'];

            if (count($accounts) > 0) {
                ?>
                <div class="coa-account-type-group">
                    <div class="coa-account-type-header">
                        <?php echo strtoupper($account_type_info->name); ?> (Type: <?php echo $account_type_info->account; ?>)
                    </div>
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

                    uksort($accounts_by_subtype, function($a, $b) use ($sub_type_info_map) {
                        if ($a == 'no_subtype') return 1;
                        if ($b == 'no_subtype') return -1;
                        $sub_account_a = isset($sub_type_info_map[$a]->sub_account) ? (int)$sub_type_info_map[$a]->sub_account : 0;
                        $sub_account_b = isset($sub_type_info_map[$b]->sub_account) ? (int)$sub_type_info_map[$b]->sub_account : 0;
                        return $sub_account_a - $sub_account_b;
                    });

                    foreach ($accounts_by_subtype as $sub_type_key => $subtype_accounts) {
                        if ($sub_type_key != 'no_subtype') {
                            $sub_type_result = $this->finance_model->account_type_sub(null, $account_type_info->account, $sub_type_key);
                            if ($sub_type_result && $sub_type_result->num_rows() > 0) {
                                $sub_type = $sub_type_result->row();
                                ?>
                                <div class="coa-account-sub-type-header">
                                    <?php echo $sub_type->name; ?> (Sub Type: <?php echo $sub_type->sub_account; ?>)
                                </div>
                                <?php
                            }
                        }

                        foreach ($subtype_accounts as $account) {
                            $account_str = (string)$account->account;
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
                            <div class="coa-account-item level-<?php echo $level; ?>">
                                <div class="coa-account-row">
                                    <div class="coa-account-code-name">
                                        <span class="account-number"><?php echo $account->account; ?></span> - <?php echo $account->name; ?>
                                    </div>
                                    <div class="coa-account-type-name"><?php echo $account_type_info->name; ?></div>
                                    <div class="coa-account-actions">
                                        <?php if ($account->edit == 1) { ?>
                                            <a href="<?php echo site_url(current_lang() . '/finance/finance_account_edit/' . encode_id($account->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?></a>
                                            <a href="javascript:void(0);" class="btn-delete-account" data-id="<?php echo encode_id($account->id); ?>" data-name="<?php echo htmlspecialchars($account->name); ?>">
                                                <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
            }
        }
    } else {
        ?>
        <div class="coa-empty"><?php echo lang('data_not_found'); ?></div>
        <?php
    }
    ?>
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
