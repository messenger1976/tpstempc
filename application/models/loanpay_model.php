<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Loanpay_model - PayMongo QR Ph loan payments from the Member Loan App.
 *
 * Lifecycle of a loan_online_payments row:
 *   pending  -> QR shown, waiting for the member to pay
 *   paid     -> PayMongo confirmed; an UNPOSTED Cash Receipt (loan-repayment
 *               mode) is created for Finance to review and post. Posting it in
 *               Journal Entry Review applies the payment to the loan schedule.
 *   expired / failed / cancelled -> no money moved
 *
 * Amounts are always computed on the server from Loan_model; the browser only
 * picks an option. Settlement is idempotent (row lock + status check) because
 * both the webhook and the member's poll can trigger it.
 */
class Loanpay_model extends CI_Model {

    const TABLE = 'loan_online_payments';
    const QR_TTL_MINUTES = 30;

    public function __construct() {
        parent::__construct();
        $this->load->library('paymongo');
    }

    // ------------------------------------------------------------------
    // Readiness
    // ------------------------------------------------------------------

    /** Payment method row for the Cash Receipt (must have a GL account). */
    public function payment_method($pin) {
        $name = $this->paymongo->payment_method_name();
        return $this->db->where('LOWER(name)', strtolower($name))
            ->where('PIN', $pin)
            ->where('status', 1)
            ->limit(1)
            ->get('paymentmenthod')->row();
    }

    /** array('ok' => bool, 'message' => string) - can members pay online right now? */
    public function readiness($pin) {
        if (!$this->db->table_exists(self::TABLE)) {
            return array('ok' => false, 'message' => 'Online payments are not set up yet (database update pending).');
        }
        if (!$this->paymongo->is_configured()) {
            return array('ok' => false, 'message' => 'Online payments are not available yet. Please pay at the office.');
        }
        $pm = $this->payment_method($pin);
        if (!$pm || trim((string) $pm->gl_account_code) === '') {
            return array('ok' => false, 'message' => 'Online payments are being set up by Finance. Please try again later.');
        }
        return array('ok' => true, 'message' => '');
    }

    // ------------------------------------------------------------------
    // Amount options
    // ------------------------------------------------------------------

    /**
     * Options the member can choose for a loan. Returns null when the loan
     * cannot take an online payment right now (with a reason in 'blocked').
     */
    public function pay_options($ctx, $LID) {
        $this->load->model('loan_model');
        $this->load->model('setting_model');
        $this->load->model('cash_receipt_model');
        $today = date('Y-m-d');

        $loan = $this->loan_model->loan_info($LID, $ctx->PID)->row();
        if (!$loan || (string) $loan->PID !== (string) $ctx->PID || (string) $loan->disburse !== '1' || (string) $loan->status === '5') {
            return array('blocked' => 'This loan is not open for payment.');
        }
        $pending = $this->open_attempt_for_loan($ctx, $LID, 'paid_unposted');
        if ($pending) {
            return array('blocked' => 'Your payment ' . $pending->reference_no . ' was received and is waiting to be posted by the office.');
        }
        $unposted = $this->cash_receipt_model->get_unposted_loan_repayment_for_loan($LID);
        if ($unposted) {
            return array('blocked' => 'A payment (' . $unposted->receipt_no . ') is still being posted by the office. You can pay again once it is posted.');
        }

        $breakdown = $this->loan_model->get_loan_outstanding_for_offset($LID);
        $payoff = $breakdown ? round((float) $breakdown['total'], 2) : 0.0;
        if ($payoff <= 0.009) {
            return array('blocked' => 'This loan has no balance to pay.');
        }
        $due = $this->loan_model->calculate_repayment_due($LID, $today);
        $amount_due = isset($due->suggested_amount) ? round((float) $due->suggested_amount, 2) : 0.0;
        $minimum = isset($due->minimum_to_apply) ? round((float) $due->minimum_to_apply, 2) : 0.0;

        $next = $this->db->select('repaydate, repayamount')
            ->where('LID', $LID)->where('PIN', $ctx->PIN)->where('status', 0)
            ->order_by('installment_number', 'ASC')->limit(1)
            ->get('loan_contract_repayment_schedule')->row();
        $next_amount = $next ? round((float) $next->repayamount, 2) : 0.0;

        $floor = $this->paymongo->min_amount_pesos();
        $options = array();
        if ($amount_due > 0) {
            $options[] = array('key' => 'due', 'label' => !empty($due->has_overdue) ? 'Amount due (incl. overdue)' : 'Amount due now', 'amount' => min($amount_due, $payoff));
        } elseif ($next_amount > 0) {
            $options[] = array('key' => 'due', 'label' => 'Next installment' . ($next ? ' (' . date('M j', strtotime($next->repaydate)) . ')' : ''), 'amount' => min($next_amount, $payoff));
        }
        $options[] = array('key' => 'payoff', 'label' => 'Full payoff', 'amount' => $payoff);

        $min_custom = max($floor, $minimum);
        return array(
            'blocked' => null,
            'loan' => $loan,
            'options' => $options,
            'payoff' => $payoff,
            'minimum' => round(min($min_custom, $payoff), 2),
            'has_overdue' => !empty($due->has_overdue),
            'fee' => $this->paymongo->fee_settings(),
        );
    }

