
<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 0px; margin: auto;">
            <div style="text-align: center;">
                <h3 style="margin: 0px; padding: 0px;"><strong>Balance Sheet</strong></h3>
                <h4 style="margin: 0px; padding: 0px;"><strong><?php echo date('F d, Y', strtotime($reportinfo->fromdate)); ?></strong></h4>
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
                 
                 <br/>
                <table style="width: 100%; table-layout: fixed;" >
                    <tr>
                        <td style="width: 50%; padding-right: 20px; vertical-align: top;">
                            ASSETS<hr/>
                             <table  style=" margin-left: 20px;">
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
                                        <td style="width: 230px;"><?php echo $value->name; ?></td>
                                        <td style=" width: 120px; text-align: right;"><?php echo $diff_sset; ?></td>
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
                                    <td style="width: 230px; padding-left: 20px;"><?php echo 'Total Assets'; ?></td>
                                    <td style="width: 120px; text-align: right; border-top:  1px solid #000; border-bottom: 1px solid #000;"><?php echo $total_asset_label; ?></td>
                                </tr>

                                
                             </table>
                            
                        </td>
                        <td style="width: 50%; padding-left:  20px; vertical-align: top;">
                            LIABILITIES<hr/>
                          <table style=" margin-left: 20px;">
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
                                        <td style="width: 230px;"><?php echo $value->name; ?></td>
                                        <td style=" width: 120px;text-align: right;"><?php echo $diff_sset; ?></td>
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
                                    <td style="width: 230px; padding-left: 20px;"><?php echo 'Total Liabilities'; ?></td>
                                    <td style="width: 120px;text-align: right; border-top:  1px solid #000; border-bottom: 1px solid #000;"><?php echo $total_liabilit_label; ?></td>
                                </tr>
                            </table>
                            
                            <br/>
                            <br/>

                            STOCKHOLDERS' EQUITY<hr/>
                           <table style="margin-left: 20px;">
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
                                        <td style="width: 230px;"><?php echo $value->name; ?></td>
                                        <td style="width: 120px; text-align: right;"><?php echo $diff_sset; ?></td>
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
                                    <td style="width: 230px; padding-left: 20px;"><?php echo 'Total stockholders\' equity'; ?></td>
                                    <td style="text-align: right; width: 120px; border-top:  1px solid #000; border-bottom: 1px solid #000;"><?php echo $total_equity_label; ?></td>
                                </tr>
                            </table>
                           
                            
                        </td>
                    </tr>
                    
                      <tr>
                        <td>
                            <br/>
                            <table style=" margin-left: 20px; padding-top: 10px; border-bottom: 1px solid  #000;">
                             <tr>
                                    <td style="width: 230px; padding-left: 20px;"><?php echo 'Total Assets'; ?></td>
                                    <td style="text-align: right; width: 120px;  border-bottom: 1px solid #000;"><?php echo $total_asset_label; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <br/>
                             <table style=" margin-left: 20px; padding-top: 10px; border-bottom: 1px solid  #000;">
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
                                    <td style="width: 240px; padding-left: 20px;"><?php echo 'Total Liabilities & stockholders\'s equity'; ?></td>
                                    <td style="text-align: right;  width: 120px; border-bottom: 1px solid #000;"> <?php echo $tmp_label; ?></td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr> 
                   
                    
                </table>


        </div>
    </div>
</div>
