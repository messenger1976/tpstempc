<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$account = isset($account) ? $account : (!empty($reportinfo->description) ? $reportinfo->description : '');
$account_info = isset($account_info) ? $account_info : ($account !== '' ? $this->finance_model->saving_account_balance($account) : null);
$display_acct = (!empty($account_info) && !empty($account_info->old_members_acct))
    ? $account_info->old_members_acct
    : $account;
$acct_name = $account !== '' ? $this->finance_model->saving_account_name($account) : '';
$member_id = '';
if (!empty($account_info)) {
    $member_id = !empty($account_info->member_id) ? $account_info->member_id : '';
}
$acct_type_name = '-';
if (!empty($account_info) && !empty($account_info->account_cat)) {
    $atype = $this->finance_model->saving_account_list(null, $account_info->account_cat)->row();
    if ($atype && !empty($atype->name)) {
        $acct_type_name = $atype->name;
    }
}
$avail = !empty($account_info) ? floatval($account_info->balance) : 0;
$maint = !empty($account_info) ? floatval($account_info->virtual_balance) : 0;
$total_bal = $avail + $maint;
$transaction = isset($transaction) ? $transaction : array();

$request_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
    . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')
    . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
$print_url = site_url(current_lang() . '/report_saving/saving_account_statement_print/' . $link_cat . '/' . $id);
$export_url = site_url(current_lang() . '/report_saving/saving_account_statement_export/' . $link_cat . '/' . $id);
$edit_url = site_url(current_lang() . '/report_saving/saving_account_report_title/' . $link_cat . '/' . $id);
$back_url = site_url(current_lang() . '/report_saving/saving_account_report/' . $link_cat);
?>
<div class="row">
    <div class="col-lg-12">
        <div style="padding: 20px 8px; margin: auto; max-width: 1100px; overflow-x: auto;">
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
                            SAVINGS ACCOUNT STATEMENT
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
                    <td style="padding:3px 0;"><strong>Account Type:</strong> <?php echo htmlspecialchars($acct_type_name); ?></td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:3px 0;">
                        <strong>Available:</strong> <?php echo number_format($avail, 2); ?>
                        &nbsp;|&nbsp; <strong>Maintaining:</strong> <?php echo number_format($maint, 2); ?>
                        &nbsp;|&nbsp; <strong>Total:</strong> <?php echo number_format($total_bal, 2); ?>
                    </td>
                </tr>
            </table>

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:800px;">
                <thead>
                    <tr>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:100px;">Date</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Description</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px; width:90px;">Method</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:100px;">Debit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:100px;">Credit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Balance</th>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:120px;">Action</th>
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
                        <td></td>
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
                            <td style="text-align:center; padding:3px 4px; white-space:nowrap;">
                                <button type="button" class="btn btn-info btn-xs btn-outline btn-ledger"
                                    data-receipt="<?php echo htmlspecialchars($value->receipt); ?>"
                                    data-transdate="<?php echo format_date($dt[0], false); ?>"
                                    data-description="<?php echo htmlspecialchars($value->system_comment); ?>">
                                    <i class="fa fa-book"></i>
                                </button>
                                <button type="button" class="btn btn-success btn-xs btn-outline"
                                    data-toggle="modal" data-target="#myModal<?php echo $value->id; ?>">
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
                                                <input type="hidden" id="trans_id<?php echo $value->id; ?>" value="<?php echo $value->id; ?>">
                                                <div class="form-group"><label>Trans Date</label> <input type="text" class="form-control" id="trans_date<?php echo $value->id; ?>" value="<?php echo format_date($dt[0], false); ?>"></div>
                                                <div class="form-group"><label>Description</label> <input type="text" class="form-control" id="description<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->system_comment); ?>"></div>
                                                <div class="form-group"><label>Remarks</label> <input type="text" class="form-control" id="comment<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->comment); ?>"></div>
                                                <div class="form-group"><label>Payment Method</label> <input type="text" class="form-control" id="paymentmethod<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->paymethod); ?>"></div>
                                                <div class="form-group"><label>Trans Type</label> <input type="text" class="form-control" id="trans_type<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->trans_type); ?>"></div>
                                                <div class="form-group"><label>Amount</label> <input type="text" class="form-control" id="amount<?php echo $value->id; ?>" value="<?php echo htmlspecialchars($value->amount); ?>"></div>
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
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:18px; color:#999; font-style:italic;">
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
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;"></td>
                    </tr>
                </tbody>
            </table>

            <div style="text-align:right; font-size:14px; font-weight:bold; margin-top:10px;">
                Ending Balance: P<?php echo number_format($balance, 2); ?>
            </div>

            <table style="width:100%; margin-top:36px; font-size:12px;">
                <tr>
                    <td style="width:33%; vertical-align:top; text-align:center;">
                        Certified Correct:<br/>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                        <span style="font-weight:bold; text-decoration:underline;">ANTONINA P. PATUNGAN</span><br/>
                        Bookkeeper
                    </td>
                    <td style="width:33%; vertical-align:top; text-align:center;">
                        Checked by:<br/>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                        <span style="font-weight:bold; text-decoration:underline;">ANA MARIE F. VALMORIA</span><br/>
                        AICOM
                    </td>
                    <td style="width:33%; vertical-align:top; text-align:center;">
                        Noted by:<br/>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                        <span style="font-weight:bold; text-decoration:underline;">REMEDIOS T. AUXTERO</span><br/>
                        Manager
                    </td>
                </tr>
            </table>
        </div>

        <div style="text-align:center; margin-top:20px;">
            <a href="<?php echo $back_url; ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
            &nbsp;
            <a href="<?php echo $export_url; ?>" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export to Excel</a>
            &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintSavingStatement"><i class="fa fa-print"></i> Print</button>
            &nbsp;
            <a href="<?php echo $edit_url; ?>" class="btn btn-primary">Edit</a>
        </div>
    </div>
