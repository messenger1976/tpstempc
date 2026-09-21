<?php

/**
 * TPSTEMPC-12 - Application for Loan (TCPDF output).
 *
 * Rendered from table based HTML so the printed sheet follows the layout of the
 * source document (TPSTEMPC-12_Loan_Application.html).
 *
 * Included from Loan::print_loan_application_pdf().
 * Expects: $company, $loaninfo, $member, $product, $maker, $co_makers, $form.
 */

$this->load->library('pdf');
@include_once dirname(__FILE__) . '/loan_form_common.php';

if (!function_exists('lf_render')) {
    show_error('Loan form renderer is missing: ' . dirname(__FILE__) . '/loan_form_common.php', 500);
    return;
}

$pdf = $this->pdf;
loan_form_pdf_start($pdf);

$member_id = ($member && !empty($member->member_id)) ? $member->member_id : '';
$product_name = ($product && !empty($product->name)) ? $product->name : '';
$reference = 'Member: ' . $maker['name'] . ($member_id !== '' ? ' (' . $member_id . ')' : '')
    . '   |   Loan No.: ' . $loaninfo->LID
    . ($product_name !== '' ? '   |   Product: ' . $product_name : '');

loan_form_pdf_letterhead($pdf, $company, 'TPSTEMPC-12', $reference);

/* --------------------------------------------------------------- data ----- */
$loan_types_first = array(
    array('label' => 'Salary Loan', 'checked' => !empty($form['loan_type_salary'])),
    array('label' => 'Supervise Loan', 'checked' => !empty($form['loan_type_supervise'])),
    array('label' => 'Bonus Loan', 'checked' => !empty($form['loan_type_bonus'])),
    array('label' => 'Cash Advance', 'checked' => !empty($form['loan_type_cashadvance'])),
    array('label' => 'Emergency Loan', 'checked' => !empty($form['loan_type_emergency'])),
);

$balance_rows = array(
    array(
        'label' => 'Maker/Borrower',
        'name' => $maker['name'],
        'cbu' => $form['maker_cbu'],
        'savings' => $form['maker_savings'],
        'collateral' => $form['maker_collateral'],
        'loan_balance' => $form['maker_loan_balance'],
        'consumer_balance' => $form['maker_consumer_balance'],
        'migs' => $form['maker_migs'],
        'other_info' => $form['maker_other_info'],
    ),
);
for ($slot = 1; $slot <= 2; $slot++) {
    $person = isset($co_makers[$slot - 1]) ? $co_makers[$slot - 1] : null;
    $balance_rows[] = array(
        'label' => 'Co-Maker',
        'name' => $person ? $person['name'] : '',
        'cbu' => $form['comaker' . $slot . '_cbu'],
        'savings' => $form['comaker' . $slot . '_savings'],
        'collateral' => $form['comaker' . $slot . '_collateral'],
        'loan_balance' => $form['comaker' . $slot . '_loan_balance'],
        'consumer_balance' => $form['comaker' . $slot . '_consumer_balance'],
        'migs' => $form['comaker' . $slot . '_migs'],
        'other_info' => $form['comaker' . $slot . '_other_info'],
    );
}

$html = '';

/* --------------------------------------------------------------- title ---- */
$html .= '<table style="width:100%;"><tr>'
    . '<td style="width:20%;">&nbsp;</td>'
    . '<td style="border-bottom:2px solid #1f2937; text-align:center; font-size:12pt; font-weight:bold; letter-spacing:0.5pt;">APPLICATION FOR LOAN</td>'
    . '<td style="width:20%;">&nbsp;</td>'
    . '</tr></table>';

/* ---------------------------------------------------------- loan types ---- */
$html .= '<table style="width:100%; font-size:8pt; margin-top:4mm;">'
    . lf_checkbox_row($loan_types_first, '30mm')
    . '</table>';

$html .= '<table style="width:100%; font-size:8pt; margin-top:2mm;"><tr>'
    . lf_checkbox_cell(!empty($form['loan_type_gadget']))
    . '<td style="width:24mm; padding-left:1.5mm;">Gadget Loan</td>'
    . lf_checkbox_cell(!empty($form['loan_type_car']))
    . '<td style="width:20mm; padding-left:1.5mm;">Car Loan</td>'
    . lf_checkbox_cell(!empty($form['loan_type_others']))
    . '<td style="width:30mm; padding-left:1.5mm;">Others pls. specify:</td>'
    . '<td style="border-bottom:1px solid #374151;">' . lf_v($form['loan_type_others']) . '</td>'
    . '</tr></table>';

/* ------------------------------------------------- passbook no. and date -- */
$html .= '<table style="width:100%; font-size:9pt; margin-top:4mm;"><tr>'
    . '<td style="width:14%;">PASSBOOK No.</td>'
    . '<td style="width:26%; border-bottom:1px solid #374151;">' . lf_v($form['passbook_no']) . '</td>'
    . '<td style="width:24%;">&nbsp;</td>'
    . '<td style="width:7%;">Date:</td>'
    . '<td style="width:29%; border-bottom:1px solid #374151;">' . lf_v($form['date']) . '</td>'
    . '</tr></table>';

/* -------------------------------------------------------- loan request ---- */
$html .= '<div style="font-size:9pt; text-align:justify; margin-top:4mm;">'
    . 'I hereby apply for a loan of P ' . lf_underline($form['amount'], 8)
    . ' for a period of ' . lf_underline($form['period'], 5)
    . ' months to be repaid in monthly installments of P ' . lf_underline($form['monthly_installment'], 8)
    . ' each plus interest. I prefer the first payment to fall a month after the loan is granted.'
    . '</div>';

