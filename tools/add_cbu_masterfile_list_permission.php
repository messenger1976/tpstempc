<?php
/**
 * Add Contribution_masterfile_list permission to Module 2 (Capital Build Up)
 * Run once via browser: http://localhost/tapstemco/add_cbu_masterfile_list_permission.php
 * Optional: ?enable_all=1 to grant all groups
 */

require_once dirname(__DIR__) . '/index.php';

echo "<h1>Adding CBU Masterfile List Permission</h1>";
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

$module_id = 2; // Capital Build Up / Contribution
$permission_name = 'Contribution_masterfile_list';

echo "<div class='section'>";
echo "<h3>Step 1: Check Module 2 (Capital Build Up)</h3>";
$module = $this->db->where('id', $module_id)->get('module')->row();
if ($module) {
    echo "<p class='success'>✅ Module 2 exists: {$module->Name}</p>";
} else {
    echo "<p class='error'>❌ Module 2 does NOT exist!</p>";
    echo "</div>";
    exit;
}
echo "</div>";

echo "<div class='section'>";
echo "<h3>Step 2: Check Existing Permission</h3>";
$existing_role = $this->db->where('Module_id', $module_id)
                          ->where('Name', $permission_name)
                          ->get('role')
                          ->row();

if ($existing_role) {
    echo "<p class='success'>✅ Permission '{$permission_name}' already exists (ID: {$existing_role->id})</p>";
    $role_id = $existing_role->id;
} else {
    echo "<p class='warning'>⚠️ Permission '{$permission_name}' does NOT exist. Will add it now.</p>";

    echo "<div class='section'>";
    echo "<h3>Step 3: Adding Permission</h3>";

    $role_data = array(
        'Module_id' => $module_id,
        'Name' => $permission_name
    );

    if ($this->db->insert('role', $role_data)) {
        $role_id = $this->db->insert_id();
        echo "<p class='success'>✅ Successfully added '{$permission_name}' permission (ID: {$role_id})</p>";
    } else {
        $error = $this->db->error();
        echo "<p class='error'>❌ Failed to add permission: " . (isset($error['message']) ? $error['message'] : 'Unknown error') . "</p>";
        echo "</div></div>";
        exit;
    }
    echo "</div>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h3>Step 4: All Capital Build Up Module Permissions</h3>";
$all_roles = $this->db->where('Module_id', $module_id)->order_by('Name', 'ASC')->get('role')->result();
echo "<table><tr><th>ID</th><th>Permission Name</th><th>Status</th></tr>";
foreach ($all_roles as $role) {
    $highlight = ($role->Name == $permission_name) ? "style='background:#fff3cd;'" : "";
    echo "<tr $highlight><td>{$role->id}</td><td>{$role->Name}</td><td class='success'>✅</td></tr>";
}
echo "</table>";
echo "</div>";

if (isset($_GET['enable_all'])) {
    echo "<div class='section'>";
    echo "<h3>Step 5: Enabling Permission for All Groups</h3>";

    $groups = $this->db->get('groups')->result();
    $enabled_count = 0;
    $error_count = 0;

    foreach ($groups as $group) {
        $check = $this->db->where('group_id', $group->id)
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

            if ($this->db->insert('access_level', $access_data)) {
                $enabled_count++;
                echo "<p class='success'>✅ Enabled for group: {$group->name}</p>";
            } else {
                $error_count++;
                $error = $this->db->error();
                echo "<p class='error'>❌ Failed for group '{$group->name}': " . (isset($error['message']) ? $error['message'] : 'Unknown error') . "</p>";
            }
        } else {
            $this->db->where('group_id', $group->id)
                     ->where('Module', $module_id)
                     ->where('link', $permission_name)
                     ->update('access_level', array('allow' => 1));
            $enabled_count++;
            echo "<p class='success'>✅ Updated for group: {$group->name}</p>";
        }
    }

    echo "<p class='success'><strong>Summary: Enabled for {$enabled_count} group(s), {$error_count} error(s)</strong></p>";
    echo "</div>";
} else {
    echo "<div class='section'>";
    echo "<h3>Step 5: Enable Permission for All Groups (Optional)</h3>";
    echo "<p>Click the button below to automatically enable this permission for all user groups:</p>";
    echo "<a href='?enable_all=1' class='btn' onclick=\"return confirm('This will enable the permission for ALL groups. Continue?')\">Enable for All Groups</a>";
    echo "<p class='warning'>⚠️ Note: You can also manually assign this permission to specific groups through the User Management → Privileges section.</p>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h3>Step 6: Manual SQL (for reference)</h3>";
echo "<pre style='background:#f0f0f0;padding:10px;border-radius:4px;overflow-x:auto;'>";
echo "-- Add permission to role table\n";
echo "INSERT INTO role (Module_id, Name) VALUES (2, 'Contribution_masterfile_list');\n\n";
echo "-- Enable for a specific group (replace GROUP_ID with actual group ID)\n";
echo "INSERT INTO access_level (group_id, Module, link, allow) VALUES (GROUP_ID, 2, 'Contribution_masterfile_list', 1);\n";
echo "</pre>";
echo "</div>";

echo "<hr>";
echo "<p><strong>✅ Permission setup complete!</strong></p>";
echo "<p>You can now assign this permission to user groups through: <strong>User Management → Privileges</strong></p>";
echo "<p>New page URL: <a href='" . site_url(current_lang() . '/contribution/masterfile_list') . "'>" . site_url(current_lang() . '/contribution/masterfile_list') . "</a></p>";
echo "<p><a href='" . site_url() . "'>← Back to Application</a></p>";
?>
