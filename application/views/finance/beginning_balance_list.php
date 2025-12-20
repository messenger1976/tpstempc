<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px; margin-bottom: 20px;">
        <a class="btn btn-primary" href="<?php echo site_url(current_lang() . '/finance/beginning_balance_create'); ?>"><?php echo lang('beginning_balance_create'); ?></a>
    </div>
    
    <!-- Fiscal Year Selection Form -->
    <div class="panel panel-default" style="margin-bottom: 20px;">
        <div class="panel-heading">
            <h4><?php echo lang('select_fiscal_year'); ?></h4>
        </div>
        <div class="panel-body">
            <form method="post" action="<?php echo site_url(current_lang() . '/finance/beginning_balance_list'); ?>" class="form-inline">
                <div class="form-group">
                    <label for="fiscal_year_id"><?php echo lang('fiscal_year'); ?>:</label>
                    <select name="fiscal_year_id" id="fiscal_year_id" class="form-control" style="margin-left: 10px; margin-right: 10px;">
                        <option value=""><?php echo lang('select_default_text'); ?></option>
                        <?php foreach ($fiscal_years as $fy) { ?>
                            <option value="<?php echo $fy->id; ?>" <?php echo (isset($selected_fiscal_year_id) && $selected_fiscal_year_id == $fy->id ? 'selected' : ''); ?>>
                                <?php echo $fy->name . ' (' . date('M d, Y', strtotime($fy->start_date)) . ' - ' . date('M d, Y', strtotime($fy->end_date)) . ')'; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-info"><?php echo lang('button_view'); ?></button>
            </form>
        </div>
    </div>

    <?php if (isset($selected_fiscal_year_id) && $selected_fiscal_year_id) { ?>
        <?php if (isset($fiscal_year)) { ?>
            <div class="alert alert-info">
                <strong><?php echo lang('fiscal_year'); ?>:</strong> <?php echo $fiscal_year->name; ?> 
                (<?php echo date('M d, Y', strtotime($fiscal_year->start_date)) . ' - ' . date('M d, Y', strtotime($fiscal_year->end_date)); ?>)
            </div>
        <?php } ?>
        
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width:80px;"><?php echo lang('sno'); ?></th>
                    <th><?php echo lang('account_no'); ?></th>
                    <th><?php echo lang('finance_account_name'); ?></th>
                    <th><?php echo lang('beginning_balance_debit'); ?></th>
                    <th><?php echo lang('beginning_balance_credit'); ?></th>
                    <th><?php echo lang('description'); ?></th>
                    <th><?php echo lang('status'); ?></th>
                    <th style="width:200px;"><?php echo lang('actioncolumn'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                if (count($beginning_balances) > 0) {
                    foreach ($beginning_balances as $balance) {
                        $account_info = account_row_info($balance->account);
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $balance->account; ?></td>
                            <td><?php echo $account_info ? $account_info->name : '-'; ?></td>
                            <td style="text-align: right;"><?php echo number_format($balance->debit, 2); ?></td>
                            <td style="text-align: right;"><?php echo number_format($balance->credit, 2); ?></td>
                            <td><?php echo $balance->description ? $balance->description : '-'; ?></td>
                            <td>
                                <?php if ($balance->posted == 1) { ?>
                                    <span class="label label-success"><?php echo lang('beginning_balance_posted'); ?></span>
                                    <?php if ($balance->posted_date) { ?>
                                        <br><small><?php echo date('M d, Y H:i', strtotime($balance->posted_date)); ?></small>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="label label-warning"><?php echo lang('beginning_balance_not_posted'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($balance->posted == 0) { ?>
                                    <a href="<?php echo site_url(current_lang() . '/finance/beginning_balance_edit/' . encode_id($balance->id)); ?>">
                                        <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                                    </a>
                                    <a href="javascript:void(0);" class="btn-delete-balance" data-id="<?php echo encode_id($balance->id); ?>" data-account="<?php echo htmlspecialchars($balance->account); ?>" style="color: red; margin-left: 10px;">
                                        <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                                    </a>
                                    <a href="<?php echo site_url(current_lang() . '/finance/beginning_balance_post/' . encode_id($balance->id)); ?>" 
                                       onclick="return confirm('<?php echo lang('beginning_balance_post_confirm'); ?>');" 
                                       style="color: green; margin-left: 10px;">
                                        <i class="fa fa-check"></i> <?php echo lang('beginning_balance_post'); ?>
                                    </a>
                                <?php } else { ?>
                                    <span class="text-muted"><?php echo lang('beginning_balance_no_edit'); ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }
                } else {
                    ?>
                    <tr>
                        <td colspan="8"><?php echo lang('data_not_found'); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="alert alert-warning">
            <?php echo lang('beginning_balance_select_fiscal_year'); ?>
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
        });
    }
    initScripts();
})();
</script>
