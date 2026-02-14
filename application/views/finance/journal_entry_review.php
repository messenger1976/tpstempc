<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

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

<div class="alert alert-info" style="margin-bottom: 15px;">
    <i class="fa fa-info-circle"></i> <strong>Where to view posted transactions:</strong> Cash Receipt and Cash Disbursement entries (after posting) appear in <strong>Journal Entries / General Journal</strong> (journal 5).
    <a href="<?php echo site_url(current_lang() . '/report/journal_entry/5'); ?>" class="alert-link">View Report &raquo;</a>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Journal Entry Review & Approval</h5>
                    <div class="ibox-tools">
                        <?php if (has_role(6, 'Journal_entry')) { ?>
                            <a href="<?php echo site_url(current_lang() . '/finance/journalentry'); ?>" class="btn btn-primary btn-xs">
                                <i class="fa fa-plus"></i> Create New Journal Entry
                            </a>
                            <!-- Test link to view entry 32800 directly -->
                            <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_view/' . encode_id(32800)); ?>" class="btn btn-info btn-xs" style="margin-left: 5px;">
                                <i class="fa fa-eye"></i> Test: View Entry 32800
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <div class="ibox-content">
                    <?php if (!empty($unposted_entries)): ?>
                        <form method="post" action="<?php echo site_url(current_lang() . '/finance/journal_entry_batch_approve'); ?>" id="approveForm" onsubmit="return confirm('Are you sure you want to approve and post the selected journal entries?');">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover dataTables-example">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;">
                                                <input type="checkbox" id="selectAll" title="Select All (JV only)"/>
                                            </th>
                                            <th>Entry ID</th>
                                            <th>Source</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Created By</th>
                                            <th>Line Items</th>
                                            <th style="text-align: right;">Total Debit</th>
                                            <th style="text-align: right;">Total Credit</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($unposted_entries as $entry): 
                                            $entry_source = isset($entry->entry_source) ? $entry->entry_source : 'general_journal';
                                            $is_general = ($entry_source === 'general_journal');
                                            $view_url = current_lang() . '/finance/journal_entry_view/' . encode_id($entry->entryid);
                                            if ($entry_source === 'cash_disbursement' && isset($entry->reference_id)) {
                                                $view_url = current_lang() . '/cash_disbursement/cash_disbursement_view/' . encode_id($entry->reference_id);
                                            } elseif ($entry_source === 'cash_receipt' && isset($entry->reference_id)) {
                                                $view_url = current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($entry->reference_id);
                                            }
                                            $source_label = function_exists('journal_source_label') ? journal_source_label($entry_source) : $entry_source;
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php if ($is_general): ?>
                                                        <input type="checkbox" name="entry_ids[]" value="<?php echo encode_id($entry->entryid); ?>" class="entry-checkbox"/>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $entry->entryid; ?></td>
                                                <td><span class="label label-default"><?php echo htmlspecialchars($source_label); ?></span></td>
                                                <td><?php echo date('M d, Y', strtotime($entry->entrydate)); ?></td>
                                                <td><?php echo htmlspecialchars($entry->description); ?></td>
                                                <td><?php echo htmlspecialchars($entry->created_by_name); ?></td>
                                                <td style="text-align: center;"><?php echo isset($entry->line_count) ? $entry->line_count : 0; ?></td>
                                                <td style="text-align: right;"><?php echo number_format(isset($entry->total_debit) ? $entry->total_debit : 0, 2); ?></td>
                                                <td style="text-align: right;"><?php echo number_format(isset($entry->total_credit) ? $entry->total_credit : 0, 2); ?></td>
                                                <td>
                                                    <?php if (abs($entry->total_debit - $entry->total_credit) <= 0.01): ?>
                                                        <span class="label label-success">Balanced</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Unbalanced</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo site_url($view_url); ?>" class="btn btn-info btn-xs" title="View Details">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                    <?php if ($is_general && abs($entry->total_debit - $entry->total_credit) <= 0.01): ?>
                                                        <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_approve/' . encode_id($entry->entryid)); ?>" 
                                                           onclick="return confirm('Are you sure you want to approve and post this journal entry?');" 
                                                           class="btn btn-success btn-xs" title="Approve & Post">
                                                            <i class="fa fa-check"></i> Approve
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php
                                                    $is_receipt_disburse = in_array($entry_source, array('cash_receipt', 'cash_disbursement'), true);
                                                    $can_post_to_gl = $is_receipt_disburse && !$entry->is_posted && abs($entry->total_debit - $entry->total_credit) <= 0.01;
                                                    if ($can_post_to_gl): ?>
                                                        <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_post_to_gl/' . encode_id($entry->entryid)); ?>" 
                                                           onclick="return confirm('Post this entry to the General Ledger?');" 
                                                           class="btn btn-success btn-xs" title="Post to GL">
                                                            <i class="fa fa-book"></i> Post to GL
                                                        </a>
                                                    <?php elseif ($is_receipt_disburse && !empty($entry->is_posted)): ?>
                                                        <span class="label label-default">Posted to GL</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-success" id="batchApproveBtn" disabled>
                                    <i class="fa fa-check"></i> Approve Selected Entries
                                </button>
                                <span id="selectedCount" style="margin-left: 10px;"></span>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> All journal entries have been posted. No entries pending approval.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTables if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.dataTables-example').DataTable({
            pageLength: 25,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: []
        });
    }

    // Select All checkbox
    $('#selectAll').on('change', function() {
        $('.entry-checkbox').prop('checked', $(this).prop('checked'));
        updateBatchButton();
    });

    // Individual checkbox change
    $('.entry-checkbox').on('change', function() {
        updateBatchButton();
        var checkedCount = $('.entry-checkbox:checked').length;
        var totalCount = $('.entry-checkbox').length;
        
        if (checkedCount === totalCount) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });

    function updateBatchButton() {
        var checkedCount = $('.entry-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#batchApproveBtn').prop('disabled', false);
            $('#selectedCount').text('(' + checkedCount + ' entry/entries selected)');
        } else {
            $('#batchApproveBtn').prop('disabled', true);
            $('#selectedCount').text('');
        }
    }
});
</script>
