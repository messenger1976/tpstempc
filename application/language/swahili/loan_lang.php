<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$lang['loan_create_new'] = 'Create New Loan';
$lang['loan_basic_info'] = 'Loan Basic Informations';
$lang['loan_product'] = 'Loan Product';
$lang['loan_search_name'] = 'Search Name';
$lang['loan_products'] = 'Loan Products';
$lang['loan_products_all'] = 'All Loan Products';
$lang['loan_applicationdate'] = 'Application Date';
$lang['loan_applied_amount'] = 'Base Amount';
$lang['loan_installment'] = 'Installment No.';
$lang['loan_paysource'] = 'Payment Source';
$lang['loan_purpose'] = 'Description';
$lang['loan_addbtn'] = 'Create Loan';
$lang['loan_LID'] = 'Loan Number';

//loan validation
$lang['loan_share_insufficient'] = 'Insufficient share';
$lang['loan_maximum_duration'] = 'Maximum duration exceeded';
$lang['loan_saved_success'] = 'Loan Information saved successfully ';
$lang['loan_add_fail'] = 'Fail to save Loan information';
$lang['loan_saving_insufficient'] = 'Insufficient amount in saving account';
$lang['loan_contribution_insufficient'] = 'Insufficient contribution amount';
$lang['loan_save_btn'] = 'Save Information';
$lang['loan_security_declaration'] = "Security Declaration";
$lang['loan_security'] = 'Loan Security';
$lang['loan_guarantors'] = 'Guarantors';
$lang['loan_supporting_document'] = 'Add Supporting document';
$lang['loan_supporting_document_comment'] = "Document Comment";
$lang['loan_supporting_document_attach'] = "Attach";
$lang['loan_supporting_document_doc'] = "Doc";
$lang['loan_supporting_document_remove'] = "Remove";
$lang['loan_supporting_document_view'] = "Download";
$lang['loan_supporting_document_upload_failed'] = "The supporting document could not be uploaded. Check the file size and try again.";
$lang['loan_supporting_document_hint'] = "Choose a file, then click Save. Uploaded files appear in the list above. Document comment is optional.";
$lang['loan_supporting_document_list'] = "Uploaded documents";

//guarantor
$lang['loan_quarantor_name'] = 'Guarantor Name';
$lang['loan_quarantor_name_placeholder'] = 'Tafuta kwa Member ID au Jina...';
$lang['loan_quarantor_relationship'] = 'Relationship';
$lang['loan_quarantor_relationship_other'] = 'Specify relationship';
$lang['loan_quarantor_relationship_options'] = array(
    'Spouse' => 'Spouse',
    'Father' => 'Father',
    'Mother' => 'Mother',
    'Son' => 'Son',
    'Daughter' => 'Daughter',
    'Brother' => 'Brother',
    'Sister' => 'Sister',
    'Uncle' => 'Uncle',
    'Aunt' => 'Aunt',
    'Cousin' => 'Cousin',
    'Friend' => 'Friend',
    'Colleague' => 'Colleague',
    'Relative' => 'Relative',
    'Others' => 'Others',
);
$lang['loan_quarantor_asset'] = 'Declared assets';
$lang['loan_quarantor_declaration'] = 'Guarantor declaration';
$lang['loan_quarantor_attachment'] = 'Attachment';
$lang['loan_quarantor_attachment_view'] = 'View';

$lang['loan_quarantor'] = 'Add New Guarantor Information';
$lang['loan_info_saved'] = 'Information saved successfully';

$lang['loan_evaluation_list'] = 'Loan List';
$lang['member_name'] = 'Member Details';
$lang['loan_installment_amount'] = 'Installment Amount';
$lang['loan_total_interest'] = 'Total Interest';
$lang['loan_evaluation_link'] = 'Evaluate';
$lang['loan_evaluation_inaction'] = 'Evaluate Loan';
$lang['loan_info'] = 'Loan Information';
$lang['loan_info_header'] = 'Loan Security & Guarantor';
$lang['loan_info_guarantor'] = 'Guarantor Informations';
$lang['loan_info_sopport'] = 'Supporting Documents';
$lang['loan_doc_not_found'] = 'NO DOCUMENT FOUND';
$lang['loan_guarantor_not_found'] = 'NO GUARANTOR FOUND';
$lang['contribution_balance'] = 'Current Contribution Amount';
$lang['saving_balance'] = 'Current Saving Amount';
$lang['share_balance'] = 'Current Share(s) Amount';
$lang['loan_edit'] = 'Edit Loan Information';

