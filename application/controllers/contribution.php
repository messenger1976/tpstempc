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
                    if ($contr_setup) {
                       
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
        if ($this->input->post('key') && $this->input->post('key') != '') {
            $explode = explode('-', $this->input->post('key'));
            $key = $explode[0];
        } else if ($this->input->get('key')) {
            $key = $this->input->get('key');
        }

        $status = null;
        if ($this->input->post('status') !== FALSE) {
            $post_status = $this->input->post('status');
            if ($post_status === '0' || $post_status === '1') {
                $status = $post_status;
            }
        } else if ($this->input->get('status') !== FALSE) {
            $get_status = $this->input->get('status');
            if ($get_status === '0' || $get_status === '1') {
                $status = $get_status;
            }
        }

        $query_params = array();
        if (!is_null($key) && $key != '') {
            $query_params['key'] = $key;
        }
        if (!is_null($status) && ($status === '0' || $status === '1')) {
            $query_params['status'] = $status;
        }

        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url(current_lang() . '/contribution/contribute_setting/?' . $query_string);
        }


        $config["base_url"] = site_url(current_lang() . '/contribution/contribute_setting');
        $config["total_rows"] = $this->contribution_model->count_contribution_setting($key, $status);
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

        $this->data['contribution_setting'] = $this->contribution_model->search_contribution_setting($key, $config["per_page"], $page, $status);


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

            // Handle member IDs with dashes (e.g., "2005-00173 - BRENDALOU SALES")
            $pid_value = trim($this->input->post('pid'));
            $member_id_value = trim($this->input->post('member_id'));
            
            // Extract PID - check for " - " separator first
            if (strpos($pid_value, ' - ') !== false) {
                $PID_initial = explode(' - ', $pid_value);
                $PID = trim($PID_initial[0]);
            } else if (preg_match('/^[\d\-]+/', $pid_value, $matches)) {
                $PID = trim($matches[0]);
            } else {
                $PID_initial = explode('-', $pid_value);
                $PID = trim($PID_initial[0]);
            }
            
            // Extract member_id - check for " - " separator first
            if (strpos($member_id_value, ' - ') !== false) {
                $member_id_initial = explode(' - ', $member_id_value);
                $member_id = trim($member_id_initial[0]);
            } else if (preg_match('/^[\d\-]+/', $member_id_value, $matches)) {
                $member_id = trim($matches[0]);
            } else {
                $member_id_initial = explode('-', $member_id_value);
                $member_id = trim($member_id_initial[0]);
            }
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
            // Handle member IDs with dashes (e.g., "2005-00173 - BRENDALOU SALES")
            $pid_value = trim($this->input->post('pid'));
            $member_id_value = trim($this->input->post('member_id'));
            
            // Extract PID - check for " - " separator first
            if (strpos($pid_value, ' - ') !== false) {
                $PID_initial = explode(' - ', $pid_value);
                $pid = trim($PID_initial[0]);
            } else if (preg_match('/^[\d\-]+/', $pid_value, $matches)) {
                $pid = trim($matches[0]);
            } else {
                $PID_initial = explode('-', $pid_value);
                $pid = trim($PID_initial[0]);
            }
            
            // Extract member_id - check for " - " separator first
            if (strpos($member_id_value, ' - ') !== false) {
                $member_id_initial = explode(' - ', $member_id_value);
                $member_id = trim($member_id_initial[0]);
            } else if (preg_match('/^[\d\-]+/', $member_id_value, $matches)) {
                $member_id = trim($matches[0]);
            } else {
                $member_id_initial = explode('-', $member_id_value);
                $member_id = trim($member_id_initial[0]);
            }



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
        // Suppress PHP warnings/errors for TCPDF compatibility (PHP 7.3+ issues)
        $old_error_reporting = error_reporting(0);
        $old_display_errors = ini_set('display_errors', 0);
        
        // Start output buffering to prevent any output before PDF
        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        $this->lang->load('setting');
        $trans = $this->contribution_model->get_transaction($receipt);
        if ($trans) {
            // Clear any output that might have been generated
            ob_clean();
            
            // Suppress warnings for TCPDF library
            @include 'include/receipt_contribution.php';
            
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
        $key1 = null;
        $from = null;
        $to = null;
        
        // Handle POST submission - save to session or clear if empty
        if (isset($_POST['key'])) {
            if ($_POST['key'] != '') {
                $key = $_POST['key'];
                $expl = explode('-', $key);
                $key1 = trim($expl[0]);
                $this->session->set_userdata('contribution_transaction_key', $key);
            } else {
                // Clear session if empty value submitted
                $this->session->unset_userdata('contribution_transaction_key');
            }
        } else if (isset($_GET['key'])) {
            // GET parameter takes priority - use it even if empty
            if ($_GET['key'] != '') {
                $key = $_GET['key'];
                $expl = explode('-', $key);
                $key1 = trim($expl[0]);
                $this->session->set_userdata('contribution_transaction_key', $key);
            } else {
                // Clear session if GET is empty
                $this->session->unset_userdata('contribution_transaction_key');
            }
        } else if ($this->session->userdata('contribution_transaction_key')) {
            // Use session value if no GET/POST
            $key = $this->session->userdata('contribution_transaction_key');
            $expl = explode('-', $key);
            $key1 = trim($expl[0]);
        }

        // If Member ID is provided, ignore date filters
        // Otherwise, use date filters
        if (empty($key1) || $key1 == '0') {
            // Member ID is blank/empty, use date filters
            if (isset($_POST['from'])) {
                if ($_POST['from'] != '') {
                    $from = format_date($_POST['from']);
                    $this->session->set_userdata('contribution_transaction_from', format_date($_POST['from'], FALSE));
                } else {
                    // Clear session if empty value submitted and set default
                    $this->session->unset_userdata('contribution_transaction_from');
                    $from = date('Y-m-d');
                }
            } else if (isset($_GET['from'])) {
                // GET parameter takes priority - use it even if empty
                if ($_GET['from'] != '') {
                    $from = format_date($_GET['from']);
                    $this->session->set_userdata('contribution_transaction_from', format_date($_GET['from'], FALSE));
                } else {
                    // Clear session if GET is empty and set default
                    $this->session->unset_userdata('contribution_transaction_from');
                    $from = date('Y-m-d');
                }
            } else if ($this->session->userdata('contribution_transaction_from')) {
                // Use session value if no GET/POST
                $from = format_date($this->session->userdata('contribution_transaction_from'));
            } else {
                $from = date('Y-m-d');
            }

            if (isset($_POST['upto'])) {
                if ($_POST['upto'] != '') {
                    $upto = format_date($_POST['upto']);
                    $this->session->set_userdata('contribution_transaction_upto', format_date($_POST['upto'], FALSE));
                } else {
                    // Clear session if empty value submitted and set default
                    $this->session->unset_userdata('contribution_transaction_upto');
                    $upto = date('Y-m-d');
                }
            } else if (isset($_GET['upto'])) {
                // GET parameter takes priority - use it even if empty
                if ($_GET['upto'] != '') {
                    $upto = format_date($_GET['upto']);
                    $this->session->set_userdata('contribution_transaction_upto', format_date($_GET['upto'], FALSE));
                } else {
                    // Clear session if GET is empty and set default
                    $this->session->unset_userdata('contribution_transaction_upto');
                    $upto = date('Y-m-d');
                }
            } else if ($this->session->userdata('contribution_transaction_upto')) {
                // Use session value if no GET/POST
                $upto = format_date($this->session->userdata('contribution_transaction_upto'));
            } else {
                $upto = date('Y-m-d');
            }
        } else {
            // Member ID is provided, ignore date filters
            $from = null;
            $upto = null;
        }


        $suffix_array = array();

        if (!is_null($key) && $key != '') {
            $suffix_array['key'] = $key;
        }

        if (!is_null($from) && $from != '') {
            $suffix_array['from'] = $from;
        }

        if (!is_null($upto) && $upto != '') {
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

        // Pass key1 (can be null if empty) and date filters
        $this->data['transactionlist'] = $this->contribution_model->search_transaction($key1, $from, $upto, $config["per_page"], $page);


        $this->data['content'] = 'contribution/transaction_history';
        $this->load->view('template', $this->data);
    }

    function delete_transaction($receipt) {
        // Verify receipt is provided
        if (empty($receipt)) {
            $this->session->set_flashdata('warning', 'Invalid transaction receipt');
            redirect(current_lang() . '/contribution/contribution_transaction', 'refresh');
            return;
        }
        
        // Delete the transaction
        $result = $this->contribution_model->delete_transaction($receipt);
        
        if ($result) {
            $this->session->set_flashdata('message', 'Transaction deleted successfully');
        } else {
            $this->session->set_flashdata('warning', 'Failed to delete transaction. Transaction may not exist or you may not have permission.');
        }
        
        // Redirect back to transaction list
        redirect(current_lang() . '/contribution/contribution_transaction', 'refresh');
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
                // Update posted status
                $ifposted = $this->contribution_model->post_to_gl($id, $posted);
                
                // Post to General Ledger
                if ($ifposted && $posted == 1) {
                    $gl_posted = $this->contribution_model->post_contribution_to_gl($id, $posted, $pid, $member_id, $amount, $trans_date);
                    if (!$gl_posted) {
                        $status['message'] = 'Transaction recorded but GL posting failed. Please check Capital Build Up Account setting.';
                        $status['success'] = 'W'; // Warning
                    } else {
                        $status['message'] = 'Successfully posted to GL';
                        $status['success'] = 'Y';
                    }
                } else if ($ifposted && $posted == 0) {
                    // Unpost from GL
                    $gl_unposted = $this->contribution_model->post_contribution_to_gl($id, $posted, $pid, $member_id, $amount, $trans_date);
                    $status['message'] = 'Successfully unposted from GL';
                    $status['success'] = 'Y';
                } else {
                    $status['success'] = 'Y';
                    $status['message'] = 'Posted successfully';
                }
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
