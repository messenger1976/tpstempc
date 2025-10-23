<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>
<?php echo form_open_multipart(current_lang() . "/mortuary/automatic_mortuary_process", 'class="form-horizontal"'); ?>

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

        <div class="form-group">
            <label class="col-lg-3 control-label">Claimant Member ID</label>
            <div class="col-lg-5">
                <input class="form-control" name="key" id="accountno"  value="<?php echo (isset($_GET['key']) ? $_GET['key'] : ''); ?>"/>
                <?php echo form_error('key'); ?>
            </div>
            
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">Process Mortuary for this month</label>
            <div class="col-lg-2">
                <input class="form-control" name="date_month" value="<?php echo date('m-Y') ?>"/>
                <?php echo form_error('date_month'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">&nbsp;</label>
            <div class="col-lg-6">
                <input class="btn btn-primary" value="Process Mortuary" type="submit"/>
            </div>
        </div>

        <script type="text/javascript" src="<?php echo base_url(); ?>media/js/jquery.autocomplete_origin.js" ></script>
        <script>
		$(document).ready(function() {
            $("#accountno").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_member_id_all/'); ?>",{
                matchContains:true
            });
			$('#member_id').on('input', function() {
				var search_val = $(this).val();
				$.ajax({
					type: 'POST',
					url: '<?php echo site_url(current_lang() . '/mortuary/recomputebalances/'); ?>',
					data: { search_val: search_val },
					success: function(response) {
						$('#member_fullname').val(response);
					}
				});
			});
		});
	</script>
    

<?php echo form_close(); ?>