$lang['evaluation_comment'] = 'Evaluation Comment';
$lang['loan_status'] = 'Status';
$lang['loan_lifecycle_pending_release'] = 'Pending Release';
$lang['loan_lifecycle_released_unposted'] = 'Released not posted';
$lang['loan_lifecycle_active'] = 'Active';
$lang['loan_lifecycle_past_due'] = 'Past Due';
$lang['loan_comment'] = 'Comment';
$lang['loan_recorder'] = 'Recorder';
$lang['loan_evaluated_test'] = 'Save Information';
$lang['loan_evaluation_error'] = 'Some error exist in submited data, Scroll down to see more details';

$lang['loan_approval_link'] = 'Approve';
$lang['loan_approval_inaction'] = 'Approve Loan';
$lang['loan_approval_comment'] = 'Approve Comment';

$lang['loan_disburse_inaction'] = 'Loan Release';
$lang['loan_disburse_info'] = 'Loan Disbursement Informations';
$lang['loan_disburse_link'] = 'Release';
$lang['loan_disburse_payment_method'] = 'Payment Method';
$lang['loan_disburse_line_items'] = 'Accounting Entries';
$lang['loan_disburse_line_help'] = 'Debit: Loan account (principal). Credit: Source account (from payment method). You can add or edit lines; debits must equal credits.';
$lang['loan_disburse_entries_required'] = 'Please add at least one accounting line with amount.';
$lang['loan_disburse_no'] = 'Nambari ya Mito';
$lang['loan_disburse_no_exists'] = 'Nambari ya Mito hii tayari ipo.';
$lang['loan_disburse_date'] = 'Tarehe ya Mito';
$lang['loan_print_disbursement'] = 'Chapisha Mito';
$lang['loan_print_beginning_balance_journal'] = 'Print Journal';
$lang['loan_void_disbursement'] = 'Void Disbursement';
$lang['loan_void_disbursement_confirm'] = 'Void this loan disbursement? This will reverse the Loan Disbursed GL entry and any Savings/Share deduction sub-ledgers from the release, delete the schedule, and mark the loan as not disbursed so you can use Loan Release → Cash Disbursement. Continue only if there are no repayments.';
$lang['loan_cancel'] = 'Ghairi Mkopo';
$lang['loan_cancel_title'] = 'Ghairi Mkopo Ulio Kubaliwa / Unaosubiri Release';
$lang['loan_cancel_help'] = 'Tumia hii mkopo ulipokubaliwa kimakosa au usipotakiwa kuachiwa. Status itakuwa Accepted && Rejected. Worksheet ya pending release (isiyohusishwa na Cash Disbursement) itaondolewa. Hakuna GL inayopostiwa au kufutwa.';
$lang['loan_cancel_confirm'] = 'Ghairi mkopo huu? Utawekwa Accepted && Rejected na kuondolewa kwenye Loan Release. Haiwezi kufutwa kutoka skrini.';
$lang['loan_cancel_comment_required'] = 'Maoni yanahitajika ili kughairi mkopo huu.';
$lang['loan_cancel_success'] = 'Mkopo umeghairiwa. Status sasa ni Accepted && Rejected.';
$lang['loan_cancel_fail'] = 'Imeshindwa kughairi mkopo. Jaribu tena.';
$lang['loan_cancel_not_eligible'] = 'Mkopo huu hauwezi kughairiwa. Mikopo iliyokubaliwa na haijatoa tu ndiyo inaweza kughairiwa.';
$lang['loan_cancel_cd_linked'] = 'Release ya mkopo imeunganishwa na Cash Disbursement. Futa Cash Disbursement isiyopostiwa kwenye Finance kwanza, kisha ghairi mkopo.';
$lang['loan_disbursement_print'] = 'Chapisho la Mito';
$lang['loan_disbursement_statement'] = 'Taarifa ya Mito';
$lang['loan_disbursement_voucher'] = 'Hati ya Mito';
$lang['loan_beginning_balance_journal'] = 'Loan Beginning Balance Journal';
$lang['loan_beginning_balance_journal_statement'] = 'Loan Beginning Balance Journal';
$lang['loan_release_loan'] = 'Release Loan';
$lang['loan_release_exists'] = 'Mkopo huu tayari una kumbukumbu ya release inayosubiri au iliyokamilika.';
$lang['loan_release_not_approved'] = 'This loan is not approved for release.';
$lang['loan_release_already_disbursed'] = 'This loan is already marked as disbursed.';
$lang['loan_release_linked_to_cd'] = 'This loan release is already linked to a Cash Disbursement. Continue from Finance → Cash Disbursement (or Journal Entry Review to post).';
$lang['loan_release_editing_pending'] = 'Editing an existing pending release. Save updates the worksheet for Cash Disbursement.';
$lang['loan_release_saved'] = 'Release ya mkopo imehifadhiwa na sasa inasubiri Cash Disbursement.';
$lang['loan_offset_pending'] = 'Offset imewekwa: mkopo %d wenye jumla ya %s utasettlewa wakati payout itakapopostiwa.';

