<div class="table-responsive">
    <div style="width: 90%; margin: auto; text-align: right; margin-bottom: 15px;">
        <?php echo anchor(current_lang().'/setting/fiscal_year_create/', lang('fiscal_year_create'), 'class="btn btn-primary"'); ?>
    </div>

    <?php if (!empty($active_fiscal_year)): ?>
    <div class="alert alert-info" style="width: 90%; margin: auto;">
        <strong><?php echo lang('fiscal_year_current_active'); ?>:</strong> <?php echo $active_fiscal_year->name; ?>
        (<?php echo date('M d, Y', strtotime($active_fiscal_year->start_date)); ?> - <?php echo date('M d, Y', strtotime($active_fiscal_year->end_date)); ?>)
    </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('fiscal_year_name'); ?></th>
                <th><?php echo lang('fiscal_year_start_date'); ?></th>
                <th><?php echo lang('fiscal_year_end_date'); ?></th>
                <th><?php echo lang('fiscal_year_status'); ?></th>
                <th><?php echo lang('fiscal_year_created_at'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($fiscal_years)) { ?>
                <?php foreach ($fiscal_years as $key => $value) {
                    $status = isset($value->status) ? (int)$value->status : 0;
                    $status_text = $status == 1 ? lang('fiscal_year_active') : lang('fiscal_year_inactive');
                    $status_class = $status == 1 ? 'label-success' : 'label-default';
                ?>
                    <tr>
                        <td><?php echo $value->name; ?></td>
                        <td><?php echo date('M d, Y', strtotime($value->start_date)); ?></td>
                        <td><?php echo date('M d, Y', strtotime($value->end_date)); ?></td>
                        <td>
                            <a href="javascript:void(0);"
                               class="toggle-status"
                               data-id="<?php echo encode_id($value->id); ?>"
                               data-name="<?php echo htmlspecialchars($value->name, ENT_QUOTES); ?>"
                               data-current-status="<?php echo $status; ?>">
                                <span class="label <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </a>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($value->created_at)); ?></td>
                        <td>
                            <?php echo anchor(current_lang() . "/setting/fiscal_year_create/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'), 'class="btn btn-sm btn-primary"'); ?>
                            <?php if ($status == 0): ?>
                                <a href="javascript:void(0);"
                                   class="btn btn-sm btn-danger btn-delete-fiscal-year"
                                   data-id="<?php echo encode_id($value->id); ?>"
                                   data-name="<?php echo htmlspecialchars($value->name, ENT_QUOTES); ?>">
                                    <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="6" style="text-align: center;"><?php echo lang('fiscal_year_no_data'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- jQuery and SweetAlert JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
(function() {
    // Ensure jQuery is available
    var $ = jQuery;

    $(document).ready(function() {
    // SweetAlert for status toggle confirmation
    $('.toggle-status').click(function(e) {
        e.preventDefault();
        var fiscalYearId = $(this).data('id');
        var fiscalYearName = $(this).data('name');
        var currentStatus = parseInt($(this).data('current-status'));

        // Determine action based on current status
        var isActivating = currentStatus === 0; // 0 = inactive, 1 = active
        var action = isActivating ? 'activate' : 'deactivate';
        var actionText = isActivating ? 'set as active' : 'deactivate';
        var confirmButtonText = isActivating ? "<?php echo lang('fiscal_year_yes_set_active'); ?>" : 'Yes, Deactivate';
        var confirmButtonColor = isActivating ? "#5cb85c" : "#f0ad4e";

        swal({
            title: "Confirm Status Change",
            text: "Are you sure you want to " + actionText + " fiscal year: " + fiscalYearName + "?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: confirmButtonColor,
            confirmButtonText: confirmButtonText,
            cancelButtonText: "<?php echo lang('button_cancel'); ?>",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function(isConfirm) {
            if (isConfirm) {
                window.location.href = '<?php echo site_url(current_lang() . '/setting/fiscal_year_toggle_status'); ?>/' + fiscalYearId;
            }
        });
    });

    // SweetAlert for delete confirmation
    $('.btn-delete-fiscal-year').click(function(e) {
        e.preventDefault();
        var fiscalYearId = $(this).data('id');
        var fiscalYearName = $(this).data('name');
        var deleteUrl = '<?php echo site_url(current_lang() . '/setting/fiscal_year_delete'); ?>/' + fiscalYearId;

        swal({
            title: "<?php echo lang('fiscal_year_delete_confirm_title'); ?>",
            text: "<?php echo lang('fiscal_year_delete_confirm_text'); ?>: " + fiscalYearName + "!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "<?php echo lang('fiscal_year_yes_delete'); ?>",
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
})();
</script>
