
<div class="table-responsive">
    <div style="width: 90%; margin: auto; text-align: right; margin-bottom: 15px;">
        <?php echo anchor(current_lang().'/setting/payment_method_create/', lang('payment_method_create'), 'class="btn btn-primary"'); ?>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('payment_method_name'); ?></th>
                <th><?php echo lang('payment_method_description'); ?></th>
                <th><?php echo lang('payment_method_gl_account'); ?></th>
                <th><?php echo lang('payment_method_status'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payment_method_list)) { ?>
                <?php foreach ($payment_method_list as $key => $value) { 
                    $status = isset($value->status) ? (int)$value->status : 1;
                    $status_text = $status == 1 ? lang('payment_method_active') : lang('payment_method_inactive');
                    $status_class = $status == 1 ? 'label-success' : 'label-danger';
                ?>
                    <tr>
                        <td><?php echo $value->name; ?></td>
                        <td><?php echo $value->description; ?></td>
                        <td>
                            <?php if (!empty($value->gl_account_code) && !empty($value->gl_account_name)) { ?>
                                <?php echo $value->gl_account_code . ' - ' . $value->gl_account_name; ?>
                            <?php } else { ?>
                                <span style="color: #999;">-</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="javascript:void(0);" 
                               class="toggle-status" 
                               data-id="<?php echo encode_id($value->id); ?>"
                               data-status="<?php echo $status; ?>"
                               data-name="<?php echo htmlspecialchars($value->name, ENT_QUOTES); ?>">
                                <span class="label <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </a>
                        </td>
                        <td>
                            <?php echo anchor(current_lang() . "/setting/payment_method_create/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'), 'class="btn btn-sm btn-primary"'); ?>
                            <a href="javascript:void(0);" 
                               class="btn btn-sm btn-danger btn-delete-payment-method" 
                               data-id="<?php echo encode_id($value->id); ?>"
                               data-name="<?php echo htmlspecialchars($value->name, ENT_QUOTES); ?>">
                                <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" style="text-align: center;"><?php echo lang('payment_method_no_data'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    // SweetAlert for status toggle
    $('.toggle-status').click(function(e) {
        e.preventDefault();
        var paymentMethodId = $(this).data('id');
        var currentStatus = $(this).data('status');
        var paymentMethodName = $(this).data('name');
        var newStatusText = currentStatus == 1 ? '<?php echo lang('payment_method_inactive'); ?>' : '<?php echo lang('payment_method_active'); ?>';
        var currentStatusText = currentStatus == 1 ? '<?php echo lang('payment_method_active'); ?>' : '<?php echo lang('payment_method_inactive'); ?>';
        
        swal({
            title: "<?php echo lang('payment_method_change_status'); ?>",
            text: "<?php echo lang('payment_method_change_status_confirm'); ?>: " + paymentMethodName + "?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#5cb85c",
            confirmButtonText: "<?php echo lang('payment_method_yes_change'); ?>",
            cancelButtonText: "<?php echo lang('button_cancel'); ?>",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: '<?php echo site_url(current_lang() . '/setting/payment_method_toggle_status'); ?>/' + paymentMethodId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            swal({
                                title: "<?php echo lang('payment_method_status_updated'); ?>",
                                text: response.message,
                                type: "success"
                            }, function() {
                                location.reload();
                            });
                        } else {
                            swal("<?php echo lang('payment_method_error'); ?>", response.message, "error");
                        }
                    },
                    error: function() {
                        swal("<?php echo lang('payment_method_error'); ?>", "<?php echo lang('payment_method_status_update_fail'); ?>", "error");
                    }
                });
            }
        });
    });
    
    // SweetAlert for delete confirmation
    $('.btn-delete-payment-method').click(function(e) {
        e.preventDefault();
        var paymentMethodId = $(this).data('id');
        var paymentMethodName = $(this).data('name');
        var deleteUrl = '<?php echo site_url(current_lang() . '/setting/payment_method_delete'); ?>/' + paymentMethodId;
        
        swal({
            title: "<?php echo lang('payment_method_delete_confirm_title'); ?>",
            text: "<?php echo lang('payment_method_delete_confirm_text'); ?>: " + paymentMethodName + "!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "<?php echo lang('payment_method_yes_delete'); ?>",
            cancelButtonText: "<?php echo lang('button_cancel'); ?>",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function(isConfirm) {
            if (isConfirm) {
                window.location.href = deleteUrl;
            }
        });
    });
});
</script>

