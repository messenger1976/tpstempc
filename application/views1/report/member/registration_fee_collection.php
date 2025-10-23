<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Registration Fee Collections</strong></h1>
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
                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">S/No</th>
                            <th style="text-align: center; widows: 130px;">Date</th>
                            <th>Member ID</th>
                            <th>Member Name</th>
                            <th style="text-align: right; padding-right: 20px; width: 200px;">Amount</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        $k = 1;
                        foreach ($transaction as $key => $value) {
                            $total += $value->amount
                            ?>
                            <tr>

                                <td style="text-align: center;"> <?php echo $k++; ?></td>
                                <td style="text-align: center;"> <?php echo format_date($value->date,false); ?></td>
                                <td> <?php echo $value->member_id; ?></td>
                                <td> <?php echo $value->name; ?></td>
                                <td style="text-align: right;"> <?php echo number_format($value->amount,2); ?></td>


                            </tr> 
                            <?php
                        }
                        ?>

                        <tr>
                            <td colspan="4" style="border-top: 1px solid #000; border-bottom:  1px solid #000;  text-align: right; padding-right: 10px;">Total: </td>
                            <td style="text-align: right; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($total, 2) ?></td>

                        </tr>
                    </tbody>

                </table>
            </div>
            <div style="text-align: center">
                <a href="<?php echo site_url(current_lang() . '/report_member/registration_fee_collection_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                &nbsp; &nbsp; &nbsp; &nbsp;
                <a href="<?php echo site_url(current_lang() . '/report_member/create_member_list_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            </div>
        </div>
    </div>
</div>