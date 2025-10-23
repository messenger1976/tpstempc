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
        <?php echo form_open_multipart(current_lang() . "/loan/loan_security/" . $loanid, 'class="form-horizontal"'); ?>

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
        <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_security_declaration'); ?>  : <span class="required">*</span></label>
            <div class="col-lg-7">
                <textarea rows="3" name="declaration" class="form-control" ><?php echo $declaration->declaration; ?></textarea>
                <?php echo form_error('declaration'); ?>
            </div>
        </div>
        <?php if (count($supporting_doc) > 0) { ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><?php echo lang('loan_supporting_document_comment'); ?></th>
                            <th><?php echo lang('loan_supporting_document_doc'); ?></th>
                            <?php
                            if ($loaninfo->edit == 0) {
                                ?>
                                <th><?php echo lang('index_action_th'); ?></th>
                            <?php } ?>
                        </tr>

                    </thead>
                    <tbody>
                        <?php foreach ($supporting_doc as $key => $value) { ?>

                            <tr>
                                <td><?php echo $value->comment; ?></td>
                                <td><?php echo anchor(base_url() . 'uploads/document/' . $value->file, lang('loan_supporting_document_view')); ?></td>
                                <?php
                                if ($loaninfo->edit == 0) {
                                    ?>
                                    <td><?php echo anchor(current_lang() . '/loan/deletedoc/' . $loanid . '/' . $value->id, lang('loan_supporting_document_remove')); ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody></table>

            <?php }
            ?>



            <div style="color: brown; border-bottom: 1px solid #ccc; font-size: 15px; padding-top: 10px;  font-weight: bold">
                <?php echo lang('loan_supporting_document'); ?>
            </div>
            <br/>
            <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_supporting_document_comment'); ?>  : </label>
                <div class="col-lg-7">
                    <input type="text" name="comment" class="form-control" />
                    <?php echo form_error('comment'); ?>
                </div>
            </div>
            <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('loan_supporting_document_attach'); ?>  : </label>
                <div class="col-lg-7">
                    <input name="file" type="file" class="form-control" />
                    <?php
                    if (isset($logo_error)) {
                        echo '<div class="error_message">' . $logo_error . '</div>';
                    }
                    ?>
                </div>
            </div>

            <?php if ($loaninfo->edit == 0) { ?>

                <div class="form-group">
                    <label class="col-lg-3 control-label">&nbsp;</label>
                    <div class="col-lg-6">
                        <input class="btn btn-primary" value="<?php echo lang('loan_save_btn'); ?>" type="submit"/>
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