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
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-sm-4 col-md-3">
                            <label for="sourceFilter">Source</label>
                            <select id="sourceFilter" class="form-control">
                                <option value="all" selected>All</option>
                                <option value="general_journal"><?php echo htmlspecialchars(function_exists('journal_source_label') ? journal_source_label('general_journal') : 'Journal Entry'); ?></option>
                                <option value="cash_receipt"><?php echo htmlspecialchars(function_exists('journal_source_label') ? journal_source_label('cash_receipt') : 'Cash Receipt'); ?></option>
                                <option value="cash_disbursement"><?php echo htmlspecialchars(function_exists('journal_source_label') ? journal_source_label('cash_disbursement') : 'Cash Disbursement'); ?></option>
                            </select>
                        </div>
                    </div>
                    <form method="post" action="<?php echo site_url(current_lang() . '/finance/journal_entry_batch_approve'); ?>" id="approveForm" onsubmit="return confirm('Are you sure you want to approve and post the selected journal entries?');">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example" id="unpostedJournalReviewTable" style="width:100%;">
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
                                <tbody></tbody>
                                <tfoot>
                                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                                        <td colspan="7" style="text-align: right;"><strong>Grand Total:</strong></td>
                                        <td id="grandTotalDebit" style="text-align: right;"><strong>0.00</strong></td>
                                        <td id="grandTotalCredit" style="text-align: right;"><strong>0.00</strong></td>
                                        <td colspan="2" id="grandTotalStatus"></td>
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

    function loadScript(src, cb) {
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        s.onerror = function() { if (typeof cb === 'function') cb(); };
        document.head.appendChild(s);
    }

    function initUnpostedTable() {
        var unpostedAjaxUrl = '<?php echo site_url(current_lang() . '/finance/journal_entry_review_unposted_data'); ?>';

        function formatMoney(value) {
            var num = parseFloat(value);
            if (isNaN(num)) num = 0;
            return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function updateGrandTotals(debit, credit) {
            var d = parseFloat(debit) || 0;
            var c = parseFloat(credit) || 0;
            jQuery('#grandTotalDebit').html('<strong>' + formatMoney(d) + '</strong>');
            jQuery('#grandTotalCredit').html('<strong>' + formatMoney(c) + '</strong>');
            var balanced = Math.abs(d - c) <= 0.01;
            jQuery('#grandTotalStatus').html(
                balanced
                    ? '<span class="label label-success">Balanced</span>'
                    : '<span class="label label-danger">Unbalanced</span>'
            );
        }

        function updateBatchButton() {
            var checkedCount = jQuery('#approveForm .entry-checkbox:checked').length;
            if (checkedCount > 0) {
                jQuery('#batchApproveBtn').prop('disabled', false);
                jQuery('#selectedCount').text('(' + checkedCount + ' entry/entries selected)');
            } else {
                jQuery('#batchApproveBtn').prop('disabled', true);
                jQuery('#selectedCount').text('');
            }
        }

        var unpostedTable = jQuery('#unpostedJournalReviewTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            responsive: true,
            order: [[3, 'desc']],
            ajax: {
                url: unpostedAjaxUrl,
                type: 'POST',
                data: function(d) {
                    d.source_filter = jQuery('#sourceFilter').val() || 'all';
                },
                dataSrc: function(json) {
                    if (json && json.grand_total_debit !== undefined && json.grand_total_credit !== undefined) {
                        updateGrandTotals(json.grand_total_debit, json.grand_total_credit);
                    }
                    return (json && json.data) ? json.data : [];
                },
                error: function(xhr, error, thrown) {
                    console.error('Journal review DataTables error:', error, thrown, xhr && xhr.responseText);
                }
            },
            columnDefs: [
                { orderable: false, searchable: false, targets: [0, 10] },
                { className: 'text-right', targets: [7, 8] },
                { className: 'text-center', targets: [6] }
            ],
            dom: 'lfrtip',
            drawCallback: function() {
                jQuery('#selectAll').prop('checked', false);
                updateBatchButton();
            },
            language: {
                emptyTable: 'All journal entries have been posted. No entries pending approval.',
                zeroRecords: 'No matching journal entries found.',
                processing: 'Loading...'
            }
        });

        jQuery('#sourceFilter').on('change', function() {
            unpostedTable.ajax.reload();
        });

        jQuery('#selectAll').on('change', function() {
            var checked = jQuery(this).prop('checked');
            jQuery('#approveForm .entry-checkbox').prop('checked', checked);
            updateBatchButton();
        });

        jQuery('#approveForm').on('change', '.entry-checkbox', function() {
            var checkedCount = jQuery('#approveForm .entry-checkbox:checked').length;
            var totalCount = jQuery('#approveForm .entry-checkbox').length;
            jQuery('#selectAll').prop('checked', totalCount > 0 && checkedCount === totalCount);
            updateBatchButton();
        });
    }

    function tryInit() {
        if (window.jQuery) {
            if (!window.jQuery.fn || !window.jQuery.fn.DataTable) {
                loadScript('<?php echo base_url(); ?>assets/js/plugins/dataTables/datatables.min.js', function() {
                    initUnpostedTable();
                });
            } else {
                initUnpostedTable();
            }
        } else {
            setTimeout(tryInit, 50);
        }
    }

    tryInit();
})();
</script>
