<div class="table-responsive">
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
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('loan_LID'); ?></th>
                <th><?php echo lang('member_name'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>

            </tr>

        </thead>
        <tbody>
            <?php
            if (count($request) > 0) {
                foreach ($request as $key => $value) {
                    $loaninfo = $this->loan_model->loan_info($value->LID)->row();
                    ?>
                    <tr>
                        <td><?php echo $value->LID; ?></td>
                        <td><?php
                            $info = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
                            echo $info->member_id . ' : ' . $info->firstname . ' ' . $info->middlename . ' ' . $info->lastname;
                            ?></td>
                        <td>
                        <?php  if ($loaninfo->edit == 0) {
                                echo anchor(current_lang() . '/loan/loan_guarantor_respond/' . encode_id($value->id).'/?s=accept', '  <i class="fa fa-weibo"></i> Accept' );
                                echo '&nbsp; | &nbsp;';
                                echo anchor(current_lang() . '/loan/loan_guarantor_respond/' . encode_id($value->id).'/?s=reject', '  <i class="fa fa-vine"></i> Reject' );
                            }
                            ?></td>
                    </tr>
                <?php } ?>

<?php } else { ?>
                <tr><td colspan="3"><?php echo 'No data found!!'; ?></td></tr>  
<?php } ?>
        </tbody>

    </table>
</div>