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
        .schedule-empty { margin-bottom: 15px; }
        .schedule-empty form { margin-top: 10px; }
        .schedule-empty .form-inline .form-group { margin-right: 8px; }
    </style>
</head>
<body>
    <div class="schedule-title">
        <strong><?php echo lang('loan_view_repayment_schedule'); ?> - <?php echo lang('loan_LID'); ?> <?php echo htmlspecialchars($loaninfo->LID); ?></strong>
    </div>
    <?php
    if ($this->session->flashdata('message') != '') {
        echo '<div class="alert alert-info">' . $this->session->flashdata('message') . '</div>';
    }
    if ($this->session->flashdata('warning') != '') {
        echo '<div class="alert alert-danger">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>
    <?php if (count($schedule) < 1) { ?>
        <div class="alert alert-warning schedule-empty">
            <strong><?php echo lang('loan_schedule_none_yet'); ?></strong>
            <div><?php echo lang('loan_schedule_none_yet_note'); ?></div>
            <?php if (!empty($can_generate)) { ?>
                <form class="form-inline" method="post" action="<?php echo site_url(current_lang() . '/loan/generate_repayment_schedule/' . $loanid); ?>">
                    <div class="form-group">
                        <label for="startdate"><?php echo lang('loan_schedule_start_date'); ?></label>
                        <input type="date" id="startdate" name="startdate" class="form-control input-sm" value="<?php echo htmlspecialchars($loaninfo->applicationdate); ?>" required/>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><?php echo lang('loan_schedule_generate'); ?></button>
                </form>
            <?php } ?>
        </div>
    <?php } else { ?>
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
                <?php } ?>
            </tbody>
        </table>
    </div>
    <div style="text-align: center; margin-top: 15px;">
        <a class="btn btn-primary btn-sm" href="<?php echo site_url(current_lang() . '/loan/print_repayment_schedule/' . $loanid); ?>" target="_blank"><?php echo lang('print'); ?></a>
        <a class="btn btn-success btn-sm" href="<?php echo site_url(current_lang() . '/loan/export_repayment_schedule/' . $loanid); ?>" target="_blank"><?php echo lang('export_to_excel'); ?></a>
    </div>
    <?php } ?>
</body>
</html>
