
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

       
        <?php echo form_open_multipart(current_lang() . "/loan/loan_guarantor_respond/" . $id.'/?s=accept', 'class="form-horizontal"'); ?>

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
      
        
<?php if ($this->ion_auth->in_group('Members')) { ?>
        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_quarantor_relationship'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <input name="relationship" type="text"  value="<?php echo set_value('relationship'); ?>"  class="form-control"/> 
                <?php echo form_error('relationship'); ?>
            </div>
        </div>
        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_quarantor_asset'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <textarea name="asset" class="form-control"><?php echo set_value('asset'); ?></textarea>
                <?php echo form_error('asset'); ?>
            </div>
        </div>
        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_quarantor_declaration'); ?>  : </label>
            <div class="col-lg-7">
                <input name="file" type="file" class="form-control" />
                <?php
                if (isset($logo_error)) {
                    echo '<div class="error_message">' . $logo_error . '</div>';
                }
                ?>
            </div>
        </div>
<?php } if ($loaninfo->edit == 0) { ?>

            <div class="form-group">
                <label class="col-lg-3 control-label">&nbsp;</label>
                <div class="col-lg-6">
                    <input class="btn btn-primary" value="<?php echo lang('loan_save_btn'); ?>" type="submit"/>
                </div>
            </div>

        <?php } ?>

        <?php echo form_close(); ?>






    </div>
</div>
<script src="<?php echo base_url() ?>media/js/chosen.jquery.js"></script>
<script type="text/javascript">
    var config = {
        no_results_text: 'Oops, nothing found!'
    }
    $("#customerid").chosen(config);

</script>