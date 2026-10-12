<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Memberapp_model - data for the Member Loan App (/customer_login).
 *
 * Account activation / password reset with one-time codes, and the "snapshot":
 * one JSON payload with everything the app shows, which the phone stores for
 * offline viewing. All loan figures come from Loan_model so the app and the
 * staff screens never disagree.
 */
class Memberapp_model extends CI_Model {

    const OTP_TTL_MINUTES = 10;
    const OTP_MAX_ATTEMPTS = 5;

    public function __construct() {
        parent::__construct();
    }

    // ==================================================================
    // Member lookup (before sign-in: no session, so no PIN scoping helpers)
    // ==================================================================

    /**
     * Find exactly one active member by Member ID. Returns null when none or
     * when the ID is ambiguous across cooperatives.
     */
    public function find_member_by_member_id($member_id) {
        $member_id = trim((string) $member_id);
        if ($member_id === '') {
            return null;
        }
        $rows = $this->db->where('member_id', $member_id)
            ->where('status !=', 2)
            ->limit(2)
            ->get('members')->result();
        if (count($rows) !== 1) {
            return null;
        }
        return $rows[0];
    }

    public function member_contact_row($PID) {
        return $this->db->where('PID', $PID)->limit(1)->get('members_contact')->row();
    }

    /** users row for this member (Ion Auth identity = Member ID). */
    public function member_user($member) {
        return $this->db->where('username', $member->member_id)
            ->limit(1)
            ->get('users')->row();
    }

    /**
     * Check the identity details a member types on the activation / reset form.
     * Birthdate must match members.dob, and the phone or email must match the
     * contact on file. Returns array('ok', 'channel', 'destination', 'masked').
     */
    public function verify_identity($member, $dob, $contact_value) {
        $fail = array('ok' => false);
        if (!$member) {
            return $fail;
        }
        $dob = $this->normalize_date($dob);
        $member_dob = $this->normalize_date($member->dob);
        if ($dob === '' || $member_dob === '' || $dob !== $member_dob) {
            return $fail;
        }
        $contact = $this->member_contact_row($member->PID);
        if (!$contact) {
            return $fail;
        }
        $value = trim((string) $contact_value);
        if (strpos($value, '@') !== false) {
            $email = strtolower(trim((string) $contact->email));
            if ($email !== '' && $email === strtolower($value)) {
                return array('ok' => true, 'channel' => 'email', 'destination' => $email, 'masked' => $this->mask_email($email));
            }
            return $fail;
        }
        $typed = $this->phone_tail($value);
        foreach (array('phone1', 'phone2') as $field) {
            $on_file = isset($contact->$field) ? (string) $contact->$field : '';
            if ($typed !== '' && $this->phone_tail($on_file) === $typed) {
                return array('ok' => true, 'channel' => 'sms', 'destination' => $on_file, 'masked' => $this->mask_phone($on_file));
            }
        }
        return $fail;
    }

    // ==================================================================
    // One-time codes
    // ==================================================================

    /**
     * Create a code and try to deliver it. Older unused codes for the same
     * purpose are retired. Returns array('ok', 'delivered', 'code' (dev only), 'message').
     */
    public function issue_otp($member, $purpose, $channel, $destination, $masked, $staff_user_id = null) {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now = date('Y-m-d H:i:s');

        $this->db->where('member_id', $member->member_id)
            ->where('purpose', $purpose)
            ->where('used_at IS NULL', null, false)
            ->update('member_app_otp', array('used_at' => $now));

        $this->db->insert('member_app_otp', array(
            'member_id' => $member->member_id,
            'PIN' => $member->PIN,
            'purpose' => $purpose,
            'code_hash' => password_hash($code, PASSWORD_DEFAULT),
            'channel' => $channel,
            'destination' => $masked,
            'attempts' => 0,
            'expires_at' => date('Y-m-d H:i:s', time() + self::OTP_TTL_MINUTES * 60),
            'ip' => $this->input->ip_address(),
            'created_by' => $staff_user_id,
            'created_at' => $now,
        ));

        $delivered = false;
        if ($channel === 'email') {
            $delivered = $this->send_code_email($destination, $code, $purpose);
        } elseif ($channel === 'sms') {
            $delivered = $this->send_code_sms($destination, $code, $purpose);
        } elseif ($channel === 'staff') {
            $delivered = true;
        }

        $out = array('ok' => true, 'delivered' => $delivered, 'masked' => $masked);
        if ($channel === 'staff' || $this->is_local_dev()) {
            $out['code'] = $code;
        }
        return $out;
    }

