<?php $this->load->view('loan/list_page_styles'); ?>

<?php
$users = isset($users) ? $users : array();
$groups = isset($groups) ? $groups : array();
$selected_group_id = isset($selected_group_id) ? $selected_group_id : null;
$sort_by = isset($sort_by) ? $sort_by : 'created_on';
$sort_order = isset($sort_order) ? strtoupper($sort_order) : 'DESC';
$search_key = isset($_GET['key']) ? $_GET['key'] : (isset($_POST['key']) ? $_POST['key'] : '');
$links = isset($links) ? $links : '';
$total_rows = count($users);
$create_url = site_url(current_lang() . '/auth/create_user');
$clear_url = site_url(current_lang() . '/auth/index');

$base_sort_params = array();
if ($search_key !== '') {
    $base_sort_params['key'] = $search_key;
}
if (!empty($selected_group_id)) {
    $base_sort_params['group_id'] = $selected_group_id;
}

$current_created_order = ($sort_by === 'created_on') ? $sort_order : 'DESC';
$next_created_order = ($current_created_order === 'ASC') ? 'DESC' : 'ASC';
$created_sort_params = array_merge($base_sort_params, array(
    'sort_by' => 'created_on',
    'sort_order' => $next_created_order,
));
$created_sort_url = site_url(current_lang() . '/auth/index?' . http_build_query($created_sort_params));
?>

<style type="text/css">
.member-list-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.member-list-page .filter-field.search-field { flex: 2 1 240px; }
.member-list-page .filter-field.group-field { flex: 0 1 200px; min-width: 160px; }
.member-list-page .status-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none !important;
}
.member-list-page .status-pill.active {
    background: #e8f8f5;
    color: #1ab394;
}
.member-list-page .status-pill.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.member-list-page .status-pill:hover {
    opacity: .9;
}
.member-list-page .sort-link {
    color: inherit;
    text-decoration: none;
    font-weight: 700;
}
.member-list-page .sort-link:hover { color: #1ab394; }
.member-list-page .sort-link i { margin-left: 4px; }
.member-list-page .group-chip {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    background: #eef3fb;
    color: #3c6eae;
}
.member-list-page .pagination-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px 18px;
    border-top: 1px solid #e7eaec;
    background: #fafbfc;
}
</style>

