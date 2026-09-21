<?php
if (!function_exists('tb_format_amt')) {
    function tb_format_amt($amount) {
        $v = floatval($amount);
        if ($v > 0) {
            return number_format($v, 2);
        }
        return '-';
    }
}

$company = company_info();
$as_at = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$tb_ledger_url = function ($account_code) use ($link_cat, $id) {
    return site_url(current_lang() . '/report/account_ledger/' . encode_id($account_code) . '/' . $link_cat . '/' . $id);
};

$transaction = $this->report_model->create_ledger_trans_summary($reportinfo->fromdate, $reportinfo->todate);
$total_credit = 0;
$total_debit = 0;

// Section order: Income, Expenses, then remaining account types.
$tb_section_order = array();
if (isset($transaction[4])) {
    $tb_section_order[] = array(4, 'Income');
}
if (isset($transaction[5])) {
    $tb_section_order[] = array(5, 'Expenses');
}
foreach ($transaction as $type_id => $_unused) {
    if ($type_id == 4 || $type_id == 5) {
        continue;
    }
    $type_account = $this->finance_model->account_typelist($type_id)->row();
    if ($type_account) {
        $tb_section_order[] = array($type_id, $type_account->name);
    }
}
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
                            TRIAL BALANCE
                        </div>
                        <div style="font-size:13px; margin-top:4px;">As at <?php echo $as_at; ?></div>
                    </td>
                    <td style="width:90px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:12px;">
                        LENDING
                    </td>
                </tr>
            </table>

            <style type="text/css">
                a.tb-account-link {
                    color: #1ab394;
                    text-decoration: none;
                }
                a.tb-account-link:hover {
                    text-decoration: underline;
                    color: #18a689;
                }
            </style>

            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;"></th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:140px;">Debit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:140px;">Credit</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tb_section_order as $section) {
                    $type_id = $section[0];
                    $section_title = $section[1];
                    if (empty($transaction[$type_id])) {
                        continue;
                    }
                    $section_rows = '';
                    $has_row = false;
                    foreach ($transaction[$type_id] as $key1 => $value1) {
                        $account_info = $this->finance_model->account_chart(null, $key1)->row();
                        if (!$account_info) {
                            continue;
                        }
                        $sides = $this->report_model->trial_balance_ending_sides($value1);
                        if ($sides['debit'] <= 0 && $sides['credit'] <= 0) {
                            continue;
                        }
                        $has_row = true;
                        $total_debit += $sides['debit'];
                        $total_credit += $sides['credit'];
                        $acct_code = !empty($account_info->account) ? $account_info->account : $key1;
                        $label = '<a class="tb-account-link" href="' . $tb_ledger_url($acct_code) . '" title="View account ledger">' . htmlspecialchars($account_info->name) . '</a>';
                        $section_rows .= '<tr>'
                            . '<td style="padding:2px 4px 2px 36px;">' . $label . '</td>'
                            . '<td style="text-align:right; white-space:nowrap; padding:2px 4px;">' . tb_format_amt($sides['debit']) . '</td>'
                            . '<td style="text-align:right; white-space:nowrap; padding:2px 4px;">' . tb_format_amt($sides['credit']) . '</td>'
                            . '</tr>';
                    }
                    if (!$has_row) {
                        continue;
                    }
                    ?>
                    <tr>
                        <td style="padding:10px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="3">
                            <?php echo htmlspecialchars($section_title); ?>
                        </td>
                    </tr>
                    <?php echo $section_rows;
                } ?>

                    <tr>
                        <td style="padding:6px 4px; font-weight:bold; border-top:1px solid #000;">Totals</td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                            <span style="float:left;">P</span><?php echo number_format($total_debit, 2); ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                            <span style="float:left;">P</span><?php echo number_format($total_credit, 2); ?>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="text-align:right; padding:0 4px;">
                            <div style="border-bottom:3px double #000; display:inline-block; min-width:120px;">&nbsp;</div>
                        </td>
                        <td style="text-align:right; padding:0 4px;">
                            <div style="border-bottom:3px double #000; display:inline-block; min-width:120px;">&nbsp;</div>
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
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report/ledger_trial_balance_print/' . $link_cat . '/' . $id)); ?>"
                    data-pdf-name="Trial_Balance.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintTrialBalance">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/ledger_trial_balance_export/' . $link_cat . '/' . $id); ?>" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i> <?php echo lang('export_excel'); ?>
            </a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/create_ledger_trans_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/general_leger_transaction/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>