    /**
     * Check a code. On success the code is marked verified (not yet used), so
     * the next step (set password) can confirm it once more and consume it.
     */
    public function verify_otp($member_id, $purpose, $code) {
        $row = $this->latest_otp($member_id, array($purpose, 'staff'));
        if (!$row) {
            return array('ok' => false, 'message' => 'The code has expired. Please request a new one.');
        }
        if ((int) $row->attempts >= self::OTP_MAX_ATTEMPTS) {
            $this->db->where('id', $row->id)->update('member_app_otp', array('used_at' => date('Y-m-d H:i:s')));
            return array('ok' => false, 'message' => 'Too many wrong codes. Please request a new one.');
        }
        $code = preg_replace('/\D/', '', (string) $code);
        if ($code === '' || !password_verify($code, $row->code_hash)) {
            $this->db->set('attempts', 'attempts+1', false)->where('id', $row->id)->update('member_app_otp');
            $left = self::OTP_MAX_ATTEMPTS - ((int) $row->attempts + 1);
            return array('ok' => false, 'message' => 'Incorrect code.' . ($left > 0 ? ' ' . $left . ' tries left.' : ''));
        }
        $this->db->where('id', $row->id)->update('member_app_otp', array('verified_at' => date('Y-m-d H:i:s')));
        return array('ok' => true, 'otp_id' => (int) $row->id);
    }

    /** A verified, unused, unexpired code row (used by the set-password step). */
    public function verified_otp($otp_id, $member_id, $purpose) {
        return $this->db->where('id', (int) $otp_id)
            ->where('member_id', $member_id)
            ->where_in('purpose', array($purpose, 'staff'))
            ->where('verified_at IS NOT NULL', null, false)
            ->where('used_at IS NULL', null, false)
            ->where('expires_at >=', date('Y-m-d H:i:s', time() - 15 * 60))
            ->limit(1)
            ->get('member_app_otp')->row();
    }

    /**
     * Codes are shown on screen only for development requests made on the
     * server itself; index.php may be left on "development" in production.
     */
    private function is_local_dev() {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        return defined('ENVIRONMENT') && ENVIRONMENT === 'development' && in_array($ip, array('127.0.0.1', '::1'), true);
    }

    public function consume_otp($otp_id) {
        $this->db->where('id', (int) $otp_id)->update('member_app_otp', array('used_at' => date('Y-m-d H:i:s')));
    }

    private function latest_otp($member_id, $purposes) {
        return $this->db->where('member_id', $member_id)
            ->where_in('purpose', (array) $purposes)
            ->where('used_at IS NULL', null, false)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('member_app_otp')->row();
    }

    private function send_code_email($email, $code, $purpose) {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $what = $purpose === 'reset' ? 'reset your password' : 'activate your account';
        $message = '<p>Your TAPSTEMCO Loan App code is:</p>'
            . '<p style="font-size:24px;font-weight:bold;letter-spacing:4px">' . $code . '</p>'
            . '<p>Use it within ' . self::OTP_TTL_MINUTES . ' minutes to ' . $what . '. '
            . 'If you did not ask for this, you can ignore this email.</p>';
        try {
            return (bool) @$this->maildata->send_email('TAPSTEMCO Loan App code', $message, array($email));
        } catch (Exception $e) {
            return false;
        }
    }

    private function send_code_sms($phone, $code, $purpose) {
        if (!defined('API_URL') || stripos(API_URL, 'http') !== 0) {
            return false;
        }
        $this->load->library('smssending');
        $message = 'TAPSTEMCO Loan App code: ' . $code . '. Valid for ' . self::OTP_TTL_MINUTES . ' minutes. Do not share this code.';
        $result = @$this->smssending->send_sms_single(defined('SENDER') ? SENDER : 'TAPSTEMCO', $message, $phone);
        return is_string($result) && stripos($result, 'NETWORK') === false;
    }

    // ==================================================================
    // Account create / password
    // ==================================================================

