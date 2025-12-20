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
        <div class="input-group date">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="text" name="start_date" value="<?php echo (isset($fiscal_year) ? date('m/d/Y', strtotime($fiscal_year->start_date)) : set_value('start_date')); ?>" class="form-control" readonly/>
        </div>
        <?php echo form_error('start_date'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('fiscal_year_end_date'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <div class="input-group date">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="text" name="end_date" value="<?php echo (isset($fiscal_year) ? date('m/d/Y', strtotime($fiscal_year->end_date)) : set_value('end_date')); ?>" class="form-control" readonly/>
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

<!-- Datepicker JS -->
<script src="<?php echo base_url(); ?>assets/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<script>
$(document).ready(function() {
    // Initialize datepickers
    $('.input-group.date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: 'mm/dd/yyyy'
    });

    // Optional: Set default dates for new fiscal years
    <?php if (!isset($fiscal_year)): ?>
        // Set start date to beginning of current year
        var currentYear = new Date().getFullYear();
        $('input[name="start_date"]').datepicker('setDate', new Date(currentYear, 0, 1)); // January 1st
        $('input[name="end_date"]').datepicker('setDate', new Date(currentYear, 11, 31)); // December 31st
    <?php endif; ?>
});
</script>

<?php echo form_close(); ?>
