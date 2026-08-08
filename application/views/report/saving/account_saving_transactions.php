<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$transaction = isset($transaction) ? $transaction : array();
$account_names = array();
$ledger_url = function ($account) use ($link_cat, $id) {
    return site_url(current_lang() . '/report_saving/new_saving_account_statement_view/' . $link_cat . '/' . $id . '/' . encode_id($account) . '?embed=1');
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
                            SAVINGS ACCOUNT TRANSACTIONS
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
                a.sat-link {
                    color: #1ab394;
                    text-decoration: none;
                    font-weight: 600;
                    cursor: pointer;
                }
                a.sat-link:hover {
                    text-decoration: underline;
                    color: #18a689;
                }
            </style>

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:1050px;">
                <thead>
                    <tr>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:45px;">S/No</th>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:90px;">Date</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px; width:110px;">Account No</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Account Name</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Particulars</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px; width:90px;">Method</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Debit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Credit</th>
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
                    foreach ($transaction as $value) {
                        $credit += floatval($value->credit);
                        $debit += floatval($value->debit);
                        $dt = explode(' ', $value->trans_date);
                        $acct = $value->account;
                        if (!isset($account_names[$acct])) {
                            $account_names[$acct] = $this->finance_model->saving_account_name($acct);
                        }
                        $acct_name = $account_names[$acct];
                        $display_acct = !empty($value->display_account) ? $value->display_account : $acct;
                        $particulars = trim(
                            (isset($value->system_comment) ? $value->system_comment : '')
                            . (isset($value->comment) && $value->comment !== '' ? ' — ' . $value->comment : '')
                        );
                        $embed_url = $ledger_url($acct);
                        $ledger_title = 'Savings Ledger — ' . $display_acct;
                        ?>
                        <tr>
                            <td style="text-align:center; padding:3px 4px;"><?php echo $i++; ?>.</td>
                            <td style="text-align:center; padding:3px 4px; white-space:nowrap;"><?php echo format_date($dt[0], false); ?></td>
                            <td style="padding:3px 4px;">
                                <a href="<?php echo htmlspecialchars($embed_url); ?>"
                                   class="sat-link sat-open-ledger"
                                   data-ledger-url="<?php echo htmlspecialchars($embed_url); ?>"
                                   data-ledger-title="<?php echo htmlspecialchars($ledger_title); ?>"
                                   title="View savings ledger">
                                    <?php echo htmlspecialchars($display_acct); ?>
                                </a>
                            </td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($acct_name); ?></td>
                            <td style="padding:3px 4px;"><?php echo $particulars !== '' ? htmlspecialchars($particulars) : '&mdash;'; ?></td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($value->paymethod); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo (floatval($value->debit) > 0 ? number_format($value->debit, 2) : ''); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo (floatval($value->credit) > 0 ? number_format($value->credit, 2) : ''); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="6"></td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($debit, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($credit, 2); ?>
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
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report_saving/saving_account_transaction_print/' . $link_cat . '/' . $id)); ?>"
                    data-pdf-name="Savings_Account_Transactions.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintSavingTrans">
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
#satLedgerOverlay {
    display: none; position: fixed; left: 0; top: 0; right: 0; bottom: 0;
    z-index: 100000; background: rgba(0,0,0,0.55);
}
#satLedgerOverlay.is-open { display: block; }
#satLedgerOverlay .sat-dialog {
    position: absolute; left: 4%; top: 3%; width: 92%; height: 94%;
    background: #fff; border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; overflow: hidden;
}
#satLedgerOverlay .sat-header {
    padding: 12px 15px; border-bottom: 1px solid #e7eaec; background: #f8f8f8; flex: 0 0 auto;
}
#satLedgerOverlay .sat-header h4 { margin: 0; display: inline-block; font-size: 16px; font-weight: 600; }
#satLedgerOverlay .sat-header .sat-actions { float: right; }
#satLedgerOverlay .sat-body { flex: 1 1 auto; min-height: 0; background: #fff; position: relative; }
#satLedgerOverlay .sat-body iframe { width: 100%; height: 100%; border: 0; display: block; }
#satLoading {
    display: none; position: absolute; left: 0; top: 0; right: 0; bottom: 0; z-index: 2;
    background: rgba(255,255,255,0.9); align-items: center; justify-content: center; flex-direction: column;
}
#satLoading.is-visible { display: flex; }
#satLoading .spinner {
    width: 42px; height: 42px; border: 4px solid #e7eaec; border-top-color: #1ab394; border-radius: 50%;
    -webkit-animation: sat-spin 0.8s linear infinite; animation: sat-spin 0.8s linear infinite; margin-bottom: 12px;
}
@-webkit-keyframes sat-spin { to { -webkit-transform: rotate(360deg); transform: rotate(360deg); } }
@keyframes sat-spin { to { transform: rotate(360deg); } }
</style>
<div id="satLedgerOverlay" aria-hidden="true">
    <div class="sat-dialog" role="dialog">
        <div class="sat-header">
            <h4 id="satLedgerTitle">Savings Account Ledger</h4>
            <div class="sat-actions">
                <a id="satLedgerOpenFull" href="#" target="_blank" class="btn btn-xs btn-white"><i class="fa fa-external-link"></i> Full page</a>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeSatLedgerPopup();">Close</button>
            </div>
        </div>
        <div class="sat-body">
            <div id="satLoading"><div class="spinner"></div><div>Loading ledger...</div></div>
            <iframe id="satLedgerFrame" src="about:blank" title="Savings Account Ledger"></iframe>
        </div>
    </div>
