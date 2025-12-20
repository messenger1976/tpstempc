<?php
echo "<h1>🔐 Admin Status Check</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
.status { padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid; }
.success { background: #d4edda; color: #155724; border-color: #28a745; }
.error { background: #f8d7da; color: #721c24; border-color: #dc3545; }
.warning { background: #fff3cd; color: #856404; border-color: #ffc107; }
.info { background: #cce7ff; color: #004085; border-color: #007bff; }
.btn { display: inline-block; padding: 12px 20px; margin: 8px 4px; text-decoration: none; border-radius: 5px; color: white; font-weight: bold; font-size: 14px; }
.btn-primary { background: #007bff; }
.btn-danger { background: #dc3545; }
.btn-success { background: #28a745; }
.btn-warning { background: #ffc107; color: #212529; }
</style>";

// Include CI bootstrap
require_once 'index.php';

echo "<h2>1. 🔑 Login Status</h2>";
if ($this->ion_auth->logged_in()) {
    $user = $this->ion_auth->user()->row();
    echo "<div class='status success'>✓ You are logged in as: <strong>{$user->username}</strong> (ID: {$user->id})</div>";
    $logged_in = true;
} else {
    echo "<div class='status error'>❌ You are NOT logged in</div>";
    echo "<div class='status warning'><strong>SOLUTION:</strong> Please log in first</div>";
    echo "<p><a href='" . site_url('auth/login') . "' class='btn btn-primary'>🔑 Login Now</a></p>";
    $logged_in = false;
}

if ($logged_in) {
    echo "<h2>2. 👑 Admin Status</h2>";
    if ($this->ion_auth->is_admin()) {
        echo "<div class='status success'>";
        echo "<h3>✅ YOU ARE AN ADMIN USER!</h3>";
        echo "<p>You should have full access to Fiscal Year Management.</p>";
        echo "</div>";

        echo "<h2>3. 📊 Fiscal Year Access Test</h2>";
        $fiscal_url = site_url(current_lang() . '/setting/fiscal_year_list');
        echo "<p><strong>Fiscal Year URL:</strong> <a href='$fiscal_url' target='_blank'>$fiscal_url</a></p>";
        echo "<div class='status info'>";
        echo "<strong>Expected Result:</strong> Clicking the link above should open Fiscal Year Management.";
        echo "<br>If it redirects to dashboard, there might be a code issue.";
        echo "</div>";
        echo "<p><a href='$fiscal_url' class='btn btn-success' target='_blank'>🚀 Test Fiscal Year Access</a></p>";

        echo "<h2>4. 📋 Menu Visibility</h2>";
        echo "<div class='status success'>✓ Fiscal Year Management should appear in your Settings menu</div>";
        echo "<div class='status info'>";
        echo "<strong>Location:</strong> Settings → Fiscal Year Management (at the bottom of Settings submenu)";
        echo "</div>";

    } else {
        echo "<div class='status error'>";
        echo "<h3>❌ YOU ARE NOT AN ADMIN USER!</h3>";
        echo "<p>This is why you're being redirected to the dashboard.</p>";
        echo "</div>";

        echo "<div class='status warning'>";
        echo "<h3>🔑 SOLUTION: Login as Admin</h3>";
        echo "<p>Fiscal Year Management is restricted to admin users only.</p>";
        echo "<p>You need to:</p>";
        echo "<ol>";
        echo "<li><strong>Log out</strong> of your current account</li>";
        echo "<li><strong>Log back in</strong> with an admin username/password</li>";
        echo "<li>Admin accounts typically have usernames like 'admin', 'administrator', or specific admin usernames</li>";
        echo "</ol>";
        echo "</div>";

        echo "<h2>🔍 How to Find Admin Credentials</h2>";
        echo "<div class='status info'>";
        echo "<strong>Contact your system administrator for:</strong>";
        echo "<ul>";
        echo "<li>Admin username</li>";
        echo "<li>Admin password</li>";
        echo "</ul>";
        echo "<strong>Or check:</strong>";
        echo "<ul>";
        echo "<li>System documentation</li>";
        echo "<li>Installation notes</li>";
        echo "<li>Database for admin users</li>";
        echo "</ul>";
        echo "</div>";

        echo "<p><a href='" . site_url('auth/logout') . "' class='btn btn-danger'>🚪 Logout & Login as Admin</a></p>";
    }

    echo "<h2>5. 🛠️ Troubleshooting</h2>";
    echo "<div class='status info'>";
    echo "<strong>If you're sure you're admin but still redirected:</strong>";
    echo "<ol>";
    echo "<li>Clear browser cache (Ctrl+F5)</li>";
    echo "<li>Try incognito/private browsing mode</li>";
    echo "<li>Check if your session expired - try logging out and back in</li>";
    echo "<li>Run: <a href='final_fiscal_year_check.php'>final_fiscal_year_check.php</a></li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h2>6. 📞 Need Help?</h2>";
echo "<div class='status info'>";
echo "<strong>Run these diagnostics:</strong>";
echo "<ul>";
echo "<li><a href='final_fiscal_year_check.php'>Complete Fiscal Year Check</a></li>";
echo "<li><a href='diagnose_fiscal_year.php'>Basic Diagnostic</a></li>";
echo "</ul>";
echo "<strong>They will tell you exactly what's wrong and how to fix it.</strong>";
echo "</div>";

echo "<p><a href='" . site_url() . "' class='btn btn-primary'>← Back to Application</a></p>";
?>
