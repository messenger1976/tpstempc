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

$html = ' <table style="border-bottom:1px solid #000; width:100%;">
        <tr>
            <td style="width:300px;">
                <img src="' . base_url() . 'logo/' . company_info()->logo . '" style="width:250px; height:200px;"/>
            </td>
            <td style="width:1800px; text-align:center"><b>
               <div style="font-size:200px;">' . company_info()->name . '</div>
                P.O.Box' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '<br/>
      ' . strtoupper(lang('repayment_schedule')) . '         
</b>
            </td>
        </tr>
    </table><br/>
   ';

$memberinfo = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();

$html.='  <div style="border-bottom:1px solid #000; font-size:150px; font-weight:bold;">Basic Informations</div> <br/><table>
                <tr>
                    <td><img  style="width: 300px; height: 300px;" src="'.base_url().'uploads/memberphoto/'.$memberinfo->photo.'"/></td>
                    <td valign="top"><div style="padding-left: 30px;">
                            <strong>'.lang('member_firstname') .' : </strong> '.$memberinfo->firstname .'<br/>
                            <strong>'.lang('member_middlename') .' : </strong> '.$memberinfo->middlename .'<br/>
                            <strong>'.lang('member_lastname') .' : </strong> '.$memberinfo->lastname.'<br/>
                            <strong>'.lang('member_gender') .' : </strong> '.$memberinfo->gender .'<br/>
                            <strong>'.lang('member_dob') .' : </strong> '.format_date($memberinfo->dob, FALSE) .'<br/>
                        </div></td>
                    <td valign="top"><div style="padding-left: 100px;">
                            <strong>'.lang('member_pid') .' : </strong> '.$memberinfo->PID .'<br/>
                            <strong>'.lang('member_member_id') .' : </strong> '.$memberinfo->member_id .'<br/>
                            <strong>'.lang('member_join_date') .' : </strong> '.format_date($memberinfo->joiningdate, FALSE) .'<br/>
                        </div></td>

                 

                </tr>
            </table>
<br/>
<div style="border-bottom:1px solid #000; font-size:150px; font-weight:bold;">Loan  Informations</div> <br/>
    <table>
            <tr>';

$product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
$interval = $this->setting_model->intervalinfo($loaninfo->interval)->row();

$html.='  <td valign="top"><div>
                        <strong>' . lang('loan_product') . ' : </strong> ' . $product->name . '<br/>
                        <strong>' . lang('loanproduct_interest') . ' : </strong> ' . $loaninfo->rate . '<br/>
                        <strong>' . lang('loan_installment') . ' : </strong> ' . $loaninfo->number_istallment . ' ' . $interval->name . '<br/> 
                       <strong>' . lang('loan_paysource') . ' : </strong> ' . $loaninfo->pay_source . '<br/>
                    </div></td>
                <td valign="top"><div>
                <strong>' . lang('loan_applicationdate') . ' : </strong> ' . format_date($loaninfo->applicationdate, FALSE) . '<br/>
                        <strong>' . lang('loan_installment_amount') . ' : </strong> ' . number_format($loaninfo->installment_amount, 2) . '<br/>
                        <strong>' . lang('loan_total_interest') . ' : </strong> ' . number_format($loaninfo->total_interest_amount, 2) . '<br/>
                        <strong>' . lang('loan_applied_amount') . ' : </strong> ' . number_format($loaninfo->basic_amount, 2) . '<br/>
                        <strong>' . lang('total_loan_amount') . ' : </strong> ' . number_format($loaninfo->total_loan, 2) . '<br/>

                    </div></td>
                <td valign="top"><div style="padding-left: 40px;">
                        <strong>' . lang('loan_LID') . ' : </strong> ' . $loaninfo->LID . '<br/>

                    </div></td>

            </tr>
        </table> <br/>
        <br/>
        <table border="1">
        <thead>
            <tr>
                <th style="text-align: center;">' . lang('sno') . '</th>
                <th style="text-align: center;">' . lang('due_date') . '</th>
                <th style="text-align: center;">' . lang('amount') . '</th>
                <th style="text-align: center;"> Interest</th>
                <th style="text-align: center;"> Principle</th>
                <th style="text-align: center;">' . lang('balance') . '</th>

            </tr>

        </thead>
        <tbody>
            <tr>
                <td></td>
                <td style="text-align: center;">
                </td>
                 <td></td>
                  <td></td>
                <td style="text-align: right;"></td>
                <td style="text-align: right;">' . number_format($loaninfo->basic_amount, 2) . '   &nbsp;  &nbsp; </td>
            </tr>';

if (count($schedule) > 0) {
    $s = 1;
    $initial = $loaninfo->total_loan;
    foreach ($schedule as $key => $value) {

        $html.=' <tr nobr="true">
                        <td style="text-align: center;">' . $s++ . '</td>
                        <td style="text-align: center;">' . date('d M, Y', strtotime($value->repaydate)) . '</td>
                        <td style="text-align: right;">' . number_format($value->repayamount,2) . ' &nbsp;  &nbsp; </td>
                        <td style="text-align: right;">' . number_format($value->interest,2) . ' &nbsp;  &nbsp; </td>
                        <td style="text-align: right;">' . number_format($value->principle,2) . ' &nbsp;  &nbsp; </td>
                        <td style="text-align: right;">' . number_format($value->balance,2) . ' &nbsp;  &nbsp; </td>
                        
                    </tr>';
    }
}
$html.='        </tbody>

</table>';

$this->pdf->writeHTML($html, true, false, false, false, '');

$this->pdf->Output($loaninfo->LID . '.pdf', 'I');
exit;
?>