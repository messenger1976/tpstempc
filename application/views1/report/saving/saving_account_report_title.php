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
                    ?>
                    <option <?php echo ($selected ? ($selected == $value->account ? 'selected="selected"' : '') : ''); ?> value="<?php echo $value->account; ?>"> <?php echo $value->account . ' :::: ' . $acc_name; ?></option>
                <?php }
                ?>
            </select>

            <?php echo form_error('description'); ?>
        </div>
    </div>

<?php } ?>

<?php
if ($link_cat == 1) {
    $account = $this->finance_model->saving_account_list()->result();
    ?>
    <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Saving Account Type'; ?>  :</label>
        <div class="col-lg-6">
            <select name="account_type" class="form-control">
                <option value="">All Saving Account Type</option>
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
<script src="<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
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
</script>