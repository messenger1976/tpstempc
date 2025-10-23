<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of report
 *
 * @author miltone
 */
class Report extends CI_Controller {

    //put your code here
    //put your code here
    //put your code here
    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_report');
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
        $this->load->model('report_model');
    }

    function loan_report() {
        $this->data['title'] = lang('loan_report');


        $this->data['content'] = 'report/loan_report';
        $this->load->view('template', $this->data);
    }

    function member_report() {
        $this->data['title'] = lang('member_report_list');

        if ($this->input->post('member')) {
            $from = trim($this->input->post('joindate'));
            $to = trim($this->input->post('joindate1'));
            include 'report/member_list.php';
        }

        $this->data['content'] = 'report/member_report';
        $this->load->view('template', $this->data);
    }

    function contribution_report() {
        $this->data['title'] = lang('member_report_list');

        if ($this->input->post('member')) {
            $from = trim($this->input->post('joindate'));
            $to = trim($this->input->post('joindate1'));
            $grouping = trim($this->input->post('grouping'));
            include 'report/contribution_report.php';
        }

        $this->data['content'] = 'report/member_report';
        $this->load->view('template', $this->data);
    }

    function contribution_statement() {
        $this->data['title'] = lang('member_report_list');

        $this->form_validation->set_rules('member_id', 'Member ID', 'required');
        if ($this->form_validation->run() == TRUE) {
            $from = trim($this->input->post('joindate'));
            $to = trim($this->input->post('joindate1'));
            $PID = trim($this->input->post('member_id'));
            include 'report/contribution_statement.php';
        }

        $this->data['content'] = 'report/member_report';
        $this->load->view('template', $this->data);
    }

}
