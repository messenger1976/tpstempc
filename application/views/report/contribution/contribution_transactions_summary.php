<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$transaction = isset($transaction) ? $transaction : array();

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
                            MEMBER CBU TRANSACTIONS SUMMARY
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

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:900px;">
                <thead>
                    <tr>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:45px;">S/No</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px; width:110px;">Member ID</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Member Name</th>
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
                        <td colspan="7" style="text-align:center; padding:20px; color:#999; font-style:italic;">
                            No CBU transactions found for
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
                        $name = trim(preg_replace('/\s+/', ' ', !empty($value->member_name) ? $value->member_name : ''));
                        $balance_open = $this->report_model->contribution_transactions_summary_previous($reportinfo->fromdate, $value->member_id);
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
                        ?>
                        <tr>
                            <td style="text-align:center; padding:3px 4px;"><?php echo $i++; ?>.</td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($value->member_id); ?></td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars($name); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo $format_cr_dr($balance_tmp); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo ($row_debit > 0 ? number_format($row_debit, 2) : ''); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo ($row_credit > 0 ? number_format($row_credit, 2) : ''); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo $format_cr_dr($close_balance); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="3"></td>
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
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report_contribution/contribution_transaction_summary_print/' . $link_cat . '/' . $id)); ?>"
                    data-pdf-name="Member_CBU_Transactions_Summary.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintCbuTransSummary">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_contribution/contribution_transaction_summary_export/' . $link_cat . '/' . $id); ?>" class="btn btn-success">
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
$cbu_sum_pdf_url = site_url(current_lang() . '/report_contribution/contribution_transaction_summary_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#cbuSumPdfOverlay {
    display: none; position: fixed; left: 0; top: 0; right: 0; bottom: 0;
    z-index: 99999; background: rgba(0,0,0,0.55);
}
#cbuSumPdfOverlay.is-open { display: block; }
#cbuSumPdfOverlay .cbusm-dialog {
    position: absolute; left: 5%; top: 4%; width: 90%; height: 92%;
    background: #fff; border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; overflow: hidden;
}
#cbuSumPdfOverlay .cbusm-header {
    padding: 12px 15px; border-bottom: 1px solid #e7eaec; background: #f8f8f8; flex: 0 0 auto;
}
#cbuSumPdfOverlay .cbusm-header h4 { margin: 0; display: inline-block; font-size: 16px; font-weight: 600; }
#cbuSumPdfOverlay .cbusm-header .cbusm-actions { float: right; }
#cbuSumPdfOverlay .cbusm-body { flex: 1 1 auto; min-height: 0; background: #f3f3f4; }
#cbuSumPdfOverlay .cbusm-body iframe { width: 100%; height: 100%; border: 0; display: block; }
</style>
<div id="cbuSumPdfOverlay" aria-hidden="true">
    <div class="cbusm-dialog" role="dialog">
        <div class="cbusm-header">
            <h4>Member CBU Transactions Summary - PDF</h4>
            <div class="cbusm-actions">
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeCbuSumPdf();">Close</button>
            </div>
        </div>
        <div class="cbusm-body">
            <iframe id="cbuSumPdfFrame" src="about:blank" title="Member CBU Transactions Summary PDF"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var pdfUrl = <?php echo json_encode($cbu_sum_pdf_url); ?>;
    window.openCbuSumPdf = function () {
        var overlay = document.getElementById('cbuSumPdfOverlay');
        var frame = document.getElementById('cbuSumPdfFrame');
        if (!overlay || !frame) { window.open(pdfUrl, '_blank'); return; }
        if (overlay.parentNode !== document.body) document.body.appendChild(overlay);
        frame.src = viewerBase + encodeURIComponent(pdfUrl + (pdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now());
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    window.closeCbuSumPdf = function () {
        var overlay = document.getElementById('cbuSumPdfOverlay');
        var frame = document.getElementById('cbuSumPdfFrame');
        if (frame) frame.src = 'about:blank';
        if (overlay) { overlay.className = ''; overlay.setAttribute('aria-hidden', 'true'); }
        document.body.style.overflow = '';
    };
    function bind() {
        var btn = document.getElementById('btnPrintCbuTransSummary');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.openCbuSumPdf();
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) window.closeCbuSumPdf();
        });
        document.addEventListener('click', function (e) {
            var po = document.getElementById('cbuSumPdfOverlay');
            if (po && po.className.indexOf('is-open') !== -1 && e.target === po) window.closeCbuSumPdf();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
    else bind();
})();
</script>
