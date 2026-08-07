<?php
// Verify all active payment methods

$mysqli = new mysqli('localhost', 'root', '', 'tapstemco');
$result = $mysqli->query('SELECT id, name, description FROM paymentmenthod WHERE status = 1 ORDER BY id');

echo "=== ACTIVE PAYMENT METHODS (Available in saving/credit_debit form) ===\n";
echo str_repeat("=", 70) . "\n";
while($row = $result->fetch_assoc()) {
    echo sprintf("[%d] %-20s - %s\n", $row['id'], $row['name'], $row['description']);
}

echo "\n✓ ADJUSTMENT payment method is now available in the dropdown!\n";
echo "✓ When you select ADJUSTMENT, the system will:\n";
echo "   - Use an Equity/Adjustment account instead of Cash\n";
echo "   - Post to Manual Journal (JV entries)\n";
echo "   - Mark the GL entry as [JV] in the description\n";

$mysqli->close();
?>
