<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payment_method_config extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('payment_method_config_model');
        $this->load->model('finance_model');
        
        if (!$this->ion_auth->logged_in()) {
            redirect('login', 'refresh');
        }
    }

    /**
     * List and configure payment method GL account mappings
     */
    public function index() {
        $this->data['title'] = 'Payment Method Configuration';
        
        // Get all payment methods for current user from the existing table
        $this->data['payment_methods'] = $this->payment_method_config_model->get_all_payment_methods();
        
        // Get all account chart for dropdown
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        
        $this->data['content'] = 'payment_method_config/index';
        $this->load->view('template', $this->data);
    }

    /**
     * Update payment method GL account mapping
     */
    public function save() {
        $payment_method_id = $this->input->post('payment_method_id');
        $gl_account_code = $this->input->post('gl_account_code');
        
        if (empty($payment_method_id) || empty($gl_account_code)) {
            $this->session->set_flashdata('warning', 'Please select both payment method and GL account');
            redirect('payment_method_config', 'refresh');
        }
        
        $result = $this->payment_method_config_model->update_gl_account($payment_method_id, $gl_account_code);
        
        if ($result) {
            $this->session->set_flashdata('message', 'GL account mapping updated successfully');
        } else {
            $this->session->set_flashdata('warning', 'Failed to update GL account mapping');
        }
        
        redirect('payment_method_config', 'refresh');
    }

    /**
     * Clear GL account mapping
     */
    public function clear_account($payment_method_id) {
        $result = $this->payment_method_config_model->update_gl_account($payment_method_id, NULL);
        
        if ($result) {
            $this->session->set_flashdata('message', 'GL account mapping cleared');
        } else {
            $this->session->set_flashdata('warning', 'Failed to clear GL account mapping');
        }
        
        redirect('payment_method_config', 'refresh');
    }
}
?>
