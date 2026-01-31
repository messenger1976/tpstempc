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

### Method 3: Using MySQL Command Line
```bash
mysql -u root -p tapstemco < /path/to/sql/cash_disbursement_module.sql
```

## Module Files Created

### Controllers
- **File:** `application/controllers/cash_disbursement.php`
- **Purpose:** Handles all cash disbursement operations
- **Methods:**
  - `index()` - Display disbursement list
  - `cash_disbursement_list()` - Paginated list view
  - `cash_disbursement_create()` - Create new disbursement form
  - `cash_disbursement_edit()` - Edit existing disbursement
  - `cash_disbursement_view()` - View disbursement details
  - `cash_disbursement_print()` - Printable disbursement voucher
  - `cash_disbursement_delete()` - Delete disbursement
  - `export_to_excel()` - Export to Excel format

### Models
- **File:** `application/models/cash_disbursement_model.php`
- **Purpose:** Database operations and journal entry creation
- **Key Methods:**
  - `create_cash_disbursement()` - Create disbursement with journal entry
  - `get_disbursements()` - Retrieve disbursements with pagination
  - `update_cash_disbursement()` - Update existing disbursement
  - `delete_cash_disbursement()` - Delete disbursement
  - `create_journal_entry()` - Auto-create journal entry

### Views
- **`application/views/cash_disbursement/cash_disbursement_list.php`** - List all disbursements with DataTables
- **`application/views/cash_disbursement/cash_disbursement_form.php`** - Create new disbursement form
- **`application/views/cash_disbursement/cash_disbursement_edit.php`** - Edit disbursement form
- **`application/views/cash_disbursement/cash_disbursement_view.php`** - View disbursement details
- **`application/views/cash_disbursement/print/cash_disbursement_print.php`** - Printable voucher template

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
- payment_method (Cash/Cheque/Bank Transfer/Mobile Money)
- cheque_no (For cheque payments)
- bank_name (For cheque/bank transfer payments)
- description (Payment description/notes)
- total_amount (Total amount disbursed)
- createdby (User ID)
- PIN (Multi-tenancy identifier)
- created_at (Timestamp)
- updated_at (Update timestamp)
```

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
1. Go to **Admin Panel → Roles & Permissions**
2. Select the desired user role
3. Find "Finance" module (ID: 6)
4. Check the cash disbursement permissions
5. Save changes

## How to Use

### Creating a Cash Disbursement

1. Navigate to **Finance → Cash Disbursement List**
2. Click **"Create New Disbursement"** button
3. Fill in the form:
   - **Disbursement Date:** Select the date of payment
   - **Paid To:** Enter the payee name
   - **Payment Method:** Choose from:
     - Cash
     - Cheque (requires cheque number and bank name)
     - Bank Transfer
     - Mobile Money
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
4. Modify the necessary fields
5. Update line items as needed
6. Click **"Update Disbursement"**

### Viewing Disbursement Details

1. Go to **Finance → Cash Disbursement List**
2. Click the **View** button (eye icon) or disbursement number
3. Review all details including:
   - Disbursement information
   - Payment method details
   - Line items breakdown
   - Creator and timestamp information

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
2. Click the **Export to Excel** button
3. Excel file downloads with:
   - All disbursement records
   - Payment dates and methods
   - Total amounts
   - Payee information

### Deleting a Disbursement

1. Go to **Finance → Cash Disbursement List**
2. Find the disbursement to delete
3. Click the **Delete** button (trash icon)
4. Confirm deletion in the popup dialog
5. Associated journal entry will also be deleted

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
- **Cash Account:** Determined by payment method
  - Cash → Bank Account (1200)
  - Cheque → Bank Account (1200)
  - Bank Transfer → Bank Account (1200)
  - Mobile Money → Mobile Money Account (1205)
- **Expense Account:** User-selected GL accounts

### Viewing Related Journal Entries

1. View the disbursement details
2. The related journal entry number is automatically recorded
3. Access journal entries from **Finance → Journal Entry**

## Troubleshooting

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
1. **List View:** View all disbursements with filtering
2. **Excel Export:** Export for further analysis
3. **Print Vouchers:** Print individual or multiple vouchers

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
│   └── cash_disbursement_module.sql ← Database schema
└── install_cash_disbursement.php ← Installation script
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

- **Module Version:** 1.0
- **Framework:** CodeIgniter 3.x
- **Database:** MySQL/MariaDB
- **Created:** 2024
- **Last Updated:** 2024

---

## Summary of Key Features

✅ Complete cash disbursement management system
✅ Automatic journal entry generation
✅ Multi-payment method support (Cash, Cheque, Bank Transfer, Mobile Money)
✅ Professional disbursement vouchers (printable)
✅ Excel export functionality
✅ Role-based permission system
✅ Multi-line item support
✅ Automatic numbering system
✅ Amount-in-words conversion
✅ Full CRUD operations
✅ Transaction-based integrity
✅ Double-entry bookkeeping compliance

Enjoy your new Cash Disbursement Module!
