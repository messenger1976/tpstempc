# Journal Entry vs General Journal: Complete Guide

This document explains the difference between **Journal Entry** and **General Journal**, their relationship, and how the transaction process works in the TAPSTEMCO system.

## Table of Contents

1. [Overview](#overview)
2. [Journal Entry Explained](#journal-entry-explained)
3. [General Journal Explained](#general-journal-explained)
4. [Key Differences](#key-differences)
5. [Relationship and Flow](#relationship-and-flow)
6. [Transaction Process](#transaction-process)
7. [Database Structure](#database-structure)
8. [Code Examples](#code-examples)
9. [Common Scenarios](#common-scenarios)
10. [Troubleshooting](#troubleshooting)

---

## Overview

In the TAPSTEMCO accounting system, there are **two types of journal entry systems** that serve as temporary holding areas before transactions are posted to the **General Ledger**:

1. **Journal Entry** (`journal_entry` table) - Auto-generated from cash transactions
2. **General Journal** (`general_journal_entry` table) - Manual entries created by users

Both systems follow the same workflow:
- **Create** → Entry is created (draft/unposted status)
- **Review** → Entry appears in Journal Entry Review page
- **Approve** → Entry is validated and posted to General Ledger
- **Final** → Entry appears in General Ledger reports

---

## Journal Entry Explained

### What is Journal Entry?

**Journal Entry** refers to entries stored in the `journal_entry` and `journal_entry_items` tables. These entries are **automatically generated** when users save cash receipts or cash disbursements.

### Characteristics

- **Source**: Automatic creation from Cash Receipt and Cash Disbursement modules
- **Purpose**: Temporary storage before posting to General Ledger
- **Status**: Created as draft/unposted, requires approval
- **Tables Used**:
  - `journal_entry` - Header information
  - `journal_entry_items` - Line items (debits/credits)

### When is it Created?

1. **Cash Receipt**: When a user saves a cash receipt transaction
   - Automatically creates `journal_entry` header
   - Automatically creates `journal_entry_items` for each account

2. **Cash Disbursement**: When a user saves a cash disbursement transaction
   - Automatically creates `journal_entry` header
   - Automatically creates `journal_entry_items` for each account

### Database Structure

```sql
-- Header table
journal_entry {
    id: INT (Primary Key)
    entry_date: DATE
    description: VARCHAR
    reference_type: VARCHAR (e.g., 'cash_receipt', 'cash_disbursement')
    reference_id: INT (ID of the source transaction)
    createdby: INT (User ID)
    PIN: VARCHAR (Organization identifier)
    created_at: TIMESTAMP
}

-- Line items table
journal_entry_items {
    id: INT (Primary Key)
    journal_id: INT (or entry_id) - Links to journal_entry.id
    account: INT (Account code from account_chart)
    debit: DECIMAL
    credit: DECIMAL
    description: VARCHAR (optional)
    PIN: VARCHAR (Organization identifier)
}
```

### Example Flow

```
User creates Cash Receipt
    ↓
System automatically creates:
    - journal_entry (header)
    - journal_entry_items (line items)
    ↓
Entry appears in Journal Entry Review (unposted)
    ↓
User approves entry
    ↓
System posts to general_ledger
```

---

## General Journal Explained

### What is General Journal?

**General Journal** refers to entries stored in the `general_journal_entry` and `general_journal` tables. These are **manual journal entries** created directly by users through the Finance module.

### Characteristics

- **Source**: Manual creation by users (Finance → Journal Entry)
- **Purpose**: Temporary storage before posting to General Ledger
- **Status**: Created as draft/unposted, requires approval
- **Tables Used**:
  - `general_journal_entry` - Header information
  - `general_journal` - Line items (debits/credits)

### When is it Created?

When a user manually creates a journal entry:
- Navigate to **Finance → Journal Entry**
- Enter header information (date, description)
- Add line items (accounts, debits, credits)
- Save entry

### Database Structure

```sql
-- Header table
general_journal_entry {
    id: INT (Primary Key)
    entrydate: DATE
    description: VARCHAR
    PIN: VARCHAR (Organization identifier)
}

-- Line items table
general_journal {
    id: INT (Primary Key)
    entryid: INT (Links to general_journal_entry.id)
    account: INT (Account code from account_chart)
    debit: DECIMAL
    credit: DECIMAL
    description: VARCHAR (optional)
    entrydate: DATE
    PIN: VARCHAR (Organization identifier)
}
```

### Example Flow

```
User creates Manual Journal Entry
    ↓
System creates:
    - general_journal_entry (header)
    - general_journal (line items)
    ↓
Entry appears in Journal Entry Review (unposted)
    ↓
User approves entry
    ↓
System posts to general_ledger
```

---

## Key Differences

| Aspect | Journal Entry | General Journal |
|--------|--------------|-----------------|
| **Table Names** | `journal_entry` + `journal_entry_items` | `general_journal_entry` + `general_journal` |
| **Creation Method** | Automatic (from cash transactions) | Manual (user-created) |
| **Source Module** | Cash Receipt, Cash Disbursement | Finance → Journal Entry |
| **Reference Fields** | `reference_type`, `reference_id` (links to source) | No reference fields (standalone) |
| **Posting Function** | `post_journal_entry_to_general_ledger()` | `post_journal_to_general_ledger()` |
| **fromtable Value** | `'journal_entry'` | `'general_journal'` |
| **linkto Value** | `'journal_entry.id'` | `'general_journal.entryid'` |
| **Use Case** | Automated accounting from transactions | Manual adjustments, corrections, allocations |

---

## Relationship and Flow

### Visual Flow Diagram

```
┌─────────────────────────────────────────────────────────┐
│              Journal Entry Sources                       │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  1. Manual Entry                                        │
│     ┌──────────────────────────────┐                     │
│     │ general_journal_entry       │                     │
│     │ general_journal             │                     │
│     └──────────────────────────────┘                     │
│                                                          │
│  2. Cash Receipt                                        │
│     ┌──────────────────────────────┐                     │
│     │ journal_entry                │                     │
│     │ journal_entry_items          │                     │
│     └──────────────────────────────┘                     │
│                                                          │
│  3. Cash Disbursement                                   │
│     ┌──────────────────────────────┐                     │
│     │ journal_entry                │                     │
│     │ journal_entry_items          │                     │
│     └──────────────────────────────┘                     │
│                                                          │
└────────────────────┬────────────────────────────────────┘
                     │
                     │ All entries appear in:
                     │ Finance → Journal Entry Review
                     │
                     ▼
        ┌────────────────────────────┐
        │   Review & Approval        │
        │   (Unposted Status)        │
        └────────────┬───────────────┘
                     │
                     │ User approves/post
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              General Ledger (Final)                       │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  general_ledger_entry (header)                          │
│    - id                                                  │
│    - date                                                │
│    - PIN                                                 │
│                                                          │
│  general_ledger (line items)                            │
│    - journalID (journal type)                           │
│    - refferenceID (original entry ID)                   │
│    - entryid (links to general_ledger_entry)            │
│    - account, debit, credit                             │
│    - fromtable ('journal_entry' or 'general_journal')   │
│    - linkto (reference link)                            │
│                                                          │
│  Used for:                                               │
│  - Financial Reports                                     │
│  - Trial Balance                                         │
│  - General Ledger Reports                                │
│  - Financial Statements                                  │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Key Relationships

1. **Both are Temporary**: Journal Entry and General Journal entries remain in their respective tables until posted to General Ledger.

2. **Unified Review**: Both types appear together in the **Journal Entry Review** page for approval.

3. **Same Destination**: Both post to the same `general_ledger` table, differentiated by the `fromtable` field.

4. **Audit Trail**: The `fromtable` and `linkto` fields in `general_ledger` track the source of each entry.

---

## Transaction Process

### Step-by-Step Process

#### Step 1: Entry Creation

**For Manual Journal Entries:**
```php
// User creates entry via Finance → Journal Entry
// System creates:
general_journal_entry {
    entrydate: '2026-02-09',
    description: 'Monthly depreciation',
    PIN: 'ORG123'
}

general_journal {
    {entryid: 1, account: 5000, debit: 1000, credit: 0},
    {entryid: 1, account: 2000, debit: 0, credit: 1000}
}
// Status: UNPOSTED
```

**For Cash Receipt/Disbursement:**
```php
// User saves cash receipt/disbursement
// System automatically creates:
journal_entry {
    entry_date: '2026-02-09',
    description: 'Cash Receipt #CR-001',
    reference_type: 'cash_receipt',
    reference_id: 123,
    PIN: 'ORG123'
}

journal_entry_items {
    {journal_id: 1, account: 1000, debit: 5000, credit: 0},
    {journal_id: 1, account: 4000, debit: 0, credit: 5000}
}
// Status: UNPOSTED
```

#### Step 2: Review & Approval

All entries appear in **Finance → Journal Entry Review**:
- Manual entries from `general_journal_entry`
- Auto-generated entries from `journal_entry` (cash receipts/disbursements)

The system displays:
- Entry date
- Description
- Total debit/credit
- Source (general_journal or journal_entry)
- Posted status

#### Step 3: Validation Before Posting

Before posting, the system validates:

1. **Balance Check**: Debits must equal Credits
   ```php
   if (abs($total_debit - $total_credit) > 0.01) {
       // Reject - entry is unbalanced
   }
   ```

2. **Account Existence**: All accounts must exist in `account_chart`
   ```php
   $account_info = account_row_info($item->account);
   if (!$account_info) {
       // Reject - account not found
   }
   ```

3. **Duplicate Check**: Entry must not already be posted
   ```php
   if (is_journal_posted($entry_id)) {
       // Skip - already posted
   }
   ```

#### Step 4: Posting to General Ledger

When approved/posted, the system:

**1. Creates `general_ledger_entry` header:**
```php
general_ledger_entry {
    id: auto_increment,
    date: '2026-02-09',
    PIN: 'ORG123'
}
$ledger_entry_id = insert_id();
```

**2. Copies each line item to `general_ledger`:**
```php
// For each line item:
general_ledger {
    journalID: 5,                    // Journal type ID
    refferenceID: original_entry_id, // ID from journal_entry or general_journal_entry
    entryid: ledger_entry_id,        // Links to general_ledger_entry
    date: '2026-02-09',
    account: account_code,
    debit: amount,
    credit: amount,
    description: description,
    account_type: from_account_chart,
    sub_account_type: from_account_chart,
    fromtable: 'journal_entry' or 'general_journal',
    linkto: 'journal_entry.id' or 'general_journal.entryid',
    PIN: 'ORG123'
}
```

#### Step 5: Final Status

After posting:
- Entry remains in original table (`journal_entry` or `general_journal_entry`)
- Entry is copied to `general_ledger`
- Entry appears in General Ledger reports
- Entry status changes to "Posted"

---

## Database Structure

### Complete Table Relationships

```
┌─────────────────────┐
│   journal_entry     │ (Auto-generated from cash transactions)
│   - id              │
│   - entry_date      │
│   - description     │
│   - reference_type  │
│   - reference_id    │
└──────────┬──────────┘
           │
           │ 1:N
           ▼
┌─────────────────────┐
│ journal_entry_items │
│   - journal_id      │ → journal_entry.id
│   - account         │
│   - debit           │
│   - credit          │
└─────────────────────┘

┌─────────────────────┐
│general_journal_entry│ (Manual entries)
│   - id              │
│   - entrydate       │
│   - description     │
└──────────┬──────────┘
           │
           │ 1:N
           ▼
┌─────────────────────┐
│   general_journal   │
│   - entryid         │ → general_journal_entry.id
│   - account         │
│   - debit           │
│   - credit          │
└─────────────────────┘

           │
           │ Posted to ↓
           ▼

┌─────────────────────┐
│general_ledger_entry │ (Final header)
│   - id              │
│   - date            │
│   - PIN             │
└──────────┬──────────┘
           │
           │ 1:N
           ▼
┌─────────────────────┐
│   general_ledger    │ (Final line items)
│   - entryid         │ → general_ledger_entry.id
│   - journalID       │ → journal.id
│   - refferenceID    │ → journal_entry.id OR general_journal_entry.id
│   - fromtable       │ ('journal_entry' or 'general_journal')
│   - linkto          │ (reference link)
│   - account         │ → account_chart.account
│   - debit           │
│   - credit          │
└─────────────────────┘
```

---

## Code Examples

### Creating a Manual Journal Entry

```php
// In finance_model.php
function enter_journal($main_array, $array_items) {
    $pin = current_user()->PIN;
    $this->db->trans_start();
    
    // 1. Create header
    $this->db->insert('general_journal_entry', $main_array);
    $jid = $this->db->insert_id();
    
    // 2. Create line items
    foreach ($array_items as $item) {
        $item['entryid'] = $jid;
        $this->db->insert('general_journal', $item);
    }
    
    $this->db->trans_complete();
    return $jid;
}
```

### Auto-Creating Journal Entry from Cash Receipt

```php
// In cash_receipt_model.php
private function create_journal_entry($receipt_id, $receipt_data, $line_items) {
    // 1. Create header
    $journal_entry = array(
        'entry_date' => $receipt_data['receipt_date'],
        'description' => 'Cash Receipt #' . $receipt_data['receipt_no'],
        'reference_type' => 'cash_receipt',
        'reference_id' => $receipt_id,
        'PIN' => current_user()->PIN
    );
    $this->db->insert('journal_entry', $journal_entry);
    $journal_id = $this->db->insert_id();
    
    // 2. Create line items
    foreach ($line_items as $item) {
        $item['journal_id'] = $journal_id;
        $this->db->insert('journal_entry_items', $item);
    }
}
```

### Posting Journal Entry to General Ledger

```php
// For general_journal entries
function post_journal_to_general_ledger($journal_entry_id, $journal_id = 5) {
    // 1. Get entry from general_journal_entry
    $entry = $this->db->get_where('general_journal_entry', array('id' => $journal_entry_id))->row();
    
    // 2. Get line items from general_journal
    $line_items = $this->db->get_where('general_journal', array('entryid' => $journal_entry_id))->result();
    
    // 3. Create general_ledger_entry header
    $ledger_entry = array('date' => $entry->entrydate, 'PIN' => $pin);
    $this->db->insert('general_ledger_entry', $ledger_entry);
    $ledger_entry_id = $this->db->insert_id();
    
    // 4. Post each line item
    foreach ($line_items as $item) {
        $ledger = array(
            'journalID' => $journal_id,
            'refferenceID' => $journal_entry_id,
            'entryid' => $ledger_entry_id,
            'account' => $item->account,
            'debit' => $item->debit,
            'credit' => $item->credit,
            'fromtable' => 'general_journal',
            'linkto' => 'general_journal.entryid',
            'PIN' => $pin
        );
        $this->db->insert('general_ledger', $ledger);
    }
}

// For journal_entry entries (cash receipts/disbursements)
function post_journal_entry_to_general_ledger($journal_entry_id, $journal_id = 5) {
    // Similar process but reads from journal_entry + journal_entry_items
    // and sets fromtable = 'journal_entry', linkto = 'journal_entry.id'
}
```

### Checking if Entry is Posted

```php
// Check general_journal entry
function is_journal_posted($journal_entry_id) {
    $this->db->where('refferenceID', $journal_entry_id);
    $this->db->where('fromtable', 'general_journal');
    $count = $this->db->count_all_results('general_ledger');
    return $count > 0;
}

// Check journal_entry entry
function is_journal_entry_posted_to_gl($journal_entry_id) {
    $this->db->where('refferenceID', $journal_entry_id);
    $this->db->where('fromtable', 'journal_entry');
    $count = $this->db->count_all_results('general_ledger');
    return $count > 0;
}
```

---

## Common Scenarios

### Scenario 1: User Creates Cash Receipt

1. User enters cash receipt details
2. System automatically creates:
   - `journal_entry` (header)
   - `journal_entry_items` (line items)
3. Entry appears in **Journal Entry Review** (unposted)
4. User approves entry
5. System posts to `general_ledger`
6. Entry appears in General Ledger reports

### Scenario 2: User Creates Manual Journal Entry

1. User navigates to **Finance → Journal Entry**
2. User enters header and line items
3. System creates:
   - `general_journal_entry` (header)
   - `general_journal` (line items)
4. Entry appears in **Journal Entry Review** (unposted)
5. User approves entry
6. System posts to `general_ledger`
7. Entry appears in General Ledger reports

### Scenario 3: Batch Approval

1. Multiple entries accumulate in Journal Entry Review
2. User selects multiple entries
3. User clicks "Batch Approve"
4. System validates each entry:
   - Checks balance (debits = credits)
   - Verifies accounts exist
   - Checks not already posted
5. System posts all valid entries to `general_ledger`
6. User sees success/failure count

### Scenario 4: Editing Unposted Entry

**For Cash Receipt/Disbursement:**
- User edits the original transaction
- System deletes old `journal_entry` and `journal_entry_items`
- System creates new `journal_entry` and `journal_entry_items`
- New entry appears in Journal Entry Review

**For Manual Journal Entry:**
- User edits entry in Finance module
- System updates `general_journal_entry` and `general_journal`
- Updated entry appears in Journal Entry Review

---

## Troubleshooting

### Issue: Entry Not Appearing in Journal Entry Review

**Possible Causes:**
1. Entry already posted to General Ledger
2. Entry belongs to different organization (PIN mismatch)
3. Database query issue

**Solution:**
```php
// Check if entry exists
$entry = $this->db->get_where('journal_entry', array('id' => $id))->row();
// Check if already posted
$is_posted = $this->db->get_where('general_ledger', 
    array('refferenceID' => $id, 'fromtable' => 'journal_entry'))->row();
```

### Issue: Entry Won't Post (Unbalanced)

**Possible Causes:**
1. Debits don't equal credits
2. Rounding errors
3. Missing line items

**Solution:**
```php
// Verify balance
$total_debit = 0;
$total_credit = 0;
foreach ($line_items as $item) {
    $total_debit += floatval($item->debit);
    $total_credit += floatval($item->credit);
}
// Allow small rounding differences (0.01)
if (abs($total_debit - $total_credit) > 0.01) {
    // Entry is unbalanced
}
```

### Issue: Account Not Found Error

**Possible Causes:**
1. Account code doesn't exist in `account_chart`
2. Account belongs to different organization (PIN mismatch)

**Solution:**
```php
// Always verify account exists before posting
$account_info = account_row_info($account_code);
if (!$account_info) {
    log_message('error', 'Account not found: ' . $account_code);
    // Skip or reject entry
}
```

### Issue: Duplicate Posting

**Possible Causes:**
1. Entry posted multiple times
2. Missing duplicate check

**Solution:**
```php
// Always check before posting
if ($this->is_journal_posted($entry_id)) {
    // Skip - already posted
    return true;
}
```

### Issue: Entry Posted but Not in Reports

**Possible Causes:**
1. Wrong date range in report
2. Wrong PIN filter
3. Entry in `general_ledger` but report query incorrect

**Solution:**
```sql
-- Verify entry exists in general_ledger
SELECT * FROM general_ledger 
WHERE refferenceID = ? 
AND fromtable = 'journal_entry' 
AND PIN = ?;

-- Check date range
SELECT * FROM general_ledger 
WHERE date BETWEEN ? AND ?
AND PIN = ?;
```

---

## Summary

### Key Takeaways

1. **Journal Entry** (`journal_entry`) = Auto-generated from cash transactions
2. **General Journal** (`general_journal_entry`) = Manual entries created by users
3. **Both are temporary** - they hold entries before posting to General Ledger
4. **Both post to the same place** - `general_ledger` table
5. **Both require approval** - entries appear in Journal Entry Review
6. **Audit trail maintained** - `fromtable` and `linkto` fields track source

### Workflow Summary

```
Entry Created → Journal Entry Review → Approval → General Ledger → Reports
```

### Important Functions

- `enter_journal()` - Create manual journal entry
- `create_journal_entry()` - Auto-create from cash transaction
- `post_journal_to_general_ledger()` - Post general_journal entries
- `post_journal_entry_to_general_ledger()` - Post journal_entry entries
- `is_journal_posted()` - Check if entry already posted
- `is_journal_entry_posted_to_gl()` - Check if journal_entry posted

---

## Related Documentation

- [HOW_TO_POST_JOURNAL_TO_GENERAL_LEDGER.md](./HOW_TO_POST_JOURNAL_TO_GENERAL_LEDGER.md) - Detailed posting guide
- [JOURNAL_ENTRY_APPROVAL_SYSTEM.md](./JOURNAL_ENTRY_APPROVAL_SYSTEM.md) - Approval system details
- [CASH_RECEIPT_MODULE_README.md](./CASH_RECEIPT_MODULE_README.md) - Cash receipt module
- [CASH_DISBURSEMENT_QUICK_START.md](./CASH_DISBURSEMENT_QUICK_START.md) - Cash disbursement module

---

**Last Updated**: February 9, 2026  
**Version**: 1.0
