<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>General Ledger Transactions</strong></h1>
                <h4><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
                <?php if (!empty($account_name)) { ?><h4><strong>Account: <?php echo htmlspecialchars($account_name); ?></strong></h4><?php } ?>
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
                            <th>Type</th>
                            <th style="text-align: center;">Date</th>
                            <th style="text-align: center;">#</th>
                            <th>Account</th>
                            <th style="text-align: right; padding-right: 20px; width: 200px;">Debit</th>
                            <th style="text-align: right; padding-right: 20px; width: 200px;">Credit</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $credittotal = 0;
                        $debittotal = 0;
                        foreach ($transaction as $key => $value) {
                            $credittotal += $value->credit;
                            $debittotal += $value->debit;
                            $journal_type = isset($value->trans_comment) ? $value->trans_comment : '';
                            $ref_no = (isset($value->invoiceid) && $value->invoiceid > 0) ? $value->invoiceid : (isset($value->refferenceID) ? $value->refferenceID : '');
                            $ref_url = get_gl_reference_url(isset($value->fromtable) ? $value->fromtable : '', isset($value->refferenceID) ? $value->refferenceID : null);
                            ?>
                            <tr>
                                <td><?php echo $journal_type !== '' ? htmlspecialchars($journal_type) : '&mdash;'; ?></td>
                                <td style="text-align: center;"><?php echo format_date($value->date,false); ?></td>
                                <td style="text-align: center;"><?php
                                    if ($ref_no !== '') {
                                        if ($ref_url !== '') {
                                            echo anchor($ref_url, '#'.$ref_no, array('title' => 'View reference'));
                                        } else {
                                            echo '#'.htmlspecialchars($ref_no);
                                        }
                                    } else {
                                        echo '&mdash;';
                                    }
                                ?></td>
                                <td style="<?php echo ($value->credit > 0 ? 'padding-left:30px;':'');  ?>"> <?php echo htmlspecialchars($value->account); ?> - <?php echo $value->name; ?></td>
                                <td style="text-align: right; padding-right: 20px;"><?php echo ($value->debit > 0 ? number_format($value->debit,2):''); ?></td>
                                <td style="text-align: right; padding-right: 20px;"><?php echo ($value->credit > 0 ? number_format($value->credit,2):''); ?></td>
                                <td><?php echo isset($value->description) ? htmlspecialchars($value->description) : '&mdash;'; ?></td>
                            </tr>
                        <?php }
                        ?>
                            <tr>
                              <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000; text-align: right; padding-right: 20px;"><?php echo number_format($debittotal,2); ?></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;text-align: right; padding-right: 20px;"><?php echo number_format($credittotal,2); ?></td>
                                <td style="border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                         
                            </tr>
                    </tbody>

                </table>
            </div>
            <div style="text-align: center">
                <a href="<?php echo site_url(current_lang().'/report/ledger_trans_print/'.$link_cat.'/'.$id); ?>" class="btn btn-primary">Print</a>
                &nbsp; &nbsp; &nbsp; &nbsp;
                <a href="<?php echo site_url(current_lang().'/report/create_ledger_trans_title/'.$link_cat.'/'.$id); ?>" class="btn btn-primary">Edit</a>
            </div>
        </div>
        
    </div>
</div>