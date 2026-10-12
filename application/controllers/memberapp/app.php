<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/Memberapp_controller.php';

/**
 * Member Loan App shell. One page; app.js renders every screen (#/home,
 * #/loans, #/loan/LN1, #/pay, #/apply, ...) from the snapshot, so the same
 * cached page works offline.
 */
class App extends Memberapp_controller {

    public function index() {
        $ctx = $this->memberapp_guard->require_member();
        $this->no_cache();
        $data = $this->data;
        $data['title'] = 'My Loans';
        $data['csrf_token'] = $this->memberapp_guard->csrf_token();
        $data['member_key'] = substr(hash('sha256', $ctx->PIN . '|' . $ctx->member_id), 0, 16);
        $data['first_name'] = (string) $ctx->member->firstname;
        $this->load->view('memberapp/app', $data);
    }
}
