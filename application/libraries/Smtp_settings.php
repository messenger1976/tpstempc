<?php
/**
 * SMTP settings used by the shared Maildata sender.
 *
 * Passwords are encrypted with AES-256-GCM. The key is read from the
 * TAPSTEMCO_SMTP_KEY environment variable or from application/config/smtp.key.
 * It is never stored in the database or in this file. If the key is missing,
 * a new password is refused instead of being saved in plaintext.
 *
 * SMTP is not used unless an administrator explicitly enables it. While it
 * is off, Maildata keeps the existing CodeIgniter Email transport.
 */
if (!defined('BASEPATH') && !defined('SMTP_SETTINGS_STANDALONE')) {
    exit('No direct script access allowed');
}

class Smtp_settings {

    const TABLE = 'email_smtp_settings';
    const KEY_ENV = 'TAPSTEMCO_SMTP_KEY';
    const KEY_PLACEHOLDER = 'REPLACE_WITH_OUTPUT_OF_THE_COMMAND_ABOVE';
    const LEGACY_FROM_EMAIL = 'info@helmatechnologies.com';
    const LEGACY_FROM_NAME = 'VIKOBA PLUS';

    /**
     * Administrator group check used by the settings page.
     * Matches ion_auth->is_admin() (config admin_group), which is how other
     * administrator-only settings pages are gated.
     */
    public function allows($is_logged_in, $is_admin) {
        return (bool) $is_logged_in && (bool) $is_admin;
    }

    public function new_csrf_token() {
        $bytes = $this->random_bytes(16);
        if ($bytes === false) {
            return '';
        }
        return bin2hex($bytes);
    }

    public function csrf_matches($expected, $posted) {
        $expected = (string) $expected;
        $posted = (string) $posted;
        if ($expected === '' || $posted === '' || strlen($expected) < 16) {
            return false;
        }
        return function_exists('hash_equals') ? hash_equals($expected, $posted) : ($expected === $posted);
    }

    public function schema_sql() {
        return "CREATE TABLE IF NOT EXISTS `email_smtp_settings` (
  `id` tinyint(3) unsigned NOT NULL,
  `smtp_host` varchar(255) NOT NULL DEFAULT '',
  `smtp_port` smallint(5) unsigned DEFAULT NULL,
  `smtp_user` varchar(255) NOT NULL DEFAULT '',
  `smtp_pass` text NULL COMMENT 'AES-256-GCM ciphertext; never plaintext',
  `smtp_crypto` varchar(10) NOT NULL DEFAULT 'tls',
  `smtp_timeout` smallint(5) unsigned NOT NULL DEFAULT 20,
  `from_email` varchar(255) NOT NULL DEFAULT '',
  `from_name` varchar(255) NOT NULL DEFAULT '',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    }

    public function blank_row() {
        return array(
            'id' => 1,
            'smtp_host' => '',
            'smtp_port' => null,
            'smtp_user' => '',
            'smtp_pass' => null,
            'smtp_crypto' => 'tls',
            'smtp_timeout' => 20,
            'from_email' => '',
            'from_name' => '',
            'is_active' => 0,
            'updated_by' => null,
            'updated_at' => null,
        );
    }

    public function normalize_row($row) {
        $blank = $this->blank_row();
        if (is_object($row)) {
            $row = get_object_vars($row);
        }
        if (!is_array($row)) {
            return $blank;
        }
        $out = $blank;
        $out['smtp_host'] = $this->clean_line(isset($row['smtp_host']) ? $row['smtp_host'] : '');
        $port = isset($row['smtp_port']) ? $row['smtp_port'] : null;
        $out['smtp_port'] = ($port === null || $port === '') ? null : (int) $port;
        $out['smtp_user'] = $this->clean_line(isset($row['smtp_user']) ? $row['smtp_user'] : '');
        $pass = isset($row['smtp_pass']) ? $row['smtp_pass'] : null;
        $out['smtp_pass'] = ($pass === null || $pass === '') ? null : (string) $pass;
        $crypto = isset($row['smtp_crypto']) ? (string) $row['smtp_crypto'] : 'tls';
        if ($crypto === 'none') {
            $crypto = '';
        }
        $out['smtp_crypto'] = in_array($crypto, array('tls', 'ssl', ''), true) ? $crypto : 'tls';
        $timeout = isset($row['smtp_timeout']) ? (int) $row['smtp_timeout'] : 20;
        $out['smtp_timeout'] = ($timeout >= 1 && $timeout <= 120) ? $timeout : 20;
        $out['from_email'] = $this->clean_line(isset($row['from_email']) ? $row['from_email'] : '');
        $out['from_name'] = $this->clean_name(isset($row['from_name']) ? $row['from_name'] : '');
        $out['is_active'] = !empty($row['is_active']) ? 1 : 0;
        $out['updated_by'] = isset($row['updated_by']) ? $row['updated_by'] : null;
        $out['updated_at'] = isset($row['updated_at']) ? $row['updated_at'] : null;
        return $out;
    }

