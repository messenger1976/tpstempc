<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/supplier/create_order'); ?>"><?php echo lang('supplier_new_order') ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th>Issue Date</th>
                <th>Delivery Date</th>
                <th>Supplier ID</th>
                <th>Supplier Name</th>
                <th>Purchase Amount</th>
                <th>Tax</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            if (count($purchase_order) > 0) {
                foreach ($purchase_order as $key => $value) {
                    $customer_info = $this->supplier_model->supplier_info(null, $value->supplierid)->row();
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo format_date($value->issue_date, false); ?></td>
                        <td><?php echo format_date($value->delivery_date, false); ?></td>
                        <td><?php echo $value->supplierid; ?></td>
                        <td><?php echo $customer_info->name; ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->totalamount, 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->totalamounttax, 2); ?></td>



                        <td>
                            <a href="<?php echo site_url(current_lang() . '/supplier/purchase_order_view/' . encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_view'); ?> </a>


                        </td>
                    </tr>
                <?php
                }
            } else {
                ?>

                <tr>
                    <td colspan="7"> <?php echo lang('data_not_found'); ?></td>
                </tr>
<?php } ?>
        </tbody>
    </table>

</div>