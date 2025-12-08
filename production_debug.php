<?php
/**
 * Production Debug Script
 * Run this on production to check what's happening
 * SECURITY: Delete after use!
 */

// Try to load CodeIgniter config
$config_file = __DIR__ . '/application/config/database.php';
if (!file_exists($config_file)) {
    die("Database config file not found");
}

// Extract database config
$config_content = file_get_contents($config_file);
preg_match("/\['default'\]\['hostname'\]\s*=\s*'([^']+)'/", $config_content, $host) || $host[1] = 'localhost';
preg_match("/\['default'\]\['username'\]\s*=\s*'([^']+)'/", $config_content, $user) || $user[1] = 'root';
preg_match("/\['default'\]\['password'\]\s*=\s*'([^']+)'/", $config_content, $pass) || $pass[1] = '';
preg_match("/\['default'\]\['database'\]\s*=\s*'([^']+)'/", $config_content, $db) || $db[1] = 'tapstemco';

$conn = new mysqli($host[1], $user[1], $pass[1], $db[1]);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Production Debug - Permission Save Issue</h2>";
echo "<style>
    body{font-family:Arial;margin:20px;}
    .success{color:green;font-weight:bold;}
    .error{color:red;font-weight:bold;}
    .warning{color:orange;font-weight:bold;}
    table{border-collapse:collapse;width:100%;margin:10px 0;}
    th,td{border:1px solid #ddd;padding:8px;}
    th{background:#4CAF50;color:white;}
    code{background:#f0f0f0;padding:2px 6px;}
</style>";

// Check roles
echo "<h3>1. Check Roles in Database</h3>";
$roles = $conn->query("SELECT * FROM role WHERE Module_id = 3 AND Name IN ('saving_account_list', 'Edit_saving_account')");
$saving_list_id = null;
$edit_id = null;

if ($roles && $roles->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Name</th></tr>";
    while ($r = $roles->fetch_assoc()) {
        echo "<tr><td>{$r['id']}</td><td class='success'>{$r['Name']}</td></tr>";
        if ($r['Name'] == 'saving_account_list') $saving_list_id = $r['id'];
        if ($r['Name'] == 'Edit_saving_account') $edit_id = $r['id'];
    }
    echo "</table>";
    echo "<p class='success'>✅ Roles exist</p>";
} else {
    echo "<p class='error'>❌ Roles NOT FOUND in production database!</p>";
    echo "<p><strong>ACTION REQUIRED:</strong> Run the SQL file: sql/saving_account_list_role_production.sql</p>";
}

// Check if they appear in privilege_list for a group
echo "<h3>2. Check if Roles Appear in Privilege List</h3>";
$group = $conn->query("SELECT * FROM groups LIMIT 1")->fetch_assoc();
if ($group) {
    $group_id = $group['id'];
    echo "<p>Testing with Group: {$group['name']} (ID: $group_id)</p>";
    
    // Simulate privilege_list
    $module = $conn->query("SELECT * FROM module WHERE id = 3")->fetch_assoc();
    if ($module) {
        echo "<p>Module: {$module['Name']} (ID: {$module['id']})</p>";
        
        $all_roles = $conn->query("SELECT * FROM role WHERE Module_id = 3 ORDER BY Name");
        echo "<p>Roles that should appear:</p>";
        echo "<table><tr><th>Role Name</th><th>Role ID</th><th>Expected Field Name</th></tr>";
        
        while ($r = $all_roles->fetch_assoc()) {
            $field_name = "module_{$module['id']}_{$r['id']}";
            $is_target = ($r['Name'] == 'saving_account_list' || $r['Name'] == 'Edit_saving_account');
            $highlight = $is_target ? "style='background:#fff3cd;'" : "";
            echo "<tr $highlight><td>{$r['Name']}</td><td>{$r['id']}</td><td><code>$field_name</code></td></tr>";
        }
        echo "</table>";
    }
}

// Check access_level entries
echo "<h3>3. Check Current Access Levels</h3>";
$access = $conn->query("SELECT al.*, g.name as group_name FROM access_level al JOIN groups g ON al.group_id = g.id WHERE al.Module = 3 AND al.link IN ('saving_account_list', 'Edit_saving_account')");
if ($access && $access->num_rows > 0) {
    echo "<table><tr><th>Group</th><th>Module</th><th>Link</th><th>Allow</th></tr>";
    while ($a = $access->fetch_assoc()) {
        $status = $a['allow'] ? "✅ Enabled" : "❌ Disabled";
        echo "<tr><td>{$a['group_name']}</td><td>{$a['Module']}</td><td>{$a['link']}</td><td>$status</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No access_level entries found (this is normal if not yet assigned)</p>";
}

// Check file versions
echo "<h3>4. Check Code Files</h3>";
$auth_file = __DIR__ . '/application/controllers/auth.php';
if (file_exists($auth_file)) {
    $content = file_get_contents($auth_file);
    if (strpos($content, 'saving_account_list expected field') !== false) {
        echo "<p class='success'>✅ auth.php contains updated code</p>";
    } else {
        echo "<p class='error'>❌ auth.php does NOT contain updated code!</p>";
        echo "<p><strong>ACTION REQUIRED:</strong> Deploy updated auth.php to production</p>";
    }
    
    if (strpos($content, 'sleep(1)') !== false) {
        echo "<p class='success'>✅ auth.php contains delay code</p>";
    }
} else {
    echo "<p class='error'>❌ auth.php not found!</p>";
}

$view_file = __DIR__ . '/application/views/auth/assign_privillege.php';
if (file_exists($view_file)) {
    $content = file_get_contents($view_file);
    if (strpos($content, 'Edit_saving_account checkbox found') !== false) {
        echo "<p class='success'>✅ assign_privillege.php contains updated code</p>";
    } else {
        echo "<p class='error'>❌ assign_privillege.php does NOT contain updated code!</p>";
        echo "<p><strong>ACTION REQUIRED:</strong> Deploy updated assign_privillege.php to production</p>";
    }
} else {
    echo "<p class='error'>❌ assign_privillege.php not found!</p>";
}

echo "<hr>";
echo "<h3>Summary & Next Steps</h3>";
if (!$saving_list_id || !$edit_id) {
    echo "<p class='error'><strong>CRITICAL:</strong> Roles are missing from production database!</p>";
    echo "<ol>";
    echo "<li>Run SQL: <code>sql/saving_account_list_role_production.sql</code></li>";
    echo "<li>Refresh permission page</li>";
    echo "<li>Try saving again</li>";
    echo "</ol>";
} else {
    echo "<p class='success'>Roles exist. If still not saving:</p>";
    echo "<ol>";
    echo "<li>Check browser console (F12) for JavaScript errors</li>";
    echo "<li>Check Network tab when clicking Save</li>";
    echo "<li>Verify code files are updated</li>";
    echo "<li>Clear browser cache</li>";
    echo "</ol>";
}

$conn->close();
echo "<hr><p><strong>⚠️ Delete this file after use!</strong></p>";
?>

