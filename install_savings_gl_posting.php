<?php
/**
 * Installation Script for Savings Account Auto-Posting to GL
 * 
 * This script:
 * 1. Checks if Journal ID 9 (Savings Journal) exists
 * 2. Creates it if it doesn't exist
 * 3. Verifies the configuration
 */

// Include database configuration
require_once('application/config/database.php');

// Get database connection settings
$db_config = $db['default'];

// Create database connection
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
    <title>Savings Account GL Posting - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .step { margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 4px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>💰 Savings Account Auto-Posting to GL - Installation</h1>";

// Step 1: Check if journal table exists
echo "<div class='step'>";
echo "<h2>Step 1: Checking Journal Table</h2>";

$result = $mysqli->query("SHOW TABLES LIKE 'journal'");
if ($result->num_rows == 0) {
    echo "<div class='error'>❌ Journal table does not exist. Please ensure your database is properly set up.</div>";
    echo "</div></div></body></html>";
    exit;
}

echo "<div class='success'>✅ Journal table exists</div>";

// Step 2: Check existing journal IDs
echo "<h2>Step 2: Checking Existing Journal Types</h2>";

$result = $mysqli->query("SELECT id, type FROM journal ORDER BY id");
$journals = array();
while ($row = $result->fetch_assoc()) {
    $journals[$row['id']] = $row['type'];
}

if (!empty($journals)) {
    echo "<table><tr><th>Journal ID</th><th>Journal Type</th></tr>";
    foreach ($journals as $id => $type) {
        echo "<tr><td>{$id}</td><td>" . htmlspecialchars($type) . "</td></tr>";
    }
    echo "</table>";
}

// Step 3: Check if Journal ID 9 exists
echo "<h2>Step 3: Checking Journal ID 9 (Savings Journal)</h2>";

if (isset($journals[9])) {
    echo "<div class='success'>✅ Journal ID 9 already exists: " . htmlspecialchars($journals[9]) . "</div>";
} else {
    echo "<div class='warning'>⚠️ Journal ID 9 (Savings Journal) does not exist. Creating it now...</div>";
    
    // Insert Journal ID 9
    $stmt = $mysqli->prepare("INSERT INTO journal (id, type) VALUES (9, 'Savings Journal')");
    
    if ($stmt) {
        if ($stmt->execute()) {
            echo "<div class='success'>✅ Successfully created Journal ID 9: Savings Journal</div>";
            $journals[9] = 'Savings Journal';
        } else {
            echo "<div class='error'>❌ Failed to create Journal ID 9: " . $mysqli->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='error'>❌ Failed to prepare statement: " . $mysqli->error . "</div>";
    }
}

// Step 4: Verify saving_account_type table has account_setup field
echo "<h2>Step 4: Verifying Savings Account Type Configuration</h2>";

$result = $mysqli->query("SHOW TABLES LIKE 'saving_account_type'");
if ($result->num_rows == 0) {
    echo "<div class='error'>❌ saving_account_type table does not exist.</div>";
} else {
    echo "<div class='success'>✅ saving_account_type table exists</div>";
    
    // Check if account_setup column exists
    $result = $mysqli->query("SHOW COLUMNS FROM saving_account_type LIKE 'account_setup'");
    if ($result->num_rows == 0) {
        echo "<div class='error'>❌ account_setup column does not exist in saving_account_type table. This field is required for GL posting.</div>";
        echo "<div class='info'>💡 You may need to add this column manually or through your database management tool.</div>";
    } else {
        echo "<div class='success'>✅ account_setup column exists in saving_account_type table</div>";
        
        // Check how many savings account types have account_setup configured
        $result = $mysqli->query("SELECT COUNT(*) as total, SUM(CASE WHEN account_setup IS NOT NULL AND account_setup != '' THEN 1 ELSE 0 END) as configured FROM saving_account_type");
        $stats = $result->fetch_assoc();
        
        echo "<div class='info'>";
        echo "📊 Savings Account Type Statistics:<br>";
        echo "Total Types: " . $stats['total'] . "<br>";
        echo "Configured with account_setup: " . $stats['configured'] . "<br>";
        echo "Not configured: " . ($stats['total'] - $stats['configured']) . "<br>";
        echo "</div>";
        
        if ($stats['configured'] == 0) {
            echo "<div class='warning'>⚠️ Warning: No savings account types have account_setup configured. GL posting will fail until account_setup is configured for each savings account type.</div>";
        }
    }
}

// Step 5: Verify general_ledger_entry and general_ledger tables exist
echo "<h2>Step 5: Verifying General Ledger Tables</h2>";

$tables_to_check = array('general_ledger_entry', 'general_ledger');
$all_tables_exist = true;

foreach ($tables_to_check as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        echo "<div class='error'>❌ Table '$table' does not exist.</div>";
        $all_tables_exist = false;
    } else {
        echo "<div class='success'>✅ Table '$table' exists</div>";
    }
}

// Step 6: Summary
echo "<h2>Step 6: Installation Summary</h2>";

if (isset($journals[9]) && $all_tables_exist) {
    echo "<div class='success'>";
    echo "✅ <strong>Installation Complete!</strong><br><br>";
    echo "Savings Account Auto-Posting to GL is now configured.<br><br>";
    echo "<strong>Next Steps:</strong><br>";
    echo "1. Ensure all savings account types have 'account_setup' field configured (linking to a GL liability account)<br>";
    echo "2. Ensure payment methods map to appropriate cash/bank accounts in your chart of accounts<br>";
    echo "3. Test by creating a new savings account with an opening balance<br>";
    echo "4. Check the general_ledger table to verify entries are being posted automatically<br>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "⚠️ <strong>Installation Incomplete</strong><br><br>";
    echo "Please resolve the errors above before using the auto-posting feature.";
    echo "</div>";
}

echo "</div></div></body></html>";

$mysqli->close();
?>
