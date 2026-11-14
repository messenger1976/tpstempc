<?php
$receipt = isset($receipt) ? $receipt : null;
$id = isset($id) ? $id : '';
$selected_member_pid = isset($selected_member_pid) ? $selected_member_pid : '';
$selected_received_from = isset($selected_received_from) ? $selected_received_from : '';
$posted_allocations = isset($posted_allocations) && is_array($posted_allocations) ? $posted_allocations : array();
$view_url = site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . $id);
$receipt_total = $receipt ? round((float) $receipt->total_amount, 2) : 0;
$preselect = array();
foreach ($posted_allocations as $row) {
    if (!empty($row['LID'])) {
        $preselect[$row['LID']] = $row;
    }
}
?>
<style type="text/css">
.cr-link-page { margin-top: 4px; }
.cr-link-page .cbu-alert {
    display: block; margin: 0 0 16px; padding: 10px 14px; border-radius: 6px;
    font-size: 13px; font-weight: 600;
}
.cr-link-page .cbu-alert.success { background: #e8f8f5; color: #0e7c69; border: 1px solid #c9ebe3; }
.cr-link-page .cbu-alert.danger { background: #fdeceb; color: #c0392b; border: 1px solid #f5c6cb; }
.cr-link-page .cbu-alert.info { background: #eef6fc; color: #1a5276; border: 1px solid #c5daf0; }
.cr-link-page .cbu-panel {
    background: #fff; border: 1px solid #e7eaec; border-radius: 10px;
    margin-bottom: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); overflow: hidden;
}
.cr-link-page .cbu-panel .panel-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 14px 20px; background: #fafbfc; border-bottom: 1px solid #e7eaec; flex-wrap: wrap;
}
.cr-link-page .cbu-panel .panel-head h4 { margin: 0; font-size: 15px; font-weight: 700; color: #2f4050; }
.cr-link-page .cbu-panel .panel-body { padding: 18px 20px; }
.cr-link-page .info-grid { display: grid; gap: 8px; }
.cr-link-page .info-row {
    display: flex; justify-content: space-between; gap: 12px;
    padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px;
}
.cr-link-page .info-row .lbl { color: #999; font-weight: 500; }
.cr-link-page .info-row .val { color: #2f4050; font-weight: 600; text-align: right; }
.cr-link-page .amount-highlight { color: #1ab394; font-size: 16px; }
.cr-link-page .btn-primary { background: #1ab394; border-color: #1ab394; font-weight: 600; }
.cr-link-page .search-results { margin-top: 8px; max-height: 220px; overflow: auto; }
.cr-link-page .loan-repayment-box {
    margin-top: 12px; padding: 12px 14px; background: #f8fafb; border: 1px solid #e7eaec; border-radius: 8px;
}
.cr-link-page .alloc-mismatch { color: #c0392b; font-weight: 600; }
.cr-link-page .alloc-ok { color: #0e7c69; font-weight: 600; }
</style>

<div class="col-lg-12 cr-link-page">
    <?php
    if (isset($warning) && !empty($warning)) {
        echo '<div class="cbu-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="cbu-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="cbu-alert info">
        <?php echo lang('cash_receipt_link_loan_help'); ?>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <h4><?php echo lang('cash_receipt_link_loan_title'); ?> — <?php echo $receipt ? htmlspecialchars($receipt->receipt_no, ENT_QUOTES, 'UTF-8') : ''; ?></h4>
            <a href="<?php echo $view_url; ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?></a>
        </div>
        <div class="panel-body">
            <?php if ($receipt) { ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-grid">
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_no'); ?></span><span class="val"><?php echo htmlspecialchars($receipt->receipt_no, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_date'); ?></span><span class="val"><?php echo htmlspecialchars(date('d-m-Y', strtotime($receipt->receipt_date)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_received_from'); ?></span><span class="val"><?php echo htmlspecialchars($receipt->received_from, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('cash_receipt_total_amount'); ?></span><span class="val amount-highlight" id="receiptTotalAmount"><?php echo number_format($receipt_total, 2); ?></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="lbl" style="color:#999;font-weight:500;font-size:13px;margin-bottom:6px;"><?php echo lang('cash_receipt_description'); ?></div>
                        <div style="background:#fafbfc;border:1px solid #eee;border-radius:6px;padding:10px 12px;min-height:70px;">
                            <?php echo nl2br(htmlspecialchars($receipt->description, ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <form method="post" action="<?php echo site_url(current_lang() . '/cash_receipt/link_loan_ledger/' . $id); ?>" id="linkLoanLedgerForm">
        <div class="cbu-panel">
            <div class="panel-head">
                <h4><?php echo lang('cash_receipt_loan_panel'); ?></h4>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label><?php echo lang('cash_receipt_search_member'); ?></label>
                    <div class="input-group">
                        <input type="text" id="member_search_key" class="form-control" placeholder="<?php echo htmlspecialchars(lang('cash_receipt_search_placeholder_member'), ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($selected_received_from, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-primary" id="btnSearchMember"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                    <input type="hidden" name="member_pid" id="member_pid" value="<?php echo htmlspecialchars($selected_member_pid, ENT_QUOTES, 'UTF-8'); ?>"/>
                    <input type="hidden" name="received_from" id="received_from" value="<?php echo htmlspecialchars($selected_received_from, ENT_QUOTES, 'UTF-8'); ?>"/>
                    <div id="memberSearchResults" class="search-results"></div>
                    <p class="help-block" id="selectedMemberLabel" style="margin-top:8px;"></p>
                </div>

                <div class="loan-repayment-box" id="loanRepaymentPanel" style="display:none;">
                    <p class="help-block" style="margin-top:0;"><?php echo lang('cash_receipt_loan_list_hint'); ?></p>
                    <div id="loanRepaymentPanelBody"></div>
                    <div id="loanAllocHiddenInputs"></div>
                    <p id="allocMatchMsg" style="margin-top:10px;"></p>
                </div>
            </div>
        </div>

        <div class="text-right" style="margin-bottom:24px;">
            <a href="<?php echo $view_url; ?>" class="btn btn-default"><?php echo lang('cancel'); ?></a>
            <button type="submit" class="btn btn-primary" id="btnLinkLoanLedger" disabled="disabled">
                <i class="fa fa-link"></i> <?php echo lang('cash_receipt_link_loan_ledger'); ?>
            </button>
        </div>
    </form>
</div>

<script type="text/javascript">
(function($){
    var memberSearchUrl = <?php echo json_encode(site_url(current_lang() . '/cash_receipt/search_member')); ?>;
    var memberLoansUrl = <?php echo json_encode(site_url(current_lang() . '/cash_receipt/member_repayable_loans')); ?>;
    var receiptExcludeId = <?php echo (int) decode_id($id); ?>;
    var receiptTotal = <?php echo json_encode($receipt_total); ?>;
    var preselectRows = <?php echo json_encode(array_values($preselect)); ?>;
    var initialPid = <?php echo json_encode($selected_member_pid); ?>;
    var i18n = {
        noLoans: <?php echo json_encode(lang('cash_receipt_no_repayable_loans')); ?>,
        paymentAmount: <?php echo json_encode(lang('cash_receipt_loan_payment_amount')); ?>,
        selectedTotal: <?php echo json_encode(lang('cash_receipt_loan_selected_total')); ?>,
        overrideToggle: <?php echo json_encode(lang('cash_receipt_loan_override_toggle')); ?>,
        overrideHelp: <?php echo json_encode(lang('cash_receipt_loan_override_help')); ?>,
        overridePrincipal: <?php echo json_encode(lang('cash_receipt_loan_override_principal')); ?>,
        overrideInterest: <?php echo json_encode(lang('cash_receipt_loan_override_interest')); ?>,
        overridePenalty: <?php echo json_encode(lang('cash_receipt_loan_override_penalty')); ?>,
        overrideNote: <?php echo json_encode(lang('cash_receipt_loan_override_note')); ?>,
        confirmLink: <?php echo json_encode(lang('cash_receipt_link_loan_confirm')); ?>,
        selected: <?php echo json_encode(lang('cash_receipt_selected_payer')); ?>
    };
    var loanCache = {};

    function formatMoney(n) {
        var x = parseFloat(n);
        if (isNaN(x)) { return ''; }
        return x.toFixed(2);
    }
    function roundMoney(n) {
        return Math.round((parseFloat(n) || 0) * 100) / 100;
    }
    function preselectMap() {
        var map = {};
        $.each(preselectRows || [], function(_, row){
            if (row && row.LID) { map[row.LID] = row; }
        });
        return map;
    }

    function searchMembers() {
        var key = $.trim($('#member_search_key').val() || '');
        if (key.length < 2) {
            $('#memberSearchResults').html('<p class="text-danger"><?php echo htmlspecialchars(lang('cash_receipt_search_min_chars'), ENT_QUOTES, 'UTF-8'); ?></p>');
            return;
        }
        $('#memberSearchResults').html('<p><i class="fa fa-spinner fa-spin"></i></p>');
        $.getJSON(memberSearchUrl, { key: key }, function(response){
            if (response.success !== 'Y' || !response.data || !response.data.length) {
                $('#memberSearchResults').html('<p class="text-danger">' + (response.error || <?php echo json_encode(lang('cash_receipt_no_members_found')); ?>) + '</p>');
                return;
            }
            var html = '<table class="table table-condensed table-bordered" style="background:#fff;margin:0;"><thead><tr><th>ID</th><th>Name</th><th></th></tr></thead><tbody>';
            $.each(response.data, function(_, m){
                html += '<tr>';
                html += '<td>' + (m.member_id || '') + '</td>';
                html += '<td>' + (m.fullname || '') + '</td>';
                html += '<td><button type="button" class="btn btn-xs btn-primary btn-select-member" data-pid="' + (m.PID || '') + '" data-name="' + String(m.fullname || '').replace(/"/g, '&quot;') + '"><i class="fa fa-check"></i> Select</button></td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            $('#memberSearchResults').html(html);
        }).fail(function(){
            $('#memberSearchResults').html('<p class="text-danger">Search failed.</p>');
        });
    }

    function loadMemberLoans(pid) {
        $('#loanRepaymentPanel').show();
        $('#loanRepaymentPanelBody').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i></p>');
        $.getJSON(memberLoansUrl, { pid: pid, exclude_id: receiptExcludeId || 0 }, function(response){
            if (response.success === 'Y' && response.data && response.data.length) {
                renderLoanAllocationTable(response.data);
            } else {
                $('#loanRepaymentPanelBody').html('<p class="text-danger">' + (response.error || i18n.noLoans) + '</p>');
                syncHidden();
            }
        }).fail(function(){
            $('#loanRepaymentPanelBody').html('<p class="text-danger">Error loading loans.</p>');
        });
    }

    function getRowOverride($ovRow) {
        if (!$ovRow.length || !$ovRow.find('.loan-override-enabled').is(':checked')) {
            return null;
        }
        return {
            enabled: 1,
            principal: roundMoney($ovRow.find('.loan-override-principal').val()),
            interest: roundMoney($ovRow.find('.loan-override-interest').val()),
            penalty: roundMoney($ovRow.find('.loan-override-penalty').val()),
            note: $.trim($ovRow.find('.loan-override-note').val() || '')
        };
    }

    function renderLoanAllocationTable(rows) {
        var map = preselectMap();
        var html = '<div class="table-responsive"><table class="table table-bordered table-condensed" id="loanAllocTable" style="background:#fff;margin-bottom:8px;">';
        html += '<thead><tr><th style="width:36px;"></th><th>Loan</th><th>Product</th><th>Status</th>';
        html += '<th class="text-right">Outstanding</th><th class="text-right">Amount Due</th>';
        html += '<th class="text-right">' + i18n.paymentAmount + '</th></tr></thead><tbody>';
        $.each(rows || [], function(_, row){
            loanCache[row.LID] = row;
            var lid = row.LID || '';
            var pre = map[lid] || null;
            var checked = !!pre;
            var amountVal = pre ? formatMoney(pre.amount) : (row.amount_due > 0 ? formatMoney(row.amount_due) : '');
            var ov = pre && pre.override_enabled ? {
                principal: pre.override_principal || 0,
                interest: pre.override_interest || 0,
                penalty: pre.override_penalty || 0,
                note: pre.override_note || ''
            } : null;
            if (ov) {
                amountVal = formatMoney(roundMoney(ov.principal + ov.interest + ov.penalty));
                checked = true;
            }
            html += '<tr class="loan-alloc-row' + (checked ? ' success' : '') + '" data-loan-id="' + lid + '">';
            html += '<td class="text-center"><input type="checkbox" class="loan-alloc-cb"' + (checked ? ' checked="checked"' : '') + '/></td>';
            html += '<td>' + lid + '</td><td>' + (row.product_name || '') + '</td><td>' + (row.status_name || '') + '</td>';
            html += '<td class="text-right">' + formatMoney(row.outstanding) + '</td>';
            html += '<td class="text-right">' + formatMoney(row.amount_due) + '</td>';
            html += '<td><input type="number" step="0.01" min="0" class="form-control input-sm text-right loan-alloc-amount" value="' + amountVal + '" ' + (checked ? '' : 'disabled="disabled"') + (ov ? ' readonly="readonly"' : '') + '/></td>';
            html += '</tr>';
            html += '<tr class="loan-override-row" data-loan-id="' + lid + '" style="' + (checked ? '' : 'display:none;') + '">';
            html += '<td></td><td colspan="6" style="background:#fffdf5;">';
            html += '<label style="font-weight:600;margin-right:10px;"><input type="checkbox" class="loan-override-enabled"' + (ov ? ' checked="checked"' : '') + '/> ' + i18n.overrideToggle + '</label>';
            html += '<span class="text-muted" style="font-size:12px;">' + i18n.overrideHelp + '</span>';
            html += '<div class="loan-override-fields" style="margin-top:8px;' + (ov ? '' : 'display:none;') + '">';
            html += '<div class="row">';
            html += '<div class="col-sm-3"><label>' + i18n.overridePrincipal + '</label><input type="number" step="0.01" min="0" class="form-control input-sm loan-override-principal" value="' + (ov ? formatMoney(ov.principal) : '') + '"/></div>';
            html += '<div class="col-sm-3"><label>' + i18n.overrideInterest + '</label><input type="number" step="0.01" min="0" class="form-control input-sm loan-override-interest" value="' + (ov ? formatMoney(ov.interest) : '') + '"/></div>';
            html += '<div class="col-sm-3"><label>' + i18n.overridePenalty + '</label><input type="number" step="0.01" min="0" class="form-control input-sm loan-override-penalty" value="' + (ov ? formatMoney(ov.penalty) : '') + '"/></div>';
            html += '<div class="col-sm-3"><label>' + i18n.overrideNote + '</label><input type="text" maxlength="255" class="form-control input-sm loan-override-note" value="' + (ov && ov.note ? String(ov.note).replace(/"/g, '&quot;') : '') + '"/></div>';
            html += '</div></div></td></tr>';
        });
        html += '</tbody><tfoot><tr><th colspan="6" class="text-right">' + i18n.selectedTotal + '</th>';
        html += '<th class="text-right" id="loanAllocSelectedTotal">0.00</th></tr></tfoot></table></div>';
        $('#loanRepaymentPanelBody').html(html);
        syncHidden();
    }

    function getSelectedAllocations() {
        var out = [];
        $('#loanAllocTable tbody tr.loan-alloc-row').each(function(){
            var $row = $(this);
            if (!$row.find('.loan-alloc-cb').is(':checked')) { return; }
            var lid = String($row.data('loanId') || '');
            var $ovRow = $('#loanAllocTable tr.loan-override-row[data-loan-id="' + lid + '"]');
            var override = getRowOverride($ovRow);
            if (override) {
                var ovAmount = roundMoney(override.principal + override.interest + override.penalty);
                $row.find('.loan-alloc-amount').val(formatMoney(ovAmount));
                out.push({ LID: lid, amount: ovAmount, override: override });
                return;
            }
            var amount = roundMoney($row.find('.loan-alloc-amount').val());
            if (lid && amount > 0) {
                out.push({ LID: lid, amount: amount });
            }
        });
        return out;
    }

    function syncHidden() {
        var allocs = getSelectedAllocations();
        var html = '';
        var total = 0;
        $.each(allocs, function(_, row){
            total += row.amount;
            html += '<input type="hidden" name="loan_alloc_lid[]" value="' + String(row.LID).replace(/"/g, '&quot;') + '"/>';
            html += '<input type="hidden" name="loan_alloc_amount[]" value="' + formatMoney(row.amount) + '"/>';
            if (row.override && row.override.enabled) {
                html += '<input type="hidden" name="loan_override_enabled[]" value="1"/>';
                html += '<input type="hidden" name="loan_override_principal[]" value="' + formatMoney(row.override.principal) + '"/>';
                html += '<input type="hidden" name="loan_override_interest[]" value="' + formatMoney(row.override.interest) + '"/>';
                html += '<input type="hidden" name="loan_override_penalty[]" value="' + formatMoney(row.override.penalty) + '"/>';
                html += '<input type="hidden" name="loan_override_note[]" value="' + String(row.override.note || '').replace(/"/g, '&quot;') + '"/>';
            } else {
                html += '<input type="hidden" name="loan_override_enabled[]" value="0"/>';
                html += '<input type="hidden" name="loan_override_principal[]" value="0"/>';
                html += '<input type="hidden" name="loan_override_interest[]" value="0"/>';
                html += '<input type="hidden" name="loan_override_penalty[]" value="0"/>';
                html += '<input type="hidden" name="loan_override_note[]" value=""/>';
            }
        });
        $('#loanAllocHiddenInputs').html(html);
        total = roundMoney(total);
        $('#loanAllocSelectedTotal').text(formatMoney(total));
        var ok = allocs.length > 0 && Math.abs(total - receiptTotal) <= 0.009;
        $('#btnLinkLoanLedger').prop('disabled', !ok);
        if (!allocs.length) {
            $('#allocMatchMsg').removeClass('alloc-ok alloc-mismatch').text('');
        } else if (ok) {
            $('#allocMatchMsg').removeClass('alloc-mismatch').addClass('alloc-ok').text('Allocated total matches receipt total.');
        } else {
            $('#allocMatchMsg').removeClass('alloc-ok').addClass('alloc-mismatch').text('Allocated ' + formatMoney(total) + ' must equal receipt total ' + formatMoney(receiptTotal) + '.');
        }
        return allocs;
    }

    $('#btnSearchMember').on('click', searchMembers);
    $('#member_search_key').on('keypress', function(e){
        if (e.which === 13) { e.preventDefault(); searchMembers(); }
    });

    $(document).on('click', '.btn-select-member', function(){
        var pid = $(this).data('pid') || '';
        var name = $(this).data('name') || '';
        $('#member_pid').val(pid);
        $('#received_from').val(name);
        $('#member_search_key').val(name);
        $('#selectedMemberLabel').text(i18n.selected + ': ' + name);
        $('#memberSearchResults').html('');
        loadMemberLoans(pid);
    });

    $(document).on('change', '.loan-alloc-cb', function(){
        var $row = $(this).closest('tr');
        var lid = $row.data('loanId');
        var checked = $(this).is(':checked');
        $row.toggleClass('success', checked);
        $row.find('.loan-alloc-amount').prop('disabled', !checked);
        var $ov = $('#loanAllocTable tr.loan-override-row[data-loan-id="' + lid + '"]');
        $ov.toggle(checked);
        if (!checked) {
            $ov.find('.loan-override-enabled').prop('checked', false);
            $ov.find('.loan-override-fields').hide();
            $row.find('.loan-alloc-amount').prop('readonly', false);
        }
        syncHidden();
    });
    $(document).on('input change', '.loan-alloc-amount', syncHidden);
    $(document).on('change', '.loan-override-enabled', function(){
        var $ov = $(this).closest('tr');
        var lid = $ov.data('loanId');
        var on = $(this).is(':checked');
        $ov.find('.loan-override-fields').toggle(on);
        var $row = $('#loanAllocTable tr.loan-alloc-row[data-loan-id="' + lid + '"]');
        $row.find('.loan-alloc-amount').prop('readonly', on);
        syncHidden();
    });
    $(document).on('input change', '.loan-override-principal, .loan-override-interest, .loan-override-penalty, .loan-override-note', syncHidden);

    $('#linkLoanLedgerForm').on('submit', function(){
        syncHidden();
        if ($('#btnLinkLoanLedger').prop('disabled')) {
            return false;
        }
        return window.confirm(i18n.confirmLink);
    });

    if (initialPid) {
        $('#selectedMemberLabel').text(i18n.selected + ': ' + ($('#received_from').val() || ''));
        loadMemberLoans(initialPid);
    }
    // Do not auto-search the full "Received From" name on load — that used to show
    // "No members found" when middlename was empty. User can still search manually.
})(jQuery);
</script>
