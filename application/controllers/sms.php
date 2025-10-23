<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sms
 *
 * @author miltone
 */
class SMS extends CI_Controller {

    //put your code here
    //put your code here
    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = 'Messaging';
        $this->load->model('sms_model');
$this->load->library('smssending');
    }

    function senderid() {
        $this->data['title'] = 'Sender ID List';
        $this->data['senderlist'] = $this->sms_model->sender_list()->result();
        $this->data['content'] = 'sms/senderlist';
        $this->load->view('template', $this->data);
    }

    function create_senderid($id = null) {

        $this->data['title'] = 'Sender ID';
        $this->data['id'] = $id;

        $this->form_validation->set_rules('sender', 'Sender ID', 'required|alpha|max_length[11]');

        if ($this->form_validation->run() == TRUE) {
            $array = array(
                'name' => trim($this->input->post('sender'))
            );

            $insert = $this->sms_model->add_sender($array, $id);
            if ($insert) {
                $this->session->set_flashdata('message', 'Sender ID saved successfully !!');
                redirect(current_lang() . '/sms/create_senderid/' . $id, 'refresh');
            } else {
                $this->data['warning'] = 'Fail to record sender ID';
            }
        }



        if (!is_null($id)) {
            $this->data['senderinfo'] = $this->sms_model->sender_list($id)->row();
        }
        $this->data['content'] = 'sms/newsender';
        $this->load->view('template', $this->data);
    }

    function group_list() {
        $this->data['title'] = 'Group List';
        $this->data['grouplist'] = $this->sms_model->group_list()->result();
        $this->data['content'] = 'sms/grouplist';
        $this->load->view('template', $this->data);
    }

    function create_group($id = null) {
        $this->data['title'] = 'Contact Group';
        $this->data['id'] = $id;
        $this->form_validation->set_rules('name', 'Group Name', 'required');

        if ($this->form_validation->run() == TRUE) {
            $array = array(
                'name' => trim($this->input->post('name'))
            );

            $insert = $this->sms_model->add_group($array, $id);
            if ($insert) {
                $this->session->set_flashdata('message', 'Group saved successfully !!');
                redirect(current_lang() . '/sms/create_group/' . $id, 'refresh');
            } else {
                $this->data['warning'] = 'Fail to record Group';
            }
        }

        if (!is_null($id)) {
            $this->data['groupinfo'] = $this->sms_model->group_list($id)->row();
        }
        $this->data['content'] = 'sms/newgroup';
        $this->load->view('template', $this->data);
    }

    function contact_list() {
        $this->load->library('pagination');
        $this->data['title'] = 'Contact List';

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
            $key = $_POST['key'];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        if (!is_null($key)) {
            $config['suffix'] = '?key=' . $key;
        }


        $config["base_url"] = site_url(current_lang() . '/sms/contact_list/');
        $config["total_rows"] = $this->sms_model->count_contact($key);
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

        $this->data['member_list'] = $this->sms_model->search_contact($key, $config["per_page"], $page);



        $this->data['content'] = 'sms/contactlist';
        $this->load->view('template', $this->data);
    }

    function newcontact($id = null) {

        $this->data['title'] = 'Add New Contact';
        $this->data['id'] = $id;

        $this->form_validation->set_rules('group', 'Contact Group', 'required');
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('mobile', 'Mobile', 'required|valid_phone');

        if ($this->form_validation->run() == TRUE) {
            $array = array(
                'group' => $this->input->post('group'),
                'name' => trim($this->input->post('name')),
                'mobile' => $this->input->post('pre_phone1') . trim($this->input->post('mobile')),
            );

            $add_contact = $this->sms_model->add_contact($array, $id);
            if ($add_contact) {
                $this->session->set_flashdata('message', 'Contact saved successfully !');
                redirect(current_lang() . '/sms/newcontact', 'refresh');
            }
        }
        if (!is_null($id)) {
            $this->data['smscontact'] = $this->db->get_where('sms_contact', array('id' => $id))->row();
        }
        $this->data['grouplist'] = $this->sms_model->group_list()->result();
        $this->data['content'] = 'sms/newcontact';
        $this->load->view('template', $this->data);
    }

       function sendSMS() {
        $this->data['title'] = 'Send SMS';
        $this->form_validation->set_rules('sender', 'Sender', 'required');
        $this->form_validation->set_rules('group', 'Group', 'required');
        $this->form_validation->set_rules('sms', 'Message', 'required');

        if ($this->form_validation->run() == TRUE) {
          $sender = $this->input->post('sender');
          $group = $this->input->post('group');
          $message = $this->input->post('sms');
          $numbers = $this->sms_model->group_number($group);
          if(count($numbers) > 0){

          $status = $this->smssending->send_sms($sender, $message, $numbers);

          $this->session->set_flashdata('message',$status);
          redirect(current_lang().'/sms/sendSMS','refresh');
          }else{
              $this->data['warning'] = 'No recipient found';
          }
        }


        $this->data['grouplist'] = $this->sms_model->group_list()->result();
        $this->data['senderlist'] = $this->sms_model->sender_list()->result();
        $this->data['content'] = 'sms/sendSMS';
        $this->load->view('template', $this->data);
    }

}
