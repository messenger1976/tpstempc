<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Paymongo - API client for QR Ph payments made from the Member Loan App.
 *
 * Ported from the Labason Water District implementation. It only talks to
 * PayMongo; the loan / cash-receipt workflow lives in Loanpay_model.
 *
 * Flow (Payment Intent + `qrph` payment method):
 *   1. POST /v1/payment_intents        { amount (centavos), currency PHP, payment_method_allowed ['qrph'] }
 *   2. POST /v1/payment_methods        { type: 'qrph' }
 *   3. POST /v1/payment_intents/{id}/attach
 *   4. next_action.code.image_url is drawn on our own page.
 *
 * The secret key is read from paymongo_settings and never reaches a template
 * or a member-facing error. A webhook only tells us WHICH payment to re-check;
 * settlement always re-reads the intent from PayMongo first.
 */
class Paymongo {

    const API_BASE = 'https://api.paymongo.com/v1';
    const MIN_CENTAVOS = 2000;
    const SETTINGS_TABLE = 'paymongo_settings';

    private $CI;
    private $settings = null;
    private $last_error = '';
    private $last_error_class = '';

    public function __construct($params = array()) {
        $this->CI =& get_instance();
    }

    // ------------------------------------------------------------------
    // Settings
    // ------------------------------------------------------------------

    public function settings($refresh = false) {
        if ($this->settings !== null && !$refresh) {
            return $this->settings;
        }
        $this->settings = array();
        if (!$this->CI->db->table_exists(self::SETTINGS_TABLE)) {
            return $this->settings;
        }
        $row = $this->CI->db->where('id', 1)->limit(1)->get(self::SETTINGS_TABLE)->row_array();
        if (is_array($row)) {
            $this->settings = $row;
        }
        return $this->settings;
    }

    public function is_configured() {
        $s = $this->settings();
        return isset($s['enabled']) && (string) $s['enabled'] === '1'
            && isset($s['secret_key']) && trim((string) $s['secret_key']) !== '';
    }

    public function has_secret_key() {
        $s = $this->settings();
        return isset($s['secret_key']) && trim((string) $s['secret_key']) !== '';
    }

    public function is_test_mode() {
        $s = $this->settings();
        return strpos(isset($s['secret_key']) ? trim((string) $s['secret_key']) : '', 'sk_test_') === 0;
    }

    public function min_amount_pesos() {
        $s = $this->settings();
        $min = isset($s['min_amount']) ? (float) $s['min_amount'] : 20.00;
        return max($min, self::MIN_CENTAVOS / 100);
    }

    public function description_prefix() {
        $s = $this->settings();
        $p = isset($s['description_prefix']) ? trim((string) $s['description_prefix']) : '';
        return $p !== '' ? $p : 'Loan Payment';
    }

    public function receipt_note() {
        $s = $this->settings();
        return isset($s['receipt_note']) ? trim((string) $s['receipt_note']) : '';
    }

    /** Name of the paymentmenthod row used on the Cash Receipt. */
    public function payment_method_name() {
        $s = $this->settings();
        $n = isset($s['payment_method_name']) ? trim((string) $s['payment_method_name']) : '';
        return $n !== '' ? $n : 'Online - PayMongo';
    }

    // ------------------------------------------------------------------
    // Convenience fee (paid by the member, never applied to the loan)
    // ------------------------------------------------------------------

    /** Income account credited with the fee; the fee cannot be charged without it. */
    public function fee_gl_account() {
        $s = $this->settings();
        return isset($s['fee_gl_account']) ? trim((string) $s['fee_gl_account']) : '';
    }

    public function fee_settings() {
        $s = $this->settings();
        $off = array('enabled' => false, 'percent' => 0.0, 'fixed' => 0.0);
        if (!isset($s['fee_enabled']) || (string) $s['fee_enabled'] !== '1' || $this->fee_gl_account() === '') {
            return $off;
        }
        $percent = max(0.0, (float) (isset($s['fee_percent']) ? $s['fee_percent'] : 0));
        $fixed = max(0.0, (float) (isset($s['fee_fixed']) ? $s['fee_fixed'] : 0));
        if ($percent <= 0 && $fixed <= 0) {
            return $off;
        }
        return array('enabled' => true, 'percent' => $percent, 'fixed' => $fixed);
    }

