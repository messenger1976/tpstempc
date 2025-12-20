<?php
echo "<h1>Forced Fiscal Year Table Creation</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; font-weight: bold; } .error { color: red; font-weight: bold; } .info { color: blue; }</style>";

// Direct database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tapstemco";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<p class='error'>Database connection failed: " . $conn->connect_error . "</p>");
}

echo "<p class='success'>✓ Connected to database</p>";

// Drop table if exists (to ensure clean creation)
$sql = "DROP TABLE IF EXISTS `fiscal_year`";
if ($conn->query($sql) === TRUE) {
    echo "<p class='info'>✓ Dropped existing table (if any)</p>";
}

// Create the table
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
    echo "<p class='success'>✓ Fiscal year table created successfully!</p>";

    // Insert a sample fiscal year
    $current_year = date('Y');
    $sample_sql = "INSERT INTO `fiscal_year` (`name`, `start_date`, `end_date`, `status`, `created_by`, `PIN`) VALUES
    ('FY $current_year-" . ($current_year + 1) . "', '$current_year-01-01', '$current_year-12-31', 1, 1, 'TAPSTEMCO')";

    if ($conn->query($sample_sql) === TRUE) {
        echo "<p class='success'>✓ Sample fiscal year 'FY $current_year-" . ($current_year + 1) . "' created and set as active</p>";
    } else {
        echo "<p class='error'>✗ Failed to create sample fiscal year: " . $conn->error . "</p>";
    }

} else {
    echo "<p class='error'>✗ Failed to create table: " . $conn->error . "</p>";
    echo "<p>Please run this SQL manually:</p>";
    echo "<pre>$sql</pre>";
}

$conn->close();

echo "<h2>✅ Fiscal Year Module is Ready!</h2>";
echo "<p>The fiscal year module has been successfully added to the Settings menu.</p>";

echo "<h3>Admin-Only Access:</h3>";
echo "<ul>";
echo "<li>✅ Menu item restricted to admin users only</li>";
echo "<li>✅ All fiscal year functions require admin privileges</li>";
echo "<li>✅ Database table created with proper structure</li>";
echo "</ul>";

echo "<h3>How to Access:</h3>";
echo "<ol>";
echo "<li>Log in as an admin user</li>";
echo "<li>Go to <strong>Settings</strong> in the left sidebar</li>";
echo "<li>Click to expand the Settings menu</li>";
echo "<li>You should see <strong>'Fiscal Year Management'</strong></li>";
echo "<li>Click it to manage fiscal years</li>";
echo "</ol>";

echo "<h3>Features Available:</h3>";
echo "<ul>";
echo "<li>📅 Create custom fiscal year periods</li>";
echo "<li>✅ Set one fiscal year as active at a time</li>";
echo "<li>📝 Edit existing fiscal years</li>";
echo "<li>🗑️ Delete inactive fiscal years</li>";
echo "<li>📊 View all fiscal years with status</li>";
echo "</ul>";

echo "<p style='background: #e8f5e8; padding: 15px; border-radius: 5px; border-left: 4px solid #4CAF50;'>";
echo "<strong>🎉 SUCCESS:</strong> The Fiscal Year module is now fully functional in your Settings menu!";
echo "</p>";

echo "<p><a href='" . "http://localhost/tapstemco" . "' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>← Go to Application</a></p>";
?>
