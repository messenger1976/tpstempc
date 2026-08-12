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
    <?php if (has_role(6, 'Void_transactions')): ?>
        To void posted GL entries, use <a href="<?php echo site_url(current_lang() . '/finance/void_transactions'); ?>" class="alert-link"><strong><?php echo lang('void_transactions'); ?></strong></a>.
    <?php endif; ?>
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
                </div>
            </div>
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
