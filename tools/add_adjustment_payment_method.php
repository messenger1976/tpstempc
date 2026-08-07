<?php
// Script to add ADJUSTMENT payment method

$mysqli = new mysqli('localhost', 'root', '', 'tapstemco');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// First, check what PIN values exist in paymentmenthod table
$result = $mysqli->query('SELECT DISTINCT PIN FROM paymentmenthod');
$pins = array();
while($row = $result->fetch_assoc()) {
    $pins[] = $row['PIN'];
}
echo "Existing PINs in paymentmenthod: " . implode(', ', $pins) . "\n";

// Use the first PIN we find (usually 105 is the default)
$pin = $pins[0] ?? 105;
echo "Using PIN: $pin\n\n";

// Insert ADJUSTMENT payment method
$sql = "INSERT INTO paymentmenthod (name, description, gl_account_code, status, PIN) 
        VALUES ('ADJUSTMENT', 'Journal Voucher / GL Adjustment Entry', NULL, 1, $pin)";

echo "Executing SQL:\n$sql\n\n";

if ($mysqli->query($sql)) {
    echo "✓ ADJUSTMENT payment method successfully added (ID: " . $mysqli->insert_id . ")\n\n";
    
    // Verify it was added
    $result = $mysqli->query("SELECT * FROM paymentmenthod WHERE name = 'ADJUSTMENT'");
    if ($row = $result->fetch_assoc()) {
        echo "=== ADJUSTMENT Payment Method Details ===\n";
        print_r($row);
    }
} else {
    echo "✗ Error adding ADJUSTMENT payment method: " . $mysqli->error . "\n";
}

$mysqli->close();
?>
