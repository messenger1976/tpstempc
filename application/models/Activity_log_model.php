<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Activity Log Model
 * Handles all database operations for activity logging
 * 
 * @author System
 */
class Activity_log_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Log an activity
     * 
     * @param array $data Activity data
     * @return int|bool Insert ID on success, false on failure
     */
    public function log_activity($data) {
        // Set default values
        $log_data = array(
            'user_id' => isset($data['user_id']) ? $data['user_id'] : 0,
            'username' => isset($data['username']) ? $data['username'] : 'Unknown',
            'module' => isset($data['module']) ? $data['module'] : NULL,
            'action' => isset($data['action']) ? $data['action'] : NULL,
            'description' => isset($data['description']) ? $data['description'] : NULL,
            'record_id' => isset($data['record_id']) ? $data['record_id'] : NULL,
            'record_type' => isset($data['record_type']) ? $data['record_type'] : NULL,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        );

        return $this->db->insert('activity_logs', $log_data);
    }

    /**
     * Get activity logs with filters
     * 
     * @param array $filters Filter options
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return object Query result
     */
    public function get_logs($filters = array(), $limit = NULL, $offset = NULL) {
        $this->db->select('activity_logs.*, users.first_name, users.last_name, users.email');
        $this->db->from('activity_logs');
        $this->db->join('users', 'users.id = activity_logs.user_id', 'left');

        // Apply filters
        if (isset($filters['user_id']) && $filters['user_id'] != '') {
            $this->db->where('activity_logs.user_id', $filters['user_id']);
        }

        if (isset($filters['module']) && $filters['module'] != '') {
            $this->db->where('activity_logs.module', $filters['module']);
        }

        if (isset($filters['action']) && $filters['action'] != '') {
            $this->db->where('activity_logs.action', $filters['action']);
        }

        if (isset($filters['date_from']) && $filters['date_from'] != '') {
            $this->db->where('activity_logs.created_at >=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to'] != '') {
            $this->db->where('activity_logs.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (isset($filters['search']) && $filters['search'] != '') {
            $this->db->group_start();
            $this->db->like('activity_logs.description', $filters['search']);
            $this->db->or_like('activity_logs.module', $filters['search']);
            $this->db->or_like('users.first_name', $filters['search']);
            $this->db->or_like('users.last_name', $filters['search']);
            $this->db->or_like('activity_logs.username', $filters['search']);
            $this->db->group_end();
        }

        // Order by created_at descending
        $this->db->order_by('activity_logs.created_at', 'DESC');

        // Apply limit and offset
        if ($limit !== NULL) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get();
    }

    /**
     * Count total logs with filters
     * 
     * @param array $filters Filter options
     * @return int Total count
     */
    public function count_logs($filters = array()) {
        $this->db->from('activity_logs');
        $this->db->join('users', 'users.id = activity_logs.user_id', 'left');

        // Apply filters
        if (isset($filters['user_id']) && $filters['user_id'] != '') {
            $this->db->where('activity_logs.user_id', $filters['user_id']);
        }

        if (isset($filters['module']) && $filters['module'] != '') {
            $this->db->where('activity_logs.module', $filters['module']);
        }

        if (isset($filters['action']) && $filters['action'] != '') {
            $this->db->where('activity_logs.action', $filters['action']);
        }

        if (isset($filters['date_from']) && $filters['date_from'] != '') {
            $this->db->where('activity_logs.created_at >=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to'] != '') {
            $this->db->where('activity_logs.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (isset($filters['search']) && $filters['search'] != '') {
            $this->db->group_start();
            $this->db->like('activity_logs.description', $filters['search']);
            $this->db->or_like('activity_logs.module', $filters['search']);
            $this->db->or_like('users.first_name', $filters['search']);
            $this->db->or_like('users.last_name', $filters['search']);
            $this->db->or_like('activity_logs.username', $filters['search']);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    /**
     * Get activity statistics
     * 
     * @param string $period Period: today, week, month, year
     * @return array Statistics
     */
    public function get_statistics($period = 'month') {
        $date_condition = '';
        switch ($period) {
            case 'today':
                $date_condition = "DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
        }

        $stats = array();

        // Total activities
        $this->db->where($date_condition);
        $stats['total'] = $this->db->count_all_results('activity_logs');

        // Activities by action
        $this->db->select('action, COUNT(*) as count');
        $this->db->from('activity_logs');
        if ($date_condition) {
            $this->db->where($date_condition);
        }
        $this->db->group_by('action');
        $stats['by_action'] = $this->db->get()->result();

        // Activities by module
        $this->db->select('module, COUNT(*) as count');
        $this->db->from('activity_logs');
        if ($date_condition) {
            $this->db->where($date_condition);
        }
        $this->db->where('module IS NOT NULL');
        $this->db->group_by('module');
        $this->db->order_by('count', 'DESC');
        $this->db->limit(10);
        $stats['by_module'] = $this->db->get()->result();

        return $stats;
    }

    /**
     * Delete old logs
     * 
     * @param int $days Number of days to keep logs
     * @return bool Success status
     */
    public function delete_old_logs($days = 90) {
        $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime("-{$days} days")));
        return $this->db->delete('activity_logs');
    }

}

