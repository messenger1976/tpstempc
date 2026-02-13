<div class="row">
    <div class="col-lg-12">
        <div style=" padding: 30px 10px; margin: auto;">
            <div style="text-align: center;"> 
                <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Loan Aging Report</strong></h1>
                <h4><strong>As of <?php echo format_date($reportinfo->fromdate, false); ?></strong></h4>
                <?php if (!empty($reportinfo->description)) { ?>
                    <h5><strong><?php echo $reportinfo->description; ?></strong></h5>
                <?php } ?>
            </div>
            <div style="padding-top: 20px;">
                <!-- View Mode Toggle -->
                <div style="text-align: right; margin-bottom: 15px;">
                    <div class="btn-group" role="group">
                        <a href="<?php echo site_url(current_lang() . '/report_loan/loan_aging_view/' . $link_cat . '/' . $id . '?view_mode=grouped'); ?>" 
                           class="btn btn-sm <?php echo ($view_mode == 'grouped') ? 'btn-primary' : 'btn-default'; ?>">
                            <i class="fa fa-list"></i> Grouped View
                        </a>
                        <a href="<?php echo site_url(current_lang() . '/report_loan/loan_aging_view/' . $link_cat . '/' . $id . '?view_mode=tabular'); ?>" 
                           class="btn btn-sm <?php echo ($view_mode == 'tabular') ? 'btn-primary' : 'btn-default'; ?>">
                            <i class="fa fa-table"></i> Tabular View
                        </a>
                        <a href="<?php echo site_url(current_lang() . '/report_loan/loan_aging_view/' . $link_cat . '/' . $id . '?view_mode=columnar'); ?>" 
                           class="btn btn-sm <?php echo ($view_mode == 'columnar') ? 'btn-primary' : 'btn-default'; ?>">
                            <i class="fa fa-columns"></i> Columnar View
                        </a>
                    </div>
                </div>

                <style type="text/css">
                    .aging-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    .aging-table th { background-color: #f0f0f0; font-weight: bold; padding: 10px; text-align: left; border: 1px solid #ddd; }
                    .aging-table td { padding: 8px; border: 1px solid #ddd; }
                    .aging-table .text-right { text-align: right; }
                    .aging-table .text-center { text-align: center; }
                    .bucket-header { background-color: #e8e8e8 !important; font-weight: bold; }
                    .bucket-total { background-color: #f9f9f9; font-weight: bold; }
                </style>

                <?php 
                $grand_total_balance = 0;
                $grand_total_principal = 0;
                $grand_total_interest = 0;
                $grand_total_penalty = 0;
                $grand_total_loans = 0;
                
                foreach ($aging_data as $bucket_key => $bucket) {
                    if (count($bucket['loans']) > 0) {
                        $grand_total_balance += $bucket['total_balance'];
                        $grand_total_principal += $bucket['total_principal'];
                        $grand_total_interest += $bucket['total_interest'];
                        $grand_total_penalty += $bucket['total_penalty'];
                        $grand_total_loans += count($bucket['loans']);
                    }
                }
                ?>

                <div class="table-responsive" style="overflow: auto;">
                    <table class="aging-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">S/No</th>
                                <?php if ($view_mode == 'tabular') { ?>
                                    <th style="width: 150px;">Aging Bucket</th>
                                <?php } ?>
                                <?php if ($view_mode == 'columnar') { ?>
                                    <!-- Columnar format will have different headers -->
                                <?php } else { ?>
                                    <th style="width: 120px;">Loan ID</th>
                                    <th style="width: 150px;">Member ID</th>
                                    <th style="width: 250px;">Member Name</th>
                                    <th style="width: 150px;">Loan Type</th>
                                    <th style="width: 120px;">Disbursed Date</th>
                                    <th style="width: 120px;">Due Date</th>
                                    <th style="width: 100px;" class="text-center">Days Overdue</th>
                                    <th style="width: 150px;" class="text-right">Outstanding Principal</th>
                                    <th style="width: 150px;" class="text-right">Outstanding Interest</th>
                                    <th style="width: 150px;" class="text-right">Outstanding Penalty</th>
                                    <th style="width: 150px;" class="text-right">Total Outstanding</th>
                                <?php } ?>
                            </tr>
                            <?php if ($view_mode == 'columnar') { ?>
                                <tr>
                                    <th>S/No</th>
                                    <th>Loan ID</th>
                                    <th>Member ID</th>
                                    <th>Member Name</th>
                                    <th>Loan Type</th>
                                    <th>Disbursed Date</th>
                                    <th>Due Date</th>
                                    <th class="text-center">Days Overdue</th>
                                    <th class="text-right">Current<br/>(0-30 days)</th>
                                    <th class="text-right">31-60<br/>days</th>
                                    <th class="text-right">61-90<br/>days</th>
                                    <th class="text-right">91-180<br/>days</th>
                                    <th class="text-right">Over 180<br/>days</th>
                                    <th class="text-right">Total<br/>Outstanding</th>
                                </tr>
                            <?php } ?>
                        </thead>
                        <tbody>
                            <?php
                            if ($view_mode == 'columnar') {
                                // Columnar format: aging buckets as columns
                                $sno = 1;
                                $all_loans = array();
                                
                                // Collect all loans with their bucket info
                                foreach ($aging_data as $bucket_key => $bucket) {
                                    foreach ($bucket['loans'] as $loan) {
                                        $loan['bucket_key'] = $bucket_key;
                                        $all_loans[] = $loan;
                                    }
                                }
                                
                                // Sort by days overdue (descending)
                                usort($all_loans, function($a, $b) {
                                    return $b['days_overdue'] - $a['days_overdue'];
                                });
                                
                                if (count($all_loans) == 0) {
                                    ?>
                                    <tr>
                                        <td colspan="14" style="text-align: center; padding: 20px;">
                                            No loans with outstanding balances found as of <?php echo format_date($reportinfo->fromdate, false); ?>
                                        </td>
                                    </tr>
                                    <?php
                                } else {
                                    foreach ($all_loans as $loan) {
                                        $member_info = $this->member_model->member_basic_info(null, $loan['PID'])->row();
                                        $product_info = $this->setting_model->loanproduct($loan['product_type'])->row();
                                        
                                        // Determine which column to show the amount in
                                        $current_col = '';
                                        $col_31_60 = '';
                                        $col_61_90 = '';
                                        $col_91_180 = '';
                                        $col_over_180 = '';
                                        
                                        $bucket_key = $loan['bucket_key'];
                                        $amount = $loan['outstanding_balance'];
                                        
                                        if ($bucket_key == 'current') {
                                            $current_col = number_format($amount, 2);
                                        } elseif ($bucket_key == '31_60') {
                                            $col_31_60 = number_format($amount, 2);
                                        } elseif ($bucket_key == '61_90') {
                                            $col_61_90 = number_format($amount, 2);
                                        } elseif ($bucket_key == '91_180') {
                                            $col_91_180 = number_format($amount, 2);
                                        } elseif ($bucket_key == 'over_180') {
                                            $col_over_180 = number_format($amount, 2);
                                        }
                                        
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo $sno++; ?></td>
                                            <td><?php echo $loan['LID']; ?></td>
                                            <td><?php echo $loan['member_id']; ?></td>
                                            <td><?php echo $member_info ? ($member_info->firstname . ' ' . $member_info->middlename . ' ' . $member_info->lastname) : 'N/A'; ?></td>
                                            <td><?php echo $product_info ? $product_info->name : 'N/A'; ?></td>
                                            <td class="text-center"><?php echo format_date($loan['disbursedate'], false); ?></td>
                                            <td class="text-center"><?php echo $loan['oldest_unpaid_due_date'] ? format_date($loan['oldest_unpaid_due_date'], false) : 'N/A'; ?></td>
                                            <td class="text-center">
                                                <?php 
                                                if ($loan['days_overdue'] > 0) {
                                                    echo '<span style="color: red; font-weight: bold;">' . $loan['days_overdue'] . '</span>';
                                                } else {
                                                    echo '<span style="color: green;">' . $loan['days_overdue'] . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-right"><?php echo $current_col; ?></td>
                                            <td class="text-right"><?php echo $col_31_60; ?></td>
                                            <td class="text-right"><?php echo $col_61_90; ?></td>
                                            <td class="text-right"><?php echo $col_91_180; ?></td>
                                            <td class="text-right"><?php echo $col_over_180; ?></td>
                                            <td class="text-right"><strong><?php echo number_format($amount, 2); ?></strong></td>
                                        </tr>
                                        <?php
                                    }
                                    
                                    // Totals row
                                    ?>
                                    <tr style="background-color: #d0d0d0; font-weight: bold; font-size: 14px;">
                                        <td colspan="7" class="text-right"><strong>TOTAL:</strong></td>
                                        <td class="text-center"><strong><?php echo $grand_total_loans; ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($aging_data['current']['total_balance'], 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($aging_data['31_60']['total_balance'], 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($aging_data['61_90']['total_balance'], 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($aging_data['91_180']['total_balance'], 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($aging_data['over_180']['total_balance'], 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($grand_total_balance, 2); ?></strong></td>
                                    </tr>
                                    <?php
                                }
                            } elseif ($view_mode == 'tabular') {
                                // Tabular format: all loans in one table with aging bucket as a column
                                $sno = 1;
                                $all_loans = array();
                                
                                // Collect all loans with their bucket labels
                                foreach ($aging_data as $bucket_key => $bucket) {
                                    foreach ($bucket['loans'] as $loan) {
                                        $loan['bucket_label'] = $bucket['label'];
                                        $loan['bucket_key'] = $bucket_key;
                                        $all_loans[] = $loan;
                                    }
                                }
                                
                                // Sort by days overdue (descending) for better visibility
                                usort($all_loans, function($a, $b) {
                                    return $b['days_overdue'] - $a['days_overdue'];
                                });
                                
                                if (count($all_loans) == 0) {
                                    ?>
                                    <tr>
                                        <td colspan="13" style="text-align: center; padding: 20px;">
                                            No loans with outstanding balances found as of <?php echo format_date($reportinfo->fromdate, false); ?>
                                        </td>
                                    </tr>
                                    <?php
                                } else {
                                    foreach ($all_loans as $loan) {
                                        $member_info = $this->member_model->member_basic_info(null, $loan['PID'])->row();
                                        $product_info = $this->setting_model->loanproduct($loan['product_type'])->row();
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo $sno++; ?></td>
                                            <td><?php echo $loan['bucket_label']; ?></td>
                                            <td><?php echo $loan['LID']; ?></td>
                                            <td><?php echo $loan['member_id']; ?></td>
                                            <td><?php echo $member_info ? ($member_info->firstname . ' ' . $member_info->middlename . ' ' . $member_info->lastname) : 'N/A'; ?></td>
                                            <td><?php echo $product_info ? $product_info->name : 'N/A'; ?></td>
                                            <td class="text-center"><?php echo format_date($loan['disbursedate'], false); ?></td>
                                            <td class="text-center"><?php echo $loan['oldest_unpaid_due_date'] ? format_date($loan['oldest_unpaid_due_date'], false) : 'N/A'; ?></td>
                                            <td class="text-center">
                                                <?php 
                                                if ($loan['days_overdue'] > 0) {
                                                    echo '<span style="color: red; font-weight: bold;">' . $loan['days_overdue'] . '</span>';
                                                } else {
                                                    echo '<span style="color: green;">' . $loan['days_overdue'] . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-right"><?php echo number_format($loan['outstanding_principal'], 2); ?></td>
                                            <td class="text-right"><?php echo number_format($loan['outstanding_interest'], 2); ?></td>
                                            <td class="text-right"><?php echo number_format($loan['outstanding_penalty'], 2); ?></td>
                                            <td class="text-right"><strong><?php echo number_format($loan['outstanding_balance'], 2); ?></strong></td>
                                        </tr>
                                        <?php
                                    }
                                    
                                    // Add subtotals by bucket
                                    foreach ($aging_data as $bucket_key => $bucket) {
                                        if (count($bucket['loans']) > 0) {
                                            ?>
                                            <tr class="bucket-total">
                                                <td colspan="6" class="text-right"><strong>Subtotal for <?php echo $bucket['label']; ?>:</strong></td>
                                                <td colspan="2"></td>
                                                <td class="text-center"><strong><?php echo count($bucket['loans']); ?></strong></td>
                                                <td class="text-right"><strong><?php echo number_format($bucket['total_principal'], 2); ?></strong></td>
                                                <td class="text-right"><strong><?php echo number_format($bucket['total_interest'], 2); ?></strong></td>
                                                <td class="text-right"><strong><?php echo number_format($bucket['total_penalty'], 2); ?></strong></td>
                                                <td class="text-right"><strong><?php echo number_format($bucket['total_balance'], 2); ?></strong></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    
                                    // Grand total
                                    ?>
                                    <tr style="background-color: #d0d0d0; font-weight: bold; font-size: 14px;">
                                        <td colspan="6" class="text-right"><strong>GRAND TOTAL:</strong></td>
                                        <td colspan="2"></td>
                                        <td class="text-center"><strong><?php echo $grand_total_loans; ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($grand_total_principal, 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($grand_total_interest, 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($grand_total_penalty, 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($grand_total_balance, 2); ?></strong></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                // Grouped format: original format with bucket headers
                                $sno = 1;
                                foreach ($aging_data as $bucket_key => $bucket) {
                                    if (count($bucket['loans']) > 0) {
                                        ?>
                                        <tr class="bucket-header">
                                            <td colspan="12" style="font-size: 14px; padding: 12px;">
                                                <strong><?php echo $bucket['label']; ?> (<?php echo count($bucket['loans']); ?> loan(s))</strong>
                                            </td>
                                        </tr>
                                        <?php
                                        foreach ($bucket['loans'] as $loan) {
                                            $member_info = $this->member_model->member_basic_info(null, $loan['PID'])->row();
                                            $product_info = $this->setting_model->loanproduct($loan['product_type'])->row();
                                            ?>
                                            <tr>
                                                <td class="text-center"><?php echo $sno++; ?></td>
                                                <td><?php echo $loan['LID']; ?></td>
                                                <td><?php echo $loan['member_id']; ?></td>
                                                <td><?php echo $member_info ? ($member_info->firstname . ' ' . $member_info->middlename . ' ' . $member_info->lastname) : 'N/A'; ?></td>
                                                <td><?php echo $product_info ? $product_info->name : 'N/A'; ?></td>
                                                <td class="text-center"><?php echo format_date($loan['disbursedate'], false); ?></td>
                                                <td class="text-center"><?php echo $loan['oldest_unpaid_due_date'] ? format_date($loan['oldest_unpaid_due_date'], false) : 'N/A'; ?></td>
                                                <td class="text-center">
                                                    <?php 
                                                    if ($loan['days_overdue'] > 0) {
                                                        echo '<span style="color: red; font-weight: bold;">' . $loan['days_overdue'] . '</span>';
                                                    } else {
                                                        echo '<span style="color: green;">' . $loan['days_overdue'] . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-right"><?php echo number_format($loan['outstanding_principal'], 2); ?></td>
                                                <td class="text-right"><?php echo number_format($loan['outstanding_interest'], 2); ?></td>
                                                <td class="text-right"><?php echo number_format($loan['outstanding_penalty'], 2); ?></td>
                                                <td class="text-right"><strong><?php echo number_format($loan['outstanding_balance'], 2); ?></strong></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr class="bucket-total">
                                            <td colspan="7" class="text-right"><strong>Subtotal for <?php echo $bucket['label']; ?>:</strong></td>
                                            <td class="text-center"><strong><?php echo count($bucket['loans']); ?></strong></td>
                                            <td class="text-right"><strong><?php echo number_format($bucket['total_principal'], 2); ?></strong></td>
                                            <td class="text-right"><strong><?php echo number_format($bucket['total_interest'], 2); ?></strong></td>
                                            <td class="text-right"><strong><?php echo number_format($bucket['total_penalty'], 2); ?></strong></td>
                                            <td class="text-right"><strong><?php echo number_format($bucket['total_balance'], 2); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="12" style="height: 10px; border: none;"></td>
                                        </tr>
                                        <?php
                                    }
                                }
                                
                                if ($grand_total_loans == 0) {
                                    ?>
                                    <tr>
                                        <td colspan="12" style="text-align: center; padding: 20px;">
                                            No loans with outstanding balances found as of <?php echo format_date($reportinfo->fromdate, false); ?>
                                        </td>
                                    </tr>
                                    <?php
                                } else {
                                    ?>
                                    <tr style="background-color: #d0d0d0; font-weight: bold; font-size: 14px;">
                                        <td colspan="7" class="text-right"><strong>GRAND TOTAL:</strong></td>
                                        <td class="text-center"><strong><?php echo $grand_total_loans; ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($grand_total_principal, 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($grand_total_interest, 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($grand_total_penalty, 2); ?></strong></td>
                                        <td class="text-right"><strong><?php echo number_format($grand_total_balance, 2); ?></strong></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
        <div style="text-align: center; padding-top: 20px; border-top: 1px solid #000;">
            <a href="<?php echo site_url(current_lang() . '/report_loan/loan_aging_print/' . $link_cat . '/' . $id . '?view_mode=' . $view_mode); ?>" class="btn btn-primary">Print</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_loan/loan_aging_export/' . $link_cat . '/' . $id . '?view_mode=' . $view_mode); ?>" class="btn btn-success"><i class="fa fa-file-excel-o"></i> <?php echo lang('export_excel'); ?></a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_loan/create_loan_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
        </div>
    </div>
</div>
