<?php echo form_open_multipart(current_lang() . "/setting/mortuary_setup", 'class="form-horizontal"'); ?>

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
$mortuary = $this->setting_model->mortuary_global_info();
?>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('mortuary_amount_deduction'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-3">
        <input type="text" name="amount" value="<?php echo (set_value('amount') ? set_value('amount') : number_format($mortuary->amount,2)); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('amount'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('maintaining_balance_amount'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-3">
         <input type="text" name="maintaining_balance" value="<?php echo (set_value('maintaining_balance') ? set_value('maintaining_balance') : number_format($mortuary->maintaining_balance,2)); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('maintaining_balance'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('endangered_amount'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-3">
         <input type="text" name="endangered_amount" value="<?php echo (set_value('endangered_amount') ? set_value('endangered_amount'): number_format($mortuary->endangered_amount,2)); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('endangered_amount'); ?>
    </div>
</div>
<div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('dismember_amount'); ?>  : <span class="required">*</span></label>
    <div class="col-lg-3">
         <input type="text" name="dismember_amount" value="<?php echo (set_value('dismember_amount') ? set_value('dismember_amount'): number_format($mortuary->dismember_amount,2)); ?>"  class="form-control amountformat"/> 
        <?php echo form_error('dismember_amount'); ?>
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


<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo lang('save_info_btn'); ?>" type="submit"/>
    </div>
</div>


<?php echo form_close(); ?>
