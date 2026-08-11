<?php
echo "<h1>🚀 Creating Fiscal Year Table</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; font-weight: bold; } .error { color: red; font-weight: bold; }</style>";

// Direct database connection to ensure it works
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tapstemco";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<p class='error'>Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p class='success'>✓ Connected to database</p>";

// Check if table exists first
$result = $conn->query("SHOW TABLES LIKE 'fiscal_year'");
$table_exists = $result->num_rows > 0;

if ($table_exists) {
    echo "<p class='success'>✓ Fiscal year table already exists</p>";
} else {
    echo "<p>Creating fiscal year table...</p>";

    $sql = "CREATE TABLE `fiscal_year` (
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
        echo "<p class='success'>✓ Table created successfully!</p>";
    } else {
        echo "<p class='error'>✗ Failed to create table: " . $conn->error . "</p>";
        $conn->close();
        exit;
    }
}

// Insert sample fiscal year if table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM fiscal_year");
$row = $result->fetch_assoc();
$count = $row['count'];

if ($count == 0) {
    $current_year = date('Y');
    $sample_sql = "INSERT INTO `fiscal_year` (`name`, `start_date`, `end_date`, `status`, `created_by`, `PIN`) VALUES
    ('FY $current_year-" . ($current_year + 1) . "', '$current_year-01-01', '$current_year-12-31', 1, 1, 'TAPSTEMCO')";

    if ($conn->query($sample_sql) === TRUE) {
        echo "<p class='success'>✓ Sample fiscal year 'FY $current_year-" . ($current_year + 1) . "' created and set as active</p>";
    } else {
        echo "<p class='error'>✗ Failed to create sample fiscal year: " . $conn->error . "</p>";
    }
} else {
    echo "<p class='success'>✓ Fiscal year records already exist ($count records)</p>";
}

$conn->close();

echo "<h2>✅ Fiscal Year Module is Ready!</h2>";
echo "<p>The fiscal year table has been created and populated.</p>";

echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Clear your browser cache</strong> (Ctrl+F5 or Cmd+Shift+R)</li>";
echo "<li><strong>Refresh the application</strong></li>";
echo "<li><strong>Go to Settings menu</strong> in the left sidebar</li>";
echo "<li><strong>Click on Settings</strong> to expand the submenu</li>";
echo "<li><strong>Look for 'Fiscal Year Management'</strong> in the list</li>";
echo "</ol>";

echo "<p>If you still don't see it, run the diagnostic script: <a href='diagnose_fiscal_year.php'>diagnose_fiscal_year.php</a></p>";

echo "<p><a href='" . "http://localhost/tapstemco" . "' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>← Go to Application</a></p>";
?>
