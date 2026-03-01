<div class="row">
    <div class="col-lg-12">
        <div style="padding: 30px 10px; margin: auto;">
            <div class="btn-group" style="margin-bottom: 15px;">
                <a href="<?php echo site_url(current_lang() . '/ar/ar_balances'); ?>" class="btn btn-default"><?php echo lang('ar_balances_title'); ?></a>
                <a href="<?php echo site_url(current_lang() . '/ar/ar_ledger'); ?>" class="btn btn-primary"><?php echo lang('ar_ledger_title'); ?></a>
                <a href="<?php echo site_url(current_lang() . '/ar/ar_aging'); ?>" class="btn btn-default"><?php echo lang('ar_aging_title'); ?></a>
            </div>
            <div style="text-align: center;">
                <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong><?php echo lang('ar_ledger_title'); ?></strong></h1>
                <h4><strong><?php echo lang('ar_date_from'); ?>: <?php echo format_date($date_from, false); ?> &nbsp; <?php echo lang('ar_date_to'); ?>: <?php echo format_date($date_to, false); ?></strong></h4>
            </div>

            <form method="get" action="<?php echo site_url(current_lang() . '/ar/ar_ledger'); ?>" class="form-inline" style="margin: 15px 0;">
                <label><?php echo lang('ar_customer'); ?>:</label>
                <select name="customer_id" class="form-control" style="width: 220px;">
                    <option value=""><?php echo lang('ar_all_customers'); ?></option>
                    <?php if (!empty($customers)) { foreach ($customers as $c) { ?>
                        <option value="<?php echo htmlspecialchars($c->customerid); ?>" <?php echo ($customer_id == $c->customerid) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($c->name . ' (' . $c->customerid . ')'); ?></option>
                    <?php } } ?>
                </select>
                <label><?php echo lang('ar_date_from'); ?>:</label>
                <input type="text" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="form-control" style="width: 120px;" />
                <label><?php echo lang('ar_date_to'); ?>:</label>
                <input type="text" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="form-control" style="width: 120px;" />
                <button type="submit" class="btn btn-primary"><?php echo lang('ar_run_report'); ?></button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><?php echo lang('date'); ?></th>
                            <th><?php echo lang('ar_reference'); ?></th>
                            <th><?php echo lang('ar_description'); ?></th>
                            <th class="text-right"><?php echo lang('ar_debit'); ?></th>
                            <th class="text-right"><?php echo lang('ar_credit'); ?></th>
                            <th class="text-right"><?php echo lang('ar_running_balance'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($ledger)) {
                            foreach ($ledger as $row) {
                                $ref = '';
                                if (!empty($row->invoiceid)) {
                                    $ref = '#' . $row->invoiceid;
                                } elseif (!empty($row->refferenceID)) {
                                    $ref = $row->refferenceID;
                                }
                                ?>
                                <tr>
                                    <td><?php echo format_date($row->date, false); ?></td>
                                    <td><?php echo htmlspecialchars($ref); ?></td>
                                    <td><?php echo htmlspecialchars($row->description ?: '-'); ?></td>
                                    <td class="text-right"><?php echo $row->debit > 0 ? number_format($row->debit, 2) : ''; ?></td>
                                    <td class="text-right"><?php echo $row->credit > 0 ? number_format($row->credit, 2) : ''; ?></td>
                                    <td class="text-right"><?php echo number_format($row->running_balance, 2); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6"><?php echo lang('ar_no_data'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; padding-top: 20px; border-top: 1px solid #ddd;">
                <a href="<?php echo site_url(current_lang() . '/ar/ar_ledger_print?customer_id=' . urlencode($customer_id) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to)); ?>" class="btn btn-primary" target="_blank"><?php echo lang('ar_print'); ?></a>
            </div>
        </div>
    </div>
</div>
