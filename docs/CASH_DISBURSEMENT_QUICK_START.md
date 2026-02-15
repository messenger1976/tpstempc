# Cash Disbursement Module - Quick Start Guide

## Overview
The Cash Disbursement Module is a complete accounting solution for managing outgoing cash payments in your TAPSTEMCO accounting system. It integrates seamlessly with your Chart of Accounts and automatically creates journal entries for double-entry bookkeeping.

## Installation

### Method 1: Using Installation Script (Recommended)
1. Navigate to: `http://your-domain.com/install_cash_disbursement.php`
2. Enter your database credentials:
   - Database Host: (usually `localhost`)
   - Database Username: (your MySQL user)
   - Database Password: (if applicable)
   - Database Name: (usually `tapstemco`)
3. Click "Install Module"
4. Wait for confirmation message

### Method 2: Manual SQL Import
1. Open your database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. Navigate to your TAPSTEMCO database
3. Import the SQL file: `sql/cash_disbursement_module.sql`
4. Execute all queries
5. **Add permissions** (required for the menu to appear): run `sql/add_cash_disbursement_permissions.sql` (see [User Permissions](#user-permissions) below).

### Method 3: Using MySQL Command Line
```bash
mysql -u root -p tapstemco < sql/cash_disbursement_module.sql
mysql -u root -p tapstemco < sql/add_cash_disbursement_permissions.sql
```

## Module Files Created

### Controllers
- **File:** `application/controllers/cash_disbursement.php`
- **Purpose:** Handles all cash disbursement operations
- **Methods:**
  - `index()` - Display disbursement list
  - `cash_disbursement_list()` - List view with date range filter
  - `cash_disbursement_create()` - Create new disbursement form
  - `cash_disbursement_edit()` - Edit existing disbursement
  - `cash_disbursement_view()` - View disbursement details
  - `cash_disbursement_print()` - Printable disbursement voucher
  - `cash_disbursement_delete()` - Delete disbursement
  - `cash_disbursement_export()` - Export to Excel (respects date range filter)
  - `cash_disbursement_report_summary()` - Trial Balance report (accounts summary)
  - `cash_disbursement_report_summary_export()` - Export Report Summary to Excel
  - `cash_disbursement_report_details()` - Report Details (grouped by transaction, Trial Balance layout)
  - `cash_disbursement_report_details_export()` - Export Report Details to Excel

### Models
- **File:** `application/models/cash_disbursement_model.php`
- **Purpose:** Database operations and journal entry creation
- **Key Methods:**
  - `create_cash_disbursement()` - Create disbursement with journal entry
  - `get_cash_disbursements($id, $disburse_no, $date_from, $date_to)` - Retrieve disbursements (with optional date range)
  - `get_account_summary($date_from, $date_to)` - Get account totals for Report Summary (Trial Balance)
  - `get_account_details($date_from, $date_to)` - Get detailed lines for Report Details (grouped by disbursement)
  - `update_cash_disbursement()` - Update existing disbursement
  - `delete_cash_disbursement()` - Delete disbursement
  - `create_journal_entry()` - Auto-create journal entry

### Views
- **`application/views/cash_disbursement/cash_disbursement_list.php`** - List with DataTables, date range filter, Report Summary & Report Details buttons
- **`application/views/cash_disbursement/cash_disbursement_form.php`** - Create new disbursement form
- **`application/views/cash_disbursement/cash_disbursement_edit.php`** - Edit disbursement form
- **`application/views/cash_disbursement/cash_disbursement_view.php`** - View disbursement details
- **`application/views/cash_disbursement/print/cash_disbursement_print.php`** - Printable voucher template
- **`application/views/cash_disbursement/cash_disbursement_report_summary.php`** - Trial Balance report (accounts summary)
- **`application/views/cash_disbursement/cash_disbursement_report_details.php`** - Report Details (grouped by transaction, Trial Balance layout)

### Database
- **File:** `sql/cash_disbursement_module.sql`
- **Tables Created:**
  - `cash_disbursements` - Main disbursement records
  - `cash_disbursement_items` - Line items for each disbursement
  - `journal_entry` - Journal entries (auto-created if not exists)
  - `journal_entry_items` - Journal entry line items

## Database Schema

### cash_disbursements Table
```sql
- id (Primary Key)
- disburse_no (Unique, e.g., CD-00001)
- disburse_date (Date of disbursement)
- paid_to (Payee name)
- payment_method (VARCHAR(100) – from paymentmenthod table, e.g. Cash, BANK DEPOSIT, Cheque)
- cheque_no (For cheque payments)
- bank_name (For cheque/bank transfer payments)
- description (Payment description/notes)
- total_amount (Total amount disbursed)
- createdby (User ID)
- PIN (Multi-tenancy identifier)
- created_at (Timestamp)
- updated_at (Update timestamp)
```

**Payment method column:** The schema uses `VARCHAR(100)` so any method from the **paymentmenthod** table can be stored. If your database still has the old `ENUM`, run once: `sql/alter_cash_disbursement_payment_method_varchar.sql`.

### cash_disbursement_items Table
```sql
- id (Primary Key)
- disbursement_id (Foreign Key to cash_disbursements)
- account (GL Account code)
- description (Line item description)
- amount (Line item amount)
- PIN (Multi-tenancy identifier)
- created_at (Timestamp)
```

## User Permissions

After installation, assign these permissions to your user roles:

| Permission | Description |
|-----------|-------------|
| `View_cash_disbursement` | View disbursement list and details |
| `Create_cash_disbursement` | Create new disbursement records |
| `Edit_cash_disbursement` | Edit existing disbursements |
| `Delete_cash_disbursement` | Delete disbursement records |

All permissions belong to **Module ID 6** (Finance).

### How to Assign Permissions

**Option A – Run the permissions SQL (quick)**  
Run `sql/add_cash_disbursement_permissions.sql` in phpMyAdmin or MySQL. This adds View/Create/Edit/Delete_cash_disbursement for **group_id = 1** (admin). Edit the script if your role uses a different group_id.

**Option B – Via application UI**  
1. Go to **Admin Panel → Roles & Permissions** (or your system’s access-level screen).
2. Select the desired user role (group).
3. Find **Finance** module (ID: 6).
4. Enable: View_cash_disbursement, Create_cash_disbursement, Edit_cash_disbursement, Delete_cash_disbursement.
5. Save changes.

Without these entries in the `access_level` table, the **Cash Disbursement** menu item will not appear.

## How to Use

### Creating a Cash Disbursement

1. Navigate to **Finance → Cash Disbursement List**
2. Click **"Create New Disbursement"** button
3. Fill in the form:
   - **Disbursement Date:** Select the date of payment
   - **Paid To:** Enter the payee name
   - **Payment Method:** Choose from the **paymentmenthod** table (e.g. Cash, BANK DEPOSIT, Cheque, Bank Transfer, Mobile Money). Options are loaded from Settings → Payment Method Config; only active methods with your PIN appear.
   - **Description:** Add any notes about the disbursement
4. Add Line Items:
   - Click **"Add Item"** button
   - Select GL Account (e.g., Office Supplies, Rent Expense)
   - Enter line description
   - Enter amount
   - Click **"Add"** to add to the list
   - Click **"Remove"** to remove any item
5. Review the **Total Amount** (auto-calculated)
6. Click **"Save Disbursement"**

**Automatic Actions:**
- Disbursement number generated automatically (CD-00001, CD-00002, etc.)
- Journal entry created automatically:
  - **Debit:** Selected expense/asset accounts
  - **Credit:** Cash/Bank account
- Maintains double-entry bookkeeping

### Editing a Disbursement

1. Go to **Finance → Cash Disbursement List**
2. Find the disbursement in the table
3. Click the **Edit** button (pencil icon)
4. Change **Payment Method**, line items (accounts/amounts), or any other fields as needed
5. Click **"Update Disbursement"**

**Behaviour:** The disbursement header and line items are updated; the old journal entry for that disbursement is removed and a new one is created with the current payment method and line items. The **Accounting entries** shown on the view page always reflect the **current** disbursement (payment method and line items), so they update as soon as you save.

### Viewing Disbursement Details

1. Go to **Finance → Cash Disbursement List**
2. Click the **View** button (eye icon) or disbursement number
3. Review all details including:
   - Disbursement information
   - Payment method details
   - Line items breakdown
   - Creator and timestamp information

### Filtering Disbursements by Date Range

1. On the Cash Disbursement List page, use the date filter section (above the table)
2. Select **Date From** (optional) and **Date To** (optional)
3. Click **Filter** to apply
4. Click **Clear** to reset and show all records
5. Export and Report Summary/Details respect the active date filter when applied

### Printing a Disbursement

1. Go to **Finance → Cash Disbursement List** or view disbursement details
2. Click the **Print** button (print icon)
3. A professional voucher will open in a new window
4. Use browser print function (Ctrl+P or Cmd+P) to print
5. The voucher includes:
   - Company name and logo
   - Disbursement details
   - Line items breakdown
   - Amount in words
   - Signature sections for approval

### Exporting to Excel

1. Go to **Finance → Cash Disbursement List**
2. Optionally set **Date From** and **Date To** and click **Filter**
3. Click the **Export to Excel** button (respects date filter)
4. Excel file downloads with:
   - Disbursement records (filtered if date range applied)
   - Payment dates and methods
   - Total amounts
   - Payee information

### Report Summary (Trial Balance)

1. On Cash Disbursement List, optionally set date range and click **Filter**
2. Click **Report Summary** button (next to Clear)
3. Report opens in new tab in Trial Balance format:
   - **Debit:** Each account used in disbursement line items (expenses, etc.)
   - **Credit:** Cash and Bank (total disbursements)
   - Total row shows matching Debit and Credit
4. Use **Print** or **Export to Excel** on the report page

### Report Details (Grouped by Transaction)

1. On Cash Disbursement List, optionally set date range and click **Filter**
2. Click **Report Details** button (next to Clear)
3. Report opens in new tab showing each disbursement as a separate Trial Balance block:
   - Header: Disburse No, Date, Paid To, Payment Method
   - Trial Balance table: line item accounts (Debit), Cash and Bank (Credit), Total row
   - Grand total at end
4. Use **Print** or **Export to Excel** on the report page

### Deleting a Disbursement

1. Go to **Finance → Cash Disbursement List**
2. Find the disbursement to delete
3. Click the **Delete** button (trash icon)
4. Confirm deletion in the popup dialog
5. The disbursement, its line items, and any linked journal entry (and journal items) are deleted.

⚠️ **Warning:** Deletion is permanent. Use with caution in production environments.

## Journal Entry Integration

### Automatic Journal Entry Creation

When you save a cash disbursement, the system automatically creates a journal entry:

**Example Transaction:** Disbursement of 5,000 for Office Supplies

| Account | Type | Amount |
|---------|------|--------|
| Office Supplies Expense | Debit | 5,000.00 |
| Cash/Bank Account | Credit | 5,000.00 |

**Mapping Logic:**
- **Cash/Bank Account (credit):** From **paymentmenthod** table (`gl_account_code`) for the selected payment method; if not set, the system looks up an asset account by payment method name or uses a Cash/Bank fallback.
- **Expense/Asset accounts (debits):** User-selected GL accounts per line item.

### Accounting Entries on the View Page

The **Accounting entries** section on the disbursement view is always built from the **current** disbursement record (current payment method and current line items). When you edit and change the payment method or line items, the displayed entries update to match after save.

### Posting to the General Ledger (Option 2 – batch post)

Cash disbursement creates rows in **`journal_entry`** and **`journal_entry_items`** only. To have them appear in **`general_ledger`** (and in trial balance / GL reports):

1. Go to **Finance → Journal Entry Review**.
2. Find the entry with source **Cash Disbursement** (or Cash Receipt).
3. Click **"Post to GL"** for that row.
4. Confirm; the entry is then copied to **`general_ledger_entry`** and **`general_ledger`** with `fromtable = 'journal_entry'`.

Entries that are already posted show a **"Posted to GL"** label instead of the button. Only balanced entries can be posted.

## Troubleshooting

### Cash Disbursement menu item not visible
The Finance menu shows **Cash Disbursement List** only when your user has the **View_cash_disbursement** permission. The module SQL does not add this automatically.

**Fix:** Run `sql/add_cash_disbursement_permissions.sql` (adds permissions for group_id = 1), or assign **View_cash_disbursement** (Module 6) to your role in the application’s Roles & Permissions (access_level). Then refresh the page or log in again.

### "No direct script access allowed" Error
This error occurs when the installer script cannot run. Solutions:
1. Ensure the installer script is in the root directory
2. Check PHP execution is allowed in your server configuration
3. Use manual SQL import instead

### "Database connection failed" Error
1. Verify database credentials are correct
2. Ensure MySQL server is running
3. Check database user has proper privileges
4. Verify network connectivity to database server

### "Permission Denied" When Creating Disbursement
1. Ensure you have the `Create_cash_disbursement` permission
2. Contact your system administrator
3. Verify your user role has Finance module access

### Line Items Not Saving
1. Ensure at least one line item is added (minimum 1 required)
2. Verify all required fields in line items are filled
3. Check that GL accounts exist in Chart of Accounts
4. Ensure account codes are correct

### Disbursement Number Duplicate Error
1. Clear application cache (if cached)
2. Manually set the next disbursement number in database
3. Contact system administrator for database reset

### Payment Method Not Saving or Shows Blank After Edit
If the **payment_method** column is still an ENUM (only Cash, Cheque, Bank Transfer, Mobile Money), values like "BANK DEPOSIT" from the paymentmenthod table will not save. **Fix:** Run once in your database (e.g. phpMyAdmin → SQL):
```sql
ALTER TABLE `cash_disbursements`
  MODIFY COLUMN `payment_method` VARCHAR(100) NOT NULL DEFAULT 'Cash';
```
Or run the file: `sql/alter_cash_disbursement_payment_method_varchar.sql`

## Maintenance

### Database Cleanup
Periodically review and archive old disbursements:
```sql
-- Archive disbursements older than 1 year
BACKUP TABLE cash_disbursements;
-- Then delete as needed
```

### Reporting
Generate disbursement reports from the module:
1. **List View:** View all disbursements with date range filtering
2. **Report Summary:** Trial Balance format – totals by account (Debit: expenses; Credit: Cash and Bank)
3. **Report Details:** Grouped by transaction – each disbursement as Trial Balance block
4. **Excel Export:** Export list, Report Summary, or Report Details (all respect date filter)
5. **Print Vouchers:** Print individual or multiple vouchers

## File Locations Summary

```
tapstemco/
├── application/
│   ├── controllers/
│   │   └── cash_disbursement.php ← Main controller
│   ├── models/
│   │   └── cash_disbursement_model.php ← Database operations
│   ├── views/
│   │   └── cash_disbursement/ ← All view templates
│   │       ├── cash_disbursement_list.php
│   │       ├── cash_disbursement_form.php
│   │       ├── cash_disbursement_edit.php
│   │       ├── cash_disbursement_view.php
│   │       └── print/
│   │           └── cash_disbursement_print.php
│   └── language/english/
│       └── systemlang_lang.php ← Translations
├── sql/
│   ├── cash_disbursement_module.sql .............. Database schema
│   ├── add_cash_disbursement_permissions.sql ..... Permissions (run after schema)
│   └── alter_cash_disbursement_payment_method_varchar.sql ... Optional: allow any payment method name (run if column is ENUM)
└── install_cash_disbursement.php ................. Installation script
```

## Security Notes

- **PIN Field:** Data is isolated by user PIN for multi-tenant systems
- **Permissions:** Always verify user permissions before accessing
- **Database Access:** Use appropriate user privileges (read/write only)
- **Backups:** Regularly backup your database including cash disbursement data

## Support & Documentation

For more information:
- Check inline code comments
- Review CodeIgniter 3 documentation
- Consult your system administrator
- Review database schema in SQL file

## Version Information

- **Module Version:** 1.2
- **Framework:** CodeIgniter 3.x
- **Database:** MySQL/MariaDB
- **Created:** 2024
- **Last Updated:** February 2026 (date filter, Report Summary, Report Details, Trial Balance layout)

---

## Summary of Key Features

✅ Complete cash disbursement management system
✅ Automatic journal entry generation
✅ **Date range filter** on list view (Date From / Date To)
✅ **Report Summary** – Trial Balance format (accounts used in disbursements)
✅ **Report Details** – Grouped by transaction, Trial Balance layout per disbursement
✅ Export to Excel for list, Report Summary, and Report Details (all respect date filter)
✅ Payment methods from **paymentmenthod** table (Cash, BANK DEPOSIT, Cheque, etc.)
✅ Professional disbursement vouchers (printable)
✅ Role-based permission system
✅ Multi-line item support
✅ Automatic numbering system
✅ Amount-in-words conversion
✅ Full CRUD operations
✅ Transaction-based integrity
✅ Double-entry bookkeeping compliance

Enjoy your new Cash Disbursement Module!
