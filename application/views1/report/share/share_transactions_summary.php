<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Member Shares Transactions Summary</strong></h1>
                <h4><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <div style="padding-top: 20px;">
                <style type="text/css">
                    table.table thead tr th{
                        border-bottom-color: #000;
                    }
                    table.table tbody tr td{
                        border: 0px;
                        padding-right: 10px;
                        vertical-align: middle !important;
                         border-bottom:  1px solid #ccc;
                    }
                    table.table tbody tr td.draw_border{
                        border-top: 1px solid #ccc;
                    }
                </style>
                <div class="table-responsive" style="overflow: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">S/No</th>  
                                <th style="width: 350px;">Member</th> 
                                <th style="text-align: right; width: 200px;">Opening balance</th>  
                                <th style="text-align: right; width: 150px;">No. of Share</th>  
                                <th style="text-align: right; width: 200px;">Debit [DR]</th>  
                                <th style="text-align: right; width: 200px;">Credit [CR]</th>  
                                <th style="text-align: right; width: 150px;">No. Share affected</th>  
                                <th style="text-align: right; width: 150px;">No. Share Remain</th>  
                                <th style="text-align: right; width: 200px;">Closing Balance</th>  
                            </tr>

                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $credit = 0;
                            $debit = 0;
                            foreach ($transaction as $key => $value) {
                                $balance_open = $this->report_model->share_transactions_summary_previous($reportinfo->fromdate, $value->member_id);

                                $balance_tmp = $balance_open->credit - $balance_open->debit;
                                $balance_tmp_label = '';
                                $share_balance_tmp = $balance_open->share_credit - $balance_open->share_debit;
                                $share_balance_tmp_label = '';
                                
                                if ($balance_tmp > 0) {
                                    $balance_tmp_label = number_format($balance_tmp, 2) . ' Cr';
                                    $share_balance_tmp_label = number_format($share_balance_tmp,2).' Cr';
                                } else if ($balance_tmp < 0) {
                                    $balance_tmp_label = number_format((-1 * $balance_tmp), 2) . 'Dr';
                                    $share_balance_tmp_label = number_format((-1*$share_balance_tmp),2).' Dr';
                                }
                                
                                $credit += $value->credit;
                                $debit += $value->debit;

                                $close_balance = $balance_tmp;
                                $close_balance_label = '';
                                $close_balance -= $value->debit;
                                $close_balance += $value->credit;
                                
                                $close_balance_share = $share_balance_tmp;
                                $close_balance_share -= $value->debit_sha;
                                $close_balance_share += $value->credit_sha;
                                $close_balance_label_share = '';
                                if ($close_balance > 0) {
                                    $close_balance_label = number_format($close_balance, 2) . ' Cr';
                                    $close_balance_label_share = number_format($close_balance_share,2).' Cr';
                                } else if ($close_balance < 0) {
                                    $close_balance_label = number_format((-1 * $close_balance), 2) . ' Dr';
                                    $close_balance_label_share = number_format((-1*$close_balance_share),2).' Cr';
                                }
                                
                                
                                ?>
                                <tr>
                                    <td style="text-align: right; padding-right: 4px;"><?php echo $i++; ?>.</td>
                                    <td><?php echo $value->member_id . ' <br/>' . $this->member_model->member_name($value->member_id); ?></td>
                                    <td style="text-align: right;"><?php echo $balance_tmp_label; ?></td>
                                    <td style="text-align: right;"><?php echo $share_balance_tmp_label; ?></td>
                                    <td style="text-align: right;"><?php echo ($value->debit > 0 ? number_format($value->debit, 2) : ''); ?></td>
                                    <td style="text-align: right;"><?php echo ($value->credit > 0 ? number_format($value->credit, 2) : ''); ?></td>
                                    <td style="text-align: right;"><?php echo '[ '.$value->debit_sha.' Dr ]<br/> [ '.$value->credit_sha. ' Cr ]'; ?></td>
                                    <td style="text-align: right;"><?php echo $close_balance_label_share; ?></td>
                                    <td style="text-align: right;"><?php echo $close_balance_label; ?></td>

                                </tr> 
                            <?php } ?>
                            <tr>
                                <td colspan="4" style="border-top: 1px solid #000; border-bottom:  1px solid #000; "></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($debit, 2); ?></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($credit, 2); ?></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                                <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>

                            </tr>


                        </tbody>
                    </table>

                </div>
                <div style="text-align: center">
                    <a href="<?php echo site_url(current_lang() . '/report_share/share_transaction_summary_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo site_url(current_lang() . '/report_share/create_share_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>