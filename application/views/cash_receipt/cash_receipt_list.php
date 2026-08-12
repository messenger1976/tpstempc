<?php $this->load->view('loan/list_page_styles'); ?>
<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

<?php
$date_from = isset($date_from) ? $date_from : '';
$date_to = isset($date_to) ? $date_to : '';
$cash_receipts = isset($cash_receipts) ? $cash_receipts : array();
$total_rows = count($cash_receipts);

$qs = array();
if ($date_from !== '') {
    $qs['date_from'] = $date_from;
}
if ($date_to !== '') {
    $qs['date_to'] = $date_to;
}
$query_suffix = count($qs) ? ('?' . http_build_query($qs)) : '';

$export_url = site_url(current_lang() . '/cash_receipt/cash_receipt_export') . $query_suffix;
$report_summary_url = site_url(current_lang() . '/cash_receipt/cash_receipt_report_summary') . $query_suffix;
$report_details_url = site_url(current_lang() . '/cash_receipt/cash_receipt_report_details') . $query_suffix;
$create_url = site_url(current_lang() . '/cash_receipt/cash_receipt_create');
$clear_url = site_url(current_lang() . '/cash_receipt/cash_receipt_list?clear=1');
$list_url = site_url(current_lang() . '/cash_receipt/cash_receipt_list');
?>

<style type="text/css">
.member-list-page .filter-field.date-field { flex: 0 1 180px; min-width: 150px; }
.member-list-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.member-list-page .status-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
}
.member-list-page .status-pill.posted {
    background: #e8f8f5;
    color: #1ab394;
}
.member-list-page .status-pill.draft {
    background: #eef1f2;
    color: #676a6c;
}
.member-list-page .status-pill.cancelled {
    background: #fdeceb;
    color: #c0392b;
}
.member-list-page .receipt-no-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.member-list-page .dataTables_wrapper .dataTables_filter input,
.member-list-page .dataTables_wrapper .dataTables_length select {
    border: 1px solid #e5e6e7;
    border-radius: 6px;
    height: 32px;
    padding: 4px 8px;
}
.member-list-page .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #1ab394 !important;
    border-color: #1ab394 !important;
    color: #fff !important;
}
.member-list-page .dt-buttons .btn {
    border-radius: 6px;
    margin-right: 4px;
}
#receiptModal .modal-header {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
#receiptModal .modal-title {
    font-weight: 700;
    color: #2f4050;
}
</style>

