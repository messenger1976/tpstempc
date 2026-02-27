<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $company = function_exists('company_info_detail') ? company_info_detail() : null; $company_name = ($company && isset($company->name) && $company->name !== '') ? $company->name : 'Cooperative'; ?>
    <title><?php echo htmlspecialchars($company_name); ?> | <?php echo lang('loan_disbursement_print'); ?></title>

    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #fff; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #333; padding-bottom: 20px; }
        .logo { max-width: 150px; height: auto; margin-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .company-info { font-size: 12px; color: #666; line-height: 1.5; }
        .document-title { text-align: center; font-size: 18px; font-weight: bold; margin: 20px 0; text-transform: uppercase; }
        .disbursement-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; font-size: 13px; }
        .info-box { border: 1px solid #ddd; padding: 15px; }
        .info-box strong { display: inline-block; width: 160px; }
        .info-row { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 12px; }
        table thead { background-color: #f5f5f5; border: 1px solid #ddd; }
        table th { padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd; }
        table td { padding: 10px; border: 1px solid #ddd; }
        table tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #f5f5f5; font-weight: bold; }
        .amount-in-words { background-color: #f9f9f9; padding: 15px; margin: 20px 0; border: 1px solid #ddd; font-size: 13px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 11px; color: #666; }
        .signature-section { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 40px; font-size: 12px; }
        .signature-box { text-align: center; }
        .signature-line { border-top: 1px solid #000; margin-top: 30px; width: 100%; }
        @media print {
            body { margin: 0; padding: 0; }
            .container { padding: 0; }
            .disbursement-info { page-break-inside: avoid; }
            table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <?php if (defined('FCPATH') && file_exists(FCPATH . 'logo/logo.png')): ?>
                <img src="<?php echo base_url('logo/logo.png'); ?>" alt="Company Logo" class="logo">
            <?php endif; ?>
            <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
            <div class="company-info">
                <div><?php echo lang('loan_disbursement_statement'); ?></div>
                <div><?php echo date('F d, Y'); ?></div>
            </div>
        </div>

        <div class="document-title"><?php echo lang('loan_disbursement_voucher'); ?></div>

        <?php
        $member = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
        $member_name = $member ? trim($member->firstname . ' ' . $member->middlename . ' ' . $member->lastname) : '';
        $disburse_no = isset($disburse->disburse_no) ? trim((string) $disburse->disburse_no) : '';
        $payment_method = isset($disburse->payment_method) ? trim((string) $disburse->payment_method) : '';
        ?>

        <div class="disbursement-info">
            <div class="info-box">
                <?php if ($disburse_no !== ''): ?>
                <div class="info-row"><strong><?php echo lang('loan_disburse_no'); ?>:</strong> <span><?php echo htmlspecialchars($disburse_no); ?></span></div>
                <?php endif; ?>
                <div class="info-row"><strong><?php echo lang('loan_LID'); ?>:</strong> <span><?php echo htmlspecialchars($loaninfo->LID); ?></span></div>
                <div class="info-row"><strong><?php echo lang('member_member_id'); ?>:</strong> <span><?php echo $member ? htmlspecialchars($member->member_id) : ''; ?></span></div>
                <div class="info-row"><strong><?php echo lang('member_name'); ?>:</strong> <span><?php echo htmlspecialchars($member_name); ?></span></div>
            </div>
            <div class="info-box">
                <div class="info-row"><strong><?php echo lang('loan_disburse_date'); ?>:</strong> <span><?php echo isset($disburse->disbursedate) ? date('d-m-Y', strtotime($disburse->disbursedate)) : ''; ?></span></div>
                <?php if ($payment_method !== ''): ?>
                <div class="info-row"><strong><?php echo lang('loan_disburse_payment_method'); ?>:</strong> <span><?php echo htmlspecialchars($payment_method); ?></span></div>
                <?php endif; ?>
                <?php if (!empty($ledger_entry_id)): ?>
                <div class="info-row"><strong>GL Entry #:</strong> <span><?php echo (int) $ledger_entry_id; ?></span></div>
                <?php endif; ?>
                <div class="info-row"><strong><?php echo lang('loan_applied_amount'); ?>:</strong> <span><?php echo number_format($loaninfo->basic_amount, 2); ?></span></div>
            </div>
        </div>

        <?php if (!empty($disburse->comment)): ?>
        <div class="info-box">
            <strong><?php echo lang('loan_comment'); ?>:</strong>
            <p><?php echo nl2br(htmlspecialchars($disburse->comment)); ?></p>
        </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th width="10%">#</th>
                    <th width="30%"><?php echo lang('account_code'); ?></th>
                    <th width="30%"><?php echo lang('journalentry_account_description'); ?></th>
                    <th width="15%" class="text-right"><?php echo lang('journalentry_debit'); ?></th>
                    <th width="15%" class="text-right"><?php echo lang('journalentry_credit'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($line_items)): ?>
                    <?php $total_debit = 0; $total_credit = 0; $index = 1; ?>
                    <?php foreach ($line_items as $item):
                        $item_debit = isset($item->debit) ? floatval($item->debit) : 0;
                        $item_credit = isset($item->credit) ? floatval($item->credit) : 0;
                        $total_debit += $item_debit;
                        $total_credit += $item_credit;
                        $acc_label = trim((string) $item->account);
                        $acc_name = isset($item->account_name) ? trim((string) $item->account_name) : '';
                        $acc_full = $acc_name !== '' ? ($acc_name . ' (' . $acc_label . ')') : $acc_label;
                        $desc = isset($item->description) ? $item->description : '';
                    ?>
                    <tr>
                        <td><?php echo $index++; ?></td>
                        <td><?php echo htmlspecialchars($acc_full); ?></td>
                        <td><?php echo htmlspecialchars($desc); ?></td>
                        <td class="text-right"><?php echo number_format($item_debit, 2); ?></td>
                        <td class="text-right"><?php echo number_format($item_credit, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3" class="text-right"><?php echo lang('total'); ?>:</td>
                        <td class="text-right"><?php echo number_format($total_debit, 2); ?></td>
                        <td class="text-right"><?php echo number_format($total_credit, 2); ?></td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center"><?php echo lang('no_records_found'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $print_total = !empty($line_items) ? max($total_debit, $total_credit) : floatval($loaninfo->basic_amount);
        if ($print_total > 0):
        ?>
        <div class="amount-in-words">
            <strong><?php echo lang('amount_in_words'); ?>:</strong>
            <?php echo ucfirst(convert_number_to_words($print_total)) . ' only.'; ?>
        </div>
        <?php endif; ?>

        <div class="signature-section">
            <div class="signature-box">
                <div><?php echo lang('prepared_by'); ?></div>
                <div class="signature-line"></div>
                <div>
                    <?php
                    $user = isset($disburse->createdby) ? $this->ion_auth->user($disburse->createdby)->row() : null;
                    echo $user ? htmlspecialchars($user->first_name . ' ' . $user->last_name) : 'N/A';
                    ?>
                </div>
            </div>
            <div class="signature-box">
                <div><?php echo lang('authorized_by'); ?></div>
                <div class="signature-line"></div>
                <div><?php echo lang('manager'); ?></div>
            </div>
        </div>

        <div class="footer">
            <div><?php echo lang('note_this_document_is'); ?></div>
            <div><?php echo lang('printed_on'); ?> <?php echo date('d-m-Y H:i:s'); ?></div>
        </div>
    </div>
</body>
</html>

