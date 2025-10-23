<?php  
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
             $url = "https://";   
        else  
             $url = "http://";   
        // Append the host(domain name, ip) to the URL.   
        $url.= $_SERVER['HTTP_HOST'];   
        
        // Append the requested resource location to the URL   
        $url.= $_SERVER['REQUEST_URI'];    
          
        //echo $url;  
      ?> 
<!-- Sweet alert -->
<!-- Sweet Alert -->
<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Account Statement</strong></h1>
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
                <div style="margin-left: 100px; margin-bottom: 20px;">
                    <strong>Account Number : </strong> <?php echo $reportinfo->description; ?><br/>
                    <strong>Account Name : </strong> <?php echo $this->finance_model->saving_account_name($reportinfo->description); ?><br/>
                </div>
                <div class="table-responsive" style="overflow: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 130px;">Date</th>  
                                <th>Description</th>  
                                <th style="width: 100px; text-align: right;">Debit [DR]</th>  
                                <th style="width: 100px; text-align: right;">Credit [CR]</th>  
                                <th style="width: 150px; text-align: right;">Balance</th>  
                                <th style="width: 50px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $balance = 0;
                            if (count($transaction) > 0) {

                                $balance = $transaction[0]->credit_total - $transaction[0]->debit_total;
                                ?>
                                <tr>
                                    <td></td>
                                    <td>BROUGHT FORWARD BALANCE</td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: right;"><?php echo number_format($balance, 2); ?></td>
                                </tr>
                                <?php
                            }
                            $credit = 0;
                            $debit = 0;
                            foreach ($transaction as $key => $value) {
                                $dt = explode(' ', $value->trans_date);
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
                                    <td><?php echo $value->system_comment . ' [' . $value->paymethod . '] '. $value->comment; ?></td>
                                    <td style="text-align: right;"><?php echo ($value->debit > 0 ? number_format($value->debit, 2) : ''); ?></td>
                                    <td style="text-align: right;"><?php echo ($value->credit > 0 ? number_format($value->credit, 2) : ''); ?></td>
                                    <td style="text-align: right;"><?php echo number_format($balance, 2); ?></td>
                                    <td><button type="button" class="btn btn-success btn-xs btn-outline" data-id="<?php echo $value->id; ?>" data-transdate="<?php echo format_date($dt[0], FALSE); ?>" data-description="<?php echo $value->system_comment; ?>" data-transtype="<?php echo $value->trans_type; ?>" data-paymentmethod="<?php echo $value->paymethod; ?>" data-toggle="modal" data-target="#myModal<?php echo $value->id; ?>"><i class="fa fa-edit"></i> Edit</button>
                                
                                
                                
<div class="modal inmodal" id="myModal<?php echo $value->id; ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content animated FadeIn">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <!--<i class="fa fa-laptop modal-icon"></i>-->
                <h2 class="modal-title"><i class="fa fa-laptop modal-icon"></i> Edit Entry</h2>
                
            </div>
              

            <?php echo form_open_multipart($url, 'class="form-horizontal" id="querydataform'.$value->id.'"'); ?>
            
            <div class="modal-body">
            
                <input type="hidden" name="trans_id<?php echo $value->id; ?>" id="trans_id<?php echo $value->id; ?>" value="<?php echo $value->id; ?>">
                
                <div class="form-group"><label>Trans Date</label> <input type="text" placeholder="Enter Transaction Date" class="form-control" name="trans_date<?php echo $value->id; ?>" id="trans_date<?php echo $value->id; ?>" value="<?php echo format_date($dt[0], FALSE); ?>"></div>
                <div class="form-group"><label>Description</label> <input type="text" placeholder="Enter Description" class="form-control" name="description<?php echo $value->id; ?>" id="description<?php echo $value->id; ?>" value="<?php echo $value->system_comment; ?>"></div>
                <div class="form-group"><label>Remarks</label> <input type="text" placeholder="Enter Remarks" class="form-control" name="comment<?php echo $value->id; ?>" id="comment<?php echo $value->id; ?>" value="<?php echo $value->comment; ?>"></div>
                <div class="form-group"><label>Payment Method</label> <input type="text" placeholder="Enter Payment Method" class="form-control" name="paymentmethod<?php echo $value->id; ?>" id="paymentmethod<?php echo $value->id; ?>" value="<?php echo $value->paymethod; ?>"></div>
                <div class="form-group"><label>Trans Type</label> <input type="text" placeholder="Enter Transaction Type" class="form-control" name="trans_type<?php echo $value->id; ?>" id="trans_type<?php echo $value->id; ?>" value="<?php echo $value->trans_type; ?>"></div>
                <div class="form-group"><label>Amount</label> <input type="text" placeholder="Enter Amount" class="form-control" name="amount<?php echo $value->id; ?>" id="amount<?php echo $value->id; ?>" value="<?php echo $value->amount; ?>"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <button type="button" class="btn btn-danger" id="btn_close" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>
<script>
    
$(document).ready(function(){
    
    $("#querydataform<?php echo $value->id; ?>").submit(function(e){
        e.preventDefault();
        //var form = $('#querydataform')[0];
        //var fd = new FormData(form);
        
        id = $('#trans_id<?php echo $value->id; ?>').val();
        //trans_date = $(this).data('trans_date<?php echo $value->id; ?>');
        //description = $(this).data('description<?php echo $value->id; ?>');
        //paymentmethod = $(this).data('paymentmethod<?php echo $value->id; ?>');
        //trans_type = $(this).data('trans_type<?php echo $value->id; ?>');
        //amount = $(this).data('amount<?php echo $value->id; ?>');
        trans_date = $('#trans_date<?php echo $value->id; ?>').val();
        description = $('#description<?php echo $value->id; ?>').val();
        comment = $('#comment<?php echo $value->id; ?>').val();
        paymentmethod = $('#paymentmethod<?php echo $value->id; ?>').val();
        trans_type = $('#trans_type<?php echo $value->id; ?>').val();
        amount = $('#amount<?php echo $value->id; ?>').val();
        
        
        //location.href = "<?php echo $url?>";

        $.ajax({
            url: '<?php echo site_url(current_lang() . '/report_saving/saving_edit_entry/'); ?>',
            type: 'POST',
            data:{
                id: id,
                trans_date:trans_date,
                description:description,
                paymentmethod:paymentmethod,
                trans_type:trans_type,
                amount:amount,
                comment:comment

                //posted : postedvalue
            },
            success:function(data){
                var json = JSON.parse(data);
                /*if(json['posted'].toString() == '1'){
                    $('#posted'+id).html('Yes');
                }else{
                    $('#posted'+id).html('No');
                }*/
                //$('#posted'+id).data('id',json['posted'].toString());
                //$('#posted'+id).data('value',json['posted'].toString());
                //swal('Posted!', 'Save Successfully','success');
                location.href = "<?php echo $url?>";
                //alert(json['success'].toString());
            }
        });
        
    });
    
});


</script>                                  
                                
                                
                                </td>
                                
                                </tr>




                            <?php }
                            ?>
                            <tr>
                                <td colspan="2" style="border-top: 1px solid #000;border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000;border-bottom:  1px solid #000; text-align: right;"><?php echo number_format($debit, 2); ?></td>
                                <td style="border-top: 1px solid #000;border-bottom:  1px solid #000; text-align: right;"><?php echo number_format($credit, 2); ?></td>
                                <td colspan="2" style="border-top: 1px solid #000;border-bottom:  1px solid #000;"></td>
                            </tr>
                        </tbody>


                    </table>
                    <div style="text-align: right; font-size: 25px; font-weight: bold;"> Balance : <?php echo number_format($balance,2);?></div>
                </div>
                <div style="text-align: center;  padding-top: 30px;">
                    <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_statement_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>




