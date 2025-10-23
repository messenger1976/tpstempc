<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Member Share balance</strong></h1>
                <h4><strong>Member Joined  from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
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
                                <th style="text-align: center; width: 60px; padding-right: 10px;">S/No</th>  
                                <th style="text-align: left; width: 200px;">Member ID</th>  
                                <th style="text-align: left;">Name</th>   
                                <th style=" text-align: right;">Share Total</th>    
                                <th style=" text-align: right;">Amount Balance</th>    
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
                <div style="text-align: center">
                    <a href="<?php echo site_url(current_lang() . '/report_share/share_balance_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo site_url(current_lang() . '/report_share/create_share_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>