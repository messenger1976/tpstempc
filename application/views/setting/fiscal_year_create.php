<!-- Datepicker CSS -->
<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<?php echo form_open_multipart(current_lang() . "/setting/fiscal_year_create/".$id, 'class="form-horizontal"'); ?>

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
    <label class="col-lg-3 control-label"><?php echo lang('fiscal_year_name'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="name" value="<?php echo (isset($fiscal_year) ? $fiscal_year->name : set_value('name')); ?>" class="form-control" placeholder="e.g., FY 2024-2025"/>
        <?php echo form_error('name'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('fiscal_year_start_date'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker">
            <input type="text" name="start_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo (isset($fiscal_year) ? date('m/d/Y', strtotime($fiscal_year->start_date)) : set_value('start_date')); ?>" data-date-format="MM/DD/YYYY" class="form-control"/>
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
        </div>
        <?php echo form_error('start_date'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('fiscal_year_end_date'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker2">
            <input type="text" name="end_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo (isset($fiscal_year) ? date('m/d/Y', strtotime($fiscal_year->end_date)) : set_value('end_date')); ?>" data-date-format="MM/DD/YYYY" class="form-control"/>
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
        </div>
        <?php echo form_error('end_date'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('fiscal_year_save_btn'); ?>" type="submit"/>
        <?php echo anchor(current_lang() . '/setting/fiscal_year_list', lang('button_cancel'), 'class="btn btn-default"'); ?>
    </div>
</div>

<!-- jQuery and Datepicker JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<script>
(function() {
    // Ensure jQuery is available as $
    var $ = jQuery;

    function initDatePickers() {
        // Double-check jQuery is available
        if (typeof $ === 'undefined' || typeof $.fn === 'undefined') {
            console.warn('jQuery not available, retrying...');
            setTimeout(initDatePickers, 100);
            return;
        }

        try {
            // Initialize fiscal year date pickers using bootstrap-datepicker
            if ($('#datetimepicker').length > 0) {
                $('#datetimepicker').datepicker({
                    format: 'mm/dd/yyyy',
                    todayBtn: 'linked',
                    todayHighlight: true,
                    autoclose: true,
                    clearBtn: false,
                    orientation: 'bottom auto'
                });
                console.log('Start date picker initialized');
            }

            if ($('#datetimepicker2').length > 0) {
                $('#datetimepicker2').datepicker({
                    format: 'mm/dd/yyyy',
                    todayBtn: 'linked',
                    todayHighlight: true,
                    autoclose: true,
                    clearBtn: false,
                    orientation: 'bottom auto'
                });
                console.log('End date picker initialized');
            }

            console.log('✅ Fiscal year date pickers initialized successfully');

        } catch (error) {
            console.error('❌ Error initializing date pickers:', error);
            // Fallback: basic input without datepicker
            console.log('📝 Falling back to manual date input');
            $('#datetimepicker input, #datetimepicker2 input').attr('placeholder', 'MM/DD/YYYY');
        }
    }

    // Initialize on document ready
    $(document).ready(function() {
        console.log('Document ready, initializing date pickers...');
        initDatePickers();
    });

    // Fallback: try to initialize after a delay
    setTimeout(function() {
        if (!$('#datetimepicker').hasClass('hasDatepicker')) {
            console.log('Retrying date picker initialization...');
            initDatePickers();
        }
    }, 1000);

})();
</script>

<style>
/* Simple date picker styling consistent with the application */
.input-group.date .input-group-addon {
    cursor: pointer;
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-left: none;
}
.input-group.date .input-group-addon:hover {
    background-color: #e9ecef;
}
.input-group.date .form-control:focus + .input-group-addon,
.input-group.date .input-group-addon:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>

<?php echo form_close(); ?>
