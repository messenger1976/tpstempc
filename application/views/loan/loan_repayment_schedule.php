 <!-- basic information -->
    <?php $memberinfo = $this->member_model->member_basic_info(null, $loaninfo->PID)->row(); ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><?php echo lang('member_basic_info'); ?></h4>
        </div>
        <div class="panel-body">
            <table>
                <tr>
                    <td><img  style="width: 100px; height: 100px;" src="<?php echo base_url() ?>uploads/memberphoto/<?php echo $memberinfo->photo; ?>"/></td>
                    <td valign='top'><div style="padding-left: 30px;">
                            <strong><?php echo lang('member_firstname') ?> : </strong> <?php echo $memberinfo->firstname; ?><br/>
                            <strong><?php echo lang('member_middlename') ?> : </strong> <?php echo $memberinfo->middlename; ?><br/>
                            <strong><?php echo lang('member_lastname') ?> : </strong> <?php echo $memberinfo->lastname; ?><br/>
                            <strong><?php echo lang('member_gender') ?> : </strong> <?php echo $memberinfo->gender; ?><br/>
                            <strong><?php echo lang('member_dob') ?> : </strong> <?php echo format_date($memberinfo->dob, FALSE); ?><br/>
                        </div></td>
                    <td valign="top"><div style="padding-left: 100px;">
                            <strong><?php echo lang('member_pid') ?> : </strong> <?php echo $memberinfo->PID; ?><br/>
                            <strong><?php echo lang('member_member_id') ?> : </strong> <?php echo $memberinfo->member_id; ?><br/>
                            <strong><?php echo lang('member_join_date') ?> : </strong> <?php echo format_date($memberinfo->joiningdate, FALSE); ?><br/>
                        </div></td>

                 

                </tr>
            </table>
        </div>
    </div>



<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?php echo lang('loan_info'); ?></h4>
    </div>
    <div class="panel-body">
        <table>
            <tr>
                <?php
                $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
                $interval = $this->setting_model->intervalinfo($loaninfo->interval)->row();
                ?>
                <td valign='top'><div style="padding-left: 30px;">
                        <strong><?php echo lang('loan_product') ?> : </strong> <?php echo $product->name; ?><br/>
                        <strong><?php echo lang('loanproduct_interest') ?> : </strong> <?php echo $loaninfo->rate; ?><br/>
                        <strong><?php echo lang('loan_installment') ?> : </strong> <?php echo $loaninfo->number_istallment . ' ' . $interval->name; ?><br/>
                        <strong><?php echo lang('loan_paysource') ?> : </strong> <?php echo $loaninfo->pay_source; ?><br/>

                    </div></td>
                <td valign="top"><div style="padding-left: 40px;">
                        <strong><?php echo lang('loan_applicationdate') ?> : </strong> <?php echo format_date($loaninfo->applicationdate, FALSE); ?><br/>
                        <strong><?php echo lang('loan_installment_amount') ?> : </strong> <?php echo number_format($loaninfo->installment_amount, 2); ?><br/>
                        <strong><?php echo lang('loan_total_interest') ?> : </strong> <?php echo number_format($loaninfo->total_interest_amount, 2); ?><br/>
                        <strong><?php echo lang('loan_applied_amount') ?> : </strong> <?php echo number_format($loaninfo->basic_amount, 2); ?><br/>

                    </div></td>
                <td valign="top"><div style="padding-left: 40px;">
                        <strong><?php echo lang('loan_LID') ?> : </strong> <?php echo $loaninfo->LID; ?><br/>

                    </div></td>

            </tr>
        </table>
    </div>
</div>

