<?php
$this->load->view('loan/topmenu');
?>

<div style="margin-top: 20px;" class="col-lg-12">
    
    
     <div class="col-lg-3">
        <img src="<?php echo base_url() ?>uploads/memberphoto/<?php echo $basicinfo->photo; ?>" style="width: 150px; height: 170px; border: 1px solid #ccc;"/>
        <div style="display: block;  margin-top: 20px; font-size: 15px;">
            <?php echo lang('member_pid') ?> : <?php echo $basicinfo->PID; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_member_id') ?> : <?php echo $basicinfo->member_id; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_firstname') ?> : <?php echo $basicinfo->firstname; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_middlename') ?> : <?php echo $basicinfo->middlename; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_lastname') ?> : <?php echo $basicinfo->lastname; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_gender') ?> : <?php echo $basicinfo->gender; ?>
        </div>
        <br/><br/>
    </div>
    
    



<div class="col-lg-9">


    <link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet"/>
    <?php echo form_open_multipart(current_lang() . "/loan/loan_editing/" . $loanid, 'class="form-horizontal"'); ?>

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

 <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_LID'); ?>  : <span class="required">*</span></label>
        <div class="col-lg-7">
            <input type="text" disabled="disabled" value="<?php echo $loaninfo->LID; ?>"  class="form-control"/> 
            
        </div>
    </div>
    <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_applicationdate'); ?>  : <span class="required">*</span></label>
        <div class=" col-lg-7">
            <div class="input-group date" id="datetimepicker" >
                <input type="text" name="applicationdate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo format_date($loaninfo->applicationdate,FALSE); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
                <span class="input-group-addon">
                    <span class="fa fa-calendar "></span>
                </span>
            </div>
            <?php echo form_error('applicationdate'); ?>
        </div>
    </div>

    <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_product'); ?>  : <span class="required">*</span></label>
        <div class="col-lg-7">
            <select name="product" class="form-control">
                <option value=""><?php echo lang('select_default_text'); ?></option>
                <?php
                $selected = $loaninfo->product_type;
                foreach ($loan_product_list as $key => $value) {
                    ?>
                    <option <?php echo ($value->id == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->id; ?>"><?php echo $value->name ?></option>
                <?php } ?>
            </select>
            <?php echo form_error('product'); ?>
        </div>
    </div>

    <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_applied_amount'); ?>  : <span class="required">*</span></label>
        <div class="col-lg-7">
            <input type="text"  name="amount" value="<?php echo $loaninfo->basic_amount; ?>"  class="form-control  amountformat"/> 
            <?php echo form_error('amount'); ?>
        </div>
    </div>
    <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_installment'); ?>  : <span class="required">*</span></label>
        <div class="col-lg-7">
            <input type="text"  name="installment" value="<?php echo $loaninfo->number_istallment; ?>"  class="form-control  amountformat"/> 
            <?php echo form_error('installment'); ?>
        </div>
    </div>
    <div class="form-group"><label class="col-lg-4 control-label"><?php echo 'Monthly Income'; ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <input type="text"  name="income" value="<?php echo $loaninfo->monthly_income; ?>"  class="form-control  amountformat"/> 
                <?php echo form_error('income'); ?>
            </div>
        </div>

    <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_paysource'); ?>  : <span class="required">*</span></label>
        <div class="col-lg-7">
            <select name="source" class="form-control">
                <option value=""><?php echo lang('select_default_text'); ?></option>
                <?php
                $selected = $loaninfo->pay_source;
                foreach ($paysource_list as $key => $value) {
                    ?>
                    <option <?php echo ($value->name == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name ?></option>
                <?php } ?>
            </select>
            <?php echo form_error('source'); ?>
        </div>
    </div>

    <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_purpose'); ?>  : <span class="required">*</span></label>
        <div class="col-lg-7">
            <textarea rows="3" name="purpose" class="form-control" > <?php echo $loaninfo->loan_purpose; ?> </textarea>
            <?php echo form_error('purpose'); ?>
        </div>
    </div>

 <?php if($loaninfo->edit == 0){ ?>

    <div class="form-group">
        <label class="col-lg-3 control-label">&nbsp;</label>
        <div class="col-lg-6">
            <input class="btn btn-primary" value="<?php echo lang('member_edit_btn'); ?>" type="submit"/>
        </div>
    </div>
    
 <?php } ?>

    <?php echo form_close(); ?>

    <script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
    <script src="<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <script type="text/javascript">
        $(function() {
            $('#datetimepicker').datetimepicker({
                pickTime: false
            });

        });
    </script>




</div>
</div>