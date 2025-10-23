
<?php echo form_open(current_lang() . "/sms/contact_list", 'class="form-horizontal"'); ?>

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
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/sms/newcontact/'); ?>"><?php echo 'New Contact'; ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('sno'); ?></th>
                <th>Group</th>
                <th>Name</th>
                <th>Mobile</th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php
            $i=1;
            foreach ($member_list as $key => $value) { 
                $grop = $this->db->get_where('sms_contact_group',array('id'=>$value->group))->row();
                ?>

                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($grop->name, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->mobile, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo anchor(current_lang() . "/sms/newcontact/" . ($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?></td>
                </tr>
            <?php } ?>
        </tbody>

    </table>
   
    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
</div>
