<?php
$this->load->view('member/topmenu');
?>

<div class="col-lg-12 member-info-page">
    <div class="row">
        <div class="col-lg-3 col-md-4">
            <?php $this->load->view('member/member_profile_sidebar'); ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <?php echo form_open(current_lang() . "/member/membercontact/" . encode_id($basicinfo->id), 'class="form-horizontal"'); ?>

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
                    <i class="fa fa-phone"></i>
                    <h4><?php echo lang('member_contact_info'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="section-divider">
                        <i class="fa fa-user"></i><?php echo lang('member_contact_personalinfo'); ?>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label">Number of Dependents :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="number" name="dependents" value="<?php echo set_value('dependents', isset($contactinfo->dependents) ? $contactinfo->dependents : ''); ?>" min="0" class="form-control"/>
                            <?php echo form_error('dependents'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label">Religion :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="religion" value="<?php echo set_value('religion', isset($contactinfo->religion) ? $contactinfo->religion : ''); ?>" class="form-control"/>
                            <?php echo form_error('religion'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_phone1'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <div class="input-group">
                                <span class="input-group-addon phone-code-addon">
                                    <select name="pre_phone1">
                                        <?php
                                        $select = substr($contactinfo->phone1, 0, -10);
                                        foreach (mobile_code() as $key => $value) {
                                            ?>
                                            <option <?php echo ($select == $value->name ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </span>
                                <input type="text" name="phone1" value="<?php echo substr($contactinfo->phone1, -10); ?>" class="form-control"/>
                            </div>
                            <?php echo form_error('phone1'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_phone2'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <div class="input-group">
                                <span class="input-group-addon phone-code-addon">
                                    <select name="pre_phone2">
                                        <?php
                                        $select1 = substr($contactinfo->phone2, 0, -9);
                                        foreach (mobile_code() as $key => $value) {
                                            ?>
                                            <option <?php echo ($select1 == $value->name ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </span>
                                <input type="text" name="phone2" value="<?php echo substr($contactinfo->phone2, -9); ?>" class="form-control"/>
                            </div>
                            <?php echo form_error('phone2'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_email'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="email" value="<?php echo $contactinfo->email; ?>" class="form-control"/>
                            <?php echo form_error('email'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_physical'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="physical" value="<?php echo $contactinfo->physicaladdress; ?>" class="form-control"/>
                            <?php echo form_error('physical'); ?>
                        </div>
                    </div>

                    <div class="section-divider">
                        <i class="fa fa-briefcase"></i><?php echo lang('member_contact_jobinfo'); ?>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label">Annual Income :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="number" name="annualincome" step="0.01" value="<?php echo set_value('annualincome', isset($contactinfo->annualincome) ? $contactinfo->annualincome : ''); ?>" class="form-control"/>
                            <?php echo form_error('annualincome'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_occupation'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="occupation" value="<?php echo $contactinfo->occupation; ?>" class="form-control"/>
                            <?php echo form_error('occupation'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_salary_grade'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="salary_grade" value="<?php echo set_value('salary_grade', isset($contactinfo->salary_grade) ? $contactinfo->salary_grade : ''); ?>" class="form-control"/>
                            <?php echo form_error('salary_grade'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_tinno'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="tinno" value="<?php echo $contactinfo->tinno; ?>" class="form-control"/>
                            <?php echo form_error('tinno'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_sssno'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="sssno" value="<?php echo $contactinfo->sssno; ?>" class="form-control"/>
                            <?php echo form_error('sssno'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_bpno'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="bpno" value="<?php echo $contactinfo->bpno; ?>" class="form-control"/>
                            <?php echo form_error('bpno'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label">Assigned School :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="assignedschool" value="<?php echo set_value('assignedschool', isset($contactinfo->assignedschool) ? $contactinfo->assignedschool : ''); ?>" class="form-control"/>
                            <?php echo form_error('assignedschool'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_contact_officeaddress'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="officeaddress" value="<?php echo $contactinfo->officeaddress; ?>" class="form-control"/>
                            <?php echo form_error('officeaddress'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_remarks'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="box" value="<?php echo $contactinfo->postaladdress; ?>" class="form-control"/>
                            <?php echo form_error('box'); ?>
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

            <script>
                (function(){
                    var form = document.querySelector('form.form-horizontal');
                    if(!form) return;
                    form.addEventListener('submit', function(e){
                        var fld = form.querySelector('[name="annualincome"]');
                        if(!fld) return true;
                        var val = fld.value.trim();
                        if(val !== ''){
                            // allow positive floats (e.g. 123, 123.45)
                            var re = /^\d+(\.\d+)?$/;
                            if(!re.test(val)){
                                e.preventDefault();
                                alert('Annual Income must be a number (e.g. 1234 or 1234.56)');
                                fld.focus();
                                return false;
                            }
                        }
                    });
                })();
            </script>
        </div>
    </div>
</div>
