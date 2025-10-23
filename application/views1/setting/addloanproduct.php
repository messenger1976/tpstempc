
<?php echo form_open_multipart(current_lang() . "/setting/addloan_product/" . $id, 'class="form-horizontal"'); ?>

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

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_name'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="name"   value="<?php echo (isset($product) ? $product->name : set_value('name')); ?>"  class="form-control"/> 
        <?php echo form_error('name'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_description'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <textarea name="description" class="form-control" ><?php echo (isset($product) ? $product->description : set_value('description')); ?></textarea>
        <?php echo form_error('description'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_interval'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="interval" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = isset($product) ? $product->interval : set_value('interval');
            foreach ($interval_list as $key => $value) {
                ?>
                <option <?php echo ($value->id == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
            <?php } ?>
        </select>
        <?php echo form_error('interval'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_interest'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="interest_rate"   value="<?php echo (isset($product) ? $product->interest_rate : set_value('interest_rate')); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('interest_rate'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_interest_method'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="interest_method" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = isset($product) ? $product->interest_method : set_value('interest_method');
            foreach ($interest_method_list as $key => $value) {
                ?>
                <option <?php echo ($value->id == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
            <?php } ?>
        </select>
        <?php echo form_error('interest_method'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_penalt_method'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="penalt_method" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = isset($product) ? $product->penalt_method : set_value('penalt_method');
            foreach ($penalt_method_list as $key => $value) {
                ?>
                <option <?php echo ($value->id == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
            <?php } ?>
        </select>
        <?php echo form_error('penalt_method'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_penalt_percentage'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="penalt_percentage"   value="<?php echo (isset($product) ? $product->penalt_percentage : set_value('penalt_percentage')); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('penalt_percentage'); ?>
    </div>
</div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_maxmum_time'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="maxmum_time"   value="<?php echo (isset($product) ? $product->maxmum_time : set_value('maxmum_time')); ?>"  class="form-control"/> 
        <?php echo form_error('maxmum_time'); ?>
    </div>
</div>

<div style="margin: 20px 0px 10px 50px; width: 80%; font-size: 16px; color: brown; font-weight: bold; border-bottom:  1px solid #ccc;"><?php echo lang('loanproduct_security'); ?></div>

<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_share'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="loan_security_share_min"   value="<?php echo (isset($product) ? $product->loan_security_share_min : set_value('loan_security_share_min')); ?>"  class="form-control"/> 
        <?php echo form_error('loan_security_share_min'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_contribution'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="loan_security_contribution_min"   value="<?php echo (isset($product) ? $product->loan_security_contribution_min : set_value('loan_security_contribution_min')); ?>"  class="form-control"/> 
        <?php echo form_error('loan_security_contribution_min'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_saving'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="loan_security_saving_minimum"   value="<?php echo (isset($product) ? $product->loan_security_saving_minimum : set_value('loan_security_saving_minimum')); ?>"  class="form-control"/> 
        <?php echo form_error('loan_security_saving_minimum'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_contribution_times'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <input type="text" name="loanproduct_contribution_times"   value="<?php echo (isset($product) ? $product->loan_security_contribution_times : set_value('loanproduct_contribution_times')); ?>"  class="form-control"/> 
        <?php echo form_error('loanproduct_contribution_times'); ?>
    </div>
</div>

<div style="margin: 20px 0px 10px 50px; width: 80%; font-size: 16px; color: brown; font-weight: bold; border-bottom:  1px solid #ccc;"><?php echo lang('loanproduct_account'); ?></div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_account_principle'); ?>  : <span class="required">*</span> </label>
    <div class="col-lg-6">
        <select name="loan_principle_account" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = isset($product) ? $product->loan_principle_account : set_value('loan_principle_account');
            foreach ($account_list as $key => $value) {
                ?>
                <optgroup label="<?php echo $value['info']->name; ?>">
                    <?php foreach ($value['data'] as $key1 => $value1) { ?>
                        <option <?php echo ($value1->account == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value1->account; ?>"><?php echo $value1->name; ?></option>
                    <?php } ?>
                </optgroup>

            <?php } ?>
        </select>
        <?php echo form_error('loan_principle_account'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_account_interest'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="loan_interest_account" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = isset($product) ? $product->loan_interest_account : set_value('loan_interest_account');
            foreach ($account_list as $key => $value) {
                ?>
                <optgroup label="<?php echo $value['info']->name; ?>">
                    <?php foreach ($value['data'] as $key1 => $value1) { ?>
                        <option <?php echo ($value1->account == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value1->account; ?>"><?php echo $value1->name; ?></option>
                    <?php } ?>
                </optgroup>

            <?php } ?>
        </select>
        <?php echo form_error('loan_interest_account'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('loanproduct_account_penalt'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select name="loan_penalt_account" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = isset($product) ? $product->loan_penalt_account : set_value('loan_penalt_account');
            foreach ($account_list as $key => $value) {
                ?>
                <optgroup label="<?php echo $value['info']->name; ?>">
                    <?php foreach ($value['data'] as $key1 => $value1) { ?>
                        <option <?php echo ($value1->account == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value1->account; ?>"><?php echo $value1->name; ?></option>
                    <?php } ?>
                </optgroup>

            <?php } ?>
        </select>
        <?php echo form_error('loan_penalt_account'); ?>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('tax_addbtn'); ?>" type="submit"/>
    </div>
</div>

<?php echo form_close(); ?>