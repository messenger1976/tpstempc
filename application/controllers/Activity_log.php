<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Activity Log Controller
 * Manages viewing and managing activity logs
 * 
 * @author System
 */
class Activity_log extends CI_Controller {

    function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        // Only allow admin access
        if (!$this->ion_auth->is_admin()) {
            $this->session->set_flashdata('warning', 'Access denied. Admin privileges required.');
            redirect('/', 'refresh');
        }

        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->data['current_title'] = 'Activity Logs';
        $this->load->model('Activity_log_model');
        $this->load->library('ion_auth');
        $this->load->helper('activity_log');
    }

    /**
     * Display activity logs list
     */
    function index() {
        $this->data['title'] = 'Activity Logs';

        // Handle pagination
        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 50);
        }

        $config["per_page"] = $this->session->userdata('PER_PAGE');

        // Get filters
        $filters = array();
        
        if (isset($_POST['filter']) || isset($_GET['filter'])) {
            $filters['user_id'] = $this->input->post('user_id') ?: $this->input->get('user_id');
            $filters['module'] = $this->input->post('module') ?: $this->input->get('module');
            $filters['action'] = $this->input->post('action') ?: $this->input->get('action');
            $filters['date_from'] = $this->input->post('date_from') ?: $this->input->get('date_from');
            $filters['date_to'] = $this->input->post('date_to') ?: $this->input->get('date_to');
            $filters['search'] = $this->input->post('search') ?: $this->input->get('search');
        }

        // Build pagination URL
        $query_string = http_build_query(array_filter($filters));
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url(current_lang() . '/activity_log/index/?' . $query_string);
        }

        $config["base_url"] = site_url(current_lang() . '/activity_log/index/');
        $config["total_rows"] = $this->Activity_log_model->count_logs($filters);
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

        $this->load->library('pagination');
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
        $this->data['links'] = $this->pagination->create_links();

        // Get logs
        $this->data['logs'] = $this->Activity_log_model->get_logs($filters, $config["per_page"], $page);

        // Get modules list for filter
        $this->db->select('DISTINCT(module) as module');
        $this->db->from('activity_logs');
        $this->db->where('module IS NOT NULL');
        $this->db->order_by('module', 'ASC');
        $this->data['modules'] = $this->db->get()->result();

        // Get users list for filter
        $this->db->select('id, username, first_name, last_name');
        $this->db->from('users');
        $this->db->order_by('first_name', 'ASC');
        $this->data['users'] = $this->db->get()->result();

        // Get statistics
        $this->data['stats'] = $this->Activity_log_model->get_statistics('month');

        // Pass filters to view
        $this->data['filters'] = $filters;

        $this->data['content'] = 'activity_log/index';
        $this->load->view('template', $this->data);
    }

    /**
     * View single log details
     */
    function view($id) {
        $id = decode_id($id);
        $this->data['title'] = 'Activity Log Details';

        $this->db->select('activity_logs.*, users.first_name, users.last_name, users.email');
        $this->db->from('activity_logs');
        $this->db->join('users', 'users.id = activity_logs.user_id', 'left');
        $this->db->where('activity_logs.id', $id);
        $this->data['log'] = $this->db->get()->row();

        if (!$this->data['log']) {
            show_404();
        }

        $this->data['content'] = 'activity_log/view';
        $this->load->view('template', $this->data);
    }

    /**
     * Get activity statistics (AJAX)
     */
    function statistics() {
        $period = $this->input->get('period') ?: 'month';
        $stats = $this->Activity_log_model->get_statistics($period);
        
        header('Content-Type: application/json');
        echo json_encode($stats);
    }

    /**
     * Delete old logs
     */
    function delete_old() {
        if (!$this->ion_auth->is_admin()) {
            show_error('Access denied');
        }

        $days = $this->input->post('days') ?: 90;
        $result = $this->Activity_log_model->delete_old_logs($days);

        if ($result) {
            $this->session->set_flashdata('message', 'Old logs deleted successfully');
        } else {
            $this->session->set_flashdata('warning', 'Failed to delete old logs');
        }

        redirect(current_lang() . '/activity_log', 'refresh');
    }

    /**
     * Export logs to CSV
     */
    function export() {
        // Get filters
        $filters = array();
        $filters['user_id'] = $this->input->get('user_id');
        $filters['module'] = $this->input->get('module');
        $filters['action'] = $this->input->get('action');
        $filters['date_from'] = $this->input->get('date_from');
        $filters['date_to'] = $this->input->get('date_to');
        $filters['search'] = $this->input->get('search');

        // Get all logs (no pagination)
        $logs = $this->Activity_log_model->get_logs($filters);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, array('ID', 'Date/Time', 'User', 'Module', 'Action', 'Description', 'Record ID', 'Record Type', 'IP Address'));

        // CSV data
        foreach ($logs->result() as $log) {
            $user_name = trim(($log->first_name ?: '') . ' ' . ($log->last_name ?: ''));
            if (empty($user_name)) {
                $user_name = $log->username;
            }

            fputcsv($output, array(
                $log->id,
                $log->created_at,
                $user_name,
                $log->module,
                $log->action,
                $log->description,
                $log->record_id,
                $log->record_type,
                $log->ip_address
            ));
        }

        fclose($output);
        exit;
    }

}

