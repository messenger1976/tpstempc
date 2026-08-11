<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<style>
    .loan-disburse-filters .select2-container { width: 260px !important; }
    .loan-disburse-filters .form-group { margin-right: 10px; margin-bottom: 12px; vertical-align: top; }
</style>

<form method="get" action="<?php echo site_url(current_lang() . '/loan/loan_disbursement'); ?>" class="form-inline loan-disburse-filters" style="margin-bottom: 20px;">
    <div class="form-group">
        <select name="pid" id="pid" class="form-control">
            <option value=""><?php echo lang('loan_search_name'); ?></option>
            <?php if (!empty($selected_pid) && !empty($selected_member_text)) { ?>
                <option value="<?php echo htmlspecialchars($selected_pid); ?>" selected="selected">
                    <?php echo htmlspecialchars($selected_member_text); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group">
        <select name="product_id" id="product_id" class="form-control" style="min-width: 220px;">
            <option value="all" <?php echo (isset($selected_product_id) && $selected_product_id == 'all' ? 'selected' : ''); ?>>
                <?php echo lang('loan_products'); ?>
            </option>
            <?php
            if (!empty($loan_products)) {
                foreach ($loan_products as $product) {
                    $sel = (isset($selected_product_id) && (string) $selected_product_id === (string) $product->id) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $product->id; ?>" <?php echo $sel; ?>>
                        <?php echo htmlspecialchars($product->name); ?>
                    </option>
                <?php }
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary"><?php echo lang('button_search'); ?></button>
        <?php if (!empty($selected_pid) || (isset($selected_product_id) && $selected_product_id != 'all')) { ?>
            <a href="<?php echo site_url(current_lang() . '/loan/loan_disbursement'); ?>" class="btn btn-default"><?php echo lang('reset'); ?></a>
        <?php } ?>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('loan_LID'); ?></th>
                <th><?php echo lang('member_name'); ?></th>
                <th><?php echo lang('loan_product'); ?></th>
                <th><?php echo lang('loan_applied_amount'); ?></th>
                <th><?php echo lang('loan_installment'); ?></th>
                <th><?php echo lang('loan_installment_amount'); ?></th>
                <th><?php echo lang('loan_total_interest'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($loan_wait) > 0) {
                foreach ($loan_wait as $key => $value) {
                    $info = $this->member_model->member_basic_info(null, $value->PID)->row();
                    $member_label = $info
                        ? ($info->member_id . ' : ' . trim($info->firstname . ' ' . $info->middlename . ' ' . $info->lastname))
                        : '-';
                    $product_name = !empty($value->loan_product_name) ? $value->loan_product_name : '-';
                    $interval = $this->setting_model->intervalinfo($value->interval)->row();
                    $installment_label = $value->number_istallment . ' ' . ($interval ? $interval->description : '');
                    ?>
            <tr>
                <td><?php echo $value->LID; ?></td>
                <td><?php echo htmlspecialchars($member_label); ?></td>
                <td><?php echo htmlspecialchars($product_name); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->basic_amount, 2); ?></td>
                <td style="text-align: center;"><?php echo htmlspecialchars($installment_label); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->installment_amount, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->total_interest_amount, 2); ?></td>
                <td><?php echo anchor(current_lang() . "/loan/loan_disburse_entry/" . encode_id($value->LID), ' <i class="fa fa-file"></i> ' . lang('loan_disburse_link')); ?></td>
            </tr>
                <?php }
            } else { ?>
            <tr>
                <td colspan="8" class="text-center"><?php echo lang('no_record_found'); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
(function ($) {
    $(function () {
        $('#pid').select2({
            placeholder: <?php echo json_encode(lang('loan_search_name')); ?>,
            allowClear: true,
            width: '260px',
            minimumInputLength: 1,
            ajax: {
                url: <?php echo json_encode(site_url(current_lang() . '/loan/search_member_select2')); ?>,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term || '' };
                },
                processResults: function (data) {
                    return { results: (data && data.results) ? data.results : [] };
                },
                cache: true
            }
        });
    });
})(jQuery);
</script>
