<div class="row">
    <div class="col-lg-12">
        <div style="padding: 30px 10px; margin: auto;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h3><strong><?php echo htmlspecialchars(company_info()->name); ?></strong></h3>
                <h1><strong><?php echo lang('cic_module_title'); ?></strong></h1>
                <p class="text-muted"><?php echo lang('cic_module_intro'); ?></p>
            </div>

            <?php if ($this->session->flashdata('warning')) { ?>
                <div class="alert alert-warning"><?php echo $this->session->flashdata('warning'); ?></div>
            <?php } ?>
            <?php if ($this->session->flashdata('message')) { ?>
                <div class="alert alert-success"><?php echo $this->session->flashdata('message'); ?></div>
            <?php } ?>

            <form method="get" action="<?php echo site_url(current_lang() . '/cic/index'); ?>" class="form-inline" style="margin: 15px 0;">
                <div class="form-group" style="margin-right: 10px;">
                    <label><?php echo lang('cic_as_of'); ?>:</label>
                    <input type="text" name="as_of" value="<?php echo htmlspecialchars($as_of); ?>" class="form-control" style="width: 140px;" />
                </div>
                <div class="checkbox" style="margin-right: 15px;">
                    <label>
                        <input type="checkbox" name="include_closed" value="1" <?php echo !empty($include_closed) ? 'checked="checked"' : ''; ?> />
                        <?php echo lang('cic_include_closed'); ?>
                    </label>
                </div>
                <div class="checkbox" style="margin-right: 15px;">
                    <label>
                        <input type="checkbox" name="export_invalid" value="1" <?php echo !empty($export_invalid) ? 'checked="checked"' : ''; ?> />
                        <?php echo lang('cic_export_invalid'); ?>
                    </label>
                </div>
                <button type="submit" class="btn btn-default"><?php echo lang('cic_preview'); ?></button>
                <a class="btn btn-primary"
                   href="<?php echo site_url(current_lang() . '/cic/export'
                       . '?as_of=' . urlencode($as_of)
                       . ($include_closed ? '&include_closed=1' : '')
                       . ($export_invalid ? '&export_invalid=1' : '')); ?>">
                    <?php echo lang('cic_download_csv'); ?>
                </a>
            </form>

            <div class="alert alert-info">
                <strong><?php echo lang('cic_summary'); ?>:</strong>
                <?php echo lang('cic_total'); ?>: <?php echo (int) $total_rows; ?> |
                <?php echo lang('cic_valid'); ?>: <?php echo (int) $valid_count; ?> |
                <?php echo lang('cic_invalid'); ?>: <?php echo (int) $invalid_count; ?>
                <br/>
                <small><?php echo lang('cic_privacy_note'); ?></small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo lang('cic_col_status'); ?></th>
                            <th><?php echo lang('cic_col_borrower_id'); ?></th>
                            <th><?php echo lang('cic_col_name'); ?></th>
                            <th><?php echo lang('cic_col_loan_date'); ?></th>
                            <th class="text-right"><?php echo lang('cic_col_loan_amount'); ?></th>
                            <th class="text-right"><?php echo lang('cic_col_outstanding'); ?></th>
                            <th><?php echo lang('cic_col_payment_status'); ?></th>
                            <th><?php echo lang('cic_col_errors'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($preview)) { ?>
                            <?php foreach ($preview as $item) {
                                $r = $item['row'];
                                ?>
                                <tr class="<?php echo $item['valid'] ? '' : 'danger'; ?>">
                                    <td><?php echo (int) $item['row_number']; ?></td>
                                    <td><?php echo $item['valid'] ? lang('cic_row_valid') : lang('cic_row_invalid'); ?></td>
                                    <td><?php echo htmlspecialchars($r['borrower_id']); ?></td>
                                    <td><?php echo htmlspecialchars($r['name']); ?></td>
                                    <td><?php echo htmlspecialchars($r['loan_date']); ?></td>
                                    <td class="text-right"><?php echo number_format((float) $r['loan_amount'], 2); ?></td>
                                    <td class="text-right"><?php echo number_format((float) $r['outstanding_balance'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($r['payment_status']); ?></td>
                                    <td>
                                        <?php
                                        if (!$item['valid'] && !empty($item['errors'])) {
                                            $msgs = array();
                                            foreach ($item['errors'] as $err) {
                                                $msgs[] = htmlspecialchars($err['message']);
                                            }
                                            echo implode('<br/>', $msgs);
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if ($total_rows > $preview_limit) { ?>
                                <tr>
                                    <td colspan="9" class="text-muted">
                                        <?php echo sprintf(lang('cic_preview_truncated'), (int) $preview_limit, (int) $total_rows); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="9"><?php echo lang('cic_no_data'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
