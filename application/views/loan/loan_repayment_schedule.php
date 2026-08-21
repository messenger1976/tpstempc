<?php
$loaninfo = isset($loaninfo) ? $loaninfo : null;
$schedule = isset($schedule) ? $schedule : array();
$basicinfo = isset($basicinfo) ? $basicinfo : ($loaninfo ? $this->member_model->member_basic_info(null, $loaninfo->PID)->row() : null);
$contactinfo = isset($contactinfo) ? $contactinfo : ($loaninfo ? $this->member_model->member_contact($loaninfo->PID) : null);

$gender_options = lang('member_genderoption');
$gender_label = ($basicinfo && isset($gender_options[$basicinfo->gender])) ? $gender_options[$basicinfo->gender] : ($basicinfo ? $basicinfo->gender : '');
$full_name = $basicinfo ? trim($basicinfo->firstname . ' ' . $basicinfo->middlename . ' ' . $basicinfo->lastname) : '';
$photo_url = $basicinfo ? member_avatar_url(isset($basicinfo->photo) ? $basicinfo->photo : '', isset($basicinfo->gender) ? $basicinfo->gender : '') : '';
$status_code = ($basicinfo && isset($basicinfo->status)) ? (string) $basicinfo->status : '';
$status_label = ($status_code === '1') ? lang('member_active') : (($status_code === '0') ? lang('member_inactive') : (($status_code === '2') ? 'Deleted' : lang('member_inactive')));
$status_active = ($status_code === '1');
$address = '';
if ($contactinfo) {
    if (!empty($contactinfo->physicaladdress)) {
        $address = $contactinfo->physicaladdress;
    } else if (!empty($contactinfo->postaladdress)) {
        $address = $contactinfo->postaladdress;
    } else if (!empty($contactinfo->officeaddress)) {
        $address = $contactinfo->officeaddress;
    }
}
$position = ($contactinfo && !empty($contactinfo->occupation)) ? $contactinfo->occupation : '';
$salary_grade = ($contactinfo && isset($contactinfo->salary_grade)) ? $contactinfo->salary_grade : '';

$product = $loaninfo ? $this->setting_model->loanproduct($loaninfo->product_type)->row() : null;
$interval = ($loaninfo && !empty($loaninfo->interval)) ? $this->setting_model->intervalinfo($loaninfo->interval)->row() : null;
$contribution = $loaninfo ? $this->contribution_model->contribution_balance($loaninfo->PID, $loaninfo->member_id) : null;
$share_data = $loaninfo ? $this->share_model->share_member_info($loaninfo->PID, $loaninfo->member_id) : null;
$saving = $loaninfo ? $this->finance_model->saving_account_balance_PID($loaninfo->PID, $loaninfo->member_id) : null;

$print_pdf_url = site_url(current_lang() . '/loan/print_repayment_schedule/' . $loanid);
$disburse_pdf_url = site_url(current_lang() . '/loan/print_loan_disbursement/' . $loanid);
$pdf_viewer_base = base_url() . 'assets/pdf_viewer_embed.html?file=';
$is_bb_journal = false;
if ($loaninfo) {
    $latest_disburse = $this->loan_model->loan_disburse_history($loaninfo->LID)->row();
    $is_bb_journal = $this->loan_model->is_beginning_balance_activated_loan($loaninfo, $latest_disburse);
}
$print_disburse_label = $is_bb_journal ? lang('loan_print_beginning_balance_journal') : lang('loan_print_disbursement');
$print_disburse_title = $is_bb_journal ? lang('loan_beginning_balance_journal') : lang('loan_disbursement_voucher');
?>

