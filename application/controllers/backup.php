<?php

/**
 * Description of Backup Controller
 * Handles database backup and restore operations
 */
class Backup extends CI_Controller {

    function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        // Only admin can access backup functionality
        if (!$this->ion_auth->is_admin()) {
            show_error('You do not have permission to access this page.', 403);
        }

        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->data['current_title'] = 'Database Backup';
        $this->load->model('Backup_model');
    }

    /**
     * Main backup page - shows list of backups
     */
    function index() {
        $this->data['title'] = 'Database Backup Management';
        $this->data['backup_list'] = $this->Backup_model->get_backup_list();
        $this->data['content'] = 'backup/backup_list';
        $this->load->view('template', $this->data);
    }

    /**
     * Create new database backup
     */
    function create_backup() {
        $backup_file = $this->Backup_model->create_backup();
        
        if ($backup_file) {
            $this->session->set_flashdata('message', 'Database backup created successfully: ' . basename($backup_file));
        } else {
            $this->session->set_flashdata('warning', 'Failed to create database backup.');
        }
        
        redirect(current_lang() . '/backup/index', 'refresh');
    }

    /**
     * Download backup file
     */
    function download($filename) {
        // Validate filename
        $filename = basename($filename);
        
        if (empty($filename)) {
            $this->session->set_flashdata('warning', 'Invalid backup file.');
            redirect(current_lang() . '/backup/index', 'refresh');
            return;
        }

        $result = $this->Backup_model->download_backup($filename);
        
        if (!$result) {
            $this->session->set_flashdata('warning', 'Backup file not found.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }
    }

    /**
     * Delete backup file
     */
    function delete($filename) {
        // Validate filename
        $filename = basename($filename);
        
        if (empty($filename)) {
            $this->session->set_flashdata('warning', 'Invalid backup file.');
            redirect(current_lang() . '/backup/index', 'refresh');
            return;
        }

        $result = $this->Backup_model->delete_backup($filename);
        
        if ($result) {
            $this->session->set_flashdata('message', 'Backup file deleted successfully.');
        } else {
            $this->session->set_flashdata('warning', 'Failed to delete backup file.');
        }
        
        redirect(current_lang() . '/backup/index', 'refresh');
    }

    /**
     * Restore database from backup
     */
    function restore($filename) {
        // Validate filename
        $filename = basename($filename);
        
        if (empty($filename)) {
            $this->session->set_flashdata('warning', 'Invalid backup file.');
            redirect(current_lang() . '/backup/index', 'refresh');
            return;
        }

        // Confirm restoration
        $this->data['title'] = 'Confirm Database Restore';
        $this->data['filename'] = $filename;
        
        if ($this->input->post('confirm') == '1') {
            $result = $this->Backup_model->restore_backup($filename);
            
            if ($result) {
                $this->session->set_flashdata('message', 'Database restored successfully from: ' . $filename);
            } else {
                $this->session->set_flashdata('warning', 'Failed to restore database from backup.');
            }
            
            redirect(current_lang() . '/backup/index', 'refresh');
        } else {
            $this->data['content'] = 'backup/backup_restore_confirm';
            $this->load->view('template', $this->data);
        }
    }
}

?>
