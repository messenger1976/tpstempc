<?php
// Simple admin check without CI framework dependencies
session_start();

echo "<h1>🔍 Simple Admin Status Check</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
.status { padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid; }
.success { background: #d4edda; color: #155724; border-color: #28a745; }
.error { background: #f8d7da; color: #721c24; border-color: #dc3545; }
.warning { background: #fff3cd; color: #856404; border-color: #ffc107; }
.info { background: #cce7ff; color: #004085; border-color: #007bff; }
.btn { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 4px; color: white; }
.btn-primary { background: #007bff; }
.btn-danger { background: #dc3545; }
.btn-success { background: #28a745; }
</style>";

echo "<h2>1. 📋 Session Check</h2>";

// Check if there's a PHP session
if (isset($_SESSION) && !empty($_SESSION)) {
    echo "<div class='status success'>✓ PHP Session exists</div>";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";

    // Show session keys (be careful with sensitive data)
    echo "<p><strong>Session keys:</strong> " . implode(', ', array_keys($_SESSION)) . "</p>";

    // Check for common CI/Ion Auth session keys
    $ci_keys = [];
    $ion_auth_keys = [];

    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'ci_') === 0) {
            $ci_keys[] = $key;
        }
        if (strpos($key, 'ion_auth') !== false) {
            $ion_auth_keys[] = $key;
        }
    }

    if (!empty($ci_keys)) {
        echo "<div class='status success'>✓ CodeIgniter session keys found: " . implode(', ', $ci_keys) . "</div>";
    } else {
        echo "<div class='status warning'>⚠ No CodeIgniter session keys found</div>";
    }

    if (!empty($ion_auth_keys)) {
        echo "<div class='status success'>✓ Ion Auth session keys found</div>";
    } else {
        echo "<div class='status error'>❌ No Ion Auth session keys found - you may not be logged in</div>";
    }

} else {
    echo "<div class='status error'>❌ No PHP session found</div>";
    echo "<div class='status warning'><strong>SOLUTION:</strong> You need to log in first</div>";
    echo "<p><a href='http://localhost/tapstemco/auth/login' class='btn btn-primary'>Login Now</a></p>";
    exit;
}

echo "<h2>2. 🍪 Cookie Check</h2>";

// Check for common CI/Ion Auth cookies
$cookies_found = [];
$important_cookies = ['ci_session', 'ion_auth', 'tapstemco_session'];

foreach ($_COOKIE as $name => $value) {
    if (in_array($name, $important_cookies) || strpos($name, 'ci_') === 0 || strpos($name, 'ion_auth') !== false) {
        $cookies_found[] = $name;
    }
}

if (!empty($cookies_found)) {
    echo "<div class='status success'>✓ Relevant cookies found: " . implode(', ', $cookies_found) . "</div>";
} else {
    echo "<div class='status warning'>⚠ No relevant cookies found</div>";
}

echo "<h2>3. 🌐 Current URL & Context</h2>";
echo "<p><strong>Current URL:</strong> " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Unknown') . "</p>";
echo "<p><strong>HTTP Host:</strong> " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'Unknown') . "</p>";
echo "<p><strong>Script:</strong> " . basename(__FILE__) . "</p>";

echo "<h2>4. 🔗 Application Links</h2>";
echo "<p><a href='http://localhost/tapstemco' class='btn btn-primary'>Go to Application</a></p>";
echo "<p><a href='http://localhost/tapstemco/auth/login' class='btn btn-primary'>Login Page</a></p>";
echo "<p><a href='http://localhost/tapstemco/auth/logout' class='btn btn-danger'>Logout</a></p>";

echo "<h2>5. 🎯 Fiscal Year Access Test</h2>";
echo "<div class='status info'>";
echo "<strong>To test fiscal year access:</strong>";
echo "<ol>";
echo "<li>Go to the application using the link above</li>";
echo "<li>Make sure you're logged in</li>";
echo "<li>Look for 'Settings' in the left menu</li>";
echo "<li>Click Settings to expand it</li>";
echo "<li>Look for 'Fiscal Year Management'</li>";
echo "<li>If you don't see it, you're not logged in as admin</li>";
echo "</ol>";
echo "</div>";

echo "<h2>6. 🔧 Troubleshooting</h2>";
echo "<div class='status warning'>";
echo "<strong>If you're being redirected:</strong>";
echo "<ol>";
echo "<li><strong>Check if you're logged in</strong> - visit the application first</li>";
echo "<li><strong>Verify you're admin</strong> - ask system admin for admin credentials</li>";
echo "<li><strong>Clear cache</strong> - Ctrl+F5 or try incognito mode</li>";
echo "<li><strong>Try direct URL</strong> - http://localhost/tapstemco/en/setting/fiscal_year_list</li>";
echo "</ol>";
echo "</div>";

echo "<div class='status info'>";
echo "<strong>Session Details:</strong><br>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
echo "</div>";
?>
