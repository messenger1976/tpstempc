<?php
/**
 * Add Journal Entry List permissions to Module 6 (Finance)
 * Run once: http://your-domain/add_journal_entry_list_permissions.php
 * SECURITY: Delete or restrict access after use!
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
define('SYSDIR', 'system');

require_once BASEPATH . 'core/CodeIgniter.php';

$CI =& get_instance();

$module_id = 6;
$permissions = array('View_journal_entry', 'Delete_journal_entry');

echo "<h1>Adding Journal Entry List Permissions</h1>";
echo "<style>
    body{font-family:Arial;margin:20px;background:#f5f5f5;}
    .success{color:green;font-weight:bold;}
    .error{color:red;font-weight:bold;}
    .warning{color:orange;font-weight:bold;}
    .section{background:white;padding:15px;margin:10px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
</style>";

$module = $CI->db->where('id', $module_id)->get('module')->row();
if (!$module) {
    echo "<p class='error'>Module 6 (Finance) not found.</p>";
    exit;
}

foreach ($permissions as $permission_name) {
    echo "<div class='section'><h3>" . htmlspecialchars($permission_name) . "</h3>";

    $existing_role = $CI->db->where('Module_id', $module_id)
                            ->where('Name', $permission_name)
                            ->get('role')
                            ->row();

    if ($existing_role) {
        echo "<p class='success'>Permission already exists (role ID: {$existing_role->id})</p>";
    } else {
        if ($CI->db->insert('role', array('Module_id' => $module_id, 'Name' => $permission_name))) {
            echo "<p class='success'>Added permission to role table.</p>";
        } else {
            $err = $CI->db->error();
            echo "<p class='error'>Failed: " . htmlspecialchars(isset($err['message']) ? $err['message'] : 'Unknown error') . "</p>";
            echo "</div>";
            continue;
        }
    }

    $groups = $CI->db->get('groups')->result();
    foreach ($groups as $group) {
        $check = $CI->db->get_where('access_level', array(
            'group_id' => $group->id,
            'Module' => $module_id,
            'link' => $permission_name
        ))->row();

        if (!$check) {
            $CI->db->insert('access_level', array(
                'group_id' => $group->id,
                'Module' => $module_id,
                'link' => $permission_name,
                'allow' => 1
            ));
            echo "<p class='success'>Enabled for group: " . htmlspecialchars($group->name) . "</p>";
        }
    }

    echo "</div>";
}

echo "<div class='section'>";
echo "<h3>Manual SQL (if needed)</h3>";
echo "<pre>INSERT INTO access_level (group_id, Module, link, allow) VALUES (1, 6, 'View_journal_entry', 1);\n";
echo "INSERT INTO access_level (group_id, Module, link, allow) VALUES (1, 6, 'Delete_journal_entry', 1);</pre>";
echo "<p>Existing <strong>Journal_entry</strong> permission is still used for creating entries via Finance → Journal Entry.</p>";
echo "</div>";
