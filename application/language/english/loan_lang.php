<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$lang['loan_create_new'] = 'Create New Loan';
$lang['loan_basic_info'] = 'Loan Basic Informations';
$lang['loan_product'] = 'Loan Product';
$lang['loan_applicationdate'] = 'Application Date';
$lang['loan_applied_amount'] = 'Base Amount';
$lang['loan_installment'] = 'Installment No.';
$lang['loan_paysource'] = 'Payment Source';
$lang['loan_purpose'] = 'Description';
$lang['loan_addbtn'] = 'Create Loan';
$lang['loan_LID'] = 'Loan Number';

//loan validation
$lang['loan_contribution_exceed_one_third'] = 'Total contribution and loan repayment exceed one third of your monthly income';
$lang['loan_contribution_times_exceed'] = 'Maximum times of contributions exceed';
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

//guarantor
$lang['loan_quarantor_name'] = 'Guarantor Name';
$lang['loan_quarantor_relationship'] = 'Relationship';
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
$lang['loan_total'] = 'Total Loan';
$lang['loan_not_allowed'] = 'Maximum loan allowed is %s';
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
$lang['loan_comment'] = 'Comment';
$lang['loan_recorder'] = 'Recorder';
$lang['loan_evaluated_test'] = 'Save Information';
$lang['loan_evaluation_error'] = 'Some error exist in submited data, Scroll down to see more details';

$lang['loan_approval_link'] = 'Approve';
$lang['loan_approval_inaction'] = 'Approve Loan';
$lang['loan_approval_comment'] = 'Approve Comment';

$lang['loan_disburse_inaction'] = 'Loan Disbursement';
$lang['loan_disburse_info'] = 'Loan Disbursement Informations';
$lang['loan_disburse_link'] = 'Disburse';
$lang['loan_disburse_payment_method'] = 'Payment Method';
$lang['loan_disburse_line_items'] = 'Accounting Entries';
$lang['loan_disburse_line_help'] = 'Debit: Loan account (principal). Credit: Source account (from payment method). With offset, credit old loan accounts and net cash only. Debits must equal credits.';
$lang['loan_disburse_entries_required'] = 'Please add at least one accounting line with amount.';
$lang['loan_disburse_no'] = 'Disbursement No.';
$lang['loan_disburse_no_exists'] = 'This Disbursement No. already exists.';
$lang['loan_disburse_date'] = 'Disbursement Date';
$lang['loan_print_disbursement'] = 'Print Disbursement';
$lang['loan_disbursement_print'] = 'Loan Disbursement Print';
$lang['loan_disbursement_statement'] = 'Loan Disbursement Statement';
$lang['loan_disbursement_voucher'] = 'Loan Disbursement Voucher';
$lang['loan_release_loan'] = 'Release Loan';
$lang['loan_offset_section'] = 'Offset Existing Loan(s) / Reloan';
$lang['loan_offset_help'] = 'Select active loan(s) to settle from this new loan. Old loans will be closed automatically. Cash to the member = new loan amount minus total offset. Accounting lines update when you tick a loan.';
$lang['loan_offset_principal'] = 'Principal Outstanding';
$lang['loan_offset_interest'] = 'Interest Outstanding';
$lang['loan_offset_total'] = 'Offset Total';
$lang['loan_offset_net_proceeds'] = 'Net Cash to Member';
$lang['loan_offset_exceeds_new_loan'] = 'Total offset exceeds the new loan amount. Increase the new loan or reduce the loans being offset.';
$lang['loan_offset_invalid_loan'] = 'One of the selected offset loans is invalid for this member';
$lang['loan_offset_settle_fail'] = 'Failed to close an offset loan. Disbursement was cancelled.';
$lang['loan_offset_success'] = 'Offset applied: %d loan(s) closed totaling %s.';
$lang['loan_disburse_deductions'] = 'Loan Proceeds Deductions';
$lang['loan_disburse_deductions_help'] = 'Enter deduction amounts to withhold from loan proceeds. Accounting lines update automatically. Net cash to member = Loan amount − deductions − offsets (if any). Leave amount blank or 0 to skip a deduction. Savings and Paid-up Share credits also update the member sub-ledgers on Save.';
$lang['loan_disburse_deduction_amount'] = 'Amount';
$lang['loan_disburse_deductions_total'] = 'Total Deductions';
$lang['loan_disburse_deductions_exceed'] = 'Total deductions (and offsets) exceed the loan amount. Reduce deductions or offsets.';
$lang['loan_disburse_subledger_fail'] = 'Failed to post Savings or Share deduction to the member sub-ledger. Disbursement was cancelled.';
$lang['loan_disburse_savings_type_missing'] = 'No savings product is mapped to GL account %s. Map a savings account type (account_setup) before disbursing with a Savings deduction.';
$lang['loan_disburse_savings_account_missing'] = 'This member has no savings account for the Savings Deposit product. Open a savings account first, then disburse.';
$lang['loan_disburse_savings_post_fail'] = 'Failed to credit the member savings account for the Savings Deposit deduction.';
$lang['loan_disburse_share_setup_missing'] = 'Share settings (cost per share) are not configured. Configure Shares before disbursing with a Paid-up Capital Share deduction.';
$lang['loan_disburse_share_max_reached'] = 'Paid-up Capital Share deduction would exceed the member maximum shares. Reduce the share deduction or raise the max.';
$lang['loan_disburse_share_post_fail'] = 'Failed to credit the member share sub-ledger for the Paid-up Capital Share deduction.';

