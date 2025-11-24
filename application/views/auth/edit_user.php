<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo (strpos($message, 'success') !== false || strpos($message, 'Success') !== false) ? 'success' : 'info'; ?> alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>
                
                <div class="form-group">
                    <label class="col-lg-3 control-label">
                        <?php echo lang('edit_user_fname_label'); ?>
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-6">
                        <input type="text" 
                               name="first_name" 
                               value="<?php echo $first_name['value']; ?>" 
                               class="form-control" 
                               placeholder="Enter first name"/>
                        <?php if (form_error('first_name')): ?>
                            <span class="help-block m-b-none text-danger">
                                <i class="fa fa-exclamation-circle"></i> <?php echo form_error('first_name'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">
                        <?php echo lang('edit_user_lname_label'); ?>
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-6">
                        <input type="text" 
                               name="last_name" 
                               value="<?php echo $last_name['value']; ?>" 
                               class="form-control" 
                               placeholder="Enter last name"/>
                        <?php if (form_error('last_name')): ?>
                            <span class="help-block m-b-none text-danger">
                                <i class="fa fa-exclamation-circle"></i> <?php echo form_error('last_name'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">
                        <?php echo lang('edit_user_company_label'); ?>
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-6">
                        <input type="text" 
                               name="company" 
                               value="<?php echo $company['value']; ?>" 
                               class="form-control" 
                               placeholder="Enter company name"/>
                        <?php if (form_error('company')): ?>
                            <span class="help-block m-b-none text-danger">
                                <i class="fa fa-exclamation-circle"></i> <?php echo form_error('company'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">
                        <?php echo lang('edit_user_phone_label'); ?>
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-6">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                            <input type="text" 
                                   name="phone" 
                                   value="<?php echo $phone['value']; ?>" 
                                   class="form-control" 
                                   placeholder="Enter phone number"/>
                        </div>
                        <?php if (form_error('phone')): ?>
                            <span class="help-block m-b-none text-danger">
                                <i class="fa fa-exclamation-circle"></i> <?php echo form_error('phone'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($this->ion_auth->is_admin()): ?>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label">
                            <?php echo lang('edit_user_groups_heading'); ?>
                        </label>
                        <div class="col-lg-9">
                            <div class="row">
                                <?php foreach ($groups as $group): ?>
                                    <?php
                                    $gID = $group['id'];
                                    $checked = null;
                                    foreach($currentGroups as $grp) {
                                        if ($gID == $grp->id) {
                                            $checked = ' checked="checked"';
                                            break;
                                        }
                                    }
                                    ?>
                                    <div class="col-lg-4 col-md-6" style="margin-bottom: 10px;">
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" 
                                                   name="groups[]" 
                                                   value="<?php echo $group['id']; ?>" 
                                                   id="group_<?php echo $group['id']; ?>"
                                                   <?php echo $checked; ?>>
                                            <label for="group_<?php echo $group['id']; ?>">
                                                <?php echo htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php echo form_hidden('id', $user->id); ?>
                <?php echo form_hidden($csrf); ?>

                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-lg-offset-3 col-lg-9">
                        <a href="javascript:history.back()" class="btn btn-white">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                        <button class="btn btn-primary" type="submit">
                            <i class="fa fa-save"></i> <?php echo lang('edit_user_submit_btn'); ?>
                        </button>
                    </div>
                </div>

<?php echo form_close(); ?>

