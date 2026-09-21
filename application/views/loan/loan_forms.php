<?php
$this->load->view('loan/topmenu');

$forms = isset($forms) ? $forms : array();
$loaninfo = isset($loaninfo) ? $loaninfo : (isset($forms['loan']) ? $forms['loan'] : null);
$member = isset($basicinfo) ? $basicinfo : (isset($forms['member']) ? $forms['member'] : null);
$co_makers = isset($co_makers) ? $co_makers : array();
$schedule = isset($schedule) ? $schedule : array();
$form_values = isset($form_values) ? $form_values : (isset($forms['values']) ? $forms['values'] : array());

$application = isset($form_values['application']) ? $form_values['application'] : array();
$comakers = isset($form_values['comakers']) ? $form_values['comakers'] : array();
$pledge = isset($form_values['pledge']) ? $form_values['pledge'] : array();
$disclosure = isset($form_values['disclosure']) ? $form_values['disclosure'] : array();

$member_name = $member ? trim($member->firstname . ' ' . $member->middlename . ' ' . $member->lastname) : '';
$member_name = preg_replace('/\s+/', ' ', $member_name);
$product_name = (isset($forms['product']) && $forms['product'] && !empty($forms['product']->name)) ? $forms['product']->name : '';

$esc = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$form_url = current_lang() . '/loan/loan_forms/' . $loanid;
$pdf_url = current_lang() . '/loan/print_loan_application_pdf/' . $loanid;
$pdf13_url = current_lang() . '/loan/print_comakers_promissory_pdf/' . $loanid;
$pdf_pledge_url = current_lang() . '/loan/print_pledge_authority_pdf/' . $loanid;
$html_url = current_lang() . '/loan/print_loan_application_html/' . $loanid;
$html13_url = current_lang() . '/loan/print_comakers_promissory_html/' . $loanid;
$html_pledge_url = current_lang() . '/loan/print_pledge_authority_html/' . $loanid;
$html_ds_url = current_lang() . '/loan/print_loan_disclosure_html/' . $loanid;
$pdf_ds_url = current_lang() . '/loan/print_loan_disclosure_pdf/' . $loanid;
?>

