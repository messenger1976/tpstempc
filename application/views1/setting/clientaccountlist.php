<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th style="width: 150px;"><?php echo lang('clientaccount_number'); ?></th>
                <th><?php echo lang('clientaccount_label_name'); ?></th>
                <th><?php echo lang('clientaccount_label_postal_address'); ?></th>
                <th><?php echo lang('clientaccount_label_email'); ?></th>
                <th><?php echo lang('clientaccount_label_phone'); ?></th>
                <th><?php echo lang('actioncolumn'); ?></th>
            </tr>
        </thead>
        <tbody>
           <?php
           $i=1;
           foreach ($account as $key => $value) { ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $value->PIN ?></td>
                <td><?php echo $value->name ?></td>
                <td><?php echo $value->box ?></td>
                <td><?php echo $value->email ?></td>
                <td><?php echo $value->mobile ?></td>
                <td><a href="<?php echo site_url(current_lang().'/setting/client_account_edit/'.  encode_id($value->PIN)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a> &nbsp; | &nbsp; <a href="<?php echo site_url(current_lang().'/setting/client_account_view/'.  encode_id($value->PIN)); ?>"><i class="fa fa-file"></i> <?php echo lang('button_view'); ?> </a>  </td>
            </tr>
           <?php }
           ?>
        </tbody>
    </table>

</div>