$lang['loan_startrepay_date'] = 'Repayment Start Date';
$lang['loan_view_detail'] = 'Details';
$lang['loan_viewdetails'] = 'Loan Information in Details';

$lang['loan_repay_amount'] = 'Amount';
$lang['loan_repay_date'] = 'Repayment Date';
$lang['loan_repay_btn'] = 'Process Payment';
$lang['loan_for_how_long'] = 'Payment for how many Installment ?';
$lang['loan_amount_required'] = 'Amount required is  %s';
$lang['loan_max_reached'] = 'Loan balance is 0';
$lang['loan_repay_due_title'] = 'Kiasi Kinachotakiwa (kwa tarehe ya malipo)';
$lang['loan_repay_status_due'] = 'Inadaiwa';
$lang['loan_repay_status_overdue'] = 'Imechelewa';
$lang['loan_repay_carry_balance'] = 'Salio lililohifadhiwa (carry)';
$lang['loan_repay_total_due'] = 'Jumla inayodaiwa';
$lang['loan_repay_net_due'] = 'Kiasi cha kukusanya';
$lang['loan_repay_nothing_due'] = 'Hakuna kikomo kinachodaiwa katika tarehe hii bado. Unaweza bado kulipa mapema kwa kiasi cha kawaida cha kikomo.';
$lang['loan_repay_due_explanation'] = 'Vikomo vilivyopita muda wa neema (%s siku baada ya tarehe ya kulipa) vina adhabu ya %s%% kwa kila mwezi uliochelewa (kulingana na bidhaa ya mkopo). Ikiwa vikomo viwili vilikosa, kiasi cha vikomo na adhabu zake vinaongezwa hapa chini. Weka angalau kiasi cha kukusanya ili malipo yaonekane kwenye orodha ya mkopo.';
$lang['loan_repay_amount_insufficient'] = 'Kiasi hakitoshi kutumia malipo haya. Kiwango cha chini kinachohitajika ni %s (kikomo pamoja na adhabu yoyote ya kuchelewa, baada ya carry).';
$lang['loan_repay_use_suggested'] = 'Tumia kiasi kilichopendekezwa';
$lang['loan_repay_penalty_months'] = 'Miezi ya adhabu';
$lang['loan_repay_suggested'] = 'Malipo yaliyopendekezwa';
$lang['loan_repay_pending_cash_receipt'] = 'Mkopo huu tayari una Stakabadhi ya Fedha ambayo bado haijachapishwa (%s). Chapisha au futa stakabadhi hiyo kabla ya kukusanya hapa.';
$lang['loan_collection_notice'] = 'Notisi ya Ukusanyaji wa Mkopo';
$lang['loan_collection_notice_print'] = 'Chapisha Notisi ya Ukusanyaji';
$lang['loan_collection_notice_as_of'] = 'Kiasi kinachodaiwa kufikia';
$lang['loan_collection_notice_printed'] = 'Imechapishwa';
$lang['loan_collection_notice_computation'] = 'Jinsi jumla inayolipwa inavyokokotolewa';
$lang['loan_collection_notice_total_payable'] = 'Jumla inayolipwa';
$lang['loan_collection_notice_formula'] = 'Jumla inayolipwa = kiasi cha vikomo vinavyodaiwa + adhabu za kuchelewa − salio lililohifadhiwa (carry). Vikomo ambavyo bado havijafika muda wake havijajumuishwa.';
$lang['loan_collection_notice_collector'] = 'Imekusanywa / Imetayarishwa na';
$lang['loan_collection_notice_member_ack'] = 'Uthibitisho wa mwanachama';
$lang['loan_collection_notice_footer'] = 'Notisi hii ni kwa rejea ya ukusanyaji. Malipo yanawekwa tu baada ya kurekodiwa katika Malipo ya Mkopo.';


