<?php
echo "<h1>🧪 Fiscal Year Access Test</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";

// Include CI bootstrap
require_once 'index.php';

echo "<h2>User Status</h2>";
if ($this->ion_auth->logged_in()) {
    $user = $this->ion_auth->user()->row();
    echo "<p class='success'>✓ Logged in as: {$user->username}</p>";

    if ($this->ion_auth->is_admin()) {
        echo "<p class='success'>✓ User is ADMIN</p>";

        echo "<h2>Direct URL Tests</h2>";

        // Test fiscal_year_list
        echo "<p><strong>Testing fiscal_year_list URL:</strong></p>";
        $url = site_url(current_lang() . '/setting/fiscal_year_list');
        echo "<p>URL: <a href='$url' target='_blank'>$url</a></p>";

        // Test if controller method exists
        if (method_exists($this->setting, 'fiscal_year_list')) {
            echo "<p class='success'>✓ fiscal_year_list method exists in controller</p>";
        } else {
            echo "<p class='error'>✗ fiscal_year_list method NOT found in controller</p>";
        }

        // Test if model methods exist
        if (method_exists($this->setting_model, 'fiscal_year_list')) {
            echo "<p class='success'>✓ fiscal_year_list method exists in model</p>";
        } else {
            echo "<p class='error'>✗ fiscal_year_list method NOT found in model</p>";
        }

        // Test database
        if ($this->db->table_exists('fiscal_year')) {
            echo "<p class='success'>✓ Database table exists</p>";
            $count = $this->db->count_all('fiscal_year');
            echo "<p>Fiscal years: $count</p>";
        } else {
            echo "<p class='error'>✗ Database table missing</p>";
        }

        echo "<h2>Expected Result</h2>";
        echo "<p class='success'>As an admin user, clicking the Fiscal Year Management link should work properly.</p>";

    } else {
        echo "<p class='error'>✗ User is NOT admin</p>";
        echo "<p class='info'>Non-admin users will be redirected to dashboard when accessing fiscal year URLs</p>";
    }

} else {
    echo "<p class='error'>✗ Not logged in</p>";
}

echo "<h2>Menu Visibility</h2>";
if ($this->ion_auth->is_admin()) {
    echo "<p class='success'>✓ Fiscal Year Management should appear in Settings menu</p>";
} else {
    echo "<p class='error'>✗ Fiscal Year Management will NOT appear in Settings menu (non-admin)</p>";
}

echo "<p><a href='" . site_url() . "' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>← Back to Application</a></p>";
?>
