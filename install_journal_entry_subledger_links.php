<?php
/**
 * Installation Script: Journal Entry Sub-ledger Links
 *
 * Adds optional per-line link fields on general_journal so manual journal
 * vouchers can attach to Customer (AR), Supplier (AP), or Member Loan.
 * On GL post, these copy into general_ledger.customerid / supplierid / LID / etc.
 *
 * Does NOT auto-update invoice balances or loan repayment schedules.
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

echo '<!DOCTYPE html><html><head><title>Journal Entry Sub-ledger Links</title>
<style>
body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5}
.container{max-width:800px;margin:0 auto;background:#fff;padding:20px;border-radius:5px}
.success{background:#d4edda;color:#155724;padding:10px;margin:8px 0;border-left:4px solid #28a745}
.error{background:#f8d7da;color:#721c24;padding:10px;margin:8px 0;border-left:4px solid #dc3545}
.info{background:#d1ecf1;color:#0c5460;padding:10px;margin:8px 0;border-left:4px solid #17a2b8}
</style></head><body><div class="container">
<h1>Journal Entry Sub-ledger Links</h1>';

$table = $mysqli->query("SHOW TABLES LIKE 'general_journal'");
if (!$table || $table->num_rows === 0) {
    echo '<div class="error">Table general_journal not found.</div></div></body></html>';
    $mysqli->close();
    exit;
}

$columns = array(
    'link_type' => "VARCHAR(20) NULL DEFAULT NULL COMMENT 'none|customer|supplier|loan'",
    'customerid' => "VARCHAR(50) NULL DEFAULT NULL",
    'supplierid' => "VARCHAR(50) NULL DEFAULT NULL",
    'LID' => "VARCHAR(50) NULL DEFAULT NULL",
    'PID' => "BIGINT NULL DEFAULT NULL",
    'member_id' => "VARCHAR(50) NULL DEFAULT NULL",
    'invoiceid' => "INT NULL DEFAULT NULL",
);

foreach ($columns as $col => $definition) {
    $check = $mysqli->query("SHOW COLUMNS FROM general_journal LIKE '" . $mysqli->real_escape_string($col) . "'");
    if ($check && $check->num_rows > 0) {
        echo '<div class="info">Column <code>' . htmlspecialchars($col) . '</code> already exists.</div>';
        continue;
    }
    $sql = "ALTER TABLE general_journal ADD COLUMN `$col` $definition";
    if ($mysqli->query($sql)) {
        echo '<div class="success">Added <code>general_journal.' . htmlspecialchars($col) . '</code>.</div>';
    } else {
        echo '<div class="error">Failed to add <code>' . htmlspecialchars($col) . '</code>: ' . htmlspecialchars($mysqli->error) . '</div>';
    }
}

echo '<div class="info">Manual journal lines can now link to Customer, Supplier, or Loan. Links copy to General Ledger on approval. Invoice/loan schedule balances are not auto-updated.</div>';
echo '</div></body></html>';
$mysqli->close();
