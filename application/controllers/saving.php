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
 //Added by Herald
    function saving_account_list() {
        $this->load->library('pagination');
        $this->data['title'] = lang('saving_account_list');
        
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        
        // Check permission
        if (!has_role(3, 'saving_account_list')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        
        
        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 40);
        }
        
        $config["per_page"] = $this->session->userdata('PER_PAGE');
        
        $key = null;
        
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $explode = explode('-', $_POST['key']);
            $key = trim($explode[0]);
        } else if (isset($_GET['key']) && $_GET['key'] != '') {
            $key = trim($_GET['key']);
        }
        
        $account_type_filter = null;
        if (isset($_POST['account_type_filter']) && $_POST['account_type_filter'] != '') {
            $account_type_filter = $_POST['account_type_filter'];
        } else if (isset($_GET['account_type_filter']) && $_GET['account_type_filter'] != '') {
            $account_type_filter = $_GET['account_type_filter'];
        }
        
        $suffix_array = array();

        if (!is_null($key) && $key != '') {
            $suffix_array['key'] = $key;
        }
        
        if (!is_null($account_type_filter) && $account_type_filter != '') {
            $suffix_array['account_type_filter'] = $account_type_filter;
        }
        
        $this->data['jxy'] = $suffix_array;
        $this->data['account_type_filter'] = $account_type_filter;
        if (count($suffix_array) > 0) {
            $query_string = http_build_query($suffix_array, '', '&');
            $config['suffix'] = '?' . $query_string;
        }
        
        
        $config["base_url"] = site_url(current_lang() . '/saving/saving_account_list');
        $config["total_rows"] = $this->finance_model->count_saving_account($key, $account_type_filter);
        $config["uri_segment"] = 4;
        
        $config['full_tag_open'] = '<div class="pagination" style="background-color:#fff; margin-left:0px;">';
        $config['full_tag_close'] = '</div>';
        
        $config['num_tag_open'] = '<div class="link-pagination">';
        $config['num_tag_close'] = '</div>';
        
        $config['prev_tag_open'] = '<div class="link-pagination">';
        $config['prev_tag_close'] = '</div>';
        
        $config['next_tag_open'] = '<div class="link-pagination">';
        $config['next_tag_close'] = '</div>';
        
        $config['last_tag_open'] = '<div class="link-pagination">';
        $config['last_tag_close'] = '</div>';
        
        $config['first_tag_open'] = '<div class="link-pagination">';
        $config['first_tag_close'] = '</div>';
        
        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Previous';
        $config['cur_tag_open'] = '<div class="link-pagination current">';
        $config['cur_tag_close'] = '</div>';
        
        
        $config["num_links"] = 10;
        
        
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
        $this->data['links'] = $this->pagination->create_links();
        
        $this->data['saving_accounts'] = $this->finance_model->search_saving_account($key, $config["per_page"], $page, $account_type_filter);
        $this->data['total_savings_amount'] = $this->finance_model->get_total_savings_amount($key, $account_type_filter);
        
        $this->data['content'] = 'saving/saving_account_list';
        $this->load->view('template', $this->data);
    }
    function edit_saving_account($id = null) {
        $this->data['title'] = lang('edit_saving_account');
        $this->data['id'] = $id;
        
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        
        // Check permission - allow if user has either saving_account_list or Edit_saving_account permission
        if (!has_role(3, 'saving_account_list') && !has_role(3, 'Edit_saving_account')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        
        if (!is_null($id)) {
            $this->data['account_info'] = $this->finance_model->get_saving_account_info($id);
            if (!$this->data['account_info']) {
                $this->session->set_flashdata('warning', lang('invalid_account'));
                redirect(current_lang() . '/saving/saving_account_list', 'refresh');
                return;
            }
        } else {
            $this->session->set_flashdata('warning', lang('invalid_account'));
            redirect(current_lang() . '/saving/saving_account_list', 'refresh');
            return;
        }
        
        // Handle comma-formatted numbers
        if ($this->input->post('balance')) {
            $initial = $this->input->post('balance');
            $_POST['balance'] = str_replace(',', '', $initial);
        }
        if ($this->input->post('virtual_balance')) {
            $initial = $this->input->post('virtual_balance');
            $_POST['virtual_balance'] = str_replace(',', '', $initial);
        }
        
        $this->form_validation->set_rules('account', lang('account_number'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('account_cat', lang('account_type'), 'required');
        $this->form_validation->set_rules('balance', lang('balance'), 'required|numeric');
        $this->form_validation->set_rules('virtual_balance', lang('virtual_balance'), 'numeric');
        
        if ($this->form_validation->run() == TRUE) {
            $update_data = array(
                'account' => trim($this->input->post('account')),
                'member_id' => trim($this->input->post('member_id')),
                'account_cat' => trim($this->input->post('account_cat')),
                'balance' => trim($this->input->post('balance')),
                'virtual_balance' => trim($this->input->post('virtual_balance')) ? trim($this->input->post('virtual_balance')) : 0,
            );
            
            $result = $this->finance_model->update_saving_account($update_data, $id);
            if ($result) {
                $this->session->set_flashdata('message', lang('account_updated_successfully'));
                redirect(current_lang() . '/saving/saving_account_list', 'refresh');
            } else {
                $this->data['warning'] = lang('account_update_failed');
            }
        }
        
        $this->data['account_list'] = $this->finance_model->saving_account_list()->result();
        $this->data['content'] = 'saving/edit_account';
        $this->load->view('template', $this->data);
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
        $this->form_validation->set_rules('posting_date', lang('mortuary_transaction_date'), 'required|valid_date');
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
            $posting_date = date("Y-m-d",strtotime($this->input->post('posting_date')));

            $account_type = $this->input->post('saving_account');
            $old_member_id = $this->input->post('old_member_id');
            $account_selected = $this->finance_model->saving_account_list(null, $account_type)->row();
            $opening_balance = trim($this->input->post('open_balance'));
            $account_selected->min_amount;
            $comment = trim($this->input->post('comment'));
            $paymethod = trim($this->input->post('paymenthod'));


            if ($account_selected->min_amount <= $opening_balance) {

                $balance = $opening_balance - $account_selected->min_amount;
                $virtual_balance = $account_selected->min_amount;

                $accountdata = $this->finance_model->create_account($PID, $member_id, $account_type, $balance, $virtual_balance, $paymethod, $comment, $check_number_received, $posting_date, $old_member_id);
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
        // Suppress PHP warnings/errors for TCPDF compatibility (PHP 7.3+ issues)
        $old_error_reporting = error_reporting(0);
        $old_display_errors = ini_set('display_errors', 0);
        
        // Start output buffering to prevent any output before PDF
        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        $this->lang->load('setting');
        $trans = $this->finance_model->get_transaction($receipt);
        if ($trans) {
            // Clear any output that might have been generated
            ob_clean();
            
            // Suppress warnings for TCPDF library
            @include 'include/receipt.php';
            
            // Restore settings
            error_reporting($old_error_reporting);
            if ($old_display_errors !== false) {
                ini_set('display_errors', $old_display_errors);
            }
            exit;
        } else {
            // Clean output buffer before showing error
            ob_end_clean();
            // Restore settings
            error_reporting($old_error_reporting);
            if ($old_display_errors !== false) {
                ini_set('display_errors', $old_display_errors);
            }
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
        $this->form_validation->set_rules('posting_date', lang('mortuary_transaction_date'), 'required|valid_date');

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
            $posting_date = date("Y-m-d",strtotime($this->input->post('posting_date')));

            $continue = true;
            if ($trans_type == 'DR') {
                $account_balance = $this->finance_model->saving_account_balance($account_number);
                if ($account_balance) {
                    $remaining = $account_balance->balance - $amount;
                    if ($remaining < 0) {
                        $continue = FALSE;
                    }
                } else {
                    $continue = FALSE;
                }
            }

            if ($continue) {
                //now finalize
                $receipt = $this->finance_model->add_saving_transaction($trans_type, $account_number, $amount, $paymethod, $comment, $check_number_received, $customer_name, $pid = null, $posting_date);
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

            if (!empty($member) && isset($member->PID)) {
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

        $value = trim($this->input->post('value'));
        $column = trim($this->input->post('column'));
        
        // Validate input
        if (empty($value) || empty($column)) {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = lang('invalid_member_id');
            echo json_encode($status);
            return;
        }
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        // Check if value contains " - " (space-dash-space) which separates ID from name
        if (strpos($value, ' - ') !== false) {
            // Extract everything before " - " as the ID
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else {
            // If no " - " separator, the value might be just the ID or formatted differently
            // Try to extract ID by splitting on first dash, but preserve member IDs with dashes
            // For member IDs like "2005-00173", we need to be smarter
            // Check if it looks like a member ID format (contains dash and has numbers)
            if (preg_match('/^[\d\-]+/', $value, $matches)) {
                // If it starts with digits and dashes, use the matched portion
                $value = trim($matches[0]);
            } else {
                // Fallback: split on first dash (old behavior for backward compatibility)
                $explode = explode('-', $value);
                $value = trim($explode[0]);
            }
        }
        
        if (empty($value)) {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = lang('invalid_member_id');
            echo json_encode($status);
            return;
        }
        
        $pid = null;
        $member_id = null;
        $error = '';
        if ($column == 'PID') {
            $pid = trim($value);
            $error = lang('invalid_PID');
        } else if ($column == 'MID') {
            $member_id = trim($value);
            $error = lang('invalid_member_id');
        } else {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = lang('invalid_member_id');
            echo json_encode($status);
            return;
        }
        
        // Ensure values are not empty after trimming
        if (($column == 'PID' && empty($pid)) || ($column == 'MID' && empty($member_id))) {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
            return;
        }
        
        // Query member with proper null handling
        $member_query = $this->member_model->member_basic_info(null, $pid, $member_id);
        $member = $member_query->row();

        $status = array();
        // Check if member exists and has valid PID
        // Use num_rows() to check if query returned results, then check object properties
        if ($member_query->num_rows() > 0 && $member && isset($member->PID) && !empty($member->PID)){
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            echo json_encode($status);
        }else{
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }
    
    function search_member_share() {
        $this->load->model('share_model');
        $this->load->model('setting_model');
        $share_setting = $this->setting_model->share_setting_info();
        $value = trim($this->input->post('value'));
        $column = trim($this->input->post('column'));
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        if (strpos($value, ' - ') !== false) {
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else if (preg_match('/^[\d\-]+/', $value, $matches)) {
            $value = trim($matches[0]);
        } else {
            $explode = explode('-', $value);
            $value = trim($explode[0]);
        }
        
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

        if (!empty($member) && isset($member->PID)) {
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
        $value = trim($this->input->post('value'));
        $column = trim($this->input->post('column'));
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        if (strpos($value, ' - ') !== false) {
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else if (preg_match('/^[\d\-]+/', $value, $matches)) {
            $value = trim($matches[0]);
        } else {
            $explode = explode('-', $value);
            $value = trim($explode[0]);
        }
        
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
        

        if (!empty($member) && isset($member->PID)) {
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
    
    function search_member_mortuary() {
        $this->load->model('setting_model');
        $this->load->model('contribution_model');
        $this->load->model('mortuary_model');
        $share_setting = $this->setting_model->share_setting_info();
        $value = trim($this->input->post('value'));
        $column = trim($this->input->post('column'));
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        if (strpos($value, ' - ') !== false) {
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else if (preg_match('/^[\d\-]+/', $value, $matches)) {
            $value = trim($matches[0]);
        } else {
            $explode = explode('-', $value);
            $value = trim($explode[0]);
        }
        
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
        
        $member = $this->member_model->member_basic_info(null, $pid, $member_id,1)->row();
        
        
        $status = array();
        
        
        if (!empty($member) && isset($member->PID)) {
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
