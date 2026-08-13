<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">

<?php
$member_id = isset($member_id) ? $member_id : '';
$memberlist = isset($memberlist) ? $memberlist : array();
$is_member_portal = $this->ion_auth->in_group('Members');
?>

<style type="text/css">
.select2-container{width:100%!important;}
.mp-page { margin-top: 4px; }
.mp-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
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
.mp-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.mp-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.mp-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.mp-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.mp-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.mp-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.mp-page .form-horizontal .form-group { margin-bottom: 0; }
.mp-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.mp-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.mp-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.mp-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.mp-page .member-load-row {
    display: flex;
    align-items: stretch;
    gap: 8px;
}
.mp-page .member-load-row .select-wrap {
    flex: 1 1 auto;
    min-width: 0;
}
.mp-page .member-load-row .btn-load {
    flex: 0 0 auto;
    height: 36px;
    white-space: nowrap;
}
.mp-page .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.mp-page .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.mp-page .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.mp-page .select2-container--default.select2-container--focus .select2-selection--single,
.mp-page .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}
.mp-page .hint-empty {
    text-align: center;
    padding: 36px 16px;
    color: #888;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
}
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
        <div class="cbu-panel">
            <div class="panel-head">
                <div class="head-left">
                    <i class="fa fa-search icon-badge"></i>
                    <h4><?php echo lang('member_profile'); ?></h4>
                </div>
            </div>
            <div class="panel-body">
                <?php echo form_open_multipart(current_lang() . '/report_member/member_profile', 'class="form-horizontal"'); ?>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?php echo lang('member_select_member'); ?> : <span class="required" style="color:#ed5565;">*</span></label>
                        <div class="col-lg-8">
                            <div class="member-load-row">
                                <div class="select-wrap">
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
                            </div>
                            <?php echo form_error('member_id'); ?>
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($member_id != '') {
        $this->data['memberinfo'] = $memberinfo;
        $this->data['contactinfo'] = $contactinfo;
        $this->data['nextkininfo'] = $nextkininfo;
        $this->data['member_id'] = $member_id;
        $this->load->view('report/member/member_profile_content', $this->data);
    } else if (!$is_member_portal) { ?>
        <div class="hint-empty">
            <i class="fa fa-id-card-o fa-3x" style="margin-bottom: 12px; opacity: 0.35;"></i>
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
