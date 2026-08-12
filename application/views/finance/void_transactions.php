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

$posted_by_source = isset($posted_by_source) ? $posted_by_source : array('all' => array(), 'general_journal' => array(), 'cash_receipt' => array(), 'cash_disbursement' => array());
$posted_tab = isset($posted_tab) ? $posted_tab : 'all';
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

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><i class="fa fa-undo"></i> <?php echo lang('void_transactions'); ?></h5>
                </div>
                <div class="ibox-content">
                    <h4><i class="fa fa-check-circle"></i> Posted General Ledger Listing</h4>
                    <p class="text-muted">These entries are already posted. Void creates a reversing journal (debits/credits swapped) and posts it to GL. Original GL lines are kept for audit.</p>

                    <form method="get" action="<?php echo site_url(current_lang() . '/finance/void_transactions'); ?>" class="form-inline" style="margin-bottom: 15px;" id="postedFilterForm">
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
                            <a href="<?php echo site_url(current_lang() . '/finance/void_transactions'); ?>" class="btn btn-default">Clear</a>
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function voidFormUpdateButton() {
        var form = document.getElementById('voidGlForm');
        if (!form) return;
        var n = form.querySelectorAll('.tab-pane.active .void-checkbox:checked:not(:disabled)').length;
        if (!n) {
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

    function initPostedTables() {
        if (!jQuery('.posted-entries-table').length) {
            return;
        }
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

        jQuery('#voidGlForm .tab-pane:not(.active) .void-checkbox, #voidGlForm .tab-pane:not(.active) .select-all-posted').prop('disabled', true);

        jQuery('#voidGlForm').on('submit', function() {
            jQuery('#voidGlForm .tab-pane:not(.active) .void-checkbox').prop('checked', false).prop('disabled', true);
        });
    }

    function tryInit() {
        if (window.jQuery) {
            if (!window.jQuery.fn || !window.jQuery.fn.DataTable) {
                loadScript('<?php echo base_url(); ?>assets/js/plugins/dataTables/datatables.min.js', function() {
                    initPostedTables();
                });
            } else {
                initPostedTables();
            }
        } else {
            setTimeout(tryInit, 50);
        }
    }

    tryInit();
})();
</script>