<div class="col-lg-12 member-list-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="member-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="member-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="member-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="member-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="member-filter-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-search icon-badge"></i>
                <h4><?php echo lang('cash_receipt_list'); ?></h4>
            </div>
            <div class="head-actions">
                <?php if (has_role(6, 'Create_cash_receipt')) { ?>
                    <a href="<?php echo $create_url; ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> <?php echo lang('cash_receipt_create'); ?>
                    </a>
                <?php } ?>
                <a href="<?php echo $export_url; ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o"></i> <?php echo lang('export_excel'); ?>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?php echo $list_url; ?>" method="get" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field date-field">
                        <label>From Date</label>
                        <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from, ENT_QUOTES, 'UTF-8'); ?>"/>
                    </div>
                    <div class="filter-field date-field">
                        <label>To Date</label>
                        <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to, ENT_QUOTES, 'UTF-8'); ?>"/>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo $clear_url; ?>" class="btn btn-default btn-clear-filter">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <a href="<?php echo $report_summary_url; ?>" class="btn btn-info" target="_blank">
                            <i class="fa fa-bar-chart"></i> <?php echo lang('cash_receipt_report_summary'); ?>
                        </a>
                        <a href="<?php echo $report_details_url; ?>" class="btn btn-info" target="_blank">
                            <i class="fa fa-list"></i> <?php echo lang('cash_receipt_report_details'); ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-list-alt icon-badge"></i>
                <h4><?php echo lang('cash_receipt_list'); ?></h4>
            </div>
            <div class="result-meta">
                Showing <strong><?php echo number_format($total_rows); ?></strong> receipt<?php echo $total_rows === 1 ? '' : 's'; ?>
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table dataTables-example" id="cashReceiptTable">
                <thead>
                    <tr>
                        <th><?php echo lang('cash_receipt_no'); ?></th>
                        <th><?php echo lang('cash_receipt_date'); ?></th>
                        <th><?php echo lang('cash_receipt_received_from'); ?></th>
                        <th><?php echo lang('cash_receipt_payment_method'); ?></th>
                        <th><?php echo lang('cash_receipt_description'); ?></th>
                        <th style="text-align:right;"><?php echo lang('cash_receipt_total_amount'); ?></th>
                        <th><?php echo lang('status'); ?></th>
                        <th><?php echo lang('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cash_receipts)) { ?>
                        <?php foreach ($cash_receipts as $receipt) {
                            $view_url = site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($receipt->id) . '?popup=1');
                            $pm = isset($receipt->payment_method_display) ? $receipt->payment_method_display : $receipt->payment_method;
                            ?>
                            <tr>
                                <td>
                                    <div class="receipt-no-wrap">
                                        <span class="member-id-chip"><?php echo htmlspecialchars($receipt->receipt_no, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if (!empty($receipt->cancelled)) { ?>
                                            <span class="status-pill cancelled"><?php echo lang('cancelled'); ?></span>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td data-order="<?php echo htmlspecialchars($receipt->receipt_date, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(date('d-m-Y', strtotime($receipt->receipt_date)), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($receipt->received_from, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($pm, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(character_limiter($receipt->description, 50), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format($receipt->total_amount, 2); ?></td>
                                <td>
                                    <?php if (!empty($receipt->cancelled)) { ?>
                                        <span class="status-pill cancelled"><?php echo lang('cancelled'); ?></span>
                                    <?php } else if (!empty($receipt->is_posted)) { ?>
                                        <span class="status-pill posted"><?php echo lang('cash_receipt_status_posted'); ?></span>
                                    <?php } else { ?>
                                        <span class="status-pill draft"><?php echo lang('cash_receipt_status_draft'); ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?php echo $view_url; ?>"
                                           class="btn btn-info btn-xs view-popup"
                                           title="<?php echo htmlspecialchars(lang('view'), ENT_QUOTES, 'UTF-8'); ?>"
                                           data-url="<?php echo htmlspecialchars($view_url, ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <?php if (has_role(6, 'Edit_cash_receipt') && empty($receipt->is_posted)) { ?>
                                            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_edit/' . encode_id($receipt->id)); ?>"
                                               class="btn btn-warning btn-xs"
                                               title="<?php echo htmlspecialchars(lang('edit'), ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        <?php } ?>
                                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_print/' . encode_id($receipt->id)); ?>"
                                           class="btn btn-primary btn-xs"
                                           target="_blank"
                                           title="<?php echo htmlspecialchars(lang('print'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        <?php if (has_role(6, 'Delete_cash_receipt') && empty($receipt->is_posted)) { ?>
                                            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_delete/' . encode_id($receipt->id)); ?>"
                                               class="btn btn-danger btn-xs delete-confirm"
                                               title="<?php echo htmlspecialchars(lang('delete'), ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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
            order: [[1, 'desc']],
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

        $(document).on('click', '.delete-confirm', function(e){
            e.preventDefault();
            var url = this.href;
            var load = window.loadSwal || function(cb){ cb(); };
            load(function(){
                if (!window.Swal) {
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
                        window.location.href = url;
                    }
                });
            });
        });

        $(document).on('click', '.view-popup', function(e){
            e.preventDefault();
            var url = $(this).data('url') || this.href;
            $('#receiptModalFrame').attr('src', url);
            $('#receiptModal').modal('show');
        });
    });
});
</script>
