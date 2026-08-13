<?php
$memberinfo = isset($memberinfo) ? $memberinfo : null;
$contactinfo = isset($contactinfo) ? $contactinfo : null;
$nextkininfo = isset($nextkininfo) ? $nextkininfo : null;
$member_id = isset($member_id) ? $member_id : '';

if (!$memberinfo) {
    return;
}

$full_name = trim($memberinfo->firstname . ' ' . $memberinfo->middlename . ' ' . $memberinfo->lastname);
$photo_url = function_exists('member_avatar_url')
    ? member_avatar_url(isset($memberinfo->photo) ? $memberinfo->photo : '', isset($memberinfo->gender) ? $memberinfo->gender : '')
    : base_url('uploads/memberphoto/' . (isset($memberinfo->photo) ? $memberinfo->photo : ''));
$print_url = site_url(current_lang() . '/report_member/member_profile_print/?member_id=' . rawurlencode($member_id));

$safe = function ($v) {
    if ($v === null || $v === '') {
        return '&mdash;';
    }
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};
?>

<style type="text/css">
.mp-content .profile-hero {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f2f3;
}
.mp-content .profile-photo {
    width: 110px;
    height: 110px;
    border-radius: 12px;
    border: 1px solid #e7eaec;
    background: #e8f8f5;
    overflow: hidden;
    flex-shrink: 0;
}
.mp-content .profile-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.mp-content .profile-hero h3 {
    margin: 0 0 4px;
    font-size: 18px;
    font-weight: 700;
    color: #2f4050;
}
.mp-content .profile-hero .meta {
    margin: 0;
    color: #888;
    font-size: 13px;
}
.mp-content .profile-hero .id-chip {
    display: inline-block;
    margin-top: 8px;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    background: #e8f8f5;
    color: #1ab394;
}
.mp-content .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}
.mp-content .info-item {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 12px 14px;
}
.mp-content .info-item .label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #888;
    margin-bottom: 4px;
}
.mp-content .info-item .value {
    font-size: 14px;
    font-weight: 600;
    color: #2f4050;
    word-break: break-word;
}
.mp-content .panel-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid #f0f2f3;
}
.mp-content .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
    border-radius: 6px;
    font-weight: 600;
}
@media (max-width: 767px) {
    .mp-content .info-grid { grid-template-columns: 1fr; }
    .mp-content .profile-hero { flex-direction: column; align-items: flex-start; }
}
</style>

<div class="mp-content">
    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-user icon-badge"></i>
                <h4><?php echo lang('member_basic_info'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="profile-hero">
                <div class="profile-photo">
                    <img src="<?php echo htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>"/>
                </div>
                <div>
                    <h3><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="meta"><?php echo lang('member_profile'); ?></p>
                    <span class="id-chip"><?php echo htmlspecialchars($memberinfo->member_id, ENT_QUOTES, 'UTF-8'); ?> · PID <?php echo htmlspecialchars($memberinfo->PID, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="label"><?php echo lang('member_firstname'); ?></span>
                    <div class="value"><?php echo $safe($memberinfo->firstname); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_middlename'); ?></span>
                    <div class="value"><?php echo $safe($memberinfo->middlename); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_lastname'); ?></span>
                    <div class="value"><?php echo $safe($memberinfo->lastname); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_gender'); ?></span>
                    <div class="value"><?php echo $safe($memberinfo->gender); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_maritalstatus'); ?></span>
                    <div class="value"><?php echo $safe($memberinfo->maritalstatus); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_dob'); ?></span>
                    <div class="value"><?php echo $safe(format_date($memberinfo->dob, false)); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_join_date'); ?></span>
                    <div class="value"><?php echo $safe(format_date($memberinfo->joiningdate, false)); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_pid'); ?></span>
                    <div class="value"><?php echo $safe($memberinfo->PID); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-phone icon-badge"></i>
                <h4><?php echo lang('member_contact_info'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="label"><?php echo lang('member_contact_phone1'); ?></span>
                    <div class="value"><?php echo $safe($contactinfo ? $contactinfo->phone1 : ''); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_contact_phone2'); ?></span>
                    <div class="value"><?php echo $safe($contactinfo ? $contactinfo->phone2 : ''); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_contact_email'); ?></span>
                    <div class="value"><?php echo $safe($contactinfo ? $contactinfo->email : ''); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_contact_box'); ?></span>
                    <div class="value"><?php echo $safe($contactinfo ? $contactinfo->postaladdress : ''); ?></div>
                </div>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <span class="label"><?php echo lang('member_contact_physical'); ?></span>
                    <div class="value"><?php echo $safe($contactinfo ? $contactinfo->physicaladdress : ''); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-users icon-badge"></i>
                <h4><?php echo lang('nextkin_title'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="label"><?php echo lang('nextkin_name'); ?></span>
                    <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->name : ''); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('nextkin_relationship'); ?></span>
                    <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->relationship : ''); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_contact_phone1'); ?></span>
                    <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->phone : ''); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_contact_email'); ?></span>
                    <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->email : ''); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_contact_box'); ?></span>
                    <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->postaladdress : ''); ?></div>
                </div>
                <div class="info-item">
                    <span class="label"><?php echo lang('member_contact_physical'); ?></span>
                    <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->physicaladdress : ''); ?></div>
                </div>
            </div>

            <div class="panel-actions">
                <a href="<?php echo $print_url; ?>" class="btn btn-primary" target="_blank">
                    <i class="fa fa-print"></i> Print
                </a>
            </div>
        </div>
    </div>
</div>
