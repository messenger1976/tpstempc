<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$transaction = isset($transaction) ? $transaction : array();
$account_names = array();
$ledger_url = function ($account) use ($link_cat, $id) {
    return site_url(current_lang() . '/report_saving/new_saving_account_statement_view/' . $link_cat . '/' . $id . '/' . encode_id($account) . '?embed=1');
};

$format_cr_dr = function ($amount) {
    $amount = floatval($amount);
    if ($amount > 0) {
        return number_format($amount, 2) . ' Cr';
    }
    if ($amount < 0) {
        return number_format(abs($amount), 2) . ' Dr';
    }
    return number_format(0, 2);
};
?>
<div class="row">
    <div class="col-lg-12">
        <div style="padding: 20px 8px; margin: auto; max-width: 1200px; overflow-x: auto;">
            <table style="width:100%; margin-bottom: 8px;">
                <tr>
                    <td style="width:70px; vertical-align:top;">
                        <?php if (!empty($company->logo)) { ?>
                            <img src="<?php echo base_url() . 'logo/' . $company->logo; ?>" style="height:60px;" alt="logo"/>
                        <?php } ?>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <div style="font-weight:bold; font-size:14px; text-transform:uppercase;">
                            <?php echo htmlspecialchars($company->name); ?>
                        </div>
                        <div style="font-size:12px;">
                            <?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?>
                        </div>
                        <div style="font-weight:bold; font-size:15px; margin-top:8px;">
                            SAVINGS ACCOUNT TRANSACTIONS SUMMARY
                        </div>
                        <div style="font-size:13px; margin-top:4px;">
                            For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?>
                        </div>
                    </td>
                    <td style="width:90px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:12px;">
                        LENDING
                    </td>
                </tr>
            </table>

            <style type="text/css">
                a.sats-link {
                    color: #1ab394;
                    text-decoration: none;
                    font-weight: 600;
                    cursor: pointer;
                }
                a.sats-link:hover {
                    text-decoration: underline;
                    color: #18a689;
                }
            </style>

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:1000px;">
                <thead>
                    <tr>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:45px;">S/No</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px; width:110px;">Account No</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px; width:100px;">Member ID</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Account Name</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:120px;">Opening Balance</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Debit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Credit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:120px;">Closing Balance</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (empty($transaction)) {
                    ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:20px; color:#999; font-style:italic;">
                            No savings transactions found for
                            <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?>.
                        </td>
                    </tr>
                    <?php
                } else {
                    $i = 1;
                    $credit = 0;
                    $debit = 0;
                    $open_total = 0;
                    $close_total = 0;
                    foreach ($transaction as $value) {
                        $acct = $value->account;
                        if (!isset($account_names[$acct])) {
                            $account_names[$acct] = $this->finance_model->saving_account_name($acct);
                        }
                        $display_acct = !empty($value->display_account) ? $value->display_account : $acct;
                        $member_id = !empty($value->member_id) ? $value->member_id : '';

                        $balance_open = $this->report_model->account_saving_transactions_summary_previous($reportinfo->fromdate, $acct);
                        $open_credit = $balance_open ? floatval($balance_open->credit) : 0;
                        $open_debit = $balance_open ? floatval($balance_open->debit) : 0;
                        $balance_tmp = $open_credit - $open_debit;

                        $row_debit = floatval($value->debit);
                        $row_credit = floatval($value->credit);
                        $debit += $row_debit;
                        $credit += $row_credit;
                        $open_total += $balance_tmp;

                        $close_balance = $balance_tmp - $row_debit + $row_credit;
                        $close_total += $close_balance;

                        $embed_url = $ledger_url($acct);
                        $ledger_title = 'Savings Ledger — ' . $display_acct;
                        ?>
                        <tr>
                            <td style="text-align:center; padding:3px 4px;"><?php echo $i++; ?>.</td>
                            <td style="padding:3px 4px;">
                                <a href="<?php echo htmlspecialchars($embed_url); ?>"
                                   class="sats-link sats-open-ledger"
                                   data-ledger-url="<?php echo htmlspecialchars($embed_url); ?>"
                                   data-ledger-title="<?php echo htmlspecialchars($ledger_title); ?>"
                                   title="View savings ledger">
                                    <?php echo htmlspecialchars($display_acct); ?>
                                </a>
                            </td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($member_id); ?></td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($account_names[$acct]); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo $format_cr_dr($balance_tmp); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo ($row_debit > 0 ? number_format($row_debit, 2) : ''); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo ($row_credit > 0 ? number_format($row_credit, 2) : ''); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo $format_cr_dr($close_balance); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="4"></td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <?php echo $format_cr_dr($open_total); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($debit, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($credit, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <?php echo $format_cr_dr($close_total); ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <table style="width:100%; margin-top:40px; font-size:12px;">
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

        <div style="text-align: center; margin-top: 20px;">
            <button type="button" class="btn btn-success js-download-report-pdf"
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report_saving/saving_account_transaction_summary_print/' . $link_cat . '/' . $id)); ?>"
                    data-pdf-name="Savings_Account_Transactions_Summary.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintSavingTransSummary">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_report/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>

<!-- Ledger popup -->
<style type="text/css">
#satsLedgerOverlay {
    display: none; position: fixed; left: 0; top: 0; right: 0; bottom: 0;
    z-index: 100000; background: rgba(0,0,0,0.55);
}
#satsLedgerOverlay.is-open { display: block; }
#satsLedgerOverlay .sats-dialog {
    position: absolute; left: 4%; top: 3%; width: 92%; height: 94%;
    background: #fff; border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; overflow: hidden;
}
#satsLedgerOverlay .sats-header {
    padding: 12px 15px; border-bottom: 1px solid #e7eaec; background: #f8f8f8; flex: 0 0 auto;
}
#satsLedgerOverlay .sats-header h4 { margin: 0; display: inline-block; font-size: 16px; font-weight: 600; }
#satsLedgerOverlay .sats-header .sats-actions { float: right; }
#satsLedgerOverlay .sats-body { flex: 1 1 auto; min-height: 0; background: #fff; position: relative; }
#satsLedgerOverlay .sats-body iframe { width: 100%; height: 100%; border: 0; display: block; }
#satsLoading {
    display: none; position: absolute; left: 0; top: 0; right: 0; bottom: 0; z-index: 2;
    background: rgba(255,255,255,0.9); align-items: center; justify-content: center; flex-direction: column;
}
#satsLoading.is-visible { display: flex; }
#satsLoading .spinner {
    width: 42px; height: 42px; border: 4px solid #e7eaec; border-top-color: #1ab394; border-radius: 50%;
    -webkit-animation: sats-spin 0.8s linear infinite; animation: sats-spin 0.8s linear infinite; margin-bottom: 12px;
}
@-webkit-keyframes sats-spin { to { -webkit-transform: rotate(360deg); transform: rotate(360deg); } }
@keyframes sats-spin { to { transform: rotate(360deg); } }
</style>
<div id="satsLedgerOverlay" aria-hidden="true">
    <div class="sats-dialog" role="dialog">
        <div class="sats-header">
            <h4 id="satsLedgerTitle">Savings Account Ledger</h4>
            <div class="sats-actions">
                <a id="satsLedgerOpenFull" href="#" target="_blank" class="btn btn-xs btn-white"><i class="fa fa-external-link"></i> Full page</a>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeSatsLedgerPopup();">Close</button>
            </div>
        </div>
        <div class="sats-body">
            <div id="satsLoading"><div class="spinner"></div><div>Loading ledger...</div></div>
            <iframe id="satsLedgerFrame" src="about:blank" title="Savings Account Ledger"></iframe>
        </div>
    </div>
