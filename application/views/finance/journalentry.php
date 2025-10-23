<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/finance/journalentry/", 'class="form-horizontal"'); ?>

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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('journalentry_date'); ?>  : <span class="required">*</span></label>

    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker">
            <input type="text" name="issue_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('issue_date'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>

        <?php echo form_error('issue_date'); ?>
    </div>
</div>


<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('journalentry_description'); ?> : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <textarea  name="description11"  class="form-control" ><?php echo set_value('journalentry_account_description') ?></textarea>

        <?php echo form_error('description11'); ?>
    </div>
</div>
<div class="table-responsive">
    <table id="quotetable" class="table table-bordered ">
        <thead>
            <tr>
                <th style="width: 140px;"><?php echo lang('journalentry_account'); ?></th>
                <th style="width: 150px;"><?php echo lang('journalentry_account_description'); ?></th>
                <th style="width: 10px;"><?php echo lang('journalentry_debit'); ?></th>
                <th style="width: 70px;"><?php echo lang('journalentry_credit'); ?></th>
            </tr>

        </thead>
        <tbody>





            <tr>

                <td>
                    <select class="form-control"  name="account[]">
                        <option miltone="" value=""><?php echo lang('select_default_text'); ?></option>
                        <?php foreach ($account_list as $key1 => $value1) { ?>
                            <optgroup label="<?php echo $value1['info']->name; ?>">
                                <?php foreach ($value1['data'] as $key => $value) {
                                    ?>
                                    <option value="<?php echo $value->account; ?>"><?php echo $value->name; ?></option>
                                <?php } ?>
                            </optgroup>
                        <?php } ?>
                    </select></td>
                <td><input type="text" name="description[]" class="form-control"/></td>
                <td><input onchange="debit_sum(this, 1)" onkeyup="debit_sum(this, 1)" type="text" name="debit[]" class="form-control amountformat debit"/></td>
                <td><input onchange="credit_sum(this, 1)" onkeyup="credit_sum(this, 1)" type="text" name="credit[]" class="form-control amountformat credit"/></td>

            </tr> 

            <tr>

                <td>
                    <select class="form-control"  name="account[]">
                        <option miltone="" value=""><?php echo lang('select_default_text'); ?></option>
                        <?php foreach ($account_list as $key1 => $value1) { ?>
                            <optgroup label="<?php echo $value1['info']->name; ?>">
                                <?php foreach ($value1['data'] as $key => $value) {
                                    ?>
                                    <option value="<?php echo $value->account; ?>"><?php echo $value->name; ?></option>
                                <?php } ?>
                            </optgroup>
                        <?php } ?>
                    </select></td>
                <td><input type="text" name="description[]" class="form-control"/></td>
                <td><input onchange="debit_sum(this, 2)" onkeyup="debit_sum(this, 2)" type="text" name="debit[]" class="form-control amountformat debit"/></td>
                <td><input onchange="credit_sum(this, 2)" onkeyup="credit_sum(this, 2)" type="text" name="credit[]" class="form-control amountformat credit"/></td>

               
            </tr> 


            <tr>
                <td ><button onclick="return false;"  class="btn btn-warning" id="addrow"><?php echo lang('add_row') ?></button></td>
                <td id="diff" style="color: red; text-align: right;"></td>
                <td>
                    <input type="text" disabled="disabled" id="open_debit"  class="form-control amountformat thisistotal_debit"/>
                    <input type="hidden" id="hidden_debit" name="summation_debit" class="form-control  thisistotal_debit"/>
                </td>
                <td>
                    <input type="text" disabled="disabled" id="open_credit"  class="form-control amountformat thisistotal_credit"/>
                    <input type="hidden" id="hidden_credit" name="summation_credit" class="form-control  thisistotal_credit"/>
                </td>
                
            </tr>
        


        </tbody>
    </table>
    
</div>



