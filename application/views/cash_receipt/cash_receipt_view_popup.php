<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php $company = function_exists('company_info_detail') ? company_info_detail() : null; $company_name = ($company && isset($company->name) && $company->name !== '') ? $company->name : 'Cooperative'; ?>
    <title><?php echo htmlspecialchars($company_name); ?> | <?php echo lang('cash_receipt_view'); ?> - <?php echo htmlspecialchars(isset($receipt->receipt_no) ? $receipt->receipt_no : '', ENT_QUOTES, 'UTF-8'); ?></title>

    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/animate.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet">
    <style>
        body { background: #f3f3f4; }
        .wrapper { padding: 15px 15px 30px; }
        .cr-view-page { margin-top: 0; }
    </style>
</head>
<body class="white-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <?php
        $this->load->view('cash_receipt/cash_receipt_view', array(
            'receipt' => isset($receipt) ? $receipt : null,
            'id' => isset($id) ? $id : '',
            'line_items' => isset($line_items) ? $line_items : array(),
            'accounting_entries' => isset($accounting_entries) ? $accounting_entries : array(),
            'message' => isset($message) ? $message : null,
            'warning' => isset($warning) ? $warning : null,
            'is_popup' => true,
        ));
        ?>
    </div>
</div>
</body>
</html>
