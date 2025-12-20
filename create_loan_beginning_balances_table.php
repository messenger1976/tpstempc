<?php
// Simple script to create loan beginning balances table
echo "<h1>Creating Loan Beginning Balances Table</h1>";

// Include the CodeIgniter bootstrap
require_once 'index.php';

$sql = "-- Loan Beginning Balances Table
-- This table stores beginning balances for Existing Loans by Fiscal Year

CREATE TABLE IF NOT EXISTS `loan_beginning_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fiscal_year_id` int(11) NOT NULL COMMENT 'Reference to fiscal_year table',
  `loan_id` varchar(50) DEFAULT NULL COMMENT 'Loan ID/Number',
  `member_id` varchar(50) NOT NULL COMMENT 'Member ID',
  `loan_product_id` int(11) NOT NULL COMMENT 'Reference to loan_product table',
  `principal_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning principal balance',
  `interest_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning interest balance',
  `penalty_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning penalty balance',
  `total_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total beginning balance',
  `disbursement_date` date DEFAULT NULL COMMENT 'Original loan disbursement date',
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
  KEY `idx_member` (`member_id`),
  KEY `idx_loan_product` (`loan_product_id`),
  KEY `idx_posted` (`posted`),
  UNIQUE KEY `unique_fiscal_member_product` (`PIN`, `fiscal_year_id`, `member_id`, `loan_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Beginning balances for Existing Loans by Fiscal Year';";

echo "<pre>Executing SQL:\n$sql</pre>";

if ($this->db->query($sql)) {
    echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS: Loan beginning balances table created successfully!</p>";
    echo "<p>You can now access the Loan Beginning Balances module from the Loan Management menu.</p>";
    echo "<p><a href='" . site_url() . "'>← Back to Application</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ ERROR: Failed to create table</p>";
    echo "<p>Error: " . $this->db->error()['message'] . "</p>";
    echo "<p>Please run this SQL manually in phpMyAdmin or your database management tool.</p>";
}
?>
