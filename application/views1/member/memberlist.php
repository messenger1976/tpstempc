
<?php echo form_open(current_lang() . "/member/member_list", 'class="form-horizontal"'); ?>

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
                <th><?php echo lang('member_firstname'); ?></th>
                <th><?php echo lang('member_middlename'); ?></th>
                <th><?php echo lang('member_lastname'); ?></th>
                <th><?php echo lang('member_gender'); ?></th>
                <th><?php echo lang('member_contact_phone1'); ?></th>
                <th><?php echo lang('member_status'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php foreach ($member_list as $key => $value) { ?>

                <tr>
                    <td><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->firstname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->middlename, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->lastname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->gender, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php
                        echo $this->member_model->member_contact($value->PID)->phone1;
                        ?>
                    </td>
                    
                    <td><?php echo ($value->status == 1? anchor(current_lang().'/member/deactivate/'.  encode_id($value->id),  lang('member_active')): anchor(current_lang().'/member/activate/'.  encode_id($value->id),  lang('member_inactive'))); ?></td>
                    
                    <td><?php echo anchor(current_lang() . "/member/memberinfo/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?></td>
                </tr>
            <?php } ?>
        </tbody>

    </table>
   
    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
</div>