    /**
     * Make sure the member has a Members-group login, then set the password.
     * Mirrors member.php registration (username/email = Member ID, group 3).
     * Clears the plain-text default password columns.
     */
    public function set_member_password($member, $password) {
        $user = $this->member_user($member);
        if (!$user) {
            $company = $this->db->where('PIN', $member->PIN)->limit(1)->get('companyinfo')->row();
            $this->ion_auth->register($member->member_id, $password, $member->member_id, array(
                'first_name' => $member->firstname,
                'last_name' => $member->lastname,
                'member_id' => $member->member_id,
                'oldpass' => '',
                'MID' => $member->id,
                'PIN' => $member->PIN,
                'company' => $company ? $company->name : '',
            ), array(3));
            $user = $this->member_user($member);
            if (!$user) {
                return false;
            }
        } else {
            if (!$this->ion_auth->update($user->id, array('password' => $password))) {
                return false;
            }
        }

        $clean = array('oldpass' => '', 'active' => 1, 'forgotten_password_code' => null);
        if ($this->db->field_exists('defaultpass', 'users')) {
            $clean['defaultpass'] = '';
        }
        if (empty($user->member_id)) {
            $clean['member_id'] = $member->member_id;
        }
        $this->db->where('id', $user->id)->update('users', $clean);

        $members_group = $this->db->where('name', 'Members')->limit(1)->get('groups')->row();
        $group_id = $members_group ? (int) $members_group->id : 3;
        $in_group = $this->db->where('user_id', $user->id)->where('group_id', $group_id)->count_all_results('users_groups');
        if (!$in_group) {
            $this->db->insert('users_groups', array('user_id' => $user->id, 'group_id' => $group_id));
        }
        return (int) $user->id;
    }

    // ==================================================================
    // Snapshot (everything the app shows; cached on the phone)
    // ==================================================================

