<?php
$loaninfo = isset($loaninfo) ? $loaninfo : null;
$trans = isset($trans) ? $trans : array();

if (!$loaninfo) {
    echo '<div class="hint-empty"><i class="fa fa-exclamation-circle fa-3x"></i><p>Loan information not found.</p></div>';
    return;
}

$memberinfo = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
if (!$memberinfo) {
    echo '<div class="hint-empty"><i class="fa fa-exclamation-circle fa-3x"></i><p>Member information not found.</p></div>';
    return;
}

$product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
$interval = $this->setting_model->intervalinfo($loaninfo->interval)->row();
$product_name = ($product && isset($product->name)) ? $product->name : '';
$interval_name = ($interval && isset($interval->name)) ? $interval->name : '';

$full_name = trim($memberinfo->firstname . ' ' . $memberinfo->middlename . ' ' . $memberinfo->lastname);
$photo_file = isset($memberinfo->photo) ? $memberinfo->photo : '';
$gender_raw = isset($memberinfo->gender) ? $memberinfo->gender : '';
$fallback_url = function_exists('member_avatar_url') ? member_avatar_url('', $gender_raw) : base_url('uploads/memberphoto/');
$photo_url = function_exists('member_avatar_url')
    ? member_avatar_url($photo_file, $gender_raw)
    : base_url('uploads/memberphoto/' . $photo_file);

$print_url = site_url(current_lang() . '/report_loan/loan_statement_print/?loan_id=' . rawurlencode($loaninfo->LID));

$safe = function ($v) {
    if ($v === null || $v === '') {
        return '&mdash;';
    }
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};
?>

<style type="text/css">
.ls-content .ls-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 16px;
    padding: 22px 24px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1ab394 0%, #147a6a 55%, #1f3348 100%);
    color: #fff;
    box-shadow: 0 4px 16px rgba(26, 179, 148, 0.22);
}
.ls-content .ls-hero-main {
    display: flex;
    align-items: center;
    gap: 18px;
    min-width: 0;
    flex: 1 1 auto;
}
.ls-content .ls-hero-photo {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    padding: 3px;
    background: rgba(255,255,255,0.95);
    border: 2px solid rgba(255,255,255,0.55);
    box-shadow: 0 4px 14px rgba(0,0,0,0.18);
    overflow: hidden;
    flex-shrink: 0;
}
.ls-content .ls-hero-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 50%;
    background: #e8f8f5;
}
.ls-content .ls-hero-text { min-width: 0; }
.ls-content .ls-hero-text h3 {
    margin: 0 0 6px;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    line-height: 1.25;
    word-wrap: break-word;
}
.ls-content .ls-hero-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.ls-content .ls-chip {
    display: inline-block;
    padding: 4px 11px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .02em;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.28);
    color: #fff;
}
.ls-content .ls-chip.soft {
    background: rgba(255,255,255,0.12);
    font-weight: 600;
}
.ls-content .ls-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    flex-shrink: 0;
}
.ls-content .ls-hero-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 7px 14px;
}
.ls-content .ls-hero-actions .btn-print {
    background: #fff;
    border-color: #fff;
    color: #147a6a;
}
.ls-content .ls-hero-actions .btn-print:hover,
.ls-content .ls-hero-actions .btn-print:focus {
    background: #f4fffc;
    border-color: #f4fffc;
    color: #0e7c69;
}

.ls-content .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 28px;
}
.ls-content .info-item {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 0;
    border-bottom: 1px solid #eef1f2;
}
.ls-content .info-item .field-label {
    flex: 0 1 48%;
    margin: 0;
    font-size: 12px;
    font-weight: 600;
    color: #888;
    line-height: 1.4;
}
.ls-content .info-item .value {
    flex: 1 1 auto;
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: #2f4050;
    text-align: right;
    word-break: break-word;
    line-height: 1.4;
}
.ls-content .amount-cell {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-weight: 600;
    white-space: nowrap;
}
.ls-content .result-meta {
    font-size: 12px;
    color: #888;
}

@media (max-width: 991px) {
    .ls-content .ls-hero {
        flex-direction: column;
        align-items: flex-start;
    }
}
@media (max-width: 767px) {
    .ls-content .ls-hero-main { flex-direction: column; align-items: flex-start; }
    .ls-content .info-grid { grid-template-columns: 1fr; }
}
</style>