<style type="text/css">
.loan-schedule-page { margin-top: 4px; }
.loan-schedule-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.loan-schedule-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.loan-schedule-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.loan-schedule-page .cbu-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.loan-schedule-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.loan-schedule-page .cbu-panel .panel-body { padding: 18px 20px; }
.loan-schedule-page .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 18px;
}
@media (max-width: 767px) {
    .loan-schedule-page .info-grid { grid-template-columns: 1fr; }
}
.loan-schedule-page .info-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-schedule-page .info-row:last-child { border-bottom: 0; }
.loan-schedule-page .info-row .lbl { color: #999; font-weight: 500; }
.loan-schedule-page .info-row .val {
    color: #2f4050;
    font-weight: 700;
    text-align: right;
    word-break: break-word;
}
.loan-schedule-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    margin-bottom: 20px;
}
.loan-schedule-page .cbu-member-card { text-align: center; }
.loan-schedule-page .cbu-member-photo {
    width: 120px;
    height: 120px;
    margin: 0 auto 14px;
    border-radius: 50%;
    padding: 4px;
    background: #fff;
    border: 3px solid #1ab394;
    box-shadow: 0 4px 14px rgba(26,179,148,0.18);
    overflow: hidden;
}
.loan-schedule-page .cbu-member-photo img {
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    object-fit: cover;
    object-position: center top;
    border-radius: 50%;
    display: block;
}
.loan-schedule-page .cbu-member-photo.avatar-fallback img {
    object-fit: contain;
    object-position: center center;
    background: #e8f8f5;
}
.loan-schedule-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.loan-schedule-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.loan-schedule-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.loan-schedule-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.loan-schedule-page .cbu-member-details {
    text-align: left;
    margin: 0;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.loan-schedule-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-schedule-page .cbu-member-details li:last-child { border-bottom: 0; }
.loan-schedule-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.loan-schedule-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.loan-schedule-page .balance-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 14px;
}
@media (max-width: 767px) {
    .loan-schedule-page .balance-strip { grid-template-columns: 1fr; }
}
.loan-schedule-page .balance-item {
    background: #fafbfc;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 10px 12px;
    text-align: center;
}
.loan-schedule-page .balance-item .lbl {
    display: block;
    color: #999;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 4px;
}
.loan-schedule-page .balance-item .val {
    display: block;
    color: #2f4050;
    font-size: 14px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.loan-schedule-page .schedule-table {
    margin: 0;
    background: #fff;
}
.loan-schedule-page .schedule-table > thead > tr > th {
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    color: #676a6c;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.loan-schedule-page .schedule-table > tbody > tr > td {
    vertical-align: middle;
    font-size: 13px;
}
.loan-schedule-page .schedule-table .amount-cell {
    text-align: right;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #2f4050;
}
.loan-schedule-page .schedule-table .opening-row td {
    background: #f8fafb;
    color: #888;
    font-weight: 600;
}
.loan-schedule-page .schedule-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    padding-top: 4px;
}
.loan-schedule-page .schedule-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 16px;
}
.loan-schedule-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.loan-schedule-page .empty-note {
    color: #999;
    font-size: 13px;
    padding: 12px 0;
    text-align: center;
}

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

