<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}


?>
<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/customer/customersales_invoice'); ?>"><?php echo 'Create New Sales Invoice' ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Amount Received</th>
                <th>Balance due</th>
                <th>Status</th>
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
                        <td><?php echo format_date($value->due_date, false); ?></td>
                        <td><?php echo $customer_info->name .' - '.$value->customerid; ?></td>
                        <td style="text-align: right;"><?php echo number_format(($value->totalamount+$value->totalamounttax), 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format(($value->totalamount+$value->totalamounttax-$value->balance), 2); ?></td>
                        <td style="text-align: right; <?php echo ($value->balance > 0 ? 'color:red;':'') ?>"><?php echo number_format($value->balance, 2); ?></td>
                        <td style="text-align: center;"><?php echo ($value->status == 1 ? 'PAID':($value->status == 2 ? 'PARTIAL PAID':'NOT PAID')); ?></td>
                        



                        <td>
                            <a href="<?php echo site_url(current_lang() . '/customer/sales_invoice_view/' . encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_view'); ?> </a>


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