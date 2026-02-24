<?php
// Simple script to create fiscal year table
echo "<h1>Creating Fiscal Year Table</h1>";

// Include the CodeIgniter bootstrap
require_once 'index.php';

$sql = "-- Fiscal Year Table
-- This table stores fiscal year configurations for the system

CREATE TABLE IF NOT EXISTS `fiscal_year` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Fiscal year name (e.g., FY 2024-2025)',
  `start_date` date NOT NULL COMMENT 'Start date of fiscal year',
  `end_date` date NOT NULL COMMENT 'End date of fiscal year',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Active, 0=Inactive',
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `PIN` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pin` (`PIN`),
  KEY `idx_status` (`status`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Fiscal year configuration table';";

echo "<pre>Executing SQL:\n$sql</pre>";

if ($this->db->query($sql)) {
    echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS: Fiscal year table created successfully!</p>";
    echo "<p>You can now access the Fiscal Year Management from the Settings menu.</p>";
    echo "<p><a href='" . site_url() . "'>← Back to Application</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ ERROR: Failed to create table</p>";
    echo "<p>Error: " . $this->db->error()['message'] . "</p>";
    echo "<p>Please run this SQL manually in phpMyAdmin or your database management tool.</p>";
}
?>
