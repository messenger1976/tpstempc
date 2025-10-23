
<?php echo form_open(current_lang() . "/member/member_current_state", 'class="form-horizontal"'); ?>

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
                <th><?php echo lang('member_pid'); ?></th>
                <th><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('contribution_member_name'); ?></th>
                <th><?php echo lang('member_current_contribution'); ?></th>
                <th><?php echo lang('member_current_mortuary'); ?></th>
                <th><?php echo lang('member_current_share'); ?></th>
                <th><?php echo lang('member_current_loan'); ?></th>
                <th><?php echo lang('member_current_loan_payment'); ?></th>

              
            </tr>

        </thead>
        <tbody>
            <?php
            $total_loan = 0;
            $total_share = 0;
            $total_contribution = 0;
            $total_mortuary = 0;
            $total_loan_paid = 0;
            foreach ($member_state as $key => $value) { 
                
                $share = $this->member_model->member_share_balance($value->PID)->amount;
                if(is_numeric($share)){
                    $total_share+=$share;
                if ($share < 0) {
                    $share_label = '(' . number_format((-1 * $share), 2) . ')';                                
                  } else {
                      $share_label = number_format($share, 2);
                  }
                } else {
                   $share_label = number_format(0, 2); 
                }
                
                
                $contribution = $this->member_model->member_contribution_balance($value->PID)->balance;
                if(is_numeric($contribution)){
                    $total_contribution+=$contribution;
                if ($contribution < 0) {
                    $contribution_label = '(' . number_format((-1 * $contribution), 2) . ')';                                
                  } else {
                      $contribution_label = number_format($contribution, 2);
                  }
                } else {
                   $contribution_label = number_format(0, 2); 
                }
                
                $mortuary = $this->member_model->member_mortuary_balance($value->PID)->balance;
                if(is_numeric($mortuary)){
                    $total_mortuary+=$mortuary;
                    if ($mortuary < 0) {
                        $mortuary_label = '(' . number_format((-1 * $mortuary), 2) . ')';
                    } else {
                        $mortuary_label = number_format($mortuary, 2);
                    }
                } else {
                    $mortuary_label = number_format(0, 2);
                }
                
                
                
                 $loan_row = $this->member_model->member_current_total_loan($value->PID);
                 $loan =  $loan_row->total_loan;
                 $loan_id = $loan_row->LID;
                 
                if(is_numeric($loan)){
                    $total_loan+=$loan;
                if ($loan < 0) {
                    $loan_label = '(' . number_format((-1 * $loan), 2) . ')';                                
                  } else {
                      $loan_label = number_format($loan, 2);
                  }
                } else {
                   $loan_label = number_format(0, 2); 
                }
                
                
                
                
                $loan_paid = $this->member_model->member_current_loan_payment($loan_id)->total_paid_amount;
                if(is_numeric($loan_paid)){
                    $total_loan_paid+=$loan_paid;
                if ($loan_paid < 0) {
                    $loan_paid_label = '(' . number_format((-1 * $loan_paid), 2) . ')';                                
                  } else {
                      $loan_paid_label = number_format($loan_paid, 2);
                  }
                } else {
                   $loan_paid_label = number_format(0, 2); 
                }
                
                
                
                ?>

                <tr>
                    <td><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->firstname." ".$value->middlename." ".$value->lastname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align: right"><?php echo $contribution_label;  ?></td>
                    <td style="text-align: right"><?php echo $mortuary_label;  ?></td>
                    <td style="text-align: right"><?php echo $share_label;  ?></td>
                    <td style="text-align: right"><?php echo $loan_label;  ?></td> 
                    <td style="text-align: right"><?php echo $loan_paid_label;  ?></td> 
                    
                    
                    </tr>
            <?php } ?>
        </tbody>

    </table>
   
    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
</div>
