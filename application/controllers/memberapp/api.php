<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/Memberapp_controller.php';

/**
 * Member Loan App JSON API (/customer_login/api/...).
 * Every call is for the signed-in member only; pid / member_id are never read
 * from the request. POST calls need the X-CSRF-Token header.
 */
class Api extends Memberapp_controller {

    const MAX_COMAKERS = 3;

    /** @var object */
    private $ctx;

    public function __construct() {
        parent::__construct();
        $this->ctx = $this->memberapp_guard->require_member(true);
    }

    public function index() {
        $this->snapshot();
    }

    /** Everything the app shows; the phone keeps it for offline viewing. */
    public function snapshot() {
        $this->memberapp_guard->json(array(
            'ok' => true,
            'csrf' => $this->memberapp_guard->csrf_token(),
            'snapshot' => $this->memberapp_model->snapshot($this->ctx),
        ));
    }

    /** Products, pay sources and reloan candidates for the Apply wizard. */
    public function products() {
        $this->load->model('setting_model');
        $this->load->model('contribution_model');
        $this->load->model('loan_model');

        $products = array();
        foreach ($this->allowed_products() as $p) {
            $interval = $this->setting_model->intervalinfo($p->interval)->row();
            $products[] = array(
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'description' => (string) $p->description,
                'rate' => (float) $p->interest_rate,
                'max_term' => (int) $p->maxmum_time,
                'interval' => $interval ? (string) $interval->name : 'Monthly',
                'interval_unit' => $interval ? (string) $interval->description : 'Month(s)',
                'max_amount' => isset($p->app_max_amount) && $p->app_max_amount !== null ? (float) $p->app_max_amount : null,
                'min_savings' => (float) $p->loan_security_saving_minimum,
            );
        }

        $sources = array();
        foreach ($this->contribution_model->contribution_source()->result() as $s) {
            if (stripos($s->name, 'BEGINNING') === false) {
                $sources[] = (string) $s->name;
            }
        }

        $reloan = array();
        foreach ($this->loan_model->get_member_repayable_loans($this->ctx->PID) as $l) {
            $reloan[] = array(
                'LID' => $l['LID'],
                'product' => (string) $l['product_name'],
                'outstanding' => (float) $l['outstanding'],
            );
        }

        $this->memberapp_guard->json(array(
            'ok' => true,
            'products' => $products,
            'sources' => $sources,
            'reloan_candidates' => $reloan,
            'pending_application' => $this->pending_application(),
        ));
    }

