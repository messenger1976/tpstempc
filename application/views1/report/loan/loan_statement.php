<link href="<?php echo base_url(); ?>media/css/choosen/chosen.css" rel="stylesheet">

<style type="text/css">

    .chosen-container-single .chosen-single{
        height: 34px !important; 
        padding-top: 8px;
    }


    div#searchboxdata{
        border-bottom:  1px solid #000; 
    }
    div.ibox-content{
        min-height: 300px;
    }
    .bgm{
        background-color: #f3f3f4;
    }

</style>

<div id="searchboxdata">
    <?php echo form_open_multipart(current_lang() . "/report_loan/loan_statement", 'class="form-horizontal"'); ?>

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

    <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_id'); ?>  : <span class="required">*</span></label>
        <div class="col-lg-7">
            <div class="input-group" >
                <select name="loan_id" class="form-control" id="loan_id">
                    <option value=""> <?php echo lang('select_default_text'); ?></option>
                    <?php
                    $selected = $loan_id;
                    foreach ($loan_list as $key => $value) {
                        ?>
                        <option <?php echo ($selected ? ($selected == $value->LID ? 'selected="selected"' : '') : ''); ?> value="<?php echo $value->LID; ?>"> <?php echo $value->LID . ' - ' . $value->name; ?></option>
                    <?php }
                    ?>
                </select>
                <span class="input-group-btn">
                    <input type="submit" value="Load" name="Load" class="btn btn-primary" />
                </span>
            </div>
            <?php echo form_error('loan_id'); ?>
        </div>
    </div>

    <?php echo form_close(); ?>
</div>

<br/>
<br/>
<?php
if ($loan_id != '') {
   $this->data['loanid'] = encode_id($loan_id);
   $LID = $loan_id;
   $this->data['trans'] = $this->report_model->loan_statement($LID);   
 $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
 $this->load->view('report/loan/loan_statement_content',$this->data);
}
?>



<script src="<?php echo base_url() ?>media/js/chosen.jquery.js"></script>
<script type="text/javascript">

    $(function() {

        var config = {
            no_results_text: 'Oops, nothing found!'
        }
        $("#loan_id").chosen(config);
    });

</script>
