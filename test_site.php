<?php
/**
 * Site Health Check
 * 
 * This page tests if CodeIgniter is loading correctly
 * SECURITY: Delete after testing!
 */

echo "<h1>Site Health Check</h1>";
echo "<hr>";

// Test 1: PHP is working
echo "<h2>1. PHP Test</h2>";
echo "✅ PHP Version: " . phpversion() . "<br>";
echo "✅ PHP is working!<br><br>";

// Test 2: Check if files exist
echo "<h2>2. Required Files Check</h2>";
$required_files = array(
    'index.php' => __DIR__ . '/index.php',
    'MY_DB_mysqli_driver.php' => __DIR__ . '/application/core/MY_DB_mysqli_driver.php',
    'MY_DB_active_record.php' => __DIR__ . '/application/core/MY_DB_active_record.php',
    'DB.php (modified)' => __DIR__ . '/system/database/DB.php'
);

foreach ($required_files as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name exists<br>";
    } else {
        echo "❌ $name NOT FOUND<br>";
    }
}
echo "<br>";

// Test 3: Syntax check on our files
echo "<h2>3. Syntax Check</h2>";
$files_to_check = array(
    'MY_DB_active_record.php' => __DIR__ . '/application/core/MY_DB_active_record.php',
    'MY_DB_mysqli_driver.php' => __DIR__ . '/application/core/MY_DB_mysqli_driver.php'
);

foreach ($files_to_check as $name => $path) {
    if (file_exists($path)) {
        $output = array();
        $return_var = 0;
        @exec("php -l " . escapeshellarg($path) . " 2>&1", $output, $return_var);
        if ($return_var === 0) {
            echo "✅ $name: Syntax OK<br>";
        } else {
            echo "❌ $name: Syntax Error<br>";
            echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
        }
    }
}
echo "<br>";

// Test 4: Try to load CodeIgniter
echo "<h2>4. CodeIgniter Load Test</h2>";
try {
    // Set up CodeIgniter constants
    define('ENVIRONMENT', 'development');
    define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
    define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
    define('SYSDIR', 'system');
    define('FCPATH', __DIR__ . '/');
    
    // Try loading CodeIgniter bootstrap
    if (file_exists(BASEPATH . 'core/CodeIgniter.php')) {
        echo "✅ CodeIgniter core file found<br>";
        
        // Try to require it (this might show errors)
        ob_start();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        try {
            require_once BASEPATH . 'core/CodeIgniter.php';
            echo "✅ CodeIgniter loaded successfully!<br>";
            $output = ob_get_clean();
            if ($output) {
                echo "<strong>Output:</strong><br><pre>" . htmlspecialchars($output) . "</pre>";
            }
        } catch (Exception $e) {
            ob_end_clean();
            echo "❌ Error loading CodeIgniter: " . htmlspecialchars($e->getMessage()) . "<br>";
        } catch (Error $e) {
            ob_end_clean();
            echo "❌ Fatal error loading CodeIgniter: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    } else {
        echo "❌ CodeIgniter core file not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Test 5: Check database connection
echo "<h2>5. Database Connection Test</h2>";
if (function_exists('get_instance')) {
    $CI =& get_instance();
    if (isset($CI->db)) {
        echo "✅ Database object exists<br>";
        $driver = get_class($CI->db);
        echo "Driver class: <strong>$driver</strong><br>";
        
        if ($driver == 'MY_DB_mysqli_driver') {
            echo "✅ Extended driver is loaded!<br>";
        } else {
            echo "⚠️ Default driver loaded ($driver). Extended driver might not be loading.<br>";
        }
    } else {
        echo "⚠️ Database object not available<br>";
    }
} else {
    echo "⚠️ CodeIgniter instance not available<br>";
}

// Test 6: Check activity_logs table
echo "<h2>6. Activity Logs Table Check</h2>";
if (function_exists('get_instance')) {
    $CI =& get_instance();
    if (isset($CI->db)) {
        if ($CI->db->table_exists('activity_logs')) {
            echo "✅ activity_logs table exists<br>";
            $count = $CI->db->count_all('activity_logs');
            echo "Total logs: <strong>$count</strong><br>";
        } else {
            echo "❌ activity_logs table does NOT exist<br>";
        }
    }
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>If all tests pass but site still doesn't load:</strong></p>";
echo "<ul>";
echo "<li>Check browser console (F12 → Console tab) for JavaScript errors</li>";
echo "<li>Try accessing the site: <a href='index.php'>index.php</a></li>";
echo "<li>Try accessing a specific controller: <a href='index.php/dashboard'>Dashboard</a></li>";
echo "<li>Check Apache/PHP error logs manually</li>";
echo "</ul>";

echo "<p style='color: red;'><strong>⚠️ SECURITY: Delete this file after testing!</strong></p>";

