<?php
$current_method = $this->router->fetch_method();
$member_tabs = array(
    'memberinfo' => array(
        'url' => site_url(current_lang() . '/member/memberinfo/' . encode_id($basicinfo->id)),
        'label' => lang('member_basic_info'),
        'icon' => 'fa-user',
    ),
    'membercontact' => array(
        'url' => site_url(current_lang() . '/member/membercontact/' . encode_id($basicinfo->id)),
        'label' => lang('member_contact_info'),
        'icon' => 'fa-phone',
    ),
    'membernextkin' => array(
        'url' => site_url(current_lang() . '/member/membernextkin/' . encode_id($basicinfo->id)),
        'label' => lang('member_nextkin_info'),
        'icon' => 'fa-users',
    ),
    'membergroup' => array(
        'url' => site_url(current_lang() . '/member/membergroup/' . encode_id($basicinfo->id)),
        'label' => lang('member_addgroup'),
        'icon' => 'fa-object-group',
    ),
);
?>
<style>
.member-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 10px 24px;
    padding: 12px;
    background: #f8fafb;
    border: 1px solid #e7eaec;
    border-radius: 8px;
}
.member-tabs .member-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: 6px;
    border: 1px solid transparent;
    background: #fff;
    color: #676a6c;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none !important;
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
    transition: background .15s ease, color .15s ease, border-color .15s ease, box-shadow .15s ease;
}
.member-tabs .member-tab:hover {
    color: #1ab394;
    border-color: #c9ebe3;
    box-shadow: 0 2px 6px rgba(26,179,148,0.12);
}
.member-tabs .member-tab.active {
    background: #1ab394;
    color: #fff !important;
    border-color: #1ab394;
    box-shadow: 0 2px 8px rgba(26,179,148,0.28);
}
.member-tabs .member-tab i { font-size: 14px; }
@media (max-width: 767px) {
    .member-tabs .member-tab {
        flex: 1 1 calc(50% - 8px);
        justify-content: center;
    }
}

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
    width: 148px;
    height: 148px;
    margin: 0 auto 16px;
    border-radius: 50%;
    padding: 4px;
    background: #fff;
    border: 3px solid #1ab394;
    box-shadow: 0 4px 14px rgba(26,179,148,0.18);
    overflow: hidden;
}
.member-info-page .member-photo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 50%;
}
.member-info-page .member-name {
    margin: 0 0 4px;
    font-size: 18px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
    word-wrap: break-word;
}
.member-info-page .member-meta {
    color: #888;
    font-size: 12px;
    margin-bottom: 14px;
}
.member-info-page .member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 16px;
}
.member-info-page .member-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
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
    justify-content: space-between;
    gap: 10px;
    padding: 10px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.member-info-page .member-detail-list li:last-child { border-bottom: 0; }
.member-info-page .member-detail-list .lbl { color: #999; font-weight: 500; }
.member-info-page .member-detail-list .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
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
.member-info-page .phone-code-addon {
    border: 0;
    padding: 0 6px 0 0;
    margin: 0;
    background: transparent;
}
.member-info-page .phone-code-addon select {
    background: #fff;
    padding: 7px 8px;
    border: 1px solid #E5E6E7;
    border-radius: 6px;
    height: 36px;
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
.member-info-page .group-transfer {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    gap: 16px;
    margin-bottom: 8px;
}
.member-info-page .group-column {
    flex: 1 1 240px;
    min-width: 220px;
}
.member-info-page .group-column-title {
    margin: 0 0 8px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #888;
}
.member-info-page #all_users,
.member-info-page #selected_users {
    float: none !important;
    width: 100% !important;
    margin: 0 !important;
    height: 360px;
    border: 1px solid #e7eaec !important;
    border-radius: 8px;
    background: #fafbfc;
    padding: 10px !important;
}
.member-info-page #all_users .innertxt,
.member-info-page #selected_users .innertxt2 {
    background: #fff;
    border: 1px solid #e7eaec !important;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 10px !important;
    border-bottom: 1px solid #e7eaec !important;
}
.member-info-page #all_users .innertxt li:first-child,
.member-info-page #selected_users .innertxt2 li:first-child {
    color: #1ab394 !important;
    font-weight: 700;
    font-size: 13px !important;
}
.member-info-page #all_users .innertxt_bg,
.member-info-page #selected_users .innertxt_bg {
    border-color: #1ab394 !important;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
    background: #e8f8f5;
}
.member-info-page .group-actions {
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 10px;
    min-width: 110px;
    padding-top: 28px;
}
.member-info-page .group-actions a {
    display: inline-block;
    min-width: 100px;
    padding: 8px 12px;
    border-radius: 6px;
    background: #1ab394;
    color: #fff !important;
    font-weight: 600;
    font-size: 12px;
    text-align: center;
    text-decoration: none !important;
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.member-info-page .group-actions a:hover { background: #18a689; }
@media (max-width: 991px) {
    .member-info-page .member-profile-card {
        max-width: 360px;
        margin-left: auto;
        margin-right: auto;
    }
    .member-info-page .group-actions {
        flex-direction: row;
        width: 100%;
        padding-top: 0;
    }
}
</style>
<nav class="member-tabs" aria-label="Member sections">
    <?php foreach ($member_tabs as $method => $tab) { ?>
        <a class="member-tab<?php echo ($current_method === $method) ? ' active' : ''; ?>" href="<?php echo $tab['url']; ?>">
            <i class="fa <?php echo $tab['icon']; ?>"></i>
            <span><?php echo $tab['label']; ?></span>
        </a>
    <?php } ?>
</nav>
