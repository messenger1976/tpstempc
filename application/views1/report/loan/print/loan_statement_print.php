<div class="row">
    <div class="col-lg-12">
        <div style=" margin: auto;">
            <div style="text-align: center;"> 
                <h3 style="margin: 0px; padding: 0px;"><strong>Loan Statement</strong></h3>

            </div>
            <div style="">
                <style type="text/css">
                    table tr td{
                        font-size: 13px;
                    }
                    table.table tbody tr td{
                        border: 0px;
                        padding-top: 10px;
                    }
                    table.table thead tr th{
                        border-bottom: 1px solid #000;
                        font-size: 13px;
                    }
                </style>

                <!-- basic information -->
                <?php $memberinfo = $this->member_model->member_basic_info(null, $loaninfo->PID)->row(); ?>

                <h4 style="border-bottom: 1px solid #000;"><?php echo lang('member_basic_info'); ?></h4>

                <table>
                    <tr>
                        <td valign='top' style="width: 120px;"><img  style="width: 100px; height: 100px;" src="<?php echo base_url() ?>uploads/memberphoto/<?php echo $memberinfo->photo; ?>"/></td>
                        <td valign='top'>
                            <table>
                                <tr><td><?php echo lang('member_firstname') ?> : </td><td> <?php echo $memberinfo->firstname; ?></td></tr>
                                <tr><td><?php echo lang('member_middlename') ?> : </td><td> <?php echo $memberinfo->middlename; ?></td></tr>
                                <tr><td><?php echo lang('member_lastname') ?> : </td><td> <?php echo $memberinfo->lastname; ?></td></tr>
                                <tr><td><?php echo lang('member_gender') ?> : </td><td> <?php echo $memberinfo->gender; ?></td></tr>
                                <tr><td><?php echo lang('member_dob') ?> : </td><td> <?php echo format_date($memberinfo->dob, FALSE); ?></td></tr>

                            </table>
                        </td>
                        <td valign='top' style="padding-left: 50px;">
                            <table>
                                <tr><td><?php echo lang('member_pid') ?> : </td><td> <?php echo $memberinfo->PID; ?></td></tr>
                                <tr><td><?php echo lang('member_member_id') ?> : </td><td> <?php echo $memberinfo->member_id; ?></td></tr>
                                <tr><td><?php echo lang('member_join_date') ?> : </td><td> <?php echo format_date($memberinfo->joiningdate, FALSE); ?></td></tr>

                            </table>
                        </td>



                    </tr>
                </table>



                <h4 style="border-bottom: 1px solid #000;"><?php echo lang('loan_info'); ?></h4>


                <table>
                    <tr>
                        <?php
                        $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
                        $interval = $this->setting_model->intervalinfo($loaninfo->interval)->row();
                        ?>
                        <td valign='top'>
                            <table>
                                <tr><td><?php echo lang('loan_product') ?> : </td><td> <?php echo $product->name; ?></td></tr>
                                <tr><td><?php echo lang('loanproduct_interest') ?> : </td><td> <?php echo $loaninfo->rate; ?></td></tr>
                                <tr><td><?php echo lang('loan_installment') ?> : </td><td> <?php echo $loaninfo->number_istallment . ' ' . $interval->name; ?></td></tr>
                                <tr><td><?php echo lang('loan_paysource') ?> : </td><td> <?php echo $loaninfo->pay_source; ?></td></tr>
                            </table>
                        </td>
                        <td valign="top" style="padding-left: 30px;">
                            <table>
                                <tr><td><?php echo lang('loan_applicationdate') ?> : </td><td style="text-align: right;"> <?php echo format_date($loaninfo->applicationdate, FALSE); ?></td></tr>
                                <tr><td><?php echo lang('loan_installment_amount') ?> : </td><td style="text-align: right;"> <?php echo number_format($loaninfo->installment_amount, 2); ?></td></tr>
                                <tr><td><?php echo lang('loan_total_interest') ?> : </td><td style="text-align: right;"> <?php echo number_format($loaninfo->total_interest_amount, 2); ?></td></tr>
                                <tr><td><?php echo lang('loan_applied_amount') ?> : </td><td style="text-align: right;"> <?php echo number_format($loaninfo->basic_amount, 2); ?></td></tr>
                            </table>
                        </td>
                        <td valign="top" style="padding-left: 10px;">
                            <?php echo lang('loan_LID') ?> :  <?php echo $loaninfo->LID; ?>

                        </td>

                    </tr>
                </table>
                <br/>
                <table class="table" cellpadding="0" cellspacing="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 30px; text-align: center;">Install#</th>
                            <th style="width: 120px; text-align: center;">Due date</th>
                            <th style="width: 120px; text-align: center;">Paid date</th>
                            <th style="width: 100px; text-align: right;">Installment Amount</th>
                            <th style="width: 100px; text-align: right;">Interest</th>
                            <th style="width: 100px; text-align: right;">Penalty</th>
                            <th style="width: 100px; text-align: right;">Principle</th>
                            <th style="width: 100px; text-align: right;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7"></td>
                            <td style="text-align: right;"><?php ?></td>
                        </tr>
                        <?php 
                        $total_interest = 0;
                        $total_penalt = 0;
                        $total_install = 0;
                        $total_principle = 0;
                        foreach ($trans as $key => $value) {
                            $total_interest += $value->interest;
                            $total_penalt += $value->penalt;
                            $total_install += $value->amount;
                            $total_principle += $value->principle;
                            ?>
                            <tr >
                                <td style="text-align: right; padding-right: 10px;"><?php echo $value->installment; ?></td>
                                <td style="text-align: center;"><?php echo date('d M, Y', strtotime($value->duedate)) ?></td>
                                <td style="text-align: center; width: 120px;"><?php echo date('d M, Y', strtotime($value->paydate)) ?></td>
                                <td style="text-align: right;"><?php echo number_format($value->amount,2);?></td>
                                <td style="text-align: right;"><?php echo number_format($value->interest,2);?></td>
                                <td style="text-align: right;"><?php echo number_format($value->penalt,2);?></td>
                                <td style="text-align: right;"><?php echo number_format($value->principle,2);?></td>
                                <td style="text-align: right;"><?php echo number_format($value->balance,2);?></td>
                              
                            </tr>
                        <?php } ?>
                            <tr >
                                <td colspan="3" style="border-top: 1px solid #000;"></td>
                                <td style="text-align: right; border-top: 1px solid #000;" ><?php echo number_format($total_install,2);?></td>
                                <td style="text-align: right; border-top: 1px solid #000;" ><?php echo number_format($total_interest,2);?></td>
                                <td style="text-align: right; border-top: 1px solid #000;" ><?php echo number_format($total_penalt,2);?></td>
                                <td style="text-align: right; border-top: 1px solid #000;" ><?php echo number_format($total_principle,2);?></td>
                                <td style="border-top: 1px solid #000;"></td>
                            </tr>
                    </tbody>
                </table>





            </div>
        </div>
    </div>
</div>