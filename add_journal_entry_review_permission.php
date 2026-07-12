<?php
/**
 * Add Review_journal_entry permission to Module 6 (Finance)
 * Run once: http://your-domain/add_journal_entry_review_permission.php
 * Or use sql/add_journal_entry_review_permission.sql in phpMyAdmin.
 * SECURITY: Delete or restrict access after use!
 */

if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}
require_once('application/config/database.php');

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

$module_id = 6;
$permission_name = 'Review_journal_entry';

echo "<!DOCTYPE html><html><head><title>Add Review_journal_entry</title>
<style>
body{font-family:Arial;margin:20px;background:#f5f5f5;}
.success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;}
.section{background:white;padding:15px;margin:10px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
</style></head><body>";
echo "<h1>Adding Journal Entry Review Permission</h1>";

$module = $mysqli->query("SELECT id FROM `module` WHERE id = {$module_id}")->fetch_assoc();
if (!$module) {
    echo "<p class='error'>Module 6 (Finance) not found.</p></body></html>";
    exit;
}

echo "<div class='section'><h3>" . htmlspecialchars($permission_name) . "</h3>";

$existing = $mysqli->query("SELECT id FROM `role` WHERE Module_id = {$module_id} AND Name = '" . $mysqli->real_escape_string($permission_name) . "'")->fetch_assoc();
if ($existing) {
    echo "<p class='success'>Permission already exists (role ID: {$existing['id']})</p>";
} else {
    if ($mysqli->query("INSERT INTO `role` (`Module_id`, `Name`) VALUES ({$module_id}, '" . $mysqli->real_escape_string($permission_name) . "')")) {
        echo "<p class='success'>Added permission to role table.</p>";
    } else {
        echo "<p class='error'>Failed: " . htmlspecialchars($mysqli->error) . "</p></div></body></html>";
        exit;
    }
}

$groups = $mysqli->query("SELECT id, name FROM `groups`");
while ($group = $groups->fetch_assoc()) {
    $gid = (int) $group['id'];
    $check = $mysqli->query("SELECT id, allow FROM `access_level` WHERE group_id = {$gid} AND Module = {$module_id} AND link = '" . $mysqli->real_escape_string($permission_name) . "'")->fetch_assoc();

    $has_je = $mysqli->query("SELECT id FROM `access_level` WHERE group_id = {$gid} AND Module = {$module_id} AND link = 'Journal_entry' AND allow = 1")->fetch_assoc();
    $allow = $has_je ? 1 : 0;

    if (!$check) {
        $mysqli->query("INSERT INTO `access_level` (`group_id`, `Module`, `link`, `allow`) VALUES ({$gid}, {$module_id}, '" . $mysqli->real_escape_string($permission_name) . "', {$allow})");
        $status = $allow ? 'Enabled' : 'Added (disabled)';
        echo "<p class='success'>{$status} for group: " . htmlspecialchars($group['name']) . "</p>";
    } elseif ($allow && (int) $check['allow'] !== 1) {
        $mysqli->query("UPDATE `access_level` SET allow = 1 WHERE id = " . (int) $check['id']);
        echo "<p class='success'>Enabled for group (had Journal_entry): " . htmlspecialchars($group['name']) . "</p>";
    } else {
        $state = ((int) $check['allow'] === 1) ? 'already enabled' : 'already present (disabled)';
        echo "<p>Group " . htmlspecialchars($group['name']) . ": {$state}</p>";
    }
}

echo "</div><div class='section'>";
echo "<p>In <strong>Auth → Group Role</strong> under Finance, assign <strong>Review_journal_entry</strong> separately from <strong>Journal_entry</strong>.</p>";
echo "<p>Delete this script after use.</p></div></body></html>";

$mysqli->close();
