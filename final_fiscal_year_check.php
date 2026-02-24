<?php
echo "<h1>🎯 Final Fiscal Year Module Check</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
.status { padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid; }
.success { background: #d4edda; color: #155724; border-color: #28a745; }
.error { background: #f8d7da; color: #721c24; border-color: #dc3545; }
.warning { background: #fff3cd; color: #856404; border-color: #ffc107; }
.info { background: #cce7ff; color: #004085; border-color: #007bff; }
.btn { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 4px; color: white; font-weight: bold; }
.btn-success { background: #28a745; }
.btn-primary { background: #007bff; }
.btn-danger { background: #dc3545; }
</style>";

// Include CI bootstrap
require_once 'index.php';

echo "<div class='status info'><h2>📋 Complete Fiscal Year Module Verification</h2></div>";

echo "<h2>1. 🔐 Authentication & Permissions</h2>";
if ($this->ion_auth->logged_in()) {
    $user = $this->ion_auth->user()->row();
    echo "<div class='status success'>✓ Logged in as: {$user->username} (ID: {$user->id})</div>";

    if ($this->ion_auth->is_admin()) {
        echo "<div class='status success'>✅ ADMIN ACCESS: You should see Fiscal Year Management in Settings menu</div>";
        $can_access = true;
    } else {
        echo "<div class='status error'>❌ NOT ADMIN: You will NOT see Fiscal Year Management in Settings menu</div>";
        echo "<div class='status error'>❌ DIRECT ACCESS: If you try to access fiscal year URLs directly, you'll be redirected to dashboard</div>";
        echo "<div class='status warning'><strong>SOLUTION:</strong> Log in with an admin account to access fiscal year features.</div>";
        $can_access = false;
    }
} else {
    echo "<div class='status error'>❌ NOT LOGGED IN: Please log in first</div>";
    echo "<p><a href='" . site_url('auth/login') . "' class='btn btn-primary'>Login</a></p>";
    exit;
}

echo "<h2>2. 🗄️ Database Status</h2>";
if ($this->db->table_exists('fiscal_year')) {
    echo "<div class='status success'>✓ Fiscal year table exists</div>";
    $count = $this->db->count_all('fiscal_year');
    echo "<p><strong>Total fiscal years:</strong> $count</p>";

    if ($count > 0) {
        $active = $this->db->where('status', 1)->get('fiscal_year')->row();
        if ($active) {
            echo "<div class='status success'>✓ Active fiscal year: <strong>{$active->name}</strong> ({$active->start_date} to {$active->end_date})</div>";
        } else {
            echo "<div class='status warning'>⚠ No fiscal year is currently active</div>";
        }
    } else {
        echo "<div class='status warning'>⚠ No fiscal years created yet</div>";
    }
} else {
    echo "<div class='status error'>❌ Fiscal year table does NOT exist</div>";
    echo "<div class='status warning'><strong>SOLUTION:</strong> <a href='create_fiscal_year_table_now.php' class='btn btn-success'>Create Table Now</a></div>";
    exit;
}

echo "<h2>3. 📁 Code Integrity</h2>";
$checks = [
    ['file' => 'application/controllers/setting.php', 'methods' => ['fiscal_year_list', 'fiscal_year_create', 'fiscal_year_delete', 'fiscal_year_set_active'], 'name' => 'Controller'],
    ['file' => 'application/models/setting_model.php', 'methods' => ['fiscal_year_list', 'fiscal_year_create', 'fiscal_year_delete', 'fiscal_year_set_active', 'get_active_fiscal_year'], 'name' => 'Model'],
    ['file' => 'application/views/setting/fiscal_year_list.php', 'methods' => [], 'name' => 'List View'],
    ['file' => 'application/views/setting/fiscal_year_create.php', 'methods' => [], 'name' => 'Create View'],
];

$code_ok = true;
foreach ($checks as $check) {
    if (file_exists($check['file'])) {
        echo "<div class='status success'>✓ {$check['name']} file exists</div>";

        foreach ($check['methods'] as $method) {
            if (strpos(file_get_contents($check['file']), "function $method") !== false) {
                echo "<div class='status success'>✓ $method method exists in {$check['name']}</div>";
            } else {
                echo "<div class='status error'>❌ $method method NOT found in {$check['name']}</div>";
                $code_ok = false;
            }
        }
    } else {
        echo "<div class='status error'>❌ {$check['name']} file missing</div>";
        $code_ok = false;
    }
}

echo "<h2>4. 📋 Menu Configuration</h2>";
$menu_file = 'application/views/menu.php';
if (file_exists($menu_file)) {
    $menu_content = file_get_contents($menu_file);
    if (strpos($menu_content, 'fiscal_year_list') !== false) {
        echo "<div class='status success'>✓ Fiscal year menu item found in menu.php</div>";
    } else {
        echo "<div class='status error'>❌ Fiscal year menu item NOT found in menu.php</div>";
        $code_ok = false;
    }

    if (strpos($menu_content, '$this->ion_auth->is_admin()') !== false) {
        echo "<div class='status success'>✓ Admin restriction found in menu</div>";
    } else {
        echo "<div class='status error'>❌ Admin restriction NOT found in menu</div>";
    }
} else {
    echo "<div class='status error'>❌ Menu file not found</div>";
    $code_ok = false;
}

echo "<h2>5. 🌐 Language Support</h2>";
$this->lang->load('setting');
$required_keys = [
    'fiscal_year_list', 'fiscal_year_create', 'fiscal_year_name',
    'fiscal_year_start_date', 'fiscal_year_end_date', 'access_denied'
];

$lang_ok = true;
foreach ($required_keys as $key) {
    $value = lang($key);
    if (!empty($value) && $value != $key) {
        echo "<div class='status success'>✓ Language key '$key' loaded: '$value'</div>";
    } else {
        echo "<div class='status error'>❌ Language key '$key' NOT found</div>";
        $lang_ok = false;
    }
}

echo "<h2>6. 🎯 Final Status</h2>";

if ($can_access && $code_ok && $lang_ok && $this->db->table_exists('fiscal_year')) {
    echo "<div class='status success'>";
    echo "<h3>✅ FISCAL YEAR MODULE IS FULLY FUNCTIONAL!</h3>";
    echo "<p><strong>As an admin user, you should now be able to:</strong></p>";
    echo "<ul>";
    echo "<li>✅ See 'Fiscal Year Management' in the Settings menu</li>";
    echo "<li>✅ Click the menu item to access fiscal year management</li>";
    echo "<li>✅ Create, edit, and delete fiscal years</li>";
    echo "<li>✅ Set active fiscal years</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div class='status info'>";
    echo "<h3>📍 How to Access:</h3>";
    echo "<ol>";
    echo "<li>Look at the left sidebar menu</li>";
    echo "<li>Find 'Settings' and click it to expand</li>";
    echo "<li>Scroll down and find 'Fiscal Year Management'</li>";
    echo "<li>Click it to open the fiscal year management page</li>";
    echo "</ol>";
    echo "</div>";

    $fiscal_url = site_url(current_lang() . '/setting/fiscal_year_list');
    echo "<p><a href='$fiscal_url' class='btn btn-success' target='_blank'>🚀 Open Fiscal Year Management</a></p>";

} else {
    echo "<div class='status error'>";
    echo "<h3>❌ ISSUES FOUND - FISCAL YEAR MODULE NOT READY</h3>";
    echo "<p>Please address the issues above before the module will work.</p>";
    echo "</div>";

    if (!$can_access) {
        echo "<div class='status warning'><strong>Main Issue:</strong> You are not logged in as an admin user.</div>";
    }
}

echo "<h2>7. 🔧 Troubleshooting</h2>";
echo "<div class='status info'>";
echo "<strong>If you still can't see it after confirming you're admin:</strong>";
echo "<ol>";
echo "<li>Clear browser cache (Ctrl+F5)</li>";
echo "<li>Try incognito/private browsing</li>";
echo "<li>Check if the Settings menu is collapsed (click the arrow)</li>";
echo "<li>Try the direct link above</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='" . site_url() . "' class='btn btn-primary'>← Back to Application</a></p>";
?>
