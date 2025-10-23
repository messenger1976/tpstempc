<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th><?php echo lang('group_id'); ?></th>
                <th><?php echo lang('member_group_name'); ?></th>
                <th><?php echo lang('member_group_description'); ?></th>
                <th><?php echo lang('actioncolumn'); ?></th>
            </tr>
        </thead>
        <tbody>
           <?php
           $i=1;
           foreach ($grouplist as $key => $value) { ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $value->GID ?></td>
                <td><?php echo $value->name ?></td>
                <td><?php echo $value->description ?></td>
                <td><a href="<?php echo site_url(current_lang().'/member/member_group_edit/'.encode_id($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a></td>
            </tr>
           <?php }
           ?>
        </tbody>
    </table>

</div>