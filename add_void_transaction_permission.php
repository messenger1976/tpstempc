<?php
/**
 * Add void_transaction permission to Module 3 (Saving)
 * Run this script once to add the void transaction role and optionally enable it for groups.
 * URL: http://your-domain/add_void_transaction_permission.php
 * SECURITY: Delete or restrict access after use!
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
define('SYSDIR', 'system');

require_once BASEPATH . 'core/CodeIgniter.php';

$CI =& get_instance();

$module_id = 3; // Saving module
$permission_name = 'void_transaction';

echo "<h1>Adding Void Transaction Permission</h1>";
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

// Step 1: Check if Module 3 exists
echo "<div class='section'>";
echo "<h3>Step 1: Check Module 3 (Saving)</h3>";
$module = $CI->db->where('id', $module_id)->get('module')->row();
if ($module) {
    echo "<p class='success'>✅ Module 3 exists: " . htmlspecialchars($module->Name) . "</p>";
} else {
    echo "<p class='error'>❌ Module 3 does NOT exist!</p>";
    echo "</div>";
    exit;
}
echo "</div>";

// Step 2: Check if permission already exists in role table
echo "<div class='section'>";
echo "<h3>Step 2: Check Role Table</h3>";
$existing_role = $CI->db->where('Module_id', $module_id)
                        ->where('Name', $permission_name)
                        ->get('role')->row();

if ($existing_role) {
    echo "<p class='warning'>⚠️ Permission already exists in role table (ID: {$existing_role->id})</p>";
} else {
    echo "<p>Permission does not exist in role table. Adding...</p>";
    
    $role_data = array(
        'Module_id' => $module_id,
        'Name' => $permission_name
    );
    
    if ($CI->db->insert('role', $role_data)) {
        $role_id = $CI->db->insert_id();
        echo "<p class='success'>✅ Successfully added '{$permission_name}' to role table (ID: {$role_id})</p>";
    } else {
        echo "<p class='error'>❌ Failed to add permission to role table</p>";
        echo "</div>";
        exit;
    }
}
echo "</div>";

// Step 3: Show existing groups
echo "<div class='section'>";
echo "<h3>Step 3: Available Groups</h3>";
$groups = $CI->db->get('groups')->result();
if ($groups) {
    echo "<table>";
    echo "<tr><th>Group ID</th><th>Group Name</th><th>Description</th></tr>";
    foreach ($groups as $group) {
        echo "<tr>";
        echo "<td>" . $group->id . "</td>";
        echo "<td>" . htmlspecialchars($group->name) . "</td>";
        echo "<td>" . htmlspecialchars($group->description) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>No groups found</p>";
}
echo "</div>";

// Step 4: Enable for admin group (optional)
echo "<div class='section'>";
echo "<h3>Step 4: Enable for Groups</h3>";

$enable_for_groups = isset($_GET['enable']) ? explode(',', $_GET['enable']) : array();

if (empty($enable_for_groups)) {
    echo "<p>No groups selected. To enable this permission for specific groups, add ?enable=1 to the URL (e.g., ?enable=1 for admin group, or ?enable=1,2,3 for multiple groups)</p>";
    echo "<p><a href='?enable=1' class='btn'>Enable for Admin Group (ID: 1)</a></p>";
} else {
    echo "<p>Enabling permission for groups: " . implode(', ', $enable_for_groups) . "</p>";
    
    foreach ($enable_for_groups as $group_id) {
        $group_id = intval(trim($group_id));
        
        if ($group_id <= 0) continue;
        
        // Check if already exists
        $existing = $CI->db->where('group_id', $group_id)
                          ->where('Module', $module_id)
                          ->where('link', $permission_name)
                          ->get('access_level')->row();
        
        if ($existing) {
            echo "<p class='warning'>⚠️ Group {$group_id}: Already has access (ID: {$existing->id}, allow: {$existing->allow})</p>";
            
            // Update to allow=1 if it's 0
            if ($existing->allow != 1) {
                $CI->db->where('id', $existing->id);
                if ($CI->db->update('access_level', array('allow' => 1))) {
                    echo "<p class='success'>✅ Group {$group_id}: Updated to allow=1</p>";
                } else {
                    echo "<p class='error'>❌ Group {$group_id}: Failed to update</p>";
                }
            }
        } else {
            $access_data = array(
                'group_id' => $group_id,
                'Module' => $module_id,
                'link' => $permission_name,
                'allow' => 1
            );
            
            if ($CI->db->insert('access_level', $access_data)) {
                $access_id = $CI->db->insert_id();
                echo "<p class='success'>✅ Group {$group_id}: Permission added (ID: {$access_id})</p>";
            } else {
                echo "<p class='error'>❌ Group {$group_id}: Failed to add permission</p>";
            }
        }
    }
}
echo "</div>";

// Step 5: Verify current permissions
echo "<div class='section'>";
echo "<h3>Step 5: Current Access Levels for void_transaction</h3>";
$access_levels = $CI->db->select('access_level.*, groups.name as group_name')
                       ->from('access_level')
                       ->join('groups', 'access_level.group_id = groups.id')
                       ->where('access_level.Module', $module_id)
                       ->where('access_level.link', $permission_name)
                       ->get()->result();

if ($access_levels) {
    echo "<table>";
    echo "<tr><th>Group ID</th><th>Group Name</th><th>Allow</th></tr>";
    foreach ($access_levels as $access) {
        echo "<tr>";
        echo "<td>" . $access->group_id . "</td>";
        echo "<td>" . htmlspecialchars($access->group_name) . "</td>";
        echo "<td>" . ($access->allow ? '✅ Yes' : '❌ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>No access levels configured yet. Use ?enable=1 to enable for admin group.</p>";
}
echo "</div>";

// Summary
echo "<div class='section'>";
echo "<h3>Summary</h3>";
echo "<ul>";
echo "<li>✅ Permission 'void_transaction' is now in the role table for Module 3 (Saving)</li>";
echo "<li>👉 Users in groups with this permission enabled can void savings transactions</li>";
echo "<li>🔒 The void button will only appear for users with this permission</li>";
echo "<li>⚙️ System administrators can manage this permission in the Roles & Permissions section</li>";
echo "</ul>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If you haven't enabled it for any groups yet, click 'Enable for Admin Group' above</li>";
echo "<li>Go to your application's Roles & Permissions management to assign this permission to other roles</li>";
echo "<li>Test by logging in with a user from the enabled group and viewing the transaction search page</li>";
echo "<li><strong>IMPORTANT:</strong> Delete this file after use for security</li>";
echo "</ol>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>SQL Commands (for reference)</h3>";
echo "<pre style='background:#f0f0f0;padding:10px;border-radius:4px;overflow-x:auto;'>";
echo "-- Add to role table\n";
echo "INSERT INTO role (Module_id, Name) VALUES ({$module_id}, '{$permission_name}');\n\n";
echo "-- Enable for group 1 (admin)\n";
echo "INSERT INTO access_level (group_id, Module, link, allow) VALUES (1, {$module_id}, '{$permission_name}', 1);\n";
echo "</pre>";
echo "</div>";
?>
