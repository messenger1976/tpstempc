<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//software name
$lang['software_name'] = 'VIKOBA PLUS';

//page title
$lang['page_home'] = 'Dashboard';
$lang['page_auth'] = 'Manage Users';
$lang['page_setting'] = 'Settings';
$lang['page_member'] = 'Members';
$lang['page_finance'] = 'Finance';
$lang['page_saving'] = 'Saving Accounts';
$lang['page_share'] = 'Member Shares';
$lang['page_contribution'] = 'Member Contribution';
$lang['page_customer'] = 'Customer';
$lang['page_loan'] = 'Loan Management';
$lang['page_report'] = 'Reports';
$lang['page_supplier'] = 'Supplier';


$lang['home'] = 'Home';


//button
$lang['button_add'] = 'Add';
$lang['button_edit'] = 'Edit';
$lang['button_view'] = 'View';
$lang['button_search'] = 'Search';
$lang['button_delete'] = 'Delete';
$lang['sno'] = 'S/No';

//action column
$lang['actioncolumn'] = 'Action';


//link title
$lang['setting_account'] = 'Settings';
$lang['setting_addaccount'] = 'Add New Company Profile';
$lang['seting_clientaccountlist'] = 'Company Profile List';
$lang['seting_accountinfo'] = 'Company  Information';
$lang['setting_share_setup'] = 'Share Setting';
$lang['saving_account_typelist'] = 'Saving Account Type';
$lang['contribution_minimum_setting'] = 'Contribution Amount';
$lang['contribution_minimum_setting1'] = 'Minimum Contribution Amount';
$lang['contribution_minimum_amount'] = 'Minimum Amount';
$lang['contribution_minimum_overdueamount'] = 'Monthly Overdue Charges';
$lang['contribution_minimum_success'] = 'Information saved successfully';
$lang['contribution_minimum_fail'] = 'Fail to save information';

//members link
$lang['members_home'] = 'Members Registration';
$lang['member_registration'] = 'Register New Member';
$lang['member_add_group'] = 'Add New Member Group';
$lang['member_list'] = 'Member List';
$lang['member_active'] = 'Active';
$lang['member_inactive'] = 'Inactive';
$lang['member_status'] = 'Status';
$lang['member_deactivated'] = 'Member deactivated';
$lang['member_activated'] = 'Member activated';

//input hint
$lang['select_default_text'] = 'SELECT';
$lang['row_per_page'] = 'Row per page';
$lang['hint_date'] = 'DD-MM-YYYY';

//assign previllege in group
$lang['privillege_heading'] = 'Group Privillege';
$lang['privillege_btn_save'] = 'Assign Previllege';
$lang['module'] = 'Module';
$lang['privillege_settings_success'] = 'Privillege successfully configured';


//finance link
$lang['finance_account_list'] = 'Account Chart List';
//

$lang['account_no'] = 'Account Number';
$lang['member_pid'] = 'System Member ID';
$lang['group_id'] = 'Group ID';
$lang['member_group_list'] = 'Member Group List';
$lang['data_not_found'] = 'No data found';

//saccoss accounts

$lang['member_saccos_saving_account_title'] = 'Saving Account Informations';
$lang['member_saccos_saving_account'] = 'Saving Account';
$lang['member_saccos_saving_account_type'] = 'Account Type';

$lang['alert_pid'] = 'Please, Fill System Member ID';
$lang['alert_member_id'] = 'Please, Fill Member ID';
$lang['please_wait'] = 'Please wait......';

//saving
$lang['create_saving_account'] = 'Create Account';
$lang['create_saving_account_another'] = 'Create another Account';
$lang['paymentmethod'] = 'Payment Method';
$lang['cheque_no'] = 'Cheque Number';
$lang['comment'] = 'Comment';
$lang['view_receipt'] = 'View Receipt';
$lang['saving_account_credit_debit'] = 'Deposit/Withdrawal';
$lang['saving_void_transaction'] = 'Void Transaction';


