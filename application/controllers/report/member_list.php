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
      ' . lang('member_list_report') . '         
</b>
            </td>
        </tr>
    </table>';
$check = '';
$from1= false;
$to1 = FALSE;
if ($from && $to) {
    $from1 = format_date($from);
    $to1 = format_date($to);
    if ($to1 > $from1) {
        $check = 'MEMBER JOINING SINCE  ' . $from . ' UP TO ' . $to;
        $html.= '<h3>' . $check . '</h3>';
    }
}
$members = $this->report_model->member_list($from1, $to1);
$si = 1;
$html.='<table border="1"><tr><th style="width:130px;">S/No</th><th style="width:330px;">Member ID</th><th style="width:500px;">Name</th><th style="width:130px;">Gender</th><th style="width:230px;">Join Date</th><th style="width:330px;">Mobile</th><th style="width:500px;">Email</th></tr>';
foreach ($members as $key => $value) {
    $html.='<tr>';
    $html.='<td style="text-align:center;">' . $si++ . '</td>';
    $html.='<td > ' . $value->member_id . '</td>';
    $html.='<td > ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . '</td>';
    $html.='<td style="text-align:center;"> ' . $value->gender . '</td>';

    $html.='<td style="text-align:center;"> ' . format_date($value->joiningdate, false) . '</td>';
    $contact = $this->member_model->member_contact($value->PID);
    $html.='<td style="text-align:center;"> ' . $contact->phone1 . '</td>';
    $html.='<td style="text-align:center;"> ' . $contact->email . '</td>';
    $html.= '</tr>';
}

$html.= '</table>';





$this->pdf->writeHTML($html, true, false, false, false, '');

$this->pdf->Output('Member_List.pdf', 'I');
exit;
?>