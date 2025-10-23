<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/finance/finance_account_create'); ?>"><?php echo lang('finance_account_create') ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th><?php echo lang('account_no'); ?></th>
                <th><?php echo lang('finance_account_name'); ?></th>
                <th><?php echo lang('finance_account_type'); ?></th>
                <th style="display: none;"><?php echo lang('finance_account_is_header'); ?></th>
                <th><?php echo lang('finance_account_description'); ?></th>
                <th><?php echo lang('actioncolumn'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            if (count($account_chart) > 0) {
                foreach ($account_chart as $key => $value) {
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $value->account ?></td>
                        <td><?php echo $value->name ?></td>
                        <td><?php
                            $account_type = $this->finance_model->account_typelist($value->account_type)->row();
                            echo $account_type->name
                            ?></td>
                        <td style="display: none;"><?php echo ($value->is_header == 1 ? lang('YES'):lang('NO')); ?></td>
                        <td ><?php echo $value->description ?></td>
                        <td>
                            <a href="<?php echo site_url(current_lang() . '/finance/finance_account_edit/' . encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a>
                            <?php if($value->is_header == 1){ ?>
                            &nbsp; &nbsp; <a href="<?php echo '#';//site_url(current_lang() . '/finance/finance_account_create/' . $value->account); ?>"><i class="fa fa-plus"></i> <?php echo lang('btn_add_sub'); ?> </a>
                            <?php } ?>
                        
                        </td>
                    </tr>
                <?php }
            } else {
                ?>

                <tr>
                    <td colspan="7"> <?php echo lang('data_not_found'); ?></td>
                </tr>
<?php } ?>
        </tbody>
    </table>

</div>