<style type="text/css">
.loan-app-page { margin-top: 4px; }
.loan-app-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.loan-app-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.loan-app-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.loan-app-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.loan-app-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    flex-wrap: wrap;
}
.loan-app-page .cbu-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
}
.loan-app-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
    flex: 1 1 auto;
}
.loan-app-page .cbu-panel .panel-body { padding: 22px 20px 12px; overflow: visible; }
.loan-app-page .form-horizontal .form-group { margin-bottom: 16px; }
.loan-app-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.loan-app-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.loan-app-page textarea.form-control { height: auto; min-height: 70px; }
.loan-app-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.loan-app-page .section-divider {
    margin: 8px 0 18px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e7eaec;
    color: #2f4050;
    font-size: 14px;
    font-weight: 700;
}
.loan-app-page .section-divider i { color: #1ab394; margin-right: 6px; }
.loan-app-page .cbu-actions { margin-top: 8px; padding-top: 8px; }
.loan-app-page .cbu-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 18px;
}
.loan-app-page .form-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 26px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
    font-size: 13px;
}
.loan-app-page .form-summary .item .lbl { display: block; color: #999; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }
.loan-app-page .form-summary .item .val { color: #2f4050; font-weight: 700; }
.loan-app-page .form-note {
    margin: 0 0 16px;
    padding: 10px 14px;
    background: #f7fcfa;
    border: 1px solid #c9ebe3;
    border-radius: 6px;
    color: #0e7c69;
    font-size: 12px;
    font-weight: 600;
}
.loan-app-page .form-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.loan-app-page .form-table th,
.loan-app-page .form-table td {
    border: 1px solid #e7eaec;
    padding: 5px 6px;
    vertical-align: middle;
}
.loan-app-page .form-table th {
    background: #fafbfc;
    color: #676a6c;
    font-weight: 700;
    text-align: center;
    font-size: 11px;
}
.loan-app-page .form-table td.row-label {
    font-weight: 700;
    color: #2f4050;
    background: #fafbfc;
    white-space: nowrap;
}
.loan-app-page .form-table input.form-control { height: 30px; font-size: 12px; padding: 4px 6px; }
.loan-app-page .check-grid { display: flex; flex-wrap: wrap; gap: 8px 22px; }
.loan-app-page .check-grid label { font-weight: 600; color: #676a6c; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
.loan-app-page .check-grid input[type="checkbox"] { width: 15px; height: 15px; }
.loan-app-page .co-maker-card {
    border: 1px solid #e7eaec;
    border-radius: 8px;
    padding: 16px 16px 4px;
    margin-bottom: 16px;
    background: #fdfefe;
}
.loan-app-page .co-maker-card .card-title {
    margin: 0 0 14px;
    font-size: 13px;
    font-weight: 700;
    color: #2f4050;
}
.loan-app-page .readonly-value {
    display: block;
    padding: 8px 0 6px;
    border-bottom: 1px dashed #e5e6e7;
    color: #2f4050;
    font-weight: 600;
    min-height: 34px;
    word-break: break-word;
}
.loan-app-page .readonly-value.empty { color: #bfc5c9; font-weight: 500; }
.loan-app-page .form-print-actions .btn { margin-right: 8px; margin-bottom: 8px; }
</style>

<div class="col-lg-12 loan-app-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="cbu-alert success displaymessage">' . $esc($message) . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="cbu-alert success displaymessage">' . $esc($this->session->flashdata('message')) . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="cbu-alert danger displaymessage">' . $esc($warning) . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="cbu-alert danger displaymessage">' . $esc($this->session->flashdata('warning')) . '</div>';
    }
    ?>

    <div class="cbu-panel">
        <div class="panel-head">
            <i class="fa fa-print"></i>
            <h4><?php echo lang('loan_forms'); ?></h4>
            <div class="form-print-actions">
                <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo site_url($html_url); ?>">
                    <i class="fa fa-eye"></i> <?php echo lang('loan_form_application'); ?>
                </a>
                <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo site_url($html13_url); ?>">
                    <i class="fa fa-eye"></i> <?php echo lang('loan_form_comakers'); ?>
                </a>
                <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo site_url($html_pledge_url); ?>">
                    <i class="fa fa-eye"></i> <?php echo lang('loan_form_pledge'); ?>
                </a>
                <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo site_url($html_ds_url); ?>">
                    <i class="fa fa-eye"></i> <?php echo lang('loan_form_disclosure'); ?>
                </a>
            </div>
        </div>
        <div class="form-summary">
            <div class="item"><span class="lbl"><?php echo lang('loan_LID'); ?></span><span class="val"><?php echo $esc($loaninfo ? $loaninfo->LID : ''); ?></span></div>
            <div class="item"><span class="lbl"><?php echo lang('member_member_id'); ?></span><span class="val"><?php echo $esc($loaninfo ? $loaninfo->member_id : ''); ?></span></div>
            <div class="item"><span class="lbl"><?php echo lang('member_name'); ?></span><span class="val"><?php echo $member_name !== '' ? $esc($member_name) : '&mdash;'; ?></span></div>
            <div class="item"><span class="lbl"><?php echo lang('loan_product'); ?></span><span class="val"><?php echo $product_name !== '' ? $esc($product_name) : '&mdash;'; ?></span></div>
            <div class="item"><span class="lbl"><?php echo lang('loan_applied_amount'); ?></span><span class="val"><?php echo $esc(number_format((float) ($loaninfo ? $loaninfo->basic_amount : 0), 2)); ?></span></div>
            <div class="item"><span class="lbl"><?php echo lang('loan_form_date_released'); ?></span><span class="val"><?php echo !empty($schedule['release_date']) ? $esc(format_date($schedule['release_date'], false)) : '&mdash;'; ?></span></div>
            <div class="item"><span class="lbl"><?php echo lang('loan_form_date_due'); ?></span><span class="val"><?php echo !empty($schedule['last_due']) ? $esc(format_date($schedule['last_due'], false)) : '&mdash;'; ?></span></div>
        </div>
        <div class="panel-body">
            <div class="form-note">
                <?php echo lang('loan_forms_intro'); ?>
                <?php echo lang('loan_form_preview_hint'); ?>
                <?php echo lang('loan_form_derived_hint'); ?>
            </div>
        </div>
    </div>

    <!-- =================== TPSTEMPC-12 Application for Loan =================== -->
    <?php echo form_open($form_url, 'class="form-horizontal"'); ?>
    <div class="cbu-panel">
        <div class="panel-head">
            <i class="fa fa-file-text-o"></i>
            <h4><?php echo lang('loan_form_application'); ?></h4>
            <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo site_url($html_url); ?>">
                <i class="fa fa-eye"></i> <?php echo lang('loan_form_preview'); ?>
            </a>
            <a class="btn btn-default btn-sm" target="_blank" href="<?php echo site_url($pdf_url); ?>">
                <i class="fa fa-file-pdf-o"></i> <?php echo lang('loan_form_print_pdf'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="section-divider"><i class="fa fa-check-square-o"></i><?php echo lang('loan_form_loan_type'); ?></div>
            <div class="form-group">
                <div class="col-lg-12 check-grid">
                    <?php
                    $loan_type_boxes = array(
                        'salary' => 'Salary Loan',
                        'supervise' => 'Supervise Loan',
                        'bonus' => 'Bonus Loan',
                        'cashadvance' => 'Cash Advance',
                        'emergency' => 'Emergency Loan',
                        'gadget' => 'Gadget Loan',
                        'car' => 'Car Loan',
                    );
                    foreach ($loan_type_boxes as $type_key => $type_label) {
                        $field_key = 'loan_type_' . $type_key;
                        ?>
                        <label>
                            <input type="checkbox" name="f[application][<?php echo $field_key; ?>]" value="1"
                                <?php echo (!empty($application[$field_key]) ? 'checked="checked"' : ''); ?> />
                            <?php echo $esc($type_label); ?>
                        </label>
                    <?php } ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_loan_type_others'); ?></label>
                <div class="col-lg-6">
                    <input type="text" name="f[application][loan_type_others]" class="form-control" value="<?php echo $esc(isset($application['loan_type_others']) ? $application['loan_type_others'] : ''); ?>"/>
                </div>
            </div>

            <div class="section-divider"><i class="fa fa-money"></i><?php echo lang('loan_info'); ?></div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_passbook_no'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[application][passbook_no]" class="form-control" value="<?php echo $esc(isset($application['passbook_no']) ? $application['passbook_no'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_date'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[application][date]" class="form-control" value="<?php echo $esc(isset($application['date']) ? $application['date'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_applied_amount'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[application][amount]" class="form-control" value="<?php echo $esc(isset($application['amount']) ? $application['amount'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_period'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[application][period]" class="form-control" value="<?php echo $esc(isset($application['period']) ? $application['period'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_monthly_installment'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[application][monthly_installment]" class="form-control" value="<?php echo $esc(isset($application['monthly_installment']) ? $application['monthly_installment'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_purpose'); ?></label>
                <div class="col-lg-9">
                    <textarea name="f[application][purpose]" class="form-control" rows="3"><?php echo $esc(isset($application['purpose']) ? $application['purpose'] : ''); ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_co_makers_security'); ?></label>
                <div class="col-lg-9">
                    <textarea name="f[application][co_makers_security]" class="form-control" rows="2"><?php echo $esc(isset($application['co_makers_security']) ? $application['co_makers_security'] : ''); ?></textarea>
                </div>
            </div>

            <div class="section-divider"><i class="fa fa-table"></i><?php echo lang('loan_form_person_maker'); ?> / <?php echo lang('loan_form_person_comaker'); ?></div>
            <div class="table-responsive">
                <table class="form-table">
                    <thead>
                        <tr>
                            <th style="width:130px;"><?php echo lang('member_name'); ?></th>
                            <th><?php echo lang('loan_form_cbu'); ?></th>
                            <th><?php echo lang('loan_form_savings'); ?></th>
                            <th><?php echo lang('loan_form_collateral'); ?></th>
                            <th><?php echo lang('loan_form_loan_balance'); ?></th>
                            <th><?php echo lang('loan_form_consumer_balance'); ?></th>
                            <th><?php echo lang('loan_form_migs'); ?></th>
                            <th><?php echo lang('loan_form_other_info'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="row-label"><?php echo lang('loan_form_person_maker'); ?></td>
                            <td><input type="text" name="f[application][maker_cbu]" class="form-control" value="<?php echo $esc(isset($application['maker_cbu']) ? $application['maker_cbu'] : ''); ?>"/></td>
                            <td><input type="text" name="f[application][maker_savings]" class="form-control" value="<?php echo $esc(isset($application['maker_savings']) ? $application['maker_savings'] : ''); ?>"/></td>
                            <td><input type="text" name="f[application][maker_collateral]" class="form-control" value="<?php echo $esc(isset($application['maker_collateral']) ? $application['maker_collateral'] : ''); ?>"/></td>
                            <td><input type="text" name="f[application][maker_loan_balance]" class="form-control" value="<?php echo $esc(isset($application['maker_loan_balance']) ? $application['maker_loan_balance'] : ''); ?>"/></td>
                            <td><input type="text" name="f[application][maker_consumer_balance]" class="form-control" value="<?php echo $esc(isset($application['maker_consumer_balance']) ? $application['maker_consumer_balance'] : ''); ?>"/></td>
                            <td><input type="text" name="f[application][maker_migs]" class="form-control" value="<?php echo $esc(isset($application['maker_migs']) ? $application['maker_migs'] : ''); ?>"/></td>
                            <td><input type="text" name="f[application][maker_other_info]" class="form-control" value="<?php echo $esc(isset($application['maker_other_info']) ? $application['maker_other_info'] : ''); ?>"/></td>
                        </tr>
                        <?php for ($slot = 1; $slot <= 2; $slot++) {
                            $person = isset($co_makers[$slot - 1]) ? $co_makers[$slot - 1] : null;
                            ?>
                            <tr>
                                <td class="row-label" title="<?php echo $esc($person ? $person['name'] : ''); ?>">
                                    <?php echo lang('loan_form_person_comaker'); ?> <?php echo $slot; ?>
                                </td>
                                <td><input type="text" name="f[application][comaker<?php echo $slot; ?>_cbu]" class="form-control" value="<?php echo $esc(isset($application['comaker' . $slot . '_cbu']) ? $application['comaker' . $slot . '_cbu'] : ''); ?>"/></td>
                                <td><input type="text" name="f[application][comaker<?php echo $slot; ?>_savings]" class="form-control" value="<?php echo $esc(isset($application['comaker' . $slot . '_savings']) ? $application['comaker' . $slot . '_savings'] : ''); ?>"/></td>
                                <td><input type="text" name="f[application][comaker<?php echo $slot; ?>_collateral]" class="form-control" value="<?php echo $esc(isset($application['comaker' . $slot . '_collateral']) ? $application['comaker' . $slot . '_collateral'] : ''); ?>"/></td>
                                <td><input type="text" name="f[application][comaker<?php echo $slot; ?>_loan_balance]" class="form-control" value="<?php echo $esc(isset($application['comaker' . $slot . '_loan_balance']) ? $application['comaker' . $slot . '_loan_balance'] : ''); ?>"/></td>
                                <td><input type="text" name="f[application][comaker<?php echo $slot; ?>_consumer_balance]" class="form-control" value="<?php echo $esc(isset($application['comaker' . $slot . '_consumer_balance']) ? $application['comaker' . $slot . '_consumer_balance'] : ''); ?>"/></td>
                                <td><input type="text" name="f[application][comaker<?php echo $slot; ?>_migs]" class="form-control" value="<?php echo $esc(isset($application['comaker' . $slot . '_migs']) ? $application['comaker' . $slot . '_migs'] : ''); ?>"/></td>
                                <td><input type="text" name="f[application][comaker<?php echo $slot; ?>_other_info]" class="form-control" value="<?php echo $esc(isset($application['comaker' . $slot . '_other_info']) ? $application['comaker' . $slot . '_other_info'] : ''); ?>"/></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="section-divider"><i class="fa fa-users"></i><?php echo lang('loan_form_signatories'); ?></div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_loan_officer'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[application][sign_loan_officer]" class="form-control" value="<?php echo $esc(isset($application['sign_loan_officer']) ? $application['sign_loan_officer'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_treasurer'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[application][sign_treasurer]" class="form-control" value="<?php echo $esc(isset($application['sign_treasurer']) ? $application['sign_treasurer'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_crecom_chairman'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[application][sign_crecom_chairman]" class="form-control" value="<?php echo $esc(isset($application['sign_crecom_chairman']) ? $application['sign_crecom_chairman'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_manager'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[application][sign_manager]" class="form-control" value="<?php echo $esc(isset($application['sign_manager']) ? $application['sign_manager'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_chairman'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[application][sign_chairman]" class="form-control" value="<?php echo $esc(isset($application['sign_chairman']) ? $application['sign_chairman'] : ''); ?>"/>
                </div>
                <div class="col-lg-5"></div>
            </div>

            <div class="cbu-actions">
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo lang('loan_form_save'); ?>
                </button>
                <a class="btn btn-default"
                   href="<?php echo site_url(current_lang() . '/loan/loan_form_reset/' . $loanid . '/application'); ?>"
                   onclick="return confirm('<?php echo $esc(lang('loan_form_reset_confirm')); ?>');">
                    <i class="fa fa-undo"></i> <?php echo lang('loan_form_reset'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>

    <!-- ============ TPSTEMPC-13 Co-Makers Statement & Promissory Note ============ -->
    <?php echo form_open($form_url, 'class="form-horizontal"'); ?>
    <div class="cbu-panel">
        <div class="panel-head">
            <i class="fa fa-users"></i>
            <h4><?php echo lang('loan_form_comakers'); ?></h4>
            <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo site_url($html13_url); ?>">
                <i class="fa fa-eye"></i> <?php echo lang('loan_form_preview'); ?>
            </a>
            <a class="btn btn-default btn-sm" target="_blank" href="<?php echo site_url($pdf13_url); ?>">
                <i class="fa fa-file-pdf-o"></i> <?php echo lang('loan_form_print_pdf'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_date'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[comakers][date]" class="form-control" value="<?php echo $esc(isset($comakers['date']) ? $comakers['date'] : ''); ?>"/>
                </div>
            </div>

            <?php for ($slot = 1; $slot <= 2; $slot++) { ?>
                <div class="co-maker-card">
                    <h5 class="card-title">
                        <?php echo sprintf(lang('loan_form_comaker_block'), $slot); ?>
                        <?php
                        $person_name = isset($co_makers[$slot - 1]) ? $co_makers[$slot - 1]['name'] : '';
                        if ($person_name !== '') {
                            echo ' &mdash; <span style="font-weight:600;color:#1ab394;">' . $esc($person_name) . '</span>';
                        }
                        ?>
                    </h5>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?php echo lang('loan_form_person_comaker'); ?> <?php echo $slot; ?></label>
                        <div class="col-lg-9">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_name]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_name']) ? $comakers['comaker' . $slot . '_name'] : ''); ?>"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?php echo lang('member_contact_address'); ?></label>
                        <div class="col-lg-9">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_address]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_address']) ? $comakers['comaker' . $slot . '_address'] : ''); ?>"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?php echo lang('loan_form_employer'); ?></label>
                        <div class="col-lg-9">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_employer]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_employer']) ? $comakers['comaker' . $slot . '_employer'] : ''); ?>"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?php echo lang('loan_form_station'); ?></label>
                        <div class="col-lg-5">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_station]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_station']) ? $comakers['comaker' . $slot . '_station'] : ''); ?>"/>
                        </div>
                        <label class="col-lg-1 control-label"><?php echo lang('loan_form_position'); ?></label>
                        <div class="col-lg-3">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_position]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_position']) ? $comakers['comaker' . $slot . '_position'] : ''); ?>"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?php echo lang('loan_form_salary'); ?></label>
                        <div class="col-lg-4">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_salary]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_salary']) ? $comakers['comaker' . $slot . '_salary'] : ''); ?>"/>
                        </div>
                        <label class="col-lg-2 control-label"><?php echo lang('loan_form_net_pay'); ?></label>
                        <div class="col-lg-3">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_net_pay]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_net_pay']) ? $comakers['comaker' . $slot . '_net_pay'] : ''); ?>"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?php echo lang('loan_form_obligations'); ?>: <?php echo lang('loan_form_obligation_loan'); ?></label>
                        <div class="col-lg-4">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_obligation_loan]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_obligation_loan']) ? $comakers['comaker' . $slot . '_obligation_loan'] : ''); ?>"/>
                        </div>
                        <label class="col-lg-2 control-label"><?php echo lang('loan_form_obligation_consumer'); ?></label>
                        <div class="col-lg-3">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_obligation_consumer]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_obligation_consumer']) ? $comakers['comaker' . $slot . '_obligation_consumer'] : ''); ?>"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?php echo lang('loan_form_spouse'); ?></label>
                        <div class="col-lg-4">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_spouse]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_spouse']) ? $comakers['comaker' . $slot . '_spouse'] : ''); ?>"/>
                        </div>
                        <label class="col-lg-2 control-label"><?php echo lang('loan_form_spouse_occupation'); ?></label>
                        <div class="col-lg-3">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_spouse_occupation]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_spouse_occupation']) ? $comakers['comaker' . $slot . '_spouse_occupation'] : ''); ?>"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?php echo lang('loan_form_dependents'); ?></label>
                        <div class="col-lg-4">
                            <input type="text" name="f[comakers][comaker<?php echo $slot; ?>_dependents]" class="form-control" value="<?php echo $esc(isset($comakers['comaker' . $slot . '_dependents']) ? $comakers['comaker' . $slot . '_dependents'] : ''); ?>"/>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="section-divider"><i class="fa fa-calendar"></i><?php echo lang('loan_form_amount'); ?> / <?php echo lang('loan_form_date_released'); ?></div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_amount'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[comakers][amount]" class="form-control" value="<?php echo $esc(isset($comakers['amount']) ? $comakers['amount'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_pn_no'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[comakers][pn_no]" class="form-control" value="<?php echo $esc(isset($comakers['pn_no']) ? $comakers['pn_no'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_date_released'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[comakers][date_released]" class="form-control" value="<?php echo $esc(isset($comakers['date_released']) ? $comakers['date_released'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_date_due'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[comakers][date_due]" class="form-control" value="<?php echo $esc(isset($comakers['date_due']) ? $comakers['date_due'] : ''); ?>"/>
                </div>
            </div>

            <div class="section-divider"><i class="fa fa-file-text"></i><?php echo lang('loan_form_promissory_terms'); ?></div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_pn_principal'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[comakers][pn_amount]" class="form-control" value="<?php echo $esc(isset($comakers['pn_amount']) ? $comakers['pn_amount'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_pn_interest'); ?></label>
                <div class="col-lg-2">
                    <input type="text" name="f[comakers][pn_interest_rate]" class="form-control" value="<?php echo $esc(isset($comakers['pn_interest_rate']) ? $comakers['pn_interest_rate'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_pn_installments'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[comakers][pn_installments]" class="form-control" value="<?php echo $esc(isset($comakers['pn_installments']) ? $comakers['pn_installments'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_pn_monthly'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[comakers][pn_monthly]" class="form-control" value="<?php echo $esc(isset($comakers['pn_monthly']) ? $comakers['pn_monthly'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_pn_penalty'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[comakers][pn_penalty_rate]" class="form-control" value="<?php echo $esc(isset($comakers['pn_penalty_rate']) ? $comakers['pn_penalty_rate'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_pn_pay_start'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[comakers][pn_pay_start]" class="form-control" value="<?php echo $esc(isset($comakers['pn_pay_start']) ? $comakers['pn_pay_start'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_pn_pay_end'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[comakers][pn_pay_end]" class="form-control" value="<?php echo $esc(isset($comakers['pn_pay_end']) ? $comakers['pn_pay_end'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_remarks'); ?></label>
                <div class="col-lg-9">
                    <input type="text" name="f[comakers][remarks]" class="form-control" value="<?php echo $esc(isset($comakers['remarks']) ? $comakers['remarks'] : ''); ?>"/>
                </div>
            </div>

            <div class="cbu-actions">
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo lang('loan_form_save'); ?>
                </button>
                <a class="btn btn-default"
                   href="<?php echo site_url(current_lang() . '/loan/loan_form_reset/' . $loanid . '/comakers'); ?>"
                   onclick="return confirm('<?php echo $esc(lang('loan_form_reset_confirm')); ?>');">
                    <i class="fa fa-undo"></i> <?php echo lang('loan_form_reset'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>

    <!-- ==================== TPSTEMPC-13 Pledge & Authority ==================== -->
    <?php echo form_open($form_url, 'class="form-horizontal"'); ?>
    <div class="cbu-panel">
        <div class="panel-head">
            <i class="fa fa-handshake-o"></i>
            <h4><?php echo lang('loan_form_pledge'); ?></h4>
            <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo site_url($html_pledge_url); ?>">
                <i class="fa fa-eye"></i> <?php echo lang('loan_form_preview'); ?>
            </a>
            <a class="btn btn-default btn-sm" target="_blank" href="<?php echo site_url($pdf_pledge_url); ?>">
                <i class="fa fa-file-pdf-o"></i> <?php echo lang('loan_form_print_pdf'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_date'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[pledge][date]" class="form-control" value="<?php echo $esc(isset($pledge['date']) ? $pledge['date'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_note_date'); ?></label>
                <div class="col-lg-4">
                    <div class="row" style="margin:0;">
                        <div class="col-lg-7" style="padding-left:0;">
                            <input type="text" name="f[pledge][note_date]" class="form-control" value="<?php echo $esc(isset($pledge['note_date']) ? $pledge['note_date'] : ''); ?>"/>
                        </div>
                        <div class="col-lg-5" style="padding-right:0;">
                            <input type="text" name="f[pledge][note_year]" class="form-control" placeholder="<?php echo lang('loan_form_note_year'); ?>" value="<?php echo $esc(isset($pledge['note_year']) ? $pledge['note_year'] : ''); ?>"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_note_amount'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[pledge][note_amount]" class="form-control" value="<?php echo $esc(isset($pledge['note_amount']) ? $pledge['note_amount'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_authority_amount'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[pledge][authority_amount]" class="form-control" value="<?php echo $esc(isset($pledge['authority_amount']) ? $pledge['authority_amount'] : ''); ?>"/>
                </div>
            </div>

            <div class="section-divider"><i class="fa fa-pencil"></i><?php echo lang('loan_form_signatories'); ?></div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_maker_name'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[pledge][maker_name]" class="form-control" value="<?php echo $esc(isset($pledge['maker_name']) ? $pledge['maker_name'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_spouse_name'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[pledge][spouse_name]" class="form-control" value="<?php echo $esc(isset($pledge['spouse_name']) ? $pledge['spouse_name'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_comaker_signature'); ?> 1</label>
                <div class="col-lg-4">
                    <input type="text" name="f[pledge][comaker1_name]" class="form-control" value="<?php echo $esc(isset($pledge['comaker1_name']) ? $pledge['comaker1_name'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_comaker_signature'); ?> 2</label>
                <div class="col-lg-3">
                    <input type="text" name="f[pledge][comaker2_name]" class="form-control" value="<?php echo $esc(isset($pledge['comaker2_name']) ? $pledge['comaker2_name'] : ''); ?>"/>
                </div>
            </div>

            <div class="cbu-actions">
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo lang('loan_form_save'); ?>
                </button>
                <a class="btn btn-default"
                   href="<?php echo site_url(current_lang() . '/loan/loan_form_reset/' . $loanid . '/pledge'); ?>"
                   onclick="return confirm('<?php echo $esc(lang('loan_form_reset_confirm')); ?>');">
                    <i class="fa fa-undo"></i> <?php echo lang('loan_form_reset'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>

    <!-- ==================== Disclosure Statement ==================== -->
    <?php echo form_open($form_url, 'class="form-horizontal"'); ?>
    <div class="cbu-panel">
        <div class="panel-head">
            <i class="fa fa-file-text-o"></i>
            <h4><?php echo lang('loan_form_disclosure'); ?></h4>
            <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo site_url($html_ds_url); ?>">
                <i class="fa fa-eye"></i> <?php echo lang('loan_form_preview'); ?>
            </a>
            <a class="btn btn-default btn-sm" target="_blank" href="<?php echo site_url($pdf_ds_url); ?>">
                <i class="fa fa-file-pdf-o"></i> <?php echo lang('loan_form_print_pdf'); ?>
            </a>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_ds_payee'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[disclosure][payee]" class="form-control" value="<?php echo $esc(isset($disclosure['payee']) ? $disclosure['payee'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_ds_no'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[disclosure][no]" class="form-control" value="<?php echo $esc(isset($disclosure['no']) ? $disclosure['no'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_ds_address'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[disclosure][address]" class="form-control" value="<?php echo $esc(isset($disclosure['address']) ? $disclosure['address'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_date'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[disclosure][date]" class="form-control" value="<?php echo $esc(isset($disclosure['date']) ? $disclosure['date'] : ''); ?>"/>
                </div>
            </div>

            <div class="section-divider"><i class="fa fa-table"></i><?php echo lang('loan_form_ds_amounts'); ?></div>
            <div class="table-responsive">
                <table class="form-table">
                    <thead>
                        <tr>
                            <th style="text-align:left;">PARTICULARS</th>
                            <th style="width:22%;">DR</th>
                            <th style="width:22%;">CR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ds_rows = array(
                            array('label' => 'Loan Granted (Amount Financed)', 'key' => 'loan_granted', 'strong' => TRUE, 'indent' => FALSE),
                            array('label' => 'Less: Financed Charges', 'key' => 'financed_charges', 'strong' => TRUE, 'indent' => FALSE),
                            array('label' => 'Interest on Loans (30%pa)(2%/mo)', 'key' => 'interest', 'strong' => FALSE, 'indent' => TRUE),
                            array('label' => 'Filing Fee', 'key' => 'filing_fee', 'strong' => FALSE, 'indent' => TRUE),
                            array('label' => 'Service Fee', 'key' => 'service_fee', 'strong' => FALSE, 'indent' => TRUE),
                            array('label' => 'Savings Deposit', 'key' => 'savings_deposit', 'strong' => FALSE, 'indent' => TRUE),
                            array('label' => 'Paid-Up Capital Share', 'key' => 'paid_up_share', 'strong' => FALSE, 'indent' => TRUE),
                            array('label' => 'Insurance', 'key' => 'insurance', 'strong' => FALSE, 'indent' => TRUE),
                            array('label' => 'Loan Balance', 'key' => 'loan_balance', 'strong' => FALSE, 'indent' => TRUE),
                            array('label' => 'Others:', 'key' => 'others', 'strong' => FALSE, 'indent' => TRUE),
                            array('label' => 'Total Deductions:', 'key' => 'total_deductions', 'strong' => TRUE, 'indent' => FALSE),
                            array('label' => 'NET PROCEEDS OF LOAN', 'key' => 'net_proceeds', 'strong' => TRUE, 'indent' => FALSE),
                        );
                        foreach ($ds_rows as $row) {
                            $dr = isset($disclosure[$row['key'] . '_dr']) ? $disclosure[$row['key'] . '_dr'] : '';
                            $cr = isset($disclosure[$row['key'] . '_cr']) ? $disclosure[$row['key'] . '_cr'] : '';
                            ?>
                            <tr>
                                <td class="row-label" style="<?php echo $row['strong'] ? 'font-weight:700;' : ''; ?><?php echo $row['indent'] ? 'padding-left:26px;' : ''; ?>">
                                    <?php echo htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><input type="text" name="f[disclosure][<?php echo $row['key']; ?>_dr]" class="form-control" value="<?php echo $esc($dr); ?>"/></td>
                                <td><input type="text" name="f[disclosure][<?php echo $row['key']; ?>_cr]" class="form-control" value="<?php echo $esc($cr); ?>"/></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="section-divider"><i class="fa fa-pencil"></i><?php echo lang('loan_form_signatories'); ?></div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_loan_officer'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[disclosure][loan_officer]" class="form-control" value="<?php echo $esc(isset($disclosure['loan_officer']) ? $disclosure['loan_officer'] : ''); ?>"/>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('loan_form_manager'); ?></label>
                <div class="col-lg-3">
                    <input type="text" name="f[disclosure][manager_chairman]" class="form-control" value="<?php echo $esc(isset($disclosure['manager_chairman']) ? $disclosure['manager_chairman'] : ''); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('loan_form_ds_conforme'); ?></label>
                <div class="col-lg-4">
                    <input type="text" name="f[disclosure][payee_conforme]" class="form-control" value="<?php echo $esc(isset($disclosure['payee_conforme']) ? $disclosure['payee_conforme'] : ''); ?>"/>
                </div>
                <div class="col-lg-5"></div>
            </div>

            <div class="cbu-actions">
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-save"></i> <?php echo lang('loan_form_save'); ?>
                </button>
                <a class="btn btn-default"
                   href="<?php echo site_url(current_lang() . '/loan/loan_form_reset/' . $loanid . '/disclosure'); ?>"
                   onclick="return confirm('<?php echo $esc(lang('loan_form_reset_confirm')); ?>');">
                    <i class="fa fa-undo"></i> <?php echo lang('loan_form_reset'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
