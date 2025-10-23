<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>
<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Member Mortuary Statement</strong></h1>
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
                    <strong>Member ID : </strong> <?php echo $id; ?><br/>
                    <strong>Member Name : </strong> <?php echo $this->member_model->member_name($id); ?><br/>
                </div>
                <div class="table-responsive" style="overflow: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 130px;">Date</th>  
                                <th>Description</th>  
                                <th style="width: 190px; text-align: right;">Debit [DR]</th>  
                                <th style="width: 190px; text-align: right;">Credit [CR]</th>  
                                <th style="width: 190px; text-align: right;">Balance</th>
                                <th style="width: 50px; text-align: center;">Action</th>  
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $balance = 0;
                            $previous_trans = $this->report_model->mortuary_statement_previous($reportinfo->fromdate, $id);
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
                <div class="form-group"><label>System Comment</label> <input type="text" placeholder="Enter Description" class="form-control" name="description<?php echo $value->id; ?>" id="description<?php echo $value->id; ?>" value="<?php echo $value->system_comment; ?>" readonly></div>
                <div class="form-group"><label>Remarks</label> <input type="text" placeholder="Enter Remarks" class="form-control" name="comment<?php echo $value->id; ?>" id="comment<?php echo $value->id; ?>" value="<?php echo $value->comment; ?>"></div>
                <div class="form-group"><label>Payment Method</label> <input type="text" placeholder="Enter Payment Method" class="form-control" name="paymentmethod<?php echo $value->id; ?>" id="paymentmethod<?php echo $value->id; ?>" value="<?php echo $value->paymethod; ?>"></div>
                <div class="form-group"><label>Trans Type</label> <input type="text" placeholder="Enter Transaction Type" class="form-control" name="trans_type<?php echo $value->id; ?>" id="trans_type<?php echo $value->id; ?>" value="<?php echo $value->trans_type; ?>"></div>
                <div class="form-group"><label>Amount</label> <input type="text" placeholder="Enter Amount" class="form-control" name="amount<?php echo $value->id; ?>" id="amount<?php echo $value->id; ?>" value="<?php echo ($value->debit > 0 ? number_format($value->debit, 2) : number_format($value->credit, 2)); ?>"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                
            </div>
            </form>
        </div>
    </div>
</div>
<script>
    
$(document).ready(function(){
    
    $("#querydataform<?php echo $value->id; ?>").submit(function(e){
        e.preventDefault();
        
        
        id = $('#trans_id<?php echo $value->id; ?>').val();
        
        trans_date = $('#trans_date<?php echo $value->id; ?>').val();
        description = $('#description<?php echo $value->id; ?>').val();
        comment = $('#comment<?php echo $value->id; ?>').val();
        paymentmethod = $('#paymentmethod<?php echo $value->id; ?>').val();
        trans_type = $('#trans_type<?php echo $value->id; ?>').val();
        amount = $('#amount<?php echo $value->id; ?>').val();
        
        
        

        $.ajax({
            url: '<?php echo site_url(current_lang() . '/report_mortuary/saving_edit_entry/'); ?>',
            type: 'POST',
            data:{
                id: id,
                trans_date:trans_date,
                description:description,
                paymentmethod:paymentmethod,
                trans_type:trans_type,
                amount:amount,
                comment:comment
                
            },
            success:function(data){
                var json = JSON.parse(data);
                
                location.href = "<?php echo $url?>";
                
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
                    <div style="text-align: right; font-size: 25px; font-weight: bold;"> Balance : <?php echo number_format($balance, 2); ?></div>
                </div>
                <div style="text-align: center">
                    <a href="#"  onclick="setTimeout(function(){var ww = window.open(window.location, '_self'); ww.close(); }, 500);" class="btn btn-primary">Close</a>
                    <?php echo anchor('#',  'Process Balances','class="btn btn-warning" id="btnprocessbalances"'); ?>
                   
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('#btnprocessbalances').on('click', function(e){
            e.preventDefault();
            $('#ibox-main').children('.ibox-content').addClass('sk-loading');
            $("body").css("cursor", "wait");
            recomputebalances();
        })
    })   
    async function recomputebalances() {
        let response = await fetch('<?php echo site_url(current_lang() . '/mortuary/recomputebalancesindividual/'.$id); ?>');
		let totalrecdata = await response.json();
		success = totalrecdata.success;
        message = totalrecdata.message;
        await new Promise((resolve, reject) => setTimeout(resolve, 1000));
        $('#ibox-main').children('.ibox-content').removeClass('sk-loading');
        $("body").css("cursor", "default");
        swal({
            title: "Good job!",
            text: "All mortuary balances are successfully process!",
            icon: "success",
            button: "Close",
        });
        return true;
    } 
</script>