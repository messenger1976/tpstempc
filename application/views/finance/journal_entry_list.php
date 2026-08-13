<?php $this->load->view('loan/list_page_styles'); ?>
<link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

<?php
$date_from = isset($date_from) ? $date_from : '';
$date_to = isset($date_to) ? $date_to : '';
$journal_entries = isset($journal_entries) ? $journal_entries : array();
$total_rows = count($journal_entries);

$qs = array();
if ($date_from !== '') {
    $qs['date_from'] = $date_from;
}
if ($date_to !== '') {
    $qs['date_to'] = $date_to;
}
$query_suffix = count($qs) ? ('?' . http_build_query($qs)) : '';

$export_url = site_url(current_lang() . '/finance/journal_entry_export') . $query_suffix;
$create_url = site_url(current_lang() . '/finance/journalentry');
$review_url = site_url(current_lang() . '/finance/journal_entry_review');
$clear_url = site_url(current_lang() . '/finance/journal_entry_list?clear=1');
$list_url = site_url(current_lang() . '/finance/journal_entry_list');
?>

<style type="text/css">
.member-list-page .filter-field.date-field { flex: 0 1 180px; min-width: 150px; }
.member-list-page .head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.member-list-page .status-pill.posted {
    background: #1ab394;
}
.member-list-page .status-pill.draft {
    background: #676a6c;
}
.member-list-page .status-pill.voided {
    background: #ed5565;
}
.member-list-page .status-pill.reversal {
    background: #1c84c6;
}
#journalEntryModal .modal-header {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
#journalEntryModal .modal-title {
    font-weight: 700;
    color: #2f4050;
}
</style>

