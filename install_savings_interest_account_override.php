<?php
/**
 * Installation Script: Per-account interest frequency override
 *
 * Adds members_account.interest_frequency so a member savings account can
 * override the product default (saving_account_type.interest_frequency).
 *
 * Values:
 *   NULL / empty  = inherit product default (backward compatible)
 *   NONE          = no automatic interest for this account
 *   MONTHLY       = post monthly for this account
 *   QUARTERLY     = post quarterly for this account
 *
 * Rate, basis, and min balance remain on the savings account type.
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
    <title>Savings Interest Account Override - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .step { margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 4px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Per-Account Interest Frequency Override - Installation</h1>";

$all_ok = true;

echo "<div class='step'>";
echo "<h2>Step 1: Add interest_frequency to members_account</h2>";

$check = $mysqli->query("SHOW COLUMNS FROM `members_account` LIKE 'interest_frequency'");
if ($check && $check->num_rows > 0) {
    echo "<div class='info'>Column 'interest_frequency' already exists on 'members_account'. Skipped.</div>";
} else {
    $sql = "ALTER TABLE `members_account`
            ADD COLUMN `interest_frequency` VARCHAR(10) NULL DEFAULT NULL
            COMMENT 'NULL=inherit product default; NONE/MONTHLY/QUARTERLY=override'
            AFTER `account_cat`";
    if ($mysqli->query($sql) === TRUE) {
        echo "<div class='success'>Column 'interest_frequency' added to 'members_account'.</div>";
    } else {
        echo "<div class='error'>Error adding column: " . htmlspecialchars($mysqli->error) . "</div>";
        $all_ok = false;
    }
}
echo "</div>";

echo "<div class='step'>";
echo "<h2>Step 2: Summary</h2>";
if ($all_ok) {
    echo "<div class='success'>";
    echo "<strong>Installation Complete!</strong><br><br>";
    echo "<strong>How to use:</strong><br>";
    echo "1. Keep product defaults under Settings &gt; Saving Account Types (rate, frequency, basis, min balance).<br>";
    echo "2. On Create/Edit Savings Account, optionally set Interest Frequency override.<br>";
    echo "3. Leave override as &quot;Use product default&quot; for normal accounts — behavior stays unchanged.<br>";
    echo "4. On Interest Posting, choose the posting frequency (Monthly or Quarterly); only accounts whose effective frequency matches are included.<br>";
    echo "</div>";
} else {
    echo "<div class='error'>Installation incomplete. Fix the errors above and re-run.</div>";
}
echo "</div></div></body></html>";

$mysqli->close();
?>
