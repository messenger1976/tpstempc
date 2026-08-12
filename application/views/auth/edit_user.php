<?php
$user = isset($user) ? $user : null;
$groups = isset($groups) ? $groups : array();
$currentGroups = isset($currentGroups) ? $currentGroups : array();
$first_name = isset($first_name) ? $first_name : array('value' => '');
$last_name = isset($last_name) ? $last_name : array('value' => '');
$company = isset($company) ? $company : array('value' => '');
$phone = isset($phone) ? $phone : array('value' => '');
$list_url = site_url(current_lang() . '/auth/index');
$user_label = $user ? trim($user->first_name . ' ' . $user->last_name) : 'User';
$username_label = ($user && !empty($user->username)) ? $user->username : '';
?>

<style type="text/css">
.eu-edit-page { margin-top: 4px; }
.eu-edit-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.eu-edit-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.eu-edit-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.eu-edit-page .cbu-alert.info {
    background: #eef3fb;
    color: #3c6eae;
    border: 1px solid #d6e2f5;
}
.eu-edit-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.eu-edit-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.eu-edit-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.eu-edit-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.eu-edit-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.eu-edit-page .cbu-panel .panel-body { padding: 20px; }
.eu-edit-page .form-horizontal .form-group { margin-bottom: 16px; }
.eu-edit-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.eu-edit-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.eu-edit-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.eu-edit-page .required,
.eu-edit-page .text-danger { color: #ed5565; }
.eu-edit-page .help-block {
    font-size: 12px;
    margin-top: 6px;
    margin-bottom: 0;
}
.eu-edit-page .input-group-addon {
    background: #f8fafb;
    color: #1ab394;
    border-color: #e5e6e7;
}
.eu-edit-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.eu-edit-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.eu-edit-page .form-actions {
    margin-top: 4px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f3;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.eu-edit-page .group-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}
.eu-edit-page .group-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    background: #fafbfc;
    margin: 0;
    cursor: pointer;
    font-weight: 600;
    color: #2f4050;
}
.eu-edit-page .group-item:hover {
    background: #e8f8f5;
    border-color: #c9ebe3;
}
.eu-edit-page .group-item input[type="checkbox"] {
    margin: 0;
    width: 16px;
    height: 16px;
}
.eu-edit-page .hint-box {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 12px;
    color: #888;
    margin-bottom: 16px;
}
@media (max-width: 991px) {
    .eu-edit-page .group-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575px) {
    .eu-edit-page .group-grid { grid-template-columns: 1fr; }
}
</style>

<?php echo form_open(uri_string(), 'class="form-horizontal" id="editUserForm"'); ?>

<div class="col-lg-12 eu-edit-page">
    <?php
    $flash = $this->session->flashdata('message');
    if (!empty($message)) {
        $is_error = (stripos(strip_tags($message), 'error') !== false || stripos(strip_tags($message), 'must') !== false || validation_errors());
        echo '<div class="cbu-alert ' . ($is_error ? 'danger' : 'success') . ' displaymessage">' . $message . '</div>';
    } else if ($flash != '') {
        echo '<div class="cbu-alert success displaymessage">' . $flash . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="cbu-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="cbu-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-user icon-badge"></i>
                <h4>Edit User<?php echo $user_label !== '' ? ' — ' . htmlspecialchars($user_label, ENT_QUOTES, 'UTF-8') : ''; ?></h4>
            </div>
            <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Back to Users
            </a>
        </div>
        <div class="panel-body">
            <?php if ($username_label !== '') { ?>
                <div class="hint-box">
                    <i class="fa fa-id-badge"></i>
                    Username: <strong><?php echo htmlspecialchars($username_label, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php if (!empty($user->email)) { ?>
                        &nbsp;·&nbsp; Email: <strong><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('edit_user_fname_label'); ?> <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name['value'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Enter first name"/>
                            <?php if (form_error('first_name')) { ?>
                                <span class="help-block text-danger"><i class="fa fa-exclamation-circle"></i> <?php echo form_error('first_name'); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('edit_user_lname_label'); ?> <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name['value'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Enter last name"/>
                            <?php if (form_error('last_name')) { ?>
                                <span class="help-block text-danger"><i class="fa fa-exclamation-circle"></i> <?php echo form_error('last_name'); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('edit_user_company_label'); ?> <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" name="company" value="<?php echo htmlspecialchars($company['value'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Enter company name"/>
                            <?php if (form_error('company')) { ?>
                                <span class="help-block text-danger"><i class="fa fa-exclamation-circle"></i> <?php echo form_error('company'); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('edit_user_phone_label'); ?> <span class="required">*</span></label>
                        <div class="col-lg-8">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone['value'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Enter phone number"/>
                            </div>
                            <?php if (form_error('phone')) { ?>
                                <span class="help-block text-danger"><i class="fa fa-exclamation-circle"></i> <?php echo form_error('phone'); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($this->ion_auth->is_admin()) { ?>
        <div class="cbu-panel">
            <div class="panel-head">
                <div class="head-left">
                    <i class="fa fa-users icon-badge"></i>
                    <h4><?php echo lang('edit_user_groups_heading'); ?></h4>
                </div>
            </div>
            <div class="panel-body">
                <div class="group-grid">
                    <?php foreach ($groups as $group) {
                        $gID = $group['id'];
                        $checked = '';
                        foreach ($currentGroups as $grp) {
                            if ($gID == $grp->id) {
                                $checked = ' checked="checked"';
                                break;
                            }
                        }
                        ?>
                        <label class="group-item" for="group_<?php echo (int) $group['id']; ?>">
                            <input type="checkbox"
                                   name="groups[]"
                                   value="<?php echo (int) $group['id']; ?>"
                                   id="group_<?php echo (int) $group['id']; ?>"
                                   <?php echo $checked; ?>>
                            <?php echo htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </label>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-lock icon-badge"></i>
                <h4>Change Password</h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="hint-box">
                Leave blank to keep the current password.
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('edit_user_password_label'); ?></label>
                        <div class="col-lg-8">
                            <input type="password" name="password" value="" class="form-control" autocomplete="new-password" placeholder="New password"/>
                            <?php if (form_error('password')) { ?>
                                <span class="help-block text-danger"><i class="fa fa-exclamation-circle"></i> <?php echo form_error('password'); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('edit_user_password_confirm_label'); ?></label>
                        <div class="col-lg-8">
                            <input type="password" name="password_confirm" value="" class="form-control" autocomplete="new-password" placeholder="Confirm new password"/>
                            <?php if (form_error('password_confirm')) { ?>
                                <span class="help-block text-danger"><i class="fa fa-exclamation-circle"></i> <?php echo form_error('password_confirm'); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <?php echo form_hidden('id', $user->id); ?>
                <?php echo form_hidden($csrf); ?>
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo lang('edit_user_submit_btn'); ?>
                </button>
                <a href="<?php echo $list_url; ?>" class="btn btn-default">
                    <i class="fa fa-undo"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
