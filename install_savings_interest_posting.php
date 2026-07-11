<?php
/**
 * Installation Script for Automated Savings Interest Posting (Monthly / Quarterly)
 *
 * This script:
 * 1. Adds interest configuration columns to 'saving_account_type'
 *    (interest_frequency, interest_basis, interest_min_balance)
 * 2. Creates the 'savings_interest_posting' table (posting log / duplicate guard)
 * 3. Registers the 'Interest_posting' role under the Savings module (Module_id = 3)
 *    and grants it to all existing user groups
 */

// Include database configuration (define BASEPATH to pass the direct-access guard)
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
    <title>Savings Interest Posting - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #ffc107; }
        .step { margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 4px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Automated Savings Interest Posting - Installation</h1>";

$all_ok = true;

// -----------------------------------------------------------------------
// Step 1: Add interest configuration columns to saving_account_type
// -----------------------------------------------------------------------
echo "<div class='step'>";
echo "<h2>Step 1: Adding interest configuration columns to 'saving_account_type'</h2>";

$columns_to_add = array(
    'interest_frequency' => "ALTER TABLE `saving_account_type` ADD COLUMN `interest_frequency` VARCHAR(10) NOT NULL DEFAULT 'NONE' AFTER `interest_rate`",
    'interest_basis' => "ALTER TABLE `saving_account_type` ADD COLUMN `interest_basis` VARCHAR(10) NOT NULL DEFAULT 'ADB' AFTER `interest_frequency`",
    'interest_min_balance' => "ALTER TABLE `saving_account_type` ADD COLUMN `interest_min_balance` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `interest_basis`",
);

foreach ($columns_to_add as $column => $sql) {
    $check = $mysqli->query("SHOW COLUMNS FROM `saving_account_type` LIKE '" . $column . "'");
    if ($check && $check->num_rows > 0) {
        echo "<div class='info'>Column '{$column}' already exists. Skipped.</div>";
    } else {
        if ($mysqli->query($sql) === TRUE) {
            echo "<div class='success'>Column '{$column}' added successfully.</div>";
        } else {
            echo "<div class='error'>Error adding column '{$column}': " . $mysqli->error . "</div>";
            $all_ok = false;
        }
    }
}
echo "</div>";

// -----------------------------------------------------------------------
// Step 2: Create savings_interest_posting table
// -----------------------------------------------------------------------
echo "<div class='step'>";
echo "<h2>Step 2: Creating 'savings_interest_posting' table</h2>";

$check = $mysqli->query("SHOW TABLES LIKE 'savings_interest_posting'");
if ($check && $check->num_rows > 0) {
    echo "<div class='info'>Table 'savings_interest_posting' already exists. Skipped.</div>";
} else {
    $create_sql = "CREATE TABLE `savings_interest_posting` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `PIN` VARCHAR(20) NOT NULL,
        `account` VARCHAR(50) NOT NULL,
        `account_cat` VARCHAR(50) NOT NULL,
        `period_type` VARCHAR(10) NOT NULL COMMENT 'MONTHLY or QUARTERLY',
        `period_start` DATE NOT NULL,
        `period_end` DATE NOT NULL,
        `basis` VARCHAR(10) NOT NULL COMMENT 'ADB, LOWEST or EOP',
        `annual_rate` DECIMAL(8,4) NOT NULL DEFAULT 0,
        `base_balance` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `days` INT(5) NOT NULL DEFAULT 0,
        `interest_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `receipt` VARCHAR(50) NULL,
        `status` VARCHAR(10) NOT NULL DEFAULT 'POSTED' COMMENT 'POSTED or VOIDED',
        `createdby` INT(11) NULL,
        `createdon` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_account_period` (`PIN`, `account`, `period_start`, `period_end`),
        KEY `idx_receipt` (`receipt`),
        KEY `idx_account_cat` (`PIN`, `account_cat`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    if ($mysqli->query($create_sql) === TRUE) {
        echo "<div class='success'>Table 'savings_interest_posting' created successfully.</div>";
    } else {
        echo "<div class='error'>Error creating table: " . $mysqli->error . "</div>";
        $all_ok = false;
    }
}
echo "</div>";

// -----------------------------------------------------------------------
// Step 3: Register 'Interest_posting' role under Savings module (Module 3)
// -----------------------------------------------------------------------
echo "<div class='step'>";
echo "<h2>Step 3: Registering 'Interest_posting' permission (Savings module)</h2>";

$role_check = $mysqli->query("SELECT id FROM `role` WHERE `Module_id` = 3 AND `Name` = 'Interest_posting'");
if ($role_check && $role_check->num_rows > 0) {
    echo "<div class='info'>Role 'Interest_posting' already exists. Skipped.</div>";
} else {
    if ($mysqli->query("INSERT INTO `role` (`Module_id`, `Name`) VALUES (3, 'Interest_posting')") === TRUE) {
        echo "<div class='success'>Role 'Interest_posting' registered under the Savings module.</div>";
    } else {
        echo "<div class='error'>Error inserting role: " . $mysqli->error . "</div>";
        $all_ok = false;
    }
}

// Grant the permission to all existing groups (allow = 1)
$groups = $mysqli->query("SELECT id FROM `groups`");
if ($groups) {
    $granted = 0;
    while ($group = $groups->fetch_assoc()) {
        $gid = (int) $group['id'];
        $exists = $mysqli->query("SELECT id FROM `access_level` WHERE `group_id` = {$gid} AND `Module` = 3 AND `link` = 'Interest_posting'");
        if ($exists && $exists->num_rows > 0) {
            continue;
        }
        if ($mysqli->query("INSERT INTO `access_level` (`group_id`, `Module`, `link`, `allow`) VALUES ({$gid}, 3, 'Interest_posting', 1)") === TRUE) {
            $granted++;
        } else {
            echo "<div class='warning'>Could not grant permission to group {$gid}: " . $mysqli->error . "</div>";
        }
    }
    echo "<div class='success'>Permission granted to {$granted} user group(s).</div>";
} else {
    echo "<div class='warning'>Could not read 'groups' table: " . $mysqli->error . "</div>";
}
echo "</div>";

// -----------------------------------------------------------------------
// Step 4: Summary
// -----------------------------------------------------------------------
echo "<div class='step'>";
echo "<h2>Step 4: Installation Summary</h2>";

if ($all_ok) {
    echo "<div class='success'>";
    echo "<strong>Installation Complete!</strong><br><br>";
    echo "<strong>Next Steps:</strong><br>";
    echo "1. Go to Settings &gt; Saving Account Types and configure for each product: Interest Rate (% per annum), Interest Frequency (Monthly/Quarterly), Computation Basis, and Minimum Balance to Earn Interest.<br>";
    echo "2. Ensure each savings account type has its GL accounts set (Account Setup and Account Setup for Interest Rate).<br>";
    echo "3. Use Savings &gt; Interest Posting to preview and post interest for a completed period.<br>";
    echo "4. You may disable the 'Interest_posting' permission per group in User Manager &gt; Assign Privilege.<br>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<strong>Installation Incomplete</strong><br><br>";
    echo "Please resolve the errors above and re-run this script.";
    echo "</div>";
}

echo "</div></div></body></html>";

$mysqli->close();
?>
