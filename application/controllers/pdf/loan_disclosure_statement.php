<?php

/**
 * Disclosure Statement (TCPDF output).
 *
 * Two copies, one per page: the browser preview (views/loan/print/loan_disclosure_print.php)
 * puts both copies on one sheet with a cut mark, which does not fit on a LETTER
 * page once the cooperative letterhead is added.
 *
 * Included from Loan::print_loan_disclosure_pdf().
 * Expects: $company, $loaninfo, $member, $maker, $form.
 */

$this->load->library('pdf');
@include_once dirname(__FILE__) . '/loan_form_common.php';

if (!function_exists('lf_render')) {
    show_error('Loan form renderer is missing: ' . dirname(__FILE__) . '/loan_form_common.php', 500);
    return;
}

$pdf = $this->pdf;

$member_id = ($member && !empty($member->member_id)) ? $member->member_id : '';
$reference = 'Member: ' . $maker['name'] . ($member_id !== '' ? ' (' . $member_id . ')' : '')
    . '   |   Loan No.: ' . $loaninfo->LID;

/* Statement rows: label, field key, "P" prefix inside the amount cells. */
$rows = array(
    array('label' => 'Loan Granted (Amount Financed)', 'key' => 'loan_granted', 'bold' => TRUE, 'indent' => FALSE, 'p_dr' => TRUE, 'p_cr' => TRUE, 'top' => FALSE),
    array('label' => 'Less: Financed Charges', 'key' => 'financed_charges', 'bold' => TRUE, 'italic' => TRUE, 'indent' => FALSE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'Interest on Loans (30%pa)(2%/mo)', 'key' => 'interest', 'bold' => FALSE, 'indent' => TRUE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'Filing Fee', 'key' => 'filing_fee', 'bold' => FALSE, 'indent' => TRUE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'Service Fee', 'key' => 'service_fee', 'bold' => FALSE, 'indent' => TRUE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'Savings Deposit', 'key' => 'savings_deposit', 'bold' => FALSE, 'indent' => TRUE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'Paid-Up Capital Share', 'key' => 'paid_up_share', 'bold' => FALSE, 'indent' => TRUE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'Insurance', 'key' => 'insurance', 'bold' => FALSE, 'indent' => TRUE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'Loan Balance', 'key' => 'loan_balance', 'bold' => FALSE, 'indent' => TRUE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'Others:', 'key' => 'others', 'bold' => FALSE, 'indent' => TRUE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'Total Deductions:', 'key' => 'total_deductions', 'bold' => TRUE, 'indent' => FALSE, 'p_dr' => FALSE, 'p_cr' => FALSE, 'top' => FALSE),
    array('label' => 'NET PROCEEDS OF LOAN', 'key' => 'net_proceeds', 'bold' => TRUE, 'indent' => FALSE, 'p_dr' => FALSE, 'p_cr' => TRUE, 'top' => TRUE),
);

$amount_cell = function ($value, $p_prefix) {
    return ($p_prefix ? 'P ' : '') . lf_v($value);
};

/**
 * One copy of the statement.
 */
