<?php
echo "<h1>Fiscal Year Module Status Check</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";

// Include CI bootstrap
require_once 'index.php';

echo "<h2>1. Database Status</h2>";
if ($this->db->table_exists('fiscal_year')) {
    echo "<p class='success'>✓ Fiscal year table exists</p>";
    $count = $this->db->count_all('fiscal_year');
    echo "<p class='info'>Fiscal years in database: $count</p>";
} else {
    echo "<p class='error'>✗ Fiscal year table does NOT exist</p>";
    echo "<p><a href='create_fiscal_year_table.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>Create Table Now</a></p>";
}

echo "<h2>2. User Authentication</h2>";
if ($this->ion_auth->logged_in()) {
    echo "<p class='success'>✓ User is logged in</p>";

    $user = $this->ion_auth->user()->row();
    echo "<p>User: {$user->username} (ID: {$user->id})</p>";

    if ($this->ion_auth->is_admin()) {
        echo "<p class='success'>✓ User is admin - Fiscal Year menu should be visible</p>";
    } else {
        echo "<p class='error'>✗ User is NOT admin - Fiscal Year menu will NOT be visible</p>";
        echo "<p class='info'>Only admin users can access the Fiscal Year module</p>";
    }
} else {
    echo "<p class='error'>✗ User is NOT logged in</p>";
}

echo "<h2>3. Code Files Check</h2>";
$files = [
    'application/controllers/setting.php' => 'fiscal_year_list',
    'application/models/setting_model.php' => 'fiscal_year_list',
    'application/views/setting/fiscal_year_list.php' => '',
    'application/views/setting/fiscal_year_create.php' => '',
    'application/language/english/setting_lang.php' => 'fiscal_year_list'
];

foreach ($files as $file => $search) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ $file exists</p>";
        if (!empty($search) && strpos(file_get_contents($file), $search) !== false) {
            echo "<p class='success'>✓ $search found in $file</p>";
        }
    } else {
        echo "<p class='error'>✗ $file missing</p>";
    }
}

echo "<h2>4. Menu Check</h2>";
$menu_file = 'application/views/menu.php';
if (file_exists($menu_file)) {
    $menu_content = file_get_contents($menu_file);
    if (strpos($menu_content, 'fiscal_year_list') !== false) {
        echo "<p class='success'>✓ Fiscal year menu item found in menu.php</p>";
    } else {
        echo "<p class='error'>✗ Fiscal year menu item NOT found in menu.php</p>";
    }

    if (strpos($menu_content, '$this->ion_auth->is_admin()') !== false) {
        echo "<p class='success'>✓ Admin restriction found in menu</p>";
    } else {
        echo "<p class='error'>✗ Admin restriction NOT found in menu</p>";
    }
}

echo "<h2>5. Next Steps</h2>";
echo "<ol>";
echo "<li><strong>If table doesn't exist:</strong> Click the 'Create Table Now' button above</li>";
echo "<li><strong>If not admin:</strong> Log in as an admin user or contact your administrator</li>";
echo "<li><strong>Clear cache:</strong> Clear your browser cache and refresh</li>";
echo "<li><strong>Check menu:</strong> Go to Settings → Fiscal Year Management should appear</li>";
echo "</ol>";

echo "<p><a href='" . site_url() . "' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>← Back to Application</a></p>";
?>
