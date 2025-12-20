<?php
// Quick check for fiscal year table
echo "<h1>Quick Fiscal Year Check</h1>";

// Include CI bootstrap
require_once 'index.php';

try {
    // Check if table exists
    if ($this->db->table_exists('fiscal_year')) {
        echo "<p style='color: green;'>✓ Fiscal year table EXISTS</p>";

        $count = $this->db->count_all('fiscal_year');
        echo "<p>Fiscal years in database: $count</p>";

        if ($count > 0) {
            $active = $this->db->where('status', 1)->get('fiscal_year')->row();
            if ($active) {
                echo "<p>Active fiscal year: {$active->name} ({$active->start_date} to {$active->end_date})</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Fiscal year table does NOT exist - creating it now...</p>";

        // Create the table
        $sql = "CREATE TABLE IF NOT EXISTS `fiscal_year` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Fiscal year configuration table'";

        if ($this->db->query($sql)) {
            echo "<p style='color: green;'>✓ Table created successfully!</p>";
            echo "<p><strong>Refresh the page to check again.</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create table: " . $this->db->error()['message'] . "</p>";
        }
    }

    // Check user permissions
    echo "<h2>User Check</h2>";
    if ($this->ion_auth->logged_in()) {
        echo "<p style='color: green;'>✓ User is logged in</p>";
        if ($this->ion_auth->is_admin()) {
            echo "<p style='color: green;'>✓ User is admin (can see fiscal year menu)</p>";
        } else {
            echo "<p style='color: red;'>✗ User is NOT admin (cannot see fiscal year menu)</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ User is NOT logged in</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='" . site_url() . "'>← Back to Application</a></p>";
?>
