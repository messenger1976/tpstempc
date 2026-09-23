<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');



define('MAX_NUMBER_DAYS_OVERDUE_PENALT', 5);
define('API_TOKEN', '8155bc545f84d9652f1012ef2bdfb6eb');
define('PUSH_URL', '#');
define('API_URL', '#');
define('SENDER', 'VIKOBA PLUS');
define('REQUEST_GUARANTOR', 'Notification, MEMBER_NAME request you to be guarantor for loan# LOAN_NUMBER. Respond by Login to the system');
define('REQUEST_GUARANTOR_RESPOND', 'Notification, MEMBER_NAME ACTION  to be guarantor for loan# LOAN_NUMBER.');
define('NEW_LOAN', '255684610038,255715222132');
define('APROVE_LOAN', '255684610038,255715222132');
define('DISBURSE_LOAN', '255684610038,255715222132');

/*
|--------------------------------------------------------------------------
| Printed loan forms letterhead (TPSTEMPC-12 / TPSTEMPC-13)
|--------------------------------------------------------------------------
| Used by the Loan Forms tab and its PDF output when the matching value is
| not present in Company Information (companyinfo).
| TAPSTEMCO_FORM_EMPLOYER is only used when a member has no Office Address.
*/
define('TAPSTEMCO_FORM_COOP_NAME', 'Talibon Public School Teachers and Employees Multipurpose Cooperative (TAPSTEMCO)');
define('TAPSTEMCO_FORM_COOP_ADDRESS', 'Purok 1 North Road, San Jose, Talibon, Bohol');
define('TAPSTEMCO_FORM_REG_NO', '9520-07014825');
define('TAPSTEMCO_FORM_EMPLOYER', '');

/*
| File name of the cooperative crest used on the printed loan forms, inside the
| app logo/ folder (the same convention as companyinfo.logo). The form previews,
| the collection notice, the disbursement voucher and the loan form PDFs all read
| this value, so re-branding every printed loan form is a single edit.
*/
if (!defined('TAPSTEMCO_FORM_LOGO')) {
    define('TAPSTEMCO_FORM_LOGO', '1761642549tapstemco-logo.png');
}

/*
|--------------------------------------------------------------------------
| Loan penalty assessment
|--------------------------------------------------------------------------
| An installment whose repayment date is past its due date + grace period is
| charged the FULL penalty percentage ONCE on that installment:
|
|   method 2  penalty = penalt_percentage / 100 x (row principal + row interest)
|   method 1  penalty = penalt_percentage / 100 x (row principal)
|
| The penalty base is the unpaid installment, never the loan amount (Lending
| Policy wording: "2% of the unpaid monthly amortization including interest" -
| see ESKB 10_LOANS/LOAN_LENDING_POLICY_PENALTY_COMPLIANCE.md).
|
| Cooperative decision 2026-09-22:
|   1. The penalty is NEVER pro-rated. As soon as the repayment date is past the
|      grace end the whole percentage is charged - an installment 1 day past
|      grace pays the same penalty as one 29 days past grace.
|   2. The penalty does NOT grow while an installment stays unpaid. It is charged
|      once for that installment; two missed installments are two penalties, one
|      each. (The Amount Due panel therefore prints Penalty months = 1.)
|
| That is what TAPSTEMCO_PENALTY_PERIOD_DAYS = 0 means below ("once"), and it is
| the setting every PIN 105 product inherits (all nine store a blank penalty
| period). A product that explicitly stores a period of N days re-enables the
| escalating reading for that product: whole N-day periods past the grace end,
| rounded up, still never a fraction.
|
| TAPSTEMCO_PENALTY_PRORATE
|   TRUE  - the period factor is days past the grace end / the period, i.e. a
|           FRACTION of a penalty unit (the reading in force during 2026-09-22
|           until the decision above reverted it).
|   FALSE - whole periods only, rounded up, never a fraction. Restored on
|           2026-09-22 with the once rule, so a product that does set its own
|           period can never bill a fraction of it either.
|
| This affects future previews and postings only; posted penalties are not
| rewritten. Compare the once rule now in force against the escalating (whole
| period, growing) counterfactual at report_loan/loan_penalty_review.
*/
if (!defined('TAPSTEMCO_PENALTY_PRORATE')) {
    define('TAPSTEMCO_PENALTY_PRORATE', FALSE);
}

/**
 * Days per penalty period.
 *   0 = charge the penalty ONCE per overdue installment (system policy).
 *   N = charge another whole period each N days past the grace end.
 * Products that store their own penalt_period_days override this entirely.
 */
if (!defined('TAPSTEMCO_PENALTY_PERIOD_DAYS')) {
    define('TAPSTEMCO_PENALTY_PERIOD_DAYS', 0);
}

/*
|--------------------------------------------------------------------------
| Penalty / interest waiver approvals
|--------------------------------------------------------------------------
| Comma separated ion_auth group names allowed to approve a waiver.
| The 'admin' group is always allowed. The initiator of a waiver can never
| approve it, even when they belong to one of these groups.
*/
if (!defined('TAPSTEMCO_WAIVER_APPROVER_GROUPS')) {
    // Names must match ion_auth `groups`.`name` exactly. Verified against the
    // live groups table: the loan-approver group is spelled "Credit_Commitee"
    // (single 't'), and "Bookeeper" is stored with the double 'e'.
    define('TAPSTEMCO_WAIVER_APPROVER_GROUPS', 'admin,General_Manager,Credit_Commitee,Bookeeper,Accounts_Department');
}

/* End of file constants.php */
/* Location: ./application/config/constants.php */
