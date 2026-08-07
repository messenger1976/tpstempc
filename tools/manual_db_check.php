<?php
// Manual database check for fiscal year table
echo "<h1>Manual Database Check</h1>";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tapstemco";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color: green;'>✓ Connected to database successfully</p>";

// Check if fiscal_year table exists
$result = $conn->query("SHOW TABLES LIKE 'fiscal_year'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Fiscal year table EXISTS</p>";

    // Check table structure
    $result = $conn->query("DESCRIBE fiscal_year");
    echo "<p>Table structure:</p><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['Field']} - {$row['Type']}</li>";
    }
    echo "</ul>";

    // Count records
    $result = $conn->query("SELECT COUNT(*) as count FROM fiscal_year");
    $row = $result->fetch_assoc();
    echo "<p>Total fiscal years: {$row['count']}</p>";

    // Check for active fiscal year
    $result = $conn->query("SELECT * FROM fiscal_year WHERE status = 1 LIMIT 1");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<p>Active fiscal year: {$row['name']} ({$row['start_date']} to {$row['end_date']})</p>";
    } else {
        echo "<p>No active fiscal year set</p>";
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

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Table created successfully!</p>";
        echo "<p><strong>Please refresh this page to verify the table was created.</strong></p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating table: " . $conn->error . "</p>";
        echo "<p>Please run this SQL manually in phpMyAdmin:</p>";
        echo "<pre>$sql</pre>";
    }
}

$conn->close();

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If table was created above, go back to your application</li>";
echo "<li>Make sure you're logged in as an admin user</li>";
echo "<li>Look for 'Settings' in the left menu and expand it</li>";
echo "<li>You should see 'Fiscal Year Management' in the submenu</li>";
echo "<li>If you don't see it, check the debug scripts above for more information</li>";
echo "</ol>";

echo "<p><a href='http://localhost/tapstemco'>← Back to Application</a></p>";
?>
