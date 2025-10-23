<?php
//$transaction = $this->report_model->ledger_trans_summary($reportinfo->fromdate,$reportinfo->todate,4);
/* echo '<pre>';
  print_r($transaction);
  echo '</pre>';
  exit; */
?>
<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>General Ledger Summary</strong></h1>
                <h4><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <div style="padding-top: 20px;">
                <style type="text/css">
                    table.table tbody tr td{
                        border: 0px;
                    }
                </style>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 300px; "></th>
                            <th style="text-align: center; width: 130px;">Opening balance</th>
                            <th style="text-align: right;  width: 130px;">Total Debit</th>
                            <th style="text-align: right;  width: 130px;">Total Credit</th>
                            <th style="text-align: right; width: 130px;">Net Movement</th>
                            <th style="text-align: right; width: 130px;">Closing balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //check income

                        $transaction = $this->report_model->create_ledger_trans_summary($reportinfo->fromdate, $reportinfo->todate);

                        $total_credit = 0;
                        $total_debit = 0;

                        $net_prfit_credit = 0;
                        $net_prfit_debit = 0;

                        $check_exp_inc = 0;
                        if (array_key_exists(40, $transaction)) {
                            $check_exp_inc = 1;
                            //income data available
                            ?>
                            <tr><td colspan="6"><strong>Income</strong></td></tr>
                            <?php
                            foreach ($transaction[4] as $key1 => $value1) {
                                $account_info = $this->finance_model->account_chart(null, $key1)->row();
                                $sub_credit = 0;
                                $sub_debit = 0;
                                $open_balance_label = '-';

                                if (count($value1['current']) > 0) {
                                    $sub_credit += $value1['current']->credit;
                                    $sub_debit += $value1['current']->debit;

                                    $net_prfit_debit += $value1['current']->debit;
                                    $net_prfit_credit += $value1['current']->credit;
                                    $total_debit += $value1['current']->debit;
                                    $total_credit += $value1['current']->credit;
                                }
                                $close_balance = 0;
                                $close_balance_label = '-';
                                $close_balance = $sub_debit - $sub_credit;
                                if ($close_balance > 0) {
                                    $close_balance_label = number_format($close_balance, 2) . 'Dr';
                                } else if ($close_balance < 0) {
                                    $close_balance_label = number_format((-1 * $close_balance), 2) . 'Cr';
                                    $close_balance = (-1 * $close_balance);
                                }
                                ?>
                                <tr>
                                    <td style="padding-left: 35px;"><?php echo $account_info->name; ?></td>
                                    <td style="text-align: right;"><?php echo $open_balance_label; ?></td>
                                    <td style="text-align: right;"><?php echo (count($value1['current']) > 0 ? ($value1['current']->debit > 0 ? number_format($value1['current']->debit, 2) : '-') : '-'); ?></td>
                                    <td style="text-align: right;"><?php echo (count($value1['current']) > 0 ? ($value1['current']->credit > 0 ? number_format($value1['current']->credit, 2) : '-') : '-'); ?></td>
                                    <td style="text-align: right;"><?php echo $close_balance_label ?></td>
                                    <td style="text-align: right;"><?php echo $close_balance_label; ?></td>
                                </tr>  
                            <?php }
                            ?>
                            <?php
                            unset($transaction[4]);
                        }


                        if (array_key_exists(5, $transaction)) {
                            $check_exp_inc = 1;
                            //income data available
                            ?>
                            <tr><td colspan="6"><strong>Expenses</strong></td></tr>
                            <?php
                            foreach ($transaction[5] as $key1 => $value1) {
                                $account_info = $this->finance_model->account_chart(null, $key1)->row();
                                $sub_credit = 0;
                                $sub_debit = 0;
                                $open_balance_label = '-';

                                if (count($value1['current']) > 0) {
                                    $sub_credit += $value1['current']->credit;
                                    $sub_debit += $value1['current']->debit;

                                    $net_prfit_debit += $value1['current']->debit;
                                    $net_prfit_credit += $value1['current']->credit;
                                     $total_debit += $value1['current']->debit;
                                    $total_credit += $value1['current']->credit;
                                }
                                $close_balance = 0;
                                $close_balance_label = '-';
                                $close_balance = $sub_debit - $sub_credit;
                                if ($close_balance > 0) {
                                    $close_balance_label = number_format($close_balance, 2) . 'Dr';
                                } else if ($close_balance < 0) {
                                    $close_balance_label = number_format((-1 * $close_balance), 2) . 'Cr';
                                    $close_balance = (-1 * $close_balance);
                                }
                                ?>
                                <tr>
                                    <td style="padding-left: 35px;"><?php echo $account_info->name; ?></td>
                                    <td style="text-align: right;"><?php echo $open_balance_label; ?></td>
                                    <td style="text-align: right;"><?php echo (count($value1['current']) > 0 ? ($value1['current']->debit > 0 ? number_format($value1['current']->debit, 2) : '-') : '-'); ?></td>
                                    <td style="text-align: right;"><?php echo (count($value1['current']) > 0 ? ($value1['current']->credit > 0 ? number_format($value1['current']->credit, 2) : '-') : '-'); ?></td>
                                    <td style="text-align: right;"><?php echo $close_balance_label ?></td>
                                    <td style="text-align: right;"><?php echo $close_balance_label; ?></td>
                                </tr>  
                            <?php }
                            ?>
                            <?php
                            unset($transaction[5]);
                        }


                        if ($check_exp_inc == 1) {
                            $close_balance = 0;
                            $close_balance_label = '-';
                            $close_balance = $net_prfit_debit - $net_prfit_credit;
                            $balance_credit = 0;
                            $balance_debit = 0;
                            if ($close_balance > 0) {
                                $close_balance_label = number_format($close_balance, 2) . 'Cr';
                                $balance_credit = $close_balance;
                                $total_credit += $close_balance;
                            } else if ($close_balance < 0) {
                                $close_balance_label = number_format((-1 * $close_balance), 2) . 'Dr';
                                $close_balance = (-1 * $close_balance);
                                $balance_debit = $close_balance;
                                $total_debit += $close_balance;
                            }
                            ?>
                           

                            <tr>
                                <td style=" border-bottom: 1px solid #000; border-top: 1px solid #000;"><strong>Net profit</strong></td>
                                <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"></td>
                                <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($balance_debit, 2); ?></td>
                                <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($balance_credit, 2); ?></td>
                                <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $close_balance_label; ?></td>
                                <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo $close_balance_label; ?></td>
                            </tr> 
                            <tr><td colspan="6"></td></tr>
                            <?php
                        }


                        // other  account
                        foreach ($transaction as $key => $value) {
                            $type_account = $this->finance_model->account_typelist($key)->row();
                            ?>
                            <tr><td colspan="6"><strong><?php echo $type_account->name; ?></strong></td></tr>

                            <?php
                            foreach ($value as $key1 => $value1) {
                                $account_info = $this->finance_model->account_chart(null, $key1)->row();
                                $sub_credit = 0;
                                $sub_debit = 0;
                                $open_balance = $value1['balance'];
                                $open_balance_label = '-';
                                if ($open_balance > 0) {
                                    $open_balance_label = number_format($open_balance, 2) . 'Dr';
                                    $sub_debit += $open_balance;
                                } else if ($open_balance < 0) {
                                    $open_balance_label = number_format((-1 * $open_balance), 2) . 'Cr';
                                    $sub_credit += (-1 * $open_balance);
                                }
                                if (count($value1['current']) > 0) {
                                    $sub_credit += $value1['current']->credit;
                                    $sub_debit += $value1['current']->debit;

                                    $total_debit += $value1['current']->debit;
                                    $total_credit += $value1['current']->credit;
                                }
                                $close_balance = 0;
                                $close_balance_label = '-';
                                $close_balance = $sub_debit - $sub_credit;
                                if ($close_balance > 0) {
                                    $close_balance_label = number_format($close_balance, 2) . 'Dr';
                                } else if ($close_balance < 0) {
                                    $close_balance_label = number_format((-1 * $close_balance), 2) . 'Cr';
                                    $close_balance = (-1 * $close_balance);
                                }
                                ?>
                                <tr>
                                    <td style="padding-left: 35px;"><?php echo $account_info->name; ?></td>
                                    <td style="text-align: right;"><?php echo $open_balance_label; ?></td>
                                    <td style="text-align: right;"><?php echo (count($value1['current']) > 0 ? ($value1['current']->debit > 0 ? number_format($value1['current']->debit, 2) : '-') : '-'); ?></td>
                                    <td style="text-align: right;"><?php echo (count($value1['current']) > 0 ? ($value1['current']->credit > 0 ? number_format($value1['current']->credit, 2) : '-') : '-'); ?></td>
                                    <td style="text-align: right;"><?php echo $close_balance_label ?></td>
                                    <td style="text-align: right;"><?php echo $close_balance_label; ?></td>
                                </tr>  
                            <?php }
                            ?>
                        <?php }
                        ?>
                        <tr>
                            <td style="padding-left: 35px; border-bottom: 1px solid #000; border-top: 1px solid #000;"></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($total_debit, 2); ?></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo number_format($total_credit, 2); ?></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"></td>
                        </tr> 
                    </tbody>

                </table>
            </div>
            <div style="text-align: center">
                <a href="<?php echo site_url(current_lang() . '/report/ledger_trans_print_summary/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                &nbsp; &nbsp; &nbsp; &nbsp;
                <a href="<?php echo site_url(current_lang() . '/report/create_ledger_trans_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            </div>
        </div>

    </div>
</div>