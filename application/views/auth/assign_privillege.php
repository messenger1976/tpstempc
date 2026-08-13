<style type="text/css">
.assign-role-page { margin-top: 4px; }
.assign-role-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.assign-role-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.assign-role-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.assign-role-page .cbu-alert.info {
    background: #eef3fb;
    color: #3c6eae;
    border: 1px solid #d6e2f5;
}
.assign-role-page .group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    padding: 16px 20px;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.assign-role-page .group-header .head-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.assign-role-page .group-header .icon-badge {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.assign-role-page .group-header h3 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
}
.assign-role-page .group-header p {
    margin: 3px 0 0;
    font-size: 13px;
    color: #888;
}
.assign-role-page .group-header .btn {
    border-radius: 6px;
    font-weight: 600;
}
.assign-role-page .module-card {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.assign-role-page .module-card .card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #2f4050;
}
.assign-role-page .module-card .card-header i {
    color: #1ab394;
}
.assign-role-page .privilege-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
}
.assign-role-page .privilege-item {
    padding: 11px 14px;
    border-bottom: 1px solid #f0f2f3;
    border-right: 1px solid #f0f2f3;
    display: flex;
    align-items: center;
    min-width: 0;
    transition: background .15s ease;
}
.assign-role-page .privilege-item:nth-child(4n) { border-right: none; }
.assign-role-page .privilege-item:hover { background: #e8f8f5; }
.assign-role-page .privilege-label {
    flex: 1;
    margin: 0;
    font-weight: 500;
    color: #2f4050;
    cursor: pointer;
    padding-left: 10px;
    word-break: break-word;
    line-height: 1.3;
    font-size: 13px;
}
.assign-role-page .custom-checkbox-wrapper {
    position: relative;
    display: inline-block;
    margin-right: 4px;
    flex-shrink: 0;
}
.assign-role-page .custom-checkbox-wrapper input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 20px;
    width: 20px;
    margin: 0;
    z-index: 1;
}
.assign-role-page .custom-checkbox {
    height: 20px;
    width: 20px;
    background-color: #fff;
    border: 2px solid #dfe3e6;
    border-radius: 4px;
    display: inline-block;
    position: relative;
    cursor: pointer;
}
.assign-role-page .custom-checkbox-wrapper:hover .custom-checkbox,
.assign-role-page .privilege-item:hover .custom-checkbox {
    border-color: #1ab394;
    box-shadow: 0 0 0 3px rgba(26,179,148,0.12);
}
.assign-role-page .custom-checkbox-wrapper input[type="checkbox"]:checked + .custom-checkbox {
    background-color: #1ab394;
    border-color: #1ab394;
}
.assign-role-page .custom-checkbox:after {
    content: "";
    position: absolute;
    display: none;
    left: 6px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
.assign-role-page .custom-checkbox-wrapper input[type="checkbox"]:checked + .custom-checkbox:after {
    display: block;
}
.assign-role-page .action-buttons {
    margin-top: 8px;
    padding: 16px 18px;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.assign-role-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
    border-radius: 6px;
    font-weight: 600;
    padding: 10px 22px;
}
.assign-role-page .btn-primary:hover {
    background: #18a689;
    border-color: #18a689;
}
.assign-role-page .btn-default {
    border-radius: 6px;
    font-weight: 600;
    padding: 10px 18px;
}
.assign-role-page .no-privileges {
    text-align: center;
    padding: 40px 20px;
    color: #888;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
}
@media (max-width: 1199px) {
    .assign-role-page .privilege-grid { grid-template-columns: repeat(3, 1fr); }
    .assign-role-page .privilege-item:nth-child(4n) { border-right: 1px solid #f0f2f3; }
    .assign-role-page .privilege-item:nth-child(3n) { border-right: none; }
}
@media (max-width: 991px) {
    .assign-role-page .privilege-grid { grid-template-columns: repeat(2, 1fr); }
    .assign-role-page .privilege-item:nth-child(3n) { border-right: 1px solid #f0f2f3; }
    .assign-role-page .privilege-item:nth-child(2n) { border-right: none; }
}
@media (max-width: 575px) {
    .assign-role-page .privilege-grid { grid-template-columns: 1fr; }
    .assign-role-page .privilege-item,
    .assign-role-page .privilege-item:nth-child(2n),
    .assign-role-page .privilege-item:nth-child(3n),
    .assign-role-page .privilege-item:nth-child(4n) {
        border-right: none;
    }
}
</style>

<?php
$group_name = isset($group_info->name) ? $group_info->name : '';
$group_desc = isset($group_info->description) ? $group_info->description : '';
$grouplist_url = site_url(current_lang() . '/auth/grouplist');
$privilege_list = isset($privilege_list) ? $privilege_list : array(array(), array());
?>

<?php echo form_open(current_url(), 'method="post" id="privilegeForm"'); ?>
<div class="col-lg-12 assign-role-page">

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

    <div class="group-header">
        <div class="head-left">
            <i class="fa fa-shield icon-badge"></i>
            <div>
                <h3><?php echo lang('edit_group_name_label'); ?>: <?php echo htmlspecialchars($group_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                <?php if ($group_desc !== '') { ?>
                    <p><i class="fa fa-info-circle"></i> <?php echo htmlspecialchars($group_desc, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php } else { ?>
                    <p>Assign module privileges for this user group.</p>
                <?php } ?>
            </div>
        </div>
        <a href="<?php echo $grouplist_url; ?>" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Back to Group List
        </a>
    </div>

    <?php if (!empty($privilege_list[0])) { ?>
        <?php foreach ($privilege_list[0] as $key => $value) {
            if (empty($value)) {
                continue;
            }
            ?>
            <div class="module-card">
                <div class="card-header">
                    <i class="fa fa-folder-open"></i>
                    <?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="privilege-grid">
                    <?php foreach ($value as $k => $v) {
                        $module_id = $privilege_list[1][$key][$k];
                        $field = 'module_' . $module_id[0] . '_' . $module_id[1];
                        ?>
                        <div class="privilege-item">
                            <div class="custom-checkbox-wrapper">
                                <input type="checkbox"
                                       name="<?php echo htmlspecialchars($field, ENT_QUOTES, 'UTF-8'); ?>"
                                       id="<?php echo htmlspecialchars($field, ENT_QUOTES, 'UTF-8'); ?>"
                                       value="1"
                                       <?php echo ($v == 1 ? 'checked="checked"' : ''); ?>
                                       class="privilege-checkbox">
                                <label class="custom-checkbox" for="<?php echo htmlspecialchars($field, ENT_QUOTES, 'UTF-8'); ?>"></label>
                            </div>
                            <label class="privilege-label" for="<?php echo htmlspecialchars($field, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <div class="action-buttons">
            <input type="hidden" name="save" value="1" />
            <button type="submit" class="btn btn-primary" id="btn-save-privileges">
                <i class="fa fa-save"></i> <?php echo lang('privillege_btn_save'); ?>
            </button>
            <a href="<?php echo $grouplist_url; ?>" class="btn btn-default">
                <i class="fa fa-undo"></i> Cancel
            </a>
        </div>
    <?php } else { ?>
        <div class="no-privileges">
            <i class="fa fa-inbox fa-3x" style="margin-bottom: 12px; opacity: 0.35;"></i>
            <p>No privileges available to assign.</p>
        </div>
    <?php } ?>
</div>
<?php echo form_close(); ?>

<script>
(function() {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }

        jQuery(function($) {
            var form = $('#privilegeForm');
            if (!form.length) {
                form = $('.assign-role-page').closest('form');
            }

            function syncHiddenForUnchecked($checkbox) {
                var name = $checkbox.attr('name');
                $('input[type="hidden"][name="' + name + '"]').remove();
                if (!$checkbox.is(':checked')) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: name,
                        value: '0'
                    }).insertAfter($checkbox);
                }
            }

            $('.privilege-checkbox').each(function() {
                var $checkbox = $(this);
                if (!$checkbox.attr('value')) {
                    $checkbox.attr('value', '1');
                }
                $checkbox.prop('disabled', false);
                syncHiddenForUnchecked($checkbox);
            });

            $('.privilege-checkbox').on('change', function() {
                var $checkbox = $(this);
                $checkbox.prop('disabled', false);
                syncHiddenForUnchecked($checkbox);
            });

            form.on('submit', function() {
                var btn = $('#btn-save-privileges');
                btn.prop('disabled', true);
                btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                var savingMsg = $('<div class="cbu-alert info"><i class="fa fa-spinner fa-spin"></i> Saving permissions, please wait...</div>');
                $('.assign-role-page').prepend(savingMsg);

                $('.privilege-checkbox').each(function() {
                    var $checkbox = $(this);
                    var name = $checkbox.attr('name');
                    if (!name) {
                        return;
                    }
                    if (!$checkbox.is(':checked')) {
                        $checkbox.data('was-enabled', true);
                        $checkbox.prop('disabled', true);
                        if (!$('input[type="hidden"][name="' + name + '"]').length) {
                            $('<input>').attr({
                                type: 'hidden',
                                name: name,
                                value: '0'
                            }).insertAfter($checkbox);
                        }
                    } else {
                        $checkbox.prop('disabled', false);
                        $('input[type="hidden"][name="' + name + '"]').remove();
                    }
                });

                setTimeout(function() {
                    $('.privilege-checkbox').each(function() {
                        if ($(this).data('was-enabled')) {
                            $(this).prop('disabled', false);
                            $(this).removeData('was-enabled');
                        }
                    });
                }, 100);

                return true;
            });
        });
    }
    initScripts();
})();
</script>
