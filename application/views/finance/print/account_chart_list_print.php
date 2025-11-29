<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart of Accounts - <?php echo company_info()->name; ?></title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
            background-color: #fff;
        }
        .print-header {
            border-bottom: 2px solid #000;
            text-align: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .print-header table {
            margin: 0 auto;
        }
        .print-header img {
            height: 50px;
            display: inline-block;
            vertical-align: top;
        }
        .print-header h2 {
            padding: 0px;
            margin: 0px;
            font-size: 23px;
        }
        .print-header h5 {
            padding: 0px;
            margin: 0px;
            font-size: 15px;
        }
        .report-title {
            text-align: center;
            margin: 20px 0;
        }
        .report-title h3 {
            margin: 0px;
            padding: 0px;
            font-size: 20px;
        }
        .report-title h4 {
            margin: 5px 0 0 0;
            padding: 0px;
            font-size: 14px;
            font-weight: normal;
        }
        .print-actions {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
        }
        .print-actions button {
            background-color: #1ab394;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 3px;
        }
        .print-actions button:hover {
            background-color: #18a689;
        }
        .ladder-container {
            margin-top: 10px;
        }
        .account-type-group {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .account-type-header {
            font-weight: bold;
            font-size: 14px;
            padding: 8px 0;
            border-bottom: 2px solid #000;
            margin-bottom: 5px;
            background-color: #f0f0f0;
            padding-left: 10px;
        }
        .account-sub-type-header {
            font-weight: bold;
            font-size: 12px;
            padding: 6px 0;
            border-bottom: 1px solid #999;
            margin-bottom: 3px;
            margin-top: 5px;
            background-color: #f8f8f8;
            padding-left: 30px;
        }
        .account-item {
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
            position: relative;
            min-height: 30px;
            line-height: 1.6;
        }
        .account-item.level-0 {
            padding-left: 15px;
            font-weight: bold;
            background-color: #f5f5f5;
            font-size: 13px;
        }
        .account-item.level-1 {
            padding-left: 50px;
            margin-left: 15px;
            position: relative;
        }
        .account-item.level-2 {
            padding-left: 90px;
            margin-left: 50px;
            position: relative;
        }
        .account-item.level-3 {
            padding-left: 130px;
            margin-left: 90px;
            position: relative;
        }
        .account-item.level-4 {
            padding-left: 170px;
            margin-left: 130px;
            position: relative;
        }
        .account-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            padding-left: 5px;
        }
        .account-number {
            display: table-cell;
            width: 130px;
            font-weight: bold;
            vertical-align: middle;
            padding-right: 5px;
        }
        .account-name {
            display: table-cell;
            width: auto;
            vertical-align: middle;
            padding-right: 10px;
        }
        .account-type-name {
            display: table-cell;
            width: 150px;
            vertical-align: middle;
            font-size: 11px;
            color: #666;
            padding-right: 10px;
        }
        .ladder-line {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #999;
        }
        .ladder-connector {
            position: absolute;
            left: 0;
            top: 50%;
            width: 15px;
            height: 2px;
            background-color: #999;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        @media print {
            body {
                padding: 10px;
            }
            .print-actions {
                display: none;
            }
            .print-header {
                page-break-after: avoid;
            }
            .account-type-group {
                page-break-inside: avoid;
            }
            .account-item {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <table>
            <tr>
                <td valign="top">
                    <img src="<?php echo base_url() . 'logo/' . company_info()->logo; ?>" alt="Logo" />
                </td>
                <td style="padding-left: 15px;">
                    <h2><strong><?php echo company_info()->name; ?></strong></h2>
                    <h5><strong>P.O.Box <?php echo strtoupper(company_info()->box); ?>, <?php echo strtoupper(lang('clientaccount_label_phone')); ?>: <?php echo company_info()->mobile; ?></strong></h5>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        <h3><strong>Chart of Accounts</strong></h3>
        <h4>As of <?php echo date('d-m-Y'); ?></h4>
    </div>

    <div class="ladder-container">
        <?php
        // Data is already sorted by account field (ASC) in the controller
        if (isset($account_chart_by_type) && count($account_chart_by_type) > 0) {
            $sno = 1;
            foreach ($account_chart_by_type as $type_id => $type_data) {
                $account_type_info = $type_data['info'];
                $accounts = $type_data['data']; // Already sorted by account field
                
                if (count($accounts) > 0) {
                    ?>
                    <div class="account-type-group">
                        <div class="account-type-header">
                            <?php echo strtoupper($account_type_info->name); ?> (Type: <?php echo $account_type_info->account; ?>)
                        </div>
                        <?php
                        // Group accounts by sub_account_type
                        $accounts_by_subtype = array();
                        $sub_type_info_map = array(); // Store sub type info for sorting
                        foreach ($accounts as $account) {
                            $sub_type_key = isset($account->sub_account_type) && !empty($account->sub_account_type) ? $account->sub_account_type : 'no_subtype';
                            if (!isset($accounts_by_subtype[$sub_type_key])) {
                                $accounts_by_subtype[$sub_type_key] = array();
                                // Get sub type info for sorting
                                if ($sub_type_key != 'no_subtype') {
                                    $sub_type_result = $this->finance_model->account_type_sub(null, $account_type_info->account, $sub_type_key);
                                    if ($sub_type_result && $sub_type_result->num_rows() > 0) {
                                        $sub_type_info_map[$sub_type_key] = $sub_type_result->row();
                                    }
                                }
                            }
                            $accounts_by_subtype[$sub_type_key][] = $account;
                        }
                        
                        // Sort sub types by sub_account code (ASC)
                        uksort($accounts_by_subtype, function($a, $b) use ($sub_type_info_map) {
                            // 'no_subtype' always comes last
                            if ($a == 'no_subtype') return 1;
                            if ($b == 'no_subtype') return -1;
                            
                            // Get sub account codes for comparison
                            $sub_account_a = isset($sub_type_info_map[$a]->sub_account) ? (int)$sub_type_info_map[$a]->sub_account : 0;
                            $sub_account_b = isset($sub_type_info_map[$b]->sub_account) ? (int)$sub_type_info_map[$b]->sub_account : 0;
                            return $sub_account_a - $sub_account_b;
                        });
                        
                        foreach ($accounts_by_subtype as $sub_type_key => $subtype_accounts) {
                            // Display sub type header if it exists
                            if ($sub_type_key != 'no_subtype') {
                                $sub_type_result = $this->finance_model->account_type_sub(null, $account_type_info->account, $sub_type_key);
                                if ($sub_type_result && $sub_type_result->num_rows() > 0) {
                                    $sub_type = $sub_type_result->row();
                                    ?>
                                    <div class="account-sub-type-header">
                                        <?php echo $sub_type->name; ?> (Sub Type: <?php echo $sub_type->sub_account; ?>)
                                    </div>
                                    <?php
                                }
                            }
                            
                            // Display accounts under this sub type
                            foreach ($subtype_accounts as $account) {
                                // Determine level based on account number structure
                                $account_str = (string)$account->account;
                                $level = 0;
                                
                                // Level detection based on trailing zeros or account structure
                                // Accounts ending in 0000 = Level 0 (Main)
                                // Accounts ending in 00 = Level 1 (Sub)
                                // Accounts ending in 0 = Level 2 (Detail)
                                // Other accounts = Level 3 (Sub-detail)
                                if (strlen($account_str) >= 4) {
                                    $last_4 = substr($account_str, -4);
                                    $last_2 = substr($account_str, -2);
                                    $last_1 = substr($account_str, -1);
                                    
                                    if ($last_4 == '0000') {
                                        $level = 0; // Main account
                                    } else if ($last_2 == '00') {
                                        $level = 1; // Sub-account
                                    } else if ($last_1 == '0') {
                                        $level = 2; // Detail account
                                    } else {
                                        $level = 3; // Sub-detail account
                                    }
                                }
                                
                                // Add extra indentation for accounts under sub type
                                if ($sub_type_key != 'no_subtype') {
                                    $level = max(1, $level + 1); // Ensure at least level 1 when under sub type
                                }
                                
                                ?>
                                <div class="account-item level-<?php echo $level; ?>">
                                    <div class="account-row">
                                        <div class="account-number"><?php echo $account->account; ?></div>
                                        <div class="account-name"><?php echo $account->name; ?></div>
                                        <div class="account-type-name"><?php echo $account_type_info->name; ?></div>
                                    </div>
                                </div>
                                <?php
                                $sno++;
                            }
                        }
                        ?>
                    </div>
                    <?php
                }
            }
        } else if (count($account_chart) > 0) {
            // Fallback: display flat list if grouped data not available
            // Data is already sorted by account field (ASC) in the controller
            $sno = 1;
            $current_type = null;
            foreach ($account_chart as $account) {
                $account_type = $this->finance_model->account_type(null, $account->account_type)->row();
                
                // Start new group if account type changes
                if ($current_type != $account->account_type) {
                    if ($current_type !== null) {
                        echo '</div>'; // Close previous group
                    }
                    $current_type = $account->account_type;
                    ?>
                    <div class="account-type-group">
                        <div class="account-type-header">
                            <?php echo isset($account_type->name) ? strtoupper($account_type->name) : 'Account Type ' . $account->account_type; ?> (Type: <?php echo $account->account_type; ?>)
                        </div>
                    <?php
                }
                
                // Determine level based on account number structure
                $account_str = (string)$account->account;
                $level = 0;
                if (strlen($account_str) >= 4) {
                    $last_4 = substr($account_str, -4);
                    $last_2 = substr($account_str, -2);
                    $last_1 = substr($account_str, -1);
                    
                    if ($last_4 == '0000') {
                        $level = 0; // Main account
                    } else if ($last_2 == '00') {
                        $level = 1; // Sub-account
                    } else if ($last_1 == '0') {
                        $level = 2; // Detail account
                    } else {
                        $level = 3; // Sub-detail account
                    }
                }
                ?>
                <div class="account-item level-<?php echo $level; ?>">
                    <div class="account-row">
                        <div class="account-number"><?php echo $account->account; ?></div>
                        <div class="account-name"><?php echo $account->name; ?></div>
                        <div class="account-type-name"><?php echo isset($account_type->name) ? $account_type->name : ''; ?></div>
                        <?php
                        // Get sub account type information
                        $sub_account_type = '';
                        if (isset($account->sub_account_type) && !empty($account->sub_account_type)) {
                            $sub_type_result = $this->finance_model->account_type_sub(null, $account->account_type, $account->sub_account_type);
                            if ($sub_type_result && $sub_type_result->num_rows() > 0) {
                                $sub_type = $sub_type_result->row();
                                $sub_account_type = $sub_type->name . ' (' . $sub_type->sub_account . ')';
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
                $sno++;
            }
            if ($current_type !== null) {
                echo '</div>'; // Close last group
            }
        } else {
            ?>
            <div class="account-item">
                <div class="account-row">
                    <div style="text-align: center; width: 100%; padding: 20px;">No accounts found</div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <div class="print-actions">
        <button onclick="window.print(); return false;"><i class="fa fa-print"></i> Print</button>
    </div>

    <div class="footer">
        <p>Printed on: <?php echo date('d-m-Y H:i:s'); ?></p>
    </div>

    <script>
        // Auto-trigger print dialog on page load (optional - uncomment if needed)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
