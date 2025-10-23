
<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 0px; margin: auto;">
            <div style="text-align: center;">
                <h3 style="margin: 0px; padding: 0px;"><strong>Income Statement</strong></h3>
                <h4 style="margin: 0px; padding: 0px;"><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <style type="text/css">
                table.table tbody tr td{
                    border: 0px;
                    font-size: 13px;
                }
                table tr td{
                    padding-top: 5px;
                    font-size: 13px;
                }
            </style>
            
            <table class="table" cellpadding='0' cellspacing='0' style="border-top: 1px solid #000;">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 500px; "></th>
                            <th style="text-align: right;  width: 250px;"></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        //check income

                        $transaction = $this->report_model->create_ledger_trans_summary($reportinfo->fromdate, $reportinfo->todate);

                        $total_income = 0;
                        $total_expenses = 0;


                        $check_exp_inc = 0;
                        if (array_key_exists(4, $transaction)) {
                            $check_exp_inc = 1;
                            //income data available
                            ?>
                            <tr><td colspan="2"><strong>Revenues & Gains</strong></td></tr>
                            <?php
                            foreach ($transaction[4] as $key1 => $value1) {
                                $account_info = $this->finance_model->account_chart(null, $key1)->row();

                                $open_balance_label = 0;

                                if (count($value1['current']) > 0) {

                                    $tmp = $value1['current']->credit - $value1['current']->debit;

                                    if ($tmp < 0) {
                                        $open_balance_label = '(' . number_format((0 - $tmp), 2) . ')';
                                        $total_income -= (0 - $tmp);
                                    } else {
                                        $open_balance_label = number_format($tmp, 2);
                                        $total_income += $tmp;
                                    }
                                }
                                ?>
                                <tr>
                                    <td style="padding-left: 40px;"><?php echo $account_info->name; ?></td>

                                    <td style="text-align: right;"><?php echo $open_balance_label; ?></td>

                                </tr>  
                            <?php
                            }
                            $total_income_label = '';

                            if ($total_income < 0) {
                                $total_income_label = '(' . number_format((0 - $total_income), 2) . ')';
                            } else {
                                $total_income_label = number_format($total_income, 2);
                            }
                            ?>
                            <tr>
                                <td style="padding-left: 60px;">Total Revenues & Gains</td>

                                <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $total_income_label; ?></td>

                            </tr> 
                            <?php
                        }


                        if (array_key_exists(5, $transaction)) {
                            $check_exp_inc = 1;
                            //income data available
                            ?>
                            <tr><td colspan="2"><strong><br/>Expenses & Losses</strong></td></tr>
                            <?php
                            foreach ($transaction[5] as $key1 => $value1) {
                                $account_info = $this->finance_model->account_chart(null, $key1)->row();

                                $open_balance_label = '-';

                                if (count($value1['current']) > 0) {

                                    $tmp = $value1['current']->debit - $value1['current']->credit;

                                    if ($tmp < 0) {
                                        $open_balance_label = '(' . number_format((0 - $tmp), 2) . ')';
                                        $total_expenses -= (0 - $tmp);
                                    } else {
                                        $open_balance_label = number_format($tmp, 2);
                                        $total_expenses += $tmp;
                                    }
                                }
                                ?>
                                <tr>
                                    <td style="padding-left: 40px;"><?php echo $account_info->name; ?></td>

                                    <td style="text-align: right;"><?php echo $open_balance_label; ?></td>

                                </tr>  
                            <?php
                            }
                            $total_expenses_label = '';

                            if ($total_expenses < 0) {
                                $total_expenses_label = '(' . number_format((0 - $total_expenses), 2) . ')';
                            } else {
                                $total_expenses_label = number_format($total_expenses, 2);
                            }
                            ?>
                            <tr>
                                <td style="padding-left: 60px;">Total Expenses & Losses</td>

                                <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $total_expenses_label; ?></td>

                            </tr> 
                            <?php
                        }


                        
                         
                          $close_balance_label = '';
                          $close_balance = $total_income - $total_expenses;
                          
                          if ($close_balance > 0) {
                          $close_balance_label = number_format($close_balance, 2) ;
                         
                          } else if ($close_balance < 0) {
                          $close_balance_label = '('.number_format((0-$close_balance), 2) . ')';
                          
                          }
                          ?>

                          <tr><td colspan="2"><?php
                          if ($check_exp_inc == 1) {
                          echo '<br/><br/>';
                          }
                          ?></td></tr>
                          <tr>
                          <td style=" border-bottom: 1px solid #000; border-top: 1px solid #000;"><strong>Net profit</strong></td>

                          <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $close_balance_label; ?></td>

                          </tr>
                          <tr><td colspan="6"></td></tr>
                        
                    </tbody>                    

                </table>





        </div>
    </div>
</div>