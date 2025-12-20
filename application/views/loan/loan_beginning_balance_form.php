<?php echo form_open_multipart(current_lang() . "/loan/loan_beginning_balance_create/" . (isset($id) ? $id : ''), 'class="form-horizontal"'); ?>

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
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_member_id'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="member_id" id="member_id" value="<?php echo isset($balance) ? $balance->member_id : set_value('member_id'); ?>" class="form-control" required />
        <?php echo form_error('member_id'); ?>
        <small class="help-block"><?php echo lang('member_id'); ?></small>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_loan_product'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select class="form-control" name="loan_product_id" id="loan_product_id" required>
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php foreach ($loan_products as $product) { ?>
                <option value="<?php echo $product->id; ?>" <?php echo (isset($balance) && $balance->loan_product_id == $product->id ? 'selected' : ''); ?>>
                    <?php echo $product->name; ?>
                </option>
            <?php } ?>
        </select>
        <?php echo form_error('loan_product_id'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_loan_id'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="loan_id" id="loan_id" value="<?php echo isset($balance) ? $balance->loan_id : set_value('loan_id'); ?>" class="form-control" />
        <?php echo form_error('loan_id'); ?>
        <small class="help-block">Optional - Reference to existing loan ID</small>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_principal'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="principal_balance" id="principal_balance" value="<?php echo isset($balance) ? number_format($balance->principal_balance, 2) : set_value('principal_balance', '0.00'); ?>" class="form-control" onkeyup="formatNumber(this); calculateTotal();" />
        <?php echo form_error('principal_balance'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_interest'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="interest_balance" id="interest_balance" value="<?php echo isset($balance) ? number_format($balance->interest_balance, 2) : set_value('interest_balance', '0.00'); ?>" class="form-control" onkeyup="formatNumber(this); calculateTotal();" />
        <?php echo form_error('interest_balance'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_penalty'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="penalty_balance" id="penalty_balance" value="<?php echo isset($balance) ? number_format($balance->penalty_balance, 2) : set_value('penalty_balance', '0.00'); ?>" class="form-control" onkeyup="formatNumber(this); calculateTotal();" />
        <?php echo form_error('penalty_balance'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_total'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" id="total_balance" value="<?php echo isset($balance) ? number_format($balance->total_balance, 2) : '0.00'; ?>" class="form-control" readonly style="font-weight: bold; background-color: #f5f5f5;" />
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_disbursement_date'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="disbursement_date" id="disbursement_date" value="<?php echo isset($balance) && $balance->disbursement_date ? date('m/d/Y', strtotime($balance->disbursement_date)) : set_value('disbursement_date'); ?>" class="form-control datepicker" />
        <?php echo form_error('disbursement_date'); ?>
        <small class="help-block">Optional - Original loan disbursement date (MM/DD/YYYY)</small>
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
        <input class="btn btn-primary" value="<?php echo isset($balance) ? lang('button_update') : lang('loan_beginning_balance_btncreate'); ?>" type="submit"/>
        <a href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_list'); ?>" class="btn btn-default"><?php echo lang('button_cancel'); ?></a>
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

function calculateTotal() {
    var principal = parseFloat($('#principal_balance').val().replace(/,/g, '')) || 0;
    var interest = parseFloat($('#interest_balance').val().replace(/,/g, '')) || 0;
    var penalty = parseFloat($('#penalty_balance').val().replace(/,/g, '')) || 0;
    var total = principal + interest + penalty;
    $('#total_balance').val(total.toFixed(2));
}

// Format on page load
$(document).ready(function() {
    $('#principal_balance, #interest_balance, #penalty_balance').on('blur', function() {
        var value = parseFloat($(this).val().replace(/,/g, ''));
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
        calculateTotal();
    });
    
    // Initialize datepicker
    $('.datepicker').datepicker({
        format: 'mm/dd/yyyy',
        autoclose: true,
        todayHighlight: true
    });
    
    // Calculate total on page load
    calculateTotal();
});
</script>
