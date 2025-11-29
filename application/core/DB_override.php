<?php
/**
 * Database Loading Override
 * 
 * This file checks if extended database driver exists before loading default
 */

defined('BASEPATH') OR exit('No direct script access allowed');

// Override the DB function to check for extended drivers first
if (!function_exists('DB_override')) {
    
    // We need to intercept the driver loading
    // Check if MY_DB_mysqli_driver exists before loading default
    $extended_driver_path = APPPATH . 'core/MY_DB_mysqli_driver.php';
    if (file_exists($extended_driver_path)) {
        require_once($extended_driver_path);
    }
}

