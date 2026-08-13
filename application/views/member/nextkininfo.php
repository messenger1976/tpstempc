<?php
$this->load->view('member/topmenu');
?>

<div class="col-lg-12 member-info-page">
    <div class="row">
        <div class="col-lg-3 col-md-4">
            <?php $this->load->view('member/member_profile_sidebar'); ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <?php echo form_open(current_lang() . "/member/membernextkin/" . encode_id($basicinfo->id), 'class="form-horizontal"'); ?>

            <?php
            if (isset($message) && !empty($message)) {
                echo '<div class="member-alert success displaymessage">' . $message . '</div>';
            } else if ($this->session->flashdata('message') != '') {
                echo '<div class="member-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
            } else if (isset($warning) && !empty($warning)) {
                echo '<div class="member-alert danger displaymessage">' . $warning . '</div>';
            } else if ($this->session->flashdata('warning') != '') {
                echo '<div class="member-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
            }
            ?>

            <div class="member-form-panel">
                <div class="panel-head">
                    <i class="fa fa-users"></i>
                    <h4><?php echo lang('member_nextkin_info'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('nextkin_name'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="name" value="<?php echo ($this->input->post('name')) ? $this->input->post('name') : $nextkininfo->name; ?>" class="form-control" required/>
                            <?php echo form_error('name'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('nextkin_relationship'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <select id="relationship" name="relationship" class="form-control" required>
                                    <option value="">-- Select --</option>
                                    <option value="Parent" <?php echo ($nextkininfo->relationship == 'Parent' ? 'selected="selected"' : ''); ?>>Parent</option>
                                    <option value="Sibling" <?php echo ($nextkininfo->relationship == 'Sibling' ? 'selected="selected"' : ''); ?>>Sibling</option>
                                    <option value="Spouse" <?php echo ($nextkininfo->relationship == 'Spouse' ? 'selected="selected"' : ''); ?>>Spouse</option>
                                    <option value="Child" <?php echo ($nextkininfo->relationship == 'Child' ? 'selected="selected"' : ''); ?>>Child</option>
                                    <option value="Friend" <?php echo ($nextkininfo->relationship == 'Friend' ? 'selected="selected"' : ''); ?>>Friend</option>
                                </select>
                            </div>
                            <?php echo form_error('relationship'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_phone1'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <div class="input-group">
                                <span class="input-group-addon phone-code-addon">
                                    <select name="pre_phone1">
                                        <?php
                                        $select = substr($nextkininfo->phone, 0, -9);
                                        foreach (mobile_code() as $key => $value) {
                                            ?>
                                            <option <?php echo ($select == $value->name ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </span>
                                <input type="text" name="phone1" value="<?php echo substr(($this->input->post('phone1')) ? $this->input->post('phone1') : $nextkininfo->phone, -10); ?>" class="form-control"/>
                            </div>
                            <?php echo form_error('phone1'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_email'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="email" value="<?php echo $nextkininfo->email; ?>" class="form-control"/>
                            <?php echo form_error('email'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_box'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="box" value="<?php echo $nextkininfo->postaladdress; ?>" class="form-control"/>
                            <?php echo form_error('box'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_physical'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="physical" value="<?php echo $nextkininfo->physicaladdress; ?>" class="form-control"/>
                            <?php echo form_error('physical'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label">Source of Income :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="sourceofincome" value="<?php echo isset($nextkininfo->sourceofincome) ? $nextkininfo->sourceofincome : ''; ?>" class="form-control"/>
                            <?php echo form_error('sourceofincome'); ?>
                        </div>
                    </div>

                    <div class="form-group member-form-actions">
                        <label class="col-lg-3 col-md-4 control-label">&nbsp;</label>
                        <div class="col-lg-7 col-md-8">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-save"></i> <?php echo lang('member_contactbtn'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<script>
(function(){
    var memberFirst = <?php echo json_encode($basicinfo->firstname); ?>;
    var memberLast = <?php echo json_encode($basicinfo->lastname); ?>;
    var memberEmail = <?php echo json_encode(isset($basicinfo->email) ? $basicinfo->email : ''); ?>;
    var memberPhone = <?php echo json_encode(isset($basicinfo->phone) ? $basicinfo->phone : ''); ?>;
    var memberBox = <?php echo json_encode(isset($basicinfo->postaladdress) ? $basicinfo->postaladdress : ''); ?>;
    var memberPhysical = <?php echo json_encode(isset($basicinfo->physicaladdress) ? $basicinfo->physicaladdress : ''); ?>;

    function splitPhone(phone){
        if(!phone) return {pre:'', rest:''};
        if(phone.length <=9) return {pre:'', rest:phone};
        return {pre: phone.slice(0, -9), rest: phone.slice(-9)};
    }

    var relationship = document.getElementById('relationship');
    function doFill(){
        var nameField = document.querySelector('input[name="name"]');
        var prePhone = document.querySelector('select[name="pre_phone1"]');
        var phone1 = document.querySelector('input[name="phone1"]');
        var email = document.querySelector('input[name="email"]');
        var box = document.querySelector('input[name="box"]');
        var physical = document.querySelector('input[name="physical"]');
        if(!nameField) return;
        nameField.value = (memberFirst||'') + (memberLast?(' '+memberLast):'');
        var p = splitPhone(memberPhone || '');
        if(prePhone && p.pre) prePhone.value = p.pre;
        if(phone1 && !phone1.value.trim()) phone1.value = p.rest || '';
        if(email) email.value = memberEmail || '';
        if(box) box.value = memberBox || '';
        if(physical) physical.value = memberPhysical || '';
    }

    // Fill helper that DOES NOT touch the name field (used when relationship changes)
    function doFillOthers(){
        var prePhone = document.querySelector('select[name="pre_phone1"]');
        var phone1 = document.querySelector('input[name="phone1"]');
        var email = document.querySelector('input[name="email"]');
        var box = document.querySelector('input[name="box"]');
        var physical = document.querySelector('input[name="physical"]');
        var p = splitPhone(memberPhone || '');
        if(prePhone && p.pre) prePhone.value = p.pre;
        if(phone1 && !phone1.value.trim()) phone1.value = p.rest || '';
        if(email) email.value = memberEmail || '';
        if(box) box.value = memberBox || '';
        if(physical) physical.value = memberPhysical || '';
    }

    if(relationship){
        // On relationship change, update contact details but do NOT overwrite the name.
        relationship.addEventListener('change', function(){ if(relationship.value) doFillOthers(); });
    }

    var form = document.querySelector('form');
    if(form){
        form.addEventListener('submit', function(e){
            var errs = [];
            var nameField = document.querySelector('input[name="name"]');
            var rel = document.querySelector('select[name="relationship"]');
            if(!nameField || !nameField.value.trim()) errs.push('Name is required');
            if(!rel || !rel.value.trim()) errs.push('Relationship is required');
            if(errs.length){
                e.preventDefault();
                alert(errs.join('\n'));
            }
        });
    }
})();
</script>