    public function compute_fee($amount_pesos) {
        $amount = round((float) $amount_pesos, 2);
        $fs = $this->fee_settings();
        if (!$fs['enabled']) {
            return array('fee' => 0.0, 'charged' => $amount);
        }
        $fee = round($amount * $fs['percent'] / 100 + $fs['fixed'], 2);
        return array('fee' => $fee, 'charged' => round($amount + $fee, 2));
    }

    public function to_centavos($pesos) {
        $centavos = (int) round(((float) $pesos) * 100);
        return $centavos < self::MIN_CENTAVOS ? false : $centavos;
    }

    public function last_error() {
        return $this->last_error;
    }

    public function last_error_class() {
        return $this->last_error_class;
    }

    // ------------------------------------------------------------------
    // HTTP
    // ------------------------------------------------------------------

    private function request($method, $path, $payload = null) {
        $s = $this->settings();
        $secret = isset($s['secret_key']) ? trim((string) $s['secret_key']) : '';
        $this->last_error = '';
        $this->last_error_class = '';
        if ($secret === '') {
            $this->last_error = 'No PayMongo secret key is saved yet.';
            $this->last_error_class = 'rejected';
            return array('ok' => false, 'status' => 0, 'data' => array());
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_BASE . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($secret . ':'),
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'TAPSTEMCO-LoanApp/1.0');
        if (defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        $method = strtoupper($method);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $errno !== 0) {
            $this->last_error = 'Could not reach PayMongo (api.paymongo.com)' . ($error !== '' ? ': ' . $error : '.');
            $this->last_error_class = 'unreachable';
            return array('ok' => false, 'status' => 0, 'data' => array());
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = array();
        }
        if ($status < 200 || $status >= 300) {
            $msg = $this->extract_error_message($data);
            $this->last_error = $msg !== '' ? $msg : 'PayMongo answered HTTP ' . $status . '.';
            $haystack = strtolower($this->last_error . ' ' . $raw);
            $this->last_error_class = (in_array($status, array(401, 403), true) || strpos($haystack, 'api_key') !== false) ? 'rejected' : 'check failed';
            return array('ok' => false, 'status' => $status, 'data' => $data);
        }
        return array('ok' => true, 'status' => $status, 'data' => $data);
    }

    private function extract_error_message($data) {
        if (isset($data['errors']) && is_array($data['errors'])) {
            $first = reset($data['errors']);
            if (is_array($first)) {
                if (!empty($first['detail'])) {
                    return (string) $first['detail'];
                }
                if (!empty($first['code'])) {
                    return (string) $first['code'];
                }
            }
        }
        return !empty($data['message']) ? (string) $data['message'] : '';
    }

    // ------------------------------------------------------------------
    // QR Ph
    // ------------------------------------------------------------------