    // ------------------------------------------------------------------
    // Attempts
    // ------------------------------------------------------------------

    /**
     * Create (or reuse) a QR for a loan payment.
     * @return array('ok' => bool, 'message' => string, 'attempt' => object)
     */
    public function start($ctx, $LID, $option, $custom_amount = null) {
        $ready = $this->readiness($ctx->PIN);
        if (!$ready['ok']) {
            return array('ok' => false, 'message' => $ready['message']);
        }
        $opts = $this->pay_options($ctx, $LID);
        if (!empty($opts['blocked'])) {
            return array('ok' => false, 'message' => $opts['blocked']);
        }

        $amount = 0.0;
        if ($option === 'custom') {
            $amount = round((float) str_replace(',', '', (string) $custom_amount), 2);
            if ($amount < $opts['minimum']) {
                return array('ok' => false, 'message' => 'The minimum payment for this loan is PHP ' . number_format($opts['minimum'], 2) . '.');
            }
            if ($amount > $opts['payoff'] + 0.009) {
                return array('ok' => false, 'message' => 'The amount is more than your payoff balance of PHP ' . number_format($opts['payoff'], 2) . '.');
            }
        } else {
            foreach ($opts['options'] as $o) {
                if ($o['key'] === $option) {
                    $amount = (float) $o['amount'];
                }
            }
        }
        if ($amount <= 0) {
            return array('ok' => false, 'message' => 'Please choose an amount to pay.');
        }
        if ($amount < $this->paymongo->min_amount_pesos()) {
            return array('ok' => false, 'message' => 'Online payments must be at least PHP ' . number_format($this->paymongo->min_amount_pesos(), 2) . '.');
        }

        $plan = $this->loan_model->plan_loan_repayment_applications($LID, $amount, date('Y-m-d'));
        if (empty($plan['success'])) {
            return array('ok' => false, 'message' => isset($plan['message']) ? strip_tags($plan['message']) : 'This amount cannot be applied to the loan.');
        }

        $fee = $this->paymongo->compute_fee($amount);
        $centavos = $this->paymongo->to_centavos($fee['charged']);

        $live = $this->open_attempt_for_loan($ctx, $LID, 'pending');
        if ($live && (int) $live->amount_centavos === (int) $centavos && $live->expires_at && strtotime($live->expires_at) > time() + 60) {
            return array('ok' => true, 'message' => '', 'attempt' => $live);
        }
        $this->cancel_open_attempts($ctx, $LID);

        $reference = $this->next_reference();
        $qr = $this->paymongo->create_qrph($fee['charged'], $this->paymongo->description_prefix() . ' ' . $LID . ' (' . $ctx->member_id . ')', array(
            'reference_no' => $reference,
            'member_id' => $ctx->member_id,
            'loan_id' => $LID,
            'context' => 'member_loan_app',
        ));
        if (!$qr) {
            log_message('error', 'Member app PayMongo create_qrph failed: ' . $this->paymongo->last_error());
            return array('ok' => false, 'message' => 'We could not create the QR code right now. Please try again in a moment.');
        }

        $now = date('Y-m-d H:i:s');
        $this->db->insert(self::TABLE, array(
            'reference_no' => $reference,
            'LID' => $LID,
            'PID' => $ctx->PID,
            'member_id' => $ctx->member_id,
            'user_id' => $ctx->user_id,
            'PIN' => $ctx->PIN,
            'channel' => 'qrph',
            'pay_option' => in_array($option, array('due', 'payoff', 'custom'), true) ? $option : 'custom',
            'amount' => $amount,
            'fee_amount' => $fee['fee'],
            'charged_amount' => $fee['charged'],
            'amount_centavos' => $centavos,
            'intent_id' => $qr['intent_id'],
            'payment_id' => $qr['payment_id'] !== '' ? $qr['payment_id'] : null,
            'qr_image_url' => $qr['qr_image_url'],
            'qr_test_url' => $qr['test_url'] !== '' ? $qr['test_url'] : null,
            'status' => 'pending',
            'expires_at' => $qr['expires_at'] !== '' ? $qr['expires_at'] : date('Y-m-d H:i:s', time() + self::QR_TTL_MINUTES * 60),
            'create_date_time' => $now,
        ));
        return array('ok' => true, 'message' => '', 'attempt' => $this->by_reference($reference));
    }

