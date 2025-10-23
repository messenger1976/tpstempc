<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/customer/copytonewinvoice/" . $quoteid, 'class="form-horizontal"'); ?>

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
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('salesquote_date'); ?>  : <span class="required">*</span></label>

    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker">
            <input type="text" name="issue_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo (isset($quoteinfo) ? format_date($quoteinfo->issue_date, false) : set_value('issue_date')); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>

        <?php echo form_error('issue_date'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('due_date'); ?>  : <span class="required">*</span></label>

    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker2">
            <input type="text" name="due_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('due_date'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>

        <?php echo form_error('due_date'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Invoice Summary'; ?>  : </label>
    <div class="col-lg-6">
        <input type="text" name="summary" value="<?php echo (isset($quoteinfo) ? $quoteinfo->summary : set_value('summary')) ?>"  class="form-control"/> 
        <?php echo form_error('summary'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Invoice Note'; ?>  : </label>
    <div class="col-lg-6">
        <textarea class="form-control" name="notes"><?php echo (set_value('notes') ? set_value('notes'):  default_text_value('SALES_INVOICE_NOTE')); ?></textarea>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('create_invoice'); ?>" type="submit"/>
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