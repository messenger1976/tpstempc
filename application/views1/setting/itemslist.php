<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/setting/additems_invoice'); ?>"><?php echo lang('salesinvoice_item') ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th><?php echo lang('salesinvoiceitem_code'); ?></th>
                <th><?php echo lang('salesinvoiceitem_name'); ?></th>
                <th><?php echo lang('salesinvoiceitem_price'); ?></th>
                <th><?php echo lang('salesinvoiceitem_account'); ?></th>
                <th><?php echo lang('salesinvoiceitem_taxcode'); ?></th>
                <th><?php echo lang('salesinvoiceitem_type'); ?></th>
                <th><?php echo lang('actioncolumn'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            if (count($item_list) > 0) {
                foreach ($item_list as $key => $value) {
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $value->code ?></td>
                        <td><?php echo $value->name ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->price, 2); ?></td>
                        <td><?php
                            $account = $this->finance_model->account_chart(null, $value->account)->row();
                            if ($account) {
                                echo $account->name;
                            } else {
                                echo '&nbsp;';
                            }
                            ?></td>

                                <td><?php echo $value->taxcode;?></td>
                                <td><?php echo $this->setting_model->invoicetype($value->invoicetype)->row()->name;?></td>
                        <td>
                            <a href="<?php echo site_url(current_lang() . '/setting/additems_invoice/' . encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a>

                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>

                <tr>
                    <td colspan="8"> <?php echo lang('data_not_found'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>