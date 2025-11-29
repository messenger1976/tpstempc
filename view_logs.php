<?php
/**
 * View PHP Error Logs
 * 
 * This page shows PHP error logs from multiple locations
 * SECURITY: Delete this file after debugging!
 */

// Enable error display temporarily
ini_set('display_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Error Logs Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
        .log-section { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px; }
        .log-content { background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 3px; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; white-space: pre-wrap; }
        .error { color: #d00; }
        .success { color: #0a0; }
        h2 { color: #333; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .empty { color: #666; font-style: italic; }
    </style>
</head>
<body>
    <h1>PHP Error Logs Viewer</h1>
    
    <?php
    // 1. CodeIgniter Logs
    echo '<div class="log-section">';
    echo '<h2>1. CodeIgniter Application Logs</h2>';
    echo '<p><strong>Location:</strong> application/logs/</p>';
    
    $ci_log_path = __DIR__ . '/application/logs/';
    $ci_log_files = glob($ci_log_path . 'log-*.php');
    
    if (!empty($ci_log_files)) {
        // Get the most recent log file
        rsort($ci_log_files);
        $latest_log = $ci_log_files[0];
        echo '<p><strong>Latest log file:</strong> ' . basename($latest_log) . '</p>';
        
        $content = file_get_contents($latest_log);
        // Remove PHP header if present
        $content = preg_replace('/^<\?php.*?\?>\s*\n/i', '', $content);
        
        echo '<div class="log-content">' . htmlspecialchars($content) . '</div>';
    } else {
        echo '<p class="empty">No CodeIgniter log files found. Logging might be disabled.</p>';
        echo '<p>To enable: Set $config[\'log_threshold\'] = 1; in application/config/config.php</p>';
    }
    echo '</div>';
    
    // 2. XAMPP Apache Error Log
    echo '<div class="log-section">';
    echo '<h2>2. XAMPP Apache Error Log</h2>';
    echo '<p><strong>Location:</strong> C:\\xampp\\apache\\logs\\error.log</p>';
    
    $apache_log = 'C:\\xampp\\apache\\logs\\error.log';
    if (file_exists($apache_log)) {
        $lines = file($apache_log);
        $recent_lines = array_slice($lines, -50); // Last 50 lines
        echo '<div class="log-content">' . htmlspecialchars(implode('', $recent_lines)) . '</div>';
    } else {
        echo '<p class="empty">Apache error log not found at: ' . $apache_log . '</p>';
    }
    echo '</div>';
    
    // 3. XAMPP PHP Error Log
    echo '<div class="log-section">';
    echo '<h2>3. XAMPP PHP Error Log</h2>';
    echo '<p><strong>Location:</strong> C:\\xampp\\php\\logs\\php_error_log</p>';
    
    $php_log = 'C:\\xampp\\php\\logs\\php_error_log';
    if (file_exists($php_log)) {
        $lines = file($php_log);
        $recent_lines = array_slice($lines, -50); // Last 50 lines
        echo '<div class="log-content">' . htmlspecialchars(implode('', $recent_lines)) . '</div>';
    } else {
        echo '<p class="empty">PHP error log not found at: ' . $php_log . '</p>';
    }
    echo '</div>';
    
    // 4. PHP error_log() location
    echo '<div class="log-section">';
    echo '<h2>4. PHP error_log() Location</h2>';
    $php_error_log = ini_get('error_log');
    echo '<p><strong>PHP error_log setting:</strong> ' . ($php_error_log ? $php_error_log : 'Default system log') . '</p>';
    
    if ($php_error_log && file_exists($php_error_log)) {
        $lines = file($php_error_log);
        $recent_lines = array_slice($lines, -50);
        echo '<div class="log-content">' . htmlspecialchars(implode('', $recent_lines)) . '</div>';
    } else {
        echo '<p class="empty">PHP error_log file not accessible.</p>';
    }
    echo '</div>';
    
    // 5. Current PHP Error Reporting Settings
    echo '<div class="log-section">';
    echo '<h2>5. Current PHP Settings</h2>';
    echo '<div class="log-content">';
    echo 'Error Reporting Level: ' . error_reporting() . "\n";
    echo 'Display Errors: ' . ini_get('display_errors') . "\n";
    echo 'Log Errors: ' . ini_get('log_errors') . "\n";
    echo 'Error Log: ' . ini_get('error_log') . "\n";
    echo 'Environment: ' . (defined('ENVIRONMENT') ? ENVIRONMENT : 'Not defined') . "\n";
    echo '</div>';
    echo '</div>';
    ?>
    
    <hr>
    <p><strong style="color: red;">⚠️ SECURITY WARNING:</strong> Delete this file (view_logs.php) after debugging!</p>
    <p><strong>Tip:</strong> If you see "No logs found", errors might be suppressed. Enable error display in index.php temporarily.</p>
</body>
</html>

