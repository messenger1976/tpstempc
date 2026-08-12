<?php $this->load->view('loan/list_page_styles'); ?>

<?php
$productlist = isset($productlist) ? $productlist : array();
$total_rows = count($productlist);
$create_url = site_url(current_lang() . '/setting/addloan_product/');
?>

<style type="text/css">
.member-list-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.member-list-page .product-name {
    font-weight: 700;
    color: #2f4050;
}
.member-list-page .meta-chip {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    background: #e8f8f5;
    color: #1ab394;
}
.member-list-page .meta-chip.method {
    background: #eef3fb;
    color: #3c6eae;
}
.member-list-page .meta-chip.interval {
    background: #fef6eb;
    color: #d68910;
}
.member-list-page .desc-cell {
    color: #676a6c;
    max-width: 280px;
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
                <i class="fa fa-briefcase icon-badge"></i>
                <h4><?php echo lang('loan_product_list'); ?></h4>
            </div>
            <div class="head-actions">
                <div class="result-meta">
                    Showing <strong><?php echo number_format($total_rows); ?></strong> product<?php echo $total_rows === 1 ? '' : 's'; ?>
                </div>
                <a href="<?php echo $create_url; ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> <?php echo lang('loanproduct_add'); ?>
                </a>
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table">
                <thead>
                    <tr>
                        <th style="width:70px;">ID</th>
                        <th><?php echo lang('loanproduct_name'); ?></th>
                        <th><?php echo lang('loanproduct_interest_year'); ?></th>
                        <th><?php echo lang('loanproduct_interest_method'); ?></th>
                        <th><?php echo lang('loanproduct_interval'); ?></th>
                        <th><?php echo lang('loanproduct_description'); ?></th>
                        <th style="width:100px;"><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productlist)) {
                        foreach ($productlist as $value) {
                            $interest_method = $this->setting_model->interest_method($value->interest_method)->row();
                            $intervalinfo = $this->setting_model->intervalinfo($value->interval)->row();
                            $method_name = $interest_method ? $interest_method->name : '—';
                            $interval_name = $intervalinfo ? $intervalinfo->name : '—';
                            ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo (int) $value->id; ?></span></td>
                                <td class="product-name"><?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="meta-chip"><?php echo htmlspecialchars($value->interest_rate, ENT_QUOTES, 'UTF-8'); ?>%</span></td>
                                <td><span class="meta-chip method"><?php echo htmlspecialchars($method_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><span class="meta-chip interval"><?php echo htmlspecialchars($interval_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td class="desc-cell"><?php echo htmlspecialchars($value->description, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?php echo site_url(current_lang() . '/setting/addloan_product/' . encode_id($value->id)); ?>"
                                           class="btn btn-warning btn-xs"
                                           title="<?php echo htmlspecialchars(lang('button_edit'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding: 28px;">
                                <?php echo lang('no_records_found'); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
