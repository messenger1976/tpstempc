<?php $this->load->view('loan/list_page_styles'); ?>
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet"/>
<style>
.member-list-page .cic-intro {
    margin: 0 0 16px;
    color: #676a6c;
    font-size: 13px;
}
.member-list-page .cic-kpis {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 18px;
}
.member-list-page .cic-kpi {
    flex: 1 1 160px;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 14px 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.member-list-page .cic-kpi .lbl {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: #888;
    margin-bottom: 4px;
}
.member-list-page .cic-kpi .val {
    font-size: 22px;
    font-weight: 700;
    color: #2f4050;
    font-variant-numeric: tabular-nums;
}
.member-list-page .cic-kpi.valid .val { color: #1ab394; }
.member-list-page .cic-kpi.invalid .val { color: #ed5565; }
.member-list-page .filter-check {
    flex: 0 1 auto;
    min-width: 180px;
    padding-bottom: 6px;
}
.member-list-page .filter-check label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    font-size: 12px;
    font-weight: 600;
    color: #676a6c;
    text-transform: none;
    letter-spacing: 0;
    cursor: pointer;
}
.member-list-page .filter-check input[type="checkbox"] {
    margin: 0;
}
.member-list-page .privacy-note {
    margin: 0;
    padding: 12px 16px;
    background: #f8fafb;
    border-top: 1px solid #eef1f2;
    color: #888;
    font-size: 12px;
    line-height: 1.5;
}
.member-list-page .status-pill.valid { background: #1ab394; }
.member-list-page .status-pill.invalid { background: #ed5565; }
.member-list-page .error-cell {
    color: #c0392b;
    font-size: 12px;
    font-weight: 600;
    max-width: 280px;
}
.member-list-page .member-table > tbody > tr.cic-invalid { background: #fdeceb !important; }
.member-list-page .member-table > tbody > tr.cic-invalid:hover { background: #f9d6d5 !important; }
.member-list-page .truncate-note {
    text-align: center;
    color: #888;
    font-size: 12px;
    font-weight: 600;
    padding: 16px !important;
}
.member-list-page .filter-field.date-field { flex: 0 1 190px; min-width: 170px; }
.member-list-page .date-field .input-group { width: 100%; }
.member-list-page .date-field .input-group-addon {
    background: #f8fafb;
    cursor: pointer;
}
.member-list-page .date-field .input-group-addon:hover { color: #1ab394; }
.member-list-page .bootstrap-datetimepicker-widget { z-index: 1060 !important; }
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
</style>

<?php
$as_of = isset($as_of) ? $as_of : '';
$include_closed = !empty($include_closed);
$export_invalid = !empty($export_invalid);
$total_rows = isset($total_rows) ? (int) $total_rows : 0;
$valid_count = isset($valid_count) ? (int) $valid_count : 0;
$invalid_count = isset($invalid_count) ? (int) $invalid_count : 0;
$preview = isset($preview) ? $preview : array();
$preview_limit = isset($preview_limit) ? (int) $preview_limit : 50;
$company_name = function_exists('company_info') && company_info() ? company_info()->name : '';
$export_url = site_url(current_lang() . '/cic/export'
    . '?as_of=' . urlencode($as_of)
    . ($include_closed ? '&include_closed=1' : '')
    . ($export_invalid ? '&export_invalid=1' : ''));
$clear_url = site_url(current_lang() . '/cic/index');
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
                <i class="fa fa-university icon-badge"></i>
                <h4><?php echo lang('cic_module_title'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <?php if ($company_name !== '') { ?>
                <p class="cic-intro">
                    <strong><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></strong>
                    &mdash; <?php echo lang('cic_module_intro'); ?>
                </p>
            <?php } else { ?>
                <p class="cic-intro"><?php echo lang('cic_module_intro'); ?></p>
            <?php } ?>

            <form method="get" action="<?php echo site_url(current_lang() . '/cic/index'); ?>" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field date-field">
                        <label><?php echo lang('cic_as_of'); ?></label>
                        <div class="input-group date" id="cic_as_of">
                            <input type="text" class="form-control" id="as_of" name="as_of" value="<?php echo htmlspecialchars($as_of, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                            <span class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <div class="filter-field filter-check">
                        <label>
                            <input type="checkbox" name="include_closed" value="1" <?php echo $include_closed ? 'checked="checked"' : ''; ?> />
                            <?php echo lang('cic_include_closed'); ?>
                        </label>
                    </div>
                    <div class="filter-field filter-check">
                        <label>
                            <input type="checkbox" name="export_invalid" value="1" <?php echo $export_invalid ? 'checked="checked"' : ''; ?> />
                            <?php echo lang('cic_export_invalid'); ?>
                        </label>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo $clear_url; ?>" class="btn btn-default">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-default">
                            <i class="fa fa-search"></i> <?php echo lang('cic_preview'); ?>
                        </button>
                        <a class="btn btn-primary" href="<?php echo $export_url; ?>">
                            <i class="fa fa-download"></i> <?php echo lang('cic_download_csv'); ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="cic-kpis">
        <div class="cic-kpi">
            <span class="lbl"><?php echo lang('cic_total'); ?></span>
            <span class="val"><?php echo number_format($total_rows); ?></span>
        </div>
        <div class="cic-kpi valid">
            <span class="lbl"><?php echo lang('cic_valid'); ?></span>
            <span class="val"><?php echo number_format($valid_count); ?></span>
        </div>
        <div class="cic-kpi invalid">
            <span class="lbl"><?php echo lang('cic_invalid'); ?></span>
            <span class="val"><?php echo number_format($invalid_count); ?></span>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-list icon-badge"></i>
                <h4><?php echo lang('cic_summary'); ?></h4>
            </div>
            <div class="result-meta">
                Showing <strong><?php echo number_format(count($preview)); ?></strong>
                of <strong><?php echo number_format($total_rows); ?></strong>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered member-table">
                <thead>
                    <tr>
                        <th style="text-align:center; width:60px;">#</th>
                        <th><?php echo lang('cic_col_status'); ?></th>
                        <th><?php echo lang('cic_col_borrower_id'); ?></th>
                        <th><?php echo lang('cic_col_name'); ?></th>
                        <th><?php echo lang('cic_col_loan_date'); ?></th>
                        <th style="text-align:right;"><?php echo lang('cic_col_loan_amount'); ?></th>
                        <th style="text-align:right;"><?php echo lang('cic_col_outstanding'); ?></th>
                        <th><?php echo lang('cic_col_payment_status'); ?></th>
                        <th><?php echo lang('cic_col_errors'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($preview)) { ?>
                        <?php foreach ($preview as $item) {
                            $r = $item['row'];
                            $is_valid = !empty($item['valid']);
                            ?>
                            <tr class="<?php echo $is_valid ? '' : 'cic-invalid'; ?>">
                                <td style="text-align:center;"><?php echo (int) $item['row_number']; ?></td>
                                <td>
                                    <span class="status-pill <?php echo $is_valid ? 'valid' : 'invalid'; ?>">
                                        <?php echo $is_valid ? lang('cic_row_valid') : lang('cic_row_invalid'); ?>
                                    </span>
                                </td>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($r['borrower_id']); ?></span></td>
                                <td><?php echo htmlspecialchars($r['name']); ?></td>
                                <td><?php echo htmlspecialchars($r['loan_date']); ?></td>
                                <td class="amount-cell"><?php echo number_format((float) $r['loan_amount'], 2); ?></td>
                                <td class="amount-cell"><?php echo number_format((float) $r['outstanding_balance'], 2); ?></td>
                                <td><?php echo htmlspecialchars($r['payment_status']); ?></td>
                                <td class="error-cell">
                                    <?php
                                    if (!$is_valid && !empty($item['errors'])) {
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
                                <td colspan="9" class="truncate-note">
                                    <?php echo sprintf(lang('cic_preview_truncated'), $preview_limit, $total_rows); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fa fa-list"></i>
                                    <?php echo lang('cic_no_data'); ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <p class="privacy-note">
            <i class="fa fa-lock"></i>
            <?php echo lang('cic_privacy_note'); ?>
        </p>
    </div>
</div>

<script src="<?php echo base_url(); ?>media/js/script/moment.js"></script>
<script type="text/javascript">
(function() {
    function bindPicker() {
        if (typeof jQuery === 'undefined' || typeof moment === 'undefined') {
            setTimeout(bindPicker, 50);
            return;
        }
        if (typeof jQuery.fn.datetimepicker === 'undefined') {
            var script = document.createElement('script');
            script.src = '<?php echo base_url(); ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
            script.onload = bindPicker;
            document.head.appendChild(script);
            return;
        }
        var $asOf = jQuery('#cic_as_of');
        if ($asOf.length && !$asOf.data('DateTimePicker')) {
            $asOf.datetimepicker({ pickTime: false, format: 'YYYY-MM-DD' });
        }
    }
    bindPicker();
})();
</script>
