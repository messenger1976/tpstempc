<div class="row">
    <div class="col-lg-12">
        <div >
            <div style="text-align: center;"> 
                <h3 style="padding: 0px; margin: 0px;"><strong>Saving Account List</strong></h3>
                <h4 style="padding: 0px; margin: 0px;"><strong>Account created  from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
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
                        font-size: 12px;
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
                                <th style="text-align: left;">Account No</th>  
                                <th style="text-align: left;">Account Name</th>  
                                <th style="text-align: left;">Account Type</th>  
                                <th style="text-align: right;">Available Balance</th>  
                                <th style="text-align: right;">Actual Balance</th>  
                            </tr>

                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $balance = 0;
                            $actual = 0;
                            foreach ($transaction as $key => $value) {
                                $account = $this->finance_model->saving_account_list(null, $value->account_cat)->row();

                                $balance += $value->balance;
                                $actual += $value->balance;
                                $actual += $value->virtual_balance;
                                ?>
                                <tr>
                                    <td style="text-align: right; padding-right: 4px;"><?php echo $i++; ?>.</td>
                                    <td><?php echo $value->account ?></td>
                                    <td><?php echo $this->report_model->saving_account_name($value->RFID, $value->tablename); ?></td>
                                    <td><?php echo $account->name ?></td>
                                    <td style="text-align: right;"><?php echo number_format($value->balance, 2); ?></td>
                                    <td style="text-align: right;"><?php echo number_format(($value->balance + $value->virtual_balance), 2); ?></td>

                                </tr> 
                            <?php } ?>
                            <tr>
                                <td colspan="4" style="border-top: 1px solid #000; border-bottom:  1px solid #000; "></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($balance, 2); ?></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($actual, 2); ?></td>


                            </tr>
                        </tbody>
                    </table>

                </div>
               
            </div>
        </div>
    </div>
</div>