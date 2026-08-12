<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet"/>
<style type="text/css">
#datetimepicker { position: relative; z-index: 1; }
.bootstrap-datetimepicker-widget {
    z-index: 1060 !important;
    min-width: 280px;
    max-width: 320px;
    padding: 4px;
    background: #fff;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 4px;
    box-shadow: 0 6px 12px rgba(0,0,0,.175);
}
.bootstrap-datetimepicker-widget .datepicker table { width: 100%; margin: 0; }
.bootstrap-datetimepicker-widget .datepicker-months td,
.bootstrap-datetimepicker-widget .datepicker-years td,
.datepicker-months td,
.datepicker-years td {
    width: auto !important;
    height: auto !important;
    overflow: hidden;
}
.bootstrap-datetimepicker-widget .datepicker table tr td span,
.datepicker table tr td span.month,
.datepicker table tr td span.year {
    display: block !important;
    float: left !important;
    width: 23% !important;
    height: 54px !important;
    line-height: 54px !important;
    margin: 1% !important;
    text-align: center;
    box-sizing: border-box;
}

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
.loan-app-page .form-control[readonly],
.loan-app-page .form-control[disabled] {
    background: #f8fafb;
    color: #676a6c;
    cursor: not-allowed;
}
.loan-app-page .help-block {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
    margin-bottom: 0;
}
.loan-app-page .input-group-addon {
    background: #f8fafb;
    cursor: pointer;
}
.loan-app-page .input-group-addon:hover { color: #1ab394; }
.loan-app-page .required { color: #ed5565; }
.loan-app-page .section-divider {
    margin: 8px 0 18px;
    padding: 10px 0 8px;
    border-bottom: 1px solid #eef1f2;
    color: #1ab394;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: .02em;
}
.loan-app-page .section-divider i { margin-right: 6px; }
.loan-app-page .cbu-actions {
    margin-top: 8px;
    margin-bottom: 8px;
    padding-top: 12px;
    border-top: 1px solid #f0f2f3;
}
.loan-app-page .cbu-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
    border-radius: 6px;
    font-weight: 600;
}
.loan-app-page .cbu-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.loan-app-page .cbu-lookup { position: relative; }
.loan-app-page .member-suggest-box {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    margin-top: 4px;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    max-height: 300px;
    overflow-y: auto;
    z-index: 10050;
}
.loan-app-page .member-suggest-box.open { display: block; }
.loan-app-page .member-suggest-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 12px;
    border: 0;
    border-bottom: 1px solid #f0f2f3;
    background: #fff;
    text-align: left;
    cursor: pointer;
}
.loan-app-page .member-suggest-item:last-child { border-bottom: 0; }
.loan-app-page .member-suggest-item:hover,
.loan-app-page .member-suggest-item.active { background: #e8f8f5; }
.loan-app-page .member-suggest-item .suggest-id {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 11px;
    white-space: nowrap;
}
.loan-app-page .member-suggest-item .suggest-name {
    flex: 1;
    font-weight: 600;
    color: #2f4050;
    font-size: 13px;
}
.loan-app-page .member-suggest-item .suggest-status {
    color: #999;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.loan-app-page .member-suggest-empty {
    padding: 12px;
    color: #999;
    font-size: 12px;
}
.loan-app-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 20px;
    min-height: 280px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.loan-app-page .cbu-preview-empty {
    text-align: center;
    color: #999;
    padding: 40px 12px;
}
.loan-app-page .cbu-preview-empty i {
    font-size: 42px;
    color: #c9ebe3;
    display: block;
    margin-bottom: 12px;
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
.loan-app-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.loan-app-page .cbu-member-photo-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8f8f5;
}
.loan-app-page .cbu-member-photo-empty i {
    font-size: 42px;
    color: #1ab394;
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
.loan-app-page .saving-account-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px dashed #eef1f2;
}
.loan-app-page .saving-account-row:last-child { border-bottom: 0; }
.loan-app-page .saving-account-row .acct { display: block; font-weight: 700; color: #2f4050; font-size: 13px; }
.loan-app-page .saving-account-row .type { display: block; color: #888; font-size: 12px; margin-top: 2px; }
.loan-app-page .saving-account-row .bal { display: block; font-weight: 700; color: #2f4050; font-variant-numeric: tabular-nums; text-align: right; }
.loan-app-page .saving-account-row .status-pill.active { background: #1ab394; }
.loan-app-page .saving-account-row .status-pill.inactive { background: #676a6c; }
.loan-app-page .cbu-metric-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-app-page .cbu-metric-row:last-child { border-bottom: 0; }
.loan-app-page .cbu-metric-row .lbl { color: #999; font-weight: 500; }
.loan-app-page .cbu-metric-row .val { color: #2f4050; font-weight: 700; font-variant-numeric: tabular-nums; }
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
.loan-app-page .loan-row .lid {
    display: block;
    font-weight: 700;
    color: #2f4050;
    font-size: 13px;
}
.loan-app-page .loan-row .product {
    display: block;
    color: #888;
    font-size: 12px;
    margin-top: 2px;
}
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

<?php
$is_member_user = $this->ion_auth->in_group('Members');
$locked_member = null;
if ($is_member_user) {
    $users = current_user();
    $locked_member = $this->db->get_where('members', array('member_id' => $users->member_id))->row();
}
?>

<?php echo form_open_multipart(current_lang() . "/loan/loan_application", 'class="form-horizontal"'); ?>

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
            <div class="cbu-panel">
                <div class="panel-head">
                    <i class="fa fa-file-text-o"></i>
                    <h4><?php echo lang('loan_create_new'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="section-divider">
                        <i class="fa fa-user"></i>Member Lookup
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('member_pid'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <div class="cbu-lookup">
                                <div class="input-group">
                                    <?php if (!$is_member_user) { ?>
                                        <input type="text" id="pid" name="pid" value="<?php echo set_value('pid'); ?>" class="form-control" autocomplete="off"/>
                                    <?php } else { ?>
                                        <input type="text" disabled="disabled" value="<?php echo htmlspecialchars($locked_member ? $locked_member->PID : '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                                        <input type="hidden" id="pid" name="pid" value="<?php echo htmlspecialchars($locked_member ? $locked_member->PID : '', ENT_QUOTES, 'UTF-8'); ?>"/>
                                    <?php } ?>
                                    <span class="input-group-addon" id="search_pid">
                                        <span class="fa fa-search"></span>
                                    </span>
                                </div>
                                <?php if (!$is_member_user) { ?>
                                    <div id="pid-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                                <?php } ?>
                            </div>
                            <?php echo form_error('pid'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('member_member_id'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <div class="cbu-lookup">
                                <div class="input-group">
                                    <?php if (!$is_member_user) { ?>
                                        <input type="text" id="member_id" name="member_id" value="<?php echo set_value('member_id'); ?>" class="form-control" autocomplete="off"/>
                                    <?php } else { ?>
                                        <input type="text" disabled="disabled" value="<?php echo htmlspecialchars($locked_member ? $locked_member->member_id : '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>
                                        <input type="hidden" id="member_id" name="member_id" value="<?php echo htmlspecialchars($locked_member ? $locked_member->member_id : '', ENT_QUOTES, 'UTF-8'); ?>"/>
                                    <?php } ?>
                                    <span class="input-group-addon" id="search_mid">
                                        <span class="fa fa-search"></span>
                                    </span>
                                </div>
                                <?php if (!$is_member_user) { ?>
                                    <div id="mid-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                                <?php } ?>
                            </div>
                            <?php echo form_error('member_id'); ?>
                        </div>
                    </div>

                    <div class="section-divider">
                        <i class="fa fa-info-circle"></i><?php echo lang('loan_basic_info'); ?>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_LID'); ?> (LN Number) :</label>
                        <div class="col-lg-7">
                            <input type="text" name="lid" value="<?php echo set_value('lid', isset($next_ln_number) ? $next_ln_number : ''); ?>" class="form-control"/>
                            <span class="help-block">Editable. Auto-generated if left empty.</span>
                            <?php echo form_error('lid'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_applicationdate'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <div class="input-group date" id="datetimepicker">
                                <input type="text" name="applicationdate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('applicationdate'); ?>" data-date-format="DD-MM-YYYY" class="form-control" autocomplete="off"/>
                                <span class="input-group-addon">
                                    <span class="fa fa-calendar"></span>
                                </span>
                            </div>
                            <?php echo form_error('applicationdate'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_product'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <select name="product" class="form-control">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php
                                $selected = set_value('product');
                                foreach ($loan_product_list as $key => $value) {
                                    ?>
                                    <option <?php echo ($value->id == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->id; ?>"><?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('product'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_applied_amount'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="amount" value="<?php echo set_value('amount'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('amount'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_installment'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="installment" value="<?php echo set_value('installment'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('installment'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label">Monthly Income : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="income" value="<?php echo set_value('income'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('income'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_paysource'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <select name="source" class="form-control">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php
                                $selected = set_value('source');
                                foreach ($paysource_list as $key => $value) {
                                    ?>
                                    <option <?php echo ($value->name == $selected ? 'selected="selected"' : ''); ?> value="<?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('source'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label">Loan Processing Fee : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="procesingfee" value="<?php echo set_value('procesingfee'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('procesingfee'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_purpose'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <textarea rows="3" name="purpose" class="form-control"><?php echo set_value('purpose'); ?></textarea>
                            <?php echo form_error('purpose'); ?>
                        </div>
                    </div>

                    <div class="form-group cbu-actions">
                        <label class="col-lg-4 control-label">&nbsp;</label>
                        <div class="col-lg-7">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-save"></i> <?php echo lang('loan_addbtn'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="cbu-preview" id="member_info">
                <div class="cbu-preview-empty">
                    <i class="fa fa-user"></i>
                    Search a member by PID or Member ID to view details.
                </div>
            </div>
            <div class="existing-loans-card" id="member_loans_card">
                <div class="panel-head">
                    <i class="fa fa-list"></i>
                    <h4>Existing Loans</h4>
                </div>
                <div class="panel-body" id="member_loans_body">
                    <div class="empty-loans">
                        <i class="fa fa-list"></i>
                        Search a member to view existing loans.
                    </div>
                </div>
            </div>
            <div class="saving-accounts-card" id="member_savings_card">
                <div class="panel-head">
                    <i class="fa fa-university"></i>
                    <h4>Savings Deposits</h4>
                </div>
                <div class="panel-body" id="member_savings_body">
                    <div class="empty-loans">
                        <i class="fa fa-university"></i>
                        Search a member to view savings deposits.
                    </div>
                </div>
            </div>
            <div class="cbu-summary-card" id="member_cbu_card">
                <div class="panel-head">
                    <i class="fa fa-briefcase"></i>
                    <h4>Capital Build Up</h4>
                </div>
                <div class="panel-body" id="member_cbu_body">
                    <div class="empty-loans">
                        <i class="fa fa-briefcase"></i>
                        Search a member to view Capital Build Up.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url(); ?>media/js/script/moment.js"></script>
<script type="text/javascript">
(function() {
    var isMemberUser = <?php echo $is_member_user ? 'true' : 'false'; ?>;

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function initDatePicker() {
        if (typeof jQuery === 'undefined' || typeof moment === 'undefined' || typeof jQuery.fn.datetimepicker === 'undefined') {
            setTimeout(initDatePicker, 50);
            return;
        }
        var $picker = jQuery('#datetimepicker');
        if ($picker.length && !$picker.data('DateTimePicker')) {
            $picker.datetimepicker({ pickTime: false, format: 'DD-MM-YYYY' });
        }
    }

    function initMain() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initMain, 50);
            return;
        }

        var SUGGEST_URL = '<?php echo site_url(current_lang() . '/member/autosuggest_member_list'); ?>';
        var SEARCH_URL = '<?php echo site_url(current_lang() . '/saving/search_member/'); ?>';
        var LOANS_URL = '<?php echo site_url(current_lang() . '/loan/member_existing_loans'); ?>';
        var ACCOUNTS_URL = '<?php echo site_url(current_lang() . '/saving/member_saving_accounts'); ?>';
        var CBU_URL = '<?php echo site_url(current_lang() . '/loan/member_cbu_summary'); ?>';
        var PHOTO_BASE = '<?php echo base_url(); ?>uploads/memberphoto/';
        var genderLabels = <?php echo json_encode(lang('member_genderoption')); ?>;

        function displayValue(value) {
            var text = jQuery.trim(String(value == null ? '' : value));
            return text ? escapeHtml(text) : '&mdash;';
        }

        function genderLabel(code) {
            if (genderLabels && genderLabels[code]) {
                return genderLabels[code];
            }
            return code || '';
        }

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
            jQuery('#member_loans_body').html(
                '<div class="empty-loans"><i class="fa fa-list"></i>' + escapeHtml(message) + '</div>'
            );
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
                html += '<div class="loan-row">';
                html += '<div class="loan-row-head"><div>';
                html += '<span class="lid">' + escapeHtml(row.lid) + '</span>';
                if (row.product) {
                    html += '<span class="product">' + escapeHtml(row.product) + '</span>';
                }
                html += '</div><div class="loan-status-wrap">';
                html += '<span class="status-pill ' + pill + '">' + escapeHtml(row.status || '-') + '</span>';
                if (row.loan_date) {
                    html += '<span class="loan-date">' + escapeHtml((row.loan_date_label || 'Loan Date') + ' ' + row.loan_date) + '</span>';
                }
                html += '</div></div>';
                html += '<div class="loan-metrics">';
                html += '<div class="metric"><span class="lbl">Loan Amount</span><span class="val">' + escapeHtml(row.loan_amount) + '</span></div>';
                html += '<div class="metric"><span class="lbl">Total Amount</span><span class="val">' + escapeHtml(row.total_amount) + '</span></div>';
                html += '<div class="metric warn"><span class="lbl">Penalty</span><span class="val">' + escapeHtml(row.penalty) + '</span></div>';
                html += '<div class="metric warn"><span class="lbl">Past due interest</span><span class="val">' + escapeHtml(row.past_due_interest) + '</span></div>';
                html += '</div></div>';
            }
            jQuery('#member_loans_body').html(html);
        }

        function loadMemberLoans(pid, memberId) {
            renderLoansEmpty(<?php echo json_encode(lang('please_wait')); ?>);
            jQuery.ajax({
                url: LOANS_URL,
                type: 'POST',
                dataType: 'json',
                data: { pid: pid || '', member_id: memberId || '' },
                success: function(json) {
                    if (!json || json.success !== 'Y') {
                        renderLoansEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        return;
                    }
                    renderMemberLoans(json.loans || []);
                },
                error: function() {
                    renderLoansEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                }
            });
        }

        function renderSavingsEmpty(message) {
            jQuery('#member_savings_body').html(
                '<div class="empty-loans"><i class="fa fa-university"></i>' + escapeHtml(message) + '</div>'
            );
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

        function loadMemberSavings(pid, memberId) {
            renderSavingsEmpty(<?php echo json_encode(lang('please_wait')); ?>);
            jQuery.ajax({
                url: ACCOUNTS_URL,
                type: 'POST',
                dataType: 'json',
                data: { pid: pid || '', member_id: memberId || '' },
                success: function(json) {
                    if (!json || json.success !== 'Y') {
                        renderSavingsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        return;
                    }
                    renderMemberSavings(json.accounts || []);
                },
                error: function() {
                    renderSavingsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                }
            });
        }

        function renderCbuEmpty(message) {
            jQuery('#member_cbu_body').html(
                '<div class="empty-loans"><i class="fa fa-briefcase"></i>' + escapeHtml(message) + '</div>'
            );
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

        function loadMemberCbu(pid, memberId) {
            renderCbuEmpty(<?php echo json_encode(lang('please_wait')); ?>);
            jQuery.ajax({
                url: CBU_URL,
                type: 'POST',
                dataType: 'json',
                data: { pid: pid || '', member_id: memberId || '' },
                success: function(json) {
                    if (!json || json.success !== 'Y') {
                        renderCbuEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        return;
                    }
                    renderMemberCbu(json);
                },
                error: function() {
                    renderCbuEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                }
            });
        }

        function showTimedWarning(title) {
            if (typeof swal === 'function') {
                swal({ title: title, type: 'warning', timer: 2500, showConfirmButton: false });
            } else {
                alert(title);
            }
        }

        function renderMemberCard(userdata, contact) {
            contact = contact || {};
            var photo = userdata['photo'] ? userdata['photo'].toString() : '';
            var name = jQuery.trim((userdata['firstname'] || '') + ' ' + (userdata['middlename'] || '') + ' ' + (userdata['lastname'] || ''));
            var gender = genderLabel(userdata['gender']);
            var accountStatus = (typeof memberAccountStatus === 'function') ? memberAccountStatus(userdata) : { label: '', active: true };
            var statusLabel = (window.TAPSTEMCO_MEMBER_STATUS && TAPSTEMCO_MEMBER_STATUS.label) ? TAPSTEMCO_MEMBER_STATUS.label : '<?php echo lang('member_status'); ?>';
            var output = '<div class="cbu-member-card">';
            if (typeof memberAvatarHtml === 'function') {
                output += memberAvatarHtml(photo, userdata['gender']);
            } else if (photo) {
                output += '<div class="cbu-member-photo"><img src="' + escapeHtml(PHOTO_BASE + photo) + '" alt=""/></div>';
            } else {
                output += '<div class="cbu-member-photo cbu-member-photo-empty"><i class="fa fa-user"></i></div>';
            }
            output += '<h3 class="cbu-member-name">' + escapeHtml(name) + '</h3>';
            output += '<div class="cbu-member-badges">';
            if (userdata['member_id']) {
                output += '<span class="cbu-badge">' + escapeHtml(userdata['member_id']) + '</span>';
            }
            if (userdata['PID'] !== undefined && userdata['PID'] !== null && String(userdata['PID']) !== '') {
                output += '<span class="cbu-badge"><?php echo lang('member_pid'); ?> ' + escapeHtml(userdata['PID']) + '</span>';
            }
            if (gender) {
                output += '<span class="cbu-badge">' + escapeHtml(gender) + '</span>';
            }
            if (accountStatus.label) {
                output += '<span class="cbu-badge' + (accountStatus.active ? '' : ' inactive') + '">' + escapeHtml(statusLabel) + ': ' + escapeHtml(accountStatus.label) + '</span>';
            }
            output += '</div>';
            output += '<ul class="cbu-member-details">';
            output += '<li><span class="lbl"><?php echo lang('member_contact_address'); ?></span><span class="val">' + displayValue(contact['physicaladdress'] || contact['postaladdress'] || contact['officeaddress'] || '') + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_dob'); ?></span><span class="val">' + displayValue(userdata['dob']) + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_join_date'); ?></span><span class="val">' + displayValue(userdata['joiningdate']) + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_contact_phone1'); ?></span><span class="val">' + displayValue(contact['phone1']) + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_contact_position'); ?></span><span class="val">' + displayValue(contact['position'] || contact['occupation'] || '') + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_contact_email'); ?></span><span class="val">' + displayValue(contact['email']) + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_contact_salary_grade'); ?></span><span class="val">' + displayValue(contact['salary_grade'] || contact['salarygrade'] || userdata['salary_grade'] || '') + '</span></li>';
            output += '</ul></div>';
            jQuery('#member_info').html(output);
        }

        function loadMember(value, column, emptyAlert) {
            value = jQuery.trim(String(value == null ? '' : value));
            if (value === '') {
                if (emptyAlert) {
                    showTimedWarning(emptyAlert);
                }
                return;
            }
            jQuery('#member_info').html('<div class="cbu-preview-empty"><i class="fa fa-spinner fa-spin"></i><?php echo lang('please_wait'); ?></div>');
            jQuery.ajax({
                url: SEARCH_URL,
                type: 'POST',
                dataType: 'text',
                data: { value: value, column: column },
                success: function(data) {
                    try {
                        var json = JSON.parse(data);
                        if (!json['success'] || json['success'].toString() == 'N') {
                            jQuery('#member_info').html('<div class="cbu-alert danger">' + escapeHtml(json['error'] || 'Invalid response from server') + '</div>');
                            renderLoansEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                            renderSavingsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                            renderCbuEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                            return;
                        }
                        var userdata = json['data'];
                        var contact = json['contact'] || {};
                        if (userdata && userdata['PID'] !== undefined) {
                            jQuery('#pid').val(userdata['PID']);
                        }
                        if (userdata && userdata['member_id']) {
                            jQuery('#member_id').val(userdata['member_id']);
                        }
                        renderMemberCard(userdata, contact);
                        loadMemberLoans(userdata['PID'], userdata['member_id']);
                        loadMemberSavings(userdata['PID'], userdata['member_id']);
                        loadMemberCbu(userdata['PID'], userdata['member_id']);
                    } catch (e) {
                        jQuery('#member_info').html('<div class="cbu-alert danger">Error parsing response: ' + escapeHtml(e.message) + '</div>');
                        renderLoansEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        renderSavingsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        renderCbuEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    if (textStatus !== 'abort') {
                        jQuery('#member_info').html('<div class="cbu-alert danger">Error: ' + escapeHtml(errorThrown) + '</div>');
                        renderLoansEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        renderSavingsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        renderCbuEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                    }
                }
            });
        }

        function attachSuggest(inputId, boxId, idKey) {
            var input = document.getElementById(inputId);
            var box = document.getElementById(boxId);
            if (!input || !box) {
                return;
            }

            var timer = null;
            var xhr = null;
            var items = [];
            var activeIndex = -1;
            var suppressBlur = false;

            function hideBox() {
                box.classList.remove('open');
                box.innerHTML = '';
                items = [];
                activeIndex = -1;
            }

            function setActive(index) {
                var nodes = box.querySelectorAll('.member-suggest-item');
                activeIndex = index;
                for (var i = 0; i < nodes.length; i++) {
                    if (i === activeIndex) {
                        nodes[i].classList.add('active');
                        if (nodes[i].scrollIntoView) {
                            nodes[i].scrollIntoView({ block: 'nearest' });
                        }
                    } else {
                        nodes[i].classList.remove('active');
                    }
                }
            }

            function choose(item) {
                if (!item) {
                    return;
                }
                jQuery('#pid').val(item.pid || '');
                jQuery('#member_id').val(item.member_id || '');
                hideBox();
                loadMember(idKey === 'pid' ? item.pid : item.member_id, idKey === 'pid' ? 'PID' : 'MID');
            }

            function render(list) {
                items = list || [];
                activeIndex = items.length ? 0 : -1;
                if (!items.length) {
                    box.innerHTML = '<div class="member-suggest-empty">No matching members</div>';
                    box.classList.add('open');
                    return;
                }
                var html = '';
                for (var i = 0; i < items.length; i++) {
                    var row = items[i];
                    var chip = idKey === 'pid' ? row.pid : row.member_id;
                    var extra = idKey === 'pid' ? row.member_id : row.pid;
                    html += '<button type="button" class="member-suggest-item' + (i === 0 ? ' active' : '') + '" data-index="' + i + '" role="option">' +
                        '<span class="suggest-id">' + escapeHtml(chip) + '</span>' +
                        '<span class="suggest-name">' + escapeHtml(row.name) + '</span>' +
                        '<span class="suggest-status">' + escapeHtml(extra || row.status) + '</span>' +
                        '</button>';
                }
                box.innerHTML = html;
                box.classList.add('open');
            }

            function fetchSuggestions() {
                var q = jQuery.trim(input.value || '');
                if (q.length < 1) {
                    hideBox();
                    return;
                }
                if (xhr && typeof xhr.abort === 'function') {
                    xhr.abort();
                }
                xhr = jQuery.getJSON(SUGGEST_URL + '?q=' + encodeURIComponent(q))
                    .done(function(data) { render(data || []); })
                    .fail(function(jqXHR, textStatus) {
                        if (textStatus !== 'abort') hideBox();
                    });
            }

            input.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(fetchSuggestions, 220);
            });
            input.addEventListener('keydown', function(e) {
                if (!box.classList.contains('open') || !items.length) {
                    return;
                }
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setActive(Math.min(activeIndex + 1, items.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setActive(Math.max(activeIndex - 1, 0));
                } else if (e.key === 'Enter' && activeIndex >= 0) {
                    e.preventDefault();
                    choose(items[activeIndex]);
                } else if (e.key === 'Escape') {
                    hideBox();
                }
            });
            input.addEventListener('blur', function() {
                setTimeout(function() {
                    if (!suppressBlur) hideBox();
                    suppressBlur = false;
                }, 150);
            });
            box.addEventListener('mousedown', function() { suppressBlur = true; });
            box.addEventListener('click', function(e) {
                var btn = e.target;
                while (btn && btn !== box && !btn.classList.contains('member-suggest-item')) {
                    btn = btn.parentNode;
                }
                if (!btn || !btn.classList.contains('member-suggest-item')) {
                    return;
                }
                choose(items[parseInt(btn.getAttribute('data-index'), 10)]);
            });
        }

        jQuery(window).keydown(function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        if (!isMemberUser) {
            attachSuggest('pid', 'pid-suggest-box', 'pid');
            attachSuggest('member_id', 'mid-suggest-box', 'member_id');
        }

        var pid = jQuery.trim(jQuery('#pid').val() || '<?php echo set_value('pid'); ?>');
        if (pid !== '') {
            loadMember(pid, 'PID');
        }

        jQuery('#search_pid').click(function() {
            loadMember(jQuery('#pid').val(), 'PID', '<?php echo lang('alert_pid'); ?>');
        });
        jQuery('#search_mid').click(function() {
            loadMember(jQuery('#member_id').val(), 'MID', '<?php echo lang('alert_member_id'); ?>');
        });
    }

    if (document.readyState === 'complete') {
        initDatePicker();
        initMain();
    } else {
        window.addEventListener('load', function() {
            initDatePicker();
            initMain();
        });
        setTimeout(function() {
            initDatePicker();
            initMain();
        }, 300);
    }
})();
</script>
