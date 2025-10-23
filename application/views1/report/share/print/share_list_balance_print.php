<div class="row">
    <div class="col-lg-12">
        <div >
            <div style="text-align: center;"> 
                <h3 style="padding: 0px; margin: 0px;"><strong>Member Shares Balance</strong></h3>
                <h4 style="padding: 0px; margin: 0px;"><strong>Member Joined  from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <br/>
            <div >
                <style type="text/css">
                    table.table thead tr th{
                        border-bottom: 1px solid #000;
                        font-size: 13px;
                    }
                    table.table tbody tr td{
                        border: 0px;
                        font-size: 13px;
                        padding-top: 5px;
                    }
                    table.table tbody tr td.draw_border{
                        border-top: 1px solid #ccc;
                    }
                </style>
                <div class="table-responsive" style="overflow: auto;">
                    <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">S/No</th>  
                                <th style="text-align: left;">Member ID</th>  
                                <th style="text-align: left;">Member Name</th>   
                                <th style="text-align: right;">Share Total</th>  
                                <th style="text-align: right;">Amount Balance</th>  
                            </tr>

                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $share_balance = 0;
                            $balance = 0;
                            foreach ($transaction as $key => $value) {
                                 $balance += $value->amount;
                                  $balance += $value->remainbalance;
                                  
                                  $share_balance += $value->totalshare;
                                 ?>
                                <tr>
                                    <td style="text-align: right; padding-right: 4px;"><?php echo $i++; ?>.</td>
                                    <td><?php echo $value->member_id ?></td>
                                   <td><?php echo $value->name ?></td>
                                   <td style="text-align: right;"><?php echo number_format($value->totalshare, 2); ?></td>
                                    <td style="text-align: right;"><?php echo number_format(($value->amount + $value->remainbalance), 2); ?></td>
                                   
                                </tr> 
                            <?php } ?>
                            <tr>
                                <td colspan="3" style="border-top: 1px solid #000; border-bottom:  1px solid #000; "></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($share_balance, 2); ?></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($balance, 2); ?></td>
                               

                            </tr>
                        </tbody>
                    </table>

                </div>
               
            </div>
        </div>
    </div>
</div>