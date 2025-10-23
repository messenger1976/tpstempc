
<?php
if (isset($message)) {
    echo '<div style="margin-bottom:20px;" class="btn btn-success btn-block">' . $message . '</div><br/>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div style="margin-bottom:20px;" class="btn btn-success btn-block">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning)) {
    echo '<div style="margin-bottom:20px;" class="btn btn-danger btn-block">' . $warning . '</div>';
}
?>

<table class="table table-bordered  table-hover">
    <thead>
    <th style="width: 70px;"><?php echo lang('sno'); ?></th>
    <th><?php echo lang('client_account_name'); ?></th>
    <th style="width: 120px;"><?php echo lang('client_account_mobile'); ?></th>
    <th style="width: 200px;"><?php echo lang('create_user_email_label'); ?></th>
    <th style="width: 200px;"><?php echo lang('client_account_address'); ?></th>
    <th style="width: 50px;"><?php echo lang('index_action_th'); ?></th>
   
    </thead>
    <tbody>
        <?php
        $i=1;
        if(count($client_list)){
        foreach ($client_list as $key => $value) { ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo $value->name ?></td>
            <td><?php echo $value->mobile ?></td>
            <td><?php echo $value->email ?></td>
            <td><?php echo $value->address ?></td>
             <td><a href="<?php echo site_url(current_lang().'/account/client_account_create/'.$value->id) ?>"><i class="fa fa-edit"></i><?php echo lang('edit') ?></a></td>
            
        <?php } }else { ?>
        <tr>
            <td colspan="6"><?php echo lang('no_data'); ?></td>
        </tr>
        <?php }
        ?>
    </tbody>
    
</table>
