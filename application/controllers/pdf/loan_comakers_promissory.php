<?php

/**
 * TPSTEMPC-13 - Co-Makers Statement and Promissory Note (TCPDF output).
 *
 * Rendered from table based HTML so the printed sheet follows the layout of the
 * source document (TPSTEMPC-13_CoMakers_PromissoryNote.html).
 *
 * Included from Loan::print_comakers_promissory_pdf().
 * Expects: $company, $loaninfo, $member, $product, $maker, $co_makers, $schedule, $form.
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

/* --------------------------------------------------- co-maker field rows --- */
$block_rows = array(
    array('Name', 'name'),
    array('Address', 'address'),
    array('Employer', 'employer'),
    array('Station/School', 'station'),
    array('Position', 'position'),
    array('Salary', 'salary'),
    array('Net Pay', 'net_pay'),
);

$co_maker_rows = '';
foreach ($block_rows as $row) {
    $co_maker_rows .= '<tr>'
        . '<td style="width:17%;">' . lf_h($row[0]) . '</td>'
        . '<td style="width:31%; border-bottom:1px solid #374151;">' . lf_v($form['comaker1_' . $row[1]]) . '</td>'
        . '<td style="width:4%;">&nbsp;</td>'
        . '<td style="width:17%;">' . lf_h($row[0]) . '</td>'
        . '<td style="width:31%; border-bottom:1px solid #374151;">' . lf_v($form['comaker2_' . $row[1]]) . '</td>'
        . '</tr>';
}

/* Obligations: "Loan" and "Consumer" are filled in inside the value cell. */
$co_maker_rows .= '<tr>'
    . '<td style="width:17%;">Obligations with TPSTEMPC:</td>'
    . '<td style="width:31%; border-bottom:1px solid #374151;">Loan <u>' . lf_vu($form['comaker1_obligation_loan'], 6) . '</u>'
    . ' &nbsp;Consumer <u>' . lf_vu($form['comaker1_obligation_consumer'], 6) . '</u></td>'
    . '<td style="width:4%;">&nbsp;</td>'
    . '<td style="width:17%;">Obligation with TPSTEMPC:</td>'
    . '<td style="width:31%; border-bottom:1px solid #374151;">Loan <u>' . lf_vu($form['comaker2_obligation_loan'], 6) . '</u>'
    . ' &nbsp;Consumer <u>' . lf_vu($form['comaker2_obligation_consumer'], 6) . '</u></td>'
    . '</tr>';

foreach (array(array('Name of Spouse', 'spouse'), array('Occupation', 'spouse_occupation'), array('Dependents', 'dependents')) as $row) {
    $co_maker_rows .= '<tr>'
        . '<td style="width:17%;">' . lf_h($row[0]) . '</td>'
        . '<td style="width:31%; border-bottom:1px solid #374151;">' . lf_v($form['comaker1_' . $row[1]]) . '</td>'
        . '<td style="width:4%;">&nbsp;</td>'
        . '<td style="width:17%;">' . lf_h($row[0]) . '</td>'
        . '<td style="width:31%; border-bottom:1px solid #374151;">' . lf_v($form['comaker2_' . $row[1]]) . '</td>'
        . '</tr>';
}

$html = '';

/* ------------------------------------------------------------- heading ---- */
$html .= '<div style="font-size:8.5pt; text-align:right;">Date: <u>' . lf_vu($form['date'], 14) . '</u></div>';

$html .= '<table style="width:100%; font-size:9pt; margin-top:2mm;"><tr>'
    . '<td style="width:26%;">&nbsp;</td>'
    . '<td style="border-bottom:2px solid #1f2937; text-align:center; font-size:12pt; font-weight:bold; letter-spacing:0.5pt;">CO-MAKERS STATEMENT</td>'
    . '<td style="width:26%;">&nbsp;</td>'
    . '</tr></table>';

/* ------------------------------------------------------ co-maker blocks --- */
$html .= '<table style="width:100%; font-size:7pt; margin-top:3mm;">'
    . '<tr>'
    . '<td style="width:17%; font-weight:bold;">CO-MAKER 1</td>'
    . '<td style="width:31%;">&nbsp;</td>'
    . '<td style="width:4%;">&nbsp;</td>'
    . '<td style="width:17%; font-weight:bold;">CO-MAKER 2</td>'
    . '<td style="width:31%;">&nbsp;</td>'
    . '</tr>'
    . '</table>';

$html .= '<table style="width:100%; font-size:8pt;">' . $co_maker_rows . '</table>';

$html .= '<div style="margin-top:6mm;">'
    . lf_signature_table(array(
        array('width' => '48%', 'caption' => 'Signature of Co-Maker', 'name' => $form['comaker1_name']),
        array('width' => '48%', 'caption' => 'Signature of Co-Maker', 'name' => $form['comaker2_name']),
    ), '4%', '10mm')
    . '</div>';

/* ------------------------------------------------- separator + amounts ---- */
$html .= '<table style="width:100%; margin-top:6mm;"><tr>'
    . '<td style="border-top:1px solid #d1d5db;"></td>'
    . '</tr></table>';