$lang['loan_view_repayment_schedule'] = 'Loan Repayment Schedule';
$lang['loan_ledger'] = 'Orodha ya Mikopo';
$lang['loan_ledger_date'] = 'Tarehe';
$lang['loan_ledger_description'] = 'Maelezo';
$lang['loan_ledger_debit'] = 'Debit';
$lang['loan_ledger_credit'] = 'Credit';
$lang['loan_ledger_balance'] = 'Salio';
$lang['loan_ledger_total'] = 'Jumla';
$lang['loan_ledger_no_transactions'] = 'Hakuna shughuli za orodha.';
$lang['loan_ledger_disbursement'] = 'Mito';
$lang['loan_ledger_repayment'] = 'Malipo';
$lang['loan_ledger_schedule'] = 'Ratiba (Kikomo / Tarehe)';
$lang['loan_ledger_interest'] = 'Riba';
$lang['loan_ledger_penalty'] = 'Adhabu';
$lang['loan_ledger_amount_paid'] = 'Kiasi Kilicholipwa';
$lang['loan_ledger_advancement_lock_note_title'] = 'Malipo ya mapema na kufunga:';
$lang['loan_ledger_advancement_lock_note'] = 'Mwanachama anapolipa kiasi kinachotoshea salio lote (malipo ya mapema), mfumo unaandika malipo moja ya kikomo hicho na kuweka vikomo vilivyobaki kama vilivyofungwa—hakuna malipo zaidi yanayokubaliwa kwa mkopo huo na hadhi ya mkopo inakuwa Imefungwa. Ikiwa hakuna vikomo vilivyo wazi, mfumo haukubali malipo mapya na mkopo huwekwa kiotomatiki kuwa Imefungwa.';
$lang['due_date'] = 'Due date';
$lang['total_loan_amount'] = 'Total Loan :';
$lang['repayment_schedule'] = 'LOAN REPAYMENT SCHEDULE';

///$lang['loan_startrepay_date'] = 'Start Repay Date';
$lang['repayment_schedule'] = 'Repayment Schedule';
$lang['loan_id'] = 'Loan Number';
$lang['loan_statement'] = 'Loan Statement';
$lang['report_loan_list'] = 'Loan List';
$lang['report_loan_balance'] = 'Loan Balance';
$lang['report_loan_interest_penalty'] = 'Loan Interest && Penalty';
$lang['report_loan_transaction'] = 'Loan Transactions';
$lang['report_loan_transaction_summary'] = 'Loan Transactions Summary';
$lang['report_loan_aging'] = 'Loan Aging Report';
$lang['report_loan_aging_status'] = 'Active or Accepted (disbursed) loans only';

$lang['loan_schedule_none_yet'] = 'Hakuna ratiba ya malipo iliyoandaliwa kwa mkopo huu.';
$lang['loan_schedule_none_yet_note'] = 'Ratiba huandaliwa mkopo unapotolewa. Kwa mikopo iliyohamishiwa kwenye mfumo, itengeneze hapa chini kwa kutumia tarehe ambayo kikomo cha kwanza kinaiva.';
$lang['loan_schedule_start_date'] = 'Tarehe ya Kikomo cha Kwanza';
$lang['loan_schedule_generate'] = 'Tengeneza Ratiba';
$lang['loan_schedule_generated'] = 'Ratiba ya malipo imetengenezwa na vikomo %d.';
$lang['loan_schedule_exists'] = 'Mkopo huu tayari una ratiba ya malipo.';
$lang['loan_schedule_not_released'] = 'Ratiba ya malipo inaweza kutengenezwa kwa mkopo uliotolewa tu.';
$lang['loan_schedule_invalid_date'] = 'Tafadhali weka tarehe sahihi ya kikomo cha kwanza.';
$lang['loan_schedule_incomplete_terms'] = 'Mkopo huu hauna idadi ya vikomo au kiasi cha kikomo, hivyo ratiba haiwezi kutengenezwa.';
$lang['loan_schedule_generate_failed'] = 'Imeshindikana kutengeneza ratiba ya malipo. Tafadhali jaribu tena.';