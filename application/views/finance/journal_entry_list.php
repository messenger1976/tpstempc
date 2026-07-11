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

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo lang('journal_entry_list'); ?></h5>
                    <div class="ibox-tools">
                        <?php if (has_role(6, 'Journal_entry')) { ?>
                            <a href="<?php echo site_url(current_lang() . '/finance/journalentry'); ?>" class="btn btn-primary btn-xs">
                                <i class="fa fa-plus"></i> <?php echo lang('journalentry'); ?>
                            </a>
                        <?php } ?>
                        <?php
                        $export_url = site_url(current_lang() . '/finance/journal_entry_export');
                        if (isset($date_from) && !empty($date_from)) {
                            $export_url .= '?date_from=' . urlencode($date_from);
                            if (isset($date_to) && !empty($date_to)) {
                                $export_url .= '&date_to=' . urlencode($date_to);
                            }
                        } elseif (isset($date_to) && !empty($date_to)) {
                            $export_url .= '?date_to=' . urlencode($date_to);
                        }
                        ?>
                        <a href="<?php echo $export_url; ?>" class="btn btn-success btn-xs">
                            <i class="fa fa-file-excel-o"></i> <?php echo lang('export_excel'); ?>
                        </a>
                        <?php if (has_role(6, 'Journal_entry')) { ?>
                            <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_review'); ?>" class="btn btn-info btn-xs">
                                <i class="fa fa-check-circle"></i> <?php echo lang('journal_entry_review'); ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <div class="ibox-content">
                    <form method="get" action="<?php echo site_url(current_lang() . '/finance/journal_entry_list'); ?>" class="form-horizontal" style="margin-bottom: 20px;">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Date From:</label>
                                <input type="date" class="form-control" name="date_from" value="<?php echo isset($date_from) ? htmlspecialchars($date_from) : ''; ?>"/>
                            </div>
                            <div class="col-md-3">
                                <label>Date To:</label>
                                <input type="date" class="form-control" name="date_to" value="<?php echo isset($date_to) ? htmlspecialchars($date_to) : ''; ?>"/>
                            </div>
                            <div class="col-md-3" style="padding-top: 25px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_list?clear=1'); ?>" class="btn btn-default">
                                    <i class="fa fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example">
                            <thead>
                                <tr>
                                    <th><?php echo lang('journal_entry_no'); ?></th>
                                    <th><?php echo lang('journalentry_date'); ?></th>
                                    <th><?php echo lang('journalentry_reference_no'); ?></th>
                                    <th><?php echo lang('journalentry_description'); ?></th>
                                    <th><?php echo lang('journalentry_debit'); ?></th>
                                    <th><?php echo lang('journalentry_credit'); ?></th>
                                    <th><?php echo lang('status'); ?></th>
                                    <th><?php echo lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($journal_entries)): ?>
                                    <?php foreach ($journal_entries as $entry): ?>
                                        <tr>
                                            <td><?php echo $entry->entryid; ?></td>
                                            <td data-order="<?php echo $entry->entrydate; ?>"><?php echo date('d-m-Y', strtotime($entry->entrydate)); ?></td>
                                            <td><?php echo !empty($entry->reference_no) ? htmlspecialchars($entry->reference_no) : '—'; ?></td>
                                            <td><?php echo character_limiter($entry->description, 50); ?></td>
                                            <td class="text-right"><?php echo number_format($entry->total_debit, 2); ?></td>
                                            <td class="text-right"><?php echo number_format($entry->total_credit, 2); ?></td>
                                            <td>
                                                <?php if (!empty($entry->is_posted)): ?>
                                                    <span class="label label-success"><?php echo lang('journal_entry_status_posted'); ?></span>
                                                <?php else: ?>
                                                    <span class="label label-default"><?php echo lang('journal_entry_status_draft'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_view/' . encode_id($entry->entryid) . '?popup=1'); ?>"
                                                       class="btn btn-info btn-xs view-popup" title="<?php echo lang('view'); ?>"
                                                       data-url="<?php echo site_url(current_lang() . '/finance/journal_entry_view/' . encode_id($entry->entryid) . '?popup=1'); ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_print/' . encode_id($entry->entryid)); ?>"
                                                       class="btn btn-success btn-xs" target="_blank" title="<?php echo lang('print'); ?>">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                    <?php if (has_role(6, 'Delete_journal_entry') && empty($entry->is_posted)) { ?>
                                                        <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_delete/' . encode_id($entry->entryid)); ?>"
                                                           class="btn btn-danger btn-xs delete-confirm" title="<?php echo lang('delete'); ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
