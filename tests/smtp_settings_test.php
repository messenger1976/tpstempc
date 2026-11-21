<?php
/**
 * SMTP settings tests. No database, no network, and no outbound mail.
 *
 *   php tests/smtp_settings_test.php
 */
define('SMTP_SETTINGS_STANDALONE', true);
require dirname(__DIR__) . '/application/libraries/Smtp_settings.php';

$failures = 0;
function check($label, $condition) {
    global $failures;
    if ($condition) {
        echo "ok  $label\n";
        return;
    }
    $failures++;
    echo "FAIL $label\n";
}

class Smtp_memory_store {
    public $row;
    public $reads = 0;
    public $writes = 0;
    public function __construct($row = null) {
        $this->row = $row;
    }
    public function read_row() {
        $this->reads++;
        return $this->row;
    }
    public function write_row($data) {
        $this->writes++;
        $this->row = $data;
        return true;
    }
}

class Fake_email {
    public $initialized = null;
    public $initialize_calls = 0;
    public $from = null;
    public $from_calls = 0;
    public $subject;
    public $to;
    public $bcc = null;
    public $bcc_calls = 0;
    public $attachment = null;
    public $message;
    public $send_result = true;
    public $send_calls = 0;
    public $debugger_called = false;
    public function initialize($config) {
        $this->initialize_calls++;
        $this->initialized = $config;
        return $this;
    }
    public function from($address, $name = '') {
        $this->from_calls++;
        $this->from = array($address, $name);
        return $this;
    }
    public function subject($subject) { $this->subject = $subject; return $this; }
    public function to($to) { $this->to = $to; return $this; }
    public function bcc($bcc) { $this->bcc_calls++; $this->bcc = $bcc; return $this; }
    public function attach($path) { $this->attachment = $path; return $this; }
    public function message($message) { $this->message = $message; return $this; }
    public function send() { $this->send_calls++; return $this->send_result; }
    public function print_debugger() {
        $this->debugger_called = true;
        return 'AUTH LOGIN secret-password 334';
    }
}

class Smtp_view_session {
    public function flashdata($key) { return ''; }
}
class Smtp_view_harness {
    public $session;
    public function __construct() { $this->session = new Smtp_view_session(); }
    public function render($vars) {
        extract($vars, EXTR_SKIP);
        ob_start();
        include dirname(__DIR__) . '/application/views/setting/smtp_settings.php';
        return ob_get_clean();
    }
}
if (!function_exists('form_open')) {
    function form_open($action, $attributes = '') {
        return '<form method="post" action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '" ' . $attributes . '>';
    }
}
if (!function_exists('form_close')) {
    function form_close() { return '</form>'; }
}
if (!function_exists('current_lang')) {
    function current_lang() { return 'en'; }
}

$smtp = new Smtp_settings();
$keyBytes = base64_decode('AAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8=');
$key = array('ok' => true, 'key' => $keyBytes, 'reason' => '');
$noKey = array('ok' => false, 'key' => null, 'reason' => 'Install a server key before saving an SMTP password.');
$secret = 'mailbox-secret-not-for-html';
$otp = '904173';

check('admin gate accepts only a logged-in administrator', $smtp->allows(true, true) === true);
check('logged-out user is denied', $smtp->allows(false, true) === false);
check('non-admin is denied', $smtp->allows(true, false) === false);

$store = new Smtp_memory_store(array('smtp_pass' => 'v1:should-not-be-read', 'smtp_host' => 'secret.example'));
foreach (array('GET', 'POST') as $method) {
    $denied = $smtp->handle(array(
        'is_logged_in' => true,
        'is_admin' => false,
        'method' => $method,
        'post' => array('smtp_host' => 'evil.example', 'smtp_pass' => $secret, 'is_active' => '1'),
        'csrf_expected' => 'abcdefghijklmnopqrstuvwxyz',
        'csrf_posted' => 'abcdefghijklmnopqrstuvwxyz',
        'key' => $key,
    ), $store);
    check("non-admin $method is denied", $denied['action'] === 'deny');
}
check('non-admin GET and POST do not read settings', $store->reads === 0);
check('non-admin GET and POST do not save', $store->writes === 0);

