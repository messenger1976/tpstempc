<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$member_id = !empty($reportinfo->description) ? $reportinfo->description : '';
$member_name = $member_id !== '' ? $this->member_model->member_name($member_id) : '';
$member_name = trim(preg_replace('/\s+/', ' ', $member_name));
$cbu_balance = 0;
if ($member_id !== '') {
    $bal_row = $this->db->query(
        "SELECT IFNULL(mc.balance, 0) AS balance
         FROM members m
         LEFT JOIN members_contribution mc ON mc.PID = m.PID
         WHERE m.member_id = ? AND m.PIN = ?
         LIMIT 1",
        array($member_id, current_user()->PIN)
    )->row();
    if ($bal_row) {
        $cbu_balance = floatval($bal_row->balance);
    }
}
$transaction = isset($transaction) ? $transaction : array();
?>
<div class="row">
    <div class="col-lg-12">
        <div style="padding: 20px 8px; margin: auto; max-width: 1100px; overflow-x: auto;">
            <table style="width:100%; margin-bottom: 8px;">
                <tr>
                    <td style="width:70px; vertical-align:top;">
                        <?php if (!empty($company->logo)) { ?>
                            <img src="<?php echo base_url() . 'logo/' . $company->logo; ?>" style="height:56px;" alt="logo"/>
                        <?php } ?>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <div style="font-weight:bold; font-size:14px; text-transform:uppercase;">
                            <?php echo htmlspecialchars($company->name); ?>
                        </div>
                        <div style="font-size:12px;">
                            <?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?>
                        </div>
                        <div style="font-weight:bold; font-size:15px; margin-top:6px;">
                            MEMBER CBU STATEMENT
                        </div>
                        <div style="font-size:12px; margin-top:3px;">
                            For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?>
                        </div>
                    </td>
                    <td style="width:90px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:12px;">
                        LENDING
                    </td>
                </tr>
            </table>

            <table style="width:100%; margin-bottom:12px; font-size:12px; border-collapse:collapse;">
                <tr>
                    <td style="padding:3px 0; width:50%;"><strong>Member ID:</strong> <?php echo htmlspecialchars($member_id); ?></td>
                    <td style="padding:3px 0;"><strong>Current CBU Balance:</strong> <?php echo number_format($cbu_balance, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:3px 0;"><strong>Member Name:</strong> <?php echo htmlspecialchars($member_name); ?></td>
                </tr>
            </table>

            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:800px;">
                <thead>
                    <tr>
                        <th style="text-align:center; border-bottom:1px solid #000; padding:4px; width:100px;">Date</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px;">Particulars</th>
                        <th style="text-align:left; border-bottom:1px solid #000; padding:4px; width:100px;">Method</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Debit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:110px;">Credit</th>
                        <th style="text-align:right; border-bottom:1px solid #000; padding:4px; width:120px;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $balance = 0;
                $credit = 0;
                $debit = 0;
                $previous_trans = $this->report_model->contribution_statement_previous($reportinfo->fromdate, $member_id);
                if ($previous_trans) {
                    $balance = floatval($previous_trans->credit) - floatval($previous_trans->debit);
                }
                ?>
                    <tr>
                        <td style="padding:3px 4px;"></td>
                        <td style="padding:3px 4px; font-weight:bold;">BROUGHT FORWARD BALANCE</td>
                        <td style="padding:3px 4px;"></td>
                        <td style="padding:3px 4px;"></td>
                        <td style="padding:3px 4px;"></td>
                        <td style="text-align:right; white-space:nowrap; padding:3px 4px; font-weight:bold;"><?php echo number_format($balance, 2); ?></td>
                    </tr>
                <?php
                if (empty($transaction)) {
                    ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:16px; color:#999; font-style:italic;">
                            No CBU transactions for this period.
                        </td>
                    </tr>
                    <?php
                } else {
                    foreach ($transaction as $value) {
                        $dt = explode(' ', $value->createdon);
                        $row_debit = floatval($value->debit);
                        $row_credit = floatval($value->credit);
                        if ($row_debit > 0) {
                            $balance -= $row_debit;
                            $debit += $row_debit;
                        } elseif ($row_credit > 0) {
                            $balance += $row_credit;
                            $credit += $row_credit;
                        }
                        $particulars = trim(isset($value->system_comment) ? $value->system_comment : '');
                        if (!empty($value->comment)) {
                            $particulars .= ($particulars !== '' ? ' — ' : '') . $value->comment;
                        }
                        ?>
                        <tr>
                            <td style="text-align:center; padding:3px 4px; white-space:nowrap;"><?php echo format_date($dt[0], false); ?></td>
                            <td style="padding:3px 4px;"><?php echo $particulars !== '' ? htmlspecialchars($particulars) : '&mdash;'; ?></td>
                            <td style="padding:3px 4px;"><?php echo htmlspecialchars(isset($value->paymethod) ? $value->paymethod : ''); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo ($row_debit > 0 ? number_format($row_debit, 2) : ''); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo ($row_credit > 0 ? number_format($row_credit, 2) : ''); ?></td>
                            <td style="text-align:right; white-space:nowrap; padding:3px 4px;"><?php echo number_format($balance, 2); ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                    <tr>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="3"></td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($debit, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <span style="float:left;">P</span><?php echo number_format($credit, 2); ?>
                        </td>
                        <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:4px; font-weight:bold;">
                            <?php echo number_format($balance, 2); ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="text-align:right; margin-top:10px; font-size:14px; font-weight:bold;">
                Ending Balance: <?php echo number_format($balance, 2); ?>
            </div>

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
                    data-pdf-url="<?php echo htmlspecialchars(site_url(current_lang() . '/report_contribution/contribution_statement_print/' . $link_cat . '/' . $id)); ?>"
                    data-pdf-name="Member_CBU_Statement.pdf">
                <i class="fa fa-download"></i> Download PDF
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button type="button" class="btn btn-primary" id="btnPrintCbuStatement">
                <i class="fa fa-print"></i> Print
            </button>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_contribution/create_contribution_report_title/' . $link_cat . '/' . $id); ?>" class="btn btn-primary">Edit</a>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a href="<?php echo site_url(current_lang() . '/report_contribution/contribution_report/' . $link_cat); ?>" class="btn btn-default">Back</a>
        </div>
    </div>
</div>

<?php
$cbu_stmt_pdf_url = site_url(current_lang() . '/report_contribution/contribution_statement_print/' . $link_cat . '/' . $id);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
?>
<style type="text/css">
#cbuStmtPdfOverlay {
    display: none; position: fixed; left: 0; top: 0; right: 0; bottom: 0;
    z-index: 99999; background: rgba(0,0,0,0.55);
}
#cbuStmtPdfOverlay.is-open { display: block; }
#cbuStmtPdfOverlay .cbus-dialog {
    position: absolute; left: 5%; top: 4%; width: 90%; height: 92%;
    background: #fff; border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; overflow: hidden;
}
#cbuStmtPdfOverlay .cbus-header {
    padding: 12px 15px; border-bottom: 1px solid #e7eaec; background: #f8f8f8; flex: 0 0 auto;
}
#cbuStmtPdfOverlay .cbus-header h4 { margin: 0; display: inline-block; font-size: 16px; font-weight: 600; }
#cbuStmtPdfOverlay .cbus-header .cbus-actions { float: right; }
#cbuStmtPdfOverlay .cbus-body { flex: 1 1 auto; min-height: 0; background: #f3f3f4; }
#cbuStmtPdfOverlay .cbus-body iframe { width: 100%; height: 100%; border: 0; display: block; }
</style>
<div id="cbuStmtPdfOverlay" aria-hidden="true">
    <div class="cbus-dialog" role="dialog">
        <div class="cbus-header">
            <h4>Member CBU Statement - PDF</h4>
            <div class="cbus-actions">
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeCbuStmtPdf();">Close</button>
            </div>
        </div>
        <div class="cbus-body">
            <iframe id="cbuStmtPdfFrame" src="about:blank" title="Member CBU Statement PDF"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var pdfUrl = <?php echo json_encode($cbu_stmt_pdf_url); ?>;
    window.openCbuStmtPdf = function () {
        var overlay = document.getElementById('cbuStmtPdfOverlay');
        var frame = document.getElementById('cbuStmtPdfFrame');
        if (!overlay || !frame) { window.open(pdfUrl, '_blank'); return; }
        if (overlay.parentNode !== document.body) document.body.appendChild(overlay);
        frame.src = viewerBase + encodeURIComponent(pdfUrl + (pdfUrl.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now());
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    window.closeCbuStmtPdf = function () {
        var overlay = document.getElementById('cbuStmtPdfOverlay');
        var frame = document.getElementById('cbuStmtPdfFrame');
        if (frame) frame.src = 'about:blank';
        if (overlay) { overlay.className = ''; overlay.setAttribute('aria-hidden', 'true'); }
        document.body.style.overflow = '';
    };
    function bind() {
        var btn = document.getElementById('btnPrintCbuStatement');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.openCbuStmtPdf();
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) window.closeCbuStmtPdf();
        });
        document.addEventListener('click', function (e) {
            var po = document.getElementById('cbuStmtPdfOverlay');
            if (po && po.className.indexOf('is-open') !== -1 && e.target === po) window.closeCbuStmtPdf();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
    else bind();
})();
</script>
