<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Balance Sheet</strong></h1>
                <h4><strong><?php echo date('F d, Y', strtotime($reportinfo->fromdate)); ?></strong></h4>
            </div>
            <div style="padding-top: 20px;">
                <style type="text/css">
                    table.table tbody tr td{
                        border: 0px;
                    }
                    table tr td{
                        padding-top: 5px;
                    }
                </style>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 50%; padding-right: 20px; vertical-align: top;">
                            <div style="border-bottom: 1px solid #000; font-size: 19px; ">ASSETS</div> 
                            <br/>
                            <table style="width: 96%; margin-left: 20px;">
                                <?php
                                $assets = $this->report_model->get_balance_sheet_data($reportinfo->fromdate, 10);
                                $total_asset = 0;
                                foreach ($assets as $key => $value) {
                                    $diff = $value->debit - $value->credit;
                                    $diff_sset = 0;
                                    if ($diff < 0) {
                                        $diff_sset = '(' . number_format((0 - $diff), 2) . ')';
                                        $total_asset -= (0 - $diff);
                                    } else {
                                        $diff_sset = number_format($diff, 2);
                                        $total_asset += $diff;
                                    }
                                    ?>
                                    <tr>
                                        <td style="width: 70%;"><?php echo $value->name; ?></td>
                                        <td style="text-align: right;"><?php echo $diff_sset; ?></td>
                                    </tr>
                                <?php }
                                  $total_asset_label=0;
                                if($total_asset < 0){
                                   $total_asset_label =  '(' . number_format((0 - $total_asset), 2) . ')';
                                }else{
                                   $total_asset_label = number_format($total_asset,2); 
                                }
                                ?>
                                <tr>
                                    <td style="width: 70%; text-indent: 40px;"><?php echo 'Total Assets'; ?></td>
                                    <td style="text-align: right; border-top:  1px solid #000; border-bottom: 1px solid #000;"><?php echo $total_asset_label; ?></td>
                                </tr>

                            </table>
                        </td>
                        <td style="width: 50%; padding-left:  20px; vertical-align: top;">
                            <div style="border-bottom: 1px solid #000; font-size: 19px;">LIABILITIES</div> 
                            <br/>
                            <table style="width: 96%; margin-left: 20px;">
                                <?php
                                $liabilities = $this->report_model->get_balance_sheet_data($reportinfo->fromdate, 20);
                                $total_liabilities = 0;

                                foreach ($liabilities as $key => $value) {
                                    $diff = $value->credit - $value->debit;
                                    $diff_sset = 0;
                                    if ($diff < 0) {
                                        $diff_sset = '(' . number_format((0 - $diff), 2) . ')';
                                        $total_liabilities -= (0 - $diff);
                                    } else {
                                        $diff_sset = number_format($diff, 2);
                                        $total_liabilities += $diff;
                                    }
                                    ?>
                                    <tr>
                                        <td style="width: 70%;"><?php echo $value->name; ?></td>
                                        <td style="text-align: right;"><?php echo $diff_sset; ?></td>
                                    </tr>
                                <?php }
                                
                                $total_liabilit_label=0;
                                if($total_liabilities < 0){
                                   $total_liabilit_label =  '(' . number_format((0 - $total_liabilities), 2) . ')';
                                }else{
                                   $total_liabilit_label = number_format($total_liabilities,2); 
                                }
                                ?>
                                <tr>
                                    <td style="width: 70%; text-indent: 40px;"><?php echo 'Total Liabilities'; ?></td>
                                    <td style="text-align: right; border-top:  1px solid #000; border-bottom: 1px solid #000;"><?php echo $total_liabilit_label; ?></td>
                                </tr>
                            </table>

                            <br/>
                            <br/>

                            <div style="border-bottom: 1px solid #000; font-size: 19px;">STOCKHOLDERS' EQUITY</div> 
                            <br/>
                            <table style="width: 96%; margin-left: 20px;">
                                <?php
                                $equaty = $this->report_model->get_balance_sheet_data($reportinfo->fromdate, 30);
                                $total_equaty = 0;

                                foreach ($equaty as $key => $value) {
                                    $diff = $value->credit - $value->debit;
                                    $diff_sset = 0;
                                    if ($diff < 0) {
                                        $diff_sset = '(' . number_format((0 - $diff), 2) . ')';
                                        $total_equaty -= (0 - $diff);
                                    } else {
                                        $diff_sset = number_format($diff, 2);
                                        $total_equaty += $diff;
                                    }
                                    ?>
                                    <tr>
                                        <td style="width: 70%;"><?php echo $value->name; ?></td>
                                        <td style="text-align: right;"><?php echo $diff_sset; ?></td>
                                    </tr>
                                <?php }
                                
                                  $total_equity_label=0;
                                if($total_equaty < 0){
                                   $total_equity_label =  '(' . number_format((0 - $total_equaty), 2) . ')';
                                }else{
                                   $total_equity_label = number_format($total_equaty,2); 
                                }
                                ?>
                                <tr>
                                    <td style="width: 70%; text-indent: 40px;"><?php echo 'Total stockholders\' equity'; ?></td>
                                    <td style="text-align: right; border-top:  1px solid #000; border-bottom: 1px solid #000;"><?php echo $total_equity_label; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <br/>
                            <br/>
                            <br/>
                            <table style="width: 96%; margin-left: 20px; padding-top: 30px;">
                             <tr>
                                    <td style="width: 70%; text-indent: 40px;"><?php echo 'Total Assets'; ?></td>
                                    <td style="text-align: right;  border-bottom: 1px double #000;"><div style="border-bottom: 1px double #000;margin-bottom: 2px;"><?php echo $total_asset_label; ?></div></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <br/>
                            <br/>
                            <br/>
                             <table style="width: 96%; margin-left: 20px;">
                                 <?php
                                 $tmp = $total_liabilities + $total_equaty;
                                 $tmp_label = 0;
                                 if($tmp < 0){
                                     $tmp_label =  '(' . number_format((0 - $tmp), 2) . ')';
                                 }else{
                                    $tmp_label = number_format($tmp,2); 
                                 }
                                 ?>
                             <tr>
                                    <td style="width: 70%; text-indent: 40px;"><?php echo 'Total Liabilities & stockholders\'s equity'; ?></td>
                                    <td style="text-align: right;  border-bottom: 1px double #000;"> <div style="border-bottom: 1px double #000;margin-bottom: 2px;"><?php echo $tmp_label; ?></div></td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                </table>




            </div>
        </div>
    </div>
      <div style="text-align: center">
                <a href="<?php echo site_url(current_lang() . '/report/ledger_balance_sheet_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                &nbsp; &nbsp; &nbsp; &nbsp;
                <a href="<?php echo site_url(current_lang() . '/report/create_ledger_trans_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            </div>
</div>