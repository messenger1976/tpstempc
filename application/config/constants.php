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



define('MAX_NUMBER_DAYS_OVERDUE_PENALT', 3);
define('API_TOKEN', '8155bc545f84d9652f1012ef2bdfb6eb');
define('PUSH_URL', '#');
define('API_URL', '#');
define('SENDER', 'VIKOBA PLUS');
define('REQUEST_GUARANTOR', 'Notification, MEMBER_NAME request you to be guarantor for loan# LOAN_NUMBER. Respond by Login to the system');
define('REQUEST_GUARANTOR_RESPOND', 'Notification, MEMBER_NAME ACTION  to be guarantor for loan# LOAN_NUMBER.');
define('NEW_LOAN', '255684610038,255715222132');
define('APROVE_LOAN', '255684610038,255715222132');
define('DISBURSE_LOAN', '255684610038,255715222132');

/* End of file constants.php */
/* Location: ./application/config/constants.php */