    public function by_reference($reference) {
        return $this->db->where('reference_no', $reference)->limit(1)->get(self::TABLE)->row();
    }

    public function by_id($id) {
        return $this->db->where('id', (int) $id)->limit(1)->get(self::TABLE)->row();
    }

    public function by_intent($intent_id) {
        return $this->db->where('intent_id', (string) $intent_id)->limit(1)->get(self::TABLE)->row();
    }

    /** The attempt, only if it belongs to the signed-in member. */
    public function for_member($ctx, $id) {
        return $this->db->where('id', (int) $id)
            ->where('PID', $ctx->PID)
            ->where('PIN', $ctx->PIN)
            ->limit(1)->get(self::TABLE)->row();
    }

    /**
     * 'pending'       -> a live QR for this loan
     * 'paid_unposted' -> a paid attempt whose Cash Receipt is not yet applied
     */
    private function open_attempt_for_loan($ctx, $LID, $kind) {
        if ($kind === 'pending') {
            return $this->db->where('LID', $LID)->where('PID', $ctx->PID)->where('PIN', $ctx->PIN)
                ->where('status', 'pending')
                ->where('expires_at >', date('Y-m-d H:i:s'))
                ->order_by('id', 'DESC')->limit(1)->get(self::TABLE)->row();
        }
        $rows = $this->db->where('LID', $LID)->where('PID', $ctx->PID)->where('PIN', $ctx->PIN)
            ->where('status', 'paid')
            ->order_by('id', 'DESC')->limit(5)->get(self::TABLE)->result();
        foreach ($rows as $r) {
            if (!$r->cash_receipt_id) {
                return $r;
            }
            $cr = $this->db->where('id', $r->cash_receipt_id)->limit(1)->get('cash_receipts')->row();
            if ($cr && empty($cr->cancelled) && empty($cr->loan_repayment_applied)) {
                return $r;
            }
        }
        return null;
    }

    private function cancel_open_attempts($ctx, $LID) {
        $this->db->where('LID', $LID)->where('PID', $ctx->PID)->where('PIN', $ctx->PIN)
            ->where('status', 'pending')
            ->update(self::TABLE, array('status' => 'cancelled', 'update_date_time' => date('Y-m-d H:i:s')));
    }

