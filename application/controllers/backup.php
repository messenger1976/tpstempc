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
            // Tables are dumped in list_tables() order (alphabetical), so a child table can be
            // created before its parent. Without these guards this file cannot be restored
            // anywhere without a #1215 foreign key error - not even back into this app.
            $sql_content .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $sql_content .= "SET UNIQUE_CHECKS=0;\n\n";

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

            $sql_content .= "SET FOREIGN_KEY_CHECKS=1;\n";
            $sql_content .= "SET UNIQUE_CHECKS=1;\n";

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

        // A restore replaces every table, but the dump creates them in alphabetical order: when
        // `beneficiaries` is created the OLD `members` table is still in place, and InnoDB only
        // accepts that foreign key if members.PID carries a unique index. FOREIGN_KEY_CHECKS=0
        // does not help there (verified on MariaDB 10.4: an unindexed parent is rejected with
        // errno 150, an absent parent is accepted).
        $prepared = $this->ensure_members_pid_key();
        if (empty($prepared['ok'])) {
            $this->db->db_debug = $previous_db_debug;
            $this->session->set_flashdata('error', 'Restore aborted: ' . $prepared['error']);
            redirect(current_lang() . '/backup/index', 'refresh');
        }

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

            // members now comes from the dump; without the key the member child tables just
            // restored would carry a foreign key that cannot be satisfied.
            $previous_debug = $this->db->db_debug;
            $this->db->db_debug = FALSE;
            $verified = $this->ensure_members_pid_key();
            $this->db->db_debug = $previous_debug;

            $message = 'Database restored successfully from: ' . $filename;
            if (!empty($verified['changed'])) {
                $message .= ' (added the missing unique key `uq_members_pid` on members.PID)';
            }
            if (empty($verified['ok'])) {
                $message .= ' Warning: ' . $verified['error'];
                log_message('error', 'Backup restore: ' . $verified['error']);
            }

            $this->session->set_flashdata('message', $message);
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
     *
     * The dump is fed to the client through a generated wrapper script that turns
     * FK/unique checking off first (see build_restore_script()), so a restore no
     * longer depends on the dump carrying its own SET FOREIGN_KEY_CHECKS=0 header.
     */
    private function restore_via_mysql_cli($file_path, $mysql_path) {
        $db_name = $this->db->database;
        $db_user = $this->db->username;
        $db_pass = $this->db->password;
        $db_host = $this->db->hostname;

        $script_path = $this->build_restore_script($file_path);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = sprintf(
                'cmd /c ""%s" --user=%s --password=%s --host=%s --default-character-set=utf8mb4 --max_allowed_packet=512M %s < "%s""',
                $mysql_path,
                $db_user,
                $db_pass,
                $db_host,
                $db_name,
                $script_path
            );
        } else {
            $command = sprintf(
                '"%s" --user=%s --password=%s --host=%s --default-character-set=utf8mb4 --max_allowed_packet=512M %s < %s 2>&1',
                $mysql_path,
                escapeshellarg($db_user),
                escapeshellarg($db_pass),
                escapeshellarg($db_host),
                escapeshellarg($db_name),
                escapeshellarg($script_path)
            );
        }

        $output = array();
        $return_var = 1;
        exec($command, $output, $return_var);

        if ($script_path !== $file_path) {
            @unlink($script_path);
        }

        if ($return_var === 0) {
            return array('success' => true, 'error' => '');
        }

        $error = trim(implode(' ', $output));
        if ($error === '') {
            $error = 'mysql client restore failed (exit code ' . $return_var . ')';
        }
        $error .= $this->restore_error_hint($error);
        log_message('error', 'Backup mysql CLI restore failed: ' . $error);
        return array('success' => false, 'error' => $error);
    }

    /**
     * Prefix a dump with restore guards and return the path of the temp script.
     *
     * Dumps are written in alphabetical table order, so a child table is created
     * before the parent it references (`beneficiaries` before `members`,
     * `member_trainings` before `members`) and InnoDB can only accept that FK while
     * foreign key checking is off. With checks on, the import aborts at the child
     * table with "#1215 - Cannot add foreign key constraint", and the real reason
     * ("Referenced table ... not found in the data dictionary") only shows up in
     * SHOW WARNINGS / SHOW ENGINE INNODB STATUS. mysqldump writes the guard header
     * into its own dumps; a phpMyAdmin or live-host export usually does not, which
     * is why such a dump fails where an app-generated backup restores fine.
     *
     * The backup file itself is never modified: it is streamed into a temp script
     * outside the web root (tempnam() also creates it owner-only). The original
     * path is returned when the temp script cannot be created, so the restore then
     * behaves exactly as it did before.
     */
    private function build_restore_script($file_path) {
        $tmp_path = @tempnam(sys_get_temp_dir(), 'tapstemco_restore_');
        if ($tmp_path === false) {
            log_message('error', 'Backup restore: could not create a temp script; restoring the dump as-is.');
            return $file_path;
        }

        $source = @fopen($file_path, 'rb');
        $target = @fopen($tmp_path, 'wb');

        if (!$source || !$target) {
            if ($source) {
                fclose($source);
            }
            if ($target) {
                fclose($target);
            }
            @unlink($tmp_path);
            log_message('error', 'Backup restore: could not open files for the temp script; restoring the dump as-is.');
            return $file_path;
        }

        // Same session guards mysqldump writes into its own dumps. Private @vars (not
        // @OLD_*) so the dump's own header/footer keeps working on its own variables.
        $header  = "-- TAPSTEMCO restore script for " . basename($file_path) . "\n";
        $header .= "-- Generated " . date('Y-m-d H:i:s') . "\n";
        $header .= "-- FK checking is off: a dump creates child tables before their parent.\n";
        $header .= "SET @TAPSTEMCO_OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;\n";
        $header .= "SET @TAPSTEMCO_OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;\n";
        $header .= "SET @TAPSTEMCO_OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        fwrite($target, $header);

        while (!feof($source)) {
            $chunk = fread($source, 1048576);
            if ($chunk === false) {
                break;
            }
            fwrite($target, $chunk);
        }

        // The client session dies with its process, so this footer only matters when
        // the script is run by hand. The leading newline keeps it off the dump's last line.
        fwrite($target, "\nSET FOREIGN_KEY_CHECKS=@TAPSTEMCO_OLD_FOREIGN_KEY_CHECKS;\n");
        fwrite($target, "SET UNIQUE_CHECKS=@TAPSTEMCO_OLD_UNIQUE_CHECKS;\n");

        fclose($source);
        fclose($target);

        return $tmp_path;
    }

    /**
     * Guarantee that `members`.`PID` is uniquely indexed - before and after a restore.
     *
     * `members` is the only FK parent in this schema whose key is not a primary key, and the
     * member child tables (`beneficiaries`, `member_trainings`, ...) reference `members.PID`.
     * Dumps are alphabetical, so while the dump builds `beneficiaries` the database still holds
     * the OLD `members` table, and InnoDB accepts that foreign key only when the referenced
     * column has a unique index - with FOREIGN_KEY_CHECKS off or on alike. So: make the key
     * exist first, then confirm the restored table still carries it.
     *
     * Returns array('ok' => bool, 'changed' => bool, 'error' => string). Callers run it with
     * db_debug off: it reports instead of throwing, and it never guesses - duplicate PIDs make
     * the key impossible, and that is surfaced as a hard failure rather than silently skipped.
     */
    private function ensure_members_pid_key() {
        if (!$this->db->table_exists('members') || !$this->db->field_exists('PID', 'members')) {
            // The dump defines `members` itself, or this install has no PID column to key.
            return array('ok' => TRUE, 'changed' => FALSE, 'error' => '');
        }

        if ($this->has_unique_pid_index()) {
            return array('ok' => TRUE, 'changed' => FALSE, 'error' => '');
        }

        $result = $this->db->query(
            'SELECT PID, COUNT(*) AS copies FROM members GROUP BY PID HAVING copies > 1 LIMIT 5'
        );
        $duplicates = $result ? $result->result_array() : array();

        if (!empty($duplicates)) {
            $sample = array();
            foreach ($duplicates as $row) {
                $sample[] = $row['PID'] . ' (x' . $row['copies'] . ')';
            }

            return array(
                'ok' => FALSE,
                'changed' => FALSE,
                'error' => 'members.PID holds duplicate values (' . implode(', ', $sample) . ') and the unique key '
                    . '`uq_members_pid` the member child tables need cannot be created until those members are '
                    . 'merged or renumbered (see sql/membership_documents_module.sql). Restoring now would fail '
                    . 'at the `beneficiaries` table.'
            );
        }

        $this->db->query('ALTER TABLE `members` ADD UNIQUE KEY `uq_members_pid` (`PID`)');

        // Re-read the dictionary instead of trusting the ALTER: db_debug is off here and the
        // app's DB layer logs writes on the same connection.
        if (!$this->has_unique_pid_index()) {
            return array(
                'ok' => FALSE,
                'changed' => FALSE,
                'error' => 'the unique key `uq_members_pid` on members.PID could not be created.'
            );
        }

        log_message('info', 'Backup restore: added UNIQUE KEY uq_members_pid on members.PID (the member child tables reference it).');

        return array('ok' => TRUE, 'changed' => TRUE, 'error' => '');
    }

    /**
     * Is there a unique index whose first column is members.PID?
     */
    private function has_unique_pid_index() {
        $result = $this->db->query(
            "SELECT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members'
               AND NON_UNIQUE = 0 AND SEQ_IN_INDEX = 1 AND COLUMN_NAME = 'PID'
             LIMIT 1"
        );

        return ($result && $result->num_rows() > 0);
    }

    /**
     * Plain-language hint appended to a failed restore message.
     */
    private function restore_error_hint($error) {
        $error_lower = strtolower($error);

        // Matched on the server wording, never on a bare "1215" - the PHP fallback prefixes the
        // message with the dump's line number, which can also be 1215.
        if (strpos($error_lower, 'errno: 150') !== false
            || strpos($error_lower, 'foreign key') !== false) {
            return ' Hint: a 1215/errno 150 on a child table has two causes. (1) With foreign key checks off'
                . ' - which the restore path sets - it means the referenced key is missing in the database being'
                . ' restored: while the dump builds `beneficiaries` the old `members` table is still in place,'
                . ' and InnoDB only accepts that FK when members.PID has a unique index. The restore adds'
                . ' `uq_members_pid` for you; seeing this anyway means it could not, normally because'
                . ' members.PID holds duplicate values. (2) With foreign key checks on, it is import order -'
                . ' a phpMyAdmin import needs `SET FOREIGN_KEY_CHECKS=0;` as the first line of the .sql.';
        }

        return '';
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
                    // The statement head makes a bare "1215" actionable without re-reading the dump.
                    $statement_head = trim(preg_replace('/\s+/', ' ', $sql));
                    if (strlen($statement_head) > 160) {
                        $statement_head = substr($statement_head, 0, 157) . '...';
                    }

                    $error = 'SQL error near line ' . $line_number . ': ' . $this->db->_error_message()
                        . ' [statement: ' . $statement_head . ']';
                    $error .= $this->restore_error_hint($error);
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
