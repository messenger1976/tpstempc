
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

    <div class="col-lg-3">
        <input type="text" class="form-control" name="key" value="<?php echo (isset($_GET['key']) ? $_GET['key'] : ''); ?>"/> 
    </div>
    <div class="col-lg-3">
        <select name="group_id" id="group_filter" class="form-control" onchange="filterByGroup()">
            <option value="">All Groups</option>
            <?php if (isset($groups) && !empty($groups)): ?>
                <?php foreach ($groups as $group): ?>
                    <option value="<?php echo $group->id; ?>" <?php echo (isset($selected_group_id) && $selected_group_id == $group->id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>
    <?php if (isset($_GET['sort_by']) && !empty($_GET['sort_by'])): ?>
        <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($_GET['sort_by'], ENT_QUOTES, 'UTF-8'); ?>"/>
    <?php endif; ?>
    <?php if (isset($_GET['sort_order']) && !empty($_GET['sort_order'])): ?>
        <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($_GET['sort_order'], ENT_QUOTES, 'UTF-8'); ?>"/>
    <?php endif; ?>

</div>
</div>


<?php echo form_close(); ?>


<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>
                    <?php
                    $current_sort = (isset($sort_by) && $sort_by == 'created_on') ? 'created_on' : '';
                    $current_order = (isset($sort_order) && $current_sort == 'created_on') ? strtoupper($sort_order) : 'DESC';
                    $new_order = ($current_order == 'ASC') ? 'DESC' : 'ASC';
                    
                    // Build sort URL
                    $sort_params = array('sort_by' => 'created_on', 'sort_order' => $new_order);
                    if (isset($_GET['key']) && !empty($_GET['key'])) {
                        $sort_params['key'] = $_GET['key'];
                    }
                    if (isset($_GET['group_id']) && !empty($_GET['group_id'])) {
                        $sort_params['group_id'] = $_GET['group_id'];
                    }
                    $sort_url = current_lang() . '/auth/index?' . http_build_query($sort_params);
                    ?>
                    <a href="<?php echo site_url($sort_url); ?>" style="text-decoration: none; color: inherit; display: inline-block;">
                        Date Created
                        <?php if ($current_sort == 'created_on'): ?>
                            <?php if ($current_order == 'ASC'): ?>
                                <i class="fa fa-sort-asc" style="margin-left: 5px;"></i>
                            <?php else: ?>
                                <i class="fa fa-sort-desc" style="margin-left: 5px;"></i>
                            <?php endif; ?>
                        <?php else: ?>
                            <i class="fa fa-sort" style="color: #ccc; margin-left: 5px;"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th><?php echo lang('index_fname_th'); ?></th>
                <th><?php echo lang('index_lname_th'); ?></th>
                <th><?php echo lang('index_username_th'); ?></th>
                <th><?php echo lang('index_email_th'); ?></th>
                <th><?php echo lang('index_groups_th'); ?></th>
                <th><?php echo lang('index_status_th'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>
        </thead>
        <tbody>
<?php
foreach ($users as $key => $user) { ?>
    
            <tr>
            <td><?php 
                if (isset($user->created_on) && !empty($user->created_on)) {
                    // Handle both Unix timestamp and MySQL datetime format
                    if (is_numeric($user->created_on)) {
                        echo date('Y-m-d H:i:s', $user->created_on);
                    } else {
                        echo date('Y-m-d H:i:s', strtotime($user->created_on));
                    }
                } else {
                    echo '-';
                }
            ?></td>
            <td><?php echo htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo isset($user->username) ? htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
            <td><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <?php echo $this->ion_auth->get_users_groups($user->id)->row()->name;?>
            </td>
            <?php $user_status = ($user->active)?'class="btn btn-info btn-xs"': 'class="btn btn-danger btn-xs"';?>
            <td><span <?php echo $user_status;?>><?php echo ($user->active) ? anchor(current_lang(). "/auth/deactivate/" . encode_id($user->id), lang('index_active_link'),'style="decoration: none; color: white;"') : anchor(current_lang(). "/auth/activate/" . encode_id($user->id), lang('index_inactive_link'),'style="decoration: none; color: white;"'); ?></span></td>
            <td><?php 
            //current_lang(). "/auth/edit_user/" . encode_id($user->id)
            echo anchor('auth/edit_user/' . encode_id($user->id), ' <i class="fa fa-edit"></i> '.lang('button_edit')); ?>
            <a href="javascript:void(0);" class="btn-delete-user" data-id="<?php echo encode_id($user->id); ?>" data-name="<?php echo htmlspecialchars($user->first_name . ' ' . $user->last_name, ENT_QUOTES, 'UTF-8'); ?>" style="color: red; margin-left: 10px;">
                <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
            </a>
            </td>
        </tr>
<?php } ?>
        </tbody>

    </table>
    
    <div style="margin-top: 20px; padding: 10px; background-color: #f5f5f5; border-top: 1px solid #ddd;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1;">
                <?php echo $links; ?>
            </div>
            <div style="margin-left: 20px; text-align: right;">
                <?php page_selector(); ?>
            </div>
        </div>
    </div>
</div>

<script>
function filterByGroup() {
    var groupId = document.getElementById('group_filter').value;
    var url = '<?php echo site_url(current_lang() . '/auth/index'); ?>';
    var params = [];
    
    <?php if (isset($_GET['key']) && !empty($_GET['key'])): ?>
    params.push('key=<?php echo urlencode($_GET['key']); ?>');
    <?php endif; ?>
    
    <?php if (isset($_GET['sort_by']) && !empty($_GET['sort_by'])): ?>
    params.push('sort_by=<?php echo urlencode($_GET['sort_by']); ?>');
    <?php endif; ?>
    
    <?php if (isset($_GET['sort_order']) && !empty($_GET['sort_order'])): ?>
    params.push('sort_order=<?php echo urlencode($_GET['sort_order']); ?>');
    <?php endif; ?>
    
    if (groupId) {
        params.push('group_id=' + groupId);
    }
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    window.location.href = url;
}

$(document).ready(function() {
    $('.btn-delete-user').click(function() {
        var userId = $(this).data('id');
        var userName = $(this).data('name');
        var deleteUrl = '<?php echo site_url(current_lang() . '/auth/delete_user/'); ?>/' + userId;
        
        swal({
            title: "Are you sure?",
            text: "You want to delete user: " + userName + "! This will hide the user from the list.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function(isConfirm) {
            if (isConfirm) {
                window.location.href = deleteUrl;
            }
        });
    });
});
</script>
