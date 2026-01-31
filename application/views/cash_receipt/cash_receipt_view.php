
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
                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_list'); ?>" class="btn btn-white btn-xs">
                            <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                        </a>
                        <?php if (has_role(6, 'Edit_cash_receipt')) { ?>
                            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_edit/' . $id); ?>" class="btn btn-warning btn-xs">
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
                                                    <th width="40%"><?php echo lang('cash_receipt_line_description'); ?></th>
                                                    <th width="20%" class="text-right"><?php echo lang('cash_receipt_amount'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($line_items)): ?>
                                                    <?php $total = 0; $index = 1; ?>
                                                    <?php foreach ($line_items as $item): ?>
                                                        <tr>
                                                            <td><?php echo $index++; ?></td>
                                                            <td><?php echo $item->account_name . ' (' . $item->account . ')'; ?></td>
                                                            <td><?php echo $item->description; ?></td>
                                                            <td class="text-right"><?php echo number_format($item->amount, 2); ?></td>
                                                        </tr>
                                                        <?php $total += $item->amount; ?>
                                                    <?php endforeach; ?>
                                                    <tr class="active">
                                                        <td colspan="3" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                                                        <td class="text-right"><strong><?php echo number_format($total, 2); ?></strong></td>
                                                    </tr>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center"><?php echo lang('no_records_found'); ?></td>
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
                                    <strong><?php echo lang('journal_entry'); ?> (<?php echo lang('reference_only'); ?>)</strong>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="10%">#</th>
                                                    <th width="40%"><?php echo lang('account'); ?></th>
                                                    <th width="25%" class="text-right"><?php echo lang('debit'); ?></th>
                                                    <th width="25%" class="text-right"><?php echo lang('credit'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_debit = 0; 
                                                $total_credit = 0; 
                                                $je_index = 1;
                                                
                                                // Load models to get the mapped GL account from paymentmenthod table
                                                $this->load->model('payment_method_config_model');
                                                $this->load->model('finance_model');
                                                
                                                // Get payment method config with GL account
                                                $payment_config = $this->payment_method_config_model->get_account_for_payment_method($receipt->payment_method);
                                                
                                                $cash_account_name = 'Cash/Bank Account (Unconfigured)';
                                                $cash_account_code = '';
                                                
                                                if ($payment_config && !empty($payment_config->gl_account_code)) {
                                                    // Use account_chart method to get account details by account code
                                                    $account_result = $this->finance_model->account_chart(null, $payment_config->gl_account_code);
                                                    $account = $account_result->row();
                                                    if ($account) {
                                                        $cash_account_name = $account->name;
                                                        $cash_account_code = $account->account;
                                                    }
                                                }
                                                ?>
                                                
                                                <!-- Debit Entry: Cash/Bank Account (from paymentmenthod GL account) -->
                                                <tr>
                                                    <td><?php echo $je_index++; ?></td>
                                                    <td>
                                                        <strong><?php echo $cash_account_name; ?></strong>
                                                        <?php if ($cash_account_code): ?>
                                                            <br><small class="text-muted"><?php echo $cash_account_code; ?> - <?php echo lang('payment_method'); ?>: <?php echo $receipt->payment_method; ?></small>
                                                        <?php else: ?>
                                                            <br><small class="text-warning"><?php echo lang('payment_method'); ?>: <?php echo $receipt->payment_method; ?> - <a href="<?php echo site_url('payment_method_config'); ?>">Configure GL account</a></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-right"><strong><?php echo number_format($receipt->total_amount, 2); ?></strong></td>
                                                    <td class="text-right">-</td>
                                                </tr>
                                                <?php $total_debit += $receipt->total_amount; ?>
                                                
                                                <!-- Credit Entries: Line Items (Revenue/Income Accounts) -->
                                                <?php if (!empty($line_items)): ?>
                                                    <?php foreach ($line_items as $item): ?>
                                                        <tr>
                                                            <td><?php echo $je_index++; ?></td>
                                                            <td>
                                                                <?php echo $item->account_name . ' (' . $item->account . ')'; ?>
                                                                <?php if (!empty($item->description)): ?>
                                                                    <br><small class="text-muted"><?php echo $item->description; ?></small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-right">-</td>
                                                            <td class="text-right"><?php echo number_format($item->amount, 2); ?></td>
                                                        </tr>
                                                        <?php $total_credit += $item->amount; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                
                                                <!-- Totals -->
                                                <tr class="active">
                                                    <td colspan="2" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                                                    <td class="text-right"><strong><?php echo number_format($total_debit, 2); ?></strong></td>
                                                    <td class="text-right"><strong><?php echo number_format($total_credit, 2); ?></strong></td>
                                                </tr>
                                                
                                                <?php if ($total_debit != $total_credit): ?>
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
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
