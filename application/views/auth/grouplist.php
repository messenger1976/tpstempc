<?php $this->load->view('loan/list_page_styles'); ?>

<?php
$grouplist = isset($grouplist) ? $grouplist : array();
$total_rows = count($grouplist);
$user = function_exists('current_user') ? current_user() : null;
$is_admin = ($user && isset($user->is_client_admin) && (int) $user->is_client_admin === 1);
$create_url = site_url(current_lang() . '/auth/create_group');
?>

<style type="text/css">
.member-list-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.member-list-page .group-name {
    font-weight: 700;
    color: #2f4050;
}
.member-list-page .group-desc {
    color: #676a6c;
}
</style>

<div class="col-lg-12 member-list-page">
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

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-users icon-badge"></i>
                <h4><?php echo lang('view_group_list'); ?></h4>
            </div>
            <div class="head-actions">
                <div class="result-meta">
                    Showing <strong><?php echo number_format($total_rows); ?></strong> group<?php echo $total_rows === 1 ? '' : 's'; ?>
                </div>
                <?php if ($is_admin) { ?>
                    <a href="<?php echo $create_url; ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> <?php echo lang('create_group_title'); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table">
                <thead>
                    <tr>
                        <th style="width:70px;"><?php echo lang('sno'); ?></th>
                        <th><?php echo lang('create_group_name_label'); ?></th>
                        <th><?php echo lang('create_group_desc_label'); ?></th>
                        <th style="width:220px;"><?php echo lang('actioncolumn'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grouplist)) {
                        $i = 1;
                        foreach ($grouplist as $value) { ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo $i++; ?></span></td>
                                <td class="group-name"><?php echo htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="group-desc"><?php echo htmlspecialchars($value->description, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if ($is_admin) { ?>
                                        <div class="action-btns">
                                            <a href="<?php echo site_url(current_lang() . '/auth/edit_group/' . encode_id($value->id)); ?>"
                                               class="btn btn-warning btn-xs"
                                               title="<?php echo htmlspecialchars(lang('button_edit'), ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                                            </a>
                                            <a href="<?php echo site_url(current_lang() . '/auth/grouprole/' . encode_id($value->id)); ?>"
                                               class="btn btn-primary btn-xs"
                                               title="<?php echo htmlspecialchars(lang('openrole_page_link'), ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa fa-lock"></i> <?php echo lang('openrole_page_link'); ?>
                                            </a>
                                        </div>
                                    <?php } else { ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding: 28px;">
                                <?php echo lang('no_records_found'); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
