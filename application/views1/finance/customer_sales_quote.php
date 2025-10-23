<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/customer/customersales_quote'); ?>"><?php echo 'Create New Sales Quote' ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th>Issue Date</th>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Quote Amount</th>
                <th>Tax</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            if (count($sales_quote) > 0) {
                foreach ($sales_quote as $key => $value) {
                    $customer_info = $this->customer_model->customer_info(null, $value->customerid)->row();
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo format_date($value->issue_date, false); ?></td>
                        <td><?php echo $value->customerid; ?></td>
                        <td><?php echo $customer_info->name; ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->totalamount, 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->totalamounttax, 2); ?></td>



                        <td>
                            <a href="<?php echo site_url(current_lang() . '/customer/sales_quote_view/' . encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_view'); ?> </a>


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