<?php
$tb_pdf_url = site_url(current_lang() . '/report/ledger_trial_balance_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#trialBalancePdfOverlay {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.55);
}
#trialBalancePdfOverlay.is-open { display: block; }
#trialBalancePdfOverlay .tb-dialog {
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
#trialBalancePdfOverlay .tb-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e7eaec;
    background: #f8f8f8;
    flex: 0 0 auto;
}
#trialBalancePdfOverlay .tb-header h4 {
    margin: 0;
    display: inline-block;
    font-size: 16px;
    font-weight: 600;
}
#trialBalancePdfOverlay .tb-header .tb-actions { float: right; }
#trialBalancePdfOverlay .tb-body {
    flex: 1 1 auto;
    min-height: 0;
    background: #f3f3f4;
    position: relative;
}
#trialBalancePdfOverlay .tb-body iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
#tbPdfPrintProgress {
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
#tbPdfPrintProgress.is-visible { display: flex; }
#tbPdfPrintProgress .spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e7eaec;
    border-top-color: #1ab394;
    border-radius: 50%;
    -webkit-animation: tb-pdf-spin 0.8s linear infinite;
    animation: tb-pdf-spin 0.8s linear infinite;
    margin-bottom: 12px;
}
#tbPdfPrintProgress .msg { color: #676a6c; font-size: 14px; }
@-webkit-keyframes tb-pdf-spin {
    to { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
@keyframes tb-pdf-spin {
    to { transform: rotate(360deg); }
}
</style>

<div id="trialBalancePdfOverlay" aria-hidden="true">
    <div class="tb-dialog" role="dialog" aria-labelledby="trialBalancePdfTitle">
        <div class="tb-header">
            <h4 id="trialBalancePdfTitle">Trial Balance - PDF</h4>
            <div class="tb-actions">
                <button type="button" id="trialBalancePdfPrintBtn" class="btn btn-xs btn-primary" onclick="window.printTrialBalancePdfModal();" title="Print">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeTrialBalancePdf();">Close</button>
            </div>
        </div>
        <div class="tb-body">
            <div id="tbPdfPrintProgress" aria-live="polite" aria-busy="false">
                <div class="spinner"></div>
                <div class="msg">Preparing print...</div>
            </div>
            <iframe id="trialBalancePdfFrame" src="about:blank" title="Trial Balance PDF Viewer"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var trialBalancePdfUrl = <?php echo json_encode($tb_pdf_url); ?>;
    var currentPdfUrl = trialBalancePdfUrl;
    var printHideTimer = null;

    function getOverlay() {
        return document.getElementById('trialBalancePdfOverlay');
    }
    function getFrame() {
        return document.getElementById('trialBalancePdfFrame');
    }
    function getPrintBtn() {
        return document.getElementById('trialBalancePdfPrintBtn');
    }

    window.showTrialBalancePdfPrintProgress = function () {
        var el = document.getElementById('tbPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = 'is-visible';
            el.setAttribute('aria-busy', 'true');
        }
        if (btn) {
            btn.disabled = true;
        }
    };

    window.hideTrialBalancePdfPrintProgress = function () {
        if (printHideTimer) {
            clearTimeout(printHideTimer);
            printHideTimer = null;
        }
        var el = document.getElementById('tbPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = '';
            el.setAttribute('aria-busy', 'false');
        }
        if (btn) {
            btn.disabled = false;
        }
    };

    window.openTrialBalancePdfModal = function () {
        var overlay = getOverlay();
        var frame = getFrame();
        if (!overlay || !frame) {
            window.open(trialBalancePdfUrl, '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        currentPdfUrl = trialBalancePdfUrl + (trialBalancePdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        window.hideTrialBalancePdfPrintProgress();
        frame.src = viewerBase + encodeURIComponent(currentPdfUrl);
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.printTrialBalancePdfModal = function () {
        window.showTrialBalancePdfPrintProgress();
        var frame = getFrame();
        if (frame && frame.contentWindow && typeof frame.contentWindow.printPdfFile === 'function') {
            try {
                frame.contentWindow.printPdfFile();
                return;
            } catch (e) {}
        }
        if (!currentPdfUrl) {
            window.hideTrialBalancePdfPrintProgress();
            return;
        }
        var existing = document.getElementById('tb-pdf-print-frame');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var iframe = document.createElement('iframe');
        iframe.id = 'tb-pdf-print-frame';
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:800px;height:600px;border:0;opacity:0;pointer-events:none;z-index:-1;';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.src = currentPdfUrl;
        document.body.appendChild(iframe);
        var printShown = false;
        function finishPrintPrep() {
            if (printShown) return;
            printShown = true;
            window.hideTrialBalancePdfPrintProgress();
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

    window.closeTrialBalancePdf = function () {
        window.hideTrialBalancePdfPrintProgress();
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
            window.closeTrialBalancePdf();
        }
    });
    document.addEventListener('click', function (e) {
        var overlay = getOverlay();
        if (!overlay || overlay.className.indexOf('is-open') === -1) {
            return;
        }
        if (e.target === overlay) {
            window.closeTrialBalancePdf();
        }
    });

    function bindPrintButton() {
        var btn = document.getElementById('btnPrintTrialBalance');
        if (!btn || btn.getAttribute('data-tb-print-bound') === '1') {
            return;
        }
        btn.setAttribute('data-tb-print-bound', '1');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.openTrialBalancePdfModal();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPrintButton);
    } else {
        bindPrintButton();
    }
})();
</script>
