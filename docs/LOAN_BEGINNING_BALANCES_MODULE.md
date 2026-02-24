# Loan Beginning Balances Module

## Overview
This module allows users to setup beginning balances for existing loans in the system. It is integrated into the Loan Management menu and provides full CRUD (Create, Read, Update, Delete) operations along with posting to the General Ledger.

## Features
- **Create** new loan beginning balances for existing loans
- **Edit** loan beginning balances (before posting)
- **Delete** loan beginning balances (before posting)
- **Post** loan beginning balances to the General Ledger
- Filter by fiscal year
- Track principal, interest, and penalty balances separately
- Automatic total calculation
- Prevent duplicate entries for same fiscal year, member, and loan product
- Prevent editing/deletion after posting to GL

## Database Setup

### Table Creation
Run the table creation script to create the `loan_beginning_balances` table:
```
http://your-domain.com/create_loan_beginning_balances_table.php
```

Or manually execute this SQL:
```sql
CREATE TABLE IF NOT EXISTS `loan_beginning_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fiscal_year_id` int(11) NOT NULL COMMENT 'Reference to fiscal_year table',
  `loan_id` varchar(50) DEFAULT NULL COMMENT 'Loan ID/Number',
  `member_id` varchar(50) NOT NULL COMMENT 'Member ID',
  `loan_product_id` int(11) NOT NULL COMMENT 'Reference to loan_product table',
  `principal_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning principal balance',
  `interest_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning interest balance',
  `penalty_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Beginning penalty balance',
  `total_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total beginning balance',
  `disbursement_date` date DEFAULT NULL COMMENT 'Original loan disbursement date',
  `description` text COMMENT 'Description/notes for the beginning balance',
  `posted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Posted to General Ledger, 0=Not Posted',
  `posted_date` datetime DEFAULT NULL COMMENT 'Date when posted to General Ledger',
  `posted_by` int(11) DEFAULT NULL COMMENT 'User ID who posted the balance',
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `PIN` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pin` (`PIN`),
  KEY `idx_fiscal_year` (`fiscal_year_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_loan_product` (`loan_product_id`),
  KEY `idx_posted` (`posted`),
  UNIQUE KEY `unique_fiscal_member_product` (`PIN`, `fiscal_year_id`, `member_id`, `loan_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Beginning balances for Existing Loans by Fiscal Year';
