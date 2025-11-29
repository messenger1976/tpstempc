<?php
/**
 * Check User ID 98 Issue
 * 
 * Diagnostic script to check database state
 * SECURITY: Delete after use!
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
define('APPPATH', realpath(dirname(__FILE__) . '/application/') . '/');
define('SYSDIR', 'system');

require_once BASEPATH . 'core/CodeIgniter.php';

$CI =& get_instance();

echo "<h2>Database Diagnostic - User ID 98</h2>";

// Check if user 98 exists
echo "<h3>1. Check if User ID 98 exists</h3>";
$user = $CI->db->get_where('users', array('id' => 98))->row();
if ($user) {
    echo "✅ User ID 98 EXISTS<br>";
    echo "Username: " . htmlspecialchars($user->username) . "<br>";
    echo "Email: " . htmlspecialchars($user->email) . "<br>";
} else {
    echo "❌ User ID 98 does NOT exist in users table<br>";
}

// Check users_groups for user 98
echo "<h3>2. Check users_groups for User ID 98</h3>";
$groups = $CI->db->get_where('users_groups', array('user_id' => 98))->result();
if (!empty($groups)) {
    echo "Found " . count($groups) . " group(s) for user 98:<br>";
    foreach ($groups as $group) {
        echo "- Group ID: " . $group->group_id . "<br>";
    }
} else {
    echo "No groups found for user 98<br>";
}

// Check last inserted user ID
echo "<h3>3. Last Inserted User ID</h3>";
$last_user = $CI->db->order_by('id', 'DESC')->limit(1)->get('users')->row();
if ($last_user) {
    echo "Last user ID: " . $last_user->id . "<br>";
    echo "Username: " . htmlspecialchars($last_user->username) . "<br>";
} else {
    echo "No users found<br>";
}

// Check for orphaned users_groups entries
echo "<h3>4. Check for Orphaned users_groups (user_id doesn't exist)</h3>";
$sql = "SELECT ug.* FROM users_groups ug LEFT JOIN users u ON ug.user_id = u.id WHERE u.id IS NULL";
$orphaned = $CI->db->query($sql)->result();
if (!empty($orphaned)) {
    echo "⚠️ Found " . count($orphaned) . " orphaned entries:<br>";
    foreach ($orphaned as $orphan) {
        echo "- user_id: " . $orphan->user_id . ", group_id: " . $orphan->group_id . "<br>";
    }
    echo "<p><strong>Recommendation:</strong> Clean up these orphaned entries.</p>";
} else {
    echo "✅ No orphaned entries found<br>";
}

echo "<hr>";
echo "<p><strong>Possible Solutions:</strong></p>";
echo "<ol>";
echo "<li>If user 98 doesn't exist, the user creation might have failed. Check the user creation code.</li>";
echo "<li>If there are orphaned entries, clean them up with: DELETE FROM users_groups WHERE user_id NOT IN (SELECT id FROM users)</li>";
echo "<li>Check if there's a transaction rollback happening during member creation</li>";
echo "</ol>";

?>

