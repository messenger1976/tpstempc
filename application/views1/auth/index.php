
<?php echo form_open(current_lang() . "/auth/index", 'class="form-horizontal"'); ?>

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
<div class="row offset1">
<div class="form-group col-lg-10">

    <div class="col-lg-5">
        <input type="text" class="form-control" name="key" value="<?php echo (isset($_GET['key']) ? $_GET['key'] : ''); ?>"/> 
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>

</div>
</div>


<?php echo form_close(); ?>


<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
 <tr>
        <th><?php echo lang('index_fname_th'); ?></th>
        <th><?php echo lang('index_lname_th'); ?></th>
        <th><?php echo lang('index_email_th'); ?></th>
        <th><?php echo lang('index_groups_th'); ?></th>
        <th><?php echo lang('index_status_th'); ?></th>
        <th><?php echo lang('index_action_th'); ?></th>
    </tr>
                
            </tr>
        </thead>
        <tbody>
<?php
foreach ($users as $key => $user) { ?>
    
            <tr>
            <td><?php echo htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <?php echo $this->ion_auth->get_users_groups($user->id)->row()->name;?>
            </td>
            <td><?php echo ($user->active) ? anchor(current_lang(). "/auth/deactivate/" . encode_id($user->id), lang('index_active_link')) : anchor(current_lang(). "/auth/activate/" . encode_id($user->id), lang('index_inactive_link')); ?></td>
            <td><?php 
            //current_lang(). "/auth/edit_user/" . encode_id($user->id)
            echo anchor('#', ' <i class="fa fa-edit"></i> '.lang('button_edit')); ?></td>
        </tr>
<?php } ?>
        </tbody>

    </table>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div>    
    <?php echo $links; ?>
</div>
