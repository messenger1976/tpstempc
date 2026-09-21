<?php
/**
 * Penalty / interest waiver approval list.
 *
 * Waivers are queued by the release worksheet (offset/reloan) and by the loan
 * repayment entry. Nothing posts until a Manager or Treasurer approves them, and
 * the requester can never approve their own request.
 */
$waivers = isset($waivers) ? $waivers : array();
$can_approve = !empty($can_approve);
$approve_url = site_url(current_lang() . '/loan/loan_waiver_approve/');
$reject_url = site_url(current_lang() . '/loan/loan_waiver_reject/');
$reason_labels = function_exists('loan_waiver_reason_codes') ? loan_waiver_reason_codes() : array();
?>
<style type="text/css">
.waiver-page .waiver-card { border: 1px solid #e3e8ee; border-radius: 8px; background: #fff; }
.waiver-page .waiver-card .waiver-head {
    padding: 12px 16px; border-bottom: 1px solid #e3e8ee;
    display: flex; justify-content: space-between; align-items: center;
}
.waiver-page .waiver-card .waiver-head h4 { margin: 0; font-weight: 700; }
.waiver-page table.waiver-table { margin-bottom: 0; }
.waiver-page table.waiver-table th { background: #f7fafc; white-space: nowrap; }
.waiver-page table.waiver-table td { vertical-align: middle; }
.waiver-page .waiver-amount { text-align: right; font-variant-numeric: tabular-nums; }
.waiver-page .waiver-type-penalty { color: #c0392b; font-weight: 600; }
.waiver-page .waiver-type-interest { color: #2a7ab9; font-weight: 600; }
.waiver-page .waiver-note { color: #6c757d; font-size: 12px; }
.waiver-page .waiver-source { color: #6c757d; font-size: 12px; }
</style>

<div class="waiver-page">
    <div class="row">
        <div class="col-md-12">
            <div class="waiver-card">
                <div class="waiver-head">
                    <h4><i class="fa fa-gavel"></i> <?php echo lang('loan_waiver_list_title'); ?> &mdash; <?php echo lang('loan_waiver_list_pending'); ?></h4>
                    <?php if (!$can_approve) { ?>
                    <span class="text-muted"><i class="fa fa-lock"></i> <?php echo lang('loan_waiver_not_allowed'); ?></span>
                    <?php } ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped waiver-table">
                        <thead>
                            <tr>
                                <th><?php echo lang('loan_waiver_col_type'); ?></th>
                                <th><?php echo lang('loan_waiver_col_loan'); ?></th>
                                <th><?php echo lang('loan_waiver_col_member'); ?></th>
                                <th class="waiver-amount"><?php echo lang('loan_waiver_col_assessed'); ?></th>
                                <th class="waiver-amount"><?php echo lang('loan_waiver_col_waived'); ?></th>
                                <th class="waiver-amount"><?php echo lang('loan_waiver_col_collected'); ?></th>
                                <th><?php echo lang('loan_waiver_col_reason'); ?></th>
                                <th><?php echo lang('loan_waiver_col_requested'); ?></th>
                                <th style="width:170px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($waivers)) { ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted" style="padding:28px;">
                                    <i class="fa fa-check-circle"></i> <?php echo lang('loan_waiver_list_empty'); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php foreach ($waivers as $w) {
                                $member_name = trim((string) $w->firstname . ' ' . (string) $w->middlename . ' ' . (string) $w->lastname);
                                $member_label = trim((string) $w->member_id);
                                if ($member_name !== '') {
                                    $member_label = ($member_label !== '') ? $member_label . ' - ' . $member_name : $member_name;
                                }
                                $type_class = ($w->waiver_type === 'interest') ? 'waiver-type-interest' : 'waiver-type-penalty';
                                $reason_label = isset($reason_labels[$w->reason_code]) ? $reason_labels[$w->reason_code] : $w->reason_code;
                                $source_label = ($w->source === 'repayment')
                                    ? lang('loan_waiver_source_repayment')
                                    : lang('loan_waiver_source_offset');
                            ?>
                            <tr data-waiver-id="<?php echo (int) $w->id; ?>">
                                <td>
                                    <span class="<?php echo $type_class; ?>">
                                        <?php echo ucfirst(htmlspecialchars($w->waiver_type)); ?>
                                    </span>
                                    <div class="waiver-source"><?php echo htmlspecialchars($source_label); ?></div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($w->LID); ?></strong>
                                    <?php if (!empty($w->product_name)) { ?>
                                    <div class="waiver-source"><?php echo htmlspecialchars($w->product_name); ?></div>
                                    <?php } ?>
                                    <?php if (!empty($w->ref_lid)) { ?>
                                    <div class="waiver-source">Ref: <?php echo htmlspecialchars($w->ref_lid); ?></div>
                                    <?php } ?>
                                </td>
                                <td><?php echo htmlspecialchars($member_label); ?></td>
                                <td class="waiver-amount"><?php echo number_format((float) $w->assessed, 2); ?></td>
                                <td class="waiver-amount"><strong><?php echo number_format((float) $w->waived, 2); ?></strong></td>
                                <td class="waiver-amount"><?php echo number_format((float) $w->collected, 2); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($reason_label); ?>
                                    <?php if (!empty($w->reason_note)) { ?>
                                    <div class="waiver-note"><?php echo htmlspecialchars($w->reason_note); ?></div>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(trim((string) $w->requested_by_name)); ?>
                                    <div class="waiver-note"><?php echo htmlspecialchars((string) $w->requestedon); ?></div>
                                </td>
                                <td class="text-right">
                                    <?php if ($can_approve) { ?>
                                    <button type="button" class="btn btn-success btn-xs waiver-approve">
                                        <i class="fa fa-check"></i> <?php echo lang('loan_waiver_approve'); ?>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-xs waiver-reject">
                                        <i class="fa fa-times"></i> <?php echo lang('loan_waiver_reject'); ?>
                                    </button>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var approveUrl = <?php echo json_encode($approve_url); ?>;
    var rejectUrl = <?php echo json_encode($reject_url); ?>;
    var messages = {
        approveConfirm: <?php echo json_encode(lang('loan_waiver_approve')); ?>,
        rejectConfirm: <?php echo json_encode(lang('loan_waiver_reject')); ?>,
        rejectPrompt: <?php echo json_encode(lang('loan_waiver_note')); ?>,
        ok: <?php echo json_encode(lang('loan_waiver_approved_ok')); ?>,
        failed: <?php echo json_encode(lang('loan_waiver_approved_ok') === 'loan_waiver_approved_ok' ? 'Action failed.' : 'Action failed.'); ?>
    };

    function post(url, data, $row, doneMessage) {
        var $buttons = $row.find('button');
        $buttons.prop('disabled', true);
        $.post(url, data)
            .done(function (res) {
                if (res && res.success) {
                    $row.fadeOut(200, function () { $(this).remove(); });
                    if (typeof toastr !== 'undefined') { toastr.success(doneMessage); }
                } else {
                    $buttons.prop('disabled', false);
                    alert((res && res.warning) ? res.warning : messages.failed);
                }
            })
            .fail(function () {
                $buttons.prop('disabled', false);
                alert(messages.failed);
            });
    }

    $(document).on('click', '.waiver-approve', function () {
        var $row = $(this).closest('tr');
        var id = $row.data('waiver-id');
        if (!confirm(messages.approveConfirm + '?')) { return; }
        post(approveUrl + id, {}, $row, messages.ok);
    });

    $(document).on('click', '.waiver-reject', function () {
        var $row = $(this).closest('tr');
        var id = $row.data('waiver-id');
        var note = window.prompt(messages.rejectPrompt + ':');
        if (note === null) { return; }
        post(rejectUrl + id, { note: note }, $row, <?php echo json_encode(lang('loan_waiver_rejected_ok')); ?>);
    });
})();
</script>
