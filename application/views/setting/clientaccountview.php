<?php
$account = isset($account) ? $account : null;
$edit_url = $account ? site_url(current_lang() . '/setting/companyinfo_edit/' . encode_id($account->id)) : '#';
$logo_file = ($account && !empty($account->logo)) ? $account->logo : '';
$logo_url = $logo_file !== '' ? base_url('logo/' . $logo_file) : '';
?>

<style type="text/css">
.ci-view-page { margin-top: 4px; }
.ci-view-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
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
.ci-view-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.ci-view-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.ci-view-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ci-view-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ci-view-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.ci-view-page .cbu-panel .panel-body { padding: 20px; }
.ci-view-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.ci-view-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.ci-view-page .brand-block {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 18px;
    border-bottom: 1px solid #f0f2f3;
}
.ci-view-page .brand-logo {
    width: 88px;
    height: 88px;
    border-radius: 12px;
    border: 1px solid #e7eaec;
    background: #fafbfc;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}
.ci-view-page .brand-logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.ci-view-page .brand-logo .logo-fallback {
    color: #1ab394;
    font-size: 28px;
}
.ci-view-page .brand-text h3 {
    margin: 0 0 4px;
    font-size: 18px;
    font-weight: 700;
    color: #2f4050;
}
.ci-view-page .brand-text p {
    margin: 0;
    color: #888;
    font-size: 13px;
}
.ci-view-page .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}
.ci-view-page .info-item {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 12px 14px;
}
.ci-view-page .info-item.full {
    grid-column: 1 / -1;
}
.ci-view-page .info-item .label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #888;
    margin-bottom: 4px;
}
.ci-view-page .info-item .value {
    font-size: 14px;
    font-weight: 600;
    color: #2f4050;
    word-break: break-word;
}
.ci-view-page .empty-state {
    text-align: center;
    padding: 40px 16px;
    color: #888;
}
@media (max-width: 767px) {
    .ci-view-page .info-grid { grid-template-columns: 1fr; }
    .ci-view-page .brand-block { flex-direction: column; align-items: flex-start; }
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

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-building icon-badge"></i>
                <h4><?php echo lang('seting_accountinfo'); ?></h4>
            </div>
            <?php if ($account) { ?>
                <a href="<?php echo $edit_url; ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-edit"></i> <?php echo lang('clientaccount_label_btnedit'); ?>
                </a>
            <?php } ?>
        </div>
        <div class="panel-body">
            <?php if ($account) { ?>
                <div class="brand-block">
                    <div class="brand-logo">
                        <?php if ($logo_url !== '') { ?>
                            <img src="<?php echo htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($account->name, ENT_QUOTES, 'UTF-8'); ?>"/>
                        <?php } else { ?>
                            <i class="fa fa-building logo-fallback"></i>
                        <?php } ?>
                    </div>
                    <div class="brand-text">
                        <h3><?php echo htmlspecialchars($account->name, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo lang('seting_accountinfo'); ?></p>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="label"><?php echo lang('clientaccount_label_name'); ?></span>
                        <div class="value"><?php echo htmlspecialchars($account->name, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label"><?php echo lang('clientaccount_label_email'); ?></span>
                        <div class="value"><?php echo htmlspecialchars($account->email, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label"><?php echo lang('clientaccount_label_phone'); ?></span>
                        <div class="value"><?php echo htmlspecialchars($account->mobile, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label"><?php echo lang('clientaccount_label_fax'); ?></span>
                        <div class="value"><?php echo ($account->fax !== '' && $account->fax !== null) ? htmlspecialchars($account->fax, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label"><?php echo lang('clientaccount_label_postal_address'); ?></span>
                        <div class="value"><?php echo ($account->box !== '' && $account->box !== null) ? htmlspecialchars($account->box, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></div>
                    </div>
                    <div class="info-item full">
                        <span class="label"><?php echo lang('clientaccount_label_physical_address'); ?></span>
                        <div class="value"><?php echo htmlspecialchars($account->address, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="empty-state">
                    <i class="fa fa-inbox fa-3x" style="margin-bottom: 12px; opacity: 0.35;"></i>
                    <p>No company information found.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
