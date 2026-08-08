<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$glt_ledger_url = function ($account_code) use ($link_cat, $id) {
    return site_url(current_lang() . '/report/account_ledger/' . encode_id($account_code) . '/' . $link_cat . '/' . $id);
};
$transaction = isset($transaction) ? $transaction : array();
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
                            GENERAL LEDGER TRANSACTIONS
                        </div>
                        <div style="font-size:13px; margin-top:4px;">
                            For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?>
                        </div>
                        <?php if (!empty($account_name)) { ?>
                            <div style="font-size:12px; margin-top:2px;">
                                Account: <?php echo htmlspecialchars($account_name); ?>
                            </div>
                        <?php } ?>
                    </td>
                    <td style="width:90px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:12px;">
                        LENDING
                    </td>
                </tr>
            </table>

            <style type="text/css">
                a.glt-account-link {
                    color: #1ab394;
                    text-decoration: none;
                }
                a.glt-account-link:hover {
                    text-decoration: underline;
                    color: #18a689;
                }
            </style>

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:1000px;">
                <thead>
                    <tr>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Type</th>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:90px;">Date</th>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:60px;">#</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Account</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Person/Member/Item</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Debit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Credit</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $credittotal = 0;
                $debittotal = 0;
                foreach ($transaction as $value) {
                    $credittotal += floatval($value->credit);
                    $debittotal += floatval($value->debit);
                    $journal_type = isset($value->trans_comment) ? $value->trans_comment : '';
                    $ref_no = (isset($value->invoiceid) && $value->invoiceid > 0) ? $value->invoiceid : (isset($value->refferenceID) ? $value->refferenceID : '');
                    $ref_url = get_gl_reference_url(isset($value->fromtable) ? $value->fromtable : '', isset($value->refferenceID) ? $value->refferenceID : null);
                    $acct_code = isset($value->account) ? $value->account : '';
                    $acct_label = htmlspecialchars($acct_code) . ' - ' . htmlspecialchars($value->name);
                    if ($acct_code !== '') {
                        $acct_label = '<a class="glt-account-link" href="' . $glt_ledger_url($acct_code) . '" title="View account ledger">' . $acct_label . '</a>';
                    }
                    $pad = (floatval($value->credit) > 0) ? 'padding-left:24px;' : '';
                    ?>
                    <tr>
                        <td style="padding:3px 4px;"><?php echo $journal_type !== '' ? htmlspecialchars($journal_type) : '&mdash;'; ?></td>
                        <td style="text-align:center; padding:3px 4px; white-space:nowrap;"><?php echo format_date($value->date, false); ?></td>
                        <td style="text-align:center; padding:3px 4px;">
                            <?php
                            if ($ref_no !== '' && $ref_no !== null) {
                                if ($ref_url !== '') {
                                    echo anchor($ref_url, '#' . $ref_no, array('title' => 'View reference'));
                                } else {
                                    echo '#' . htmlspecialchars($ref_no);
                                }
                            } else {
                                echo '&mdash;';
                            }
                            ?>
                        </td>
                        <td style="padding:3px 4px; <?php echo $pad; ?>"><?php echo $acct_label; ?></td>
                        <td style="padding:3px 4px;">
                            <?php
                            $rel_name = isset($value->related_entity_name) ? $value->related_entity_name : '';
                            $rel_url = isset($value->related_entity_url) ? $value->related_entity_url : '';
                            if ($rel_name !== '') {
                                if ($rel_url !== '') {
                                    echo anchor($rel_url, htmlspecialchars($rel_name), array('title' => 'View'));
                                } else {
                                    echo htmlspecialchars($rel_name);
                                }
                            } else {
                                echo '&mdash;';
                            }
                            ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap; padding:3px 4px;">
                            <?php echo (floatval($value->debit) > 0 ? number_format($value->debit, 2) : ''); ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap; padding:3px 4px;">
                            <?php echo (floatval($value->credit) > 0 ? number_format($value->credit, 2) : ''); ?>
                        </td>
                        <td style="padding:3px 4px;"><?php echo isset($value->description) && $value->description !== '' ? htmlspecialchars($value->description) : '&mdash;'; ?></td>
                    </tr>
                <?php } ?>
                    <tr>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="5"></td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($debittotal, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($credittotal, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;"></td>
                    </tr>
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
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report/ledger_trans_print/' . $link_cat . '/' . $id)); ?>"
                    data-pdf-name="Ledger_Transactions.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintLedgerTrans">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <?php if ((int) $link_cat === 1) { ?>
                <a href="<?php echo site_url(current_lang() . '/report/ledger_trans_export/' . $link_cat . '/' . $id); ?>" class="btn btn-success">
                    <i class="fa fa-file-excel-o"></i> Export to Excel
                </a>
                &nbsp; &nbsp; &nbsp; &nbsp;
            <?php } ?>
            <a href="<?php echo site_url(current_lang() . '/report/create_ledger_trans_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/general_leger_transaction/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>

<?php
$glt_pdf_url = site_url(current_lang() . '/report/ledger_trans_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#ledgerTransPdfOverlay {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.55);
}
#ledgerTransPdfOverlay.is-open { display: block; }
#ledgerTransPdfOverlay .glt-dialog {
    position: absolute;
    left: 5%;
    top: 4%;
    width: 90%;
    height: 92%;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
#ledgerTransPdfOverlay .glt-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e7eaec;
    background: #f8f8f8;
    flex: 0 0 auto;
}
#ledgerTransPdfOverlay .glt-header h4 {
    margin: 0;
    display: inline-block;
    font-size: 16px;
    font-weight: 600;
}
#ledgerTransPdfOverlay .glt-header .glt-actions { float: right; }
#ledgerTransPdfOverlay .glt-body {
    flex: 1 1 auto;
    min-height: 0;
    background: #f3f3f4;
    position: relative;
}
#ledgerTransPdfOverlay .glt-body iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
#gltPdfPrintProgress {
    display: none;
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 5;
    background: rgba(255,255,255,0.88);
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
}
#gltPdfPrintProgress.is-visible { display: flex; }
#gltPdfPrintProgress .spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e7eaec;
    border-top-color: #1ab394;
    border-radius: 50%;
    -webkit-animation: glt-pdf-spin 0.8s linear infinite;
    animation: glt-pdf-spin 0.8s linear infinite;
    margin-bottom: 12px;
}
#gltPdfPrintProgress .msg { color: #676a6c; font-size: 14px; }
@-webkit-keyframes glt-pdf-spin {
    to { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
@keyframes glt-pdf-spin {
    to { transform: rotate(360deg); }
}
</style>

<div id="ledgerTransPdfOverlay" aria-hidden="true">
    <div class="glt-dialog" role="dialog" aria-labelledby="ledgerTransPdfTitle">
        <div class="glt-header">
            <h4 id="ledgerTransPdfTitle">General Ledger Transactions - PDF</h4>
            <div class="glt-actions">
                <button type="button" id="ledgerTransPdfPrintBtn" class="btn btn-xs btn-primary" onclick="window.printLedgerTransPdfModal();" title="Print">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeLedgerTransPdf();">Close</button>
            </div>
        </div>
        <div class="glt-body">
            <div id="gltPdfPrintProgress" aria-live="polite" aria-busy="false">
                <div class="spinner"></div>
                <div class="msg">Preparing print...</div>
            </div>
            <iframe id="ledgerTransPdfFrame" src="about:blank" title="General Ledger Transactions PDF Viewer"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var ledgerTransPdfUrl = <?php echo json_encode($glt_pdf_url); ?>;
    var currentPdfUrl = ledgerTransPdfUrl;
    var printHideTimer = null;

    function getOverlay() {
        return document.getElementById('ledgerTransPdfOverlay');
    }
    function getFrame() {
        return document.getElementById('ledgerTransPdfFrame');
    }
    function getPrintBtn() {
        return document.getElementById('ledgerTransPdfPrintBtn');
    }

    window.showLedgerTransPdfPrintProgress = function () {
        var el = document.getElementById('gltPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = 'is-visible';
            el.setAttribute('aria-busy', 'true');
        }
        if (btn) {
            btn.disabled = true;
        }
    };

    window.hideLedgerTransPdfPrintProgress = function () {
        if (printHideTimer) {
            clearTimeout(printHideTimer);
            printHideTimer = null;
        }
        var el = document.getElementById('gltPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = '';
            el.setAttribute('aria-busy', 'false');
        }
        if (btn) {
            btn.disabled = false;
        }
    };

    window.openLedgerTransPdfModal = function () {
        var overlay = getOverlay();
        var frame = getFrame();
        if (!overlay || !frame) {
            window.open(ledgerTransPdfUrl, '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        currentPdfUrl = ledgerTransPdfUrl + (ledgerTransPdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        window.hideLedgerTransPdfPrintProgress();
        frame.src = viewerBase + encodeURIComponent(currentPdfUrl);
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.printLedgerTransPdfModal = function () {
        window.showLedgerTransPdfPrintProgress();
        var frame = getFrame();
        if (frame && frame.contentWindow && typeof frame.contentWindow.printPdfFile === 'function') {
            try {
                frame.contentWindow.printPdfFile();
                return;
            } catch (e) {}
        }
        if (!currentPdfUrl) {
            window.hideLedgerTransPdfPrintProgress();
            return;
        }
        var existing = document.getElementById('glt-pdf-print-frame');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var iframe = document.createElement('iframe');
        iframe.id = 'glt-pdf-print-frame';
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:800px;height:600px;border:0;opacity:0;pointer-events:none;z-index:-1;';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.src = currentPdfUrl;
        document.body.appendChild(iframe);
        var printShown = false;
        function finishPrintPrep() {
            if (printShown) return;
            printShown = true;
            window.hideLedgerTransPdfPrintProgress();
        }
        iframe.onload = function () {
            setTimeout(function () {
                try {
                    var win = iframe.contentWindow;
                    if (!win) {
                        finishPrintPrep();
                        return;
                    }
                    win.addEventListener('beforeprint', finishPrintPrep);
                    win.addEventListener('afterprint', finishPrintPrep);
                    printHideTimer = setTimeout(finishPrintPrep, 1500);
                    win.focus();
                    win.print();
                } catch (err) {
                    finishPrintPrep();
                }
            }, 600);
        };
        iframe.onerror = function () {
            finishPrintPrep();
        };
    };

    window.closeLedgerTransPdf = function () {
        window.hideLedgerTransPdfPrintProgress();
        var overlay = getOverlay();
        var frame = getFrame();
        if (frame) {
            frame.src = 'about:blank';
        }
        if (overlay) {
            overlay.className = '';
            overlay.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            window.closeLedgerTransPdf();
        }
    });
    document.addEventListener('click', function (e) {
        var overlay = getOverlay();
        if (!overlay || overlay.className.indexOf('is-open') === -1) {
            return;
        }
        if (e.target === overlay) {
            window.closeLedgerTransPdf();
        }
    });

    function bindPrintButton() {
        var btn = document.getElementById('btnPrintLedgerTrans');
        if (!btn || btn.getAttribute('data-glt-print-bound') === '1') {
            return;
        }
        btn.setAttribute('data-glt-print-bound', '1');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.openLedgerTransPdfModal();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPrintButton);
    } else {
        bindPrintButton();
    }
})();
</script>
