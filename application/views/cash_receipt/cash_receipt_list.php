<link href="<?php echo base_url(); ?>media/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

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
                    <h5><?php echo lang('cash_receipt_list'); ?></h5>
                    <div class="ibox-tools">
                        <?php if (has_role(6, 'Create_cash_receipt')) { ?>
                            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_create'); ?>" class="btn btn-primary btn-xs">
                                <i class="fa fa-plus"></i> <?php echo lang('cash_receipt_create'); ?>
                            </a>
                        <?php } ?>
                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_export'); ?>" class="btn btn-success btn-xs">
                            <i class="fa fa-file-excel-o"></i> <?php echo lang('export_excel'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th><?php echo lang('cash_receipt_no'); ?></th>
                                    <th><?php echo lang('cash_receipt_date'); ?></th>
                                    <th><?php echo lang('cash_receipt_received_from'); ?></th>
                                    <th><?php echo lang('cash_receipt_payment_method'); ?></th>
                                    <th><?php echo lang('cash_receipt_description'); ?></th>
                                    <th><?php echo lang('cash_receipt_total_amount'); ?></th>
                                    <th><?php echo lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($cash_receipts)): ?>
                                    <?php foreach ($cash_receipts as $receipt): ?>
                                        <tr>
                                            <td><?php echo $receipt->receipt_no; ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($receipt->receipt_date)); ?></td>
                                            <td><?php echo $receipt->received_from; ?></td>
                                            <td><?php echo $receipt->payment_method; ?></td>
                                            <td><?php echo character_limiter($receipt->description, 50); ?></td>
                                            <td class="text-right"><?php echo number_format($receipt->total_amount, 2); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($receipt->id)); ?>" 
                                                       class="btn btn-info btn-xs" title="<?php echo lang('view'); ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <?php if (has_role(6, 'Edit_cash_receipt')) { ?>
                                                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_edit/' . encode_id($receipt->id)); ?>" 
                                                           class="btn btn-warning btn-xs" title="<?php echo lang('edit'); ?>">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_print/' . encode_id($receipt->id)); ?>" 
                                                       class="btn btn-success btn-xs" target="_blank" title="<?php echo lang('print'); ?>">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                    <?php if (has_role(6, 'Delete_cash_receipt')) { ?>
                                                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_delete/' . encode_id($receipt->id)); ?>" 
                                                           class="btn btn-danger btn-xs delete-confirm" title="<?php echo lang('delete'); ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center"><?php echo lang('no_records_found'); ?></td>
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

<!-- DataTables -->
<script src="<?php echo base_url(); ?>media/js/plugins/dataTables/datatables.min.js"></script>

<script>
$(document).ready(function(){
    $('.dataTables-example').DataTable({
        pageLength: 25,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            {extend: 'copy'},
            {extend: 'csv'},
            {extend: 'excel', title: 'CashReceipts'},
            {extend: 'pdf', title: 'CashReceipts'},
            {extend: 'print',
             customize: function (win){
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');
                    $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
            }
            }
        ]
    });

    // Delete confirmation
    $('.delete-confirm').click(function(e){
        if(!confirm('<?php echo lang('delete_confirm'); ?>')){
            e.preventDefault();
            return false;
        }
    });
});
</script>
