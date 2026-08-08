<?php
if (!function_exists('cf_format_amount')) {
    function cf_format_amount($amount) {
        if ($amount === null) {
            return '';
        }
        $v = floatval($amount);
        if (abs($v) < 0.005) {
            return '-';
        }
        if ($v < 0) {
            return '(' . number_format(abs($v), 2) . ')';
        }
        return number_format($v, 2);
    }
}

$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$cf_rows = isset($cash_flow_data['rows']) ? $cash_flow_data['rows'] : array();
$cf_totals = isset($cash_flow_data['totals']) ? $cash_flow_data['totals'] : array();
$indent_px = array(0 => 0, 1 => 18, 2 => 36, 3 => 54, 4 => 72);
?>
<div class="row">
    <div class="col-lg-12">
        <div style="padding: 20px 10px; margin: auto; max-width: 960px;">
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
                            STATEMENT OF CASH FLOWS
                        </div>
                        <div style="font-size:12px; font-style:italic; margin-top:2px;">
                            Indirect Method
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

            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;"></th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:140px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cf_rows as $row) {
                    $type = $row['type'];
                    if ($type === 'spacer') {
                        echo '<tr><td colspan="2" style="height:14px;"></td></tr>';
                        continue;
                    }
                    $indent = isset($indent_px[$row['indent']]) ? $indent_px[$row['indent']] : ($row['indent'] * 18);
                    $has_amount = array_key_exists('amount', $row) && $row['amount'] !== null;
                    $show = !empty($row['always_show']) || $type === 'section' || $type === 'group' || $type === 'subtotal' || $type === 'total'
                        || ($has_amount && abs(floatval($row['amount'])) >= 0.005);
                    if (!$show) {
                        continue;
                    }
                    $label_style = 'padding:2px 4px 2px ' . $indent . 'px;';
                    if (!empty($row['bold'])) {
                        $label_style .= 'font-weight:bold;';
                    }
                    if (!empty($row['italic'])) {
                        $label_style .= 'font-style:italic;';
                    }
                    if ($type === 'section') {
                        $label_style .= 'text-transform:none; padding-top:10px;';
                    }
                    $amt_style = 'text-align:right; white-space:nowrap; padding:2px 4px; width:140px;';
                    if (!empty($row['bold'])) {
                        $amt_style .= 'font-weight:bold;';
                    }
                    if (!empty($row['line'])) {
                        $amt_style .= 'border-top:1px solid #000;';
                    }

                    $label_html = htmlspecialchars($row['label']);

                    $amount_html = '';
                    if ($has_amount) {
                        $formatted = cf_format_amount($row['amount']);
                        if (!empty($row['peso']) && $formatted !== '-') {
                            $amount_html = '<span style="float:left;">P</span>' . $formatted;
                        } else {
                            $amount_html = $formatted;
                        }
                        if (!empty($row['line']) && $row['line'] === 'double') {
                            $amount_html = '<div style="border-bottom:3px double #000; display:inline-block; min-width:120px; text-align:right;">' . $amount_html . '</div>';
                        }
                    }
                    ?>
                    <tr>
                        <td style="<?php echo $label_style; ?>"><?php echo $label_html; ?></td>
                        <td style="<?php echo $amt_style; ?>"><?php echo $amount_html; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <?php
            $end = isset($cf_totals['ending_cash']) ? floatval($cf_totals['ending_cash']) : 0;
            $recon = isset($cf_totals['ending_cash_reconciled']) ? floatval($cf_totals['ending_cash_reconciled']) : 0;
            if (abs($end - $recon) >= 0.05) {
                ?>
                <div style="margin-top:12px; color:#a94442; font-size:12px;">
                    Note: Cash balance end (<?php echo number_format($end, 2); ?>) and beginning + net change
                    (<?php echo number_format($recon, 2); ?>) differ by
                    <?php echo number_format(abs($end - $recon), 2); ?>.
                    Review unposted journals or non-cash equity movements while encoding continues.
                </div>
            <?php } ?>

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
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report/cash_flow_report_print/' . $id)); ?>"
                    data-pdf-name="Cash_Flow_Report.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintCashFlow">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/create_cash_flow_report/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/cash_flow_report'); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>

