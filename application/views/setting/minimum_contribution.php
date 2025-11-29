<!-- Select2 CSS -->
<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php echo form_open_multipart(current_lang() . "/setting/contribution_minimum", 'class="form-horizontal"'); ?>

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
$contribution = $this->setting_model->global_contribution_info();
?>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('contribution_minimum_amount'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="amount" value="<?php echo (set_value('amount') ? set_value('amount') : $contribution->amount); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('amount'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('contribution_minimum_overdueamount'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="charge" value="<?php echo (set_value('charge') ? set_value('charge') : number_format($contribution->overdue_amount)); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('charge'); ?>
    </div>
</div>

<div style="margin: 20px 0px 10px 50px; width: 80%; font-size: 16px; color: brown; font-weight: bold; border-bottom:  1px solid #ccc;">Capital Build Up Account</div>
<div class="form-group"><label class="col-lg-3 control-label">Capital Build Up Account  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="capital_build_up_account" id="capital_build_up_account" class="form-control select2-account">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = set_value('capital_build_up_account') ? set_value('capital_build_up_account') : (isset($contribution->capital_build_up_account) ? $contribution->capital_build_up_account : '');
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
        <?php echo form_error('capital_build_up_account'); ?>
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
    $('#capital_build_up_account').select2({
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
        $('#capital_build_up_account').val(savedValue).trigger('change');
    }
});
</script>

<!-- 
Note: Make sure the database table 'contribution_global' has the column 'capital_build_up_account'.
If the column doesn't exist, run this SQL:
ALTER TABLE `contribution_global` ADD COLUMN `capital_build_up_account` VARCHAR(50) NULL AFTER `overdue_amount`;
-->

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('save_info_btn'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>