</div>

<!-- GL entries modal -->
<div class="modal inmodal fade" id="ledgerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeIn">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h2 class="modal-title"><i class="fa fa-book modal-icon"></i> Transaction Accounting Entries</h2>
            </div>
            <div class="modal-body">
                <div id="ledger-loading" style="text-align: center; padding: 20px;">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p>Loading accounting entries...</p>
                </div>
                <div id="ledger-content" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Transaction Date:</strong> <span id="ledger-trans-date"></span></p>
                            <p><strong>Description:</strong> <span id="ledger-description"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Receipt No:</strong> <span id="ledger-receipt"></span></p>
                            <p><strong>Reference:</strong> <span id="ledger-reference"></span></p>
                        </div>
                    </div>
                    <hr>
                    <h4>Journal Entries:</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th style="text-align: right;">Debit</th>
                                    <th style="text-align: right;">Credit</th>
                                </tr>
                            </thead>
                            <tbody id="ledger-entries-table"></tbody>
                            <tfoot>
                                <tr style="font-weight: bold;">
                                    <td colspan="2" style="text-align: right;">Total:</td>
                                    <td style="text-align: right;" id="ledger-total-debit">0.00</td>
                                    <td style="text-align: right;" id="ledger-total-credit">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="ledger-no-entries" style="display: none; padding: 20px; text-align: center;">
                        <p class="text-muted">No accounting entries found for this transaction.</p>
                    </div>
                </div>
                <div id="ledger-error" style="display: none; padding: 20px; text-align: center;">
                    <p class="text-danger"><i class="fa fa-exclamation-triangle"></i> <span id="ledger-error-message"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
