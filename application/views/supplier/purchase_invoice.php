<?php $this->load->view('loan/list_page_styles'); ?>
<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

<?php
$purchase_invoice = isset($purchase_invoice) ? $purchase_invoice : array();
$total_rows = count($purchase_invoice);
$create_url = site_url(current_lang() . '/supplier/create_purchase_invoice');
?>

<style type="text/css">
.member-list-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.member-list-page .supplier-name {
    font-weight: 700;
    color: #2f4050;
}
.member-list-page .status-pill.paid { background: #1ab394; }
.member-list-page .status-pill.partial { background: #f8ac59; }
.member-list-page .status-pill.unpaid { background: #ed5565; }
.member-list-page .balance-due {
    color: #ed5565;
    font-weight: 700;
}
.member-list-page .balance-clear {
    color: #1ab394;
    font-weight: 700;
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

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-file-text icon-badge"></i>
                <h4><?php echo lang('supplier_purchase_invoice'); ?></h4>
            </div>
            <div class="head-actions">
                <div class="result-meta">
                    Showing <strong><?php echo number_format($total_rows); ?></strong> invoice<?php echo $total_rows === 1 ? '' : 's'; ?>
                </div>
                <a href="<?php echo $create_url; ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> <?php echo lang('create_purchase_invoice'); ?>
                </a>
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table dataTables-example" id="purchaseInvoiceTable" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:70px;"><?php echo lang('sno'); ?></th>
                        <th><?php echo lang('purchaseorder_date'); ?></th>
                        <th><?php echo lang('due_date'); ?></th>
                        <th><?php echo lang('supplier_name'); ?></th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:right;">Amount Paid</th>
                        <th style="text-align:right;">Balance Due</th>
                        <th><?php echo lang('status'); ?></th>
                        <th style="width:90px;"><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($purchase_invoice)) {
                        $i = 1;
                        foreach ($purchase_invoice as $value) {
                            $supplier_info = $this->supplier_model->supplier_info(null, $value->supplierid)->row();
                            $supplier_name = ($supplier_info && isset($supplier_info->name)) ? $supplier_info->name : '';
                            $total = (float) $value->totalamount + (float) $value->totalamounttax;
                            $paid = $total - (float) $value->balance;
                            $balance = (float) $value->balance;
                            $status_code = (int) $value->status;
                            if ($status_code === 1) {
                                $status_label = 'FULL PAID';
                                $status_pill = 'paid';
                            } else if ($status_code === 2) {
                                $status_label = 'PARTIAL PAID';
                                $status_pill = 'partial';
                            } else {
                                $status_label = 'NOT PAID';
                                $status_pill = 'unpaid';
                            }
                            $issue_ts = !empty($value->issue_date) ? strtotime($value->issue_date) : false;
                            $due_ts = !empty($value->due_date) ? strtotime($value->due_date) : false;
                            ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo $i++; ?></span></td>
                                <td data-order="<?php echo htmlspecialchars($value->issue_date, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($issue_ts ? format_date($value->issue_date, false) : '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td data-order="<?php echo htmlspecialchars($value->due_date, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($due_ts ? format_date($value->due_date, false) : '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td>
                                    <div class="supplier-name"><?php echo htmlspecialchars($supplier_name, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <span class="member-id-chip"><?php echo htmlspecialchars($value->supplierid, ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="amount-cell" data-order="<?php echo $total; ?>"><?php echo number_format($total, 2); ?></td>
                                <td class="amount-cell" data-order="<?php echo $paid; ?>"><?php echo number_format($paid, 2); ?></td>
                                <td class="amount-cell <?php echo ($balance > 0) ? 'balance-due' : 'balance-clear'; ?>" data-order="<?php echo $balance; ?>">
                                    <?php echo number_format($balance, 2); ?>
                                </td>
                                <td data-order="<?php echo $status_code; ?>">
                                    <span class="status-pill <?php echo htmlspecialchars($status_pill, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?php echo site_url(current_lang() . '/supplier/purchase_invoice_view/' . encode_id($value->id)); ?>"
                                           class="btn btn-info btn-xs"
                                           title="<?php echo htmlspecialchars(lang('button_view'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-eye"></i> <?php echo lang('button_view'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function(){
    function loadScript(src, cb){
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        document.head.appendChild(s);
    }
    function tryInit(){
        if (window.jQuery) {
            if (!window.jQuery.fn || !window.jQuery.fn.DataTable) {
                loadScript('<?php echo base_url(); ?>assets/js/plugins/dataTables/datatables.min.js', init);
            } else {
                init();
            }
        } else {
            setTimeout(tryInit, 50);
        }
    }
    function init(){
        jQuery(function($){
            $('#purchaseInvoiceTable').DataTable({
                pageLength: 25,
                responsive: true,
                order: [[1, 'desc']],
                language: {
                    emptyTable: <?php echo json_encode(lang('data_not_found')); ?>
                }
            });
        });
    }
    tryInit();
})();
</script>
