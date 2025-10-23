<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Loan Interest && Balance</strong></h1>
                <h4><strong>Loan disbursed from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
                
            </div>
            <div style="padding-top: 20px;">

                <style type="text/css">
                   
                </style>
<div class="table-responsive" style="overflow: auto;">
    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px; padding-right: 10px;">S/No</th>  
                                <th style="text-align: left; width: 150px;">Loan ID</th>  
                                <th style="text-align: left; width: 300px;">Name</th>   
                                <th style=" text-align: left; width: 200px; ">Loan Type</th>       
                                <th style=" text-align: left; width: 100px; ">Disbursed Date</th>       
                                <th style=" text-align: right; width: 180px; ">Interest Required</th>       
                                <th style=" text-align: right; width: 180px; ">Interest Paid</th>       
                                <th style=" text-align: right; width: 180px; ">Penalty</th>      
                                <th style=" text-align: right; width: 180px; ">Interest Balance</th>      
                            </tr>

                        </thead>
                        <tbody>
                            <?php
                            $i=1;
                            if(count($transaction) > 0){
                                
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
                            
                           <?php }else{?>
                            <tr>
                                <td colspan="13">No loan disbursed within selected date range</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        
                        
                    </table>
</div>



            </div>

        </div>
        <div style="text-align: center; padding-top: 20px; border-top: 1px solid #000;">
                    <a href="<?php echo site_url(current_lang() . '/report_loan/loan_interest_penalty_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo site_url(current_lang() . '/report_loan/create_loan_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
                </div>
    </div>
</div>