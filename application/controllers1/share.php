<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of share
 *
 * @author miltone
 */
class Share extends CI_Controller {

    //put your code here


    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_share');
        $this->lang->load('setting');
        $this->load->model('member_model');
        $this->load->model('finance_model');
        $this->load->model('setting_model');
        $this->load->model('share_model');
    }

    function index() {
        
    }

    
    function refund_share(){
         $this->data['title'] = lang('refund_share');
        $this->data['paymenthod'] = $this->finance_model->paymentmenthod(1);
        if ($this->input->post('open_balance')) {
            $initial = $this->input->post('open_balance');
            $_POST['open_balance'] = str_replace(',', '', $initial);
        }
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('open_balance', lang('index_amount'), 'required|numeric');
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
            $amount = trim($this->input->post('open_balance'));
            $real_amount = $amount;
            $comment = trim($this->input->post('comment'));
            $paymethod = trim($this->input->post('paymenthod'));

            $share_info = $this->share_model->share_member_info($pid, $member_id);
            $share_setup = $this->setting_model->share_setting_info();
            $cost_per_share = $share_setup->amount;

            if ($share_info) {
                //share row exist
                $total_amount_share = $share_info->remainbalance + $share_info->amount;
                if($total_amount_share >= $amount){
                ///$amount = $amount + $share_info->remainbalance;
                $share_item = $this->get_share_from_amount($amount, $share_setup->amount);
                $totalshare = $share_item[0];
                $remaining_amount = $share_item[1];
                $share_number = 0;

                
                    //safe to add share
                    $share_number = $totalshare;
                    $amountshare = ($totalshare * $share_setup->amount);
                    $remain_amount = $remaining_amount;
                    
                    $add_share = $this->share_model->refund_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $check_number_received);

                    if ($add_share) {
                        $this->session->set_flashdata('next_customer', site_url(current_lang() . '/share/refund_share'));
                        $this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                        redirect(current_lang() . '/share/receipt_view/' . $add_share, 'refresh');
                    } else {
                        $this->data['warning'] = lang('share_trans_fail');
                    }
                    
                
                }else{
                    $this->data['warning'] = lang('share_amount_exceed_amount_available');
                }
            } else {
                // share row not exist
                $this->data['warning'] = lang('share_not_found');
            }
        }


        $this->data['content'] = 'share/refund_share';
        $this->load->view('template', $this->data);
        
    }






    function is_max_share_reached($newshare, $previuous_share, $maxshare) {
        $temp = $newshare + $previuous_share;
        if ($temp > $maxshare) {
            return TRUE;
        }
        return FALSE;
    }

    function get_share_from_amount($dividend, $divisor) {
        $quotient = intval($dividend / $divisor);
        $remainder = $dividend % $divisor;
        return array($quotient, $remainder);
    }

    function share_buy() {
        $this->data['title'] = lang('share_buy');
        $this->data['paymenthod'] = $this->finance_model->paymentmenthod();
        if ($this->input->post('open_balance')) {
            $initial = $this->input->post('open_balance');
            $_POST['open_balance'] = str_replace(',', '', $initial);
        }
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('open_balance', lang('index_amount'), 'required|numeric');
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
            $amount = trim($this->input->post('open_balance'));
            $real_amount = $amount;
            $comment = trim($this->input->post('comment'));
            $paymethod = trim($this->input->post('paymenthod'));

            $share_info = $this->share_model->share_member_info($pid, $member_id);
            $share_setup = $this->setting_model->share_setting_info();
            $cost_per_share = $share_setup->amount;

            if ($share_info) {
                //share row exist
                $amount = $amount + $share_info->remainbalance;
                $share_item = $this->get_share_from_amount($amount, $share_setup->amount);
                $totalshare = $share_item[0];
                $remaining_amount = $share_item[1];
                $share_number = 0;

                $is_max_share_reached = $this->is_max_share_reached($totalshare, $share_info->totalshare, $share_setup->max_share);
                if (!$is_max_share_reached) {
                    //safe to add share
                    $share_number = $totalshare;
                    $amountshare = ($totalshare * $share_setup->amount);
                    $remain_amount = $remaining_amount;
                    
                    $add_share = $this->share_model->add_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $check_number_received);

                    if ($add_share) {
                        $this->session->set_flashdata('next_customer', site_url(current_lang() . '/share/share_buy'));
                        $this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                        redirect(current_lang() . '/share/receipt_view/' . $add_share, 'refresh');
                    } else {
                        $this->data['warning'] = lang('share_trans_fail');
                    }
                } else {
                    $this->data['warning'] = lang('share_max_reached');
                }
            } else {
                // share row not exist

                $share_item = $this->get_share_from_amount($amount, $share_setup->amount);
                $totalshare = $share_item[0];
                $remaining_amount = $share_item[1];
                $share_number = 0;
                $is_max_share_reached = $this->is_max_share_reached($totalshare, 0, $share_setup->max_share);
                if (!$is_max_share_reached) {
                    //safe to add share
                    $share_number = $totalshare;
                    $amountshare = ($totalshare * $share_setup->amount);
                    $remain_amount = $remaining_amount;
                    $add_share = $this->share_model->add_share($pid, $member_id,  $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $check_number_received);

                    if ($add_share) {
                        $this->session->set_flashdata('next_customer', site_url(current_lang() . '/share/share_buy'));
                        $this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                        redirect(current_lang() . '/share/receipt_view/' . $add_share, 'refresh');
                    } else {
                        $this->data['warning'] = lang('share_trans_fail');
                    }
                } else {
                    $this->data['warning'] = lang('share_max_reached');
                }
            }
        }


        $this->data['content'] = 'share/buy_share';
        $this->load->view('template', $this->data);
    }

    function receipt_view($receipt) {
        $this->lang->load('setting');
        $trans = $this->share_model->get_transaction($receipt);
        if ($trans) {
            $this->data['title'] = lang('view_receipt');
            $this->data['trans'] = $trans;
            $this->data['content'] = 'share/receipt_share';
            $this->load->view('template', $this->data);
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function print_receipt($receipt) {
        $this->lang->load('setting');
        $trans = $this->share_model->get_transaction($receipt);
        if ($trans) {
            include 'include/receipt_share.php';
            exit;
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }
 
    
    
    function share_transaction_search(){
        
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

        $config["base_url"] = site_url(current_lang() . '/share/share_transaction_search/');
        $config["total_rows"] = $this->share_model->count_transaction($key, $from, $upto);
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

        $this->data['transactionlist'] = $this->share_model->search_transaction($key, $from, $upto, $config["per_page"], $page);


        $this->data['content'] = 'share/transaction_history';
        $this->load->view('template', $this->data);
    }
    
 

}

?>