$token = $smtp->new_csrf_token();
check('csrf token is non-empty', strlen($token) >= 32);
check('csrf rejects a missing token', $smtp->csrf_matches($token, '') === false);
check('csrf rejects a different token', $smtp->csrf_matches($token, strrev($token)) === false);
check('csrf accepts the issued token', $smtp->csrf_matches($token, $token) === true);

$admin = array(
    'is_logged_in' => true,
    'is_admin' => true,
    'method' => 'POST',
    'csrf_expected' => $token,
    'csrf_posted' => 'not-the-token',
    'key' => $key,
    'actor_id' => 7,
    'post' => array(
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_user' => 'mailbox@example.com',
        'smtp_pass' => $secret,
        'smtp_crypto' => 'tls',
        'smtp_timeout' => '20',
        'from_email' => 'notices@example.com',
        'from_name' => 'Example Cooperative',
        'is_active' => '1',
    ),
);
$csrfStore = new Smtp_memory_store($smtp->blank_row());
$csrfResult = $smtp->handle($admin, $csrfStore);
check('save without the csrf token is rejected', $csrfResult['action'] === 'csrf');
check('rejected csrf does not write', $csrfStore->writes === 0);

$badPosts = array(
    'bad host' => array('smtp_host' => 'https://smtp.example.com', 'smtp_port' => '587'),
    'bad port' => array('smtp_host' => 'smtp.example.com', 'smtp_port' => '70000'),
    'port zero' => array('smtp_host' => 'smtp.example.com', 'smtp_port' => '0'),
    'bad from address' => array('smtp_host' => 'smtp.example.com', 'smtp_port' => '587', 'from_email' => 'not-an-email'),
    'header break in host' => array('smtp_host' => "smtp.example.com\r\nBcc: x@example.com", 'smtp_port' => '587'),
    'bad encryption' => array('smtp_host' => 'smtp.example.com', 'smtp_port' => '587', 'smtp_crypto' => 'starttls'),
    'bad timeout' => array('smtp_host' => 'smtp.example.com', 'smtp_port' => '587', 'smtp_timeout' => '0'),
    'enabled without host' => array('smtp_host' => '', 'smtp_port' => '587', 'is_active' => '1', 'from_email' => 'notices@example.com', 'from_name' => 'Example'),
);
foreach ($badPosts as $label => $overrides) {
    $post = array_merge(array(
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_user' => 'mailbox@example.com',
        'smtp_pass' => '',
        'smtp_crypto' => 'tls',
        'smtp_timeout' => '20',
        'from_email' => 'notices@example.com',
        'from_name' => 'Example Cooperative',
        'is_active' => '',
    ), $overrides);
    $badStore = new Smtp_memory_store($smtp->blank_row());
    $bad = $smtp->handle(array(
        'is_logged_in' => true,
        'is_admin' => true,
        'method' => 'POST',
        'csrf_expected' => $token,
        'csrf_posted' => $token,
        'key' => $key,
        'post' => $post,
    ), $badStore);
    check("validation rejects $label", $bad['action'] === 'invalid' && $badStore->writes === 0);
}

$enabledWithoutPassword = $smtp->handle(array(
    'is_logged_in' => true,
    'is_admin' => true,
    'method' => 'POST',
    'csrf_expected' => $token,
    'csrf_posted' => $token,
    'key' => $noKey,
    'actor_id' => 3,
    'post' => array(
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_user' => '',
        'smtp_pass' => '',
        'smtp_crypto' => 'tls',
        'smtp_timeout' => '20',
        'from_email' => 'notices@example.com',
        'from_name' => 'Example Cooperative',
        'is_active' => '',
    ),
), new Smtp_memory_store($smtp->blank_row()));
check('disabled settings can be saved before a server key exists', $enabledWithoutPassword['action'] === 'saved');
check('saving does not enable SMTP by itself', (int) $enabledWithoutPassword['view']['is_active'] === 0);

