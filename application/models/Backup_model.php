<?php

/**
 * Description of Backup_model
 * Database backup and restore model
 */
class Backup_Model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->dbutil();
    }

    /**
     * Create database backup
     * @return string|false Path to backup file or false on failure
     */
    function create_backup() {
        // Get database configuration
        $db_config = $this->db->database;
        
        // Set backup preferences
        $prefs = array(
            'format' => 'zip',
            'filename' => 'backup_' . date('Y-m-d_H-i-s') . '.sql'
        );

        // Create backup
        $backup = $this->dbutil->backup($prefs);

        if (!$backup) {
            return FALSE;
        }

        // Create backups directory if it doesn't exist
        $backup_path = './backups/';
        if (!is_dir($backup_path)) {
            mkdir($backup_path, 0755, true);
        }

        // Generate filename
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.zip';
        $filepath = $backup_path . $filename;

        // Save backup file
        if (write_file($filepath, $backup)) {
            return $filepath;
        }

        return FALSE;
    }

    /**
     * Get list of backup files
     * @return array List of backup files with details
     */
    function get_backup_list() {
        $backup_path = './backups/';
        $backups = array();

        if (is_dir($backup_path)) {
            $files = scandir($backup_path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'zip') {
                    $filepath = $backup_path . $file;
                    $backups[] = array(
                        'filename' => $file,
                        'filepath' => $filepath,
                        'size' => filesize($filepath),
                        'date' => date('Y-m-d H:i:s', filemtime($filepath))
                    );
                }
            }
            // Sort by date descending
            usort($backups, function($a, $b) {
                return strcmp($b['date'], $a['date']);
            });
        }

        return $backups;
    }

    /**
     * Delete backup file
     * @param string $filename Name of backup file to delete
     * @return bool True on success, false on failure
     */
    function delete_backup($filename) {
        $backup_path = './backups/';
        $filepath = $backup_path . $filename;

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return FALSE;
    }

    /**
     * Download backup file
     * @param string $filename Name of backup file to download
     * @return bool True if file exists, false otherwise
     */
    function download_backup($filename) {
        $backup_path = './backups/';
        $filepath = $backup_path . $filename;

        if (file_exists($filepath)) {
            $this->load->helper('download');
            $data = file_get_contents($filepath);
            force_download($filename, $data);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Restore database from backup
     * @param string $filename Name of backup file to restore
     * @return bool True on success, false on failure
     */
    function restore_backup($filename) {
        $backup_path = './backups/';
        $filepath = $backup_path . $filename;

        if (!file_exists($filepath)) {
            return FALSE;
        }

        // Extract zip file
        $this->load->library('zip');
        $this->zip->read_file($filepath);
        
        $temp_path = './backups/temp/';
        if (!is_dir($temp_path)) {
            mkdir($temp_path, 0755, true);
        }

        $extracted = $this->zip->extract($temp_path);
        
        if (!$extracted) {
            return FALSE;
        }

        // Find SQL file
        $sql_file = null;
        $files = scandir($temp_path);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                $sql_file = $temp_path . $file;
                break;
            }
        }

        if (!$sql_file || !file_exists($sql_file)) {
            // Clean up temp directory
            $this->delete_directory($temp_path);
            return FALSE;
        }

        // Read SQL file and execute queries
        $sql_content = file_get_contents($sql_file);
        
        // Split queries by semicolon
        $queries = explode(';', $sql_content);
        
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $this->db->query($query);
            }
        }

        // Clean up temp directory
        $this->delete_directory($temp_path);

        return TRUE;
    }

    /**
     * Helper function to delete directory recursively
     * @param string $dir Directory path to delete
     */
    private function delete_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->delete_directory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

?>
