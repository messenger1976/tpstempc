<?php
/**
 * Extended Active Record with Automatic Activity Logging
 * 
 * This extends the Active Record class to log all database operations
 * 
 * Note: DB_active_rec.php will be loaded by DB.php before this file
 */

defined('BASEPATH') OR exit('No direct script access allowed');

// Don't require here - it's loaded by DB.php first
// require_once(BASEPATH.'database/DB_active_rec.php');

class MY_DB_active_record extends CI_DB_active_record {
    
    private $_logging_enabled = true;
    private $_excluded_tables = array(
        'activity_logs',
        'sessions',
        'ci_sessions',
        'login_attempts'
    );
    
    /**
     * Override insert
     */
    public function insert($table = '', $set = NULL) {
        $actual_table = $table;
        if ($actual_table == '' && isset($this->ar_from[0])) {
            $actual_table = $this->ar_from[0];
        }
        
        $result = parent::insert($table, $set);
        
        if ($result !== FALSE && $this->_logging_enabled && $actual_table && !in_array($actual_table, $this->_excluded_tables)) {
            $this->_log_database_activity('create', $actual_table);
        }
        
        return $result;
    }
    
    /**
     * Override update
     */
    public function update($table = '', $set = NULL, $where = NULL, $limit = NULL) {
        $result = parent::update($table, $set, $where, $limit);
        
        if ($result !== FALSE && $this->_logging_enabled && $table && !in_array($table, $this->_excluded_tables)) {
            $this->_log_database_activity('update', $table, $where);
        }
        
        return $result;
    }
    
