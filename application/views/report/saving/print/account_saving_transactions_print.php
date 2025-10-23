<div class="row">
    <div class="col-lg-12">
            <div >
            <div style="text-align: center;"> 
                
                <h3 style="padding: 0px; margin: 0px;"><strong>Saving Accounts Transactions</strong></h3>
                <h4 style="padding: 0px; margin: 0px;"><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <div>
                <style type="text/css">
                    table.table thead tr th{
                        border-bottom: 1px solid #000;
                    }
                    table.table tbody tr td{
                        border: 0px;
                        padding-top: 5px;
                    }
                    table.table tbody tr td.draw_border{
                        border-top: 1px solid #ccc;
                    }
                </style>
                <div class="table-responsive" style="overflow: auto;">
                    <table class="table" cellspacing='0' cellpadding='0' style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">S/No</th>  
                                <th style="text-align: center; width: 120px;">Date</th>  
                                <th style="text-align: left;">Account</th>    
                                <th style="text-align: center; width: 100px;">Method</th>  
                                <th style="text-align: right;">Debit [DR]</th>  
                                <th style="text-align: right;">Credit [CR]</th>  
                            </tr>

                        </thead>
                        <tbody>
                            <?php
                          $i = 1;
                            $credit = 0;
                            $debit = 0;
                            foreach ($transaction as $key => $value) {
                           $dt = explode(' ', $value->trans_date);
                             
                                $credit += $value->credit;
                                $debit += $value->debit;
                                ?>
                                <tr>
                                    <td style="text-align: right; padding-right: 4px;"><?php echo $i++; ?>.</td>
                                   <td style="text-align: center;"><?php echo format_date($dt[0], FALSE); ?></td>
                                    <td><?php echo $value->account . ' :: ' . $this->finance_model->saving_account_name($value->account); ?></td>
                                    <td style="text-align: center;"><?php echo $value->paymethod; ?></td>
                                     <td style="text-align: right;"><?php echo ($value->debit > 0 ? number_format($value->debit, 2) : ''); ?></td>
                                    <td style="text-align: right;"><?php echo ($value->credit > 0 ? number_format($value->credit, 2) : ''); ?></td>
                                    
                                </tr> 
                            <?php } ?>
                            <tr>
                                <td colspan="4" style="border-top: 1px solid #000; border-bottom:  1px solid #000; "></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($debit, 2); ?></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($credit, 2); ?></td>

                            </tr>
                           
                          
                        </tbody>
                    </table>

                </div>
                
            </div>
        </div>
    </div>
</div>