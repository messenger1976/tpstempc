<!-- basic information -->
<?php $memberinfo = $this->member_model->member_basic_info(null, $loaninfo->PID)->row(); ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?php echo lang('member_basic_info'); ?></h4>
    </div>
    <div class="panel-body">
        <table>
            <tr>
                <td><img  style="width: 100px; height: 100px;" src="<?php echo base_url() ?>uploads/memberphoto/<?php echo $memberinfo->photo; ?>"/></td>
                <td valign='top'><div style="padding-left: 30px;">
                        <strong><?php echo lang('member_firstname') ?> : </strong> <?php echo $memberinfo->firstname; ?><br/>
                        <strong><?php echo lang('member_middlename') ?> : </strong> <?php echo $memberinfo->middlename; ?><br/>
                        <strong><?php echo lang('member_lastname') ?> : </strong> <?php echo $memberinfo->lastname; ?><br/>
                        <strong><?php echo lang('member_gender') ?> : </strong> <?php echo $memberinfo->gender; ?><br/>
                        <strong><?php echo lang('member_dob') ?> : </strong> <?php echo format_date($memberinfo->dob, FALSE); ?><br/>
                    </div></td>
                <td valign="top"><div style="padding-left: 100px;">
                        <strong><?php echo lang('member_pid') ?> : </strong> <?php echo $memberinfo->PID; ?><br/>
                        <strong><?php echo lang('member_member_id') ?> : </strong> <?php echo $memberinfo->member_id; ?><br/>
                        <strong><?php echo lang('member_join_date') ?> : </strong> <?php echo format_date($memberinfo->joiningdate, FALSE); ?><br/>
                    </div></td>



            </tr>
        </table>
    </div>
</div>



<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?php echo lang('loan_info'); ?></h4>
    </div>
    <div class="panel-body">
        <table>
            <tr>
                <?php
                $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
                $interval = $this->setting_model->intervalinfo($loaninfo->interval)->row();
                ?>
                <td valign='top'><div style="padding-left: 30px;">
                        <strong><?php echo lang('loan_product') ?> : </strong> <?php echo $product->name; ?><br/>
                        <strong><?php echo lang('loanproduct_interest') ?> : </strong> <?php echo $loaninfo->rate; ?><br/>
                        <strong><?php echo lang('loan_installment') ?> : </strong> <?php echo $loaninfo->number_istallment . ' ' . $interval->name; ?><br/>
                        <strong><?php echo lang('loan_paysource') ?> : </strong> <?php echo $loaninfo->pay_source; ?><br/>

                    </div></td>
                <td valign="top"><div style="padding-left: 40px;">
                        <strong><?php echo lang('loan_applicationdate') ?> : </strong> <?php echo format_date($loaninfo->applicationdate, FALSE); ?><br/>
                        <strong><?php echo lang('loan_installment_amount') ?> : </strong> <?php echo number_format($loaninfo->installment_amount, 2); ?><br/>
                        <strong><?php echo lang('loan_total_interest') ?> : </strong> <?php echo number_format($loaninfo->total_interest_amount, 2); ?><br/>
                        <strong><?php echo lang('loan_applied_amount') ?> : </strong> <?php echo number_format($loaninfo->basic_amount, 2); ?><br/>

                    </div></td>
                <td valign="top"><div style="padding-left: 40px;">
                        <strong><?php echo lang('loan_LID') ?> : </strong> <?php echo $loaninfo->LID; ?><br/>

                    </div></td>

            </tr>
        </table>
    </div>
</div>


<div class="table-responsive">


    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Due Date</th>
                <th>Paid Date</th>
                <th>Installment Amount</th>
                <th>Interest</th>
                <th>Penalty</th>
                <th>Principle</th>
                <th>Balance</th>


            </tr>

        </thead>
        <tbody>
            <?php foreach ($trans as $key => $value) { ?>
            <tr>
                <td style="text-align: center;"><?php echo $value->installment; ?></td>
                <td style="text-align: center;"><?php echo format_date($value->duedate,false); ?></td>
                <td style="text-align: center;"><?php echo format_date($value->paydate,false); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->amount,2); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->interest,2); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->penalt,2); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->principle,2); ?></td>
                <td style="text-align: right;"><?php echo number_format($value->balance,2); ?></td>
            </tr>
            <?php }
            ?>


        </tbody>
    </table>
</div>
 <div style="text-align: center">
     <a href="<?php echo site_url(current_lang().'/report_loan/loan_statement_print/?loan_id='.$loaninfo->LID); ?>" class="btn btn-primary">Print</a>
                  </div>




