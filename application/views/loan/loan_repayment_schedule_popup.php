<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo lang('loan_view_repayment_schedule'); ?> - <?php echo htmlspecialchars($loaninfo->LID); ?></title>
    <link href="<?php echo base_url(); ?>media/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
        body { padding: 15px; margin: 0; }
        .schedule-title { margin-bottom: 15px; font-size: 16px; }
    </style>
</head>
<body>
    <div class="schedule-title">
        <strong><?php echo lang('loan_view_repayment_schedule'); ?> - <?php echo lang('loan_LID'); ?> <?php echo htmlspecialchars($loaninfo->LID); ?></strong>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th><?php echo lang('sno'); ?></th>
                    <th><?php echo lang('due_date'); ?></th>
                    <th><?php echo lang('amount'); ?></th>
                    <th>Interest</th>
                    <th>Principle</th>
                    <th><?php echo lang('balance'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"><?php echo number_format($loaninfo->basic_amount, 2); ?></td>
                </tr>
                <?php
                if (count($schedule) > 0) {
                    $s = 1;
                    foreach ($schedule as $key => $value) {
                        ?>
                        <tr>
                            <td><?php echo $s++; ?></td>
                            <td style="text-align: center;"><?php echo date('d M, Y', strtotime($value->repaydate)); ?></td>
                            <td style="text-align: right;"><?php echo number_format($value->repayamount, 2); ?></td>
                            <td style="text-align: right;"><?php echo number_format($value->interest, 2); ?></td>
                            <td style="text-align: right;"><?php echo number_format($value->principle, 2); ?></td>
                            <td style="text-align: right;"><?php echo number_format($value->balance, 2); ?></td>
                        </tr>
                    <?php }
                }
                ?>
            </tbody>
        </table>
    </div>
    <div style="text-align: center; margin-top: 15px;">
        <a class="btn btn-primary btn-sm" href="<?php echo site_url(current_lang() . '/loan/print_repayment_schedule/' . $loanid); ?>" target="_blank"><?php echo lang('print'); ?></a>
        <a class="btn btn-success btn-sm" href="<?php echo site_url(current_lang() . '/loan/export_repayment_schedule/' . $loanid); ?>" target="_blank"><?php echo lang('export_to_excel'); ?></a>
    </div>
</body>
</html>
