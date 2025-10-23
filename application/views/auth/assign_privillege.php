<?php echo form_open(current_url(), 'class="form-horizontal"'); ?>

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
<div style="padding-bottom: 10px;"><h3><?php echo lang('edit_group_name_label') ?> : <?php echo $group_info->name.'   ===> '.$group_info->description; ?></h3></div>
<?php
      foreach ($privilege_list[0] as $key => $value) {
           
            ?>
        <fieldset style="width: 90%; padding-bottom: 20px;">
            <legend style="font-weight: bold; text-transform: uppercase;"><?php echo 'Module : '.$key;?></legend>
            <?php 
                        foreach ($value as $k => $v) { 
                            $module_id = $privilege_list[1][$key][$k];
                            ?>
            <div  style="border-bottom: 1px dotted black; margin-left: 20px;">
                <label class="col-lg-4" style="text-align: left; " ><?php echo $k ?></label>
                <input  type="checkbox" name="module_<?php echo $module_id[0].'_'.$module_id[1] ?>" <?php echo ($v==1 ? 'checked="checked"':''); ?> class="pure-input-1-2" value="1" placeholder="Group name">

            </div>    
                        <?php }
            ?>
        </fieldset>
<?php }
        ?>

<div style="text-align: center;">
    
    <input type="submit" value="<?php echo lang('privillege_btn_save'); ?>" name="save" class="btn btn-primary"/>
</div>



<?php echo form_close(); ?>