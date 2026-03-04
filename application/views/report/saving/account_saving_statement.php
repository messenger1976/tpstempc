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
                    <strong>Account Number : </strong> 
                    <?php 
                    // Get account info to find the member
                    $account_info = $this->finance_model->saving_account_balance($reportinfo->description);
                    
                    // Display account number (prefer old_members_acct if available)
                    $display_account = !empty($account_info->old_members_acct) ? $account_info->old_members_acct : $reportinfo->description;
                    echo htmlspecialchars($display_account, ENT_QUOTES, 'UTF-8');
                    ?><br/>
                    <strong>Account Name : </strong> <?php echo $this->finance_model->saving_account_name($reportinfo->description); ?><br/>
                    <strong>Savings Account Type : </strong> 
                    <?php 
                    if ($account_info && !empty($account_info->account_cat)) {
                        $account_type = $this->finance_model->saving_account_list(null, $account_info->account_cat)->row();
                        echo $account_type ? htmlspecialchars($account_type->name, ENT_QUOTES, 'UTF-8') : '-';
                    } else {
                        echo '-';
                    }
                    ?><br/>
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
                                    <td style="white-space: nowrap;">
                                        <button type="button" class="btn btn-info btn-xs btn-outline btn-ledger" style="margin-right: 3px;" data-receipt="<?php echo $value->receipt; ?>" data-transdate="<?php echo format_date($dt[0], FALSE); ?>" data-description="<?php echo $value->system_comment; ?>"><i class="fa fa-book"></i> Ledger</button>
                                        <button type="button" class="btn btn-success btn-xs btn-outline" data-id="<?php echo $value->id; ?>" data-transdate="<?php echo format_date($dt[0], FALSE); ?>" data-description="<?php echo $value->system_comment; ?>" data-transtype="<?php echo $value->trans_type; ?>" data-paymentmethod="<?php echo $value->paymethod; ?>" data-toggle="modal" data-target="#myModal<?php echo $value->id; ?>"><i class="fa fa-edit"></i> Edit</button>
                                
                                
                                
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
                    <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_report/' . $link_cat); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_statement_export/' . $link_cat . '/' . $id); ?>" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export to Excel</a>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_statement_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ledger Modal -->
<div class="modal inmodal fade" id="ledgerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeIn">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h2 class="modal-title"><i class="fa fa-book modal-icon"></i> Transaction Accounting Entries</h2>
            </div>
            <div class="modal-body">
                <div id="ledger-loading" style="text-align: center; padding: 20px;">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p>Loading accounting entries...</p>
                </div>
                <div id="ledger-content" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Transaction Date:</strong> <span id="ledger-trans-date"></span></p>
                            <p><strong>Description:</strong> <span id="ledger-description"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Receipt No:</strong> <span id="ledger-receipt"></span></p>
                            <p><strong>Reference:</strong> <span id="ledger-reference"></span></p>
                        </div>
                    </div>
                    <hr>
                    <h4>Journal Entries:</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th style="text-align: right;">Debit</th>
                                    <th style="text-align: right;">Credit</th>
                                </tr>
                            </thead>
                            <tbody id="ledger-entries-table">
                                <!-- Entries will be loaded here -->
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold;">
                                    <td colspan="2" style="text-align: right;">Total:</td>
                                    <td style="text-align: right;" id="ledger-total-debit">0.00</td>
                                    <td style="text-align: right;" id="ledger-total-credit">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="ledger-no-entries" style="display: none; padding: 20px; text-align: center;">
                        <p class="text-muted">No accounting entries found for this transaction.</p>
                    </div>
                </div>
                <div id="ledger-error" style="display: none; padding: 20px; text-align: center;">
                    <p class="text-danger"><i class="fa fa-exclamation-triangle"></i> <span id="ledger-error-message"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Handle Ledger button click
    $('.btn-ledger').click(function(){
        var receipt = $(this).data('receipt');
        var transDate = $(this).data('transdate');
        var description = $(this).data('description');
        
        // Show modal
        $('#ledgerModal').modal('show');
        
        // Reset modal content
        $('#ledger-loading').show();
        $('#ledger-content').hide();
        $('#ledger-error').hide();
        $('#ledger-entries-table').empty();
        
        // Fetch ledger entries via AJAX
        $.ajax({
            url: '<?php echo site_url(current_lang() . '/report_saving/get_transaction_ledger_entries'); ?>',
            type: 'POST',
            data: {
                receipt: receipt
            },
            dataType: 'json',
            success: function(response){
                $('#ledger-loading').hide();
                
                if (response.success) {
                    if (response.entries && response.entries.length > 0) {
                        // Populate header information
                        $('#ledger-trans-date').text(transDate);
                        $('#ledger-description').text(description);
                        $('#ledger-receipt').text(receipt);
                        $('#ledger-reference').text(response.entries[0].linkto || 'N/A');
                        
                        // Populate entries table
                        var totalDebit = 0;
                        var totalCredit = 0;
                        var html = '';
                        
                        $.each(response.entries, function(index, entry){
                            var debit = parseFloat(entry.debit) || 0;
                            var credit = parseFloat(entry.credit) || 0;
                            totalDebit += debit;
                            totalCredit += credit;
                            
                            html += '<tr>';
                            html += '<td>' + entry.account + '</td>';
                            html += '<td>' + entry.account_name + '</td>';
                            html += '<td style="text-align: right;">' + (debit > 0 ? number_format(debit, 2) : '') + '</td>';
                            html += '<td style="text-align: right;">' + (credit > 0 ? number_format(credit, 2) : '') + '</td>';
                            html += '</tr>';
                        });
                        
                        $('#ledger-entries-table').html(html);
                        $('#ledger-total-debit').text(number_format(totalDebit, 2));
                        $('#ledger-total-credit').text(number_format(totalCredit, 2));
                        
                        $('#ledger-content').show();
                        $('#ledger-no-entries').hide();
                    } else {
                        $('#ledger-content').show();
                        $('#ledger-entries-table').empty();
                        $('#ledger-no-entries').show();
                    }
                } else {
                    $('#ledger-error-message').text(response.message || 'Failed to load accounting entries.');
                    $('#ledger-error').show();
                }
            },
            error: function(xhr, status, error){
                $('#ledger-loading').hide();
                $('#ledger-error-message').text('An error occurred: ' + error);
                $('#ledger-error').show();
            }
        });
    });
    
    // Number format function
    function number_format(number, decimals) {
        return parseFloat(number).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
});
</script>