<div class="table-responsive">


    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('sno'); ?></th>
                <th><?php echo lang('due_date'); ?></th>
                <th><?php echo lang('amount'); ?></th>
                <th><?php echo 'Interest'; ?></th>
                <th><?php echo 'Principle'; ?></th>
                <th><?php echo lang('balance'); ?></th>

            </tr>

        </thead>
        <tbody>
            <tr>
                <td></td>
                <td style="text-align: center;"></td>
                <td style="text-align: right;"></td>
                <td style="text-align: right;"></td>
                <td style="text-align: right;"></td>
                <td style="text-align: right;"><?php echo number_format($loaninfo->basic_amount, 2); ?></td>
            </tr>
            <?php
            if (count($schedule) > 0) {
                $s = 1;
                foreach ($schedule as $key => $value) {
                    ?>
                    <tr>
                        <td><?php echo $s++; ?></td>
                        <td style="text-align: center;"><?php
                            echo date('d M, Y', strtotime($value->repaydate));
                            ?>
                        </td>
                        <td style="text-align: right;"><?php echo number_format($value->repayamount,2) ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->interest,2) ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->principle,2) ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->balance,2) ?></td>
                       
                    </tr>
                <?php }
                ?>

<?php } ?>
        </tbody>

    </table>

    <?php
    $print_pdf_url = site_url(current_lang() . '/loan/print_repayment_schedule/' . $loanid);
    $disburse_pdf_url = site_url(current_lang() . '/loan/print_loan_disbursement/' . $loanid);
    $pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
    ?>
    <div style="text-align: center;">
        <button type="button" class="btn btn-primary" id="btnPrintRepaymentSchedule">
            <?php echo lang('print'); ?>
        </button>
        <button type="button" class="btn btn-info" id="btnPrintDisbursement">
            <?php echo lang('loan_print_disbursement'); ?>
        </button>
        <a class="btn btn-success" href="<?php echo site_url(current_lang() . '/loan/export_repayment_schedule/' . $loanid); ?>"><?php echo lang('export_to_excel'); ?></a>
    </div>
</div>