</div>

<?php
$sats_pdf_url = site_url(current_lang() . '/report_saving/saving_account_transaction_summary_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#satsPdfOverlay {
    display: none; position: fixed; left: 0; top: 0; right: 0; bottom: 0;
    z-index: 99999; background: rgba(0,0,0,0.55);
}
#satsPdfOverlay.is-open { display: block; }
#satsPdfOverlay .satsp-dialog {
    position: absolute; left: 5%; top: 4%; width: 90%; height: 92%;
    background: #fff; border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; overflow: hidden;
}
#satsPdfOverlay .satsp-header {
    padding: 12px 15px; border-bottom: 1px solid #e7eaec; background: #f8f8f8; flex: 0 0 auto;
}
#satsPdfOverlay .satsp-header h4 { margin: 0; display: inline-block; font-size: 16px; font-weight: 600; }
#satsPdfOverlay .satsp-header .satsp-actions { float: right; }
#satsPdfOverlay .satsp-body { flex: 1 1 auto; min-height: 0; background: #f3f3f4; }
#satsPdfOverlay .satsp-body iframe { width: 100%; height: 100%; border: 0; display: block; }
</style>
<div id="satsPdfOverlay" aria-hidden="true">
    <div class="satsp-dialog" role="dialog">
        <div class="satsp-header">
            <h4>Savings Account Transactions Summary - PDF</h4>
            <div class="satsp-actions">
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeSatsPdf();">Close</button>
            </div>
        </div>
        <div class="satsp-body">
            <iframe id="satsPdfFrame" src="about:blank" title="Savings Transactions Summary PDF"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    window.openSatsLedgerPopup = function (url, title) {
        var overlay = document.getElementById('satsLedgerOverlay');
        var frame = document.getElementById('satsLedgerFrame');
        var titleEl = document.getElementById('satsLedgerTitle');
        var fullBtn = document.getElementById('satsLedgerOpenFull');
        var loading = document.getElementById('satsLoading');
        if (!overlay || !frame || !url) {
            if (url) window.open(String(url).replace('?embed=1', '').replace('&embed=1', ''), '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) document.body.appendChild(overlay);
        if (titleEl) titleEl.textContent = title || 'Savings Account Ledger';
        if (fullBtn) fullBtn.href = String(url).replace('?embed=1', '').replace('&embed=1', '');
        if (loading) loading.className = 'is-visible';
        frame.onload = function () { if (loading) loading.className = ''; };
        frame.src = url + (url.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    window.closeSatsLedgerPopup = function () {
        var overlay = document.getElementById('satsLedgerOverlay');
        var frame = document.getElementById('satsLedgerFrame');
        var loading = document.getElementById('satsLoading');
        if (loading) loading.className = '';
        if (frame) frame.src = 'about:blank';
        if (overlay) { overlay.className = ''; overlay.setAttribute('aria-hidden', 'true'); }
        document.body.style.overflow = '';
    };
    window.closeSavingLedgerPopup = window.closeSatsLedgerPopup;

    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var pdfUrl = <?php echo json_encode($sats_pdf_url); ?>;
    window.openSatsPdf = function () {
        var overlay = document.getElementById('satsPdfOverlay');
        var frame = document.getElementById('satsPdfFrame');
        if (!overlay || !frame) { window.open(pdfUrl, '_blank'); return; }
        if (overlay.parentNode !== document.body) document.body.appendChild(overlay);
        frame.src = viewerBase + encodeURIComponent(pdfUrl + (pdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now());
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    window.closeSatsPdf = function () {
        var overlay = document.getElementById('satsPdfOverlay');
        var frame = document.getElementById('satsPdfFrame');
        if (frame) frame.src = 'about:blank';
        if (overlay) { overlay.className = ''; overlay.setAttribute('aria-hidden', 'true'); }
        document.body.style.overflow = '';
    };

    function bind() {
        document.addEventListener('click', function (e) {
            var t = e.target;
            while (t && t !== document && !(t.classList && t.classList.contains('sats-open-ledger'))) {
                t = t.parentNode;
            }
            if (t && t !== document) {
                e.preventDefault();
                window.openSatsLedgerPopup(t.getAttribute('data-ledger-url') || t.getAttribute('href'), t.getAttribute('data-ledger-title'));
            }
        });
        var btn = document.getElementById('btnPrintSavingTransSummary');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.openSatsPdf();
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                window.closeSatsLedgerPopup();
                window.closeSatsPdf();
            }
        });
        document.addEventListener('click', function (e) {
            var lo = document.getElementById('satsLedgerOverlay');
            var po = document.getElementById('satsPdfOverlay');
            if (lo && lo.className.indexOf('is-open') !== -1 && e.target === lo) window.closeSatsLedgerPopup();
            if (po && po.className.indexOf('is-open') !== -1 && e.target === po) window.closeSatsPdf();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
    else bind();
})();
</script>