<div class="ls-content">
    <div class="ls-hero">
        <div class="ls-hero-main">
            <div class="ls-hero-photo">
                <img
                    src="<?php echo htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>"
                    onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($fallback_url, ENT_QUOTES, 'UTF-8'); ?>';"
                />
            </div>
            <div class="ls-hero-text">
                <h3><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></h3>
                <div class="ls-hero-chips">
                    <span class="ls-chip"><?php echo htmlspecialchars($loaninfo->LID, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="ls-chip soft"><?php echo htmlspecialchars($memberinfo->member_id, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="ls-chip soft">PID <?php echo htmlspecialchars($memberinfo->PID, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($product_name !== '') { ?>
                        <span class="ls-chip soft"><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="ls-hero-actions">
            <a href="<?php echo $print_url; ?>" class="btn btn-print" target="_blank">
                <i class="fa fa-print"></i> Print
            </a>
        </div>
    </div>

    <div class="row" style="margin-left:-8px;margin-right:-8px;">
        <div class="col-md-6" style="padding-left:8px;padding-right:8px;">
            <div class="cbu-panel">
                <div class="panel-head">
                    <div class="head-left">
                        <i class="fa fa-user icon-badge"></i>
                        <h4><?php echo lang('member_basic_info'); ?></h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_firstname'); ?></span>
                            <div class="value"><?php echo $safe($memberinfo->firstname); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_middlename'); ?></span>
                            <div class="value"><?php echo $safe($memberinfo->middlename); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_lastname'); ?></span>
                            <div class="value"><?php echo $safe($memberinfo->lastname); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_gender'); ?></span>
                            <div class="value"><?php echo $safe($memberinfo->gender); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_dob'); ?></span>
                            <div class="value"><?php echo $safe(format_date($memberinfo->dob, false)); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_join_date'); ?></span>
                            <div class="value"><?php echo $safe(format_date($memberinfo->joiningdate, false)); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_pid'); ?></span>
                            <div class="value"><?php echo $safe($memberinfo->PID); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('member_member_id'); ?></span>
                            <div class="value"><?php echo $safe($memberinfo->member_id); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6" style="padding-left:8px;padding-right:8px;">
            <div class="cbu-panel">
                <div class="panel-head">
                    <div class="head-left">
                        <i class="fa fa-money icon-badge"></i>
                        <h4><?php echo lang('loan_info'); ?></h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('loan_LID'); ?></span>
                            <div class="value"><?php echo $safe($loaninfo->LID); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('loan_product'); ?></span>
                            <div class="value"><?php echo $safe($product_name); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('loanproduct_interest'); ?></span>
                            <div class="value"><?php echo $safe($loaninfo->rate); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('loan_installment'); ?></span>
                            <div class="value"><?php echo $safe($loaninfo->number_istallment . ($interval_name !== '' ? ' ' . $interval_name : '')); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('loan_paysource'); ?></span>
                            <div class="value"><?php echo $safe($loaninfo->pay_source); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('loan_applicationdate'); ?></span>
                            <div class="value"><?php echo $safe(format_date($loaninfo->applicationdate, false)); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('loan_installment_amount'); ?></span>
                            <div class="value"><?php echo number_format((float) $loaninfo->installment_amount, 2); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('loan_total_interest'); ?></span>
                            <div class="value"><?php echo number_format((float) $loaninfo->total_interest_amount, 2); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="field-label"><?php echo lang('loan_applied_amount'); ?></span>
                            <div class="value"><?php echo number_format((float) $loaninfo->basic_amount, 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <div class="head-left">
                <i class="fa fa-list-alt icon-badge"></i>
                <h4><?php echo lang('loan_statement'); ?></h4>
            </div>
            <div class="result-meta">
                Showing <strong><?php echo number_format(count($trans)); ?></strong> installment<?php echo count($trans) === 1 ? '' : 's'; ?>
            </div>
        </div>
        <div class="table-responsive" style="padding: 0 4px 8px;">
            <table class="table table-striped member-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Due Date</th>
                        <th>Paid Date</th>
                        <th style="text-align:right;">Installment Amount</th>
                        <th style="text-align:right;">Interest</th>
                        <th style="text-align:right;">Penalty</th>
                        <th style="text-align:right;">Principle</th>
                        <th style="text-align:right;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($trans)) {
                        foreach ($trans as $value) { ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($value->installment, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars(format_date($value->duedate, false), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(format_date($value->paydate, false), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format((float) $value->amount, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format((float) $value->interest, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format((float) $value->penalt, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format((float) $value->principle, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format((float) $value->balance, 2); ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fa fa-list-alt"></i>
                                    <?php echo lang('data_not_found'); ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
