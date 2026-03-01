
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
                <th><?php echo lang('member_current_share'); ?></th>
                <th><?php echo lang('member_current_loan'); ?></th>
                <th><?php echo lang('member_current_loan_payment'); ?></th>
                <th><?php echo lang('member_current_savings'); ?></th>

              
            </tr>

        </thead>
        <tbody>
            <?php
            $total_loan = 0;
            $total_share = 0;
            $total_contribution = 0;
            $total_loan_paid = 0;
            $total_savings = 0;
            foreach ($member_state as $key => $value) { 
                
                $share_row = $this->member_model->member_share_balance_by_member($value->PID, $value->member_id);
                $share = 0;
                if ($share_row && (isset($share_row->amount) || isset($share_row->remainbalance))) {
                    $amt = isset($share_row->amount) && is_numeric($share_row->amount) ? floatval($share_row->amount) : 0;
                    $rem = isset($share_row->remainbalance) && is_numeric($share_row->remainbalance) ? floatval($share_row->remainbalance) : 0;
                    $share = $amt + $rem;
                }
                if(is_numeric($share)){
                    $total_share += $share;
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
                
                $loan_row = $this->member_model->member_current_total_loan($value->PID);
                $loan = isset($loan_row->total_loan) ? $loan_row->total_loan : null;
                $loan_id = isset($loan_row->LID) ? $loan_row->LID : null;
                
                $loan_bb_sum = $this->member_model->member_loan_beginning_balances_sum($value->member_id);
                if (is_numeric($loan)) {
                    $loan = floatval($loan) + floatval($loan_bb_sum);
                } else {
                    $loan = $loan_bb_sum;
                }
                
                if ($loan_id !== null && $loan_id !== '') {
                    $loan_paid_row = $this->member_model->member_current_loan_payment($loan_id);
                    $loan_paid = isset($loan_paid_row->total_paid_amount) ? $loan_paid_row->total_paid_amount : null;
                } else {
                    $loan_paid = null;
                }
                
                if(is_numeric($loan)){
                    $total_loan += $loan;
                if ($loan < 0) {
                    $loan_label = '(' . number_format((-1 * $loan), 2) . ')';                                
                  } else {
                      $loan_label = number_format($loan, 2);
                  }
                } else {
                   $loan_label = number_format(0, 2); 
                }
                
                if(is_numeric($loan_paid)){
                    $total_loan_paid += $loan_paid;
                if ($loan_paid < 0) {
                    $loan_paid_label = '(' . number_format((-1 * $loan_paid), 2) . ')';                                
                  } else {
                      $loan_paid_label = number_format($loan_paid, 2);
                  }
                } else {
                   $loan_paid_label = number_format(0, 2); 
                }
                
                $saving = $this->finance_model->saving_account_balance_PID($value->PID, $value->member_id);
                $savings_balance = ($saving && is_numeric($saving->balance)) ? $saving->balance : 0;
                $total_savings += $savings_balance;
                if ($savings_balance < 0) {
                    $savings_label = '(' . number_format((-1 * $savings_balance), 2) . ')';
                } else {
                    $savings_label = number_format($savings_balance, 2);
                }
                
                
                ?>

                <tr>
                    <td><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->firstname." ".$value->middlename." ".$value->lastname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align: right"><?php echo $contribution_label;  ?></td>
                    <td style="text-align: right"><?php echo $share_label;  ?></td>
                    <td style="text-align: right"><?php echo $loan_label;  ?></td> 
                    <td style="text-align: right"><?php echo $loan_paid_label;  ?></td> 
                    <td style="text-align: right"><?php echo $savings_label;  ?></td> 
                    
                    
                    </tr>
            <?php } ?>
        </tbody>

    </table>
   
    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
</div>
