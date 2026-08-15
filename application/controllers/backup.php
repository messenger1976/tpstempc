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
                $file_path = $backup_path . $file;
                if ($file != '.' && $file != '..' && is_file($file_path) && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                    $backups[] = array(
                        'filename' => $file,
                        'size' => $this->formatBytes(filesize($file_path)),
                        'date' => date('Y-m-d H:i:s', filemtime($file_path)),
                        'timestamp' => filemtime($file_path)
                    );
                }
            }
        }
        
        // Sort by timestamp descending (newest first)
        usort($backups, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        $this->data['backups'] = $backups;
        $this->data['max_upload_bytes'] = $this->upload_limit_bytes();
        $this->data['max_upload_label'] = ini_get('upload_max_filesize');
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
     * Upload a .sql backup file into the backups folder
     */
    public function upload() {
        $request_method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';
        if ($request_method !== 'POST') {
            $this->session->set_flashdata('error', 'Invalid upload request.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        // When post_max_size is exceeded, PHP empties $_POST and $_FILES.
        $content_length = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
        if ($content_length > 0 && empty($_FILES) && empty($_POST)) {
            $this->session->set_flashdata(
                'error',
                'Upload failed: file is larger than the server post limit (post_max_size=' . ini_get('post_max_size') . ').'
            );
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        if (!isset($_FILES['backup_file']) || empty($_FILES['backup_file']['name'])) {
            $this->session->set_flashdata('error', 'No backup file selected.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        $file = $_FILES['backup_file'];
        $upload_error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;

        if ($upload_error !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', $this->upload_error_message($upload_error));
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        $original_name = basename($file['name']);
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if ($extension !== 'sql') {
            $this->session->set_flashdata('error', 'Only .sql backup files are allowed.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        // Prevent directory traversal and unsafe characters in stored filename
        $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $original_name);
        if ($safe_name === '' || strtolower(pathinfo($safe_name, PATHINFO_EXTENSION)) !== 'sql') {
            $safe_name = 'uploaded_backup_' . date('Y-m-d_H-i-s') . '.sql';
        }

        if (strpos($safe_name, '..') !== false) {
            $this->session->set_flashdata('error', 'Invalid filename.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        $backup_path = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR;
        if (!is_dir($backup_path)) {
            if (!@mkdir($backup_path, 0755, true) && !is_dir($backup_path)) {
                $this->session->set_flashdata('error', 'Backup folder could not be created. Please check directory permissions.');
                redirect(current_lang() . '/backup/index', 'refresh');
            }
        }

        if (!is_writable($backup_path)) {
            $this->session->set_flashdata('error', 'Backup folder is not writable. Please check directory permissions on backups/.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        $destination = $backup_path . $safe_name;
        if (file_exists($destination)) {
            $name_only = pathinfo($safe_name, PATHINFO_FILENAME);
            $safe_name = $name_only . '_' . date('Ymd_His') . '.sql';
            $destination = $backup_path . $safe_name;
        }

        $moved = false;
        if (is_uploaded_file($file['tmp_name'])) {
            $moved = @move_uploaded_file($file['tmp_name'], $destination);
            if (!$moved) {
                // Fallback for hosts where move_uploaded_file is restricted
                $moved = @copy($file['tmp_name'], $destination);
                if ($moved) {
                    @unlink($file['tmp_name']);
                }
            }
        }

        if (!$moved) {
            log_message('error', 'Backup upload save failed for ' . $safe_name . ' to ' . $destination);
            $this->session->set_flashdata('error', 'Failed to save uploaded backup file. Please check directory permissions on backups/.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        // Basic sanity check: reject empty uploads
        if (!file_exists($destination) || filesize($destination) <= 0) {
            if (file_exists($destination)) {
                @unlink($destination);
            }
            $this->session->set_flashdata('error', 'Uploaded backup file is empty or invalid.');
            redirect(current_lang() . '/backup/index', 'refresh');
        }

        try {
            $log_data = array(
                'user_id' => current_user()->PIN,
                'action' => 'Database Backup Uploaded',
                'description' => 'Uploaded backup file: ' . $safe_name,
                'ip_address' => $this->input->ip_address(),
                'created_at' => date('Y-m-d H:i:s')
            );

            if ($this->db->table_exists('activity_log')) {
                $this->db->insert('activity_log', $log_data);
            }
        } catch (Exception $e) {
            log_message('error', 'Backup upload logging failed: ' . $e->getMessage());
        }

        $this->session->set_flashdata('message', 'Backup uploaded successfully: ' . $safe_name . '. You can restore it from Available Backups.');
        redirect(current_lang() . '/backup/index', 'refresh');
    }

    /**
     * Human-readable PHP upload error message
     */
    private function upload_error_message($error_code) {
        $max_upload = ini_get('upload_max_filesize');
        $max_post = ini_get('post_max_size');

        switch ((int) $error_code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'Upload failed: file exceeds the server limit (upload_max_filesize=' . $max_upload . ', post_max_size=' . $max_post . ').';
            case UPLOAD_ERR_PARTIAL:
                return 'Upload failed: the file was only partially uploaded. Please try again.';
            case UPLOAD_ERR_NO_FILE:
                return 'No backup file selected.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Upload failed: missing temporary folder on the server.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Upload failed: could not write file to disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload failed: a PHP extension stopped the file upload.';
            default:
                return 'Failed to upload backup file (error code ' . (int) $error_code . ').';
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

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $previous_db_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        $success = false;
        $error_detail = '';

        try {
            $mysql_path = $this->find_mysql();
            if ($mysql_path) {
                $cli_result = $this->restore_via_mysql_cli($file_path, $mysql_path);
                $success = !empty($cli_result['success']);
                if (!$success && !empty($cli_result['error'])) {
                    $error_detail = $cli_result['error'];
                }
            }

            if (!$success) {
                $php_result = $this->restore_via_php($file_path);
                $success = !empty($php_result['success']);
                if (!$success && !empty($php_result['error'])) {
                    $error_detail = $php_result['error'];
                }
            }
        } catch (Exception $e) {
            $success = false;
            $error_detail = $e->getMessage();
            log_message('error', 'Backup restore exception: ' . $e->getMessage());
        }

        // Always release any table locks left by a partial dump restore
        @$this->db->query('UNLOCK TABLES');
        @$this->db->query('SET FOREIGN_KEY_CHECKS=1');
        @$this->db->query('SET UNIQUE_CHECKS=1');
        $this->db->db_debug = $previous_db_debug;

        if ($success) {
            try {
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
            } catch (Exception $e) {
                log_message('error', 'Backup restore logging failed: ' . $e->getMessage());
            }

            $this->session->set_flashdata('message', 'Database restored successfully from: ' . $filename);
        } else {
            $message = 'Failed to restore database from: ' . $filename;
            if ($error_detail !== '') {
                $message .= ' (' . $error_detail . ')';
            }
            $this->session->set_flashdata('error', $message);
        }

        redirect(current_lang() . '/backup/index', 'refresh');
    }

    /**
     * Restore using mysql client (preferred for mysqldump files)
     */
    private function restore_via_mysql_cli($file_path, $mysql_path) {
        $db_name = $this->db->database;
        $db_user = $this->db->username;
        $db_pass = $this->db->password;
        $db_host = $this->db->hostname;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = sprintf(
                'cmd /c ""%s" --user=%s --password=%s --host=%s --default-character-set=utf8mb4 --max_allowed_packet=512M %s < "%s""',
                $mysql_path,
                $db_user,
                $db_pass,
                $db_host,
                $db_name,
                $file_path
            );
        } else {
            $command = sprintf(
                '"%s" --user=%s --password=%s --host=%s --default-character-set=utf8mb4 --max_allowed_packet=512M %s < %s 2>&1',
                $mysql_path,
                escapeshellarg($db_user),
                escapeshellarg($db_pass),
                escapeshellarg($db_host),
                escapeshellarg($db_name),
                escapeshellarg($file_path)
            );
        }

        $output = array();
        $return_var = 1;
        exec($command, $output, $return_var);

        if ($return_var === 0) {
            return array('success' => true, 'error' => '');
        }

        $error = trim(implode(' ', $output));
        if ($error === '') {
            $error = 'mysql client restore failed (exit code ' . $return_var . ')';
        }
        log_message('error', 'Backup mysql CLI restore failed: ' . $error);
        return array('success' => false, 'error' => $error);
    }

    /**
     * PHP fallback restore — skips LOCK TABLES so app queries (e.g. users) do not hit error 1100
     */
    private function restore_via_php($file_path) {
        $handle = @fopen($file_path, 'r');
        if (!$handle) {
            return array('success' => false, 'error' => 'Could not open backup file for reading');
        }

        @$this->db->query('SET FOREIGN_KEY_CHECKS=0');
        @$this->db->query('SET UNIQUE_CHECKS=0');
        @$this->db->query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
        @$this->db->query('UNLOCK TABLES');

        $statement = '';
        $line_number = 0;
        $error = '';

        try {
            while (($line = fgets($handle)) !== false) {
                $line_number++;
                $trim = trim($line);

                // Skip empty lines and pure SQL comments
                if ($trim === '' || strpos($trim, '--') === 0 || strpos($trim, '#') === 0) {
                    continue;
                }

                // Skip dump lock statements (they break CodeIgniter auth queries mid-restore)
                if (preg_match('/^(LOCK\s+TABLES|UNLOCK\s+TABLES)/i', $trim)) {
                    continue;
                }

                $statement .= $line;

                // Execute when statement terminator is reached
                if (substr(rtrim($line), -1) !== ';') {
                    continue;
                }

                $sql = trim($statement);
                $statement = '';

                if ($sql === '' || $sql === ';') {
                    continue;
                }

                if (preg_match('/^(LOCK\s+TABLES|UNLOCK\s+TABLES)/i', $sql)) {
                    continue;
                }

                // Strip trailing semicolon for CI query driver consistency
                $sql = rtrim($sql, " \t\n\r\0\x0B;");
                if ($sql === '') {
                    continue;
                }

                $result = $this->db->query($sql);
                if ($result === FALSE) {
                    $error = 'SQL error near line ' . $line_number . ': ' . $this->db->_error_message();
                    log_message('error', 'Backup PHP restore failed: ' . $error);
                    break;
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            log_message('error', 'Backup PHP restore exception: ' . $error);
        }

        fclose($handle);
        @$this->db->query('UNLOCK TABLES');
        @$this->db->query('SET FOREIGN_KEY_CHECKS=1');
        @$this->db->query('SET UNIQUE_CHECKS=1');

        if ($error !== '') {
            return array('success' => false, 'error' => $error);
        }

        return array('success' => true, 'error' => '');
    }

    /**
     * Find mysql client executable path
     */
    private function find_mysql() {
        $possible_paths = array(
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\xampp3\\mysql\\bin\\mysql.exe',
            'C:\\wamp\\bin\\mysql\\mysql5.7.14\\bin\\mysql.exe',
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            '/usr/local/mysql/bin/mysql',
            'mysql'
        );

        // Prefer sibling of mysqldump when available
        $mysqldump_path = $this->find_mysqldump();
        if ($mysqldump_path) {
            $sibling = str_ireplace('mysqldump', 'mysql', $mysqldump_path);
            if ($sibling !== $mysqldump_path) {
                array_unshift($possible_paths, $sibling);
            }
        }

        foreach ($possible_paths as $path) {
            if ($path !== 'mysql' && file_exists($path)) {
                return $path;
            }
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('where mysql', $output, $return_var);
        } else {
            exec('which mysql', $output, $return_var);
        }

        if ($return_var === 0 && !empty($output[0])) {
            return $output[0];
        }

        return false;
    }

    /**
     * Effective upload size limit in bytes (min of upload_max_filesize and post_max_size)
     */
    public function upload_limit_bytes() {
        $upload = $this->ini_size_to_bytes(ini_get('upload_max_filesize'));
        $post = $this->ini_size_to_bytes(ini_get('post_max_size'));
        if ($upload <= 0) {
            return $post;
        }
        if ($post <= 0) {
            return $upload;
        }
        return min($upload, $post);
    }

    /**
     * Convert php.ini size string (e.g. 40M) to bytes
     */
    private function ini_size_to_bytes($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        switch ($unit) {
            case 'g':
                $number *= 1024;
                // no break
            case 'm':
                $number *= 1024;
                // no break
            case 'k':
                $number *= 1024;
                break;
        }

        return (int) $number;
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