    /**
     * Values safe to put in HTML or JSON. The password and ciphertext are omitted.
     */
    public function present($row, $keyStatus) {
        $row = $this->normalize_row($row);
        $keyStatus = is_array($keyStatus) ? $keyStatus : array('ok' => false, 'reason' => '');
        $hasPassword = ($row['smtp_pass'] !== null && $row['smtp_pass'] !== '');
        return array(
            'is_active' => (int) $row['is_active'],
            'smtp_host' => $row['smtp_host'],
            'smtp_port' => $row['smtp_port'] === null ? '' : (string) (int) $row['smtp_port'],
            'smtp_user' => $row['smtp_user'],
            'smtp_pass' => '',
            'has_password' => $hasPassword ? 1 : 0,
            'smtp_crypto' => $row['smtp_crypto'],
            'smtp_timeout' => (int) $row['smtp_timeout'],
            'from_email' => $row['from_email'],
            'from_name' => $row['from_name'],
            'configured' => $this->is_configured($row) ? 1 : 0,
            'key_ready' => !empty($keyStatus['ok']) ? 1 : 0,
            'key_message' => empty($keyStatus['ok']) ? (isset($keyStatus['reason']) ? (string) $keyStatus['reason'] : '') : '',
            'status_label' => $this->status_label($row, $keyStatus),
        );
    }

    public function is_configured($row) {
        $row = $this->normalize_row($row);
        return $row['smtp_host'] !== ''
            && $this->valid_host($row['smtp_host'])
            && $row['smtp_port'] !== null
            && (int) $row['smtp_port'] >= 1
            && (int) $row['smtp_port'] <= 65535
            && $this->valid_email($row['from_email'])
            && $row['from_name'] !== ''
            && in_array($row['smtp_crypto'], array('tls', 'ssl', ''), true);
    }

    public function status_label($row, $keyStatus) {
        $row = $this->normalize_row($row);
        $keyStatus = is_array($keyStatus) ? $keyStatus : array();
        if ((int) $row['is_active'] !== 1) {
            if ($this->is_configured($row)) {
                return 'Saved, not enabled. Outgoing mail still uses the existing mail transport.';
            }
            return 'Not enabled. Outgoing mail still uses the existing mail transport.';
        }
        if (!$this->is_configured($row)) {
            return 'SMTP is enabled but incomplete. Messages will not be sent until the required fields are saved.';
        }
        if ($row['smtp_pass'] !== null && $row['smtp_pass'] !== '' && empty($keyStatus['ok'])) {
            return 'SMTP is enabled but the server key cannot read the saved password. Messages will not be sent.';
        }
        return 'SMTP is enabled. Outgoing mail uses these settings and will not fall back if sending fails.';
    }

