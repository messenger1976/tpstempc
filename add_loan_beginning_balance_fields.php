<?php
// Script to add new fields to loan_beginning_balances table
echo "<h1>Adding New Fields to Loan Beginning Balances Table</h1>";

// Database configuration - adjust these values
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'tapstemco';

// Try to get config from CodeIgniter config file without including it
$config_file = __DIR__ . '/application/config/database.php';
if (file_exists($config_file)) {
    // Read config without loading CodeIgniter (avoids "No direct script access allowed" error)
    $config_content = file_get_contents($config_file);
    // Extract database config using regex
    if (preg_match("/\['default'\]\['hostname'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_host = $matches[1];
    }
    if (preg_match("/\['default'\]\['username'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_user = $matches[1];
    }
    if (preg_match("/\['default'\]\['password'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_pass = $matches[1];
    }
    if (preg_match("/\['default'\]\['database'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_name = $matches[1];
    }
}

$sql = "-- Add new fields to loan_beginning_balances table
-- Loan Amount, Monthly Amortization, Last Date Paid, and Term

ALTER TABLE `loan_beginning_balances`
ADD COLUMN IF NOT EXISTS `loan_amount` decimal(15,2) DEFAULT NULL COMMENT 'Original loan amount' AFTER `disbursement_date`,
ADD COLUMN IF NOT EXISTS `monthly_amort` decimal(15,2) DEFAULT NULL COMMENT 'Monthly amortization amount' AFTER `loan_amount`,
ADD COLUMN IF NOT EXISTS `last_date_paid` date DEFAULT NULL COMMENT 'Last payment date' AFTER `monthly_amort`,
ADD COLUMN IF NOT EXISTS `term` int(11) DEFAULT NULL COMMENT 'Loan term in months' AFTER `last_date_paid`;";

echo "<pre>Executing SQL:\n$sql</pre>";

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check if columns already exist
    $check_sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA = '$db_name' 
                  AND TABLE_NAME = 'loan_beginning_balances' 
                  AND COLUMN_NAME IN ('loan_amount', 'monthly_amort', 'last_date_paid', 'term')";
    
    $result = $conn->query($check_sql);
    $existing_columns = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $existing_columns[] = $row['COLUMN_NAME'];
        }
    }
    
    // Build ALTER statements only for columns that don't exist
    $alter_statements = array();
    
    if (!in_array('loan_amount', $existing_columns)) {
        $alter_statements[] = "ADD COLUMN `loan_amount` decimal(15,2) DEFAULT NULL COMMENT 'Original loan amount' AFTER `disbursement_date`";
    }
    
    if (!in_array('monthly_amort', $existing_columns)) {
        $alter_statements[] = "ADD COLUMN `monthly_amort` decimal(15,2) DEFAULT NULL COMMENT 'Monthly amortization amount' AFTER `loan_amount`";
    }
    
    if (!in_array('last_date_paid', $existing_columns)) {
        $alter_statements[] = "ADD COLUMN `last_date_paid` date DEFAULT NULL COMMENT 'Last payment date' AFTER `monthly_amort`";
    }
    
    if (!in_array('term', $existing_columns)) {
        $alter_statements[] = "ADD COLUMN `term` int(11) DEFAULT NULL COMMENT 'Loan term in months' AFTER `last_date_paid`";
    }
    
    if (empty($alter_statements)) {
        echo "<p style='color: blue; font-weight: bold;'>ℹ INFO: All columns already exist. No changes needed.</p>";
    } else {
        $alter_sql = "ALTER TABLE `loan_beginning_balances` " . implode(", ", $alter_statements);
        echo "<pre>Executing:\n$alter_sql</pre>";
        
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS: New fields added successfully!</p>";
            echo "<p>Added fields:</p><ul>";
            foreach ($alter_statements as $stmt) {
                preg_match('/`(\w+)`/', $stmt, $matches);
                if (isset($matches[1])) {
                    echo "<li>" . $matches[1] . "</li>";
                }
            }
            echo "</ul>";
        } else {
            throw new Exception("Error: " . $conn->error);
        }
    }
    
    $conn->close();
    echo "<p><a href='index.php'>← Back to Application</a></p>";
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ ERROR: Failed to add fields</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please run this SQL manually in phpMyAdmin or your database management tool:</p>";
    echo "<pre>$sql</pre>";
}
?>
