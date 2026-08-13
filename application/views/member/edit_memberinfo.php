<?php
$this->load->view('member/topmenu');

$gender_options = lang('member_genderoption');
$member_id_label = ($basicinfo->none_member) ? lang('member_none_member_id') : lang('member_member_id');
$join_date_label = ($basicinfo->none_member) ? lang('member_reg_date') : lang('member_join_date');

// determine which option keys correspond to "female" (try to detect localized label)
$femaleKeys = array();
foreach ($gender_options as $k => $v) {
    if (stripos(trim($v), 'female') !== false) {
        $femaleKeys[] = $k;
    }
}
// fallback common keys
if (empty($femaleKeys)) {
    $femaleKeys = array('F', 'f', 'female');
}
$isFemale = in_array($basicinfo->gender, $femaleKeys) || in_array(strtolower($basicinfo->gender), array_map('strtolower', $femaleKeys));
?>

<div class="col-lg-12 member-info-page">
    <div class="row">
        <div class="col-lg-3 col-md-4">
            <?php $this->load->view('member/member_profile_sidebar'); ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
            <?php echo form_open_multipart(current_lang() . "/member/memberinfo/" . encode_id($basicinfo->id), 'class="form-horizontal"'); ?>

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
                    <h4><?php echo lang('member_basic_info'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo $member_id_label; ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="memberid" value="<?php echo $basicinfo->member_id; ?>" class="form-control" <?php echo ($basicinfo->none_member) ? 'readonly' : ''; ?>/>
                            <?php echo form_error('memberid'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_firstname'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="firstname" value="<?php echo $basicinfo->firstname; ?>" class="form-control"/>
                            <?php echo form_error('firstname'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_middlename'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="middlename" value="<?php echo $basicinfo->middlename; ?>" class="form-control"/>
                            <?php echo form_error('middlename'); ?>
                        </div>
                    </div>
                    <div id="maiden-group" class="form-group" style="<?php echo ($isFemale ? '' : 'display:none;'); ?>">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_maidenname'); ?> :</label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="maidenname" value="<?php echo isset($basicinfo->maidenname) ? $basicinfo->maidenname : set_value('maidenname'); ?>" class="form-control"/>
                            <?php echo form_error('maidenname'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_lastname'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="lastname" value="<?php echo $basicinfo->lastname; ?>" class="form-control"/>
                            <?php echo form_error('lastname'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('member_gender'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <select id="gender" name="gender" class="form-control">
                                <option value=""> <?php echo lang('select_default_text'); ?></option>
                                <?php
                                $selected = $basicinfo->gender;
                                foreach ($gender_options as $key => $value) {
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
                                $selected = $basicinfo->maritalstatus;
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
                                <input type="text" name="dob" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo format_date($basicinfo->dob, false); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
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
                            <input type="text" name="placeofbirth" value="<?php echo set_value('placeofbirth', isset($basicinfo->placeofbirth) ? $basicinfo->placeofbirth : ''); ?>" class="form-control"/>
                            <?php echo form_error('placeofbirth'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo $join_date_label; ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <div class="input-group date" id="datetimepicker2">
                                <input type="text" name="joindate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo format_date($basicinfo->joiningdate, false); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
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
                                <i class="fa fa-save"></i> <?php echo lang('member_edit_btn'); ?>
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

                    if (document.readyState === 'complete') {
                        initDatePickers();
                        initGenderToggle();
                    } else {
                        window.addEventListener('load', function() {
                            initDatePickers();
                            initGenderToggle();
                        });
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