    /**
     * @return array|false array(intent_id, payment_id, qr_image_url, test_url, expires_at)
     */
    public function create_qrph($amount_pesos, $description, $metadata = array()) {
        $centavos = $this->to_centavos($amount_pesos);
        if ($centavos === false) {
            $this->last_error = 'The amount is below PayMongo\'s minimum of PHP ' . number_format(self::MIN_CENTAVOS / 100, 2) . '.';
            $this->last_error_class = 'check failed';
            return false;
        }
        $meta = array();
        foreach ((array) $metadata as $k => $v) {
            $v = is_scalar($v) ? trim((string) $v) : '';
            if ($v !== '') {
                $meta[(string) $k] = $v;
            }
        }
        $attributes = array(
            'amount' => $centavos,
            'currency' => 'PHP',
            'payment_method_allowed' => array('qrph'),
            'capture_type' => 'automatic',
            'description' => (string) $description,
        );
        if (!empty($meta)) {
            $attributes['metadata'] = $meta;
        }
        $res = $this->request('POST', '/payment_intents', array('data' => array('attributes' => $attributes)));
        if (!$res['ok']) {
            return false;
        }
        $intent = $this->dig($res['data'], array('data'));
        $intent_id = isset($intent['id']) ? (string) $intent['id'] : '';
        if ($intent_id === '') {
            $this->last_error = 'PayMongo did not return a payment intent id.';
            return false;
        }
        $qr = $this->extract_qr($intent);
        if ($qr['image_url'] === '') {
            $pm = $this->request('POST', '/payment_methods', array('data' => array('attributes' => array('type' => 'qrph'))));
            if (!$pm['ok']) {
                return false;
            }
            $pm_data = $this->dig($pm['data'], array('data'));
            $pm_id = isset($pm_data['id']) ? (string) $pm_data['id'] : '';
            if ($pm_id === '') {
                $this->last_error = 'PayMongo did not return a payment method id.';
                return false;
            }
            $attach = $this->request('POST', '/payment_intents/' . rawurlencode($intent_id) . '/attach', array(
                'data' => array('attributes' => array('payment_method' => $pm_id)),
            ));
            if (!$attach['ok']) {
                return false;
            }
            $qr = $this->extract_qr($this->dig($attach['data'], array('data')));
            if ($qr['image_url'] === '') {
                $this->last_error = 'PayMongo accepted the payment but returned no QR image for intent ' . $intent_id . '.';
                return false;
            }
        }
        return array(
            'intent_id' => $intent_id,
            'payment_id' => $qr['payment_id'],
            'qr_image_url' => $qr['image_url'],
            'test_url' => $qr['test_url'],
            'expires_at' => $qr['expires_at'],
        );
    }

    public function get_intent($intent_id) {
        $intent_id = trim((string) $intent_id);
        if ($intent_id === '') {
            $this->last_error = 'No payment intent id given.';
            return false;
        }
        $res = $this->request('GET', '/payment_intents/' . rawurlencode($intent_id));
        if (!$res['ok']) {
            return false;
        }
        $intent = $this->dig($res['data'], array('data'));
        return empty($intent) ? false : $intent;
    }

