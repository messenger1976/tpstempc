<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - <?php echo company_info()->name; ?></title>
    <link href="<?php echo base_url(); ?>media/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; font-family: Arial, sans-serif; font-size: 12px; }
        .report-container { max-width: 960px; margin: 20px auto; padding: 20px; }
        .company-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .company-name { font-size: 20px; font-weight: bold; margin: 0; }
        .report-title { text-align: center; font-size: 18px; font-weight: bold; margin: 15px 0; text-decoration: underline; }
        .report-subtitle { text-align: center; font-size: 14px; margin: 0 0 5px 0; color: #555; }
        .report-period { text-align: center; margin-bottom: 25px; color: #555; font-size: 12px; }
        .txn-group { margin-bottom: 28px; page-break-inside: avoid; }
        .txn-header { background: #f5f5f5; border: 1px solid #333; border-bottom: none; padding: 10px 12px; }
        .txn-header .txn-title { font-weight: bold; margin: 0 0 6px 0; }
        .txn-header .txn-meta { font-size: 11px; color: #555; }
        .tb-table { width: 100%; border-collapse: collapse; margin: 0; }
        .tb-table th, .tb-table td { border: 1px solid #333; padding: 6px 10px; }
        .tb-table th { background-color: #f0f0f0; font-weight: bold; text-align: left; }
        .tb-table th.col-debit, .tb-table th.col-credit { text-align: right; width: 120px; }
        .tb-table td.col-account-code { width: 110px; }
        .tb-table .col-debit, .tb-table .col-credit { text-align: right; width: 120px; }
        .tb-table .total-row { background-color: #e8e8e8; font-weight: bold; }
        .tb-table .total-row td { text-align: right; }
        .grand-total { margin-top: 20px; padding: 10px; background: #e0e0e0; border: 1px solid #333; font-weight: bold; text-align: right; }
        .no-print { margin-top: 20px; }
        @media print { .no-print { display: none; } body { font-size: 10px; } .txn-group { page-break-inside: avoid; } }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="company-header">
            <h1 class="company-name"><?php echo company_info()->name; ?></h1>
            <?php if (!empty(company_info()->address)): ?>
                <div><?php echo company_info()->address; ?></div>
            <?php endif; ?>
        </div>

        <div class="report-title"><?php echo lang('ledger_trial_balance'); ?></div>
        <div class="report-subtitle"><?php echo lang('cash_disbursement_report_details'); ?></div>
        <div class="report-period">
            <?php if (!empty($date_from) || !empty($date_to)): ?>
                <?php echo lang('cash_disbursement_period'); ?>: <?php echo !empty($date_from) ? date('d-m-Y', strtotime($date_from)) : 'Start'; ?>
                to <?php echo !empty($date_to) ? date('d-m-Y', strtotime($date_to)) : 'End'; ?>
            <?php else: ?>
                <?php echo lang('all_dates'); ?>
            <?php endif; ?>
        </div>

        <?php
        if (!empty($details)) {
            $grouped = array();
            foreach ($details as $row) {
                $key = $row->disburse_no;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = array(
                        'disburse_no' => $row->disburse_no,
                        'disburse_date' => $row->disburse_date,
                        'paid_to' => $row->paid_to,
                        'payment_method' => $row->payment_method,
                        'disburse_description' => isset($row->disburse_description) ? $row->disburse_description : '',
                        'lines' => array()
                    );
                }
                $grouped[$key]['lines'][] = $row;
            }
            $report_grand_total = 0;
            foreach ($grouped as $txn):
                $lines = $txn['lines'];
                $txn_total = 0;
                foreach ($lines as $l) $txn_total += $l->amount;
                $report_grand_total += $txn_total;
        ?>
        <div class="txn-group">
            <div class="txn-header">
                <div class="txn-title"><?php echo lang('cash_disbursement_no'); ?>: <?php echo htmlspecialchars($txn['disburse_no']); ?>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <?php echo lang('cash_disbursement_date'); ?>: <?php echo date('d-m-Y', strtotime($txn['disburse_date'])); ?>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <?php echo lang('cash_disbursement_paid_to'); ?>: <?php echo htmlspecialchars($txn['paid_to']); ?>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <?php echo lang('cash_disbursement_payment_method'); ?>: <?php echo htmlspecialchars($txn['payment_method']); ?>
                </div>
                <?php if (!empty($txn['disburse_description'])): ?>
                    <div class="txn-meta"><?php echo lang('cash_disbursement_description'); ?>: <?php echo htmlspecialchars($txn['disburse_description']); ?></div>
                <?php endif; ?>
            </div>
            <table class="tb-table table table-bordered">
                <thead>
                    <tr>
                        <th class="col-account-code"><?php echo lang('account_code'); ?></th>
                        <th><?php echo lang('account_name'); ?></th>
                        <th class="col-debit"><?php echo lang('journalentry_debit'); ?></th>
                        <th class="col-credit"><?php echo lang('journalentry_credit'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $l): ?>
                    <tr>
                        <td class="col-account-code"><?php echo htmlspecialchars($l->account); ?></td>
                        <td><?php echo htmlspecialchars($l->account_name); ?><?php if (!empty($l->line_description)): ?> — <?php echo htmlspecialchars($l->line_description); ?><?php endif; ?></td>
                        <td class="col-debit"><?php echo number_format($l->amount, 2); ?></td>
                        <td class="col-credit"><?php echo number_format(0, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td class="col-account-code">—</td>
                        <td><?php echo lang('cash_and_bank'); ?></td>
                        <td class="col-debit"><?php echo number_format(0, 2); ?></td>
                        <td class="col-credit"><?php echo number_format($txn_total, 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right;"><?php echo lang('total'); ?></td>
                        <td class="col-debit"><?php echo number_format($txn_total, 2); ?></td>
                        <td class="col-credit"><?php echo number_format($txn_total, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
            endforeach;
        ?>
        <div class="grand-total"><?php echo lang('total'); ?> (<?php echo count($grouped); ?> <?php echo lang('cash_disbursement_transactions'); ?>): <?php echo number_format($report_grand_total, 2); ?></div>
        <?php
        } else {
            echo '<p class="text-center">' . lang('no_records_found') . '</p>';
        }
        ?>

        <p><strong><?php echo lang('report_generated'); ?>:</strong> <?php echo date('d-m-Y H:i:s'); ?></p>

        <div class="no-print">
            <button onclick="window.print();" class="btn btn-primary"><i class="fa fa-print"></i> <?php echo lang('print'); ?></button>
            <?php
            $export_url = site_url(current_lang() . '/cash_disbursement/cash_disbursement_report_details_export');
            if (!empty($date_from)) { $export_url .= '?date_from=' . urlencode($date_from); $export_url .= !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; }
            elseif (!empty($date_to)) { $export_url .= '?date_to=' . urlencode($date_to); }
            ?>
            <a href="<?php echo $export_url; ?>" class="btn btn-success"><i class="fa fa-file-excel-o"></i> <?php echo lang('export_excel'); ?></a>
            <a href="javascript:window.close();" class="btn btn-default"><?php echo lang('close'); ?></a>
        </div>
    </div>
    <script src="<?php echo base_url(); ?>media/js/jquery-3.1.1.min.js"></script>
    <script src="<?php echo base_url(); ?>media/js/bootstrap.min.js"></script>
</body>
</html>
