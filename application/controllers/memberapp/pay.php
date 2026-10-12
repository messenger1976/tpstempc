<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/Memberapp_controller.php';

/**
 * Member Loan App - PayMongo QR Ph loan payments (JSON).
 *
 *   GET  pay/options/{LID}   amounts the member can pay
 *   POST pay/create          { LID, option: due|payoff|custom, amount }
 *   GET  pay/status/{id}     poll; re-checks PayMongo at most every 5 seconds
 *   POST pay/cancel/{id}     drop a QR the member no longer wants
 *
 * A paid QR becomes an UNPOSTED Cash Receipt; the loan balance changes only
 * when Finance posts it (see Loanpay_model).
 */
class Pay extends Memberapp_controller {

    const POLL_SECONDS = 5;

    /** @var object */
    private $ctx;

    public function __construct() {
        parent::__construct();
        $this->ctx = $this->memberapp_guard->require_member(true);
        $this->load->library('paymongo');
        $this->load->model('loanpay_model');
    }

    public function index() {
        $this->memberapp_guard->json(array('ok' => false, 'message' => 'Not found.'), 404);
    }

    public function options($LID = '') {
        $LID = urldecode((string) $LID);
        if (!$this->memberapp_guard->owns_loan($LID)) {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'Loan not found.'), 404);
        }
        $ready = $this->loanpay_model->readiness($this->ctx->PIN);
        $opts = $this->loanpay_model->pay_options($this->ctx, $LID);
        $fee = isset($opts['fee']) ? $opts['fee'] : $this->paymongo->fee_settings();
        $this->memberapp_guard->json(array(
            'ok' => true,
            'LID' => $LID,
            'available' => $ready['ok'] && empty($opts['blocked']),
            'message' => !$ready['ok'] ? $ready['message'] : (string) $opts['blocked'],
            'options' => isset($opts['options']) ? $opts['options'] : array(),
            'payoff' => isset($opts['payoff']) ? $opts['payoff'] : 0,
            'minimum' => isset($opts['minimum']) ? $opts['minimum'] : 0,
            'has_overdue' => !empty($opts['has_overdue']),
            'fee' => array(
                'enabled' => !empty($fee['enabled']),
                'percent' => isset($fee['percent']) ? (float) $fee['percent'] : 0,
                'fixed' => isset($fee['fixed']) ? (float) $fee['fixed'] : 0,
            ),
            'test_mode' => $this->paymongo->is_test_mode(),
            'note' => $this->paymongo->receipt_note(),
        ));
    }

    public function create() {
        $this->require_post();
        $LID = trim((string) $this->input->post('LID'));
        if (!$this->memberapp_guard->owns_loan($LID)) {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'Loan not found.'), 404);
        }
        if ($this->memberapp_guard->too_many($this->ctx->member_id, 'pay_create', 10, 15)) {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'Too many QR codes requested. Please wait a few minutes.'), 429);
        }
        $this->memberapp_guard->record_attempt($this->ctx->member_id, 'pay_create');

        $result = $this->loanpay_model->start($this->ctx, $LID, (string) $this->input->post('option'), $this->input->post('amount'));
        if (!$result['ok']) {
            $this->memberapp_guard->json(array('ok' => false, 'message' => $result['message']), 422);
        }
        $this->memberapp_guard->json(array('ok' => true, 'payment' => $this->public_row($result['attempt'])));
    }

    public function status($id = 0) {
        $row = $this->loanpay_model->for_member($this->ctx, (int) $id);
        if (!$row) {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'Payment not found.'), 404);
        }
        if ($row->status === 'pending') {
            $key = 'ma_poll_' . (int) $row->id;
            $last = (int) $this->session->userdata($key);
            if (time() - $last >= self::POLL_SECONDS) {
                $this->session->set_userdata($key, time());
                $row = $this->loanpay_model->refresh($row, 'poll');
            }
        }
        $this->memberapp_guard->json(array('ok' => true, 'payment' => $this->public_row($row)));
    }

    public function cancel($id = 0) {
        $this->require_post();
        $row = $this->loanpay_model->for_member($this->ctx, (int) $id);
        if (!$row) {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'Payment not found.'), 404);
        }
        // Last look in case the member paid just before cancelling.
        $row = $this->loanpay_model->refresh($row, 'poll');
        if ($row->status === 'pending') {
            $this->db->where('id', $row->id)->where('status', 'pending')
                ->update('loan_online_payments', array('status' => 'cancelled', 'update_date_time' => date('Y-m-d H:i:s')));
            $row = $this->loanpay_model->by_id($row->id);
        }
        $this->memberapp_guard->json(array('ok' => true, 'payment' => $this->public_row($row)));
    }

    // ------------------------------------------------------------------

    private function public_row($row) {
        $expires = $row->expires_at ? strtotime($row->expires_at) : 0;
        return array(
            'id' => (int) $row->id,
            'reference_no' => $row->reference_no,
            'LID' => $row->LID,
            'amount' => round((float) $row->amount, 2),
            'fee' => round((float) $row->fee_amount, 2),
            'charged' => round((float) $row->charged_amount, 2),
            'status' => $row->status,
            'expires_at' => $expires ? date('c', $expires) : null,
            'seconds_left' => $expires ? max(0, $expires - time()) : 0,
            'qr_image_url' => $row->status === 'pending' ? $row->qr_image_url : null,
            'test_url' => $row->status === 'pending' && $this->paymongo->is_test_mode() ? $row->qr_test_url : null,
            'paid_at' => $row->paid_at,
            'receipt_no' => $row->receipt_no,
            'awaiting_posting' => $row->status === 'paid',
        );
    }

    private function require_post() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'Method not allowed.'), 405);
        }
        if (!$this->memberapp_guard->csrf_valid()) {
            $this->memberapp_guard->json(array('ok' => false, 'error' => 'csrf', 'message' => 'Your session expired. Please reload the app.'), 403);
        }
    }
}
