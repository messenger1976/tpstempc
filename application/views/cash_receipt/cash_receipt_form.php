<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<?php echo form_open_multipart(current_lang() . "/cash_receipt/cash_receipt_create/", 'class="form-horizontal" id="cashReceiptForm"'); ?>

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

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo lang('cash_receipt_create'); ?></h5>
                    <div class="ibox-tools">
                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_list'); ?>" class="btn btn-white btn-xs">
                            <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    
                    <!-- Receipt Header Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_no'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" name="receipt_no" value="<?php echo set_value('receipt_no', $next_receipt_no); ?>" class="form-control" required/>
                                    <?php echo form_error('receipt_no'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_date'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <div class="input-group date" id="datetimepicker">
                                        <input type="text" name="receipt_date" placeholder="<?php echo lang('hint_date'); ?>" 
                                               value="<?php echo set_value('receipt_date', date('d-m-Y')); ?>" 
                                               data-date-format="DD-MM-YYYY" class="form-control" required/> 
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar"></span>
                                        </span>
                                    </div>
                                    <?php echo form_error('receipt_date'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_received_from'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" name="received_from" value="<?php echo set_value('received_from'); ?>" class="form-control" required/>
                                    <?php echo form_error('received_from'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_payment_method'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                        <option value=""><?php echo lang('select_default_text'); ?></option>
                                        <?php foreach ($payment_methods as $key => $method): ?>
                                            <option value="<?php echo $key; ?>" <?php echo set_select('payment_method', $key); ?>><?php echo $method; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php echo form_error('payment_method'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="cheque_details" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_cheque_no'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="cheque_no" value="<?php echo set_value('cheque_no'); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_bank_name'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="bank_name" value="<?php echo set_value('bank_name'); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="col-lg-2 control-label"><?php echo lang('cash_receipt_description'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-10">
                                    <textarea name="description" class="form-control" rows="3" required><?php echo set_value('description'); ?></textarea>
                                    <?php echo form_error('description'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <!-- Line Items Table -->
                    <h4><?php echo lang('cash_receipt_line_items'); ?></h4>
                    <div class="table-responsive">
                        <table id="lineItemsTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 40%;"><?php echo lang('cash_receipt_account'); ?> <span class="required">*</span></th>
                                    <th style="width: 40%;"><?php echo lang('cash_receipt_line_description'); ?></th>
                                    <th style="width: 15%;"><?php echo lang('cash_receipt_amount'); ?> <span class="required">*</span></th>
                                    <th style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="line-item">
                                    <td>
                                        <select class="form-control account-select" name="account[]" required>
                                            <option value=""><?php echo lang('select_default_text'); ?></option>
                                            <?php foreach ($account_list as $key1 => $value1) { ?>
                                                <optgroup label="<?php echo $value1['info']->name; ?>">
                                                    <?php foreach ($value1['data'] as $key => $value) { ?>
                                                        <option value="<?php echo $value->account; ?>"><?php echo $value->name; ?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="line_description[]" class="form-control"/>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="amount[]" class="form-control amount-input" required/>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-xs remove-line" disabled>
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                                    <td>
                                        <input type="text" id="total_amount" class="form-control" readonly value="0.00"/>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="button" class="btn btn-primary" id="addLineItem">
                            <i class="fa fa-plus"></i> <?php echo lang('add_row'); ?>
                        </button>
                    </div>

                    <hr/>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo lang('save'); ?>
                            </button>
                            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_list'); ?>" class="btn btn-white">
                                <?php echo lang('cancel'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<!-- Date Picker -->
<script src="<?php echo base_url(); ?>media/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<script>
$(document).ready(function(){
    
    // Initialize date picker
    $('#datetimepicker').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: 'dd-mm-yyyy'
    });

    // Show/hide cheque details based on payment method
    $('#payment_method').change(function(){
        if($(this).val() == 'Cheque'){
            $('#cheque_details').show();
        } else {
            $('#cheque_details').hide();
        }
    });

    // Add line item
    $('#addLineItem').click(function(){
        var newRow = $('.line-item:first').clone();
        newRow.find('input, select').val('');
        newRow.find('.remove-line').prop('disabled', false);
        $('#lineItemsTable tbody').append(newRow);
        calculateTotal();
    });

    // Remove line item
    $(document).on('click', '.remove-line', function(){
        $(this).closest('tr').remove();
        calculateTotal();
    });

    // Calculate total when amount changes
    $(document).on('keyup change', '.amount-input', function(){
        calculateTotal();
    });

    // Calculate total function
    function calculateTotal(){
        var total = 0;
        $('.amount-input').each(function(){
            var amount = parseFloat($(this).val()) || 0;
            total += amount;
        });
        $('#total_amount').val(total.toFixed(2));
    }

    // Form validation
    $('#cashReceiptForm').submit(function(e){
        var hasItems = false;
        $('.amount-input').each(function(){
            if(parseFloat($(this).val()) > 0){
                hasItems = true;
            }
        });

        if(!hasItems){
            alert('<?php echo lang('cash_receipt_no_items'); ?>');
            e.preventDefault();
            return false;
        }

        return true;
    });
});
</script>
