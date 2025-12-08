<style>
    .assign-role-container {
        padding: 20px 0;
    }
    .group-header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .group-header-card h3 {
        margin: 0;
        font-weight: 600;
        font-size: 24px;
    }
    .group-header-card .group-description {
        margin-top: 8px;
        opacity: 0.9;
        font-size: 14px;
    }
    .module-card {
        border: none;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .module-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    }
    .module-card .card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 15px 20px;
        font-weight: 600;
        text-transform: uppercase;
        color: #495057;
        font-size: 14px;
        letter-spacing: 0.5px;
    }
    .privilege-item {
        padding: 12px 20px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        transition: background-color 0.2s;
    }
    .privilege-item:last-child {
        border-bottom: none;
    }
    .privilege-item:hover {
        background-color: #f8f9fa;
    }
    .privilege-label {
        flex: 1;
        margin: 0;
        font-weight: 500;
        color: #495057;
        cursor: pointer;
        padding-left: 10px;
    }
    .custom-checkbox-wrapper {
        position: relative;
        display: inline-block;
        margin-right: 12px;
    }
    .custom-checkbox-wrapper input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 22px;
        width: 22px;
        margin: 0;
        z-index: 1;
    }
    .custom-checkbox {
        height: 22px;
        width: 22px;
        background-color: #fff;
        border: 2px solid #dee2e6;
        border-radius: 4px;
        display: inline-block;
        position: relative;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .custom-checkbox-wrapper:hover .custom-checkbox,
    .privilege-item:hover .custom-checkbox {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    .custom-checkbox-wrapper input[type="checkbox"]:checked + .custom-checkbox {
        background-color: #28a745;
        border-color: #28a745;
    }
    .custom-checkbox:after {
        content: "";
        position: absolute;
        display: none;
        left: 7px;
        top: 3px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }
    .custom-checkbox-wrapper input[type="checkbox"]:checked + .custom-checkbox:after {
        display: block;
    }
    .action-buttons {
        margin-top: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        text-align: center;
    }
    .btn-save {
        padding: 12px 40px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    .alert-modern {
        border-radius: 8px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .no-privileges {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    .module-icon {
        margin-right: 8px;
        color: #667eea;
    }
    .group-header-card .module-icon {
        color: rgba(255, 255, 255, 0.9);
    }
</style>

<?php echo form_open(current_url(), 'method="post"'); ?>
<div class="assign-role-container">

<?php
// Modern alert messages
if (isset($message) && !empty($message)) {
    echo '<div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">';
    echo '<i class="fa fa-check-circle"></i> ' . $message;
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    echo '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">';
    echo '<i class="fa fa-check-circle"></i> ' . $this->session->flashdata('message');
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    echo '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">';
    echo '<i class="fa fa-exclamation-circle"></i> ' . $warning;
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    echo '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">';
    echo '<i class="fa fa-exclamation-circle"></i> ' . $this->session->flashdata('warning');
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    echo '</div>';
}
?>

<!-- Group Header Card -->
<div class="group-header-card">
    <div style="display: flex; align-items: center;">
        <i class="fa fa-users fa-2x" style="margin-right: 15px;"></i>
        <div>
            <h3>
                <i class="fa fa-shield-alt module-icon"></i>
                <?php echo lang('edit_group_name_label') ?>: <?php echo htmlspecialchars($group_info->name, ENT_QUOTES, 'UTF-8'); ?>
            </h3>
            <?php if (!empty($group_info->description)): ?>
                <div class="group-description">
                    <i class="fa fa-info-circle"></i> <?php echo htmlspecialchars($group_info->description, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Privileges List -->
<?php
if (!empty($privilege_list[0])):
    foreach ($privilege_list[0] as $key => $value):
        if (!empty($value)):
?>
    <div class="card module-card">
        <div class="card-header">
            <i class="fa fa-folder-open module-icon"></i>
            <?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php 
            foreach ($value as $k => $v): 
                $module_id = $privilege_list[1][$key][$k];
            ?>
                <div class="privilege-item">
                    <div class="custom-checkbox-wrapper">
                        <input type="checkbox" 
                               name="module_<?php echo $module_id[0].'_'.$module_id[1]; ?>" 
                               id="module_<?php echo $module_id[0].'_'.$module_id[1]; ?>"
                               value="1" 
                               <?php echo ($v==1 ? 'checked="checked"':''); ?>
                               class="privilege-checkbox">
                        <label class="custom-checkbox" for="module_<?php echo $module_id[0].'_'.$module_id[1]; ?>"></label>
                    </div>
                    <label class="privilege-label" for="module_<?php echo $module_id[0].'_'.$module_id[1]; ?>">
                        <?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php 
        endif;
    endforeach;
else:
?>
    <div class="no-privileges">
        <i class="fa fa-inbox fa-3x" style="margin-bottom: 15px; opacity: 0.3;"></i>
        <p>No privileges available to assign.</p>
    </div>
<?php endif; ?>

<!-- Action Buttons -->
<?php if (!empty($privilege_list[0])): ?>
<div class="action-buttons">
    <!-- Hidden field to ensure 'save' parameter is always sent -->
    <input type="hidden" name="save" value="1" />
    
    <!-- Hidden fields for all checkboxes to ensure unchecked state is sent -->
    <?php 
    foreach ($privilege_list[1] as $key => $value): 
        foreach ($value as $k => $v): 
            $module_id = $privilege_list[1][$key][$k];
            $field_name = 'module_' . $module_id[0] . '_' . $module_id[1];
            // Add hidden field with value 0 - checked checkbox will override with value 1
            // This ensures unchecked checkboxes send 0 instead of nothing
    ?>
        <input type="hidden" name="<?php echo $field_name; ?>" value="0" />
    <?php 
        endforeach; 
    endforeach; 
    ?>
    
    <button type="submit" class="btn btn-primary btn-save" id="btn-save-privileges">
        <i class="fa fa-save"></i> <?php echo lang('privillege_btn_save'); ?>
    </button>
    <a href="<?php echo site_url(current_lang() . '/auth/grouplist'); ?>" class="btn btn-secondary" style="margin-left: 10px; padding: 12px 30px;">
        <i class="fa fa-arrow-left"></i> Back to Group List
    </a>
</div>
<?php endif; ?>

</div>
<?php echo form_close(); ?>

<script>
// Ensure form submission works properly
$(document).ready(function() {
    var form = $('form').has('.assign-role-container');
    
    if (form.length === 0) {
        form = $('.assign-role-container').closest('form');
    }
    
    // Ensure all checkboxes are properly handled - make sure they have proper name/value
    $('.privilege-checkbox').each(function() {
        if (!$(this).attr('value')) {
            $(this).attr('value', '1');
        }
    });
    
    // Initialize: Setup checkboxes on page load - keep ALL checkboxes ENABLED for user interaction
    $('.privilege-checkbox').each(function() {
        var $checkbox = $(this);
        var name = $checkbox.attr('name');
        
        // Ensure all checkboxes are enabled (user can interact with them)
        $checkbox.prop('disabled', false);
        
        if ($checkbox.is(':checked')) {
            // Checked: Remove any hidden fields
            $('input[type="hidden"][name="' + name + '"]').remove();
        } else {
            // Unchecked: Remove existing hidden fields first, then add one with value 0
            $('input[type="hidden"][name="' + name + '"]').remove();
            $('<input>').attr({
                type: 'hidden',
                name: name,
                value: '0'
            }).insertAfter($checkbox);
        }
    });
    
    // Handle checkbox changes - add/remove hidden fields (ALWAYS keep checkboxes ENABLED)
    $('.privilege-checkbox').on('change', function() {
        var $checkbox = $(this);
        var name = $checkbox.attr('name');
        var isChecked = $checkbox.is(':checked');
        
        // CRITICAL: Always keep checkbox enabled so user can interact with it
        $checkbox.prop('disabled', false);
        
        // Remove any existing hidden field with same name
        $('input[type="hidden"][name="' + name + '"]').remove();
        
        if (!isChecked) {
            // Unchecked: Add hidden field with value 0 (will be used during form submit)
            $('<input>').attr({
                type: 'hidden',
                name: name,
                value: '0'
            }).insertAfter($checkbox);
            console.log('Checkbox unchecked - added hidden field (0), checkbox remains enabled:', name);
        } else {
            console.log('Checkbox checked - removed hidden field, checkbox enabled:', name);
        }
    });
    
    // Handle form submission - DO NOT prevent default
    form.on('submit', function(e) {
        console.log('========================================');
        console.log('Form submit event triggered');
        console.log('========================================');
        
        // Count checked checkboxes and log them
        var checkedBoxes = [];
        var allBoxes = [];
        $('.privilege-checkbox').each(function() {
            var name = $(this).attr('name');
            var checked = $(this).is(':checked');
            allBoxes.push(name + '=' + (checked ? '1' : 'NOT CHECKED'));
            if (checked) {
                checkedBoxes.push(name + '=1');
            }
        });
        console.log('All checkboxes:', allBoxes);
        console.log('Checked checkboxes:', checkedBoxes.length, checkedBoxes);
        
        // Log all form data that will be submitted
        var formData = $(this).serialize();
        console.log('Form data to submit:', formData);
        console.log('Form data length:', formData.length);
        
        // Verify save hidden field
        var saveField = $('input[name="save"]');
        console.log('Save hidden field exists:', saveField.length > 0);
        console.log('Save hidden field value:', saveField.val());
        
        // Check specifically for saving_account_list
        var savingListCheckbox = $('input[name*="saving_account_list"], input[id*="saving_account_list"]');
        console.log('saving_account_list checkbox found:', savingListCheckbox.length);
        if (savingListCheckbox.length > 0) {
            console.log('saving_account_list checkbox name:', savingListCheckbox.attr('name'));
            console.log('saving_account_list checkbox checked:', savingListCheckbox.is(':checked'));
        }
        
        // Check specifically for Edit_saving_account
        var editCheckbox = $('input[name*="Edit_saving_account"], input[id*="Edit_saving_account"]');
        console.log('Edit_saving_account checkbox found:', editCheckbox.length);
        if (editCheckbox.length > 0) {
            console.log('Edit_saving_account checkbox name:', editCheckbox.attr('name'));
            console.log('Edit_saving_account checkbox checked:', editCheckbox.is(':checked'));
        }
        
        console.log('========================================');
        console.log('PAUSING FOR 3 SECONDS - Check console above');
        console.log('Form will submit after delay...');
        console.log('========================================');
        
        // Set button state
        var btn = $('#btn-save-privileges');
        btn.prop('disabled', true);
        btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        // Show saving message
        var savingMsg = $('<div class="alert alert-info" style="margin:15px 0;"><i class="fa fa-spinner fa-spin"></i> Saving permissions, please wait...</div>');
        $('.assign-role-container').prepend(savingMsg);
        
        // Don't prevent default - let form submit normally
        // The delay was just for viewing console, but we need actual submission
        // Remove the delay and submit immediately
        console.log('========================================');
        console.log('Submitting form now...');
        console.log('========================================');
        
        // CRITICAL: Ensure unchecked checkboxes send value 0
        // Temporarily disable unchecked checkboxes ONLY during form submission
        // This prevents them from sending value 1, hidden field with 0 will be sent instead
        $('.privilege-checkbox').each(function() {
            var $checkbox = $(this);
            var name = $checkbox.attr('name');
            if (name) {
                // Remove ALL hidden fields with this name first
                $('input[type="hidden"][name="' + name + '"]').remove();
                
                if (!$checkbox.is(':checked')) {
                    // For unchecked: Temporarily disable checkbox during submit only
                    // Store that it was enabled so we can re-enable it later
                    $checkbox.data('was-enabled', true);
                    $checkbox.prop('disabled', true); // Disable only during submit
                    
                    // Ensure hidden field with 0 exists
                    $('<input>').attr({
                        type: 'hidden',
                        name: name,
                        value: '0'
                    }).insertAfter($checkbox);
                    console.log('Unchecked checkbox - temporarily disabled for submit, hidden field (0) will be sent:', name);
                } else {
                    // For checked: ensure checkbox is enabled and no hidden field
                    $checkbox.prop('disabled', false);
                    console.log('Checked checkbox - enabled, will send value 1:', name);
                }
            }
        });
        
        // Re-enable all checkboxes after form submits (in case submission fails or is prevented)
        setTimeout(function() {
            $('.privilege-checkbox').each(function() {
                if ($(this).data('was-enabled')) {
                    $(this).prop('disabled', false);
                    $(this).removeData('was-enabled');
                }
            });
        }, 100);
        
        // Final check - log what will be submitted
        var finalFormData = form.serialize();
        console.log('Final form data length:', finalFormData.length);
        
        var savingListCheckbox = $('input[name*="saving_account_list"], input[id*="saving_account_list"]');
        var editCheckbox = $('input[name*="Edit_saving_account"], input[id*="Edit_saving_account"]');
        
        if (savingListCheckbox.length > 0) {
            var savingName = savingListCheckbox.attr('name');
            var savingChecked = savingListCheckbox.is(':checked');
            var savingInData = finalFormData.indexOf(savingName + '=1') !== -1 || finalFormData.indexOf(savingName + '=0') !== -1;
            console.log('saving_account_list - Checked:', savingChecked, 'In form data:', savingInData);
            if (!savingInData) {
                console.error('ERROR: saving_account_list NOT in form data!');
            }
        }
        if (editCheckbox.length > 0) {
            var editName = editCheckbox.attr('name');
            var editChecked = editCheckbox.is(':checked');
            var editInData = finalFormData.indexOf(editName + '=1') !== -1 || finalFormData.indexOf(editName + '=0') !== -1;
            console.log('Edit_saving_account - Checked:', editChecked, 'In form data:', editInData);
            if (!editInData) {
                console.error('ERROR: Edit_saving_account NOT in form data!');
            }
        }
        
        // Allow form to submit normally
        return true;
    });
    
    // Ensure button click doesn't interfere
    $('#btn-save-privileges').on('click', function(e) {
        console.log('Save button clicked');
        // Don't prevent default - let it submit
        return true;
    });
    
    // Debug: Check if form exists and is valid
    console.log('Form element:', form.length > 0 ? 'Found' : 'NOT FOUND');
    if (form.length > 0) {
        console.log('Form action:', form.attr('action'));
        console.log('Form method:', form.attr('method') || 'GET (default)');
    }
});
</script>
</script>