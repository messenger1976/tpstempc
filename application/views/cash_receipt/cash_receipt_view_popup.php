<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo lang('cash_receipt_view'); ?> - <?php echo $receipt->receipt_no; ?></title>
    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/animate.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet">
    <style>
        /* Keep iframe version tidy without the full app shell */
        body { background: #f3f3f4; }
        .wrapper { padding: 15px 15px 30px; }
        .ibox-title { border-top: none; }
    </style>
</head>
<body class="white-bg">
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

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo lang('cash_receipt_view'); ?> - <?php echo $receipt->receipt_no; ?></h5>
                    <div class="ibox-tools">
                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . $id); ?>" class="btn btn-white btn-xs" onclick="window.parent && window.parent.$ ? window.parent.$('#receiptModal').modal('hide') : window.parent.location.reload(); return false;">
                            <i class="fa fa-times"></i> <?php echo lang('close'); ?>
                        </a>
                        <?php if (has_role(6, 'Edit_cash_receipt')) { ?>
                            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_edit/' . $id); ?>" class="btn btn-warning btn-xs" target="_top">
                                <i class="fa fa-edit"></i> <?php echo lang('edit'); ?>
                            </a>
                        <?php } ?>
                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_print/' . $id); ?>" class="btn btn-success btn-xs" target="_blank">
                            <i class="fa fa-print"></i> <?php echo lang('print'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong><?php echo lang('cash_receipt_information'); ?></strong>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-condensed">
                                        <tr>
                                            <td width="40%"><strong><?php echo lang('cash_receipt_no'); ?>:</strong></td>
                                            <td><?php echo $receipt->receipt_no; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('cash_receipt_date'); ?>:</strong></td>
                                            <td><?php echo date('d-m-Y', strtotime($receipt->receipt_date)); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('cash_receipt_received_from'); ?>:</strong></td>
                                            <td><?php echo $receipt->received_from; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('cash_receipt_payment_method'); ?>:</strong></td>
                                            <td><?php echo $receipt->payment_method; ?></td>
                                        </tr>
                                        <?php if ($receipt->payment_method == 'Cheque' && !empty($receipt->cheque_no)): ?>
                                        <tr>
                                            <td><strong><?php echo lang('cash_receipt_cheque_no'); ?>:</strong></td>
                                            <td><?php echo $receipt->cheque_no; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('cash_receipt_bank_name'); ?>:</strong></td>
                                            <td><?php echo $receipt->bank_name; ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><strong><?php echo lang('cash_receipt_total_amount'); ?>:</strong></td>
                                            <td><strong><?php echo number_format($receipt->total_amount, 2); ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong><?php echo lang('additional_information'); ?></strong>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-condensed">
                                        <tr>
                                            <td width="40%"><strong><?php echo lang('created_by'); ?>:</strong></td>
                                            <td>
                                                <?php 
                                                $user = $this->ion_auth->user($receipt->createdby)->row();
                                                echo $user ? $user->first_name . ' ' . $user->last_name : 'N/A';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('created_at'); ?>:</strong></td>
                                            <td><?php echo date('d-m-Y H:i:s', strtotime($receipt->created_at)); ?></td>
                                        </tr>
                                        <?php if (!empty($receipt->updated_at)): ?>
                                        <tr>
                                            <td><strong><?php echo lang('updated_at'); ?>:</strong></td>
                                            <td><?php echo date('d-m-Y H:i:s', strtotime($receipt->updated_at)); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong><?php echo lang('cash_receipt_description'); ?></strong>
                                </div>
                                <div class="panel-body">
                                    <?php echo nl2br($receipt->description); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <strong><?php echo lang('cash_receipt_line_items'); ?></strong>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="10%">#</th>
                                                    <th width="30%"><?php echo lang('cash_receipt_account'); ?></th>
                                                    <th width="30%"><?php echo lang('cash_receipt_line_description'); ?></th>
                                                    <th width="15%" class="text-right"><?php echo lang('journalentry_debit'); ?></th>
                                                    <th width="15%" class="text-right"><?php echo lang('journalentry_credit'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($line_items)): ?>
                                                    <?php $total_debit = 0; $total_credit = 0; $index = 1; ?>
                                                    <?php foreach ($line_items as $item): 
                                                        $item_debit = isset($item->debit) ? floatval($item->debit) : 0;
                                                        $item_credit = isset($item->credit) ? floatval($item->credit) : (isset($item->amount) ? floatval($item->amount) : 0);
                                                        $total_debit += $item_debit;
                                                        $total_credit += $item_credit;
                                                    ?>
                                                        <tr>
                                                            <td><?php echo $index++; ?></td>
                                                            <td><?php echo $item->account_name . ' (' . $item->account . ')'; ?></td>
                                                            <td><?php echo $item->description; ?></td>
                                                            <td class="text-right"><?php echo $item_debit > 0 ? number_format($item_debit, 2) : '—'; ?></td>
                                                            <td class="text-right"><?php echo $item_credit > 0 ? number_format($item_credit, 2) : '—'; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    <tr class="active">
                                                        <td colspan="3" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                                                        <td class="text-right"><strong><?php echo number_format($total_debit, 2); ?></strong></td>
                                                        <td class="text-right"><strong><?php echo number_format($total_credit, 2); ?></strong></td>
                                                    </tr>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center"><?php echo lang('no_records_found'); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <strong><?php echo lang('accounting_entries'); ?></strong>
                                    <?php
                                    $ae = isset($accounting_entries) ? $accounting_entries : array('journal' => null, 'items' => array());
                                    $journal = isset($ae['journal']) ? $ae['journal'] : null;
                                    $journal_items = isset($ae['items']) ? $ae['items'] : array();
                                    if ($journal && !empty($journal->description)): ?>
                                        <span class="text-muted small"> — <?php echo htmlspecialchars($journal->description); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="panel-body">
                                    <?php if (!empty($journal_items)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="30%"><?php echo lang('account'); ?></th>
                                                    <th width="35%"><?php echo lang('description'); ?></th>
                                                    <th width="17.5%" class="text-right"><?php echo lang('debit'); ?></th>
                                                    <th width="17.5%" class="text-right"><?php echo lang('credit'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_debit = 0;
                                                $total_credit = 0;
                                                foreach ($journal_items as $entry): 
                                                    $total_debit += isset($entry->debit) ? floatval($entry->debit) : 0;
                                                    $total_credit += isset($entry->credit) ? floatval($entry->credit) : 0;
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((isset($entry->account_name) ? $entry->account_name : '') . ' (' . (isset($entry->account) ? $entry->account : '') . ')'); ?></td>
                                                    <td><?php echo htmlspecialchars(isset($entry->description) ? $entry->description : ''); ?></td>
                                                    <td class="text-right"><?php echo (isset($entry->debit) && $entry->debit > 0) ? number_format($entry->debit, 2) : '—'; ?></td>
                                                    <td class="text-right"><?php echo (isset($entry->credit) && $entry->credit > 0) ? number_format($entry->credit, 2) : '—'; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="active">
                                                    <td colspan="2" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                                                    <td class="text-right"><strong><?php echo number_format($total_debit, 2); ?></strong></td>
                                                    <td class="text-right"><strong><?php echo number_format($total_credit, 2); ?></strong></td>
                                                </tr>
                                                <?php if (abs($total_debit - $total_credit) > 0.001): ?>
                                                <tr class="danger">
                                                    <td colspan="4" class="text-center">
                                                        <i class="fa fa-exclamation-triangle"></i> 
                                                        <strong><?php echo lang('warning'); ?>:</strong> <?php echo lang('debits_credits_not_balanced'); ?>
                                                    </td>
                                                </tr>
                                                <?php else: ?>
                                                <tr class="success">
                                                    <td colspan="4" class="text-center">
                                                        <i class="fa fa-check-circle"></i> 
                                                        <?php echo lang('debits_credits_balanced'); ?>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <p class="text-muted"><?php echo lang('no_accounting_entries'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
