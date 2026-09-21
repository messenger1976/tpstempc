<?php

/**
 * TPSTEMPC-13 - Pledge and Authority (TCPDF output).
 *
 * Rendered from table based HTML so the printed sheet follows the layout of the
 * source document (TPSTEMPC-13_Pledge_Authority.html).
 *
 * Included from Loan::print_pledge_authority_pdf().
 * Expects: $company, $loaninfo, $member, $maker, $co_makers, $schedule, $form.
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
$reference = 'Member: ' . $maker['name'] . ($member_id !== '' ? ' (' . $member_id . ')' : '')
    . '   |   Loan No.: ' . $loaninfo->LID;

loan_form_pdf_letterhead($pdf, $company, 'TPSTEMPC-13', $reference);

$html = '';

/* ---------------------------------------------------------------- date ---- */
$html .= '<table style="width:100%; font-size:9pt;"><tr>'
    . '<td style="width:70%;">&nbsp;</td>'
    . '<td style="width:8%;">Date:</td>'
    . '<td style="width:22%; border-bottom:1px solid #374151; text-align:center;">' . lf_v($form['date']) . '</td>'
    . '</tr></table>';

/* -------------------------------------------------------------- pledge ---- */
$html .= '<div style="font-size:9pt; text-align:justify; margin-top:4mm;">'
    . 'I/We the undersigned hereby pledge all deposits and payments on deposits which I/We now have or hereafter may have in the Multipurpose Cooperative as security for the loan as evidenced by a note dated '
    . lf_underline($form['note_date'], 10)
    . ' , 20' . lf_underline($form['note_year'], 2)
    . ' in the amount of P ' . lf_underline($form['note_amount'], 10)
    . ' executed by us payable to the <b>TALIBON PUBLIC SCHOOL TEACHERS AND EMPLOYEES MULTIPURPOSE COOPERATIVE, TALIBON, BOHOL</b>,'
    . ' this pledge is given to secure the payments of the above described loan and interests, fines, costs or expenses that may accrue thereon, and I/We hereby authorize this Multi-Purpose Cooperative to apply any or all such deposits and payments of said loan interests, fines and cost or expenses.'
    . '</div>';

/* ---------------------------------------------------------- signatures ---- */
$html .= '<div style="margin-top:8mm;">'
    . lf_signature_table(array(
        array('width' => '26%', 'caption' => '', 'name' => ''),
        array('width' => '48%', 'caption' => 'Signature of Maker', 'name' => $form['maker_name']),
        array('width' => '26%', 'caption' => '', 'name' => ''),
    ), '0%', '10mm')
    . '</div>';

$html .= '<div style="margin-top:8mm;">'
    . lf_signature_table(array(
        array('width' => '48%', 'caption' => 'Signature of Co-Maker', 'name' => $form['comaker1_name']),
        array('width' => '48%', 'caption' => 'Signature of Co-Maker', 'name' => $form['comaker2_name']),
    ), '4%', '10mm')
    . '</div>';

/* ----------------------------------------------------------- authority ---- */
$html .= '<div style="text-align:center; font-size:12pt; font-weight:bold; letter-spacing:2pt; margin-top:10mm;">A U T H O R I T Y</div>';

$html .= '<div style="font-size:9pt; text-align:justify; margin-top:4mm;">'
    . 'In consideration for the Loan granted to me by the <b>TALIBON PUBLIC SCHOOL TEACHERS &amp; EMPLOYEES MULTIPURPOSE COOPERATIVE, Talibon, Bohol</b>'
    . ' in the amount of P ' . lf_underline($form['authority_amount'], 10)
    . ' in Philippine Currency, I authorize the Multipurpose Cooperative to withhold my Treasury Warrant / ATM CARD corresponding to my salary for the period to redeem to by paying the amount at the Multi-Purpose Cooperative.'
    . '</div>';

/* ------------------------------------------------------ spouse consent ---- */
$html .= '<div style="font-size:8pt; margin-top:8mm;">With Spouse consent:</div>';

$html .= '<div style="margin-top:2mm;">'
    . lf_signature_table(array(
        array('width' => '48%', 'caption' => '(Signature of Spouse over printed name)', 'name' => $form['spouse_name']),
        array('width' => '48%', 'caption' => 'Signature of Maker', 'name' => $form['maker_name']),
    ), '4%', '10mm')
    . '</div>';

lf_render($pdf, $html, 'TPSTEMPC-13-pledge-authority-' . $loaninfo->LID . '.pdf');