    /**
     * GET shows the form. POST validates and, when valid, asks the store to write.
     * Non-administrators are rejected before the store is touched.
     */
    public function handle($request, $store) {
        $request = is_array($request) ? $request : array();
        if (!$this->allows(!empty($request['is_logged_in']), !empty($request['is_admin']))) {
            return array('action' => 'deny', 'errors' => array(), 'view' => null);
        }
        $key = (isset($request['key']) && is_array($request['key'])) ? $request['key'] : array('ok' => false, 'key' => null, 'reason' => '');
        $method = isset($request['method']) ? strtoupper((string) $request['method']) : 'GET';
        if ($method !== 'POST') {
            return array(
                'action' => 'form',
                'errors' => array(),
                'view' => $this->present($store->read_row(), $key),
            );
        }
        $existing = $store->read_row();
        if (!$this->csrf_matches(
            isset($request['csrf_expected']) ? $request['csrf_expected'] : '',
            isset($request['csrf_posted']) ? $request['csrf_posted'] : ''
        )) {
            return array(
                'action' => 'csrf',
                'errors' => array('The form expired. Please try again.'),
                'view' => $this->present($existing, $key),
            );
        }
        $merged = $this->merge(
            $existing,
            isset($request['post']) && is_array($request['post']) ? $request['post'] : array(),
            $key,
            isset($request['actor_id']) ? $request['actor_id'] : 0
        );
        if (empty($merged['ok'])) {
            return array('action' => 'invalid', 'errors' => $merged['errors'], 'view' => $merged['view']);
        }
        if (!$store->write_row($merged['row'])) {
            return array(
                'action' => 'invalid',
                'errors' => array('The settings could not be saved.'),
                'view' => $merged['view'],
            );
        }
        return array('action' => 'saved', 'errors' => array(), 'view' => $merged['view']);
    }

    public function merge($existing, $post, $keyStatus, $actorId) {
        $existing = $this->normalize_row($existing);
        $keyStatus = is_array($keyStatus) ? $keyStatus : array('ok' => false, 'key' => null, 'reason' => '');
        $errors = array();

        $hostRaw = isset($post['smtp_host']) ? (string) $post['smtp_host'] : '';
        $userRaw = isset($post['smtp_user']) ? (string) $post['smtp_user'] : '';
        $fromEmailRaw = isset($post['from_email']) ? (string) $post['from_email'] : '';
        $fromNameRaw = isset($post['from_name']) ? (string) $post['from_name'] : '';
        $host = $this->clean_line($hostRaw);
        $portRaw = trim((string) (isset($post['smtp_port']) ? $post['smtp_port'] : ''));
        $user = $this->clean_line($userRaw);
        $pass = isset($post['smtp_pass']) ? (string) $post['smtp_pass'] : '';
        $crypto = isset($post['smtp_crypto']) ? trim((string) $post['smtp_crypto']) : 'tls';
        $timeoutRaw = trim((string) (isset($post['smtp_timeout']) ? $post['smtp_timeout'] : ''));
        $fromEmail = $this->clean_line($fromEmailRaw);
        $fromName = $this->clean_name($fromNameRaw);
        $active = !empty($post['is_active']) ? 1 : 0;
        $clear = !empty($post['clear_password']);

        if ($crypto === 'none') {
            $crypto = '';
        }
        if (!in_array($crypto, array('tls', 'ssl', ''), true)) {
            $errors[] = 'Encryption must be TLS, SSL, or None.';
            $crypto = 'tls';
        }
        if ($this->has_header_break($hostRaw) || ($host !== '' && !$this->valid_host($host))) {
            $errors[] = 'SMTP host must be a hostname or IPv4 address, without a scheme or spaces.';
        } elseif ($active && $host === '') {
            $errors[] = 'SMTP host is required while SMTP is enabled.';
        }

        $port = null;
        if ($portRaw === '') {
            if ($active) {
                $errors[] = 'SMTP port is required while SMTP is enabled.';
            }
        } elseif (!preg_match('/^\d+$/', $portRaw) || (int) $portRaw < 1 || (int) $portRaw > 65535) {
            $errors[] = 'SMTP port must be a number from 1 to 65535.';
        } else {
            $port = (int) $portRaw;
        }

        if ($this->has_header_break($userRaw) || strlen($user) > 255) {
            $errors[] = 'SMTP username is not valid.';
            $user = '';
        }
        if (strlen($pass) > 512 || strpos($pass, "\0") !== false || $this->has_header_break($pass)) {
            $errors[] = 'SMTP password is not valid.';
            $pass = '';
        }

        $timeout = 20;
        if ($timeoutRaw === '') {
            $timeout = 20;
        } elseif (!preg_match('/^\d+$/', $timeoutRaw) || (int) $timeoutRaw < 1 || (int) $timeoutRaw > 120) {
            $errors[] = 'Timeout must be a number from 1 to 120 seconds.';
        } else {
            $timeout = (int) $timeoutRaw;
        }

        if ($this->has_header_break($fromEmailRaw) || ($fromEmail !== '' && !$this->valid_email($fromEmail))) {
            $errors[] = 'From address must be a single email address.';
        } elseif ($active && $fromEmail === '') {
            $errors[] = 'From address is required while SMTP is enabled.';
        }
        if ($this->has_header_break($fromNameRaw) || strlen($fromName) > 255) {
            $errors[] = 'From name is not valid.';
            $fromName = '';
        } elseif ($active && $fromName === '') {
            $errors[] = 'From name is required while SMTP is enabled.';
        }
        if ($clear && $pass !== '') {
            $errors[] = 'Enter a new password or remove the saved password, not both.';
        }

        $cipher = $existing['smtp_pass'];
        if ($clear && $pass === '') {
            $cipher = null;
        } elseif ($pass !== '') {
            if (empty($keyStatus['ok']) || empty($keyStatus['key'])) {
                $reason = !empty($keyStatus['reason']) ? $keyStatus['reason'] : 'The server encryption key is not installed.';
                $errors[] = $reason . ' The password was not saved.';
            } else {
                $encrypted = $this->encrypt_password($pass, $keyStatus['key']);
                if ($encrypted === false) {
                    $errors[] = 'The password could not be encrypted. It was not saved.';
                } else {
                    $cipher = $encrypted;
                }
            }
        }

        if ($active && $cipher !== null && $cipher !== '') {
            $check = $this->decrypt_password($cipher, empty($keyStatus['key']) ? null : $keyStatus['key']);
            if ($check === false || $check === '') {
                $errors[] = 'SMTP cannot be enabled because the saved password cannot be read with the server key.';
            }
        }

        $draft = $existing;
        $draft['smtp_host'] = $host;
        $draft['smtp_port'] = $port;
        $draft['smtp_user'] = $user;
        $draft['smtp_crypto'] = $crypto;
        $draft['smtp_timeout'] = $timeout;
        $draft['from_email'] = $fromEmail;
        $draft['from_name'] = $fromName;
        $draft['is_active'] = $active;
        $draft['smtp_pass'] = $cipher;

        $view = $this->present($draft, $keyStatus);
        if ($errors) {
            return array('ok' => false, 'errors' => $errors, 'view' => $view);
        }

        $stored = $this->persistent_row($draft, $actorId);
        if ($stored === false) {
            return array(
                'ok' => false,
                'errors' => array('The password could not be stored safely. Nothing was saved.'),
                'view' => $view,
            );
        }
        return array('ok' => true, 'errors' => array(), 'row' => $stored, 'view' => $this->present($stored, $keyStatus));
    }

