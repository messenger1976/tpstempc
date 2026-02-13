# How to Post Journal Entries to General Ledger

This document explains how journal entries are posted to the `general_ledger` table in the TAPSTEMCO system.

## Overview

Journal entries are posted to the general ledger through a two-step process:
1. Create a header entry in `general_ledger_entry` table
2. Create line items in `general_ledger` table for each account

## Database Structure

### Tables Involved:

1. **`journal`** - Journal types (e.g., "Journal Received Money", "Sales Journal", etc.)
   - `id` - Journal type ID
   - `type` - Journal type name

2. **`general_journal_entry`** - Journal entry headers (for manual entries)
   - `entryid` - Entry ID
   - `entrydate` - Entry date
   - `description` - Description

3. **`general_journal`** - Journal entry line items (for manual entries)
   - `entryid` - Links to general_journal_entry
   - `account` - Account code
   - `debit` - Debit amount
   - `credit` - Credit amount
   - `description` - Line description
   - `entrydate` - Entry date

4. **`general_ledger_entry`** - General ledger entry headers
   - `id` - Entry ID
   - `date` - Entry date
   - `PIN` - Organization PIN

5. **`general_ledger`** - General ledger line items (the posted entries)
   - `journalID` - Journal type ID (from `journal` table)
   - `refferenceID` - Reference to source transaction
   - `entryid` - Links to general_ledger_entry
   - `date` - Transaction date
   - `account` - Account code
   - `debit` - Debit amount
   - `credit` - Credit amount
   - `description` - Description
   - `account_type` - Account type (from account_chart)
   - `sub_account_type` - Sub account type
   - `fromtable` - Source table name
   - `linkto` - Link reference
   - `PIN` - Organization PIN

## Posting Process

### Step-by-Step Process:

1. **Create General Ledger Entry Header**
   ```php
   $ledger_entry = array(
       'date' => $entry_date,
       'PIN' => $pin
   );
   $this->db->insert('general_ledger_entry', $ledger_entry);
   $ledger_entry_id = $this->db->insert_id();
   ```

2. **For Each Line Item, Create General Ledger Entry**
   ```php
   $ledger = array(
       'journalID' => $journal_id,           // Journal type ID from journal table
       'refferenceID' => $reference_id,      // ID of source transaction
       'entryid' => $ledger_entry_id,        // From step 1
       'date' => $entry_date,
       'account' => $account_code,
       'debit' => $debit_amount,
       'credit' => $credit_amount,
       'description' => $description,
       'account_type' => $account_info->account_type,
       'sub_account_type' => $account_info->sub_account_type,
       'fromtable' => 'general_journal',     // Source table
       'linkto' => 'general_journal.entryid', // Link reference
       'PIN' => $pin
   );
   $this->db->insert('general_ledger', $ledger);
   ```

## Example: Posting Manual Journal Entries

The `enter_journal()` function in `application/models/finance_model.php` shows the complete process:

```php
function enter_journal($main_array, $array_items) {
    $pin = current_user()->PIN;
    $this->db->trans_start();

    // 1. Create journal entry header
    $this->db->insert('general_journal_entry', $main_array);
    $jid = $this->db->insert_id();

    // 2. Create general ledger entry header
    $ledger_entry = array('date' => $main_array['entrydate']);
    $this->db->insert('general_ledger_entry', $ledger_entry);
    $ledger_entry_id = $this->db->insert_id();

    // 3. Base ledger data structure
    $ledger = array(
        'journalID' => 5,  // Journal ID for manual journal entries
        'refferenceID' => $jid,
        'entryid' => $ledger_entry_id,
        'date' => $main_array['entrydate'],
        'linkto' => 'general_journal.entryid',
        'fromtable' => 'general_journal',
        'PIN' => $pin
    );

    // 4. Post each line item to general ledger
    foreach ($array_items as $key => $value) {
        // Save to general_journal
        $value['entryid'] = $jid;
        $this->db->insert('general_journal', $value);

        // Post to general_ledger
        $ledger['account'] = $value['account'];
        $ledger['credit'] = $value['credit'];
        $ledger['description'] = $value['description'];
        $ledger['debit'] = $value['debit'];
        $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
        $this->db->insert('general_ledger', $ledger);
    }

    $this->db->trans_complete();
    return $jid;
}
```

## Posting Unposted Journal Entries

If you have journal entries in `general_journal` that haven't been posted to `general_ledger`, you can create a function to post them:

### Function to Post Unposted Journal Entries

