<?php
// Simple script to check payment methods table

$mysqli = new mysqli('localhost', 'root', '', 'tapstemco');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "=== PAYMENTMENTHOD TABLE STRUCTURE ===\n";
$result = $mysqli->query('DESCRIBE paymentmenthod;');
while($row = $result->fetch_assoc()) {
    echo implode(' | ', $row) . "\n";
}

echo "\n=== EXISTING PAYMENT METHODS ===\n";
$result = $mysqli->query('SELECT * FROM paymentmenthod ORDER BY id;');
while($row = $result->fetch_assoc()) {
    echo implode(' | ', $row) . "\n";
}

echo "\n=== Checking if ADJUSTMENT exists ===\n";
$result = $mysqli->query("SELECT * FROM paymentmenthod WHERE name = 'ADJUSTMENT'");
if ($result->num_rows > 0) {
    echo "ADJUSTMENT already exists\n";
    $row = $result->fetch_assoc();
    print_r($row);
} else {
    echo "ADJUSTMENT does NOT exist - will need to be added\n";
}

$mysqli->close();
?>
