
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
                    <h5><?php echo lang('cash_disbursement_view'); ?> - <?php echo $disburse->disburse_no; ?></h5>
                    <div class="ibox-tools">
                        <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_list'); ?>" class="btn btn-white btn-xs">
                            <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                        </a>
                        <?php if (has_role(6, 'Edit_cash_disbursement')) { ?>
                            <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_edit/' . $id); ?>" class="btn btn-warning btn-xs">
                                <i class="fa fa-edit"></i> <?php echo lang('edit'); ?>
                            </a>
                        <?php } ?>
                        <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_print/' . $id); ?>" class="btn btn-success btn-xs" target="_blank">
                            <i class="fa fa-print"></i> <?php echo lang('print'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong><?php echo lang('cash_disbursement_information'); ?></strong>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-condensed">
                                        <tr>
                                            <td width="40%"><strong><?php echo lang('cash_disbursement_no'); ?>:</strong></td>
                                            <td><?php echo $disburse->disburse_no; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('cash_disbursement_date'); ?>:</strong></td>
                                            <td><?php echo date('d-m-Y', strtotime($disburse->disburse_date)); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('cash_disbursement_paid_to'); ?>:</strong></td>
                                            <td><?php echo $disburse->paid_to; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('cash_disbursement_payment_method'); ?>:</strong></td>
                                            <td><?php echo $disburse->payment_method; ?></td>
                                        </tr>
                                        <?php if ($disburse->payment_method == 'Cheque' && !empty($disburse->cheque_no)): ?>
                                        <tr>
                                            <td><strong><?php echo lang('cash_disbursement_cheque_no'); ?>:</strong></td>
                                            <td><?php echo $disburse->cheque_no; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('cash_disbursement_bank_name'); ?>:</strong></td>
                                            <td><?php echo $disburse->bank_name; ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><strong><?php echo lang('cash_disbursement_total_amount'); ?>:</strong></td>
                                            <td><strong><?php echo number_format($disburse->total_amount, 2); ?></strong></td>
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
                                                $user = $this->ion_auth->user($disburse->createdby)->row();
                                                echo $user ? $user->first_name . ' ' . $user->last_name : 'N/A';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo lang('created_at'); ?>:</strong></td>
                                            <td><?php echo date('d-m-Y H:i:s', strtotime($disburse->created_at)); ?></td>
                                        </tr>
                                        <?php if (!empty($disburse->updated_at)): ?>
                                        <tr>
                                            <td><strong><?php echo lang('updated_at'); ?>:</strong></td>
                                            <td><?php echo date('d-m-Y H:i:s', strtotime($disburse->updated_at)); ?></td>
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
                                    <strong><?php echo lang('cash_disbursement_description'); ?></strong>
                                </div>
                                <div class="panel-body">
                                    <?php echo nl2br($disburse->description); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <strong><?php echo lang('cash_disbursement_line_items'); ?></strong>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="10%">#</th>
                                                    <th width="30%"><?php echo lang('cash_disbursement_account'); ?></th>
                                                    <th width="40%"><?php echo lang('cash_disbursement_line_description'); ?></th>
                                                    <th width="20%" class="text-right"><?php echo lang('cash_disbursement_amount'); ?></th>
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

                </div>
            </div>
        </div>
    </div>
</div>