    /** Only the gateway's own answer counts as paid. */
    public function intent_is_paid($intent) {
        if (!is_array($intent)) {
            return false;
        }
        $attrs = isset($intent['attributes']) && is_array($intent['attributes']) ? $intent['attributes'] : $intent;
        if (strtolower(isset($attrs['status']) ? (string) $attrs['status'] : '') !== 'succeeded') {
            return false;
        }
        if (isset($attrs['payments']) && is_array($attrs['payments'])) {
            foreach ($attrs['payments'] as $p) {
                $pa = isset($p['attributes']) && is_array($p['attributes']) ? $p['attributes'] : $p;
                if (strtolower(isset($pa['status']) ? (string) $pa['status'] : '') === 'paid') {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /** Amount actually captured on the intent, in centavos (0 when unknown). */
    public function intent_amount_centavos($intent) {
        $attrs = isset($intent['attributes']) && is_array($intent['attributes']) ? $intent['attributes'] : $intent;
        return isset($attrs['amount']) ? (int) $attrs['amount'] : 0;
    }

    public function intent_status($intent) {
        $attrs = isset($intent['attributes']) && is_array($intent['attributes']) ? $intent['attributes'] : $intent;
        return strtolower(isset($attrs['status']) ? (string) $attrs['status'] : '');
    }

    public function extract_paid_payment_id($intent) {
        $attrs = isset($intent['attributes']) && is_array($intent['attributes']) ? $intent['attributes'] : $intent;
        if (isset($attrs['payments']) && is_array($attrs['payments'])) {
            foreach ($attrs['payments'] as $p) {
                $pa = isset($p['attributes']) && is_array($p['attributes']) ? $p['attributes'] : $p;
                if (strtolower(isset($pa['status']) ? (string) $pa['status'] : '') === 'paid' && !empty($p['id'])) {
                    return (string) $p['id'];
                }
            }
        }
        return '';
    }

    private function dig($data, $path) {
        $node = $data;
        foreach ((array) $path as $key) {
            if (!is_array($node) || !array_key_exists($key, $node)) {
                return array();
            }
            $node = $node[$key];
        }
        return is_array($node) ? $node : array();
    }

    private function extract_qr($resource) {
        $out = array('image_url' => '', 'test_url' => '', 'expires_at' => '', 'payment_id' => '');
        if (!is_array($resource)) {
            return $out;
        }
        $attrs = isset($resource['attributes']) && is_array($resource['attributes']) ? $resource['attributes'] : $resource;
        if (!empty($attrs['payments'][0]['id'])) {
            $out['payment_id'] = (string) $attrs['payments'][0]['id'];
        }
        $candidates = array();
        if (isset($attrs['next_action']) && is_array($attrs['next_action'])) {
            $candidates[] = $attrs['next_action'];
            if (isset($attrs['next_action']['code']) && is_array($attrs['next_action']['code'])) {
                $candidates[] = $attrs['next_action']['code'];
            }
        }
        foreach ($candidates as $c) {
            foreach (array('image_url', 'qr_image_url', 'code_image_url') as $k) {
                if ($out['image_url'] === '' && !empty($c[$k]) && is_string($c[$k])) {
                    $out['image_url'] = $c[$k];
                }
            }
            foreach (array('test_url', 'simulator_url') as $k) {
                if ($out['test_url'] === '' && !empty($c[$k]) && is_string($c[$k])) {
                    $out['test_url'] = $c[$k];
                }
            }
            foreach (array('expires_at', 'expiry') as $k) {
                if ($out['expires_at'] === '' && !empty($c[$k])) {
                    // PayMongo answers in UTC; store local time.
                    $ts = is_numeric($c[$k]) ? (int) $c[$k] : strtotime((string) $c[$k]);
                    $out['expires_at'] = ($ts && $ts > 0) ? date('Y-m-d H:i:s', $ts) : '';
                }
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Webhooks
    // ------------------------------------------------------------------

    /**
     * Verify the Paymongo-Signature header (t=<ts>,te=<test sig>,li=<live sig>),
     * HMAC-SHA256 of "<t>.<raw body>" with the webhook secret.
     * Unlike the Labason original, a missing secret REJECTS the call.
     */
    public function verify_webhook_signature($raw_body, $signature_header) {
        $s = $this->settings();
        $secret = isset($s['webhook_secret']) ? trim((string) $s['webhook_secret']) : '';
        $signature_header = trim((string) $signature_header);
        if ($secret === '' || $signature_header === '') {
            return false;
        }
        $parts = array();
        foreach (explode(',', $signature_header) as $piece) {
            $kv = explode('=', $piece, 2);
            if (count($kv) === 2) {
                $parts[trim($kv[0])] = trim($kv[1]);
            }
        }
        if (empty($parts['t'])) {
            return false;
        }
        $expected = hash_hmac('sha256', $parts['t'] . '.' . (string) $raw_body, $secret);
        foreach (array('te', 'li') as $k) {
            if (!empty($parts[$k]) && hash_equals($expected, $parts[$k])) {
                return true;
            }
        }
        return false;
    }

    public function webhook_url() {
        return rtrim($this->CI->config->base_url(), '/') . '/customer_login/paymongo/webhook';
    }

    /** Read-only key check; creates nothing on the PayMongo account. */
    public function test_connection() {
        if (!$this->has_secret_key()) {
            return array('ok' => false, 'message' => 'No secret key is saved yet.');
        }
        $res = $this->request('GET', '/payments?limit=1');
        if ($res['ok']) {
            return array('ok' => true, 'message' => 'PayMongo accepted the saved key - ' . ($this->is_test_mode() ? 'test mode (sk_test_).' : 'live mode (sk_live_).'));
        }
        if ($this->last_error_class === 'unreachable') {
            return array('ok' => false, 'message' => 'This server could not reach PayMongo, so the key was NOT checked. ' . $this->last_error);
        }
        return array('ok' => false, 'message' => 'PayMongo did not accept the request: ' . $this->last_error);
    }

    public static function mask($value) {
        $value = trim((string) $value);
        $len = strlen($value);
        if ($len === 0) {
            return '';
        }
        if ($len <= 10) {
            return str_repeat('*', $len);
        }
        return substr($value, 0, 4) . str_repeat('*', max(4, $len - 8)) . substr($value, -4);
    }
}
