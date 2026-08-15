<?php
$memberinfo = isset($memberinfo) ? $memberinfo : null;
$contactinfo = isset($contactinfo) ? $contactinfo : null;
$nextkininfo = isset($nextkininfo) ? $nextkininfo : null;
$member_id = isset($member_id) ? $member_id : '';
$is_member_portal = isset($is_member_portal) ? $is_member_portal : $this->ion_auth->in_group('Members');

if (!$memberinfo) {
    return;
}

$full_name = trim($memberinfo->firstname . ' ' . $memberinfo->middlename . ' ' . $memberinfo->lastname);
$photo_file = isset($memberinfo->photo) ? $memberinfo->photo : '';
$gender_raw = isset($memberinfo->gender) ? $memberinfo->gender : '';
$fallback_url = function_exists('member_avatar_url') ? member_avatar_url('', $gender_raw) : base_url('uploads/memberphoto/');
$photo_url = function_exists('member_avatar_url')
    ? member_avatar_url($photo_file, $gender_raw)
    : base_url('uploads/memberphoto/' . $photo_file);

$print_url = site_url(current_lang() . '/report_member/member_profile_print/?member_id=' . rawurlencode($member_id));
$edit_url = '';
if (!$is_member_portal && !empty($memberinfo->id) && function_exists('encode_id')) {
    $edit_url = site_url(current_lang() . '/member/memberinfo/' . encode_id($memberinfo->id));
}

$gender_options = lang('member_genderoption');
$gender_label = '';
if (is_array($gender_options) && isset($gender_options[$gender_raw])) {
    $gender_label = $gender_options[$gender_raw];
} else if ($gender_raw !== '') {
    $gender_label = $gender_raw;
}

$marital_label = isset($memberinfo->maritalstatus) ? $memberinfo->maritalstatus : '';
$maidenname = isset($memberinfo->maidenname) ? $memberinfo->maidenname : '';
$join_date_label = (!empty($memberinfo->none_member)) ? lang('member_reg_date') : lang('member_join_date');

$safe = function ($v) {
    if ($v === null || $v === '') {
        return '&mdash;';
    }
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};
?>

<style type="text/css">
/* Identity banner */
.mp-content .mp-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 16px;
    padding: 22px 24px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1ab394 0%, #147a6a 55%, #1f3348 100%);
    color: #fff;
    box-shadow: 0 4px 16px rgba(26, 179, 148, 0.22);
}
.mp-content .mp-hero-main {
    display: flex;
    align-items: center;
    gap: 18px;
    min-width: 0;
    flex: 1 1 auto;
}
.mp-content .mp-hero-photo {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    padding: 3px;
    background: rgba(255,255,255,0.95);
    border: 2px solid rgba(255,255,255,0.55);
    box-shadow: 0 4px 14px rgba(0,0,0,0.18);
    overflow: hidden;
    flex-shrink: 0;
}
.mp-content .mp-hero-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 50%;
    background: #e8f8f5;
}
.mp-content .mp-hero-text { min-width: 0; }
.mp-content .mp-hero-text h3 {
    margin: 0 0 6px;
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    line-height: 1.25;
    word-wrap: break-word;
}
.mp-content .mp-hero-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.mp-content .mp-chip {
    display: inline-block;
    padding: 4px 11px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .02em;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.28);
    color: #fff;
}
.mp-content .mp-chip.soft {
    background: rgba(255,255,255,0.12);
    font-weight: 600;
}
.mp-content .mp-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    flex-shrink: 0;
    justify-content: flex-end;
}
.mp-content .mp-hero-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 7px 14px;
}
.mp-content .mp-hero-actions .btn-print {
    background: #fff;
    border-color: #fff;
    color: #147a6a;
}
.mp-content .mp-hero-actions .btn-print:hover,
.mp-content .mp-hero-actions .btn-print:focus {
    background: #f4fffc;
    border-color: #f4fffc;
    color: #0e7c69;
}
.mp-content .mp-hero-actions .btn-edit {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.65);
    color: #fff;
}
.mp-content .mp-hero-actions .btn-edit:hover,
.mp-content .mp-hero-actions .btn-edit:focus {
    background: rgba(255,255,255,0.12);
    border-color: #fff;
    color: #fff;
}

