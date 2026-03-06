<div class="row">
    <div class="col-lg-12">
        <div style="padding: 30px 10px; margin: auto;">
            <div class="btn-group" style="margin-bottom: 15px;">
                <a href="<?php echo site_url(current_lang() . '/ar/ar_balances'); ?>" class="btn btn-primary"><?php echo lang('ar_balances_title'); ?></a>
                <a href="<?php echo site_url(current_lang() . '/ar/ar_ledger'); ?>" class="btn btn-default"><?php echo lang('ar_ledger_title'); ?></a>
                <a href="<?php echo site_url(current_lang() . '/ar/ar_aging'); ?>" class="btn btn-default"><?php echo lang('ar_aging_title'); ?></a>
            </div>
            <div style="text-align: center;">
                <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong><?php echo lang('ar_balances_title'); ?></strong></h1>
                <h4><strong><?php echo lang('ar_as_of'); ?>: <?php echo format_date($as_of, false); ?></strong></h4>
            </div>

            <form method="get" action="<?php echo site_url(current_lang() . '/ar/ar_balances'); ?>" class="form-inline" style="margin: 15px 0;">
                <label><?php echo lang('ar_as_of'); ?>:</label>
                <input type="text" name="as_of" value="<?php echo htmlspecialchars($as_of); ?>" class="form-control" data-date-format="YYYY-MM-DD" style="width: 140px;" />
                <button type="submit" class="btn btn-primary"><?php echo lang('ar_run_report'); ?></button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th><?php echo lang('customer_id'); ?></th>
                            <th><?php echo lang('customer_name'); ?></th>
                            <th class="text-right"><?php echo lang('ar_balance'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        if (!empty($balances)) {
                            foreach ($balances as $row) {
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row->customer_number ?: $row->customerid); ?></td>
                                    <td><?php echo htmlspecialchars($row->customer_name ?: $row->customerid); ?></td>
                                    <td class="text-right"><?php echo number_format($row->balance, 2); ?></td>
                                </tr>
                            <?php } ?>
                            <tr class="active">
                                <td colspan="3" class="text-right"><strong><?php echo lang('ar_total'); ?>:</strong></td>
                                <td class="text-right"><strong><?php echo number_format($total_balance, 2); ?></strong></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4"><?php echo lang('ar_no_data'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; padding-top: 20px; border-top: 1px solid #ddd;">
                <a href="<?php echo site_url(current_lang() . '/ar/ar_balances_print?as_of=' . urlencode($as_of)); ?>" class="btn btn-primary" target="_blank"><?php echo lang('ar_print'); ?></a>
            </div>
        </div>
    </div>
</div>
