<?php
if(isset($feedback)){ ?>
<div class="btn-info" style="width: 100%;"><?php echo $imported; ?> row(s) imported successfully..</div>
<br/>
<br/>
<?php
    
    foreach ($feedback as $key => $value) {
        echo  $value;
    
    }
    
    
}else{

echo form_open_multipart(current_lang() . "/import/import_share/" , 'class="form-horizontal"'); ?>
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
<input type="hidden" value="1" name="upload"/>
<div class="form-group"><label class="col-lg-3 control-label"></label>
    <div class="col-lg-6">
        <a href="<?php echo base_url().'doc/import_member_share.xls' ?>">Download Templates</a>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Choose File (.xls)'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input name="file" type="file" />
         <?php echo isset($logo_error) ? '<div class="form-group required"><br/>' . $logo_error . '</div>' : ''; ?> 
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo 'Import'; ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); 

}

?>

