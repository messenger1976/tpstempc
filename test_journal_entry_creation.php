<?php
/**
 * Diagnostic script to test journal entry creation and verify line items are saved correctly
 */

// Define BASEPATH to allow direct access
define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');

// Load database config
require_once('application/config/database.php');
$db_config = $db['default'];

// Create mysqli connection
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<!DOCTYPE html><html><head><title>Test Journal Entry Creation</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
    h1 { color: #333; border-bottom: 3px solid #4472C4; padding-bottom: 10px; }
    .success { color: #28a745; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; margin: 10px 0; }
    .error { color: #dc3545; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; margin: 10px 0; }
    .info { color: #17a2b8; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th { background: #4472C4; color: white; padding: 10px; text-align: left; }
    td { padding: 8px; border: 1px solid #ddd; }
    tr:nth-child(even) { background: #f9f9f9; }
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔍 Test Journal Entry Creation & Line Items</h1>";

// Get PIN from URL or use first available
$pin = isset($_GET['pin']) ? $_GET['pin'] : '';
if (empty($pin)) {
    $result = $mysqli->query("SELECT DISTINCT PIN FROM general_journal_entry LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        $pin = $row['PIN'];
    }
}

if (empty($pin)) {
    die("<div class='error'>Error: No PIN found. Please specify PIN in URL: ?pin=YOUR_PIN</div></div></body></html>");
}

echo "<div class='info'>Using PIN: <strong>" . htmlspecialchars($pin) . "</strong></div>";

// Test 1: Check table structures
echo "<h2>1. Table Structure Check</h2>";
echo "<table>";
echo "<tr><th>Table</th><th>Has PIN Column</th><th>Has entryid Column</th><th>Has id Column</th></tr>";

// Check general_journal_entry
$result = $mysqli->query("SHOW COLUMNS FROM general_journal_entry");
$gje_cols = array();
while ($row = $result->fetch_assoc()) {
    $gje_cols[] = $row['Field'];
}
echo "<tr><td>general_journal_entry</td>";
echo "<td>" . (in_array('PIN', $gje_cols) ? '✅ Yes' : '❌ No') . "</td>";
echo "<td>" . (in_array('entryid', $gje_cols) ? '✅ Yes' : '❌ No') . "</td>";
echo "<td>" . (in_array('id', $gje_cols) ? '✅ Yes (Primary Key)' : '❌ No') . "</td></tr>";

// Check general_journal
$result = $mysqli->query("SHOW COLUMNS FROM general_journal");
$gj_cols = array();
while ($row = $result->fetch_assoc()) {
    $gj_cols[] = $row['Field'];
}
echo "<tr><td>general_journal</td>";
echo "<td>" . (in_array('PIN', $gj_cols) ? '✅ Yes' : '❌ No') . "</td>";
echo "<td>" . (in_array('entryid', $gj_cols) ? '✅ Yes' : '❌ No') . "</td>";
echo "<td>" . (in_array('id', $gj_cols) ? '✅ Yes' : '❌ No') . "</td></tr>";
echo "</table>";

// Test 2: Check existing entries and their line items
echo "<h2>2. Existing Journal Entries Analysis</h2>";
$result = $mysqli->query("
    SELECT gje.id, gje.entrydate, gje.description, gje.PIN as header_pin,
           COUNT(gj.id) as line_item_count
    FROM general_journal_entry gje
    LEFT JOIN general_journal gj ON gj.entryid = gje.id
    WHERE gje.PIN = '" . $mysqli->real_escape_string($pin) . "'
    GROUP BY gje.id
    ORDER BY gje.id DESC
    LIMIT 10
");

if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Entry ID</th><th>Date</th><th>Description</th><th>Header PIN</th><th>Line Items</th><th>Check</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $entry_id = $row['id'];
        // Check line items directly
        $line_check = $mysqli->query("
            SELECT id, entryid, account, debit, credit, PIN as item_pin 
            FROM general_journal 
            WHERE entryid = " . intval($entry_id) . "
            LIMIT 5
        ");
        
        $direct_count = $line_check ? $line_check->num_rows : 0;
        
        echo "<tr>";
        echo "<td>" . $entry_id . "</td>";
        echo "<td>" . $row['entrydate'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['description'], 0, 50)) . "</td>";
        echo "<td>" . ($row['header_pin'] ?: 'NULL') . "</td>";
        echo "<td>JOIN: " . $row['line_item_count'] . " / Direct: " . $direct_count . "</td>";
        echo "<td>";
        
        if ($line_check && $line_check->num_rows > 0) {
            echo "✅ Found " . $direct_count . " items";
            // Show first item's entryid and PIN
            $first_item = $line_check->fetch_assoc();
            echo "<br><small>First item entryid: " . $first_item['entryid'] . ", PIN: " . ($first_item['item_pin'] ?: 'NULL') . "</small>";
        } else {
            echo "❌ No items found for entryid=" . $entry_id;
            // Check if items exist with different entryid format
            $check_str = $mysqli->query("SELECT COUNT(*) as cnt FROM general_journal WHERE entryid = '" . $mysqli->real_escape_string($entry_id) . "'");
            if ($check_str && $row_str = $check_str->fetch_assoc() && $row_str['cnt'] > 0) {
                echo "<br><small>⚠️ Found " . $row_str['cnt'] . " items when entryid is treated as string</small>";
            }
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='info'>No existing journal entries found for this PIN.</div>";
}

// Test 3: Create a test journal entry
echo "<h2>3. Create Test Journal Entry</h2>";

if (isset($_GET['create_test'])) {
    $mysqli->autocommit(false);
    
    try {
        // Create header
        $test_date = date('Y-m-d');
        $test_desc = 'TEST ENTRY - ' . date('Y-m-d H:i:s');
        
        $stmt = $mysqli->prepare("INSERT INTO general_journal_entry (entrydate, description, PIN) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $test_date, $test_desc, $pin);
        $stmt->execute();
        $header_id = $mysqli->insert_id;
        
        // Fallback if insert_id is 0
        if (!$header_id) {
            $result = $mysqli->query("SELECT id FROM general_journal_entry WHERE PIN = '" . $mysqli->real_escape_string($pin) . "' ORDER BY id DESC LIMIT 1");
            if ($row = $result->fetch_assoc()) {
                $header_id = $row['id'];
            }
        }
        
        if (!$header_id) {
            throw new Exception("Failed to get header ID");
        }
        
        echo "<div class='success'>✅ Created header with ID: " . $header_id . "</div>";
        
        // Create test line items
        $test_items = array(
            array('account' => '1000', 'debit' => 1000, 'credit' => 0, 'description' => 'Test Debit Item 1'),
            array('account' => '2000', 'debit' => 0, 'credit' => 1000, 'description' => 'Test Credit Item 1')
        );
        
        $items_inserted = 0;
        foreach ($test_items as $item) {
            if (in_array('PIN', $gj_cols)) {
                $stmt = $mysqli->prepare("INSERT INTO general_journal (entryid, account, debit, credit, description, entrydate, PIN) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isddsss", $header_id, $item['account'], $item['debit'], $item['credit'], $item['description'], $test_date, $pin);
            } else {
                $stmt = $mysqli->prepare("INSERT INTO general_journal (entryid, account, debit, credit, description, entrydate) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isddss", $header_id, $item['account'], $item['debit'], $item['credit'], $item['description'], $test_date);
            }
            $stmt->execute();
            $items_inserted++;
        }
        
        $mysqli->commit();
        echo "<div class='success'>✅ Inserted " . $items_inserted . " line items with entryid = " . $header_id . "</div>";
        
        // Verify the items were saved
        $verify = $mysqli->query("SELECT * FROM general_journal WHERE entryid = " . intval($header_id));
        if ($verify && $verify->num_rows == $items_inserted) {
            echo "<div class='success'>✅ Verification: Found " . $verify->num_rows . " line items matching entryid = " . $header_id . "</div>";
            echo "<table>";
            echo "<tr><th>ID</th><th>entryid</th><th>account</th><th>debit</th><th>credit</th><th>PIN</th></tr>";
            while ($row = $verify->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['entryid'] . " (" . gettype($row['entryid']) . ")</td>";
                echo "<td>" . $row['account'] . "</td>";
                echo "<td>" . $row['debit'] . "</td>";
                echo "<td>" . $row['credit'] . "</td>";
                echo "<td>" . (isset($row['PIN']) ? ($row['PIN'] ?: 'NULL') : 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='error'>❌ Verification failed: Expected " . $items_inserted . " items, found " . ($verify ? $verify->num_rows : 0) . "</div>";
        }
        
    } catch (Exception $e) {
        $mysqli->rollback();
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    $mysqli->autocommit(true);
} else {
    echo "<div class='info'>Click <a href='?pin=" . urlencode($pin) . "&create_test=1'>here</a> to create a test journal entry.</div>";
}

echo "</div></body></html>";
$mysqli->close();
?>
