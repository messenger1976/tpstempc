<?php echo form_open_multipart(current_lang() . "/setting/mobile_notification/" . $id, 'class="form-horizontal"'); ?>
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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Group'; ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="group" class="form-control">
            <option value="">--Select--</option>
            <?php
            $select = isset($smscontact) ? $smscontact->group : set_value('group');
            $grouplist = array('NEW_LOAN', 'APROVE_LOAN', 'DISBURSE_LOAN');

            foreach ($grouplist as $key => $value) {
                ?>
                <option <?php echo ($select == $value ? 'selected="selected"' : ''); ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
            <?php }
            ?>
        </select>   
            <?php echo form_error('group'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label">Mobile  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <div class="input-group"><span class="input-group-addon" style="border: 0px; padding: 0px 5px 0px 0px; margin: 0px"> <select name="pre_phone1" style="background: transparent; padding: 7px;  border:  1px solid #E5E6E7">
<?php
$select = substr((isset($smscontact) ? $smscontact->mobile : set_value('mobile')), 0, -9);
foreach (mobile_code() as $key => $value) {
    ?>
                        <option <?php echo ($select == $value->name ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                    <?php } ?>
                </select> </span><input type="text" name="mobile" value="<?php echo substr((isset($smscontact) ? $smscontact->mobile : set_value('mobile')), -9); ?>"  class="form-control"/> </div>
                    <?php echo form_error('mobile'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('save_info_btn'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>


<?php
if (count($list) > 0) { ?>

<div class="btn btn-primary" style="width: 100%; text-align: left;">Mobile Numbers</div>

<table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo lang('sno'); ?></th>
                <th><?php echo 'Group'; ?></th>
                <th><?php echo 'Mobile'; ?></th>
                <th><?php echo lang('actioncolumn'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
                foreach ($list as $key => $value) {
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $value->group ?></td>
                        <td><?php echo $value->mobile ?></td>
                        <td style="width: 200px;">
                            <a href="<?php echo site_url(current_lang() . '/setting/mobile_notification/' . ($value->id)); ?>"><i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?> </a>
                            &nbsp; &nbsp; | &nbsp; &nbsp; <a href="<?php echo site_url(current_lang() . '/setting/mobile_notification_delete/' . ($value->id)); ?>"><i class="fa fa-remove"></i> <?php echo lang('button_delete'); ?> </a>

                        </td>
                    </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>

<?php 
}
?>
