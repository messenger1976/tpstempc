<?php
// detect female option keys to control maiden name visibility
$femaleKeys = array();
foreach (lang('member_genderoption') as $k => $v) {
    if (stripos(trim($v), 'female') !== false) {
        $femaleKeys[] = $k;
    }
}
if (empty($femaleKeys)) {
    $femaleKeys = array('F', 'f', 'female');
}
$selectedGender = set_value('gender');
$isFemale = in_array($selectedGender, $femaleKeys) || in_array(strtolower($selectedGender), array_map('strtolower', $femaleKeys));
?>
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<style>
.member-info-page { margin-top: 4px; }
.member-info-page .member-profile-card {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 20px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.member-info-page .member-photo-wrap {
    width: 120px;
    height: 120px;
    margin: 0 auto 16px;
    border-radius: 50%;
    padding: 4px;
    background: #fff;
    border: 3px solid #1ab394;
    box-shadow: 0 4px 14px rgba(26,179,148,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
}
.member-info-page .member-photo-wrap i {
    font-size: 46px;
    color: #1ab394;
}
.member-info-page .member-name {
    margin: 0 0 6px;
    font-size: 18px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.member-info-page .member-meta {
    color: #888;
    font-size: 12px;
    margin-bottom: 16px;
    line-height: 1.5;
}
.member-info-page .member-detail-list {
    text-align: left;
    margin: 0;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.member-info-page .member-detail-list li {
    display: flex;
    gap: 10px;
    padding: 11px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
    color: #676a6c;
    align-items: flex-start;
}
.member-info-page .member-detail-list li:last-child { border-bottom: 0; }
.member-info-page .member-detail-list i {
    color: #1ab394;
    width: 16px;
    margin-top: 2px;
}
.member-info-page .member-form-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.member-info-page .member-form-panel .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.member-info-page .member-form-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.member-info-page .member-form-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.member-info-page .member-form-panel .panel-body { padding: 22px 20px 8px; overflow: visible; }
.member-info-page .bootstrap-datetimepicker-widget {
    z-index: 1060 !important;
}
.member-info-page .form-horizontal .form-group { margin-bottom: 16px; }
.member-info-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.member-info-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.member-info-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.member-info-page .input-group-addon {
    border-radius: 0 6px 6px 0;
    background: #f8fafb;
}
.member-info-page .input-group .form-control {
    border-radius: 6px 0 0 6px;
}
.member-info-page .required { color: #ed5565; }
.member-info-page .member-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.member-info-page .member-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.member-info-page .member-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.member-info-page .member-form-actions {
    margin-top: 8px;
    margin-bottom: 12px;
    padding-top: 8px;
    border-top: 1px solid #f0f2f3;
}
.member-info-page .member-form-actions .btn-primary {
    min-width: 160px;
    padding: 9px 20px;
    border-radius: 6px;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.member-info-page .file-hint {
    display: block;
    margin-top: 6px;
    color: #999;
    font-size: 12px;
}
.member-info-page .section-divider {
    margin: 8px 0 18px;
    padding: 10px 0 8px;
    border-bottom: 1px solid #eef1f2;
    color: #1ab394;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: .02em;
}
.member-info-page .section-divider i { margin-right: 6px; }
@media (max-width: 991px) {
    .member-info-page .member-profile-card {
        max-width: 360px;
        margin-left: auto;
        margin-right: auto;
    }
}
</style>

<div class="col-lg-12 member-info-page">
    <div class="row">
        <div class="col-lg-3 col-md-4">
            <aside class="member-profile-card">
                <div class="member-photo-wrap">
                    <i class="fa fa-user-plus"></i>
                </div>
                <h3 class="member-name"><?php echo lang('member_registration'); ?></h3>
                <div class="member-meta">Fill in the member details to create a new membership record.</div>
                <ul class="member-detail-list">
                    <li><i class="fa fa-check-circle"></i><span>Enter registration fee and member ID</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Complete personal information</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Upload a photo (optional)</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Save to continue with contact & next of kin</span></li>
                </ul>
            </aside>
        </div>

        <div class="col-lg-9 col-md-8">
            <?php echo form_open_multipart(current_lang() . "/member/new_member", 'class="form-horizontal"'); ?>

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
                    <i class="fa fa-id-card-o"></i>
                    <h4><?php echo lang('member_registration'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="section-divider">
                        <i class="fa fa-money"></i>Registration Details
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_registration_fee'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="fee" value="<?php echo set_value('fee'); ?>" class="form-control amountformat"/>
                            <?php echo form_error('fee'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_member_id'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="memberid" value="<?php echo set_value('memberid'); ?>" class="form-control"/>
                            <?php echo form_error('memberid'); ?>
                        </div>
                    </div>

                    <div class="section-divider">
                        <i class="fa fa-user"></i><?php echo lang('member_basic_info'); ?>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_firstname'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="firstname" value="<?php echo set_value('firstname'); ?>" class="form-control"/>
                            <?php echo form_error('firstname'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_middlename'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="middlename" value="<?php echo set_value('middlename'); ?>" class="form-control"/>
                            <?php echo form_error('middlename'); ?>
                        </div>
                    </div>
                    <div id="maiden-group" class="form-group" style="<?php echo ($isFemale ? '' : 'display:none;'); ?>">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_maidenname'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="maidenname" value="<?php echo set_value('maidenname'); ?>" class="form-control"/>
                            <?php echo form_error('maidenname'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_lastname'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="lastname" value="<?php echo set_value('lastname'); ?>" class="form-control"/>
                            <?php echo form_error('lastname'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_gender'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <select id="gender" name="gender" class="form-control">
                                <option value=""> <?php echo lang('select_default_text'); ?></option>
                                <?php
                                $loop = lang('member_genderoption');
                                $selected = set_value('gender');
                                foreach ($loop as $key => $value) {
                                    ?>
                                    <option <?php echo ($selected ? ($selected == $key ? 'selected="selected"' : '') : ''); ?> value="<?php echo $key; ?>"> <?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('gender'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_maritalstatus'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <select name="maritalstatus" class="form-control">
                                <option value=""> <?php echo lang('select_default_text'); ?></option>
                                <?php
                                $loop = lang('member_maritalstatus_option');
                                $selected = set_value('maritalstatus');
                                foreach ($loop as $key => $value) {
                                    ?>
                                    <option <?php echo ($selected ? ($selected == $key ? 'selected="selected"' : '') : ''); ?> value="<?php echo $key; ?>"> <?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('maritalstatus'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_dob'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <div class="input-group date" id="datetimepicker">
                                <input type="text" name="dob" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('dob'); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
                                <span class="input-group-addon">
                                    <span class="fa fa-calendar"></span>
                                </span>
                            </div>
                            <?php echo form_error('dob'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label">Place of Birth :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="placeofbirth" value="<?php echo set_value('placeofbirth', (isset($basicinfo) && isset($basicinfo->placeofbirth)) ? $basicinfo->placeofbirth : ''); ?>" class="form-control"/>
                            <?php echo form_error('placeofbirth'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_join_date'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <div class="input-group date" id="datetimepicker2">
                                <input type="text" name="joindate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('joindate'); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
                                <span class="input-group-addon">
                                    <span class="fa fa-calendar"></span>
                                </span>
                            </div>
                            <?php echo form_error('joindate'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_photo'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif"/>
                            <span class="file-hint">JPG, JPEG, PNG or GIF</span>
                            <?php if (isset($logo_error)) {
                                echo '<div class="error_message">' . $logo_error . '</div>';
                            } ?>
                        </div>
                    </div>

                    <div class="form-group member-form-actions">
                        <label class="col-lg-3 col-md-4 control-label">&nbsp;</label>
                        <div class="col-lg-7 col-md-8">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-user-plus"></i> <?php echo lang('member_addbtn'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>

            <script type="text/javascript">
                (function() {
                    var datepickerScript = '<?php echo base_url(); ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
                    var momentScript = '<?php echo base_url(); ?>media/js/script/moment.js';
                    var loadingPicker = false;

                    function loadScript(src, done) {
                        var existing = document.querySelector('script[src="' + src + '"]');
                        if (existing) {
                            done();
                            return;
                        }
                        var script = document.createElement('script');
                        script.src = src;
                        script.onload = done;
                        script.onerror = done;
                        document.body.appendChild(script);
                    }

                    function initDatePickers() {
                        if (typeof jQuery === 'undefined') {
                            setTimeout(initDatePickers, 50);
                            return;
                        }
                        if (typeof moment === 'undefined') {
                            loadScript(momentScript, function() {
                                setTimeout(initDatePickers, 20);
                            });
                            return;
                        }
                        if (typeof jQuery.fn.datetimepicker === 'undefined') {
                            if (!loadingPicker) {
                                loadingPicker = true;
                                loadScript(datepickerScript, function() {
                                    loadingPicker = false;
                                    setTimeout(initDatePickers, 20);
                                });
                            } else {
                                setTimeout(initDatePickers, 50);
                            }
                            return;
                        }

                        var $dob = jQuery('#datetimepicker');
                        var $join = jQuery('#datetimepicker2');
                        if ($dob.length && !$dob.data('DateTimePicker')) {
                            $dob.datetimepicker({ pickTime: false });
                        }
                        if ($join.length && !$join.data('DateTimePicker')) {
                            $join.datetimepicker({ pickTime: false });
                        }
                    }

                    function initGenderToggle() {
                        if (typeof jQuery === 'undefined') {
                            setTimeout(initGenderToggle, 50);
                            return;
                        }
                        var femaleKeys = <?php echo json_encode($femaleKeys); ?>;
                        function isFemale(val, text){
                            if(!val && !text) return false;
                            if(val && femaleKeys.indexOf(val) !== -1) return true;
                            var l = (val||'').toLowerCase();
                            if(l === 'f' || l === 'female') return true;
                            if(text && text.toLowerCase().indexOf('female') !== -1) return true;
                            return false;
                        }
                        jQuery('#gender').off('change.maiden').on('change.maiden', function(){
                            var val = jQuery(this).val();
                            var text = jQuery(this).find('option:selected').text();
                            if(isFemale(val, text)){
                                jQuery('#maiden-group').show();
                            } else {
                                jQuery('#maiden-group').hide();
                            }
                        });
                    }

                    // Footer scripts (moment + datetimepicker) load after this view;
                    // wait for them, then initialize.
                    if (document.readyState === 'complete') {
                        initDatePickers();
                        initGenderToggle();
                    } else {
                        window.addEventListener('load', function() {
                            initDatePickers();
                            initGenderToggle();
                        });
                        // Also retry shortly in case load already fired partially
                        setTimeout(function() {
                            initDatePickers();
                            initGenderToggle();
                        }, 300);
                    }
                })();
            </script>
        </div>
    </div>
</div>
