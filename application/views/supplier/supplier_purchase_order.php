<?php $this->load->view('loan/list_page_styles'); ?>
<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

<?php
$purchase_order = isset($purchase_order) ? $purchase_order : array();
$total_rows = count($purchase_order);
$create_url = site_url(current_lang() . '/supplier/create_order');
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
                <i class="fa fa-file-text-o icon-badge"></i>
                <h4><?php echo lang('supplier_purchase_order'); ?></h4>
            </div>
            <div class="head-actions">
                <div class="result-meta">
                    Showing <strong><?php echo number_format($total_rows); ?></strong> order<?php echo $total_rows === 1 ? '' : 's'; ?>
                </div>
                <a href="<?php echo $create_url; ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> <?php echo lang('supplier_new_order'); ?>
                </a>
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table dataTables-example" id="purchaseOrderTable" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:70px;"><?php echo lang('sno'); ?></th>
                        <th><?php echo lang('purchaseorder_date'); ?></th>
                        <th><?php echo lang('delivery_date'); ?></th>
                        <th><?php echo lang('supplier_id'); ?></th>
                        <th><?php echo lang('supplier_name'); ?></th>
                        <th style="text-align:right;"><?php echo lang('purchaseorder_amount'); ?></th>
                        <th style="text-align:right;"><?php echo lang('purchaseorder_taxcode'); ?></th>
                        <th style="width:90px;"><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($purchase_order)) {
                        $i = 1;
                        foreach ($purchase_order as $value) {
                            $supplier_info = $this->supplier_model->supplier_info(null, $value->supplierid)->row();
                            $supplier_name = ($supplier_info && isset($supplier_info->name)) ? $supplier_info->name : '';
                            $issue_ts = !empty($value->issue_date) ? strtotime($value->issue_date) : false;
                            $delivery_ts = !empty($value->delivery_date) ? strtotime($value->delivery_date) : false;
                            ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo $i++; ?></span></td>
                                <td data-order="<?php echo htmlspecialchars($value->issue_date, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($issue_ts ? format_date($value->issue_date, false) : '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td data-order="<?php echo htmlspecialchars($value->delivery_date, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($delivery_ts ? format_date($value->delivery_date, false) : '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($value->supplierid, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td class="supplier-name"><?php echo htmlspecialchars($supplier_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell" data-order="<?php echo (float) $value->totalamount; ?>"><?php echo number_format($value->totalamount, 2); ?></td>
                                <td class="amount-cell" data-order="<?php echo (float) $value->totalamounttax; ?>"><?php echo number_format($value->totalamounttax, 2); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?php echo site_url(current_lang() . '/supplier/purchase_order_view/' . encode_id($value->id)); ?>"
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
            $('#purchaseOrderTable').DataTable({
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
