<?php
/**
 * Add dedicated void_transaction roles for Loan (5), Share (4), Contribution (2).
 * Seeds access_level for all groups (admin / existing voiders = enabled; others = off).
 *
 * Run once: http://your-domain/tools/add_cross_module_void_permissions.php
 * Or: sql/add_cross_module_void_permissions.sql
 * SECURITY: Delete or restrict access after use!
 */

if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}
require_once __DIR__ . '/../application/config/database.php';

$db_config = $db['default'];
$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}
$mysqli->set_charset(isset($db_config['char_set']) ? $db_config['char_set'] : 'utf8');

$targets = array(
    array('module_id' => 5, 'module_label' => 'Loan', 'permission' => 'void_transaction'),
    array('module_id' => 4, 'module_label' => 'Share', 'permission' => 'void_transaction'),
    array('module_id' => 2, 'module_label' => 'Contribution', 'permission' => 'void_transaction'),
);

echo "<!DOCTYPE html><html><head><title>Cross-module void permissions</title>
<style>
body{font-family:Arial;margin:20px;background:#f5f5f5;}
.success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warn{color:#b36b00;}
.section{background:white;padding:15px;margin:10px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background:#4CAF50;color:#fff;}
</style></head><body>";
echo "<h1>Add dedicated void permissions</h1>";

function group_should_get_void($mysqli, $group_id) {
    // Default deny. Only admin + accounting-style groups get void on install.
    // Cashiers / encoders must be granted manually in Auth → Group Role.
    $gid = (int) $group_id;
    if ($gid === 1) {
        return true;
    }
    $row = $mysqli->query("SELECT name FROM groups WHERE id = {$gid} LIMIT 1")->fetch_assoc();
    if (!$row || empty($row['name'])) {
        return false;
    }
    $name = strtolower(trim($row['name']));
    $trusted = array(
        'accounts_department',
        'accounts',
        'accountant',
        'bookeeper',
        'bookkeeper',
        'general_manager',
    );
    return in_array($name, $trusted, true);
}

foreach ($targets as $target) {
    $module_id = (int) $target['module_id'];
    $permission = $mysqli->real_escape_string($target['permission']);
    $label = htmlspecialchars($target['module_label'], ENT_QUOTES, 'UTF-8');

    echo "<div class='section'><h3>{$label} (Module {$module_id}) → {$permission}</h3>";

    $module = $mysqli->query("SELECT id, Name FROM module WHERE id = {$module_id}")->fetch_assoc();
    if (!$module) {
        echo "<p class='error'>Module {$module_id} not found. Skipped.</p></div>";
        continue;
    }

    $existing = $mysqli->query("SELECT id FROM role WHERE Module_id = {$module_id} AND Name = '{$permission}'")->fetch_assoc();
    if ($existing) {
        echo "<p class='success'>Role already exists (ID {$existing['id']}).</p>";
    } else {
        if ($mysqli->query("INSERT INTO role (Module_id, Name) VALUES ({$module_id}, '{$permission}')")) {
            echo "<p class='success'>Added role.</p>";
        } else {
            echo "<p class='error'>Failed to add role: " . htmlspecialchars($mysqli->error) . "</p></div>";
            continue;
        }
    }

    echo "<table><tr><th>Group</th><th>Allow</th><th>Action</th></tr>";
    $groups = $mysqli->query("SELECT id, name FROM groups ORDER BY id ASC");
    while ($group = $groups->fetch_assoc()) {
        $gid = (int) $group['id'];
        $allow = group_should_get_void($mysqli, $gid) ? 1 : 0;
        $check = $mysqli->query("SELECT id, allow FROM access_level WHERE group_id = {$gid} AND Module = {$module_id} AND link = '{$permission}'")->fetch_assoc();
        $gname = htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8');
        if (!$check) {
            $mysqli->query("INSERT INTO access_level (group_id, Module, link, allow) VALUES ({$gid}, {$module_id}, '{$permission}', {$allow})");
            $action = 'Inserted';
        } else {
            // Do not overwrite an explicit existing row (admin may have tuned it).
            $allow = (int) $check['allow'];
            $action = 'Already present (left unchanged)';
        }
        $allow_label = $allow ? 'Yes' : 'No';
        echo "<tr><td>{$gname} (#{$gid})</td><td>{$allow_label}</td><td>{$action}</td></tr>";
    }
    echo "</table></div>";
}

echo "<div class='section'>";
echo "<p><strong>Next:</strong> Auth → Group Role — enable <code>void_transaction</code> under Loan / Share / Contribution only for trusted groups (e.g. Accountant).</p>";
echo "<p>Also keep Finance <code>Void_transactions</code> and Savings <code>void_transaction</code> off for cashiers.</p>";
echo "<p class='warn'>Delete this script after use.</p>";
echo "</div></body></html>";

$mysqli->close();
