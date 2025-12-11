<!-- Select2 CSS -->
<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php echo form_open_multipart(current_lang() . "/setting/payment_method_create/".$id, 'class="form-horizontal"'); ?>

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
    <label class="col-lg-3 control-label"><?php echo lang('payment_method_name'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="name" value="<?php echo (isset($payment_method) ? $payment_method->name : set_value('name')); ?>" class="form-control"/> 
        <?php echo form_error('name'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('payment_method_description'); ?> : </label>
    <div class="col-lg-6">
        <textarea name="description" class="form-control"><?php echo (isset($payment_method) ? $payment_method->description : set_value('description')); ?></textarea>
        <?php echo form_error('description'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('payment_method_gl_account'); ?> : </label>
    <div class="col-lg-6">
        <select name="gl_account_code" id="gl_account_code" class="form-control select2-account">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = set_value('gl_account_code') ? set_value('gl_account_code') : (isset($payment_method->gl_account_code) ? $payment_method->gl_account_code : '');
            if (isset($account_list) && !empty($account_list)) {
                foreach ($account_list as $key => $value) {
                    ?>
                    <optgroup label="<?php echo $value['info']->name; ?>">
                        <?php foreach ($value['data'] as $key1 => $value1) { 
                            $level = isset($value1->display_level) ? (int)$value1->display_level : 0;
                            // Get chart type name
                            $chart_type_name = isset($value['info']->name) ? strtoupper($value['info']->name) : '';
                            // Store data attributes for Select2 HTML formatting
                            $data_chart_type = htmlspecialchars($chart_type_name, ENT_QUOTES);
                            $data_account_name = htmlspecialchars($value1->name, ENT_QUOTES);
                            $data_level = $level;
                            ?>
                            <option 
                                <?php echo ($value1->account == $selected ? 'selected="selected"' : ''); ?> 
                                value="<?php echo $value1->account; ?>"
                                data-chart-type="<?php echo $data_chart_type; ?>"
                                data-account-name="<?php echo $data_account_name; ?>"
                                data-level="<?php echo $data_level; ?>"
                            >
                                <?php echo $value1->account . ' - ' . $chart_type_name . ' - ' . htmlspecialchars($value1->name); ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                <?php } ?>
            <?php } ?>
        </select>
        <?php echo form_error('gl_account_code'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('payment_method_save_btn'); ?>" type="submit"/>
        <?php echo anchor(current_lang() . '/setting/payment_method_list', lang('button_cancel'), 'class="btn btn-default"'); ?>
    </div>
</div>

<style>
.select2-results__option .chart-type-name {
    font-weight: bold;
    font-size: 1.1em;
    color: #2c3e50;
}
.select2-results__option .account-indent {
    display: inline-block;
    margin-left: 0;
}
</style>

<!-- Select2 JS -->
<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 with custom HTML formatting
    $('#gl_account_code').select2({
        width: '100%',
        allowClear: true,
        templateResult: function(data) {
            if (!data.id) {
                return data.text;
            }
            
            var $result = $('<span></span>');
            var level = $(data.element).data('level') || 0;
            var chartType = $(data.element).data('chart-type') || '';
            var accountName = $(data.element).data('account-name') || '';
            var accountNumber = data.id;
            
            // Add indentation
            if (level > 0) {
                var indent = '';
                for (var i = 0; i < level; i++) {
                    indent += '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                $result.append('<span class="account-indent">' + indent + '└ </span>');
            }
            
            // Add account number
            $result.append('<span>' + accountNumber + ' - </span>');
            
            // Add chart type name with bold and larger font
            $result.append('<span class="chart-type-name">' + chartType + '</span>');
            
            // Add account name
            $result.append('<span> - ' + accountName + '</span>');
            
            return $result;
        },
        templateSelection: function(data) {
            if (!data.id) {
                return data.text;
            }
            
            var chartType = $(data.element).data('chart-type') || '';
            var accountName = $(data.element).data('account-name') || '';
            var accountNumber = data.id;
            
            return accountNumber + ' - <strong style="font-size: 1.1em;">' + chartType + '</strong> - ' + accountName;
        },
        escapeMarkup: function(markup) {
            return markup; // Allow HTML
        }
    });
    
    // Ensure the saved value is properly selected after Select2 initialization
    var savedValue = '<?php echo $selected; ?>';
    if (savedValue && savedValue !== '') {
        $('#gl_account_code').val(savedValue).trigger('change');
    }
});
</script>

<?php echo form_close(); ?>

