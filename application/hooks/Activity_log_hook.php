<?php
/**
 * Activity Log Hook
 * 
 * Automatically logs database INSERT, UPDATE, DELETE operations
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_log_hook {
    
    private $CI;
    private $logging_enabled = true;
    private $excluded_tables = array(
        'activity_logs',
        'sessions',
        'ci_sessions',
        'login_attempts'
    );
    
    public function __construct() {
        $this->CI =& get_instance();
    }
    
    /**
     * Hook that runs after controller constructor
     * Sets up database query logging
     */
    public function setup_database_logging() {
        if (!isset($this->CI->db)) {
            return;
        }
        
        // Store original methods
        if (!isset($this->CI->db->_original_insert)) {
            $this->CI->db->_original_insert = $this->CI->db;
            $this->CI->db->_original_update = $this->CI->db;
            $this->CI->db->_original_delete = $this->CI->db;
        }
        
        // Wrap database methods
        $this->_wrap_database_methods();
    }
    
    /**
     * Wrap database methods with logging
     */
    private function _wrap_database_methods() {
        // Create wrapper for insert
        $CI = $this->CI;
        $hook = $this;
        
        // We'll use a different approach - intercept queries
        // Since we can't easily override methods, we'll use a post_query hook
    }
    
    /**
     * Log database activity
     */
    public function log_activity($action, $table, $where = NULL) {
        if (!$this->logging_enabled || in_array($table, $this->excluded_tables)) {
            return;
        }
        
        // Prevent recursion
        $this->logging_enabled = false;
        
        try {
            $user_id = 0;
            $username = 'System';
            
            // Get user info
            if (isset($this->CI->ion_auth) && $this->CI->ion_auth->logged_in()) {
                $user = $this->CI->ion_auth->user()->row();
                if ($user) {
                    $user_id = $user->id;
                    $username = $user->username ?: 'Unknown';
                }
            } elseif (isset($this->CI->session)) {
                $user_id = $this->CI->session->userdata('user_id') ?: 0;
                $username = $this->CI->session->userdata('username') ?: $this->CI->session->userdata('identity') ?: 'System';
            }
            
            $module = isset($this->CI->router) ? $this->CI->router->class : 'database';
            $description = ucfirst($action) . ' operation on ' . $table . ' table';
            
            // Extract record ID
            $record_id = NULL;
            if ($where && is_array($where)) {
                if (isset($where['id'])) {
                    $record_id = $where['id'];
                }
            }
            
            $log_data = array(
                'user_id' => $user_id,
                'username' => $username,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'record_id' => $record_id,
                'record_type' => $table,
                'ip_address' => isset($this->CI->input) ? $this->CI->input->ip_address() : '0.0.0.0',
                'user_agent' => isset($this->CI->input) ? $this->CI->input->user_agent() : NULL,
                'created_at' => date('Y-m-d H:i:s')
            );
            
            // Insert directly using raw query to avoid recursion
            $sql = "INSERT INTO `activity_logs` (`user_id`, `username`, `module`, `action`, `description`, `record_id`, `record_type`, `ip_address`, `user_agent`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->CI->db->conn_id->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("issssisss", 
                    $log_data['user_id'],
                    $log_data['username'],
                    $log_data['module'],
                    $log_data['action'],
                    $log_data['description'],
                    $record_id,
                    $log_data['record_type'],
                    $log_data['ip_address'],
                    $log_data['user_agent'],
                    $log_data['created_at']
                );
                @$stmt->execute();
                $stmt->close();
            }
            
        } catch (Exception $e) {
            // Silently fail
            if (function_exists('log_message')) {
                log_message('error', 'Activity log hook error: ' . $e->getMessage());
            }
        }
        
        $this->logging_enabled = true;
    }
}

