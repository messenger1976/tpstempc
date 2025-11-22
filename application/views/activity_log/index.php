<?php echo form_open(current_lang() . "/activity_log/index", 'class="form-horizontal" method="GET"'); ?>

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

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Filter Activity Logs</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="col-lg-2">
                        <label>Search:</label>
                        <input type="text" class="form-control" name="search" value="<?php echo isset($filters['search']) ? htmlspecialchars($filters['search']) : ''; ?>" placeholder="Search..."/>
                    </div>
                    <div class="col-lg-2">
                        <label>User:</label>
                        <select class="form-control" name="user_id">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user->id; ?>" <?php echo (isset($filters['user_id']) && $filters['user_id'] == $user->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(trim(($user->first_name ?: '') . ' ' . ($user->last_name ?: '')) ?: $user->username); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label>Module:</label>
                        <select class="form-control" name="module">
                            <option value="">All Modules</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo htmlspecialchars($module->module); ?>" <?php echo (isset($filters['module']) && $filters['module'] == $module->module) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($module->module)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label>Action:</label>
                        <select class="form-control" name="action">
                            <option value="">All Actions</option>
                            <option value="login" <?php echo (isset($filters['action']) && $filters['action'] == 'login') ? 'selected' : ''; ?>>Login</option>
                            <option value="logout" <?php echo (isset($filters['action']) && $filters['action'] == 'logout') ? 'selected' : ''; ?>>Logout</option>
                            <option value="create" <?php echo (isset($filters['action']) && $filters['action'] == 'create') ? 'selected' : ''; ?>>Create</option>
                            <option value="update" <?php echo (isset($filters['action']) && $filters['action'] == 'update') ? 'selected' : ''; ?>>Update</option>
                            <option value="delete" <?php echo (isset($filters['action']) && $filters['action'] == 'delete') ? 'selected' : ''; ?>>Delete</option>
                            <option value="view" <?php echo (isset($filters['action']) && $filters['action'] == 'view') ? 'selected' : ''; ?>>View</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label>Date From:</label>
                        <input type="date" class="form-control" name="date_from" value="<?php echo isset($filters['date_from']) ? htmlspecialchars($filters['date_from']) : ''; ?>"/>
                    </div>
                    <div class="col-lg-2">
                        <label>Date To:</label>
                        <input type="date" class="form-control" name="date_to" value="<?php echo isset($filters['date_to']) ? htmlspecialchars($filters['date_to']) : ''; ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-lg-12">
                        <input type="hidden" name="filter" value="1"/>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
                        <a href="<?php echo site_url(current_lang() . '/activity_log/export?' . http_build_query(array_filter($filters))); ?>" class="btn btn-success"><i class="fa fa-download"></i> Export CSV</a>
                        <?php if ($this->ion_auth->is_admin()): ?>
                            <button type="button" class="btn btn-danger" onclick="deleteOldLogs()"><i class="fa fa-trash"></i> Delete Old Logs</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<!-- Statistics Panel -->
<?php if (isset($stats) && !empty($stats)): ?>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">Statistics (Last 30 Days)</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="well">
                            <h4><?php echo number_format($stats['total']); ?></h4>
                            <p>Total Activities</p>
                        </div>
                    </div>
                    <?php if (isset($stats['by_action']) && !empty($stats['by_action'])): ?>
                        <?php foreach ($stats['by_action'] as $action_stat): ?>
                            <div class="col-lg-2">
                                <div class="well">
                                    <h4><?php echo number_format($action_stat->count); ?></h4>
                                    <p><?php echo ucfirst($action_stat->action); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Activity Logs Table -->
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Record ID</th>
                        <th>IP Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs->num_rows() > 0): ?>
                        <?php foreach ($logs->result() as $log): ?>
                            <tr>
                                <td><?php echo $log->id; ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($log->created_at)); ?></td>
                                <td>
                                    <?php 
                                    $user_name = trim(($log->first_name ?: '') . ' ' . ($log->last_name ?: ''));
                                    echo htmlspecialchars($user_name ?: $log->username);
                                    ?>
                                </td>
                                <td>
                                    <?php if ($log->module): ?>
                                        <span class="label label-info"><?php echo htmlspecialchars(ucfirst($log->module)); ?></span>
                                    <?php else: ?>
                                        <span class="label label-default">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $action_colors = array(
                                        'login' => 'success',
                                        'logout' => 'warning',
                                        'create' => 'primary',
                                        'update' => 'info',
                                        'delete' => 'danger',
                                        'view' => 'default'
                                    );
                                    $color = isset($action_colors[$log->action]) ? $action_colors[$log->action] : 'default';
                                    ?>
                                    <span class="label label-<?php echo $color; ?>"><?php echo htmlspecialchars(ucfirst($log->action)); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($log->description); ?></td>
                                <td>
                                    <?php if ($log->record_id): ?>
                                        <?php echo $log->record_id; ?>
                                        <?php if ($log->record_type): ?>
                                            <small class="text-muted">(<?php echo htmlspecialchars($log->record_type); ?>)</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($log->ip_address); ?></small>
                                </td>
                                <td>
                                    <a href="<?php echo site_url(current_lang() . '/activity_log/view/' . encode_id($log->id)); ?>" class="btn btn-xs btn-info" title="View Details">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                <p class="text-muted">No activity logs found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-right: 20px; text-align: right;">
            <?php if (function_exists('page_selector')): ?>
                <?php page_selector(); ?>
            <?php endif; ?>
        </div>
        <?php echo $links; ?>
    </div>
</div>

<script>
function deleteOldLogs() {
    if (confirm('Are you sure you want to delete logs older than 90 days? This action cannot be undone.')) {
        var days = prompt('Enter number of days to keep (default: 90):', '90');
        if (days !== null) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo site_url(current_lang() . '/activity_log/delete_old'); ?>';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'days';
            input.value = days || 90;
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>

