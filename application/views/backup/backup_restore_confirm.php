
<?php echo form_open(current_lang() . "/backup/restore/" . $filename, 'class="form-horizontal"'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?php echo $title; ?></h5>
            </div>
            <div class="ibox-content">
                <div class="alert alert-warning">
                    <i class="fa fa-warning"></i> <strong>Warning!</strong> 
                    <p>You are about to restore the database from the backup file: <strong><?php echo $filename; ?></strong></p>
                    <p>This action will overwrite all current database data with the data from the backup file.</p>
                    <p><strong>This action cannot be undone!</strong></p>
                    <p>It is recommended to create a backup of your current database before proceeding.</p>
                </div>

                <div class="form-group">
                    <div class="col-lg-12">
                        <a href="<?php echo site_url(current_lang() . '/backup/index'); ?>" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Cancel
                        </a>
                        <input type="hidden" name="confirm" value="1"/>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you absolutely sure you want to restore this backup?');">
                            <i class="fa fa-refresh"></i> Confirm Restore
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
