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
                                        <?php 
                                        $grand_total_debit = 0;
                                        $grand_total_credit = 0;
                                        foreach ($unposted_entries as $entry): 
                                            $entry_source = isset($entry->entry_source) ? $entry->entry_source : 'general_journal';
                                            $is_general = ($entry_source === 'general_journal');
                                            $view_url = current_lang() . '/finance/journal_entry_view/' . encode_id($entry->entryid);
                                            if ($entry_source === 'cash_disbursement' && isset($entry->reference_id)) {
                                                $view_url = current_lang() . '/cash_disbursement/cash_disbursement_view/' . encode_id($entry->reference_id);
                                            } elseif ($entry_source === 'cash_receipt' && isset($entry->reference_id)) {
                                                $view_url = current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($entry->reference_id);
                                            }
                                            $source_label = function_exists('journal_source_label') ? journal_source_label($entry_source) : $entry_source;
                                            
                                            // Calculate totals
                                            $entry_debit = isset($entry->total_debit) ? floatval($entry->total_debit) : 0;
                                            $entry_credit = isset($entry->total_credit) ? floatval($entry->total_credit) : 0;
                                            $grand_total_debit += $entry_debit;
                                            $grand_total_credit += $entry_credit;
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
                                                <td style="text-align: right;"><?php echo number_format($entry_debit, 2); ?></td>
                                                <td style="text-align: right;"><?php echo number_format($entry_credit, 2); ?></td>
                                                <td>
                                                    <?php if (abs($entry_debit - $entry_credit) <= 0.01): ?>
                                                        <span class="label label-success">Balanced</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Unbalanced</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo site_url($view_url); ?>" class="btn btn-info btn-xs" title="View Details">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                    <?php if ($is_general && abs($entry_debit - $entry_credit) <= 0.01): ?>
                                                        <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_approve/' . encode_id($entry->entryid)); ?>" 
                                                           onclick="return confirm('Are you sure you want to approve and post this journal entry?');" 
                                                           class="btn btn-success btn-xs" title="Approve & Post">
                                                            <i class="fa fa-check"></i> Approve
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php
                                                    $is_receipt_disburse = in_array($entry_source, array('cash_receipt', 'cash_disbursement'), true);
                                                    $can_post_to_gl = $is_receipt_disburse && !$entry->is_posted && abs($entry_debit - $entry_credit) <= 0.01;
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
                                    <tfoot>
                                        <tr style="background-color: #f5f5f5; font-weight: bold;">
                                            <td colspan="7" style="text-align: right;"><strong>Grand Total:</strong></td>
                                            <td style="text-align: right;"><strong><?php echo number_format($grand_total_debit, 2); ?></strong></td>
                                            <td style="text-align: right;"><strong><?php echo number_format($grand_total_credit, 2); ?></strong></td>
                                            <td colspan="2">
                                                <?php if (abs($grand_total_debit - $grand_total_credit) <= 0.01): ?>
                                                    <span class="label label-success">Balanced</span>
                                                <?php else: ?>
                                                    <span class="label label-danger">Unbalanced</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tfoot>
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

                    <?php if (!empty($posted_entries)): ?>
                        <hr style="margin: 30px 0;">
                        <h4><i class="fa fa-check-circle"></i> Posted to General Ledger</h4>
                        <p class="text-muted">These entries are already posted. Select rows and use "Void GL Posting (Selected)" to void multiple at once, or void one via the action button.</p>
                        <form method="post" action="<?php echo site_url(current_lang() . '/finance/void_gl_posting_batch'); ?>" id="voidGlForm" onsubmit="return confirm('Void GL posting for the selected entries? Journal entries will stay and can be reposted later.');">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="postedEntriesTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 32px;">
                                                <input type="checkbox" id="selectAllPosted" title="Select all posted entries"/>
                                            </th>
                                            <th>Entry ID</th>
                                            <th>Source</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Created By</th>
                                            <th>Line Items</th>
                                            <th style="text-align: right;">Total Debit</th>
                                            <th style="text-align: right;">Total Credit</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($posted_entries as $entry):
                                            $entry_source = isset($entry->entry_source) ? $entry->entry_source : 'general_journal';
                                            $is_general = ($entry_source === 'general_journal');
                                            $view_url = current_lang() . '/finance/journal_entry_view/' . encode_id($entry->entryid);
                                            if ($entry_source === 'cash_disbursement' && isset($entry->reference_id)) {
                                                $view_url = current_lang() . '/cash_disbursement/cash_disbursement_view/' . encode_id($entry->reference_id);
                                            } elseif ($entry_source === 'cash_receipt' && isset($entry->reference_id)) {
                                                $view_url = current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($entry->reference_id);
                                            }
                                            $source_label = function_exists('journal_source_label') ? journal_source_label($entry_source) : $entry_source;
                                            $entry_debit = isset($entry->total_debit) ? floatval($entry->total_debit) : 0;
                                            $entry_credit = isset($entry->total_credit) ? floatval($entry->total_credit) : 0;
                                            $void_value = $entry_source . '::' . encode_id($entry->entryid);
                                        ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="void_ids[]" value="<?php echo htmlspecialchars($void_value); ?>" class="void-checkbox"/>
                                                </td>
                                                <td><?php echo $entry->entryid; ?></td>
                                                <td><span class="label label-default"><?php echo htmlspecialchars($source_label); ?></span></td>
                                                <td><?php echo date('M d, Y', strtotime($entry->entrydate)); ?></td>
                                                <td><?php echo htmlspecialchars($entry->description); ?></td>
                                                <td><?php echo htmlspecialchars($entry->created_by_name); ?></td>
                                                <td style="text-align: center;"><?php echo isset($entry->line_count) ? $entry->line_count : 0; ?></td>
                                                <td style="text-align: right;"><?php echo number_format($entry_debit, 2); ?></td>
                                                <td style="text-align: right;"><?php echo number_format($entry_credit, 2); ?></td>
                                                <td>
                                                    <a href="<?php echo site_url($view_url); ?>" class="btn btn-info btn-xs" title="View"><i class="fa fa-eye"></i> View</a>
                                                    <?php if ($is_general): ?>
                                                        <a href="<?php echo site_url(current_lang() . '/finance/void_gl_posting_general/' . encode_id($entry->entryid)); ?>"
                                                           onclick="return confirm('Void the GL posting only? The journal entry will stay and you can repost later.');"
                                                           class="btn btn-warning btn-xs" title="Void GL Posting"><i class="fa fa-undo"></i> Void</a>
                                                    <?php else: ?>
                                                        <a href="<?php echo site_url(current_lang() . '/finance/void_gl_posting_journal_entry/' . encode_id($entry->entryid)); ?>"
                                                           onclick="return confirm('Void the GL posting only? The entry will stay; you can repost from this page later.');"
                                                           class="btn btn-warning btn-xs" title="Void GL Posting"><i class="fa fa-undo"></i> Void</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 15px;">
                                <button type="submit" class="btn btn-warning" id="voidGlBatchBtn" disabled>
                                    <i class="fa fa-undo"></i> Void GL Posting (Selected)
                                </button>
                                <span id="voidSelectedCount" class="text-muted" style="margin-left: 10px;"></span>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // Void GL Posted form: plain JS only so it works regardless of jQuery
    function voidFormUpdateButton() {
        var form = document.getElementById('voidGlForm');
        if (!form) return;
        var n = form.querySelectorAll('.void-checkbox:checked').length;
        var btn = form.querySelector('#voidGlBatchBtn');
        var span = form.querySelector('#voidSelectedCount');
        if (btn) btn.disabled = (n === 0);
        if (span) span.textContent = n > 0 ? '(' + n + ' selected)' : '';
    }
    function voidFormChange(ev) {
        var form = document.getElementById('voidGlForm');
        if (!form || !ev.target) return;
        var el = ev.target;
        var inside = false;
        for (var p = el; p; p = p.parentNode) { if (p === form) { inside = true; break; } }
        if (!inside) return;
        if (el.id === 'selectAllPosted') {
            var cbs = form.querySelectorAll('.void-checkbox');
            for (var i = 0; i < cbs.length; i++) cbs[i].checked = el.checked;
        } else if (el.classList && el.classList.contains('void-checkbox')) {
            var checked = form.querySelectorAll('.void-checkbox:checked').length;
            var total = form.querySelectorAll('.void-checkbox').length;
            var allCb = form.querySelector('#selectAllPosted');
            if (allCb) allCb.checked = (total > 0 && checked === total);
        }
        voidFormUpdateButton();
    }
    document.addEventListener('change', voidFormChange);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', voidFormUpdateButton);
    } else {
        voidFormUpdateButton();
    }

    if (typeof jQuery === 'undefined') return;
    jQuery(document).ready(function() {
        if (typeof jQuery.fn.DataTable !== 'undefined') {
            jQuery('.dataTables-example').DataTable({
                pageLength: 25,
                responsive: true,
                dom: '<"html5buttons"B>lTfgitp',
                buttons: [],
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            parseFloat(i.replace(/[\$,]/g, '')) :
                            typeof i === 'number' ? i : 0;
                    };
                    var totalDebit = api.column(7, { page: 'all' }).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
                    var totalCredit = api.column(8, { page: 'all' }).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
                    jQuery(api.column(7).footer()).html('<strong>' + totalDebit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '</strong>');
                    jQuery(api.column(8).footer()).html('<strong>' + totalCredit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '</strong>');
                }
            });
        }
        jQuery('#selectAll').on('change', function() {
            jQuery('.entry-checkbox').prop('checked', jQuery(this).prop('checked'));
            updateBatchButton();
        });
        jQuery('.entry-checkbox').on('change', function() {
            updateBatchButton();
            var checkedCount = jQuery('.entry-checkbox:checked').length;
            var totalCount = jQuery('.entry-checkbox').length;
            jQuery('#selectAll').prop('checked', totalCount > 0 && checkedCount === totalCount);
        });
        function updateBatchButton() {
            var checkedCount = jQuery('.entry-checkbox:checked').length;
            if (checkedCount > 0) {
                jQuery('#batchApproveBtn').prop('disabled', false);
                jQuery('#selectedCount').text('(' + checkedCount + ' entry/entries selected)');
            } else {
                jQuery('#batchApproveBtn').prop('disabled', true);
                jQuery('#selectedCount').text('');
            }
        }
    });
})();
</script>
