<?php

$this->load->library('pdf');
$this->pdf->set_subtitle('');
$this->pdf->hidefooter(FALSE);
$this->pdf->start_pdf(FALSE);
$this->pdf->SetSubject('miltone');
$this->pdf->SetKeywords('miltone');
$this->pdf->AddPage();
$initial_page = $this->pdf->getNumPages();
$y = $this->pdf->SetY(0);
$y = $this->pdf->SetY(10);

$this->pdf->SetFont('times', '', 10);

$html = '   
<table><tr><td></td><td align="right">  <h1>INVOICE</h1>
        <h1><small>Invoice #' . $transaction->id . '<br/>Issue Date : '.format_date($transaction->issue_date,false).'<br/>Due Date : '.format_date($transaction->due_date,false).'</small></h1>
          </td>
      </tr></table>

<table><tr><td width="70%"> <h4>From: ' . company_info()->name . '</h4>
            
                    P.O.BOX ' . company_info()->box . '<br/>
                    ' . company_info()->address . ' <br/>
                    Mobile :' . company_info()->mobile . ' <br/>
                    Email :' . company_info()->email . ' <br/>
               </td>
               <td>
                <h4>To : ';
$customerinfo = $this->customer_model->customer_info(null, $transaction->customerid)->row();
$html.=$customerinfo->name . '
                    </h4>
            
                    Customer ID : ' . $transaction->customerid . ' <br/>
                    Address : ' . $transaction->address . ' <br>

                </td></tr></table>
        <strong>
            ' . $transaction->summary . '
        </strong>
   <br/>
   <br/>
   
<table  cellpadding="0" cellspacing="0">
<tr>
<th border="1">
<h4> Item</h4>
</th>
<th border="1">
<h4> Description</h4>
</th>
<th border="1" style="text-align:center;">
<h4> Qty</h4>
</th>
<th border="1">
<h4> Unit Price</h4>
</th>
<th border="1">
<h4> Sub Total</h4>
</th>
</tr>';
$items = $this->db->get_where('sales_invoice_item', array('invoiceid' => $transaction->id))->result();
foreach ($items as $key => $value) {
    $iteminfo = $this->setting_model->item_info(null, $value->itemcode)->row();

    $html.=' <tr>
            <td border="1"> ' . $iteminfo->name . '</td>
            <td border="1" style="text-align: left;"> ' . $value->description . '</td>
            <td border="1" style="text-align: center; padding-top:10px;">' . number_format($value->qty,2) . '</td>
            <td border="1" style="text-align: right;">' . number_format($value->unit_price,2) . '&nbsp; &nbsp; </td>
            <td border="1" style="text-align: right;">' . number_format(($value->qty * $value->unit_price),2) . '&nbsp; &nbsp; </td>
        </tr>  ';
}

$html.='
    <tr><td  colspan="4" style="text-align:right;"><strong>Sub Total : </strong></td><td border="1" style="text-align:right;"><strong>'.number_format($transaction->totalamount,2).'&nbsp; &nbsp;</strong></td></tr>
    <tr><td  colspan="4" style="text-align:right;"><strong>Tax : </strong></td><td border="1" style="text-align:right;"><strong>'.number_format($transaction->totalamounttax,2).'&nbsp; &nbsp;</strong></td></tr>
    <tr><td  colspan="4" style="text-align:right;"><strong>Total : </strong></td><td border="1" style="text-align:right;"><strong>'.number_format(($transaction->totalamount+$transaction->totalamounttax),2).'&nbsp; &nbsp;</strong></td></tr>
    <tr><td  colspan="4" style="text-align:right;"><strong>Amount Received : </strong></td><td border="1" style="text-align:right;"><strong>'.number_format(($transaction->totalamount+$transaction->totalamounttax-$transaction->balance),2).'&nbsp; &nbsp;</strong></td></tr>
    <tr><td  colspan="4" style="text-align:right;"><strong>Amount due : </strong></td><td  border="1" style="text-align:right;"><strong>'.number_format($transaction->balance,2).'&nbsp; &nbsp;</strong></td></tr>
</table>
<br/>' . $transaction->notes ;



$this->pdf->writeHTML($html, true, false, false, false, '');
$path_file = './uploads/salesinvoice/'; 
$this->pdf->Output($path_file.'invoice_'.$quoteid.'.pdf', 'F');
?>