
<?php echo form_open_multipart(current_lang() . "/setting/global_setting/" , 'class="form-horizontal"'); ?>

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
<?php
foreach ($default_list as $key => $value) { ?>
   <div class="form-group">
       <label class="col-lg-4 control-label"><?php echo str_replace('_', ' ', $value->key); ?></label>
    <div class="col-lg-5">
        <?php if($value->is_number == 0){ ?>
        <textarea name="field_<?php echo $value->id; ?>" class="form-control"><?php echo $value->text; ?></textarea>
        <?php }else{ ?>
        <input name="field_<?php echo $value->id; ?>" class="form-control " value="<?php echo $value->text; ?>" />
        <?php } ?>
         <?php echo form_error('field_'.$value->id); ?>
    </div>
      
</div> 
<?php }
?>

<input type="hidden" value="1" name="SAVEDATA"/>
<div class="form-group">
    <label class="col-lg-4 control-label">&nbsp;</label>
    <div class="col-lg-5">
        <input class="btn btn-primary" value="<?php echo lang('tax_addbtn'); ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>