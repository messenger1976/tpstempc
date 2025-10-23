<!-- Gritter -->
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/report_member/create_member_list_title/" . $link_cat . '/' . $id, 'class="form-horizontal"'); ?>
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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo ($link_cat == 1 ? 'Joined From' : 'FROM'); ?>  : <span class="required">*</span></label>
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


<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Description'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <textarea type="text" name="description" class="form-control"><?php echo (isset($reportinfo) ? $reportinfo->description : set_value('description')); ?> </textarea>
        <?php echo form_error('description'); ?>
    </div>
</div>

<?php if ($link_cat == 1) { ?>
    <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Columns'; ?>  : <span class="required">*</span></label>
        <div class="col-lg-6">
            <div style="font-weight: bold; border-bottom: 1px solid #ccc;">Basic Information</div>
            <?php
            $array_column = (isset($reportinfo) ? explode(',', $reportinfo->column):array());
            foreach ($column_list[0] as $key => $value) {
                ?>
            <input type="checkbox" <?php echo (in_array($key, $array_column) ? 'checked="checked"':''); ?> name="column[]" value="<?php echo $key ?>" class="checkbox-inline"/><?php echo $value; ?>
            <?php } ?>
            <div style="font-weight: bold; border-bottom: 1px solid #ccc; padding-top: 10px;">Contact Information</div>
            <?php
            foreach ($column_list[1] as $key => $value) {
                ?>
                <input type="checkbox" <?php echo (in_array($key, $array_column) ? 'checked="checked"':''); ?> name="column[]" value="<?php echo $key ?>" class="checkbox-inline"/><?php echo $value; ?>
            <?php } ?>
            <div style="font-weight: bold; border-bottom: 1px solid #ccc; padding-top: 10px;">Next of Kin Information</div>
            <?php
            foreach ($column_list[2] as $key => $value) {
                ?>
                <input type="checkbox" <?php echo (in_array($key, $array_column) ? 'checked="checked"':''); ?> name="column[]" value="<?php echo $key ?>" class="checkbox-inline"/><?php echo $value; ?>
            <?php } ?>

            <?php echo form_error('column[]'); ?>
        </div>
    </div>
<?php } ?>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Page Orientation'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input name="page" type="radio" <?php echo (isset($reportinfo) ? ($reportinfo->page == 'A4' ? 'checked="checked"':'') : ''); ?> value="A4" class="radio-inline"/> Portrait     
        <input name="page" type="radio" <?php echo (isset($reportinfo) ? ($reportinfo->page == 'A4-L' ? 'checked="checked"':'') : ''); ?> value="A4-L" class="radio-inline"/> Landscape     
        
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo 'Save Report Information'; ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script src="<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
    $(function() {
        $('#datetimepicker').datetimepicker({
            pickTime: false
        });
        $('#datetimepicker2').datetimepicker({
            pickTime: false
        });
    });
</script>