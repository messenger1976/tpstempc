<?php
/**
 * Database Migration Script
 * Add 'account_setup_interest_rate' column to 'saving_account_type' table
 * 
 * This script adds the column for storing the GL account code for interest rate postings.
 * Run this script once to add the column to your database.
 */

// Database configuration - adjust these if needed
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'your_database_name'; // Change this to your actual database name

// Or use CodeIgniter database config
// Uncomment the following lines if you want to use CodeIgniter's database config
/*
define('BASEPATH', true);
require_once('application/config/database.php');
$db_config = $db['default'];
$db_host = $db_config['hostname'];
$db_user = $db_config['username'];
$db_pass = $db_config['password'];
$db_name = $db_config['database'];
*/

try {
    // Connect to database
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to database successfully.\n\n";
    
    // Check if column already exists
    $check_column = "SHOW COLUMNS FROM `saving_account_type` LIKE 'account_setup_interest_rate'";
    $result = $conn->query($check_column);
    
    if ($result->num_rows > 0) {
        echo "Column 'account_setup_interest_rate' already exists in 'saving_account_type' table.\n";
        echo "No changes needed.\n";
    } else {
        // Add the column
        $sql = "ALTER TABLE `saving_account_type` 
                ADD COLUMN `account_setup_interest_rate` VARCHAR(50) NULL 
                AFTER `account_setup`";
        
        if ($conn->query($sql) === TRUE) {
            echo "Column 'account_setup_interest_rate' added successfully to 'saving_account_type' table.\n";
        } else {
            echo "Error adding column: " . $conn->error . "\n";
        }
    }
    
    $conn->close();
    echo "\nMigration completed.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
