
<div class="row">
    <div class="col-xs-5">
        <h1>
            Purchase Invoice
        </h1>
        <div style="margin-left: 50px;">
            <table>
                <tr>
                    <td valign="top"><strong>To : &nbsp; &nbsp;</strong></td>
                    <td><?php
                        $customerinfo = $this->supplier_model->supplier_info(null, $transaction->supplierid)->row();
                        echo $customerinfo->name . ' - ' . $transaction->supplierid . '<br/>';
                        ?>
                        <?php echo $transaction->address; ?> <br></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-xs-7 text-right">
        <div style="text-align: left; display: inline-block; margin-right: 50px;">
            <br/>
            <br/>
            <table>
                <tr>
                    <td valign="top" style="border-right: 1px solid #000; padding-right: 10px;">
                        <strong>Issue Date</strong><br/>
                        <?php echo format_date($transaction->issue_date,FALSE) ?>
                        <br/>
                        <br/>
                        <strong>Due Date</strong><br/>
                        <?php echo format_date($transaction->due_date,FALSE) ?>
                         <br/>
                        <br/>
                        <strong>Invoice #</strong><br/>
                        <?php echo $transaction->id; ?>
                    </td>
                    <td valign="top" style="padding-left: 10px;">
                        <strong><?php echo company_info()->name; ?></strong><br/>
                        <?php echo 'P.O.BOX ' . company_info()->box ?><br/>
                        <?php echo company_info()->address; ?> <br/>
                        <?php echo 'Mobile :' . company_info()->mobile; ?> <br/>
                        <?php echo 'Email :' . company_info()->email; ?> <br/>

                    </td>
                </tr>
            </table>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-xs-8">
        <strong>
            <?php echo $transaction->summary; ?>
        </strong>
        <br/>
    </div>
</div>
<!-- / end client details section -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>
    <h4>Item</h4>
</th>
<th>
<h4>Description</h4>
</th>
<th>
<h4>Qty</h4>
</th>
<th>
<h4>Unit Price</h4>
</th>
<th>
<h4>Sub Total</h4>
</th>
</tr>
</thead>
<tbody>

    <?php
    $items = $this->db->get_where('purchase_invoice_item', array('invoiceid' => $transaction->id))->result();
    foreach ($items as $key => $value) {
        $iteminfo = $this->setting_model->item_info(null, $value->itemcode)->row();
        ?>
        <tr>
            <td><?php echo $iteminfo->name; ?></td>
            <td style="text-align: left;"><?php echo $value->description; ?></td>
            <td><?php echo $value->qty; ?></td>
            <td style="text-align: right;"><?php echo $value->unit_price; ?></td>
            <td style="text-align: right;"><?php echo ($value->qty * $value->unit_price); ?></td>
        </tr>  
    <?php }
    ?>
</tbody>
</table>
<div class="row text-right">
    <div class="col-xs-2 col-xs-offset-8">
        <p>
            <strong>
                Sub Total : <br>
                TAX : <br>
                Total : <br>
            </strong>
        </p>
    </div>
    <div class="col-xs-2">
        <strong>
            <?php echo ($transaction->totalamount) ?> <br>
            <?php echo $transaction->totalamounttax; ?> <br>
            <?php echo ($transaction->totalamount + $transaction->totalamounttax) ?> <br>
        </strong>
    </div>
</div>
<div class="row">
    <div class="col-xs-8">
        <strong>Internal Information</strong><br/>
        <?php echo $transaction->notes; ?>
    </div>
</div>

<br/>
<br/>
<div style="text-align: center; border-top: 1px solid #ccc;">
    <br/>
    <a class="btn btn-info" href="<?php echo site_url(current_lang() . '/supplier/send_purchase_invoice/' . $quoteid); ?>"><?php echo lang('customer_email'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
    <a class="btn btn-info" href="<?php echo site_url(current_lang() . '/supplier/print_sales_purchase_invoice/' . encode_id($transaction->id)) ?>"><?php echo lang('print'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
     <?php if($transaction->status == 0){ ?>
    <a class="btn btn-info" href="<?php echo site_url(current_lang().'/supplier/purchase_invoice_delete/'.encode_id($transaction->id)); ?>"><?php echo lang('button_delete'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
     <?php } ?>
     <a class="btn btn-info" href="<?php echo site_url(current_lang().'/supplier/spendmoney_purchase_invoice/'.$quoteid); ?>"><?php echo lang('purchase_spend_money'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
   
    
</div>
