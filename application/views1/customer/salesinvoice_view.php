
<div class="row">
    <div class="col-xs-6">
        <h1>

        </h1>
    </div>
    <div class="col-xs-6 text-right">
        <h1>INVOICE</h1>
        <h1><small>Invoice #<?php echo $transaction->id; ?><br/>Issue Date : <?php echo format_date($transaction->issue_date,FALSE) ?><br/>Due Date : <?php echo format_date($transaction->due_date,FALSE) ?></small></h1>
    </div>
</div>
<div class="row">
    <div class="col-xs-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>From: <?php echo company_info()->name; ?></h4>
            </div>
            <div class="panel-body">
                <p>
                    <?php echo 'P.O.BOX ' . company_info()->box ?><br/>
                    <?php echo company_info()->address; ?> <br/>
                    <?php echo 'Mobile :' . company_info()->mobile; ?> <br/>
                    <?php echo 'Email :' . company_info()->email; ?> <br/>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xs-5 col-xs-offset-2 text-right">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>To : <?php
                    $customerinfo = $this->customer_model->customer_info(null, $transaction->customerid)->row();
                    echo $customerinfo->name;
                    ?></h4>
            </div>
            <div class="panel-body">
                <p>
                    Customer ID : <?php echo $transaction->customerid; ?> <br/>
                    Address : <?php echo $transaction->address; ?> <br>

                </p>
            </div>
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
    $items = $this->db->get_where('sales_invoice_item', array('invoiceid' => $transaction->id))->result();
    foreach ($items as $key => $value) {
        $iteminfo = $this->setting_model->item_info(null, $value->itemcode)->row();
        ?>
        <tr>
            <td><?php echo $iteminfo->name; ?></td>
            <td style="text-align: left;"><?php echo $value->description; ?></td>
            <td><?php echo number_format($value->qty,2); ?></td>
            <td style="text-align: right;"><?php echo number_format($value->unit_price,2); ?></td>
            <td style="text-align: right;"><?php echo number_format(($value->qty * $value->unit_price),2); ?></td>
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
                Amount Received : <br>
                Amount due : <br>
            </strong>
        </p>
    </div>
    <div class="col-xs-2">
        <strong>
            <?php echo number_format(($transaction->totalamount),2) ?> <br>
            <?php echo number_format($transaction->totalamounttax,2); ?> <br>
            <?php echo number_format(($transaction->totalamount + $transaction->totalamounttax),2) ?> <br>
            <?php echo number_format(($transaction->totalamount + $transaction->totalamounttax-$transaction->balance),2) ?> <br>
            <?php echo number_format($transaction->balance,2) ?> <br>
        </strong>
    </div>
</div>
<div class="row">
    <div class="col-xs-8">
        <?php echo $transaction->notes; ?>
    </div>
</div>

<br/>
<br/>
<div style="text-align: center; border-top: 1px solid #ccc;">
    <br/>
    <a class="btn btn-info" href="<?php echo site_url(current_lang().'/customer/sendsalesinvoice/'.$quoteid); ?>"><?php echo lang('customer_email'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
    <a class="btn btn-info" href="<?php echo site_url(current_lang().'/customer/print_sales_invoice/'.  encode_id($transaction->id)) ?>"><?php echo lang('print'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
    <?php if($transaction->status == 0){ ?>
    <a class="btn btn-info" href="<?php echo site_url(current_lang().'/customer/sales_invoice_delete/'.  encode_id($transaction->id)) ?>"><?php echo lang('button_delete'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
    <?php } ?>
    <a class="btn btn-info" href="<?php echo site_url(current_lang().'/customer/pay_sales_invoice/'.$quoteid); ?>"><?php echo lang('customer_pay_invoice'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
</div>
