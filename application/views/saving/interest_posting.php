<style>
.interest-post-page { margin-top: 4px; }
.interest-post-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.interest-post-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.interest-post-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.interest-post-page .cbu-alert.warning {
    background: #fff8ee;
    color: #c87f0a;
    border: 1px solid #f5e0b8;
}
.interest-post-page .cbu-panel,
.interest-post-page .member-table-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.interest-post-page .member-table-panel { overflow: hidden; }
.interest-post-page .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 18px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.interest-post-page .panel-head-left,
.interest-post-page .panel-head-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.interest-post-page .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.interest-post-page .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.interest-post-page .panel-head .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 14px;
    height: 36px;
}
.interest-post-page .panel-body { padding: 22px 20px 12px; }
.interest-post-page .form-horizontal .form-group { margin-bottom: 16px; }
.interest-post-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.interest-post-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.interest-post-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.interest-post-page .help-block {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
    margin-bottom: 0;
}
.interest-post-page .help-block.danger { color: #a94442; }
.interest-post-page .required { color: #ed5565; }
.interest-post-page .cbu-actions {
    margin-top: 8px;
    margin-bottom: 8px;
    padding-top: 12px;
    border-top: 1px solid #f0f2f3;
}
.interest-post-page .cbu-actions .btn {
    min-width: 160px;
    padding: 9px 20px;
    border-radius: 6px;
    font-weight: 600;
    margin-right: 8px;
}
.interest-post-page .cbu-actions .btn-primary,
.interest-post-page .preview-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.interest-post-page .result-banner {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin: 0 0 18px;
    padding: 16px 18px;
    border-radius: 10px;
    border: 1px solid #c9ebe3;
    background: linear-gradient(165deg, #e8f8f5 0%, #f7fcfa 100%);
    color: #0e7c69;
}
.interest-post-page .result-banner.warning {
    border-color: #f5e0b8;
    background: linear-gradient(165deg, #fff8ee 0%, #fffdf8 100%);
    color: #8a6d3b;
}
.interest-post-page .result-banner .stat {
    min-width: 120px;
}
.interest-post-page .result-banner .lbl {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    opacity: .8;
    margin-bottom: 4px;
}
.interest-post-page .result-banner .val {
    font-size: 20px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.interest-post-page .preview-meta {
    color: #676a6c;
    font-size: 13px;
    margin: 0 18px 0;
    padding: 12px 0 0;
}
.interest-post-page .preview-meta strong { color: #2f4050; }
.interest-post-page .member-table {
    margin: 0;
    background: #fff;
    border-collapse: separate;
    border-spacing: 0;
}
.interest-post-page .table-responsive {
    max-height: 62vh;
    overflow: auto;
}
.interest-post-page .member-table > thead > tr > th,
.interest-post-page .member-table > tfoot > tr > th {
    background: linear-gradient(180deg, #fbfcfd 0%, #f4f7f8 100%);
    border-bottom: 1px solid #e7eaec !important;
    border-top: 0 !important;
    color: #5a5e63;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    vertical-align: middle;
    white-space: nowrap;
    padding: 13px 14px;
}
.interest-post-page .member-table > thead > tr > th {
    position: sticky;
    top: 0;
    z-index: 2;
}
.interest-post-page .member-table > tfoot > tr > th {
    position: sticky;
    bottom: 0;
    z-index: 2;
}
.interest-post-page .member-table > tbody > tr > td {
    vertical-align: middle;
    border-color: #eef1f2;
    padding: 12px 14px;
    color: #2f4050;
    font-size: 13px;
}
.interest-post-page .member-table > tbody > tr:nth-child(even) { background: #fcfdfd; }
.interest-post-page .member-table > tbody > tr:hover { background: #f3fbf8 !important; }
.interest-post-page .member-table > tbody > tr.ineligible { color: #999; }
.interest-post-page .member-table > tbody > tr.ineligible:hover { background: #fafafa !important; }
.interest-post-page .member-id-chip {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 12px;
}
.interest-post-page .amount-cell {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    white-space: nowrap;
}
.interest-post-page .status-pill {
    display: inline-block;
    padding: 5px 11px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 700;
    color: #fff !important;
}
.interest-post-page .status-pill.eligible { background: #1ab394; }
.interest-post-page .status-pill.posted { background: #23c6c8; }
.interest-post-page .status-pill.warning { background: #f8ac59; }
.interest-post-page .status-pill.zero { background: #676a6c; }
.interest-post-page .override-pill {
    display: inline-block;
    margin-left: 4px;
    padding: 2px 8px;
    border-radius: 10px;
    background: #eef1f2;
    color: #676a6c;
    font-size: 10px;
    font-weight: 700;
}
.interest-post-page .preview-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    padding: 16px 18px;
    border-top: 1px solid #eef1f2;
    background: linear-gradient(180deg, #fbfcfd 0%, #f6f8f9 100%);
}
.interest-post-page .preview-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 9px 18px;
}
.interest-post-page .empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #999;
}
.interest-post-page .empty-state i {
    font-size: 40px;
    color: #c9ebe3;
    margin-bottom: 12px;
    display: block;
}
.interest-post-page .result-messages {
    margin: 12px 0 0;
    padding-left: 18px;
    color: #a94442;
}
#interest_posting_progress_overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 32, 0.55);
    z-index: 99999;
}
#interest_posting_progress_overlay .progress-card {
    position: absolute;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 420px;
    max-width: 90%;
    background: #fff;
    border-radius: 10px;
    padding: 24px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.22);
}
#interest_posting_progress_overlay h4 {
    margin: 0 0 12px;
    text-align: center;
    font-weight: 700;
    color: #2f4050;
}
#interest_progress_text {
    text-align: center;
    color: #666;
    margin-bottom: 14px;
}
#interest_posting_progress_overlay .progress {
    height: 22px;
    margin-bottom: 0;
    border-radius: 11px;
    overflow: hidden;
}
</style>

<?php
$prev_month = date('Y-m', strtotime('first day of last month'));
$prev_quarter = (int) ceil(date('n') / 3) - 1;
$prev_quarter_year = (int) date('Y');
if ($prev_quarter < 1) {
    $prev_quarter = 4;
    $prev_quarter_year--;
}

$sel_type = set_value('account_type') ? set_value('account_type') : (isset($selected_type) ? $selected_type->account : (isset($saved_account_type) ? $saved_account_type : ''));
$sel_month = set_value('period_month') ? set_value('period_month') : (isset($saved_period_month) && $saved_period_month ? $saved_period_month : $prev_month);
$sel_year = set_value('period_year') ? (int) set_value('period_year') : (isset($saved_period_year) && $saved_period_year ? (int) $saved_period_year : $prev_quarter_year);
$sel_quarter = set_value('period_quarter') ? (int) set_value('period_quarter') : (isset($saved_period_quarter) && $saved_period_quarter ? (int) $saved_period_quarter : $prev_quarter);
$sel_posting_freq = set_value('posting_frequency') ? set_value('posting_frequency') : (isset($posting_frequency) && $posting_frequency ? $posting_frequency : (isset($saved_posting_frequency) && $saved_posting_frequency ? $saved_posting_frequency : 'MONTHLY'));
if (!in_array($sel_posting_freq, array('MONTHLY', 'QUARTERLY'))) {
    $sel_posting_freq = 'MONTHLY';
}
?>

<div class="col-lg-12 interest-post-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="cbu-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="cbu-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="cbu-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="cbu-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>
    <div class="cbu-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-percent icon-badge"></i>
                <h4><?php echo lang('interest_posting'); ?></h4>
            </div>
            <div class="panel-head-right">
                <?php echo anchor(current_lang() . '/saving/interest_posting_history', '<i class="fa fa-history"></i> ' . lang('interest_posting_history'), 'class="btn btn-default"'); ?>
            </div>
        </div>
        <div class="panel-body">
            <?php echo form_open(current_lang() . "/saving/interest_posting", 'class="form-horizontal"'); ?>
            <input type="hidden" name="action" value="preview"/>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('interest_account_type'); ?> : <span class="required">*</span></label>
                <div class="col-lg-7">
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
                        <span class="help-block danger"><?php echo lang('interest_no_types_configured'); ?></span>
                    <?php } ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('interest_posting_frequency'); ?> : <span class="required">*</span></label>
                <div class="col-lg-7">
                    <select name="posting_frequency" id="posting_frequency" class="form-control">
                        <option value="MONTHLY" <?php echo ($sel_posting_freq == 'MONTHLY' ? 'selected="selected"' : ''); ?>><?php echo lang('interest_frequency_monthly'); ?></option>
                        <option value="QUARTERLY" <?php echo ($sel_posting_freq == 'QUARTERLY' ? 'selected="selected"' : ''); ?>><?php echo lang('interest_frequency_quarterly'); ?></option>
                    </select>
                    <span class="help-block"><?php echo lang('interest_posting_frequency_help'); ?></span>
                </div>
            </div>

            <div class="form-group" id="period_month_group">
                <label class="col-lg-3 control-label"><?php echo lang('interest_period_month'); ?> : <span class="required">*</span></label>
                <div class="col-lg-7">
                    <input type="month" name="period_month" class="form-control" value="<?php echo htmlspecialchars($sel_month, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo date('Y-m', strtotime('first day of last month')); ?>"/>
                    <span class="help-block"><?php echo lang('interest_period_completed_help'); ?></span>
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
                <div class="col-lg-4">
                    <select name="period_year" class="form-control">
                        <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 10; $y--) { ?>
                            <option value="<?php echo $y; ?>" <?php echo ($sel_year == $y ? 'selected="selected"' : ''); ?>><?php echo $y; ?></option>
                        <?php } ?>
                    </select>
                    <span class="help-block"><?php echo lang('interest_period_completed_help'); ?></span>
                </div>
            </div>

            <div class="form-group cbu-actions">
                <label class="col-lg-3 control-label">&nbsp;</label>
                <div class="col-lg-7">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-calculator"></i> <?php echo lang('interest_preview_btn'); ?>
                    </button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <?php if (isset($post_results)) { ?>
        <div class="result-banner <?php echo ($post_results['failed'] > 0 ? 'warning' : ''); ?>">
            <div class="stat">
                <span class="lbl"><?php echo lang('interest_result_posted'); ?></span>
                <span class="val"><?php echo (int) $post_results['posted']; ?></span>
            </div>
            <div class="stat">
                <span class="lbl"><?php echo lang('interest_result_total'); ?></span>
                <span class="val"><?php echo number_format($post_results['total_amount'], 2); ?></span>
            </div>
            <div class="stat">
                <span class="lbl"><?php echo lang('interest_result_skipped'); ?></span>
                <span class="val"><?php echo (int) $post_results['skipped']; ?></span>
            </div>
            <div class="stat">
                <span class="lbl"><?php echo lang('interest_result_failed'); ?></span>
                <span class="val"><?php echo (int) $post_results['failed']; ?></span>
            </div>
            <div class="stat" style="margin-left:auto; align-self:center;">
                <?php echo anchor(current_lang() . '/saving/interest_posting_history', '<i class="fa fa-history"></i> ' . lang('interest_posting_history'), 'class="btn btn-default"'); ?>
            </div>
        </div>
        <?php if (!empty($post_results['messages'])) { ?>
            <ul class="result-messages">
                <?php foreach ($post_results['messages'] as $msg) { ?>
                    <li><?php echo htmlspecialchars($msg); ?></li>
                <?php } ?>
            </ul>
        <?php } ?>
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
        $basis_label = lang('interest_basis_adb');
        $b = strtoupper($selected_type->interest_basis);
        if ($b == 'LOWEST') { $basis_label = lang('interest_basis_lowest'); }
        else if ($b == 'EOP') { $basis_label = lang('interest_basis_eop'); }
        $freq_label = ($posting_frequency == 'QUARTERLY' ? lang('interest_frequency_quarterly') : lang('interest_frequency_monthly'));
        ?>
        <div class="member-table-panel">
            <div class="panel-head">
                <div class="panel-head-left">
                    <i class="fa fa-list icon-badge"></i>
                    <h4>
                        <?php echo lang('interest_preview_title'); ?>:
                        <?php echo htmlspecialchars($selected_type->name); ?> -
                        <?php echo htmlspecialchars($period['label']); ?>
                    </h4>
                </div>
            </div>
            <p class="preview-meta">
                <?php echo $period['start'] . ' ' . lang('interest_to') . ' ' . $period['end']; ?>
                &nbsp;|&nbsp;
                <?php echo lang('interest_posting_frequency'); ?>: <strong><?php echo $freq_label; ?></strong>
                &nbsp;|&nbsp;
                <?php echo lang('interest_basis'); ?>: <strong><?php echo $basis_label; ?></strong>
                &nbsp;|&nbsp;
                <?php echo lang('account_interest_rate'); ?>: <strong><?php echo number_format((float) $selected_type->interest_rate, 2); ?>% p.a.</strong>
                &nbsp;|&nbsp;
                <?php echo lang('interest_min_balance'); ?>: <strong><?php echo number_format((float) $selected_type->interest_min_balance, 2); ?></strong>
            </p>

            <?php if (empty($preview)) { ?>
                <div class="empty-state">
                    <i class="fa fa-percent"></i>
                    <?php echo lang('interest_no_accounts_for_frequency'); ?>
                </div>
            <?php } else { ?>
                <?php echo form_open(current_lang() . "/saving/interest_posting", 'id="interest_post_form"'); ?>
                <input type="hidden" name="action" value="post"/>
                <input type="hidden" name="account_type" value="<?php echo htmlspecialchars($selected_type->account); ?>"/>
                <input type="hidden" name="posting_frequency" value="<?php echo htmlspecialchars($posting_frequency); ?>"/>
                <input type="hidden" name="period_month" value="<?php echo htmlspecialchars($sel_month); ?>"/>
                <input type="hidden" name="period_year" value="<?php echo htmlspecialchars((string) $sel_year); ?>"/>
                <input type="hidden" name="period_quarter" value="<?php echo htmlspecialchars((string) $sel_quarter); ?>"/>

                <div class="table-responsive">
                    <table class="table table-striped member-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align:center;"><input type="checkbox" id="check_all" checked="checked"/></th>
                                <th><?php echo lang('account_no'); ?></th>
                                <th><?php echo lang('member_id'); ?></th>
                                <th><?php echo lang('member_old_account_no'); ?></th>
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
                                <tr class="<?php echo (!$row['eligible'] ? 'ineligible' : ''); ?>">
                                    <td style="text-align:center;">
                                        <?php if ($row['eligible']) { ?>
                                            <input type="checkbox" name="accounts[]" class="account_check" value="<?php echo htmlspecialchars($row['account']); ?>" checked="checked"/>
                                        <?php } ?>
                                    </td>
                                    <td><span class="member-id-chip"><?php echo htmlspecialchars($row['account']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['member_id']); ?></td>
                                    <td><?php echo htmlspecialchars(!empty($row['savings_account_no']) ? $row['savings_account_no'] : '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['holder_name']); ?></td>
                                    <td>
                                        <?php
                                        $ef = isset($row['effective_frequency']) ? $row['effective_frequency'] : $posting_frequency;
                                        if ($ef == 'QUARTERLY') { echo lang('interest_frequency_quarterly'); }
                                        else { echo lang('interest_frequency_monthly'); }
                                        if (isset($row['frequency_source']) && $row['frequency_source'] == 'OVERRIDE') {
                                            echo ' <span class="override-pill">' . lang('interest_frequency_override_label') . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="amount-cell"><?php echo number_format($row['base_balance'], 2); ?></td>
                                    <td class="amount-cell"><?php echo number_format($row['annual_rate'], 2); ?></td>
                                    <td class="amount-cell"><?php echo $row['days']; ?></td>
                                    <td class="amount-cell"><?php echo number_format($row['interest'], 2); ?></td>
                                    <td>
                                        <?php
                                        if ($row['skip_reason'] == 'ALREADY_POSTED') {
                                            echo '<span class="status-pill posted">' . lang('interest_already_posted') . '</span>';
                                        } else if ($row['skip_reason'] == 'BELOW_MIN_BALANCE') {
                                            echo '<span class="status-pill warning">' . lang('interest_below_min_balance') . '</span>';
                                        } else if ($row['skip_reason'] == 'ZERO_INTEREST') {
                                            echo '<span class="status-pill zero">' . lang('interest_zero') . '</span>';
                                        } else {
                                            echo '<span class="status-pill eligible">' . lang('interest_eligible') . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="9" style="text-align: right;"><?php echo lang('interest_total_eligible'); ?> (<?php echo $eligible_count; ?>)</th>
                                <th class="amount-cell"><?php echo number_format($eligible_total, 2); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="preview-actions">
                    <?php if ($eligible_count > 0) { ?>
                        <button type="submit" id="btn_post_interest" class="btn btn-primary">
                            <i class="fa fa-check"></i> <?php echo lang('interest_post_btn'); ?>
                        </button>
                    <?php } ?>
                    <?php echo form_close(); ?>

                    <?php echo form_open(current_lang() . "/saving/interest_posting", 'id="interest_export_form"'); ?>
                    <input type="hidden" name="action" value="export"/>
                    <input type="hidden" name="account_type" value="<?php echo htmlspecialchars($selected_type->account); ?>"/>
                    <input type="hidden" name="posting_frequency" value="<?php echo htmlspecialchars($posting_frequency); ?>"/>
                    <input type="hidden" name="period_month" value="<?php echo htmlspecialchars($sel_month); ?>"/>
                    <input type="hidden" name="period_year" value="<?php echo htmlspecialchars((string) $sel_year); ?>"/>
                    <input type="hidden" name="period_quarter" value="<?php echo htmlspecialchars((string) $sel_quarter); ?>"/>
                    <button type="submit" class="btn btn-success" id="btn_export_interest">
                        <i class="fa fa-file-excel-o"></i> <?php echo lang('interest_export_excel'); ?>
                    </button>
                    <?php echo form_close(); ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<div id="interest_posting_progress_overlay">
    <div class="progress-card">
        <h4><?php echo lang('interest_posting_processing'); ?></h4>
        <p id="interest_progress_text"><?php echo lang('interest_posting_please_wait'); ?></p>
        <div class="progress">
            <div id="interest_progress_bar" class="progress-bar progress-bar-striped active" role="progressbar" style="width:5%; min-width:5%;">5%</div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    function initInterestPosting() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initInterestPosting, 50);
            return;
        }

        jQuery(document).ready(function ($) {
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

            var progressTimer = null;
            function startInterestProgress() {
                var $overlay = $('#interest_posting_progress_overlay');
                var $bar = $('#interest_progress_bar');
                var pct = 5;
                $bar.css('width', pct + '%').text(pct + '%');
                $overlay.show();
                $('body').css('overflow', 'hidden');
                $('#btn_post_interest').prop('disabled', true);

                progressTimer = setInterval(function () {
                    if (pct < 90) {
                        pct += Math.max(1, Math.floor((90 - pct) / 12));
                        if (pct > 90) {
                            pct = 90;
                        }
                        $bar.css('width', pct + '%').text(pct + '%');
                    }
                }, 400);
            }

            $('#interest_post_form').on('submit', function (e) {
                var selected = $('.account_check:checked').length;
                if (selected === 0) {
                    e.preventDefault();
                    if (typeof swal === 'function') {
                        swal('<?php echo lang('interest_no_accounts_selected'); ?>', '', 'warning');
                    } else {
                        alert('<?php echo lang('interest_no_accounts_selected'); ?>');
                    }
                    return false;
                }

                if ($(this).data('submitting')) {
                    return true;
                }

                e.preventDefault();
                var $form = $(this);

                if (typeof swal === 'function') {
                    swal({
                        title: '<?php echo lang('interest_post_btn'); ?>',
                        text: '<?php echo lang('interest_post_confirm'); ?> (' + selected + ')',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#1ab394',
                        confirmButtonText: '<?php echo lang('interest_post_btn'); ?>',
                        cancelButtonText: 'Cancel',
                        closeOnConfirm: true
                    }, function (isConfirm) {
                        if (isConfirm) {
                            $form.data('submitting', true);
                            startInterestProgress();
                            $form[0].submit();
                        }
                    });
                } else {
                    if (!confirm('<?php echo lang('interest_post_confirm'); ?> (' + selected + ')')) {
                        return false;
                    }
                    $form.data('submitting', true);
                    startInterestProgress();
                    $form[0].submit();
                }
                return false;
            });

            <?php if (isset($post_results)) { ?>
            (function () {
                var posted = <?php echo (int) $post_results['posted']; ?>;
                var failed = <?php echo (int) $post_results['failed']; ?>;
                var total = <?php echo json_encode(number_format($post_results['total_amount'], 2)); ?>;
                var msg = '<?php echo lang('interest_result_posted'); ?>: ' + posted
                    + '\n<?php echo lang('interest_result_total'); ?>: ' + total
                    + '\n<?php echo lang('interest_result_failed'); ?>: ' + failed;

                if (typeof swal === 'function') {
                    swal({
                        title: '<?php echo lang('interest_posting_complete'); ?>',
                        text: msg,
                        type: (failed > 0 ? 'warning' : 'success')
                    });
                }
            })();
            <?php } ?>
        });
    }

    initInterestPosting();
})();
</script>
