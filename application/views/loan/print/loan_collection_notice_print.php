<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $company = function_exists('company_info_detail') ? company_info_detail() : (function_exists('company_info') ? company_info() : null);
    $company_name = ($company && isset($company->name) && $company->name !== '') ? $company->name : 'Cooperative';
    $due = isset($due) ? $due : null;
    $paydate = isset($paydate) ? $paydate : date('Y-m-d');
    $member_name = '';
    $member_id = '';
    if (!empty($member)) {
        $member_id = isset($member->member_id) ? $member->member_id : '';
        $member_name = trim($member->firstname . ' ' . $member->middlename . ' ' . $member->lastname);
    }
    $interval_label = '';
    if (!empty($interval)) {
        $interval_label = isset($interval->description) ? $interval->description : (isset($interval->name) ? $interval->name : '');
    }
    $net_payable = $due ? (float) $due->suggested_amount : 0;
    ?>
    <title><?php echo htmlspecialchars($company_name); ?> | <?php echo lang('loan_collection_notice'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #fff; color: #222; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .no-print { margin-bottom: 15px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #333; padding-bottom: 16px; }
        .logo { max-width: 120px; height: auto; margin-bottom: 8px; }
        .company-name { font-size: 22px; font-weight: bold; margin-bottom: 4px; }
        .company-info { font-size: 12px; color: #555; line-height: 1.45; }
        .document-title { text-align: center; font-size: 17px; font-weight: bold; margin: 18px 0 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .document-subtitle { text-align: center; font-size: 12px; color: #555; margin-bottom: 18px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; font-size: 13px; }
        .info-box { border: 1px solid #ddd; padding: 12px 14px; }
        .info-row { margin-bottom: 6px; }
        .info-row strong { display: inline-block; min-width: 150px; }
        .section-title { font-size: 14px; font-weight: bold; margin: 18px 0 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .note { font-size: 12px; color: #444; background: #f8f8f8; border: 1px solid #e0e0e0; padding: 10px 12px; margin: 10px 0 14px; line-height: 1.45; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 14px; font-size: 12px; }
        table thead { background: #f5f5f5; }
        table th, table td { padding: 8px 10px; border: 1px solid #ddd; }
        table th { text-align: left; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background: #f5f5f5; font-weight: bold; }
        .payable-box { border: 2px solid #333; padding: 14px 16px; margin: 16px 0; font-size: 15px; }
        .payable-box .label { font-weight: bold; }
        .payable-box .amount { font-size: 22px; font-weight: bold; float: right; }
        .clearfix { clear: both; }
        .footer { margin-top: 28px; padding-top: 14px; border-top: 1px solid #ddd; text-align: center; font-size: 11px; color: #666; }
        .signature-section { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 36px; font-size: 12px; }
        .signature-box { text-align: center; }
        .signature-line { border-top: 1px solid #000; margin-top: 40px; width: 100%; }
        @media print {
            body { margin: 0; }
            .container { padding: 0; }
            .no-print { display: none !important; }
            table, .info-grid, .payable-box { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button type="button" onclick="window.print();" style="padding:8px 14px;cursor:pointer;">
                <?php echo lang('loan_collection_notice_print'); ?>
            </button>
            <button type="button" onclick="window.close();" style="padding:8px 14px;cursor:pointer;margin-left:6px;">
                <?php echo lang('button_cancel'); ?>
            </button>
        </div>

        <div class="header">
            <?php if (defined('FCPATH') && file_exists(FCPATH . 'logo/logo.png')): ?>
                <img src="<?php echo base_url('logo/logo.png'); ?>" alt="Logo" class="logo">
            <?php endif; ?>
            <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
            <div class="company-info">
                <?php if ($company && !empty($company->address)): ?>
                    <div><?php echo htmlspecialchars($company->address); ?></div>
                <?php endif; ?>
                <?php if ($company && !empty($company->mobile)): ?>
                    <div><?php echo htmlspecialchars($company->mobile); ?></div>
                <?php endif; ?>
                <?php if ($company && !empty($company->email)): ?>
                    <div><?php echo htmlspecialchars($company->email); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="document-title"><?php echo lang('loan_collection_notice'); ?></div>
        <div class="document-subtitle">
            <?php echo lang('loan_collection_notice_as_of'); ?>:
            <strong><?php echo htmlspecialchars(format_date($paydate, FALSE)); ?></strong>
            &nbsp;|&nbsp;
            <?php echo lang('loan_collection_notice_printed'); ?>:
            <?php echo date('d-m-Y H:i'); ?>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <div class="info-row"><strong><?php echo lang('loan_LID'); ?>:</strong> <?php echo htmlspecialchars($loaninfo->LID); ?></div>
                <div class="info-row"><strong><?php echo lang('member_member_id'); ?>:</strong> <?php echo htmlspecialchars($member_id); ?></div>
                <div class="info-row"><strong><?php echo lang('member_name'); ?>:</strong> <?php echo htmlspecialchars($member_name); ?></div>
                <div class="info-row"><strong><?php echo lang('loan_product'); ?>:</strong> <?php echo htmlspecialchars($product ? $product->name : '—'); ?></div>
            </div>
            <div class="info-box">
                <div class="info-row"><strong><?php echo lang('loan_disburse_date'); ?>:</strong>
                    <?php echo (!empty($disburse) && !empty($disburse->disbursedate)) ? htmlspecialchars(format_date($disburse->disbursedate, FALSE)) : '—'; ?>
                </div>
                <div class="info-row"><strong><?php echo lang('loan_applied_amount'); ?>:</strong> <?php echo number_format((float) $loaninfo->basic_amount, 2); ?></div>
                <div class="info-row"><strong><?php echo lang('loan_installment_amount'); ?>:</strong> <?php echo number_format((float) $loaninfo->installment_amount, 2); ?></div>
                <div class="info-row"><strong><?php echo lang('loanproduct_interest'); ?>:</strong>
                    <?php echo (isset($loaninfo->rate) && $loaninfo->rate !== '' && $loaninfo->rate !== null) ? htmlspecialchars($loaninfo->rate) . '%' : '—'; ?>
                    <?php if ($interval_label !== ''): ?> / <?php echo (int) $loaninfo->number_istallment . ' ' . htmlspecialchars($interval_label); ?><?php endif; ?>
                </div>
                <div class="info-row"><strong><?php echo lang('loanproduct_penalt_percentage'); ?>:</strong>
                    <?php echo ($product && $product->penalt_percentage !== '' && $product->penalt_percentage !== null) ? htmlspecialchars($product->penalt_percentage) . '%' : '—'; ?>
                </div>
            </div>
        </div>

        <div class="section-title"><?php echo lang('loan_collection_notice_computation'); ?></div>
        <div class="note">
            <?php
            echo sprintf(
                lang('loan_repay_due_explanation'),
                ($due && isset($due->grace_days)) ? (int) $due->grace_days : (defined('MAX_NUMBER_DAYS_OVERDUE_PENALT') ? (int) MAX_NUMBER_DAYS_OVERDUE_PENALT : 0),
                ($due && isset($due->penalt_percentage)) ? rtrim(rtrim(number_format((float) $due->penalt_percentage, 2), '0'), '.') : '0'
            );
            ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th><?php echo lang('loan_installment'); ?></th>
                    <th><?php echo lang('due_date'); ?></th>
                    <th><?php echo lang('index_status_th'); ?></th>
                    <th class="text-right"><?php echo lang('loan_installment_amount'); ?></th>
                    <th class="text-right"><?php echo lang('loan_ledger_penalty'); ?></th>
                    <th class="text-right"><?php echo lang('loan_repay_penalty_months'); ?></th>
                    <th class="text-right"><?php echo lang('total'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($due && !empty($due->items)): ?>
                    <?php foreach ($due->items as $item): ?>
                        <tr>
                            <td><?php echo (int) $item->installment; ?></td>
                            <td><?php echo htmlspecialchars(format_date($item->due_date, FALSE)); ?></td>
                            <td><?php echo $item->status === 'overdue' ? lang('loan_repay_status_overdue') : lang('loan_repay_status_due'); ?></td>
                            <td class="text-right"><?php echo number_format((float) $item->installment_amount, 2); ?></td>
                            <td class="text-right"><?php echo number_format((float) $item->penalty, 2); ?></td>
                            <td class="text-right"><?php echo (int) $item->penalty_months; ?></td>
                            <td class="text-right"><strong><?php echo number_format((float) $item->total, 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7"><?php echo lang('loan_repay_nothing_due'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right"><?php echo lang('loan_repay_total_due'); ?></td>
                    <td class="text-right"><?php echo number_format($due ? (float) $due->total_installments : 0, 2); ?></td>
                    <td class="text-right"><?php echo number_format($due ? (float) $due->total_penalty : 0, 2); ?></td>
                    <td></td>
                    <td class="text-right"><?php echo number_format($due ? (float) $due->total_due : 0, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right"><?php echo lang('loan_repay_carry_balance'); ?></td>
                    <td class="text-right"><?php echo number_format($due ? (float) $due->carry_balance : 0, 2); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="6" class="text-right"><?php echo lang('loan_repay_net_due'); ?></td>
                    <td class="text-right"><?php echo number_format($due ? (float) $due->net_due : 0, 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="payable-box">
            <span class="label"><?php echo lang('loan_collection_notice_total_payable'); ?>:</span>
            <span class="amount"><?php echo number_format($net_payable, 2); ?></span>
            <div class="clearfix"></div>
        </div>

        <p style="font-size:12px;color:#555;margin:0 0 8px;">
            <?php echo lang('loan_collection_notice_formula'); ?>
        </p>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div><?php echo lang('loan_collection_notice_collector'); ?></div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div><?php echo lang('loan_collection_notice_member_ack'); ?></div>
            </div>
        </div>

        <div class="footer">
            <?php echo lang('loan_collection_notice_footer'); ?>
        </div>
    </div>
    <script>
        window.addEventListener('load', function () {
            // Auto-open print dialog when opened from the repayment page
            if (window.location.search.indexOf('autoprint=1') >= 0) {
                setTimeout(function () { window.print(); }, 300);
            }
        });
    </script>
</body>
</html>
