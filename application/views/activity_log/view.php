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
                <h3 class="panel-title">Activity Log Details</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Log ID:</th>
                                <td><?php echo $log->id; ?></td>
                            </tr>
                            <tr>
                                <th>Date/Time:</th>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($log->created_at)); ?></td>
                            </tr>
                            <tr>
                                <th>User:</th>
                                <td>
                                    <?php 
                                    $user_name = trim(($log->first_name ?: '') . ' ' . ($log->last_name ?: ''));
                                    echo htmlspecialchars($user_name ?: $log->username);
                                    if ($log->email) {
                                        echo ' <small class="text-muted">(' . htmlspecialchars($log->email) . ')</small>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>User ID:</th>
                                <td><?php echo $log->user_id; ?></td>
                            </tr>
                            <tr>
                                <th>Username:</th>
                                <td><?php echo htmlspecialchars($log->username); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-lg-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Module:</th>
                                <td>
                                    <?php if ($log->module): ?>
                                        <span class="label label-info"><?php echo htmlspecialchars(ucfirst($log->module)); ?></span>
                                    <?php else: ?>
                                        <span class="label label-default">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Action:</th>
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
                            </tr>
                            <tr>
                                <th>Record Type:</th>
                                <td>
                                    <?php if ($log->record_type): ?>
                                        <?php echo htmlspecialchars(ucfirst($log->record_type)); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Record ID:</th>
                                <td>
                                    <?php if ($log->record_id): ?>
                                        <?php echo $log->record_id; ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>IP Address:</th>
                                <td><?php echo htmlspecialchars($log->ip_address); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table table-bordered">
                            <tr>
                                <th width="15%">Description:</th>
                                <td><?php echo htmlspecialchars($log->description); ?></td>
                            </tr>
                            <tr>
                                <th>User Agent:</th>
                                <td>
                                    <small><?php echo htmlspecialchars($log->user_agent); ?></small>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-12">
                        <a href="<?php echo site_url(current_lang() . '/activity_log'); ?>" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

