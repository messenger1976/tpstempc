<?php
/**
 * Diagnostic script to check journal entry data
 * Run this file directly in browser or via command line
 * URL: http://your-domain/check_journal_data.php
 */

// Include CodeIgniter bootstrap
require_once('index.php');

// Get database instance
$CI =& get_instance();
$CI->load->database();

$pin = '105'; // Change this to your PIN if different

echo "<h2>Journal Entry Data Diagnostic</h2>";
echo "<style>table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; }</style>";

// 1. Check journal_entry table structure
echo "<h3>1. journal_entry Table Structure</h3>";
$columns = $CI->db->query("SHOW COLUMNS FROM journal_entry")->result();
echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
foreach ($columns as $col) {
    echo "<tr><td>{$col->Field}</td><td>{$col->Type}</td><td>{$col->Null}</td><td>{$col->Key}</td></tr>";
}
echo "</table>";

// 2. Check journal_entry_items table structure
echo "<h3>2. journal_entry_items Table Structure</h3>";
$columns = $CI->db->query("SHOW COLUMNS FROM journal_entry_items")->result();
echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
foreach ($columns as $col) {
    echo "<tr><td>{$col->Field}</td><td>{$col->Type}</td><td>{$col->Null}</td><td>{$col->Key}</td></tr>";
}
echo "</table>";

// 3. Check journal_entry records for cash receipt and disbursement
echo "<h3>3. journal_entry Records (Cash Receipt & Disbursement)</h3>";
$entries = $CI->db->query(
    "SELECT id, entry_date, description, reference_type, reference_id, createdby, PIN 
     FROM journal_entry 
     WHERE PIN = ? AND reference_type IN ('cash_receipt', 'cash_disbursement')
     ORDER BY entry_date DESC, id DESC
     LIMIT 20",
    array($pin)
)->result();

if (empty($entries)) {
    echo "<p><strong>No journal_entry records found for PIN {$pin}</strong></p>";
} else {
    echo "<p>Found " . count($entries) . " entries</p>";
    echo "<table><tr><th>ID</th><th>Date</th><th>Type</th><th>Description</th><th>Reference ID</th></tr>";
    foreach ($entries as $entry) {
        echo "<tr>";
        echo "<td>{$entry->id}</td>";
        echo "<td>{$entry->entry_date}</td>";
        echo "<td>{$entry->reference_type}</td>";
        echo "<td>" . htmlspecialchars($entry->description) . "</td>";
        echo "<td>{$entry->reference_id}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Check journal_entry_items for each entry
if (!empty($entries)) {
    echo "<h3>4. Line Items for Each Entry</h3>";
    foreach ($entries as $entry) {
        echo "<h4>Entry ID: {$entry->id} ({$entry->reference_type})</h4>";
        
        // Try journal_id first
        $items_journal_id = $CI->db->query(
            "SELECT id, journal_id, account, debit, credit, PIN 
             FROM journal_entry_items 
             WHERE journal_id = ?",
            array($entry->id)
        )->result();
        
        // Try entry_id if journal_id didn't work
        $items_entry_id = array();
        if (empty($items_journal_id)) {
            $items_entry_id = $CI->db->query(
                "SELECT id, entry_id, account, debit, credit, PIN 
                 FROM journal_entry_items 
                 WHERE entry_id = ?",
                array($entry->id)
            )->result();
        }
        
        $items = !empty($items_journal_id) ? $items_journal_id : $items_entry_id;
        
        if (empty($items)) {
            echo "<p style='color: red;'><strong>NO LINE ITEMS FOUND for Entry ID {$entry->id}</strong></p>";
            echo "<p>Checked columns: journal_id, entry_id</p>";
        } else {
            $total_debit = 0;
            $total_credit = 0;
            echo "<table><tr><th>Item ID</th><th>Link Column</th><th>Account</th><th>Debit</th><th>Credit</th><th>PIN</th></tr>";
            foreach ($items as $item) {
                $link_col = isset($item->journal_id) ? 'journal_id: ' . $item->journal_id : 'entry_id: ' . (isset($item->entry_id) ? $item->entry_id : 'N/A');
                $total_debit += floatval($item->debit);
                $total_credit += floatval($item->credit);
                echo "<tr>";
                echo "<td>{$item->id}</td>";
                echo "<td>{$link_col}</td>";
                echo "<td>{$item->account}</td>";
                echo "<td>" . number_format($item->debit, 2) . "</td>";
                echo "<td>" . number_format($item->credit, 2) . "</td>";
                echo "<td>" . ($item->PIN ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p><strong>Total Debit: " . number_format($total_debit, 2) . " | Total Credit: " . number_format($total_credit, 2) . " | Line Count: " . count($items) . "</strong></p>";
        }
        echo "<hr>";
    }
}

// 5. Check if entries are posted to GL
if (!empty($entries)) {
    echo "<h3>5. Posted Status Check</h3>";
    echo "<table><tr><th>Entry ID</th><th>Posted to GL?</th><th>GL Records</th></tr>";
    foreach ($entries as $entry) {
        $gl_records = $CI->db->query(
            "SELECT COUNT(*) as cnt FROM general_ledger 
             WHERE refferenceID = ? AND fromtable = 'journal_entry' AND PIN = ?",
            array($entry->id, $pin)
        )->row();
        
        $is_posted = ($gl_records && $gl_records->cnt > 0);
        echo "<tr>";
        echo "<td>{$entry->id}</td>";
        echo "<td>" . ($is_posted ? '<span style="color: green;">YES</span>' : '<span style="color: orange;">NO</span>') . "</td>";
        echo "<td>{$gl_records->cnt}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 6. Summary query - all entries with their line item counts
echo "<h3>6. Summary: All Entries with Line Item Counts</h3>";
$summary = $CI->db->query(
    "SELECT 
        je.id,
        je.entry_date,
        je.reference_type,
        je.description,
        COUNT(jei.id) as line_item_count,
        COALESCE(SUM(jei.debit), 0) as total_debit,
        COALESCE(SUM(jei.credit), 0) as total_credit
     FROM journal_entry je
     LEFT JOIN journal_entry_items jei ON jei.journal_id = je.id
     WHERE je.PIN = ? AND je.reference_type IN ('cash_receipt', 'cash_disbursement')
     GROUP BY je.id
     ORDER BY je.entry_date DESC, je.id DESC
     LIMIT 20",
    array($pin)
)->result();

if (!empty($summary)) {
    echo "<table><tr><th>Entry ID</th><th>Date</th><th>Type</th><th>Line Items</th><th>Total Debit</th><th>Total Credit</th></tr>";
    foreach ($summary as $sum) {
        echo "<tr>";
        echo "<td>{$sum->id}</td>";
        echo "<td>{$sum->entry_date}</td>";
        echo "<td>{$sum->reference_type}</td>";
        echo "<td>{$sum->line_item_count}</td>";
        echo "<td>" . number_format($sum->total_debit, 2) . "</td>";
        echo "<td>" . number_format($sum->total_credit, 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No summary data found</p>";
}

echo "<hr><p><em>Diagnostic completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>
