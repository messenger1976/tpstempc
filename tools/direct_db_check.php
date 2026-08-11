<?php
// Direct database check for users and admin status
echo "<h1>🗄️ Direct Database User Check</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
table { border-collapse: collapse; width: 100%; background: white; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #007bff; color: white; }
tr:nth-child(even) { background: #f2f2f2; }
.status { padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid; }
.success { background: #d4edda; color: #155724; border-color: #28a745; }
.error { background: #f8d7da; color: #721c24; border-color: #dc3545; }
.warning { background: #fff3cd; color: #856404; border-color: #ffc107; }
.info { background: #cce7ff; color: #004085; border-color: #007bff; }
.btn { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 4px; color: white; }
.btn-primary { background: #007bff; }
.btn-danger { background: #dc3545; }
.btn-success { background: #28a745; }
</style>";

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tapstemco";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "<div class='status error'>❌ Database connection failed: " . $conn->connect_error . "</div>";
    echo "<div class='status warning'>";
    echo "<strong>Possible Issues:</strong>";
    echo "<ul>";
    echo "<li>MySQL service not running</li>";
    echo "<li>Wrong database credentials</li>";
    echo "<li>XAMPP not started</li>";
    echo "</ul>";
    echo "</div>";
    exit;
}

echo "<div class='status success'>✅ Connected to database successfully</div>";

echo "<h2>👥 Users in System</h2>";

// Query users and their groups
$sql = "SELECT u.id, u.username, u.email, u.active, u.created_on, g.name as group_name
        FROM users u
        LEFT JOIN users_groups ug ON u.id = ug.user_id
        LEFT JOIN groups g ON ug.group_id = g.id
        ORDER BY u.username, g.name";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[$row['id']]['username'] = $row['username'];
        $users[$row['id']]['email'] = $row['email'];
        $users[$row['id']]['active'] = $row['active'];
        $users[$row['id']]['created_on'] = $row['created_on'];
        if ($row['group_name']) {
            $users[$row['id']]['groups'][] = $row['group_name'];
        }
    }

    echo "<table>";
    echo "<tr><th>Username</th><th>Email</th><th>Status</th><th>Groups</th><th>Admin?</th><th>Created</th></tr>";

    foreach ($users as $user) {
        $groups = isset($user['groups']) ? implode(', ', $user['groups']) : 'No groups';
        $status = $user['active'] ? 'Active' : 'Inactive';
        $is_admin = isset($user['groups']) && (in_array('admin', $user['groups']) || in_array('administrator', $user['groups']));
        $admin_indicator = $is_admin ? '✅ YES' : '❌ No';
        $created_date = date('Y-m-d H:i', strtotime($user['created_on']));

        $row_class = $is_admin ? ' style="background: #e8f5e8;"' : '';

        echo "<tr$row_class>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>$status</td>";
        echo "<td>$groups</td>";
        echo "<td>$admin_indicator</td>";
        echo "<td>$created_date</td>";
        echo "</tr>";
    }

    echo "</table>";

    // Count admins
    $admin_count = 0;
    $admin_usernames = [];
    foreach ($users as $user) {
        if (isset($user['groups']) && (in_array('admin', $user['groups']) || in_array('administrator', $user['groups']))) {
            $admin_count++;
            $admin_usernames[] = $user['username'];
        }
    }

    echo "<div class='status success'>";
    echo "<h3>📊 Summary</h3>";
    echo "<p><strong>Total Users:</strong> " . count($users) . "</p>";
    echo "<p><strong>Admin Users:</strong> $admin_count</p>";
    if (!empty($admin_usernames)) {
        echo "<p><strong>Admin Usernames:</strong> " . implode(', ', $admin_usernames) . "</p>";
    }
    echo "</div>";

} else {
    echo "<div class='status error'>❌ No users found in database</div>";
    echo "<div class='status warning'>";
    echo "<strong>Possible Issues:</strong>";
    echo "<ul>";
    echo "<li>Database tables not created</li>";
    echo "<li>No users registered yet</li>";
    echo "<li>Wrong table structure</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h2>🔑 How to Access Fiscal Year Management</h2>";
echo "<div class='status info'>";
echo "<strong>To access Fiscal Year Management, you need to:</strong>";
echo "<ol>";
echo "<li><strong>Log in</strong> with one of the admin usernames listed above</li>";
echo "<li><strong>Navigate</strong> to Settings in the left menu</li>";
echo "<li><strong>Click Settings</strong> to expand the submenu</li>";
echo "<li><strong>Find 'Fiscal Year Management'</strong> and click it</li>";
echo "</ol>";
echo "</div>";

echo "<div class='status warning'>";
echo "<strong>Don't know admin passwords?</strong>";
echo "<ul>";
echo "<li>Contact your system administrator</li>";
echo "<li>Check system documentation</li>";
echo "<li>Look for default passwords (often 'admin' or 'password')</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='http://localhost/tapstemco' class='btn btn-primary'>Go to Application</a></p>";
echo "<p><a href='http://localhost/tapstemco/auth/login' class='btn btn-primary'>Login Page</a></p>";

$conn->close();
?>