    /**
     * Columns written to the database. Refuses anything that is not ciphertext.
     */
    public function persistent_row($row, $actorId) {
        $row = $this->normalize_row($row);
        if ($row['smtp_pass'] !== null && strpos((string) $row['smtp_pass'], 'v1:') !== 0) {
            return false;
        }
        return array(
            'smtp_host' => $row['smtp_host'],
            'smtp_port' => $row['smtp_port'],
            'smtp_user' => $row['smtp_user'],
            'smtp_pass' => $row['smtp_pass'],
            'smtp_crypto' => $row['smtp_crypto'],
            'smtp_timeout' => (int) $row['smtp_timeout'],
            'from_email' => $row['from_email'],
            'from_name' => $row['from_name'],
            'is_active' => (int) $row['is_active'] === 1 ? 1 : 0,
            'updated_by' => (int) $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        );
    }

    /**
     * null  = SMTP is off; caller must keep the existing transport.
     * fail  = SMTP is on but cannot be used; caller must not fall back.
     * smtp  = initialize CodeIgniter Email with this config, then send once.
     */
    public function transport_plan($row, $keyStatus) {
        $row = $this->normalize_row($row);
        $keyStatus = is_array($keyStatus) ? $keyStatus : array();
        if ((int) $row['is_active'] !== 1) {
            return array('mode' => 'legacy');
        }
        if (!$this->is_configured($row)) {
            return array(
                'mode' => 'fail',
                'error' => 'SMTP is turned on but the settings are incomplete. The message was not sent.',
            );
        }
        $password = '';
        if ($row['smtp_pass'] !== null && $row['smtp_pass'] !== '') {
            $password = $this->decrypt_password($row['smtp_pass'], isset($keyStatus['key']) ? $keyStatus['key'] : null);
            if ($password === false || $password === '') {
                return array(
                    'mode' => 'fail',
                    'error' => 'SMTP is turned on but the saved password could not be read. The message was not sent.',
                );
            }
        }
        $crypto = $row['smtp_crypto'] === 'none' ? '' : (string) $row['smtp_crypto'];
        if (!in_array($crypto, array('tls', 'ssl', ''), true)) {
            return array(
                'mode' => 'fail',
                'error' => 'SMTP is turned on but the encryption setting is not valid. The message was not sent.',
            );
        }
        return array(
            'mode' => 'smtp',
            'from_email' => $row['from_email'],
            'from_name' => $row['from_name'],
            'initialize' => array(
                'protocol' => 'smtp',
                'smtp_host' => $row['smtp_host'],
                'smtp_port' => (int) $row['smtp_port'],
                'smtp_user' => $row['smtp_user'],
                'smtp_pass' => $password,
                'smtp_crypto' => $crypto,
                'smtp_timeout' => max(1, (int) $row['smtp_timeout']),
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'crlf' => "\r\n",
                'wordwrap' => true,
                'validate' => false,
            ),
        );
    }

