<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Activity Log Helper
 * Provides easy-to-use functions for logging user activities
 * 
 * @author System
 */

if (!function_exists('log_activity')) {

    /**
     * Log a user activity
     * 
     * @param string $action Action type (login, logout, create, update, delete, view)
     * @param string $module Module/Controller name
     * @param string $description Description of the activity
     * @param int $record_id ID of the record being acted upon
     * @param string $record_type Type of record
     * @return bool Success status
     */
    function log_activity($action, $module = NULL, $description = NULL, $record_id = NULL, $record_type = NULL) {
        $CI = &get_instance();
        $CI->load->model('activity_log_model');

        // Get current user info
        $user_id = 0;
        $username = 'Unknown';

        if ($CI->ion_auth && $CI->ion_auth->logged_in()) {
            $user = $CI->ion_auth->user()->row();
            $user_id = $user->id;
            $username = $user->username;
        } elseif (isset($CI->session) && $CI->session->userdata('user_id')) {
            $user_id = $CI->session->userdata('user_id');
            $username = $CI->session->userdata('username') ?: $CI->session->userdata('identity');
        }

        // Auto-detect module if not provided
        if ($module === NULL) {
            $module = $CI->router->class;
        }

        // Auto-generate description if not provided
        if ($description === NULL) {
            $description = ucfirst($action) . ' ' . ($record_type ?: $module);
            if ($record_id) {
                $description .= ' (ID: ' . $record_id . ')';
            }
        }

        $data = array(
            'user_id' => $user_id,
            'username' => $username,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'record_id' => $record_id,
            'record_type' => $record_type
        );

        return $CI->Activity_log_model->log_activity($data);
    }

}

if (!function_exists('log_login')) {

    /**
     * Log user login
     * 
     * @param int $user_id User ID
     * @param string $username Username
     * @return bool Success status
     */
    function log_login($user_id, $username) {
        $CI = &get_instance();
        $CI->load->model('Activity_log_model');

        $data = array(
            'user_id' => $user_id,
            'username' => $username,
            'module' => 'auth',
            'action' => 'login',
            'description' => 'User logged in: ' . $username
        );

        return $CI->Activity_log_model->log_activity($data);
    }

}

if (!function_exists('log_logout')) {

    /**
     * Log user logout
     * 
     * @param int $user_id User ID
     * @param string $username Username
     * @return bool Success status
     */
    function log_logout($user_id, $username) {
        $CI = &get_instance();
        $CI->load->model('Activity_log_model');

        $data = array(
            'user_id' => $user_id,
            'username' => $username,
            'module' => 'auth',
            'action' => 'logout',
            'description' => 'User logged out: ' . $username
        );

        return $CI->Activity_log_model->log_activity($data);
    }

}

if (!function_exists('log_create')) {

    /**
     * Log record creation
     * 
     * @param string $module Module name
     * @param int $record_id Record ID
     * @param string $record_type Record type
     * @param string $description Optional description
     * @return bool Success status
     */
    function log_create($module, $record_id, $record_type = NULL, $description = NULL) {
        if ($description === NULL) {
            $description = 'Created new ' . ($record_type ?: $module) . ' (ID: ' . $record_id . ')';
        }
        return log_activity('create', $module, $description, $record_id, $record_type);
    }

}

if (!function_exists('log_update')) {

    /**
     * Log record update
     * 
     * @param string $module Module name
     * @param int $record_id Record ID
     * @param string $record_type Record type
     * @param string $description Optional description
     * @return bool Success status
     */
    function log_update($module, $record_id, $record_type = NULL, $description = NULL) {
        if ($description === NULL) {
            $description = 'Updated ' . ($record_type ?: $module) . ' (ID: ' . $record_id . ')';
        }
        return log_activity('update', $module, $description, $record_id, $record_type);
    }

}

if (!function_exists('log_delete')) {

    /**
     * Log record deletion
     * 
     * @param string $module Module name
     * @param int $record_id Record ID
     * @param string $record_type Record type
     * @param string $description Optional description
     * @return bool Success status
     */
    function log_delete($module, $record_id, $record_type = NULL, $description = NULL) {
        if ($description === NULL) {
            $description = 'Deleted ' . ($record_type ?: $module) . ' (ID: ' . $record_id . ')';
        }
        return log_activity('delete', $module, $description, $record_id, $record_type);
    }

}

if (!function_exists('log_view')) {

    /**
     * Log record view
     * 
     * @param string $module Module name
     * @param int $record_id Record ID
     * @param string $record_type Record type
     * @param string $description Optional description
     * @return bool Success status
     */
    function log_view($module, $record_id = NULL, $record_type = NULL, $description = NULL) {
        if ($description === NULL) {
            $description = 'Viewed ' . ($record_type ?: $module);
            if ($record_id) {
                $description .= ' (ID: ' . $record_id . ')';
            }
        }
        return log_activity('view', $module, $description, $record_id, $record_type);
    }

}

