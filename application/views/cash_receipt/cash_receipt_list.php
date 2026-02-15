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
                    <h5><?php echo lang('cash_receipt_list'); ?></h5>
                    <div class="ibox-tools">
                        <?php if (has_role(6, 'Create_cash_receipt')) { ?>
                            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_create'); ?>" class="btn btn-primary btn-xs">
                                <i class="fa fa-plus"></i> <?php echo lang('cash_receipt_create'); ?>
                            </a>
                        <?php } ?>
                        <?php
                        $export_url = site_url(current_lang() . '/cash_receipt/cash_receipt_export');
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
                    <form method="get" action="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_list'); ?>" class="form-horizontal" style="margin-bottom: 20px;">
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
                                <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_list'); ?>" class="btn btn-default">
                                    <i class="fa fa-times"></i> Clear
                                </a>
                                <?php
                                $report_summary_url = site_url(current_lang() . '/cash_receipt/cash_receipt_report_summary');
                                $report_details_url = site_url(current_lang() . '/cash_receipt/cash_receipt_report_details');
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
                                    <i class="fa fa-bar-chart"></i> <?php echo lang('cash_receipt_report_summary'); ?>
                                </a>
                                <a href="<?php echo $report_details_url; ?>" class="btn btn-info" target="_blank">
                                    <i class="fa fa-list"></i> <?php echo lang('cash_receipt_report_details'); ?>
                                </a>
                            </div>
                        </div>
                    </form>
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
                                            <td><?php echo $receipt->receipt_no; ?>
                                                <?php if (!empty($receipt->cancelled)): ?><span class="label label-default"><?php echo lang('cancelled'); ?></span><?php endif; ?>
                                            </td>
                                            <td><?php echo date('d-m-Y', strtotime($receipt->receipt_date)); ?></td>
                                            <td><?php echo $receipt->received_from; ?></td>
                                            <td><?php echo $receipt->payment_method; ?></td>
                                            <td><?php echo character_limiter($receipt->description, 50); ?></td>
                                            <td class="text-right"><?php echo number_format($receipt->total_amount, 2); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($receipt->id) . '?popup=1'); ?>" 
                                                       class="btn btn-info btn-xs view-popup" title="<?php echo lang('view'); ?>" data-url="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($receipt->id) . '?popup=1'); ?>">
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
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for popup view -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo lang('cash_receipt_view'); ?></h4>
            </div>
            <div class="modal-body" style="height:70vh; padding:0;">
                <iframe id="receiptModalFrame" src="about:blank" style="border:0; width:100%; height:100%;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
(function(init){
    function loadScript(src, cb){
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        document.head.appendChild(s);
    }

    function loadSwal(cb){
        if (window.Swal) { cb(); return; }
        loadScript('https://cdn.jsdelivr.net/npm/sweetalert2@11', cb);
    }
    // expose globally so inner init can access
    window.loadSwal = loadSwal;

    function tryInit(){
        if (window.jQuery) {
            if (!window.jQuery.fn || !window.jQuery.fn.DataTable) {
                loadScript('<?php echo base_url(); ?>assets/js/plugins/dataTables/datatables.min.js', function(){
                    init();
                });
            } else {
                init();
            }
        } else {
            setTimeout(tryInit, 50);
        }
    }

    tryInit();
})(function(){
    jQuery(function($){
        $('.dataTables-example').DataTable({
            pageLength: 25,
            responsive: true,
            dom: 'lBfrtip',
            order: [[1, 'desc']], // Sort by receipt_date (column 1) newest first
            buttons: [
                {extend: 'copy'},
                {extend: 'csv'},
                {extend: 'excel', title: 'CashReceipts'},
                {extend: 'pdf', title: 'CashReceipts'},
                {extend: 'print',
                 customize: function (win){
                        jQuery(win.document.body).addClass('white-bg');
                        jQuery(win.document.body).css('font-size', '10px');
                        jQuery(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                }
                }
            ],
            language: {
                emptyTable: '<?php echo lang('no_records_found'); ?>'
            }
        });

        // Delete confirmation using SweetAlert (delegated for DataTables redraw)
        $(document).on('click', '.delete-confirm', function(e){
            e.preventDefault();
            var url = this.href;
            var load = window.loadSwal || function(cb){ cb(); };
            load(function(){
                if (!window.Swal) {
                    // Fallback to native confirm if CDN fails
                    if (confirm('<?php echo lang('delete_confirm'); ?>')) {
                        window.location.href = url;
                    }
                    return;
                }
                Swal.fire({
                    title: '<?php echo lang('delete_confirm'); ?>',
                    text: 'This will permanently delete the cash receipt and its items.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<?php echo lang('delete'); ?>',
                    cancelButtonText: '<?php echo lang('cancel'); ?>'
                }).then(function(result){
                    if (result.isConfirmed) {
                        // Use GET redirect (controller expects URL param)
                        window.location.href = url;
                    }
                });
            });
        });

        // View popup using iframe modal
        $(document).on('click', '.view-popup', function(e){
            e.preventDefault();
            var url = $(this).data('url') || this.href;
            $('#receiptModalFrame').attr('src', url);
            $('#receiptModal').modal('show');
        });
    });
});
</script>
