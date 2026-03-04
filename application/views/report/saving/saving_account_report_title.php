<link href="<?php echo base_url(); ?>media/css/choosen/chosen.css" rel="stylesheet">
<!-- Gritter -->
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/report_saving/saving_account_report_title/" . $link_cat . '/' . $id, 'class="form-horizontal"'); ?>
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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo ($link_cat == 1 ? 'Account created From' : 'From'); ?>  : <span class="required">*</span></label>
    <div class=" col-lg-6">
        <div class="input-group date" id="datetimepicker" >
            <input type="text" name="fromdate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo (isset($reportinfo) ? format_date($reportinfo->fromdate, false) : set_value('fromdate')); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>
        <?php echo form_error('fromdate'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Until'; ?>  : <span class="required">*</span></label>
    <div class=" col-lg-6">
        <div class="input-group date" id="datetimepicker2" >
            <input type="text" name="todate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo (isset($reportinfo) ? format_date($reportinfo->todate, false) : set_value('todate')); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>
        <?php echo form_error('todate'); ?>
    </div>
</div>

<?php if ($link_cat != 2) { ?>
    <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Description'; ?>  : <span class="required">*</span></label>
        <div class="col-lg-6">
            <textarea type="text" name="description" class="form-control"><?php echo (isset($reportinfo) ? $reportinfo->description : set_value('description')); ?> </textarea>
            <?php echo form_error('description'); ?>
        </div>
    </div>

<?php } else { ?>
    <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Account'; ?>  : <span class="required">*</span></label>
        <div class="col-lg-6">
            <select name="description" class="form-control" id="description">
                <option value=""> <?php echo lang('select_default_text'); ?></option>
                <?php
                $account_list = $this->finance_model->member_saving_account_list()->result();
                $selected = (isset($reportinfo) ? $reportinfo->description : set_value('description'));

                foreach ($account_list as $key => $value) {
                    $acc_name = $this->finance_model->saving_account_name($value->account);
                    
                    // Format display text: account_cat - [old_members_acct] account_name
                    $display_text = $value->account_cat;
                    if (!empty($value->old_members_acct)) {
                        $display_text .= ' - [' . $value->old_members_acct . ']';
                    }
                    $display_text .= ' ' . $acc_name;
                    ?>
                    <option data-account-cat="<?php echo $value->account_cat; ?>" <?php echo ($selected ? ($selected == $value->account ? 'selected="selected"' : '') : ''); ?> value="<?php echo $value->account; ?>"> <?php echo $display_text; ?></option>
                <?php }
                ?>
            </select>

            <?php echo form_error('description'); ?>
        </div>
    </div>

<?php } ?>

<?php
if ($link_cat == 1 || $link_cat == 2) {
    $account = $this->finance_model->saving_account_list()->result();
    ?>
    <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Account Type'; ?>  :</label>
        <div class="col-lg-6">
            <select name="account_type" class="form-control" id="account_type">
                <option value="">All</option>
                <?php
                $select = (isset($reportinfo) ? $reportinfo->account_type : set_value('account_type'));
                foreach ($account as $key => $value) {
                    ?>
                    <option <?php echo ($select == $value->account ? 'selected="selected"' : ''); ?> value="<?php echo $value->account; ?>"><?php echo $value->name; ?></option> 
    <?php } ?>
            </select>
        </div>
    </div>
<?php } ?>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Page Orientation'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input name="page" type="radio" <?php echo (isset($reportinfo) ? ($reportinfo->page == 'A4' ? 'checked="checked"' : '') : ''); ?> value="A4" class="radio-inline"/> Portrait     
        <input name="page" type="radio" <?php echo (isset($reportinfo) ? ($reportinfo->page == 'A4-L' ? 'checked="checked"' : '') : ''); ?> value="A4-L" class="radio-inline"/> Landscape     

    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo 'Save Report Information'; ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>
<script src="<?php echo base_url() ?>media/js/chosen.jquery.js"></script>
<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script type="text/javascript">
    (function() {
        function initScripts() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initScripts, 50);
                return;
            }
            
            // Load bootstrap-datepicker after jQuery is available
            if (typeof $.fn.datetimepicker === 'undefined') {
                var script = document.createElement('script');
                script.src = '<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
                script.onload = function() {
                    $(function() {
                        $('#datetimepicker').datetimepicker({
                            pickTime: false
                        });
                        $('#datetimepicker2').datetimepicker({
                            pickTime: false
                        });
                        var config = {
                            no_results_text: 'Oops, nothing found!'
                        }
                        $("#description").chosen(config);
                    });
                };
                document.head.appendChild(script);
            } else {
                $(function() {
                    $('#datetimepicker').datetimepicker({
                        pickTime: false
                    });
                    $('#datetimepicker2').datetimepicker({
                        pickTime: false
                    });
                    var config = {
                        no_results_text: 'Oops, nothing found!'
                    }
                    $("#description").chosen(config);
                });
            }
        }
        initScripts();
    })();
</script>
<script type="text/javascript">
    // Filter accounts by account type for link_cat = 2
    <?php if ($link_cat == 2) { ?>
    (function() {
        function initAccountFilter() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initAccountFilter, 50);
                return;
            }
            
            $(function() {
                var allOptions = $('#description option').clone(); // Store all options
                
                $('#account_type').on('change', function() {
                    var selectedType = $(this).val();
                    var currentValue = $('#description').val();
                    
                    // Clear and rebuild options
                    $('#description').empty();
                    $('#description').append('<option value=""><?php echo lang('select_default_text'); ?></option>');
                    
                    if (selectedType === '') {
                        // Show all accounts
                        allOptions.each(function() {
                            if ($(this).val() !== '') {
                                $('#description').append($(this).clone());
                            }
                        });
                    } else {
                        // Filter by account type
                        allOptions.each(function() {
                            if ($(this).val() !== '' && $(this).data('account-cat') == selectedType) {
                                $('#description').append($(this).clone());
                            }
                        });
                    }
                    
                    // Try to restore previous selection if still available
                    if ($('#description option[value="' + currentValue + '"]').length > 0) {
                        $('#description').val(currentValue);
                    }
                    
                    // Refresh chosen if it's initialized
                    if (typeof $('#description').chosen !== 'undefined') {
                        $('#description').trigger('chosen:updated');
                    }
                });
            });
        }
        initAccountFilter();
    })();
    <?php } ?>
</script>