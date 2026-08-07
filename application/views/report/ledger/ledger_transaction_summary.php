<?php
if (!function_exists('gls_format_amt')) {
    function gls_format_amt($amount) {
        $v = floatval($amount);
        if ($v > 0) {
            return number_format($v, 2);
        }
        return '-';
    }
}
if (!function_exists('gls_dr_cr_label')) {
    function gls_dr_cr_label($amount) {
        $v = floatval($amount);
        if ($v > 0) {
            return number_format($v, 2) . ' Dr';
        }
        if ($v < 0) {
            return number_format(abs($v), 2) . ' Cr';
        }
        return '-';
    }
}

$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$gls_ledger_url = function ($account_code) use ($link_cat, $id) {
    return site_url(current_lang() . '/report/account_ledger/' . encode_id($account_code) . '/' . $link_cat . '/' . $id);
};

$transaction = $this->report_model->create_ledger_trans_summary($reportinfo->fromdate, $reportinfo->todate);
$total_credit = 0;
$total_debit = 0;
$net_prfit_credit = 0;
$net_prfit_debit = 0;
$check_exp_inc = 0;
$col_w = '100px';
?>
<div class="row">
    <div class="col-lg-12">
        <div style="padding: 20px 8px; margin: auto; max-width: 1100px; overflow-x: auto;">
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
                            GENERAL LEDGER SUMMARY
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
                a.gls-account-link {
                    color: #1ab394;
                    text-decoration: none;
                }
                a.gls-account-link:hover {
                    text-decoration: underline;
                    color: #18a689;
                }
            </style>

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:900px;">
                <thead>
                    <tr>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;"></th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:<?php echo $col_w; ?>;">Opening</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:<?php echo $col_w; ?>;">Debit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:<?php echo $col_w; ?>;">Credit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:<?php echo $col_w; ?>;">Net Movement</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:<?php echo $col_w; ?>;">Closing</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (array_key_exists(4, $transaction)) {
                    $check_exp_inc = 1;
                    ?>
                    <tr>
                        <td style="padding:10px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="6">Income</td>
                    </tr>
                    <?php
                    foreach ($transaction[4] as $key1 => $value1) {
                        $account_info = $this->finance_model->account_chart(null, $key1)->row();
                        if (!$account_info) {
                            continue;
                        }
                        $debit = 0;
                        $credit = 0;
                        if (!empty($value1['current']) && is_object($value1['current'])) {
                            $debit = floatval($value1['current']->debit);
                            $credit = floatval($value1['current']->credit);
                            $net_prfit_debit += $debit;
                            $net_prfit_credit += $credit;
                            $total_debit += $debit;
                            $total_credit += $credit;
                        }
                        $net_move = $debit - $credit;
                        $acct_code = !empty($account_info->account) ? $account_info->account : $key1;
                        $label = '<a class="gls-account-link" href="' . $gls_ledger_url($acct_code) . '" title="View account ledger">' . htmlspecialchars($account_info->name) . '</a>';
                        ?>
                        <tr>
                            <td style="padding:2px 4px 2px 28px;"><?php echo $label; ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;">-</td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_format_amt($debit); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_format_amt($credit); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_dr_cr_label($net_move); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_dr_cr_label($net_move); ?></td>
                        </tr>
                    <?php }
                    unset($transaction[4]);
                }

                if (array_key_exists(5, $transaction)) {
                    $check_exp_inc = 1;
                    ?>
                    <tr>
                        <td style="padding:14px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="6">Expenses</td>
                    </tr>
                    <?php
                    foreach ($transaction[5] as $key1 => $value1) {
                        $account_info = $this->finance_model->account_chart(null, $key1)->row();
                        if (!$account_info) {
                            continue;
                        }
                        $debit = 0;
                        $credit = 0;
                        if (!empty($value1['current']) && is_object($value1['current'])) {
                            $debit = floatval($value1['current']->debit);
                            $credit = floatval($value1['current']->credit);
                            $net_prfit_debit += $debit;
                            $net_prfit_credit += $credit;
                            $total_debit += $debit;
                            $total_credit += $credit;
                        }
                        $net_move = $debit - $credit;
                        $acct_code = !empty($account_info->account) ? $account_info->account : $key1;
                        $label = '<a class="gls-account-link" href="' . $gls_ledger_url($acct_code) . '" title="View account ledger">' . htmlspecialchars($account_info->name) . '</a>';
                        ?>
                        <tr>
                            <td style="padding:2px 4px 2px 28px;"><?php echo $label; ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;">-</td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_format_amt($debit); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_format_amt($credit); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_dr_cr_label($net_move); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_dr_cr_label($net_move); ?></td>
                        </tr>
                    <?php }
                    unset($transaction[5]);
                }

                if ($check_exp_inc == 1) {
                    $close_balance = $net_prfit_debit - $net_prfit_credit;
                    $balance_credit = 0;
                    $balance_debit = 0;
                    $close_balance_label = '-';
                    if ($close_balance > 0) {
                        $close_balance_label = number_format($close_balance, 2) . ' Cr';
                        $balance_credit = $close_balance;
                        $total_credit += $close_balance;
                    } else if ($close_balance < 0) {
                        $close_balance_label = number_format(abs($close_balance), 2) . ' Dr';
                        $balance_debit = abs($close_balance);
                        $total_debit += abs($close_balance);
                    }
                    ?>
                    <tr>
                        <td style="padding:4px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">Net Surplus (Loss)</td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;"></td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                            <?php echo number_format($balance_debit, 2); ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                            <?php echo number_format($balance_credit, 2); ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                            <?php echo $close_balance_label; ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000; border-bottom:1px solid #000;">
                            <?php echo $close_balance_label; ?>
                        </td>
                    </tr>
                    <tr><td colspan="6" style="height:12px;"></td></tr>
                <?php }

                foreach ($transaction as $key => $value) {
                    $type_account = $this->finance_model->account_typelist($key)->row();
                    if (!$type_account) {
                        continue;
                    }
                    ?>
                    <tr>
                        <td style="padding:10px 4px 2px 0; font-weight:bold; text-transform:uppercase;" colspan="6">
                            <?php echo htmlspecialchars($type_account->name); ?>
                        </td>
                    </tr>
                    <?php
                    foreach ($value as $key1 => $value1) {
                        $account_info = $this->finance_model->account_chart(null, $key1)->row();
                        if (!$account_info) {
                            continue;
                        }
                        $sub_credit = 0;
                        $sub_debit = 0;
                        $open_balance = isset($value1['balance']) ? floatval($value1['balance']) : 0;
                        $open_balance_label = gls_dr_cr_label($open_balance);
                        if ($open_balance > 0) {
                            $sub_debit += $open_balance;
                        } else if ($open_balance < 0) {
                            $sub_credit += abs($open_balance);
                        }
                        $period_debit = 0;
                        $period_credit = 0;
                        if (!empty($value1['current']) && is_object($value1['current'])) {
                            $period_debit = floatval($value1['current']->debit);
                            $period_credit = floatval($value1['current']->credit);
                            $sub_credit += $period_credit;
                            $sub_debit += $period_debit;
                            $total_debit += $period_debit;
                            $total_credit += $period_credit;
                        }
                        $close_balance = $sub_debit - $sub_credit;
                        $acct_code = !empty($account_info->account) ? $account_info->account : $key1;
                        $label = '<a class="gls-account-link" href="' . $gls_ledger_url($acct_code) . '" title="View account ledger">' . htmlspecialchars($account_info->name) . '</a>';
                        ?>
                        <tr>
                            <td style="padding:2px 4px 2px 28px;"><?php echo $label; ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo $open_balance_label; ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_format_amt($period_debit); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_format_amt($period_credit); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_dr_cr_label($period_debit - $period_credit); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:2px 4px;"><?php echo gls_dr_cr_label($close_balance); ?></td>
                        </tr>
                    <?php }
                } ?>

                    <tr>
                        <td style="padding:6px 4px; font-weight:bold; border-top:1px solid #000;">Totals</td>
                        <td style="border-top:1px solid #000;"></td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                            <span style="float:left;">P</span><?php echo number_format($total_debit, 2); ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap; padding:2px 4px; font-weight:bold; border-top:1px solid #000;">
                            <span style="float:left;">P</span><?php echo number_format($total_credit, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000;"></td>
                        <td style="border-top:1px solid #000;"></td>
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
            <button type="button" class="btn btn-primary" id="btnPrintLedgerSummary">
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
$gls_pdf_url = site_url(current_lang() . '/report/ledger_trans_print_summary/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#ledgerSummaryPdfOverlay {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.55);
}
#ledgerSummaryPdfOverlay.is-open { display: block; }
#ledgerSummaryPdfOverlay .gls-dialog {
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
#ledgerSummaryPdfOverlay .gls-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e7eaec;
    background: #f8f8f8;
    flex: 0 0 auto;
}
#ledgerSummaryPdfOverlay .gls-header h4 {
    margin: 0;
    display: inline-block;
    font-size: 16px;
    font-weight: 600;
}
#ledgerSummaryPdfOverlay .gls-header .gls-actions { float: right; }
#ledgerSummaryPdfOverlay .gls-body {
    flex: 1 1 auto;
    min-height: 0;
    background: #f3f3f4;
    position: relative;
}
#ledgerSummaryPdfOverlay .gls-body iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
#glsPdfPrintProgress {
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
#glsPdfPrintProgress.is-visible { display: flex; }
#glsPdfPrintProgress .spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e7eaec;
    border-top-color: #1ab394;
    border-radius: 50%;
    -webkit-animation: gls-pdf-spin 0.8s linear infinite;
    animation: gls-pdf-spin 0.8s linear infinite;
    margin-bottom: 12px;
}
#glsPdfPrintProgress .msg { color: #676a6c; font-size: 14px; }
@-webkit-keyframes gls-pdf-spin {
    to { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
@keyframes gls-pdf-spin {
    to { transform: rotate(360deg); }
}
</style>

<div id="ledgerSummaryPdfOverlay" aria-hidden="true">
    <div class="gls-dialog" role="dialog" aria-labelledby="ledgerSummaryPdfTitle">
        <div class="gls-header">
            <h4 id="ledgerSummaryPdfTitle">General Ledger Summary - PDF</h4>
            <div class="gls-actions">
                <button type="button" id="ledgerSummaryPdfPrintBtn" class="btn btn-xs btn-primary" onclick="window.printLedgerSummaryPdfModal();" title="Print">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeLedgerSummaryPdf();">Close</button>
            </div>
        </div>
        <div class="gls-body">
            <div id="glsPdfPrintProgress" aria-live="polite" aria-busy="false">
                <div class="spinner"></div>
                <div class="msg">Preparing print...</div>
            </div>
            <iframe id="ledgerSummaryPdfFrame" src="about:blank" title="General Ledger Summary PDF Viewer"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var ledgerSummaryPdfUrl = <?php echo json_encode($gls_pdf_url); ?>;
    var currentPdfUrl = ledgerSummaryPdfUrl;
    var printHideTimer = null;

    function getOverlay() {
        return document.getElementById('ledgerSummaryPdfOverlay');
    }
    function getFrame() {
        return document.getElementById('ledgerSummaryPdfFrame');
    }
    function getPrintBtn() {
        return document.getElementById('ledgerSummaryPdfPrintBtn');
    }

    window.showLedgerSummaryPdfPrintProgress = function () {
        var el = document.getElementById('glsPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = 'is-visible';
            el.setAttribute('aria-busy', 'true');
        }
        if (btn) {
            btn.disabled = true;
        }
    };

    window.hideLedgerSummaryPdfPrintProgress = function () {
        if (printHideTimer) {
            clearTimeout(printHideTimer);
            printHideTimer = null;
        }
        var el = document.getElementById('glsPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = '';
            el.setAttribute('aria-busy', 'false');
        }
        if (btn) {
            btn.disabled = false;
        }
    };

    window.openLedgerSummaryPdfModal = function () {
        var overlay = getOverlay();
        var frame = getFrame();
        if (!overlay || !frame) {
            window.open(ledgerSummaryPdfUrl, '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        currentPdfUrl = ledgerSummaryPdfUrl + (ledgerSummaryPdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        window.hideLedgerSummaryPdfPrintProgress();
        frame.src = viewerBase + encodeURIComponent(currentPdfUrl);
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.printLedgerSummaryPdfModal = function () {
        window.showLedgerSummaryPdfPrintProgress();
        var frame = getFrame();
        if (frame && frame.contentWindow && typeof frame.contentWindow.printPdfFile === 'function') {
            try {
                frame.contentWindow.printPdfFile();
                return;
            } catch (e) {}
        }
        if (!currentPdfUrl) {
            window.hideLedgerSummaryPdfPrintProgress();
            return;
        }
        var existing = document.getElementById('gls-pdf-print-frame');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var iframe = document.createElement('iframe');
        iframe.id = 'gls-pdf-print-frame';
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:800px;height:600px;border:0;opacity:0;pointer-events:none;z-index:-1;';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.src = currentPdfUrl;
        document.body.appendChild(iframe);
        var printShown = false;
        function finishPrintPrep() {
            if (printShown) return;
            printShown = true;
            window.hideLedgerSummaryPdfPrintProgress();
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

    window.closeLedgerSummaryPdf = function () {
        window.hideLedgerSummaryPdfPrintProgress();
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
            window.closeLedgerSummaryPdf();
        }
    });
    document.addEventListener('click', function (e) {
        var overlay = getOverlay();
        if (!overlay || overlay.className.indexOf('is-open') === -1) {
            return;
        }
        if (e.target === overlay) {
            window.closeLedgerSummaryPdf();
        }
    });

    function bindPrintButton() {
        var btn = document.getElementById('btnPrintLedgerSummary');
        if (!btn || btn.getAttribute('data-gls-print-bound') === '1') {
            return;
        }
        btn.setAttribute('data-gls-print-bound', '1');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.openLedgerSummaryPdfModal();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPrintButton);
    } else {
        bindPrintButton();
    }
})();
</script>
