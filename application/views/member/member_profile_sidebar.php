<?php
$photo_file = !empty($basicinfo->photo) ? $basicinfo->photo : '';
$photo_url = member_avatar_url($photo_file, isset($basicinfo->gender) ? $basicinfo->gender : '');
$full_name = trim($basicinfo->firstname . ' ' . $basicinfo->middlename . ' ' . $basicinfo->lastname);
$gender_options = lang('member_genderoption');
$gender_label = isset($gender_options[$basicinfo->gender]) ? $gender_options[$basicinfo->gender] : $basicinfo->gender;
$member_id_label = (!empty($basicinfo->none_member)) ? lang('member_none_member_id') : lang('member_member_id');
$join_date_label = (!empty($basicinfo->none_member)) ? lang('member_reg_date') : lang('member_join_date');
?>
<aside class="member-profile-card">
    <div class="member-photo-wrap">
        <img src="<?php echo htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($full_name); ?>" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars(member_avatar_url('', isset($basicinfo->gender) ? $basicinfo->gender : ''), ENT_QUOTES, 'UTF-8'); ?>';"/>
    </div>
    <h3 class="member-name"><?php echo htmlspecialchars($full_name); ?></h3>
    <div class="member-meta"><?php echo htmlspecialchars($member_id_label); ?></div>
    <div class="member-badges">
        <span class="member-badge"><?php echo htmlspecialchars($basicinfo->member_id); ?></span>
        <?php if (!empty($gender_label)) { ?>
            <span class="member-badge"><?php echo htmlspecialchars($gender_label); ?></span>
        <?php } ?>
    </div>
    <ul class="member-detail-list">
        <li>
            <span class="lbl"><?php echo lang('member_pid'); ?></span>
            <span class="val"><?php echo htmlspecialchars($basicinfo->PID); ?></span>
        </li>
        <li>
            <span class="lbl"><?php echo $join_date_label; ?></span>
            <span class="val"><?php echo format_date($basicinfo->joiningdate, false); ?></span>
        </li>
    </ul>
</aside>
