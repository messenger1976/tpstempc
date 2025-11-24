<?php
/**
 * Test Database Logging
 * 
 * This file helps verify that automatic database logging is working.
 * Upload this to your root directory and access it via browser.
 * 
 * SECURITY: Delete this file after testing!
 */

// Load CodeIgniter
define('ENVIRONMENT', 'development');
define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
define('SYSDIR', 'system');

require_once BASEPATH . 'core/CodeIgniter.php';

// But wait, we need to check if the driver is loaded
// Let's create a simple test controller

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Logging Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Database Logging Test</h1>
    
    <?php
    // Check if CodeIgniter is loaded
    if (!function_exists('get_instance')) {
        echo '<div class="error">CodeIgniter is not loaded. This test needs to be run from within CodeIgniter.</div>';
        echo '<div class="info">To test logging, perform a database operation in your application and check the activity_logs table.</div>';
        exit;
    }
    
    $CI =& get_instance();
    
    // Check if database is loaded
    if (!isset($CI->db)) {
        $CI->load->database();
    }
    
    // Check what driver class is being used
    $driver_class = get_class($CI->db);
    echo '<div class="info"><strong>Current Database Driver:</strong> ' . $driver_class . '</div>';
    
    if ($driver_class == 'MY_DB_mysqli_driver') {
        echo '<div class="success">✅ Extended driver (MY_DB_mysqli_driver) is loaded! Automatic logging should work.</div>';
    } else {
        echo '<div class="error">❌ Extended driver is NOT loaded. Current driver: ' . $driver_class . '</div>';
        echo '<div class="info">The extended driver should be: MY_DB_mysqli_driver</div>';
        echo '<div class="info"><strong>Possible issues:</strong><ul>';
        echo '<li>File not uploaded: Check if application/core/MY_DB_mysqli_driver.php exists</li>';
        echo '<li>Cache issue: Run clear_cache.php to clear OPcache</li>';
        echo '<li>File permissions: Ensure file is readable</li>';
        echo '<li>Class name mismatch: Ensure class name is exactly MY_DB_mysqli_driver</li>';
        echo '</ul></div>';
    }
    
    // Test if we can check logging status
    if (method_exists($CI->db, 'set_logging_enabled')) {
        echo '<div class="success">✅ Extended driver methods are available!</div>';
        
        // Check excluded tables
        if (method_exists($CI->db, 'exclude_table_from_logging')) {
            echo '<div class="info">Extended driver methods are working correctly.</div>';
        }
    } else {
        echo '<div class="error">Extended driver methods are not available.</div>';
    }
    
    // Check activity_logs table
    $table_exists = $CI->db->table_exists('activity_logs');
    if ($table_exists) {
        echo '<div class="success">✅ activity_logs table exists</div>';
        
        // Count recent logs
        $CI->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-1 hour')));
        $recent_count = $CI->db->count_all_results('activity_logs');
        echo '<div class="info">Recent logs (last hour): ' . $recent_count . '</div>';
    } else {
        echo '<div class="error">❌ activity_logs table does not exist!</div>';
    }
    
    ?>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
        <h3>Manual Test</h3>
        <p>To manually test logging, try this in your application:</p>
        <pre>
// In any controller, after loading database:
$this->load->database();

// Test insert (should be logged automatically)
$test_data = array(
    'test_field' => 'test_value_' . time(),
    'created_at' => date('Y-m-d H:i:s')
);
$this->db->insert('your_test_table', $test_data);

// Then check activity_logs table
$this->db->order_by('id', 'DESC');
$this->db->limit(1);
$log = $this->db->get('activity_logs')->row();
        </pre>
    </div>
    
    <div class="info" style="margin-top: 20px;">
        <strong>⚠️ Security:</strong> Delete this file after testing!
    </div>
</body>
</html>

