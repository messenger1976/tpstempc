<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/finance/finance_account_create'); ?>"><?php echo lang('finance_account_create') ?></a>
        <a  class="btn btn-success" href="<?php echo site_url(current_lang() . '/finance/finance_account_list_print'); ?>" target="_blank" style="margin-left: 10px;"><i class="fa fa-print"></i> Print</a>
        <a  class="btn btn-info" href="<?php echo site_url(current_lang() . '/finance/finance_account_list_export'); ?>" style="margin-left: 10px;"><i class="fa fa-file-excel-o"></i> Export to Excel</a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th><?php echo lang('account_no'); ?></th>
                <th><?php echo lang('finance_account_type'); ?></th>
                <th><?php echo lang('chart_sub_type_name'); ?></th>
                <th><?php echo lang('finance_account_name'); ?></th>
               <th><?php echo lang('finance_account_description'); ?></th>
                <th style="width:150px;"><?php echo lang('actioncolumn'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            if (count($account_chart) > 0) {
                foreach ($account_chart as $key => $value) {
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $value->account ?></td>
                         <td><?php
                            $account_type = $this->finance_model->account_type(null,$value->account_type)->row();
                            echo $account_type->name
                            ?></td>
                        <td><?php
                            $sub_account_type = '-';
                            if (isset($value->sub_account_type) && !empty($value->sub_account_type)) {
                                $sub_type_result = $this->finance_model->account_type_sub(null, $value->account_type, $value->sub_account_type);
                                if ($sub_type_result && $sub_type_result->num_rows() > 0) {
                                    $sub_type = $sub_type_result->row();
                                    $sub_account_type = $sub_type->name;
                                }
                            }
                            echo $sub_account_type;
                            ?></td>
                        <td><?php echo $value->name ?></td>
                       
                       <td ><?php echo $value->description ?></td>
                        <td>
                            <?php if($value->edit == 1){ ?>
                            <a href="<?php echo site_url(current_lang() . '/finance/finance_account_edit/' . encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a>
                            <?php } ?>
                            <?php if($value->edit == 1){ ?>
                            <a href="javascript:void(0);" class="btn-delete-account" data-id="<?php echo encode_id($value->id); ?>" data-name="<?php echo htmlspecialchars($value->name); ?>" style="color: red; margin-left: 10px;">
                                <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                            </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php }
            } else {
                ?>

                <tr>
                    <td colspan="8"> <?php echo lang('data_not_found'); ?></td>
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
    $('.btn-delete-account').click(function() {
        var accountId = $(this).data('id');
        var accountName = $(this).data('name');
        var deleteUrl = '<?php echo site_url(current_lang() . '/finance/finance_account_delete/'); ?>/' + accountId;
        
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover the chart of account: " + accountName + "!",
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