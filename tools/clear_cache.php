<?php
/**
 * Clear Cache Utility
 * 
 * This script clears PHP OPcache and CodeIgniter cache
 * Run this file once after uploading new PHP files to ensure changes are loaded
 * 
 * SECURITY: Delete this file after use or restrict access to admin users only
 */

// Disable direct access if not from browser (optional security measure)
// Uncomment the line below to require the script to be accessed via browser
// if (php_sapi_name() === 'cli' && !isset($_SERVER['HTTP_HOST'])) {
//     die('This script can only be run from a web browser.');
// }

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Clear Cache Utility</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #1c84c6;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #1c84c6;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .button:hover {
            background: #155d8b;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Cache Clear Utility</h1>
        
        <?php
        $results = array();
        
        // Check PHP OPcache
        if (function_exists('opcache_reset')) {
            $opcache_enabled = ini_get('opcache.enable');
            if ($opcache_enabled) {
                if (opcache_reset()) {
                    $results[] = array('type' => 'success', 'message' => 'PHP OPcache cleared successfully!');
                } else {
                    $results[] = array('type' => 'warning', 'message' => 'OPcache reset attempted but may have failed.');
                }
            } else {
                $results[] = array('type' => 'info', 'message' => 'OPcache is installed but not enabled in php.ini.');
            }
            
            // Get OPcache statistics
            if (function_exists('opcache_get_status')) {
                $opcache_status = opcache_get_status();
                if ($opcache_status) {
                    $results[] = array('type' => 'info', 'message' => 'OPcache Status: Enabled');
                    $results[] = array('type' => 'info', 'message' => 'Cached Scripts: ' . (isset($opcache_status['opcache_statistics']['num_cached_scripts']) ? $opcache_status['opcache_statistics']['num_cached_scripts'] : 'N/A'));
                }
            }
        } else {
            $results[] = array('type' => 'info', 'message' => 'OPcache extension is not installed on this server.');
        }
        
        // Clear CodeIgniter cache directories
        $cache_dirs = array(
            'application/cache',
            'application/logs'
        );
        
        $cleared_dirs = array();
        foreach ($cache_dirs as $dir) {
            if (file_exists($dir) && is_dir($dir)) {
                $files = glob($dir . '/*');
                $file_count = 0;
                foreach ($files as $file) {
                    if (is_file($file) && basename($file) !== 'index.html' && basename($file) !== '.htaccess') {
                        if (@unlink($file)) {
                            $file_count++;
                        }
                    }
                }
                if ($file_count > 0) {
                    $cleared_dirs[] = $dir . ' (' . $file_count . ' files)';
                }
            }
        }
        
        if (!empty($cleared_dirs)) {
            $results[] = array('type' => 'success', 'message' => 'CodeIgniter cache cleared: ' . implode(', ', $cleared_dirs));
        } else {
            $results[] = array('type' => 'info', 'message' => 'No CodeIgniter cache files found to clear.');
        }
        
        // Display results
        foreach ($results as $result) {
            echo '<div class="' . $result['type'] . '">';
            echo htmlspecialchars($result['message']);
            echo '</div>';
        }
        
        // Server information
        echo '<div class="info">';
        echo '<strong>Server Information:</strong><br>';
        echo 'PHP Version: ' . phpversion() . '<br>';
        echo 'Server Time: ' . date('Y-m-d H:i:s') . '<br>';
        echo 'Script Location: ' . __FILE__ . '<br>';
        if (function_exists('opcache_get_configuration')) {
            $config = opcache_get_configuration();
            if ($config) {
                echo 'OPcache Memory: ' . (isset($config['directives']['opcache.memory_consumption']) ? round($config['directives']['opcache.memory_consumption'] / 1024 / 1024, 2) . ' MB' : 'N/A') . '<br>';
            }
        }
        echo '</div>';
        
        // Security warning
        echo '<div class="warning">';
        echo '<strong>⚠️ Security Warning:</strong><br>';
        echo 'This file should be deleted after use to prevent unauthorized access. ';
        echo 'For production environments, add authentication or IP restrictions.';
        echo '</div>';
        ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <a href="javascript:location.reload();" class="button">🔄 Refresh / Run Again</a>
            <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>" class="button" style="background: #28a745;">🏠 Go to Home</a>
        </div>
        
        <div style="margin-top: 20px; font-size: 12px; color: #666;">
            <strong>Instructions:</strong><br>
            1. Run this script after uploading new PHP files<br>
            2. Verify the cache has been cleared<br>
            3. <strong>Delete this file</strong> for security<br>
            <br>
            <strong>Note:</strong> If you still see old code after clearing cache, try:
            <ul>
                <li>Restarting PHP-FPM or Apache service (requires server access)</li>
                <li>Checking file permissions on uploaded files</li>
                <li>Verifying the correct file was uploaded</li>
            </ul>
        </div>
    </div>
</body>
</html>

