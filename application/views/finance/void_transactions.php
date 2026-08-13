<?php $this->load->view('loan/list_page_styles'); ?>
<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

<?php
$posted_by_source = isset($posted_by_source) ? $posted_by_source : array(
    'all' => array(),
    'general_journal' => array(),
    'cash_receipt' => array(),
    'cash_disbursement' => array(),
);
$posted_tab = isset($posted_tab) ? $posted_tab : 'all';
$posted_date_from = isset($posted_date_from) ? $posted_date_from : '';
$posted_date_to = isset($posted_date_to) ? $posted_date_to : '';
$tab_defs = array(
    'all' => array(
        'label' => 'All',
        'count' => count($posted_by_source['all']),
    ),
    'general_journal' => array(
        'label' => function_exists('journal_source_label') ? journal_source_label('general_journal') : 'Journal Entry',
        'count' => count($posted_by_source['general_journal']),
    ),
    'cash_receipt' => array(
        'label' => function_exists('journal_source_label') ? journal_source_label('cash_receipt') : 'Cash Receipt',
        'count' => count($posted_by_source['cash_receipt']),
    ),
    'cash_disbursement' => array(
        'label' => function_exists('journal_source_label') ? journal_source_label('cash_disbursement') : 'Cash Disbursement',
        'count' => count($posted_by_source['cash_disbursement']),
    ),
);
if (!isset($tab_defs[$posted_tab])) {
    $posted_tab = 'all';
}
$list_url = site_url(current_lang() . '/finance/void_transactions');
?>

<style type="text/css">
.vt-page .info-banner {
    background: #fff8e6;
    border: 1px solid #f0e0b2;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 16px;
    font-size: 13px;
    color: #2f4050;
    line-height: 1.45;
}
.vt-page .filter-field.date-field { flex: 0 1 180px; min-width: 150px; }
.vt-page .source-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 16px;
    padding: 0;
    list-style: none;
}
.vt-page .source-tabs > li > a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 20px;
    border: 1px solid #e7eaec;
    background: #fff;
    color: #676a6c;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}