<style type="text/css">
#repaymentPdfOverlay {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.55);
}
#repaymentPdfOverlay.is-open { display: block; }
#repaymentPdfOverlay .rps-dialog {
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
#repaymentPdfOverlay .rps-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e7eaec;
    background: #f8f8f8;
    flex: 0 0 auto;
}
#repaymentPdfOverlay .rps-header h4 {
    margin: 0;
    display: inline-block;
    font-size: 16px;
    font-weight: 600;
}
#repaymentPdfOverlay .rps-header .rps-actions {
    float: right;
}
#repaymentPdfOverlay .rps-body {
    flex: 1 1 auto;
    min-height: 0;
    background: #f3f3f4;
}
#repaymentPdfOverlay .rps-body iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
#loanPdfPrintProgress {
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
#loanPdfPrintProgress.is-visible {
    display: flex;
}
#loanPdfPrintProgress .spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e7eaec;
    border-top-color: #1ab394;
    border-radius: 50%;
    -webkit-animation: loan-pdf-spin 0.8s linear infinite;
    animation: loan-pdf-spin 0.8s linear infinite;
    margin-bottom: 12px;
}
#loanPdfPrintProgress .msg {
    color: #676a6c;
    font-size: 14px;
}
@-webkit-keyframes loan-pdf-spin {
    to { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
}
@keyframes loan-pdf-spin {
    to { transform: rotate(360deg); }
}
</style>

<div id="repaymentPdfOverlay" aria-hidden="true">
    <div class="rps-dialog" role="dialog" aria-labelledby="repaymentPdfTitle">
        <div class="rps-header">
            <h4 id="repaymentPdfTitle"><?php echo lang('loan_view_repayment_schedule'); ?> - PDF</h4>
            <div class="rps-actions">
                <button type="button" id="repaymentPdfOpenTab" class="btn btn-xs btn-primary" onclick="window.printLoanPdfModal();" title="Print">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-xs btn-white" onclick="window.closeRepaymentSchedulePdf();">Close</button>
            </div>
        </div>
        <div class="rps-body" style="position:relative;">
            <div id="loanPdfPrintProgress" aria-live="polite" aria-busy="false">
                <div class="spinner"></div>
                <div class="msg">Preparing print...</div>
            </div>
            <iframe id="repaymentPdfFrame" src="about:blank" title="PDF Viewer"></iframe>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var viewerBase = <?php echo json_encode($pdf_viewer_base); ?>;
    var schedulePdfUrl = <?php echo json_encode($print_pdf_url); ?>;
    var disbursePdfUrl = <?php echo json_encode($disburse_pdf_url); ?>;
    var scheduleTitle = <?php echo json_encode(lang('loan_view_repayment_schedule') . ' - PDF'); ?>;
    var disburseTitle = <?php echo json_encode(lang('loan_disbursement_voucher') . ' - PDF'); ?>;
    var currentPdfUrl = schedulePdfUrl;
    var printHideTimer = null;

    function getOverlay() {
        return document.getElementById('repaymentPdfOverlay');
    }

    function getFrame() {
        return document.getElementById('repaymentPdfFrame');
    }

    function getPrintBtn() {
        return document.getElementById('repaymentPdfOpenTab');
    }

    window.showLoanPdfPrintProgress = function () {
        var el = document.getElementById('loanPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = 'is-visible';
            el.setAttribute('aria-busy', 'true');
        }
        if (btn) {
            btn.disabled = true;
        }
    };

    window.hideLoanPdfPrintProgress = function () {
        if (printHideTimer) {
            clearTimeout(printHideTimer);
            printHideTimer = null;
        }
        var el = document.getElementById('loanPdfPrintProgress');
        var btn = getPrintBtn();
        if (el) {
            el.className = '';
            el.setAttribute('aria-busy', 'false');
        }
        if (btn) {
            btn.disabled = false;
        }
    };

    window.openLoanPdfModal = function (pdfUrl, title) {
        var overlay = getOverlay();
        var frame = getFrame();
        var titleEl = document.getElementById('repaymentPdfTitle');
        if (!overlay || !frame) {
            window.open(pdfUrl, '_blank');
            return;
        }
        if (overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }
        currentPdfUrl = pdfUrl;
        window.hideLoanPdfPrintProgress();
        if (titleEl && title) {
            titleEl.textContent = title;
        }
        frame.src = viewerBase + encodeURIComponent(pdfUrl);
        overlay.className = 'is-open';
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.printLoanPdfModal = function () {
        window.showLoanPdfPrintProgress();

        var frame = getFrame();
        if (frame && frame.contentWindow && typeof frame.contentWindow.printPdfFile === 'function') {
            try {
                frame.contentWindow.printPdfFile();
                return;
            } catch (e) {}
        }

        // Fallback: hidden iframe print from parent (same approach)
        if (!currentPdfUrl) {
            window.hideLoanPdfPrintProgress();
            return;
        }

        var existing = document.getElementById('loan-pdf-print-frame');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }

        var iframe = document.createElement('iframe');
        iframe.id = 'loan-pdf-print-frame';
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:800px;height:600px;border:0;opacity:0;pointer-events:none;z-index:-1;';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.src = currentPdfUrl;
        document.body.appendChild(iframe);

        var printShown = false;
        function finishPrintPrep() {
            if (printShown) return;
            printShown = true;
            window.hideLoanPdfPrintProgress();
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

    window.openRepaymentSchedulePdf = function () {
        window.openLoanPdfModal(schedulePdfUrl, scheduleTitle);
    };

    window.openDisbursementPdf = function () {
        window.openLoanPdfModal(disbursePdfUrl, disburseTitle);
    };

    window.closeRepaymentSchedulePdf = function () {
        window.hideLoanPdfPrintProgress();
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
            window.closeRepaymentSchedulePdf();
        }
    });

    document.addEventListener('click', function (e) {
        var overlay = getOverlay();
        if (!overlay || overlay.className.indexOf('is-open') === -1) {
            return;
        }
        if (e.target === overlay) {
            window.closeRepaymentSchedulePdf();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var btnSchedule = document.getElementById('btnPrintRepaymentSchedule');
        if (btnSchedule) {
            btnSchedule.addEventListener('click', function (e) {
                e.preventDefault();
                window.openRepaymentSchedulePdf();
            });
        }
        var btnDisburse = document.getElementById('btnPrintDisbursement');
        if (btnDisburse) {
            btnDisburse.addEventListener('click', function (e) {
                e.preventDefault();
                window.openDisbursementPdf();
            });
        }
    });
})();
</script>