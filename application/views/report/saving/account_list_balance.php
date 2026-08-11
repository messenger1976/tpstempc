<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$account_type_names = isset($account_type_names) ? $account_type_names : array();
$account_type_label = isset($account_type_label) ? $account_type_label : 'All Account Types';
$transaction = isset($transaction) ? $transaction : array();
$ledger_url = function ($account) use ($link_cat, $id) {
    return site_url(current_lang() . '/report_saving/new_saving_account_statement_view/' . $link_cat . '/' . $id . '/' . encode_id($account));
};
$ledger_embed_url = function ($account) use ($ledger_url) {
    return $ledger_url($account) . '?embed=1';
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
                            SAVINGS ACCOUNT LIST
                        </div>
                        <div style="font-size:13px; margin-top:4px;">
                            Accounts opened from <?php echo $period_from; ?> to <?php echo $period_to; ?>
                        </div>
                        <div style="font-size:12px; margin-top:2px;">
                            Account Type: <?php echo htmlspecialchars($account_type_label); ?>
                        </div>
                    </td>
                    <td style="width:90px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:12px;">
                        LENDING
                    </td>
                </tr>
            </table>

            <style type="text/css">
                a.sal-link {
                    color: #1ab394;
                    text-decoration: none;
                    cursor: pointer;
                }
                a.sal-link:hover {
                    text-decoration: underline;
                    color: #18a689;
                }
                a.sal-open-ledger {
                    font-weight: 600;
                }
            </style>

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:1000px;">
                <thead>
                    <tr>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:45px;">S/No</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Account No</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Member ID</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Account Name</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Account Type</th>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:90px;">Date Opened</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Available Balance</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Maintaining Balance</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Total Balance</th>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (empty($transaction)) {
                    ?>
                    <tr>
                        <td colspan="10" style="text-align:center; padding:20px; color:#999; font-style:italic;">
                            No savings accounts found for accounts opened
                            <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?>.
                        </td>
                    </tr>
                    <?php
                } else {
                    $i = 1;
                    $balance = 0;
                    $maintaining = 0;
                    $actual = 0;
                    foreach ($transaction as $value) {
                        $avail = floatval($value->balance);
                        $maint = floatval($value->virtual_balance);
                        $act = $avail + $maint;
                        $balance += $avail;
                        $maintaining += $maint;
                        $actual += $act;

                        $acct_no = !empty($value->old_members_acct) ? $value->old_members_acct : $value->account;
                        $member_id = !empty($value->members_member_id) ? $value->members_member_id : $value->member_id;
                        $acct_name = $this->report_model->saving_account_name($value->RFID, $value->tablename);
                        $type_name = isset($account_type_names[$value->account_cat])
                            ? $account_type_names[$value->account_cat]
                            : $value->account_cat;
                        $opened = !empty($value->createdon) ? date('M d, Y', strtotime($value->createdon)) : '&mdash;';
                        $member_url = current_lang() . '/report_member/member_profile/?member=' . urlencode($member_id);
                        $stmt_url = $ledger_url($value->account);
                        $stmt_embed = $ledger_embed_url($value->account);
                        $ledger_title = 'Savings Ledger — ' . $acct_no;
                        ?>
                        <tr>
                            <td style="text-align:center; padding:3px 4px;"><?php echo $i++; ?>.</td>
                            <td style="padding:3px 4px;">
                                <a href="<?php echo htmlspecialchars($stmt_embed); ?>"
                                   class="sal-link sal-open-ledger"
                                   data-ledger-url="<?php echo htmlspecialchars($stmt_embed); ?>"
                                   data-ledger-title="<?php echo htmlspecialchars($ledger_title); ?>"
                                   title="View savings ledger">
                                    <?php echo htmlspecialchars($acct_no); ?>
                                </a>
                            </td>
                            <td style="padding:3px 4px;">
                                <?php
                                if ($member_id !== '' && $member_id !== null) {
                                    echo anchor($member_url, htmlspecialchars($member_id), array('class' => 'sal-link', 'title' => 'View member profile', 'target' => '_blank'));
                                } else {
                                    echo '&mdash;';
                                }
                                ?>
                            </td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($acct_name); ?></td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($type_name); ?></td>
                            <td style="text-align:center; padding:3px 4px; white-space:nowrap;"><?php echo $opened; ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo number_format($avail, 2); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo number_format($maint, 2); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo number_format($act, 2); ?></td>
                            <td style="text-align:center; padding:3px 4px;">
                                <a href="<?php echo htmlspecialchars($stmt_url); ?>"
                                   class="btn btn-success btn-xs btn-outline"
                                   target="_blank"
                                   title="Open savings ledger in full page">
                                    <i class="fa fa-th-list"></i> Ledger
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="6"></td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($balance, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($maintaining, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($actual, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;"></td>
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
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report_saving/saving_account_accountlist_print/' . $link_cat . '/' . $id)); ?>"
                    data-pdf-name="Savings_Account_List.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintSavingAcctList">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_accountlist_export/' . $link_cat . '/' . $id); ?>" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_saving/saving_account_report/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>

<!-- Savings Account Ledger popup (Account No click) -->
<style type="text/css">
#savingLedgerOverlay {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 100000;
    background: rgba(0,0,0,0.55);
}
#savingLedgerOverlay.is-open { display: block; }
#savingLedgerOverlay .slg-dialog {
    position: absolute;
    left: 4%;
    top: 3%;
    width: 92%;
    height: 94%;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
#savingLedgerOverlay .slg-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e7eaec;
    background: #f8f8f8;
    flex: 0 0 auto;
}
#savingLedgerOverlay .slg-header h4 {
    margin: 0;
    display: inline-block;
    font-size: 16px;
    font-weight: 600;
}
#savingLedgerOverlay .slg-header .slg-actions { float: right; }
#savingLedgerOverlay .slg-body {
    flex: 1 1 auto;
    min-height: 0;
    background: #fff;
    position: relative;
}
#savingLedgerOverlay .slg-body iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
#slgLoading {
    display: none;
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 2;
    background: rgba(255,255,255,0.9);
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
}
#slgLoading.is-visible { display: flex; }
#slgLoading .spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e7eaec;
    border-top-color: #1ab394;
    border-radius: 50%;
    -webkit-animation: slg-spin 0.8s linear infinite;
    animation: slg-spin 0.8s linear infinite;
    margin-bottom: 12px;
}
@-webkit-keyframes slg-spin { to { -webkit-transform: rotate(360deg); transform: rotate(360deg); } }
@keyframes slg-spin { to { transform: rotate(360deg); } }
</style>

