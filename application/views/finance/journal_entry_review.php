<?php $this->load->view('loan/list_page_styles'); ?>
<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

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
?>

<style type="text/css">
.jer-page .info-banner {
    background: #f0faf7;
    border: 1px solid #c9ebe3;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 16px;
    font-size: 13px;
    color: #2f4050;
    line-height: 1.45;
}
.jer-page .info-banner a { color: #1ab394; font-weight: 700; }
.jer-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.jer-page .source-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 16px;
    padding: 0;
    list-style: none;
}
.jer-page .source-tabs > li > a {
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
.jer-page .source-tabs > li.active > a,
.jer-page .source-tabs > li > a:hover {
    background: #e8f8f5;
    border-color: #1ab394;
    color: #1ab394;
}
.jer-page .source-tabs .badge,
.jer-page .source-tabs .count-badge {
    display: inline-block;
    min-width: 22px;
    padding: 2px 8px;
    border-radius: 10px;
    background: #eef1f2;
    color: #676a6c;
    font-size: 11px;
    font-weight: 700;
}
.jer-page .source-tabs > li.active .badge,
.jer-page .source-tabs > li.active .count-badge {
    background: #1ab394;
    color: #fff;
}
.jer-page .status-pill,
.jer-page .label {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    border: 0;
}
.jer-page .label-success,
.jer-page .status-pill.balanced {
    background: #e8f8f5 !important;
    color: #1ab394 !important;
}
.jer-page .label-danger,
.jer-page .status-pill.unbalanced {
    background: #fdeceb !important;
    color: #c0392b !important;
}
.jer-page .label-default,
.jer-page .status-pill.source {
    background: #eef3fb !important;
    color: #3c6eae !important;
}
.jer-page .progress {
    height: 10px;
    border-radius: 6px;
    background: #eef1f2;
    box-shadow: none;
    margin: 0;
}
.jer-page .progress-bar {
    background: #1ab394;
    box-shadow: none;
    font-size: 0;
    line-height: 10px;
}
.jer-page .batch-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid #eef1f2;
}
.jer-page #selectedCount {
    color: #888;
    font-size: 13px;
    font-weight: 600;
}
.jer-page .member-table > tfoot > tr > td {
    background: #f8fafb;
    border-top: 2px solid #e7eaec;
    font-weight: 700;
    font-size: 13px;
}
.jer-page .amount-cell {
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.jer-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.jer-page .btn {
    border-radius: 6px;
    font-weight: 600;
}
.jer-page .action-btns .btn {
    margin: 1px 2px;
}
.jer-page .filter-field.date-field { flex: 0 1 160px; min-width: 140px; }
.jer-page .jer-filter-row {
    margin: 0 0 16px;
}
.jer-page .dataTables_wrapper .dataTables_filter {
    display: none !important;
}
</style>

<div class="col-lg-12 member-list-page jer-page">
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
        <strong>Where to view posted transactions:</strong>
        Cash Receipt and Cash Disbursement entries (after posting) appear in
        <strong>Journal Entries / General Journal</strong> (journal 5).
        <a href="<?php echo site_url(current_lang() . '/report/journal_entry/5'); ?>">View Report &raquo;</a>
        <?php if (has_role(6, 'Void_transactions')) { ?>
            To void posted GL entries, use
            <a href="<?php echo site_url(current_lang() . '/finance/void_transactions'); ?>"><strong><?php echo lang('void_transactions'); ?></strong></a>.
        <?php } ?>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-check-square-o icon-badge"></i>
                <h4><?php echo lang('journal_entry_review'); ?></h4>
            </div>
            <div class="head-actions">
                <?php if (has_role(6, 'Journal_entry')) { ?>
                    <a href="<?php echo site_url(current_lang() . '/finance/journalentry'); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create New Journal Entry
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="source-tabs" role="tablist" id="unpostedSourceTabs">
                <?php foreach ($unposted_tab_defs as $tab_key => $tab_label) { ?>
                    <li role="presentation" class="<?php echo ($tab_key === 'all') ? 'active' : ''; ?>">
                        <a href="javascript:void(0);" role="tab" data-source-filter="<?php echo htmlspecialchars($tab_key, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($tab_label, ENT_QUOTES, 'UTF-8'); ?>
                            <span class="count-badge unposted-source-count" data-source="<?php echo htmlspecialchars($tab_key, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo (int) (isset($unposted_counts[$tab_key]) ? $unposted_counts[$tab_key] : 0); ?>
                            </span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <input type="hidden" id="sourceFilter" value="all"/>

            <div class="filter-row jer-filter-row" id="jerFilterBar">
                <div class="filter-field search-field">
                    <label for="jerSearch">Search</label>
                    <input type="text" class="form-control" id="jerSearch" name="jer_search" placeholder="Entry ID, description, source, user..." autocomplete="off"/>
                </div>
                <div class="filter-field date-field">
                    <label for="jerDateFrom">Start Date</label>
                    <input type="date" class="form-control" id="jerDateFrom" name="date_from"/>
                </div>
                <div class="filter-field date-field">
                    <label for="jerDateTo">End Date</label>
                    <input type="date" class="form-control" id="jerDateTo" name="date_to"/>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn btn-primary" id="jerSearchBtn">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-default btn-clear-filter" id="jerClearBtn">
                        <i class="fa fa-undo"></i> Clear
                    </button>
                </div>
            </div>

            <div id="unpostedTableProgress" style="display:none; margin-bottom: 14px;">
                <p id="unpostedProgressText" class="text-muted" style="margin-bottom: 6px; font-size: 13px;">
                    <i class="fa fa-spinner fa-spin"></i> Loading entries...
                </p>
                <div class="progress">
                    <div id="unpostedProgressBar" class="progress-bar progress-bar-striped active" role="progressbar" style="width: 10%; min-width: 10%;">10%</div>
                </div>
            </div>

            <form method="post" action="<?php echo site_url(current_lang() . '/finance/journal_entry_batch_approve'); ?>" id="approveForm" onsubmit="return confirm('Are you sure you want to approve and post the selected journal entries?');">
                <div class="table-responsive" style="position: relative;">
                    <div id="unpostedTableBusyOverlay" style="display:none; position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.65); z-index:20;"></div>
                    <table class="table table-striped member-table dataTables-example" id="unpostedJournalReviewTable" style="width:100%;">
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
                            <tr>
                                <td colspan="7" style="text-align: right;"><strong>Grand Total:</strong></td>
                                <td id="grandTotalDebit" class="amount-cell"><strong>0.00</strong></td>
                                <td id="grandTotalCredit" class="amount-cell"><strong>0.00</strong></td>
                                <td colspan="2" id="grandTotalStatus"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="batch-bar">
                    <button type="submit" class="btn btn-success" id="batchApproveBtn" disabled>
                        <i class="fa fa-check"></i> Approve Selected Entries
                    </button>
                    <span id="selectedCount"></span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
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
                    ? '<span class="status-pill balanced">Balanced</span>'
                    : '<span class="status-pill unbalanced">Unbalanced</span>'
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
                    d.search = d.search || {};
                    d.search.value = jQuery.trim(jQuery('#jerSearch').val() || '');
                    d.date_from = jQuery('#jerDateFrom').val() || '';
                    d.date_to = jQuery('#jerDateTo').val() || '';
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
                { className: 'text-right amount-cell', targets: [7, 8] },
                { className: 'text-center', targets: [6] }
            ],
            searching: false,
            dom: 'lrtip',
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

        function reloadUnpostedTable(progressLabel) {
            showUnpostedProgress(progressLabel || 'Loading entries...');
            unpostedTable.ajax.reload();
        }

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
            jQuery('#unpostedSourceTabs li').removeClass('active');
            $a.closest('li').addClass('active');
            jQuery('#sourceFilter').val(source);
            reloadUnpostedTable('Loading ' + label + '...');
        });

        jQuery('#jerSearchBtn').on('click', function() {
            reloadUnpostedTable('Searching...');
        });

        jQuery('#jerSearch').on('keydown', function(e) {
            if (e.which === 13 || e.keyCode === 13) {
                e.preventDefault();
                reloadUnpostedTable('Searching...');
            }
        });

        jQuery('#jerClearBtn').on('click', function() {
            jQuery('#jerSearch').val('');
            jQuery('#jerDateFrom').val('');
            jQuery('#jerDateTo').val('');
            reloadUnpostedTable('Loading entries...');
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
