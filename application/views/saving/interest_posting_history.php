<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}
?>

<div style="width: 95%; margin: 0 auto 10px auto;">
    <form method="get" action="<?php echo site_url(current_lang() . '/saving/interest_posting_history'); ?>" class="form-inline" style="display: inline-block;">
        <select name="account_type_filter" class="form-control" onchange="this.form.submit();" style="margin-right: 8px;">
            <option value=""><?php echo lang('interest_all_account_types'); ?></option>
            <?php if (isset($interest_types) && !empty($interest_types)) { ?>
                <?php foreach ($interest_types as $t) { ?>
                    <option value="<?php echo $t->account; ?>" <?php echo (isset($account_type_filter) && (string) $account_type_filter === (string) $t->account ? 'selected="selected"' : ''); ?>>
                        <?php echo htmlspecialchars($t->name); ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>
        <select name="period_filter" class="form-control" onchange="this.form.submit();">
            <option value=""><?php echo lang('interest_all_periods'); ?></option>
            <?php if (isset($period_list) && !empty($period_list)) { ?>
                <?php foreach ($period_list as $p) { ?>
                    <option value="<?php echo htmlspecialchars($p->period_start); ?>" <?php echo (isset($period_filter) && (string) $period_filter === (string) $p->period_start ? 'selected="selected"' : ''); ?>>
                        <?php echo htmlspecialchars($p->label); ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>
    </form>
    <span style="float: right;">
        <?php echo anchor(current_lang() . '/saving/interest_posting', '<i class="fa fa-calculator"></i> ' . lang('interest_posting'), 'class="btn btn-primary"'); ?>
    </span>
</div>

<div class="table-responsive" style="width: 95%; margin: auto;">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('interest_posted_on'); ?></th>
                <th><?php echo lang('account_no'); ?></th>
                <th><?php echo lang('member_id'); ?></th>
                <th><?php echo lang('customer_name'); ?></th>
                <th><?php echo lang('interest_account_type'); ?></th>
                <th><?php echo lang('interest_period'); ?></th>
                <th><?php echo lang('interest_basis'); ?></th>
                <th style="text-align: right;"><?php echo lang('account_interest_rate'); ?> (%)</th>
                <th style="text-align: right;"><?php echo lang('interest_base_balance'); ?></th>
                <th style="text-align: right;"><?php echo lang('interest_days'); ?></th>
                <th style="text-align: right;"><?php echo lang('interest_amount'); ?></th>
                <th><?php echo lang('receipt_no'); ?></th>
                <th><?php echo lang('interest_status'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($history) && !empty($history)) { ?>
                <?php foreach ($history as $row) { ?>
                    <tr>
                        <td><?php echo date('d M Y H:i', strtotime($row->createdon)); ?></td>
                        <td><?php echo htmlspecialchars($row->account); ?></td>
                        <td><?php echo htmlspecialchars((string) $row->member_id); ?></td>
                        <td><?php echo htmlspecialchars((string) $row->holder_name); ?></td>
                        <td><?php echo htmlspecialchars((string) $row->type_name); ?></td>
                        <td>
                            <?php
                            if (strtoupper($row->period_type) == 'QUARTERLY') {
                                echo 'Q' . ceil(date('n', strtotime($row->period_start)) / 3) . ' ' . date('Y', strtotime($row->period_start));
                            } else {
                                echo strtoupper(date('M Y', strtotime($row->period_start)));
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $b = strtoupper($row->basis);
                            if ($b == 'LOWEST') { echo lang('interest_basis_lowest'); }
                            else if ($b == 'EOP') { echo lang('interest_basis_eop'); }
                            else { echo lang('interest_basis_adb'); }
                            ?>
                        </td>
                        <td style="text-align: right;"><?php echo number_format((float) $row->annual_rate, 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format((float) $row->base_balance, 2); ?></td>
                        <td style="text-align: right;"><?php echo $row->days; ?></td>
                        <td style="text-align: right;"><strong><?php echo number_format((float) $row->interest_amount, 2); ?></strong></td>
                        <td>
                            <?php if (!empty($row->receipt)) { ?>
                                <?php echo anchor(current_lang() . '/saving/receipt_view/' . $row->receipt, $row->receipt); ?>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (strtoupper($row->status) == 'VOIDED') { ?>
                                <span class="label label-danger"><?php echo lang('interest_status_voided'); ?></span>
                            <?php } else { ?>
                                <span class="label label-success"><?php echo lang('interest_status_posted'); ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="13" style="text-align: center; color: #888;"><?php echo lang('interest_no_history'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php if (isset($links)) { echo $links; } ?>
</div>