```php
function post_unposted_journal_entries($journal_entry_id = null) {
    $pin = current_user()->PIN;
    $this->db->trans_start();

    // Get unposted journal entries
    $this->db->select('gje.*');
    $this->db->from('general_journal_entry gje');
    $this->db->join('general_ledger gl', 'gl.refferenceID = gje.entryid AND gl.fromtable = "general_journal"', 'left');
    $this->db->where('gl.id IS NULL'); // Not yet posted
    $this->db->where('gje.PIN', $pin);
    
    if ($journal_entry_id) {
        $this->db->where('gje.entryid', $journal_entry_id);
    }
    
    $unposted_entries = $this->db->get()->result();

    foreach ($unposted_entries as $entry) {
        // Get line items for this entry
        $line_items = $this->db->where('entryid', $entry->entryid)
                               ->get('general_journal')
                               ->result();

        // Create general ledger entry header
        $ledger_entry = array(
            'date' => $entry->entrydate,
            'PIN' => $pin
        );
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();

        // Base ledger data
        $ledger = array(
            'journalID' => 5, // Manual journal entry
            'refferenceID' => $entry->entryid,
            'entryid' => $ledger_entry_id,
            'date' => $entry->entrydate,
            'linkto' => 'general_journal.entryid',
            'fromtable' => 'general_journal',
            'PIN' => $pin
        );

        // Post each line item
        foreach ($line_items as $item) {
            $account_info = account_row_info($item->account);
            if (!$account_info) {
                continue; // Skip if account doesn't exist
            }

            $ledger['account'] = $item->account;
            $ledger['debit'] = $item->debit;
            $ledger['credit'] = $item->credit;
            $ledger['description'] = $item->description;
            $ledger['account_type'] = $account_info->account_type;
            $ledger['sub_account_type'] = isset($account_info->sub_account_type) ? $account_info->sub_account_type : null;

            $this->db->insert('general_ledger', $ledger);
        }
    }

    $this->db->trans_complete();
    return $this->db->trans_status();
}
```

## Important Notes

1. **Journal ID**: Each journal type has a specific ID in the `journal` table:
   - ID 1: Sales Journal
   - ID 2: Member Registration / Loan Processing
   - ID 3: Sales Invoice
   - ID 4: Loan Journal
   - ID 5: Manual Journal Entry
   - ID 6: Purchase Journal
   - ID 7: Contribution Journal
   - ID 8: Beginning Balance

2. **Account Type**: Always get account type from `account_chart` using `account_row_info()` function

3. **Transaction Safety**: Always use database transactions (`$this->db->trans_start()` / `trans_complete()`) when posting multiple entries

4. **PIN**: Always include PIN for multi-tenancy support

5. **Double-Entry**: Ensure debits equal credits before posting

## Checking Posted Status

To check if a journal entry has been posted:

```php
function is_journal_posted($journal_entry_id) {
    $this->db->where('refferenceID', $journal_entry_id);
    $this->db->where('fromtable', 'general_journal');
    $this->db->where('PIN', current_user()->PIN);
    $count = $this->db->count_all_results('general_ledger');
    return $count > 0;
}
```

## Common Journal IDs by Module

| Journal ID | Journal Type | Used By |
|------------|--------------|---------|
| 1 | Sales Journal | Customer/Sales Module |
| 2 | Member Registration | Member Registration, Loan Processing |
| 3 | Sales Invoice | Sales Invoice Module |
| 4 | Loan Journal | Loan Module |
| 5 | Manual Journal Entry | Finance → Journal Entry |
| 6 | Purchase Journal | Supplier/Purchase Module |
| 7 | Contribution Journal | Contribution Module |
| 8 | Beginning Balance | Beginning Balance Module, Loan Beginning Balances ⚠️ |

## Troubleshooting

1. **Entries not appearing in General Ledger**:
   - Check if `general_ledger_entry` was created
   - Verify journalID matches an existing journal type
   - Check PIN matches current user's PIN
   - Ensure account exists in account_chart

2. **Account Type Missing**:
   - Always use `account_row_info($account_code)` to get account details
   - Verify account exists before posting

3. **Transaction Errors**:
   - Ensure database transaction is properly closed
   - Check for foreign key constraints
   - Verify all required fields are provided

4. **Unbalanced Entries (Loan Beginning Balances)**:
   - ⚠️ **Known Issue**: Loan beginning balances create DEBIT-only entries without CREDIT entries
   - This violates double-entry accounting principles
   - See `LOAN_BEGINNING_BALANCES_MODULE.md` for details and recommendations
   - Manual adjustment entries may be required to balance the general ledger

## Important Notes About Beginning Balances

### Regular Beginning Balances (Finance Module)
- Creates balanced entries from `beginning_balances` table
- Uses both `debit` and `credit` fields from the table
- Properly implements double-entry accounting

### Loan Beginning Balances (Loan Module) ⚠️
- **Current Limitation**: Creates DEBIT-only entries (principal, interest, penalty receivables)
- **Missing**: Corresponding CREDIT entries to balance the debits
- **Impact**: Results in unbalanced general ledger entries
- **Recommendation**: Add credit entries to equity/retained earnings or opening balance equity account
- See `LOAN_BEGINNING_BALANCES_MODULE.md` section "Known Issues and Limitations" for details
