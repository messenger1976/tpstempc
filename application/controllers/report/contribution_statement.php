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

$this->pdf->SetFont('times', '', 9);
$html = '<table style="border-bottom:1px solid #000; width:100%;">
        <tr>
            <td style="width:300px;">
                <img src="' . base_url() . 'logo/' . company_info()->logo . '" style="width:250px; height:200px;"/>
            </td>
            <td style="width:1800px; text-align:center"><b>
               <div style="font-size:200px;">' . company_info()->name . '</div>
                P.O.Box' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '<br/>
       MEMBER CBU STATEMENT         
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
    } else {
        $html.='<br/>';
    }
} else {
    $html.='<br/>';
}
$memberinfo = $this->member_model->member_basic_info(null, $PID)->row();
$html.='  <table>
                <tr>
                    <td style="width:300px;"><img  style="width:200px; height: 200px;" src="' . base_url() . 'uploads/memberphoto/' . $memberinfo->photo . '"/></td>
                    <td valign="top"><div style="padding-left: 30px;"> <strong>' . lang('member_firstname') . ' : </strong> ' . $memberinfo->firstname . '<br/>';
$html.=' <strong>' . lang('member_middlename') . ': </strong> ' . $memberinfo->middlename . '<br/>';
$html.='<strong>' . lang('member_lastname') . ' : </strong> ' . $memberinfo->lastname . '<br/>';
$html.='<strong>' . lang('member_gender') . ' : </strong> ' . $memberinfo->gender . '<br/>';
$html.='<strong>' . lang('member_dob') . ' : </strong> ' . format_date($memberinfo->dob, FALSE) . '<br/>';
$html.='</div></td>
<td valign="top"><div style="padding-left: 40px;">
        <strong>' . lang('member_pid') . ' : </strong> ' . $memberinfo->PID . '<br/>';
$html.=' <strong>' . lang('member_member_id') . ' : </strong> ' . $memberinfo->member_id . '<br/>';
$html.='  <strong>' . lang('member_join_date') . ' : </strong> ';
$html.= format_date($memberinfo->joiningdate, FALSE) . '<br/>';
$html.='   </div></td>
 </tr>
            </table><br/>';
 $contribution = $this->report_model->contribution_statement($PID, $from1, $to1);
 
 
  $html.='<table border="1"><tr><th style="width:130px;">S/No</th><th style="width:400px;">Trans Date</th><th style="width:600px;">Comment</th><th style="width:300px;">DR</th><th style="width:300px;">CR</th><th style="width:300px;">Balance</th></tr>';
  $html.='<tr>';
  $html.='<td style="text-align:center;"></td>';
  $html.='<td > </td>';
  $html.='<td > B/F</td>';
  $html.='<td style="text-align:right;"></td>';
  $html.='<td style="text-align:right;"> &nbsp; </td>';
  $html.='<td style="text-align:right;"> ';
  if(count($contribution) > 0){
      $balance = $contribution[0]->previous_balance;
      $html.= number_format($balance,2);
      
  }
  $html.=' &nbsp; </td>';
  $html.= '</tr>';
   $si = 1;
  foreach ($contribution as $key => $value) {
      if($value->trans_type == 'DR'){$balance -= $value->amount; }else{ $balance += $value->amount; }
  $html.='<tr>';
  $html.='<td style="text-align:center;">' . $si++ . '</td>';
  $html.='<td > ' . $value->createdon . '</td>';
  $html.='<td > ' . $value->system_comment . '  -->>  ' . $value->comment . ' </td>';
  $html.='<td style="text-align:right;"> '.($value->trans_type == 'DR' ? number_format($value->amount,2):'').' &nbsp; </td>';
  $html.='<td style="text-align:right;"> '.($value->trans_type == 'CR' ? number_format($value->amount,2):'') .'&nbsp; </td>';
  $html.='<td style="text-align:right;"> '.  number_format($balance,2).'&nbsp; </td>';
  $html.= '</tr>';
  }

  $html.= '</table>';
  


$this->pdf->writeHTML($html, true, false, false, false, '');

$this->pdf->Output('Contribution_statement.pdf', 'I');
exit;
?>