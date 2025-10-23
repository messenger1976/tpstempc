<style type="text/css">
    table tr td{
        line-height: 20px;

    }
</style>
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

<div class="col-lg-12">
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
                    <td valign="top"><div style="padding-left: 40px;">
                            <strong><?php echo lang('member_pid') ?> : </strong> <?php echo $memberinfo->PID; ?><br/>
                            <strong><?php echo lang('member_member_id') ?> : </strong> <?php echo $memberinfo->member_id; ?><br/>
                            <strong><?php echo lang('member_join_date') ?> : </strong> <?php echo format_date($memberinfo->joiningdate, FALSE); ?><br/>
                        </div></td>

                    <td valign="top"><div style="padding-left: 40px;">
                            <?php $contribution = $this->contribution_model->contribution_balance($loaninfo->PID, $loaninfo->member_id); ?>
                            <strong><?php echo lang('contribution_balance') ?> : </strong> <?php echo ($contribution ? number_format($contribution->balance, 2) : ''); ?><br/>
                            <?php $share_data = $this->share_model->share_member_info($loaninfo->PID, $loaninfo->member_id); ?>
                            <strong><?php echo lang('share_balance') ?> : </strong> <?php echo ($share_data ? number_format(($share_data->amount + $share_data->remainbalance), 2) : ''); ?><br/>
                            <?php $saving = $this->finance_model->saving_account_balance_PID($loaninfo->PID, $loaninfo->member_id); ?>
                            <strong><?php echo lang('saving_balance') ?> : </strong> <?php echo ($saving ? number_format($saving->balance, 2) : ''); ?><br/>
                        </div></td>

                </tr>
            </table>
        </div>
    </div>

    <!-- basic loan information -->

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
<strong><?php echo lang('loan_total') ?> : </strong> <?php echo number_format($loaninfo->total_loan, 2); ?><br/>

                        </div></td>

                </tr>
            </table>
        </div>
    </div>
    
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><?php echo 'Amount Allowed'; ?></h4>
        </div>
        <div class="panel-body">
            <table>
                <tr>
                    <td valign='top'><div style="padding-left: 30px;">
                            <strong><?php echo 'Normal Maximum loan ' ?> : </strong> <?php $max = $product->loan_security_contribution_times * (isset($contribution->balance) ? $contribution->balance : 0);
                    echo number_format($max, 2);
                    ?><br/>
                            <strong><?php echo 'Opening Principles ' ?> : </strong> <?php
                            $open_loan = $this->db->query("SELECT * FROM loan_contract WHERE PID='$loaninfo->PID' AND approval=4")->result();

                            $principles = 0;
                            $amount_paid = 0;
                            foreach ($open_loan as $key => $value) {
                                $amount_paid += $this->db->query("SELECT SUM('amount') as amount FROM loan_repayment_receipt WHERE LID='$value->LID'")->row()->amount;
                                $principles += $value->basic_amount;
                            }
                            $open_principle = $principles - $amount_paid;
                            echo number_format($open_principle, 2);
                            ?><br/>
                            <strong><?php echo 'Maximum Loan allowed is:' ?> : </strong> <?php
                            echo number_format(($max - $open_principle), 2);
                            ?><br/>
                        </div>
                    </td>

                </tr>
            </table>
        </div>
    </div>
    <!-- basic loan information -->
    <?php $declaration = $this->loan_model->get_declaration($loaninfo->LID); ?>
    <?php $supporting_doc = $this->loan_model->get_supporting_doc($loaninfo->LID); ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><?php echo lang('loan_info_header'); ?></h4>
        </div>
        <div class="panel-body">
            <div class="col-lg-12">
                <div class="col-lg-5"> <strong><?php echo lang('loan_security_declaration'); ?></strong> <br/> <?php echo $declaration->declaration; ?></div>
                <div class="col-lg-6"> <strong><?php echo lang('loan_info_sopport'); ?></strong> <br/>
                    <?php if (count($supporting_doc) > 0) { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo lang('loan_supporting_document_comment'); ?></th>
                                        <th><?php echo lang('loan_supporting_document_doc'); ?></th>

                                    </tr>

                                </thead>
                                <tbody>
                                    <?php foreach ($supporting_doc as $key => $value) { ?>

                                        <tr>
                                            <td><?php echo $value->comment; ?></td>
                                            <td><?php echo anchor(base_url() . 'uploads/document/' . $value->file, lang('loan_supporting_document_view')); ?></td>

                                        </tr>
                                    <?php } ?>
                                </tbody></table>
                        </div>
                        <?php
                    } else {
                        echo lang('loan_doc_not_found');
                    }
                    ?>


                </div>
            </div>
            <div class="col-lg-12">

                <div style="border-bottom: 1px solid #ccc; margin-bottom: 20px;  font-weight: bold; padding-top: 20px;"><?php echo lang('loan_info_guarantor'); ?></div>



                <?php
                $guarantor_list = $this->loan_model->get_guarantor(null, $loaninfo->LID)->result();
                if (count($guarantor_list) > 0) {
                    ?>
                    <div class="col-lg-12">
                        <?php foreach ($guarantor_list as $key => $value) { ?>
                            <div class="col-lg-6" style="float: left;">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <?php
                                        $customerinfo = $this->member_model->member_basic_info(null, $value->PID)->row();
                                        ?>
                                        <strong>  <?php echo lang('member_member_id') ?> :</strong> <?php echo $customerinfo->member_id; ?> <br/>
                                        <strong>  <?php echo lang('contribution_member_name') ?> :</strong> <?php echo $customerinfo->middlename . ' ' . $customerinfo->lastname; ?> <br/>
                                        <strong>  <?php echo lang('loan_quarantor_relationship') ?> :</strong> <?php echo $value->relationship; ?> <br/>
                                        <strong> <?php echo lang('loan_quarantor_asset') ?> :</strong> <?php echo $value->declaration; ?> <br>
                                        <strong> <?php echo lang('loan_quarantor_attachment') ?> :</strong> <?php echo ($value->file <> '' ? anchor(base_url() . 'uploads/document/' . $value->file, lang('loan_quarantor_attachment_view')) : ''); ?> <br>

                                        </p>

                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <?php
                } else {
                    echo lang('loan_guarantor_not_found');
                }
                ?>






            </div>
        </div>
    </div>
    
    
     <div class="panel panel-default">
        <div class="panel-heading">
            <h4><?php echo lang('evaluation_comment'); ?></h4>
            
        </div>
         <div class="panel-body">
               <?php
                $evaluation_histry = $this->loan_model->loan_evaluation_history($loaninfo->LID)->result();
                foreach ($evaluation_histry as $key => $value) {
                    ?>
                <div style="border-bottom: 1px solid #ccc; margin-bottom: 20px; <?php echo ($loaninfo->evaluated == $value->status ? 'color:blue':''); ?>">
                    <strong><?php echo lang('loan_status'); ?></strong> : <?php echo $value->name; ?><br/>
                    <strong><?php echo lang('loan_comment'); ?></strong> : <?php echo $value->comment; ?><br/>
                    <strong><?php echo lang('loan_recorder'); ?></strong> : <?php echo $value->first_name.' '.$value->last_name .' &nbsp; &nbsp; '.$value->createdon; ?>
                </div>
                <?php } ?>
         </div>
     </div>
    
    

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><?php echo lang('loan_approval_comment'); ?></h4>
            
        </div>
        <div class="panel-body">
            <div class="col-lg-6">
                <?php echo form_open_multipart(current_lang() . "/loan/loan_approval_action/" . $loanid); ?>

                <label class="control-label"><?php echo lang('loan_status'); ?>  : <span class="required">*</span></label><br/>

                <select name="status" class="form-control">
                    <option value=""><?php echo lang('select_default_text'); ?></option>
                    <?php
                    $selected = set_value('status');
                    $paysource_list = array('4' => 'Approved & Accepted',  '2' => 'Approved & Rejected');
                    foreach ($paysource_list as $key => $value) {
                        ?>
                        <option <?php echo ($key == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php } ?>
                </select>
                <?php echo form_error('status'); ?>
                <br/>

                <label class="control-label"><?php echo lang('loan_comment'); ?>  : <span class="required">*</span></label><br/>

                <textarea rows="3" name="comment" class="form-control" ><?php echo set_value('comment'); ?> </textarea>
                <?php echo form_error('comment'); ?>

                <?php if ($loaninfo->status != 4) { ?>
                    <br/>
                    <input class="btn btn-primary" value="<?php echo lang('loan_evaluated_test'); ?>" type="submit"/>

                <?php } ?>
                <?php echo form_close(); ?>
            </div>
            <div  class="col-lg-6">
                <?php
                $evaluation_histry = $this->loan_model->loan_approval_history($loaninfo->LID)->result();
                foreach ($evaluation_histry as $key => $value) {
                    ?>
                <div style="border-bottom: 1px solid #ccc; margin-bottom: 20px;">
                    <strong><?php echo lang('loan_status'); ?></strong> : <?php echo $value->name; ?><br/>
                    <strong><?php echo lang('loan_comment'); ?></strong> : <?php echo $value->comment; ?><br/>
                    <strong><?php echo lang('loan_recorder'); ?></strong> : <?php echo $value->first_name.' '.$value->last_name .' &nbsp; &nbsp; '.$value->createdon; ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>


</div>