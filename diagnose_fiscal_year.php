<?php
echo "<h1>🔍 Fiscal Year Module Diagnostic</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.status { padding: 15px; margin: 10px 0; border-radius: 5px; }
.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
.info { background: #cce7ff; color: #004085; border: 1px solid #99d6ff; }
.btn { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 4px; color: white; }
.btn-primary { background: #007bff; }
.btn-success { background: #28a745; }
.btn-danger { background: #dc3545; }
</style>";

// Include CI bootstrap
require_once 'index.php';

echo "<div class='status info'><h2>📋 Step-by-Step Diagnostic</h2></div>";

echo "<h2>1. 🔐 User Authentication Check</h2>";
if ($this->ion_auth->logged_in()) {
    echo "<div class='status success'>✓ User is logged in</div>";

    $user = $this->ion_auth->user()->row();
    echo "<p><strong>User:</strong> {$user->username} (ID: {$user->id})</p>";

    if ($this->ion_auth->is_admin()) {
        echo "<div class='status success'>✓ User is ADMIN - Fiscal Year menu SHOULD be visible</div>";
    } else {
        echo "<div class='status error'>✗ User is NOT admin - Fiscal Year menu will NOT be visible</div>";
        echo "<div class='status warning'>";
        echo "<strong>SOLUTION:</strong> You must log in as an admin user to see the Fiscal Year Management menu.";
        echo "<br><strong>How to fix:</strong> Log out and log back in with an admin account.";
        echo "</div>";
        echo "<p><a href='" . site_url('auth/logout') . "' class='btn btn-danger'>Logout & Login as Admin</a></p>";
        exit;
    }
} else {
    echo "<div class='status error'>✗ User is NOT logged in</div>";
    echo "<div class='status warning'>";
    echo "<strong>SOLUTION:</strong> Please log in first.";
    echo "</div>";
    echo "<p><a href='" . site_url('auth/login') . "' class='btn btn-primary'>Login</a></p>";
    exit;
}

echo "<h2>2. 🗄️ Database Check</h2>";
if ($this->db->table_exists('fiscal_year')) {
    echo "<div class='status success'>✓ Fiscal year table exists</div>";
    $count = $this->db->count_all('fiscal_year');
    echo "<p><strong>Fiscal years in database:</strong> $count</p>";

    if ($count > 0) {
        $active = $this->db->where('status', 1)->get('fiscal_year')->row();
        if ($active) {
            echo "<div class='status success'>✓ Active fiscal year: {$active->name} ({$active->start_date} to {$active->end_date})</div>";
        } else {
            echo "<div class='status warning'>⚠ No active fiscal year set</div>";
        }
    } else {
        echo "<div class='status warning'>⚠ No fiscal years created yet</div>";
    }
} else {
    echo "<div class='status error'>✗ Fiscal year table does NOT exist</div>";
    echo "<div class='status warning'>";
    echo "<strong>SOLUTION:</strong> Database table needs to be created.";
    echo "</div>";
    echo "<p><a href='create_fiscal_year_table_now.php' class='btn btn-success'>Create Table Now</a></p>";
    exit;
}

echo "<h2>3. 📁 Code Files Check</h2>";
$checks = [
    ['file' => 'application/controllers/setting.php', 'search' => 'fiscal_year_list', 'name' => 'Controller'],
    ['file' => 'application/models/setting_model.php', 'search' => 'fiscal_year_list', 'name' => 'Model'],
    ['file' => 'application/views/setting/fiscal_year_list.php', 'search' => '', 'name' => 'List View'],
    ['file' => 'application/views/setting/fiscal_year_create.php', 'search' => '', 'name' => 'Create View'],
    ['file' => 'application/language/english/setting_lang.php', 'search' => 'fiscal_year_list', 'name' => 'Language File'],
];

$all_files_ok = true;
foreach ($checks as $check) {
    if (file_exists($check['file'])) {
        echo "<div class='status success'>✓ {$check['name']} file exists</div>";
        if (!empty($check['search'])) {
            $content = file_get_contents($check['file']);
            if (strpos($content, $check['search']) !== false) {
                echo "<div class='status success'>✓ {$check['search']} found in {$check['name']}</div>";
            } else {
                echo "<div class='status error'>✗ {$check['search']} NOT found in {$check['name']}</div>";
                $all_files_ok = false;
            }
        }
    } else {
        echo "<div class='status error'>✗ {$check['name']} file missing</div>";
        $all_files_ok = false;
    }
}

echo "<h2>4. 📋 Menu Structure Check</h2>";
$menu_file = 'application/views/menu.php';
if (file_exists($menu_file)) {
    $menu_content = file_get_contents($menu_file);
    if (strpos($menu_content, 'fiscal_year_list') !== false) {
        echo "<div class='status success'>✓ Fiscal year menu item found in menu.php</div>";
    } else {
        echo "<div class='status error'>✗ Fiscal year menu item NOT found in menu.php</div>";
        $all_files_ok = false;
    }

    if (strpos($menu_content, '$this->ion_auth->is_admin()') !== false) {
        echo "<div class='status success'>✓ Admin restriction found in menu</div>";
    } else {
        echo "<div class='status error'>✗ Admin restriction NOT found in menu</div>";
    }
} else {
    echo "<div class='status error'>✗ Menu file not found</div>";
    $all_files_ok = false;
}

echo "<h2>5. 🧪 Direct URL Test</h2>";
$test_url = site_url(current_lang() . '/setting/fiscal_year_list');
echo "<p><strong>Test URL:</strong> <a href='$test_url' target='_blank'>$test_url</a></p>";
echo "<div class='status info'>";
echo "Click the link above to test if the fiscal year page loads directly. ";
echo "If it works, the issue is with menu visibility. If it doesn't work, there's a code issue.";
echo "</div>";

echo "<h2>6. 🎯 Solutions</h2>";

if ($all_files_ok && $this->db->table_exists('fiscal_year') && $this->ion_auth->is_admin()) {
    echo "<div class='status success'>✓ All checks passed! The fiscal year module should be working.</div>";
    echo "<div class='status info'>";
    echo "<strong>If you still don't see it:</strong>";
    echo "<ol>";
    echo "<li>Clear your browser cache (Ctrl+F5)</li>";
    echo "<li>Try a different browser or incognito mode</li>";
    echo "<li>Make sure you're looking in the Settings menu (click to expand it)</li>";
    echo "<li>Check if the menu is collapsed - look for the arrow next to Settings</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='status error'>✗ Some issues found. Please address them above.</div>";
}

echo "<h2>7. 📍 Menu Location</h2>";
echo "<div class='status info'>";
echo "The Fiscal Year Management should appear in the Settings menu as:<br>";
echo "<strong>Settings → Fiscal Year Management</strong><br>";
echo "It should be listed after 'Payment Methods' and before 'Mobile Notification'.";
echo "</div>";

echo "<p><a href='" . site_url() . "' class='btn btn-primary'>← Back to Application</a></p>";
?>
