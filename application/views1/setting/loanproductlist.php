

<div class="table-responsive">
    <div style="width: 90%; margin: auto; text-align: right;">
        <?php echo anchor(current_lang() . '/setting/addloan_product/', lang('loanproduct_add'), 'class="btn btn-primary"'); ?>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo 'ID'; ?></th>
                <th><?php echo lang('loanproduct_name'); ?></th>
                <th><?php echo lang('loanproduct_interest_year'); ?></th>
                <th><?php echo lang('loanproduct_interest_method'); ?></th>
                <th><?php echo lang('loanproduct_interval'); ?></th>
                <th><?php echo lang('loanproduct_description'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>

            </tr>

        </thead>
        <tbody>
            <?php
            foreach ($productlist as $key => $value) {
                $interest_method = $this->setting_model->interest_method($value->interest_method)->row();
                $intervalinfo = $this->setting_model->intervalinfo($value->interval)->row();
                ?>

                <tr>

                    <td><?php echo $value->id; ?></td>
                    <td><?php echo $value->name; ?></td>
                    <td><?php echo $value->interest_rate; ?></td>
                    <td><?php echo $interest_method->name; ?></td>
                    <td><?php echo $intervalinfo->name; ?></td>
                     <td><?php echo $value->description; ?></td>
                    <td><?php echo anchor(current_lang() . "/setting/addloan_product/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?></td>


                </tr>
<?php } ?>
        </tbody>

    </table>



</div>
