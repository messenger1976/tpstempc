
<?php echo form_open_multipart(current_lang() . "/setting/global_setting/" , 'class="form-horizontal"'); ?>

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
<br/>
<?php
foreach ($default_list as $key => $value) { ?>
   <div class="form-group">
       <label class="col-lg-4 control-label"><?php echo str_replace('_', ' ', $value->key); ?></label>
    <div class="col-lg-5">
        <?php if ($value->key == 'RETAINED_EARNINGS_ACCOUNT') { ?>
            <select name="field_<?php echo $value->id; ?>" id="retained_earnings_account" class="form-control select2-account">
                <option value=""><?php echo lang('select_default_text'); ?></option>
                <?php
                $selected = $value->text;
                if (isset($account_list) && !empty($account_list)) {
                    foreach ($account_list as $type_key => $type_data) {
                        ?>
                        <optgroup label="<?php echo isset($type_data['info']->name) ? $type_data['info']->name : $type_key; ?>">
                            <?php foreach ($type_data['data'] as $acc) {
                                $level = isset($acc->display_level) ? (int)$acc->display_level : 0;
                                $chart_type_name = isset($type_data['info']->name) ? strtoupper($type_data['info']->name) : '';
                                $data_chart_type = htmlspecialchars($chart_type_name, ENT_QUOTES);
                                $data_account_name = htmlspecialchars($acc->name, ENT_QUOTES);
                                $data_level = $level;
                                ?>
                                <option
                                    <?php echo ($acc->account == $selected ? 'selected="selected"' : ''); ?>
                                    value="<?php echo $acc->account; ?>"
                                    data-chart-type="<?php echo $data_chart_type; ?>"
                                    data-account-name="<?php echo $data_account_name; ?>"
                                    data-level="<?php echo $data_level; ?>"
                                >
                                    <?php echo $acc->account . ' - ' . $chart_type_name . ' - ' . htmlspecialchars($acc->name); ?>
                                </option>
                            <?php } ?>
                        </optgroup>
                    <?php }
                } ?>
            </select>
        <?php } else if($value->is_number == 0){ ?>
        <textarea name="field_<?php echo $value->id; ?>" class="form-control"><?php echo $value->text; ?></textarea>
        <?php }else{ ?>
        <input name="field_<?php echo $value->id; ?>" class="form-control " value="<?php echo $value->text; ?>" />
        <?php } ?>
         <?php echo form_error('field_'.$value->id); ?>
    </div>
      
</div> 
<?php }
?>

<input type="hidden" value="1" name="SAVEDATA"/>
<div class="form-group">
    <label class="col-lg-4 control-label">&nbsp;</label>
    <div class="col-lg-5">
        <input class="btn btn-primary" value="<?php echo lang('tax_addbtn'); ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>

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

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
$(document).ready(function() {
    if ($('#retained_earnings_account').length) {
        $('#retained_earnings_account').select2({
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
                
                if (level > 0) {
                    var indent = '';
                    for (var i = 0; i < level; i++) {
                        indent += '&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    $result.append('<span class="account-indent">' + indent + '└ </span>');
                }
                
                $result.append('<span>' + accountNumber + ' - </span>');
                $result.append('<span class="chart-type-name">' + chartType + '</span>');
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
                
                return accountNumber + ' - ' + chartType + ' - ' + accountName;
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        });
    }
});
</script>