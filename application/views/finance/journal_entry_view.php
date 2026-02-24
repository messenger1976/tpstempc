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
                    <h5>Journal Entry Details - #<?php echo $entry->entryid; ?></h5>
                    <div class="ibox-tools">
                        <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_review'); ?>" class="btn btn-default btn-xs">
                            <i class="fa fa-arrow-left"></i> Back to Review
                        </a>
                        <?php if (isset($entry->id) && $entry->id == 32800): ?>
                            <span class="label label-info" style="margin-left: 10px;">This is the test entry with items</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px;">Entry ID:</th>
                                    <td><?php echo $entry->entryid; ?></td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td><?php echo date('M d, Y', strtotime($entry->entrydate)); ?></td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td><?php echo htmlspecialchars($entry->description); ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <?php if ($entry->is_posted): ?>
                                            <span class="label label-success">Posted</span>
                                        <?php else: ?>
                                            <span class="label label-warning">Pending Approval</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Balance Status:</th>
                                    <td>
                                        <?php if (abs($entry->total_debit - $entry->total_credit) <= 0.01): ?>
                                            <span class="label label-success">Balanced</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Unbalanced (Difference: <?php echo number_format(abs($entry->total_debit - $entry->total_credit), 2); ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px;">Total Debit:</th>
                                    <td style="text-align: right; font-weight: bold;"><?php echo number_format($entry->total_debit, 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Credit:</th>
                                    <td style="text-align: right; font-weight: bold;"><?php echo number_format($entry->total_credit, 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Difference:</th>
                                    <td style="text-align: right; font-weight: bold; <?php echo (abs($entry->total_debit - $entry->total_credit) > 0.01) ? 'color: red;' : 'color: green;'; ?>">
                                        <?php echo number_format($entry->total_debit - $entry->total_credit, 2); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Line Items:</th>
                                    <td><?php echo count($entry->line_items); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <h4>Journal Entry Line Items</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">S.No</th>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Description</th>
                                    <th style="text-align: right; width: 120px;">Debit</th>
                                    <th style="text-align: right; width: 120px;">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($entry->line_items)): ?>
                                    <?php $i = 1; foreach ($entry->line_items as $item): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo $item->account; ?></td>
                                            <td><?php echo htmlspecialchars($item->account_name); ?></td>
                                            <td><?php echo htmlspecialchars($item->description); ?></td>
                                            <td style="text-align: right;"><?php echo number_format($item->debit, 2); ?></td>
                                            <td style="text-align: right;"><?php echo number_format($item->credit, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                                        <td colspan="4" style="text-align: right;">TOTAL:</td>
                                        <td style="text-align: right;"><?php echo number_format($entry->total_debit, 2); ?></td>
                                        <td style="text-align: right;"><?php echo number_format($entry->total_credit, 2); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="alert alert-warning">
                                                <i class="fa fa-warning"></i> No line items found for this journal entry.
                                                <br><small>
                                                    Entry ID: <?php echo isset($entry->entryid) ? $entry->entryid : 'N/A'; ?>, 
                                                    Header ID: <?php echo isset($entry->id) ? $entry->id : 'N/A'; ?>,
                                                    Line Items Count: <?php echo isset($entry->line_items) ? count($entry->line_items) : 'N/A'; ?>
                                                </small>
                                                <?php if (isset($entry->debug_info)): ?>
                                                    <div style="margin-top: 10px; padding: 10px; background: #f0f0f0; border: 1px solid #ccc; font-size: 11px;">
                                                        <strong>Debug Information:</strong><br>
                                                        Actual ID used in query: <?php echo $entry->debug_info['actual_id']; ?><br>
                                                        Entry ID parameter: <?php echo $entry->debug_info['entry_id']; ?><br>
                                                        Direct count query result: <?php echo $entry->debug_info['any_items_count']; ?> items found<br>
                                                        Query used: <?php echo htmlspecialchars($entry->debug_info['query_used']); ?><br>
                                                        <?php if (!empty($entry->debug_info['matching_entryids'])): ?>
                                                            Matching entryids in DB: <?php echo json_encode($entry->debug_info['matching_entryids']); ?><br>
                                                        <?php endif; ?>
                                                        Recent entryids in general_journal: <?php echo json_encode($entry->debug_info['recent_entryids']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <?php if (!$entry->is_posted): ?>
                                <?php if (abs($entry->total_debit - $entry->total_credit) <= 0.01): ?>
                                    <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_approve/' . $id); ?>" 
                                       onclick="return confirm('Are you sure you want to approve and post this journal entry to General Ledger?');" 
                                       class="btn btn-success">
                                        <i class="fa fa-check"></i> Approve & Post to General Ledger
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-danger">
                                        <i class="fa fa-warning"></i> This journal entry is not balanced. Please correct it before posting.
                                        <br>Difference: <?php echo number_format(abs($entry->total_debit - $entry->total_credit), 2); ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-success">
                                    <i class="fa fa-check-circle"></i> This journal entry has already been posted to General Ledger.
                                </div>
                                <a href="<?php echo site_url(current_lang() . '/finance/void_gl_posting_general/' . $id); ?>"
                                   onclick="return confirm('Void the GL posting only? The journal entry will stay and you can repost it later.');"
                                   class="btn btn-warning">
                                    <i class="fa fa-undo"></i> Void GL Posting
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?php echo site_url(current_lang() . '/finance/journal_entry_review'); ?>" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Back to Review
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
