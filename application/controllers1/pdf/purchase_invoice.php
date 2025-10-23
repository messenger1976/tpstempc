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

$html = ' <h1 style="font-size:300px;">Purchase Invoice</h1><br/>';
$html .= '<table width="100%" ><tr><td width="40%" valign="top">';
$html .='<table width="100%" >
                <tr>
                    <td valign="top" width="10%"><strong>To : &nbsp; &nbsp;</strong></td>
                    <td valign="top" width="90%" >';
$customerinfo = $this->supplier_model->supplier_info(null, $transaction->supplierid)->row();
$html.= $customerinfo->name . ' - ' . $transaction->supplierid . '<br/>
                       ' . $transaction->address . '<br></td>
                </tr>
            </table>';
$html .= '</td><td width="60%">';
$html .=' <table width="100%">
                <tr>
                    <td width="30%" valign="top" style="border-right: 1px solid #000;"><table width="90%"><tr><td style="text-align:right;">
                        <strong>Issue Date</strong><br/>
                        ' . format_date($transaction->issue_date, FALSE) . '
                        <br/>
                        <br/>
                        <strong>Delivery Date</strong><br/>
                        ' . format_date($transaction->due_date, FALSE) . '
                         <br/>
                        <br/>
                        <strong>Invoice #</strong><br/>
                        ' . $transaction->id . '</td></tr></table>
                    </td>
                    <td width="70%" valign="top" style="padding-left: 10px; text-align:right">&nbsp; &nbsp; <table  width="100%"><tr><td style="text-align:left;"><strong>' . company_info()->name . '</strong><br/>
                        P.O.BOX ' . company_info()->box . '<br/>
                        ' . company_info()->address . ' <br/>
                        Mobile :' . company_info()->mobile . ' <br/>
                        Email :' . company_info()->email . '<br/>
</td></tr></table>
                    </td>
                </tr>
            </table>';

$html.= '</td></tr></table>';



$html.='<div>&nbsp;</div>' . $transaction->summary . '<br/><br/>
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
$items = $this->db->get_where('purchase_invoice_item', array('invoiceid' => $transaction->id))->result();
foreach ($items as $key => $value) {
    $iteminfo = $this->setting_model->item_info(null, $value->itemcode)->row();

    $html.=' <tr>
            <td border="1"> ' . $iteminfo->name . '</td>
            <td border="1" style="text-align: left;"> ' . $value->description . '</td>
            <td border="1" style="text-align: center; padding-top:10px;">' . $value->qty . '</td>
            <td border="1" style="text-align: right;">' . $value->unit_price . '&nbsp; &nbsp; </td>
            <td border="1" style="text-align: right;">' . ($value->qty * $value->unit_price) . '&nbsp; &nbsp; </td>
        </tr>  ';
}

$html.='
    <tr><td  colspan="4" style="text-align:right;"><strong>Sub Total : </strong></td><td style="text-align:right;"><strong>' . ($transaction->totalamount) . '&nbsp; &nbsp;</strong></td></tr>
    <tr><td  colspan="4" style="text-align:right;"><strong>Tax : </strong></td><td style="text-align:right;"><strong>' . ($transaction->totalamounttax) . '&nbsp; &nbsp;</strong></td></tr>
    <tr><td  colspan="4" style="text-align:right;"><strong>Total : </strong></td><td style="text-align:right;"><strong>' . ($transaction->totalamount + $transaction->totalamounttax) . '&nbsp; &nbsp;</strong></td></tr>
</table>
<br/><strong>Internal Information</strong><br/>' . $transaction->notes ;



$this->pdf->writeHTML($html, true, false, false, false, '');

$this->pdf->Output('purchase_invoice_' . $quoteid . '.pdf', 'I');
exit;
?>