<?php
/**
 * Post Journal Entries to General Ledger
 * 
 * This script posts unposted journal entries from general_journal to general_ledger
 * 
 * SECURITY: Delete after use!
 */

// Direct database connection to bypass CodeIgniter authentication
if (!defined('BASEPATH')) {
    define('BASEPATH', realpath(dirname(__FILE__) . '/system/') . '/');
}

if (!file_exists('application/config/database.php')) {
    die("Error: database.php config file not found!");
}

require_once 'application/config/database.php';

// Check if config is loaded
if (!isset($db) || !isset($db['default'])) {
    die("Error: Database configuration not found. Please check application/config/database.php");
}

// Get database config
$db_config = $db['default'];

// Create direct database connection
$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set charset
$mysqli->set_charset($db_config['char_set']);

// Helper function to execute queries
function db_query($mysqli, $query) {
    $result = $mysqli->query($query);
    if (!$result) {
        die("Query failed: " . $mysqli->error . "<br>Query: " . htmlspecialchars($query));
    }
    return $result;
}

// Helper function to fetch all results
function db_fetch_all($result) {
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = (object)$row;
    }
    return $rows;
}

// Helper function to escape string
function db_escape($mysqli, $string) {
    return $mysqli->real_escape_string($string);
}

// Get PIN from URL parameter or use default (you may want to change this)
$pin = isset($_GET['pin']) ? db_escape($mysqli, $_GET['pin']) : '';
if (empty($pin)) {
    // Try to get first PIN from database
    $result = db_query($mysqli, "SELECT DISTINCT PIN FROM general_journal_entry LIMIT 1");
    $row = $result->fetch_assoc();
    $pin = $row ? $row['PIN'] : '';
}

if (empty($pin)) {
    die("Error: No PIN found. Please specify PIN in URL: ?pin=YOUR_PIN");
}

