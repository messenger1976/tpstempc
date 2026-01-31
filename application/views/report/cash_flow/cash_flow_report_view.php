<div class="row">
    <div class="col-lg-12">
        <div style="padding: 30px 10px; margin: auto;">
            <div style="text-align: center;">
                <h3><strong><?php echo company_info()->name; ?></strong></h3>
                <h1><strong>Cash Flow Statement</strong></h1>
                <h4><strong>For the period from <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?></strong></h4>
            </div>
            <div style="padding-top: 20px;">
                <style type="text/css">
                    table.table tbody tr td{
                        border: 0px;
                    }
                </style>

                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align: left; width: 500px;"></th>
                            <th style="text-align: right; width: 250px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $cash_flow = $cash_flow_data; ?>
                        
                        <!-- Operating Activities -->
                        <tr>
                            <td colspan="2"><strong>CASH FLOWS FROM OPERATING ACTIVITIES</strong></td>
                        </tr>
                        
                        <!-- Cash Receipts (Inflows) -->
                        <?php if (!empty($cash_flow['operating_activities']['cash_inflows'])): ?>
                            <?php foreach ($cash_flow['operating_activities']['cash_inflows'] as $inflow): ?>
                                <tr>
                                    <td style="padding-left: 40px;">
                                        <?php echo htmlspecialchars($inflow['description']); ?>
                                        <?php if (!empty($inflow['received_from'])): ?>
                                            <small style="color: #666;">(from: <?php echo htmlspecialchars($inflow['received_from']); ?>)</small>
                                        <?php endif; ?>
                                        <br/>
                                        <small style="color: #999;"><?php echo $inflow['reference']; ?> - <?php echo format_date($inflow['date'], false); ?></small>
                                    </td>
                                    <td style="text-align: right;"><?php echo number_format($inflow['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td style="padding-left: 40px;">No cash receipts</td>
                                <td style="text-align: right;">0.00</td>
                            </tr>
                        <?php endif; ?>
                        
                        <!-- Cash Disbursements (Outflows) -->
                        <?php if (!empty($cash_flow['operating_activities']['cash_outflows'])): ?>
                            <?php foreach ($cash_flow['operating_activities']['cash_outflows'] as $outflow): ?>
                                <tr>
                                    <td style="padding-left: 40px;">
                                        <?php echo htmlspecialchars($outflow['description']); ?>
                                        <?php if (!empty($outflow['paid_to'])): ?>
                                            <small style="color: #666;">(to: <?php echo htmlspecialchars($outflow['paid_to']); ?>)</small>
                                        <?php endif; ?>
                                        <br/>
                                        <small style="color: #999;"><?php echo $outflow['reference']; ?> - <?php echo format_date($outflow['date'], false); ?></small>
                                    </td>
                                    <td style="text-align: right;">(<?php echo number_format($outflow['amount'], 2); ?>)</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td style="padding-left: 40px;">No cash disbursements</td>
                                <td style="text-align: right;">(0.00)</td>
                            </tr>
                        <?php endif; ?>
                        
                        <!-- Net Cash from Operating Activities -->
                        <tr>
                            <td style="padding-left: 60px; border-bottom: 1px solid #000; border-top: 1px solid #000;"><strong>Net Cash from Operating Activities</strong></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;">
                                <strong><?php echo number_format($cash_flow['operating_activities']['net_cash'], 2); ?></strong>
                            </td>
                        </tr>
                        
                        <tr><td colspan="2"><br/></td></tr>
                        
                        <!-- Investing Activities -->
                        <tr>
                            <td colspan="2"><strong>CASH FLOWS FROM INVESTING ACTIVITIES</strong></td>
                        </tr>
                        
                        <?php if (!empty($cash_flow['investing_activities']['cash_inflows']) || !empty($cash_flow['investing_activities']['cash_outflows'])): ?>
                            <?php foreach ($cash_flow['investing_activities']['cash_inflows'] as $inflow): ?>
                                <tr>
                                    <td style="padding-left: 40px;">
                                        <?php echo htmlspecialchars($inflow['description']); ?>
                                        <br/>
                                        <small style="color: #999;"><?php echo $inflow['reference']; ?> - <?php echo format_date($inflow['date'], false); ?></small>
                                    </td>
                                    <td style="text-align: right;"><?php echo number_format($inflow['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php foreach ($cash_flow['investing_activities']['cash_outflows'] as $outflow): ?>
                                <tr>
                                    <td style="padding-left: 40px;">
                                        <?php echo htmlspecialchars($outflow['description']); ?>
                                        <br/>
                                        <small style="color: #999;"><?php echo $outflow['reference']; ?> - <?php echo format_date($outflow['date'], false); ?></small>
                                    </td>
                                    <td style="text-align: right;">(<?php echo number_format($outflow['amount'], 2); ?>)</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td style="padding-left: 40px;">No investing activities</td>
                                <td style="text-align: right;">0.00</td>
                            </tr>
                        <?php endif; ?>
                        
                        <!-- Net Cash from Investing Activities -->
                        <tr>
                            <td style="padding-left: 60px; border-bottom: 1px solid #000; border-top: 1px solid #000;"><strong>Net Cash from Investing Activities</strong></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;">
                                <strong><?php echo number_format($cash_flow['investing_activities']['net_cash'], 2); ?></strong>
                            </td>
                        </tr>
                        
                        <tr><td colspan="2"><br/></td></tr>
                        
                        <!-- Financing Activities -->
                        <tr>
                            <td colspan="2"><strong>CASH FLOWS FROM FINANCING ACTIVITIES</strong></td>
                        </tr>
                        
                        <!-- Financing Inflows -->
                        <?php if (!empty($cash_flow['financing_activities']['cash_inflows'])): ?>
                            <?php foreach ($cash_flow['financing_activities']['cash_inflows'] as $inflow): ?>
                                <tr>
                                    <td style="padding-left: 40px;">
                                        <?php echo htmlspecialchars($inflow['description']); ?>
                                        <br/>
                                        <small style="color: #999;"><?php echo $inflow['reference']; ?> - <?php echo format_date($inflow['date'], false); ?></small>
                                    </td>
                                    <td style="text-align: right;"><?php echo number_format($inflow['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Financing Outflows -->
                        <?php if (!empty($cash_flow['financing_activities']['cash_outflows'])): ?>
                            <?php foreach ($cash_flow['financing_activities']['cash_outflows'] as $outflow): ?>
                                <tr>
                                    <td style="padding-left: 40px;">
                                        <?php echo htmlspecialchars($outflow['description']); ?>
                                        <br/>
                                        <small style="color: #999;"><?php echo $outflow['reference']; ?> - <?php echo format_date($outflow['date'], false); ?></small>
                                    </td>
                                    <td style="text-align: right;">(<?php echo number_format($outflow['amount'], 2); ?>)</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (empty($cash_flow['financing_activities']['cash_inflows']) && empty($cash_flow['financing_activities']['cash_outflows'])): ?>
                            <tr>
                                <td style="padding-left: 40px;">No financing activities</td>
                                <td style="text-align: right;">0.00</td>
                            </tr>
                        <?php endif; ?>
                        
                        <!-- Net Cash from Financing Activities -->
                        <tr>
                            <td style="padding-left: 60px; border-bottom: 1px solid #000; border-top: 1px solid #000;"><strong>Net Cash from Financing Activities</strong></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;">
                                <strong><?php echo number_format($cash_flow['financing_activities']['net_cash'], 2); ?></strong>
                            </td>
                        </tr>
                        
                        <tr><td colspan="2"><br/><br/></td></tr>
                        
                        <!-- Net Increase/Decrease in Cash -->
                        <tr>
                            <td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><strong>Net Increase (Decrease) in Cash</strong></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;">
                                <strong><?php echo number_format($cash_flow['total_net_cash_flow'], 2); ?></strong>
                            </td>
                        </tr>
                        
                        <!-- Beginning Cash -->
                        <tr>
                            <td><strong>Cash at Beginning of Period</strong></td>
                            <td style="text-align: right;"><?php echo number_format($cash_flow['beginning_cash'], 2); ?></td>
                        </tr>
                        
                        <!-- Ending Cash -->
                        <tr>
                            <td style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><strong>Cash at End of Period</strong></td>
                            <td style="text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;">
                                <strong><?php echo number_format($cash_flow['ending_cash'], 2); ?></strong>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div style="text-align: center; margin-top: 20px;">
        <a href="<?php echo site_url(current_lang() . '/report/cash_flow_report_print/' . $id); ?>" class="btn btn-primary">Print</a>
        &nbsp; &nbsp; &nbsp; &nbsp;
        <a href="<?php echo site_url(current_lang() . '/report/cash_flow_report/' . $id); ?>" class="btn btn-primary">Edit</a>
    </div>
</div>
