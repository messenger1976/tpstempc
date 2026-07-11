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
                <th style="text-align: right;"><?php echo lang('account_interest_rate'); ?> (%)</th>
                <th><?php echo lang('interest_frequency'); ?></th>
                <th><?php echo lang('interest_basis'); ?></th>
                <th style="text-align: right;"><?php echo lang('interest_min_balance'); ?></th>
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
                <td style="text-align: right;"><?php echo number_format((float)$value->interest_rate, 2); ?></td>
                <td><?php
                    $frequency = isset($value->interest_frequency) ? strtoupper($value->interest_frequency) : 'NONE';
                    if ($frequency == 'MONTHLY') { echo lang('interest_frequency_monthly'); }
                    else if ($frequency == 'QUARTERLY') { echo lang('interest_frequency_quarterly'); }
                    else { echo lang('interest_frequency_none'); }
                ?></td>
                <td><?php
                    $basis = isset($value->interest_basis) ? strtoupper($value->interest_basis) : 'ADB';
                    if ($basis == 'LOWEST') { echo lang('interest_basis_lowest'); }
                    else if ($basis == 'EOP') { echo lang('interest_basis_eop'); }
                    else { echo lang('interest_basis_adb'); }
                ?></td>
                <td style="text-align: right;"><?php echo number_format(isset($value->interest_min_balance) ? (float)$value->interest_min_balance : 0, 2); ?></td>
                <td><a href="<?php echo site_url(current_lang().'/setting/saving_account_typecreate/'.  encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a> &nbsp; </td>
            </tr>
           <?php }
           ?>
        </tbody>
    </table>

</div>