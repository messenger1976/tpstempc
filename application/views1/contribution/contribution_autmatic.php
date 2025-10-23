<?php echo form_open_multipart(current_lang() . "/contribution/automatic_contribution_process", 'class="form-horizontal"'); ?>

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


 <div class="form-group">
            <label class="col-lg-3 control-label">Process contribution for this month</label>
            <div class="col-lg-6">
                <input class="form-control" name="date_month" value="<?php echo date('m-Y') ?>"/>
                <?php echo form_error('date_month'); ?>
            </div>
        </div>
 <div class="form-group">
            <label class="col-lg-3 control-label">&nbsp;</label>
            <div class="col-lg-6">
                <input class="btn btn-primary" value="Process contribution" type="submit"/>
            </div>
        </div>

   
   
    

<?php echo form_close(); ?>