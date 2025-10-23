<div class="row">
    <div class="col-lg-12">
        <div style=" margin: auto;">
            <div style="text-align: center;"> 
                <h3 style="margin: 0px; padding: 0px;"><strong>Loan Repayment Transactions Summary</strong></h3>
                <h4 style="margin: 0px; padding: 0px;"><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
              
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
                    }
                    table.table thead tr th{
                        border-left:  1px solid #000;
                        border-top:   1px solid #000;
                        border-bottom:1px solid #000;
                    }
                    
                </style>
                <table class="table"  cellspacing="0" cellpading="0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px; padding-right: 10px;">S/No</th>  
                                <th style="text-align: left;">Loan ID</th>  
                                <th style="text-align: left;">Name</th>   
                                <th style=" text-align: left;">Loan Type</th>       
                                <th style=" text-align: right; width: 200px; ">Paid Amount</th>     
                            </tr>

                        </thead>  
                        <tbody>
                             <tbody>
                            <?php
                            $i=1;
                            
                                 $amount_total = 0;
                            foreach ($transaction as $key => $value) { 
                                $loaninfo = $this->loan_model->loan_info($value->LID)->row();
                               $amount_total += $value->amount;
                                ?>
                            <tr>
                                <td style="text-align: right;"><?php echo $i++; ?>.</td>
                                <td style="text-align: left;"><?php echo $value->LID ?></td>
                                <td style="text-align: left;"><?php echo $this->member_model->member_name($loaninfo->member_id) ?></td>
                                <td style="text-align: left;"><?php echo $this->setting_model->loanproduct($loaninfo->product_type)->row()->name; ?></td>
                                <td style="text-align: right;"><?php echo number_format($value->amount,2); ?></td>
                            </tr>  
                            <?php } ?>
                           <tr>
                                <td colspan="4" ></td>
                                <td style="text-align: right;"><?php echo number_format($amount_total,2); ?></td>
                                
                            </tr>
                          
                        </tbody>
                        
                        
              </table>
                
                
                
                
            </div>
        </div>
    </div>
</div>