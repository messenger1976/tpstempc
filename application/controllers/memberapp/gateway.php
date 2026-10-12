<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/Memberapp_controller.php';

/**
 * PayMongo webhook for Member Loan App payments.
 * URL to register in the PayMongo dashboard: /customer_login/paymongo/webhook
 * Events: payment.paid, payment.failed
 *
 * The event only says WHICH attempt to re-check: Loanpay_model::refresh()
 * re-reads the intent from PayMongo before anything is recorded.
 */
class Gateway extends Memberapp_controller {

    public function index() {
        $this->memberapp_guard->json(array('ok' => true, 'service' => 'paymongo-webhook'));
    }

    public function webhook() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->memberapp_guard->json(array('ok' => false), 405);
        }
        $this->load->library('paymongo');
        $this->load->model('loanpay_model');

        $raw = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_PAYMONGO_SIGNATURE']) ? (string) $_SERVER['HTTP_PAYMONGO_SIGNATURE'] : '';
        if (!$this->paymongo->verify_webhook_signature($raw, $signature)) {
            log_message('error', 'Member app PayMongo webhook: bad signature.');
            $this->memberapp_guard->json(array('ok' => false), 401);
        }

        $payload = json_decode($raw, true);
        $type = isset($payload['data']['attributes']['type']) ? (string) $payload['data']['attributes']['type'] : '';
        if (!in_array($type, array('payment.paid', 'payment.failed'), true)) {
            $this->memberapp_guard->json(array('ok' => true, 'ignored' => $type));
        }

        $attempt = $this->loanpay_model->resolve_from_payload($payload);
        if (!$attempt) {
            // Not one of ours (e.g. another app on the same PayMongo account).
            $this->memberapp_guard->json(array('ok' => true, 'ignored' => 'unknown'));
        }
        $fresh = $this->loanpay_model->refresh($attempt, 'webhook');
        $this->memberapp_guard->json(array('ok' => true, 'status' => $fresh ? $fresh->status : null));
    }
}
