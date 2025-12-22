<?php
/**
 * CASH RECEIPT MODULE - STANDALONE DATABASE INSTALLER
 * 
 * This script runs independently of CodeIgniter to avoid "No direct script access" errors
 * 
 * INSTRUCTIONS:
 * 1. This script will auto-detect your database settings
 * 2. Access this file directly in your browser
 * 3. Delete this file after successful installation for security
 */

// Prevent CodeIgniter from blocking this script
define('BASEPATH', TRUE);

// Start output buffering to prevent headers already sent errors
ob_start();

// ============================================
// AUTO-DETECT DATABASE CONFIGURATION
// ============================================
$config_file = __DIR__ . '/application/config/database.php';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'tapstemco';

if (file_exists($config_file)) {
    // Temporarily define BASEPATH for the config file
    $config_content = file_get_contents($config_file);
    
    // Extract database settings using regex
    if (preg_match("/\['hostname'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_host = $matches[1];
    }
    if (preg_match("/\['username'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_user = $matches[1];
    }
    if (preg_match("/\['password'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_pass = $matches[1];
    }
    if (preg_match("/\['database'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_name = $matches[1];
    }
}
// ============================================

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Cash Receipt Module - Installation Error</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background-color: #f5f5f5; }
            .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px; }
            h2 { color: #721c24; }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>❌ Database Connection Failed</h2>
            <p><strong>Error:</strong> <?php echo $conn->connect_error; ?></p>
            <p><strong>Host:</strong> <?php echo $db_host; ?></p>
            <p><strong>Database:</strong> <?php echo $db_name; ?></p>
            <p><strong>User:</strong> <?php echo $db_user; ?></p>
            <hr>
            <h3>How to Fix:</h3>
            <ol>
                <li>Check your database credentials in <code>application/config/database.php</code></li>
                <li>Make sure MySQL/MariaDB is running (start XAMPP)</li>
                <li>Verify the database exists</li>
                <li>Refresh this page after fixing</li>
            </ol>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// HTML Header
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cash Receipt Module - Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        .db-info {
            background: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .step {
            margin: 15px 0;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .summary {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .summary.errors {
            background: #f8d7da;
            border-color: #f5c6cb;
        }
        hr {
            border: none;
            border-top: 2px solid #ecf0f1;
            margin: 25px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-success {
            background: #27ae60;
        }
        .btn-success:hover {
            background: #229954;
        }
        ol li {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>💰 Cash Receipt Module Installation</h2>
        
        <div class="db-info">
            <strong>📊 Database Connection:</strong><br>
            Host: <code><?php echo $db_host; ?></code><br>
            Database: <code><?php echo $db_name; ?></code><br>
            User: <code><?php echo $db_user; ?></code>
        </div>
        
        <hr>
<?php
// SQL statements
$sqls = array(
    // Create cash_receipts table
    "CREATE TABLE IF NOT EXISTS `cash_receipts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `receipt_no` varchar(50) NOT NULL,
      `receipt_date` date NOT NULL,
      `received_from` varchar(255) NOT NULL,
      `payment_method` varchar(50) NOT NULL COMMENT 'Cash, Cheque, Bank Transfer, Mobile Money',
      `cheque_no` varchar(50) DEFAULT NULL,
      `bank_name` varchar(100) DEFAULT NULL,
      `description` text NOT NULL,
      `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
      `createdby` int(11) NOT NULL,
      `PIN` varchar(20) NOT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `idx_pin` (`PIN`),
      KEY `idx_receipt_no` (`receipt_no`),
      KEY `idx_receipt_date` (`receipt_date`),
      KEY `idx_createdby` (`createdby`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cash Receipt Headers'",
    
    // Create cash_receipt_items table
    "CREATE TABLE IF NOT EXISTS `cash_receipt_items` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `receipt_id` int(11) NOT NULL,
      `account` varchar(50) NOT NULL COMMENT 'Account code from chart of accounts',
      `description` varchar(255) DEFAULT NULL,
      `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
      `PIN` varchar(20) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `idx_receipt_id` (`receipt_id`),
      KEY `idx_account` (`account`),
      KEY `idx_pin` (`PIN`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cash Receipt Line Items'",
    
    // Add foreign key constraint
    "ALTER TABLE `cash_receipt_items` 
     ADD CONSTRAINT `fk_receipt_items_receipt` 
     FOREIGN KEY (`receipt_id`) REFERENCES `cash_receipts` (`id`) ON DELETE CASCADE",
    
    // Create journal_entry table if not exists
    "CREATE TABLE IF NOT EXISTS `journal_entry` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `entry_date` date NOT NULL,
      `description` text,
      `reference_type` varchar(50) DEFAULT NULL COMMENT 'Type: cash_receipt, journal, invoice, etc',
      `reference_id` int(11) DEFAULT NULL COMMENT 'ID of the referenced transaction',
      `createdby` int(11) NOT NULL,
      `PIN` varchar(20) NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `idx_pin` (`PIN`),
      KEY `idx_entry_date` (`entry_date`),
      KEY `idx_reference` (`reference_type`, `reference_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Journal Entry Headers'",
    
    // Create journal_entry_items table if not exists
    "CREATE TABLE IF NOT EXISTS `journal_entry_items` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `journal_id` int(11) NOT NULL,
      `account` varchar(50) NOT NULL,
      `debit` decimal(15,2) NOT NULL DEFAULT '0.00',
      `credit` decimal(15,2) NOT NULL DEFAULT '0.00',
      `description` varchar(255) DEFAULT NULL,
      `PIN` varchar(20) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `idx_journal_id` (`journal_id`),
      KEY `idx_account` (`account`),
      KEY `idx_pin` (`PIN`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Journal Entry Line Items'",
    
    // Add indexes for performance
    "ALTER TABLE `cash_receipts` ADD INDEX `idx_received_from` (`received_from`)",
    "ALTER TABLE `cash_receipts` ADD INDEX `idx_payment_method` (`payment_method`)"
);

// Execute SQL statements
$success_count = 0;
$error_count = 0;
$errors = array();

foreach ($sqls as $index => $sql) {
    echo "<div class='step'><strong>Step " . ($index + 1) . ":</strong> ";
    
    if ($conn->query($sql) === TRUE) {
        echo "<span class='success'>✓ Success</span></div>";
        $success_count++;
    } else {
        // Check if error is because constraint already exists
        if (strpos($conn->error, 'Duplicate key name') !== false || 
            strpos($conn->error, 'already exists') !== false ||
            strpos($conn->error, 'Duplicate entry') !== false) {
            echo "<span class='warning'>⚠ Already exists (skipped)</span></div>";
            $success_count++;
        } else {
            echo "<span class='error'>✗ Error: " . htmlspecialchars($conn->error) . "</span></div>";
            $error_count++;
            $errors[] = $conn->error;
        }
    }
}

echo "<hr>";

// Summary
if ($error_count > 0) {
    echo "<div class='summary errors'>";
    echo "<h3>⚠ Installation Completed with Errors</h3>";
    echo "<p>✓ Successful: <strong class='success'>{$success_count}</strong></p>";
    echo "<p>✗ Errors: <strong class='error'>{$error_count}</strong></p>";
    echo "<h4>Error Details:</h4><ul>";
    foreach ($errors as $error) {
        echo "<li class='error'>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "<p><strong>What to do:</strong> Most errors can be ignored if they say 'already exists'. Otherwise, check the error messages above.</p>";
    echo "</div>";
} else {
    echo "<div class='summary'>";
    echo "<h3>✅ Installation Completed Successfully!</h3>";
    echo "<p>✓ Successful Steps: <strong class='success'>{$success_count}</strong></p>";
    echo "<p>✗ Errors: <strong class='success'>0</strong></p>";
    echo "<p style='color:#27ae60; font-size:18px; font-weight:bold;'>🎉 The Cash Receipt module is now installed and ready to use!</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>📋 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Delete this file</strong> - Remove <code>install_cash_receipt.php</code> for security</li>";
echo "<li><strong>Set up permissions</strong> - Add Cash Receipt roles to your user groups:
    <ul>
        <li>View_cash_receipt</li>
        <li>Create_cash_receipt</li>
        <li>Edit_cash_receipt</li>
        <li>Delete_cash_receipt</li>
    </ul>
</li>";
echo "<li><strong>Clear cache</strong> - Delete contents of <code>application/cache/</code> folder</li>";
echo "<li><strong>Access the module</strong> - Login and go to Finance → Cash Receipt List</li>";
echo "</ol>";

echo "<hr>";
echo "<div style='text-align:center; margin-top:30px;'>";
echo "<a href='" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/' class='btn btn-success'>Go to Application</a>";
echo "</div>";

$conn->close();
ob_end_flush();
?>

    </div>
</body>
</html>
