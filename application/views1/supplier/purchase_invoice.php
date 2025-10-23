<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/supplier/create_purchase_invoice'); ?>"><?php echo lang('create_purchase_invoice') ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Supplier</th>
                <th>Total</th>
                <th>Amount Paid</th>
                <th>Balance due</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            if (count($purchase_invoice) > 0) {
                foreach ($purchase_invoice as $key => $value) {
                    $customer_info = $this->supplier_model->supplier_info(null, $value->supplierid)->row();
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo format_date($value->issue_date, false); ?></td>
                        <td><?php echo format_date($value->due_date, false); ?></td>
                        <td><?php echo $customer_info->name .' - '.$value->supplierid; ?></td>
                        <td style="text-align: right;"><?php echo number_format(($value->totalamount+$value->totalamounttax), 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format(($value->totalamount+$value->totalamounttax-$value->balance), 2); ?></td>
                        <td style="text-align: right; <?php echo ($value->balance > 0 ? 'color:red;':'') ?>"><?php echo number_format($value->balance, 2); ?></td>
                        <td style="text-align: center;"><?php echo ($value->status == 1 ? 'FULL PAID':($value->status == 0 ? 'NOT PAID':'PARTIAL PAID')); ?></td>
                        



                        <td>
                            <a href="<?php echo site_url(current_lang() . '/supplier/purchase_invoice_view/' . encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_view'); ?> </a>


                        </td>
                    </tr>
                <?php
                }
            } else {
                ?>

                <tr>
                    <td colspan="9"> <?php echo lang('data_not_found'); ?></td>
                </tr>
<?php } ?>
        </tbody>
    </table>

</div>