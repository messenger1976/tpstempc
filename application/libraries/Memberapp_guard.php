<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Memberapp_guard - access rules for the Member Loan App (/customer_login).
 *
 * - Only Ion Auth users in the "Members" group, linked to an active member row,
 *   may use the app. Staff accounts are sent to the normal staff login.
 * - Every form carries a per-session token because CSRF protection is off
 *   globally in config.php.
 * - Login and one-time-code requests are rate limited per identity and per IP.
 */
class Memberapp_guard {

    const MEMBERS_GROUP = 'Members';
    const CSRF_KEY = 'ma_csrf';

    /** @var CI_Controller */
    private $CI;

    /** @var object|null cached member context */
    private $context = null;

    public function __construct() {
        $this->CI =& get_instance();
    }

    // ------------------------------------------------------------------
    // Session / member context
    // ------------------------------------------------------------------

    /**
     * Resolve the signed-in member, or null.
     * Restores a "remember me" login first so the installed app survives the
     * 2-hour session expiry.
     */
    public function member() {
        if ($this->context !== null) {
            return $this->context ?: null;
        }
        $this->context = false;

        if (!$this->CI->ion_auth->logged_in()) {
            if (get_cookie($this->CI->config->item('remember_cookie_name', 'ion_auth'))) {
                $this->CI->ion_auth->login_remembered_user();
            }
        }
        if (!$this->CI->ion_auth->logged_in()) {
            return null;
        }
        $user = current_user();
        if (!$user || !$this->CI->ion_auth->in_group(self::MEMBERS_GROUP, $user->id)) {
            return null;
        }
        if (!empty($user->deleted) || (string) $user->active === '0') {
            return null;
        }
        $member_id = trim((string) $user->member_id);
        if ($member_id === '') {
            $member_id = trim((string) $user->username);
        }
        $member = $this->CI->db->where('member_id', $member_id)
            ->where('PIN', $user->PIN)
            ->limit(1)
            ->get('members')->row();
        if (!$member || (string) $member->status === '2') {
            return null;
        }

        $this->context = (object) array(
            'user' => $user,
            'member' => $member,
            'user_id' => (int) $user->id,
            'member_id' => $member->member_id,
            'PID' => $member->PID,
            'PIN' => $user->PIN,
            'name' => trim($member->firstname . ' ' . $member->lastname),
            'full_name' => trim(preg_replace('/\s+/', ' ', $member->firstname . ' ' . $member->middlename . ' ' . $member->lastname)),
        );
        return $this->context;
    }

    /**
     * Stop the request unless a member is signed in.
     * Pages redirect to the app login; JSON calls get HTTP 401.
     */
    public function require_member($json = false) {
        $ctx = $this->member();
        if ($ctx) {
            return $ctx;
        }
        if ($json || $this->CI->input->is_ajax_request()) {
            $this->json(array('ok' => false, 'error' => 'auth', 'message' => 'Please sign in again.'), 401);
        }
        redirect('customer_login', 'location');
        exit;
    }

    /** Forget the cached context (after a login/logout in the same request). */
    public function reset() {
        $this->context = null;
    }

    /** Password rules for the app. Returns '' when acceptable. */
    public function password_problem($password, $confirm, $member_id) {
        if (strlen($password) < 8) {
            return 'Use at least 8 characters.';
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            return 'Use both letters and numbers.';
        }
        if (strcasecmp($password, $member_id) === 0) {
            return 'Your password cannot be your Member ID.';
        }
        if ($password !== $confirm) {
            return 'The passwords do not match.';
        }
        return '';
    }

    /** True when the given LID belongs to the signed-in member. */
    public function owns_loan($LID) {
        $ctx = $this->member();
        $LID = trim((string) $LID);
        if (!$ctx || $LID === '') {
            return false;
        }
        $row = $this->CI->db->select('LID')
            ->where('LID', $LID)
            ->where('PIN', $ctx->PIN)
            ->where('PID', $ctx->PID)
            ->limit(1)
            ->get('loan_contract')->row();
        return (bool) $row;
    }

    // ------------------------------------------------------------------
    // CSRF
    // ------------------------------------------------------------------

    public function csrf_token() {
        $token = $this->CI->session->userdata(self::CSRF_KEY);
        if (empty($token)) {
            $token = bin2hex(random_bytes(16));
            $this->CI->session->set_userdata(self::CSRF_KEY, $token);
        }
        return $token;
    }

    public function csrf_field() {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($this->csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public function csrf_valid() {
        $sent = (string) $this->CI->input->post('_token');
        if ($sent === '') {
            $sent = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? (string) $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
        }
        $token = (string) $this->CI->session->userdata(self::CSRF_KEY);
        return $token !== '' && $sent !== '' && hash_equals($token, $sent);
    }

    // ------------------------------------------------------------------
    // Rate limiting
    // ------------------------------------------------------------------

    /** Count recent attempts for an identity (and the caller's IP). */
    public function too_many($identity, $kind, $max, $window_minutes) {
        if (!$this->CI->db->table_exists('member_app_login_attempts')) {
            return false;
        }
        $since = date('Y-m-d H:i:s', time() - ($window_minutes * 60));
        $by_identity = $this->CI->db->where('identity', (string) $identity)
            ->where('kind', $kind)
            ->where('created_at >=', $since)
            ->count_all_results('member_app_login_attempts');
        if ($by_identity >= $max) {
            return true;
        }
        $by_ip = $this->CI->db->where('ip', $this->CI->input->ip_address())
            ->where('kind', $kind)
            ->where('created_at >=', $since)
            ->count_all_results('member_app_login_attempts');
        return $by_ip >= ($max * 4);
    }

    public function record_attempt($identity, $kind) {
        if (!$this->CI->db->table_exists('member_app_login_attempts')) {
            return;
        }
        $this->CI->db->insert('member_app_login_attempts', array(
            'identity' => substr((string) $identity, 0, 100),
            'kind' => $kind,
            'ip' => $this->CI->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    public function clear_attempts($identity, $kind) {
        if (!$this->CI->db->table_exists('member_app_login_attempts')) {
            return;
        }
        $this->CI->db->where('identity', (string) $identity)->where('kind', $kind)->delete('member_app_login_attempts');
    }

    // ------------------------------------------------------------------
    // Output
    // ------------------------------------------------------------------

    public function json($payload, $status = 200) {
        $this->CI->output->set_status_header($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode($payload);
        exit;
    }
}
