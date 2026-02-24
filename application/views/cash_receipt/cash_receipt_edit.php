<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
</style>

<?php echo form_open_multipart(current_lang() . "/cash_receipt/cash_receipt_edit/" . $id, 'class="form-horizontal" id="cashReceiptForm"'); ?>

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

<input type="hidden" name="id" value="<?php echo $receipt->id; ?>"/>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo lang('cash_receipt_edit'); ?> - <?php echo $receipt->receipt_no; ?></h5>
                    <div class="ibox-tools">
                        <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_list'); ?>" class="btn btn-white btn-xs">
                            <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    
                    <!-- Receipt Header Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_no'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" name="receipt_no" value="<?php echo set_value('receipt_no', $receipt->receipt_no); ?>" class="form-control" required/>
                                    <?php echo form_error('receipt_no'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_date'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <div class="input-group date" id="datetimepicker">
                                             <input type="text" name="receipt_date" placeholder="<?php echo lang('hint_date'); ?>" 
                                                 value="<?php echo set_value('receipt_date', date('d-m-Y', strtotime($receipt->receipt_date))); ?>" 
                                                 data-date-format="dd-mm-yyyy" class="form-control" required/> 
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar"></span>
                                        </span>
                                    </div>
                                    <?php echo form_error('receipt_date'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_received_from'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <input type="text" name="received_from" id="received_from" value="<?php echo set_value('received_from', $receipt->received_from); ?>" class="form-control" required/>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-info" id="searchMemberBtn" data-toggle="modal" data-target="#memberSearchModal">
                                                <i class="fa fa-search"></i> Search Member
                                            </button>
                                        </span>
                                    </div>
                                    <input type="hidden" name="member_pid" id="member_pid" value=""/>
                                    <input type="hidden" name="member_id" id="member_id" value=""/>
                                    <?php echo form_error('received_from'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_payment_method'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                        <option value=""><?php echo lang('select_default_text'); ?></option>
                                        <?php 
                                        $selected_payment_id = set_value('payment_method');
                                        // Handle saved payment_method: if it's numeric (ID), use it directly; if it's a name, lookup the ID
                                        if (empty($selected_payment_id) && !empty($receipt->payment_method)) {
                                            if (is_numeric($receipt->payment_method)) {
                                                // Saved value is an ID
                                                $selected_payment_id = (int)$receipt->payment_method;
                                            } else {
                                                // Saved value is a name, lookup the ID
                                                $saved_method_lower = strtolower(trim($receipt->payment_method));
                                                if (isset($payment_method_id_by_name[$saved_method_lower])) {
                                                    $selected_payment_id = $payment_method_id_by_name[$saved_method_lower];
                                                }
                                            }
                                        }
                                        foreach ($payment_methods as $id => $name): 
                                            $is_selected = (!empty($selected_payment_id) && $id == $selected_payment_id);
                                        ?>
                                            <option value="<?php echo $id; ?>" <?php echo $is_selected ? 'selected="selected"' : ''; ?>><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php echo form_error('payment_method'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="cheque_details" style="display: <?php echo ($receipt->payment_method == 'Cheque') ? 'block' : 'none'; ?>;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_cheque_no'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="cheque_no" value="<?php echo set_value('cheque_no', $receipt->cheque_no); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_bank_name'); ?> :</label>
                                <div class="col-lg-8">
                                    <input type="text" name="bank_name" value="<?php echo set_value('bank_name', $receipt->bank_name); ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="col-lg-2 control-label"><?php echo lang('cash_receipt_description'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-10">
                                    <textarea name="description" class="form-control" rows="3" required><?php echo set_value('description', $receipt->description); ?></textarea>
                                    <?php echo form_error('description'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="cancelled" id="cancelled" value="1" <?php echo set_checkbox('cancelled', '1', !empty($receipt->cancelled)); ?>/>
                                            <?php echo lang('cancelled'); ?>
                                        </label>
                                    </div>
                                    <p class="help-block text-muted" style="margin-left: 0;"><?php echo lang('cash_receipt_cancelled_help'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <!-- Line Items Table (journal-entry style: Account | Description | Debit | Credit) -->
                    <h4><?php echo lang('cash_receipt_line_items'); ?></h4>
                    <div class="table-responsive">
                        <table id="lineItemsTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 30%;"><?php echo lang('cash_receipt_account'); ?> <span class="required">*</span></th>
                                    <th style="width: 30%;"><?php echo lang('cash_receipt_line_description'); ?></th>
                                    <th style="width: 15%;"><?php echo lang('journalentry_debit'); ?></th>
                                    <th style="width: 15%;"><?php echo lang('journalentry_credit'); ?></th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $edit_total_debit = 0; $edit_total_credit = 0;
                                foreach ($line_items as $index => $item): 
                                    $item_debit = isset($item->debit) ? floatval($item->debit) : 0;
                                    $item_credit = isset($item->credit) ? floatval($item->credit) : (isset($item->amount) ? floatval($item->amount) : 0);
                                    $edit_total_debit += $item_debit;
                                    $edit_total_credit += $item_credit;
                                ?>
                                <tr class="line-item">
                                    <td>
                                        <select class="form-control account-select" name="account[]">
                                            <option value=""><?php echo lang('select_default_text'); ?></option>
                                            <?php foreach ($account_list as $key1 => $value1) { ?>
                                                <optgroup label="<?php echo $value1['info']->name; ?>">
                                                    <?php foreach ($value1['data'] as $key => $value) { ?>
                                                        <option value="<?php echo $value->account; ?>" <?php echo ($item->account == $value->account) ? 'selected' : ''; ?>><?php echo $value->name; ?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="line_description[]" class="form-control" value="<?php echo htmlspecialchars($item->description); ?>"/>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="debit[]" class="form-control debit-input" value="<?php echo $item_debit; ?>" placeholder="0.00"/>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="credit[]" class="form-control credit-input" value="<?php echo $item_credit; ?>" placeholder="0.00"/>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-xs remove-line" <?php echo (count($line_items) <= 1) ? 'disabled' : ''; ?> title="<?php echo lang('delete'); ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-right"><strong><?php echo lang('total'); ?>:</strong></td>
                                    <td>
                                        <input type="text" id="total_debit" class="form-control" readonly value="<?php echo number_format($edit_total_debit, 2); ?>"/>
                                    </td>
                                    <td>
                                        <input type="text" id="total_credit" class="form-control" readonly value="<?php echo number_format($edit_total_credit, 2); ?>"/>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="2" id="balance_diff" class="text-right" style="color: red; font-weight: bold;"></td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="button" class="btn btn-primary" id="addLineItem">
                            <i class="fa fa-plus"></i> <?php echo lang('add_row'); ?>
                        </button>
                    </div>

                    <hr/>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo lang('update'); ?>
                            </button>
                            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . $id); ?>" class="btn btn-white">
                                <?php echo lang('cancel'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<!-- Member Search Modal -->
<div class="modal fade" id="memberSearchModal" tabindex="-1" role="dialog" aria-labelledby="memberSearchModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="memberSearchModalLabel">Search Member</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Search by Member ID, PID, or Name:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="memberSearchKey" placeholder="Enter member ID, PID, or name...">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="button" id="doMemberSearch">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </span>
                    </div>
                </div>
                <div id="memberSearchResults" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-muted text-center">Enter search keyword and click Search</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    function loadScript(src, cb, fallback){ var s=document.createElement('script'); s.src=src; s.onload=cb; if(fallback){ s.onerror=function(){ loadScript(fallback, cb); }; } document.head.appendChild(s); }
    function initOnceReady(){
        if(!window.jQuery){ setTimeout(initOnceReady, 50); return; }
        var $=window.jQuery;
        function boot(){
            function ensureBootstrapDP(cb){
                function wrapBootstrapDP(){
                    if ($.fn.datepicker && $.fn.datepicker.DPGlobal){
                        var bootstrapDP = $.fn.datepicker;
                        if ($.fn.datepicker.noConflict){
                            $.fn.datepicker.noConflict();
                        }
                        $.fn.bootstrapDP = bootstrapDP;
                        cb();
                    } else {
                        cb();
                    }
                }

                if(!($.fn.datepicker && $.fn.datepicker.DPGlobal)){
                    loadScript(
                        '<?php echo base_url(); ?>assets/js/plugins/datapicker/bootstrap-datepicker.js',
                        wrapBootstrapDP,
                        'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js'
                    );
                } else {
                    wrapBootstrapDP();
                }
            }

            function initPicker(){
                var picker = $.fn.bootstrapDP || $.fn.datepicker;
                if (!picker){ return; }
                picker.call($('#datetimepicker'), {
                    todayBtn:'linked', keyboardNavigation:false, forceParse:false,
                    calendarWeeks:true, autoclose:true, format:'dd-mm-yyyy',
                    orientation:'bottom auto', todayHighlight:true, container:'body'
                });
            }

            ensureBootstrapDP(initPicker);

            updateRemoveButtons();

            $('#payment_method').on('change', function(){
                if($(this).val()==='Cheque'){ $('#cheque_details').show(); } else { $('#cheque_details').hide(); }
            });

            $('#addLineItem').on('click', function(){
                var newRow=$('.line-item:first').clone();
                newRow.find('input, select').val('');
                $('#lineItemsTable tbody').append(newRow);
                updateRemoveButtons();
                calculateTotals();
            });

            $(document).on('click', '.remove-line', function(){
                if($('.line-item').length>1){ $(this).closest('tr').remove(); updateRemoveButtons(); calculateTotals(); }
            });

            function updateRemoveButtons(){
                var count=$('.line-item').length;
                $('.remove-line').prop('disabled', count<=1);
            }

            $(document).on('keyup change', '.debit-input, .credit-input', function(){ calculateTotals(); });

            function calculateTotals(){
                var totalDebit=0, totalCredit=0;
                $('.debit-input').each(function(){ totalDebit += parseFloat($(this).val()) || 0; });
                $('.credit-input').each(function(){ totalCredit += parseFloat($(this).val()) || 0; });
                $('#total_debit').val(totalDebit.toFixed(2));
                $('#total_credit').val(totalCredit.toFixed(2));
                var diff=totalDebit-totalCredit;
                if(Math.abs(diff)<0.01){ $('#balance_diff').text('').css('color','green'); }
                else{ $('#balance_diff').text('Diff: '+diff.toFixed(2)).css('color','red'); }
            }

            // Form validation - debits must equal credits (skip when cancelled)
            $('#cashReceiptForm').on('submit', function(e){
                if ($('#cancelled').is(':checked')) {
                    return true; // No line items validation when cancelled
                }
                var totalDebit=0, totalCredit=0, hasItems=false;
                $('.debit-input').each(function(){ totalDebit += parseFloat($(this).val()) || 0; });
                $('.credit-input').each(function(){ var v=parseFloat($(this).val())||0; totalCredit+=v; if(v>0)hasItems=true; });
                if(!hasItems){ alert('<?php echo lang('cash_receipt_no_items'); ?>'); e.preventDefault(); return false; }
                if(Math.abs(totalDebit-totalCredit)>0.01){ alert('<?php echo lang('debits_credits_not_balanced'); ?>'); e.preventDefault(); return false; }
                return true;
            });

            // Toggle line items required indicator when cancelled changes
            $('#cancelled').on('change', function(){
                if ($(this).is(':checked')) {
                    $('#lineItemsTable th .required').hide();
                } else {
                    $('#lineItemsTable th .required').show();
                }
            });
            
            // Initialize on page load
            if ($('#cancelled').is(':checked')) {
                $('#lineItemsTable th .required').hide();
            }

            // Member Search functionality
            var memberSearchUrl = '<?php echo site_url(current_lang() . '/cash_receipt/search_member'); ?>';
            var arAccountUrl = '<?php echo site_url(current_lang() . '/cash_receipt/get_ar_account'); ?>';

            // Search on button click
            $('#doMemberSearch').on('click', function(){
                var key = $('#memberSearchKey').val().trim();
                if(key.length < 2){
                    alert('Please enter at least 2 characters to search');
                    return;
                }
                searchMembers(key);
            });

            // Search on Enter key
            $('#memberSearchKey').on('keypress', function(e){
                if(e.which === 13){
                    e.preventDefault();
                    $('#doMemberSearch').click();
                }
            });

            function searchMembers(key){
                $('#memberSearchResults').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Searching...</p>');
                
                $.ajax({
                    url: memberSearchUrl,
                    type: 'GET',
                    data: { key: key },
                    dataType: 'json',
                    success: function(response){
                        if(response.success === 'Y' && response.data && response.data.length > 0){
                            var html = '<table class="table table-bordered table-hover">';
                            html += '<thead><tr><th>Member ID</th><th>PID</th><th>Full Name</th><th>Action</th></thead>';
                            html += '<tbody>';
                            
                            $.each(response.data, function(index, member){
                                html += '<tr>';
                                html += '<td>' + (member.member_id || '') + '</td>';
                                html += '<td>' + (member.PID || '') + '</td>';
                                html += '<td>' + (member.fullname || '') + '</td>';
                                html += '<td><button type="button" class="btn btn-sm btn-primary select-member" ';
                                html += 'data-pid="' + (member.PID || '') + '" ';
                                html += 'data-member-id="' + (member.member_id || '') + '" ';
                                html += 'data-fullname="' + (member.fullname || '') + '">';
                                html += '<i class="fa fa-check"></i> Select</button></td>';
                                html += '</tr>';
                            });
                            
                            html += '</tbody></table>';
                            $('#memberSearchResults').html(html);
                        } else {
                            $('#memberSearchResults').html('<p class="text-danger text-center">' + (response.error || 'No members found') + '</p>');
                        }
                    },
                    error: function(){
                        $('#memberSearchResults').html('<p class="text-danger text-center">Error searching members. Please try again.</p>');
                    }
                });
            }

            // Handle member selection
            $(document).on('click', '.select-member', function(){
                var pid = $(this).data('pid');
                var memberId = $(this).data('member-id');
                var fullname = $(this).data('fullname');
                
                // Populate received from field
                $('#received_from').val(fullname);
                $('#member_pid').val(pid);
                $('#member_id').val(memberId);
                
                // Close modal
                $('#memberSearchModal').modal('hide');
                
                // Get AR account and auto-add to line items
                addARAccountEntry(fullname);
            });

            // Function to add AR account entry
            function addARAccountEntry(memberName){
                $.ajax({
                    url: arAccountUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response){
                        if(response.success === 'Y' && response.account){
                            // Check if AR account already exists in line items
                            var arExists = false;
                            $('.account-select').each(function(){
                                if($(this).val() === response.account){
                                    arExists = true;
                                    return false;
                                }
                            });
                            
                            if(!arExists){
                                // Add new line item with AR account
                                var newRow = $('.line-item:first').clone();
                                newRow.find('input, select').val('');
                                
                                // Set AR account
                                newRow.find('.account-select').val(response.account);
                                
                                // Set description with member name
                                var description = 'AR - ' + memberName;
                                newRow.find('input[name="line_description[]"]').val(description);
                                
                                // Add to table
                                $('#lineItemsTable tbody').append(newRow);
                                updateRemoveButtons();
                                calculateTotals();
                                
                                // Show message
                                alert('Accounts Receivable account (' + response.name + ') has been added. Please enter the credit amount.');
                            } else {
                                alert('Accounts Receivable account already exists in line items.');
                            }
                        } else {
                            // AR account not found, just show info
                            console.log('AR account not found: ' + (response.error || ''));
                        }
                    },
                    error: function(){
                        console.log('Error fetching AR account');
                    }
                });
            }
        }
        $(boot);
    }
    initOnceReady();
})();
</script>
