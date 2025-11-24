<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extended MySQLi Database Driver with Automatic Activity Logging
 * 
 * This class extends the MySQLi driver to automatically log database changes
 * to the activity_logs table.
 * 
 * It automatically logs:
 * - INSERT operations as 'create'
 * - UPDATE operations as 'update'
 * - DELETE operations as 'delete'
 */
class MY_DB_mysqli_driver extends CI_DB_mysqli_driver {

    /**
     * Track if we're currently logging to prevent infinite loops
     */
    private $_logging_enabled = true;
    
    /**
     * Tables to exclude from logging (system tables)
     */
    private $_excluded_tables = array(
        'activity_logs',
        'sessions',
        'ci_sessions',
        'login_attempts'
    );

    /**
     * Override insert method to log activities
     */
    public function insert($table = '', $set = NULL) {
        // Get the actual table name before calling parent
        $actual_table = $table;
        if ($actual_table == '' && isset($this->ar_from[0])) {
            $actual_table = $this->ar_from[0];
        }
        
        $result = parent::insert($table, $set);
        
        // Check if insert was successful (result is object or TRUE)
        $success = ($result !== FALSE && $result !== NULL);
        
        if ($success && $this->_logging_enabled && $actual_table && !in_array($actual_table, $this->_excluded_tables)) {
            $this->_log_database_activity('create', $actual_table);
        }
        
        return $result;
    }

    /**
     * Override update method to log activities
     */
    public function update($table = '', $set = NULL, $where = NULL, $limit = NULL) {
        $result = parent::update($table, $set, $where, $limit);
        
        // Check if update was successful (result is object or TRUE)
        $success = ($result !== FALSE && $result !== NULL);
        
        if ($success && $this->_logging_enabled && $table && !in_array($table, $this->_excluded_tables)) {
            $this->_log_database_activity('update', $table, $where);
        }
        
        return $result;
    }