$missingKeyStore = new Smtp_memory_store($smtp->blank_row());
$missingKey = $smtp->handle(array(
    'is_logged_in' => true,
    'is_admin' => true,
    'method' => 'POST',
    'csrf_expected' => $token,
    'csrf_posted' => $token,
    'key' => $noKey,
    'post' => array(
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_pass' => $secret,
        'from_email' => 'notices@example.com',
        'from_name' => 'Example Cooperative',
        'is_active' => '1',
    ),
), $missingKeyStore);
check('a password is refused when the server key is missing', $missingKey['action'] === 'invalid');
check('missing key does not write plaintext', $missingKeyStore->writes === 0);
$missingBlob = json_encode($missingKey);
check('missing-key error does not echo the password', strpos($missingBlob, $secret) === false);

$firstStore = new Smtp_memory_store($smtp->blank_row());
$first = $smtp->handle(array(
    'is_logged_in' => true,
    'is_admin' => true,
    'method' => 'POST',
    'csrf_expected' => $token,
    'csrf_posted' => $token,
    'key' => $key,
    'actor_id' => 9,
    'post' => array(
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_user' => 'mailbox@example.com',
        'smtp_pass' => $secret,
        'smtp_crypto' => 'tls',
        'smtp_timeout' => '25',
        'from_email' => 'notices@example.com',
        'from_name' => 'Example Cooperative',
        'is_active' => '',
    ),
), $firstStore);
$storedCipher = $firstStore->row['smtp_pass'];
check('first password save succeeds while SMTP stays off', $first['action'] === 'saved' && (int) $firstStore->row['is_active'] === 0);
check('stored password is ciphertext', is_string($storedCipher) && strpos($storedCipher, 'v1:') === 0);
check('stored password is not the plaintext', strpos($storedCipher, $secret) === false);
check('ciphertext decrypts to the password', $smtp->decrypt_password($storedCipher, $keyBytes) === $secret);
check('a different key cannot read the password', $smtp->decrypt_password($storedCipher, hash('sha256', 'another-server-key-material-32', true)) === false);
check('plaintext in the password column is not accepted', $smtp->decrypt_password($secret, $keyBytes) === false);

$shown = $smtp->handle(array(
    'is_logged_in' => true,
    'is_admin' => true,
    'method' => 'GET',
    'key' => $key,
), $firstStore);
$viewJson = json_encode($shown['view']);
check('edit form loads for an administrator', $shown['action'] === 'form');
check('form password is blank', $shown['view']['smtp_pass'] === '');
check('form reports that a password exists', (int) $shown['view']['has_password'] === 1);
check('form JSON does not contain the password or ciphertext', strpos($viewJson, $secret) === false && strpos($viewJson, 'v1:') === false);

$keepStore = new Smtp_memory_store($firstStore->row);
$keep = $smtp->handle(array(
    'is_logged_in' => true,
    'is_admin' => true,
    'method' => 'POST',
    'csrf_expected' => $token,
    'csrf_posted' => $token,
    'key' => $key,
    'actor_id' => 9,
    'post' => array(
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_user' => 'mailbox@example.com',
        'smtp_pass' => '',
        'smtp_crypto' => 'tls',
        'smtp_timeout' => '25',
        'from_email' => 'notices@example.com',
        'from_name' => 'Example Cooperative',
        'is_active' => '1',
    ),
), $keepStore);
check('blank password keeps the previous ciphertext', $keep['action'] === 'saved' && $keepStore->row['smtp_pass'] === $storedCipher);
check('blank password can still turn SMTP on', (int) $keepStore->row['is_active'] === 1);
check('kept ciphertext still decrypts', $smtp->decrypt_password($keepStore->row['smtp_pass'], $keyBytes) === $secret);

