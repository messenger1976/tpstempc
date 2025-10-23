<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>media/css/choosen/chosen.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/supplier/create_order/", 'class="form-horizontal"'); ?>

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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('purchaseorder_date'); ?>  : <span class="required">*</span></label>

    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker">
            <input type="text" name="issue_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo (isset($supplierinfo) ? format_date($supplierinfo->issue_date, false) : set_value('issue_date')); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>

        <?php echo form_error('issue_date'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('delivery_date'); ?>  : <span class="required">*</span></label>

    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker2">
            <input type="text" name="delivery_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo (isset($supplierinfo) ? format_date($supplierinfo->delivery_date, false) : set_value('issue_date')); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
            <span class="input-group-addon">
                <span class="fa fa-calendar "></span>
            </span>
        </div>

        <?php echo form_error('delivery_date'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('supplier_name'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <?php
        $selected = (isset($customerinfo) ? $customerinfo->name : set_value('customerid'));
        ?>
        <select id="customerid" name="supplierid" class="form-control">
            <option miltone="" value=""><?php echo lang('select_default_text'); ?></option>
            <?php foreach ($supplierlist as $key => $value) { ?>
                <option miltone="<?php echo $value->address; ?>" <?php echo ($selected == $value->supplierid ? 'selected="selected"' : '') ?> value="<?php echo $value->supplierid ?>"><?php echo $value->supplierid . ' : ' . $value->name; ?></option>
            <?php }
            ?>
        </select> 
        <?php echo form_error('supplierid'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('purchaseorder_address'); ?>  : </label>
    <div class="col-lg-6">
        <textarea class="form-control" name="address" id="address"><?php echo (isset($customerinfo) ? $customerinfo->address : set_value('address')) ?></textarea>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('purchaseorder_summary'); ?>  : </label>
    <div class="col-lg-6">
        <input type="text" name="summary" value="<?php echo (isset($customerinfo) ? $customerinfo->summary : set_value('summary')) ?>"  class="form-control"/> 
        <?php echo form_error('summary'); ?>
    </div>
</div>

<div>
    <div class="table-responsive">
        <table id="suppliertable" class="table table-bordered ">
            <thead>
                <tr>
                    <th style="width: 120px;"><?php echo lang('purchaseorder_item'); ?></th>
                    <th style="width: 140px;"><?php echo lang('purchaseorder_account'); ?></th>
                    <th style="width: 150px;"><?php echo lang('purchaseorder_description'); ?></th>
                    <th style="width: 10px;"><?php echo lang('purchaseorder_qty'); ?></th>
                    <th style="width: 70px;"><?php echo lang('purchaseorder_unitprice'); ?></th>
                    <th style="width: 100px;"><?php echo lang('purchaseorder_amount'); ?></th>
                    <th style="width: 90px;"><?php echo lang('purchaseorder_taxcode'); ?></th>
                </tr>

            </thead>
            <tbody>
              
                
                
                
                
                <tr>
                    <td>
                        <select class="form-control" onchange="fill_other(this, '1')"  id="item" name="item[]">
                            <option account="" desc="" price="" taxcode="" miltone=""  value=""><?php echo lang('select_default_text'); ?></option>

                            <?php foreach ($item_list as $key => $value) { ?>
                                <option account="<?php echo $value->account; ?>" desc="<?php echo $value->description; ?>" price="<?php echo $value->price; ?>" taxcode="<?php echo $value->taxcode; ?>"  value="<?php echo $value->code; ?>"><?php echo $value->name; ?></option>
                            <?php } ?>
                        </select>
                    </td>
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
                    <td><input onchange="onchangeInput(this, '1', '3')" onkeyup="onchangeInput(this, '1', '3')" type="text" name="qty[]" class="form-control"/></td>
                    <td><input onchange="onchangeInput(this, '1', '4')" onkeyup="onchangeInput(this, '1', '4')" type="text" name="price[]" class="form-control"/></td>

                    <td>
                        <input type="text" disabled="disabled"  class="form-control"/>
                        <input type="hidden"  name="total[]" class="form-control sumthisdata"/></td>
                    <td>
                        <select onchange="onchangeTaxCode(this, '1')" class="form-control"  name="taxcode[]" >
                            <option taxrate="" miltone="" value=""><?php echo lang('select_default_text'); ?></option>

                            <?php foreach ($taxcode_list as $key => $value) { ?>
                                <option taxrate="<?php echo $value->rate; ?>" value="<?php echo $value->code; ?>"><?php echo $value->code; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr> 



                <tr>
                    <td colspan="5"><button onclick="return false;"  class="btn btn-warning" id="addrow"><?php echo lang('add_row') ?></button></td>
                    <td>
                        <input type="text" disabled="disabled"  class="form-control thisistotal"/>
                        <input type="hidden" name="summation" class="form-control thisistotal"/>
                    </td>
                    <td >&nbsp;</td>
                </tr>


            </tbody>
        </table>
    </div>
</div>



<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('purchaseorder_notes'); ?>  : </label>
    <div class="col-lg-6">
        <textarea class="form-control" name="notes"><?php echo (set_value('notes') ? set_value('notes'):  ''); ?></textarea>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('purchaseorder_authosizedby'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="authosizedby" value="<?php echo (isset($customerinfo) ? $customerinfo->summary : set_value('summary')) ?>"  class="form-control"/> 
        <?php echo form_error('authosizedby'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('customer_addbtn'); ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>
<script src="<?php echo base_url() ?>media/js/chosen.jquery.js"></script>
<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script src="<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
                        var config = {
                            no_results_text: 'Oops, nothing found!'
                        }
                        $("#customerid").chosen(config);

                        function fill_other(val, index) {
                            // var row_object = $("#suppliertable  tr").eq(index);
                            var account = $(val).find("option:selected").attr('account');
                            var description = $(val).find("option:selected").attr('desc');
                            var price = $(val).find("option:selected").attr('price');
                            var taxcode = $(val).find("option:selected").attr('taxcode');


                            $("#suppliertable  tr:eq(" + index + ") td:eq(1) select").val(account);
                            $("#suppliertable  tr:eq(" + index + ") td:eq(2) input").val(description);
                            $("#suppliertable  tr:eq(" + index + ") td:eq(3) input").val(1);
                            $("#suppliertable  tr:eq(" + index + ") td:eq(4) input").val(price);
                            $("#suppliertable  tr:eq(" + index + ") td:eq(6) select").val(taxcode);
                            var taxrate = $("#suppliertable  tr:eq(" + index + ") td:eq(6) select option:selected").attr("taxrate");
                            $("#suppliertable  tr:eq(" + index + ") td:eq(5) input").val(rowAmount(1, price, taxrate));

                            grandtotal();
                        }

                        function rowAmount(qty, price, rate) {
                            var tmp = (qty * price);
                            var rate_amount = ((rate / 100) * tmp)
                            return parseFloat(tmp + rate_amount);

                        }

                        function onchangeTaxCode(val, index) {
                            var taxrate = $(val).find("option:selected").attr('taxrate');
                            var qty = $("#suppliertable  tr:eq(" + index + ") td:eq(3) input").val();
                            var price = $("#suppliertable  tr:eq(" + index + ") td:eq(4) input").val();
                            $("#suppliertable  tr:eq(" + index + ") td:eq(5) input").val(rowAmount(qty, price, taxrate));
                            grandtotal();
                        }

                        function onchangeInput(val, index, td) {
                            var taxrate = $("#suppliertable  tr:eq(" + index + ") td:eq(6) select option:selected").attr('taxrate');
                            if (td == 3) {
                                var qty = $(val).val();
                            } else {
                                var qty = $("#suppliertable  tr:eq(" + index + ") td:eq(3) input").val();
                            }
                            if (td == 4) {
                                var price = $(val).val();
                            } else {
                                var price = $("#suppliertable  tr:eq(" + index + ") td:eq(4) input").val();
                            }

                            $("#suppliertable  tr:eq(" + index + ") td:eq(5) input").val(rowAmount(qty, price, taxrate));
                            grandtotal();
                        }

                        function grandtotal() {
                            var total = 0;
                            $("#suppliertable  tr td input.sumthisdata").each(function() {
                                total += parseFloat($(this).val());
                            });
                            $("#suppliertable  tr:last td input").val(total);
                        }

                        $(function() {


                       


                            $('#datetimepicker2').datetimepicker({
                                pickTime: false
                            });

                            $('#datetimepicker').datetimepicker({
                                pickTime: false
                            });
                            $("#customerid").change(function() {
                                $("#address").val($("#customerid option:selected").attr('miltone'));
                            });
                            $("#addrow").click(function() {
                                /* var newRow = '<tr><td><select class="form-control"  name="item[]" >';
                                 newRow += '<option miltone="" value=""><?php echo lang('select_default_text'); ?></option>';
                                 newRow += '<?php foreach ($item_list as $key => $value) { ?>';
                                     newRow += '      <option value="<?php echo $value->code; ?>"><?php echo $value->name; ?></option>';
                                     newRow += '    <?php } ?>';
                                 newRow += ' </select>';
                                 newRow += '</td> </tr>';*/
                                var rowindex = ($('#suppliertable  tbody tr').eq(-1).index() + 1);

                                var newRow = '<tr><td><select class="form-control" onChange="fill_other(this,' + rowindex + ')"  name="item[]" ><option account="" desc="" price="" taxcode="" taxrate="" miltone="" value=""><?php echo lang('select_default_text'); ?></option>';
                                newRow += '<?php foreach ($item_list as $key => $value) { ?> <option account="<?php echo $value->account; ?>" desc="<?php echo $value->description; ?>" price="<?php echo $value->price; ?>" taxcode="<?php echo $value->taxcode; ?>"   value="<?php echo $value->code; ?>"><?php echo $value->name; ?></option>';
                                    newRow += '<?php } ?> </select></td><td ><select class = "form-control"  name = "account[]" >';
                                newRow += '<option miltone = "" value = "" ><?php echo lang('select_default_text'); ?> </option><?php foreach ($account_list as $key1 => $value1) { ?><optgroup label = "<?php echo $value1['info']->name; ?>" >';
                                    newRow += ' <?php foreach ($value1['data'] as $key => $value) { ?>            <option value = "<?php echo $value->account; ?>" ><?php echo $value->name; ?> </option> <?php } ?>';
                                    newRow += ' </optgroup><?php } ?></select></td> <td> <input type = "text" name = "description[]" class = "form-control" /> </td> <td> <input nchange="onchangeInput(this,' + rowindex + ',3)" onkeyup="onchangeInput(this, ' + rowindex + ',3)" type = "text" name = "qty[]" class = "form-control" /> </td><td> <input nchange="onchangeInput(this,' + rowindex + ',4)" onkeyup="onchangeInput(this, ' + rowindex + ',4)" type = "text" name = "price[]" class = "form-control" /> </td>';

                                newRow += '            <td>';
                                newRow += '               <input type = "text" disabled = "disabled"  class = "form-control" />';
                                newRow += '               <input type = "hidden" name = "total[]" class = "form-control sumthisdata" /> </td>';
                                newRow += '           <td>';
                                newRow += '               <select onchange="onchangeTaxCode(this, ' + rowindex + ')" class = "form-control"  name = "taxcode[]">';
                                newRow += '                                   <option taxrate="" miltone = "" value = ""><?php echo lang('select_default_text'); ?> </option>';
                                newRow += '';
                                newRow += ' <?php foreach ($taxcode_list as $key => $value) { ?>';
                                    newRow += '                                        <option taxrate="<?php echo $value->rate; ?>" value = "<?php echo $value->code; ?>"><?php echo $value->code; ?> </option>';
                                    newRow += ' <?php } ?>';
                                newRow += '                                   </select>';
                                newRow += '                                          </td>';
                                newRow += '                                         </tr> ';


                                $('#suppliertable tr:last').before(newRow);
                                return false;
                            });
                        });
</script>
