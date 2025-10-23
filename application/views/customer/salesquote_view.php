
        <link href="<?php echo base_url(); ?>media/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>media/font-awesome/css/font-awesome.css" rel="stylesheet">

   
        <link href="<?php echo base_url(); ?>media/css/animate.css" rel="stylesheet">
        
  
<div class="row">
    <div class="col-xs-6">
        <h1>

        </h1>
    </div>
    <div class="col-xs-6 text-right">
        <h1>QUOTATION</h1>
        <h1><small>Quote #<?php echo $transaction->id; ?><br/>Issue Date : <?php echo format_date($transaction->issue_date,FALSE) ?></small></h1>
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
    $items = $this->db->get_where('sales_quote_item', array('quoteid' => $transaction->id))->result();
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
        <?php echo $transaction->notes; ?>
    </div>
</div>

<br/>
<br/>
<div style="text-align: center; border-top: 1px solid #ccc;">
    <br/>
    <a class="btn btn-info" href="<?php echo site_url(current_lang().'/customer/sendquote/'.$quoteid); ?>"><?php echo lang('customer_email'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
    <a class="btn btn-info" href="<?php echo site_url(current_lang().'/customer/print_sales_quote/'.  encode_id($transaction->id)) ?>"><?php echo lang('print'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
   <!-- <a class="btn btn-info"><?php echo lang('edit_btn'); ?></a> --> &nbsp; &nbsp; &nbsp; &nbsp;
    <?php if($transaction->copy_to_invoice == 0){ ?>
    <a class="btn btn-info" href="<?php echo site_url(current_lang().'/customer/copytonewinvoice/'.$quoteid); ?>"><?php echo lang('copytosalesinvoice'); ?></a> &nbsp; &nbsp; &nbsp; &nbsp;
    <?php } ?>
</div>
