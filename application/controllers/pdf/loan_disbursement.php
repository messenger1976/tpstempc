<?php

$this->load->library('pdf');
$this->pdf->set_subtitle('');
$this->pdf->hidefooter(FALSE);
$this->pdf->start_pdf(FALSE);
$this->pdf->SetSubject('miltone');
$this->pdf->SetKeywords('miltone');
$this->pdf->AddPage();
$this->pdf->SetY(10);
$this->pdf->SetFont('times', '', 10);

$company = company_info();
$logo = !empty($company->logo) ? $company->logo : 'logo.png';
$member = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
$member_name = $member ? trim($member->firstname . ' ' . $member->middlename . ' ' . $member->lastname) : '';
$disburse_no = isset($disburse->disburse_no) ? trim((string) $disburse->disburse_no) : '';
$payment_method = isset($disburse->payment_method) ? trim((string) $disburse->payment_method) : '';
$disburse_date = isset($disburse->disbursedate) ? date('d-m-Y', strtotime($disburse->disbursedate)) : '';
$member_id = $member ? $member->member_id : '';

$html = '<table style="border-bottom:1px solid #000; width:100%;">
        <tr>
            <td style="width:300px;">
                <img src="' . base_url() . 'logo/' . $logo . '" style="width:200px; height:160px;"/>
            </td>
            <td style="width:1800px; text-align:center"><b>
               <div style="font-size:180px;">' . htmlspecialchars($company->name) . '</div>
                P.O.Box ' . strtoupper($company->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . $company->mobile . '<br/>
      ' . strtoupper(lang('loan_disbursement_voucher')) . '
</b>
            </td>
        </tr>
    </table><br/>';

$html .= '<div style="text-align:center; font-size:140px; font-weight:bold; margin-bottom:10px;">'
    . htmlspecialchars(lang('loan_disbursement_statement'))
    . '<br/><span style="font-size:110px; font-weight:normal;">' . date('F d, Y') . '</span></div><br/>';

$html .= '<table cellpadding="4" style="width:100%;">
    <tr>
        <td style="width:50%; border:1px solid #000;" valign="top">';
if ($disburse_no !== '') {
    $html .= '<strong>' . lang('loan_disburse_no') . ':</strong> ' . htmlspecialchars($disburse_no) . '<br/>';
}
$html .= '<strong>' . lang('loan_LID') . ':</strong> ' . htmlspecialchars($loaninfo->LID) . '<br/>'
    . '<strong>' . lang('member_member_id') . ':</strong> ' . htmlspecialchars($member_id) . '<br/>'
    . '<strong>' . lang('member_name') . ':</strong> ' . htmlspecialchars($member_name)
    . '</td>
        <td style="width:50%; border:1px solid #000;" valign="top">'
    . '<strong>' . lang('loan_disburse_date') . ':</strong> ' . $disburse_date . '<br/>';
if ($payment_method !== '') {
    $html .= '<strong>' . lang('loan_disburse_payment_method') . ':</strong> ' . htmlspecialchars($payment_method) . '<br/>';
}
if (!empty($ledger_entry_id)) {
    $html .= '<strong>GL Entry #:</strong> ' . (int) $ledger_entry_id . '<br/>';
}
$html .= '<strong>' . lang('loan_applied_amount') . ':</strong> ' . number_format($loaninfo->basic_amount, 2)
    . '</td>
    </tr>
</table><br/>';

if (!empty($disburse->comment)) {
    $html .= '<div style="border:1px solid #000; padding:6px; margin-bottom:10px;">'
        . '<strong>' . lang('loan_comment') . ':</strong><br/>'
        . nl2br(htmlspecialchars($disburse->comment))
        . '</div><br/>';
}

$html .= '<table border="1" cellpadding="4">
    <thead>
        <tr style="background-color:#f5f5f5;">
            <th style="width:8%; text-align:center;">#</th>
            <th style="width:32%; text-align:center;">' . lang('account_code') . '</th>
            <th style="width:30%; text-align:center;">' . lang('journalentry_account_description') . '</th>
            <th style="width:15%; text-align:center;">' . lang('journalentry_debit') . '</th>
            <th style="width:15%; text-align:center;">' . lang('journalentry_credit') . '</th>
        </tr>
    </thead>
    <tbody>';

$total_debit = 0;
$total_credit = 0;
if (!empty($line_items)) {
    $index = 1;
    foreach ($line_items as $item) {
        $item_debit = isset($item->debit) ? floatval($item->debit) : 0;
        $item_credit = isset($item->credit) ? floatval($item->credit) : 0;
        $total_debit += $item_debit;
        $total_credit += $item_credit;
        $acc_label = trim((string) $item->account);
        $acc_name = isset($item->account_name) ? trim((string) $item->account_name) : '';
        $acc_full = $acc_name !== '' ? ($acc_name . ' (' . $acc_label . ')') : $acc_label;
        $desc = isset($item->description) ? $item->description : '';

        $html .= '<tr nobr="true">
            <td style="width:8%; text-align:center;">' . $index++ . '</td>
            <td style="width:32%;">' . htmlspecialchars($acc_full) . '</td>
            <td style="width:30%;">' . htmlspecialchars($desc) . '</td>
            <td style="width:15%; text-align:right;">' . number_format($item_debit, 2) . '</td>
            <td style="width:15%; text-align:right;">' . number_format($item_credit, 2) . '</td>
        </tr>';
    }
    $html .= '<tr>
            <td colspan="3" style="text-align:right;"><strong>' . lang('total') . ':</strong></td>
            <td style="text-align:right;"><strong>' . number_format($total_debit, 2) . '</strong></td>
            <td style="text-align:right;"><strong>' . number_format($total_credit, 2) . '</strong></td>
        </tr>';
} else {
    $html .= '<tr><td colspan="5" style="text-align:center;">' . lang('no_records_found') . '</td></tr>';
}

$html .= '</tbody></table><br/>';

$print_total = !empty($line_items) ? max($total_debit, $total_credit) : floatval($loaninfo->basic_amount);
if ($print_total > 0 && function_exists('convert_number_to_words')) {
    $html .= '<div style="border:1px solid #000; padding:8px; margin-bottom:15px;">'
        . '<strong>' . lang('amount_in_words') . ':</strong> '
        . ucfirst(convert_number_to_words($print_total)) . ' only.'
        . '</div><br/>';
}

$prepared_by = 'N/A';
if (!empty($disburse->createdby)) {
    $user = $this->ion_auth->user($disburse->createdby)->row();
    if ($user) {
        $prepared_by = trim($user->first_name . ' ' . $user->last_name);
    }
}

$html .= '<table cellpadding="6" style="width:100%;">
    <tr>
        <td style="width:50%; text-align:center;">
            ' . lang('prepared_by') . '<br/><br/><br/>
            ________________________________<br/>
            ' . htmlspecialchars($prepared_by) . '
        </td>
        <td style="width:50%; text-align:center;">
            ' . lang('authorized_by') . '<br/><br/><br/>
            ________________________________<br/>
            ' . lang('manager') . '
        </td>
    </tr>
</table><br/>
<div style="font-size:90px; font-style:italic; border-top:1px solid #000; padding-top:6px;">'
    . lang('note_this_document_is') . '<br/>'
    . lang('printed_on') . ' ' . date('d-m-Y H:i:s')
    . '</div>';

$this->pdf->writeHTML($html, true, false, false, false, '');

$filename = 'loan_disbursement_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $loaninfo->LID) . '.pdf';
$this->pdf->Output($filename, 'I');
exit;
?>