<div class="col-lg-12 member-list-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="member-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="member-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="member-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="member-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="member-filter-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-search icon-badge"></i>
                <h4><?php echo lang('user_manager_list'); ?></h4>
            </div>
            <div class="head-actions">
                <a href="<?php echo $create_url; ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> <?php echo lang('account_creation_new'); ?>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <?php echo form_open(current_lang() . '/auth/index', 'class="form-horizontal"'); ?>
                <div class="filter-row">
                    <div class="filter-field search-field">
                        <label>Search</label>
                        <input type="text" class="form-control" name="key"
                               value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="Name, username, or email"/>
                    </div>
                    <div class="filter-field group-field">
                        <label>Group</label>
                        <select name="group_id" id="group_filter" class="form-control" onchange="filterByGroup()">
                            <option value="">All Groups</option>
                            <?php foreach ($groups as $group) { ?>
                                <option value="<?php echo (int) $group->id; ?>" <?php echo (!empty($selected_group_id) && (int) $selected_group_id === (int) $group->id) ? 'selected="selected"' : ''; ?>>
                                    <?php echo htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <?php if (!empty($sort_by)) { ?>
                        <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by, ENT_QUOTES, 'UTF-8'); ?>"/>
                    <?php } ?>
                    <?php if (!empty($sort_order)) { ?>
                        <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order, ENT_QUOTES, 'UTF-8'); ?>"/>
                    <?php } ?>
                    <div class="filter-actions">
                        <a href="<?php echo $clear_url; ?>" class="btn btn-default btn-clear-filter">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter"></i> <?php echo lang('button_search'); ?>
                        </button>
                    </div>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-user icon-badge"></i>
                <h4><?php echo lang('user_manager_list'); ?></h4>
            </div>
            <div class="result-meta">
                Showing <strong><?php echo number_format($total_rows); ?></strong> user<?php echo $total_rows === 1 ? '' : 's'; ?> on this page
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table">
                <thead>
                    <tr>
                        <th>
                            <a class="sort-link" href="<?php echo $created_sort_url; ?>">
                                Date Created
                                <?php if ($sort_by === 'created_on') { ?>
                                    <i class="fa <?php echo ($sort_order === 'ASC') ? 'fa-sort-asc' : 'fa-sort-desc'; ?>"></i>
                                <?php } else { ?>
                                    <i class="fa fa-sort" style="color:#ccc;"></i>
                                <?php } ?>
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
                    <?php if (!empty($users)) {
                        foreach ($users as $user) {
                            $created_display = '-';
                            if (isset($user->created_on) && $user->created_on !== '' && $user->created_on !== null) {
                                if (is_numeric($user->created_on)) {
                                    $created_display = date('Y-m-d H:i:s', $user->created_on);
                                } else {
                                    $created_display = date('Y-m-d H:i:s', strtotime($user->created_on));
                                }
                            }
                            $group_row = $this->ion_auth->get_users_groups($user->id)->row();
                            $group_name = $group_row ? $group_row->name : '—';
                            $full_name = trim($user->first_name . ' ' . $user->last_name);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($created_display, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="member-id-chip"><?php echo isset($user->username) ? htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8') : '—'; ?></span></td>
                                <td><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="group-chip"><?php echo htmlspecialchars($group_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td>
                                    <?php if ($user->active) { ?>
                                        <a class="status-pill active" href="<?php echo site_url(current_lang() . '/auth/deactivate/' . encode_id($user->id)); ?>">
                                            <?php echo lang('index_active_link'); ?>
                                        </a>
                                    <?php } else { ?>
                                        <a class="status-pill inactive" href="<?php echo site_url(current_lang() . '/auth/activate/' . encode_id($user->id)); ?>">
                                            <?php echo lang('index_inactive_link'); ?>
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?php echo site_url(current_lang() . '/auth/edit_user/' . encode_id($user->id)); ?>"
                                           class="btn btn-warning btn-xs"
                                           title="<?php echo htmlspecialchars(lang('button_edit'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);"
                                           class="btn btn-danger btn-xs btn-delete-user"
                                           data-id="<?php echo encode_id($user->id); ?>"
                                           data-name="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>"
                                           title="<?php echo htmlspecialchars(lang('button_delete'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding: 28px;">
                                <?php echo lang('no_records_found'); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="pagination-bar">
            <div><?php echo $links; ?></div>
            <div><?php page_selector(); ?></div>
        </div>
    </div>
</div>

<script>
function filterByGroup() {
    var groupId = document.getElementById('group_filter').value;
    var url = '<?php echo site_url(current_lang() . '/auth/index'); ?>';
    var params = [];

    <?php if ($search_key !== '') { ?>
    params.push('key=<?php echo rawurlencode($search_key); ?>');
    <?php } ?>

    <?php if (!empty($sort_by)) { ?>
    params.push('sort_by=<?php echo rawurlencode($sort_by); ?>');
    <?php } ?>

    <?php if (!empty($sort_order)) { ?>
    params.push('sort_order=<?php echo rawurlencode($sort_order); ?>');
    <?php } ?>

    if (groupId) {
        params.push('group_id=' + encodeURIComponent(groupId));
    }

    if (params.length > 0) {
        url += '?' + params.join('&');
    }

    window.location.href = url;
}

(function() {
    function boot() {
        if (!window.jQuery) {
            setTimeout(boot, 50);
            return;
        }
        jQuery(function($) {
            $('.btn-delete-user').on('click', function() {
                var userId = $(this).data('id');
                var userName = $(this).data('name');
                var deleteUrl = '<?php echo site_url(current_lang() . '/auth/delete_user/'); ?>/' + userId;

                if (typeof swal === 'function') {
                    swal({
                        title: 'Are you sure?',
                        text: 'You want to delete user: ' + userName + '! This will hide the user from the list.',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DD6B55',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!',
                        closeOnConfirm: false,
                        closeOnCancel: true
                    }, function(isConfirm) {
                        if (isConfirm) {
                            window.location.href = deleteUrl;
                        }
                    });
                } else if (window.confirm('Delete user: ' + userName + '?')) {
                    window.location.href = deleteUrl;
                }
            });
        });
    }
    boot();
})();
</script>
