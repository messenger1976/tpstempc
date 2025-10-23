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
class Contribution extends CI_Controller {

    //put your code here

    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_contribution');
        $this->lang->load('member');
        $this->lang->load('finance');
        $this->load->model('finance_model');
        $this->load->model('member_model');
        $this->load->model('contribution_model');
        $this->load->model('setting_model');
    }

    function automatic_contribution_process() {
        $this->data['title'] = 'Automatic CBU Payment';
        $this->form_validation->set_rules('date_month', 'Months', 'required|valid_month');

        if ($this->form_validation->run() == TRUE) {
            $month = trim($this->input->post('date_month'));
            $exp = explode('-', $month);
            
            $selected = $exp[1] . $exp[0];
            $current = date('Ym');
            if ($selected <= $current) {
                $member_list = $this->member_model->member_basic_info()->result();
                foreach ($member_list as $key => $value) {
                    $monthly_contribution = 0;
                    $contr_setup = $this->db->get_where('contribution_settings', array('PID' => $value->PID))->row();
                    if (count($contr_setup) > 0) {
                       
                        $monthly_contribution = $contr_setup->amount;
                    } else {
                        $contr_setup = $this->db->get('contribution_global')->row();
                        $monthly_contribution = $contr_setup->amount;
                    }

                    $this->contribution_model->contribution_transaction('CR', $value->PID, $value->member_id, $monthly_contribution, 'CASH', '', '', $selected, 1);
                }
                
                $this->data['message'] ='Payment process successfully';
            } else {
                $this->data['warning'] = 'Invalid month';
            }
        }

        $this->data['content'] = 'contribution/contribution_autmatic';
        $this->load->view('template', $this->data);
    }

    function contribute_setting() {
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

    function contribute_setting_create($id = null) {
        $this->data['title'] = lang('contribute_setting');
        $this->data['id'] = $id;
        $global_contribution = $this->setting_model->global_contribution_info();
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
        $this->form_validation->set_rules('open_balance', lang('contribution_amount'), 'required|numeric');

        if ($this->form_validation->run() == TRUE) {

            $PID_initial = explode('-', trim($this->input->post('pid')));
            $member_id_initial = explode('-', trim($this->input->post('member_id')));
            $PID = $PID_initial[0];
            $member_id = $member_id_initial[0];
            $posting_date = date("Y-m-d",strtotime($this->input->post('posting_date')));
            $source = $this->input->post('contribution_source');
            $amount = trim($this->input->post('open_balance'));
            if ($global_contribution->amount <= $amount) {
                $info = array(
                    'PID' => $PID,
                    'member_id' => $member_id,
                    'contribute_source' => $source,
                    'posting_date' => $posting_date,
                    'amount' => $amount,
                    'createdby' => current_user()->id,
                    'PIN' => current_user()->PIN,
                );
                $accountdata = $this->contribution_model->contribution_setting($info, $id);
                if ($accountdata) {
                    $this->session->set_flashdata('message', lang('contribution_setting_success'));
                    redirect(current_lang() . '/contribution/contribute_setting_create/' . $this->data['id'], 'refresh');
                } else {
                    $this->data['warning'] = lang('contribution_setting_exist');
                }
            } else {
                $this->data['warning'] = lang('contribution_minimum_required') . ' ' . number_format($global_contribution->amount, 2);
            }
        }

        if (!is_null($id)) {
            $this->data['contr'] = $this->contribution_model->contribution_setting_info($id)->row();
        }
        $this->data['contribution_source_list'] = $this->contribution_model->contribution_source()->result();
        $this->data['content'] = 'contribution/setting_new_contribution';
        $this->load->view('template', $this->data);
    }

    function contribution_payment() {
        $this->data['title'] = lang('contribution_service');
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



            $trans_type = $this->input->post('trans_type');

            $comment = trim($this->input->post('comment'));
            $paymethod = trim($this->input->post('paymenthod'));
            $amount = trim($this->input->post('amount'));
            $customer_name = trim($this->input->post('customer_name'));
            $continue = true;
            if ($trans_type == 'DR') {
                $account_balance = $this->contribution_model->contribution_balance($pid, $member_id);
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
                $receipt = $this->contribution_model->contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, $check_number_received);
                if ($receipt) {
                    $this->session->set_flashdata('next_customer', site_url(current_lang() . '/contribution/contribution_payment'));
                    $this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                    redirect(current_lang() . '/contribution/receipt_view/' . $receipt, 'refresh');
                } else {
                    $this->data['warning'] = lang('transaction_fail');
                }
            } else {
                //insufficient balance
                //echo 'dddd';
                $this->data['warning'] = lang('insufficient_balance');
            }
        }
        $this->data['content'] = 'contribution/contribute';
        $this->load->view('template', $this->data);
    }

    function receipt_view($receipt) {
        $this->lang->load('setting');
        $trans = $this->contribution_model->get_transaction($receipt);
        if ($trans) {
            $this->data['title'] = lang('view_receipt');
            $this->data['trans'] = $trans;
            $this->data['content'] = 'contribution/receipt_contribute';
            $this->load->view('template', $this->data);
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function print_receipt($receipt) {
        $this->lang->load('setting');
        $trans = $this->contribution_model->get_transaction($receipt);
        if ($trans) {
            include 'include/receipt_contribution.php';
            exit;
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function contribution_transaction() {
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
            $key1 = $expl[0];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
            $expl = explode('-', $key);
            $key1 = $expl[0];
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

        $config["base_url"] = site_url(current_lang() . '/contribution/contribution_transaction');
        $config["total_rows"] = $this->contribution_model->count_transaction($key1, $from, $upto);
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

        $this->data['transactionlist'] = $this->contribution_model->search_transaction($key1, $from, $upto, $config["per_page"], $page);


        $this->data['content'] = 'contribution/transaction_history';
        $this->load->view('template', $this->data);
    }

    function contribution_post_to_gl(){
        $this->load->model('contribution_model');
        $id = $this->input->post('id');
        $posted = $this->input->post('posted')?0:1;

        $resultset = $this->contribution_model->search_contribution_setting_id($id);

        $pid = trim($resultset->PID);
        $member_id = trim($resultset->member_id);
        
        $trans_date = date("Y-m-d",strtotime($resultset->posting_date));

        $trans_type = $posted ? 'CR' : 'DR';

        $comment = 'BEGINNING BALANCE';
        $paymethod = 'ADJUSTMENT';
        $amount = trim($resultset->amount);
        $continue = true;
        if ($trans_type == 'DR') {
            $account_balance = $this->contribution_model->contribution_balance($pid, $member_id);
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
            $receipt = $this->contribution_model->contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, '','',0, $trans_date);
            if ($receipt) {
                $ifposted = $this->contribution_model->post_to_gl($id, $posted);
                $status['success'] = 'Y';
                $status['posted'] = $posted;
            } else {
                $status['message'] = lang('transaction_fail');
                $status['success'] = 'N';
                $status['posted'] = $this->input->post('posted');
            }
        } else {
            //insufficient balance
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

}

?>
