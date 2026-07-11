<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php $company = function_exists('company_info_detail') ? company_info_detail() : null; $company_name = ($company && isset($company->name) && $company->name !== '') ? $company->name : 'Cooperative'; ?>
    <title><?php echo htmlspecialchars($company_name); ?> | <?php echo lang('journal_entry_view'); ?> - #<?php echo $entry->entryid; ?></title>

    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/animate.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet">
    <style>
        body { background: #f3f3f4; }
        .wrapper { padding: 15px 15px 30px; }
        .ibox-title { border-top: none; }
    </style>
</head>
<body class="white-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo lang('journal_entry_view'); ?> - #<?php echo $entry->entryid; ?></h5>
                    <div class="ibox-tools">
                        <a href="#" class="btn btn-white btn-xs" onclick="window.parent && window.parent.$ ? window.parent.$('#journalEntryModal').modal('hide') : window.close(); return false;">
                            <i class="fa fa-times"></i> <?php echo lang('close'); ?>
                        </a>
                        <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_print/' . $id); ?>" class="btn btn-success btn-xs" target="_blank">
                            <i class="fa fa-print"></i> <?php echo lang('print'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px;"><?php echo lang('journal_entry_no'); ?>:</th>
                                    <td><?php echo $entry->entryid; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo lang('journalentry_date'); ?>:</th>
                                    <td><?php echo date('d-m-Y', strtotime($entry->entrydate)); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo lang('journalentry_reference_no'); ?>:</th>
                                    <td><?php echo !empty($entry->reference_no) ? htmlspecialchars($entry->reference_no) : '—'; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo lang('journalentry_description'); ?>:</th>
                                    <td><?php echo htmlspecialchars($entry->description); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo lang('status'); ?>:</th>
                                    <td>
                                        <?php if ($entry->is_posted): ?>
                                            <span class="label label-success"><?php echo lang('journal_entry_status_posted'); ?></span>
                                        <?php else: ?>
                                            <span class="label label-default"><?php echo lang('journal_entry_status_draft'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px;"><?php echo lang('journalentry_debit'); ?>:</th>
                                    <td style="text-align: right; font-weight: bold;"><?php echo number_format($entry->total_debit, 2); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo lang('journalentry_credit'); ?>:</th>
                                    <td style="text-align: right; font-weight: bold;"><?php echo number_format($entry->total_credit, 2); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo lang('journal_entry_line_items'); ?>:</th>
                                    <td><?php echo count($entry->line_items); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <h4><?php echo lang('journal_entry_line_items'); ?></h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th><?php echo lang('account_code'); ?></th>
                                    <th><?php echo lang('journalentry_account'); ?></th>
                                    <th><?php echo lang('journalentry_account_description'); ?></th>
                                    <th style="text-align: right; width: 120px;"><?php echo lang('journalentry_debit'); ?></th>
                                    <th style="text-align: right; width: 120px;"><?php echo lang('journalentry_credit'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($entry->line_items)): ?>
                                    <?php $i = 1; foreach ($entry->line_items as $item): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo $item->account; ?></td>
                                            <td><?php echo htmlspecialchars($item->account_name); ?></td>
                                            <td><?php echo htmlspecialchars($item->description); ?></td>
                                            <td style="text-align: right;"><?php echo number_format($item->debit, 2); ?></td>
                                            <td style="text-align: right;"><?php echo number_format($item->credit, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                                        <td colspan="4" style="text-align: right;"><?php echo lang('journalentry_total'); ?>:</td>
                                        <td style="text-align: right;"><?php echo number_format($entry->total_debit, 2); ?></td>
                                        <td style="text-align: right;"><?php echo number_format($entry->total_credit, 2); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-muted"><?php echo lang('no_records_found'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