    /** Installment preview for the Apply wizard (no data is saved). */
    public function quote() {
        $this->require_post();
        $product = $this->find_product((int) $this->input->post('product'));
        $amount = round((float) str_replace(',', '', (string) $this->input->post('amount')), 2);
        $term = (int) $this->input->post('term');
        if (!$product || $amount <= 0 || $term <= 0) {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'Choose a product, amount and term.'));
        }
        if ($term > (int) $product->maxmum_time) {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'The longest term for this product is ' . (int) $product->maxmum_time . '.'));
        }
        $this->load->library('loanbase');
        $installment = $this->loanbase->get_installment($product->interest_rate, $amount, $term, $product->interest_method, $product->interval);
        $interest = $this->loanbase->totalInterest($product->interest_rate, $amount, $term, $installment, $product->interest_method, $product->interval);
        $this->memberapp_guard->json(array(
            'ok' => true,
            'installment' => round((float) $installment, 2),
            'total_interest' => round((float) $interest, 2),
            'total' => round($amount + (float) $interest, 2),
            'rate' => (float) $product->interest_rate,
        ));
    }

    /**
     * Submit a new loan or a reloan. Uses the same rules as the staff Loan
     * Application form (Loan_eligibility) and lands as status "New" for
     * evaluation. The processing fee is set by staff during evaluation.
     */
    public function apply() {
        $this->require_post();
        $this->load->model('loan_model');
        $this->load->model('contribution_model');
        $this->load->library('loan_eligibility');

        $ctx = $this->ctx;
        $product = $this->find_product((int) $this->input->post('product'));
        $amount = round((float) str_replace(',', '', (string) $this->input->post('amount')), 2);
        $term = (int) $this->input->post('term');
        $source = trim((string) $this->input->post('source'));
        $purpose = trim(strip_tags((string) $this->input->post('purpose')));
        $income = round((float) str_replace(',', '', (string) $this->input->post('income')), 2);
        $reloan_of = $this->input->post('reloan_of');
        $comakers = $this->input->post('comakers');
        $reloan_of = is_array($reloan_of) ? $reloan_of : array();
        $comakers = is_array($comakers) ? $comakers : array();

        if (!$product) {
            $this->fail('Please choose a loan product.');
        }
        if ($amount <= 0) {
            $this->fail('Enter the amount you want to borrow.');
        }
        if ($product->app_max_amount !== null && $amount > (float) $product->app_max_amount) {
            $this->fail('The most you can apply for in the app for ' . $product->name . ' is PHP ' . number_format($product->app_max_amount, 2) . '.');
        }
        if ($term <= 0 || $term > (int) $product->maxmum_time) {
            $this->fail('Choose a term between 1 and ' . (int) $product->maxmum_time . '.');
        }
        $valid_source = false;
        foreach ($this->contribution_model->contribution_source()->result() as $s) {
            if (strcasecmp($s->name, $source) === 0 && stripos($s->name, 'BEGINNING') === false) {
                $source = $s->name;
                $valid_source = true;
            }
        }
        if (!$valid_source) {
            $this->fail('Choose how you will pay the loan.');
        }
        if (strlen($purpose) < 3) {
            $this->fail('Tell us the purpose of the loan.');
        }
        if ($income <= 0) {
            $this->fail('Enter your monthly income.');
        }
        if ($this->pending_application()) {
            $this->fail('You already have an application in progress. Please wait for it to be evaluated before applying again.');
        }

        $offset_ids = array();
        foreach (array_slice($reloan_of, 0, 5) as $lid) {
            $lid = trim((string) $lid);
            if ($lid !== '' && $this->memberapp_guard->owns_loan($lid)) {
                $offset_ids[] = $lid;
            }
        }

        $guarantors = array();
        foreach (array_slice($comakers, 0, self::MAX_COMAKERS) as $c) {
            $member_id = trim((string) (is_array($c) && isset($c['member_id']) ? $c['member_id'] : $c));
            $relationship = is_array($c) && isset($c['relationship']) ? trim(strip_tags((string) $c['relationship'])) : '';
            if ($member_id === '') {
                continue;
            }
            $m = $this->comaker_member($member_id);
            if (!$m) {
                $this->fail('Co-maker ' . $member_id . ' was not found or cannot be a co-maker.');
            }
            $guarantors[$m->PID] = array('PID' => $m->PID, 'relationship' => $relationship !== '' ? $relationship : 'Co-member');
        }

        $check = $this->loan_eligibility->check($product, $amount, $term, $ctx->PID, $ctx->member_id);
        if (!$check['ok']) {
            $this->fail(strip_tags($check['message']));
        }

        $loan_purpose = $purpose;
        if (!empty($offset_ids)) {
            $loan_purpose .= ' | Reloan of: ' . implode(', ', $offset_ids);
        }
        $loan_purpose .= ' | Applied via Member App';

        $createloan = array(
            'LID' => '',
            'PID' => $ctx->PID,
            'member_id' => $ctx->member_id,
            'product_type' => $product->id,
            'rate' => $product->interest_rate,
            'interval' => $product->interval,
            'basic_amount' => $amount,
            'number_istallment' => $term,
            'pay_source' => $source,
            'applicationdate' => date('Y-m-d'),
            'loan_purpose' => $loan_purpose,
            'monthly_income' => $income,
            'installment_amount' => $check['installment_amount'],
            'total_interest_amount' => $check['total_interest'],
            'total_loan' => $check['total_interest'] + $amount,
            'createdby' => $ctx->user_id,
            'PIN' => $ctx->PIN,
        );
        unset($createloan['LID']);

        $LID = $this->loan_model->add_newloan($createloan, 0, false);
        if (!$LID) {
            $this->fail('We could not save your application. Please try again.');
        }
        foreach ($guarantors as $g) {
            $this->loan_model->add_guarantor(array(
                'LID' => $LID,
                'PID' => $g['PID'],
                'relationship' => $g['relationship'],
                'declaration' => '',
                'file' => '',
                'request' => 1,
                'request_status' => 0,
                'PIN' => $ctx->PIN,
            ));
        }

        $this->memberapp_guard->json(array(
            'ok' => true,
            'LID' => $LID,
            'message' => 'Application ' . $LID . ' was submitted. We will notify you once it is evaluated.',
        ));
    }

    /** Confirm a co-maker by Member ID before adding them (name is masked). */
    public function comaker_lookup() {
        $m = $this->comaker_member(trim((string) $this->input->get('member_id')));
        if (!$m) {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'No active member with that Member ID.'));
        }
        $last = trim((string) $m->lastname);
        $this->memberapp_guard->json(array(
            'ok' => true,
            'member_id' => $m->member_id,
            'name' => trim($m->firstname) . ' ' . ($last !== '' ? mb_substr($last, 0, 1) . '.' : ''),
        ));
    }

    /** Accept or decline a request to be a co-maker on someone's loan. */
    public function comaker_respond() {
        $this->require_post();
        $id = (int) $this->input->post('id');
        $action = (string) $this->input->post('action');
        $row = $this->db->where('id', $id)
            ->where('PID', $this->ctx->PID)
            ->where('PIN', $this->ctx->PIN)
            ->where('request', 1)
            ->limit(1)->get('loan_contract_guarantor')->row();
        if (!$row || (int) $row->request_status !== 0) {
            $this->fail('This request is no longer open.');
        }
        if ($action === 'decline') {
            $update = array('request_status' => 2);
        } elseif ($action === 'accept') {
            $relationship = trim(strip_tags((string) $this->input->post('relationship')));
            if ($relationship === '') {
                $this->fail('Tell us your relationship to the borrower.');
            }
            $update = array(
                'request_status' => 1,
                'relationship' => $relationship,
                'declaration' => trim(strip_tags((string) $this->input->post('declaration'))),
            );
        } else {
            $this->fail('Choose accept or decline.');
        }
        $this->db->where('id', $row->id)->update('loan_contract_guarantor', $update);
        $this->memberapp_guard->json(array('ok' => true, 'message' => $action === 'accept' ? 'Thank you. You are now a co-maker on ' . $row->LID . '.' : 'Request declined.'));
    }

    public function change_password() {
        $this->require_post();
        $current = (string) $this->input->post('current');
        $new = (string) $this->input->post('password');
        $confirm = (string) $this->input->post('password_confirm');
        if ($this->memberapp_guard->too_many($this->ctx->member_id, 'change_pw', 5, 15)) {
            $this->fail('Too many attempts. Please wait 15 minutes.');
        }
        $problem = $this->memberapp_guard->password_problem($new, $confirm, $this->ctx->member_id);
        if ($problem !== '') {
            $this->fail($problem);
        }
        if (!$this->ion_auth->change_password($this->ctx->user->username, $current, $new)) {
            $this->memberapp_guard->record_attempt($this->ctx->member_id, 'change_pw');
            $this->fail('Your current password is not correct.');
        }
        $clean = array('oldpass' => '');
        if ($this->db->field_exists('defaultpass', 'users')) {
            $clean['defaultpass'] = '';
        }
        $this->db->where('id', $this->ctx->user_id)->update('users', $clean);
        $this->memberapp_guard->json(array('ok' => true, 'message' => 'Your password was changed.'));
    }

    // ------------------------------------------------------------------

    private function require_post() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->memberapp_guard->json(array('ok' => false, 'message' => 'Method not allowed.'), 405);
        }
        if (!$this->memberapp_guard->csrf_valid()) {
            $this->memberapp_guard->json(array('ok' => false, 'error' => 'csrf', 'message' => 'Your session expired. Please reload the app.'), 403);
        }
    }

    private function fail($message) {
        $this->memberapp_guard->json(array('ok' => false, 'message' => $message), 422);
    }

    /** Products offered in the app (member_app_products; none chosen = all). */
    private function allowed_products() {
        $this->load->model('setting_model');
        $all = $this->setting_model->loanproduct()->result();
        $chosen = array();
        if ($this->db->table_exists('member_app_products')) {
            foreach ($this->db->where('PIN', $this->ctx->PIN)->get('member_app_products')->result() as $r) {
                $chosen[(int) $r->product_id] = $r->max_amount !== null ? (float) $r->max_amount : null;
            }
        }
        $out = array();
        foreach ($all as $p) {
            if (!empty($chosen) && !array_key_exists((int) $p->id, $chosen)) {
                continue;
            }
            $p->app_max_amount = isset($chosen[(int) $p->id]) ? $chosen[(int) $p->id] : null;
            $out[] = $p;
        }
        return $out;
    }

    private function find_product($id) {
        foreach ($this->allowed_products() as $p) {
            if ((int) $p->id === $id) {
                return $p;
            }
        }
        return null;
    }

    /** An application not yet decided (New / Need changes / Evaluated). */
    private function pending_application() {
        $row = $this->db->select('LID')
            ->where('PID', $this->ctx->PID)
            ->where('PIN', $this->ctx->PIN)
            ->where_in('status', array(0, 1, 3))
            ->where('disburse !=', 1)
            ->limit(1)->get('loan_contract')->row();
        return $row ? $row->LID : null;
    }

    private function comaker_member($member_id) {
        if ($member_id === '' || strcasecmp($member_id, $this->ctx->member_id) === 0) {
            return null;
        }
        return $this->db->where('member_id', $member_id)
            ->where('PIN', $this->ctx->PIN)
            ->where('status !=', 2)
            ->limit(1)->get('members')->row();
    }
}
