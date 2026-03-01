<?php
/**
 * Add View_AR permission to Module 6 (Finance)
 * Run this script once to add the AR role and optionally enable it for groups.
 * URL: http://your-domain/add_ar_permission.php
 * SECURITY: Delete or restrict access after use!
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
define('SYSDIR', 'system');

require_once BASEPATH . 'core/CodeIgniter.php';

$CI =& get_instance();

$module_id = 6; // Finance module
$permission_name = 'View_AR';

echo "<h1>Adding AR Module Permission (View_AR)</h1>";
echo "<style>
    body{font-family:Arial;margin:20px;background:#f5f5f5;}
    .success{color:green;font-weight:bold;}
    .error{color:red;font-weight:bold;}
    .warning{color:orange;font-weight:bold;}
    table{border-collapse:collapse;width:100%;margin:10px 0;background:white;}
    th,td{border:1px solid #ddd;padding:8px;text-align:left;}
    th{background-color:#4CAF50;color:white;}
    .section{background:white;padding:15px;margin:10px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
    .btn{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;margin:5px;}
    .btn:hover{background:#0056b3;}
</style>";

// Step 1: Check if Module 6 exists
echo "<div class='section'>";
echo "<h3>Step 1: Check Module 6 (Finance)</h3>";
$module = $CI->db->where('id', $module_id)->get('module')->row();
if ($module) {
    echo "<p class='success'>✅ Module 6 exists: " . htmlspecialchars($module->Name) . "</p>";
} else {
    echo "<p class='error'>❌ Module 6 does NOT exist!</p>";
    echo "</div>";
    exit;
}
echo "</div>";

// Step 2: Check if permission already exists
echo "<div class='section'>";
echo "<h3>Step 2: Check Existing Permission</h3>";
$existing_role = $CI->db->where('Module_id', $module_id)
                        ->where('Name', $permission_name)
                        ->get('role')
                        ->row();

if ($existing_role) {
    echo "<p class='success'>✅ Permission '" . htmlspecialchars($permission_name) . "' already exists (ID: {$existing_role->id})</p>";
    $role_id = $existing_role->id;
} else {
    echo "<p class='warning'>⚠️ Permission '" . htmlspecialchars($permission_name) . "' does NOT exist. Adding it now.</p>";
    $role_data = array(
        'Module_id' => $module_id,
        'Name' => $permission_name
    );
    if ($CI->db->insert('role', $role_data)) {
        $role_id = $CI->db->insert_id();
        echo "<p class='success'>✅ Successfully added '" . htmlspecialchars($permission_name) . "' permission (ID: {$role_id})</p>";
    } else {
        $err = $CI->db->error();
        echo "<p class='error'>❌ Failed to add permission: " . htmlspecialchars(isset($err['message']) ? $err['message'] : 'Unknown error') . "</p>";
        echo "</div></div>";
        exit;
    }
}
echo "</div>";

// Step 3: List Finance module permissions
echo "<div class='section'>";
echo "<h3>Step 3: Finance Module Permissions (including AR)</h3>";
$all_roles = $CI->db->where('Module_id', $module_id)->order_by('Name', 'ASC')->get('role')->result();
echo "<table><tr><th>ID</th><th>Permission Name</th></tr>";
foreach ($all_roles as $role) {
    $highlight = ($role->Name == $permission_name) ? "style='background:#fff3cd;'" : "";
    echo "<tr $highlight><td>{$role->id}</td><td>" . htmlspecialchars($role->Name) . "</td></tr>";
}
echo "</table>";
echo "</div>";

// Step 4: Enable for all groups (optional)
if (isset($_GET['enable_all'])) {
    echo "<div class='section'>";
    echo "<h3>Step 4: Enabling View_AR for All Groups</h3>";
    $groups = $CI->db->get('groups')->result();
    $enabled_count = 0;
    foreach ($groups as $group) {
        $check = $CI->db->where('group_id', $group->id)
                        ->where('Module', $module_id)
                        ->where('link', $permission_name)
                        ->get('access_level')
                        ->row();
        if (!$check) {
            $access_data = array(
                'group_id' => $group->id,
                'Module' => $module_id,
                'link' => $permission_name,
                'allow' => 1
            );
            if ($CI->db->insert('access_level', $access_data)) {
                $enabled_count++;
                echo "<p class='success'>✅ Enabled for group: " . htmlspecialchars($group->name) . "</p>";
            }
        } else {
            $CI->db->where('group_id', $group->id)
                   ->where('Module', $module_id)
                   ->where('link', $permission_name)
                   ->update('access_level', array('allow' => 1));
            $enabled_count++;
            echo "<p class='success'>✅ Updated for group: " . htmlspecialchars($group->name) . "</p>";
        }
    }
    echo "<p class='success'><strong>Summary: Enabled for {$enabled_count} group(s)</strong></p>";
    echo "</div>";
} else {
    echo "<div class='section'>";
    echo "<h3>Step 4: Enable Permission for All Groups (Optional)</h3>";
    echo "<p>Click the button below to enable <strong>View_AR</strong> for all user groups so they can see the Accounts Receivable menu and reports:</p>";
    echo "<a href='?enable_all=1' class='btn' onclick=\"return confirm('Enable View_AR for ALL groups?')\">Enable for All Groups</a>";
    echo "<p class='warning'>⚠️ Or assign it per group via <strong>User Management → Privileges</strong> (edit a group and check the Finance → View_AR checkbox).</p>";
    echo "</div>";
}

// Step 5: Manual SQL reference
echo "<div class='section'>";
echo "<h3>Step 5: Manual SQL (optional)</h3>";
echo "<pre style='background:#f0f0f0;padding:10px;border-radius:4px;overflow-x:auto;'>";
echo "-- Add permission to role table\n";
echo "INSERT INTO role (Module_id, Name) VALUES (6, 'View_AR');\n\n";
echo "-- Enable for a specific group (replace 1 with your group_id)\n";
echo "INSERT INTO access_level (group_id, Module, link, allow) VALUES (1, 6, 'View_AR', 1);\n";
echo "</pre>";
echo "</div>";

echo "<hr>";
echo "<p><strong>✅ AR permission setup complete.</strong></p>";
echo "<p>Assign <strong>View_AR</strong> to groups via: <strong>User Management → View group list → Edit (Privileges)</strong> → under Finance, check <strong>View_AR</strong>.</p>";
echo "<p><a href='" . $CI->config->base_url() . "' class='btn'>← Back to Application</a></p>";
