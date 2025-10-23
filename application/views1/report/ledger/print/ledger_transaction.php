<div class="row">
    <div class="col-lg-12">
        <div style=" margin: auto;">
            <div style="text-align: center;"> 
                <h3 style="margin: 0px; padding: 0px;"><strong>General Ledger Transactions</strong></h3>
                <h4 style="margin: 0px; padding: 0px;"><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <div style="">
                <style type="text/css">
                    table.table tbody tr td{
                        border: 0px;
                        padding-left: 20px;
                         padding-top: 10px;
                         font-size: 13px;
                        
                    }
                     table.table thead tr th{
                        border-bottom: 1px solid #000;
                        padding-left: 20px;
                       
                    }
                </style>
                <table cellpadding="0" cellspacing="0" class="table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Date</th>
                            <th style="text-align: center;">#</th>
                            <th>Account</th>
                            <th style="text-align: right; padding-right: 20px; width: 200px;">Debit</th>
                            <th style="text-align: right; padding-right: 20px; width: 200px;">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $credittotal = 0;
                        $debittotal = 0;
                        foreach ($transaction as $key => $value) {
                            $credittotal += $value->credit;
                            $debittotal += $value->debit;
                            ?>
                            <tr>
                                <td style="text-align: center;"><?php echo format_date($value->date,false); ?></td>
                                <td style="text-align: center;"><?php echo ($value->invoiceid > 0 ? '#'.$value->invoiceid:''); ?></td>
                                <td style="<?php echo ($value->credit > 0 ? 'padding-left:30px;':'');  ?>"> <?php echo $value->name; ?></td>
                                <td style="text-align: right; padding-right: 20px;"><?php echo ($value->debit > 0 ? number_format($value->debit,2):''); ?></td>
                                <td style="text-align: right; padding-right: 20px;"><?php echo ($value->credit > 0 ? number_format($value->credit,2):''); ?></td>
                            </tr>
                        <?php }
                        ?>
                            <tr>
                              <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000; text-align: right; padding-right: 20px;"><?php echo number_format($debittotal,2); ?></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;text-align: right; padding-right: 20px;"><?php echo number_format($credittotal,2); ?></td>
                         
                            </tr>
                    </tbody>

                </table>
            </div>
            
        </div>
        
    </div>
</div>