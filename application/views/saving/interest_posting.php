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

$prev_month = date('Y-m', strtotime('first day of last month'));
$prev_quarter = (int) ceil(date('n') / 3) - 1;
$prev_quarter_year = (int) date('Y');
if ($prev_quarter < 1) {
    $prev_quarter = 4;
    $prev_quarter_year--;
}

$sel_type = set_value('account_type') ? set_value('account_type') : (isset($selected_type) ? $selected_type->account : '');
$sel_month = set_value('period_month') ? set_value('period_month') : $prev_month;
$sel_year = set_value('period_year') ? (int) set_value('period_year') : $prev_quarter_year;
$sel_quarter = set_value('period_quarter') ? (int) set_value('period_quarter') : $prev_quarter;
$sel_posting_freq = set_value('posting_frequency') ? set_value('posting_frequency') : (isset($posting_frequency) && $posting_frequency ? $posting_frequency : 'MONTHLY');
if (!in_array($sel_posting_freq, array('MONTHLY', 'QUARTERLY'))) {
    $sel_posting_freq = 'MONTHLY';
}
?>

<div style="width: 95%; margin: 0 auto 10px auto; text-align: right;">
    <?php echo anchor(current_lang() . '/saving/interest_posting_history', '<i class="fa fa-history"></i> ' . lang('interest_posting_history'), 'class="btn btn-default"'); ?>
</div>

