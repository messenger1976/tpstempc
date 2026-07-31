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
                    <ul class="nav nav-tabs" role="tablist" id="unpostedSourceTabs" style="margin-bottom: 15px;">
                        <?php
                        $unposted_counts = isset($unposted_source_counts) ? $unposted_source_counts : array(
                            'all' => 0,
                            'general_journal' => 0,
                            'cash_receipt' => 0,
                            'cash_disbursement' => 0,
                        );
                        $unposted_tab_defs = array(
                            'all' => 'All',
                            'general_journal' => (function_exists('journal_source_label') ? journal_source_label('general_journal') : 'Journal Entry'),
                            'cash_receipt' => (function_exists('journal_source_label') ? journal_source_label('cash_receipt') : 'Cash Receipt'),
                            'cash_disbursement' => (function_exists('journal_source_label') ? journal_source_label('cash_disbursement') : 'Cash Disbursement'),
                        );
                        foreach ($unposted_tab_defs as $tab_key => $tab_label):
                        ?>
                            <li role="presentation" class="<?php echo ($tab_key === 'all') ? 'active' : ''; ?>">
                                <a href="javascript:void(0);" role="tab" data-source-filter="<?php echo htmlspecialchars($tab_key); ?>">
                                    <?php echo htmlspecialchars($tab_label); ?>
                                    <span class="badge unposted-source-count" data-source="<?php echo htmlspecialchars($tab_key); ?>"><?php echo (int) (isset($unposted_counts[$tab_key]) ? $unposted_counts[$tab_key] : 0); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <input type="hidden" id="sourceFilter" value="all"/>
                    <div id="unpostedTableProgress" style="display:none; margin-bottom: 12px;">
                        <p id="unpostedProgressText" class="text-muted" style="margin-bottom: 6px;">
                            <i class="fa fa-spinner fa-spin"></i> Loading entries...
                        </p>
                        <div class="progress" style="height: 20px; margin-bottom: 0;">
                            <div id="unpostedProgressBar" class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" style="width: 10%; min-width: 10%;">10%</div>
                        </div>
                    </div>
                    <form method="post" action="<?php echo site_url(current_lang() . '/finance/journal_entry_batch_approve'); ?>" id="approveForm" onsubmit="return confirm('Are you sure you want to approve and post the selected journal entries?');">
                        <div class="table-responsive" style="position: relative;">
                            <div id="unpostedTableBusyOverlay" style="display:none; position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.65); z-index:20;"></div>
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

                    <?php
                    $posted_by_source = isset($posted_by_source) ? $posted_by_source : array('all' => array(), 'general_journal' => array(), 'cash_receipt' => array(), 'cash_disbursement' => array());
                    $posted_tab = isset($posted_tab) ? $posted_tab : 'all';
                    $has_posted_filters = !empty($posted_date_from) || !empty($posted_date_to);
                    $has_any_posted = !empty($posted_by_source['all']);
                    if ($has_any_posted || $has_posted_filters):
                        $tab_defs = array(
                            'all' => array('label' => 'All', 'count' => count($posted_by_source['all'])),
                            'general_journal' => array('label' => function_exists('journal_source_label') ? journal_source_label('general_journal') : 'Journal Entry', 'count' => count($posted_by_source['general_journal'])),
                            'cash_receipt' => array('label' => function_exists('journal_source_label') ? journal_source_label('cash_receipt') : 'Cash Receipt', 'count' => count($posted_by_source['cash_receipt'])),
                            'cash_disbursement' => array('label' => function_exists('journal_source_label') ? journal_source_label('cash_disbursement') : 'Cash Disbursement', 'count' => count($posted_by_source['cash_disbursement'])),
                        );
                        if (!isset($tab_defs[$posted_tab])) {
                            $posted_tab = 'all';
                        }
                    ?>
                        <hr style="margin: 30px 0;">
                        <h4><i class="fa fa-check-circle"></i> Posted General Ledger Listing</h4>
                        <p class="text-muted">These entries are already posted. Void creates a reversing journal (debits/credits swapped) and posts it to GL. Original GL lines are kept for audit.</p>

                        <form method="get" action="<?php echo site_url(current_lang() . '/finance/journal_entry_review'); ?>" class="form-inline" style="margin-bottom: 15px;" id="postedFilterForm">
                            <input type="hidden" name="posted_tab" id="posted_tab_input" value="<?php echo htmlspecialchars($posted_tab); ?>"/>
                            <div class="form-group" style="margin-right: 10px;">
                                <label for="posted_date_from" style="display:block;">Date From</label>
                                <input type="date" class="form-control" id="posted_date_from" name="posted_date_from" value="<?php echo isset($posted_date_from) ? htmlspecialchars($posted_date_from) : ''; ?>"/>
                            </div>
                            <div class="form-group" style="margin-right: 10px;">
                                <label for="posted_date_to" style="display:block;">Date To</label>
                                <input type="date" class="form-control" id="posted_date_to" name="posted_date_to" value="<?php echo isset($posted_date_to) ? htmlspecialchars($posted_date_to) : ''; ?>"/>
                            </div>
                            <div class="form-group" style="margin-top: 20px;">
                                <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Search Posted Entry</button>
                                <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_review'); ?>" class="btn btn-default">Clear</a>
                            </div>
                        </form>

                        <form method="post" action="<?php echo site_url(current_lang() . '/finance/void_gl_posting_batch'); ?>" id="voidGlForm" onsubmit="return confirm('Void selected entries with reversing journals?\n\nThis will create and post reversing entries to GL. Original postings remain for audit.');">
                            <ul class="nav nav-tabs" role="tablist" id="postedSourceTabs" style="margin-bottom: 0;">
                                <?php foreach ($tab_defs as $tab_key => $tab_info): ?>
                                    <li role="presentation" class="<?php echo ($posted_tab === $tab_key) ? 'active' : ''; ?>">
                                        <a href="#posted-tab-<?php echo htmlspecialchars($tab_key); ?>" aria-controls="posted-tab-<?php echo htmlspecialchars($tab_key); ?>" role="tab" data-toggle="tab" data-posted-tab="<?php echo htmlspecialchars($tab_key); ?>">
                                            <?php echo htmlspecialchars($tab_info['label']); ?>
                                            <span class="badge"><?php echo (int) $tab_info['count']; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="tab-content" style="border: 1px solid #ddd; border-top: 0; padding: 15px;">
                                <?php foreach ($tab_defs as $tab_key => $tab_info):
                                    $tab_entries = isset($posted_by_source[$tab_key]) ? $posted_by_source[$tab_key] : array();
                                    $table_id = 'postedEntriesTable_' . $tab_key;
                                ?>
                                <div role="tabpanel" class="tab-pane <?php echo ($posted_tab === $tab_key) ? 'active' : ''; ?>" id="posted-tab-<?php echo htmlspecialchars($tab_key); ?>">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover posted-entries-table" id="<?php echo htmlspecialchars($table_id); ?>" style="width:100%;" data-posted-tab="<?php echo htmlspecialchars($tab_key); ?>">
                                            <thead>
                                                <tr>
                                                    <th style="width: 32px;">
                                                        <input type="checkbox" class="select-all-posted" title="Select all on this page"/>
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
                                                <?php foreach ($tab_entries as $entry):
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
                                                        <td data-order="<?php echo htmlspecialchars($entry->entrydate); ?>"><?php echo date('M d, Y', strtotime($entry->entrydate)); ?></td>
                                                        <td><?php echo htmlspecialchars($entry->description); ?></td>
                                                        <td><?php echo htmlspecialchars($entry->created_by_name); ?></td>
                                                        <td style="text-align: center;"><?php echo isset($entry->line_count) ? $entry->line_count : 0; ?></td>
                                                        <td style="text-align: right;"><?php echo number_format($entry_debit, 2); ?></td>
                                                        <td style="text-align: right;"><?php echo number_format($entry_credit, 2); ?></td>
                                                        <td>
                                                            <a href="<?php echo site_url($view_url); ?>" class="btn btn-info btn-xs" title="View"><i class="fa fa-eye"></i> View</a>
                                                            <?php if ($is_general): ?>
                                                                <a href="<?php echo site_url(current_lang() . '/finance/void_gl_posting_general/' . encode_id($entry->entryid)); ?>"
                                                                   onclick="return confirm('Void with reversing entry?\n\nCreates a reversing JE, posts it to GL, and reverses CBU links if any. Original GL is kept.');"
                                                                   class="btn btn-warning btn-xs" title="Void with Reversing Entry"><i class="fa fa-undo"></i> Void</a>
                                                            <?php else: ?>
                                                                <a href="<?php echo site_url(current_lang() . '/finance/void_gl_posting_journal_entry/' . encode_id($entry->entryid)); ?>"
                                                                   onclick="return confirm('Void with reversing entry?\n\nCreates a reversing JE and posts it to GL. Original GL is kept for audit.');"
                                                                   class="btn btn-warning btn-xs" title="Void with Reversing Entry"><i class="fa fa-undo"></i> Void</a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div style="margin-top: 15px;">
                                <button type="submit" class="btn btn-warning" id="voidGlBatchBtn" disabled>
                                    <i class="fa fa-undo"></i> Void with Reversing Entry (Selected)
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
        var n = form.querySelectorAll('.tab-pane.active .void-checkbox:checked:not(:disabled)').length;
        if (!n) {
            // Fallback if tab markup missing
            n = form.querySelectorAll('.void-checkbox:checked:not(:disabled)').length;
        }
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

        function activePostedTable() {
            var pane = form.querySelector('.tab-pane.active');
            return pane ? pane.querySelector('table.posted-entries-table') : form.querySelector('table.posted-entries-table');
        }

        if (el.classList && el.classList.contains('select-all-posted')) {
            var table = el.closest ? el.closest('table') : null;
            if (!table) {
                var th = el.parentNode;
                while (th && th.tagName !== 'TABLE') th = th.parentNode;
                table = th;
            }
            var cbs = [];
            if (table && window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(table)) {
                var nodes = jQuery(table).DataTable().rows({ page: 'current' }).nodes();
                jQuery(nodes).find('.void-checkbox').each(function() { cbs.push(this); });
            } else if (table) {
                cbs = table.querySelectorAll('tbody .void-checkbox');
            }
            for (var i = 0; i < cbs.length; i++) cbs[i].checked = el.checked;
        } else if (el.classList && el.classList.contains('void-checkbox')) {
            var tableEl = activePostedTable();
            var checked = 0, total = 0;
            var selectAll = null;
            if (tableEl) {
                selectAll = tableEl.querySelector('.select-all-posted');
                if (window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(tableEl)) {
                    var pageNodes = jQuery(tableEl).DataTable().rows({ page: 'current' }).nodes();
                    total = jQuery(pageNodes).find('.void-checkbox').length;
                    checked = jQuery(pageNodes).find('.void-checkbox:checked').length;
                } else {
                    checked = tableEl.querySelectorAll('tbody .void-checkbox:checked').length;
                    total = tableEl.querySelectorAll('tbody .void-checkbox').length;
                }
            }
            if (selectAll) selectAll.checked = (total > 0 && checked === total);
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

        var unpostedProgressTimer = null;
        function showUnpostedProgress(label) {
            var $wrap = jQuery('#unpostedTableProgress');
            var $bar = jQuery('#unpostedProgressBar');
            var $text = jQuery('#unpostedProgressText');
            var $overlay = jQuery('#unpostedTableBusyOverlay');
            var pct = 8;
            if (label) {
                $text.html('<i class="fa fa-spinner fa-spin"></i> ' + label);
            } else {
                $text.html('<i class="fa fa-spinner fa-spin"></i> Loading entries...');
            }
            $bar.css('width', pct + '%').attr('aria-valuenow', pct).text(pct + '%');
            $wrap.show();
            $overlay.show();
            jQuery('#unpostedSourceTabs a').css('pointer-events', 'none');
            if (unpostedProgressTimer) {
                clearInterval(unpostedProgressTimer);
            }
            unpostedProgressTimer = setInterval(function() {
                if (pct < 90) {
                    pct += Math.max(1, Math.round((90 - pct) / 12));
                    $bar.css('width', pct + '%').attr('aria-valuenow', pct).text(pct + '%');
                }
            }, 200);
        }

        function hideUnpostedProgress() {
            var $wrap = jQuery('#unpostedTableProgress');
            var $bar = jQuery('#unpostedProgressBar');
            var $overlay = jQuery('#unpostedTableBusyOverlay');
            if (unpostedProgressTimer) {
                clearInterval(unpostedProgressTimer);
                unpostedProgressTimer = null;
            }
            $bar.css('width', '100%').attr('aria-valuenow', 100).text('100%');
            setTimeout(function() {
                $wrap.hide();
                $overlay.hide();
                $bar.css('width', '10%').attr('aria-valuenow', 10).text('10%');
                jQuery('#unpostedSourceTabs a').css('pointer-events', '');
            }, 250);
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
                    if (json && json.source_counts) {
                        jQuery.each(json.source_counts, function(src, count) {
                            jQuery('.unposted-source-count[data-source="' + src + '"]').text(count);
                        });
                    }
                    return (json && json.data) ? json.data : [];
                },
                error: function(xhr, error, thrown) {
                    console.error('Journal review DataTables error:', error, thrown, xhr && xhr.responseText);
                    hideUnpostedProgress();
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
                hideUnpostedProgress();
            },
            language: {
                emptyTable: 'All journal entries have been posted. No entries pending approval.',
                zeroRecords: 'No matching journal entries found.',
                processing: 'Loading...'
            }
        });

        jQuery('#unpostedJournalReviewTable').on('preXhr.dt', function() {
            if (!jQuery('#unpostedTableProgress').is(':visible')) {
                showUnpostedProgress('Loading entries...');
            }
        });

        jQuery('#unpostedSourceTabs a[data-source-filter]').on('click', function(e) {
            e.preventDefault();
            var $a = jQuery(this);
            var source = $a.data('source-filter') || 'all';
            var label = jQuery.trim($a.clone().children().remove().end().text()) || 'entries';
            if ($a.closest('li').hasClass('active') && jQuery('#sourceFilter').val() === String(source)) {
                // Still reload so user gets feedback when re-clicking active tab
            }
            jQuery('#unpostedSourceTabs li').removeClass('active');
            $a.closest('li').addClass('active');
            jQuery('#sourceFilter').val(source);
            showUnpostedProgress('Loading ' + label + '...');
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

        if (jQuery('.posted-entries-table').length) {
            var postedTables = {};
            jQuery('.posted-entries-table').each(function() {
                var $table = jQuery(this);
                var tabKey = $table.data('posted-tab') || $table.attr('id');
                postedTables[tabKey] = $table.DataTable({
                    pageLength: 25,
                    responsive: true,
                    order: [[3, 'desc']],
                    columnDefs: [
                        { orderable: false, searchable: false, targets: [0, 9] },
                        { className: 'text-right', targets: [7, 8] },
                        { className: 'text-center', targets: [6] }
                    ],
                    dom: 'lfrtip',
                    language: {
                        emptyTable: 'No posted entries found for this source.',
                        zeroRecords: 'No matching posted entries found.'
                    },
                    drawCallback: function() {
                        $table.find('.select-all-posted').prop('checked', false);
                        voidFormUpdateButton();
                    }
                });
            });

            jQuery('#postedSourceTabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var tabKey = jQuery(e.target).data('posted-tab') || 'all';
                jQuery('#posted_tab_input').val(tabKey);
                jQuery('#voidGlForm .tab-pane').each(function() {
                    var active = jQuery(this).hasClass('active');
                    jQuery(this).find('.void-checkbox, .select-all-posted').prop('disabled', !active);
                    if (!active) {
                        jQuery(this).find('.void-checkbox, .select-all-posted').prop('checked', false);
                    }
                });
                var table = postedTables[tabKey];
                if (table) {
                    table.columns.adjust();
                    if (table.responsive && table.responsive.recalc) {
                        table.responsive.recalc();
                    }
                }
                voidFormUpdateButton();
            });

            // Only active tab checkboxes should submit
            jQuery('#voidGlForm .tab-pane:not(.active) .void-checkbox, #voidGlForm .tab-pane:not(.active) .select-all-posted').prop('disabled', true);

            jQuery('#voidGlForm').on('submit', function() {
                jQuery('#voidGlForm .tab-pane:not(.active) .void-checkbox').prop('checked', false).prop('disabled', true);
            });
        }
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
