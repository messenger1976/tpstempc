<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<style type="text/css">
#datetimepicker { position: relative; z-index: 1; }

.cbu-page { margin-top: 4px; }
.cbu-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.cbu-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.cbu-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.cbu-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.cbu-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.cbu-page .cbu-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cbu-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.cbu-page .cbu-panel .panel-body { padding: 22px 20px 12px; overflow: visible; }
.cbu-page .form-horizontal .form-group { margin-bottom: 16px; }
.cbu-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.cbu-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.cbu-page textarea.form-control { height: auto; min-height: 80px; }
.cbu-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.cbu-page .input-group-addon {
    background: #f8fafb;
    cursor: pointer;
}
.cbu-page .input-group-addon:hover { color: #1ab394; }
.cbu-page .required { color: #ed5565; }
.cbu-page .section-divider {
    margin: 8px 0 18px;
    padding: 10px 0 8px;
    border-bottom: 1px solid #eef1f2;
    color: #1ab394;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: .02em;
}
.cbu-page .section-divider i { margin-right: 6px; }
.cbu-page .cbu-actions {
    margin-top: 8px;
    margin-bottom: 8px;
    padding-top: 12px;
    border-top: 1px solid #f0f2f3;
}
.cbu-page .cbu-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
    border-radius: 6px;
    font-weight: 600;
    margin-right: 8px;
}
.cbu-page .cbu-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
.cbu-page .cbu-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.cbu-page .cbu-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.cbu-page .cbu-lookup { position: relative; }
.cbu-page .member-suggest-box {
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
.cbu-page .member-suggest-box.open { display: block; }
.cbu-page .member-suggest-item {
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
.cbu-page .member-suggest-item:last-child { border-bottom: 0; }
.cbu-page .member-suggest-item:hover,
.cbu-page .member-suggest-item.active {
    background: #e8f8f5;
}
.cbu-page .member-suggest-item .suggest-id {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 11px;
    white-space: nowrap;
}
.cbu-page .member-suggest-item .suggest-name {
    flex: 1;
    font-weight: 600;
    color: #2f4050;
    font-size: 13px;
}
.cbu-page .member-suggest-item .suggest-status {
    color: #999;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.cbu-page .member-suggest-empty {
    padding: 12px;
    color: #999;
    font-size: 12px;
}
.cbu-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 20px;
    min-height: 280px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.cbu-page .cbu-preview-empty {
    text-align: center;
    color: #999;
    padding: 40px 12px;
}
.cbu-page .cbu-preview-empty i {
    font-size: 42px;
    color: #c9ebe3;
    display: block;
    margin-bottom: 12px;
}
.cbu-page .cbu-member-card { text-align: center; }
.cbu-page .cbu-member-photo {
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
.cbu-page .cbu-member-photo img {
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    object-fit: cover;
    object-position: center top;
    border-radius: 50%;
    display: block;
}
.cbu-page .cbu-member-photo.avatar-fallback img {
    object-fit: contain;
    object-position: center center;
    background: #e8f8f5;
}
.cbu-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.cbu-page .cbu-member-photo-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8f8f5;
}
.cbu-page .cbu-member-photo-empty i {
    font-size: 42px;
    color: #1ab394;
}
.cbu-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.cbu-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.cbu-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.cbu-page .cbu-member-details {
    text-align: left;
    margin: 0 0 16px;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.cbu-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.cbu-page .cbu-member-details li:last-child { border-bottom: 0; }
.cbu-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.cbu-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.cbu-page .cbu-share-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 12px;
}
.cbu-page .cbu-share-stat {
    background: #f8fafb;
    border: 1px solid #eef1f2;
    border-radius: 8px;
    padding: 10px 12px;
    text-align: left;
}
.cbu-page .cbu-share-stat .lbl {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .03em;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 4px;
}
.cbu-page .cbu-share-stat .val {
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
    font-variant-numeric: tabular-nums;
}
.cbu-page .cbu-balance {
    background: linear-gradient(165deg, #1ab394 0%, #18a689 100%);
    color: #fff;
    border-radius: 10px;
    padding: 14px 16px;
    text-align: center;
}
.cbu-page .cbu-balance .lbl {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    opacity: .9;
    margin-bottom: 4px;
}
.cbu-page .cbu-balance .val {
    font-size: 22px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.cbu-page .bootstrap-datetimepicker-widget { z-index: 1060 !important; }
</style>

<?php echo form_open_multipart(current_lang() . "/share/share_buy", 'class="form-horizontal"'); ?>

<div class="col-lg-12 cbu-page">
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
                    <i class="fa fa-certificate"></i>
                    <h4><?php echo lang('share_buy'); ?></h4>
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
                                    <input type="text" id="pid" name="pid" value="<?php echo set_value('pid'); ?>" class="form-control" autocomplete="off"/>
                                    <span class="input-group-addon" id="search_pid">
                                        <span class="fa fa-search"></span>
                                    </span>
                                </div>
                                <div id="pid-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                            </div>
                            <?php echo form_error('pid'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('member_member_id'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <div class="cbu-lookup">
                                <div class="input-group">
                                    <input type="text" id="member_id" name="member_id" value="<?php echo set_value('member_id'); ?>" class="form-control" autocomplete="off"/>
                                    <span class="input-group-addon" id="search_mid">
                                        <span class="fa fa-search"></span>
                                    </span>
                                </div>
                                <div id="mid-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                            </div>
                            <?php echo form_error('member_id'); ?>
                        </div>
                    </div>

                    <div class="section-divider">
                        <i class="fa fa-exchange"></i><?php echo lang('share_payment_info_label'); ?>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('transaction_date'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <div class="input-group date" id="datetimepicker">
                                <input type="text" name="trans_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('trans_date', date('d-m-Y')); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
                                <span class="input-group-addon">
                                    <span class="fa fa-calendar"></span>
                                </span>
                            </div>
                            <?php echo form_error('trans_date'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('index_amount'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="open_balance" value="<?php echo set_value('open_balance'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('open_balance'); ?>
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
                        <label class="col-lg-4 control-label"><?php echo lang('comment'); ?> :</label>
                        <div class="col-lg-7">
                            <textarea name="comment" class="form-control"><?php echo set_value('comment'); ?></textarea>
                            <?php echo form_error('comment'); ?>
                        </div>
                    </div>

                    <div class="form-group cbu-actions">
                        <label class="col-lg-4 control-label">&nbsp;</label>
                        <div class="col-lg-7">
                            <a href="<?php echo site_url(current_lang() . '/share/share_buy'); ?>" class="btn btn-default">
                                <i class="fa fa-undo"></i> Clear
                            </a>
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-save"></i> <?php echo lang('member_group_btn'); ?>
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
                    Search a member by PID or Member ID to view details and share balance.
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
        function initTransactionDatePicker() {
            if (typeof $.fn.datetimepicker === 'undefined') {
                var datepickerScript = document.createElement('script');
                datepickerScript.src = '<?php echo base_url(); ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
                datepickerScript.onload = initTransactionDatePicker;
                document.head.appendChild(datepickerScript);
                return;
            }
            if (!$('#datetimepicker').data('DateTimePicker')) {
                $('#datetimepicker').datetimepicker({
                    pickTime: false,
                    format: 'DD-MM-YYYY'
                });
            }
        }
        initTransactionDatePicker();
        $(window).on('load', initTransactionDatePicker);

        var SUGGEST_URL = '<?php echo site_url(current_lang() . '/member/autosuggest_member_list'); ?>';
        var SEARCH_URL = '<?php echo site_url(current_lang() . '/saving/search_member_share/'); ?>';
        var PHOTO_BASE = '<?php echo base_url(); ?>uploads/memberphoto/';
        var genderLabels = <?php echo json_encode(lang('member_genderoption')); ?>;

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

        function renderMemberCard(userdata, contact, share) {
            contact = contact || {};
            share = share || {};
            var photo = userdata['photo'] ? userdata['photo'].toString() : '';
            var name = $.trim((userdata['firstname'] || '') + ' ' + (userdata['middlename'] || '') + ' ' + (userdata['lastname'] || ''));
            var gender = genderLabel(userdata['gender']);
            var accountStatus = (typeof memberAccountStatus === 'function') ? memberAccountStatus(userdata) : { label: '', active: true };
            var statusLabel = (window.TAPSTEMCO_MEMBER_STATUS && TAPSTEMCO_MEMBER_STATUS.label) ? TAPSTEMCO_MEMBER_STATUS.label : '<?php echo lang('member_status'); ?>';
            var shareCount = parseInt(share['share'], 10) || 0;
            var maxShare = parseInt(share['max_share'], 10) || 0;
            var remainShare = maxShare - shareCount;
            if (isNaN(remainShare)) {
                remainShare = 0;
            }

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
            if (userdata['PID']) {
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
            output += '<li><span class="lbl"><?php echo lang('member_dob'); ?></span><span class="val">' + displayValue(userdata['dob']) + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_join_date'); ?></span><span class="val">' + displayValue(userdata['joiningdate']) + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_contact_phone1'); ?></span><span class="val">' + displayValue(contact['phone1']) + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_contact_phone2'); ?></span><span class="val">' + displayValue(contact['phone2']) + '</span></li>';
            output += '<li><span class="lbl"><?php echo lang('member_contact_email'); ?></span><span class="val">' + displayValue(contact['email']) + '</span></li>';
            output += '</ul>';
            output += '<div class="cbu-share-stats">';
            output += '<div class="cbu-share-stat"><span class="lbl"><?php echo lang('balance_share'); ?></span><span class="val">' + escapeHtml(String(shareCount)) + '</span></div>';
            output += '<div class="cbu-share-stat"><span class="lbl"><?php echo lang('remainshare'); ?></span><span class="val">' + escapeHtml(String(remainShare)) + '</span></div>';
            output += '<div class="cbu-share-stat"><span class="lbl"><?php echo lang('min_share'); ?></span><span class="val">' + displayValue(share['min_share']) + '</span></div>';
            output += '<div class="cbu-share-stat"><span class="lbl"><?php echo lang('max_share'); ?></span><span class="val">' + displayValue(share['max_share']) + '</span></div>';
            output += '</div>';
            output += '<div class="cbu-balance"><span class="lbl"><?php echo lang('total_shareamount'); ?></span><span class="val">' + escapeHtml(addCommas(share['amount'] || 0)) + '</span></div>';
            output += '</div>';
            $('#member_info').html(output);
        }

        function loadMember(value, column, emptyAlert) {
            value = $.trim(String(value == null ? '' : value));
            if (value === '') {
                if (emptyAlert) {
                    if (typeof swal === 'function') {
                        swal({ title: emptyAlert, type: 'warning', timer: 2500, showConfirmButton: false });
                    } else {
                        alert(emptyAlert);
                    }
                }
                return;
            }
            $('#member_info').html('<div class="cbu-preview-empty"><i class="fa fa-spinner fa-spin"></i><?php echo lang('please_wait'); ?></div>');
            $.ajax({
                url: SEARCH_URL,
                type: 'POST',
                data: {
                    value: value,
                    column: column
                },
                success: function(data) {
                    var json = JSON.parse(data);
                    if (json['success'].toString() == 'N') {
                        $('#member_info').html('<div class="cbu-alert danger">' + escapeHtml(json['error']) + '</div>');
                        return;
                    }
                    var userdata = json['data'];
                    var contact = json['contact'] || {};
                    $('#pid').val(userdata['PID']);
                    $('#member_id').val(userdata['member_id']);
                    renderMemberCard(userdata, contact, json['share']);
                },
                error: function(xhr, textStatus, errorThrown) {
                    if (textStatus !== 'abort') {
                        alert(errorThrown);
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
                $('#pid').val(item.pid || '');
                $('#member_id').val(item.member_id || '');
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
                var q = $.trim(input.value || '');
                if (q.length < 1) {
                    hideBox();
                    return;
                }
                if (xhr && typeof xhr.abort === 'function') {
                    xhr.abort();
                }
                xhr = jQuery.getJSON(SUGGEST_URL + '?q=' + encodeURIComponent(q))
                    .done(function(data) {
                        render(data || []);
                    })
                    .fail(function(jqXHR, textStatus) {
                        if (textStatus !== 'abort') {
                            hideBox();
                        }
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
                    if (!suppressBlur) {
                        hideBox();
                    }
                    suppressBlur = false;
                }, 150);
            });

            box.addEventListener('mousedown', function() {
                suppressBlur = true;
            });

            box.addEventListener('click', function(e) {
                var btn = e.target;
                while (btn && btn !== box && !btn.classList.contains('member-suggest-item')) {
                    btn = btn.parentNode;
                }
                if (!btn || !btn.classList.contains('member-suggest-item')) {
                    return;
                }
                var idx = parseInt(btn.getAttribute('data-index'), 10);
                choose(items[idx]);
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
            var val = $(this).val();
            if (val == 'CHEQUE') {
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

        attachSuggest('pid', 'pid-suggest-box', 'pid');
        attachSuggest('member_id', 'mid-suggest-box', 'member_id');

        var pid = '<?php echo set_value('pid'); ?>';
        if (pid.length > 0) {
            loadMember(pid, 'PID');
        }

        $("#search_pid").click(function() {
            loadMember($("#pid").val(), 'PID', '<?php echo lang("alert_pid"); ?>');
        });

        $("#search_mid").click(function() {
            loadMember($("#member_id").val(), 'MID', '<?php echo lang("alert_member_id"); ?>');
        });

            });
        }
        initScripts();
    })();
</script>
