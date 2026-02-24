<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a class="btn btn-primary" href="<?php echo site_url(current_lang() . '/report/cash_flow_report'); ?>"><?php echo 'New Report'; ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('index_action_th'); ?></th>
                <th><?php echo 'From'; ?></th>
                <th><?php echo 'Until'; ?></th>
                <th><?php echo 'Description'; ?></th>
                <th><?php echo 'Page Orientation'; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reportlist)): ?>
                <?php foreach ($reportlist as $key => $value) { ?>
                    <tr>
                        <td style="width: 350px;">
                            <?php echo anchor(current_lang() . "/report/cash_flow_report/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?>   &nbsp; | &nbsp;
                            <?php echo anchor(current_lang() . "/report/delete_cash_flow_report/" . encode_id($value->id), ' <i class="fa fa-times"></i> ' . lang('button_delete')); ?>   &nbsp; | &nbsp;
                            <?php echo anchor(current_lang() . "/report/cash_flow_report_view/" . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view')); ?>   &nbsp; | &nbsp;
                            <?php echo anchor(current_lang() . "/report/cash_flow_report_print/" . encode_id($value->id), ' <i class="fa fa-print"></i> ' . 'Print'); ?>
                        </td>
                        <td><?php echo format_date($value->fromdate, false); ?></td>
                        <td><?php echo format_date($value->todate, false); ?></td>
                        <td><?php echo $value->description; ?></td>
                        <td><?php echo ($value->page == 'A4' ? 'Portrait' : 'Landscape'); ?></td>
                    </tr>
                <?php } ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No cash flow reports found. <a href="<?php echo site_url(current_lang() . '/report/cash_flow_report'); ?>">Create a new one</a></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($id) && !empty($id)): ?>
    <!-- Show form for editing -->
    <?php echo form_open_multipart(current_lang() . "/report/cash_flow_report/" . $id, 'class="form-horizontal"'); ?>
<?php else: ?>
    <!-- Show form for creating new -->
    <?php echo form_open_multipart(current_lang() . "/report/cash_flow_report", 'class="form-horizontal"'); ?>
<?php endif; ?>

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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'From'; ?> : <span class="required">*</span></label>
    <div class=" col-lg-6">
        <div class="input-group date" id="datetimepicker">
            <input type="text" name="fromdate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo (isset($reportinfo) ? format_date($reportinfo->fromdate, false) : set_value('fromdate')); ?>" data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>
        <?php echo form_error('fromdate'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Until'; ?> : <span class="required">*</span></label>
    <div class=" col-lg-6">
        <div class="input-group date" id="datetimepicker2">
            <input type="text" name="todate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo (isset($reportinfo) ? format_date($reportinfo->todate, false) : set_value('todate')); ?>" data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>
        <?php echo form_error('todate'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Description'; ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <textarea type="text" name="description" class="form-control"><?php echo (isset($reportinfo) ? $reportinfo->description : set_value('description')); ?></textarea>
        <?php echo form_error('description'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Page Orientation'; ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input name="page" type="radio" <?php echo (isset($reportinfo) ? ($reportinfo->page == 'A4' ? 'checked="checked"' : '') : 'checked'); ?> value="A4" class="radio-inline"/> Portrait     
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

<!-- Gritter -->
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
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
                    $(function () {
                        $('#datetimepicker').datetimepicker({
                            pickTime: false
                        });
                        $('#datetimepicker2').datetimepicker({
                            pickTime: false
                        });
                    });
                };
                document.head.appendChild(script);
            } else {
                $(function () {
                    $('#datetimepicker').datetimepicker({
                        pickTime: false
                    });
                    $('#datetimepicker2').datetimepicker({
                        pickTime: false
                    });
                });
            }
        }
        initScripts();
    })();
</script>
