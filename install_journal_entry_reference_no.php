<?php
/**
 * Installation Script: Journal Entry Reference #
 *
 * Adds general_journal_entry.reference_no for optional voucher / document
 * reference numbers on manual journal entries (Finance → Journal Entry).
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
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Journal Entry Reference # - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #17a2b8; }
    </style>
</head>
<body>
<div class=\"container\">
    <h1>Journal Entry Reference #</h1>
";

$table_check = $mysqli->query("SHOW TABLES LIKE 'general_journal_entry'");
if (!$table_check || $table_check->num_rows === 0) {
    echo '<div class="error">Table <code>general_journal_entry</code> not found.</div></div></body></html>';
    $mysqli->close();
    exit;
}

$col_check = $mysqli->query("SHOW COLUMNS FROM general_journal_entry LIKE 'reference_no'");
if ($col_check && $col_check->num_rows > 0) {
    echo '<div class="info">Column <code>reference_no</code> already exists. Nothing to do.</div>';
} else {
    $sql = "ALTER TABLE general_journal_entry ADD COLUMN reference_no VARCHAR(100) NULL DEFAULT NULL AFTER description";
    if ($mysqli->query($sql)) {
        echo '<div class="success">Added column <code>general_journal_entry.reference_no</code>.</div>';
    } else {
        echo '<div class="error">Failed to add column: ' . htmlspecialchars($mysqli->error) . '</div>';
    }
}

echo '<div class="info">Manual journal entries can now store an optional Reference # (voucher / document number).</div>';
echo '</div></body></html>';
$mysqli->close();