$lang['CR'] = 'CASH DEPOSIT';
$lang['DR'] = 'CASH WITHDRAWAL';
$lang['print_receipt'] = 'Print Receipt';
$lang['transaction_type'] = 'Transaction Type';
$lang['transaction_list'] = 'Transaction List';
$lang['transaction_details'] = 'Transaction Details';
$lang['transaction_date'] = 'Transaction Date';
$lang['saving_transaction_type_option'] = array('CR'=>'DEPOSIT','DR'=>'WITHDRAWAL');
$lang['balance'] = 'Balance';
$lang['amount'] = 'Amount';
$lang['customer_name'] = 'Customer Name';
$lang['record_btn'] = 'Record Information';
$lang['invalid_account'] = 'Invalid Account Number';
$lang['insufficient_balance'] = 'Insufficient Balance';
$lang['transaction_fail'] = 'Transaction fail';
$lang['next_deposit_withdrawal'] = 'Next Customer >>';
$lang['saving_transaction_search'] ='Search Transaction';
$lang['view_link'] ='View';
$lang['void_link'] ='Void';

// Automated savings interest posting
$lang['interest_posting'] = 'Interest Posting';
$lang['interest_posting_history'] = 'Interest Posting History';
$lang['interest_setup'] = 'Interest Setup';
$lang['interest_frequency'] = 'Interest Frequency';
$lang['interest_frequency_none'] = 'None (No Automatic Interest)';
$lang['interest_frequency_monthly'] = 'Monthly';
$lang['interest_frequency_quarterly'] = 'Quarterly';
$lang['interest_frequency_help'] = 'How often interest is computed and posted for this savings product (default for member accounts).';
$lang['interest_frequency_inherit'] = 'Use product default';
$lang['interest_frequency_account_help'] = 'Optional override for this account only. Leave as "Use product default" unless this member needs a different posting frequency.';
$lang['interest_frequency_override_label'] = 'Override';
$lang['interest_posting_frequency'] = 'Posting Frequency';
$lang['interest_posting_frequency_help'] = 'Run Monthly or Quarterly. Only accounts whose effective frequency matches (product default or account override) are included.';
$lang['interest_product_default'] = 'Product default';
$lang['interest_invalid_posting_frequency'] = 'Please select Monthly or Quarterly posting frequency.';
$lang['interest_no_accounts_for_frequency'] = 'No active accounts match this posting frequency for the selected account type. Check product defaults or per-account overrides.';
$lang['interest_basis'] = 'Computation Basis';
$lang['interest_basis_adb'] = 'Average Daily Balance';
$lang['interest_basis_lowest'] = 'Lowest Balance';
$lang['interest_basis_eop'] = 'End-of-Period Balance';
$lang['interest_basis_help'] = 'The balance used to compute interest. Average Daily Balance is the modern cooperative standard.';
$lang['interest_min_balance'] = 'Minimum Balance to Earn Interest';
$lang['interest_min_balance_help'] = 'Accounts whose computed base balance is below this amount earn no interest for the period.';
$lang['interest_account_type'] = 'Savings Account Type';
$lang['interest_period_month'] = 'Period (Month)';
$lang['interest_period_quarter'] = 'Period (Quarter)';
$lang['interest_period'] = 'Period';
$lang['interest_period_completed_help'] = 'Only fully completed periods can be posted.';
$lang['interest_preview_btn'] = 'Compute / Preview Interest';
$lang['interest_post_btn'] = 'Post Selected Interest';
$lang['interest_post_confirm'] = 'Post interest for the selected accounts? This will credit each account and post to the General Ledger.';
$lang['interest_preview_title'] = 'Interest Computation Preview';
$lang['interest_base_balance'] = 'Base Balance';
$lang['interest_days'] = 'Days';
$lang['interest_amount'] = 'Interest Amount';
$lang['interest_status'] = 'Status';
$lang['interest_eligible'] = 'Eligible';
$lang['interest_already_posted'] = 'Already Posted';
$lang['interest_below_min_balance'] = 'Below Minimum Balance';
$lang['interest_zero'] = 'Zero Interest';
$lang['interest_total_eligible'] = 'Total Eligible Interest';
$lang['interest_posting_results'] = 'Interest Posting Results';
$lang['interest_result_posted'] = 'Posted';
$lang['interest_result_total'] = 'Total Interest';
$lang['interest_result_skipped'] = 'Skipped';
$lang['interest_result_failed'] = 'Failed';
$lang['interest_invalid_account_type'] = 'Invalid or unconfigured savings account type';
$lang['interest_invalid_period'] = 'Invalid period. Only fully completed months/quarters can be posted.';
$lang['interest_no_accounts_selected'] = 'No accounts selected for posting';
$lang['interest_no_accounts_found'] = 'No active accounts found for this savings account type';
$lang['interest_no_types_configured'] = 'No savings account types are configured for automatic interest. Set the Interest Frequency under Settings > Saving Account Types.';
$lang['interest_all_account_types'] = 'All Account Types';
$lang['interest_posted_on'] = 'Posted On';
$lang['interest_status_posted'] = 'Posted';
$lang['interest_status_voided'] = 'Voided';
$lang['interest_no_history'] = 'No interest postings recorded yet';
$lang['interest_to'] = 'to';
$lang['receipt_no'] = 'Receipt';
$lang['member_id'] = 'Member ID';
$lang['saving_void_confirm'] = 'Confirm Void Transaction';
$lang['saving_void_transaction_warning'] = 'This action will reverse this transaction by creating a counter-entry. This action cannot be undone.';
$lang['saving_void_success'] = 'Transaction voided successfully';
$lang['saving_void_already_done'] = 'This transaction has already been voided';
$lang['saving_void_not_allowed'] = 'Only Deposit/Withdrawal transactions can be voided in this module';
$lang['saving_void_reason_required'] = 'Void reason is required';
$lang['enter_reason_for_void'] = 'Enter the reason for voiding this transaction';
$lang['void_reason_help'] = 'This reason will be recorded in the system for audit purposes.';
$lang['confirm_void_transaction'] = 'Confirm Void Transaction';
$lang['saving_void_final_confirm'] = 'Are you absolutely sure you want to void this transaction?';
$lang['invalid_receipt_number'] = 'Invalid receipt number';
$lang['date_from'] = 'From Date';
$lang['date_to'] = 'To Date';
$lang['search_key'] = 'Search';
$lang['search'] = 'Search';
$lang['reset'] = 'Reset';
$lang['name'] = 'Name';
$lang['payment_method'] = 'Payment Method';
$lang['records_per_page'] = 'Records Per Page';
$lang['no_record_found'] = 'No record found';
$lang['index_action_th'] = 'Action';
$lang['action'] = 'Action';
$lang['member_name'] = 'Member Name';
$lang['cheque_number'] = 'Cheque Number';
$lang['warning'] = 'Warning';