<?php echo form_open(current_lang() . "/saving/interest_posting", 'class="form-horizontal"'); ?>
<input type="hidden" name="action" value="preview"/>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('interest_account_type'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="account_type" id="interest_account_type" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php if (isset($interest_types) && !empty($interest_types)) { ?>
                <?php foreach ($interest_types as $t) { ?>
                    <option value="<?php echo $t->account; ?>"
                            data-default-frequency="<?php echo strtoupper($t->interest_frequency); ?>"
                            <?php echo ((string) $t->account === (string) $sel_type ? 'selected="selected"' : ''); ?>>
                        <?php echo htmlspecialchars($t->name); ?>
                        (<?php echo lang('interest_product_default'); ?>:
                        <?php
                        $pf = strtoupper($t->interest_frequency);
                        if ($pf == 'MONTHLY') { echo lang('interest_frequency_monthly'); }
                        else if ($pf == 'QUARTERLY') { echo lang('interest_frequency_quarterly'); }
                        else { echo lang('interest_frequency_none'); }
                        ?>,
                        <?php echo number_format((float) $t->interest_rate, 2); ?>% p.a.)
                    </option>
                <?php } ?>
            <?php } ?>
        </select>
        <?php if (!isset($interest_types) || empty($interest_types)) { ?>
            <span class="help-block" style="color: #a94442;"><?php echo lang('interest_no_types_configured'); ?></span>
        <?php } ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('interest_posting_frequency'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="posting_frequency" id="posting_frequency" class="form-control">
            <option value="MONTHLY" <?php echo ($sel_posting_freq == 'MONTHLY' ? 'selected="selected"' : ''); ?>><?php echo lang('interest_frequency_monthly'); ?></option>
            <option value="QUARTERLY" <?php echo ($sel_posting_freq == 'QUARTERLY' ? 'selected="selected"' : ''); ?>><?php echo lang('interest_frequency_quarterly'); ?></option>
        </select>
        <span class="help-block" style="font-size: 12px; color: #888;"><?php echo lang('interest_posting_frequency_help'); ?></span>
    </div>
</div>

<div class="form-group" id="period_month_group">
    <label class="col-lg-3 control-label"><?php echo lang('interest_period_month'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="month" name="period_month" class="form-control" value="<?php echo $sel_month; ?>" max="<?php echo date('Y-m', strtotime('first day of last month')); ?>"/>
        <span class="help-block" style="font-size: 12px; color: #888;"><?php echo lang('interest_period_completed_help'); ?></span>
    </div>
</div>

<div class="form-group" id="period_quarter_group" style="display: none;">
    <label class="col-lg-3 control-label"><?php echo lang('interest_period_quarter'); ?> : <span class="required">*</span></label>
    <div class="col-lg-3">
        <select name="period_quarter" class="form-control">
            <?php for ($q = 1; $q <= 4; $q++) { ?>
                <option value="<?php echo $q; ?>" <?php echo ($sel_quarter == $q ? 'selected="selected"' : ''); ?>>Q<?php echo $q; ?> (<?php echo date('M', mktime(0, 0, 0, ($q - 1) * 3 + 1, 1)); ?> - <?php echo date('M', mktime(0, 0, 0, ($q - 1) * 3 + 3, 1)); ?>)</option>
            <?php } ?>
        </select>
    </div>
    <div class="col-lg-3">
        <select name="period_year" class="form-control">
            <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 10; $y--) { ?>
                <option value="<?php echo $y; ?>" <?php echo ($sel_year == $y ? 'selected="selected"' : ''); ?>><?php echo $y; ?></option>
            <?php } ?>
        </select>
        <span class="help-block" style="font-size: 12px; color: #888;"><?php echo lang('interest_period_completed_help'); ?></span>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('interest_preview_btn'); ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>

<?php if (isset($post_results)) { ?>
    <div style="width: 95%; margin: 20px auto;">
        <div class="panel panel-<?php echo ($post_results['failed'] > 0 ? 'warning' : 'success'); ?>">
            <div class="panel-heading"><strong><?php echo lang('interest_posting_results'); ?> - <?php echo htmlspecialchars($selected_type->name); ?>, <?php echo $period['label']; ?> (<?php echo ($posting_frequency == 'QUARTERLY' ? lang('interest_frequency_quarterly') : lang('interest_frequency_monthly')); ?>)</strong></div>
            <div class="panel-body">
                <p>
                    <strong><?php echo lang('interest_result_posted'); ?>:</strong> <?php echo $post_results['posted']; ?> &nbsp;|&nbsp;
                    <strong><?php echo lang('interest_result_total'); ?>:</strong> <?php echo number_format($post_results['total_amount'], 2); ?> &nbsp;|&nbsp;
                    <strong><?php echo lang('interest_result_skipped'); ?>:</strong> <?php echo $post_results['skipped']; ?> &nbsp;|&nbsp;
                    <strong><?php echo lang('interest_result_failed'); ?>:</strong> <?php echo $post_results['failed']; ?>
                </p>
                <?php if (!empty($post_results['messages'])) { ?>
                    <ul>
                        <?php foreach ($post_results['messages'] as $msg) { ?>
                            <li style="color: #a94442;"><?php echo htmlspecialchars($msg); ?></li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <?php echo anchor(current_lang() . '/saving/interest_posting_history', lang('interest_posting_history'), 'class="btn btn-default btn-sm"'); ?>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (isset($preview)) { ?>
    <?php
    $eligible_count = 0;
    $eligible_total = 0;
    foreach ($preview as $row) {
        if ($row['eligible']) {
            $eligible_count++;
            $eligible_total += $row['interest'];
        }
    }
    ?>
    <div style="width: 95%; margin: 20px auto;">
        <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 8px;">
            <?php echo lang('interest_preview_title'); ?>:
            <?php echo htmlspecialchars($selected_type->name); ?> -
            <?php echo $period['label']; ?>
            (<?php echo $period['start'] . ' ' . lang('interest_to') . ' ' . $period['end']; ?>)
        </h3>
        <p style="color: #666;">
            <?php echo lang('interest_posting_frequency'); ?>:
            <strong><?php echo ($posting_frequency == 'QUARTERLY' ? lang('interest_frequency_quarterly') : lang('interest_frequency_monthly')); ?></strong>
            &nbsp;|&nbsp;
            <?php echo lang('interest_basis'); ?>:
            <strong>
                <?php
                $b = strtoupper($selected_type->interest_basis);
                if ($b == 'LOWEST') { echo lang('interest_basis_lowest'); }
                else if ($b == 'EOP') { echo lang('interest_basis_eop'); }
                else { echo lang('interest_basis_adb'); }
                ?>
            </strong> &nbsp;|&nbsp;
            <?php echo lang('account_interest_rate'); ?>: <strong><?php echo number_format((float) $selected_type->interest_rate, 2); ?>% p.a.</strong> &nbsp;|&nbsp;
            <?php echo lang('interest_min_balance'); ?>: <strong><?php echo number_format((float) $selected_type->interest_min_balance, 2); ?></strong>
        </p>

        <?php if (empty($preview)) { ?>
            <div class="label label-warning displaymessage"><?php echo lang('interest_no_accounts_for_frequency'); ?></div>
        <?php } else { ?>
            <?php echo form_open(current_lang() . "/saving/interest_posting", 'id="interest_post_form"'); ?>
            <input type="hidden" name="action" value="post"/>
            <input type="hidden" name="account_type" value="<?php echo htmlspecialchars($selected_type->account); ?>"/>
            <input type="hidden" name="posting_frequency" value="<?php echo htmlspecialchars($posting_frequency); ?>"/>
            <input type="hidden" name="period_month" value="<?php echo htmlspecialchars((string) $this->input->post('period_month')); ?>"/>
            <input type="hidden" name="period_year" value="<?php echo htmlspecialchars((string) $this->input->post('period_year')); ?>"/>
            <input type="hidden" name="period_quarter" value="<?php echo htmlspecialchars((string) $this->input->post('period_quarter')); ?>"/>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" id="check_all" checked="checked"/></th>
                            <th><?php echo lang('account_no'); ?></th>
                            <th><?php echo lang('member_id'); ?></th>
                            <th><?php echo lang('customer_name'); ?></th>
                            <th><?php echo lang('interest_frequency'); ?></th>
                            <th style="text-align: right;"><?php echo lang('interest_base_balance'); ?></th>
                            <th style="text-align: right;"><?php echo lang('account_interest_rate'); ?> (%)</th>
                            <th style="text-align: right;"><?php echo lang('interest_days'); ?></th>
                            <th style="text-align: right;"><?php echo lang('interest_amount'); ?></th>
                            <th><?php echo lang('interest_status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview as $row) { ?>
                            <tr <?php echo (!$row['eligible'] ? 'style="color: #999;"' : ''); ?>>
                                <td>
                                    <?php if ($row['eligible']) { ?>
                                        <input type="checkbox" name="accounts[]" class="account_check" value="<?php echo htmlspecialchars($row['account']); ?>" checked="checked"/>
                                    <?php } ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['account']); ?></td>
                                <td><?php echo htmlspecialchars($row['member_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['holder_name']); ?></td>
                                <td>
                                    <?php
                                    $ef = isset($row['effective_frequency']) ? $row['effective_frequency'] : $posting_frequency;
                                    if ($ef == 'QUARTERLY') { echo lang('interest_frequency_quarterly'); }
                                    else { echo lang('interest_frequency_monthly'); }
                                    if (isset($row['frequency_source']) && $row['frequency_source'] == 'OVERRIDE') {
                                        echo ' <span class="label label-default">' . lang('interest_frequency_override_label') . '</span>';
                                    }
                                    ?>
                                </td>
                                <td style="text-align: right;"><?php echo number_format($row['base_balance'], 2); ?></td>
                                <td style="text-align: right;"><?php echo number_format($row['annual_rate'], 2); ?></td>
                                <td style="text-align: right;"><?php echo $row['days']; ?></td>
                                <td style="text-align: right;"><strong><?php echo number_format($row['interest'], 2); ?></strong></td>
                                <td>
                                    <?php
                                    if ($row['skip_reason'] == 'ALREADY_POSTED') {
                                        echo '<span class="label label-info">' . lang('interest_already_posted') . '</span>';
                                    } else if ($row['skip_reason'] == 'BELOW_MIN_BALANCE') {
                                        echo '<span class="label label-warning">' . lang('interest_below_min_balance') . '</span>';
                                    } else if ($row['skip_reason'] == 'ZERO_INTEREST') {
                                        echo '<span class="label label-default">' . lang('interest_zero') . '</span>';
                                    } else {
                                        echo '<span class="label label-success">' . lang('interest_eligible') . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="8" style="text-align: right;"><?php echo lang('interest_total_eligible'); ?> (<?php echo $eligible_count; ?>)</th>
                            <th style="text-align: right;"><?php echo number_format($eligible_total, 2); ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if ($eligible_count > 0) { ?>
                <div style="margin: 15px 0;">
                    <input class="btn btn-primary" value="<?php echo lang('interest_post_btn'); ?>" type="submit"/>
                </div>
            <?php } ?>
            <?php echo form_close(); ?>
        <?php } ?>
    </div>
<?php } ?>

<script>
    $(document).ready(function () {
        function togglePeriodInputs() {
            var frequency = $('#posting_frequency').val() || 'MONTHLY';
            if (frequency === 'QUARTERLY') {
                $('#period_quarter_group').show();
                $('#period_month_group').hide();
            } else {
                $('#period_month_group').show();
                $('#period_quarter_group').hide();
            }
        }

        $('#posting_frequency').on('change', togglePeriodInputs);
        togglePeriodInputs();

        $('#check_all').on('click', function () {
            $('.account_check').prop('checked', $(this).prop('checked'));
        });

        $('#interest_post_form').on('submit', function () {
            var selected = $('.account_check:checked').length;
            if (selected === 0) {
                alert('<?php echo lang('interest_no_accounts_selected'); ?>');
                return false;
            }
            return confirm('<?php echo lang('interest_post_confirm'); ?> (' + selected + ')');
        });
    });
</script>
