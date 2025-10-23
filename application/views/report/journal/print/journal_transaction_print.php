
<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 0px; margin: 0px;">
            <div style="text-align: center;"> 
                <h3 style="padding: 0px; margin: 0px;"><strong><?php echo $journalinfo->type . ' Journal Entries'; ?></strong></h3>
                <h4 style="padding: 0px; margin: 0px;"><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <div>
                <style type="text/css">
                    table.table thead tr th{
                        border-bottom: 1px solid #000;
                    }
                    table.table tbody tr td{
                        padding-left: 10px;
                    }
                    table.table tbody tr td.draw_border{
                        border-top: 1px solid #000;
                    }
                </style>
                <table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Date</th>
                            <th>Account</th>
                            <th style="text-align: right; padding-right: 20px; width: 200px;">Debit</th>
                            <th style="text-align: right; padding-right: 20px; width: 200px;">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $entry_id = 0;
                        $entry_id_track = 0;
                        $start = 0;
                        $year = 0;
                        $year_track = 0;
                        $total_credit = 0;
                        $total_debit = 0;
                        foreach ($transaction as $key => $value) {
                            $total_credit += $value->credit;
                            $total_debit += $value->debit;
                            $new_year = 0;
                            if ($key == 0) {
                                $entry_id = $value->entryid;
                                $entry_id_track = $value->entryid;
                                $year = date('Y', strtotime($value->date));
                                $year_track = date('Y', strtotime($value->date));
                                $new_year = 1;
                            }
                            $entry_id_track = $value->entryid;
                            $year_track = date('Y', strtotime($value->date));

                            if ($year != $year_track) {
                                $new_year = 1;
                            }
                            $class = '';
                            if ($entry_id != $entry_id_track) {
                                $class = 'draw_border';
                                $entry_id = $value->entryid;
                                $entry_id_track = $value->entryid;
                                $start = 0;
                            }
                            if ($new_year == 1) {
                                ?>
                                <tr>
                                    <td colspan="4"><?php echo $year_track ?></td>
                                </tr>  
                            <?php }
                            ?>

                            <tr>
                                <td class="<?php echo $class; ?>" style="text-align: center; padding-left: 15px;"><?php
                                    if ($start == 0) {
                                        echo date('M d', strtotime($value->date));
                                    } else {
                                        echo '';
                                    }
                                    ?></td>
                                <td style="<?php echo ($value->credit > 0 ? 'padding-left:30px;' : ''); ?>" class="<?php echo $class; ?>"> <?php echo $value->name; ?></td>
                                <td class="<?php echo $class; ?>" style="text-align: right; padding-right: 20px;"><?php echo ($value->debit > 0 ? number_format($value->debit, 2) : ''); ?></td>
                                <td class="<?php echo $class; ?>" style="text-align: right; padding-right: 20px;"><?php echo ($value->credit > 0 ? number_format($value->credit, 2) : ''); ?></td>
                            </tr> 
                            <?php
                            if ($key == 0) {
                                $start++;
                            }

                            $start++;
                        }
                        ?>

                        <tr>
                            <td colspan="2" style="border-top: 1px solid #000; border-bottom:  1px solid #000;"></td>
                            <th style="text-align: right; padding-right: 20px; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($total_debit, 2) ?></th>
                            <th style="text-align: right; padding-right: 20px; border-top: 1px solid #000; border-bottom:  1px solid #000;"><?php echo number_format($total_credit, 2) ?></th>
                        </tr>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>