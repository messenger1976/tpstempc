<?php $this->load->view('loan/list_page_styles'); ?>

<?php
$customer_list = isset($customer_list) ? $customer_list : array();
$key = isset($key) ? $key : (isset($_GET['key']) ? $_GET['key'] : '');
$total_rows = isset($total_rows) ? (int) $total_rows : count($customer_list);
$page_start = isset($page_start) ? (int) $page_start : 0;
$per_page = isset($per_page) ? (int) $per_page : 0;
$from = $total_rows > 0 ? ($page_start + 1) : 0;
$to = $total_rows > 0 ? min($page_start + ($per_page > 0 ? $per_page : count($customer_list)), $total_rows) : 0;
$list_url = site_url(current_lang() . '/customer/customerlist');
$create_url = site_url(current_lang() . '/customer/customer_register');
$clear_url = site_url(current_lang() . '/customer/customerlist');
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
.member-list-page .muted-cell {
    color: #999;
}
.member-list-page .amount-cell {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    white-space: nowrap;
    font-size: 12px;
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
                <h4><?php echo lang('customer_list'); ?></h4>
            </div>
            <div class="head-actions">
                <a href="<?php echo $create_url; ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> <?php echo lang('customer_addnew'); ?>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?php echo $list_url; ?>" method="get" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field search-field">
                        <label><?php echo lang('button_search'); ?></label>
                        <input type="text" class="form-control" name="key" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo htmlspecialchars(lang('customer_name') . ' / ' . lang('customer_id') . ' / ' . lang('customer_phone'), ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo $clear_url; ?>" class="btn btn-default btn-clear-filter">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> <?php echo lang('button_search'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-users icon-badge"></i>
                <h4><?php echo lang('customer_list'); ?></h4>
            </div>
            <div class="result-meta">
                Showing <strong><?php echo number_format($from); ?>-<?php echo number_format($to); ?></strong>
                of <strong><?php echo number_format($total_rows); ?></strong>
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table">
                <thead>
                    <tr>
                        <th><?php echo lang('customer_id'); ?></th>
                        <th><?php echo lang('customer_name'); ?></th>
                        <th><?php echo lang('customer_phone'); ?></th>
                        <th><?php echo lang('customer_email'); ?></th>
                        <th style="text-align:right;"><?php echo lang('customer_sale_invoice'); ?></th>
                        <th style="text-align:right;"><?php echo lang('customer_advance_pay'); ?></th>
                        <th style="text-align:right;"><?php echo lang('customer_account_receivable'); ?></th>
                        <th style="width:90px;"><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customer_list)) { ?>
                        <?php foreach ($customer_list as $value) { ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($value->customerid, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td class="customer-name"><?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($value->phone, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($value->email, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell muted-cell">&mdash;</td>
                                <td class="amount-cell muted-cell">&mdash;</td>
                                <td class="amount-cell muted-cell">&mdash;</td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?php echo site_url(current_lang() . '/customer/customer_register/' . encode_id($value->id)); ?>"
                                           class="btn btn-warning btn-xs"
                                           title="<?php echo htmlspecialchars(lang('button_edit'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fa fa-users"></i>
                                    <?php echo lang('no_records_found'); ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="list-footer">
            <div class="pagination-wrap"><?php echo isset($links) ? $links : ''; ?></div>
            <div class="page-size-wrap"><?php page_selector(); ?></div>
        </div>
    </div>
</div>