$lang['loan_startrepay_date'] = 'Repayment Start Date';
$lang['loan_view_detail'] = 'Details';
$lang['loan_viewdetails'] = 'Loan Information in Details';

$lang['loan_repay_amount'] = 'Amount';
$lang['loan_repay_date'] = 'Repayment Date';
$lang['loan_repay_btn'] = 'Process Payment';
$lang['loan_for_how_long'] = 'Payment for how many Installment ?';
$lang['loan_amount_required'] = 'Amount required is  %s';
$lang['loan_max_reached'] = 'Loan balance is 0';


$lang['loan_view_repayment_schedule'] = 'Loan Repayment Schedule';
$lang['loan_ledger'] = 'Loan Ledger';
$lang['loan_ledger_date'] = 'Date';
$lang['loan_ledger_description'] = 'Description';
$lang['loan_ledger_debit'] = 'Debit';
$lang['loan_ledger_credit'] = 'Credit';
$lang['loan_ledger_balance'] = 'Balance';
$lang['loan_ledger_total'] = 'Total';
$lang['loan_ledger_no_transactions'] = 'No ledger transactions found.';
$lang['loan_ledger_disbursement'] = 'Disbursement';
$lang['loan_ledger_repayment'] = 'Repayment';
$lang['loan_ledger_schedule'] = 'Schedule (Inst / Due)';
$lang['loan_ledger_interest'] = 'Interest';
$lang['loan_ledger_penalty'] = 'Penalty';
$lang['loan_ledger_amount_paid'] = 'Amount Paid';
$lang['loan_ledger_advancement_lock_note_title'] = 'Advancement & lock of payment:';
$lang['loan_ledger_advancement_lock_note'] = 'When a member pays enough to clear the full remaining balance (advancement / early pay-off), the system records one repayment for that installment and marks all remaining schedule installments as closed—no further payments are accepted for that loan and the loan status becomes Closed. If there are no open installments left, the system does not allow new payments (payment is locked) and the loan is automatically set to Closed.';
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
$lang['report_loan_processing_fee_collection'] = 'Loan Processing Fee Collection';
$lang['report_loan_aging'] = 'Loan Aging Report';
$lang['report_loan_aging_status'] = 'Active or Accepted (disbursed) loans only';