    /**
     * Send through a CodeIgniter Email-like object.
     * Does not call print_debugger and does not include the message body in errors.
     */
    public function deliver($email, $plan, $subject, $message, $recipient, $bcc, $attachment) {
        if (!is_array($plan) || empty($plan['mode']) || $plan['mode'] === 'fail') {
            $error = (is_array($plan) && isset($plan['error'])) ? $plan['error'] : 'The message could not be sent.';
            return array('ok' => false, 'error' => $this->safe_error($error));
        }
        if ($plan['mode'] === 'smtp') {
            $email->initialize($plan['initialize']);
            $email->from($plan['from_email'], $plan['from_name']);
        } else {
            $email->from(self::LEGACY_FROM_EMAIL, self::LEGACY_FROM_NAME);
        }
        $email->subject($subject);
        $email->to($recipient);
        if ($bcc) {
            $email->bcc($bcc);
        }
        if (!is_null($attachment)) {
            $email->attach($attachment);
        }
        $email->message($message);

        $ok = false;
        $started = ob_start();
        try {
            $ok = (bool) $email->send();
        } catch (Exception $e) {
            $ok = false;
        }
        if ($started) {
            ob_end_clean();
        }
        if (!$ok) {
            return array('ok' => false, 'error' => 'The message could not be sent.');
        }
        return array('ok' => true, 'error' => '');
    }

    public function safe_error($message) {
        $message = trim(preg_replace('/\s+/', ' ', strip_tags((string) $message)));
        if ($message === '') {
            return 'The message could not be sent.';
        }
        if (preg_match('/AUTH|smtp_pass|password|v1:/i', $message)) {
            return 'The message could not be sent.';
        }
        if (strlen($message) > 180) {
            $message = substr($message, 0, 180);
        }
        return $message;
    }

    public function encrypt_password($password, $key) {
        $key = $this->normalize_key_bytes($key);
        if ($key === false || $password === '' || $password === null) {
            return false;
        }
        $iv = $this->random_bytes(12);
        if ($iv === false) {
            return false;
        }
        $tag = '';
        $cipher = openssl_encrypt((string) $password, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '');
        if ($cipher === false || !is_string($tag) || strlen($tag) !== 16) {
            return false;
        }
        return 'v1:' . base64_encode($iv . $tag . $cipher);
    }

    public function decrypt_password($payload, $key) {
        if ($payload === null || $payload === '') {
            return '';
        }
        if (!is_string($payload) || strpos($payload, 'v1:') !== 0) {
            return false;
        }
        $key = $this->normalize_key_bytes($key);
        if ($key === false) {
            return false;
        }
        $raw = base64_decode(substr($payload, 3), true);
        if ($raw === false || strlen($raw) < 29) {
            return false;
        }
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return ($plain === false) ? false : $plain;
    }

