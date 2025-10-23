<style type="text/css">
    table.table {
        width: 100%;
         border-right:  1px solid #000;
         font-size: 12px;
    }
    table.table tbody tr td{
        border-left:  1px solid #000;
        border-bottom:   1px solid #000;
        padding: 5px;
    } 
    table.table thead tr th{
         border-left:  1px solid #000;
        border-bottom:   1px solid #000;
        border-top:   1px solid #000;
        padding: 5px;
    } 
</style>
<div class="row">
    <div class="col-lg-12">
        <div style="padding: 0px; margin: 0px;">
            <div style="text-align: center;"> 
                <h3  style="padding: 0px; margin: 0px;"><strong>Member List</strong></h3>
                <h4  style="padding: 0px; margin: 0px;"><strong>Joining  from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <div style="padding-top: 20px;">
                <div class="table-responsive" style="overflow: auto;">
                    <table cellspacing="0" cellpadding="0" class="table" >
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">S/No</th>
                                <?php
                                // $all_column
                                foreach ($column as $key => $value) {
                                    ?>
                                <th><?php echo $all_column[$value]; ?></th>
                                <?php }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i=1;
                            foreach ($transaction as $key => $value) { ?>
                                <tr>
                                    <td style="text-align: right; padding-right: 5px;"><?php echo $i++; ?></td>
                                    <?php foreach ($column as $key1 => $value1) { ?>
                                    <td><?php echo  ($value1 == 'members.dob' || $value1 == 'members.joiningdate' ? format_date($value->{str_replace('.', '', $value1)},false):$value->{str_replace('.', '', $value1)}); ?></td>
                                    <?php }
                                    ?>
                                </tr>
                                <?php }
                                ?>
                        </tbody>


                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>