$build_copy = function ($caption) use ($form, $rows, $amount_cell) {
    $html = '';

    if ($caption !== '') {
        $html .= '<div style="font-size:7pt; font-style:italic; color:#6b7280; text-align:right;">' . lf_h($caption) . '</div>';
    }

    /* ------------------------------------------------------- payee block -- */
    $html .= '<table cellpadding="2" style="width:100%; font-size:8.5pt;">'
        . '<tr>'
        . '<td style="width:12%;">Payee:</td>'
        . '<td style="width:46%; border-bottom:1px solid #374151;">' . lf_v($form['payee']) . '</td>'
        . '<td style="width:8%; text-align:right;">No.</td>'
        . '<td style="width:34%; border-bottom:1px solid #374151;">' . lf_v($form['no']) . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td>Address:</td>'
        . '<td style="border-bottom:1px solid #374151;">' . lf_v($form['address']) . '</td>'
        . '<td style="text-align:right;">Date:</td>'
        . '<td style="border-bottom:1px solid #374151;">' . lf_v($form['date']) . '</td>'
        . '</tr>'
        . '</table>';

    /* ----------------------------------------------------------- table ---- */
    $html .= '<table border="1" cellpadding="3" style="width:100%; font-size:8pt; margin-top:2mm;">'
        . '<thead>'
        . '<tr style="background-color:#f3f4f6;">'
        . '<td rowspan="2" style="width:50%; text-align:center; font-weight:bold;">PARTICULARS</td>'
        . '<td colspan="2" style="text-align:center; font-weight:bold;">AMOUNT</td>'
        . '</tr>'
        . '<tr style="background-color:#f3f4f6;">'
        . '<td style="width:25%; text-align:center; font-weight:bold;">DR</td>'
        . '<td style="width:25%; text-align:center; font-weight:bold;">CR</td>'
        . '</tr>'
        . '</thead><tbody>';

    foreach ($rows as $row) {
        $label_style = 'text-align:left;';
        if (!empty($row['bold'])) {
            $label_style .= ' font-weight:bold;';
        }
        if (!empty($row['italic'])) {
            $label_style .= ' font-style:italic;';
        }
        if (!empty($row['indent'])) {
            $label_style .= ' padding-left:7mm;';
        }
        $row_style = !empty($row['top']) ? ' border-top:2px solid #000000;' : '';

        $html .= '<tr>'
            . '<td style="' . $label_style . $row_style . '">' . lf_h($row['label']) . '</td>'
            . '<td style="text-align:center;' . $row_style . '">' . $amount_cell($form[$row['key'] . '_dr'], !empty($row['p_dr'])) . '</td>'
            . '<td style="text-align:center;' . $row_style . '">' . $amount_cell($form[$row['key'] . '_cr'], !empty($row['p_cr'])) . '</td>'
            . '</tr>';
    }
    $html .= '</tbody></table>';

    /* ------------------------------------------------------ signatures ---- */
    $html .= '<table cellpadding="2" style="width:100%; font-size:8pt; margin-top:5mm;">'
        . '<tr>'
        . '<td style="width:46%; font-weight:bold;">APPROVED:</td>'
        . '<td style="width:8%;">&nbsp;</td>'
        . '<td style="width:46%; font-weight:bold; text-align:right;">CONFORME:</td>'
        . '</tr>'
        . '<tr>'
        . '<td style="height:10mm; text-align:center; border-bottom:1px solid #374151;" valign="bottom">' . lf_v($form['loan_officer']) . '</td>'
        . '<td>&nbsp;</td>'
        . '<td style="height:10mm; text-align:center; border-bottom:1px solid #374151;" valign="bottom">' . lf_v($form['payee_conforme']) . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td style="text-align:center; font-size:7pt;">Loan Officer</td>'
        . '<td>&nbsp;</td>'
        . '<td style="text-align:center; font-size:7pt;">Signature of Payee over printed name</td>'
        . '</tr>'
        . '<tr>'
        . '<td style="height:10mm; text-align:center; border-bottom:1px solid #374151;" valign="bottom">' . lf_v($form['manager_chairman']) . '</td>'
        . '<td>&nbsp;</td>'
        . '<td>&nbsp;</td>'
        . '</tr>'
        . '<tr>'
        . '<td style="text-align:center; font-size:7pt;">Manager/Chairman</td>'
        . '<td>&nbsp;</td>'
        . '<td>&nbsp;</td>'
        . '</tr>'
        . '</table>';

    return $html;
};

loan_form_pdf_start($pdf);

loan_form_pdf_letterhead($pdf, $company, '', $reference);
$pdf->writeHTML($build_copy('ORIGINAL'), TRUE, FALSE, TRUE, FALSE, '');

$pdf->AddPage();
loan_form_pdf_letterhead($pdf, $company, '', $reference);
$pdf->writeHTML($build_copy('DUPLICATE COPY'), TRUE, FALSE, TRUE, FALSE, '');

$pdf->Output('TPSTEMCO-Disclosure-' . $loaninfo->LID . '.pdf', 'I');
