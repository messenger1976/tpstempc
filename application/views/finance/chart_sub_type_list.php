<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a class="btn btn-primary" href="<?php echo site_url(current_lang() . '/finance/chart_sub_type_create'); ?>"><?php echo lang('chart_sub_type_create'); ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th><?php echo lang('chart_type'); ?></th>
                <th><?php echo lang('chart_sub_type_account'); ?></th>
                <th><?php echo lang('chart_sub_type_name'); ?></th>
                <th><?php echo lang('actioncolumn'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            if (count($chart_sub_types) > 0) {
                foreach ($chart_sub_types as $key => $value) {
                    $chart_type = $this->finance_model->account_type(null, $value->accounttype)->row();
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo ($chart_type ? $chart_type->name : '-'); ?></td>
                        <td><?php echo $value->sub_account; ?></td>
                        <td><?php echo $value->name; ?></td>
                        <td>
                            <a href="<?php echo site_url(current_lang() . '/finance/chart_sub_type_edit/' . encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a>
                            <a href="javascript:void(0);" class="btn-delete-chart-sub-type" data-id="<?php echo encode_id($value->id); ?>" data-name="<?php echo htmlspecialchars($value->name); ?>" style="color: red; margin-left: 10px;">
                                <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                            </a>
                        </td>
                    </tr>
                <?php }
            } else {
                ?>
                <tr>
                    <td colspan="5"> <?php echo lang('data_not_found'); ?></td>
                </tr>
<?php } ?>
        </tbody>
    </table>
</div>

<script>
(function() {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }
        
        $(document).ready(function() {
    $('.btn-delete-chart-sub-type').click(function() {
        var chartSubTypeId = $(this).data('id');
        var chartSubTypeName = $(this).data('name');
        var deleteUrl = '<?php echo site_url(current_lang() . '/finance/chart_sub_type_delete/'); ?>/' + chartSubTypeId;
        
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover the chart sub type: " + chartSubTypeName + "!",
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