<div class="col-lg-12 loan-schedule-page">
    <div class="row">
        <div class="col-lg-4">
            <div class="cbu-preview" id="member_info">
                <?php if ($basicinfo) { ?>
                    <div class="cbu-member-card">
                        <?php
                        $has_real_photo = !empty($basicinfo->photo) && $basicinfo->photo !== '0' && strtolower($basicinfo->photo) !== 'avatar.gif';
                        ?>
                        <div class="cbu-member-photo<?php echo $has_real_photo ? '' : ' avatar-fallback'; ?>">
                            <img src="<?php echo htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars(member_avatar_url('', isset($basicinfo->gender) ? $basicinfo->gender : ''), ENT_QUOTES, 'UTF-8'); ?>';"/>
                        </div>
                        <h3 class="cbu-member-name"><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="cbu-member-badges">
                            <?php if (!empty($basicinfo->member_id)) { ?>
                                <span class="cbu-badge"><?php echo htmlspecialchars($basicinfo->member_id, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php } ?>
                            <span class="cbu-badge"><?php echo lang('member_pid'); ?> <?php echo htmlspecialchars($basicinfo->PID, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if ($gender_label !== '') { ?>
                                <span class="cbu-badge"><?php echo htmlspecialchars($gender_label, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php } ?>
                            <span class="cbu-badge<?php echo $status_active ? '' : ' inactive'; ?>"><?php echo lang('member_status'); ?>: <?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <ul class="cbu-member-details">
                            <li><span class="lbl"><?php echo lang('member_contact_address'); ?></span><span class="val"><?php echo $address !== '' ? htmlspecialchars($address, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_dob'); ?></span><span class="val"><?php echo !empty($basicinfo->dob) ? htmlspecialchars(format_date($basicinfo->dob, FALSE), ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_join_date'); ?></span><span class="val"><?php echo !empty($basicinfo->joiningdate) ? htmlspecialchars(format_date($basicinfo->joiningdate, FALSE), ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_phone1'); ?></span><span class="val"><?php echo ($contactinfo && !empty($contactinfo->phone1)) ? htmlspecialchars($contactinfo->phone1, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_position'); ?></span><span class="val"><?php echo $position !== '' ? htmlspecialchars($position, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_email'); ?></span><span class="val"><?php echo ($contactinfo && !empty($contactinfo->email)) ? htmlspecialchars($contactinfo->email, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                            <li><span class="lbl"><?php echo lang('member_contact_salary_grade'); ?></span><span class="val"><?php echo $salary_grade !== '' ? htmlspecialchars($salary_grade, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></li>
                        </ul>
                        <div class="balance-strip">
                            <div class="balance-item">
                                <span class="lbl"><?php echo lang('contribution_balance'); ?></span>
                                <span class="val"><?php echo $contribution ? number_format($contribution->balance, 2) : '0.00'; ?></span>
                            </div>
                            <div class="balance-item">
                                <span class="lbl"><?php echo lang('share_balance'); ?></span>
                                <span class="val"><?php echo $share_data ? number_format(($share_data->amount + $share_data->remainbalance), 2) : '0.00'; ?></span>
                            </div>
                            <div class="balance-item">
                                <span class="lbl"><?php echo lang('saving_balance'); ?></span>
                                <span class="val"><?php echo $saving ? number_format($saving->balance, 2) : '0.00'; ?></span>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="empty-note"><?php echo lang('no_records_found'); ?></div>
                <?php } ?>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="cbu-panel">
                <div class="panel-head">
                    <div class="head-left">
                        <i class="fa fa-file-text-o"></i>
                        <h4><?php echo lang('loan_info'); ?></h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="info-grid">
                        <div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_LID'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->LID, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_product'); ?></span><span class="val"><?php echo $product ? htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loanproduct_interest'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->rate, ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_installment'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->number_istallment . ($interval ? ' ' . $interval->name : ''), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_paysource'); ?></span><span class="val"><?php echo htmlspecialchars($loaninfo->pay_source, ENT_QUOTES, 'UTF-8'); ?></span></div>
                        </div>
                        <div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_applicationdate'); ?></span><span class="val"><?php echo htmlspecialchars(format_date($loaninfo->applicationdate, FALSE), ENT_QUOTES, 'UTF-8'); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_applied_amount'); ?></span><span class="val"><?php echo number_format($loaninfo->basic_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_installment_amount'); ?></span><span class="val"><?php echo number_format($loaninfo->installment_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_total_interest'); ?></span><span class="val"><?php echo number_format($loaninfo->total_interest_amount, 2); ?></span></div>
                            <div class="info-row"><span class="lbl"><?php echo lang('loan_total'); ?></span><span class="val"><?php echo number_format($loaninfo->total_loan, 2); ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-calendar"></i>
                <h4><?php echo lang('loan_view_repayment_schedule'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped schedule-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('sno'); ?></th>
                            <th style="text-align:center;"><?php echo lang('due_date'); ?></th>
                            <th style="text-align:right;"><?php echo lang('amount'); ?></th>
                            <th style="text-align:right;">Interest</th>
                            <th style="text-align:right;">Principle</th>
                            <th style="text-align:right;"><?php echo lang('balance'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="opening-row">
                            <td></td>
                            <td style="text-align:center;">&mdash;</td>
                            <td class="amount-cell">&mdash;</td>
                            <td class="amount-cell">&mdash;</td>
                            <td class="amount-cell">&mdash;</td>
                            <td class="amount-cell"><?php echo number_format($loaninfo->basic_amount, 2); ?></td>
                        </tr>
                        <?php if (count($schedule) > 0) {
                            $s = 1;
                            foreach ($schedule as $value) { ?>
                                <tr>
                                    <td><?php echo $s++; ?></td>
                                    <td style="text-align:center;"><?php echo date('d M, Y', strtotime($value->repaydate)); ?></td>
                                    <td class="amount-cell"><?php echo number_format($value->repayamount, 2); ?></td>
                                    <td class="amount-cell"><?php echo number_format($value->interest, 2); ?></td>
                                    <td class="amount-cell"><?php echo number_format($value->principle, 2); ?></td>
                                    <td class="amount-cell"><?php echo number_format($value->balance, 2); ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="6"><div class="empty-note"><?php echo lang('no_records_found'); ?></div></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="schedule-actions">
                <button type="button" class="btn btn-primary" id="btnPrintRepaymentSchedule">
                    <i class="fa fa-print"></i> <?php echo lang('print'); ?>
                </button>
                <button type="button" class="btn btn-info" id="btnPrintDisbursement">
                    <i class="fa fa-file-text-o"></i> <?php echo $print_disburse_label; ?>
                </button>
                <a class="btn btn-success" href="<?php echo site_url(current_lang() . '/loan/export_repayment_schedule/' . $loanid); ?>">
                    <i class="fa fa-file-excel-o"></i> <?php echo lang('export_to_excel'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

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
    var disburseTitle = <?php echo json_encode($print_disburse_title . ' - PDF'); ?>;
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