$sas_pdf_url = $print_url;
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#savingStmtPdfOverlay {
    display: none; position: fixed; left: 0; top: 0; right: 0; bottom: 0;
    z-index: 99999; background: rgba(0,0,0,0.55);
}
#savingStmtPdfOverlay.is-open { display: block; }
#savingStmtPdfOverlay .sas-dialog {
    position: absolute; left: 5%; top: 4%; width: 90%; height: 92%;
    background: #fff; border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; overflow: hidden;
}
#savingStmtPdfOverlay .sas-header {
    padding: 12px 15px; border-bottom: 1px solid #e7eaec; background: #f8f8f8; flex: 0 0 auto;
}
#savingStmtPdfOverlay .sas-header h4 { margin: 0; display: inline-block; font-size: 16px; font-weight: 600; }
#savingStmtPdfOverlay .sas-header .sas-actions { float: right; }
#savingStmtPdfOverlay .sas-body { flex: 1 1 auto; min-height: 0; background: #f3f3f4; }
#savingStmtPdfOverlay .sas-body iframe { width: 100%; height: 100%; border: 0; display: block; }
</style>
<div id="savingStmtPdfOverlay" aria-hidden="true">
    <div class="sas-dialog" role="dialog">
        <div class="sas-header">
            <h4>Savings Account Statement - PDF</h4>
            <div class="sas-actions">
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeSavingStmtPdf();">Close</button>
            </div>
        </div>
        <div class="sas-body">
            <iframe id="savingStmtPdfFrame" src="about:blank" title="Savings Account Statement PDF"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var pdfUrl = <?php echo json_encode($sas_pdf_url); ?>;

    window.openSavingStmtPdf = function () {
        var overlay = document.getElementById('savingStmtPdfOverlay');
        var frame = document.getElementById('savingStmtPdfFrame');
        if (!overlay || !frame) {
            window.open(pdfUrl, '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        frame.src = viewerBase + encodeURIComponent(pdfUrl + (pdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now());
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    window.closeSavingStmtPdf = function () {
        var overlay = document.getElementById('savingStmtPdfOverlay');
        var frame = document.getElementById('savingStmtPdfFrame');
        if (frame) frame.src = 'about:blank';
        if (overlay) {
            overlay.className = '';
            overlay.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
    };

    function bind() {
        var btn = document.getElementById('btnPrintSavingStatement');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.openSavingStmtPdf();
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) window.closeSavingStmtPdf();
        });
        document.addEventListener('click', function (e) {
            var overlay = document.getElementById('savingStmtPdfOverlay');
            if (overlay && overlay.className.indexOf('is-open') !== -1 && e.target === overlay) {
                window.closeSavingStmtPdf();
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();

(function () {
    function initLedger() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initLedger, 50);
            return;
        }
        jQuery(function ($) {
            function number_format(number, decimals) {
                return parseFloat(number).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            $('.btn-ledger').on('click', function () {
                var receipt = $(this).data('receipt');
                var transDate = $(this).data('transdate');
                var description = $(this).data('description');
                $('#ledgerModal').modal('show');
                $('#ledger-loading').show();
                $('#ledger-content').hide();
                $('#ledger-error').hide();
                $('#ledger-entries-table').empty();
                $.ajax({
                    url: '<?php echo site_url(current_lang() . '/report_saving/get_transaction_ledger_entries'); ?>',
                    type: 'POST',
                    data: { receipt: receipt },
                    dataType: 'json',
                    success: function (response) {
                        $('#ledger-loading').hide();
                        if (response.success) {
                            if (response.entries && response.entries.length > 0) {
                                $('#ledger-trans-date').text(transDate);
                                $('#ledger-description').text(description);
                                $('#ledger-receipt').text(receipt);
                                $('#ledger-reference').text(response.entries[0].linkto || 'N/A');
                                var totalDebit = 0, totalCredit = 0, html = '';
                                $.each(response.entries, function (index, entry) {
                                    var d = parseFloat(entry.debit) || 0;
                                    var c = parseFloat(entry.credit) || 0;
                                    totalDebit += d;
                                    totalCredit += c;
                                    html += '<tr><td>' + entry.account + '</td><td>' + entry.account_name + '</td>'
                                        + '<td style="text-align:right;">' + (d > 0 ? number_format(d, 2) : '') + '</td>'
                                        + '<td style="text-align:right;">' + (c > 0 ? number_format(c, 2) : '') + '</td></tr>';
                                });
                                $('#ledger-entries-table').html(html);
                                $('#ledger-total-debit').text(number_format(totalDebit, 2));
                                $('#ledger-total-credit').text(number_format(totalCredit, 2));
                                $('#ledger-content').show();
                                $('#ledger-no-entries').hide();
                            } else {
                                $('#ledger-content').show();
                                $('#ledger-no-entries').show();
                            }
                        } else {
                            $('#ledger-error-message').text(response.message || 'Failed to load accounting entries.');
                            $('#ledger-error').show();
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#ledger-loading').hide();
                        $('#ledger-error-message').text('An error occurred: ' + error);
                        $('#ledger-error').show();
                    }
                });
            });
        });
    }
    initLedger();
})();
</script>