<div id="savingLedgerOverlay" aria-hidden="true">
    <div class="slg-dialog" role="dialog" aria-labelledby="savingLedgerTitle">
        <div class="slg-header">
            <h4 id="savingLedgerTitle">Savings Account Ledger</h4>
            <div class="slg-actions">
                <a id="savingLedgerOpenFull" href="#" target="_blank" class="btn btn-xs btn-white" title="Open full page">
                    <i class="fa fa-external-link"></i> Full page
                </a>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeSavingLedgerPopup();">Close</button>
            </div>
        </div>
        <div class="slg-body">
            <div id="slgLoading" aria-live="polite">
                <div class="spinner"></div>
                <div>Loading ledger...</div>
            </div>
            <iframe id="savingLedgerFrame" src="about:blank" title="Savings Account Ledger"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    function getLedgerOverlay() {
        return document.getElementById('savingLedgerOverlay');
    }
    function getLedgerFrame() {
        return document.getElementById('savingLedgerFrame');
    }
    function showLedgerLoading(show) {
        var el = document.getElementById('slgLoading');
        if (!el) return;
        el.className = show ? 'is-visible' : '';
    }

    window.openSavingLedgerPopup = function (url, title) {
        var overlay = getLedgerOverlay();
        var frame = getLedgerFrame();
        var titleEl = document.getElementById('savingLedgerTitle');
        var fullBtn = document.getElementById('savingLedgerOpenFull');
        if (!overlay || !frame || !url) {
            if (url) window.open(url.replace('?embed=1', '').replace('&embed=1', ''), '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        if (titleEl) {
            titleEl.textContent = title || 'Savings Account Ledger';
        }
        if (fullBtn) {
            fullBtn.href = url.replace('?embed=1', '').replace('&embed=1', '');
        }
        showLedgerLoading(true);
        frame.onload = function () {
            showLedgerLoading(false);
        };
        frame.src = url + (url.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.closeSavingLedgerPopup = function () {
        var overlay = getLedgerOverlay();
        var frame = getLedgerFrame();
        showLedgerLoading(false);
        if (frame) {
            frame.src = 'about:blank';
        }
        if (overlay) {
            overlay.className = '';
            overlay.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
    };

    function bindLedgerLinks() {
        document.addEventListener('click', function (e) {
            var t = e.target;
            while (t && t !== document && !(t.classList && t.classList.contains('sal-open-ledger'))) {
                t = t.parentNode;
            }
            if (!t || t === document) {
                return;
            }
            e.preventDefault();
            var url = t.getAttribute('data-ledger-url') || t.getAttribute('href');
            var title = t.getAttribute('data-ledger-title') || 'Savings Account Ledger';
            window.openSavingLedgerPopup(url, title);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                window.closeSavingLedgerPopup();
            }
        });
        document.addEventListener('click', function (e) {
            var overlay = getLedgerOverlay();
            if (!overlay || overlay.className.indexOf('is-open') === -1) {
                return;
            }
            if (e.target === overlay) {
                window.closeSavingLedgerPopup();
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindLedgerLinks);
    } else {
        bindLedgerLinks();
    }
})();
</script>

<?php
$sal_pdf_url = site_url(current_lang() . '/report_saving/saving_account_accountlist_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#savingAcctListPdfOverlay {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.55);
}
#savingAcctListPdfOverlay.is-open { display: block; }
#savingAcctListPdfOverlay .sal-dialog {
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
#savingAcctListPdfOverlay .sal-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e7eaec;
    background: #f8f8f8;
    flex: 0 0 auto;
}
#savingAcctListPdfOverlay .sal-header h4 {
    margin: 0;
    display: inline-block;
    font-size: 16px;
    font-weight: 600;
}
#savingAcctListPdfOverlay .sal-header .sal-actions { float: right; }
#savingAcctListPdfOverlay .sal-body {
    flex: 1 1 auto;
    min-height: 0;
    background: #f3f3f4;
    position: relative;
}
#savingAcctListPdfOverlay .sal-body iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
#salPdfPrintProgress {
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
#salPdfPrintProgress.is-visible { display: flex; }
#salPdfPrintProgress .spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e7eaec;
    border-top-color: #1ab394;
    border-radius: 50%;
    -webkit-animation: sal-pdf-spin 0.8s linear infinite;
    animation: sal-pdf-spin 0.8s linear infinite;
    margin-bottom: 12px;
}
#salPdfPrintProgress .msg { color: #676a6c; font-size: 14px; }
@-webkit-keyframes sal-pdf-spin {
    to { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
@keyframes sal-pdf-spin {
    to { transform: rotate(360deg); }
}
</style>

<div id="savingAcctListPdfOverlay" aria-hidden="true">
    <div class="sal-dialog" role="dialog" aria-labelledby="savingAcctListPdfTitle">
        <div class="sal-header">
            <h4 id="savingAcctListPdfTitle">Savings Account List - PDF</h4>
            <div class="sal-actions">
                <button type="button" id="savingAcctListPdfPrintBtn" class="btn btn-xs btn-primary" onclick="window.printSavingAcctListPdfModal();" title="Print">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeSavingAcctListPdf();">Close</button>
            </div>
        </div>
        <div class="sal-body">
            <div id="salPdfPrintProgress" aria-live="polite" aria-busy="false">
                <div class="spinner"></div>
                <div class="msg">Preparing print...</div>
            </div>
            <iframe id="savingAcctListPdfFrame" src="about:blank" title="Savings Account List PDF Viewer"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var savingAcctListPdfUrl = <?php echo json_encode($sal_pdf_url); ?>;
    var currentPdfUrl = savingAcctListPdfUrl;
    var printHideTimer = null;

    function getOverlay() {
        return document.getElementById('savingAcctListPdfOverlay');
    }
    function getFrame() {
        return document.getElementById('savingAcctListPdfFrame');
    }
    function getPrintBtn() {
        return document.getElementById('savingAcctListPdfPrintBtn');
    }

    window.showSavingAcctListPdfPrintProgress = function () {
        var el = document.getElementById('salPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = 'is-visible';
            el.setAttribute('aria-busy', 'true');
        }
        if (btn) {
            btn.disabled = true;
        }
    };

    window.hideSavingAcctListPdfPrintProgress = function () {
        if (printHideTimer) {
            clearTimeout(printHideTimer);
            printHideTimer = null;
        }
        var el = document.getElementById('salPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = '';
            el.setAttribute('aria-busy', 'false');
        }
        if (btn) {
            btn.disabled = false;
        }
    };

    window.openSavingAcctListPdfModal = function () {
        var overlay = getOverlay();
        var frame = getFrame();
        if (!overlay || !frame) {
            window.open(savingAcctListPdfUrl, '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        currentPdfUrl = savingAcctListPdfUrl + (savingAcctListPdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        window.hideSavingAcctListPdfPrintProgress();
        frame.src = viewerBase + encodeURIComponent(currentPdfUrl);
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.printSavingAcctListPdfModal = function () {
        window.showSavingAcctListPdfPrintProgress();
        var frame = getFrame();
        if (frame && frame.contentWindow && typeof frame.contentWindow.printPdfFile === 'function') {
            try {
                frame.contentWindow.printPdfFile();
                return;
            } catch (e) {}
        }
        if (!currentPdfUrl) {
            window.hideSavingAcctListPdfPrintProgress();
            return;
        }
        var existing = document.getElementById('sal-pdf-print-frame');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var iframe = document.createElement('iframe');
        iframe.id = 'sal-pdf-print-frame';
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:800px;height:600px;border:0;opacity:0;pointer-events:none;z-index:-1;';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.src = currentPdfUrl;
        document.body.appendChild(iframe);
        var printShown = false;
        function finishPrintPrep() {
            if (printShown) return;
            printShown = true;
            window.hideSavingAcctListPdfPrintProgress();
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

    window.closeSavingAcctListPdf = function () {
        window.hideSavingAcctListPdfPrintProgress();
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
            window.closeSavingAcctListPdf();
        }
    });
    document.addEventListener('click', function (e) {
        var overlay = getOverlay();
        if (!overlay || overlay.className.indexOf('is-open') === -1) {
            return;
        }
        if (e.target === overlay) {
            window.closeSavingAcctListPdf();
        }
    });

    function bindPrintButton() {
        var btn = document.getElementById('btnPrintSavingAcctList');
        if (!btn || btn.getAttribute('data-sal-print-bound') === '1') {
            return;
        }
        btn.setAttribute('data-sal-print-bound', '1');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.openSavingAcctListPdfModal();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPrintButton);
    } else {
        bindPrintButton();
    }
})();
</script>
