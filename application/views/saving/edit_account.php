<style type="text/css">
.saving-edit-page { margin-top: 4px; }
.saving-edit-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.saving-edit-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.saving-edit-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.saving-edit-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.saving-edit-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.saving-edit-page .cbu-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.saving-edit-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.saving-edit-page .cbu-panel .panel-body { padding: 22px 20px 12px; overflow: visible; }
.saving-edit-page .form-horizontal .form-group { margin-bottom: 16px; }
.saving-edit-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.saving-edit-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.saving-edit-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.saving-edit-page .form-control[readonly] {
    background: #f8fafb;
    color: #676a6c;
    cursor: not-allowed;
}
.saving-edit-page .help-block {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
    margin-bottom: 0;
}
.saving-edit-page .required { color: #ed5565; }
.saving-edit-page .cbu-actions {
    margin-top: 8px;
    margin-bottom: 8px;
    padding-top: 12px;
    border-top: 1px solid #f0f2f3;
}
.saving-edit-page .cbu-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
    border-radius: 6px;
    font-weight: 600;
    margin-right: 8px;
}
.saving-edit-page .cbu-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
.saving-edit-page .cbu-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.saving-edit-page .cbu-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.saving-edit-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 20px;
    min-height: 280px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    text-align: center;
}
.saving-edit-page .cbu-preview-empty {
    text-align: center;
    color: #999;
    padding: 40px 12px;
}
.saving-edit-page .cbu-preview-empty i {
    font-size: 42px;
    color: #c9ebe3;
    display: block;
    margin-bottom: 12px;
}
.saving-edit-page .cbu-member-photo {
    width: 88px;
    height: 88px;
    margin: 0 auto 14px;
    border-radius: 50%;
    background: #e8f8f5;
    border: 3px solid #1ab394;
    box-shadow: 0 4px 14px rgba(26,179,148,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
}
.saving-edit-page .cbu-member-photo i {
    font-size: 36px;
    color: #1ab394;
}
.saving-edit-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.saving-edit-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.saving-edit-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.saving-edit-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.saving-edit-page .cbu-member-details {
    text-align: left;
    margin: 0 0 16px;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.saving-edit-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.saving-edit-page .cbu-member-details li:last-child { border-bottom: 0; }
.saving-edit-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.saving-edit-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.saving-edit-page .cbu-balance {
    background: linear-gradient(165deg, #1ab394 0%, #18a689 100%);
    color: #fff;
    border-radius: 10px;
    padding: 14px 16px;
    text-align: center;
}
.saving-edit-page .cbu-balance .lbl {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    opacity: .9;
    margin-bottom: 4px;
}
.saving-edit-page .cbu-balance .val {
    font-size: 22px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.saving-edit-page .saving-accounts-card {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-top: 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.saving-edit-page .saving-accounts-card .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.saving-edit-page .saving-accounts-card .panel-head i {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.saving-edit-page .saving-accounts-card .panel-head h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #2f4050;
}
.saving-edit-page .saving-accounts-card .panel-body { padding: 0; }
.saving-edit-page .saving-account-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px dashed #eef1f2;
}
.saving-edit-page .saving-account-row:last-child { border-bottom: 0; }
.saving-edit-page .saving-account-row.current { background: #f7fcfa; }
.saving-edit-page .saving-account-row .acct-meta { text-align: left; min-width: 0; }
.saving-edit-page .saving-account-row .acct {
    display: block;
    font-weight: 700;
    color: #2f4050;
    font-size: 13px;
}
.saving-edit-page .saving-account-row .type {
    display: block;
    color: #888;
    font-size: 12px;
    margin-top: 2px;
}
.saving-edit-page .saving-account-row .bal-wrap { text-align: right; white-space: nowrap; }
.saving-edit-page .saving-account-row .bal {
    display: block;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #0e7c69;
    font-size: 14px;
}
.saving-edit-page .saving-account-row .status-pill {
    display: inline-block;
    margin-top: 4px;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    color: #fff;
}
.saving-edit-page .saving-account-row .status-pill.active { background: #1ab394; }
.saving-edit-page .saving-account-row .status-pill.inactive { background: #ed5565; }
.saving-edit-page .empty-accounts {
    text-align: center;
    color: #999;
    padding: 28px 16px;
    font-size: 13px;
}
.saving-edit-page .empty-accounts i {
    display: block;
    font-size: 28px;
    color: #c9ebe3;
    margin-bottom: 8px;
}
</style>

<?php echo form_open(current_lang() . "/saving/edit_saving_account/" . $id, 'class="form-horizontal"'); ?>

<?php
$account_info = isset($account_info) ? $account_info : null;
$member_id_val = $account_info && isset($account_info->member_member_id) ? $account_info->member_member_id : ($account_info ? $account_info->member_id : '');
$account_val = $account_info ? ($account_info->old_members_acct ? $account_info->old_members_acct : $account_info->account) : '';
$full_name = $account_info ? trim($account_info->lastname . ', ' . $account_info->firstname . ' ' . $account_info->middlename) : '';
$status_value = $account_info && isset($account_info->status) ? $account_info->status : '1';
$is_active = ($status_value == '1' || $status_value === 1);
$acc_freq = ($account_info && isset($account_info->interest_frequency) && $account_info->interest_frequency !== null && $account_info->interest_frequency !== '')
    ? strtoupper($account_info->interest_frequency) : 'INHERIT';
if ($acc_freq == 'MONTHLY') {
    $freq_label = lang('interest_frequency_monthly') . ' (' . lang('interest_frequency_override_label') . ')';
} else if ($acc_freq == 'QUARTERLY') {
    $freq_label = lang('interest_frequency_quarterly') . ' (' . lang('interest_frequency_override_label') . ')';
} else if ($acc_freq == 'NONE') {
    $freq_label = lang('interest_frequency_none') . ' (' . lang('interest_frequency_override_label') . ')';
} else {
    $freq_label = lang('interest_frequency_inherit');
}
?>

<div class="col-lg-12 saving-edit-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="cbu-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="cbu-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="cbu-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="cbu-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="cbu-panel">
                <div class="panel-head">
                    <i class="fa fa-pencil"></i>
                    <h4><?php echo lang('edit_saving_account'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('account_number'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="account" value="<?php echo set_value('account', $account_val); ?>" class="form-control" required/>
                            <?php echo form_error('account'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('member_member_id'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="text" name="member_id" value="<?php echo set_value('member_id', $member_id_val); ?>" class="form-control" readonly required/>
                            <?php echo form_error('member_id'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('account_type'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <select name="account_cat" class="form-control" required>
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php
                                $selected = set_value('account_cat', $account_info ? $account_info->account_cat : '');
                                foreach ($account_list as $key => $value) {
                                    ?>
                                    <option <?php echo ($value->account == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->account; ?>"><?php echo $value->account . ' - ' . $value->name; ?></option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('account_cat'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('balance'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="balance" value="<?php echo set_value('balance', $account_info ? number_format($account_info->balance, 2, '.', '') : '0.00'); ?>" class="form-control amountformat" required/>
                            <?php echo form_error('balance'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('virtual_balance'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="text" name="virtual_balance" value="<?php echo set_value('virtual_balance', $account_info ? number_format($account_info->virtual_balance, 2, '.', '') : '0.00'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('virtual_balance'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('interest_frequency'); ?> :</label>
                        <div class="col-lg-7">
                            <?php
                            $selected_freq = set_value('interest_frequency');
                            if ($selected_freq === FALSE || $selected_freq === null || $selected_freq === '') {
                                if ($account_info && isset($account_info->interest_frequency) && $account_info->interest_frequency !== null && $account_info->interest_frequency !== '') {
                                    $selected_freq = strtoupper($account_info->interest_frequency);
                                } else {
                                    $selected_freq = 'INHERIT';
                                }
                            }
                            ?>
                            <select name="interest_frequency" class="form-control">
                                <option value="INHERIT" <?php echo ($selected_freq == 'INHERIT' ? 'selected="selected"' : ''); ?>><?php echo lang('interest_frequency_inherit'); ?></option>
                                <option value="NONE" <?php echo ($selected_freq == 'NONE' ? 'selected="selected"' : ''); ?>><?php echo lang('interest_frequency_none'); ?></option>
                                <option value="MONTHLY" <?php echo ($selected_freq == 'MONTHLY' ? 'selected="selected"' : ''); ?>><?php echo lang('interest_frequency_monthly'); ?></option>
                                <option value="QUARTERLY" <?php echo ($selected_freq == 'QUARTERLY' ? 'selected="selected"' : ''); ?>><?php echo lang('interest_frequency_quarterly'); ?></option>
                            </select>
                            <?php echo form_error('interest_frequency'); ?>
                            <span class="help-block"><?php echo lang('interest_frequency_account_help'); ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('account_status'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <select name="status" class="form-control" required>
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php $selected_status = set_value('status', $account_info && isset($account_info->status) ? $account_info->status : '1'); ?>
                                <option value="1" <?php echo ($selected_status == '1' || $selected_status === 1) ? 'selected="selected"' : ''; ?>><?php echo lang('account_status_active'); ?></option>
                                <option value="0" <?php echo ($selected_status == '0' || $selected_status === 0) ? 'selected="selected"' : ''; ?>><?php echo lang('account_status_inactive'); ?></option>
                            </select>
                            <?php echo form_error('status'); ?>
                        </div>
                    </div>

                    <div class="form-group cbu-actions">
                        <label class="col-lg-4 control-label">&nbsp;</label>
                        <div class="col-lg-7">
                            <?php echo anchor(current_lang() . '/saving/saving_account_listing', '<i class="fa fa-undo"></i> ' . lang('button_cancel'), 'class="btn btn-default"'); ?>
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-save"></i> <?php echo lang('button_update'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="cbu-preview">
                <?php if ($account_info) { ?>
                    <div class="cbu-member-photo"><i class="fa fa-university"></i></div>
                    <h3 class="cbu-member-name"><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="cbu-member-badges">
                        <?php if ($member_id_val !== '') { ?>
                            <span class="cbu-badge"><?php echo htmlspecialchars($member_id_val, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php } ?>
                        <span class="cbu-badge <?php echo $is_active ? '' : 'inactive'; ?>">
                            <?php echo $is_active ? lang('account_status_active') : lang('account_status_inactive'); ?>
                        </span>
                    </div>
                    <ul class="cbu-member-details">
                        <li>
                            <span class="lbl"><?php echo lang('account_type'); ?></span>
                            <span class="val"><?php echo htmlspecialchars($account_info->account_type_name ? $account_info->account_type_name : '-', ENT_QUOTES, 'UTF-8'); ?></span>
                        </li>
                        <li>
                            <span class="lbl"><?php echo lang('member_virtual_balance'); ?></span>
                            <span class="val"><?php echo number_format($account_info->virtual_balance, 2, '.', ','); ?></span>
                        </li>
                        <li>
                            <span class="lbl"><?php echo lang('interest_frequency'); ?></span>
                            <span class="val"><?php echo htmlspecialchars($freq_label, ENT_QUOTES, 'UTF-8'); ?></span>
                        </li>
                        <?php if ($account_info->createdon) { ?>
                        <li>
                            <span class="lbl"><?php echo lang('member_created_date'); ?></span>
                            <span class="val"><?php echo date('Y-m-d', strtotime($account_info->createdon)); ?></span>
                        </li>
                        <?php } ?>
                    </ul>
                    <div class="cbu-balance">
                        <span class="lbl"><?php echo lang('member_current_balance'); ?></span>
                        <span class="val"><?php echo number_format($account_info->balance, 2, '.', ','); ?></span>
                    </div>
                <?php } else { ?>
                    <div class="cbu-preview-empty">
                        <i class="fa fa-university"></i>
                        <?php echo lang('invalid_account'); ?>
                    </div>
                <?php } ?>
            </div>
            <div class="saving-accounts-card">
                <div class="panel-head">
                    <i class="fa fa-university"></i>
                    <h4>Existing Savings Accounts</h4>
                </div>
                <div class="panel-body">
                    <?php
                    $member_accounts = isset($member_accounts) ? $member_accounts : array();
                    if (!empty($member_accounts)) {
                        foreach ($member_accounts as $row) {
                            $row_status = isset($row->status) ? $row->status : '1';
                            $row_active = ($row_status == '1' || $row_status === 1 || $row_status === null || $row_status === '');
                            $row_acct = $row->old_members_acct ? $row->old_members_acct : $row->account;
                            $is_current = $account_info && isset($account_info->account) && $row->account == $account_info->account;
                            ?>
                            <div class="saving-account-row<?php echo $is_current ? ' current' : ''; ?>">
                                <div class="acct-meta">
                                    <span class="acct"><?php echo htmlspecialchars($row_acct, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if (!empty($row->account_type_name)) { ?>
                                        <span class="type"><?php echo htmlspecialchars($row->account_type_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php } ?>
                                </div>
                                <div class="bal-wrap">
                                    <span class="bal"><?php echo number_format((float) $row->balance, 2, '.', ','); ?></span>
                                    <span class="status-pill <?php echo $row_active ? 'active' : 'inactive'; ?>">
                                        <?php echo $row_active ? lang('account_status_active') : lang('account_status_inactive'); ?>
                                    </span>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="empty-accounts">
                            <i class="fa fa-university"></i>
                            <?php echo lang('no_records_found'); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
