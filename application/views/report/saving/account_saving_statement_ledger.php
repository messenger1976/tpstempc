<?php
$company = company_info();
$embed = !empty($embed);
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$display_acct = (!empty($account_info) && !empty($account_info->old_members_acct))
    ? $account_info->old_members_acct
    : $account;
$acct_name = $this->finance_model->saving_account_name($account);
$member_id = '';
if (!empty($account_info)) {
    if (!empty($account_info->members_member_id)) {
        $member_id = $account_info->members_member_id;
    } elseif (!empty($account_info->member_id)) {
        $member_id = $account_info->member_id;
    }
}
$avail = !empty($account_info) ? floatval($account_info->balance) : 0;
$maint = !empty($account_info) ? floatval($account_info->virtual_balance) : 0;
$total_bal = $avail + $maint;
$print_url = site_url(current_lang() . '/report_saving/new_saving_account_statement_print/' . $link_cat . '/' . $id . '/' . encode_id($account));
$export_url = site_url(current_lang() . '/report_saving/new_saving_account_statement_export/' . $link_cat . '/' . $id . '/' . encode_id($account));
$transaction = isset($transaction) ? $transaction : array();

$request_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
    . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')
    . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
?>
<div class="sal-ledger-wrap" style="padding: <?php echo $embed ? '12px 14px' : '20px 8px'; ?>; margin: auto; max-width: 1100px;">
    <table style="width:100%; margin-bottom: 8px;">
        <tr>
            <td style="width:70px; vertical-align:top;">
                <?php if (!empty($company->logo)) { ?>
                    <img src="<?php echo base_url() . 'logo/' . $company->logo; ?>" style="height:56px;" alt="logo"/>
                <?php } ?>
            </td>
            <td style="text-align:center; vertical-align:middle;">
                <div style="font-weight:bold; font-size:14px; text-transform:uppercase;">
                    <?php echo htmlspecialchars($company->name); ?>
                </div>
                <div style="font-size:12px;">
                    <?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?>
                </div>
                <div style="font-weight:bold; font-size:15px; margin-top:6px;">
                    SAVINGS ACCOUNT LEDGER
                </div>
                <div style="font-size:12px; margin-top:3px;">
                    For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?>
                </div>
            </td>
            <td style="width:90px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:12px;">
                LENDING
            </td>
        </tr>
    </table>

    <table style="width:100%; margin-bottom:12px; font-size:12px; border-collapse:collapse;">
        <tr>
            <td style="padding:3px 0; width:50%;"><strong>Account No:</strong> <?php echo htmlspecialchars($display_acct); ?></td>
            <td style="padding:3px 0;"><strong>Member ID:</strong> <?php echo $member_id !== '' ? htmlspecialchars($member_id) : '&mdash;'; ?></td>
        </tr>
        <tr>
            <td style="padding:3px 0;"><strong>Account Name:</strong> <?php echo htmlspecialchars($acct_name); ?></td>
            <td style="padding:3px 0;">
                <strong>Available:</strong> <?php echo number_format($avail, 2); ?>
                &nbsp;|&nbsp; <strong>Maintaining:</strong> <?php echo number_format($maint, 2); ?>
                &nbsp;|&nbsp; <strong>Total:</strong> <?php echo number_format($total_bal, 2); ?>
            </td>
        </tr>
    </table>

    <div style="overflow-x:auto; <?php echo $embed ? 'max-height:52vh; overflow-y:auto;' : ''; ?>">
        <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:720px;">
            <thead>
                <tr>
                    <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:100px;">Date</th>
                    <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Description</th>
                    <th style="text-align:left; border-bottom:1px solid #000; padding:4px; width:90px;">Method</th>
                    <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:100px;">Debit</th>
                    <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:100px;">Credit</th>
                    <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Balance</th>
                    <?php if (!$embed) { ?>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:70px;">Action</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
            <?php
            $balance = 0;
            $credit = 0;
            $debit = 0;
            if (!empty($transaction)) {
                $balance = floatval($transaction[0]->credit_total) - floatval($transaction[0]->debit_total);
                ?>
                <tr>
                    <td style="padding:3px 4px;"></td>
                    <td style="padding:3px 4px; font-weight:bold;">BROUGHT FORWARD BALANCE</td>
                    <td style="padding:3px 4px;"></td>
                    <td style="padding:3px 4px;"></td>
                    <td style="padding:3px 4px;"></td>
                    <td style="text-align:right; padding:3px 4px; font-weight:bold;"><?php echo number_format($balance, 2); ?></td>
                    <?php if (!$embed) { ?><td></td><?php } ?>
                </tr>
                <?php
                foreach ($transaction as $value) {
                    $dt = explode(' ', $value->trans_date);
                    if ($value->debit > 0) {
                        $balance -= $value->debit;
                        $debit += $value->debit;
                    } elseif ($value->credit > 0) {
                        $balance += $value->credit;
                        $credit += $value->credit;
                    }
                    $desc = trim($value->system_comment . (isset($value->comment) && $value->comment !== '' ? ' — ' . $value->comment : ''));
                    ?>
                    <tr>
                        <td style="text-align:center; padding:3px 4px; white-space:nowrap;"><?php echo format_date($dt[0], false); ?></td>
                        <td style="padding:3px 4px;"><?php echo htmlspecialchars($desc); ?></td>
                        <td style="padding:3px 4px;"><?php echo htmlspecialchars($value->paymethod); ?></td>
                        <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo ($value->debit > 0 ? number_format($value->debit, 2) : ''); ?></td>
                        <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo ($value->credit > 0 ? number_format($value->credit, 2) : ''); ?></td>
                        <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo number_format($balance, 2); ?></td>
                        <?php if (!$embed) { ?>
                            <td style="text-align:center; padding:3px 4px;">
                                <button type="button" class="btn btn-success btn-xs btn-outline"
                                    data-id="<?php echo $value->id; ?>"
                                    data-toggle="modal"
                                    data-target="#myModal<?php echo $value->id; ?>">
                                    <i class="fa fa-edit"></i>
                                </button>

                                <div class="modal inmodal" id="myModal<?php echo $value->id; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content animated FadeIn">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                                <h2 class="modal-title"><i class="fa fa-laptop modal-icon"></i> Edit Entry</h2>
                                            </div>
                                            <?php echo form_open_multipart($request_url, 'class="form-horizontal" id="querydataform' . $value->id . '"'); ?>
                                            <div class="modal-body">
                                                <input type="hidden" name="trans_id<?php echo $value->id; ?>" id="trans_id<?php echo $value->id; ?>" value="<?php echo $value->id; ?>">
                                                <div class="form-group"><label>Trans Date</label> <input type="text" class="form-control" name="trans_date<?php echo $value->id; ?>" id="trans_date<?php echo $value->id; ?>" value="<?php echo format_date($dt[0], false); ?>"></div>
                                                <div class="form-group"><label>Description</label> <input type="text" class="form-control" name="description<?php echo $value->id; ?>" id="description<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->system_comment); ?>"></div>
                                                <div class="form-group"><label>Remarks</label> <input type="text" class="form-control" name="comment<?php echo $value->id; ?>" id="comment<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->comment); ?>"></div>
                                                <div class="form-group"><label>Payment Method</label> <input type="text" class="form-control" name="paymentmethod<?php echo $value->id; ?>" id="paymentmethod<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->paymethod); ?>"></div>
                                                <div class="form-group"><label>Trans Type</label> <input type="text" class="form-control" name="trans_type<?php echo $value->id; ?>" id="trans_type<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->trans_type); ?>"></div>
                                                <div class="form-group"><label>Amount</label> <input type="text" class="form-control" name="amount<?php echo $value->id; ?>" id="amount<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->amount); ?>"></div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                (function () {
                                    if (typeof jQuery === 'undefined') return;
                                    jQuery(function ($) {
                                        $('#querydataform<?php echo $value->id; ?>').on('submit', function (e) {
                                            e.preventDefault();
                                            $.ajax({
                                                url: '<?php echo site_url(current_lang() . '/report_saving/saving_edit_entry/'); ?>',
                                                type: 'POST',
                                                data: {
                                                    id: $('#trans_id<?php echo $value->id; ?>').val(),
                                                    trans_date: $('#trans_date<?php echo $value->id; ?>').val(),
                                                    description: $('#description<?php echo $value->id; ?>').val(),
                                                    comment: $('#comment<?php echo $value->id; ?>').val(),
                                                    paymentmethod: $('#paymentmethod<?php echo $value->id; ?>').val(),
                                                    trans_type: $('#trans_type<?php echo $value->id; ?>').val(),
                                                    amount: $('#amount<?php echo $value->id; ?>').val()
                                                },
                                                success: function () {
                                                    location.href = <?php echo json_encode($request_url); ?>;
                                                }
                                            });
                                        });
                                    });
                                })();
                                </script>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                }
            } else {
                $cols = $embed ? 6 : 7;
                ?>
                <tr>
                    <td colspan="<?php echo $cols; ?>" style="text-align:center; padding:18px; color:#999; font-style:italic;">
                        No savings transactions found for this account in the selected period.
                    </td>
                </tr>
            <?php } ?>
                <tr>
                    <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="3"></td>
                    <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                        <span style="float:left;">P</span><?php echo number_format($debit, 2); ?>
                    </td>
                    <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                        <span style="float:left;">P</span><?php echo number_format($credit, 2); ?>
                    </td>
                    <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                        <span style="float:left;">P</span><?php echo number_format($balance, 2); ?>
                    </td>
                    <?php if (!$embed) { ?>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;"></td>
                    <?php } ?>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="text-align:right; font-size:14px; font-weight:bold; margin-top:10px;">
        Ending Balance: P<?php echo number_format($balance, 2); ?>
    </div>

    <div style="text-align:center; margin-top:18px;">
        <button type="button" class="btn btn-success js-download-report-pdf"
                data-pdf-url="<?php echo htmlspecialchars($print_url); ?>"
                data-pdf-name="Savings_Account_Ledger.pdf">
            <i class="fa fa-download"></i> Download PDF
        </button>
        &nbsp;
        <a href="<?php echo $print_url; ?>" class="btn btn-primary" target="_blank"><i class="fa fa-print"></i> Print</a>
        &nbsp;
        <a href="<?php echo $export_url; ?>" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export</a>
        <?php if ($embed) { ?>
            &nbsp;
            <button type="button" class="btn btn-default" onclick="if (window.parent && window.parent.closeSavingLedgerPopup) { window.parent.closeSavingLedgerPopup(); }">Close</button>
        <?php } else { ?>
            &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_accountlist_view/' . $link_cat . '/' . $id); ?>" class="btn btn-default">Back</a>
            &nbsp;
            <?php echo anchor('#', 'Process Balances', 'class="btn btn-warning" id="btnprocessbalances"'); ?>
        <?php } ?>
    </div>
</div>

<?php if (!$embed) { ?>
<script>
(function () {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }
        jQuery(function ($) {
            $('#btnprocessbalances').on('click', function (e) {
                e.preventDefault();
                $('#ibox-main').children('.ibox-content').addClass('sk-loading');
                $('body').css('cursor', 'wait');
                recomputebalances();
            });
        });
        async function recomputebalances() {
            let response = await fetch('<?php echo site_url(current_lang() . '/report_saving/recomputebalancesindividual/' . $account . '/' . $balance); ?>');
            let totalrecdata = await response.json();
            await new Promise(function (resolve) { setTimeout(resolve, 1000); });
            jQuery('#ibox-main').children('.ibox-content').removeClass('sk-loading');
            jQuery('body').css('cursor', 'default');
            if (totalrecdata.success == 1) {
                swal({ title: 'Good job!', text: 'Saving balances are successfully process!', icon: 'success', button: 'Close' });
            } else {
                swal({ title: 'Process Failed!', text: 'Savings balances are not process!', icon: 'error', button: 'Close' });
            }
            return true;
        }
    }
    initScripts();
})();
</script>
<?php } ?>
