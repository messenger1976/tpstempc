<?php
$account = isset($account) ? $account : null;
$edit_url = $account ? site_url(current_lang() . '/setting/companyinfo_edit/' . encode_id($account->id)) : '#';
$logo_file = ($account && !empty($account->logo)) ? $account->logo : '';
$logo_url = $logo_file !== '' ? base_url('logo/' . $logo_file) : '';

$safe = function ($v) {
    if ($v === null || $v === '') {
        return '&mdash;';
    }
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};
?>

<style type="text/css">
.ci-view-page { margin-top: 0; }
.ci-view-page .cbu-alert {
    display: block;
    margin: 0 0 14px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.ci-view-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.ci-view-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}

.ci-view-page .ci-hero {
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
.ci-view-page .ci-hero-main {
    display: flex;
    align-items: center;
    gap: 18px;
    min-width: 0;
    flex: 1 1 auto;
}
.ci-view-page .ci-hero-logo {
    width: 96px;
    height: 96px;
    border-radius: 16px;
    padding: 8px;
    background: rgba(255,255,255,0.95);
    border: 2px solid rgba(255,255,255,0.55);
    box-shadow: 0 4px 14px rgba(0,0,0,0.18);
    overflow: hidden;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ci-view-page .ci-hero-logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
}
.ci-view-page .ci-hero-logo .logo-fallback {
    color: #1ab394;
    font-size: 32px;
}
.ci-view-page .ci-hero-text { min-width: 0; }
.ci-view-page .ci-hero-text h3 {
    margin: 0 0 6px;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    line-height: 1.25;
    word-wrap: break-word;
}
.ci-view-page .ci-hero-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.ci-view-page .ci-chip {
    display: inline-block;
    padding: 4px 11px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.28);
    color: #fff;
}
.ci-view-page .ci-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    flex-shrink: 0;
}
.ci-view-page .ci-hero-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 7px 14px;
}
.ci-view-page .ci-hero-actions .btn-edit {
    background: #fff;
    border-color: #fff;
    color: #147a6a;
}
.ci-view-page .ci-hero-actions .btn-edit:hover,
.ci-view-page .ci-hero-actions .btn-edit:focus {
    background: #f4fffc;
    border-color: #f4fffc;
    color: #0e7c69;
}

.ci-view-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.ci-view-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.ci-view-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ci-view-page .cbu-panel .panel-head i.icon-badge {
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
.ci-view-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #2f4050;
}
.ci-view-page .cbu-panel .panel-body { padding: 12px 16px 16px; }

.ci-view-page .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 28px;
}
.ci-view-page .info-item {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 0;
    border-bottom: 1px solid #eef1f2;
    background: transparent;
}
.ci-view-page .info-item.full { grid-column: 1 / -1; }
.ci-view-page .info-item .field-label {
    flex: 0 1 42%;
    margin: 0;
    font-size: 12px;
    font-weight: 600;
    color: #888;
    line-height: 1.4;
}
.ci-view-page .info-item .value {
    flex: 1 1 auto;
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: #2f4050;
    text-align: right;
    word-break: break-word;
    line-height: 1.4;
}

.ci-view-page .empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #888;
    background: #fff;
    border: 1px dashed #dfe4e8;
    border-radius: 10px;
}
.ci-view-page .empty-state i {
    display: block;
    margin-bottom: 12px;
    opacity: 0.35;
}
.ci-view-page .empty-state p { margin: 0; font-size: 14px; }

#ibox-main > .ibox-content { padding-top: 14px; }

@media (max-width: 991px) {
    .ci-view-page .ci-hero {
        flex-direction: column;
        align-items: flex-start;
    }
    .ci-view-page .ci-hero-actions { width: 100%; }
}
@media (max-width: 767px) {
    .ci-view-page .ci-hero-main { flex-direction: column; align-items: flex-start; }
    .ci-view-page .ci-hero-logo { width: 84px; height: 84px; }
    .ci-view-page .ci-hero-text h3 { font-size: 18px; }
    .ci-view-page .info-grid { grid-template-columns: 1fr; }
}
</style>

<div class="col-lg-12 ci-view-page">
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

    <?php if ($account) { ?>
        <div class="ci-hero">
            <div class="ci-hero-main">
                <div class="ci-hero-logo">
                    <?php if ($logo_url !== '') { ?>
                        <img src="<?php echo htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($account->name, ENT_QUOTES, 'UTF-8'); ?>"/>
                    <?php } else { ?>
                        <i class="fa fa-building logo-fallback"></i>
                    <?php } ?>
                </div>
                <div class="ci-hero-text">
                    <h3><?php echo htmlspecialchars($account->name, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="ci-hero-chips">
                        <?php if (!empty($account->email)) { ?>
                            <span class="ci-chip"><i class="fa fa-envelope-o"></i> <?php echo htmlspecialchars($account->email, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php } ?>
                        <?php if (!empty($account->mobile)) { ?>
                            <span class="ci-chip"><i class="fa fa-phone"></i> <?php echo htmlspecialchars($account->mobile, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="ci-hero-actions">
                <a href="<?php echo $edit_url; ?>" class="btn btn-edit">
                    <i class="fa fa-pencil"></i> <?php echo lang('clientaccount_label_btnedit'); ?>
                </a>
            </div>
        </div>

        <div class="cbu-panel">
            <div class="panel-head">
                <div class="head-left">
                    <i class="fa fa-building icon-badge"></i>
                    <h4><?php echo lang('seting_accountinfo'); ?></h4>
                </div>
            </div>
            <div class="panel-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="field-label"><?php echo lang('clientaccount_label_email'); ?></span>
                        <div class="value"><?php echo $safe($account->email); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="field-label"><?php echo lang('clientaccount_label_phone'); ?></span>
                        <div class="value"><?php echo $safe($account->mobile); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="field-label"><?php echo lang('clientaccount_label_fax'); ?></span>
                        <div class="value"><?php echo $safe($account->fax); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="field-label"><?php echo lang('clientaccount_label_postal_address'); ?></span>
                        <div class="value"><?php echo $safe($account->box); ?></div>
                    </div>
                    <div class="info-item full">
                        <span class="field-label"><?php echo lang('clientaccount_label_physical_address'); ?></span>
                        <div class="value"><?php echo $safe($account->address); ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="empty-state">
            <i class="fa fa-inbox fa-3x"></i>
            <p>No company information found.</p>
        </div>
    <?php } ?>
</div>
