<?php
// Creates audit/restore table for loan BB → Finance chart BB offsets
echo "<h1>Creating Loan BB Chart Offsets Table</h1>";

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'your_database_name';

if (file_exists(__DIR__ . '/../application/config/database.php')) {
    if (!defined('BASEPATH')) {
        define('BASEPATH', true);
    }
    include __DIR__ . '/../application/config/database.php';
    if (isset($db['default'])) {
        $db_host = $db['default']['hostname'];
        $db_user = $db['default']['username'];
        $db_pass = $db['default']['password'];
        $db_name = $db['default']['database'];
    }
}

$sql = "CREATE TABLE IF NOT EXISTS `loan_bb_chart_offsets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_bb_id` int(11) NOT NULL COMMENT 'loan_beginning_balances.id',
  `beginning_balance_id` int(11) NOT NULL COMMENT 'beginning_balances.id',
  `account` varchar(50) NOT NULL COMMENT 'GL account number',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount deducted from chart BB debit',
  `side` varchar(10) NOT NULL DEFAULT 'debit' COMMENT 'Column reduced on beginning_balances',
  `gl_posted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 if offset credit lines were written to general_ledger',
  `PIN` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_loan_bb_account` (`PIN`, `loan_bb_id`, `account`),
  KEY `idx_loan_bb` (`loan_bb_id`),
  KEY `idx_beginning_balance` (`beginning_balance_id`),
  KEY `idx_pin` (`PIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Loan BB post offsets against Finance chart beginning_balances';";

echo "<pre>Executing SQL:\n$sql</pre>";

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if ($conn->query($sql)) {
        echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS: loan_bb_chart_offsets table created.</p>";
        echo "<p><a href='index.php'>← Back to Application</a></p>";
    } else {
        throw new Exception("Error: " . $conn->error);
    }

    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ ERROR: Failed to create table</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please run this SQL manually in phpMyAdmin or your database management tool.</p>";
}
?>