$updatedSecret = 'rotated-mailbox-secret';
$updateStore = new Smtp_memory_store($keepStore->row);
$update = $smtp->handle(array(
    'is_logged_in' => true,
    'is_admin' => true,
    'method' => 'POST',
    'csrf_expected' => $token,
    'csrf_posted' => $token,
    'key' => $key,
    'post' => array(
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '465',
        'smtp_user' => 'mailbox@example.com',
        'smtp_pass' => $updatedSecret,
        'smtp_crypto' => 'ssl',
        'smtp_timeout' => '20',
        'from_email' => 'notices@example.com',
        'from_name' => 'Example Cooperative',
        'is_active' => '1',
        'clear_password' => '1',
    ),
), $updateStore);
check('new password plus remove is rejected', $update['action'] === 'invalid' && $updateStore->writes === 0);

$replaceStore = new Smtp_memory_store($keepStore->row);
$replace = $smtp->handle(array(
    'is_logged_in' => true,
    'is_admin' => true,
    'method' => 'POST',
    'csrf_expected' => $token,
    'csrf_posted' => $token,
    'key' => $key,
    'post' => array(
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '465',
        'smtp_user' => 'mailbox@example.com',
        'smtp_pass' => $updatedSecret,
        'smtp_crypto' => 'ssl',
        'from_email' => 'notices@example.com',
        'from_name' => 'Example Cooperative',
        'is_active' => '1',
    ),
), $replaceStore);
check('a new password replaces the ciphertext', $replace['action'] === 'saved' && $replaceStore->row['smtp_pass'] !== $storedCipher);
check('replaced ciphertext decrypts to the new password', $smtp->decrypt_password($replaceStore->row['smtp_pass'], $keyBytes) === $updatedSecret);
check('form after replace still hides the password', $replace['view']['smtp_pass'] === '' && strpos(json_encode($replace['view']), $updatedSecret) === false);

$clearStore = new Smtp_memory_store($replaceStore->row);
$clear = $smtp->handle(array(
    'is_logged_in' => true,
    'is_admin' => true,
    'method' => 'POST',
    'csrf_expected' => $token,
    'csrf_posted' => $token,
    'key' => $key,
    'post' => array(
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '465',
        'smtp_user' => 'mailbox@example.com',
        'smtp_pass' => '',
        'smtp_crypto' => 'ssl',
        'from_email' => 'notices@example.com',
        'from_name' => 'Example Cooperative',
        'clear_password' => '1',
        'is_active' => '',
    ),
), $clearStore);
check('remove saved password clears the ciphertext', $clear['action'] === 'saved' && $clearStore->row['smtp_pass'] === null);
check('removing the password also leaves SMTP off when the box is unchecked', (int) $clearStore->row['is_active'] === 0);

$legacyRow = $smtp->blank_row();
$legacyPlan = $smtp->transport_plan($legacyRow, $key);
$legacyEmail = new Fake_email();
$legacyResult = $smtp->deliver($legacyEmail, $legacyPlan, 'Subject', '<p>code ' . $otp . '</p>', array('member@example.com'), 'audit@example.com', './uploads/quote.pdf');
check('disabled SMTP keeps the legacy transport', $legacyPlan['mode'] === 'legacy' && $legacyEmail->initialize_calls === 0);
check('legacy sender is unchanged', $legacyEmail->from === array(Smtp_settings::LEGACY_FROM_EMAIL, Smtp_settings::LEGACY_FROM_NAME));
check('legacy send keeps the bcc string and attachment', $legacyResult['ok'] === true && $legacyEmail->bcc === 'audit@example.com' && $legacyEmail->attachment === './uploads/quote.pdf');
check('legacy send does not expose the message in the result', strpos(json_encode($legacyResult), $otp) === false);

