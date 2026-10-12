<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Staff side of the Member Loan App (/customer_login).
 *
 *   payments  Online loan payments (PayMongo QR Ph) and their Cash Receipts
 *   settings  PayMongo keys, fees, webhook URL, products offered in the app
 *   access    Look up a member's app account and issue an office code
 */
class Member_app extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect(current_lang() . '/auth/login', 'refresh');
        }
        if ($this->ion_auth->in_group('Members')) {
            redirect(current_lang() . '/dashboard', 'refresh');
        }
        $this->load->model('memberapp_schema_model');
        $this->memberapp_schema_model->ensure();
        $this->load->library('memberapp_guard');
        $this->load->library('paymongo');
        $this->data['title'] = 'Member Loan App';
        $this->data['csrf_field'] = $this->memberapp_guard->csrf_field();
    }

    public function index() {
        redirect(current_lang() . '/member_app/payments');
    }

    // ------------------------------------------------------------------
    // Online loan payments
    // ------------------------------------------------------------------

    public function payments() {
        $this->require_role(5, 'Online_loan_payments');
        $this->load->model('loanpay_model');
        $filters = array(
            'status' => trim((string) $this->input->get('status')),
            'from' => $this->clean_date($this->input->get('from')),
            'to' => $this->clean_date($this->input->get('to')),
            'key' => trim((string) $this->input->get('key')),
        );
        $this->data['title'] = 'Online Loan Payments';
        $this->data['filters'] = $filters;
        $this->data['rows'] = $this->loanpay_model->report($filters);
        $this->data['readiness'] = $this->loanpay_model->readiness(current_user()->PIN);
        $this->data['content'] = 'member_app/payments';
        $this->load->view('template', $this->data);
    }

    /** Re-check one attempt with PayMongo (useful if the webhook was missed). */
    public function recheck($id = 0) {
        $this->require_role(5, 'Online_loan_payments');
        $this->require_post('member_app/payments');
        $this->load->model('loanpay_model');
        $row = $this->own_attempt($id);
        if ($row) {
            $fresh = $this->loanpay_model->refresh($row, 'webhook');
            $this->session->set_flashdata('message', 'Payment ' . $row->reference_no . ' is now: ' . ($fresh ? $fresh->status : 'unknown') . '.');
        }
        redirect(current_lang() . '/member_app/payments');
    }

    public function retry_receipt($id = 0) {
        $this->require_role(5, 'Online_loan_payments');
        $this->require_post('member_app/payments');
        $this->load->model('loanpay_model');
        $row = $this->own_attempt($id);
        if ($row) {
            $r = $this->loanpay_model->retry_receipt($row->id);
            $this->session->set_flashdata($r['ok'] ? 'message' : 'warning', $r['message']);
        }
        redirect(current_lang() . '/member_app/payments');
    }

    // ------------------------------------------------------------------
    // Settings
    // ------------------------------------------------------------------

    public function settings() {
        $this->require_role(9, 'Paymongo_settings');
        $pin = current_user()->PIN;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->require_post('member_app/settings');
            $action = (string) $this->input->post('action');
            if ($action === 'test') {
                $r = $this->paymongo->test_connection();
                $this->session->set_flashdata($r['ok'] ? 'message' : 'warning', $r['message']);
            } elseif ($action === 'products') {
                $this->save_products($pin);
                $this->session->set_flashdata('message', 'Loan products for the app were saved.');
            } else {
                $this->save_gateway();
                $this->session->set_flashdata('message', 'PayMongo settings were saved.');
            }
            redirect(current_lang() . '/member_app/settings');
        }

        $s = $this->paymongo->settings(true);
        $this->load->model('setting_model');
        $this->load->model('loanpay_model');
        $chosen = array();
        foreach ($this->db->where('PIN', $pin)->get('member_app_products')->result() as $r) {
            $chosen[(int) $r->product_id] = $r->max_amount;
        }
        $this->data['title'] = 'Member App Settings';
        $this->data['s'] = $s;
        $this->data['masked'] = array(
            'public_key' => Paymongo::mask(isset($s['public_key']) ? $s['public_key'] : ''),
            'secret_key' => Paymongo::mask(isset($s['secret_key']) ? $s['secret_key'] : ''),
            'webhook_secret' => Paymongo::mask(isset($s['webhook_secret']) ? $s['webhook_secret'] : ''),
        );
        $this->data['test_mode'] = $this->paymongo->is_test_mode();
        $this->data['webhook_url'] = $this->paymongo->webhook_url();
        $this->data['app_url'] = site_url('customer_login');
        $this->data['payment_method'] = $this->loanpay_model->payment_method($pin);
        $this->load->model('finance_model');
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        $this->data['readiness'] = $this->loanpay_model->readiness($pin);
        $this->data['products'] = $this->setting_model->loanproduct()->result();
        $this->data['chosen'] = $chosen;
        $this->data['content'] = 'member_app/settings';
        $this->load->view('template', $this->data);
    }

    private function save_gateway() {
        $s = $this->paymongo->settings(true);
        $data = array(
            'enabled' => $this->input->post('enabled') ? 1 : 0,
            'min_amount' => max(20, round((float) $this->input->post('min_amount'), 2)),
            'description_prefix' => substr(trim(strip_tags((string) $this->input->post('description_prefix'))), 0, 100),
            'receipt_note' => substr(trim(strip_tags((string) $this->input->post('receipt_note'))), 0, 255),
            'fee_enabled' => $this->input->post('fee_enabled') ? 1 : 0,
            'fee_percent' => max(0, min(10, round((float) $this->input->post('fee_percent'), 3))),
            'fee_fixed' => max(0, round((float) $this->input->post('fee_fixed'), 2)),
            'updated_by' => current_user()->id,
            'update_date_time' => date('Y-m-d H:i:s'),
        );
        // Blank key fields keep the stored value; "clear" checkboxes remove it.
        foreach (array('public_key' => 'pk_', 'secret_key' => 'sk_', 'webhook_secret' => 'whsk_') as $field => $prefix) {
            $value = trim((string) $this->input->post($field));
            if ($this->input->post('clear_' . $field)) {
                $data[$field] = null;
            } elseif ($value !== '') {
                if (strpos($value, $prefix) !== 0) {
                    $this->session->set_flashdata('warning', ucfirst(str_replace('_', ' ', $field)) . ' should start with "' . $prefix . '". It was not saved.');
                    continue;
                }
                $data[$field] = $value;
            }
        }
        if ($data['enabled'] && empty($s['secret_key']) && empty($data['secret_key'])) {
            $data['enabled'] = 0;
        }

        $fee_account = trim((string) $this->input->post('fee_gl_account'));
        if ($fee_account !== '' && !$this->db->where('account', $fee_account)->where('PIN', current_user()->PIN)->count_all_results('account_chart')) {
            $this->session->set_flashdata('warning', 'The fee GL account was not found in the chart of accounts. It was not saved.');
            $fee_account = isset($s['fee_gl_account']) ? (string) $s['fee_gl_account'] : '';
        }
        $data['fee_gl_account'] = $fee_account !== '' ? $fee_account : null;
        if ($data['fee_enabled'] && $fee_account === '') {
            $data['fee_enabled'] = 0;
            $this->session->set_flashdata('warning', 'The convenience fee stays off until a fee GL account is chosen.');
        }

        $this->db->where('id', 1)->update('paymongo_settings', $data);
    }

    private function save_products($pin) {
        $ids = $this->input->post('product_ids');
        $max = $this->input->post('max_amount');
        $ids = is_array($ids) ? array_map('intval', $ids) : array();
        $this->db->where('PIN', $pin)->delete('member_app_products');
        $this->load->model('setting_model');
        foreach ($this->setting_model->loanproduct()->result() as $p) {
            if (!in_array((int) $p->id, $ids, true)) {
                continue;
            }
            $cap = isset($max[$p->id]) ? round((float) str_replace(',', '', $max[$p->id]), 2) : 0;
            $this->db->insert('member_app_products', array(
                'PIN' => $pin,
                'product_id' => (int) $p->id,
                'max_amount' => $cap > 0 ? $cap : null,
                'updated_by' => current_user()->id,
                'update_date_time' => date('Y-m-d H:i:s'),
            ));
        }
    }

    // ------------------------------------------------------------------
    // Member app access (office codes)
    // ------------------------------------------------------------------

    public function access() {
        $this->require_role(1, 'Member_app_access');
        $this->load->model('memberapp_model');
        $pin = current_user()->PIN;
        $member_id = trim((string) ($this->input->post('member_id') ? $this->input->post('member_id') : $this->input->get('member_id')));
        $this->data['title'] = 'Member App Access';
        $this->data['member_id'] = $member_id;
        $this->data['member'] = null;
        $this->data['account'] = null;
        $this->data['issued_code'] = null;

        if ($member_id !== '') {
            $member = $this->db->where('member_id', $member_id)->where('PIN', $pin)->where('status !=', 2)->limit(1)->get('members')->row();
            $this->data['member'] = $member;
            if ($member) {
                $this->data['account'] = $this->memberapp_model->member_user($member);
                if ($this->input->server('REQUEST_METHOD') === 'POST') {
                    $this->require_post('member_app/access?member_id=' . rawurlencode($member_id));
                    $action = (string) $this->input->post('action');
                    if ($action === 'code') {
                        $r = $this->memberapp_model->issue_otp($member, 'staff', 'staff', '', 'office', current_user()->id);
                        $this->data['issued_code'] = $r['code'];
                    } elseif ($action === 'unlock') {
                        foreach (array('login', 'otp_request', 'office_code', 'change_pw') as $kind) {
                            $this->memberapp_guard->clear_attempts($member->member_id, $kind);
                        }
                        $this->data['message'] = 'Sign-in limits for ' . $member->member_id . ' were cleared.';
                    }
                }
            }
        }
        $this->data['content'] = 'member_app/access';
        $this->load->view('template', $this->data);
    }

    // ------------------------------------------------------------------

    private function require_role($module, $link) {
        if (!$this->ion_auth->is_admin() && !has_role($module, $link)) {
            $this->session->set_flashdata('warning', 'You do not have access to ' . str_replace('_', ' ', $link) . '.');
            redirect(current_lang() . '/dashboard', 'refresh');
        }
    }

    private function require_post($back) {
        if ($this->input->server('REQUEST_METHOD') !== 'POST' || !$this->memberapp_guard->csrf_valid()) {
            $this->session->set_flashdata('warning', 'The form expired. Please try again.');
            redirect(current_lang() . '/' . $back);
        }
    }

    private function own_attempt($id) {
        return $this->db->where('id', (int) $id)->where('PIN', current_user()->PIN)->limit(1)->get('loan_online_payments')->row();
    }

    private function clean_date($v) {
        $v = trim((string) $v);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';
    }
}