echo "<!DOCTYPE html><html><head><title>Post Journal to General Ledger</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    h1 { color: #333; border-bottom: 3px solid #4472C4; padding-bottom: 10px; }
    .success { color: #28a745; font-weight: bold; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
    .error { color: #dc3545; font-weight: bold; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
    .info { color: #17a2b8; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
    .warning { color: #856404; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th { background: #4472C4; color: white; padding: 10px; text-align: left; }
    td { padding: 8px; border: 1px solid #ddd; }
    tr:nth-child(even) { background: #f9f9f9; }
    .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 4px; color: white; background: #4472C4; }
    .btn:hover { background: #365a9e; }
</style></head><body>";
echo "<div class='container'>";

echo "<h1>📝 Post Journal Entries to General Ledger</h1>";
echo "<div class='info'>";
echo "Using PIN: <strong>" . htmlspecialchars($pin) . "</strong><br>";
echo "To use a different PIN, add <code>?pin=YOUR_PIN</code> to the URL";
echo "</div>";

// Get unposted journal entries
$result = db_query($mysqli, "
    SELECT 
        gje.entryid, 
        gje.entrydate, 
        gje.description, 
        COUNT(gj.id) as line_count
    FROM general_journal_entry gje
    LEFT JOIN general_journal gj ON gj.entryid = gje.entryid
    LEFT JOIN general_ledger gl ON gl.refferenceID = gje.entryid AND gl.fromtable = 'general_journal'
    WHERE gl.id IS NULL
      AND gje.PIN = '" . db_escape($mysqli, $pin) . "'
    GROUP BY gje.entryid
    ORDER BY gje.entrydate DESC
");
$unposted_entries = db_fetch_all($result);

if (empty($unposted_entries)) {
    echo "<div class='success'>✅ All journal entries have been posted to general ledger.</div>";
} else {
    echo "<div class='info'>";
    echo "<strong>Found " . count($unposted_entries) . " unposted journal entry/entries:</strong>";
    echo "</div>";
    
    echo "<table>";
    echo "<tr><th>Entry ID</th><th>Date</th><th>Description</th><th>Line Items</th><th>Status</th></tr>";
    
    foreach ($unposted_entries as $entry) {
        // Check if posted
        $result = db_query($mysqli, "
            SELECT COUNT(*) as count 
            FROM general_ledger 
            WHERE refferenceID = " . intval($entry->entryid) . "
              AND fromtable = 'general_journal'
              AND PIN = '" . db_escape($mysqli, $pin) . "'
        ");
        $check = $result->fetch_assoc();
        $is_posted = $check['count'] > 0;
        $status = $is_posted ? "<span style='color: green;'>Posted</span>" : "<span style='color: red;'>Not Posted</span>";
        
        echo "<tr>";
        echo "<td>{$entry->entryid}</td>";
        echo "<td>" . date('Y-m-d', strtotime($entry->entrydate)) . "</td>";
        echo "<td>" . htmlspecialchars($entry->description) . "</td>";
        echo "<td>{$entry->line_count}</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if POST request to post entries
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_entries'])) {
        echo "<hr>";
        echo "<h2>Posting Journal Entries...</h2>";
        
        $posted_count = 0;
        $failed_count = 0;
        $errors = array();
        
        foreach ($unposted_entries as $entry) {
            // Check if already posted
            $check_result = db_query($mysqli, "
                SELECT COUNT(*) as count 
                FROM general_ledger 
                WHERE refferenceID = " . intval($entry->entryid) . "
                  AND fromtable = 'general_journal'
                  AND PIN = '" . db_escape($mysqli, $pin) . "'
            ");
            $check = $check_result->fetch_assoc();
            if ($check['count'] > 0) {
                continue; // Already posted
            }
            
            // Start transaction
            $mysqli->begin_transaction();
            
            try {
                // Get line items for this entry
                $line_result = db_query($mysqli, "
                    SELECT * FROM general_journal 
                    WHERE entryid = " . intval($entry->entryid) . "
                      AND PIN = '" . db_escape($mysqli, $pin) . "'
                ");
                $line_items = db_fetch_all($line_result);
                
                if (empty($line_items)) {
                    throw new Exception("No line items found");
                }
                
                // Verify debits equal credits
                $total_debit = 0;
                $total_credit = 0;
                foreach ($line_items as $item) {
                    $total_debit += floatval($item->debit);
                    $total_credit += floatval($item->credit);
                }
                
                if (abs($total_debit - $total_credit) > 0.01) {
                    throw new Exception("Debits (" . $total_debit . ") do not equal credits (" . $total_credit . ")");
                }
                
                // Create general ledger entry header
                $entry_date = db_escape($mysqli, $entry->entrydate);
                db_query($mysqli, "
                    INSERT INTO general_ledger_entry (date, PIN) 
                    VALUES ('{$entry_date}', '" . db_escape($mysqli, $pin) . "')
                ");
                $ledger_entry_id = $mysqli->insert_id;
                
                if (!$ledger_entry_id) {
                    throw new Exception("Failed to create general_ledger_entry");
                }
                
                // Post each line item
                $journal_id = 5; // Manual journal entry
                foreach ($line_items as $item) {
                    // Get account info
                    $account_result = db_query($mysqli, "
                        SELECT account_type, sub_account_type 
                        FROM account_chart 
                        WHERE account = '" . db_escape($mysqli, $item->account) . "'
                          AND PIN = '" . db_escape($mysqli, $pin) . "'
                        LIMIT 1
                    ");
                    $account_info = $account_result->fetch_assoc();
                    
                    if (!$account_info) {
                        throw new Exception("Account not found: " . $item->account);
                    }
                    
                    $account_type = intval($account_info['account_type']);
                    $sub_account_type_value = !empty($account_info['sub_account_type']) ? intval($account_info['sub_account_type']) : null;
                    $debit = floatval($item->debit);
                    $credit = floatval($item->credit);
                    $description = db_escape($mysqli, $item->description);
                    $account = db_escape($mysqli, $item->account);
                    
                    $sub_account_type_sql = $sub_account_type_value !== null ? $sub_account_type_value : 'NULL';
                    
                    db_query($mysqli, "
                        INSERT INTO general_ledger (
                            journalID, refferenceID, entryid, date, account, 
                            debit, credit, description, account_type, 
                            sub_account_type, linkto, fromtable, PIN
                        ) VALUES (
                            {$journal_id},
                            " . intval($entry->entryid) . ",
                            {$ledger_entry_id},
                            '{$entry_date}',
                            '{$account}',
                            {$debit},
                            {$credit},
                            '{$description}',
                            {$account_type},
                            {$sub_account_type_sql},
                            'general_journal.entryid',
                            'general_journal',
                            '" . db_escape($mysqli, $pin) . "'
                        )
                    ");
                }
                
                // Commit transaction
                $mysqli->commit();
                $posted_count++;
                echo "<div class='success'>✅ Posted journal entry #{$entry->entryid} - " . htmlspecialchars($entry->description) . "</div>";
                
            } catch (Exception $e) {
                // Rollback transaction
                $mysqli->rollback();
                $failed_count++;
                $error_msg = "Failed to post entry #{$entry->entryid}: " . $e->getMessage();
                $errors[] = $error_msg;
                echo "<div class='error'>❌ {$error_msg}</div>";
            }
        }
        
        echo "<hr>";
        echo "<div class='info'>";
        echo "<strong>Summary:</strong><br>";
        echo "Posted: {$posted_count}<br>";
        echo "Failed: {$failed_count}";
        echo "</div>";
        
        if (!empty($errors)) {
            echo "<div class='warning'>";
            echo "<strong>Errors:</strong><br>";
            foreach ($errors as $error) {
                echo "- {$error}<br>";
            }
            echo "</div>";
        }
        
        // Refresh page after 2 seconds
        echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
    } else {
        // Show form to post entries
        echo "<hr>";
        echo "<form method='POST' onsubmit='return confirm(\"Are you sure you want to post all unposted journal entries to general ledger?\");'>";
        echo "<input type='hidden' name='post_entries' value='1'>";
        echo "<button type='submit' class='btn' style='background: #28a745;'>Post All Unposted Entries to General Ledger</button>";
        echo "</form>";
    }
}

echo "<hr>";
echo "<h2>How It Works</h2>";
echo "<div class='info'>";
echo "<p><strong>The posting process:</strong></p>";
echo "<ol>";
echo "<li>Creates an entry in <code>general_ledger_entry</code> table (header)</li>";
echo "<li>For each line item in the journal entry, creates an entry in <code>general_ledger</code> table</li>";
echo "<li>Links the entry to the journal type (journalID) from the <code>journal</code> table</li>";
echo "<li>Includes account type and sub-account type from <code>account_chart</code></li>";
echo "<li>Verifies that debits equal credits before posting</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><em>Generated on: " . date('Y-m-d H:i:s') . "</em></p>";
echo "<p><strong>Note:</strong> Delete this file after use for security purposes.</p>";

echo "</div></body></html>";

// Close database connection
$mysqli->close();
?>
