<?php
/**
 * Test Automatic Database Logging
 * 
 * Add this to any controller's method to test if automatic logging is working
 * Or access directly if CodeIgniter is loaded
 */

// If accessed directly, we need to load CodeIgniter
if (!function_exists('get_instance')) {
    define('ENVIRONMENT', 'development');
    define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
    define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
    define('SYSDIR', 'system');
    require_once BASEPATH . 'core/CodeIgniter.php';
}

$CI =& get_instance();

echo "<h2>Database Logging Diagnostic Test</h2>";

// 1. Check what driver class is loaded
echo "<h3>1. Database Driver Check</h3>";
$driver_class = get_class($CI->db);
echo "Current Driver: <strong>" . $driver_class . "</strong><br>";

if ($driver_class == 'MY_DB_mysqli_driver') {
    echo "<span style='color:green;'>✅ Extended driver is loaded!</span><br>";
} else {
    echo "<span style='color:red;'>❌ Extended driver NOT loaded. Using: " . $driver_class . "</span><br>";
    echo "<p>This means automatic logging won't work. The extended driver needs to be loaded.</p>";
}

// 2. Check if extended methods exist
echo "<h3>2. Extended Methods Check</h3>";
if (method_exists($CI->db, 'set_logging_enabled')) {
    echo "<span style='color:green;'>✅ Extended methods are available</span><br>";
} else {
    echo "<span style='color:red;'>❌ Extended methods NOT available</span><br>";
}

// 3. Check activity_logs table
echo "<h3>3. Activity Logs Table Check</h3>";
if ($CI->db->table_exists('activity_logs')) {
    echo "<span style='color:green;'>✅ activity_logs table exists</span><br>";
    
    // Count existing logs
    $total_logs = $CI->db->count_all('activity_logs');
    echo "Total logs in database: <strong>" . $total_logs . "</strong><br>";
} else {
    echo "<span style='color:red;'>❌ activity_logs table does NOT exist</span><br>";
}

// 4. Test Insert Operation
echo "<h3>4. Test Database Operation</h3>";
echo "Creating a test log entry...<br>";

$CI->load->model('activity_log_model');
$test_log_result = $CI->activity_log_model->log_activity(array(
    'action' => 'test',
    'module' => 'test',
    'description' => 'Diagnostic test - automatic logging check'
));

if ($test_log_result) {
    echo "<span style='color:green;'>✅ Manual logging works!</span><br>";
} else {
    echo "<span style='color:red;'>❌ Manual logging failed</span><br>";
}

// 5. Test Automatic Logging (if table exists)
if ($CI->db->table_exists('activity_logs')) {
    echo "<h3>5. Testing Automatic Logging</h3>";
    
    // Get current log count
    $before_count = $CI->db->count_all('activity_logs');
    echo "Logs before test: " . $before_count . "<br>";
    
    // Try to create a test table entry (if test table exists, or use a safe query)
    // We'll just check if the driver has the logging capability
    echo "Checking if driver supports automatic logging...<br>";
    
    if (method_exists($CI->db, 'set_logging_enabled')) {
        echo "<span style='color:green;'>✅ Driver has logging methods</span><br>";
        echo "Automatic logging should work when you perform database operations.<br>";
    } else {
        echo "<span style='color:red;'>❌ Driver does not have logging methods</span><br>";
        echo "The extended driver is not being used. Check:<br>";
        echo "<ul>";
        echo "<li>File exists: application/core/MY_DB_mysqli_driver.php</li>";
        echo "<li>Class name is exactly: MY_DB_mysqli_driver</li>";
        echo "<li>System file modified: system/database/DB.php (should check for extended driver)</li>";
        echo "<li>Clear OPcache: Run clear_cache.php</li>";
        echo "</ul>";
    }
}

// 6. Check system file modification
echo "<h3>6. System File Check</h3>";
$db_file = BASEPATH . 'database/DB.php';
if (file_exists($db_file)) {
    $content = file_get_contents($db_file);
    if (strpos($content, 'MY_DB_') !== false) {
        echo "<span style='color:green;'>✅ System/database/DB.php has been modified to support extended drivers</span><br>";
    } else {
        echo "<span style='color:red;'>❌ System/database/DB.php has NOT been modified</span><br>";
        echo "The system file needs to be updated to check for extended drivers.<br>";
    }
}

// 7. Recent logs
echo "<h3>7. Recent Activity Logs</h3>";
$CI->db->order_by('id', 'DESC');
$CI->db->limit(5);
$recent_logs = $CI->db->get('activity_logs');
if ($recent_logs && $recent_logs->num_rows() > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Action</th><th>Module</th><th>Description</th><th>User</th><th>Date</th></tr>";
    foreach ($recent_logs->result() as $log) {
        echo "<tr>";
        echo "<td>" . $log->id . "</td>";
        echo "<td>" . $log->action . "</td>";
        echo "<td>" . $log->module . "</td>";
        echo "<td>" . substr($log->description, 0, 50) . "...</td>";
        echo "<td>" . $log->username . "</td>";
        echo "<td>" . $log->created_at . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No logs found in database.<br>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If extended driver is NOT loaded, check the troubleshooting guide</li>";
echo "<li>Try performing a database INSERT/UPDATE/DELETE operation</li>";
echo "<li>Check the activity_logs table to see if a log entry was created</li>";
echo "<li>If still not working, use manual logging functions from activity_log_helper.php</li>";
echo "</ol>";

echo "<p><strong>Note:</strong> Delete this file after testing for security.</p>";