    public function snapshot($ctx) {
        $this->load->model('loan_model');
        $this->load->model('setting_model');
        $this->load->model('cash_receipt_model');
        $this->lang->load('loan');

        $today = date('Y-m-d');
        $pin = $ctx->PIN;
        $PID = $ctx->PID;

        $loans = array();
        $upcoming = array();
        $kpi = array(
            'total_outstanding' => 0.0,
            'amount_due' => 0.0,
            'total_paid' => 0.0,
            'active_loans' => 0,
            'pending_applications' => 0,
            'has_overdue' => false,
            'next_due_date' => null,
            'next_due_amount' => 0.0,
            'next_due_lid' => null,
        );

        $contract_rows = $this->db->query(
            "SELECT lc.*, ls.name AS status_name, lp.name AS product_name, li.name AS interval_name,
                    (SELECT MAX(e.createdon) FROM loan_contract_evaluation e WHERE e.LID = lc.LID AND e.PIN = lc.PIN) AS evaluation_date,
                    (SELECT MAX(a.createdon) FROM loan_contract_approve a WHERE a.LID = lc.LID AND a.PIN = lc.PIN) AS approval_date,
                    (SELECT MAX(d.disbursedate) FROM loan_contract_disburse d WHERE d.LID = lc.LID AND d.PIN = lc.PIN) AS disbursement_date"
            . $this->loan_model->_lifecycle_select_sql('lc') . "
             FROM loan_contract lc
             LEFT JOIN loan_status ls ON ls.code = lc.status
             LEFT JOIN loan_product lp ON lp.id = lc.product_type AND lp.PIN = lc.PIN
             LEFT JOIN loan_interval li ON li.id = lc.interval
             WHERE lc.PIN = ? AND lc.PID = ?
             ORDER BY lc.applicationdate DESC, lc.id DESC",
            array($pin, $PID)
        )->result();

        $online_pending = $this->online_pending_by_loan($ctx);

        foreach ($contract_rows as $row) {
            $row->name = $row->status_name;
            $life = $this->loan_model->resolve_loan_lifecycle($row);
            $status = (string) $row->status;
            $disbursed = ((string) $row->disburse === '1');

            $schedule = $this->schedule_rows($row->LID, $pin);
            $paid = $this->paid_totals($row->LID, $pin);

            $loan = array(
                'LID' => $row->LID,
                'product' => (string) $row->product_name,
                'status_code' => $status,
                'status_name' => (string) $row->status_name,
                'lifecycle' => $life['code'],
                'lifecycle_name' => $life['name'],
                'pill' => $life['pill'],
                'applicationdate' => $this->d($row->applicationdate),
                'evaluation_date' => $this->d($row->evaluation_date),
                'approval_date' => $this->d($row->approval_date),
                'release_date' => $this->d($row->disbursement_date),
                'purpose' => (string) $row->loan_purpose,
                'amount' => round((float) $row->basic_amount, 2),
                'total_loan' => round((float) $row->total_loan, 2),
                'total_interest' => round((float) $row->total_interest_amount, 2),
                'rate' => (float) $row->rate,
                'term' => (int) $row->number_istallment,
                'interval' => (string) ($row->interval_name ? $row->interval_name : 'Monthly'),
                'installment' => round((float) $row->installment_amount, 2),
                'disbursed' => $disbursed,
                'is_open' => $disbursed && $status !== '5',
                'outstanding' => 0.0,
                'principal_outstanding' => 0.0,
                'interest_outstanding' => 0.0,
                'penalty_outstanding' => 0.0,
                'amount_due' => 0.0,
                'minimum_to_apply' => 0.0,
                'has_overdue' => false,
                'overdue_count' => 0,
                'paid_total' => $paid['amount'],
                'paid_principal' => $paid['principal'],
                'paid_interest' => $paid['interest'],
                'paid_penalty' => $paid['penalty'],
                'progress_pct' => 0,
                'next_due_date' => null,
                'next_due_amount' => 0.0,
                'pending_payment' => isset($online_pending[$row->LID]) ? $online_pending[$row->LID] : null,
                'can_pay' => false,
                'schedule' => $schedule,
                'ledger' => array(),
                'timeline' => $this->timeline($row, $pin),
            );

            if ($loan['is_open']) {
                $breakdown = $this->loan_model->get_loan_outstanding_for_offset($row->LID);
                $due = $this->loan_model->calculate_repayment_due($row->LID, $today);
                if ($breakdown) {
                    $loan['outstanding'] = round((float) $breakdown['total'], 2);
                    $loan['principal_outstanding'] = round((float) $breakdown['principal'], 2);
                    $loan['interest_outstanding'] = round((float) $breakdown['interest'], 2);
                    $loan['penalty_outstanding'] = round((float) $breakdown['penalty'], 2);
                }
                $loan['amount_due'] = isset($due->suggested_amount) ? round((float) $due->suggested_amount, 2) : 0.0;
                $loan['minimum_to_apply'] = isset($due->minimum_to_apply) ? round((float) $due->minimum_to_apply, 2) : 0.0;
                $loan['has_overdue'] = !empty($due->has_overdue);
                $loan['overdue_count'] = isset($due->overdue_count) ? (int) $due->overdue_count : 0;

                foreach ($schedule as $s) {
                    if ((int) $s['status'] === 0) {
                        $loan['next_due_date'] = $s['date'];
                        $loan['next_due_amount'] = $s['amount'];
                        break;
                    }
                }

                $unposted = $this->cash_receipt_model->get_unposted_loan_repayment_for_loan($row->LID);
                if ($unposted && !$loan['pending_payment']) {
                    $loan['pending_payment'] = array('status' => 'awaiting_posting', 'receipt_no' => $unposted->receipt_no, 'amount' => round((float) $unposted->total_amount, 2), 'reference_no' => null);
                }
                $loan['can_pay'] = $loan['outstanding'] > 0.009 && empty($loan['pending_payment']);

                $ledger = $this->loan_model->get_loan_ledger_transactions($row->LID);
                foreach ($ledger as $l) {
                    $loan['ledger'][] = array(
                        'date' => $this->d($l->date),
                        'type' => $l->type,
                        'description' => (string) $l->description,
                        'debit' => round((float) $l->debit, 2),
                        'credit' => round((float) $l->credit, 2),
                        'principal' => $l->principle !== null ? round((float) $l->principle, 2) : null,
                        'interest' => $l->interest !== null ? round((float) $l->interest, 2) : null,
                        'penalty' => $l->penalt !== null ? round((float) $l->penalt, 2) : null,
                        'receipt' => isset($l->receipt) ? (string) $l->receipt : '',
                    );
                }

                $kpi['active_loans']++;
                $kpi['total_outstanding'] += $loan['outstanding'];
                $kpi['amount_due'] += $loan['amount_due'];
                if ($loan['has_overdue']) {
                    $kpi['has_overdue'] = true;
                }
                if ($loan['next_due_date'] && ($kpi['next_due_date'] === null || $loan['next_due_date'] < $kpi['next_due_date'])) {
                    $kpi['next_due_date'] = $loan['next_due_date'];
                    $kpi['next_due_amount'] = $loan['next_due_amount'];
                    $kpi['next_due_lid'] = $row->LID;
                }
                $open_count = 0;
                foreach ($schedule as $s) {
                    if ((int) $s['status'] === 0 && $open_count < 3) {
                        $upcoming[] = array('LID' => $row->LID, 'product' => $loan['product'], 'date' => $s['date'], 'amount' => $s['amount'], 'installment' => $s['no']);
                        $open_count++;
                    }
                }
            } elseif (in_array($status, array('0', '1', '3', '4', '6', '9'), true) && !$disbursed) {
                $kpi['pending_applications']++;
            }

            if ($loan['amount'] > 0) {
                $loan['progress_pct'] = (int) max(0, min(100, round(($loan['paid_principal'] / $loan['amount']) * 100)));
                if ($status === '5') {
                    $loan['progress_pct'] = 100;
                }
            }
            $kpi['total_paid'] += $loan['paid_total'];
            $loans[] = $loan;
        }

        foreach ($this->beginning_balance_rows($ctx) as $bb) {
            $loans[] = $bb;
        }

        usort($upcoming, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
        $upcoming = array_slice($upcoming, 0, 6);

        foreach (array('total_outstanding', 'amount_due', 'total_paid') as $k) {
            $kpi[$k] = round($kpi[$k], 2);
        }

        $payments = $this->payment_history($ctx);
        $contact = $this->member_contact_row($PID);

        return array(
            'version' => 1,
            'synced_at' => date('c'),
            'member' => array(
                'member_id' => $ctx->member_id,
                'name' => $ctx->name,
                'full_name' => $ctx->full_name,
                'initials' => strtoupper(substr((string) $ctx->member->firstname, 0, 1) . substr((string) $ctx->member->lastname, 0, 1)),
                'photo' => member_avatar_url(isset($ctx->member->photo) ? $ctx->member->photo : '', isset($ctx->member->gender) ? $ctx->member->gender : ''),
                'joined' => $this->d(isset($ctx->member->joiningdate) ? $ctx->member->joiningdate : null),
                'phone' => $contact ? $this->mask_phone($contact->phone1) : '',
                'email' => $contact ? $this->mask_email($contact->email) : '',
            ),
            'kpi' => $kpi,
            'loans' => $loans,
            'upcoming' => $upcoming,
            'payments' => $payments,
            'charts' => $this->charts($loans, $payments),
            'comaker_requests' => $this->comaker_requests($ctx),
        );
    }

    /** Online attempts that are paid but whose Cash Receipt is not yet posted (or are still pending). */
    private function online_pending_by_loan($ctx) {
        $out = array();
        if (!$this->db->table_exists('loan_online_payments')) {
            return $out;
        }
        $rows = $this->db->where('PID', $ctx->PID)
            ->where('PIN', $ctx->PIN)
            ->where('status', 'paid')
            ->order_by('id', 'DESC')
            ->get('loan_online_payments')->result();
        foreach ($rows as $r) {
            if (isset($out[$r->LID])) {
                continue;
            }
            $applied = false;
            if ($r->cash_receipt_id) {
                $cr = $this->db->where('id', $r->cash_receipt_id)->limit(1)->get('cash_receipts')->row();
                $applied = $cr && !empty($cr->loan_repayment_applied);
                if ($cr && !empty($cr->cancelled)) {
                    $applied = true;
                }
            }
            if (!$applied) {
                $out[$r->LID] = array(
                    'status' => 'awaiting_posting',
                    'reference_no' => $r->reference_no,
                    'receipt_no' => $r->receipt_no,
                    'amount' => round((float) $r->amount, 2),
                    'paid_at' => $r->paid_at,
                );
            }
        }
        return $out;
    }

    private function schedule_rows($LID, $pin) {
        $rows = $this->db->select('installment_number, repaydate, repayamount, interest, principle, balance, status')
            ->where('LID', $LID)
            ->where('PIN', $pin)
            ->order_by('installment_number', 'ASC')
            ->get('loan_contract_repayment_schedule')->result();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'no' => (int) $r->installment_number,
                'date' => $this->d($r->repaydate),
                'amount' => round((float) $r->repayamount, 2),
                'principal' => round((float) $r->principle, 2),
                'interest' => round((float) $r->interest, 2),
                'balance' => round((float) $r->balance, 2),
                'status' => (int) $r->status,
            );
        }
        return $out;
    }

    private function paid_totals($LID, $pin) {
        $voided = $this->db->field_exists('is_voided', 'loan_contract_repayment') ? ' AND (is_voided IS NULL OR is_voided = 0)' : '';
        $row = $this->db->query(
            "SELECT COALESCE(SUM(amount),0) AS amount, COALESCE(SUM(principle),0) AS principal,
                    COALESCE(SUM(interest),0) AS interest, COALESCE(SUM(penalt),0) AS penalty
             FROM loan_contract_repayment WHERE LID = ? AND PIN = ?" . $voided,
            array($LID, $pin)
        )->row();
        return array(
            'amount' => round((float) $row->amount + (float) $row->penalty, 2),
            'principal' => round((float) $row->principal, 2),
            'interest' => round((float) $row->interest, 2),
            'penalty' => round((float) $row->penalty, 2),
        );
    }

    /** Application progress: Submitted -> Evaluated -> Approved -> Released -> Closed. */
    private function timeline($row, $pin) {
        $status = (string) $row->status;
        $eval = $this->db->select('status, comment, createdon')->where('LID', $row->LID)->where('PIN', $pin)
            ->order_by('createdon', 'DESC')->limit(1)->get('loan_contract_evaluation')->row();
        $appr = $this->db->select('status, comment, createdon')->where('LID', $row->LID)->where('PIN', $pin)
            ->order_by('createdon', 'DESC')->limit(1)->get('loan_contract_approve')->row();

        $steps = array();
        $steps[] = array('key' => 'submitted', 'label' => 'Application submitted', 'date' => $this->d($row->applicationdate), 'state' => 'done', 'note' => '');

        $eval_state = 'todo';
        $eval_note = '';
        if ($eval) {
            $eval_note = (string) $eval->comment;
            $eval_state = in_array((string) $eval->status, array('2', '7'), true) ? 'rejected' : ((string) $eval->status === '3' ? 'changes' : 'done');
        } elseif (in_array($status, array('0', '3'), true)) {
            $eval_state = 'current';
        }
        if ($status === '3') {
            $eval_state = 'changes';
        }
        $steps[] = array('key' => 'evaluated', 'label' => $eval_state === 'changes' ? 'Needs changes' : 'Credit evaluation', 'date' => $eval ? $this->d($eval->createdon) : null, 'state' => $eval_state, 'note' => $eval_note);

        $appr_state = 'todo';
        $appr_note = '';
        if ($appr) {
            $appr_note = (string) $appr->comment;
            $appr_state = in_array((string) $appr->status, array('2', '8'), true) ? 'rejected' : 'done';
        } elseif ($status === '1') {
            $appr_state = 'current';
        }
        if ($status === '8') {
            $appr_state = 'rejected';
        }
        $steps[] = array('key' => 'approved', 'label' => 'Approval', 'date' => $appr ? $this->d($appr->createdon) : null, 'state' => $appr_state, 'note' => $appr_note);

        $disbursed = ((string) $row->disburse === '1');
        $rel_state = $disbursed ? 'done' : (in_array($status, array('4', '6', '9'), true) ? 'current' : 'todo');
        $steps[] = array('key' => 'released', 'label' => 'Loan released', 'date' => $disbursed ? $this->d($row->disbursement_date) : null, 'state' => $rel_state, 'note' => $rel_state === 'current' ? 'Approved - waiting for release' : '');

        $steps[] = array('key' => 'closed', 'label' => 'Fully paid', 'date' => null, 'state' => $status === '5' ? 'done' : ($disbursed ? 'current' : 'todo'), 'note' => '');

        $stopped = false;
        foreach ($steps as $i => $s) {
            if ($stopped) {
                $steps[$i]['state'] = 'skipped';
            }
            if ($s['state'] === 'rejected') {
                $stopped = true;
            }
        }
        return $steps;
    }

    /** Beginning-balance loans that have not been activated as loan_contract rows (read-only). */
    private function beginning_balance_rows($ctx) {
        if (!$this->db->table_exists('loan_beginning_balances')) {
            return array();
        }
        $rows = $this->db->query(
            "SELECT lbb.*, lp.name AS product_name
             FROM loan_beginning_balances lbb
             LEFT JOIN loan_product lp ON lp.id = lbb.loan_product_id AND lp.PIN = lbb.PIN
             WHERE lbb.PIN = ? AND lbb.member_id = ?
               AND (lbb.loan_id IS NULL OR lbb.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN = ?))",
            array($ctx->PIN, $ctx->member_id, $ctx->PIN)
        )->result();
        $out = array();
        foreach ($rows as $r) {
            $total = round((float) $r->total_balance, 2);
            $out[] = array(
                'LID' => $r->loan_id ? $r->loan_id : 'BB-' . $r->id,
                'product' => (string) $r->product_name,
                'status_code' => 'bb',
                'status_name' => 'Beginning Balance',
                'lifecycle' => 'bb',
                'lifecycle_name' => 'Carried-over balance',
                'pill' => 'bb',
                'applicationdate' => $this->d($r->disbursement_date),
                'release_date' => $this->d($r->disbursement_date),
                'amount' => round((float) ($r->loan_amount ? $r->loan_amount : $r->principal_balance), 2),
                'total_loan' => $total,
                'outstanding' => $total,
                'principal_outstanding' => round((float) $r->principal_balance, 2),
                'interest_outstanding' => round((float) $r->interest_balance, 2),
                'penalty_outstanding' => round((float) $r->penalty_balance, 2),
                'is_open' => $total > 0.009,
                'disbursed' => true,
                'can_pay' => false,
                'is_beginning_balance' => true,
                'schedule' => array(),
                'ledger' => array(),
                'timeline' => array(),
                'progress_pct' => 0,
                'paid_total' => 0,
            );
        }
        return $out;
    }

    private function payment_history($ctx) {
        $out = array();
        $has_receipt_no = $this->db->field_exists('receipt_no', 'loan_repayment_receipt');
        $rows = $this->db->query(
            "SELECT r.receipt, " . ($has_receipt_no ? "r.receipt_no" : "'' AS receipt_no") . ", r.LID, r.amount, r.paydate
             FROM loan_repayment_receipt r
             INNER JOIN loan_contract lc ON lc.LID = r.LID AND lc.PIN = r.PIN
             WHERE lc.PID = ? AND lc.PIN = ?
             ORDER BY r.paydate DESC, r.id DESC
             LIMIT 200",
            array($ctx->PID, $ctx->PIN)
        )->result();

        $voided = $this->db->field_exists('is_voided', 'loan_contract_repayment') ? ' AND (is_voided IS NULL OR is_voided = 0)' : '';
        foreach ($rows as $r) {
            $split = $this->db->query(
                "SELECT COALESCE(SUM(principle),0) p, COALESCE(SUM(interest),0) i, COALESCE(SUM(penalt),0) pen, COUNT(*) n
                 FROM loan_contract_repayment WHERE receipt = ? AND LID = ? AND PIN = ?" . $voided,
                array($r->receipt, $r->LID, $ctx->PIN)
            )->row();
            if ((int) $split->n === 0 && $voided !== '') {
                continue;
            }
            $out[] = array(
                'date' => $this->d($r->paydate),
                'LID' => $r->LID,
                'receipt' => $r->receipt_no ? $r->receipt_no : $r->receipt,
                'amount' => round((float) $r->amount, 2),
                'principal' => round((float) $split->p, 2),
                'interest' => round((float) $split->i, 2),
                'penalty' => round((float) $split->pen, 2),
                'status' => 'posted',
                'channel' => strpos((string) $r->receipt, 'OFFSET-') === 0 ? 'offset' : 'counter',
            );
        }

        if ($this->db->table_exists('loan_online_payments')) {
            $online = $this->db->where('PID', $ctx->PID)->where('PIN', $ctx->PIN)
                ->where_in('status', array('paid', 'pending'))
                ->order_by('id', 'DESC')->limit(50)
                ->get('loan_online_payments')->result();
            $pending = $this->online_pending_by_loan($ctx);
            foreach ($online as $o) {
                if ($o->status === 'pending' && $o->expires_at && strtotime($o->expires_at) < time()) {
                    continue;
                }
                $awaiting = $o->status === 'paid' && isset($pending[$o->LID]) && $pending[$o->LID]['reference_no'] === $o->reference_no;
                if ($o->status === 'paid' && !$awaiting) {
                    continue;
                }
                $out[] = array(
                    'date' => $this->d($o->paid_at ? $o->paid_at : $o->create_date_time),
                    'LID' => $o->LID,
                    'receipt' => $o->receipt_no ? $o->receipt_no : $o->reference_no,
                    'amount' => round((float) $o->amount, 2),
                    'principal' => null,
                    'interest' => null,
                    'penalty' => null,
                    'status' => $o->status === 'paid' ? 'awaiting_posting' : 'pending',
                    'channel' => 'online',
                    'reference_no' => $o->reference_no,
                );
            }
        }

        usort($out, function ($a, $b) {
            return strcmp((string) $b['date'], (string) $a['date']);
        });
        return $out;
    }

    /** Chart datasets computed from the loans and posted payments. */
    private function charts($loans, $payments) {
        $months = array();
        for ($i = 11; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("first day of -$i month"));
            $months[$key] = 0.0;
        }
        $principal = 0.0;
        $interest = 0.0;
        $penalty = 0.0;
        foreach ($payments as $p) {
            if ($p['status'] !== 'posted') {
                continue;
            }
            $k = substr((string) $p['date'], 0, 7);
            if (isset($months[$k])) {
                $months[$k] += $p['amount'];
            }
            $principal += (float) $p['principal'];
            $interest += (float) $p['interest'];
            $penalty += (float) $p['penalty'];
        }

        $events = array();
        foreach ($loans as $l) {
            if (!empty($l['is_beginning_balance'])) {
                continue;
            }
            if (!empty($l['disbursed']) && !empty($l['release_date'])) {
                $events[] = array(substr($l['release_date'], 0, 7), (float) $l['amount']);
            }
        }
        foreach ($payments as $p) {
            if ($p['status'] === 'posted' && $p['principal'] !== null) {
                $events[] = array(substr((string) $p['date'], 0, 7), -1 * (float) $p['principal']);
            }
        }
        $balance = array();
        foreach (array_keys($months) as $m) {
            $sum = 0.0;
            foreach ($events as $e) {
                if ($e[0] <= $m) {
                    $sum += $e[1];
                }
            }
            $balance[] = round(max(0, $sum), 2);
        }

        $progress = array();
        foreach ($loans as $l) {
            if (!empty($l['is_open']) && empty($l['is_beginning_balance'])) {
                $progress[] = array('LID' => $l['LID'], 'product' => $l['product'], 'pct' => $l['progress_pct']);
            }
        }

        $labels = array();
        foreach (array_keys($months) as $m) {
            $labels[] = date('M y', strtotime($m . '-01'));
        }

        return array(
            'labels' => $labels,
            'monthly_payments' => array_map(function ($v) { return round($v, 2); }, array_values($months)),
            'balance_trend' => $balance,
            'composition' => array('principal' => round($principal, 2), 'interest' => round($interest, 2), 'penalty' => round($penalty, 2)),
            'progress' => $progress,
        );
    }

    private function comaker_requests($ctx) {
        $rows = $this->db->query(
            "SELECT g.id, g.LID, g.request_status, lc.basic_amount, lc.applicationdate, m.firstname, m.lastname, lp.name AS product_name
             FROM loan_contract_guarantor g
             INNER JOIN loan_contract lc ON lc.LID = g.LID AND lc.PIN = g.PIN
             LEFT JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
             LEFT JOIN loan_product lp ON lp.id = lc.product_type AND lp.PIN = lc.PIN
             WHERE g.PID = ? AND g.PIN = ? AND g.request = 1
             ORDER BY g.id DESC LIMIT 30",
            array($ctx->PID, $ctx->PIN)
        )->result();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'id' => (int) $r->id,
                'LID' => $r->LID,
                'borrower' => trim($r->firstname . ' ' . $r->lastname),
                'product' => (string) $r->product_name,
                'amount' => round((float) $r->basic_amount, 2),
                'date' => $this->d($r->applicationdate),
                'status' => (int) $r->request_status === 1 ? 'accepted' : ((int) $r->request_status === 2 ? 'declined' : 'pending'),
            );
        }
        return $out;
    }

    // ==================================================================
    // Helpers
    // ==================================================================

    private function d($value) {
        $value = trim((string) $value);
        if ($value === '' || strpos($value, '0000-00-00') === 0) {
            return null;
        }
        return substr($value, 0, 10);
    }

    public function normalize_date($value) {
        $value = trim((string) $value);
        if ($value === '' || strpos($value, '0000-00-00') === 0) {
            return '';
        }
        $ts = strtotime($value);
        if ($ts === false) {
            $parts = preg_split('/[\/\-\.]/', $value);
            if (count($parts) === 3 && strlen($parts[2]) === 4) {
                $ts = strtotime($parts[2] . '-' . $parts[1] . '-' . $parts[0]);
            }
        }
        return $ts ? date('Y-m-d', $ts) : '';
    }

    private function phone_tail($value) {
        $digits = preg_replace('/\D/', '', (string) $value);
        return strlen($digits) >= 10 ? substr($digits, -10) : '';
    }

    public function mask_phone($value) {
        $digits = preg_replace('/\D/', '', (string) $value);
        if (strlen($digits) < 4) {
            return '';
        }
        return str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
    }

    public function mask_email($value) {
        $value = trim((string) $value);
        if (strpos($value, '@') === false) {
            return '';
        }
        list($user, $domain) = explode('@', $value, 2);
        return substr($user, 0, 1) . str_repeat('*', max(2, strlen($user) - 1)) . '@' . $domain;
    }
}
