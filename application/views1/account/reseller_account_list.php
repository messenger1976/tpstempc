<?php
if (isset($message)) {
    echo '<div style="margin-bottom:20px;" class="btn btn-success btn-block">' . $message . '</div><br/>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div style="margin-bottom:20px;" class="btn btn-success btn-block">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning)) {
    echo '<div style="margin-bottom:20px;" class="btn btn-danger btn-block">' . $warning . '</div>';
}
?>
<div style="text-align: right; padding-bottom: 10px;"><a href="<?php echo site_url(current_lang().'/account/create_resseller_account'); ?>"><button class="btn btn-lg btn-sm btn-primary">
            <i class="fa fa-plus"></i>
            <?php echo lang('account_reseller_account_create'); ?></button></a></div>

<table class="table table-bordered  table-hover">
    <thead>
    <th style="width: 70px;"><?php echo lang('sno'); ?></th>
    <th><?php echo lang('index_fname_th'); ?></th>
    <th><?php echo lang('index_lname_th'); ?></th>
    <th><?php echo lang('index_email_th'); ?></th>
    <th><?php echo lang('mobile'); ?></th>
    <th><?php echo lang('index_action_th'); ?></th>
   
    </thead>
    <tbody>
        <?php
        $i=1;
        foreach ($resellers as $key => $value) { ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo $value->firstname ?></td>
            <td><?php echo $value->lastname ?></td>
            <td><?php echo $value->email ?></td>
            <td><?php echo $value->mobile ?></td>
            <td><a href="<?php echo site_url(current_lang().'/account/create_resseller_account/'.$value->id) ?>"><i class="fa fa-edit"></i><?php echo lang('edit') ?></a></td>
        </tr>
        <?php }
        ?>
    </tbody>
    
</table>