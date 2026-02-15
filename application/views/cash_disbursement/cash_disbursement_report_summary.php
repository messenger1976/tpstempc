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
        .tb-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .tb-table th, .tb-table td { border: 1px solid #333; padding: 8px 10px; }
        .tb-table th { background-color: #f0f0f0; font-weight: bold; text-align: left; }
        .tb-table th.col-debit, .tb-table th.col-credit { text-align: right; width: 140px; }
        .tb-table td.col-account-code { width: 120px; }
        .tb-table .col-debit, .tb-table .col-credit { text-align: right; width: 140px; }
        .tb-table .total-row { background-color: #e8e8e8; font-weight: bold; }
        .tb-table .total-row td.col-debit, .tb-table .total-row td.col-credit { text-align: right; }
        .no-print { margin-top: 20px; }
        @media print { .no-print { display: none; } }
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
        <div class="report-subtitle"><?php echo lang('cash_disbursement_report_summary'); ?></div>
        <div class="report-period">
            <?php if (!empty($date_from) || !empty($date_to)): ?>
                <?php echo lang('cash_disbursement_period'); ?>: <?php echo !empty($date_from) ? date('d-m-Y', strtotime($date_from)) : 'Start'; ?>
                to <?php echo !empty($date_to) ? date('d-m-Y', strtotime($date_to)) : 'End'; ?>
            <?php else: ?>
                <?php echo lang('all_dates'); ?>
            <?php endif; ?>
        </div>

        <table class="tb-table table table-bordered">
            <thead>
                <tr>
                    <th class="col-account-code"><?php echo lang('account_code'); ?></th>
                    <th class="col-account-name"><?php echo lang('account_name'); ?></th>
                    <th class="col-debit"><?php echo lang('journalentry_debit'); ?></th>
                    <th class="col-credit"><?php echo lang('journalentry_credit'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                if (!empty($summary)):
                    foreach ($summary as $row) { $grand_total += $row->total_amount; }
                    foreach ($summary as $row):
                ?>
                <tr>
                    <td class="col-account-code"><?php echo htmlspecialchars($row->account); ?></td>
                    <td class="col-account-name"><?php echo htmlspecialchars($row->account_name); ?></td>
                    <td class="col-debit"><?php echo number_format($row->total_amount, 2); ?></td>
                    <td class="col-credit"><?php echo number_format(0, 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="col-account-code">—</td>
                    <td class="col-account-name"><?php echo lang('cash_and_bank'); ?></td>
                    <td class="col-debit"><?php echo number_format(0, 2); ?></td>
                    <td class="col-credit"><?php echo number_format($grand_total, 2); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="2" class="text-right"><?php echo lang('total'); ?></td>
                    <td class="col-debit"><?php echo number_format($grand_total, 2); ?></td>
                    <td class="col-credit"><?php echo number_format($grand_total, 2); ?></td>
                </tr>
                <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center"><?php echo lang('no_records_found'); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <p><strong><?php echo lang('report_generated'); ?>:</strong> <?php echo date('d-m-Y H:i:s'); ?></p>

        <div class="no-print">
            <button onclick="window.print();" class="btn btn-primary"><i class="fa fa-print"></i> <?php echo lang('print'); ?></button>
            <?php
            $export_url = site_url(current_lang() . '/cash_disbursement/cash_disbursement_report_summary_export');
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
