<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Receipt - <?php echo $receipt->receipt_no; ?></title>
    <link href="<?php echo base_url(); ?>media/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fff;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 2px solid #000;
        }
        .company-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .company-info {
            font-size: 12px;
            margin: 5px 0;
        }
        .receipt-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
        }
        .receipt-info {
            margin: 20px 0;
        }
        .info-row {
            margin: 10px 0;
            clear: both;
        }
        .info-label {
            font-weight: bold;
            width: 180px;
            display: inline-block;
        }
        .info-value {
            display: inline-block;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .amount-words {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #000;
        }
        .signature-section {
            margin-top: 60px;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            width: 250px;
            display: inline-block;
        }
        .signature-block {
            display: inline-block;
            width: 45%;
            vertical-align: top;
        }
        @media print {
            .no-print {
                display: none;
            }
            .receipt-container {
                border: none;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Company Header -->
        <div class="company-header">
            <h1 class="company-name"><?php echo company_info()->name; ?></h1>
            <div class="company-info"><?php echo company_info()->address; ?></div>
            <div class="company-info">Tel: <?php echo company_info()->phone; ?></div>
            <?php if (!empty(company_info()->email)): ?>
                <div class="company-info">Email: <?php echo company_info()->email; ?></div>
            <?php endif; ?>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">CASH RECEIPT</div>

        <!-- Receipt Information -->
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">Receipt No:</span>
                <span class="info-value"><strong><?php echo $receipt->receipt_no; ?></strong></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value"><?php echo date('d-m-Y', strtotime($receipt->receipt_date)); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Received From:</span>
                <span class="info-value"><strong><?php echo $receipt->received_from; ?></strong></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value"><?php echo $receipt->payment_method; ?></span>
            </div>
            <?php if ($receipt->payment_method == 'Cheque' && !empty($receipt->cheque_no)): ?>
            <div class="info-row">
                <span class="info-label">Cheque No:</span>
                <span class="info-value"><?php echo $receipt->cheque_no; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Bank Name:</span>
                <span class="info-value"><?php echo $receipt->bank_name; ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <div class="info-row">
            <span class="info-label">Description:</span>
            <span class="info-value"><?php echo $receipt->description; ?></span>
        </div>

        <!-- Line Items Table (Debit | Credit like journal entry) -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="35%">Account</th>
                    <th width="30%">Description</th>
                    <th width="15%" class="text-right">Debit</th>
                    <th width="15%" class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($line_items)): ?>
                    <?php $index = 1; $total_debit = 0; $total_credit = 0; ?>
                    <?php foreach ($line_items as $item): 
                        $item_debit = isset($item->debit) ? floatval($item->debit) : 0;
                        $item_credit = isset($item->credit) ? floatval($item->credit) : (isset($item->amount) ? floatval($item->amount) : 0);
                        $total_debit += $item_debit;
                        $total_credit += $item_credit;
                    ?>
                        <tr>
                            <td class="text-center"><?php echo $index++; ?></td>
                            <td><?php echo $item->account_name; ?></td>
                            <td><?php echo $item->description; ?></td>
                            <td class="text-right"><?php echo number_format($item_debit, 2); ?></td>
                            <td class="text-right"><?php echo number_format($item_credit, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3" class="text-right">TOTAL:</td>
                        <td class="text-right"><?php echo number_format($total_debit, 2); ?></td>
                        <td class="text-right"><?php echo number_format($total_credit, 2); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Amount in Words -->
        <div class="amount-words">
            <strong>Amount in Words:</strong> 
            <?php 
            // Convert amount to words (basic implementation)
            $amount_words = ucwords(convert_number_to_words($receipt->total_amount));
            echo $amount_words . ' Only';
            ?>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-block">
                <div>Received By:</div>
                <div class="signature-line"></div>
                <div>Signature & Date</div>
            </div>
            <div class="signature-block" style="float: right;">
                <div>Authorized By:</div>
                <div class="signature-line"></div>
                <div>Signature & Date</div>
            </div>
        </div>

        <!-- Print Button -->
        <div class="no-print text-center" style="margin-top: 40px;">
            <button onclick="window.print();" class="btn btn-primary">
                <i class="fa fa-print"></i> Print Receipt
            </button>
            <a href="<?php echo site_url(current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($receipt->id)); ?>" class="btn btn-default">
                Back
            </a>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo base_url(); ?>media/js/jquery-3.1.1.min.js"></script>
    <script src="<?php echo base_url(); ?>media/js/bootstrap.min.js"></script>
    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
