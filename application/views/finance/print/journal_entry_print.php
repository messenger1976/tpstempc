<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo lang('journal_entry_view'); ?> #<?php echo $entry->entryid; ?> - <?php echo company_info()->name; ?></title>
    <style type="text/css">
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; background-color: #fff; }
        .print-header { border-bottom: 2px solid #000; text-align: center; padding-bottom: 15px; margin-bottom: 20px; }
        .print-header h2 { font-size: 20px; margin: 0; }
        .print-header h5 { font-size: 13px; margin: 5px 0 0; font-weight: normal; }
        .report-title { text-align: center; margin: 15px 0 20px; }
        .report-title h3 { font-size: 18px; margin: 0; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table th, .info-table td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        .info-table th { background: #f5f5f5; width: 25%; }
        .line-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .line-table th, .line-table td { border: 1px solid #ccc; padding: 6px 8px; }
        .line-table th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .print-actions { text-align: center; margin-bottom: 20px; }
        .print-actions button { background-color: #1ab394; color: white; border: none; padding: 10px 20px; font-size: 14px; cursor: pointer; border-radius: 3px; }
        @media print { .print-actions { display: none; } }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print();"><?php echo lang('print'); ?></button>
    </div>

    <div class="print-header">
        <h2><?php echo htmlspecialchars(company_info()->name); ?></h2>
        <h5><?php echo lang('journal_entry_view'); ?></h5>
    </div>

    <div class="report-title">
        <h3><?php echo lang('journal_entry_no'); ?> #<?php echo $entry->entryid; ?></h3>
    </div>

    <table class="info-table">
        <tr>
            <th><?php echo lang('journalentry_date'); ?></th>
            <td><?php echo date('d-m-Y', strtotime($entry->entrydate)); ?></td>
            <th><?php echo lang('status'); ?></th>
            <td><?php echo $entry->is_posted ? lang('journal_entry_status_posted') : lang('journal_entry_status_draft'); ?></td>
        </tr>
        <tr>
            <th><?php echo lang('journalentry_reference_no'); ?></th>
            <td><?php echo !empty($entry->reference_no) ? htmlspecialchars($entry->reference_no) : '—'; ?></td>
            <th><?php echo lang('journalentry_description'); ?></th>
            <td><?php echo htmlspecialchars($entry->description); ?></td>
        </tr>
    </table>

    <table class="line-table">
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo lang('account_code'); ?></th>
                <th><?php echo lang('journalentry_account'); ?></th>
                <th><?php echo lang('journalentry_account_description'); ?></th>
                <th class="text-right"><?php echo lang('journalentry_debit'); ?></th>
                <th class="text-right"><?php echo lang('journalentry_credit'); ?></th>
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
                        <td class="text-right"><?php echo number_format($item->debit, 2); ?></td>
                        <td class="text-right"><?php echo number_format($item->credit, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="font-weight: bold; background: #f5f5f5;">
                    <td colspan="4" class="text-right"><?php echo lang('journalentry_total'); ?>:</td>
                    <td class="text-right"><?php echo number_format($entry->total_debit, 2); ?></td>
                    <td class="text-right"><?php echo number_format($entry->total_credit, 2); ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="6"><?php echo lang('no_records_found'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
