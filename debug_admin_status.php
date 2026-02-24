<?php
// Debug admin status and permissions
echo "<h1>Admin Status Debug</h1>";

// Include CI bootstrap
require_once 'index.php';

echo "<h2>User Authentication Status</h2>";

if ($this->ion_auth->logged_in()) {
    echo "<p style='color: green;'>✓ User is logged in</p>";

    $user = $this->ion_auth->user()->row();
    echo "<p>User ID: {$user->id}</p>";
    echo "<p>Username: {$user->username}</p>";
    echo "<p>Email: {$user->email}</p>";

    if ($this->ion_auth->is_admin()) {
        echo "<p style='color: green;'>✓ User is admin</p>";
    } else {
        echo "<p style='color: red;'>✗ User is NOT admin</p>";

        // Check user groups
        $groups = $this->ion_auth->get_users_groups($user->id)->result();
        echo "<p>User groups:</p><ul>";
        foreach ($groups as $group) {
            echo "<li>{$group->name} (ID: {$group->id})</li>";
        }
        echo "</ul>";
    }

    echo "<h2>Permission Check</h2>";

    // Test has_role function
    if (function_exists('has_role')) {
        echo "<p>has_role function exists</p>";

        // Test various permissions
        $permissions = [
            'Manage_payment_method',
            'Manage_company_information',
            'Share_settings',
            'Mortuary_settings',
            'Manage_saving_account_type',
            'Contributions_setting',
            'Manage_sales_purchase_items',
            'Manage_tax_code',
            'Global_settings',
            'Manage_loan_product'
        ];

        echo "<ul>";
        foreach ($permissions as $perm) {
            $has_perm = has_role(9, $perm);
            $status = $has_perm ? "<span style='color: green;'>YES</span>" : "<span style='color: red;'>NO</span>";
            echo "<li>$perm: $status</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>has_role function does NOT exist</p>";
    }

} else {
    echo "<p style='color: red;'>✗ User is NOT logged in</p>";
}

echo "<h2>Menu Visibility Test</h2>";

// Simulate the menu condition
if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
    echo "<p style='color: green;'>✓ Fiscal year menu SHOULD be visible (admin condition met)</p>";
} else {
    echo "<p style='color: red;'>✗ Fiscal year menu will NOT be visible (admin condition not met)</p>";
}

echo "<h2>Database Check</h2>";

// Check if fiscal_year table exists
if ($this->db->table_exists('fiscal_year')) {
    echo "<p style='color: green;'>✓ Fiscal year table exists</p>";
    $count = $this->db->count_all('fiscal_year');
    echo "<p>Fiscal years: $count</p>";
} else {
    echo "<p style='color: red;'>✗ Fiscal year table does NOT exist</p>";
}

echo "<p><a href='" . site_url() . "'>← Back to Application</a></p>";
?>
