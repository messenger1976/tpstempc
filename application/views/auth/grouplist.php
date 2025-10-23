<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th><?php echo lang('create_group_name_label'); ?></th>
                <th><?php echo lang('create_group_desc_label'); ?></th>
                <th><?php echo lang('actioncolumn'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;

            $user = current_user();
            foreach ($grouplist as $key => $value) {
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $value->name ?></td>
                    <td><?php echo $value->description ?></td>
                    <td>
                        <?php if ($user->is_client_admin == 1) { ?>
                            <a href="<?php echo site_url(current_lang() . '/auth/edit_group/' . encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a> &nbsp; | &nbsp; <a href="<?php echo site_url(current_lang() . '/auth/grouprole/' . encode_id($value->id)); ?>"><i class="fa fa-lock"></i> <?php echo lang('openrole_page_link'); ?> </a>  
    <?php } ?>
                    </td>
                </tr>
            <?php }
            ?>
        </tbody>
    </table>

</div>