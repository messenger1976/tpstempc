<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet"/>
<?php echo form_open(current_lang() . "/loan/loan_repayment", 'class="form-horizontal"'); ?>
<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}
?>
<div class="form-group col-lg-10">
    <div class="col-lg-3">
        <input type="text" class="form-control" name="key" value="<?php echo (isset($_GET['key']) ? htmlspecialchars($_GET['key']) : (isset($_POST['key']) ? htmlspecialchars($_POST['key']) : '')); ?>" placeholder="<?php echo htmlspecialchars(lang('loan_LID') . ' / ' . lang('member_name')); ?>"/>
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>
</div>
<?php echo form_close(); ?>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('loan_LID'); ?></th>
                <th><?php echo lang('member_name'); ?></th>
                <th><?php echo lang('loan_applied_amount'); ?></th>
                <th><?php echo lang('loan_installment'); ?></th>
                <th><?php echo lang('loan_installment_amount'); ?></th>
                <th><?php echo lang('loan_total_interest'); ?></th>
                <th><?php echo lang('loan_total'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $loan_list = isset($loan_list) ? $loan_list : array();
            if (count($loan_list) > 0) {
                foreach ($loan_list as $value) {
                    $info = $this->member_model->member_basic_info(null, $value->PID)->row();
                    $member_display = $info ? ($info->member_id . ' : ' . $info->firstname . ' ' . $info->middlename . ' ' . $info->lastname) : ($value->firstname . ' ' . $value->middlename . ' ' . $value->lastname);
                    $interval = $this->setting_model->intervalinfo(isset($value->interval) ? $value->interval : 1)->row();
                    $interval_desc = $interval ? $interval->description : '';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($value->LID); ?></td>
                <td><?php echo htmlspecialchars($member_display); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->basic_amount, 2); ?></td>
                <td style="text-align: center;"><?php echo (int)$value->number_istallment . ' ' . $interval_desc; ?></td>
                <td style="text-align: right;"><?php echo number_format($value->installment_amount, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->total_interest_amount, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->total_loan, 2); ?></td>
                <td>
                    <a href="<?php echo site_url(current_lang() . '/loan/loan_repayment_entry/' . encode_id($value->LID)); ?>" class="btn btn-primary btn-xs" title="<?php echo htmlspecialchars(lang('loan_repay_btn')); ?>">
                        <i class="fa fa-plus"></i> <?php echo lang('loan_repay_btn'); ?>
                    </a>
                </td>
            </tr>
            <?php
                }
            } else {
            ?>
            <tr>
                <td colspan="8"><?php echo function_exists('lang') ? lang('no_records_found') : 'No records found'; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php echo isset($links) ? $links : ''; ?>
    <div style="margin-right: 20px; text-align: right;"><?php page_selector(); ?></div>
</div>
