<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
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
                            MEMBER CBU TRANSACTIONS
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

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:1050px;">
                <thead>
                    <tr>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:45px;">S/No</th>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:90px;">Date</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px; width:110px;">Member ID</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Member Name</th>
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
                            No CBU transactions found for
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
                        $dt = explode(' ', $value->createdon);
                        $name = trim(preg_replace('/\s+/', ' ', !empty($value->member_name) ? $value->member_name : ''));
                        $particulars = trim(isset($value->system_comment) ? $value->system_comment : '');
                        if (!empty($value->comment)) {
                            $particulars .= ($particulars !== '' ? ' — ' : '') . $value->comment;
                        }
                        ?>
                        <tr>
                            <td style="text-align:center; padding:3px 4px;"><?php echo $i++; ?>.</td>
                            <td style="text-align:center; padding:3px 4px; white-space:nowrap;"><?php echo format_date($dt[0], false); ?></td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($value->member_id); ?></td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($name); ?></td>
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
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report_contribution/contribution_transaction_print/' . $link_cat . '/' . $id)); ?>"
                    data-pdf-name="Member_CBU_Transactions.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintCbuTrans">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_contribution/contribution_transaction_export/' . $link_cat . '/' . $id); ?>" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_contribution/create_contribution_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_contribution/contribution_report/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>

<?php
$cbu_trans_pdf_url = site_url(current_lang() . '/report_contribution/contribution_transaction_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#cbuTransPdfOverlay {
    display: none; position: fixed; left: 0; top: 0; right: 0; bottom: 0;
    z-index: 99999; background: rgba(0,0,0,0.55);
}
#cbuTransPdfOverlay.is-open { display: block; }
#cbuTransPdfOverlay .cbut-dialog {
    position: absolute; left: 5%; top: 4%; width: 90%; height: 92%;
    background: #fff; border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; overflow: hidden;
}
#cbuTransPdfOverlay .cbut-header {
    padding: 12px 15px; border-bottom: 1px solid #e7eaec; background: #f8f8f8; flex: 0 0 auto;
}
#cbuTransPdfOverlay .cbut-header h4 { margin: 0; display: inline-block; font-size: 16px; font-weight: 600; }
#cbuTransPdfOverlay .cbut-header .cbut-actions { float: right; }
#cbuTransPdfOverlay .cbut-body { flex: 1 1 auto; min-height: 0; background: #f3f3f4; }
#cbuTransPdfOverlay .cbut-body iframe { width: 100%; height: 100%; border: 0; display: block; }
</style>
<div id="cbuTransPdfOverlay" aria-hidden="true">
    <div class="cbut-dialog" role="dialog">
        <div class="cbut-header">
            <h4>Member CBU Transactions - PDF</h4>
            <div class="cbut-actions">
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeCbuTransPdf();">Close</button>
            </div>
        </div>
        <div class="cbut-body">
            <iframe id="cbuTransPdfFrame" src="about:blank" title="Member CBU Transactions PDF"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var pdfUrl = <?php echo json_encode($cbu_trans_pdf_url); ?>;
    window.openCbuTransPdf = function () {
        var overlay = document.getElementById('cbuTransPdfOverlay');
        var frame = document.getElementById('cbuTransPdfFrame');
        if (!overlay || !frame) { window.open(pdfUrl, '_blank'); return; }
        if (overlay.parentNode !== document.body) document.body.appendChild(overlay);
        frame.src = viewerBase + encodeURIComponent(pdfUrl + (pdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now());
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    window.closeCbuTransPdf = function () {
        var overlay = document.getElementById('cbuTransPdfOverlay');
        var frame = document.getElementById('cbuTransPdfFrame');
        if (frame) frame.src = 'about:blank';
        if (overlay) { overlay.className = ''; overlay.setAttribute('aria-hidden', 'true'); }
        document.body.style.overflow = '';
    };
    function bind() {
        var btn = document.getElementById('btnPrintCbuTrans');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.openCbuTransPdf();
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) window.closeCbuTransPdf();
        });
        document.addEventListener('click', function (e) {
            var po = document.getElementById('cbuTransPdfOverlay');
            if (po && po.className.indexOf('is-open') !== -1 && e.target === po) window.closeCbuTransPdf();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
    else bind();
})();
</script>