.vt-page .source-tabs > li.active > a,
.vt-page .source-tabs > li > a:hover {
    background: #e8f8f5;
    border-color: #1ab394;
    color: #1ab394;
}
.vt-page .source-tabs .count-badge {
    display: inline-block;
    min-width: 22px;
    padding: 2px 8px;
    border-radius: 10px;
    background: #eef1f2;
    color: #676a6c;
    font-size: 11px;
    font-weight: 700;
}
.vt-page .source-tabs > li.active .count-badge {
    background: #1ab394;
    color: #fff;
}
.vt-page .status-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
}
.vt-page .status-pill.source {
    background: #eef3fb;
    color: #3c6eae;
}
.vt-page .tab-content-wrap {
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 14px;
    background: #fff;
}
.vt-page .batch-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid #eef1f2;
}
.vt-page #voidSelectedCount {
    color: #888;
    font-size: 13px;
    font-weight: 600;
}
.vt-page .amount-cell {
    text-align: right;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.vt-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.vt-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.vt-page .action-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.vt-page .empty-note {
    color: #999;
    font-size: 13px;
    padding: 18px 0;
    text-align: center;
}
</style>

<div class="col-lg-12 member-list-page vt-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="member-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="member-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="member-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="member-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="info-banner">
        <i class="fa fa-info-circle"></i>
        These entries are already posted. Void creates a reversing journal (debits/credits swapped) and posts it to GL.
        Original GL lines are kept for audit.
    </div>

    <div class="member-filter-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-search icon-badge"></i>
                <h4><?php echo lang('void_transactions'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <form method="get" action="<?php echo $list_url; ?>" class="form-horizontal" id="postedFilterForm">
                <input type="hidden" name="posted_tab" id="posted_tab_input" value="<?php echo htmlspecialchars($posted_tab, ENT_QUOTES, 'UTF-8'); ?>"/>
                <div class="filter-row">
                    <div class="filter-field date-field">
                        <label for="posted_date_from">From Date</label>
                        <input type="date" class="form-control" id="posted_date_from" name="posted_date_from" value="<?php echo htmlspecialchars($posted_date_from, ENT_QUOTES, 'UTF-8'); ?>"/>
                    </div>
                    <div class="filter-field date-field">
                        <label for="posted_date_to">To Date</label>
                        <input type="date" class="form-control" id="posted_date_to" name="posted_date_to" value="<?php echo htmlspecialchars($posted_date_to, ENT_QUOTES, 'UTF-8'); ?>"/>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo $list_url; ?>" class="btn btn-default btn-clear-filter">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Search Posted Entry
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-undo icon-badge"></i>
                <h4>Posted General Ledger Listing</h4>
            </div>
            <div class="result-meta">
                Showing <strong><?php echo number_format((int) $tab_defs[$posted_tab]['count']); ?></strong>
                in current tab
            </div>
        </div>
        <div class="panel-body">
            <form method="post" action="<?php echo site_url(current_lang() . '/finance/void_gl_posting_batch'); ?>" id="voidGlForm" onsubmit="return confirm('Void selected entries with reversing journals?\n\nThis will create and post reversing entries to GL. Original postings remain for audit.');">
                <ul class="source-tabs" role="tablist" id="postedSourceTabs">
                    <?php foreach ($tab_defs as $tab_key => $tab_info) { ?>
                        <li role="presentation" class="<?php echo ($posted_tab === $tab_key) ? 'active' : ''; ?>">
                            <a href="#posted-tab-<?php echo htmlspecialchars($tab_key, ENT_QUOTES, 'UTF-8'); ?>"
                               aria-controls="posted-tab-<?php echo htmlspecialchars($tab_key, ENT_QUOTES, 'UTF-8'); ?>"
                               role="tab"
                               data-toggle="tab"
                               data-posted-tab="<?php echo htmlspecialchars($tab_key, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($tab_info['label'], ENT_QUOTES, 'UTF-8'); ?>
                                <span class="count-badge"><?php echo (int) $tab_info['count']; ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>

                <div class="tab-content tab-content-wrap">
                    <?php foreach ($tab_defs as $tab_key => $tab_info) {
                        $tab_entries = isset($posted_by_source[$tab_key]) ? $posted_by_source[$tab_key] : array();
                        $table_id = 'postedEntriesTable_' . $tab_key;
                        ?>
                        <div role="tabpanel" class="tab-pane <?php echo ($posted_tab === $tab_key) ? 'active' : ''; ?>" id="posted-tab-<?php echo htmlspecialchars($tab_key, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="table-responsive">
                                <table class="table table-striped member-table posted-entries-table" id="<?php echo htmlspecialchars($table_id, ENT_QUOTES, 'UTF-8'); ?>" style="width:100%;" data-posted-tab="<?php echo htmlspecialchars($tab_key, ENT_QUOTES, 'UTF-8'); ?>">
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
                                        <?php foreach ($tab_entries as $entry) {
                                            $entry_source = isset($entry->entry_source) ? $entry->entry_source : 'general_journal';
                                            $is_general = ($entry_source === 'general_journal');
                                            $view_url = current_lang() . '/finance/journal_entry_view/' . encode_id($entry->entryid);
                                            if ($entry_source === 'cash_disbursement' && isset($entry->reference_id)) {
                                                $view_url = current_lang() . '/cash_disbursement/cash_disbursement_view/' . encode_id($entry->reference_id);
                                            } else if ($entry_source === 'cash_receipt' && isset($entry->reference_id)) {
                                                $view_url = current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($entry->reference_id);
                                            }
                                            $source_label = function_exists('journal_source_label') ? journal_source_label($entry_source) : $entry_source;
                                            $entry_debit = isset($entry->total_debit) ? (float) $entry->total_debit : 0;
                                            $entry_credit = isset($entry->total_credit) ? (float) $entry->total_credit : 0;
                                            $void_value = $entry_source . '::' . encode_id($entry->entryid);
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="void_ids[]" value="<?php echo htmlspecialchars($void_value, ENT_QUOTES, 'UTF-8'); ?>" class="void-checkbox"/>
                                                </td>
                                                <td><span class="member-id-chip"><?php echo (int) $entry->entryid; ?></span></td>
                                                <td><span class="status-pill source"><?php echo htmlspecialchars($source_label, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td data-order="<?php echo htmlspecialchars($entry->entrydate, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars(date('M d, Y', strtotime($entry->entrydate)), ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($entry->description, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($entry->created_by_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td style="text-align: center;"><?php echo isset($entry->line_count) ? (int) $entry->line_count : 0; ?></td>
                                                <td class="amount-cell"><?php echo number_format($entry_debit, 2); ?></td>
                                                <td class="amount-cell"><?php echo number_format($entry_credit, 2); ?></td>
                                                <td>
                                                    <div class="action-btns">
                                                        <a href="<?php echo site_url($view_url); ?>" class="btn btn-info btn-xs" title="View">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                        <?php if ($is_general) { ?>
                                                            <a href="<?php echo site_url(current_lang() . '/finance/void_gl_posting_general/' . encode_id($entry->entryid)); ?>"
                                                               onclick="return confirm('Void with reversing entry?\n\nCreates a reversing JE, posts it to GL, and reverses CBU links if any. Original GL is kept.');"
                                                               class="btn btn-warning btn-xs" title="Void with Reversing Entry">
                                                                <i class="fa fa-undo"></i> Void
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="<?php echo site_url(current_lang() . '/finance/void_gl_posting_journal_entry/' . encode_id($entry->entryid)); ?>"
                                                               onclick="return confirm('Void with reversing entry?\n\nCreates a reversing JE and posts it to GL. Original GL is kept for audit.');"
                                                               class="btn btn-warning btn-xs" title="Void with Reversing Entry">
                                                                <i class="fa fa-undo"></i> Void
                                                            </a>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="batch-bar">
                    <button type="submit" class="btn btn-warning" id="voidGlBatchBtn" disabled>
                        <i class="fa fa-undo"></i> Void with Reversing Entry (Selected)
                    </button>
                    <span id="voidSelectedCount"></span>
                </div>
            </form>
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
                    { className: 'text-right amount-cell', targets: [7, 8] },
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
