<?php $this->load->view('loan/list_page_styles'); ?>
<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

<?php
$sales_quote = isset($sales_quote) ? $sales_quote : array();
$total_rows = count($sales_quote);
$create_url = site_url(current_lang() . '/customer/customersales_quote');
?>

<style type="text/css">
.member-list-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.member-list-page .customer-name {
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
                <h4><?php echo lang('customersales_quote'); ?></h4>
            </div>
            <div class="head-actions">
                <div class="result-meta">
                    Showing <strong><?php echo number_format($total_rows); ?></strong> quote<?php echo $total_rows === 1 ? '' : 's'; ?>
                </div>
                <a href="<?php echo $create_url; ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Create New Sales Quote
                </a>
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table dataTables-example" id="salesQuoteTable" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:70px;"><?php echo lang('sno'); ?></th>
                        <th><?php echo lang('salesquote_date'); ?></th>
                        <th><?php echo lang('customer_id'); ?></th>
                        <th><?php echo lang('customer_name'); ?></th>
                        <th style="text-align:right;"><?php echo lang('salesquote_amount'); ?></th>
                        <th style="text-align:right;"><?php echo lang('salesquote_taxcode'); ?></th>
                        <th style="width:90px;"><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sales_quote)) {
                        $i = 1;
                        foreach ($sales_quote as $value) {
                            $customer_info = $this->customer_model->customer_info(null, $value->customerid)->row();
                            $customer_name = ($customer_info && isset($customer_info->name)) ? $customer_info->name : '';
                            $issue_ts = !empty($value->issue_date) ? strtotime($value->issue_date) : false;
                            ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo $i++; ?></span></td>
                                <td data-order="<?php echo htmlspecialchars($value->issue_date, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($issue_ts ? format_date($value->issue_date, false) : '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($value->customerid, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td class="customer-name"><?php echo htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell" data-order="<?php echo (float) $value->totalamount; ?>"><?php echo number_format($value->totalamount, 2); ?></td>
                                <td class="amount-cell" data-order="<?php echo (float) $value->totalamounttax; ?>"><?php echo number_format($value->totalamounttax, 2); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?php echo site_url(current_lang() . '/customer/sales_quote_view/' . encode_id($value->id)); ?>"
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
            $('#salesQuoteTable').DataTable({
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