// Loan Beginning Balances
$lang['loan_beginning_balance_list'] = 'Loan Beginning Balances';
$lang['loan_beginning_balance_create'] = 'Create Loan Beginning Balance';
$lang['loan_beginning_balance_edit'] = 'Edit Loan Beginning Balance';
$lang['loan_beginning_balance_btncreate'] = 'Create Beginning Balance';
$lang['loan_beginning_balance_member_id'] = 'Member ID';
$lang['loan_beginning_balance_loan_id'] = 'Loan ID (Optional)';
$lang['loan_beginning_balance_loan_product'] = 'Loan Product';
$lang['loan_beginning_balance_principal'] = 'Principal Balance';
$lang['loan_beginning_balance_interest'] = 'Interest Balance';
$lang['loan_beginning_balance_penalty'] = 'Penalty Balance';
$lang['loan_beginning_balance_total'] = 'Total Balance';
$lang['loan_beginning_balance_disbursement_date'] = 'Disbursement Date';
$lang['loan_beginning_balance_loan_amount'] = 'Loan Amount';
$lang['loan_beginning_balance_monthly_amort'] = 'Monthly Amortization';
$lang['loan_beginning_balance_last_date_paid'] = 'Last Date Paid';
$lang['loan_beginning_balance_term'] = 'Term (Months)';
$lang['loan_beginning_balance_create_success'] = 'Loan beginning balance created successfully';
$lang['loan_beginning_balance_create_fail'] = 'Failed to create loan beginning balance';
$lang['loan_beginning_balance_update_success'] = 'Loan beginning balance updated successfully';
$lang['loan_beginning_balance_update_fail'] = 'Failed to update loan beginning balance';
$lang['loan_beginning_balance_delete_success'] = 'Loan beginning balance deleted successfully';
$lang['loan_beginning_balance_delete_fail'] = 'Failed to delete loan beginning balance';
$lang['loan_beginning_balance_not_found'] = 'Loan beginning balance not found';
$lang['loan_beginning_balance_already_exists'] = 'Loan beginning balance already exists for this fiscal year, member and loan product';
$lang['loan_beginning_balance_amount_required'] = 'At least one balance amount (principal, interest, or penalty) must be greater than zero';
$lang['loan_beginning_balance_posted'] = 'Posted';
$lang['loan_beginning_balance_not_posted'] = 'Not Posted';
$lang['loan_beginning_balance_post'] = 'Post to General Ledger';
$lang['loan_beginning_balance_post_success'] = 'Loan beginning balance posted to General Ledger successfully';
$lang['loan_beginning_balance_post_fail'] = 'Failed to post loan beginning balance to General Ledger';
$lang['loan_beginning_balance_post_confirm'] = 'Are you sure you want to post this loan beginning balance to the General Ledger? This action cannot be undone.';
$lang['loan_beginning_balance_delete_confirm'] = 'Are you sure you want to delete this loan beginning balance';
$lang['loan_beginning_balance_no_edit'] = 'Already posted - Cannot edit';
$lang['loan_beginning_balance_already_posted'] = 'This loan beginning balance has already been posted to the General Ledger';
$lang['loan_beginning_balance_cannot_delete_posted'] = 'Cannot delete a loan beginning balance that has been posted to the General Ledger';
$lang['loan_beginning_balance_select_fiscal_year'] = 'Please select a fiscal year to view loan beginning balances';
$lang['loan_beginning_balance_member_not_found'] = 'Member not found';
$lang['loan_beginning_balance_product_not_found'] = 'Loan product not found';
$lang['loan_beginning_balance_activate'] = 'Activate as Loan';
$lang['loan_beginning_balance_activate_confirm'] = 'Activate this beginning balance as an Accepted loan with repayment schedule and loan ledger? General Ledger will not be posted again.';
$lang['loan_beginning_balance_activate_success'] = 'Beginning balance activated as loan %s. You can now collect repayments and view the loan ledger.';
$lang['loan_beginning_balance_activate_fail'] = 'Failed to activate beginning balance as a loan';
$lang['loan_beginning_balance_activate_must_post'] = 'Post this beginning balance to the General Ledger before activating it as a loan';
$lang['loan_beginning_balance_already_activated'] = 'This beginning balance is already activated as a loan';
$lang['loan_beginning_balance_activate_no_principal'] = 'Cannot activate: principal balance must be greater than zero';
$lang['loan_beginning_balance_activate_incomplete_terms'] = 'Cannot activate: set Term and/or Monthly Amortization on the beginning balance first';
$lang['loan_beginning_balance_cannot_void_activated'] = 'Cannot void a beginning balance that has already been activated as a loan';
$lang['loan_beginning_balance_activated'] = 'Activated';
$lang['loan_ledger_beginning_balance'] = 'Beginning Balance (Opening)';
$lang['export_to_excel'] = 'Export to Excel';

$lang['loan_schedule_none_yet'] = 'No repayment schedule has been created for this loan yet.';
$lang['loan_schedule_none_yet_note'] = 'The schedule is normally created when the loan is disbursed. For loans migrated into the system, generate it below using the date the first installment falls due.';
$lang['loan_schedule_start_date'] = 'First Installment Due Date';
$lang['loan_schedule_generate'] = 'Generate Schedule';
$lang['loan_schedule_generated'] = 'Repayment schedule generated with %d installment(s).';
$lang['loan_schedule_exists'] = 'This loan already has a repayment schedule.';
$lang['loan_schedule_not_released'] = 'A repayment schedule can only be generated for a released loan.';
$lang['loan_schedule_invalid_date'] = 'Please provide a valid first installment due date.';
$lang['loan_schedule_incomplete_terms'] = 'This loan has no installment count or installment amount, so a schedule cannot be generated.';
$lang['loan_schedule_generate_failed'] = 'Could not generate the repayment schedule. Please try again.';