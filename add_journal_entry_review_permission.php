<?php
/**
 * Add Review_journal_entry permission to Module 6 (Finance)
 * Run once: http://your-domain/add_journal_entry_review_permission.php
 * SECURITY: Delete or restrict access after use!
 *
 * Separates Journal Entry create (Journal_entry) from Review & Approval.
 * Groups that already have Journal_entry are granted Review_journal_entry
 * so existing access is preserved.
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
define('SYSDIR', 'system');

require_once BASEPATH . 'core/CodeIgniter.php';

$CI =& get_instance();

$module_id = 6;
$permission_name = 'Review_journal_entry';

echo "<h1>Adding Journal Entry Review Permission</h1>";
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
        exit;
    }
}

$groups = $CI->db->get('groups')->result();
foreach ($groups as $group) {
    $check = $CI->db->get_where('access_level', array(
        'group_id' => $group->id,
        'Module' => $module_id,
        'link' => $permission_name
    ))->row();

    $has_journal_entry = $CI->db->get_where('access_level', array(
        'group_id' => $group->id,
        'Module' => $module_id,
        'link' => 'Journal_entry',
        'allow' => 1
    ))->row();

    $allow = $has_journal_entry ? 1 : 0;

    if (!$check) {
        $CI->db->insert('access_level', array(
            'group_id' => $group->id,
            'Module' => $module_id,
            'link' => $permission_name,
            'allow' => $allow
        ));
        $status = $allow ? 'Enabled' : 'Added (disabled)';
        echo "<p class='success'>{$status} for group: " . htmlspecialchars($group->name) . "</p>";
    } elseif ($allow && (int) $check->allow !== 1) {
        $CI->db->where('id', $check->id)->update('access_level', array('allow' => 1));
        echo "<p class='success'>Enabled for group (had Journal_entry): " . htmlspecialchars($group->name) . "</p>";
    } else {
        $state = ((int) $check->allow === 1) ? 'already enabled' : 'already present (disabled)';
        echo "<p>Group " . htmlspecialchars($group->name) . ": {$state}</p>";
    }
}

echo "</div>";

echo "<div class='section'>";
echo "<h3>Role Setup</h3>";
echo "<p>In <strong>Auth → Group Role</strong> under Finance, you can now assign separately:</p>";
echo "<ul>";
echo "<li><strong>Journal_entry</strong> — create journal entries</li>";
echo "<li><strong>Review_journal_entry</strong> — Journal Entry Review &amp; Approval (approve / post / void)</li>";
echo "<li><strong>View_journal_entry</strong> — Journal Entry List</li>";
echo "</ul>";
echo "<p>Groups that already had <strong>Journal_entry</strong> were granted <strong>Review_journal_entry</strong> so they keep review access.</p>";
echo "<h3>Manual SQL (if needed)</h3>";
echo "<pre>INSERT INTO role (Module_id, Name) VALUES (6, 'Review_journal_entry');\n";
echo "INSERT INTO access_level (group_id, Module, link, allow) VALUES (1, 6, 'Review_journal_entry', 1);</pre>";
echo "</div>";
