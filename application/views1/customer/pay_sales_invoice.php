<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>media/css/choosen/chosen.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/customer/pay_sales_invoice/".$quoteid, 'class="form-horizontal"'); ?>

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
  $customer_info = $this->customer_model->customer_info(null, $transaction->customerid)->row();
?>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('customer_name'); ?>  : </label>

    <div class="col-lg-6">
        <input type="text" disabled="disabled" value="<?php echo $customer_info->name .' - '.$transaction->customerid; ?>"  class="form-control "/> 

    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('invoice_total'); ?>  : </label>

    <div class="col-lg-6">
        <input type="text" disabled="disabled" value="<?php echo ($transaction->totalamount + $transaction->totalamounttax) ?>"  class="form-control amountformat"/> 

    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('invoice_advance'); ?>  : </label>

    <div class="col-lg-6">
        <input type="text" disabled="disabled" value="<?php echo ($transaction->totalamount + $transaction->totalamounttax - $transaction->balance) ?>"  class="form-control amountformat"/> 

    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('invoice_amountdue'); ?>  : </label>

    <div class="col-lg-6">
        <input type="text" disabled="disabled" value="<?php echo ($transaction->balance) ?>"  class="form-control amountformat"/> 

    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('invoice_pay_in'); ?>  : <span class="required">*</span></label>

    <div class="col-lg-6">
        <select name="received_account" class="form-control">
            <option value=""> <?php echo lang('select_default_text'); ?></option>
            <?php
            $loop = $this->finance_model->account_cash_received();
            $selected = set_value('received_account');
            foreach ($loop as $key => $value) {
                ?>
                <option <?php echo ($selected ? ($selected == $value->account ? 'selected="selected"' : '') : ''); ?> value="<?php echo $value->account; ?>"> <?php echo $value->name; ?></option>
            <?php }
            ?>
        </select>
        <?php echo form_error('received_account'); ?>

    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('paydate'); ?>  : <span class="required">*</span></label>

    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker">
            <input type="text" name="paydate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('paydate'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>

        <?php echo form_error('paydate'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('amount'); ?>  : <span class="required">*</span></label>

    <div class="col-lg-6">
        <input type="text" name="amount" value="<?php echo set_value('amount'); ?>"  class="form-control amountformat"/> 
<?php echo form_error('amount'); ?>
    </div>
</div>




<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('customer_addbtn'); ?>" type="submit"/>
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

    });
</script>