<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $company = function_exists('company_info_detail') ? company_info_detail() : null; $company_name = ($company && isset($company->name) && $company->name !== '') ? $company->name : 'Cooperative'; ?>
    <title><?php echo htmlspecialchars($company_name); ?> | <?php echo lang('cash_disbursement_print'); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 12px;
            color: #666;
            line-height: 1.5;
        }
        .document-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .disbursement-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            font-size: 13px;
        }
        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
        }
        .info-box strong {
            display: inline-block;
            width: 150px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }
        table thead {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
            font-size: 12px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 30px;
            width: 100%;
        }
        .amount-in-words {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #ddd;
            font-size: 13px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                padding: 0;
            }
            .disbursement-info {
                page-break-inside: avoid;
            }
            table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <?php if (file_exists(FCPATH . 'logo/logo.png')): ?>
                <img src="<?php echo base_url('logo/logo.png'); ?>" alt="Company Logo" class="logo">
            <?php endif; ?>
            <div class="company-name">TAPSTEMCO</div>
            <div class="company-info">
                <div><?php echo lang('cash_disbursement_statement'); ?></div>
                <div><?php echo date('F d, Y'); ?></div>
            </div>
        </div>

        <!-- Document Title -->
        <div class="document-title">
            <?php echo lang('cash_disbursement_voucher'); ?>
        </div>

        <!-- Disbursement Information -->
        <div class="disbursement-info">
            <div class="info-box">
                <div class="info-row">
                    <strong><?php echo lang('cash_disbursement_no'); ?>:</strong>
                    <span><?php echo $disburse->disburse_no; ?></span>
                </div>
                <div class="info-row">
                    <strong><?php echo lang('cash_disbursement_date'); ?>:</strong>
                    <span><?php echo date('d-m-Y', strtotime($disburse->disburse_date)); ?></span>
                </div>
                <div class="info-row">
                    <strong><?php echo lang('cash_disbursement_paid_to'); ?>:</strong>
                    <span><?php echo $disburse->paid_to; ?></span>
                </div>
            </div>
            <div class="info-box">
                <div class="info-row">
                    <strong><?php echo lang('cash_disbursement_payment_method'); ?>:</strong>
                    <span><?php echo $disburse->payment_method; ?></span>
                </div>
                <?php if ($disburse->payment_method == 'Cheque' && !empty($disburse->cheque_no)): ?>
                <div class="info-row">
                    <strong><?php echo lang('cash_disbursement_cheque_no'); ?>:</strong>
                    <span><?php echo $disburse->cheque_no; ?></span>
                </div>
                <div class="info-row">
                    <strong><?php echo lang('cash_disbursement_bank_name'); ?>:</strong>
                    <span><?php echo $disburse->bank_name; ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Description -->
        <?php if (!empty($disburse->description)): ?>
        <div class="info-box" style="grid-column: span 2;">
            <strong><?php echo lang('description'); ?>:</strong>
            <p><?php echo nl2br($disburse->description); ?></p>
        </div>
        <?php endif; ?>

        <!-- Line Items Table (Debit | Credit like journal entry) -->
        <table>
            <thead>
                <tr>
                    <th width="10%">#</th>
                    <th width="25%"><?php echo lang('cash_disbursement_account'); ?></th>
                    <th width="35%"><?php echo lang('cash_disbursement_line_description'); ?></th>
                    <th width="15%" class="text-right"><?php echo lang('journalentry_debit'); ?></th>
                    <th width="15%" class="text-right"><?php echo lang('journalentry_credit'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($line_items)): ?>
                    <?php $total_debit = 0; $total_credit = 0; $index = 1; ?>
                    <?php foreach ($line_items as $item): 
                        $item_debit = isset($item->debit) ? floatval($item->debit) : (isset($item->amount) ? floatval($item->amount) : 0);
                        $item_credit = isset($item->credit) ? floatval($item->credit) : 0;
                        $total_debit += $item_debit;
                        $total_credit += $item_credit;
                    ?>
                        <tr>
                            <td><?php echo $index++; ?></td>
                            <td><?php echo $item->account_name . ' (' . $item->account . ')'; ?></td>
                            <td><?php echo $item->description; ?></td>
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
                    <tr>
                        <td colspan="5" class="text-center"><?php echo lang('no_records_found'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Amount in Words -->
        <?php 
        $print_total = !empty($line_items) ? max($total_debit, $total_credit) : (isset($disburse->total_amount) ? $disburse->total_amount : 0);
        if ($print_total > 0): 
        ?>
        <div class="amount-in-words">
            <strong><?php echo lang('amount_in_words'); ?>:</strong>
            <?php 
            echo ucfirst(convert_number_to_words($print_total)) . ' only.';
            ?>
        </div>
        <?php endif; ?>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div><?php echo lang('prepared_by'); ?></div>
                <div class="signature-line"></div>
                <div><?php 
                    $user = $this->ion_auth->user($disburse->createdby)->row();
                    echo $user ? $user->first_name . ' ' . $user->last_name : 'N/A';
                ?></div>
            </div>
            <div class="signature-box">
                <div><?php echo lang('authorized_by'); ?></div>
                <div class="signature-line"></div>
                <div><?php echo lang('manager'); ?></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div><?php echo lang('note_this_document_is'); ?></div>
            <div><?php echo lang('printed_on'); ?> <?php echo date('d-m-Y H:i:s'); ?></div>
        </div>
    </div>
</body>
</html>