//label
$lang['index_account'] = 'Account No.';
$lang['index_receipt'] = 'Receipt No.';
$lang['index_name'] = 'Account Name.';
$lang['index_transtype'] = 'Trans Type.';
$lang['index_transmethod'] = 'Method';
$lang['index_chequeno'] = 'Cheque No';
$lang['index_amount'] = 'Amount';
$lang['index_trans_date'] = 'Trans. Date';

$lang['save_info_btn'] = 'Save Information';


//shares
$lang['share_buy'] = 'Buy Shares';
$lang['share_transfer'] = 'Sell/Transfer Share';
$lang['share_transaction_search'] = 'Share transaction Search';
$lang['share_payment_info_label'] = 'Share Payment Information';
$lang['share_max_reached'] = 'Maximum number of shares reached';
$lang['refund_share'] = 'Refund Share';
$lang['share_refund_info_label'] = 'Share Refund Information';
$lang['share_not_found'] = 'No share found for selected member';
$lang['share_amount_exceed_amount_available'] = 'Amount exceed total amount of your share available';


$lang['balance_share'] = 'Balance Share';
$lang['total_shareamount'] = 'Total Paid Amount';
$lang['max_share'] = 'Maximum Share';
$lang['min_share'] = 'Minimum Share';
$lang['remainshare'] = 'Maximum Remaning Share';
$lang['BUY'] = 'BUY SHARES';
$lang['SELL'] = 'SELL SHARES';

