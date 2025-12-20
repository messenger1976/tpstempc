<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet">
<!-- Datepicker CSS -->
<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<?php echo form_open_multipart(current_lang() . "/loan/loan_beginning_balance_create/" . (isset($id) ? $id : ''), 'class="form-horizontal"'); ?>

<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}
?>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('fiscal_year'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select class="form-control" name="fiscal_year_id" id="fiscal_year_id" required>
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php foreach ($fiscal_years as $fy) { ?>
                <option value="<?php echo $fy->id; ?>" <?php echo (isset($balance) && $balance->fiscal_year_id == $fy->id ? 'selected' : ''); ?>>
                    <?php echo $fy->name . ' (' . date('M d, Y', strtotime($fy->start_date)) . ' - ' . date('M d, Y', strtotime($fy->end_date)) . ')'; ?>
                </option>
            <?php } ?>
        </select>
        <?php echo form_error('fiscal_year_id'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_member_id'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <div class="input-group">
            <input type="text" name="member_id" id="member_id" value="<?php echo isset($balance) ? $balance->member_id : set_value('member_id'); ?>" class="form-control" required />
            <span class="input-group-addon" id="search_mid" style="cursor: pointer;">
                <span class="fa fa-search"></span>
            </span>
        </div>
        <?php echo form_error('member_id'); ?>
        <small class="help-block"><?php echo lang('member_id'); ?></small>
        <div id="member_info" style="margin-top: 10px;"></div>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_loan_product'); ?> : <span class="required">*</span></label>
    <div class="col-lg-6">
        <select class="form-control" name="loan_product_id" id="loan_product_id" required>
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php foreach ($loan_products as $product) { ?>
                <option value="<?php echo $product->id; ?>" <?php echo (isset($balance) && $balance->loan_product_id == $product->id ? 'selected' : ''); ?>>
                    <?php echo $product->name; ?>
                </option>
            <?php } ?>
        </select>
        <?php echo form_error('loan_product_id'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_loan_id'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="loan_id" id="loan_id" value="<?php echo isset($balance) ? $balance->loan_id : set_value('loan_id'); ?>" class="form-control" />
        <?php echo form_error('loan_id'); ?>
        <small class="help-block">Optional - Reference to existing loan ID</small>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_principal'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="principal_balance" id="principal_balance" value="<?php echo isset($balance) ? number_format($balance->principal_balance, 2) : set_value('principal_balance', '0.00'); ?>" class="form-control" onkeyup="formatNumber(this); calculateTotal();" />
        <?php echo form_error('principal_balance'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_interest'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="interest_balance" id="interest_balance" value="<?php echo isset($balance) ? number_format($balance->interest_balance, 2) : set_value('interest_balance', '0.00'); ?>" class="form-control" onkeyup="formatNumber(this); calculateTotal();" />
        <?php echo form_error('interest_balance'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_penalty'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="penalty_balance" id="penalty_balance" value="<?php echo isset($balance) ? number_format($balance->penalty_balance, 2) : set_value('penalty_balance', '0.00'); ?>" class="form-control" onkeyup="formatNumber(this); calculateTotal();" />
        <?php echo form_error('penalty_balance'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_total'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" id="total_balance" value="<?php echo isset($balance) ? number_format($balance->total_balance, 2) : '0.00'; ?>" class="form-control" readonly style="font-weight: bold; background-color: #f5f5f5;" />
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_disbursement_date'); ?> : </label>
    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker_disbursement">
            <input type="text" name="disbursement_date" id="disbursement_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo isset($balance) && $balance->disbursement_date ? date('d-m-Y', strtotime($balance->disbursement_date)) : set_value('disbursement_date'); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
        </div>
        <?php echo form_error('disbursement_date'); ?>
        <small class="help-block">Optional - Original loan disbursement date (DD-MM-YYYY)</small>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_loan_amount'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="loan_amount" id="loan_amount" value="<?php echo isset($balance) && $balance->loan_amount ? number_format($balance->loan_amount, 2) : set_value('loan_amount', ''); ?>" class="form-control" onkeyup="formatNumber(this);" />
        <?php echo form_error('loan_amount'); ?>
        <small class="help-block">Optional - Original loan amount</small>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_monthly_amort'); ?> : </label>
    <div class="col-lg-6">
        <input type="text" name="monthly_amort" id="monthly_amort" value="<?php echo isset($balance) && $balance->monthly_amort ? number_format($balance->monthly_amort, 2) : set_value('monthly_amort', ''); ?>" class="form-control" onkeyup="formatNumber(this);" />
        <?php echo form_error('monthly_amort'); ?>
        <small class="help-block">Optional - Monthly amortization amount</small>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_last_date_paid'); ?> : </label>
    <div class="col-lg-6">
        <div class="input-group date" id="datetimepicker_last_date_paid">
            <input type="text" name="last_date_paid" id="last_date_paid" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo isset($balance) && $balance->last_date_paid ? date('d-m-Y', strtotime($balance->last_date_paid)) : set_value('last_date_paid'); ?>" data-date-format="DD-MM-YYYY" class="form-control"/>
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
        </div>
        <?php echo form_error('last_date_paid'); ?>
        <small class="help-block">Optional - Last payment date (DD-MM-YYYY)</small>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('loan_beginning_balance_term'); ?> : </label>
    <div class="col-lg-6">
        <input type="number" name="term" id="term" value="<?php echo isset($balance) ? $balance->term : set_value('term', ''); ?>" class="form-control" min="1" step="1" />
        <?php echo form_error('term'); ?>
        <small class="help-block">Optional - Loan term in months</small>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label"><?php echo lang('description'); ?> : </label>
    <div class="col-lg-6">
        <textarea name="description" class="form-control" rows="3"><?php echo isset($balance) ? $balance->description : set_value('description'); ?></textarea>
        <?php echo form_error('description'); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label">&nbsp;</label>
    <div class="col-lg-6">
        <input class="btn btn-primary" value="<?php echo isset($balance) ? lang('button_update') : lang('loan_beginning_balance_btncreate'); ?>" type="submit"/>
        <a href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_list'); ?>" class="btn btn-default"><?php echo lang('button_cancel'); ?></a>
    </div>
</div>

<?php echo form_close(); ?>

<script>
function formatNumber(input) {
    // Remove all non-numeric characters except decimal point
    var value = input.value.replace(/[^\d.]/g, '');
    
    // Ensure only one decimal point
    var parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit to 2 decimal places
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    input.value = value;
}

function calculateTotal() {
    var principal = parseFloat($('#principal_balance').val().replace(/,/g, '')) || 0;
    var interest = parseFloat($('#interest_balance').val().replace(/,/g, '')) || 0;
    var penalty = parseFloat($('#penalty_balance').val().replace(/,/g, '')) || 0;
    var total = principal + interest + penalty;
    $('#total_balance').val(total.toFixed(2));
}

// Format on page load - wait for jQuery to be available
(function() {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }
        
        // Load autocomplete script dynamically if not already loaded
        var autocompleteScriptLoaded = false;
        var existingScript = document.querySelector('script[src*="jquery.autocomplete_saving.js"]');
        
        if (existingScript) {
            // Script already exists, initialize after a short delay
            setTimeout(function() {
                initializePage();
            }, 200);
        } else {
            // Load the autocomplete script
            var autocompleteScript = document.createElement('script');
            autocompleteScript.src = '<?php echo base_url(); ?>media/js/jquery.autocomplete_saving.js';
            autocompleteScript.onload = function() {
                autocompleteScriptLoaded = true;
                setTimeout(function() {
                    initializePage();
                }, 300);
            };
            autocompleteScript.onerror = function() {
                console.error('Failed to load autocomplete plugin');
                // Still try to initialize without autocomplete
                setTimeout(function() {
                    initializePage();
                }, 300);
            };
            document.head.appendChild(autocompleteScript);
        }
        
        function initializePage() {
            jQuery(document).ready(function($) {
                $('#principal_balance, #interest_balance, #penalty_balance').on('blur', function() {
                    var value = parseFloat($(this).val().replace(/,/g, ''));
                    if (!isNaN(value)) {
                        $(this).val(value.toFixed(2));
                    }
                    calculateTotal();
                });
                
                // Calculate total on page load
                calculateTotal();
                
                // Initialize member autocomplete
                try {
                    if ($("#member_id").data('ui-autocomplete')) {
                        $("#member_id").autocomplete('destroy');
                    }
                } catch(e) {
                    // Ignore errors
                }
                
                // Wait a bit to ensure cleanup is complete before initializing
                setTimeout(function() {
                    try {
                        if (typeof $.fn.autocomplete !== 'undefined') {
                            $("#member_id").autocomplete("<?php echo site_url(current_lang() . '/loan/autosuggest_member/mid'); ?>", {
                                pleasewait: '<?php echo lang("please_wait"); ?>',
                                serverURLq: '<?php echo site_url(current_lang() . '/loan/search_member/'); ?>',
                                Name: '<?php echo lang('member_fullname'); ?>',
                                gender: '<?php echo lang('member_gender'); ?>',
                                dob: '<?php echo lang('member_dob'); ?>',
                                joindate: '<?php echo lang('member_join_date'); ?>',
                                phone1: '<?php echo lang('member_contact_phone1'); ?> ',
                                phone2: '<?php echo lang('member_contact_phone2'); ?>',
                                email: '<?php echo lang('member_contact_email'); ?>',
                                photourl: '<?php echo base_url(); ?>uploads/memberphoto/',
                                matchContains: true,
                                column: 'MID',
                                balance: '', // Don't show balance for loan beginning balances
                                customerNameID: '' // Don't set customer name field
                            });
                        }
                    } catch(e) {
                        console.error('Autocomplete initialization error:', e);
                        // Retry once more after a longer delay
                        setTimeout(function() {
                            try {
                                if (typeof $.fn.autocomplete !== 'undefined') {
                                    $("#member_id").autocomplete("<?php echo site_url(current_lang() . '/loan/autosuggest_member/mid'); ?>", {
                                        pleasewait: '<?php echo lang("please_wait"); ?>',
                                        serverURLq: '<?php echo site_url(current_lang() . '/loan/search_member/'); ?>',
                                        Name: '<?php echo lang('member_fullname'); ?>',
                                        gender: '<?php echo lang('member_gender'); ?>',
                                        dob: '<?php echo lang('member_dob'); ?>',
                                        joindate: '<?php echo lang('member_join_date'); ?>',
                                        phone1: '<?php echo lang('member_contact_phone1'); ?> ',
                                        phone2: '<?php echo lang('member_contact_phone2'); ?>',
                                        email: '<?php echo lang('member_contact_email'); ?>',
                                        photourl: '<?php echo base_url(); ?>uploads/memberphoto/',
                                        matchContains: true,
                                        column: 'MID',
                                        balance: '', // Don't show balance for loan beginning balances
                                        customerNameID: '' // Don't set customer name field
                                    });
                                }
                            } catch(e2) {
                                console.error('Autocomplete retry failed:', e2);
                            }
                        }, 300);
                    }
                }, 150);
                
                // Load member info if member_id is already set
                var member_id = '<?php echo isset($balance) ? $balance->member_id : set_value('member_id'); ?>';
                if (member_id && member_id.length > 0) {
                    $('#member_info').html('<?php echo lang("please_wait"); ?>');
                    $.ajax({
                        url: '<?php echo site_url(current_lang() . '/loan/search_member/'); ?>',
                        type: 'POST',
                        data: {
                            value: member_id,
                            column: 'MID'
                        },
                        success: function(data) {
                            var json = JSON.parse(data);
                            if (json['success'].toString() == 'N') {
                                $('#member_info').html('<div style="color:red;">' + json['error'].toString() + '</div>');
                            } else {
                                var userdata = json['data'];
                                var contact = json['contact'];
                                var output = '<div style="border:1px solid #ccc;font-size:15px;"><table style="width:100%;"><tr><td style="width:70%;">';
                                output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_fullname'); ?> : </strong> ' + userdata["firstname"] + ' ' + userdata["middlename"] + ' ' + userdata["lastname"] + '</div>';
                                output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_gender'); ?> : </strong> ' + userdata["gender"] + '</div>';
                                output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_dob'); ?> : </strong> ' + userdata["dob"] + '</div>';
                                output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_join_date'); ?> : </strong> ' + userdata["joiningdate"] + '</div>';
                                output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone1'); ?> : </strong> ' + contact["phone1"] + '</div>';
                                output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone2'); ?> : </strong> ' + contact["phone2"] + '</div>';
                                output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_email'); ?> : </strong> ' + contact["email"] + '</div>';
                                output += '</td><td>  <img style="height:120px;" src="<?php echo base_url(); ?>uploads/memberphoto/' + userdata["photo"].toString() + '"/></td></tr></table></div>';
                                $('#member_info').html(output);
                            }
                        },
                        error: function(xhr, textStatus, errorThrown) {
                            $('#member_info').html('<div style="color:red;">Error loading member information</div>');
                        }
                    });
                }
                
                // Search button click handler
                $('#search_mid').click(function() {
                    var member_id_val = $('#member_id').val();
                    if (member_id_val && member_id_val.length > 0) {
                        $('#member_info').html('<?php echo lang("please_wait"); ?>');
                        $.ajax({
                            url: '<?php echo site_url(current_lang() . '/loan/search_member/'); ?>',
                            type: 'POST',
                            data: {
                                value: member_id_val,
                                column: 'MID'
                            },
                            success: function(data) {
                                var json = JSON.parse(data);
                                if (json['success'].toString() == 'N') {
                                    $('#member_info').html('<div style="color:red;">' + json['error'].toString() + '</div>');
                                } else {
                                    var userdata = json['data'];
                                    var contact = json['contact'];
                                    var output = '<div style="border:1px solid #ccc;font-size:15px;"><table style="width:100%;"><tr><td style="width:70%;">';
                                    output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_fullname'); ?> : </strong> ' + userdata["firstname"] + ' ' + userdata["middlename"] + ' ' + userdata["lastname"] + '</div>';
                                    output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_gender'); ?> : </strong> ' + userdata["gender"] + '</div>';
                                    output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_dob'); ?> : </strong> ' + userdata["dob"] + '</div>';
                                    output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_join_date'); ?> : </strong> ' + userdata["joiningdate"] + '</div>';
                                    output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone1'); ?> : </strong> ' + contact["phone1"] + '</div>';
                                    output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_phone2'); ?> : </strong> ' + contact["phone2"] + '</div>';
                                    output += '<div style="border-bottom:1px dashed #ccc;"><strong><?php echo lang('member_contact_email'); ?> : </strong> ' + contact["email"] + '</div>';
                                    output += '</td><td>  <img style="height:120px;" src="<?php echo base_url(); ?>uploads/memberphoto/' + userdata["photo"].toString() + '"/></td></tr></table></div>';
                                    $('#member_info').html(output);
                                }
                            },
                            error: function(xhr, textStatus, errorThrown) {
                                $('#member_info').html('<div style="color:red;">Error loading member information</div>');
                            }
                        });
                    }
                });
            });
        }
    }
    initScripts();
})();
</script>

<!-- jQuery and Datepicker JS - Same as fiscal year create page -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/plugins/datapicker/bootstrap-datepicker.js"></script>

<script>
(function() {
    // Ensure jQuery is available as $
    var $ = jQuery;

    function initDatePickers() {
        // Double-check jQuery is available
        if (typeof $ === 'undefined' || typeof $.fn === 'undefined') {
            console.warn('jQuery not available, retrying...');
            setTimeout(initDatePickers, 100);
            return;
        }

        try {
            // Initialize datepicker for disbursement date - DD-MM-YYYY format
            if ($('#datetimepicker_disbursement').length > 0) {
                $('#datetimepicker_disbursement').datepicker({
                    format: 'dd-mm-yyyy',  // This produces DD-MM-YYYY format (e.g., 15-01-2024)
                    todayBtn: 'linked',
                    todayHighlight: true,
                    autoclose: true,
                    clearBtn: false,
                    orientation: 'bottom auto'
                });
                console.log('Disbursement date picker initialized with format: dd-mm-yyyy (displays as DD-MM-YYYY)');
            }

            // Initialize datepicker for last date paid - DD-MM-YYYY format
            if ($('#datetimepicker_last_date_paid').length > 0) {
                $('#datetimepicker_last_date_paid').datepicker({
                    format: 'dd-mm-yyyy',  // This produces DD-MM-YYYY format (e.g., 15-01-2024)
                    todayBtn: 'linked',
                    todayHighlight: true,
                    autoclose: true,
                    clearBtn: false,
                    orientation: 'bottom auto'
                });
                console.log('Last date paid picker initialized with format: dd-mm-yyyy (displays as DD-MM-YYYY)');
            }

            console.log('✅ Date pickers initialized successfully');

        } catch (error) {
            console.error('❌ Error initializing date picker:', error);
            // Fallback: basic input without datepicker
            console.log('📝 Falling back to manual date input');
            $('#datetimepicker_disbursement input, #datetimepicker_last_date_paid input').attr('placeholder', 'DD-MM-YYYY');
        }
    }

    // Initialize on document ready
    $(document).ready(function() {
        console.log('Document ready, initializing date pickers...');
        initDatePickers();
    });

    // Fallback: try to initialize after a delay
    setTimeout(function() {
        if (($('#datetimepicker_disbursement').length > 0 && !$('#datetimepicker_disbursement').hasClass('hasDatepicker')) ||
            ($('#datetimepicker_last_date_paid').length > 0 && !$('#datetimepicker_last_date_paid').hasClass('hasDatepicker'))) {
            console.log('Retrying date picker initialization...');
            initDatePickers();
        }
    }, 1000);

})();
</script>

<style>
/* Date picker styling consistent with fiscal year create page */
.input-group.date .input-group-addon {
    cursor: pointer;
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-left: none;
}
.input-group.date .input-group-addon:hover {
    background-color: #e9ecef;
}
.input-group.date .form-control:focus + .input-group-addon,
.input-group.date .input-group-addon:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