<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" id="submitdata" value="<?php echo lang('record_addbtn'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>

<script src="<?php echo base_url() ?>media/js/chosen.jquery.js"></script>
<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script src="<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">

                    var diff = 0;
                    function credit_sum(val, index) {
                        var sum = 0;
                        //iterate through each textboxes and add the values
                        $("input.credit").each(function() {
                            //add only if the value is number
                            var va = this.value.replace(/,/g, '');
                            this.value = va;
                            if (!isNaN(this.value) && this.value.length != 0) {
                                var taxrate = parseFloat($("#quotetable  tr:eq(" + index + ") td:eq(4) select option:selected").attr('taxrate'));
                                if (!isNaN(taxrate) && taxrate.length != 0) {
                                    sum += ((taxrate / 100) * va);
                                }
                                sum += parseFloat(this.value);
                            }
                        });


                        //.toFixed() method will roundoff the final sum to 2 decimal places
                        if (sum > 0) {
                            $("input.thisistotal_credit").val(sum.toFixed(2));

                        } else {
                            $("input.thisistotal_credit").val('');

                        }

                        if (!isNaN($(val).val()) && $(val).val().length != 0) {
                            $("#quotetable  tr:eq(" + index + ") td:eq(2) input").hide();
                        } else {
                            $("#quotetable  tr:eq(" + index + ") td:eq(2) input").show();
                        }
                        difference();
                    }

                    function debit_sum(val, index) {
                        var sum = 0;

                        //iterate through each textboxes and add the values
                        $("input.debit").each(function() {
                            //add only if the value is number
                            var va = this.value.replace(/,/g, '');
                            this.value = va;
                            if (!isNaN(this.value) && this.value.length != 0) {
                                var taxrate = parseFloat($("#quotetable  tr:eq(" + index + ") td:eq(4) select option:selected").attr('taxrate'));
                                if (!isNaN(taxrate) && taxrate.length != 0) {
                                    sum += ((taxrate / 100) * va);
                                }
                                sum += parseFloat(this.value);
                            }
                        });
                        //.toFixed() method will roundoff the final sum to 2 decimal places
                        if (sum > 0) {
                            $("input.thisistotal_debit").val(sum.toFixed(2));
                        } else {
                            $("input.thisistotal_debit").val('');
                        }

                        if (!isNaN($(val).val()) && $(val).val().length != 0) {
                            $("#quotetable  tr:eq(" + index + ") td:eq(3) input").hide();
                        } else {
                            $("#quotetable  tr:eq(" + index + ") td:eq(3) input").show();
                        }
                        difference();
                    }



                    function difference() {
                        var debit1 = $("#open_debit").val();
                        var credit1 = $("#open_credit").val();

                        var debit = 0;
                        var credit = 0;
                        if (!isNaN(debit1) && debit1.length != 0) {
                            debit = parseFloat(debit1);
                        }
                        if (!isNaN(credit1) && credit1.length != 0) {
                            credit = parseFloat(credit1);
                        }

                        var dif = credit - debit;

                        if (dif != 0) {
                            diff = dif;
                            $("#diff").html(diff);
                        } else {
                            diff = 0;
                            $("#diff").html(0);
                        }


                    }


                    $(function() {
                        $('#datetimepicker').datetimepicker({
                            pickTime: false
                        });




                        $("#submitdata").click(function() {
                            var debit1 = $("#open_debit").val();
                            var credit1 = $("#open_credit").val();
                            if (!isNaN(credit1) && credit1.length !== 0 && !isNaN(debit1) && credit1.length !== 0) {
                                if (diff == 0) {
                                    return  true;
                                }
                                alert('Journal not balanced');
                                return false;

                            }
                            alert('Please fill form first');
                            return false;
                        });
                        $("#addrow").click(function() {
                            var rowindex = ($('#quotetable  tbody tr').eq(-1).index() + 1);
                            var newRow = '<tr><td ><select class = "form-control"  name = "account[]" >';
                            newRow += '<option miltone = "" value = "" ><?php echo lang('select_default_text'); ?> </option><?php foreach ($account_list as $key1 => $value1) { ?><optgroup label = "<?php echo $value1['info']->name; ?>" >';
                                newRow += ' <?php foreach ($value1['data'] as $key => $value) { ?>            <option value = "<?php echo $value->account; ?>" ><?php echo $value->name; ?> </option> <?php } ?>';
                                newRow += ' </optgroup><?php } ?></select></td> <td> <input type = "text" name = "description[]" class = "form-control" /> </td> <td> <input onchange="debit_sum(this,' + rowindex + ')" onkeyup="debit_sum(this, ' + rowindex + ')" type = "text" name = "debit[]" class = "form-control amountformat debit" /> </td><td> <input onchange="credit_sum(this,' + rowindex + ')" onkeyup="credit_sum(this, ' + rowindex + ')" type = "text" name = "credit[]" class = "form-control amountformat credit" /> </td>';


                            newRow += '                                         </tr> ';


                            $('#quotetable tr:last').before(newRow);
                            return false;
                        });



                    });


</script>
