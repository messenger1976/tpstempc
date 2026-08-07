<?php
$company = company_info();
$journal_label = !empty($journalinfo->type)
    ? strtoupper(function_exists('journal_display_type') ? journal_display_type($journalinfo->type) : $journalinfo->type)
    : 'JOURNAL';
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$journal_back = 'j' . $link_cat;
$journal_ledger_url = function ($account_code) use ($journal_back, $id) {
    return site_url(current_lang() . '/report/account_ledger/' . encode_id($account_code) . '/' . $journal_back . '/' . $id);
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
                            <?php echo htmlspecialchars($journal_label); ?> JOURNAL ENTRIES
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
                a.cje-account-link {
                    color: #1ab394;
                    text-decoration: none;
                }
                a.cje-account-link:hover {
                    text-decoration: underline;
                    color: #18a689;
                }
                table.cje-table tbody tr td.draw_border {
                    border-top: 1px solid #ccc;
                }
            </style>

            <table class="cje-table" style="width:100%; border-collapse:collapse; font-size:12px; min-width:1050px;">
                <thead>
                    <tr>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:80px;">Date</th>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:55px;">Entry</th>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:70px;">Ref #</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Account</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Member / Person</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Particulars</th>
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
                            No journal transactions found for the selected date range
                            (<?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?>).
                        </td>
                    </tr>
                    <?php
                } else {
                    $entry_id = null;
                    $year = null;
                    $total_credit = 0;
                    $total_debit = 0;
                    $show_date = true;

                    foreach ($transaction as $key => $value) {
                        $total_credit += floatval($value->credit);
                        $total_debit += floatval($value->debit);
                        $year_track = date('Y', strtotime($value->date));
                        $class = '';

                        if ($key === 0) {
                            $entry_id = $value->entryid;
                            $year = $year_track;
                            $show_date = true;
                            ?>
                            <tr>
                                <td colspan="8" style="padding:6px 4px 2px; font-weight:bold;"><?php echo $year_track; ?></td>
                            </tr>
                            <?php
                        } else {
                            if ($year != $year_track) {
                                $year = $year_track;
                                ?>
                                <tr>
                                    <td colspan="8" style="padding:10px 4px 2px; font-weight:bold;"><?php echo $year_track; ?></td>
                                </tr>
                                <?php
                            }
                            if ($entry_id != $value->entryid) {
                                $class = 'draw_border';
                                $entry_id = $value->entryid;
                                $show_date = true;
                            } else {
                                $show_date = false;
                            }
                        }

                        $ref_no = '';
                        $ref_url = '';
                        if (!empty($value->related_ref_no)) {
                            $ref_no = $value->related_ref_no;
                            $ref_url = !empty($value->related_ref_url) ? $value->related_ref_url : '';
                        } else {
                            $ref_no = (isset($value->invoiceid) && $value->invoiceid > 0)
                                ? $value->invoiceid
                                : (isset($value->refferenceID) ? $value->refferenceID : '');
                            $ref_url = function_exists('get_gl_reference_url')
                                ? get_gl_reference_url(isset($value->fromtable) ? $value->fromtable : '', isset($value->refferenceID) ? $value->refferenceID : null)
                                : '';
                        }
                        $acct_code = isset($value->account) ? $value->account : '';
                        $acct_label = htmlspecialchars($acct_code) . ' - ' . htmlspecialchars($value->name);
                        if ($acct_code !== '') {
                            $acct_label = '<a class="cje-account-link" href="' . $journal_ledger_url($acct_code) . '" title="View account ledger">' . $acct_label . '</a>';
                        }
                        $pad = (floatval($value->credit) > 0) ? 'padding-left:24px;' : '';
                        $particulars = '';
                        if (!empty($value->description)) {
                            $particulars = $value->description;
                        } elseif (!empty($value->trans_comment)) {
                            $particulars = $value->trans_comment;
                        }
                        ?>
                        <tr>
                            <td class="<?php echo $class; ?>" style="text-align:center; padding:3px 4px; white-space:nowrap;">
                                <?php echo $show_date ? date('M d', strtotime($value->date)) : ''; ?>
                            </td>
                            <td class="<?php echo $class; ?>" style="text-align:center; padding:3px 4px;">
                                <?php echo $show_date ? htmlspecialchars($value->entryid) : ''; ?>
                            </td>
                            <td class="<?php echo $class; ?>" style="text-align:center; padding:3px 4px;">
                                <?php
                                if ($ref_no !== '' && $ref_no !== null) {
                                    if ($ref_url !== '') {
                                        echo anchor($ref_url, '#' . $ref_no, array('title' => 'View source document'));
                                    } else {
                                        echo '#' . htmlspecialchars($ref_no);
                                    }
                                } else {
                                    echo '&mdash;';
                                }
                                ?>
                            </td>
                            <td class="<?php echo $class; ?>" style="padding:3px 4px; <?php echo $pad; ?>"><?php echo $acct_label; ?></td>
                            <td class="<?php echo $class; ?>" style="padding:3px 4px;">
                                <?php
                                $rel_name = isset($value->related_entity_name) ? $value->related_entity_name : '';
                                $rel_url = isset($value->related_entity_url) ? $value->related_entity_url : '';
                                if ($rel_name !== '') {
                                    if ($rel_url !== '') {
                                        echo anchor($rel_url, htmlspecialchars($rel_name), array('title' => 'View member / subledger'));
                                    } else {
                                        echo htmlspecialchars($rel_name);
                                    }
                                } else {
                                    echo '&mdash;';
                                }
                                ?>
                            </td>
                            <td class="<?php echo $class; ?>" style="padding:3px 4px;">
                                <?php echo $particulars !== '' ? htmlspecialchars($particulars) : '&mdash;'; ?>
                            </td>
                            <td class="<?php echo $class; ?>" style="text-align:right; white-space:nowrap; padding:3px 4px;">
                                <?php echo (floatval($value->debit) > 0 ? number_format($value->debit, 2) : ''); ?>
                            </td>
                            <td class="<?php echo $class; ?>" style="text-align:right; white-space:nowrap; padding:3px 4px;">
                                <?php echo (floatval($value->credit) > 0 ? number_format($value->credit, 2) : ''); ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="6"></td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($total_debit, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($total_credit, 2); ?>
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
            <button type="button" class="btn btn-primary" id="btnPrintJournalTrans">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/create_journal_trans_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report/journal_entry/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>

<?php
$cje_pdf_url = site_url(current_lang() . '/report/journal_trans_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#journalTransPdfOverlay {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.55);
}
#journalTransPdfOverlay.is-open { display: block; }
#journalTransPdfOverlay .cje-dialog {
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
#journalTransPdfOverlay .cje-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e7eaec;
    background: #f8f8f8;
    flex: 0 0 auto;
}
#journalTransPdfOverlay .cje-header h4 {
    margin: 0;
    display: inline-block;
    font-size: 16px;
    font-weight: 600;
}
#journalTransPdfOverlay .cje-header .cje-actions { float: right; }
#journalTransPdfOverlay .cje-body {
    flex: 1 1 auto;
    min-height: 0;
    background: #f3f3f4;
    position: relative;
}
#journalTransPdfOverlay .cje-body iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
#cjePdfPrintProgress {
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
#cjePdfPrintProgress.is-visible { display: flex; }
#cjePdfPrintProgress .spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e7eaec;
    border-top-color: #1ab394;
    border-radius: 50%;
    -webkit-animation: cje-pdf-spin 0.8s linear infinite;
    animation: cje-pdf-spin 0.8s linear infinite;
    margin-bottom: 12px;
}
#cjePdfPrintProgress .msg { color: #676a6c; font-size: 14px; }
@-webkit-keyframes cje-pdf-spin {
    to { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
@keyframes cje-pdf-spin {
    to { transform: rotate(360deg); }
}
</style>

<div id="journalTransPdfOverlay" aria-hidden="true">
    <div class="cje-dialog" role="dialog" aria-labelledby="journalTransPdfTitle">
        <div class="cje-header">
            <h4 id="journalTransPdfTitle"><?php echo htmlspecialchars($journal_label); ?> Journal Entries - PDF</h4>
            <div class="cje-actions">
                <button type="button" id="journalTransPdfPrintBtn" class="btn btn-xs btn-primary" onclick="window.printJournalTransPdfModal();" title="Print">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeJournalTransPdf();">Close</button>
            </div>
        </div>
        <div class="cje-body">
            <div id="cjePdfPrintProgress" aria-live="polite" aria-busy="false">
                <div class="spinner"></div>
                <div class="msg">Preparing print...</div>
            </div>
            <iframe id="journalTransPdfFrame" src="about:blank" title="Journal Entries PDF Viewer"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var journalTransPdfUrl = <?php echo json_encode($cje_pdf_url); ?>;
    var currentPdfUrl = journalTransPdfUrl;
    var printHideTimer = null;

    function getOverlay() {
        return document.getElementById('journalTransPdfOverlay');
    }
    function getFrame() {
        return document.getElementById('journalTransPdfFrame');
    }
    function getPrintBtn() {
        return document.getElementById('journalTransPdfPrintBtn');
    }

    window.showJournalTransPdfPrintProgress = function () {
        var el = document.getElementById('cjePdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = 'is-visible';
            el.setAttribute('aria-busy', 'true');
        }
        if (btn) {
            btn.disabled = true;
        }
    };

    window.hideJournalTransPdfPrintProgress = function () {
        if (printHideTimer) {
            clearTimeout(printHideTimer);
            printHideTimer = null;
        }
        var el = document.getElementById('cjePdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = '';
            el.setAttribute('aria-busy', 'false');
        }
        if (btn) {
            btn.disabled = false;
        }
    };

    window.openJournalTransPdfModal = function () {
        var overlay = getOverlay();
        var frame = getFrame();
        if (!overlay || !frame) {
            window.open(journalTransPdfUrl, '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        currentPdfUrl = journalTransPdfUrl + (journalTransPdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        window.hideJournalTransPdfPrintProgress();
        frame.src = viewerBase + encodeURIComponent(currentPdfUrl);
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.printJournalTransPdfModal = function () {
        window.showJournalTransPdfPrintProgress();
        var frame = getFrame();
        if (frame && frame.contentWindow && typeof frame.contentWindow.printPdfFile === 'function') {
            try {
                frame.contentWindow.printPdfFile();
                return;
            } catch (e) {}
        }
        if (!currentPdfUrl) {
            window.hideJournalTransPdfPrintProgress();
            return;
        }
        var existing = document.getElementById('cje-pdf-print-frame');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var iframe = document.createElement('iframe');
        iframe.id = 'cje-pdf-print-frame';
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:800px;height:600px;border:0;opacity:0;pointer-events:none;z-index:-1;';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.src = currentPdfUrl;
        document.body.appendChild(iframe);
        var printShown = false;
        function finishPrintPrep() {
            if (printShown) return;
            printShown = true;
            window.hideJournalTransPdfPrintProgress();
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

    window.closeJournalTransPdf = function () {
        window.hideJournalTransPdfPrintProgress();
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
            window.closeJournalTransPdf();
        }
    });
    document.addEventListener('click', function (e) {
        var overlay = getOverlay();
        if (!overlay || overlay.className.indexOf('is-open') === -1) {
            return;
        }
        if (e.target === overlay) {
            window.closeJournalTransPdf();
        }
    });

    function bindPrintButton() {
        var btn = document.getElementById('btnPrintJournalTrans');
        if (!btn || btn.getAttribute('data-cje-print-bound') === '1') {
            return;
        }
        btn.setAttribute('data-cje-print-bound', '1');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.openJournalTransPdfModal();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPrintButton);
    } else {
        bindPrintButton();
    }
})();
</script>
