<!-- Select2 CSS -->
<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php echo form_open_multipart(current_lang() . "/setting/saving_account_typecreate/".$id, 'class="form-horizontal"'); ?>

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
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_name'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="account_name" value="<?php echo (isset ($account) ? $account->name: set_value('account_name')); ?>"  class="form-control"/> 
        <?php echo form_error('account_name'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_description'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <textarea name="account_description"   class="form-control"/> <?php echo (isset ($account) ?  $account->description: set_value('account_description') ); ?> </textarea>
        <?php echo form_error('account_description'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_min_amount'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="minimum_amount" value="<?php echo (isset ($account) ? $account->min_amount: set_value('minimum_amount') ); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('minimum_amount'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_max_withdrawal'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="max_withdrawal" value="<?php echo (isset ($account) ? $account->max_withdrawal : set_value('max_withdrawal')); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('max_withdrawal'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_interest_rate'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="interest_rate" value="<?php echo (isset ($account) ? $account->interest_rate : set_value('interest_rate')); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('monthly_amount'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('account_min_deposit'); ?> :</label>
    <div class="col-lg-6">
        <input type="text" name="min_deposit" value="<?php echo (isset ($account) ? $account->min_deposit: set_value('min_deposit')); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('min_deposit'); ?>
    </div>
</div>

<div style="margin: 20px 0px 10px 50px; width: 80%; font-size: 16px; color: brown; font-weight: bold; border-bottom:  1px solid #ccc;">Account Setup</div>
<div class="form-group"><label class="col-lg-3 control-label">Account Setup  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="account_setup" id="account_setup" class="form-control select2-account">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = set_value('account_setup') ? set_value('account_setup') : (isset($account->account_setup) ? $account->account_setup : '');
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
        <?php echo form_error('account_setup'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label">Account Setup for Interest Rate  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="account_setup_interest_rate" id="account_setup_interest_rate" class="form-control select2-account">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected_interest = set_value('account_setup_interest_rate') ? set_value('account_setup_interest_rate') : (isset($account->account_setup_interest_rate) ? $account->account_setup_interest_rate : '');
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
                                <?php echo ($value1->account == $selected_interest ? 'selected="selected"' : ''); ?> 
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
        <?php echo form_error('account_setup_interest_rate'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('save_info_btn'); ?>" type="submit"/>
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
    $('#account_setup').select2({
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
        $('#account_setup').val(savedValue).trigger('change');
    }
});
</script>

<!-- 
Note: Make sure the database table 'saving_account_type' has the columns 'account_setup' and 'account_setup_interest_rate'.
If the columns don't exist, run this SQL:
ALTER TABLE `saving_account_type` ADD COLUMN `account_setup` VARCHAR(50) NULL AFTER `min_deposit`;
ALTER TABLE `saving_account_type` ADD COLUMN `account_setup_interest_rate` VARCHAR(50) NULL AFTER `account_setup`;
-->

<?php echo form_close(); ?>
