<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Member Contributions Transactions</strong></h1>
                <h4><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <div style="padding-top: 20px;">
                <style type="text/css">
                    table.table thead tr th{
                        border-bottom-color: #000;
                    }
                    table.table tbody tr td{
                        border: 0px;
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
                                <th style="text-align: center; width: 100px;">Date</th>  
                                <th>Member</th>    
                                <th>Method</th>  
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
                                $dt = explode(' ', $value->createdon);

                                $credit += $value->credit;
                                $debit += $value->debit;
                                ?>
                                <tr>
                                    <td style="text-align: right; padding-right: 4px;"><?php echo $i++; ?>.</td>
                                    <td style="text-align: center;"><?php echo format_date($dt[0], FALSE); ?></td>
                                    <td><?php echo $value->member_id . ' :: ' . $this->member_model->member_name($value->member_id); ?></td>
                                    <td><?php echo $value->paymethod; ?></td>
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
                 <div style="text-align: center">
                    <a href="<?php echo site_url(current_lang() . '/report_contribution/contribution_transaction_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo site_url(current_lang() . '/report_contribution/create_contribution_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>