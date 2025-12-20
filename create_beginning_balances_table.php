<?php
// Simple script to create beginning balances table
echo "<h1>Creating Beginning Balances Table</h1>";

// Include the CodeIgniter bootstrap
require_once 'index.php';

$sql = "-- Beginning Balances Table
-- This table stores beginning balances for General Ledger Accounts by Fiscal Year

CREATE TABLE IF NOT EXISTS `beginning_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fiscal_year_id` int(11) NOT NULL COMMENT 'Reference to fiscal_year table',
  `account` varchar(20) NOT NULL COMMENT 'Account number from account_chart',
  `debit` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning debit balance',
  `credit` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning credit balance',
  `description` text COMMENT 'Description/notes for the beginning balance',
  `posted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Posted to General Ledger, 0=Not Posted',
  `posted_date` datetime DEFAULT NULL COMMENT 'Date when posted to General Ledger',
  `posted_by` int(11) DEFAULT NULL COMMENT 'User ID who posted the balance',
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `PIN` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pin` (`PIN`),
  KEY `idx_fiscal_year` (`fiscal_year_id`),
  KEY `idx_account` (`account`),
  KEY `idx_posted` (`posted`),
  UNIQUE KEY `unique_fiscal_account` (`PIN`, `fiscal_year_id`, `account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Beginning balances for General Ledger Accounts by Fiscal Year';";

echo "<pre>Executing SQL:\n$sql</pre>";

if ($this->db->query($sql)) {
    echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS: Beginning balances table created successfully!</p>";
    echo "<p>You can now access the Beginning Balances module from the Finance menu.</p>";
    echo "<p><a href='" . site_url() . "'>← Back to Application</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ ERROR: Failed to create table</p>";
    echo "<p>Error: " . $this->db->error()['message'] . "</p>";
    echo "<p>Please run this SQL manually in phpMyAdmin or your database management tool.</p>";
}
?>
