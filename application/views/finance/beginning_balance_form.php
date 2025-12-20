<?php echo form_open_multipart(current_lang() . "/finance/beginning_balance_create/" . (isset($id) ? $id : ''), 'class="form-horizontal"'); ?>

<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}
?>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('fiscal_year'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select class="form-control" name="fiscal_year_id" id="fiscal_year_id" required>
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php foreach ($fiscal_years as $fy) { ?>
                <option value="<?php echo $fy->id; ?>" <?php echo (isset($balance) && $balance->fiscal_year_id == $fy->id ? 'selected' : ''); ?>>
                    <?php echo $fy->name . ' (' . date('M d, Y', strtotime($fy->start_date)) . ' - ' . date('M d, Y', strtotime($fy->end_date)) . ')'; ?>
                </option>
            <?php } ?>
        </select>
        <?php echo form_error('fiscal_year_id'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('finance_account_code'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select class="form-control" name="account" id="account" required>
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php 
            foreach ($account_list as $type_key => $type_data) {
                if (isset($type_data['data']) && count($type_data['data']) > 0) {
                    $type_info = $type_data['info'];
                    ?>
                    <optgroup label="<?php echo $type_info->name; ?>">
                        <?php
                        foreach ($type_data['data'] as $account) {
                            $selected = (isset($balance) && $balance->account == $account->account) ? 'selected' : '';
                            ?>
                            <option value="<?php echo $account->account; ?>" <?php echo $selected; ?>>
                                <?php echo $account->account . ' - ' . $account->name; ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                <?php }
            } ?>
        </select>
        <?php echo form_error('account'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('beginning_balance_debit'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="debit" id="debit" value="<?php echo isset($balance) ? number_format($balance->debit, 2) : set_value('debit', '0.00'); ?>" class="form-control" onkeyup="formatNumber(this);" />
        <?php echo form_error('debit'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('beginning_balance_credit'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="credit" id="credit" value="<?php echo isset($balance) ? number_format($balance->credit, 2) : set_value('credit', '0.00'); ?>" class="form-control" onkeyup="formatNumber(this);" />
        <?php echo form_error('credit'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('description'); ?> : </label>
    <div class="col-lg-6">
        <textarea name="description" class="form-control" rows="3"><?php echo isset($balance) ? $balance->description : set_value('description'); ?></textarea>
        <?php echo form_error('description'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo isset($balance) ? lang('button_update') : lang('beginning_balance_btncreate'); ?>" type="submit"/>
        <a href="<?php echo site_url(current_lang() . '/finance/beginning_balance_list'); ?>" class="btn btn-default"><?php echo lang('button_cancel'); ?></a>
    </div>
</div>

<?php echo form_close(); ?>

<script>
function formatNumber(input) {
    // Remove all non-numeric characters except decimal point
    var value = input.value.replace(/[^\d.]/g, '');
    
    // Ensure only one decimal point
    var parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit to 2 decimal places
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    input.value = value;
}

// Format on page load
$(document).ready(function() {
    $('#debit, #credit').on('blur', function() {
        var value = parseFloat($(this).val().replace(/,/g, ''));
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });
});
</script>
