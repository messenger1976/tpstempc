<?php

/**
 * Field collector map — GPS directions to member addresses for loan collection.
 */
class Collector_map extends CI_Controller {

    function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        $this->data['current_title'] = 'Collector Map';
        $this->load->model('member_model');
        $this->load->model('report_model');
    }

    function index() {
        $filter = $this->input->get('filter');
        $overdue_only = ($filter !== 'all');

        $this->data['title'] = 'Collector Map';
        $this->data['filter_mode'] = $overdue_only ? 'overdue' : 'all';
        $this->data['collector_targets'] = $this->member_model->get_collector_map_targets($overdue_only);
        $this->data['office_map_location'] = $this->member_model->get_office_map_location();
        $this->data['map_stats'] = $this->member_model->get_member_map_stats();

        $this->load->view('collector_map/index', $this->data);
    }
}
