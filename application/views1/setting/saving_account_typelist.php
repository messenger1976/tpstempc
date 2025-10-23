<div class="table-responsive">
    <div style="width: 90%; margin: auto; text-align: right;">
    <?php echo anchor(current_lang().'/setting/saving_account_typecreate/',  lang('create_saving_account'),'class="btn btn-primary"'); ?>
</div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th style="width: 150px;"><?php echo lang('account_no'); ?></th>
                <th><?php echo lang('account_name'); ?></th>
                <th><?php echo lang('account_description'); ?></th>
                <th><?php echo lang('account_min_amount'); ?></th>
                <th><?php echo lang('account_charge'); ?></th>
                <th><?php echo lang('actioncolumn'); ?></th>
            </tr>
        </thead>
        <tbody>
           <?php
           $i=1;
           foreach ($saving_acc_list as $key => $value) { ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $value->account ?></td>
                <td><?php echo $value->name ?></td>
                <td><?php echo $value->description ?></td>
                <td style="text-align: right;"><?php echo number_format($value->min_amount,2); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->month_fee,2) ?></td>
                <td><a href="<?php echo site_url(current_lang().'/setting/saving_account_typecreate/'.  encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a> &nbsp; </td>
            </tr>
           <?php }
           ?>
        </tbody>
    </table>

</div>