<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of saving
 *
 * @author miltone
 */
class Saving extends CI_Controller {

    //put your code here



    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_saving');
        $this->lang->load('member');
        $this->lang->load('finance');
        $this->load->model('member_model');
        $this->load->model('finance_model');
    }

    function create_saving_account() {
        $this->data['title'] = lang('create_saving_account');
        $this->data['account_list'] = $this->finance_model->saving_account_list()->result();
        $this->data['paymenthod'] = $this->finance_model->paymentmenthod();
        if ($this->input->post('open_balance')) {
            $initial = $this->input->post('open_balance');
            $_POST['open_balance'] = str_replace(',', '', $initial);
        }
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('saving_account', lang('member_saccos_saving_account_type'), 'required');
        $this->form_validation->set_rules('open_balance', lang('account_balance_open'), 'required|numeric');
        $this->form_validation->set_rules('paymenthod', lang('paymentmethod'), 'required');
        $this->form_validation->set_rules('comment', lang('comment'), '');
        $check_number_received = '';
        if ($this->input->post('paymenthod')) {
            $is_cheque = $this->input->post('paymenthod');
            if ($is_cheque == 'CHEQUE') {
                $this->form_validation->set_rules('cheque', lang('cheque_no'), 'required');
                $check_number_received = trim($this->input->post('cheque'));
            }
        }
        if ($this->form_validation->run() == TRUE) {

            $PID_initial = explode('-', trim($this->input->post('pid')));
            $member_id_initial = explode('-', trim($this->input->post('member_id')));
            $PID = $PID_initial[0];
            $member_id = $member_id_initial[0];

            $account_type = $this->input->post('saving_account');
            $account_selected = $this->finance_model->saving_account_list(null, $account_type)->row();
            $opening_balance = trim($this->input->post('open_balance'));
            $account_selected->min_amount;
            $comment = trim($this->input->post('comment'));
            $paymethod = trim($this->input->post('paymenthod'));


            if ($account_selected->min_amount <= $opening_balance) {

                $balance = $opening_balance - $account_selected->min_amount;
                $virtual_balance = $account_selected->min_amount;

                $accountdata = $this->finance_model->create_account($PID, $member_id, $account_type, $balance, $virtual_balance, $paymethod, $comment, $check_number_received);
                if ($accountdata) {
                    $this->session->set_flashdata('next_customer', site_url(current_lang() . '/saving/create_saving_account'));
                    $this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                    redirect(current_lang() . '/saving/receipt_view/' . $accountdata, 'refresh');
                } else {
                    $this->data['warning'] = lang('create_saving_account_error');
                }
            } else {
                $this->data['warning'] = lang('opening_balance_error') . ' ' . number_format($account_selected->min_amount, 2);
            }
        }
        $this->data['content'] = 'saving/create_account';
        $this->load->view('template', $this->data);
    }

    function receipt_view($receipt) {
        $this->lang->load('setting');
        $trans = $this->finance_model->get_transaction($receipt);
        if ($trans) {
            $this->data['title'] = lang('view_receipt');
            $this->data['trans'] = $trans;
            $this->data['content'] = 'saving/receipt';
            $this->load->view('template', $this->data);
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function print_receipt($receipt) {
        $this->lang->load('setting');
        $trans = $this->finance_model->get_transaction($receipt);
        if ($trans) {
            include 'include/receipt.php';
            exit;
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function credit_debit() {
        $this->data['title'] = lang('saving_account_credit_debit');
        $this->data['paymenthod'] = $this->finance_model->paymentmenthod();
        if ($this->input->post('amount')) {
            $initial = $this->input->post('amount');
            $_POST['amount'] = str_replace(',', '', $initial);
        }
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('trans_type', lang('transaction_type'), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');
        $this->form_validation->set_rules('paymenthod', lang('paymentmethod'), 'required');
        $this->form_validation->set_rules('comment', lang('comment'), '');
        $this->form_validation->set_rules('customer_name', lang('customer_name'), 'required');
        $check_number_received = '';
        if ($this->input->post('paymenthod')) {
            $is_cheque = $this->input->post('paymenthod');
            if ($is_cheque == 'CHEQUE') {
                $this->form_validation->set_rules('cheque', lang('cheque_no'), 'required');
                $check_number_received = trim($this->input->post('cheque'));
            }
        }
        if ($this->form_validation->run() == TRUE) {

            $account_initial = explode('-', trim($this->input->post('pid')));

            $account_number = $account_initial[0];
            $trans_type = $this->input->post('trans_type');

            $comment = trim($this->input->post('comment'));
            $paymethod = trim($this->input->post('paymenthod'));
            $amount = trim($this->input->post('amount'));
            $customer_name = trim($this->input->post('customer_name'));
            $continue = true;
            if ($trans_type == 'DR') {
                $account_balance = $this->finance_model->saving_account_balance($account_number);
                if ($account_balance) {
                    $remaining = $account_balance->balance - $amount;
                    if ($remaining < 1) {
                        $continue = FALSE;
                    }
                } else {
                    $continue = FALSE;
                }
            }

            if ($continue) {
                //now finalize
                $receipt = $this->finance_model->add_saving_transaction($trans_type, $account_number, $amount, $paymethod, $comment, $check_number_received, $customer_name, $pid = null);
                if ($receipt) {
                    $this->session->set_flashdata('next_customer', site_url(current_lang() . '/saving/credit_debit'));
                    $this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                    redirect(current_lang() . '/saving/receipt_view/' . $receipt, 'refresh');
                } else {
                    $this->data['warning'] = lang('transaction_fail');
                }
            } else {
                //insufficient balance
                $this->data['warning'] = lang('insufficient_balance');
            }
        }
        $this->data['content'] = 'saving/credit_debit';
        $this->load->view('template', $this->data);
    }

    function transaction_search() {
        $this->load->library('pagination');
        $this->data['title'] = lang('saving_transaction_search');

        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 30);
        }

        $config["per_page"] = $this->session->userdata('PER_PAGE');

        $key = null;
        $from = null;
        $to = null;
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $key = $_POST['key'];
            $expl = explode('-', $key);
            $key = $expl[0];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        if (isset($_POST['from']) && $_POST['from'] != '') {
            $from = format_date($_POST['from']);
        } else if (isset($_GET['from'])) {
            $from = format_date($_GET['from']);
        } else {
            $from = date('Y-m-d');
        }

        if (isset($_POST['upto']) && $_POST['upto'] != '') {
            $upto = format_date($_POST['upto']);
        } else if (isset($_GET['upto'])) {
            $upto = format_date($_GET['upto']);
        } else {
            $upto = date('Y-m-d');
        }


        $suffix_array = array();

        if (!is_null($key)) {
            $suffix_array['key'] = $key;
        }

        if (!is_null($from)) {
            $suffix_array['from'] = $from;
        }

        if (!is_null($upto)) {
            $suffix_array['upto'] = $upto;
        }
        $this->data['jxy'] = $suffix_array;

        if (count($suffix_array) > 0) {
            $query_string = http_build_query($suffix_array, '', '&');
            $config['suffix'] = '?' . $query_string;
        }

        $config["base_url"] = site_url(current_lang() . '/saving/transaction_search/');
        $config["total_rows"] = $this->finance_model->count_transaction($key, $from, $upto);
        $config["uri_segment"] = 4;

        $config['full_tag_open'] = '<div class="pagination" style="background-color:#fff; margin-left:0px;">';
        $config['full_tag_close'] = '</div>';

        $config['num_tag_open'] = '<div class="link-pagination">';
        $config['num_tag_close'] = '</div>';

        $config['prev_tag_open'] = '<div class="link-pagination">';
        $config['prev_tag_close'] = '</div>';

        $config['next_tag_open'] = '<div class="link-pagination">';
        $config['next_tag_close'] = '</div>';

        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Previous';
        $config['cur_tag_open'] = '<div class="link-pagination current">';
        $config['cur_tag_close'] = '</div>';


        $config["num_links"] = 10;


        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
        $this->data['links'] = $this->pagination->create_links();

        $this->data['transactionlist'] = $this->finance_model->search_transaction($key, $from, $upto, $config["per_page"], $page);


        $this->data['content'] = 'saving/transaction_history';
        $this->load->view('template', $this->data);
    }

    
    
    function autosuggest($id) {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;
        

        $auto = $this->db->query("SELECT PID,firstname, middlename, lastname,member_id FROM members WHERE PIN='$pin' AND ( PID LIKE '$q%' OR  member_id LIKE '$q%' OR firstname LIKE '$q%' OR lastname LIKE '$q%')")->result();
        //$auto = $this->db->query("SELECT PIDFROM members WHERE  PID LIKE '$q%' or member_id LIKE '$q%' AND PIN='$PIN'")->result();

        foreach ($auto as $key => $value) {
            if ($id == 'pid') {
                echo $value->PID . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
            } else if ($id == 'mid') {
                echo $value->member_id . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
            }
        }
    }

    function autosuggest_account($id) {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;

        $auto = $this->db->query("SELECT a.account,b.firstname, b.middlename, b.lastname FROM members_account as a INNER JOIN members as b ON a.RFID=b.PID   WHERE b.PIN='$pin' AND ( a.account LIKE '$q%'  OR b.firstname LIKE '$q%' OR b.lastname LIKE '$q%') ")->result();


        foreach ($auto as $key => $value) {

            echo $value->account . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
        }
    }

    function autosuggest_account_all() {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;

        $auto = $this->db->query("SELECT a.account,b.firstname, b.middlename, b.lastname FROM members_account as a INNER JOIN members as b ON a.RFID=b.PID   WHERE b.PIN='$pin' AND ( a.account LIKE '$q%'  OR b.firstname LIKE '$q%' OR b.lastname LIKE '$q%')")->result();
        $auto1 = $this->db->query("SELECT a.account,b.name FROM members_account as a INNER JOIN members_grouplist as b ON a.RFID=b.GID   WHERE a.PIN='$pin' AND ( a.account LIKE '$q%'  OR b.name LIKE '$q%' ) ")->result();


        foreach ($auto as $key => $value) {

            echo $value->account . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
        }
        foreach ($auto1 as $key => $value) {

            echo $value->account . ' - ' . $value->name . "\n";
        }
    }
    function autosuggest_member_id_all() {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;

        $auto = $this->db->query("SELECT * FROM members WHERE PIN='$pin' AND  (PID LIKE '$q%'  OR firstname LIKE '$q%' OR lastname LIKE '$q%' OR member_id LIKE '$q%')")->result();
      

        foreach ($auto as $key => $value) {

            echo $value->PID . ' - '.$value->member_id.'  ==> ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
        }
      
    }
    
    
    

    function search_account() {

        $value = $this->input->post('value');
        $column = $this->input->post('column');
        $explode = explode('-', $value);
        $value = $explode[0];
        $account_pin = null;
        $error = '';
        if ($column == 'PID') {
            $account_pin = $value;
            $error = lang('invalid_account');
        }
        //$pid is the account number; query account info
        $account_info = $this->finance_model->saving_account_balance($account_pin);
        $status = array();
        if (count($account_info) == 1) {
            $status['accountinfo'] = $account_info;
            $member = $this->member_model->member_basic_info(null, $account_info->RFID, $account_info->member_id)->row();

            if (count($member) == 1) {
                $contact = $this->member_model->member_contact($member->PID);
                $status['success'] = 'Y';
                $status['data'] = $member;
                $status['contact'] = $contact;
                echo json_encode($status);
            } else {
                $status['success'] = 'N';
                $status['error'] = $error;
                echo json_encode($status);
            }
        } else {
            $error = lang('invalid_account');
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }

    function search_member() {

        $value = $this->input->post('value');
        $column = $this->input->post('column');
        $explode = explode('-', $value);
        $value = $explode[0];
        $pid = null;
        $member_id = null;
        $error = '';
        if ($column == 'PID') {
            $pid = $value;
            $error = lang('invalid_PID');
        } else if ($column == 'MID') {
            $member_id = $value;
            $error = lang('invalid_member_id');
        }
        $member = $this->member_model->member_basic_info(null, $pid, $member_id)->row();


        $status = array();
        if (count($member) == 1) {
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            echo json_encode($status);
        } else {
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }

    
    
    function search_member_share() {
        $this->load->model('share_model');
        $this->load->model('setting_model');
        $share_setting = $this->setting_model->share_setting_info();
        $value = $this->input->post('value');
        $column = $this->input->post('column');
        $explode = explode('-', $value);
        $value = $explode[0];
        $pid = null;
        $member_id = null;
        $error = '';
        if ($column == 'PID') {
            $pid = $value;
            $error = lang('invalid_PID');
        } else if ($column == 'MID') {
            $member_id = $value;
            $error = lang('invalid_member_id');
        }

        $member = $this->member_model->member_basic_info(null, $pid, $member_id)->row();


        $status = array();
        $share_array = array('amount' => 0, 'share' => 0, 'max_share' => $share_setting->max_share, 'min_share' => $share_setting->min_share);


        if (count($member) == 1) {
            $current_share = $this->share_model->share_member_info($member->PID, $member->member_id);
            if ($current_share) {
                $share_array['share'] = $current_share->totalshare;
                $share_array['amount'] = ($current_share->amount + $current_share->remainbalance);
            }
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            $status['share'] = $share_array;
            echo json_encode($status);
        } else {
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }
    
    function search_member_contribution() {
        $this->load->model('setting_model');
        $this->load->model('contribution_model');
        $share_setting = $this->setting_model->share_setting_info();
        $value = $this->input->post('value');
        $column = $this->input->post('column');
        $explode = explode('-', $value);
        $value = $explode[0];
        $pid = null;
        $member_id = null;
        $error = '';
        if ($column == 'PID') {
            $pid = $value;
            $error = lang('invalid_PID');
        } else if ($column == 'MID') {
            $member_id = $value;
            $error = lang('invalid_member_id');
        }

        $member = $this->member_model->member_basic_info(null, $pid, $member_id)->row();


        $status = array();
        

        if (count($member) == 1) {
            $balance = 0;
            $current_share = $this->contribution_model->contribution_balance($member->PID, $member->member_id);
            if ($current_share) {
                $balance = $current_share->balance;
            }
            
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            $status['balance'] = $balance;
            echo json_encode($status);
        } else {
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }

}

?>
