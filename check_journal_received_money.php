<?php
/**
 * Check Journal Received Money Usage
 * 
 * This script identifies which modules are using "Journal Received Money"
 * and provides a comprehensive report of all related transactions.
 * 
 * SECURITY: Delete after use!
 */

// Direct database connection to bypass CodeIgniter authentication
// Define constants to satisfy database.php requirements
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
        die("Query failed: " . $mysqli->error . "<br>Query: " . $query);
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

echo "<!DOCTYPE html><html><head><title>Journal Received Money Analysis</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    h1 { color: #333; border-bottom: 3px solid #4472C4; padding-bottom: 10px; }
    h2 { color: #555; margin-top: 30px; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
    h3 { color: #666; margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th { background: #4472C4; color: white; padding: 10px; text-align: left; }
    td { padding: 8px; border: 1px solid #ddd; }
    tr:nth-child(even) { background: #f9f9f9; }
    .success { color: #28a745; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .info { color: #17a2b8; font-weight: bold; }
    .section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #4472C4; }
    .count { font-size: 1.2em; font-weight: bold; color: #4472C4; }
</style></head><body>";
echo "<div class='container'>";

echo "<h1>📊 Journal Received Money - Module Usage Analysis</h1>";

// Step 1: Find journal types matching "Received Money" or similar
echo "<h2>Step 1: Finding Journal Types</h2>";
$result = db_query($mysqli, "
    SELECT id, type 
    FROM journal 
    WHERE type LIKE '%Received Money%' 
       OR type LIKE '%Received%Money%'
       OR type LIKE '%Money Received%'
       OR type LIKE '%Receive%Money%'
    ORDER BY id
");
$journal_types = db_fetch_all($result);

if (empty($journal_types)) {
    echo "<div class='warning'>⚠️ No journal types found matching 'Received Money' pattern.</div>";
    echo "<p>Searching for all journal types to help identify the correct one...</p>";
    
    // Show all journal types
    $result = db_query($mysqli, "SELECT id, type FROM journal ORDER BY id");
    $all_journals = db_fetch_all($result);
    if (!empty($all_journals)) {
        echo "<h3>All Available Journal Types:</h3>";
        echo "<table><tr><th>Journal ID</th><th>Journal Type</th></tr>";
        foreach ($all_journals as $journal) {
            echo "<tr><td>{$journal->id}</td><td>" . htmlspecialchars($journal->type) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ No journal types found in the database.</div>";
    }
} else {
    echo "<div class='success'>✅ Found " . count($journal_types) . " matching journal type(s):</div>";
    echo "<table><tr><th>Journal ID</th><th>Journal Type</th></tr>";
    foreach ($journal_types as $journal) {
        echo "<tr><td><strong>{$journal->id}</strong></td><td><strong>" . htmlspecialchars($journal->type) . "</strong></td></tr>";
    }
    echo "</table>";
}

// Step 2: Find all transactions using these journal IDs
if (!empty($journal_types)) {
    echo "<h2>Step 2: Transactions Using These Journal Types</h2>";
    
    $journal_ids = array();
    foreach ($journal_types as $journal) {
        $journal_ids[] = $journal->id;
    }
    $journal_ids_str = implode(',', $journal_ids);
    
    // Get transaction summary
    $result = db_query($mysqli, "
        SELECT 
            journalID,
            (SELECT type FROM journal WHERE id = general_ledger.journalID) as journal_type,
            COUNT(*) as transaction_count,
            COUNT(DISTINCT entryid) as unique_entries,
            COUNT(DISTINCT date) as unique_dates,
            MIN(date) as first_transaction,
            MAX(date) as last_transaction,
            SUM(debit) as total_debit,
            SUM(credit) as total_credit
        FROM general_ledger
        WHERE journalID IN ($journal_ids_str)
        GROUP BY journalID
    ");
    $transaction_summary = db_fetch_all($result);
    
    if (!empty($transaction_summary)) {
        echo "<div class='section'>";
        echo "<h3>Transaction Summary</h3>";
        echo "<table>";
        echo "<tr><th>Journal ID</th><th>Journal Type</th><th>Total Transactions</th><th>Unique Entries</th><th>Date Range</th><th>Total Debit</th><th>Total Credit</th></tr>";
        foreach ($transaction_summary as $summary) {
            echo "<tr>";
            echo "<td>{$summary->journalID}</td>";
            echo "<td><strong>" . htmlspecialchars($summary->journal_type) . "</strong></td>";
            echo "<td class='count'>{$summary->transaction_count}</td>";
            echo "<td>{$summary->unique_entries}</td>";
            echo "<td>" . date('Y-m-d', strtotime($summary->first_transaction)) . " to " . date('Y-m-d', strtotime($summary->last_transaction)) . "</td>";
            echo "<td>" . number_format($summary->total_debit, 2) . "</td>";
            echo "<td>" . number_format($summary->total_credit, 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
    // Step 3: Identify which modules are using these journal IDs
    echo "<h2>Step 3: Module Identification</h2>";
    
    // Check general_ledger for fromtable references
    $result = db_query($mysqli, "
        SELECT 
            fromtable,
            linkto,
            COUNT(*) as usage_count,
            COUNT(DISTINCT refferenceID) as unique_references,
            MIN(date) as first_use,
            MAX(date) as last_use
        FROM general_ledger
        WHERE journalID IN ($journal_ids_str)
          AND fromtable IS NOT NULL
          AND fromtable != ''
        GROUP BY fromtable, linkto
        ORDER BY usage_count DESC
    ");
    $module_usage = db_fetch_all($result);
    
    if (!empty($module_usage)) {
        echo "<div class='section'>";
        echo "<h3>Modules Using This Journal Type</h3>";
        echo "<table>";
        echo "<tr><th>Source Table</th><th>Link To</th><th>Usage Count</th><th>Unique References</th><th>Date Range</th></tr>";
        foreach ($module_usage as $module) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($module->fromtable) . "</strong></td>";
            echo "<td>" . htmlspecialchars($module->linkto) . "</td>";
            echo "<td class='count'>{$module->usage_count}</td>";
            echo "<td>{$module->unique_references}</td>";
            echo "<td>" . date('Y-m-d', strtotime($module->first_use)) . " to " . date('Y-m-d', strtotime($module->last_use)) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
        // Map tables to modules
        echo "<h3>Module Mapping</h3>";
        echo "<div class='section'>";
        $module_map = array(
            'member_registrationfee' => 'Member Registration Module',
            'loanprocessing_fee' => 'Loan Processing Module',
            'sales_invoice' => 'Sales Invoice Module',
            'sales_quote' => 'Sales Quote Module',
            'purchase_invoice' => 'Purchase Invoice Module',
            'purchase_order' => 'Purchase Order Module',
            'contribution_settings' => 'Contribution Module',
            'general_journal' => 'Manual Journal Entry',
            'cash_receipts' => 'Cash Receipt Module (New)',
            'cash_disbursements' => 'Cash Disbursement Module (New)'
        );
        
        echo "<ul>";
        foreach ($module_usage as $module) {
            $table_name = $module->fromtable;
            $module_name = isset($module_map[$table_name]) ? $module_map[$table_name] : "Unknown Module ($table_name)";
            echo "<li><strong>$module_name</strong> - {$module->usage_count} transactions</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Step 4: Check new journal_entry table (for cash receipts/disbursements)
    echo "<h2>Step 4: New Journal Entry System (Cash Receipt/Disbursement)</h2>";
    
    // Check if journal_entry table exists
    $result = db_query($mysqli, "SHOW TABLES LIKE 'journal_entry'");
    $table_exists = $result->num_rows > 0;
    
    if ($table_exists) {
        $result = db_query($mysqli, "
            SELECT 
                reference_type,
                COUNT(*) as entry_count,
                COUNT(DISTINCT reference_id) as unique_references,
                MIN(entry_date) as first_entry,
                MAX(entry_date) as last_entry
            FROM journal_entry
            WHERE reference_type IS NOT NULL
            GROUP BY reference_type
            ORDER BY entry_count DESC
        ");
        $new_journal_entries = db_fetch_all($result);
        
        if (!empty($new_journal_entries)) {
            echo "<div class='section'>";
            echo "<h3>New Journal Entry System Usage</h3>";
            echo "<p>These are from the newer journal_entry table (used by Cash Receipt and Cash Disbursement modules):</p>";
            echo "<table>";
            echo "<tr><th>Reference Type</th><th>Entry Count</th><th>Unique References</th><th>Date Range</th></tr>";
            foreach ($new_journal_entries as $entry) {
                $module_name = '';
                switch($entry->reference_type) {
                    case 'cash_receipt':
                        $module_name = ' (Cash Receipt Module)';
                        break;
                    case 'cash_disbursement':
                        $module_name = ' (Cash Disbursement Module)';
                        break;
                    case 'journal':
                        $module_name = ' (Manual Journal Entry)';
                        break;
                }
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($entry->reference_type) . "</strong>$module_name</td>";
                echo "<td class='count'>{$entry->entry_count}</td>";
                echo "<td>{$entry->unique_references}</td>";
                echo "<td>" . date('Y-m-d', strtotime($entry->first_entry)) . " to " . date('Y-m-d', strtotime($entry->last_entry)) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ The new journal_entry table does not exist. This is normal if Cash Receipt/Disbursement modules haven't been installed.</div>";
    }
    
    // Step 5: Sample transactions
    echo "<h2>Step 5: Sample Transactions</h2>";
    $result = db_query($mysqli, "
        SELECT 
            gl.*,
            (SELECT type FROM journal WHERE id = gl.journalID) as journal_type,
            ac.name as account_name
        FROM general_ledger gl
        LEFT JOIN account_chart ac ON gl.account = ac.account AND gl.PIN = ac.PIN
        WHERE gl.journalID IN ($journal_ids_str)
        ORDER BY gl.date DESC, gl.entryid DESC
        LIMIT 20
    ");
    $sample_transactions = db_fetch_all($result);
    
    if (!empty($sample_transactions)) {
        echo "<div class='section'>";
        echo "<h3>Recent Transactions (Last 20)</h3>";
        echo "<table>";
        echo "<tr><th>Date</th><th>Account</th><th>Account Name</th><th>Description</th><th>Debit</th><th>Credit</th><th>From Table</th></tr>";
        foreach ($sample_transactions as $trans) {
            echo "<tr>";
            echo "<td>" . date('Y-m-d', strtotime($trans->date)) . "</td>";
            echo "<td>{$trans->account}</td>";
            echo "<td>" . htmlspecialchars($trans->account_name) . "</td>";
            echo "<td>" . htmlspecialchars($trans->description) . "</td>";
            echo "<td>" . number_format($trans->debit, 2) . "</td>";
            echo "<td>" . number_format($trans->credit, 2) . "</td>";
            echo "<td>" . htmlspecialchars($trans->fromtable) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
}

// Step 6: Code references
echo "<h2>Step 6: Code References</h2>";
echo "<div class='section'>";
echo "<h3>Files That May Use This Journal Type</h3>";
echo "<p>Based on the modules identified above, check these files:</p>";
echo "<ul>";

if (!empty($module_usage)) {
    foreach ($module_usage as $module) {
        $table_name = $module->fromtable;
        switch($table_name) {
            case 'member_registrationfee':
                echo "<li><strong>Member Registration:</strong> <code>application/models/member_model.php</code> (uses journalID 2)</li>";
                break;
            case 'loanprocessing_fee':
                echo "<li><strong>Loan Processing:</strong> <code>application/models/loan_model.php</code> (uses journalID 2)</li>";
                break;
            case 'sales_invoice':
            case 'sales_quote':
                echo "<li><strong>Sales Module:</strong> <code>application/models/customer_model.php</code> (uses journalID 1, 3)</li>";
                break;
            case 'purchase_invoice':
            case 'purchase_order':
                echo "<li><strong>Purchase Module:</strong> <code>application/models/supplier_model.php</code> (uses journalID 6)</li>";
                break;
            case 'contribution_settings':
                echo "<li><strong>Contribution Module:</strong> <code>application/models/contribution_model.php</code> (uses journalID 7)</li>";
                break;
            case 'general_journal':
                echo "<li><strong>Manual Journal Entry:</strong> <code>application/controllers/finance.php</code> (journalentry function)</li>";
                break;
        }
    }
}

echo "<li><strong>Cash Receipt Module:</strong> <code>application/models/cash_receipt_model.php</code> (uses reference_type 'cash_receipt')</li>";
echo "<li><strong>Cash Disbursement Module:</strong> <code>application/models/cash_disbursement_model.php</code> (uses reference_type 'cash_disbursement')</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<div class='section'>";
if (!empty($journal_types)) {
    echo "<p class='success'>✅ Found journal type(s) matching 'Received Money' pattern.</p>";
    echo "<p><strong>Total Journal Types Found:</strong> " . count($journal_types) . "</p>";
    if (!empty($transaction_summary)) {
        $total_transactions = 0;
        foreach ($transaction_summary as $summary) {
            $total_transactions += $summary->transaction_count;
        }
        echo "<p><strong>Total Transactions:</strong> <span class='count'>$total_transactions</span></p>";
    }
    if (!empty($module_usage)) {
        echo "<p><strong>Modules Using This Journal:</strong> " . count($module_usage) . "</p>";
    }
} else {
    echo "<p class='warning'>⚠️ No journal types found matching 'Received Money'. Please check the journal table manually or review all journal types listed above.</p>";
}
echo "</div>";

echo "<hr>";
echo "<p><em>Generated on: " . date('Y-m-d H:i:s') . "</em></p>";
echo "<p><strong>Note:</strong> Delete this file after use for security purposes.</p>";

echo "</div></body></html>";

// Close database connection
$mysqli->close();
?>
