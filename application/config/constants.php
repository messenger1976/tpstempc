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
|--------------------------------------------------------------------------
| Loan penalty assessment
|--------------------------------------------------------------------------
| TAPSTEMCO_PENALTY_PRORATE
|   TRUE  - penalty is pro-rated over 30-day periods counted from the end of
|           the grace period (15 days past grace = half a month).
|   FALSE - policy mode: after the grace period, charge a full monthly penalty
|           period; a partial month is not prorated.
| The cooperative Lending Policy specifies 5 days grace and 2% of the unpaid
| monthly amortization including interest. Product-specific settings still
| override the grace/period defaults when explicitly configured. This affects
| future previews and postings only; posted penalties are not rewritten.
*/
if (!defined('TAPSTEMCO_PENALTY_PRORATE')) {
    define('TAPSTEMCO_PENALTY_PRORATE', FALSE);
}

/**
 * Days in the pro-rated penalty period (30-day month convention).
 */
if (!defined('TAPSTEMCO_PENALTY_PERIOD_DAYS')) {
    define('TAPSTEMCO_PENALTY_PERIOD_DAYS', 30);
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
