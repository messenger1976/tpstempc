<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

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
                    <h5><?php echo lang('cash_disbursement_list'); ?></h5>
                    <div class="ibox-tools">
                        <?php if (has_role(6, 'Create_cash_disbursement')) { ?>
                            <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_create'); ?>" class="btn btn-primary btn-xs">
                                <i class="fa fa-plus"></i> <?php echo lang('cash_disbursement_create'); ?>
                            </a>
                        <?php } ?>
                        <?php
                        $export_url = site_url(current_lang() . '/cash_disbursement/cash_disbursement_export');
                        if (isset($date_from) && !empty($date_from)) {
                            $export_url .= '?date_from=' . urlencode($date_from);
                            if (isset($date_to) && !empty($date_to)) {
                                $export_url .= '&date_to=' . urlencode($date_to);
                            }
                        } elseif (isset($date_to) && !empty($date_to)) {
                            $export_url .= '?date_to=' . urlencode($date_to);
                        }
                        ?>
                        <a href="<?php echo $export_url; ?>" class="btn btn-success btn-xs">
                            <i class="fa fa-file-excel-o"></i> <?php echo lang('export_excel'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <!-- Date Range Filter -->
                    <form method="get" action="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_list'); ?>" class="form-horizontal" style="margin-bottom: 20px;">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Date From:</label>
                                <input type="date" class="form-control" name="date_from" value="<?php echo isset($date_from) ? htmlspecialchars($date_from) : ''; ?>"/>
                            </div>
                            <div class="col-md-3">
                                <label>Date To:</label>
                                <input type="date" class="form-control" name="date_to" value="<?php echo isset($date_to) ? htmlspecialchars($date_to) : ''; ?>"/>
                            </div>
                            <div class="col-md-3" style="padding-top: 25px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_list?clear=1'); ?>" class="btn btn-default">
                                    <i class="fa fa-times"></i> Clear
                                </a>
                                <?php
                                $report_summary_url = site_url(current_lang() . '/cash_disbursement/cash_disbursement_report_summary');
                                $report_details_url = site_url(current_lang() . '/cash_disbursement/cash_disbursement_report_details');
                                if (isset($date_from) && !empty($date_from)) {
                                    $report_summary_url .= '?date_from=' . urlencode($date_from);
                                    $report_details_url .= '?date_from=' . urlencode($date_from);
                                    if (isset($date_to) && !empty($date_to)) {
                                        $report_summary_url .= '&date_to=' . urlencode($date_to);
                                        $report_details_url .= '&date_to=' . urlencode($date_to);
                                    }
                                } elseif (isset($date_to) && !empty($date_to)) {
                                    $report_summary_url .= '?date_to=' . urlencode($date_to);
                                    $report_details_url .= '?date_to=' . urlencode($date_to);
                                }
                                ?>
                                <a href="<?php echo $report_summary_url; ?>" class="btn btn-info" target="_blank">
                                    <i class="fa fa-bar-chart"></i> <?php echo lang('cash_disbursement_report_summary'); ?>
                                </a>
                                <a href="<?php echo $report_details_url; ?>" class="btn btn-info" target="_blank">
                                    <i class="fa fa-list"></i> <?php echo lang('cash_disbursement_report_details'); ?>
                                </a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th><?php echo lang('cash_disbursement_no'); ?></th>
                                    <th><?php echo lang('cash_disbursement_date'); ?></th>
                                    <th><?php echo lang('cash_disbursement_paid_to'); ?></th>
                                    <th><?php echo lang('cash_disbursement_payment_method'); ?></th>
                                    <th><?php echo lang('cash_disbursement_description'); ?></th>
                                    <th><?php echo lang('cash_disbursement_total_amount'); ?></th>
                                    <th><?php echo lang('status'); ?></th>
                                    <th><?php echo lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($cash_disbursements)): ?>
                                    <?php foreach ($cash_disbursements as $disburse): ?>
                                        <tr>
                                            <td><?php echo $disburse->disburse_no; ?>
                                                <?php if (!empty($disburse->cancelled)): ?><span class="label label-default"><?php echo lang('cancelled'); ?></span><?php endif; ?>
                                            </td>
                                            <td data-order="<?php echo $disburse->disburse_date; ?>"><?php echo date('d-m-Y', strtotime($disburse->disburse_date)); ?></td>
                                            <td><?php echo $disburse->paid_to; ?></td>
                                            <td><?php echo isset($disburse->payment_method_display) ? $disburse->payment_method_display : $disburse->payment_method; ?></td>
                                            <td><?php echo character_limiter($disburse->description, 50); ?></td>
                                            <td class="text-right"><?php echo number_format($disburse->total_amount, 2); ?></td>
                                            <td>
                                                <?php if (!empty($disburse->cancelled)): ?>
                                                    <span class="label label-default"><?php echo lang('cancelled'); ?></span>
                                                <?php elseif (!empty($disburse->is_posted)): ?>
                                                    <span class="label label-success"><?php echo lang('cash_disbursement_status_posted'); ?></span>
                                                <?php else: ?>
                                                    <span class="label label-default"><?php echo lang('cash_disbursement_status_draft'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_view/' . encode_id($disburse->id)); ?>" 
                                                       class="btn btn-info btn-xs" title="<?php echo lang('view'); ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <?php if (has_role(6, 'Edit_cash_disbursement') && empty($disburse->is_posted)) { ?>
                                                        <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_edit/' . encode_id($disburse->id)); ?>" 
                                                           class="btn btn-warning btn-xs" title="<?php echo lang('edit'); ?>">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_print/' . encode_id($disburse->id)); ?>" 
                                                       class="btn btn-success btn-xs" target="_blank" title="<?php echo lang('print'); ?>">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                    <?php if (has_role(6, 'Delete_cash_disbursement') && empty($disburse->is_posted)) { ?>
                                                        <a href="<?php echo site_url(current_lang() . '/cash_disbursement/cash_disbursement_delete/' . encode_id($disburse->id)); ?>" 
                                                           class="btn btn-danger btn-xs delete-confirm" title="<?php echo lang('delete'); ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(init){
    function loadScript(src, cb){
        var s = document.createElement('script');
        s.src = src; s.onload = cb; document.head.appendChild(s);
    }
    function tryInit(){
        if (window.jQuery) {
            if (!window.jQuery.fn || !window.jQuery.fn.DataTable) {
                loadScript('<?php echo base_url(); ?>assets/js/plugins/dataTables/datatables.min.js', function(){ init(); });
            } else { init(); }
        } else { setTimeout(tryInit, 50); }
    }
    tryInit();
})(function(){
    jQuery(function($){
        $('.dataTables-example').DataTable({
            pageLength: 25,
            responsive: true,
            dom: 'lBfrtip',
            order: [[1, 'desc']], // Sort by disburse_date (column 1) newest first
            buttons: [
                {extend: 'copy'},
                {extend: 'csv'},
                {extend: 'excel', title: 'CashDisbursements'},
                {extend: 'pdf', title: 'CashDisbursements'},
                {extend: 'print',
                 customize: function (win){
                        jQuery(win.document.body).addClass('white-bg');
                        jQuery(win.document.body).css('font-size', '10px');
                        jQuery(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                }}
            ],
            language: { emptyTable: '<?php echo lang('no_records_found'); ?>' }
        });

        $('.delete-confirm').on('click', function(e){
            if(!confirm('<?php echo lang('delete_confirm'); ?>')){
                e.preventDefault();
                return false;
            }
        });
    });
});
</script>
