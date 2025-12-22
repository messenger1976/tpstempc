<?php

/**
 * Database Backup Controller
 * Handles database backup and restore operations
 */
class Backup extends CI_Controller {

    function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        // Only admins or users with backup permission can access
        if (!$this->ion_auth->is_admin()) {
            $this->session->set_flashdata('message', 'You do not have permission to access this module.');
            redirect(current_lang(), 'refresh');
        }

        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->data['current_title'] = 'Database Backup';
        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->helper('download');
    }

    /**
     * Display backup management page
     */
    public function index() {
        $this->data['title'] = 'Database Backup Management';
        
        // Get list of existing backups
        $backup_path = FCPATH . 'backups/';
        
        if (!is_dir($backup_path)) {
            mkdir($backup_path, 0755, true);
        }
        
        $backups = array();
        if (is_dir($backup_path)) {
            $files = scandir($backup_path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                    $backups[] = array(
                        'filename' => $file,
                        'size' => $this->formatBytes(filesize($backup_path . $file)),
                        'date' => date('Y-m-d H:i:s', filemtime($backup_path . $file)),
                        'timestamp' => filemtime($backup_path . $file)
                    );
                }
            }
        }
        
        // Sort by timestamp descending (newest first)
        usort($backups, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        $this->data['backups'] = $backups;
        $this->data['content'] = 'backup/index';
        $this->load->view('template', $this->data);
    }

    /**
     * Create a new database backup
     */
    public function create() {
        // Get database configuration
        $db_config = $this->db;
        $db_name = $db_config->database;
        $db_user = $db_config->username;
        $db_pass = $db_config->password;
        $db_host = $db_config->hostname;

        // Create backup directory if it doesn't exist
        $backup_path = FCPATH . 'backups/';
        if (!is_dir($backup_path)) {
            mkdir($backup_path, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backup_path . $filename;

        // Try mysqldump first (best method)
        $mysqldump_path = $this->find_mysqldump();
        
        if ($mysqldump_path) {
            // Use mysqldump
            $command = sprintf(
                '"%s" --user=%s --password=%s --host=%s %s > "%s" 2>&1',
                $mysqldump_path,
                escapeshellarg($db_user),
                escapeshellarg($db_pass),
                escapeshellarg($db_host),
                escapeshellarg($db_name),
                $filepath
            );
            
            exec($command, $output, $return_var);
            
            if ($return_var === 0 && file_exists($filepath) && filesize($filepath) > 0) {
                $success = true;
            } else {
                // Fallback to PHP method
                $success = $this->create_backup_php($filepath);
            }
        } else {
            // Use PHP-based backup
            $success = $this->create_backup_php($filepath);
        }

        if ($success) {
            // Log the backup
            $log_data = array(
                'user_id' => current_user()->PIN,
                'action' => 'Database Backup Created',
                'description' => 'Backup file: ' . $filename,
                'ip_address' => $this->input->ip_address(),
                'created_at' => date('Y-m-d H:i:s')
            );
            
            // Try to log if activity_log table exists
            if ($this->db->table_exists('activity_log')) {
                $this->db->insert('activity_log', $log_data);
            }
            
            $this->session->set_flashdata('message', 'Database backup created successfully: ' . $filename);
        } else {
            $this->session->set_flashdata('error', 'Failed to create backup file. Please check directory permissions.');
        }

        redirect(current_lang() . '/backup/index', 'refresh');
    }

    /**
     * Find mysqldump executable path
     */
    private function find_mysqldump() {
        // Common paths for mysqldump
        $possible_paths = array(
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\xampp3\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp\\bin\\mysql\\mysql5.7.14\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
            'mysqldump' // Try system path
        );

        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Try to find it in system path
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('where mysqldump', $output, $return_var);
        } else {
            exec('which mysqldump', $output, $return_var);
        }

        if ($return_var === 0 && !empty($output[0])) {
            return $output[0];
        }

        return false;
    }

    /**
     * Create backup using PHP (fallback method)
     */
    private function create_backup_php($filepath) {
        try {
            $sql_content = "-- Database Backup\n";
            $sql_content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql_content .= "-- Host: " . $this->db->hostname . "\n";
            $sql_content .= "-- Database: " . $this->db->database . "\n\n";
            $sql_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sql_content .= "SET time_zone = \"+00:00\";\n\n";

            // Get all tables
            $tables = $this->db->list_tables();

            foreach ($tables as $table) {
                // Drop table statement
                $sql_content .= "\n-- Table structure for table `{$table}`\n";
                $sql_content .= "DROP TABLE IF EXISTS `{$table}`;\n";

                // Create table statement
                $create_table = $this->db->query("SHOW CREATE TABLE `{$table}`")->row_array();
                $sql_content .= $create_table['Create Table'] . ";\n\n";

                // Get table data
                $sql_content .= "-- Dumping data for table `{$table}`\n";
                $query = $this->db->get($table);
                
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $values = array();
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . $this->db->escape_str($value) . "'";
                            }
                        }
                        $sql_content .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                }
                $sql_content .= "\n";
            }

            // Write to file
            return write_file($filepath, $sql_content);
        } catch (Exception $e) {
            log_message('error', 'Backup error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Download a backup file
     */
    public function download($filename = '') {
        if (empty($filename)) {
            show_404();
        }

        $filename = urldecode($filename);
        $backup_path = FCPATH . 'backups/';
        $file_path = $backup_path . $filename;

        // Security check - prevent directory traversal
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            show_error('Invalid filename');
        }

        if (!file_exists($file_path)) {
            show_404();
        }

        // Log the download
        $log_data = array(
            'user_id' => current_user()->PIN,
            'action' => 'Database Backup Downloaded',
            'description' => 'Downloaded backup file: ' . $filename,
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s')
        );
        
        if ($this->db->table_exists('activity_log')) {
            $this->db->insert('activity_log', $log_data);
        }

        // Force download
        $data = file_get_contents($file_path);
        force_download($filename, $data);
    }

    /**
     * Delete a backup file
     */
    public function delete($filename = '') {
        if (empty($filename)) {
            $this->session->set_flashdata('error', 'No backup file specified.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        $filename = urldecode($filename);
        $backup_path = FCPATH . 'backups/';
        $file_path = $backup_path . $filename;

        // Security check - prevent directory traversal
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            show_error('Invalid filename');
        }

        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'Backup file not found.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        if (unlink($file_path)) {
            // Log the deletion
            $log_data = array(
                'user_id' => current_user()->PIN,
                'action' => 'Database Backup Deleted',
                'description' => 'Deleted backup file: ' . $filename,
                'ip_address' => $this->input->ip_address(),
                'created_at' => date('Y-m-d H:i:s')
            );
            
            if ($this->db->table_exists('activity_log')) {
                $this->db->insert('activity_log', $log_data);
            }
            
            $this->session->set_flashdata('message', 'Backup deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete backup file.');
        }

        redirect(current_lang() . '/backup/index', 'refresh');
    }

    /**
     * Restore database from backup (Advanced feature - use with caution)
     */
    public function restore($filename = '') {
        if (empty($filename)) {
            $this->session->set_flashdata('error', 'No backup file specified.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        $filename = urldecode($filename);
        $backup_path = FCPATH . 'backups/';
        $file_path = $backup_path . $filename;

        // Security check
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            show_error('Invalid filename');
        }

        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'Backup file not found.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        // Read SQL file
        $sql_content = file_get_contents($file_path);

        // Execute SQL commands
        try {
            // Split by semicolons and execute each statement
            $statements = explode(';', $sql_content);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $this->db->query($statement);
                }
            }

            // Log the restore
            $log_data = array(
                'user_id' => current_user()->PIN,
                'action' => 'Database Restored',
                'description' => 'Restored from backup file: ' . $filename,
                'ip_address' => $this->input->ip_address(),
                'created_at' => date('Y-m-d H:i:s')
            );
            
            if ($this->db->table_exists('activity_log')) {
                $this->db->insert('activity_log', $log_data);
            }

            $this->session->set_flashdata('message', 'Database restored successfully from: ' . $filename);
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Failed to restore database: ' . $e->getMessage());
        }

        redirect(current_lang() . '/backup/index', 'refresh');
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Auto backup - can be called via cron job
     */
    public function auto_backup() {
        // This can be called via cron for automatic backups
        $backup_path = FCPATH . 'backups/';
        
        if (!is_dir($backup_path)) {
            mkdir($backup_path, 0755, true);
        }

        $filename = 'auto_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backup_path . $filename;

        // Use the same backup method as create()
        $mysqldump_path = $this->find_mysqldump();
        
        if ($mysqldump_path) {
            $db_config = $this->db;
            $command = sprintf(
                '"%s" --user=%s --password=%s --host=%s %s > "%s" 2>&1',
                $mysqldump_path,
                escapeshellarg($db_config->username),
                escapeshellarg($db_config->password),
                escapeshellarg($db_config->hostname),
                escapeshellarg($db_config->database),
                $filepath
            );
            
            exec($command, $output, $return_var);
            $success = ($return_var === 0 && file_exists($filepath) && filesize($filepath) > 0);
        } else {
            $success = $this->create_backup_php($filepath);
        }
        
        if ($success) {
            echo "Backup created successfully: " . $filename;
            
            // Optional: Delete old backups (keep only last 30 days)
            $this->cleanup_old_backups(30);
        } else {
            echo "Failed to create backup";
        }
    }

    /**
     * Clean up old backup files
     */
    private function cleanup_old_backups($days = 30) {
        $backup_path = FCPATH . 'backups/';
        
        if (is_dir($backup_path)) {
            $files = scandir($backup_path);
            $now = time();
            
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                    $file_path = $backup_path . $file;
                    $file_age = $now - filemtime($file_path);
                    
                    // Delete if older than specified days
                    if ($file_age > ($days * 24 * 60 * 60)) {
                        unlink($file_path);
                    }
                }
            }
        }
    }
}
