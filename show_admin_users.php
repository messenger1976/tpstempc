<?php
echo "<h1>👑 Admin Users in System</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
table { border-collapse: collapse; width: 100%; background: white; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background: #007bff; color: white; }
tr:nth-child(even) { background: #f2f2f2; }
.status { padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid; }
.success { background: #d4edda; color: #155724; border-color: #28a745; }
.warning { background: #fff3cd; color: #856404; border-color: #ffc107; }
.btn { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 4px; color: white; }
.btn-primary { background: #007bff; }
</style>";

// Include CI bootstrap
require_once 'index.php';

// Check if current user is admin
$is_current_admin = $this->ion_auth->logged_in() && $this->ion_auth->is_admin();

if (!$is_current_admin) {
    echo "<div class='status warning'>";
    echo "<h3>⚠️ Access Restricted</h3>";
    echo "<p>This page shows admin users in the system. You need admin access to view it.</p>";
    echo "<p>If you're an admin, please log in with admin credentials first.</p>";
    echo "</div>";
    echo "<p><a href='" . site_url('auth/login') . "' class='btn btn-primary'>Login as Admin</a></p>";
    exit;
}

echo "<div class='status success'>";
echo "<h3>✅ Admin Access Granted</h3>";
echo "<p>You can view all users and their admin status.</p>";
echo "</div>";

echo "<h2>👥 All Users in System</h2>";

// Get all users
$this->db->select('u.id, u.username, u.email, u.active, g.name as group_name');
$this->db->from('users u');
$this->db->join('users_groups ug', 'u.id = ug.user_id');
$this->db->join('groups g', 'ug.group_id = g.id');
$this->db->order_by('u.username');
$query = $this->db->get();

$users = [];
foreach ($query->result() as $row) {
    $users[$row->id]['username'] = $row->username;
    $users[$row->id]['email'] = $row->email;
    $users[$row->id]['active'] = $row->active;
    $users[$row->id]['groups'][] = $row->group_name;
}

echo "<table>";
echo "<tr><th>Username</th><th>Email</th><th>Status</th><th>Groups</th><th>Admin Access</th></tr>";

$current_user_id = $this->ion_auth->user()->row()->id;

foreach ($users as $user_id => $user) {
    $is_admin = in_array('admin', $user['groups']) || in_array('administrator', $user['groups']);
    $status = $user['active'] ? 'Active' : 'Inactive';
    $groups = implode(', ', $user['groups']);
    $admin_indicator = $is_admin ? '✅ ADMIN' : '❌ Regular User';

    $highlight = '';
    if ($user_id == $current_user_id) {
        $highlight = ' style="background: #e8f5e8; font-weight: bold;"';
    }

    echo "<tr$highlight>";
    echo "<td>{$user['username']}</td>";
    echo "<td>{$user['email']}</td>";
    echo "<td>$status</td>";
    echo "<td>$groups</td>";
    echo "<td>$admin_indicator</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>🎯 Admin Users Summary</h2>";
$admin_count = 0;
$admin_list = [];
foreach ($users as $user) {
    if (in_array('admin', $user['groups']) || in_array('administrator', $user['groups'])) {
        $admin_count++;
        $admin_list[] = $user['username'];
    }
}

echo "<div class='status success'>";
echo "<p><strong>Total Admin Users:</strong> $admin_count</p>";
if (!empty($admin_list)) {
    echo "<p><strong>Admin Usernames:</strong> " . implode(', ', $admin_list) . "</p>";
}
echo "</div>";

echo "<h2>🔑 How to Login as Admin</h2>";
echo "<div class='status warning'>";
echo "<p><strong>To access Fiscal Year Management:</strong></p>";
echo "<ol>";
echo "<li>Log out of your current account</li>";
echo "<li>Use one of the admin usernames listed above</li>";
echo "<li>Enter the correct admin password</li>";
echo "<li>Navigate to Settings → Fiscal Year Management</li>";
echo "</ol>";
echo "</div>";

echo "<h2>❓ Don't Know Admin Password?</h2>";
echo "<div class='status warning'>";
echo "<p><strong>If you don't know the admin password:</strong></p>";
echo "<ul>";
echo "<li>Contact your system administrator</li>";
echo "<li>Check system documentation</li>";
echo "<li>Look for installation notes</li>";
echo "<li>Check with the person who set up the system</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='" . site_url() . "' class='btn btn-primary'>← Back to Application</a></p>";
?>