    /**
     * Override delete method to log activities
     */
    public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE) {
        // Capture record data BEFORE deletion for logging
        $deleted_records = NULL;
        if ($this->_logging_enabled && $table && !in_array($table, $this->_excluded_tables)) {
            // Temporarily disable logging to prevent recursion
            $this->_logging_enabled = false;
            
            try {
                // Build a SELECT query to fetch records before deletion
                // We'll build the query manually to avoid interfering with delete operation
                $select_sql = "SELECT * FROM `" . mysqli_real_escape_string($this->conn_id, $table) . "`";
                $where_parts = array();
                
                // Handle WHERE clause from parameter
                if (!empty($where)) {
                    if (is_array($where)) {
                        foreach ($where as $key => $val) {
                            if (is_numeric($key)) {
                                $where_parts[] = "(" . mysqli_real_escape_string($this->conn_id, $val) . ")";
                            } else {
                                $escaped_key = "`" . mysqli_real_escape_string($this->conn_id, $key) . "`";
                                if (is_array($val)) {
                                    $val_list = array();
                                    foreach ($val as $v) {
                                        $val_list[] = "'" . mysqli_real_escape_string($this->conn_id, $v) . "'";
                                    }
                                    $where_parts[] = $escaped_key . " IN (" . implode(",", $val_list) . ")";
                                } else {
                                    $where_parts[] = $escaped_key . " = '" . mysqli_real_escape_string($this->conn_id, $val) . "'";
                                }
                            }
                        }
                    } else {
                        $where_parts[] = mysqli_real_escape_string($this->conn_id, $where);
                    }
                }
                
                // Add existing WHERE conditions from query builder
                if (!empty($this->ar_where)) {
                    foreach ($this->ar_where as $where_clause) {
                        $where_parts[] = $where_clause;
                    }
                }
                
                // Build WHERE clause
                if (!empty($where_parts)) {
                    $select_sql .= " WHERE " . implode(" AND ", $where_parts);
                }
                
                // Add LIMIT
                if ($limit) {
                    $select_sql .= " LIMIT " . (int)$limit;
                }
                
                // Execute SELECT query to fetch records
                $result = @mysqli_query($this->conn_id, $select_sql);
                if ($result) {
                    $deleted_records = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                        $deleted_records[] = $row;
                    }
                    mysqli_free_result($result);
                }
            } catch (Exception $e) {
                // If fetching fails, continue without record info
                $deleted_records = NULL;
            }
            
            $this->_logging_enabled = true;
        }
        
        // Now perform the actual delete
        $result = parent::delete($table, $where, $limit, $reset_data);
        
        // Check if delete was successful (result is object or TRUE)
        $success = ($result !== FALSE && $result !== NULL);
        
        if ($success && $this->_logging_enabled && $table && !in_array($table, $this->_excluded_tables)) {
            $this->_log_database_activity('delete', $table, $where, $deleted_records);
        }
        
        return $result;
    }

    /**
     * Override query method to intercept raw SQL queries
     */
    public function query($sql, $binds = FALSE, $return_object = TRUE) {
        $result = parent::query($sql, $binds, $return_object);
        
        // Check if it's a write query (INSERT, UPDATE, DELETE)
        if ($result && $this->_logging_enabled && $this->_is_write_query($sql)) {
            $table = $this->_extract_table_from_sql($sql);
            if ($table && !in_array($table, $this->_excluded_tables)) {
                $action = $this->_extract_action_from_sql($sql);
                $this->_log_database_activity($action, $table);
            }
        }
        
        return $result;
    }

    /**
     * Log database activity to activity_logs table
     * 
     * @param string $action Action type: create, update, delete
     * @param string $table Table name
     * @param mixed $where Where clause (for updates/deletes)
     * @param array $deleted_records Array of deleted records (for delete operations)
     */
    private function _log_database_activity($action, $table, $where = NULL, $deleted_records = NULL) {
        // Prevent infinite loops by disabling logging during log insert
        $this->_logging_enabled = false;
        
        try {
            // Get current user info
            $user_id = 0;
            $username = 'System';
            
            if (class_exists('CI_Controller')) {
                $CI =& get_instance();
                
                // Try to get user from Ion Auth
                if (isset($CI->ion_auth) && $CI->ion_auth->logged_in()) {
                    $user = $CI->ion_auth->user()->row();
                    if ($user) {
                        $user_id = $user->id;
                        $username = $user->username ?: 'Unknown';
                    }
                }
                
                // Try to get from session
                if ($user_id == 0 && isset($CI->session)) {
                    $user_id = $CI->session->userdata('user_id') ?: 0;
                    $username = $CI->session->userdata('username') ?: $CI->session->userdata('identity') ?: 'System';
                }
                
                // Get module/controller name
                $module = $CI->router->class ?: 'database';
                
                // Generate description
                $description = ucfirst($action) . ' operation on ' . $table . ' table';
                
                // Try to extract record ID from where clause
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
                        // Include more details in description (up to 500 chars)
                        $description .= ' - Deleted records: ' . substr($record_info, 0, 500) . (strlen($record_info) > 500 ? '...' : '');
                    }
                } elseif ($where) {
                    // For update operations, extract ID from where clause
                    if (is_array($where)) {
                        // Extract ID from where array
                        if (isset($where['id'])) {
                            $record_id = $where['id'];
                        } elseif (isset($where[0]) && is_array($where[0])) {
                            $record_id = isset($where[0]['id']) ? $where[0]['id'] : NULL;
                        }
                    }
                }
                
                // Prepare log data
                $log_data = array(
                    'user_id' => $user_id,
                    'username' => $username,
                    'module' => $module,
                    'action' => $action,
                    'description' => $description,
                    'record_id' => $record_id,
                    'record_type' => $table,
                    'ip_address' => isset($CI->input) ? $CI->input->ip_address() : '0.0.0.0',
                    'user_agent' => isset($CI->input) ? $CI->input->user_agent() : NULL,
                    'created_at' => date('Y-m-d H:i:s')
                );
                
                // Insert log directly using raw SQL to avoid recursion
                // We use simple_query to bypass our own insert method
                $sql = "INSERT INTO `activity_logs` (`user_id`, `username`, `module`, `action`, `description`, `record_id`, `record_type`, `ip_address`, `user_agent`, `created_at`) VALUES (";
                $sql .= (int)$log_data['user_id'] . ", ";
                $sql .= "'" . mysqli_real_escape_string($this->conn_id, $log_data['username']) . "', ";
                $sql .= ($log_data['module'] ? "'" . mysqli_real_escape_string($this->conn_id, $log_data['module']) . "'" : "NULL") . ", ";
                $sql .= ($log_data['action'] ? "'" . mysqli_real_escape_string($this->conn_id, $log_data['action']) . "'" : "NULL") . ", ";
                $sql .= ($log_data['description'] ? "'" . mysqli_real_escape_string($this->conn_id, $log_data['description']) . "'" : "NULL") . ", ";
                $sql .= ($log_data['record_id'] ? (int)$log_data['record_id'] : "NULL") . ", ";
                $sql .= ($log_data['record_type'] ? "'" . mysqli_real_escape_string($this->conn_id, $log_data['record_type']) . "'" : "NULL") . ", ";
                $sql .= "'" . mysqli_real_escape_string($this->conn_id, $log_data['ip_address']) . "', ";
                $sql .= ($log_data['user_agent'] ? "'" . mysqli_real_escape_string($this->conn_id, $log_data['user_agent']) . "'" : "NULL") . ", ";
                $sql .= "'" . $log_data['created_at'] . "')";
                
                @mysqli_query($this->conn_id, $sql);
            }
        } catch (Exception $e) {
            // Silently fail to prevent breaking the main operation
            // Log to error log if available
            if (function_exists('log_message')) {
                log_message('error', 'Activity log error: ' . $e->getMessage());
            }
        }
        
        // Re-enable logging
        $this->_logging_enabled = true;
    }

    /**
     * Check if SQL query is a write operation (INSERT, UPDATE, DELETE)
     * 
     * @param string $sql SQL query
     * @return bool
     */
    private function _is_write_query($sql) {
        $sql = trim(strtoupper($sql));
        return (
            strpos($sql, 'INSERT') === 0 ||
            strpos($sql, 'UPDATE') === 0 ||
            strpos($sql, 'DELETE') === 0 ||
            strpos($sql, 'REPLACE') === 0 ||
            strpos($sql, 'TRUNCATE') === 0
        );
    }

    /**
     * Extract table name from SQL query
     * 
     * @param string $sql SQL query
     * @return string|false Table name or false
     */
    private function _extract_table_from_sql($sql) {
        $sql = trim($sql);
        
        // Pattern for INSERT INTO table
        if (preg_match('/INSERT\s+INTO\s+[`]?([a-zA-Z0-9_]+)[`]?/i', $sql, $matches)) {
            return $matches[1];
        }
        
        // Pattern for UPDATE table
        if (preg_match('/UPDATE\s+[`]?([a-zA-Z0-9_]+)[`]?/i', $sql, $matches)) {
            return $matches[1];
        }
        
        // Pattern for DELETE FROM table
        if (preg_match('/DELETE\s+FROM\s+[`]?([a-zA-Z0-9_]+)[`]?/i', $sql, $matches)) {
            return $matches[1];
        }
        
        return false;
    }

    /**
     * Extract action type from SQL query
     * 
     * @param string $sql SQL query
     * @return string Action type
     */
    private function _extract_action_from_sql($sql) {
        $sql = trim(strtoupper($sql));
        
        if (strpos($sql, 'INSERT') === 0 || strpos($sql, 'REPLACE') === 0) {
            return 'create';
        } elseif (strpos($sql, 'UPDATE') === 0) {
            return 'update';
        } elseif (strpos($sql, 'DELETE') === 0 || strpos($sql, 'TRUNCATE') === 0) {
            return 'delete';
        }
        
        return 'unknown';
    }

    /**
     * Enable/disable automatic logging
     * 
     * @param bool $enable
     */
    public function set_logging_enabled($enable = true) {
        $this->_logging_enabled = (bool) $enable;
    }

    /**
     * Add table to exclusion list
     * 
     * @param string $table Table name
     */
    public function exclude_table_from_logging($table) {
        if (!in_array($table, $this->_excluded_tables)) {
            $this->_excluded_tables[] = $table;
        }
    }

    /**
     * Remove table from exclusion list
     * 
     * @param string $table Table name
     */
    public function include_table_in_logging($table) {
        $key = array_search($table, $this->_excluded_tables);
        if ($key !== false) {
            unset($this->_excluded_tables[$key]);
            $this->_excluded_tables = array_values($this->_excluded_tables);
        }
    }
}

/* End of file MY_DB_mysqli_driver.php */
/* Location: ./application/core/MY_DB_mysqli_driver.php */