/* Info grids — 2-col inline rows, no gray tiles */
.mp-content .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 28px;
}
.mp-content .info-grid-basic {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
.mp-content .info-item {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 0;
    border-bottom: 1px solid #eef1f2;
    background: transparent;
    border-radius: 0;
}
.mp-content .info-item.span-2 { grid-column: 1 / -1; }
.mp-content .info-item .field-label {
    flex: 0 1 42%;
    margin: 0;
    font-size: 12px;
    font-weight: 600;
    text-transform: none;
    letter-spacing: 0;
    color: #888;
    line-height: 1.4;
}
.mp-content .info-item .value {
    flex: 1 1 auto;
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: #2f4050;
    text-align: right;
    word-break: break-word;
    line-height: 1.4;
}

.mp-content .mp-detail-row { margin-left: -8px; margin-right: -8px; }
.mp-content .mp-detail-row > [class*="col-"] { padding-left: 8px; padding-right: 8px; }

@media (max-width: 991px) {
    .mp-content .mp-hero {
        flex-direction: column;
        align-items: flex-start;
    }
    .mp-content .mp-hero-actions {
        width: 100%;
        justify-content: flex-start;
    }
}
@media (max-width: 767px) {
    .mp-content .mp-hero-main { flex-direction: column; align-items: flex-start; }
    .mp-content .mp-hero-photo { width: 84px; height: 84px; }
    .mp-content .mp-hero-text h3 { font-size: 18px; }
    .mp-content .info-grid,
    .mp-content .info-grid-basic { grid-template-columns: 1fr; }
}
</style>

<div class="mp-content">
    <div class="mp-hero">
        <div class="mp-hero-main">
            <div class="mp-hero-photo">
                <img
                    src="<?php echo htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>"
                    onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($fallback_url, ENT_QUOTES, 'UTF-8'); ?>';"
                />
            </div>
            <div class="mp-hero-text">
                <h3><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                <div class="mp-hero-chips">
                    <span class="mp-chip"><?php echo htmlspecialchars($memberinfo->member_id, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="mp-chip soft">PID <?php echo htmlspecialchars($memberinfo->PID, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($gender_label !== '') { ?>
                        <span class="mp-chip soft"><?php echo htmlspecialchars($gender_label, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php } ?>
                    <?php if ($marital_label !== '') { ?>
                        <span class="mp-chip soft"><?php echo htmlspecialchars($marital_label, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="mp-hero-actions">
            <a href="<?php echo $print_url; ?>" class="btn btn-print" target="_blank">
                <i class="fa fa-print"></i> Print
            </a>
            <?php if ($edit_url !== '') { ?>
                <a href="<?php echo $edit_url; ?>" class="btn btn-edit">
                    <i class="fa fa-pencil"></i> Edit
                </a>
            <?php } ?>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-user icon-badge"></i>
                <h4><?php echo lang('member_basic_info'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="info-grid info-grid-basic">
                <div class="info-item">
                    <span class="field-label"><?php echo lang('member_gender'); ?></span>
                    <div class="value"><?php echo $safe($gender_label !== '' ? $gender_label : $gender_raw); ?></div>
                </div>
                <div class="info-item">
                    <span class="field-label"><?php echo lang('member_maritalstatus'); ?></span>
                    <div class="value"><?php echo $safe($marital_label); ?></div>
                </div>
                <div class="info-item">
                    <span class="field-label"><?php echo lang('member_dob'); ?></span>
                    <div class="value"><?php echo $safe(format_date($memberinfo->dob, false)); ?></div>
                </div>
                <div class="info-item">
                    <span class="field-label"><?php echo $join_date_label; ?></span>
                    <div class="value"><?php echo $safe(format_date($memberinfo->joiningdate, false)); ?></div>
                </div>
                <div class="info-item">
                    <span class="field-label"><?php echo lang('member_pid'); ?></span>
                    <div class="value"><?php echo $safe($memberinfo->PID); ?></div>
                </div>
                <?php if ($maidenname !== null && $maidenname !== '') { ?>
                    <div class="info-item">
                        <span class="field-label"><?php echo lang('member_maidenname'); ?></span>
                        <div class="value"><?php echo $safe($maidenname); ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="row mp-detail-row">
        <div class="col-md-6">
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
                            <span class="field-label"><?php echo lang('member_contact_phone1'); ?></span>
                            <div class="value"><?php echo $safe($contactinfo ? $contactinfo->phone1 : ''); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_contact_phone2'); ?></span>
                            <div class="value"><?php echo $safe($contactinfo ? $contactinfo->phone2 : ''); ?></div>
                        </div>
                        <div class="info-item span-2">
                            <span class="field-label"><?php echo lang('member_contact_email'); ?></span>
                            <div class="value"><?php echo $safe($contactinfo ? $contactinfo->email : ''); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_contact_box'); ?></span>
                            <div class="value"><?php echo $safe($contactinfo ? $contactinfo->postaladdress : ''); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_contact_physical'); ?></span>
                            <div class="value"><?php echo $safe($contactinfo ? $contactinfo->physicaladdress : ''); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
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
                            <span class="field-label"><?php echo lang('nextkin_name'); ?></span>
                            <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->name : ''); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('nextkin_relationship'); ?></span>
                            <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->relationship : ''); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_contact_phone1'); ?></span>
                            <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->phone : ''); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_contact_email'); ?></span>
                            <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->email : ''); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_contact_box'); ?></span>
                            <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->postaladdress : ''); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_contact_physical'); ?></span>
                            <div class="value"><?php echo $safe($nextkininfo ? $nextkininfo->physicaladdress : ''); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