<div class="col-lg-12 member-list-page">
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

    <div class="member-filter-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-search icon-badge"></i>
                <h4><?php echo lang('journal_entry_list'); ?></h4>
            </div>
            <div class="head-actions">
                <?php if (has_role(6, 'Journal_entry')) { ?>
                    <a href="<?php echo $create_url; ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> <?php echo lang('journalentry'); ?>
                    </a>
                <?php } ?>
                <a href="<?php echo $export_url; ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o"></i> <?php echo lang('export_excel'); ?>
                </a>
                <?php if (has_role(6, 'Review_journal_entry')) { ?>
                    <a href="<?php echo $review_url; ?>" class="btn btn-info btn-sm">
                        <i class="fa fa-check-circle"></i> <?php echo lang('journal_entry_review'); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?php echo $list_url; ?>" method="get" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field date-field">
                        <label>From Date</label>
                        <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from, ENT_QUOTES, 'UTF-8'); ?>"/>
                    </div>
                    <div class="filter-field date-field">
                        <label>To Date</label>
                        <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to, ENT_QUOTES, 'UTF-8'); ?>"/>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo $clear_url; ?>" class="btn btn-default btn-clear-filter">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-book icon-badge"></i>
                <h4><?php echo lang('journal_entry_list'); ?></h4>
            </div>
            <div class="result-meta">
                Showing <strong><?php echo number_format($total_rows); ?></strong> entr<?php echo $total_rows === 1 ? 'y' : 'ies'; ?>
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table dataTables-example" id="journalEntryTable">
                <thead>
                    <tr>
                        <th><?php echo lang('journal_entry_no'); ?></th>
                        <th><?php echo lang('journalentry_date'); ?></th>
                        <th><?php echo lang('journalentry_reference_no'); ?></th>
                        <th><?php echo lang('journalentry_document_no'); ?></th>
                        <th><?php echo lang('journalentry_description'); ?></th>
                        <th style="text-align:right;"><?php echo lang('journalentry_debit'); ?></th>
                        <th style="text-align:right;"><?php echo lang('journalentry_credit'); ?></th>
                        <th><?php echo lang('status'); ?></th>
                        <th><?php echo lang('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($journal_entries)) { ?>
                        <?php foreach ($journal_entries as $entry) {
                            $view_url = site_url(current_lang() . '/finance/journal_entry_view/' . encode_id($entry->entryid) . '?popup=1');
                            ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo (int) $entry->entryid; ?></span></td>
                                <td data-order="<?php echo htmlspecialchars($entry->entrydate, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(date('d-m-Y', strtotime($entry->entrydate)), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><?php echo !empty($entry->reference_no) ? htmlspecialchars($entry->reference_no, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></td>
                                <td><?php echo !empty($entry->document_no) ? htmlspecialchars($entry->document_no, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></td>
                                <td><?php echo htmlspecialchars(character_limiter($entry->description, 50), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format($entry->total_debit, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format($entry->total_credit, 2); ?></td>
                                <td>
                                    <?php if (!empty($entry->is_voided)) { ?>
                                        <span class="status-pill voided">Voided</span>
                                    <?php } else if (!empty($entry->is_reversal)) { ?>
                                        <span class="status-pill reversal">Reversal</span>
                                    <?php } else if (!empty($entry->is_posted)) { ?>
                                        <span class="status-pill posted"><?php echo lang('journal_entry_status_posted'); ?></span>
                                    <?php } else { ?>
                                        <span class="status-pill draft"><?php echo lang('journal_entry_status_draft'); ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?php echo $view_url; ?>"
                                           class="btn btn-info btn-xs view-popup"
                                           title="<?php echo htmlspecialchars(lang('view'), ENT_QUOTES, 'UTF-8'); ?>"
                                           data-url="<?php echo htmlspecialchars($view_url, ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <?php if ((has_role(6, 'Edit_journal_entry') || has_role(6, 'Journal_entry')) && empty($entry->is_posted)) { ?>
                                            <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_edit/' . encode_id($entry->entryid)); ?>"
                                               class="btn btn-warning btn-xs"
                                               title="<?php echo htmlspecialchars(lang('edit'), ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        <?php } ?>
                                        <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_print/' . encode_id($entry->entryid)); ?>"
                                           class="btn btn-primary btn-xs"
                                           target="_blank"
                                           title="<?php echo htmlspecialchars(lang('print'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        <?php if (has_role(6, 'Delete_journal_entry') && empty($entry->is_posted)) { ?>
                                            <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_delete/' . encode_id($entry->entryid)); ?>"
                                               class="btn btn-danger btn-xs delete-confirm"
                                               title="<?php echo htmlspecialchars(lang('delete'), ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="journalEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo lang('journal_entry_view'); ?></h4>
            </div>
            <div class="modal-body" style="height:70vh; padding:0;">
                <iframe id="journalEntryModalFrame" src="about:blank" style="border:0; width:100%; height:100%;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
(function(init){
    function loadScript(src, cb){
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        document.head.appendChild(s);
    }

    function loadSwal(cb){
        if (window.Swal) { cb(); return; }
        loadScript('https://cdn.jsdelivr.net/npm/sweetalert2@11', cb);
    }
    window.loadSwal = loadSwal;

    function tryInit(){
        if (window.jQuery) {
            if (!window.jQuery.fn || !window.jQuery.fn.DataTable) {
                loadScript('<?php echo base_url(); ?>assets/js/plugins/dataTables/datatables.min.js', function(){
                    init();
                });
            } else {
                init();
            }
        } else {
            setTimeout(tryInit, 50);
        }
    }

    tryInit();
})(function(){
    jQuery(function($){
        $('.dataTables-example').DataTable({
            pageLength: 25,
            responsive: true,
            dom: 'lBfrtip',
            order: [[1, 'desc']],
            buttons: [
                {extend: 'copy'},
                {extend: 'csv'},
                {extend: 'excel', title: 'JournalEntries'},
                {extend: 'pdf', title: 'JournalEntries'},
                {extend: 'print',
                 customize: function (win){
                        jQuery(win.document.body).addClass('white-bg');
                        jQuery(win.document.body).css('font-size', '10px');
                        jQuery(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                }
                }
            ],
            language: {
                emptyTable: '<?php echo lang('no_records_found'); ?>'
            }
        });

        $(document).on('click', '.delete-confirm', function(e){
            e.preventDefault();
            var url = this.href;
            var load = window.loadSwal || function(cb){ cb(); };
            load(function(){
                if (!window.Swal) {
                    if (confirm('<?php echo lang('delete_confirm'); ?>')) {
                        window.location.href = url;
                    }
                    return;
                }
                Swal.fire({
                    title: '<?php echo lang('delete_confirm'); ?>',
                    text: '<?php echo lang('journal_entry_delete_confirm_text'); ?>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<?php echo lang('delete'); ?>',
                    cancelButtonText: '<?php echo lang('cancel'); ?>'
                }).then(function(result){
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });

        $(document).on('click', '.view-popup', function(e){
            e.preventDefault();
            var url = $(this).data('url') || this.href;
            $('#journalEntryModalFrame').attr('src', url);
            $('#journalEntryModal').modal('show');
        });
    });
});
</script>
