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
               
</b>
            </td>
        </tr>
    </table><div style="text-align:center; font-size:200px; padding:0px; margin:0px;"><strong>General Ledger Transactions</strong></div><div style="text-align:center; padding:0px; margin:0px;"><strong>For the period from  '.format_date($reportinfo->fromdate, false) .' to '.format_date($reportinfo->todate, false).'</strong></div>';

$html.='<br/><br/> <table  style="width:100%;">
                    <thead>
                        <tr>
                            <th style="text-align: center; width:20%; border-bottom:1px solid #000;">Date</th>
                            <th style="text-align: center;width:10%; border-bottom:1px solid #000;">#</th>
                            <th style="width:35%; border-bottom:1px solid #000;">Account</th>
                            <th style="text-align: right; padding-right: 20px; width: 18%; border-bottom:1px solid #000;">Debit</th>
                            <th style="text-align: right; padding-right: 20px; width: 18%; border-bottom:1px solid #000;">Credit</th>
                        </tr>
                    </thead>
                    <tbody>';
                        $credittotal = 0;
                        $debittotal = 0;
                        foreach ($transaction as $key => $value) {
                            $credittotal += $value->credit;
                            $debittotal += $value->debit;
                            
                         $html.='   <tr>
                                <td style="text-align: center; width:20%;">'.format_date($value->date,false).'</td>
                                <td style="text-align: center; width:10%;">'.($value->invoiceid > 0 ? '#'.$value->invoiceid:'') .'</td>
                                <td style="width:35%;"> '. $value->name.'</td>
                                <td style="text-align: right; width:18%; ">'.($value->debit > 0 ? number_format($value->debit,2):'').'</td>
                                <td style="text-align: right; width:18%; ">'. ($value->credit > 0 ? number_format($value->credit,2):'').'</td>
                            </tr>';
                         }
                       
                          $html.='  <tr>
                              <td style="border-top: 1px solid #000; width:20%; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; width:10%; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; width:35%; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; width:18%; border-bottom:  1px solid #000; text-align: right; padding-right: 20px;">'.number_format($debittotal,2).'</td>
                                <td style="border-top: 1px solid #000; width:18%; border-bottom:  1px solid #000;text-align: right; padding-right: 20px;">'.number_format($credittotal,2).'</td>
                         
                            </tr>
                    </tbody>';


$html.= '</table>';




$this->pdf->writeHTML($html, true, false, false, false, '');

$from = str_replace('-', '', $reportinfo->fromdate);
$to = str_replace('-', '', $reportinfo->todate);
$this->pdf->Output('ledger_trans_' . $from . '_' . $to . '.pdf', 'I');
exit;
?>