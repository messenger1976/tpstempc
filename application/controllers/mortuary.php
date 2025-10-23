<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of contribution
 *
 * @author miltone
 */
class Mortuary extends CI_Controller {

    //put your code here

    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        
        $this->data['current_title'] = lang('page_mortuary');
        $this->lang->load('member');
        $this->lang->load('finance');
        $this->lang->load('finance');
        $this->load->model('member_model');
        $this->load->model('contribution_model');
        $this->load->model('mortuary_model');
        $this->load->model('setting_model');
    }

    function automatic_mortuary_process() {
        $this->data['title'] = 'Automatic Mortuary Payment';
        $this->form_validation->set_rules('date_month', 'Months', 'required|valid_month');
        $this->form_validation->set_rules('key', lang('member_member_id'), 'required');
        
        if ($this->form_validation->run() == TRUE) {
            $month = trim($this->input->post('date_month'));
            $exp = explode('-', $month);
            $selected = $exp[1] . $exp[0];
            
            $claim_member_info = trim($this->input->post('key'));
            $claim_member_info = explode('-', $claim_member_info);
            $claim_member_pid = $claim_member_info[0];
            $claim_member_info = explode('==>', $claim_member_info[1]);
            $claim_member_mid = trim($claim_member_info[0]);
            $global_mortuary = $this->setting_model->global_mortuary_info();
            $deduction_amount = $global_mortuary->amount;
            $reference_number = uniqid();
            
            $mortuarycountprocess = $this->mortuary_model->mortuary_setting_info(null,$claim_member_pid,$claim_member_mid)->row();
            $this->mortuary_model->update_mortuary_autopayment($claim_member_pid,$claim_member_mid,$reference_number);
            //$current = date('Ym');
            if ($mortuarycountprocess->claim_status == 0) {
                $mortuary_list = $this->mortuary_model->masterlisting();
                foreach ($mortuary_list as $key => $value) {
                    if($value->PID==$claim_member_pid){
                       continue;
                    }
                    if($value->status_flag==3){
                       continue;
                    }
                    
                    $memberinfo = $this->member_model->member_basic_info(null,$value->PID,$value->member_id)->row();
                    $dob = new DateTime($memberinfo->dob);
                    $today   = new DateTime('today');
                    $year = $dob->diff($today)->y;
                    $contribution = $deduction_amount*$year;
                    $newbalance=0;
                    //$mortuary_balance = $this->db->get_where('members_mortuary', array('PID' => $value->PID))->row();
                    $mortuary_balance = $this->mortuary_model->get_mortuary_balances($value->PID,$value->member_id)->balance;
                    $newbalance = $mortuary_balance-$contribution;
                    if($newbalance<=0){
                        continue;
                    }
                    /*if (count($contr_setup) > 0) {
                       
                        $contribution = $contr_setup->amount;
                    } else {
                        $contr_setup = $this->db->get('mortuary_global')->row();
                        $contribution = $contr_setup->amount;
                    }*/

                    $this->mortuary_model->mortuary_transaction('DR', $value->PID, $value->member_id, $contribution, 'ADJUSTMENT','Death Claim: '.trim($claim_member_info[1]), '', $selected, 1,'',$reference_number);
                    $this->mortuary_model->recomputebalances($value->PID,$value->member_id,$newbalance);
                }
                
                $this->data['message'] ='Payment process successfully';
            } else {
                $this->data['warning'] = 'Claim already process';
            }
        }

        $this->data['content'] = 'mortuary/mortuary_autmatic';
        $this->load->view('template', $this->data);
    }

    function mortuary_setting() {
        $this->load->library('pagination');
        $this->data['title'] = lang('contribution_setting_list');

        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
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
            $key = $explode[0];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        if (!is_null($key)) {
            $config['suffix'] = '?key=' . $key;
        }


        $config["base_url"] = site_url(current_lang() . '/contribution/contribute_setting');
        //$config["back_url"] = current_url();
        $config["total_rows"] = $this->contribution_model->count_contribution_setting($key);
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

        $this->data['contribution_setting'] = $this->contribution_model->search_contribution_setting($key, $config["per_page"], $page);


        $this->data['content'] = 'contribution/contribute_setting_list';
        $this->load->view('template', $this->data);
    }
    //Added by Herald 01/08/2023 - for mortuary masterlisting
    function mortuary_master_list() {
        $this->load->library('pagination');
        $this->data['title'] = lang('mortuary_master_list');
        
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        
        
        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 40);
        }
        
        $config["per_page"] = $this->session->userdata('PER_PAGE');
        
        $key = null;
        $searchstatus = null;
        
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $explode = explode('-', $_POST['key']);
            $key = $explode[0];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        if (isset($_POST['searchstatus']) || $_POST['searchstatus'] != '') {
            $searchstatus = $_POST['searchstatus'];
        } else if (isset($_GET['searchstatus'])) {
            $searchstatus = $_GET['searchstatus'];
        } else {
            $searchstatus = '';
        }
        $suffix_array = array();

        if (!is_null($key)) {
            $suffix_array['key'] = $key;
        }

        if (!is_null($searchstatus) || $searchstatus!='') {
            $suffix_array['searchstatus'] = $searchstatus;
        }
        $this->data['jxy'] = $suffix_array;
        if (count($suffix_array) > 0) {
            $query_string = http_build_query($suffix_array, '', '&');
            $config['suffix'] = '?' . $query_string;
        }
        /*if (!is_null($key)) {
            $config['suffix'] = '?key=' . $key;
        }*/
        
        
        $config["base_url"] = site_url(current_lang() . '/mortuary/mortuary_master_list');
        $config["total_rows"] = $this->mortuary_model->count_contribution_setting($key,$searchstatus);
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
        
        $this->data['mortuary_setting'] = $this->mortuary_model->search_contribution_setting($key, $config["per_page"], $page, $searchstatus);
        $this->data['mortuary_status_list'] = $this->mortuary_model->mortuary_status()->result();
        $global_mortuary = $this->setting_model->global_mortuary_info();
        $this->data['deduction_amount'] = $global_mortuary->amount;
        $mortuary_type_claim_array = array('0'=>'N/A','1'=>'Pending','2'=>'Processing','3'=>'Approved','4'=>'Released','5'=>'Denied');
        $this->data['mortuary_type_claim_array'] = $mortuary_type_claim_array;
        
        $this->data['content'] = 'mortuary/mortuary_master_list';
        $this->load->view('template', $this->data);
    }
    
    //Added by Herald
    function mortuary_account_list() {
        $this->load->library('pagination');
        $this->data['title'] = lang('mortuary_account_list');
        
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        
        
        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 40);
        }
        
        $config["per_page"] = $this->session->userdata('PER_PAGE');
        
        $key = null;
        $searchstatus = null;
        
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $explode = explode('-', $_POST['key']);
            $key = $explode[0];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        if (isset($_POST['searchstatus']) || $_POST['searchstatus'] != '') {
            $searchstatus = $_POST['searchstatus'];
        } else if (isset($_GET['searchstatus'])) {
            $searchstatus = $_GET['searchstatus'];
        } else {
            $searchstatus = '';
        }
        $suffix_array = array();

        if (!is_null($key)) {
            $suffix_array['key'] = $key;
        }

        if (!is_null($searchstatus) || $searchstatus!='') {
            $suffix_array['searchstatus'] = $searchstatus;
        }
        $this->data['jxy'] = $suffix_array;
        if (count($suffix_array) > 0) {
            $query_string = http_build_query($suffix_array, '', '&');
            $config['suffix'] = '?' . $query_string;
        }
        /*if (!is_null($key)) {
            $config['suffix'] = '?key=' . $key;
        }*/
        
        
        $config["base_url"] = site_url(current_lang() . '/mortuary/mortuary_account_list');
        $config["total_rows"] = $this->mortuary_model->count_contribution_setting($key,$searchstatus);
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
        
        $this->data['mortuary_setting'] = $this->mortuary_model->search_contribution_setting($key, $config["per_page"], $page, $searchstatus);
        $this->data['mortuary_status_list'] = $this->mortuary_model->mortuary_status()->result();
        
        $this->data['content'] = 'mortuary/mortuary_setting_list';
        $this->load->view('template', $this->data);
    }
    
    function mortuary_setting_create($id = null) {
        $this->data['title'] = lang('mortuary_setting');
        $this->data['id'] = $id;
        $global_contribution = $this->setting_model->global_mortuary_info();
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        if ($this->input->post('open_balance')) {
            $initial = $this->input->post('open_balance');
            $_POST['open_balance'] = str_replace(',', '', $initial);
        }
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('contribution_source', lang('contribution_source'), 'required');
        $this->form_validation->set_rules('mortuary_status', lang('mortuary_status'), 'required|numeric');
        $this->form_validation->set_rules('open_balance', lang('open_balance'), 'required|numeric');
        $this->form_validation->set_rules('posting_date', lang('mortuary_transaction_date'), 'required|valid_date');
        
        if ($this->form_validation->run() == TRUE) {

            $PID_initial = explode('-', trim($this->input->post('pid')));
            $member_id_initial = explode('-', trim($this->input->post('member_id')));
            $PID = $PID_initial[0];
            $member_id = $member_id_initial[0];
            $posting_date = date("Y-m-d",strtotime($this->input->post('posting_date')));

            $source = $this->input->post('contribution_source');
            $status = $this->input->post('mortuary_status');
            $amount = trim($this->input->post('open_balance'));
            //if ($global_contribution->amount <= $amount) {
            if(1){
                $info = array(
                    'PID' => $PID,
                    'member_id' => $member_id,
                    'contribute_source' => $source,
                    'amount' => $amount,
                    'status_flag' => $status,
                    'posting_date' => $posting_date,
                    'createdby' => current_user()->id,
                    'PIN' => current_user()->PIN,
                );
                $accountdata = $this->mortuary_model->contribution_setting($info, $id);
                if ($accountdata) {
                    $this->session->set_flashdata('message', lang('mortuary_setting_success'));
                    redirect(current_lang() . '/mortuary/mortuary_setting_create/' . $this->data['id'], 'refresh');
                } else {
                    $this->data['warning'] = lang('mortuary_setting_exist');
                }
            } else {
                $this->data['warning'] = lang('mortuary_minimum_required') . ' ' . number_format($global_contribution->amount, 2);
            }
        }

        if (!is_null($id)) {
            $this->data['contr'] = $this->mortuary_model->contribution_setting_info($id)->row();
        }
        $this->data['contribution_source_list'] = $this->mortuary_model->contribution_source()->result();
        $this->data['mortuary_status_list'] = $this->mortuary_model->mortuary_status()->result();
        $this->data['content'] = 'mortuary/setting_new_contribution';
        $this->load->view('template', $this->data);
    }

    function mortuary_payment() {
        $this->data['title'] = lang('mortuary_service');
        $this->data['paymenthod'] = $this->finance_model->paymentmenthod();
        if ($this->input->post('amount')) {
            $initial = $this->input->post('amount');
            $_POST['amount'] = str_replace(',', '', $initial);
        }
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('trans_type', lang('transaction_type'), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');
        $this->form_validation->set_rules('paymenthod', lang('paymentmethod'), 'required');
        $this->form_validation->set_rules('comment', lang('comment'), '');
        $this->form_validation->set_rules('trans_date', lang('mortuary_transaction_date'), 'required|valid_date');
        
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
            $pid = $PID_initial[0];
            $member_id = $member_id_initial[0];


            $trans_date = date("Y-m-d",strtotime($this->input->post('trans_date')));
            
            $trans_type = $this->input->post('trans_type');

            $comment = trim($this->input->post('comment'));
            $paymethod = trim($this->input->post('paymenthod'));
            $amount = trim($this->input->post('amount'));
            $customer_name = trim($this->input->post('customer_name'));
            $continue = true;
            if ($trans_type == 'DR') {
                $account_balance = $this->mortuary_model->contribution_balance($pid, $member_id);
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
                $receipt = $this->mortuary_model->contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, $check_number_received,'',0, $trans_date);
                if ($receipt) {
                    $this->session->set_flashdata('next_customer', site_url(current_lang() . '/mortuary/mortuary_payment'));
                    $this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                    redirect(current_lang() . '/mortuary/receipt_view/' . $receipt, 'refresh');
                } else {
                    $this->data['warning'] = lang('transaction_fail');
                }
            } else {
                //insufficient balance
                //echo 'dddd';
                $this->data['warning'] = lang('insufficient_balance');
            }
        }
        $this->data['content'] = 'mortuary/fund';
        $this->load->view('template', $this->data);
    }

    function receipt_view($receipt) {
        $this->lang->load('setting');
        $trans = $this->mortuary_model->get_transaction($receipt);
        if ($trans) {
            $this->data['title'] = lang('view_receipt');
            $this->data['trans'] = $trans;
            $this->data['content'] = 'mortuary/receipt_mortuary';
            $this->load->view('template', $this->data);
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function print_receipt($receipt) {
        $this->lang->load('setting');
        $trans = $this->mortuary_model->get_transaction($receipt);
        if ($trans) {
            include 'include/receipt_mortuary.php';
            exit;
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function mortuary_transaction() {
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

        $config["base_url"] = site_url(current_lang() . '/mortuary/mortuary_transaction');
        $config["total_rows"] = $this->mortuary_model->count_transaction($key, $from, $upto);
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

        $this->data['transactionlist'] = $this->mortuary_model->search_transaction($key, $from, $upto, $config["per_page"], $page);


        $this->data['content'] = 'mortuary/transaction_history';
        $this->load->view('template', $this->data);
    }
    //Added by Herald - 02/23/2023
    function mortuary_ledger() {
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

        $config["base_url"] = site_url(current_lang() . '/mortuary/mortuary_transaction');
        $config["total_rows"] = $this->mortuary_model->count_transaction($key, $from, $upto);
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

        $this->data['transactionlist'] = $this->mortuary_model->search_transaction($key, $from, $upto, $config["per_page"], $page);


        $this->data['content'] = 'mortuary/mortuary_ledger';
        $this->load->view('template', $this->data);
    }
    
    function autosuggest($id) {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;
            
            
            $auto = $this->db->query("SELECT PID,firstname, middlename, lastname,member_id FROM members WHERE status=1 AND PIN='$pin' AND ( PID LIKE '$q%' OR  member_id LIKE '$q%' OR firstname LIKE '$q%' OR lastname LIKE '$q%')")->result();
            //$auto = $this->db->query("SELECT PIDFROM members WHERE  PID LIKE '$q%' or member_id LIKE '$q%' AND PIN='$PIN'")->result();
            
            foreach ($auto as $key => $value) {
                if ($id == 'pid') {
                    echo $value->PID . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
                } else if ($id == 'mid') {
                    echo $value->member_id . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
                }
            }
    }
    
    function search_member_mortuary() {
        $this->load->model('setting_model');
        $this->load->model('contribution_model');
        $this->load->model('mortuary_model');
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
        
        $member = $this->member_model->member_basic_info(null, $pid, $member_id,1)->row();
        
        
        $status = array();
        
        
        if (count($member) == 1) {
            $balance = 0;
            $current_mortuary = $this->mortuary_model->contribution_balance($member->PID, $member->member_id);
            if ($current_mortuary) {
                $balance = $current_mortuary->balance;
            }
            
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            $status['balance'] = number_format($balance,2);
            echo json_encode($status);
        } else {
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }

    function mortuary_post_to_gl(){
        $this->load->model('mortuary_model');
        $id = $this->input->post('id');
        $posted = $this->input->post('posted')?0:1;

        $resultset = $this->mortuary_model->search_mortuary_setting($id);



        $pid = trim($resultset->PID);
        $member_id = trim($resultset->member_id);
        //$pid = $PID_initial[0];
        //$member_id = $member_id_initial[0];


        $trans_date = date("Y-m-d",strtotime($resultset->posting_date));

        $trans_type = $posted ? 'CR' : 'DR';

        $comment = 'BEGINNING BALANCE';
        $paymethod = 'OTHERS';
        $amount = trim($resultset->amount);
        //$customer_name = trim($this->input->post('customer_name'));
        $continue = true;
        if ($trans_type == 'DR') {
            $account_balance = $this->mortuary_model->contribution_balance($pid, $member_id);
            if ($account_balance) {
                $remaining = $account_balance->balance - $amount;
                if ($remaining < 1) {
                    $continue = FALSE;
                }
            } else {
                $continue = FALSE;
            }
        }
        $status = array();
        $postedsuccess = $posted;
        if ($continue) {
            //now finalize
            $receipt = $this->mortuary_model->contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, '','',0, $trans_date);
            if ($receipt) {
                //$this->session->set_flashdata('next_customer', site_url(current_lang() . '/mortuary/mortuary_payment'));
                //$this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                //redirect(current_lang() . '/mortuary/receipt_view/' . $receipt, 'refresh');
                $ifposted = $this->mortuary_model->post_to_gl($id, $posted);
                $status['success'] = 'Y';
                $status['posted'] = $posted;
            } else {
                $status['message'] = lang('transaction_fail');
                $status['success'] = 'N';
                $status['posted'] = $this->input->post('posted');
            }
        } else {
            //insufficient balance
            //echo 'dddd';
            $status['message'] = lang('insufficient_balance');
            $status['success'] = 'N';
            $status['posted'] = $this->input->post('posted');
        }



        
       // $ifposted = $this->mortuary_model->post_to_gl($id, $posted);
        //$status['success'] = 'Y';
        $status['id'] = $id;
        //$status['posted'] = $posted;
        
        echo json_encode($status);
    }

    function autosuggest_member_id_all() {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;

        $auto = $this->db->query("SELECT * FROM members WHERE PIN='$pin' AND  (PID LIKE '$q%'  OR firstname LIKE '$q%' OR lastname LIKE '$q%' OR member_id LIKE '$q%')")->result();
      

        foreach ($auto as $key => $value) {

            echo $value->PID . ' - '.$value->member_id.'  ==> ' . $value->lastname . ', ' . $value->firstname . ' ' . $value->middlename . "\n";
        }
      
    }

    function mortuary_setting_edit($id = null) {
        $this->data['title'] = lang('mortuary_setting');
        $this->data['id'] = $id;
        $global_contribution = $this->setting_model->global_mortuary_info();
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        if ($this->input->post('open_balance')) {
            $initial = $this->input->post('open_balance');
            $_POST['open_balance'] = str_replace(',', '', $initial);
        }
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        //$this->form_validation->set_rules('contribution_source', lang('contribution_source'), 'required');
        $this->form_validation->set_rules('mortuary_status', lang('mortuary_status'), 'required|numeric');
        //$this->form_validation->set_rules('open_balance', lang('open_balance'), 'required|numeric');
        //$this->form_validation->set_rules('posting_date', lang('mortuary_transaction_date'), 'required|valid_date');
        
        if ($this->form_validation->run() == TRUE) {

            $PID_initial = explode('-', trim($this->input->post('pid')));
            $member_id_initial = explode('-', trim($this->input->post('member_id')));
            $PID = $PID_initial[0];
            $member_id = $member_id_initial[0];
            $posting_date = date("Y-m-d",strtotime($this->input->post('posting_date')));

            $source = $this->input->post('contribution_source');
            $status = $this->input->post('mortuary_status');
            $amount = trim($this->input->post('open_balance'));
            $remarks = $this->input->post('mortuary_remarks');
            //if ($global_contribution->amount <= $amount) {
            if(1){
                $info = array(
                    'PID' => $PID,
                    'member_id' => $member_id,
                    //'contribute_source' => $source,
                    //'amount' => $amount,
                    'status_flag' => $status,
                    //'posting_date' => $posting_date,
                    'remarks' => $remarks,
                    'createdby' => current_user()->id,
                    'PIN' => current_user()->PIN,
                );
                $accountdata = $this->mortuary_model->contribution_setting($info, $id);
                if ($accountdata) {
                    $this->session->set_flashdata('message', lang('mortuary_setting_success'));
                    redirect(current_lang() . '/mortuary/mortuary_setting_edit/' . $this->data['id'], 'refresh');
                } else {
                    $this->data['warning'] = lang('mortuary_setting_exist');
                }
            } else {
                $this->data['warning'] = lang('mortuary_minimum_required') . ' ' . number_format($global_contribution->amount, 2);
            }
        }

        if (!is_null($id)) {
            $this->data['contr'] = $this->mortuary_model->contribution_setting_info($id)->row();
        }
        $this->data['contribution_source_list'] = $this->mortuary_model->contribution_source()->result();
        $this->data['mortuary_status_list'] = $this->mortuary_model->mortuary_status()->result();
        
        $this->load->library('user_agent');
        //$this->data['http_referrer'] = $this->agent->referrer();
        $this->data['http_referrer'] = $this->agent->referrer();
        
        $this->data['content'] = 'mortuary/setting_edit';
        $this->load->view('template', $this->data);
    }

    function recomputebalances(){
        $this->load->model('mortuary_model');
        $this->load->model('report_model');
        $this->load->model('setting_model');
        
        $global_mortuary = $this->setting_model->global_mortuary_info();
        $deduct_amount = $global_mortuary->amount;       
        $maintaining_balance = $global_mortuary->maintaining_balance;       
        $endangered_amount = $global_mortuary->endangered_amount;       
        $dismember_amount = $global_mortuary->dismember_amount;       
        $mortuary_list = $this->mortuary_model->masterlisting();
       
        $todaydate = date('Y-m-d');
        
        foreach ($mortuary_list as $key => $value) {
            //echo json_encode(array('success'=>1,'message'=>'Process Successfully Break'));
            $balance=0;
            $debit=0;
            $credit=0;
            $mortuary_ledger = $this->report_model->mortuary_ledger('1900-01-01',$todaydate,$value->member_id);
            foreach($mortuary_ledger as $key1 => $value1){
                if ($value1->debit > 0) {
                    $balance -= $value1->debit;
                    $debit += $value1->debit;
                } else if ($value1->credit > 0) {
                    $balance += $value1->credit;
                    $credit += $value1->credit;
                }
                //echo 'member:'.$value->member_id.' Debit:'.$debit.' Credit:'.$credit.' Balance:'.$balance.'<br/>';
            }
            //echo 'member:'.$value->member_id.' Balance:'.$balance.'<br/>';
            $status_flag=1;
            if($balance>=$maintaining_balance){
                $status_flag=1;
            }else if($balance>0 && $balance<=$endangered_amount){
                $status_flag=2;
            }else{
                $status_flag = $value->status_flag;
            }
            $this->mortuary_model->update_mortuary_status($value->PID,$value->member_id,$status_flag);
            $this->mortuary_model->recomputebalances($value->PID,$value->member_id,$balance);
            
        }
        echo json_encode(array('success'=>1,'message'=>'Process Successfully'));
    }

    function recomputebalancesindividual($id){
        $this->load->model('mortuary_model');
        $this->load->model('report_model');
        $this->load->model('setting_model');
        
        $global_mortuary = $this->setting_model->global_mortuary_info();
        $deduct_amount = $global_mortuary->amount;       
        $maintaining_balance = $global_mortuary->maintaining_balance;       
        $endangered_amount = $global_mortuary->endangered_amount;       
        $dismember_amount = $global_mortuary->dismember_amount;       
        $mortuary_list = $this->mortuary_model->masterlisting(0,$id);
       
        $todaydate = date('Y-m-d');
        
        foreach ($mortuary_list as $key => $value) {
            //echo json_encode(array('success'=>1,'message'=>'Process Successfully Break'));
            $balance=0;
            $debit=0;
            $credit=0;
            $mortuary_ledger = $this->report_model->mortuary_ledger('1900-01-01',$todaydate,$value->member_id);
            foreach($mortuary_ledger as $key1 => $value1){
                if ($value1->debit > 0) {
                    $balance -= $value1->debit;
                    $debit += $value1->debit;
                } else if ($value1->credit > 0) {
                    $balance += $value1->credit;
                    $credit += $value1->credit;
                }
                //echo 'member:'.$value->member_id.' Debit:'.$debit.' Credit:'.$credit.' Balance:'.$balance.'<br/>';
            }
            //echo 'member:'.$value->member_id.' Balance:'.$balance.'<br/>';
            $status_flag=1;
            if($balance>=$maintaining_balance){
                $status_flag=1;
            }else if($balance>0 && $balance<=$endangered_amount){
                $status_flag=2;
            }else{
                $status_flag = $value->status_flag;
            }
            $this->mortuary_model->update_mortuary_status($value->PID,$value->member_id,$status_flag);
            $this->mortuary_model->recomputebalances($value->PID,$value->member_id,$balance);
            
        }
        echo json_encode(array('success'=>1,'message'=>'Process Successfully','balance'=>$balance));
    }


}

?>
