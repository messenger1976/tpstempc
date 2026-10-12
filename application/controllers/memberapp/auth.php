<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/Memberapp_controller.php';

/**
 * Member Loan App - sign in, account activation and password reset.
 * URL: /customer_login (see routes.php). Member ID is the username.
 */
class Auth extends Memberapp_controller {

    const FLOW_KEY = 'ma_flow';
    const FLOW_TTL = 1800;

    public function index() {
        if ($this->memberapp_guard->member()) {
            redirect('customer_login/app');
        }
        $data = array(
            'title' => 'Sign in',
            'error' => '',
            'member_id' => '',
            'notice' => $this->session->flashdata('ma_notice'),
            'clear_local' => $this->input->get('logged_out') ? true : false,
        );

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $member_id = trim((string) $this->input->post('member_id'));
            $password = (string) $this->input->post('password');
            $remember = $this->input->post('remember') ? true : false;
            $data['member_id'] = $member_id;

            if (!$this->memberapp_guard->csrf_valid()) {
                $data['error'] = 'Your session expired. Please try again.';
            } elseif ($member_id === '' || $password === '') {
                $data['error'] = 'Enter your Member ID and password.';
            } elseif ($this->memberapp_guard->too_many($member_id, 'login', 5, 15)) {
                $data['error'] = 'Too many failed attempts. Please wait 15 minutes or reset your password.';
            } elseif (!$this->ion_auth->login($member_id, $password, $remember)) {
                $this->memberapp_guard->record_attempt($member_id, 'login');
                $data['error'] = 'Incorrect Member ID or password.';
            } elseif (!$this->member_after_login()) {
                $this->ion_auth->logout();
                $this->memberapp_guard->record_attempt($member_id, 'login');
                $data['error'] = 'This app is for cooperative members. Staff please use the main system.';
            } else {
                $this->memberapp_guard->clear_attempts($member_id, 'login');
                $this->session->unset_userdata(self::FLOW_KEY);
                redirect('customer_login/app');
            }
        }
        $this->auth_view('memberapp/auth/login', $data);
    }

    public function activate() {
        $this->identity_step('activate');
    }

    public function forgot() {
        $this->identity_step('reset');
    }

    /** Step 1 of activation / reset: Member ID + birthdate + phone or email on file. */
    private function identity_step($purpose) {
        $data = array(
            'title' => $purpose === 'reset' ? 'Reset password' : 'Activate account',
            'purpose' => $purpose,
            'error' => '',
            'member_id' => '',
            'dob' => '',
            'contact' => '',
        );

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $member_id = trim((string) $this->input->post('member_id'));
            $dob = trim((string) $this->input->post('dob'));
            $contact = trim((string) $this->input->post('contact'));
            $data['member_id'] = $member_id;
            $data['dob'] = $dob;
            $data['contact'] = $contact;

            if (!$this->memberapp_guard->csrf_valid()) {
                $data['error'] = 'Your session expired. Please try again.';
            } elseif ($member_id === '' || $dob === '' || $contact === '') {
                $data['error'] = 'Please fill in all fields.';
            } elseif ($this->memberapp_guard->too_many($member_id, 'otp_request', 5, 30)) {
                $data['error'] = 'Too many requests. Please wait 30 minutes or visit the cooperative office.';
            } else {
                $this->memberapp_guard->record_attempt($member_id, 'otp_request');
                $member = $this->memberapp_model->find_member_by_member_id($member_id);
                $match = $this->memberapp_model->verify_identity($member, $dob, $contact);
                if (!$match['ok']) {
                    $data['error'] = 'These details do not match our records. Check them, or visit the cooperative office to update your contact details.';
                } else {
                    $sent = $this->memberapp_model->issue_otp($member, $purpose, $match['channel'], $match['destination'], $match['masked']);
                    $this->session->set_userdata(self::FLOW_KEY, array(
                        'member_id' => $member->member_id,
                        'purpose' => $purpose,
                        'masked' => $match['masked'],
                        'delivered' => $sent['delivered'] ? 1 : 0,
                        'dev_code' => isset($sent['code']) ? $sent['code'] : '',
                        'otp_id' => 0,
                        'ts' => time(),
                    ));
                    redirect('customer_login/verify');
                }
            }
        }
        $this->auth_view('memberapp/auth/identity', $data);
    }

    /** Step 2: enter the 6-digit code. */
    public function verify() {
        $flow = $this->flow();
        if (!$flow) {
            redirect('customer_login/activate');
        }
        $data = array(
            'title' => 'Enter code',
            'flow' => $flow,
            'error' => '',
            'notice' => '',
        );

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            if (!$this->memberapp_guard->csrf_valid()) {
                $data['error'] = 'Your session expired. Please try again.';
            } elseif ($this->input->post('resend')) {
                $data = $this->resend($flow, $data);
                $flow = $this->flow();
                $data['flow'] = $flow;
            } else {
                $result = $this->memberapp_model->verify_otp($flow['member_id'], $flow['purpose'], $this->input->post('code'));
                if ($result['ok']) {
                    $flow['otp_id'] = $result['otp_id'];
                    $this->session->set_userdata(self::FLOW_KEY, $flow);
                    redirect('customer_login/set_password');
                }
                $data['error'] = $result['message'];
            }
        }
        $this->auth_view('memberapp/auth/verify', $data);
    }

    private function resend($flow, $data) {
        if ($this->memberapp_guard->too_many($flow['member_id'], 'otp_request', 5, 30)) {
            $data['error'] = 'Too many requests. Please wait 30 minutes or visit the cooperative office.';
            return $data;
        }
        $this->memberapp_guard->record_attempt($flow['member_id'], 'otp_request');
        $member = $this->memberapp_model->find_member_by_member_id($flow['member_id']);
        if (!$member) {
            $data['error'] = 'We could not find your account.';
            return $data;
        }
        $contact = $this->memberapp_model->member_contact_row($member->PID);
        $channel = 'email';
        $destination = '';
        if ($contact && strpos($flow['masked'], '@') !== false) {
            $destination = strtolower(trim((string) $contact->email));
        } elseif ($contact) {
            $channel = 'sms';
            foreach (array('phone1', 'phone2') as $field) {
                if ($this->memberapp_model->mask_phone($contact->$field) === $flow['masked']) {
                    $destination = $contact->$field;
                    break;
                }
            }
        }
        $sent = $this->memberapp_model->issue_otp($member, $flow['purpose'], $channel, $destination, $flow['masked']);
        $flow['delivered'] = $sent['delivered'] ? 1 : 0;
        $flow['dev_code'] = isset($sent['code']) ? $sent['code'] : '';
        $flow['ts'] = time();
        $this->session->set_userdata(self::FLOW_KEY, $flow);
        $data['notice'] = 'A new code was sent.';
        return $data;
    }

    /** Code given by the cooperative office (Member App Access screen). */
    public function office_code() {
        $data = array(
            'title' => 'Office code',
            'error' => '',
            'member_id' => '',
            'dob' => '',
        );
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $member_id = trim((string) $this->input->post('member_id'));
            $dob = trim((string) $this->input->post('dob'));
            $data['member_id'] = $member_id;
            $data['dob'] = $dob;

            if (!$this->memberapp_guard->csrf_valid()) {
                $data['error'] = 'Your session expired. Please try again.';
            } elseif ($this->memberapp_guard->too_many($member_id, 'office_code', 5, 30)) {
                $data['error'] = 'Too many attempts. Please wait 30 minutes.';
            } else {
                $member = $this->memberapp_model->find_member_by_member_id($member_id);
                $dob_ok = $member && $this->memberapp_model->normalize_date($dob) !== ''
                    && $this->memberapp_model->normalize_date($dob) === $this->memberapp_model->normalize_date($member->dob);
                $result = $dob_ok
                    ? $this->memberapp_model->verify_otp($member->member_id, 'staff', $this->input->post('code'))
                    : array('ok' => false);
                if ($result['ok']) {
                    $this->memberapp_guard->clear_attempts($member_id, 'office_code');
                    $this->session->set_userdata(self::FLOW_KEY, array(
                        'member_id' => $member->member_id,
                        'purpose' => 'staff',
                        'masked' => '',
                        'delivered' => 1,
                        'dev_code' => '',
                        'otp_id' => $result['otp_id'],
                        'ts' => time(),
                    ));
                    redirect('customer_login/set_password');
                }
                $this->memberapp_guard->record_attempt($member_id, 'office_code');
                $data['error'] = $dob_ok && !empty($result['message']) ? $result['message'] : 'The details or code are not correct.';
            }
        }
        $this->auth_view('memberapp/auth/office_code', $data);
    }

    /** Step 3: choose a password. Creates the login when the member has none yet. */
    public function set_password() {
        $flow = $this->flow();
        if (!$flow || empty($flow['otp_id'])) {
            redirect('customer_login/activate');
        }
        $data = array('title' => 'Set password', 'error' => '', 'flow' => $flow);

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $password = (string) $this->input->post('password');
            $confirm = (string) $this->input->post('password_confirm');
            $error = $this->memberapp_guard->password_problem($password, $confirm, $flow['member_id']);
            $otp = $this->memberapp_model->verified_otp($flow['otp_id'], $flow['member_id'], $flow['purpose']);
            $member = $this->memberapp_model->find_member_by_member_id($flow['member_id']);

            if (!$this->memberapp_guard->csrf_valid()) {
                $data['error'] = 'Your session expired. Please try again.';
            } elseif ($error !== '') {
                $data['error'] = $error;
            } elseif (!$otp || !$member) {
                $this->session->unset_userdata(self::FLOW_KEY);
                $this->session->set_flashdata('ma_notice', 'Your code expired. Please start again.');
                redirect('customer_login');
            } elseif (!$this->memberapp_model->set_member_password($member, $password)) {
                $data['error'] = 'We could not save your password. Please contact the cooperative office.';
            } else {
                $this->memberapp_model->consume_otp($otp->id);
                $this->memberapp_guard->clear_attempts($member->member_id, 'login');
                $this->session->unset_userdata(self::FLOW_KEY);
                if ($this->ion_auth->login($member->member_id, $password, false) && $this->member_after_login()) {
                    redirect('customer_login/app#/home?welcome=1');
                }
                $this->session->set_flashdata('ma_notice', 'Your password is set. Please sign in.');
                redirect('customer_login');
            }
        }
        $this->auth_view('memberapp/auth/set_password', $data);
    }

    public function logout() {
        if ($this->ion_auth->logged_in()) {
            $this->ion_auth->logout();
        }
        redirect(site_url('customer_login') . '?logged_out=1');
    }

    private function member_after_login() {
        $this->memberapp_guard->reset();
        return $this->memberapp_guard->member();
    }

    private function flow() {
        $flow = $this->session->userdata(self::FLOW_KEY);
        if (!is_array($flow) || empty($flow['member_id']) || (time() - (int) $flow['ts']) > self::FLOW_TTL) {
            return null;
        }
        return $flow;
    }
}
