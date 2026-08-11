<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">

<?php
$form_id = !empty($id) ? $id : '';
echo form_open_multipart(current_lang() . '/report/create_cash_flow_report' . ($form_id !== '' ? '/' . $form_id : ''), 'class="form-horizontal"');

if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}

$from_val = set_value('fromdate', isset($reportinfo) ? format_date($reportinfo->fromdate, false) : '');
$to_val = set_value('todate', isset($reportinfo) ? format_date($reportinfo->todate, false) : '');
$desc_val = set_value('description', isset($reportinfo) ? $reportinfo->description : '');
$page_val = set_value('page', isset($reportinfo) && !empty($reportinfo->page) ? $reportinfo->page : 'A4');
?>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo 'From'; ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker">
            <input type="text" name="fromdate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo htmlspecialchars($from_val); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
        </div>
        <?php echo form_error('fromdate'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo 'Until'; ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker2">
            <input type="text" name="todate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo htmlspecialchars($to_val); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
        </div>
        <?php echo form_error('todate'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo 'Description'; ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <textarea name="description" class="form-control"><?php echo htmlspecialchars($desc_val); ?></textarea>
        <?php echo form_error('description'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo 'Page Orientation'; ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input name="page" type="radio" value="A4" class="radio-inline" <?php echo ($page_val == 'A4' ? 'checked="checked"' : ''); ?>/> Portrait
        <input name="page" type="radio" value="A4-L" class="radio-inline" <?php echo ($page_val == 'A4-L' ? 'checked="checked"' : ''); ?>/> Landscape
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo 'Save Report Information'; ?>" type="submit"/>
        &nbsp;
        <a class="btn btn-default" href="<?php echo site_url(current_lang() . '/report/cash_flow_report'); ?>">Cancel</a>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url(); ?>media/js/script/moment.js"></script>
<script type="text/javascript">
(function () {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }

        function bindPickers() {
            $('#datetimepicker').datetimepicker({ pickTime: false });
            $('#datetimepicker2').datetimepicker({ pickTime: false });
        }

        if (typeof $.fn.datetimepicker === 'undefined') {
            var script = document.createElement('script');
            script.src = '<?php echo base_url(); ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
            script.onload = function () {
                $(bindPickers);
            };
            document.head.appendChild(script);
        } else {
            $(bindPickers);
        }
    }
    initScripts();
})();
</script>
