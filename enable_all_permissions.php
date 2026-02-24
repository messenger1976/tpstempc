<?php
/**
 * Enable all permissions for all existing groups
 * Run this script once to enable all permissions for all groups
 * SECURITY: Delete after use!
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
define('SYSDIR', 'system');

require_once BASEPATH . 'core/CodeIgniter.php';

$CI =& get_instance();

echo "<h2>Enable All Permissions for All Groups</h2>";
echo "<style>
    body{font-family:Arial;margin:20px;background:#f5f5f5;}
    .success{color:green;font-weight:bold;}
    .error{color:red;font-weight:bold;}
    table{border-collapse:collapse;width:100%;margin:10px 0;background:white;}
    th,td{border:1px solid #ddd;padding:8px;}
    th{background:#4CAF50;color:white;}
</style>";

// Get all groups
$groups = $CI->db->get('groups')->result();

echo "<p><strong>Found " . count($groups) . " groups</strong></p>";

if (isset($_GET['run'])) {
    echo "<h3>Enabling permissions...</h3>";
    
    $enabled_count = 0;
    $error_count = 0;
    
    foreach ($groups as $group) {
        echo "<p>Processing group: <strong>{$group->name}</strong> (ID: {$group->id})...</p>";
        
        // Get all modules and roles
        $modules = $CI->db->get('module')->result();
        
        foreach ($modules as $module) {
            $roles = $CI->db->get_where('role', array('Module_id' => $module->id))->result();
            
            foreach ($roles as $role) {
                // Check if access_level entry exists
                $check = $CI->db->get_where('access_level', array(
                    'group_id' => $group->id,
                    'Module' => $module->id,
                    'link' => $role->Name
                ))->row();
                
                if ($check === null) {
                    // Insert new entry with allow=1
                    $insert_data = array(
                        'group_id' => $group->id,
                        'Module' => $module->id,
                        'link' => $role->Name,
                        'allow' => 1
                    );
                    if ($CI->db->insert('access_level', $insert_data)) {
                        $enabled_count++;
                    } else {
                        $error_count++;
                        echo "<p class='error'>Failed to insert: {$group->name} - {$module->Name} - {$role->Name}</p>";
                    }
                } else {
                    // Update existing entry to allow=1
                    $CI->db->where('group_id', $group->id);
                    $CI->db->where('Module', $module->id);
                    $CI->db->where('link', $role->Name);
                    if ($CI->db->update('access_level', array('allow' => 1))) {
                        $enabled_count++;
                    } else {
                        $error_count++;
                        echo "<p class='error'>Failed to update: {$group->name} - {$module->Name} - {$role->Name}</p>";
                    }
                }
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Results:</h3>";
    echo "<p class='success'>✅ Enabled/Updated: $enabled_count permissions</p>";
    if ($error_count > 0) {
        echo "<p class='error'>❌ Errors: $error_count</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>✅ All permissions have been enabled for all groups!</strong></p>";
    echo "<p><a href='?' style='padding:10px 20px;background:#4CAF50;color:white;text-decoration:none;border-radius:3px;'>Refresh</a></p>";
} else {
    echo "<h3>Groups to process:</h3>";
    echo "<table><tr><th>ID</th><th>Name</th><th>Description</th></tr>";
    foreach ($groups as $group) {
        echo "<tr><td>{$group->id}</td><td>{$group->name}</td><td>" . ($group->description ? $group->description : '-') . "</td></tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<p><strong>⚠️ This will enable ALL permissions for ALL groups!</strong></p>";
    echo "<p><a href='?run=1' style='padding:10px 20px;background:#4CAF50;color:white;text-decoration:none;border-radius:3px;'>Click to Enable All Permissions</a></p>";
}

echo "<hr>";
echo "<p><strong>⚠️ SECURITY: Delete this file (enable_all_permissions.php) after use!</strong></p>";
?>