$html .= '<div style="font-size:9pt; margin-top:4mm;">I desire this loan for the following provident/productive purpose:</div>';
$html .= '<div style="font-size:7pt; font-style:italic; color:#6b7280;">(Please explain fully)</div>';
$html .= '<table style="width:100%; font-size:9pt;">'
    . '<tr><td style="height:6mm; border-bottom:1px solid #374151;">' . lf_v($form['purpose']) . '</td></tr>'
    . '<tr><td style="height:6mm; border-bottom:1px solid #374151;">&nbsp;</td></tr>'
    . '</table>';

$html .= '<table style="width:100%; font-size:9pt; margin-top:3mm;"><tr>'
    . '<td style="width:22%;">Co-Makers security offered:</td>'
    . '<td style="border-bottom:1px solid #374151;">' . lf_v($form['co_makers_security']) . '</td>'
    . '</tr></table>';

/* ------------------------------------------------------- certification ---- */
$html .= '<div style="font-size:7.5pt; text-align:justify; margin-top:4mm;">'
    . 'I/We Hereby certify that my/our treasury warrant is not confined in any loaning agencies of the province and that all statements made including those on the reverse side herein are true and complete and submitted for the purpose of obtaining credit.'
    . '</div>';

/* ------------------------------------------------------- balance table ---- */
$html .= '<table style="width:100%; font-size:7pt; text-align:center; margin-top:4mm;">';
$html .= '<tr style="font-weight:bold;">'
    . '<td style="width:17%; text-align:left; border-bottom:2px solid #9ca3af;">Name</td>'
    . '<td style="width:11%; border-bottom:2px solid #9ca3af;">CBU</td>'
    . '<td style="width:12%; border-bottom:2px solid #9ca3af;">Savings Deposit</td>'
    . '<td style="width:14%; border-bottom:2px solid #9ca3af;">Collateral</td>'
    . '<td style="width:13%; border-bottom:2px solid #9ca3af;">Loan Balance</td>'
    . '<td style="width:13%; border-bottom:2px solid #9ca3af;">Consumer Balance</td>'
    . '<td style="width:8%; border-bottom:2px solid #9ca3af;">MIGS</td>'
    . '<td style="width:12%; border-bottom:2px solid #9ca3af;">Other Info.</td>'
    . '</tr>';

foreach ($balance_rows as $row_index => $row) {
    $row_top = ($row_index > 0) ? 'border-top:1px solid #e5e7eb;' : '';
    $underline = 'border-bottom:1px solid #374151;';

    $html .= '<tr>'
        . '<td style="' . $row_top . ' text-align:left; font-weight:bold;">' . lf_h($row['label'])
        . ($row['name'] !== '' ? '<br/><span style="font-weight:normal; font-size:6pt; color:#6b7280;">' . lf_h($row['name']) . '</span>' : '')
        . '</td>'
        . '<td style="' . $row_top . $underline . '">' . lf_v($row['cbu']) . '</td>'
        . '<td style="' . $row_top . $underline . '">' . lf_v($row['savings']) . '</td>'
        . '<td style="' . $row_top . $underline . '">' . lf_v($row['collateral']) . '</td>'
        . '<td style="' . $row_top . $underline . '">' . lf_v($row['loan_balance']) . '</td>'
        . '<td style="' . $row_top . $underline . '">' . lf_v($row['consumer_balance']) . '</td>'
        . '<td style="' . $row_top . $underline . '">' . lf_v($row['migs']) . '</td>'
        . '<td style="' . $row_top . $underline . '">' . lf_v($row['other_info']) . '</td>'
        . '</tr>';
}
$html .= '</table>';

/* ----------------------------------------------------- validation rule ---- */
$html .= '<table style="width:100%; margin-top:5mm; margin-bottom:5mm;"><tr>'
    . '<td style="height:1mm; border-top:2px solid #1f2937; border-bottom:2px solid #1f2937;"></td>'
    . '</tr></table>';

$html .= '<div style="font-size:7.5pt; text-align:justify;">'
    . 'We hereby certify that the Maker and Co-Makers of this application for loan have the following savings and obligations with the TAPSTEMCO and we approved the loan amount and the terms and conditions by the applicant.'
    . '</div>';

/* ------------------------------------------------------ approval blocks --- */
$html .= '<div style="font-size:7.5pt; font-style:italic; margin-top:6mm;">Recommending Approval:</div>';

$html .= '<div style="margin-top:2mm;">'
    . lf_signature_table(array(
        array('width' => '26%', 'caption' => '', 'name' => ''),
        array('width' => '48%', 'caption' => 'Loan Officer', 'name' => $form['sign_loan_officer']),
        array('width' => '26%', 'caption' => '', 'name' => ''),
    ), '0%', '10mm')
    . '</div>';

$html .= '<div style="font-size:7.5pt; font-style:italic; margin-top:6mm;">Approved:</div>';

$html .= '<div style="margin-top:2mm;">'
    . lf_signature_table(array(
        array('width' => '48%', 'caption' => 'Treasurer', 'name' => $form['sign_treasurer']),
        array('width' => '48%', 'caption' => 'CRECOM Chairman', 'name' => $form['sign_crecom_chairman']),
    ), '4%', '10mm')
    . '</div>';

$html .= '<div style="margin-top:6mm;">'
    . lf_signature_table(array(
        array('width' => '48%', 'caption' => 'Manager', 'name' => $form['sign_manager']),
        array('width' => '48%', 'caption' => 'Chairman', 'name' => $form['sign_chairman']),
    ), '4%', '10mm')
    . '</div>';

lf_render($pdf, $html, 'TPSTEMPC-12-' . $loaninfo->LID . '.pdf');
