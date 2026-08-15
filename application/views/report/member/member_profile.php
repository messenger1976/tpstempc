<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php
$member_id = isset($member_id) ? $member_id : '';
$memberlist = isset($memberlist) ? $memberlist : array();
$is_member_portal = $this->ion_auth->in_group('Members');
?>

<style type="text/css">
.select2-container{width:100%!important;}
.mp-page { margin-top: 0; }
.mp-page .cbu-alert {
    display: block;
    margin: 0 0 14px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.mp-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.mp-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}

/* Compact finder toolbar */
.mp-page .mp-finder {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px 14px;
    margin-bottom: 16px;
    padding: 12px 16px;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.mp-page .mp-finder .finder-label {
    flex: 0 0 auto;
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: #676a6c;
    white-space: nowrap;
}
.mp-page .mp-finder .finder-label .required { color: #ed5565; }
.mp-page .mp-finder .finder-select {
    flex: 1 1 220px;
    min-width: 180px;
    max-width: 520px;
}
.mp-page .mp-finder .btn-load {
    flex: 0 0 auto;
    height: 36px;
    padding: 0 16px;
    border-radius: 6px;
    font-weight: 600;
    background: #1ab394;
    border-color: #1ab394;
    white-space: nowrap;
}
.mp-page .mp-finder .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.mp-page .mp-finder .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.mp-page .mp-finder .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.mp-page .mp-finder .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.mp-page .mp-finder .select2-container--default.select2-container--focus .select2-selection--single,
.mp-page .mp-finder .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}
.mp-page .mp-finder .finder-error {
    flex: 1 1 100%;
    margin: 0;
    color: #ed5565;
    font-size: 12px;
}

/* Shared panels (used by content view) */
.mp-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.mp-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.mp-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.mp-page .cbu-panel .panel-head i.icon-badge {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}
.mp-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #2f4050;
}
.mp-page .cbu-panel .panel-body { padding: 16px; }

.mp-page .hint-empty {
    text-align: center;
    padding: 48px 20px;
    color: #888;
    background: #fff;
    border: 1px dashed #dfe4e8;
    border-radius: 10px;
}
.mp-page .hint-empty i {
    display: block;
    margin-bottom: 12px;
    opacity: 0.35;
}
.mp-page .hint-empty p {
    margin: 0;
    font-size: 14px;
}

/* Soften nested ibox chrome */
#ibox-main > .ibox-content { padding-top: 14px; }
</style>

<div class="col-lg-12 mp-page">
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

    <?php if (!$is_member_portal) { ?>
        <?php echo form_open_multipart(current_lang() . '/report_member/member_profile', 'class="mp-finder-form"'); ?>
            <div class="mp-finder">
                <label class="finder-label" for="member_id">
                    <?php echo lang('member_select_member'); ?> <span class="required">*</span>
                </label>
                <div class="finder-select">
                    <select name="member_id" class="form-control" id="member_id">
                        <option value=""><?php echo lang('select_default_text'); ?></option>
                        <?php foreach ($memberlist as $value) {
                            $label = $value->member_id . ' - ' . trim($value->firstname . ' ' . $value->middlename . ' ' . $value->lastname);
                            ?>
                            <option value="<?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo ((string) $member_id === (string) $value->member_id) ? 'selected="selected"' : ''; ?>>
                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" name="Load" value="Load" class="btn btn-primary btn-load">
                    <i class="fa fa-user"></i> Load
                </button>
                <?php
                $member_err = form_error('member_id');
                if ($member_err) {
                    echo '<div class="finder-error">' . $member_err . '</div>';
                }
                ?>
            </div>
        <?php echo form_close(); ?>
    <?php } ?>

    <?php if ($member_id != '') {
        $this->data['memberinfo'] = $memberinfo;
        $this->data['contactinfo'] = $contactinfo;
        $this->data['nextkininfo'] = $nextkininfo;
        $this->data['member_id'] = $member_id;
        $this->data['is_member_portal'] = $is_member_portal;
        $this->load->view('report/member/member_profile_content', $this->data);
    } else if (!$is_member_portal) { ?>
        <div class="hint-empty">
            <i class="fa fa-id-card-o fa-3x"></i>
            <p>Select a member and click Load to view the profile.</p>
        </div>
    <?php } ?>
</div>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
(function(){
    function boot(){
        if (!window.jQuery || !jQuery.fn.select2) {
            setTimeout(boot, 50);
            return;
        }
        var $ = window.jQuery;
        if ($('#member_id').length) {
            $('#member_id').select2({
                width: '100%',
                placeholder: <?php echo json_encode(lang('select_default_text')); ?>,
                allowClear: true
            });
        }
    }
    boot();
})();
</script>