<?php
$cf_pdf_url = site_url(current_lang() . '/report/cash_flow_report_print/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#cashFlowPdfOverlay {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.55);
}
#cashFlowPdfOverlay.is-open { display: block; }
#cashFlowPdfOverlay .cf-dialog {
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
#cashFlowPdfOverlay .cf-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e7eaec;
    background: #f8f8f8;
    flex: 0 0 auto;
}
#cashFlowPdfOverlay .cf-header h4 {
    margin: 0;
    display: inline-block;
    font-size: 16px;
    font-weight: 600;
}
#cashFlowPdfOverlay .cf-header .cf-actions { float: right; }
#cashFlowPdfOverlay .cf-body {
    flex: 1 1 auto;
    min-height: 0;
    background: #f3f3f4;
    position: relative;
}
#cashFlowPdfOverlay .cf-body iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
#cfPdfPrintProgress {
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
#cfPdfPrintProgress.is-visible { display: flex; }
#cfPdfPrintProgress .spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e7eaec;
    border-top-color: #1ab394;
    border-radius: 50%;
    -webkit-animation: cf-pdf-spin 0.8s linear infinite;
    animation: cf-pdf-spin 0.8s linear infinite;
    margin-bottom: 12px;
}
#cfPdfPrintProgress .msg { color: #676a6c; font-size: 14px; }
@-webkit-keyframes cf-pdf-spin {
    to { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
@keyframes cf-pdf-spin {
    to { transform: rotate(360deg); }
}
</style>

<div id="cashFlowPdfOverlay" aria-hidden="true">
    <div class="cf-dialog" role="dialog" aria-labelledby="cashFlowPdfTitle">
        <div class="cf-header">
            <h4 id="cashFlowPdfTitle">Statement of Cash Flows - PDF</h4>
            <div class="cf-actions">
                <button type="button" id="cashFlowPdfPrintBtn" class="btn btn-xs btn-primary" onclick="window.printCashFlowPdfModal();" title="Print">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeCashFlowPdf();">Close</button>
            </div>
        </div>
        <div class="cf-body">
            <div id="cfPdfPrintProgress" aria-live="polite" aria-busy="false">
                <div class="spinner"></div>
                <div class="msg">Preparing print...</div>
            </div>
            <iframe id="cashFlowPdfFrame" src="about:blank" title="Cash Flow PDF Viewer"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var cashFlowPdfUrl = <?php echo json_encode($cf_pdf_url); ?>;
    var currentPdfUrl = cashFlowPdfUrl;
    var printHideTimer = null;

    function getOverlay() {
        return document.getElementById('cashFlowPdfOverlay');
    }
    function getFrame() {
        return document.getElementById('cashFlowPdfFrame');
    }
    function getPrintBtn() {
        return document.getElementById('cashFlowPdfPrintBtn');
    }

    window.showCashFlowPdfPrintProgress = function () {
        var el = document.getElementById('cfPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = 'is-visible';
            el.setAttribute('aria-busy', 'true');
        }
        if (btn) {
            btn.disabled = true;
        }
    };

    window.hideCashFlowPdfPrintProgress = function () {
        if (printHideTimer) {
            clearTimeout(printHideTimer);
            printHideTimer = null;
        }
        var el = document.getElementById('cfPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = '';
            el.setAttribute('aria-busy', 'false');
        }
        if (btn) {
            btn.disabled = false;
        }
    };

    window.openCashFlowPdfModal = function () {
        var overlay = getOverlay();
        var frame = getFrame();
        if (!overlay || !frame) {
            window.open(cashFlowPdfUrl, '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        currentPdfUrl = cashFlowPdfUrl + (cashFlowPdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        window.hideCashFlowPdfPrintProgress();
        frame.src = viewerBase + encodeURIComponent(currentPdfUrl);
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.printCashFlowPdfModal = function () {
        window.showCashFlowPdfPrintProgress();
        var frame = getFrame();
        if (frame && frame.contentWindow && typeof frame.contentWindow.printPdfFile === 'function') {
            try {
                frame.contentWindow.printPdfFile();
                return;
            } catch (e) {}
        }
        if (!currentPdfUrl) {
            window.hideCashFlowPdfPrintProgress();
            return;
        }
        var existing = document.getElementById('cf-pdf-print-frame');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var iframe = document.createElement('iframe');
        iframe.id = 'cf-pdf-print-frame';
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:800px;height:600px;border:0;opacity:0;pointer-events:none;z-index:-1;';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.src = currentPdfUrl;
        document.body.appendChild(iframe);
        var printShown = false;
        function finishPrintPrep() {
            if (printShown) return;
            printShown = true;
            window.hideCashFlowPdfPrintProgress();
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

    window.closeCashFlowPdf = function () {
        window.hideCashFlowPdfPrintProgress();
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
            window.closeCashFlowPdf();
        }
    });
    document.addEventListener('click', function (e) {
        var overlay = getOverlay();
        if (!overlay || overlay.className.indexOf('is-open') === -1) {
            return;
        }
        if (e.target === overlay) {
            window.closeCashFlowPdf();
        }
    });

    function bindPrintButton() {
        var btn = document.getElementById('btnPrintCashFlow');
        if (!btn || btn.getAttribute('data-cf-print-bound') === '1') {
            return;
        }
        btn.setAttribute('data-cf-print-bound', '1');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.openCashFlowPdfModal();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPrintButton);
    } else {
        bindPrintButton();
    }
})();
</script>
