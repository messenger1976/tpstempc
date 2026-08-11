<?php
if (!function_exists('is_format_amount')) {
    function is_format_amount($amount) {
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
$is_ledger_url = function ($account_code) use ($link_cat, $id) {
    return site_url(current_lang() . '/report/account_ledger/' . encode_id($account_code) . '/' . $link_cat . '/' . $id);
};

$transaction = $this->report_model->create_ledger_trans_summary($reportinfo->fromdate, $reportinfo->todate);
$total_income = 0;
$total_expenses = 0;
$check_exp_inc = 0;
?>
<div class="row">
    <div class="col-lg-12">
        <div style="padding: 20px 10px; margin: auto; max-width: 920px;">
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
                            INCOME STATEMENT
                        </div>
                        <div style="font-size:12px; font-style:italic; margin-top:2px;">
                            Statement of Financial Operations
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
                a.is-account-link {
                    color: #1ab394;
                    text-decoration: none;
                }
                a.is-account-link:hover {
                    text-decoration: underline;
                    color: #18a689;
                }
            </style>

            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;"></th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:140px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (array_key_exists(4, $transaction)) {
                    $check_exp_inc = 1;
                    ?>
                    <tr>
                        <td style="padding:10px 4px 2px 0; font-weight:bold; text-transform:uppercase; letter-spacing:0.3px;" colspan="2">
                            Revenues &amp; Gains
                        </td>
                    </tr>
                    <?php
                    foreach ($transaction[4] as $key1 => $value1) {
                        $account_info = $this->finance_model->account_chart(null, $key1)->row();
                        if (!$account_info) {
                            continue;
                        }
                        $tmp = 0;
                        if (isset($value1['current']) && is_object($value1['current']) && isset($value1['current']->credit) && isset($value1['current']->debit)) {
                            $tmp = $value1['current']->credit - $value1['current']->debit;
                        }
                        $total_income += $tmp;
                        $acct_code = !empty($account_info->account) ? $account_info->account : $key1;
                        $label = htmlspecialchars($account_info->name);
                        $label = '<a class="is-account-link" href="' . $is_ledger_url($acct_code) . '" title="View account ledger">' . $label . '</a>';
                        ?>
                        <tr>
                            <td style="padding:2px 4px 2px 36px;"><?php echo $label; ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px; width:140px;">
                                <?php echo is_format_amount($tmp); ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td style="padding:4px 4px 2px 54px; font-weight:bold;">Total Revenues &amp; Gains</td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                            <span style="float:left;">P</span><?php echo is_format_amount($total_income); ?>
                        </td>
                    </tr>
                <?php } ?>

                <?php if (array_key_exists(5, $transaction)) {
                    $check_exp_inc = 1;
                    ?>
                    <tr>
                        <td style="padding:14px 4px 2px 0; font-weight:bold; text-transform:uppercase; letter-spacing:0.3px;" colspan="2">
                            Expenses &amp; Losses
                        </td>
                    </tr>
                    <?php
                    foreach ($transaction[5] as $key1 => $value1) {
                        $account_info = $this->finance_model->account_chart(null, $key1)->row();
                        if (!$account_info) {
                            continue;
                        }
                        $tmp = 0;
                        if (isset($value1['current']) && is_object($value1['current']) && isset($value1['current']->credit) && isset($value1['current']->debit)) {
                            $tmp = $value1['current']->debit - $value1['current']->credit;
                        }
                        $total_expenses += $tmp;
                        $acct_code = !empty($account_info->account) ? $account_info->account : $key1;
                        $label = htmlspecialchars($account_info->name);
                        $label = '<a class="is-account-link" href="' . $is_ledger_url($acct_code) . '" title="View account ledger">' . $label . '</a>';
                        ?>
                        <tr>
                            <td style="padding:2px 4px 2px 36px;"><?php echo $label; ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px; width:140px;">
                                <?php echo is_format_amount($tmp); ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td style="padding:4px 4px 2px 54px; font-weight:bold;">Total Expenses &amp; Losses</td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                            <span style="float:left;">P</span><?php echo is_format_amount($total_expenses); ?>
                        </td>
                    </tr>
                <?php } ?>

                <?php
                $close_balance = $total_income - $total_expenses;
                if ($check_exp_inc == 1) {
                    echo '<tr><td colspan="2" style="height:18px;"></td></tr>';
                }
                ?>
                    <tr>
                        <td style="padding:4px; font-weight:bold; border-top:1px solid #000;">Net Surplus (Loss)</td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                            <div style="border-bottom:3px double #000; display:inline-block; min-width:120px; text-align:right;">
                                <span style="float:left;">P</span><?php echo is_format_amount($close_balance); ?>
                            </div>
                        </td>
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
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report/ledger_income_statement_print/' . $link_cat . '/' . $id)); ?>"
                    data-pdf-name="Income_Statement.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintIncomeStatement">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/create_ledger_trans_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/general_leger_transaction/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>

<?php
$is_pdf_url = site_url(current_lang() . '/report/ledger_income_statement_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#incomeStatementPdfOverlay {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.55);
}
#incomeStatementPdfOverlay.is-open { display: block; }
#incomeStatementPdfOverlay .is-dialog {
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
#incomeStatementPdfOverlay .is-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e7eaec;
    background: #f8f8f8;
    flex: 0 0 auto;
}
#incomeStatementPdfOverlay .is-header h4 {
    margin: 0;
    display: inline-block;
    font-size: 16px;
    font-weight: 600;
}
#incomeStatementPdfOverlay .is-header .is-actions { float: right; }
#incomeStatementPdfOverlay .is-body {
    flex: 1 1 auto;
    min-height: 0;
    background: #f3f3f4;
    position: relative;
}
#incomeStatementPdfOverlay .is-body iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
#isPdfPrintProgress {
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
#isPdfPrintProgress.is-visible { display: flex; }
#isPdfPrintProgress .spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e7eaec;
    border-top-color: #1ab394;
    border-radius: 50%;
    -webkit-animation: is-pdf-spin 0.8s linear infinite;
    animation: is-pdf-spin 0.8s linear infinite;
    margin-bottom: 12px;
}
#isPdfPrintProgress .msg { color: #676a6c; font-size: 14px; }
@-webkit-keyframes is-pdf-spin {
    to { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
@keyframes is-pdf-spin {
    to { transform: rotate(360deg); }
}
</style>

<div id="incomeStatementPdfOverlay" aria-hidden="true">
    <div class="is-dialog" role="dialog" aria-labelledby="incomeStatementPdfTitle">
        <div class="is-header">
            <h4 id="incomeStatementPdfTitle">Income Statement - PDF</h4>
            <div class="is-actions">
                <button type="button" id="incomeStatementPdfPrintBtn" class="btn btn-xs btn-primary" onclick="window.printIncomeStatementPdfModal();" title="Print">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeIncomeStatementPdf();">Close</button>
            </div>
        </div>
        <div class="is-body">
            <div id="isPdfPrintProgress" aria-live="polite" aria-busy="false">
                <div class="spinner"></div>
                <div class="msg">Preparing print...</div>
            </div>
            <iframe id="incomeStatementPdfFrame" src="about:blank" title="Income Statement PDF Viewer"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var incomeStatementPdfUrl = <?php echo json_encode($is_pdf_url); ?>;
    var currentPdfUrl = incomeStatementPdfUrl;
    var printHideTimer = null;

    function getOverlay() {
        return document.getElementById('incomeStatementPdfOverlay');
    }
    function getFrame() {
        return document.getElementById('incomeStatementPdfFrame');
    }
    function getPrintBtn() {
        return document.getElementById('incomeStatementPdfPrintBtn');
    }

    window.showIncomeStatementPdfPrintProgress = function () {
        var el = document.getElementById('isPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = 'is-visible';
            el.setAttribute('aria-busy', 'true');
        }
        if (btn) {
            btn.disabled = true;
        }
    };

    window.hideIncomeStatementPdfPrintProgress = function () {
        if (printHideTimer) {
            clearTimeout(printHideTimer);
            printHideTimer = null;
        }
        var el = document.getElementById('isPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = '';
            el.setAttribute('aria-busy', 'false');
        }
        if (btn) {
            btn.disabled = false;
        }
    };

    window.openIncomeStatementPdfModal = function () {
        var overlay = getOverlay();
        var frame = getFrame();
        if (!overlay || !frame) {
            window.open(incomeStatementPdfUrl, '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        currentPdfUrl = incomeStatementPdfUrl + (incomeStatementPdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        window.hideIncomeStatementPdfPrintProgress();
        frame.src = viewerBase + encodeURIComponent(currentPdfUrl);
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.printIncomeStatementPdfModal = function () {
        window.showIncomeStatementPdfPrintProgress();
        var frame = getFrame();
        if (frame && frame.contentWindow && typeof frame.contentWindow.printPdfFile === 'function') {
            try {
                frame.contentWindow.printPdfFile();
                return;
            } catch (e) {}
        }
        if (!currentPdfUrl) {
            window.hideIncomeStatementPdfPrintProgress();
            return;
        }
        var existing = document.getElementById('is-pdf-print-frame');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var iframe = document.createElement('iframe');
        iframe.id = 'is-pdf-print-frame';
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:800px;height:600px;border:0;opacity:0;pointer-events:none;z-index:-1;';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.src = currentPdfUrl;
        document.body.appendChild(iframe);
        var printShown = false;
        function finishPrintPrep() {
            if (printShown) return;
            printShown = true;
            window.hideIncomeStatementPdfPrintProgress();
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

    window.closeIncomeStatementPdf = function () {
        window.hideIncomeStatementPdfPrintProgress();
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
            window.closeIncomeStatementPdf();
        }
    });
    document.addEventListener('click', function (e) {
        var overlay = getOverlay();
        if (!overlay || overlay.className.indexOf('is-open') === -1) {
            return;
        }
        if (e.target === overlay) {
            window.closeIncomeStatementPdf();
        }
    });

    function bindPrintButton() {
        var btn = document.getElementById('btnPrintIncomeStatement');
        if (!btn || btn.getAttribute('data-is-print-bound') === '1') {
            return;
        }
        btn.setAttribute('data-is-print-bound', '1');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.openIncomeStatementPdfModal();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPrintButton);
    } else {
        bindPrintButton();
    }
})();
</script>
