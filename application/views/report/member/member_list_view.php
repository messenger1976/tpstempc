<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Member List</strong></h1>
                <h4><strong>Joining  from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <div style="padding-top: 20px;">
                <div class="table-responsive" style="overflow: auto;">
                    <table class="table table-condensed table-bordered">
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
                                    <td><?php echo $i++; ?></td>
                                    <?php foreach ($column as $key1 => $value1) { ?>
                                    <td><?php echo ($value1 == 'members.dob' || $value1 == 'members.joiningdate' ? format_date($value->{str_replace('.', '', $value1)},false):$value->{str_replace('.', '', $value1)}); ?></td>
                                    <?php }
                                    ?>
                                </tr>
                                <?php }
                                ?>
                        </tbody>


                    </table>
                </div>
            </div>
            <div style="text-align: center">
                <a href="<?php echo site_url(current_lang() . '/report_member/member_list_print/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Print</a>
                &nbsp; &nbsp; &nbsp; &nbsp;
                <a href="<?php echo site_url(current_lang() . '/report_member/member_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            </div>
        </div>
    </div>
</div>