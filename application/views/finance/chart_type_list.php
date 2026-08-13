<?php $this->load->view('loan/list_page_styles'); ?>
<style>
.member-list-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.member-list-page .head-actions .btn {
    border-radius: 6px;
    font-weight: 600;
}
.member-list-page .member-table > thead > tr > th,
.member-list-page .member-table > tbody > tr > td {
    border: 1px solid #e7eaec !important;
}
.member-list-page .member-table > thead > tr > th {
    border-top: 0 !important;
}
.member-list-page .member-table > thead > tr > th:not(:first-child),
.member-list-page .member-table > tbody > tr > td:not(:first-child) {
    border-left: 0 !important;
}
.member-list-page .member-table > tbody > tr:hover td {
    box-shadow: none;
}
</style>

<?php
$chart_types = isset($chart_types) ? $chart_types : array();
$row_count = is_array($chart_types) ? count($chart_types) : 0;
?>

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
                <i class="fa fa-th-list icon-badge"></i>
                <h4><?php echo lang('chart_type_list'); ?></h4>
            </div>
            <div class="head-actions">
                <span class="result-meta">
                    <strong><?php echo number_format($row_count); ?></strong>
                    <?php echo $row_count === 1 ? 'record' : 'records'; ?>
                </span>
                <a class="btn btn-primary btn-sm" href="<?php echo site_url(current_lang() . '/finance/chart_type_create'); ?>">
                    <i class="fa fa-plus"></i> <?php echo lang('chart_type_create'); ?>
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered member-table">
                <thead>
                    <tr>
                        <th style="text-align:center; width:60px;"><?php echo lang('sno'); ?></th>
                        <th><?php echo lang('chart_type_account'); ?></th>
                        <th><?php echo lang('chart_type_name'); ?></th>
                        <th><?php echo lang('actioncolumn'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($row_count > 0) {
                        $i = 1;
                        foreach ($chart_types as $value) { ?>
                            <tr>
                                <td style="text-align:center;"><?php echo $i++; ?></td>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($value->account, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a class="btn btn-primary btn-xs" href="<?php echo site_url(current_lang() . '/finance/chart_type_edit/' . encode_id($value->id)); ?>">
                                            <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-danger btn-xs btn-delete-chart-type" data-id="<?php echo encode_id($value->id); ?>" data-name="<?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="fa fa-th-list"></i>
                                    <?php echo lang('data_not_found'); ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }

        $(document).ready(function() {
            $('.btn-delete-chart-type').click(function() {
                var chartTypeId = $(this).data('id');
                var chartTypeName = $(this).data('name');
                var deleteUrl = '<?php echo site_url(current_lang() . '/finance/chart_type_delete/'); ?>/' + chartTypeId;

                swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover the chart type: " + chartTypeName + "!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }, function(isConfirm) {
                    if (isConfirm) {
                        window.location.href = deleteUrl;
                    }
                });
            });
        });
    }
    initScripts();
})();
</script>