</div>

<?php
$sat_pdf_url = site_url(current_lang() . '/report_saving/saving_account_transaction_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#satPdfOverlay {
    display: none; position: fixed; left: 0; top: 0; right: 0; bottom: 0;
    z-index: 99999; background: rgba(0,0,0,0.55);
}
#satPdfOverlay.is-open { display: block; }
#satPdfOverlay .satp-dialog {
    position: absolute; left: 5%; top: 4%; width: 90%; height: 92%;
    background: #fff; border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; overflow: hidden;
}
#satPdfOverlay .satp-header {
    padding: 12px 15px; border-bottom: 1px solid #e7eaec; background: #f8f8f8; flex: 0 0 auto;
}
#satPdfOverlay .satp-header h4 { margin: 0; display: inline-block; font-size: 16px; font-weight: 600; }
#satPdfOverlay .satp-header .satp-actions { float: right; }
#satPdfOverlay .satp-body { flex: 1 1 auto; min-height: 0; background: #f3f3f4; }
#satPdfOverlay .satp-body iframe { width: 100%; height: 100%; border: 0; display: block; }
</style>
<div id="satPdfOverlay" aria-hidden="true">
    <div class="satp-dialog" role="dialog">
        <div class="satp-header">
            <h4>Savings Account Transactions - PDF</h4>
            <div class="satp-actions">
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeSatPdf();">Close</button>
            </div>
        </div>
        <div class="satp-body">
            <iframe id="satPdfFrame" src="about:blank" title="Savings Transactions PDF"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    window.openSatLedgerPopup = function (url, title) {
        var overlay = document.getElementById('satLedgerOverlay');
        var frame = document.getElementById('satLedgerFrame');
        var titleEl = document.getElementById('satLedgerTitle');
        var fullBtn = document.getElementById('satLedgerOpenFull');
        var loading = document.getElementById('satLoading');
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
    window.closeSatLedgerPopup = function () {
        var overlay = document.getElementById('satLedgerOverlay');
        var frame = document.getElementById('satLedgerFrame');
        var loading = document.getElementById('satLoading');
        if (loading) loading.className = '';
        if (frame) frame.src = 'about:blank';
        if (overlay) { overlay.className = ''; overlay.setAttribute('aria-hidden', 'true'); }
        document.body.style.overflow = '';
    };
    window.closeSavingLedgerPopup = window.closeSatLedgerPopup;

    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var pdfUrl = <?php echo json_encode($sat_pdf_url); ?>;
    window.openSatPdf = function () {
        var overlay = document.getElementById('satPdfOverlay');
        var frame = document.getElementById('satPdfFrame');
        if (!overlay || !frame) { window.open(pdfUrl, '_blank'); return; }
        if (overlay.parentNode !== document.body) document.body.appendChild(overlay);
        frame.src = viewerBase + encodeURIComponent(pdfUrl + (pdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now());
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    window.closeSatPdf = function () {
        var overlay = document.getElementById('satPdfOverlay');
        var frame = document.getElementById('satPdfFrame');
        if (frame) frame.src = 'about:blank';
        if (overlay) { overlay.className = ''; overlay.setAttribute('aria-hidden', 'true'); }
        document.body.style.overflow = '';
    };

    function bind() {
        document.addEventListener('click', function (e) {
            var t = e.target;
            while (t && t !== document && !(t.classList && t.classList.contains('sat-open-ledger'))) {
                t = t.parentNode;
            }
            if (t && t !== document) {
                e.preventDefault();
                window.openSatLedgerPopup(t.getAttribute('data-ledger-url') || t.getAttribute('href'), t.getAttribute('data-ledger-title'));
            }
        });
        var btn = document.getElementById('btnPrintSavingTrans');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.openSatPdf();
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                window.closeSatLedgerPopup();
                window.closeSatPdf();
            }
        });
        document.addEventListener('click', function (e) {
            var lo = document.getElementById('satLedgerOverlay');
            var po = document.getElementById('satPdfOverlay');
            if (lo && lo.className.indexOf('is-open') !== -1 && e.target === lo) window.closeSatLedgerPopup();
            if (po && po.className.indexOf('is-open') !== -1 && e.target === po) window.closeSatPdf();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
    else bind();
})();
</script>
