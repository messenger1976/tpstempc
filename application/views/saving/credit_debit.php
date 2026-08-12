<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<style type="text/css">
#datetimepicker { position: relative; z-index: 1; }

.saving-cd-page { margin-top: 4px; }
.saving-cd-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.saving-cd-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.saving-cd-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.saving-cd-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.saving-cd-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.saving-cd-page .cbu-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.saving-cd-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.saving-cd-page .cbu-panel .panel-body { padding: 22px 20px 12px; overflow: visible; }
.saving-cd-page .form-horizontal .form-group { margin-bottom: 16px; }
.saving-cd-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.saving-cd-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.saving-cd-page textarea.form-control { height: auto; min-height: 80px; }
.saving-cd-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.saving-cd-page .input-group-addon {
    background: #f8fafb;
    cursor: pointer;
}
.saving-cd-page .input-group-addon:hover { color: #1ab394; }
.saving-cd-page .required { color: #ed5565; }
.saving-cd-page .section-divider {
    margin: 8px 0 18px;
    padding: 10px 0 8px;
    border-bottom: 1px solid #eef1f2;
    color: #1ab394;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: .02em;
}
.saving-cd-page .section-divider i { margin-right: 6px; }
.saving-cd-page .cbu-actions {
    margin-top: 8px;
    margin-bottom: 8px;
    padding-top: 12px;
    border-top: 1px solid #f0f2f3;
}
.saving-cd-page .cbu-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
    border-radius: 6px;
    font-weight: 600;
}
.saving-cd-page .cbu-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.saving-cd-page .cbu-lookup { position: relative; }
.saving-cd-page .member-suggest-box {
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
.saving-cd-page .member-suggest-box.open { display: block; }
.saving-cd-page .member-suggest-item {
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
.saving-cd-page .member-suggest-item:last-child { border-bottom: 0; }
.saving-cd-page .member-suggest-item:hover,
.saving-cd-page .member-suggest-item.active { background: #e8f8f5; }
.saving-cd-page .member-suggest-item .suggest-id {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 11px;
    white-space: nowrap;
}
.saving-cd-page .member-suggest-item .suggest-name {
    flex: 1;
    font-weight: 600;
    color: #2f4050;
    font-size: 13px;
}
.saving-cd-page .member-suggest-item .suggest-status {
    color: #999;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.saving-cd-page .member-suggest-empty {
    padding: 12px;
    color: #999;
    font-size: 12px;
}
.saving-cd-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 20px;
    min-height: 280px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.saving-cd-page .cbu-preview-empty {
    text-align: center;
    color: #999;
    padding: 40px 12px;
}
.saving-cd-page .cbu-preview-empty i {
    font-size: 42px;
    color: #c9ebe3;
    display: block;
    margin-bottom: 12px;
}
.saving-cd-page .cbu-member-card { text-align: center; }
.saving-cd-page .cbu-member-photo {
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
.saving-cd-page .cbu-member-photo img {
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    object-fit: cover;
    object-position: center top;
    border-radius: 50%;
    display: block;
}
.saving-cd-page .cbu-member-photo.avatar-fallback img {
    object-fit: contain;
    object-position: center center;
    background: #e8f8f5;
}
.saving-cd-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.saving-cd-page .cbu-member-photo-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8f8f5;
}
.saving-cd-page .cbu-member-photo-empty i {
    font-size: 42px;
    color: #1ab394;
}
.saving-cd-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.saving-cd-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.saving-cd-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.saving-cd-page .cbu-member-details {
    text-align: left;
    margin: 0 0 14px;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.saving-cd-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.saving-cd-page .cbu-member-details li:last-child { border-bottom: 0; }
.saving-cd-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.saving-cd-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.saving-cd-page .cd-type-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 0 0 12px;
    text-align: left;
}
.saving-cd-page .cd-type-row .lbl {
    color: #676a6c;
    font-weight: 600;
    font-size: 13px;
}
.saving-cd-page .cd-type-row select {
    width: auto;
    min-width: 140px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #e5e6e7;
    padding: 4px 8px;
}
.saving-cd-page .cbu-balance {
    background: linear-gradient(165deg, #1ab394 0%, #18a689 100%);
    color: #fff;
    border-radius: 10px;
    padding: 14px 16px;
    text-align: center;
}
.saving-cd-page .cbu-balance .lbl {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    opacity: .9;
    margin-bottom: 4px;
}
.saving-cd-page .cbu-balance .val {
    font-size: 22px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.saving-cd-page .saving-accounts-card {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-top: 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.saving-cd-page .saving-accounts-card .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.saving-cd-page .saving-accounts-card .panel-head i {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.saving-cd-page .saving-accounts-card .panel-head h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #2f4050;
}
.saving-cd-page .saving-accounts-card .panel-body { padding: 0; }
.saving-cd-page .saving-account-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px dashed #eef1f2;
}
.saving-cd-page .saving-account-row:last-child { border-bottom: 0; }
.saving-cd-page .saving-account-row.current { background: #f7fcfa; }
.saving-cd-page .saving-account-row .acct-meta { text-align: left; min-width: 0; }
.saving-cd-page .saving-account-row .acct {
    display: block;
    font-weight: 700;
    color: #2f4050;
    font-size: 13px;
}
.saving-cd-page .saving-account-row .type {
    display: block;
    color: #888;
    font-size: 12px;
    margin-top: 2px;
}
.saving-cd-page .saving-account-row .bal-wrap { text-align: right; white-space: nowrap; }
.saving-cd-page .saving-account-row .bal {
    display: block;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #0e7c69;
    font-size: 14px;
}
.saving-cd-page .saving-account-row .status-pill {
    display: inline-block;
    margin-top: 4px;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    color: #fff;
}
.saving-cd-page .saving-account-row .status-pill.active { background: #1ab394; }
.saving-cd-page .saving-account-row .status-pill.inactive { background: #ed5565; }
.saving-cd-page .empty-accounts {
    text-align: center;
    color: #999;
    padding: 28px 16px;
    font-size: 13px;
}
.saving-cd-page .empty-accounts i {
    display: block;
    font-size: 28px;
    color: #c9ebe3;
    margin-bottom: 8px;
}
.saving-cd-page .bootstrap-datetimepicker-widget { z-index: 1060 !important; }
</style>

<?php echo form_open_multipart(current_lang() . "/saving/credit_debit", 'class="form-horizontal"'); ?>

<div class="col-lg-12 saving-cd-page">
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
                    <i class="fa fa-exchange"></i>
                    <h4><?php echo lang('saving_account_credit_debit'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="section-divider">
                        <i class="fa fa-university"></i>Account Lookup
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('account_no'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <div class="cbu-lookup">
                                <div class="input-group">
                                    <input type="text" id="pid" name="pid" value="<?php echo set_value('pid'); ?>" class="form-control" autocomplete="off"/>
                                    <span class="input-group-addon" id="search_account">
                                        <span class="fa fa-search"></span>
                                    </span>
                                </div>
                                <div id="account-suggest-box" class="member-suggest-box" role="listbox" aria-label="Account suggestions"></div>
                            </div>
                            <?php echo form_error('pid'); ?>
                        </div>
                    </div>

                    <div class="section-divider">
                        <i class="fa fa-money"></i>Transaction Details
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('mortuary_transaction_date'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <div class="input-group date" id="datetimepicker">
                                <?php $posting_date = (isset($value) ? date('d-m-Y', strtotime($value->posting_date)) : set_value('posting_date')); ?>
                                <input type="text" name="posting_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo $posting_date; ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
                                <span class="input-group-addon">
                                    <span class="fa fa-calendar"></span>
                                </span>
                            </div>
                            <?php echo form_error('posting_date'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('transaction_type'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <select name="trans_type" class="form-control">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php
                                $selected = set_value('trans_type');
                                $transtype = lang('saving_transaction_type_option');
                                foreach ($transtype as $key => $value) {
                                    ?>
                                    <option <?php echo ($key == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('trans_type'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label">Ref. No. :</label>
                        <div class="col-lg-7">
                            <input type="text" name="refno" value="<?php echo set_value('refno'); ?>" class="form-control"/>
                            <?php echo form_error('refno'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('amount'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="amount" value="<?php echo set_value('amount'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('amount'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('paymentmethod'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <select name="paymenthod" id="paymenthod" class="form-control">
                                <?php
                                $selected = set_value('paymenthod');
                                foreach ($paymenthod as $key => $value) {
                                    ?>
                                    <option <?php echo ($value->name == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('paymenthod'); ?>
                        </div>
                    </div>

                    <div id="chequenumber" class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('cheque_no'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="cheque" value="<?php echo set_value('cheque'); ?>" class="form-control"/>
                            <?php echo form_error('cheque'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('customer_name'); ?> :</label>
                        <div class="col-lg-7">
                            <input name="customer_name" id="customer_name" class="form-control" value="<?php echo set_value('customer_name'); ?>"/>
                            <?php echo form_error('customer_name'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('comment'); ?> :</label>
                        <div class="col-lg-7">
                            <textarea name="comment" class="form-control"><?php echo set_value('comment'); ?></textarea>
                            <?php echo form_error('comment'); ?>
                        </div>
                    </div>

                    <div class="form-group cbu-actions">
                        <label class="col-lg-4 control-label">&nbsp;</label>
                        <div class="col-lg-7">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-save"></i> <?php echo lang('record_btn'); ?>
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
                    Search an account number to view member details.
                </div>
            </div>
            <div class="saving-accounts-card" id="member_accounts_card">
                <div class="panel-head">
                    <i class="fa fa-university"></i>
                    <h4>Existing Savings Accounts</h4>
                </div>
                <div class="panel-body" id="member_accounts_body">
                    <div class="empty-accounts">
                        <i class="fa fa-university"></i>
                        Search an account to view existing savings accounts.
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
        function initScripts() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initScripts, 50);
                return;
            }

            $(document).ready(function() {
        function initDocumentDatePicker() {
            if (typeof $.fn.datetimepicker === 'undefined') {
                var datepickerScript = document.createElement('script');
                datepickerScript.src = '<?php echo base_url(); ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
                datepickerScript.onload = initDocumentDatePicker;
                document.head.appendChild(datepickerScript);
                return;
            }
            if (!$('#datetimepicker').data('DateTimePicker')) {
                $('#datetimepicker').datetimepicker({
                    pickTime: true,
                    format: 'DD-MM-YYYY'
                });
            }
        }
        initDocumentDatePicker();
        $(window).on('load', initDocumentDatePicker);

        var SUGGEST_URL = '<?php echo site_url(current_lang() . '/saving/autosuggest_account_list'); ?>';
        var SEARCH_URL = '<?php echo site_url(current_lang() . '/saving/search_account/'); ?>';
        var ACCOUNTS_URL = '<?php echo site_url(current_lang() . '/saving/member_saving_accounts'); ?>';
        var PHOTO_BASE = '<?php echo base_url(); ?>uploads/memberphoto/';
        var genderLabels = <?php echo json_encode(lang('member_genderoption')); ?>;
        var lastLookupPid = '';
        var currentAccountNo = '';

        function escapeHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function displayValue(value) {
            var text = $.trim(String(value == null ? '' : value));
            return text ? escapeHtml(text) : '&mdash;';
        }

        function genderLabel(code) {
            if (genderLabels && genderLabels[code]) {
                return genderLabels[code];
            }
            return code || '';
        }

        function addCommas(nStr) {
            nStr += '';
            var x = nStr.split('.');
            var x1 = x[0];
            var x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            return x1 + x2;
        }

        function showTimedWarning(title) {
            if (typeof swal === 'function') {
                swal({ title: title, type: 'warning', timer: 2500, showConfirmButton: false });
            } else {
                alert(title);
            }
        }

        function normalizePidInput(rawPid) {
            if (!rawPid) {
                return '';
            }
            var cleaned = $.trim(rawPid.toString());
            if (cleaned.indexOf(' - ') !== -1) {
                cleaned = $.trim(cleaned.split(' - ')[0]);
            }
            return cleaned;
        }

        function renderAccountsEmpty(message) {
            $('#member_accounts_body').html(
                '<div class="empty-accounts"><i class="fa fa-university"></i>' + escapeHtml(message) + '</div>'
            );
        }

        function renderMemberAccounts(accounts, selectedAccount) {
            accounts = accounts || [];
            if (!accounts.length) {
                renderAccountsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                return;
            }
            var html = '';
            for (var i = 0; i < accounts.length; i++) {
                var row = accounts[i];
                var acct = row.old_account ? row.old_account : row.account;
                var isCurrent = selectedAccount && String(row.account) === String(selectedAccount);
                html += '<div class="saving-account-row' + (isCurrent ? ' current' : '') + '">';
                html += '<div class="acct-meta">';
                html += '<span class="acct">' + escapeHtml(acct) + '</span>';
                if (row.type) {
                    html += '<span class="type">' + escapeHtml(row.type) + '</span>';
                }
                html += '</div>';
                html += '<div class="bal-wrap">';
                html += '<span class="bal">' + escapeHtml(row.balance) + '</span>';
                html += '<span class="status-pill ' + (row.active ? 'active' : 'inactive') + '">' + escapeHtml(row.status) + '</span>';
                html += '</div></div>';
            }
            $('#member_accounts_body').html(html);
        }

        function loadMemberAccounts(memberPid, selectedAccount) {
            renderAccountsEmpty(<?php echo json_encode(lang('please_wait')); ?>);
            $.ajax({
                url: ACCOUNTS_URL,
                type: 'POST',
                dataType: 'json',
                data: { pid: memberPid || '' },
                success: function(json) {
                    if (!json || json.success !== 'Y') {
                        renderAccountsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                        return;
                    }
                    renderMemberAccounts(json.accounts || [], selectedAccount);
                },
                error: function() {
                    renderAccountsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                }
            });
        }

        function accountTypeLabel(typeKey) {
            if (typeKey === 'special') {
                return 'Special';
            }
            if (typeKey === 'mso') {
                return 'MSO';
            }
            return '-';
        }

        function renderMemberInfo(json) {
            var userdata = json['data'] || {};
            var contact = json['contact'] || {};
            var account_balance = json['accountinfo'] || {};
            var accountTotals = json['account_totals'] || {};
            var selectedType = (json['selected_account_type'] || '').toLowerCase();
            var specialTotal = parseFloat(accountTotals['special'] || 0);
            var msoTotal = parseFloat(accountTotals['mso'] || 0);

            if (selectedType !== 'special' && selectedType !== 'mso') {
                if (specialTotal > 0) {
                    selectedType = 'special';
                } else if (msoTotal > 0) {
                    selectedType = 'mso';
                }
            }

            var activeBalance = parseFloat(account_balance['balance'] || 0);
            if (selectedType === 'special') {
                activeBalance = specialTotal;
            } else if (selectedType === 'mso') {
                activeBalance = msoTotal;
            }

            var photo = userdata['photo'] ? userdata['photo'].toString() : '';
            var name = $.trim((userdata['firstname'] || '') + ' ' + (userdata['middlename'] || '') + ' ' + (userdata['lastname'] || ''));
            var gender = genderLabel(userdata['gender']);
            var accountStatus = (typeof memberAccountStatus === 'function') ? memberAccountStatus(userdata) : { label: '', active: true };
            var statusLabel = (window.TAPSTEMCO_MEMBER_STATUS && TAPSTEMCO_MEMBER_STATUS.label) ? TAPSTEMCO_MEMBER_STATUS.label : '<?php echo lang('member_status'); ?>';
            $('#customer_name').val(name);

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
            if (account_balance['account']) {
                output += '<span class="cbu-badge">' + escapeHtml(account_balance['old_members_acct'] || account_balance['account']) + '</span>';
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
            output += '<li><span class="lbl">Savings Account Type</span><span class="val" id="member_savings_type_label">' + escapeHtml(accountTypeLabel(selectedType)) + '</span></li>';
            output += '</ul>';
            output += '<div class="cd-type-row"><span class="lbl">Account Type</span>';
            output += '<select id="member_account_type">';
            output += '<option value="special"' + (selectedType === 'special' ? ' selected="selected"' : '') + '>Special</option>';
            output += '<option value="mso"' + (selectedType === 'mso' ? ' selected="selected"' : '') + '>MSO</option>';
            output += '</select></div>';
            output += '<div class="cbu-balance"><span class="lbl"><?php echo lang('balance'); ?></span><span class="val" id="member_balance_value">' + addCommas(activeBalance.toFixed(2)) + '</span></div>';
            output += '</div>';
            $('#member_info').html(output);

            $('#member_account_type').off('change').on('change', function() {
                var selected = $(this).val();
                var selectedBalance = 0;
                if (selected === 'special') {
                    selectedBalance = specialTotal;
                } else if (selected === 'mso') {
                    selectedBalance = msoTotal;
                }
                $('#member_savings_type_label').text(accountTypeLabel(selected));
                $('#member_balance_value').text(addCommas(parseFloat(selectedBalance).toFixed(2)));
            });

            currentAccountNo = account_balance['account'] || '';
            if (userdata['PID'] !== undefined && userdata['PID'] !== null && String(userdata['PID']) !== '') {
                loadMemberAccounts(userdata['PID'], currentAccountNo);
            } else {
                renderAccountsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
            }
        }

        function handleSearchAccountResponse(data) {
            var json;
            if (typeof data === 'object' && data !== null) {
                json = data;
            } else if (typeof data === 'string') {
                data = data.trim();
                if (!data) {
                    $('#member_info').html('<div class="cbu-alert danger">Error: Invalid response from server. Please try again.</div>');
                    renderAccountsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                    return;
                }
                try {
                    json = JSON.parse(data);
                } catch (e) {
                    $('#member_info').html('<div class="cbu-alert danger">Error: Invalid response from server. Please try again.</div>');
                    renderAccountsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                    return;
                }
            } else {
                $('#member_info').html('<div class="cbu-alert danger">Error: Invalid response from server. Please try again.</div>');
                renderAccountsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                return;
            }

            if (!json['success'] || json['success'].toString() == 'N') {
                $('#member_info').html('<div class="cbu-alert danger">' + escapeHtml(json['error'] || <?php echo json_encode(lang('invalid_account')); ?>) + '</div>');
                renderAccountsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
            } else {
                renderMemberInfo(json);
            }
        }

        function lookupAccountByPid(pid) {
            $('#member_info').html('<div class="cbu-preview-empty"><i class="fa fa-spinner fa-spin"></i><?php echo lang('please_wait'); ?></div>');
            $.ajax({
                url: SEARCH_URL,
                type: 'POST',
                dataType: 'text',
                data: {
                    value: pid,
                    column: 'PID'
                },
                success: handleSearchAccountResponse,
                error: function() {
                    $('#member_info').html('<div class="cbu-alert danger">Error: Unable to connect to server. Please try again.</div>');
                    renderAccountsEmpty(<?php echo json_encode(lang('no_records_found')); ?>);
                }
            });
        }

        function triggerLookupFromPidField(forceLookup, emptyAlert) {
            var normalizedPid = normalizePidInput($('#pid').val());
            if (normalizedPid.length === 0) {
                if (emptyAlert) {
                    showTimedWarning(emptyAlert);
                }
                return;
            }
            if (forceLookup === true || normalizedPid !== lastLookupPid) {
                lastLookupPid = normalizedPid;
                lookupAccountByPid(normalizedPid);
            }
        }

        function attachAccountSuggest() {
            var input = document.getElementById('pid');
            var box = document.getElementById('account-suggest-box');
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
                $('#pid').val(item.account || '');
                hideBox();
                lastLookupPid = item.account || '';
                lookupAccountByPid(lastLookupPid);
            }

            function render(list) {
                items = list || [];
                activeIndex = items.length ? 0 : -1;
                if (!items.length) {
                    box.innerHTML = '<div class="member-suggest-empty">No matching accounts</div>';
                    box.classList.add('open');
                    return;
                }

                var html = '';
                for (var i = 0; i < items.length; i++) {
                    var row = items[i];
                    html += '<button type="button" class="member-suggest-item' + (i === 0 ? ' active' : '') + '" data-index="' + i + '" role="option">' +
                        '<span class="suggest-id">' + escapeHtml(row.display_account || row.account) + '</span>' +
                        '<span class="suggest-name">' + escapeHtml(row.name) + '</span>' +
                        '<span class="suggest-status">' + escapeHtml(row.type_label || '') + '</span>' +
                        '</button>';
                }
                box.innerHTML = html;
                box.classList.add('open');
            }

            function fetchSuggestions() {
                var q = $.trim(input.value || '');
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

        $("#chequenumber").hide();
        var paymenthod = '<?php echo set_value("paymenthod"); ?>';
        if (paymenthod == 'CHEQUE') {
            $("#chequenumber").show();
        } else {
            $("#chequenumber").hide();
        }
        $("#paymenthod").change(function() {
            if ($(this).val() == 'CHEQUE') {
                $("#chequenumber").show();
            } else {
                $("#chequenumber").hide();
            }
        });

        $(window).keydown(function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        attachAccountSuggest();

        var pid = normalizePidInput('<?php echo set_value('pid'); ?>');
        if (pid.length > 0) {
            lastLookupPid = pid;
            lookupAccountByPid(lastLookupPid);
        }

        $('#pid').on('change blur', function() {
            triggerLookupFromPidField(false);
        });

        $('#search_account').click(function() {
            triggerLookupFromPidField(true, 'Please enter Account Number');
        });

            });
        }
        initScripts();
    })();
</script>
