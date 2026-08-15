<?php
/**
 * Turn off Savings void_transaction + Finance Void_transactions for cashier/encoder-style groups.
 * Trusted accounting groups are left unchanged.
 * Run: php tools/fix_cashier_void_off.php
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

$deny_names = array(
    'cashiers',
    'cashier',
    'encoder',
    'encoders',
    'members',
    'customer_care',
    'demo',
    'demousers',
    'deactivated_users',
    'dvisales',
);

$groups = $m->query("SELECT id, name FROM groups");
while ($g = $groups->fetch_assoc()) {
    $name = strtolower(trim($g['name']));
    $gid = (int) $g['id'];
    if ($gid === 1 || !in_array($name, $deny_names, true)) {
        continue;
    }
    $m->query("UPDATE access_level SET allow = 0 WHERE group_id = {$gid} AND Module = 3 AND link = 'void_transaction'");
    $m->query("UPDATE access_level SET allow = 0 WHERE group_id = {$gid} AND Module = 6 AND link = 'Void_transactions'");
    echo "Denied void for {$g['name']}\n";
}
$m->close();