    private function next_reference() {
        $prefix = 'LOP-' . date('Ymd') . '-';
        $row = $this->db->select('reference_no')->like('reference_no', $prefix, 'after')
            ->order_by('reference_no', 'DESC')->limit(1)->get(self::TABLE)->row();
        $n = $row ? ((int) substr($row->reference_no, -4)) + 1 : 1;
        return $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    // ------------------------------------------------------------------
    // Status refresh + settlement
    // ------------------------------------------------------------------

    /**
     * Re-check a pending attempt with PayMongo and settle it when paid.
     * Returns the fresh row.
     */
    public function refresh($attempt, $source = 'poll') {
        if (!$attempt || $attempt->status === 'paid' || $attempt->status === 'failed') {
            return $attempt;
        }
        if ($attempt->status !== 'pending' && $source !== 'webhook') {
            return $attempt;
        }
        $intent = $this->paymongo->get_intent($attempt->intent_id);
        if ($intent && $this->paymongo->intent_is_paid($intent)) {
            $this->settle($attempt->id, $this->paymongo->extract_paid_payment_id($intent), $intent, $source);
        } elseif ($intent && in_array($this->paymongo->intent_status($intent), array('cancelled', 'failed'), true)) {
            $this->db->where('id', $attempt->id)->where('status', 'pending')
                ->update(self::TABLE, array('status' => 'failed', 'update_date_time' => date('Y-m-d H:i:s')));
        } elseif ($attempt->expires_at && strtotime($attempt->expires_at) < time() - 120) {
            $this->db->where('id', $attempt->id)->where('status', 'pending')
                ->update(self::TABLE, array('status' => 'expired', 'update_date_time' => date('Y-m-d H:i:s')));
        } elseif (!$intent) {
            $this->db->where('id', $attempt->id)->update(self::TABLE, array('last_error' => $this->paymongo->last_error()));
        }
        return $this->by_id($attempt->id);
    }

    /**
     * Mark the attempt paid and create the unposted Cash Receipt.
     * Safe to call twice: a paid attempt is returned untouched.
     */
    public function settle($id, $payment_id, $intent, $source) {
        // Claim: only the request that moves the row out of pending/cancelled/expired
        // creates the Cash Receipt. A paid QR that we had already replaced is still
        // real money, so cancelled/expired rows can be claimed too.
        $now = date('Y-m-d H:i:s');
        $this->db->trans_begin();
        $row = $this->db->query('SELECT * FROM `' . self::TABLE . '` WHERE id = ? FOR UPDATE', array((int) $id))->row();
        if (!$row) {
            $this->db->trans_rollback();
            return array('ok' => false, 'message' => 'Payment not found.');
        }
        if ($row->status === 'paid') {
            $this->db->trans_commit();
            return array('ok' => true, 'already' => true);
        }
        if ($row->status === 'failed') {
            $this->db->trans_rollback();
            return array('ok' => false, 'message' => 'Payment is failed.');
        }
        $claim = array(
            'status' => 'paid',
            'paid_at' => $now,
            'payment_id' => $payment_id !== '' ? $payment_id : $row->payment_id,
            'update_date_time' => $now,
        );
        if ($source === 'webhook') {
            $claim['webhook_received_at'] = $now;
        }
        $this->db->where('id', $row->id)->update(self::TABLE, $claim);
        $this->db->trans_commit();

        $captured = $this->paymongo->intent_amount_centavos($intent);
        if ($captured > 0 && $captured !== (int) $row->amount_centavos) {
            $this->db->where('id', $row->id)->update(self::TABLE, array(
                'settle_error' => 'PayMongo captured ' . number_format($captured / 100, 2) . ' but the attempt expected '
                    . number_format($row->amount_centavos / 100, 2) . '. No Cash Receipt was created - record it manually.',
            ));
            log_message('error', 'Member app payment ' . $row->reference_no . ' amount mismatch.');
            return array('ok' => true, 'needs_review' => true);
        }

        $result = $this->issue_receipts($row);
        if (!$result['ok']) {
            log_message('error', 'Member app payment ' . $row->reference_no . ': ' . $result['message']);
        }
        return array('ok' => true);
    }

    /** True when a paid attempt still lacks its loan receipt or its fee receipt. */
    public function needs_receipt($row) {
        return $row && $row->status === 'paid'
            && (!$row->cash_receipt_id || ((float) $row->fee_amount > 0 && empty($row->fee_receipt_id)));
    }

    /**
     * Staff retry for a paid attempt whose loan or fee Cash Receipt is missing
     * (for example a GL account was not set at the time).
     */
    public function retry_receipt($id) {
        $this->db->trans_begin();
        $row = $this->db->query('SELECT * FROM `' . self::TABLE . '` WHERE id = ? FOR UPDATE', array((int) $id))->row();
        if (!$this->needs_receipt($row)) {
            $this->db->trans_rollback();
            return array('ok' => false, 'message' => 'Only a paid payment with a missing Cash Receipt can be retried.');
        }
        $this->db->where('id', $row->id)->update(self::TABLE, array('settle_error' => 'Retrying...', 'update_date_time' => date('Y-m-d H:i:s')));
        $this->db->trans_commit();

        return $this->issue_receipts($row);
    }

    /**
     * Create whichever of the two unposted Cash Receipts is still missing:
     * the loan repayment (applied to the loan when Finance posts it) and, when
     * a convenience fee was charged, a separate fee receipt that never touches
     * the loan, because posting applies a loan receipt's whole total to the loan.
     */
    private function issue_receipts($row) {
        $update = array('update_date_time' => date('Y-m-d H:i:s'));
        $notes = array();
        $created = array();
        $ok = true;

        if (!$row->cash_receipt_id) {
            $cr = $this->create_cash_receipt($row);
            if ($cr['ok']) {
                $update['cash_receipt_id'] = $cr['id'];
                $update['receipt_no'] = $cr['receipt_no'];
                $update['settled_at'] = date('Y-m-d H:i:s');
                $created[] = $cr['receipt_no'];
                if (!empty($cr['note'])) {
                    $notes[] = $cr['note'];
                }
            } else {
                $notes[] = $cr['message'];
                $ok = false;
            }
        }

        if ((float) $row->fee_amount > 0 && empty($row->fee_receipt_id)) {
            $fee = $this->create_fee_receipt($row);
            if ($fee['ok']) {
                $update['fee_receipt_id'] = $fee['id'];
                $update['fee_receipt_no'] = $fee['receipt_no'];
                $created[] = $fee['receipt_no'] . ' (fee)';
            } else {
                $notes[] = $fee['message'];
                $ok = false;
            }
        }

        $update['settle_error'] = $notes ? substr(implode(' ', $notes), 0, 500) : null;
        $this->db->where('id', $row->id)->update(self::TABLE, $update);

        if (!$ok) {
            return array('ok' => false, 'message' => implode(' ', $notes));
        }
        return array('ok' => true, 'message' => 'Cash Receipt ' . implode(' and ', $created) . ' created (unposted).');
    }

    /**
     * Fee receipt: debit the PayMongo payment method account, credit the fee
     * income account from Settings. Typed "member" so posting never applies it to a loan.
     */
    private function create_fee_receipt($row) {
        $this->load->model('cash_receipt_model');
        $fee = round((float) $row->fee_amount, 2);
        $fee_account = $this->paymongo->fee_gl_account();

        $previous_user = $this->session->userdata('user_id');
        $this->session->userdata['user_id'] = (int) $row->user_id;
        try {
            if ($fee_account === '') {
                return array('ok' => false, 'message' => 'Convenience fee of PHP ' . number_format($fee, 2) . ' has no fee GL account; set it in Member App & PayMongo settings, then use Create receipt.');
            }
            $pm = $this->payment_method($row->PIN);
            if (!$pm || trim((string) $pm->gl_account_code) === '') {
                return array('ok' => false, 'message' => 'Payment method "' . $this->paymongo->payment_method_name() . '" has no GL account; the fee receipt was not created.');
            }

            $receipt_no = $this->next_free_receipt_no();
            $receipt = array(
                'receipt_no' => $receipt_no,
                'receipt_date' => date('Y-m-d'),
                'received_from' => $this->member_label($row),
                'received_from_type' => 'member',
                'payment_method' => $pm->name,
                'cheque_no' => '',
                'bank_name' => 'PayMongo ' . $row->reference_no,
                'description' => 'Convenience fee - PayMongo QR Ph ' . $row->reference_no . ' (loan ' . $row->LID . ')',
                'total_amount' => $fee,
                'cancelled' => 0,
                'loan_repayment_lid' => null,
                'createdby' => (int) $row->user_id,
                'PIN' => $row->PIN,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $lines = array(
                array('account' => $pm->gl_account_code, 'debit' => $fee, 'credit' => 0, 'amount' => $fee, 'description' => 'Convenience fee ' . $row->reference_no),
                array('account' => $fee_account, 'debit' => 0, 'credit' => $fee, 'amount' => $fee, 'description' => 'Convenience fee ' . $row->reference_no),
            );
            $id = $this->cash_receipt_model->create_cash_receipt($receipt, $lines);
            if (!$id) {
                return array('ok' => false, 'message' => 'The fee Cash Receipt could not be created; create it manually.');
            }
            $saved = $this->db->where('receipt_no', $receipt_no)->where('PIN', $row->PIN)->order_by('id', 'DESC')->limit(1)->get('cash_receipts')->row();
            return array('ok' => true, 'id' => $saved ? (int) $saved->id : (int) $id, 'receipt_no' => $receipt_no);
        } catch (Exception $e) {
            log_message('error', 'Member app fee receipt exception: ' . $e->getMessage());
            return array('ok' => false, 'message' => 'Fee Cash Receipt error: ' . $e->getMessage());
        } finally {
            $this->session->userdata['user_id'] = $previous_user;
        }
    }

    private function next_free_receipt_no() {
        $receipt_no = $this->cash_receipt_model->get_next_shared_receipt_no();
        $guard = 0;
        while ($this->cash_receipt_model->receipt_no_exists($receipt_no) && $guard++ < 20) {
            $num = (int) preg_replace('/\D/', '', $receipt_no) + 1;
            $receipt_no = 'CR-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
        }
        return $receipt_no;
    }

    private function member_label($row) {
        $member = $this->db->where('PID', $row->PID)->where('PIN', $row->PIN)->limit(1)->get('members')->row();
        $name = $member ? trim(preg_replace('/\s+/', ' ', $member->firstname . ' ' . $member->middlename . ' ' . $member->lastname)) : $row->member_id;
        return $name . ' (' . $row->member_id . ')';
    }

    /**
     * Build the loan-repayment Cash Receipt exactly as the Cash Receipt screen
     * would, acting as the member's user so PIN scoping and createdby are right.
     * It is left unposted for Finance.
     */
    private function create_cash_receipt($row) {
        $this->load->model('loan_model');
        $this->load->model('setting_model');
        $this->load->model('cash_receipt_model');

        $previous_user = $this->session->userdata('user_id');
        $this->session->userdata['user_id'] = (int) $row->user_id;

        try {
            $pm = $this->payment_method($row->PIN);
            if (!$pm || trim((string) $pm->gl_account_code) === '') {
                return array('ok' => false, 'message' => 'Payment method "' . $this->paymongo->payment_method_name() . '" has no GL account; create the Cash Receipt manually.');
            }
            $amount = round((float) $row->amount, 2);
            $today = date('Y-m-d');
            $worksheet = $this->loan_model->get_cash_receipt_repayment_worksheet($row->LID, $pm->id, $today, $amount);
            if (!$worksheet) {
                return array('ok' => false, 'message' => 'Loan ' . $row->LID . ' could not be read; create the Cash Receipt manually.');
            }

            $note = '';
            $lines = array();
            $debit = 0.0;
            $credit = 0.0;
            foreach ($worksheet['line_items'] as $li) {
                $d = $li['debit'] === '' ? 0.0 : round((float) $li['debit'], 2);
                $c = $li['credit'] === '' ? 0.0 : round((float) $li['credit'], 2);
                if ($d <= 0 && $c <= 0) {
                    continue;
                }
                $lines[] = array('account' => $li['account'], 'debit' => $d, 'credit' => $c, 'amount' => $d + $c, 'description' => $li['description']);
                $debit += $d;
                $credit += $c;
            }
            $cash_total = round((float) $worksheet['payment_amount'], 2);
            if (abs($cash_total - $amount) > 0.009 || abs($debit - $credit) > 0.009 || empty($lines)) {
                // The engine could not split this exact amount; post it to principal and flag it.
                $loan = $this->loan_model->loan_info($row->LID)->row();
                $product = $loan ? $this->setting_model->loanproduct($loan->product_type)->row() : null;
                $principal_account = $product ? $product->loan_principle_account : null;
                if (!$principal_account) {
                    return array('ok' => false, 'message' => 'Could not build the receipt lines for ' . $row->LID . '; create the Cash Receipt manually.');
                }
                $lines = array(
                    array('account' => $pm->gl_account_code, 'debit' => $amount, 'credit' => 0, 'amount' => $amount, 'description' => 'Loan Repayment ' . $row->LID),
                    array('account' => $principal_account, 'debit' => 0, 'credit' => $amount, 'amount' => $amount, 'description' => 'Loan principal ' . $row->LID),
                );
                $note = 'Receipt lines need review: the amount could not be split automatically (all on principal).';
            }

            $receipt_no = $this->next_free_receipt_no();

            $receipt = array(
                'receipt_no' => $receipt_no,
                'receipt_date' => $today,
                'received_from' => $this->member_label($row),
                'received_from_type' => 'loan_repayment',
                'payment_method' => $pm->name,
                'cheque_no' => '',
                'bank_name' => 'PayMongo ' . $row->reference_no,
                'description' => 'Loan Repayment ' . $row->LID . ' - PayMongo QR Ph ' . $row->reference_no . ($row->payment_id ? ' (' . $row->payment_id . ')' : ''),
                'total_amount' => $amount,
                'cancelled' => 0,
                'loan_repayment_lid' => $row->LID,
                'createdby' => (int) $row->user_id,
                'PIN' => $row->PIN,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $id = $this->cash_receipt_model->create_cash_receipt($receipt, $lines);
            if (!$id) {
                return array('ok' => false, 'message' => 'Cash Receipt could not be created; create it manually.');
            }
            $saved = $this->db->where('receipt_no', $receipt_no)->where('PIN', $row->PIN)->order_by('id', 'DESC')->limit(1)->get('cash_receipts')->row();
            return array('ok' => true, 'id' => $saved ? (int) $saved->id : (int) $id, 'receipt_no' => $receipt_no, 'note' => $note);
        } catch (Exception $e) {
            log_message('error', 'Member app settle exception: ' . $e->getMessage());
            return array('ok' => false, 'message' => 'Cash Receipt error: ' . $e->getMessage());
        } finally {
            $this->session->userdata['user_id'] = $previous_user;
        }
    }

    // ------------------------------------------------------------------
    // Webhook
    // ------------------------------------------------------------------

    /** Find the attempt a PayMongo event refers to. */
    public function resolve_from_payload($payload) {
        $resource = isset($payload['data']['attributes']['data']) ? $payload['data']['attributes']['data'] : array();
        $attrs = isset($resource['attributes']) ? $resource['attributes'] : array();
        $candidates = array();
        if (!empty($attrs['payment_intent_id'])) {
            $candidates[] = array('intent', $attrs['payment_intent_id']);
        }
        if (!empty($resource['id']) && strpos($resource['id'], 'pi_') === 0) {
            $candidates[] = array('intent', $resource['id']);
        }
        if (!empty($attrs['metadata']['reference_no'])) {
            $candidates[] = array('reference', $attrs['metadata']['reference_no']);
        }
        foreach ($candidates as $c) {
            $row = $c[0] === 'intent' ? $this->by_intent($c[1]) : $this->by_reference($c[1]);
            if ($row) {
                return $row;
            }
        }
        return null;
    }

    // ------------------------------------------------------------------
    // Staff report
    // ------------------------------------------------------------------

    public function report($filters = array()) {
        $this->db->select('op.*, cr.loan_repayment_applied, cr.cancelled AS cr_cancelled, m.firstname, m.lastname', false);
        $this->db->from(self::TABLE . ' op');
        $this->db->join('cash_receipts cr', 'cr.id = op.cash_receipt_id', 'left');
        $this->db->join('members m', 'm.PID = op.PID AND m.PIN = op.PIN', 'left');
        $this->db->where('op.PIN', current_user()->PIN);
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'awaiting_posting') {
                $this->db->where('op.status', 'paid');
                $this->db->where('(cr.loan_repayment_applied IS NULL OR cr.loan_repayment_applied = 0)', null, false);
            } elseif ($filters['status'] === 'needs_review') {
                $this->db->where('op.status', 'paid');
                $this->db->where('op.settle_error IS NOT NULL', null, false);
            } else {
                $this->db->where('op.status', $filters['status']);
            }
        }
        if (!empty($filters['from'])) {
            $this->db->where('op.create_date_time >=', $filters['from'] . ' 00:00:00');
        }
        if (!empty($filters['to'])) {
            $this->db->where('op.create_date_time <=', $filters['to'] . ' 23:59:59');
        }
        if (!empty($filters['key'])) {
            $k = $this->db->escape_like_str($filters['key']);
            $this->db->where("(op.reference_no LIKE '%$k%' OR op.LID LIKE '%$k%' OR op.member_id LIKE '%$k%' OR op.receipt_no LIKE '%$k%' OR op.fee_receipt_no LIKE '%$k%')", null, false);
        }
        $this->db->order_by('op.id', 'DESC');
        $this->db->limit(500);
        return $this->db->get()->result();
    }
}
