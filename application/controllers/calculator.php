<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Calculator extends CI_Controller {

    //put your code here
    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = 'Loan Calculator';
        $this->lang->load('member');
        $this->lang->load('finance');
        $this->lang->load('loan');
        $this->lang->load('setting');
        $this->lang->load('customer');
        $this->load->library('loanbase');
        $this->load->model('finance_model');
        $this->load->model('member_model');
        $this->load->model('contribution_model');
        $this->load->model('setting_model');
        $this->load->model('customer_model');
        $this->load->model('loan_model');
        $this->load->model('share_model');
    }

    function index() {
        $this->data['title'] = 'Loan Calculator';
        $this->form_validation->set_rules('base_amount', 'Base Amount', 'required|numeric');
        $this->form_validation->set_rules('install_no', 'Installment Numbers', 'required|integer');
        $this->form_validation->set_rules('product', 'Loan Product', 'required');

        if ($this->form_validation->run() == TRUE) {
            $return_data = array();
            $amount = $this->input->post('base_amount');
            $return_data['base_amount'] = $amount;
            $installment = trim($this->input->post('install_no'));
            $return_data['installment_no'] = $installment;
            $product_id = $this->input->post('product');

            $product = $this->setting_model->loanproduct($product_id)->row();
            $return_data['product'] = $product;
            $installment_amount = $this->loanbase->get_installment($product->interest_rate, $amount, $installment, $product->interest_method, $product->interval);
            $return_data['installment_amount'] = $installment_amount;
            $total_interest_amount = $this->loanbase->totalInterest($product->interest_rate, $amount, $installment, $installment_amount, $product->interest_method, $product->interval);
            $return_data['interest_amount'] = $total_interest_amount;
            $this->data['return_data'] = $return_data;
        }
        $this->data['loan_product_list'] = $this->setting_model->loanproduct()->result();
        $this->data['content'] = 'calculator/calculator';
        $this->load->view('template', $this->data);
    }

}