```

## Files Modified/Created

### Models
- **application/models/loan_model.php** - Added loan beginning balance methods:
  - `loan_beginning_balance_list($fiscal_year_id, $id)`
  - `loan_beginning_balance_create($data)`
  - `loan_beginning_balance_update($data, $id)`
  - `loan_beginning_balance_delete($id)`
  - `loan_beginning_balance_post_to_ledger($id)`
  - `check_loan_beginning_balance_exists($fiscal_year_id, $member_id, $loan_product_id)`

### Controllers
- **application/controllers/loan.php** - Added controller methods:
  - `loan_beginning_balance_list()` - Display list of loan beginning balances
  - `loan_beginning_balance_create($id)` - Create or edit form
  - `loan_beginning_balance_delete($id)` - Delete a balance
  - `loan_beginning_balance_post($id)` - Post to General Ledger

### Views
- **application/views/loan/loan_beginning_balance_list.php** - List view with fiscal year filter
- **application/views/loan/loan_beginning_balance_form.php** - Create/edit form

### Language Files
- **application/language/english/loan_lang.php** - Added language strings for loan beginning balances

### Menu
- **application/views/menu.php** - Added menu item under Loan Management

### Database Scripts
- **create_loan_beginning_balances_table.php** - Database table creation script

## Usage

### Accessing the Module
1. Navigate to **Loan Management** menu
2. Click on **Loan Beginning Balances**

### Creating a Beginning Balance
1. Click **Create Loan Beginning Balance** button
2. Select the fiscal year
3. Enter member ID
4. Select loan product
5. Enter balances (principal, interest, penalty)
6. Optionally enter loan ID and disbursement date
7. Add description if needed
8. Click **Create Beginning Balance**

### Posting to General Ledger
1. Select a fiscal year to view balances
2. Click **Post to General Ledger** for the balance you want to post
3. Confirm the action
4. The balance will be posted with entries created for:
   - Principal balance (if > 0)
   - Interest balance (if > 0)
   - Penalty balance (if > 0)

### Editing a Balance
- Only unposted balances can be edited
- Click the **Edit** button
- Make changes and save

### Deleting a Balance
- Only unposted balances can be deleted
- Click the **Delete** button
- Confirm the deletion

## General Ledger Posting Details

When a loan beginning balance is posted to the General Ledger:
- A general ledger entry header is created with the fiscal year start date
- Separate ledger entries are created for principal, interest, and penalty (if > 0)
- The accounts used are from the loan product configuration:
  - `loan_principle_account` for principal
  - `loan_interest_account` for interest
  - `loan_penalt_account` for penalty
- Journal ID 8 is used (Beginning Balance journal)
- The balance is marked as posted and cannot be edited or deleted

### Accounting Entries Created

**Current Implementation (As of 2026-01-10):**

For each balance type (principal, interest, penalty), the following entry is created:
- **DEBIT**: Loan account (principal/interest/penalty receivable account)
- **CREDIT**: 0 (no credit entry is created)

**Example Entry:**
```
DEBIT:  Loan Principal Account    $1,000.00
DEBIT:  Loan Interest Account     $100.00
DEBIT:  Loan Penalty Account      $50.00
CREDIT: (none)                    $0.00
```

**⚠️ Important Limitation - Unbalanced Entries:**

The current implementation creates **DEBIT-only entries** with no corresponding CREDIT entries. This violates double-entry accounting principles and will result in:

- Unbalanced general ledger entries
- Incorrect trial balances
- Financial reports showing errors
- The accounting equation (Assets = Liabilities + Equity) not balancing

**Comparison with Other Modules:**
- **Regular Beginning Balances**: Creates balanced entries with both debit and credit as specified in the beginning_balances table
- **Journal Entries**: Requires debits to equal credits before posting
- **Loan Repayments**: Creates balanced entries (e.g., DEBIT Cash, CREDIT Loan Principal/Interest accounts)

**Recommendation for Future Enhancement:**

To properly balance these entries, a corresponding CREDIT entry should be added. Options include:

1. **Credit to Equity/Retained Earnings Account** (e.g., account 3000002, similar to loan interest income processing)
   - This represents the opening equity position for existing loan receivables

2. **Credit to Opening Balance Equity Account**
   - A dedicated account for fiscal year opening balances

3. **Credit to a Liability Account**
   - If the loan receivables are funded from a specific liability source

**Code Location:** `application/models/loan_model.php` → `loan_beginning_balance_post_to_ledger($id)`

**Error Handling:**

The function includes comprehensive error checking:
- Verifies `general_ledger_entry` header creation
- Validates `insert_id()` and uses `LAST_INSERT_ID()` as fallback
- Checks `affected_rows()` after each insert
- Validates account existence before posting
- Verifies transaction status before marking as posted
- Logs detailed error messages for troubleshooting

## Permission Required
Users need the **Loan_beginning_balances** role permission under module 5 (Loan Management) to access this feature.

### Setting Up the Permission

**Option A: Use the Automated Script (Recommended)**
1. Navigate to: `http://your-domain/add_loan_beginning_balance_permission.php`
2. The script will:
   - Check if the permission exists
   - Add it to the `role` table if missing
   - Optionally enable it for all user groups
3. After running, assign the permission to specific groups through **User Management → Privileges**

**Option B: Manual SQL**
Run this SQL in your database:
```sql
-- Add permission to role table
INSERT INTO role (Module_id, Name) VALUES (5, 'Loan_beginning_balances');

-- Enable for a specific group (replace GROUP_ID with actual group ID)
INSERT INTO access_level (group_id, Module, link, allow) VALUES (GROUP_ID, 5, 'Loan_beginning_balances', 1);
```

**Option C: Through User Interface**
1. Go to **User Management → Privileges**
2. Select a user group
3. The permission should appear under the Loan Management module (Module 5)
4. Check the box to enable it

## Notes
- Each combination of fiscal year, member ID, and loan product can only have one beginning balance record
- At least one balance amount (principal, interest, or penalty) must be greater than zero
- Once posted to General Ledger, balances cannot be edited or deleted
- The total balance is automatically calculated as the sum of principal, interest, and penalty balances

## Known Issues and Limitations

### Unbalanced General Ledger Entries ⚠️

**Issue:** Loan beginning balance postings create DEBIT-only entries without corresponding CREDIT entries, resulting in unbalanced accounting entries.

**Impact:**
- General ledger entries do not balance (debits ≠ credits)
- Trial balance will show discrepancies
- Financial statements may be inaccurate
- Violates fundamental double-entry accounting principles

**Status:** Known limitation as of 2026-01-10. Enhancement required to add credit entries for proper double-entry accounting.

**Workaround:** Manual adjustment entries may be required to balance the general ledger after posting loan beginning balances.

**Related Documentation:**
- See "General Ledger Posting Details" section above for detailed explanation
- Compare with `HOW_TO_POST_JOURNAL_TO_GENERAL_LEDGER.md` for standard posting practices
