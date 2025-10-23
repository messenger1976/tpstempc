
<?php echo form_open_multipart(current_lang() . "/setting/additems_invoice/" . $id, 'class="form-horizontal"'); ?>

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


<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('salesinvoiceitem_code'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="code"  <?php echo (isset($iteminfo) ? 'disabled="disabled"' : ''); ?> value="<?php echo (isset($iteminfo) ? $iteminfo->code : set_value('code')); ?>"  class="form-control"/> 
        <?php echo form_error('code'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('salesinvoiceitem_name'); ?>  :  <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="name" value="<?php echo (isset($iteminfo) ? $iteminfo->name : set_value('name')); ?>"  class="form-control"/> 
        <?php echo form_error('name'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('salesinvoiceitem_description'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <textarea type="text" name="description" class="form-control"><?php echo (isset($iteminfo) ? $iteminfo->description : set_value('description')); ?> </textarea>
        <?php echo form_error('description'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('salesinvoiceitem_price'); ?>  :  <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="price" value="<?php echo (isset($iteminfo) ? $iteminfo->price : set_value('price')); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('price'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('salesinvoiceitem_type'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="invoicetype" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = isset($iteminfo) ? $iteminfo->invoicetype : set_value('invoicetype');
            foreach ($invoicetype_list as $key => $value) {
                ?>
                <option <?php echo ($value->id == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
            <?php } ?>
        </select>
        <?php echo form_error('invoicetype'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('salesinvoiceitem_account'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <select name="account" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = isset($iteminfo) ? $iteminfo->account : set_value('account');
            foreach ($account_list as $key => $value) {
                ?>
            <optgroup label="<?php echo $value['info']->name; ?>">
                <?php  foreach ($value['data'] as $key1 => $value1) { ?>
                <option <?php echo ($value1->account == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value1->account; ?>"><?php echo $value1->name; ?></option>
                <?php } ?>
            </optgroup>
               
            <?php } ?>
        </select>
        <?php echo form_error('account'); ?>
    </div>
</div>


<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('salesinvoiceitem_taxcode'); ?>  : </label>
    <div class="col-lg-6">
        <select name="taxcode" class="form-control">
            <option value=""><?php echo lang('salesinvoiceitem_no_taxcode'); ?></option>
            <?php
            $selected = isset($iteminfo) ? $iteminfo->taxcode : set_value('taxcode');
            foreach ($taxcode_list as $key => $value) {
                ?>
                <option <?php echo ($value->code == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->code; ?>"><?php echo $value->code . ' - ' . $value->rate . '%'; ?></option>
            <?php } ?>
        </select>
        <?php echo form_error('taxcode'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('tax_addbtn'); ?>" type="submit"/>
    </div>
</div>
<?php echo form_close(); ?>