    /**
     * Override delete
     */
    public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE) {
        // Capture record data BEFORE deletion for logging
        $deleted_records = NULL;
        if ($this->_logging_enabled && $table && !in_array($table, $this->_excluded_tables)) {
            // Temporarily disable logging to prevent recursion
            $this->_logging_enabled = false;
            
            try {
                // Create a separate query using raw SQL to fetch records before deletion
                // This avoids interfering with the delete query builder state
                if (!isset($this->conn_id) || !$this->conn_id) {
                    $deleted_records = NULL;
                } else {
                    $select_sql = "SELECT * FROM `" . mysqli_real_escape_string($this->conn_id, $table) . "`";
                    $where_sql = "";
                    
                    if (!empty($where)) {
                        $where_conditions = array();
                        if (is_array($where)) {
                            foreach ($where as $key => $val) {
                                if (is_numeric($key)) {
                                    $where_conditions[] = "(" . mysqli_real_escape_string($this->conn_id, $val) . ")";
                                } else {
                                    $where_conditions[] = "`" . mysqli_real_escape_string($this->conn_id, $key) . "` = '" . mysqli_real_escape_string($this->conn_id, $val) . "'";
                                }
                            }
                        } else {
                            $where_conditions[] = mysqli_real_escape_string($this->conn_id, $where);
                        }
                        if (!empty($where_conditions)) {
                            $where_sql = " WHERE " . implode(" AND ", $where_conditions);
                        }
                    }
                    
                    if ($limit) {
                        $select_sql .= $where_sql . " LIMIT " . (int)$limit;
                    } else {
                        $select_sql .= $where_sql;
                    }
                    
                    // Execute query to fetch records
                    $result = @mysqli_query($this->conn_id, $select_sql);
                    if ($result) {
                        $deleted_records = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $deleted_records[] = $row;
                        }
                        mysqli_free_result($result);
                    }
                }
            } catch (Exception $e) {
                // If fetching fails, continue without record info
                $deleted_records = NULL;
            }
            
            $this->_logging_enabled = true;
        }
        
        // Now perform the actual delete
        $result = parent::delete($table, $where, $limit, $reset_data);
        
        if ($result !== FALSE && $this->_logging_enabled && $table && !in_array($table, $this->_excluded_tables)) {
            $this->_log_database_activity('delete', $table, $where, $deleted_records);
        }
        
        return $result;
    }
    
    /**
     * Log database activity
     */
    private function _log_database_activity($action, $table, $where = NULL, $deleted_records = NULL) {
        $this->_logging_enabled = false;
        
        try {
            $user_id = 0;
            $username = 'System';
            
            if (class_exists('CI_Controller')) {
                $CI =& get_instance();
                
                if (isset($CI->ion_auth) && $CI->ion_auth->logged_in()) {
                    $user = $CI->ion_auth->user()->row();
                    if ($user) {
                        $user_id = $user->id;
                        $username = $user->username ?: 'Unknown';
                    }
                } elseif (isset($CI->session)) {
                    $user_id = $CI->session->userdata('user_id') ?: 0;
                    $username = $CI->session->userdata('username') ?: $CI->session->userdata('identity') ?: 'System';
                }
                
                $module = isset($CI->router) ? $CI->router->class : 'database';
                $description = ucfirst($action) . ' operation on ' . $table . ' table';
                
                $record_id = NULL;
                $record_info = '';
                
                if ($action === 'delete' && !empty($deleted_records)) {
                    // Include deleted record information
                    $record_count = count($deleted_records);
                    $description .= ' (' . $record_count . ' record' . ($record_count > 1 ? 's' : '') . ' deleted)';
                    
                    // Build detailed info about deleted records
                    $record_details = array();
                    foreach ($deleted_records as $record) {
                        $record_detail = array();
                        // Include key fields (id, name, title, etc.) if available
                        if (isset($record['id'])) {
                            $record_detail['id'] = $record['id'];
                            $record_id = $record['id']; // Use first record's ID
                        }
                        if (isset($record['name'])) {
                            $record_detail['name'] = $record['name'];
                        }
                        if (isset($record['title'])) {
                            $record_detail['title'] = $record['title'];
                        }
                        if (isset($record['firstname']) || isset($record['lastname'])) {
                            $record_detail['name'] = trim((isset($record['firstname']) ? $record['firstname'] : '') . ' ' . (isset($record['lastname']) ? $record['lastname'] : ''));
                        }
                        if (isset($record['email'])) {
                            $record_detail['email'] = $record['email'];
                        }
                        if (isset($record['username'])) {
                            $record_detail['username'] = $record['username'];
                        }
                        if (isset($record['member_id'])) {
                            $record_detail['member_id'] = $record['member_id'];
                        }
                        
                        // If we have some detail, add it; otherwise include all non-sensitive fields
                        if (!empty($record_detail)) {
                            $record_details[] = $record_detail;
                        } else {
                            // Include first few fields as sample (limit to avoid huge logs)
                            $sample = array_slice($record, 0, 5);
                            $record_details[] = $sample;
                        }
                    }
                    
                    if (!empty($record_details)) {
                        $record_info = json_encode($record_details);
                        $description .= ' - Deleted records: ' . substr($record_info, 0, 500) . (strlen($record_info) > 500 ? '...' : '');
                    }
                } elseif ($where && is_array($where) && isset($where['id'])) {
                    $record_id = $where['id'];
                }
                
                // Insert using raw SQL to avoid recursion
                // Check if conn_id exists (it should exist in driver instances)
                if (!isset($this->conn_id) || !$this->conn_id) {
                    // Can't log without database connection
                    return;
                }
                
                $sql = "INSERT INTO `activity_logs` (`user_id`, `username`, `module`, `action`, `description`, `record_id`, `record_type`, `ip_address`, `user_agent`, `created_at`) VALUES (";
                $sql .= (int)$user_id . ", ";
                $sql .= "'" . mysqli_real_escape_string($this->conn_id, $username) . "', ";
                $sql .= "'" . mysqli_real_escape_string($this->conn_id, $module) . "', ";
                $sql .= "'" . mysqli_real_escape_string($this->conn_id, $action) . "', ";
                $sql .= "'" . mysqli_real_escape_string($this->conn_id, $description) . "', ";
                $sql .= ($record_id ? (int)$record_id : "NULL") . ", ";
                $sql .= "'" . mysqli_real_escape_string($this->conn_id, $table) . "', ";
                $sql .= "'" . mysqli_real_escape_string($this->conn_id, isset($CI->input) ? $CI->input->ip_address() : '0.0.0.0') . "', ";
                $sql .= "'" . mysqli_real_escape_string($this->conn_id, isset($CI->input) ? $CI->input->user_agent() : '') . "', ";
                $sql .= "'" . date('Y-m-d H:i:s') . "')";
                
                @mysqli_query($this->conn_id, $sql);
            }
        } catch (Exception $e) {
            // Silently fail
        }
        
        $this->_logging_enabled = true;
    }
}

