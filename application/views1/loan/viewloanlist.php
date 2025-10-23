<?php echo form_open(current_lang() . "/loan/loan_viewlist", 'class="form-horizontal"'); ?>

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

<div class="form-group col-lg-10">

    <div class="col-lg-5">
        <input type="text" class="form-control" name="key" value="<?php echo (isset($_GET['key']) ? $_GET['key'] : ''); ?>"/> 
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>

</div>


<?php echo form_close(); ?>
</div>
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
                <th><?php echo lang('loan_status'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>

            </tr>

        </thead>
        <tbody>
            <?php if (count($loan_list) > 0) {
                foreach ($loan_list as $key => $value) {
                    ?>
            <tr>
                <td><?php echo $value->LID;?></td>
                <td><?php 
                $info = $this->member_model->member_basic_info(null,$value->PID)->row();
                echo $info->member_id.' : '.$info->firstname.' '.$info->middlename.' '.$info->lastname; ?></td>
                <td style="text-align: right;"><?php echo number_format($value->basic_amount,2) ?></td>
                <td style="text-align: center;"><?php 
                $interval = $this->setting_model->intervalinfo($value->interval)->row();
                echo $value->number_istallment.' '.$interval->description; ?></td>
                <td style="text-align: right;"><?php echo number_format($value->installment_amount,2) ?></td>
                <td style="text-align: right;"><?php echo number_format($value->total_interest_amount,2) ?></td>
                <td style="text-align: right;"><?php echo number_format($value->total_loan,2) ?></td>
                <td ><?php echo $value->name; ?></td>
                 <td><?php echo anchor(current_lang() . "/loan/view_indetail/" . encode_id($value->LID), ' <i class="fa fa-folder-open"></i> ' . lang('loan_view_detail'));
                 if($value->edit == 0){
                     echo anchor(current_lang().'/loan/loan_editing/'.encode_id($value->LID),' |  <i class="fa fa-edit"></i> ' . lang('button_edit'));
                 }
                 ?></td>
            </tr>
                <?php }
                ?>

<?php } ?>
        </tbody>

    </table>
       <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
</div>