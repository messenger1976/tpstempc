<div class="row">
    <div class="col-lg-12">
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-success alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <?php echo $this->session->flashdata('message'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <!-- Backup Actions -->
        <div class="ibox">
            <div class="ibox-title">
                <h5><i class="fa fa-database"></i> Database Backup Operations</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-12">
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> Create, download, and manage your database backups. 
                            Regular backups are essential for data security and disaster recovery.
                        </p>
                    </div>
                </div>
                <div class="row" style="margin-top: 15px;">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary" onclick="createBackup()">
                            <i class="fa fa-plus"></i> Create New Backup
                        </button>
                        <a href="<?php echo base_url('backups/'); ?>" target="_blank" class="btn btn-info">
                            <i class="fa fa-folder-open"></i> Open Backup Folder
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup List -->
        <div class="ibox">
            <div class="ibox-title">
                <h5><i class="fa fa-list"></i> Available Backups (<?php echo count($backups); ?>)</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><i class="fa fa-file"></i> Filename</th>
                                <th><i class="fa fa-hdd-o"></i> Size</th>
                                <th><i class="fa fa-calendar"></i> Created Date</th>
                                <th><i class="fa fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($backups)): ?>
                                <?php $counter = 1; ?>
                                <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td>
                                            <i class="fa fa-database text-navy"></i> 
                                            <strong><?php echo $backup['filename']; ?></strong>
                                        </td>
                                        <td><?php echo $backup['size']; ?></td>
                                        <td><?php echo $backup['date']; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo site_url(current_lang() . '/backup/download/' . urlencode($backup['filename'])); ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="Download Backup">
                                                    <i class="fa fa-download"></i> Download
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="deleteBackup('<?php echo addslashes($backup['filename']); ?>')"
                                                        title="Delete Backup">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning" 
                                                        onclick="restoreBackup('<?php echo addslashes($backup['filename']); ?>')"
                                                        title="Restore from this Backup">
                                                    <i class="fa fa-upload"></i> Restore
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> No backups found. Create your first backup now!
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Information Panel -->
        <div class="ibox">
            <div class="ibox-title">
                <h5><i class="fa fa-question-circle"></i> Backup Information</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fa fa-shield"></i> Best Practices</h4>
                        <ul>
                            <li>Create regular backups (daily, weekly, monthly)</li>
                            <li>Download and store backups in a safe location</li>
                            <li>Test your backups periodically</li>
                            <li>Keep multiple backup versions</li>
                            <li>Store backups offline or in cloud storage</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="fa fa-exclamation-triangle"></i> Important Notes</h4>
                        <ul>
                            <li>Backup files are stored in: <code>backups/</code> folder</li>
                            <li>Restoring a backup will overwrite current data</li>
                            <li>Always create a backup before restoring</li>
                            <li>Only administrators can access this module</li>
                            <li>All backup operations are logged</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Backup Operations -->
<script>
function createBackup() {
    swal({
        title: "Create Database Backup?",
        text: "This will create a backup of the entire database. This may take a few moments.",
        type: "info",
        showCancelButton: true,
        confirmButtonColor: "#1ab394",
        confirmButtonText: "Yes, Create Backup!",
        cancelButtonText: "Cancel",
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function() {
        window.location.href = "<?php echo site_url(current_lang() . '/backup/create'); ?>";
    });
}

function deleteBackup(filename) {
    swal({
        title: "Delete Backup?",
        text: "Are you sure you want to delete backup: " + filename + "? This action cannot be undone!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, Delete It!",
        cancelButtonText: "Cancel",
        closeOnConfirm: false
    }, function() {
        window.location.href = "<?php echo site_url(current_lang() . '/backup/delete/'); ?>" + encodeURIComponent(filename);
    });
}

function restoreBackup(filename) {
    swal({
        title: "Restore Database?",
        text: "WARNING: This will overwrite ALL current data with the backup: " + filename + ". This action cannot be undone! Make sure you have a current backup before proceeding.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, Restore Database!",
        cancelButtonText: "Cancel",
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function(isConfirm) {
        if (isConfirm) {
            // Double confirmation
            swal({
                title: "Final Confirmation",
                text: "Are you ABSOLUTELY sure? This will replace ALL current data!",
                type: "error",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, I'm Sure!",
                cancelButtonText: "Cancel"
            }, function(confirmed) {
                if (confirmed) {
                    window.location.href = "<?php echo site_url(current_lang() . '/backup/restore/'); ?>" + encodeURIComponent(filename);
                }
            });
        }
    });
}

// Auto-dismiss alerts after 5 seconds
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<style>
.btn-group {
    display: flex;
    gap: 5px;
}

.table thead th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.ibox {
    margin-bottom: 20px;
}

.alert {
    margin-bottom: 20px;
}
</style>