$html .= '<table style="width:100%; font-size:9pt; margin-top:3mm;"><tr>'
    . '<td style="width:5%;">(P</td>'
    . '<td style="width:28%; border-bottom:1px solid #374151; text-align:center;">' . lf_v($form['amount']) . '</td>'
    . '<td style="width:5%;">)</td>'
    . '<td style="width:8%;">&nbsp;</td>'
    . '<td style="width:22%; text-align:right;">Date Released</td>'
    . '<td style="width:32%; border-bottom:1px solid #374151;">' . lf_v($form['date_released']) . '</td>'
    . '</tr>'
    . '<tr>'
    . '<td colspan="4">&nbsp;</td>'
    . '<td style="text-align:right;">Date Due:</td>'
    . '<td style="border-bottom:1px solid #374151;">' . lf_v($form['date_due']) . '</td>'
    . '</tr>'
    . '<tr>'
    . '<td colspan="4">&nbsp;</td>'
    . '<td style="text-align:right;">PN NO.</td>'
    . '<td style="border-bottom:1px solid #374151;">' . lf_v($form['pn_no']) . '</td>'
    . '</tr>'
    . '</table>';

/* ----------------------------------------------------- promissory note ---- */
$html .= '<div style="text-align:center; font-size:12pt; font-weight:bold; letter-spacing:0.5pt; margin-top:6mm;">PROMISSORY NOTE</div>';

$html .= '<div style="font-size:8.5pt; text-align:justify; margin-top:3mm;">'
    . 'For value received, I/We jointly and severally promise to pay the '
    . '<b>TALIBON PUBLIC SCHOOL TEACHERS &amp; EMPLOYEES MULTIPURPOSE COOPERATIVE, TALIBON, BOHOL</b>'
    . ' the sum of P ' . lf_underline($form['pn_amount'], 10)
    . ' with interest of ' . lf_underline($form['pn_interest_rate'], 4)
    . ' % payable in ' . lf_underline($form['pn_installments'], 4)
    . ' monthly equal installments and a like amount every month thereafter until the full amount has been paid and on unpaid balance at the rate of '
    . '<b>' . lf_underline($form['pn_penalty_rate'], 3) . ' % per month.</b>'
    . '</div>';

$html .= '<div style="font-size:8.5pt; margin-top:3mm; margin-left:6mm;">'
    . 'Pay starts on ' . lf_underline($form['pn_pay_start'], 10)
    . ' and ends on ' . lf_underline($form['pn_pay_end'], 10) . '.'
    . '</div>';

$html .= '<div style="font-size:8.5pt; margin-top:2mm; margin-left:6mm;">'
    . 'At P ' . lf_underline($form['pn_monthly'], 10) . ' monthly installments.'
    . '</div>';

$html .= '<div style="font-size:8.5pt; text-align:justify; margin-top:4mm;">'
    . 'In case of default in payments as herein agreed, the entire balance of this note shall become immediately due and payable at the option of the holder. Each party to this note whether as maker/co-maker/guarantor severally waives presentment of payments, demands, protests and notice of protest and dishonor of the same.'
    . '</div>';

$html .= '<div style="font-size:8.5pt; text-align:justify; margin-top:2mm;">'
    . "It is agreed by party hereto, that in case payment shall not be made at maturity, he shall pay the cost of collections, and attorney's fee not be in the amount equal to twenty percent (20%) of the principal and interest due on this note but such in no event to be less than ten pesos (10.00)."
    . '</div>';

$html .= '<div style="font-size:8.5pt; text-align:justify; margin-top:2mm;">'
    . 'In case of judicial execution of this obligation in any part of it, the debtor waives all his rights under the provisions of Rule 3 Section 13 and Rule 39 Section 12 of the Rules of the Court.'
    . '</div>';

/* ---------------------------------------------------------- signatures ---- */
$html .= '<div style="margin-top:8mm;">'
    . lf_signature_table(array(
        array('width' => '48%', 'caption' => 'Signature of Maker', 'name' => $form['maker_name']),
        array('width' => '48%', 'caption' => 'Address', 'name' => $form['maker_address']),
    ), '4%', '10mm')
    . '</div>';

$html .= '<div style="margin-top:6mm;">'
    . lf_signature_table(array(
        array('width' => '48%', 'caption' => 'Signature of Co-Maker', 'name' => $form['comaker1_name']),
        array('width' => '48%', 'caption' => 'Address', 'name' => $form['comaker1_address']),
    ), '4%', '10mm')
    . '</div>';

$html .= '<div style="margin-top:6mm;">'
    . lf_signature_table(array(
        array('width' => '48%', 'caption' => 'Signature of Co-Maker', 'name' => $form['comaker2_name']),
        array('width' => '48%', 'caption' => 'Address', 'name' => $form['comaker2_address']),
    ), '4%', '10mm')
    . '</div>';

if (trim((string) $form['remarks']) !== '') {
    $html .= '<div style="font-size:7pt; color:#6b7280; margin-top:4mm;">Remarks: ' . lf_h($form['remarks']) . '</div>';
}

lf_render($pdf, $html, 'TPSTEMPC-13-co-makers-' . $loaninfo->LID . '.pdf');
