<?php
/**
 * Installation Script: Manual Map Pins
 *
 * - members_contact: map_lat, map_lng, map_updated_at, map_updated_by (per-member manual pin)
 * - companyinfo:     map_lat, map_lng, map_updated_at, map_updated_by (office pin)
 * - member_address_geocode: ensures table exists; adds updated_by
 * - role / access_level: Manage_map_locations (Module 9) allowed for the admin group
 *
 * Safe to run more than once.
 *
 * Usage:
 *   php tools/install_map_locations.php
 *   php tools/install_map_locations.php --enable-all   (allow the role for every group)
 *
 * Browser: http://localhost/tapstemco/tools/install_map_locations.php[?enable_all=1]
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
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');

$is_cli = (php_sapi_name() === 'cli');
if ($is_cli) {
    $enable_all = in_array('--enable-all', isset($argv) ? $argv : array(), true);
} else {
    $enable_all = isset($_GET['enable_all']) && $_GET['enable_all'] == '1';
}

$module_id = 9;
$permission_name = 'Manage_map_locations';

function out($msg, $is_cli, $class = 'info') {
    if ($is_cli) {
        echo html_entity_decode(strip_tags($msg), ENT_QUOTES, 'UTF-8') . PHP_EOL;
    } else {
        echo "<div class='$class'>" . $msg . "</div>";
    }
}

function column_exists($mysqli, $table, $column) {
    $t = $mysqli->real_escape_string($table);
    $c = $mysqli->real_escape_string($column);
    $res = $mysqli->query("SHOW COLUMNS FROM `$t` LIKE '$c'");
    return $res && $res->num_rows > 0;
}

function table_exists($mysqli, $table) {
    $t = $mysqli->real_escape_string($table);
    $res = $mysqli->query("SHOW TABLES LIKE '$t'");
    return $res && $res->num_rows > 0;
}

function add_column($mysqli, $table, $column, $definition, $is_cli, &$all_ok) {
    if (column_exists($mysqli, $table, $column)) {
        out("$table.$column already exists.", $is_cli);
        return;
    }
    if ($mysqli->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition")) {
        out("Added $table.$column.", $is_cli, 'success');
    } else {
        out("ERROR adding $table.$column: " . htmlspecialchars($mysqli->error), $is_cli, 'error');
        $all_ok = false;
    }
}

if (!$is_cli) {
    echo "<!DOCTYPE html><html><head><title>Manual Map Pins - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; border-bottom: 2px solid #1ab394; padding-bottom: 10px; }
        .success { background: #d4edda; color: #155724; padding: 8px 10px; border-radius: 4px; margin: 6px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 8px 10px; border-radius: 4px; margin: 6px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 8px 10px; border-radius: 4px; margin: 6px 0; }
    </style></head><body><div class='container'>
    <h1>Manual Map Pins - Installation</h1>";
}

$all_ok = true;

out($is_cli ? "Step 1: members_contact pin columns" : "<strong>Step 1: members_contact pin columns</strong>", $is_cli);
add_column($mysqli, 'members_contact', 'map_lat', 'DECIMAL(10,7) NULL DEFAULT NULL', $is_cli, $all_ok);
add_column($mysqli, 'members_contact', 'map_lng', 'DECIMAL(10,7) NULL DEFAULT NULL', $is_cli, $all_ok);
add_column($mysqli, 'members_contact', 'map_updated_at', 'DATETIME NULL DEFAULT NULL', $is_cli, $all_ok);
add_column($mysqli, 'members_contact', 'map_updated_by', 'INT(11) NULL DEFAULT NULL', $is_cli, $all_ok);

out($is_cli ? "Step 2: companyinfo office pin columns" : "<strong>Step 2: companyinfo office pin columns</strong>", $is_cli);
add_column($mysqli, 'companyinfo', 'map_lat', 'DECIMAL(10,7) NULL DEFAULT NULL', $is_cli, $all_ok);
add_column($mysqli, 'companyinfo', 'map_lng', 'DECIMAL(10,7) NULL DEFAULT NULL', $is_cli, $all_ok);
add_column($mysqli, 'companyinfo', 'map_updated_at', 'DATETIME NULL DEFAULT NULL', $is_cli, $all_ok);
add_column($mysqli, 'companyinfo', 'map_updated_by', 'INT(11) NULL DEFAULT NULL', $is_cli, $all_ok);

out($is_cli ? "Step 3: member_address_geocode" : "<strong>Step 3: member_address_geocode</strong>", $is_cli);
$create_sql = "CREATE TABLE IF NOT EXISTS `member_address_geocode` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `address_key` VARCHAR(255) NOT NULL,
  `address_raw` VARCHAR(255) NOT NULL,
  `lat` DECIMAL(10,7) NULL DEFAULT NULL,
  `lng` DECIMAL(10,7) NULL DEFAULT NULL,
  `geocode_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `source` VARCHAR(50) NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_address_key` (`address_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
if ($mysqli->query($create_sql)) {
    out("member_address_geocode is ready.", $is_cli, 'success');
} else {
    out("ERROR: " . htmlspecialchars($mysqli->error), $is_cli, 'error');
    $all_ok = false;
}
add_column($mysqli, 'member_address_geocode', 'updated_by', 'INT(11) NULL DEFAULT NULL', $is_cli, $all_ok);

out($is_cli ? "Step 4: $permission_name permission" : "<strong>Step 4: $permission_name permission</strong>", $is_cli);
$perm_esc = $mysqli->real_escape_string($permission_name);
if (!table_exists($mysqli, 'role') || !table_exists($mysqli, 'access_level')) {
    out("role / access_level tables not found; skipped permission setup.", $is_cli, 'error');
    $all_ok = false;
} else {
    $role = $mysqli->query("SELECT id FROM role WHERE Module_id = $module_id AND Name = '$perm_esc' LIMIT 1");
    if ($role && $role->num_rows > 0) {
        out("Role '$permission_name' already exists.", $is_cli);
    } elseif ($mysqli->query("INSERT INTO role (Module_id, Name) VALUES ($module_id, '$perm_esc')")) {
        out("Added role '$permission_name' (Module $module_id).", $is_cli, 'success');
    } else {
        out("ERROR adding role: " . htmlspecialchars($mysqli->error), $is_cli, 'error');
        $all_ok = false;
    }

    $group_sql = $enable_all ? "SELECT id, name FROM `groups`" : "SELECT id, name FROM `groups` WHERE name = 'admin'";
    $groups = $mysqli->query($group_sql);
    $granted = 0;
    while ($groups && ($g = $groups->fetch_assoc())) {
        $gid = intval($g['id']);
        $exists = $mysqli->query("SELECT allow FROM access_level
                                  WHERE group_id = $gid AND Module = $module_id AND link = '$perm_esc' LIMIT 1");
        if ($exists && $exists->num_rows > 0) {
            $mysqli->query("UPDATE access_level SET allow = 1
                            WHERE group_id = $gid AND Module = $module_id AND link = '$perm_esc'");
        } else {
            $mysqli->query("INSERT INTO access_level (group_id, Module, link, allow)
                            VALUES ($gid, $module_id, '$perm_esc', 1)");
        }
        $granted++;
        out("Allowed for group: " . htmlspecialchars($g['name']), $is_cli, 'success');
    }
    if ($granted === 0) {
        out("No matching group found to grant the permission.", $is_cli, 'error');
    }
    if (!$enable_all) {
        out("Assign it to other groups via User Management &rarr; Privileges (Settings &rarr; $permission_name)"
            . ($is_cli ? ", or re-run with --enable-all." : ", or <a href='?enable_all=1'>enable for all groups</a>."), $is_cli);
    }
}

out($all_ok ? "Done." : "Finished with errors - see above.", $is_cli, $all_ok ? 'success' : 'error');

if (!$is_cli) {
    echo "</div></body></html>";
}

$mysqli->close();
