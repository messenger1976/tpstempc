<div class="row">
    <div class="col-lg-12">
        <div style=" margin: auto;">
            <div style="text-align: center;"> 
                <h3 style="margin: 0px; padding: 0px;"><strong>Loan Interest && Penalty Report</strong></h3>
                <h4 style="margin: 0px; padding: 0px;"><strong>Loan disbursed from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
              
            </div>
            <div style="">
                <style type="text/css">
                    table.table{
                     border-right:   1px solid #000;   
                    }
                    table.table tbody tr td{
                       
                        border-left:  1px solid #000;
                        border-bottom:    1px solid #000;
                        padding-top: 5px;
                        font-size: 17px;
                    }
                    table.table thead tr th{
                        border-left:  1px solid #000;
                        border-top:   1px solid #000;
                        border-bottom:1px solid #000;
                    }
                    
                </style>
              <table class="table"  cellspacing="0" cellpading="0">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px; padding-right: 10px;">S/No</th>  
                                <th style="text-align: left; width: 120px;">Loan ID</th>  
                                <th style="text-align: left; width: 250px;">Name</th>   
                                <th style=" text-align: left; width: 220px; ">Loan Type</th>       
                                <th style=" text-align: left; width: 100px; ">Disbursed Date</th>       
                                <th style=" text-align: right; width: 170px; ">Interest Required</th>       
                                <th style=" text-align: right; width: 170px; ">Interest Paid</th>       
                                <th style=" text-align: right; width: 170px; ">Penalty</th>      
                                <th style=" text-align: right; width: 170px; ">Interest Balance</th>      
                           </tr>

                        </thead>  
                        <tbody>
                             <tbody>
                            <?php
                            $i=1;
                            
                               $interest = 0;
                                $penalt = 0;
                                $interest_required = 0;
                                $interest_remain = 0;
                            foreach ($transaction as $key => $value) { 
                                 $loaninfo = $this->loan_model->loan_info($value->LID)->row();
                                $interest += $value->interest;
                                $penalt += $value->penalt;
                                $interest_required += $loaninfo->total_interest_amount;
                                $interest_remain += ($loaninfo->total_interest_amount-$value->interest);
                           ?>
                            <tr>
                                <td style="text-align: right;"><?php echo $i++; ?>.</td>
                                <td style="text-align: left;"><?php echo $value->LID ?></td>
                                <td style="text-align: left;"><?php echo $this->member_model->member_name($value->member_id) ?></td>
                                <td style="text-align: left;"><?php echo $this->setting_model->loanproduct($value->product_type)->row()->name; ?></td>
                                <td style="text-align: center;"><?php echo format_date($value->disbursedate,FALSE); ?></td>
                                 <td style="text-align: right;"><?php echo number_format($loaninfo->total_interest_amount,2); ?></td>
                                <td style="text-align: right;"><?php echo number_format($value->interest,2); ?></td>
                                <td style="text-align: right;"><?php echo number_format($value->penalt,2); ?></td>
                                <td style="text-align: right;"><?php echo number_format(($loaninfo->total_interest_amount - $value->interest),2); ?></td>
                            </tr>  
                            <?php } ?>
                           <tr>
                                <td colspan="5" ></td>
                                <td style="text-align: right;"><?php echo number_format($interest_required,2); ?></td>
                                <td style="text-align: right;"><?php echo number_format($interest,2); ?></td>
                                <td style="text-align: right;"><?php echo number_format($penalt,2); ?></td>
                                <td style="text-align: right;"><?php echo number_format($interest_remain,2); ?></td>
                                
                            </tr>
                        </tbody>
                        
                        
              </table>
                
                
                
                
            </div>
        </div>
    </div>
</div>