<div class="row">
    <div class="col-lg-12">
        <div >
            <div style="text-align: center;"> 
                
                <h3 style="padding: 0px; margin: 0px;"><strong>Member Contribution Statement</strong></h3>
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
                <div style="margin-left: 100px; margin-bottom: 10px; margin-top: 10px;">
                    <strong>Member ID : </strong> <?php echo $reportinfo->description; ?><br/>
                    <strong>Member Name : </strong> <?php echo $this->member_model->member_name($reportinfo->description); ?><br/>
                </div>
                <div class="table-responsive" style="overflow: auto;">
                    <table class="table" style="width: 100%;" cellspacing='0' cellpadding='0' >
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 130px;">Date</th>  
                                <th style="width: 300px; text-align: left;">Description</th>  
                                <th style="width: 190px; text-align: right;">Debit [DR]</th>  
                                <th style="width: 190px; text-align: right;">Credit [CR]</th>  
                                <th style="width: 190px; text-align: right;">Balance</th>  
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $balance = 0;
                            $previous_trans = $this->report_model->contribution_statement_previous($reportinfo->fromdate, $reportinfo->description);

                            if ($previous_trans) {
                                $balance = $previous_trans->credit - $previous_trans->debit;

                                ?>
                                <tr>
                                    <td></td>
                                    <td>BROUGHT FORWARD BALANCE</td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: right;"><?php echo number_format($balance, 2); ?></td>
                                </tr>
                                <?php } else {
                                ?>
                                <tr>
                                    <td></td>
                                    <td>BROUGHT FORWARD BALANCE</td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: right;"><?php echo number_format(0, 2); ?></td>
                                </tr>  

                                <?php
                            }
                            $credit = 0;
                            $debit = 0;
                            foreach ($transaction as $key => $value) {
                                $dt = explode(' ', $value->createdon);
                                if ($value->debit > 0) {
                                    $balance -= $value->debit;
                                    $debit += $value->debit;
                                } else if ($value->credit > 0) {
                                    $balance += $value->credit;
                                    $credit += $value->credit;
                                }
                                ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo format_date($dt[0], FALSE); ?></td>
                                    <td><?php echo $value->system_comment . ' [' . $value->paymethod . ']'; ?></td>
                                    <td style="text-align: right;"><?php echo ($value->debit > 0 ? number_format($value->debit, 2) : ''); ?></td>
                                    <td style="text-align: right;"><?php echo ($value->credit > 0 ? number_format($value->credit, 2) : ''); ?></td>
                                    <td style="text-align: right;"><?php echo number_format($balance, 2); ?></td>
                                </tr>
                            <?php }
                            ?>
                            <tr>
                                <td colspan="2" style="border-top: 1px solid #000;border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000;border-bottom:  1px solid #000; text-align: right;"><?php echo number_format($debit, 2); ?></td>
                                <td style="border-top: 1px solid #000;border-bottom:  1px solid #000; text-align: right;"><?php echo number_format($credit, 2); ?></td>
                                <td  style="border-top: 1px solid #000;border-bottom:  1px solid #000;"></td>
                            </tr>
                        </tbody>


                    </table>
                    <div style="text-align: right; font-size: 25px; font-weight: bold;"> Balance : <?php echo number_format($balance,2);?></div>
                </div>
               
            </div>
        </div>
    </div>
</div>
