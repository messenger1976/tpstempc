<?php
/**
 * Quick Status Check
 * 
 * This page will tell you if the site is working
 * SECURITY: Delete after checking!
 */

// Turn on all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Site Status Check</h1>";
echo "<hr>";

// Test 1: Can we access index.php?
echo "<h2>1. Testing index.php</h2>";
echo "<p>Try accessing: <a href='index.php' target='_blank'>index.php</a></p>";
echo "<p>If the link above works, your site is running!</p>";
echo "<hr>";

// Test 2: Try loading CodeIgniter manually
echo "<h2>2. Testing CodeIgniter Bootstrap</h2>";

define('ENVIRONMENT', 'development');
define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
define('SYSDIR', 'system');
define('FCPATH', __DIR__ . '/');
define('SELF', 'check_status.php');
define('EXT', '.php');

// Try to load CodeIgniter
echo "Attempting to load CodeIgniter...<br>";

ob_start();
try {
    require_once BASEPATH . 'core/CodeIgniter.php';
    $output = ob_get_clean();
    echo "<span style='color: green; font-weight: bold;'>✅ CodeIgniter loaded successfully!</span><br>";
    if (!empty($output)) {
        echo "<p><strong>Output captured:</strong></p>";
        echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($output) . "</pre>";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "<span style='color: red; font-weight: bold;'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<span style='color: red; font-weight: bold;'>❌ EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
}

echo "<hr>";

// Test 3: Check if database classes can be loaded
echo "<h2>3. Testing Database Driver Loading</h2>";
try {
    require_once(BASEPATH.'database/DB_driver.php');
    require_once(BASEPATH.'database/DB_active_rec.php');
    
    // Check if extended active record exists
    if (file_exists(APPPATH.'core/MY_DB_active_record.php')) {
        require_once(APPPATH.'core/MY_DB_active_record.php');
        echo "✅ MY_DB_active_record.php loaded<br>";
        
        if (class_exists('MY_DB_active_record')) {
            echo "✅ MY_DB_active_record class exists<br>";
        } else {
            echo "❌ MY_DB_active_record class NOT found<br>";
        }
    } else {
        echo "⚠️ MY_DB_active_record.php not found<br>";
    }
    
    // Check extended driver
    if (file_exists(APPPATH.'core/MY_DB_mysqli_driver.php')) {
        require_once(APPPATH.'core/MY_DB_mysqli_driver.php');
        echo "✅ MY_DB_mysqli_driver.php loaded<br>";
        
        if (class_exists('MY_DB_mysqli_driver')) {
            echo "✅ MY_DB_mysqli_driver class exists<br>";
        } else {
            echo "❌ MY_DB_mysqli_driver class NOT found<br>";
        }
    } else {
        echo "⚠️ MY_DB_mysqli_driver.php not found<br>";
    }
    
} catch (Throwable $e) {
    echo "<span style='color: red;'>❌ Error loading database classes: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}

echo "<hr>";

// Test 4: Check file permissions
echo "<h2>4. File Permissions</h2>";
$files_to_check = array(
    'application/logs/' => 'Must be writable',
    'application/cache/' => 'Must be writable'
);

foreach ($files_to_check as $path => $note) {
    $full_path = __DIR__ . '/' . $path;
    if (is_dir($full_path)) {
        if (is_writable($full_path)) {
            echo "✅ $path is writable<br>";
        } else {
            echo "⚠️ $path is NOT writable ($note)<br>";
        }
    } else {
        echo "⚠️ $path does not exist<br>";
    }
}

echo "<hr>";

// Summary
echo "<h2>Summary</h2>";
echo "<p><strong>If you see:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Green checkmarks:</strong> Everything looks good! Try accessing your site normally.</li>";
echo "<li>❌ <strong>Red X marks:</strong> There's an error. Copy the error message and share it.</li>";
echo "<li>⚠️ <strong>Yellow warnings:</strong> Minor issues that might not prevent the site from working.</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Click the link above to test index.php</li>";
echo "<li>If index.php loads, your site is working!</li>";
echo "<li>If you see errors, copy them and share</li>";
echo "<li>Delete this file (check_status.php) after testing</li>";
echo "</ol>";

?>

