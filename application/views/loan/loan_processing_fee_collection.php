<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Loan Processing Fee Collection</strong></h1>
                <h4><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
                
            </div>
            <div style="padding-top: 20px;">

                <style type="text/css">
                   
                </style>
<div class="table-responsive" style="overflow: auto;">
    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px; padding-right: 10px;">S/No</th> 
                                <th style="text-align: left; width: 100px; ">Date</th>
                                <th style="text-align: left; width: 200px;">Loan ID</th>  
                                <th style="text-align: left; width: 300px;">Name</th>
                                <th style=" text-align: right; width: 200px; ">Amount</th>      
                            </tr>

                        </thead>
                        <tbody>
                            <?php
                            $i=1;
                            if(count($transaction) > 0){
                                
                             
                                $amount_total = 0;
                            foreach ($transaction as $key => $value) { 
                               $loaninfo = $this->loan_model->loan_info($value->LID)->row();
                               $amount_total += $value->amount;
                                ?>
                            <tr>
                                <td style="text-align: right;"><?php echo $i++; ?>.</td>
                                <td style="text-align: center;"><?php echo substr(format_date($value->createdon,false),0,10); ?></td>
                                <td style="text-align: left;"><?php echo $value->LID ?></td>
                                <td style="text-align: left;"><?php echo $this->member_model->member_name($loaninfo->member_id) ?></td>                              
                                <td style="text-align: right;"><?php echo number_format($value->amount,2); ?></td>
                                
                            </tr>  
                            <?php } ?>
                            <tr>
                                <td colspan="4" style="text-align: right;">Total</td>
                                <td style="text-align: right;"><?php echo number_format($amount_total,2); ?></td>
                                
                           
                                
                            </tr>
                            
                           <?php }else{?>
                            <tr>
                                <td colspan="13">No data found</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        
                        
                    </table>
</div>



            </div>

        </div>
        <div style="text-align: center; padding-top: 20px; border-top: 1px solid #000;">
                    <a href="<?php echo site_url(current_lang() . '/report_loan/loan_processing_fee_collection_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo site_url(current_lang() . '/report_loan/create_loan_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
                </div>
    </div>
</div>