$activeRow = $replaceStore->row;
$activePlan = $smtp->transport_plan($activeRow, $key);
$activeEmail = new Fake_email();
$activeResult = $smtp->deliver(
    $activeEmail,
    $activePlan,
    'TAPSTEMCO Loan App code',
    '<p>Your code is ' . $otp . '</p>',
    array('member@example.com'),
    array('copy@example.com'),
    null
);
check('enabled SMTP selects the smtp transport', $activePlan['mode'] === 'smtp');
check('enabled SMTP initializes host, port, ssl, and username',
    $activeEmail->initialized['protocol'] === 'smtp'
    && $activeEmail->initialized['smtp_host'] === 'smtp.example.com'
    && (int) $activeEmail->initialized['smtp_port'] === 465
    && $activeEmail->initialized['smtp_crypto'] === 'ssl'
    && $activeEmail->initialized['smtp_user'] === 'mailbox@example.com'
    && $activeEmail->initialized['smtp_pass'] === $updatedSecret
);
check('enabled SMTP uses the saved From address', $activeEmail->from === array('notices@example.com', 'Example Cooperative'));
check('enabled SMTP keeps a bcc list', $activeEmail->bcc === array('copy@example.com') && $activeEmail->to === array('member@example.com'));
check('SMTP config does not disable certificate checks', !isset($activeEmail->initialized['verify_peer']) && !isset($activeEmail->initialized['smtp_conn_options']) && !isset($activeEmail->initialized['allow_self_signed']));
check('successful send result does not include the password or code', strpos(json_encode($activeResult), $updatedSecret) === false && strpos(json_encode($activeResult), $otp) === false);
check('debugger output is not requested', $activeEmail->debugger_called === false);

$activeEmail->send_result = false;
$failed = $smtp->deliver($activeEmail, $activePlan, 'Subject', 'code ' . $otp, array('member@example.com'), array(), null);
check('SMTP rejection is a failure', $failed['ok'] === false);
check('SMTP rejection does not send a second time', $activeEmail->send_calls === 2 && $activeEmail->initialize_calls === 2);
check('SMTP rejection does not switch back to the legacy sender', $activeEmail->from === array('notices@example.com', 'Example Cooperative'));
check('SMTP rejection error hides the code', strpos($failed['error'], $otp) === false && strpos($failed['error'], $updatedSecret) === false);

$broken = $activeRow;
$broken['smtp_host'] = '';
$brokenPlan = $smtp->transport_plan($broken, $key);
$brokenEmail = new Fake_email();
$brokenResult = $smtp->deliver($brokenEmail, $brokenPlan, 'Subject', 'code ' . $otp, array('member@example.com'), null, null);
check('enabled but incomplete SMTP fails closed', $brokenPlan['mode'] === 'fail' && $brokenResult['ok'] === false);
check('incomplete SMTP does not send', $brokenEmail->send_calls === 0 && $brokenEmail->initialize_calls === 0);

$undecryptable = $activeRow;
$undecryptablePlan = $smtp->transport_plan($undecryptable, $noKey);
$undecryptableEmail = new Fake_email();
$undecryptableResult = $smtp->deliver($undecryptableEmail, $undecryptablePlan, 'Subject', 'code ' . $otp, array('a@example.com'), null, null);
check('enabled SMTP with an unreadable password fails closed', $undecryptablePlan['mode'] === 'fail' && $undecryptableEmail->send_calls === 0);
check('unreadable-password error hides secrets', strpos($undecryptableResult['error'], $updatedSecret) === false && stripos($undecryptableResult['error'], 'AUTH') === false);

$plaintextRow = $activeRow;
$plaintextRow['smtp_pass'] = $updatedSecret;
check('a plaintext password cannot be written back', $smtp->persistent_row($plaintextRow, 1) === false);
$plaintextPlan = $smtp->transport_plan($plaintextRow, $key);
check('a plaintext password is not used as SMTP auth', $plaintextPlan['mode'] === 'fail');

check('safe error drops debugger text', $smtp->safe_error('AUTH LOGIN ' . $secret . ' code ' . $otp) === 'The message could not be sent.');

$example = file_get_contents(dirname(__DIR__) . '/application/config/smtp.key.example');
check('example key file is not a usable key', $smtp->parse_key_material($example) === false);
check('a short key is rejected', $smtp->parse_key_material('short-key') === false);
$parsed = $smtp->parse_key_material("AAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8=\n");
check('base64 key material parses to 32 bytes', $parsed !== false && strlen($parsed) === 32);