    /**
     * Resolve the server key. Environment wins over the gitignored key file.
     * The CodeIgniter encryption_key is intentionally not used: it lives in source control.
     */
    public function load_key() {
        $env = getenv(self::KEY_ENV);
        if (($env === false || $env === '') && isset($_SERVER[self::KEY_ENV])) {
            $env = $_SERVER[self::KEY_ENV];
        }
        if (($env === false || $env === '') && isset($_ENV[self::KEY_ENV])) {
            $env = $_ENV[self::KEY_ENV];
        }
        $envKey = $this->parse_key_material($env);
        if ($envKey !== false) {
            return array('ok' => true, 'key' => $envKey, 'source' => 'env', 'reason' => '');
        }
        $path = defined('APPPATH') ? rtrim(APPPATH, '/\\') . '/config/smtp.key' : '';
        if ($path !== '' && is_readable($path)) {
            $fileKey = $this->parse_key_material(@file_get_contents($path));
            if ($fileKey !== false) {
                return array('ok' => true, 'key' => $fileKey, 'source' => 'file', 'reason' => '');
            }
        }
        return array(
            'ok' => false,
            'key' => null,
            'source' => '',
            'reason' => 'Install a server key before saving an SMTP password. Set the environment variable TAPSTEMCO_SMTP_KEY, or create application/config/smtp.key (see application/config/smtp.key.example). The key stays on the server and is not stored in the database.',
        );
    }

    public function parse_key_material($material) {
        $material = trim((string) $material);
        if ($material === '') {
            return false;
        }
        $picked = '';
        foreach (preg_split('/\R/', $material) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (stripos($line, self::KEY_PLACEHOLDER) !== false) {
                continue;
            }
            $picked = $line;
            break;
        }
        if ($picked === '') {
            return false;
        }
        if (preg_match('/^[a-f0-9]{64}$/i', $picked)) {
            $bin = hex2bin($picked);
            return ($bin === false || strlen($bin) !== 32) ? false : $bin;
        }
        $decoded = base64_decode($picked, true);
        if ($decoded !== false && strlen($decoded) >= 32 && rtrim(strtr(base64_encode($decoded), '+/', '-_'), '=') !== '') {
            $reencoded = rtrim(base64_encode($decoded), '=');
            $given = rtrim(strtr($picked, '-_', '+/'), '=');
            if (function_exists('hash_equals') ? hash_equals($reencoded, $given) : $reencoded === $given) {
                return substr($decoded, 0, 32);
            }
        }
        if (strlen($picked) >= 32) {
            return hash('sha256', $picked, true);
        }
        return false;
    }

    private function normalize_key_bytes($key) {
        if (!is_string($key) || strlen($key) < 32) {
            return false;
        }
        return substr($key, 0, 32);
    }

    private function random_bytes($length) {
        if (function_exists('random_bytes')) {
            try {
                return random_bytes($length);
            } catch (Exception $e) {
                return false;
            }
        }
        $strong = false;
        $bytes = openssl_random_pseudo_bytes($length, $strong);
        if ($bytes === false || !$strong || strlen($bytes) !== $length) {
            return false;
        }
        return $bytes;
    }

    private function clean_line($value) {
        return trim(str_replace(array("\r", "\n"), '', (string) $value));
    }

    private function clean_name($value) {
        $value = trim(strip_tags((string) $value));
        $value = str_replace(array("\r", "\n"), '', $value);
        return $value;
    }

    private function has_header_break($value) {
        return preg_match('/[\r\n]/', (string) $value) === 1;
    }

    public function valid_host($host) {
        $host = (string) $host;
        if ($host === '' || strlen($host) > 255) {
            return false;
        }
        if (preg_match('/[\s\r\n@\/\\\\]/', $host)) {
            return false;
        }
        if (stripos($host, 'ssl://') === 0 || stripos($host, 'tls://') === 0) {
            return false;
        }
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }
        return preg_match('/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/i', $host) === 1;
    }

    public function valid_email($email) {
        $email = (string) $email;
        if ($email === '' || strlen($email) > 255 || $this->has_header_break($email) || strpos($email, ',') !== false) {
            return false;
        }
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