$lang['share_trans_fail'] = 'Fail to record information';
$lang['share_trans_success'] = 'Information successfully recorded';

$lang['saving_account_edit']='Edit Account Information';
$lang['account_name']='Account Name';
$lang['account_description']='Description';
$lang['account_charge']='Monthly Fee';
$lang['account_min_amount']='Minimum Amount';
$lang['saving_account_process_success']='Information saved successfully';
$lang['saving_account_process_fail']='Fail to save information';


//contribution
$lang['contribute_setting'] = 'Contribution Setting';
$lang['contribution_payment'] = 'Contribution Payment';
$lang['contribution_refund'] = 'Contribution Refund';
$lang['contribution_transaction'] = 'Contribution Transaction';
$lang['CONTRIBUTE'] = 'CONTRIBUTE';
$lang['REFUND'] = 'REFUND';

// customer
$lang['customer'] = 'Customer';

//sales item invoice
$lang['items_invoice'] = 'Sales/Purchase Invoice Items';


//tax
$lang['tax_code_list'] = 'Tax Code';
$lang['global_setting'] = 'Global Settings';
$lang['loan_product_list'] = 'Loan Product';


//loan
$lang['loan_application'] = 'New Loan Application';
$lang['loan_evaluation'] = 'Loan Evaluation';
$lang['loan_approval'] = 'Loan Approval';
$lang['loan_disbursement'] = 'Loan Disbursement';
$lang['loan_repayment'] = 'Loan Repayment';
$lang['loan_viewlist'] = 'Loan List';
$lang['loan_report'] = 'Loan Reports';


//report
$lang['loan_report_list'] = 'Loan Report List';
$lang['loan_report_collection'] = 'Loan Collection';
$lang['loan_report_statement'] = 'Loan Statement';
$lang['loan_report_statement'] = 'Loan Transaction';

$lang['member_report'] ='Member Report';
$lang['member_report_list'] ='Member List';
$lang['member_join_date_to'] = 'Joining up to';
$lang['member_join_date_from'] = 'Joining from';
$lang['member_report_btn'] = 'Generate Report';
$lang['member_memberlist_default'] = 'Click Generate button without specify date to generate report for all members';
$lang['member_list_report'] ='MEMBER LIST REPORT';
$lang['customersales_quote'] ='Sales Quote';

$lang['customersales_invoice'] ='Sales Invoice';



$lang['supplier_list'] = 'Supplier';
$lang['supplier_purchase_order'] = 'Purchase Orders';
$lang['supplier_purchase_invoice'] = 'Purchase Invoice';


//reports
$lang['ledger_transaction'] = 'General Ledger Transactions';
$lang['ledger_transaction_summary'] = 'General Ledger Summary';
$lang['ledger_trial_balance'] = 'Trial Balance';



//Journal Entry
$lang['journalentry'] = 'Journal Entry';
$lang['journal_entry_list'] = 'Journal Entry List';
$lang['journal_entry_view'] = 'View Journal Entry';
$lang['journal_entry_no'] = 'Entry No';
$lang['journal_entry_line_items'] = 'Line Items';
$lang['journal_entry_status_posted'] = 'Posted';
$lang['journal_entry_status_draft'] = 'Draft';
$lang['journal_entry_review'] = 'Journal Entry Review & Approval';
$lang['journal_entry_delete_success'] = 'Journal entry deleted successfully';
$lang['journal_entry_delete_fail'] = 'Failed to delete journal entry';
$lang['journal_entry_not_found'] = 'Journal entry not found';
$lang['journal_entry_cannot_delete_posted'] = 'This journal entry has been posted to the General Ledger and cannot be deleted.';
$lang['journal_entry_delete_confirm_text'] = 'This will permanently delete the journal entry and its line items.';

// Application name
$lang['app_name'] = 'COOP';

// Payment Method
$lang['payment_method_list'] = 'Orodha ya Njia za Malipo';

?>
