# CASH RECEIPT MODULE - INSTALLATION & USER GUIDE

## Overview
The Cash Receipt Module is a comprehensive accounting solution integrated into your TAPSTEMCO system. It allows you to record, track, and manage all cash receipts with full integration to the general ledger through automatic journal entries.

---

## TABLE OF CONTENTS
1. [Features](#features)
2. [Installation Steps](#installation-steps)
3. [Database Setup](#database-setup)
4. [Permissions Setup](#permissions-setup)
5. [Module Components](#module-components)
6. [How to Use](#how-to-use)
7. [Troubleshooting](#troubleshooting)

---

## FEATURES

### Core Functionality
- ✅ Create and manage cash receipts
- ✅ Multiple payment methods (Cash, Cheque, Bank Transfer, Mobile Money)
- ✅ Multi-line item receipts with different revenue accounts
- ✅ Automatic journal entry creation
- ✅ Receipt numbering system with auto-increment
- ✅ Print-ready receipt format
- ✅ Export to Excel functionality
- ✅ Full audit trail (created by, created at, updated at)
- ✅ Integration with Chart of Accounts
- ✅ Responsive design for all devices

### Key Features
1. **Receipt Management**
   - Create, edit, view, and delete cash receipts
   - Automatic receipt number generation
   - Support for multiple line items per receipt
   - Date-based receipt tracking

2. **Payment Methods**
   - Cash
   - Cheque (with cheque number and bank name)
   - Bank Transfer
   - Mobile Money

3. **Accounting Integration**
   - Automatic journal entry creation
   - Double-entry bookkeeping compliance
   - Integration with Chart of Accounts
   - Real-time ledger updates

4. **Reporting & Export**
   - Print receipts with company letterhead
   - Export receipts to Excel
   - Amount in words conversion
   - Professional receipt format

---

## INSTALLATION STEPS

### Step 1: Database Installation

1. **Run the SQL Script**
   - Navigate to: `sql/cash_receipt_module.sql`
   - Execute this script in your database using phpMyAdmin or MySQL command line:
   
   ```bash
   mysql -u your_username -p your_database_name < sql/cash_receipt_module.sql
   ```
   
   OR
   
   - Open phpMyAdmin
   - Select your database
   - Go to "Import" tab
   - Choose the file: `sql/cash_receipt_module.sql`
   - Click "Go"

2. **Verify Tables Created**
   The following tables should now exist:
   - `cash_receipts` - Main receipt header table
   - `cash_receipt_items` - Line items for receipts
   - `journal_entry` - Journal entry headers (if not already exists)
   - `journal_entry_items` - Journal entry line items (if not already exists)

### Step 2: Verify Files

Ensure all the following files are in place:

**Controller:**
- `application/controllers/cash_receipt.php`

**Model:**
- `application/models/cash_receipt_model.php`

**Views:**
- `application/views/cash_receipt/cash_receipt_list.php`
- `application/views/cash_receipt/cash_receipt_form.php`
- `application/views/cash_receipt/cash_receipt_edit.php`
- `application/views/cash_receipt/cash_receipt_view.php`
- `application/views/cash_receipt/print/cash_receipt_print.php`

**Helper Functions:**
- `application/helpers/common_helper.php` (updated with convert_number_to_words function)

**Language Files:**
- `application/language/english/systemlang_lang.php` (updated with cash receipt translations)

**Menu:**
- `application/views/menu.php` (updated with cash receipt menu item)

### Step 3: Clear Cache

Clear your application cache to ensure changes take effect:
- Delete contents of: `application/cache/` (keep the index.html file)
- Clear browser cache

---

## DATABASE SETUP

### Tables Structure

#### 1. cash_receipts
Stores main receipt information:
```sql
- id (Primary Key)
- receipt_no (Unique receipt number)
- receipt_date (Date of receipt)
- received_from (Customer/Payer name)
- payment_method (Cash, Cheque, Bank Transfer, Mobile Money)
- cheque_no (Optional - for cheque payments)
- bank_name (Optional - for cheque payments)
- description (Receipt description)
- total_amount (Total receipt amount)
- createdby (User ID who created)
- PIN (Organization identifier)
- created_at (Timestamp)
- updated_at (Timestamp)
```

#### 2. cash_receipt_items
Stores line items for each receipt:
```sql
- id (Primary Key)
- receipt_id (Foreign Key to cash_receipts)
- account (Account code from chart of accounts)
- description (Line item description)
- amount (Line item amount)
- PIN (Organization identifier)
```

### Important Notes
- The module automatically creates journal entries when receipts are created or updated
- All transactions are tied to the user's PIN (organization)
- Receipt numbers must be unique per organization

---

## PERMISSIONS SETUP

### Adding Permissions to Your System

The Cash Receipt module requires the following permissions to be set in your roles/permissions table:

1. **View_cash_receipt** - View cash receipts list and details
2. **Create_cash_receipt** - Create new cash receipts
3. **Edit_cash_receipt** - Edit existing cash receipts
4. **Delete_cash_receipt** - Delete cash receipts

### How to Add Permissions (Manual Method)

If your system uses the `module_roles` or `access_level` table for permissions:

```sql
-- Example: Add permissions for Finance module (Module ID = 6)
INSERT INTO access_level (group_id, Module, link, allow) VALUES
(1, 6, 'View_cash_receipt', 1),
(1, 6, 'Create_cash_receipt', 1),
(1, 6, 'Edit_cash_receipt', 1),
(1, 6, 'Delete_cash_receipt', 1);
```

**Note:** Replace `group_id = 1` with the appropriate group ID for your admin or user groups.

### Verify Permissions
1. Log in as an administrator
2. Go to Settings > User Groups/Permissions
3. Find the Finance module (Module 6)
4. Check that the following roles are available:
   - View_cash_receipt
   - Create_cash_receipt
   - Edit_cash_receipt
   - Delete_cash_receipt
5. Assign these permissions to appropriate user groups

---

## MODULE COMPONENTS

### Controller (`cash_receipt.php`)
Located: `application/controllers/cash_receipt.php`

**Main Functions:**
- `cash_receipt_list()` - Display all receipts
- `cash_receipt_create()` - Create new receipt
- `cash_receipt_edit($id)` - Edit existing receipt
- `cash_receipt_view($id)` - View receipt details
- `cash_receipt_print($id)` - Print receipt
- `cash_receipt_delete($id)` - Delete receipt
- `cash_receipt_export()` - Export to Excel

### Model (`cash_receipt_model.php`)
Located: `application/models/cash_receipt_model.php`

**Main Functions:**
- `get_cash_receipts()` - Retrieve receipts
- `get_cash_receipt($id)` - Get single receipt
- `get_receipt_items($id)` - Get receipt line items
- `create_cash_receipt()` - Create new receipt
- `update_cash_receipt()` - Update existing receipt
- `delete_cash_receipt()` - Delete receipt
- `create_journal_entry()` - Auto-create journal entry
- `get_next_receipt_no()` - Generate next receipt number

### Views

1. **cash_receipt_list.php** - Receipt listing with DataTables
2. **cash_receipt_form.php** - Create new receipt
3. **cash_receipt_edit.php** - Edit receipt
4. **cash_receipt_view.php** - View receipt details
5. **cash_receipt_print.php** - Printable receipt format

---

## HOW TO USE

### Accessing the Cash Receipt Module

1. **Login to System**
   - Navigate to your system URL
   - Log in with credentials that have Finance module access

2. **Open Cash Receipt**
   - Click on "Finance" menu
   - Click on "Cash Receipt List"

### Creating a New Cash Receipt

1. **Navigate to Create Form**
   - From Cash Receipt List, click "Create Cash Receipt" button
   - Or navigate to: Finance > Cash Receipt List > Create Cash Receipt

2. **Fill Receipt Information**
   - **Receipt No:** Auto-generated (can be modified if needed)
   - **Receipt Date:** Select the date of receipt
   - **Received From:** Enter customer/payer name
   - **Payment Method:** Select payment method:
     - Cash
     - Cheque (will show cheque number and bank name fields)
     - Bank Transfer
     - Mobile Money
   - **Description:** Enter main receipt description

3. **Add Line Items**
   - **Account:** Select revenue account from chart of accounts
   - **Line Description:** Enter description for this line item
   - **Amount:** Enter amount for this line item
   - Click "Add Row" to add more line items
   - Click the trash icon to remove a line item

4. **Review and Save**
   - Check that total amount is correct
   - Click "Save" button
   - Receipt will be created and journal entry will be posted automatically

### Viewing a Receipt

1. From Cash Receipt List, click the "eye" icon next to the receipt
2. View all receipt details including:
   - Receipt information
   - Payment details
   - Line items
   - Creator information
   - Created/Updated timestamps

### Editing a Receipt

1. From Cash Receipt List or View page, click "Edit" button
2. Modify receipt details as needed
3. Add/remove line items
4. Click "Update" to save changes
5. Old journal entries will be deleted and new ones created

### Printing a Receipt

1. From Cash Receipt List or View page, click "Print" button
2. Receipt will open in new window with print-ready format
3. Click "Print Receipt" button or use browser print (Ctrl+P)
4. Receipt includes:
   - Company letterhead
   - Receipt details
   - Line items
   - Amount in words
   - Signature sections

### Exporting to Excel

1. From Cash Receipt List, click "Export to Excel" button
2. Excel file will download automatically
3. Contains all receipt information in spreadsheet format

### Deleting a Receipt

1. From Cash Receipt List, click the "trash" icon
2. Confirm deletion
3. Receipt and all associated journal entries will be deleted

---

## ACCOUNTING INTEGRATION

### How Journal Entries are Created

When a cash receipt is created, the system automatically creates a journal entry with:

**Debit Entry:**
- Account: Cash/Bank account (based on payment method)
- Amount: Total receipt amount
- Description: Receipt from [Received From]

**Credit Entries:**
- Accounts: As specified in line items
- Amounts: As specified in line items
- Descriptions: As specified in line items

### Example:
**Cash Receipt Details:**
- Receipt No: CR-00001
- Received From: John Doe
- Payment Method: Cash
- Total: 5,000

**Line Items:**
- Sales Revenue: 3,000
- Service Income: 2,000

**Journal Entry Created:**
```
Date: [Receipt Date]
Description: Cash Receipt: CR-00001 - Payment received from John Doe

Debit:  Cash Account         5,000
Credit: Sales Revenue                 3,000
Credit: Service Income                2,000
                           -------  -------
                             5,000    5,000
```

---

## TROUBLESHOOTING

### Common Issues and Solutions

#### 1. Menu Item Not Showing
**Problem:** Cash Receipt menu item doesn't appear in Finance menu

**Solution:**
- Check that user has "View_cash_receipt" permission
- Verify menu.php is updated correctly
- Clear browser cache
- Check that Module 6 (Finance) is accessible to the user

#### 2. Database Tables Not Created
**Problem:** Error: "Table 'cash_receipts' doesn't exist"

**Solution:**
- Run the SQL script: `sql/cash_receipt_module.sql`
- Verify database connection settings
- Check database user has CREATE TABLE privileges

#### 3. Receipt Number Already Exists
**Problem:** "This receipt number already exists" error

**Solution:**
- System auto-generates unique numbers
- If manually changing receipt numbers, ensure uniqueness
- Check existing receipts in your PIN/organization

#### 4. Journal Entry Not Created
**Problem:** Receipt saves but journal entry not created

**Solution:**
- Verify `journal_entry` and `journal_entry_items` tables exist
- Check that cash/bank accounts exist in chart of accounts
- Review error logs in `application/logs/`

#### 5. Permissions Not Working
**Problem:** User can see menu but gets "Access Denied"

**Solution:**
- Check permissions in access_level table
- Verify user's group_id has correct permissions
- Ensure Module ID is 6 for Finance

#### 6. Print Function Not Working
**Problem:** Print page is blank or shows errors

**Solution:**
- Verify `convert_number_to_words()` function exists in common_helper.php
- Check that company_info() function returns data
- Review browser console for JavaScript errors

#### 7. Excel Export Not Working
**Problem:** Export returns error or empty file

**Solution:**
- Verify PHPExcel library is installed
- Check that receipts exist in database
- Ensure proper write permissions for temp files

---

## TECHNICAL SPECIFICATIONS

### System Requirements
- PHP 5.6 or higher
- MySQL 5.5 or higher
- CodeIgniter 3.x
- PHPExcel library (for export functionality)
- jQuery (for AJAX and interactions)
- Bootstrap (for UI)
- DataTables (for listing)

### Dependencies
- Ion Auth (authentication)
- Finance Module (Module ID: 6)
- Chart of Accounts
- Company Settings

### File Permissions
Ensure the following directories are writable:
- `application/cache/`
- `application/logs/`

---

## SUPPORT & MAINTENANCE

### Regular Maintenance
1. **Database Backups**
   - Regular backups of `cash_receipts` and `cash_receipt_items` tables
   - Include `journal_entry` tables in backups

2. **Log Monitoring**
   - Check `application/logs/` for errors
   - Monitor database activity logs

3. **Performance**
   - Index maintenance on large tables
   - Archive old receipts if needed

### Customization
The module can be customized to:
- Add more payment methods
- Change receipt number format
- Add custom fields
- Modify print layout
- Add additional validations

### Updates
When updating the module:
1. Backup database and files
2. Test in development environment
3. Update production during low-traffic period
4. Clear cache after updates

---

## VERSION HISTORY

**Version 1.0.0** - December 22, 2025
- Initial release
- Core cash receipt functionality
- Journal entry integration
- Print and export features
- Multi-payment method support

---

## CONTACT & CREDITS

**Developed for:** TAPSTEMCO Accounting System
**Module:** Cash Receipt Module
**Date:** December 22, 2025
**Framework:** CodeIgniter 3.x

---

## LICENSE

This module is proprietary software developed for TAPSTEMCO. Unauthorized copying, distribution, or modification is prohibited.

---

**End of Documentation**
