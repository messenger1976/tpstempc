
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
<br/>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?php echo $title; ?></h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-12">
                        <a href="<?php echo site_url(current_lang() . '/backup/create_backup'); ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Create New Backup
                        </a>
                    </div>
                </div>
                <br/>
                
                <?php if (!empty($backup_list)) { ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>Backup File</th>
                                <th>Date Created</th>
                                <th>File Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backup_list as $backup) { ?>
                            <tr>
                                <td><?php echo $backup['filename']; ?></td>
                                <td><?php echo $backup['date']; ?></td>
                                <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                                <td>
                                    <a href="<?php echo site_url(current_lang() . '/backup/download/' . $backup['filename']); ?>" 
                                       class="btn btn-sm btn-info" title="Download">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                    <a href="<?php echo site_url(current_lang() . '/backup/restore/' . $backup['filename']); ?>" 
                                       class="btn btn-sm btn-warning" title="Restore"
                                       onclick="return confirm('Are you sure you want to restore this backup? This will overwrite current database data.');">
                                        <i class="fa fa-refresh"></i> Restore
                                    </a>
                                    <a href="<?php echo site_url(current_lang() . '/backup/delete/' . $backup['filename']); ?>" 
                                       class="btn btn-sm btn-danger" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this backup file?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } else { ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> No backup files found. Click "Create New Backup" to create your first backup.
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('.dataTables-example').DataTable({
        pageLength: 25,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: []
    });
});
</script>
