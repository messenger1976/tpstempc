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
$html = '<table style="border-bottom:1px solid #000; width:100%;">
        <tr>
            <td style="width:300px;">
                <img src="' . base_url() . 'logo/' . company_info()->logo . '" style="width:250px; height:200px;"/>
            </td>
            <td style="width:1800px; text-align:center"><b>
               <div style="font-size:200px;">' . company_info()->name . '</div>
                P.O.Box' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '<br/>
       MEMBER CBU         
</b>
            </td>
        </tr>
    </table>';
$check = '';
$from1 = false;
$to1 = FALSE;
if ($from && $to) {
    $from1 = format_date($from);
    $to1 = format_date($to);
    if ($to1 > $from1) {
        $check = 'CBU FROM  ' . $from . ' UP TO ' . $to;
        $html.= '<h3>' . $check . '</h3>';
    }
}
$contribution = $this->report_model->contribution_report($grouping, $from1, $to1);
$si = 1;
if ($grouping == 1) {
    $html.='<table border="1"><tr><th style="width:130px;">S/No</th><th style="width:400px;">Member ID</th><th style="width:650px;">Name</th><th style="width:400px;">Amount</th></tr>';
    foreach ($contribution as $key => $value) {
        $memberinfo = $this->member_model->member_basic_info(null, $value->PID)->row();
        $html.='<tr>';
        $html.='<td style="text-align:center;">' . $si++ . '</td>';
        $html.='<td > ' . $memberinfo->member_id . '</td>';
        $html.='<td > ' . $memberinfo->firstname . ' ' . $memberinfo->middlename . ' ' . $memberinfo->lastname . '</td>';
        $html.='<td style="text-align:right;"> ' . number_format($value->CR, 2) . ' &nbsp; </td>';
        $html.= '</tr>';
    }

    $html.= '</table>';
} else {
    $html.='<table border="1"><tr><th style="width:130px;">S/No</th><th style="width:350px;">Member ID</th><th style="width:600px;">Name</th><th style="width:200px;">Trans Type</th><th style="width:300px;">Amount</th><th style="width:600px;">Trans date</th></tr>';
    $total = 0;
    foreach ($contribution as $key => $value) {
        $memberinfo = $this->member_model->member_basic_info(null, $value->PID)->row();

        $total += $value->amount;
        $html.='<tr>';
        $html.='<td style="text-align:center;">' . $si++ . '</td>';
        $html.='<td > ' . $value->member_id . '</td>';
        $html.='<td > ' . $memberinfo->firstname . ' ' . $memberinfo->middlename . ' ' . $memberinfo->lastname . '</td>';
        $html.='<td style="text-align:center;"> ' . $value->trans_type . ' </td>';

        $html.='<td style="text-align:right;"> ' . number_format($value->amount, 2) . ' </td>';

        $html.='<td style="text-align:center;"> ' . $value->createdon . '</td>';
        $html.= '</tr>';
    }

    $html.= '</table> <br/><strong>TOTAL : </strong>' . number_format($total, 2);
}




$this->pdf->writeHTML($html, true, false, false, false, '');

$this->pdf->Output('Member_List.pdf', 'I');
exit;
?>