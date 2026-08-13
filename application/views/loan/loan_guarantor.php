<?php
$this->load->view('loan/topmenu');

$basicinfo = isset($basicinfo) ? $basicinfo : null;
$contactinfo = isset($contactinfo) ? $contactinfo : null;
$loaninfo = isset($loaninfo) ? $loaninfo : null;
$guarantor_list = isset($guarantor_list) ? $guarantor_list : array();
$selected_guarantor = isset($selected_guarantor) ? $selected_guarantor : null;
$exclude_pid = isset($exclude_pid) ? $exclude_pid : ($basicinfo ? $basicinfo->PID : '');
$relationship_options = array(
    'Spouse' => 'Spouse',
    'Father' => 'Father',
    'Mother' => 'Mother',
    'Son' => 'Son',
    'Daughter' => 'Daughter',
    'Brother' => 'Brother',
    'Sister' => 'Sister',
    'Uncle' => 'Uncle',
    'Aunt' => 'Aunt',
    'Cousin' => 'Cousin',
    'Friend' => 'Friend',
    'Colleague' => 'Colleague',
    'Relative' => 'Relative',
    'Others' => 'Others',
);
$posted_relationship = set_value('relationship');
$posted_relationship_other = set_value('relationship_other');
$relationship_selected = $posted_relationship;
$relationship_other_value = $posted_relationship_other;
if ($posted_relationship !== '' && $posted_relationship !== 'Others' && !isset($relationship_options[$posted_relationship])) {
    $relationship_selected = 'Others';
    if ($relationship_other_value === '') {
        $relationship_other_value = $posted_relationship;
    }
}
$gender_options = lang('member_genderoption');
$gender_label = ($basicinfo && isset($gender_options[$basicinfo->gender])) ? $gender_options[$basicinfo->gender] : ($basicinfo ? $basicinfo->gender : '');
$full_name = $basicinfo ? trim($basicinfo->firstname . ' ' . $basicinfo->middlename . ' ' . $basicinfo->lastname) : '';
$photo_url = $basicinfo ? member_avatar_url(isset($basicinfo->photo) ? $basicinfo->photo : '', isset($basicinfo->gender) ? $basicinfo->gender : '') : '';
$status_code = ($basicinfo && isset($basicinfo->status)) ? (string) $basicinfo->status : '';
$status_label = ($status_code === '1') ? lang('member_active') : (($status_code === '0') ? lang('member_inactive') : (($status_code === '2') ? 'Deleted' : lang('member_inactive')));
$status_active = ($status_code === '1');
$address = '';
if ($contactinfo) {
    if (!empty($contactinfo->physicaladdress)) {
        $address = $contactinfo->physicaladdress;
    } else if (!empty($contactinfo->postaladdress)) {
        $address = $contactinfo->postaladdress;
    } else if (!empty($contactinfo->officeaddress)) {
        $address = $contactinfo->officeaddress;
    }
}
$position = ($contactinfo && !empty($contactinfo->occupation)) ? $contactinfo->occupation : '';
$salary_grade = ($contactinfo && isset($contactinfo->salary_grade)) ? $contactinfo->salary_grade : '';
$pid_js = $basicinfo ? $basicinfo->PID : '';
$mid_js = $basicinfo ? $basicinfo->member_id : '';
$can_edit = ($loaninfo && isset($loaninfo->edit) && (string) $loaninfo->edit === '0');
$is_member_user = $this->ion_auth->in_group('Members');
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
<style type="text/css">
.loan-app-page { margin-top: 4px; }
.loan-app-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.loan-app-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.loan-app-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.loan-app-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.loan-app-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.loan-app-page .cbu-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.loan-app-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.loan-app-page .cbu-panel .panel-body { padding: 22px 20px 12px; overflow: visible; }
.loan-app-page .form-horizontal .form-group { margin-bottom: 16px; }
.loan-app-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.loan-app-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.loan-app-page textarea.form-control { height: auto; min-height: 80px; }
.loan-app-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.loan-app-page .cbu-actions { margin-top: 8px; padding-top: 8px; }
.loan-app-page .cbu-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 18px;
}
.loan-app-page .section-divider {
    margin: 8px 0 18px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e7eaec;
    color: #2f4050;
    font-size: 14px;
    font-weight: 700;
}
.loan-app-page .error_message {
    color: #c0392b;
    font-size: 12px;
    margin-top: 6px;
}
.loan-app-page .guarantor-grid {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -8px 8px;
}
.loan-app-page .guarantor-grid .guarantor-col {
    width: 50%;
    padding: 0 8px 16px;
    box-sizing: border-box;
}
@media (max-width: 991px) {
    .loan-app-page .guarantor-grid .guarantor-col { width: 100%; }
}
.loan-app-page .guarantor-card {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    overflow: hidden;
    height: 100%;
}
.loan-app-page .guarantor-card .g-head {
    padding: 12px 14px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    font-size: 13px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.35;
}
.loan-app-page .guarantor-card .g-body {
    padding: 12px 14px 14px;
    font-size: 13px;
}
.loan-app-page .guarantor-card .g-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 6px 0;
    border-bottom: 1px dashed #eef1f2;
}
.loan-app-page .guarantor-card .g-row:last-child { border-bottom: 0; }
.loan-app-page .guarantor-card .g-row .lbl { color: #999; font-weight: 500; }
.loan-app-page .guarantor-card .g-row .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.loan-app-page .guarantor-card .g-actions {
    margin-top: 10px;
    text-align: right;
}
.loan-app-page .guarantor-card .g-actions a {
    color: #c0392b;
    font-weight: 600;
    font-size: 12px;
}
.loan-app-page .guarantor-card .btn-link-action {
    color: #1ab394;
    font-weight: 600;
}
.loan-app-page .select2-container { width: 100% !important; }
.loan-app-page .select2-container--default .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
    background: #fff;
}
.loan-app-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    color: #2f4050;
    padding-left: 12px;
}
.loan-app-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.loan-app-page .select2-container--default.select2-container--focus .select2-selection--single,
.loan-app-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.loan-app-page .select2-dropdown {
    border-color: #e5e6e7;
    border-radius: 6px;
}
.loan-app-page .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: #1ab394;
}
.loan-app-page .select2-search--dropdown .select2-search__field {
    border-radius: 6px;
    border-color: #e5e6e7;
}
.loan-app-page .relationship-other-wrap { margin-top: 8px; display: none; }
.loan-app-page .relationship-other-wrap.is-visible { display: block; }
.loan-app-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.loan-app-page .cbu-member-card { text-align: center; }
.loan-app-page .cbu-member-photo {
    width: 120px;
    height: 120px;
    margin: 0 auto 14px;
    border-radius: 50%;
    padding: 4px;
    background: #fff;
    border: 3px solid #1ab394;
    box-shadow: 0 4px 14px rgba(26,179,148,0.18);
    overflow: hidden;
}
.loan-app-page .cbu-member-photo img {
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    object-fit: cover;
    object-position: center top;
    border-radius: 50%;
    display: block;
}
.loan-app-page .cbu-member-photo.avatar-fallback img {
    object-fit: contain;
    object-position: center center;
    background: #e8f8f5;
}
.loan-app-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.loan-app-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.loan-app-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.loan-app-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.loan-app-page .cbu-member-details {
    text-align: left;
    margin: 0;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.loan-app-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-app-page .cbu-member-details li:last-child { border-bottom: 0; }
.loan-app-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.loan-app-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.loan-app-page .existing-loans-card,
.loan-app-page .saving-accounts-card,
.loan-app-page .cbu-summary-card {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-top: 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.loan-app-page .existing-loans-card .panel-head,
.loan-app-page .saving-accounts-card .panel-head,
.loan-app-page .cbu-summary-card .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.loan-app-page .existing-loans-card .panel-head i,
.loan-app-page .saving-accounts-card .panel-head i,
.loan-app-page .cbu-summary-card .panel-head i {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.loan-app-page .existing-loans-card .panel-head h4,
.loan-app-page .saving-accounts-card .panel-head h4,
.loan-app-page .cbu-summary-card .panel-head h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #2f4050;
}
.loan-app-page .existing-loans-card .panel-body,
.loan-app-page .saving-accounts-card .panel-body,
.loan-app-page .cbu-summary-card .panel-body { padding: 0; }
.loan-app-page .loan-row {
    padding: 12px 16px;
    border-bottom: 1px dashed #eef1f2;
}
.loan-app-page .loan-row:last-child { border-bottom: 0; }
.loan-app-page .loan-row-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 8px;
}
.loan-app-page .loan-row .lid { display: block; font-weight: 700; color: #2f4050; font-size: 13px; }
.loan-app-page .loan-row .product { display: block; color: #888; font-size: 12px; margin-top: 2px; }
.loan-app-page .status-pill {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    color: #fff;
    white-space: nowrap;
}
.loan-app-page .status-pill.new { background: #23c6c8; }
.loan-app-page .status-pill.eval { background: #1c84c6; }
.loan-app-page .status-pill.rejected { background: #ed5565; }
.loan-app-page .status-pill.accepted { background: #1ab394; }
.loan-app-page .status-pill.closed { background: #676a6c; }
.loan-app-page .status-pill.disburse { background: #f8ac59; }
.loan-app-page .status-pill.bb { background: #f8ac59; }
.loan-app-page .status-pill.mixed { background: #f8ac59; }
.loan-app-page .status-pill.other { background: #676a6c; }
.loan-app-page .status-pill.active { background: #1ab394; }
.loan-app-page .status-pill.inactive { background: #676a6c; }
.loan-app-page .loan-status-wrap { text-align: right; }
.loan-app-page .loan-date {
    display: block;
    margin-top: 4px;
    color: #888;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.35;
}
.loan-app-page .loan-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 12px;
}
.loan-app-page .loan-metrics .metric {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    font-size: 12px;
}
.loan-app-page .loan-metrics .metric .lbl { color: #999; font-weight: 500; }
.loan-app-page .loan-metrics .metric .val {
    color: #2f4050;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    text-align: right;
}
.loan-app-page .loan-metrics .metric.warn .val { color: #c0392b; }
.loan-app-page .saving-account-row,
.loan-app-page .cbu-metric-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-app-page .saving-account-row:last-child,
.loan-app-page .cbu-metric-row:last-child { border-bottom: 0; }
.loan-app-page .saving-account-row .acct { display: block; font-weight: 700; color: #2f4050; font-size: 13px; }
.loan-app-page .saving-account-row .type { display: block; color: #888; font-size: 12px; margin-top: 2px; }
.loan-app-page .saving-account-row .bal { display: block; font-weight: 700; color: #2f4050; font-variant-numeric: tabular-nums; text-align: right; }
.loan-app-page .cbu-metric-row .lbl { color: #999; font-weight: 500; }
.loan-app-page .cbu-metric-row .val { color: #2f4050; font-weight: 700; font-variant-numeric: tabular-nums; }
.loan-app-page .empty-loans {
    text-align: center;
    color: #999;
    padding: 28px 16px;
    font-size: 13px;
}
.loan-app-page .empty-loans i {
    display: block;
    font-size: 28px;
    color: #c9ebe3;
    margin-bottom: 8px;
}
</style>

<div class="col-lg-12 loan-app-page">
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
            <?php echo form_open_multipart(current_lang() . "/loan/loan_guarantor/" . $loanid, 'class="form-horizontal"'); ?>
            <div class="cbu-panel">
                <div class="panel-head">
                    <i class="fa fa-users"></i>
                    <h4><?php echo lang('loan_guarantors'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_LID'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" disabled="disabled" value="<?php echo htmlspecialchars($loaninfo->LID, ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                        </div>
                    </div>

                    <?php if (count($guarantor_list) > 0) { ?>
                        <div class="guarantor-grid">
                            <?php foreach ($guarantor_list as $value) {
                                $customerinfo = $this->member_model->member_basic_info(null, $value->PID)->row();
                                $g_name = $customerinfo
                                    ? trim($customerinfo->PID . ' : ' . $customerinfo->member_id . ' : ' . $customerinfo->firstname . ' ' . $customerinfo->middlename . ' ' . $customerinfo->lastname)
                                    : $value->PID;
                                ?>
                                <div class="guarantor-col">
                                    <div class="guarantor-card">
                                        <div class="g-head"><?php echo htmlspecialchars($g_name, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="g-body">
                                            <div class="g-row">
                                                <span class="lbl"><?php echo lang('loan_quarantor_relationship'); ?></span>
                                                <span class="val"><?php echo htmlspecialchars($value->relationship, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                            <div class="g-row">
                                                <span class="lbl"><?php echo lang('loan_quarantor_asset'); ?></span>
                                                <span class="val"><?php echo htmlspecialchars($value->declaration, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                            <div class="g-row">
                                                <span class="lbl"><?php echo lang('loan_quarantor_attachment'); ?></span>
                                                <span class="val">
                                                    <?php
                                                    if (!empty($value->file)) {
                                                        echo anchor(base_url() . 'uploads/document/' . $value->file, lang('loan_quarantor_attachment_view'), 'class="btn-link-action" target="_blank"');
                                                    } else {
                                                        echo '&mdash;';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <?php if ($can_edit) { ?>
                                                <div class="g-actions">
                                                    <?php echo anchor(current_lang() . '/loan/remove_guarantor/' . $loanid . '/' . $value->id, lang('loan_supporting_document_remove')); ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="section-divider"><?php echo lang('loan_quarantor'); ?></div>
                    <?php } ?>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_quarantor_name'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <select id="customerid" name="customerid" class="form-control">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php if ($selected_guarantor) {
                                    $selected_label = trim($selected_guarantor->member_id . ' : ' . $selected_guarantor->firstname . ' ' . $selected_guarantor->middlename . ' ' . $selected_guarantor->lastname);
                                    ?>
                                    <option value="<?php echo htmlspecialchars($selected_guarantor->PID, ENT_QUOTES, 'UTF-8'); ?>" selected="selected">
                                        <?php echo htmlspecialchars($selected_label, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('customerid'); ?>
                        </div>
                    </div>

                    <?php if (!$is_member_user) { ?>
                        <div class="form-group">
                            <label class="col-lg-4 control-label"><?php echo lang('loan_quarantor_relationship'); ?> : <span class="required">*</span></label>
                            <div class="col-lg-7">
                                <select id="relationship" name="relationship" class="form-control">
                                    <option value=""><?php echo lang('select_default_text'); ?></option>
                                    <?php foreach ($relationship_options as $rel_key => $rel_label) { ?>
                                        <option value="<?php echo htmlspecialchars($rel_key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((string) $relationship_selected === (string) $rel_key ? 'selected="selected"' : ''); ?>>
                                            <?php echo htmlspecialchars($rel_label, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="relationship-other-wrap<?php echo ($relationship_selected === 'Others' ? ' is-visible' : ''); ?>" id="relationship_other_wrap">
                                    <input name="relationship_other" id="relationship_other" type="text" value="<?php echo htmlspecialchars($relationship_other_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="<?php echo htmlspecialchars(lang('loan_quarantor_relationship_other'), ENT_QUOTES, 'UTF-8'); ?>"/>
                                </div>
                                <?php echo form_error('relationship'); ?>
                                <?php echo form_error('relationship_other'); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label"><?php echo lang('loan_quarantor_asset'); ?> : <span class="required">*</span></label>
                            <div class="col-lg-7">
                                <textarea name="asset" class="form-control"><?php echo set_value('asset'); ?></textarea>
                                <?php echo form_error('asset'); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label"><?php echo lang('loan_quarantor_declaration'); ?> :</label>
                            <div class="col-lg-7">
                                <input name="file" type="file" class="form-control"/>
                                <?php
                                if (isset($logo_error)) {
                                    echo '<div class="error_message">' . $logo_error . '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($can_edit) { ?>
                        <div class="form-group cbu-actions">
                            <label class="col-lg-4 control-label">&nbsp;</label>
                            <div class="col-lg-7">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-save"></i> <?php echo lang('loan_save_btn'); ?>
                                </button>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>

        <div class="col-lg-5">
            <div class="cbu-preview" id="member_info">
                <?php if ($basicinfo) { ?>
                    <div class="cbu-member-card">
                        <?php
                        $has_real_photo = !empty($basicinfo->photo) && $basicinfo->photo !== '0' && strtolower($basicinfo->photo) !== 'avatar.gif';
                        ?>
                        <div class="cbu-member-photo<?php echo $has_real_photo ? '' : ' avatar-fallback'; ?>">
                            <img src="<?php echo htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars(member_avatar_url('', isset($basicinfo->gender) ? $basicinfo->gender : ''), ENT_QUOTES, 'UTF-8'); ?>';"/>
                        </div>
                        <h3 class="cbu-member-name"><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="cbu-member-badges">
                            <?php if (!empty($basicinfo->member_id)) { ?>
                                <span class="cbu-badge"><?php echo htmlspecialchars($basicinfo->member_id, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php } ?>
                            <span class="cbu-badge"><?php echo lang('member_pid'); ?> <?php echo htmlspecialchars($basicinfo->PID, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if ($gender_label !== '') { ?>
                                <span class="cbu-badge"><?php echo htmlspecialchars($gender_label, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php } ?>
                            <span class="cbu-badge<?php echo $status_active ? '' : ' inactive'; ?>"><?php echo lang('member_status'); ?>: <?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <ul class="cbu-member-details">
                            <li><span class="lbl"><?php echo lang('member_contact_address'); ?></span><span class="val"><?php echo $address !== '' ? htmlspecialchars($address, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_dob'); ?></span><span class="val"><?php echo !empty($basicinfo->dob) ? htmlspecialchars($basicinfo->dob, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_join_date'); ?></span><span class="val"><?php echo !empty($basicinfo->joiningdate) ? htmlspecialchars($basicinfo->joiningdate, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_phone1'); ?></span><span class="val"><?php echo ($contactinfo && !empty($contactinfo->phone1)) ? htmlspecialchars($contactinfo->phone1, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_position'); ?></span><span class="val"><?php echo $position !== '' ? htmlspecialchars($position, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_email'); ?></span><span class="val"><?php echo ($contactinfo && !empty($contactinfo->email)) ? htmlspecialchars($contactinfo->email, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_salary_grade'); ?></span><span class="val"><?php echo $salary_grade !== '' ? htmlspecialchars($salary_grade, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                        </ul>
                    </div>
                <?php } else { ?>
                    <div class="empty-loans"><i class="fa fa-user"></i><?php echo lang('no_records_found'); ?></div>
                <?php } ?>
            </div>

            <div class="existing-loans-card" id="member_loans_card">
                <div class="panel-head">
                    <i class="fa fa-list"></i>
                    <h4>Existing Loans</h4>
                </div>
                <div class="panel-body" id="member_loans_body">
                    <div class="empty-loans"><i class="fa fa-spinner fa-spin"></i><?php echo lang('please_wait'); ?></div>
                </div>
            </div>

            <div class="saving-accounts-card" id="member_savings_card">
                <div class="panel-head">
                    <i class="fa fa-university"></i>
                    <h4>Savings Deposits</h4>
                </div>
                <div class="panel-body" id="member_savings_body">
                    <div class="empty-loans"><i class="fa fa-spinner fa-spin"></i><?php echo lang('please_wait'); ?></div>
                </div>
            </div>

            <div class="cbu-summary-card" id="member_cbu_card">
                <div class="panel-head">
                    <i class="fa fa-briefcase"></i>
                    <h4>Capital Build Up</h4>
                </div>
                <div class="panel-body" id="member_cbu_body">
                    <div class="empty-loans"><i class="fa fa-spinner fa-spin"></i><?php echo lang('please_wait'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script type="text/javascript">
(function() {
    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function initSelect2() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            setTimeout(initSelect2, 50);
            return;
        }
        var $select = jQuery('#customerid');
        if (!$select.length || $select.data('select2')) {
            return;
        }
        $select.select2({
            placeholder: <?php echo json_encode(lang('loan_quarantor_name_placeholder')); ?>,
            allowClear: true,
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: <?php echo json_encode(site_url(current_lang() . '/loan/search_member_select2')); ?>,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term || '',
                        exclude_pid: <?php echo json_encode((string) $exclude_pid); ?>
                    };
                },
                processResults: function(data) {
                    return { results: (data && data.results) ? data.results : [] };
                },
                cache: true
            }
        });
    }

    function toggleRelationshipOther() {
        var $rel = jQuery('#relationship');
        var $wrap = jQuery('#relationship_other_wrap');
        if (!$rel.length || !$wrap.length) {
            return;
        }
        if ($rel.val() === 'Others') {
            $wrap.addClass('is-visible');
        } else {
            $wrap.removeClass('is-visible');
        }
    }

    function initMain() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initMain, 50);
            return;
        }

        jQuery('#relationship').on('change', toggleRelationshipOther);
        toggleRelationshipOther();

        var LOANS_URL = '<?php echo site_url(current_lang() . '/loan/member_existing_loans'); ?>';
        var ACCOUNTS_URL = '<?php echo site_url(current_lang() . '/saving/member_saving_accounts'); ?>';
        var CBU_URL = '<?php echo site_url(current_lang() . '/loan/member_cbu_summary'); ?>';
        var PID = <?php echo json_encode((string) $pid_js); ?>;
        var MEMBER_ID = <?php echo json_encode((string) $mid_js); ?>;

        function statusPillClass(code, name) {
            if (name === 'Beginning Balance' || code === 'bb') return 'bb';
            if (code === '0') return 'new';
            if (code === '1') return 'eval';
            if (code === '2') return 'rejected';
            if (code === '4' || code === '9') return 'accepted';
            if (code === '5') return 'closed';
            if (code === '6') return 'disburse';
            if (code === '7' || code === '8') return 'mixed';
            return 'other';
        }

        function renderLoansEmpty(message) {
            jQuery('#member_loans_body').html('<div class="empty-loans"><i class="fa fa-list"></i>' + escapeHtml(message) + '</div>');
        }
        function renderSavingsEmpty(message) {
            jQuery('#member_savings_body').html('<div class="empty-loans"><i class="fa fa-university"></i>' + escapeHtml(message) + '</div>');
        }
        function renderCbuEmpty(message) {
            jQuery('#member_cbu_body').html('<div class="empty-loans"><i class="fa fa-briefcase"></i>' + escapeHtml(message) + '</div>');
        }

        function renderMemberLoans(loans) {
            loans = loans || [];
            if (!loans.length) {
                renderLoansEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                return;
            }
            var html = '';
            for (var i = 0; i < loans.length; i++) {
                var row = loans[i];
                var pill = statusPillClass(String(row.status_code || ''), row.status || '');
                html += '<div class="loan-row"><div class="loan-row-head"><div>';
                html += '<span class="lid">' + escapeHtml(row.lid) + '</span>';
                if (row.product) {
                    html += '<span class="product">' + escapeHtml(row.product) + '</span>';
                }
                html += '</div><div class="loan-status-wrap">';
                html += '<span class="status-pill ' + pill + '">' + escapeHtml(row.status || '-') + '</span>';
                if (row.loan_date) {
                    html += '<span class="loan-date">' + escapeHtml((row.loan_date_label || 'Loan Date') + ' ' + row.loan_date) + '</span>';
                }
                html += '</div></div><div class="loan-metrics">';
                html += '<div class="metric"><span class="lbl">Loan Amount</span><span class="val">' + escapeHtml(row.loan_amount) + '</span></div>';
                html += '<div class="metric"><span class="lbl">Total Amount</span><span class="val">' + escapeHtml(row.total_amount) + '</span></div>';
                html += '<div class="metric warn"><span class="lbl">Penalty</span><span class="val">' + escapeHtml(row.penalty) + '</span></div>';
                html += '<div class="metric warn"><span class="lbl">Past due interest</span><span class="val">' + escapeHtml(row.past_due_interest) + '</span></div>';
                html += '</div></div>';
            }
            jQuery('#member_loans_body').html(html);
        }

        function renderMemberSavings(accounts) {
            accounts = accounts || [];
            if (!accounts.length) {
                renderSavingsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                return;
            }
            var html = '';
            for (var i = 0; i < accounts.length; i++) {
                var row = accounts[i];
                var acct = row.old_account ? row.old_account : row.account;
                html += '<div class="saving-account-row"><div>';
                html += '<span class="acct">' + escapeHtml(acct) + '</span>';
                if (row.type) {
                    html += '<span class="type">' + escapeHtml(row.type) + '</span>';
                }
                html += '</div><div>';
                html += '<span class="bal">' + escapeHtml(row.balance) + '</span>';
                html += '<span class="status-pill ' + (row.active ? 'active' : 'inactive') + '">' + escapeHtml(row.status || '') + '</span>';
                html += '</div></div>';
            }
            jQuery('#member_savings_body').html(html);
        }

        function renderMemberCbu(data) {
            if (!data || data.has_record !== 'Y') {
                renderCbuEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                return;
            }
            var html = '';
            html += '<div class="cbu-metric-row"><span class="lbl"><?php echo lang('member_current_contribution'); ?></span><span class="val">' + escapeHtml(data.balance || '0.00') + '</span></div>';
            html += '<div class="cbu-metric-row"><span class="lbl"><?php echo lang('contribution_minimum_setting'); ?></span><span class="val">' + escapeHtml(data.monthly_amount || '0.00') + '</span></div>';
            if (data.source) {
                html += '<div class="cbu-metric-row"><span class="lbl"><?php echo lang('contribution_source'); ?></span><span class="val">' + escapeHtml(data.source) + '</span></div>';
            }
            jQuery('#member_cbu_body').html(html);
        }

        function loadSidePanels() {
            if (!PID && !MEMBER_ID) {
                renderLoansEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                renderSavingsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                renderCbuEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                return;
            }
            jQuery.ajax({
                url: LOANS_URL,
                type: 'POST',
                dataType: 'json',
                data: { pid: PID, member_id: MEMBER_ID },
                success: function(json) {
                    if (!json || json.success !== 'Y') {
                        renderLoansEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        return;
                    }
                    renderMemberLoans(json.loans || []);
                },
                error: function() { renderLoansEmpty(<?php echo json_encode(lang('no_records_found')); ?>); }
            });
            jQuery.ajax({
                url: ACCOUNTS_URL,
                type: 'POST',
                dataType: 'json',
                data: { pid: PID, member_id: MEMBER_ID },
                success: function(json) {
                    if (!json || json.success !== 'Y') {
                        renderSavingsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        return;
                    }
                    renderMemberSavings(json.accounts || []);
                },
                error: function() { renderSavingsEmpty(<?php echo json_encode(lang('no_records_found')); ?>); }
            });
            jQuery.ajax({
                url: CBU_URL,
                type: 'POST',
                dataType: 'json',
                data: { pid: PID, member_id: MEMBER_ID },
                success: function(json) {
                    if (!json || json.success !== 'Y') {
                        renderCbuEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        return;
                    }
                    renderMemberCbu(json);
                },
                error: function() { renderCbuEmpty(<?php echo json_encode(lang('no_records_found')); ?>); }
            });
        }

        loadSidePanels();
    }

    if (document.readyState === 'complete') {
        initSelect2();
        initMain();
    } else {
        window.addEventListener('load', function() {
            initSelect2();
            initMain();
        });
        setTimeout(function() {
            initSelect2();
            initMain();
        }, 300);
    }
})();
</script>
