<?php
/**
 * One-time tighten: set Loan/Share/Contribution void_transaction to trusted groups only.
 * Run: php tools/fix_void_permissions_trusted_only.php
 */
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}
require_once __DIR__ . '/../application/config/database.php';
$c = $db['default'];
$m = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($m->connect_error) {
    die($m->connect_error);
}
$m->set_charset('utf8');

$trusted_names = array(
    'admin',
    'accounts_department',
    'accounts',
    'accountant',
    'bookeeper',
    'bookkeeper',
    'general_manager',
);

$m->query("UPDATE access_level SET allow = 0 WHERE Module IN (2,4,5) AND link = 'void_transaction'");

$groups = $m->query("SELECT id, name FROM groups");
while ($g = $groups->fetch_assoc()) {
    $name = strtolower(trim($g['name']));
    $gid = (int) $g['id'];
    $allow = ($gid === 1 || in_array($name, $trusted_names, true)) ? 1 : 0;
    if ($allow) {
        $m->query("UPDATE access_level SET allow = 1 WHERE group_id = {$gid} AND Module IN (2,4,5) AND link = 'void_transaction'");
    }
    echo $g['name'] . " => " . ($allow ? 'ON' : 'off') . PHP_EOL;
}
$m->close();