$root = dirname(__DIR__);
$controller = file_get_contents($root . '/application/controllers/setting.php');
$methodAt = strpos($controller, 'function smtp_settings()');
$method = substr($controller, $methodAt);
$allowAt = strpos($method, 'allows(');
$schemaAt = strpos($method, 'ensure_schema(');
$handleAt = strpos($method, '->handle(');
check('controller checks administrator before schema or save', $allowAt !== false && $allowAt < $schemaAt && $schemaAt < $handleAt);

$menu = file_get_contents($root . '/application/views/newmenu.php');
check('settings menu links Email / SMTP for administrators', strpos($menu, "activefunction == 'smtp_settings'") !== false && strpos($menu, '/setting/smtp_settings') !== false);
$menuItem = substr($menu, strpos($menu, 'smtp_settings'));
check('menu item is inside an administrator check', strrpos(substr($menu, 0, strpos($menu, "activefunction == 'smtp_settings'")), 'is_admin()') !== false);

$mailer = file_get_contents($root . '/application/libraries/maildata.php');
check('shared mailer asks for a transport plan', strpos($mailer, 'transport_plan') !== false && strpos($mailer, '->deliver(') !== false);
check('shared mailer does not print the email debugger', strpos($mailer, 'print_debugger') === false);

$sql = $smtp->schema_sql();
check('schema creates the table without dropping data', strpos($sql, 'CREATE TABLE IF NOT EXISTS') !== false && stripos($sql, 'DROP ') === false);
check('schema leaves SMTP disabled', strpos($sql, 'is_active') !== false && strpos($sql, 'DEFAULT 0') !== false && strpos($sql, 'DEFAULT 1') === false);

$setup = shell_exec('php ' . escapeshellarg($root . '/tools/smtp_settings_setup.php') . ' 2>&1');
check('setup script prints SQL and does not change a database', strpos($setup, 'CREATE TABLE IF NOT EXISTS') !== false && strpos($setup, 'No database changes were made.') !== false);

$harness = new Smtp_view_harness();
$html = $harness->render(array(
    'smtp' => array(
        'is_active' => 0,
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_user' => 'mailbox@example.com',
        'smtp_pass' => $secret,
        'has_password' => 1,
        'smtp_crypto' => 'tls',
        'smtp_timeout' => 20,
        'from_email' => 'notices@example.com',
        'from_name' => 'Example <Coop>',
        'configured' => 1,
        'key_ready' => 1,
        'key_message' => '',
        'status_label' => 'Saved, not enabled. Outgoing mail still uses the existing mail transport.',
    ),
    'smtp_errors' => array('Example <error>'),
    'csrf_token' => 'csrf-token-value',
    'message' => '',
    'warning' => '',
));
file_put_contents('/tmp/smtp_settings_preview.html', $html);
check('rendered password field is empty', strpos($html, 'name="smtp_pass"') !== false && strpos($html, 'value=""') !== false);
check('rendered page does not contain the password', strpos($html, $secret) === false);
check('rendered page does not contain ciphertext', strpos($html, 'v1:') === false);
check('enable box is unchecked by default', strpos($html, 'name="is_active"') !== false && strpos($html, 'name="is_active" value="1" checked') === false);
check('rendered page has no test-send control', stripos($html, 'Send Test') === false && strpos($html, 'smtp_token') !== false);
check('rendered page escapes the from name and errors', strpos($html, 'Example &lt;Coop&gt;') !== false && strpos($html, 'Example &lt;error&gt;') !== false);
check('rendered page shows host, port, encryption, username, from address, and timeout',
    strpos($html, 'name="smtp_host"') !== false
    && strpos($html, 'name="smtp_port"') !== false
    && strpos($html, 'name="smtp_crypto"') !== false
    && strpos($html, 'name="smtp_user"') !== false
    && strpos($html, 'name="from_email"') !== false
    && strpos($html, 'name="smtp_timeout"') !== false
);

if ($failures > 0) {
    echo "\n$failures failed.\n";
    exit(1);
}
echo "\nAll SMTP settings tests passed.\n";
exit(0);
