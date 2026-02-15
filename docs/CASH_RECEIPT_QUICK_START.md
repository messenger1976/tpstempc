# CASH RECEIPT MODULE - QUICK START GUIDE

## 📋 What Has Been Created

A complete Cash Receipt Module has been created and integrated into your TAPSTEMCO accounting system under the Finance Menu.

---

## 📁 Files Created

### Controllers (1 file)
- ✅ `application/controllers/cash_receipt.php`

### Models (1 file)
- ✅ `application/models/cash_receipt_model.php`

### Views (8 files)
- ✅ `application/views/cash_receipt/cash_receipt_list.php` (includes popup modal)
- ✅ `application/views/cash_receipt/cash_receipt_form.php` (Debit/Credit line items)
- ✅ `application/views/cash_receipt/cash_receipt_edit.php` (Debit/Credit line items)
- ✅ `application/views/cash_receipt/cash_receipt_view.php`
- ✅ `application/views/cash_receipt/cash_receipt_view_popup.php` (popup iframe)
- ✅ `application/views/cash_receipt/print/cash_receipt_print.php`
- ✅ `application/views/cash_receipt/cash_receipt_report_summary.php` (Trial Balance report)
- ✅ `application/views/cash_receipt/cash_receipt_report_details.php` (Details grouped by transaction)

### Database (2 files)
- ✅ `sql/cash_receipt_module.sql`
- ✅ `sql/alter_cash_receipt_items_debit_credit.sql` (migration for existing installs)

### Documentation (2 files)
- ✅ `CASH_RECEIPT_MODULE_README.md` (Complete documentation)
- ✅ `CASH_RECEIPT_QUICK_START.md` (This file)

### Installation Helper (1 file)
- ✅ `install_cash_receipt.php` (Browser-based installer)

---

## 📝 Files Modified

### Modified Files (3 files)
- ✅ `application/views/menu.php` - Added Cash Receipt menu item
- ✅ `application/language/english/systemlang_lang.php` - Added translations
- ✅ `application/helpers/common_helper.php` - Added number to words function

---

## 🚀 QUICK INSTALLATION (3 Steps)

### Step 1: Run Database Installation
**Option A - Using Browser Installer (Recommended)**
1. Open browser and navigate to: `http://your-domain.com/install_cash_receipt.php`
2. Wait for installation to complete
3. Delete `install_cash_receipt.php` file for security

**Option B - Using phpMyAdmin**
1. Open phpMyAdmin
2. Select your database
3. Import file: `sql/cash_receipt_module.sql`

**Option C - Using MySQL Command Line**
```bash
mysql -u your_username -p your_database_name < sql/cash_receipt_module.sql
```

**Existing installations (upgrade to v1.3.0 Debit/Credit):**
```bash
mysql -u your_username -p your_database_name < sql/alter_cash_receipt_items_debit_credit.sql
```

### Step 2: Set Up Permissions
Add these permissions to your admin user group:
- `View_cash_receipt`
- `Create_cash_receipt`
- `Edit_cash_receipt`
- `Delete_cash_receipt`

**SQL Example:**
```sql
INSERT INTO access_level (group_id, Module, link, allow) VALUES
(1, 6, 'View_cash_receipt', 1),
(1, 6, 'Create_cash_receipt', 1),
(1, 6, 'Edit_cash_receipt', 1),
(1, 6, 'Delete_cash_receipt', 1);
```
*Replace `group_id = 1` with your admin group ID*

### Step 3: Clear Cache
- Delete contents of `application/cache/` folder (keep index.html)
- Clear browser cache (Ctrl + F5)

---

## ✨ HOW TO USE

### Access the Module
1. Login to your system
2. Click **Finance** in the main menu
3. Click **Cash Receipt List**

### Create Your First Receipt
1. Click **Create Cash Receipt** button
2. Fill in receipt details:
   - Receipt No (auto-generated)
   - Date
   - Received From
   - Payment Method
   - Description
3. Add line items (Account | Description | **Debit** | **Credit** – same as journal entry)
   - Each row has Debit and Credit columns; enter value in one or the other
   - Total debits must equal total credits (form validates)
   - Click trash icon to delete rows (at least one row required)
4. Click **Save**

### Filter Receipts by Date Range
1. On the Cash Receipt List page, use the date filter section
2. Select **Date From** (optional)
3. Select **Date To** (optional)
4. Click **Filter** button
5. Click **Clear** to reset and show all records
6. **Note:** If no dates are selected, all records are shown by default

### View/Print Receipts
- Click the 👁️ icon to view details (opens popup modal on list page)
- Click the 🖨️ icon to print
- Click the ✏️ icon to edit
- Click the 🗑️ icon to delete
- Click **Export to Excel** to export (respects current date filters)

### Report Summary & Report Details
- Click **Report Summary** for Trial Balance format (accounts summary with Debit/Credit columns)
- Click **Report Details** for transaction-grouped Trial Balance (each receipt as separate block)
- Both open in new tab; include Print and Export to Excel buttons

---

## 🎯 KEY FEATURES

✅ **Receipt Management**
- Create, edit, view, delete receipts
- Auto receipt numbering (CR-00001, CR-00002, etc.)
- Multiple line items per receipt

✅ **Payment Methods**
- Cash
- Cheque (with cheque# and bank name)
- Bank Transfer
- Mobile Money

✅ **Accounting Integration**
- Automatic journal entries
- Double-entry bookkeeping
- Integration with Chart of Accounts

✅ **Reporting**
- Print receipts with letterhead
- Export to Excel (respects date filters)
- **Report Summary** – Trial Balance format (accounts summary)
- **Report Details** – Grouped by transaction, Trial Balance layout
- Export to Excel for both reports
- Amount in words
- Date range filtering on list view

---

## 🏗️ Module Structure

```
Cash Receipt Module
├── Frontend (Views)
│   ├── List receipts (with DataTables + Date Range Filter)
│   ├── Create receipt form
│   ├── Edit receipt form
│   ├── View receipt details
│   └── Print receipt
│
├── Backend (Controller & Model)
│   ├── CRUD operations
│   ├── Validation
│   ├── Journal entry creation
│   └── Receipt numbering
│
└── Database
    ├── cash_receipts (header table)
    ├── cash_receipt_items (line items)
    └── journal_entry (automatic posting)
```

---

## 📊 Database Tables Created

### 1. cash_receipts
Main receipt information
- Receipt number, date, customer
- Payment method details
- Total amount
- Audit fields

### 2. cash_receipt_items
Line items for each receipt (journal-entry style)
- Account code
- Description
- **Debit** | **Credit** columns
- Amount (for backward compatibility)

**Migration:** Run `sql/alter_cash_receipt_items_debit_credit.sql` for existing installations.

### 3. journal_entry (if not exists)
Journal entry headers

### 4. journal_entry_items (if not exists)
Journal entry line items

---

## 🔐 Required Permissions

Make sure these permissions are assigned to appropriate user groups:

| Permission | Description |
|------------|-------------|
| `View_cash_receipt` | View receipts list and details |
| `Create_cash_receipt` | Create new receipts |
| `Edit_cash_receipt` | Edit existing receipts |
| `Delete_cash_receipt` | Delete receipts |

---

## 🧪 Test the Module

### Quick Test Steps:
1. **Create a Test Receipt**
   - Go to Finance > Cash Receipt List
   - Click "Create Cash Receipt"
   - Fill in test data
   - Save

2. **Verify Journal Entry**
   - Check that journal entry was created
   - Verify debit and credit balances match

3. **Print Test**
   - View the receipt
   - Click Print
   - Verify format is correct

4. **Export Test**
   - Go to receipt list
   - Click "Export to Excel"
   - Verify data exports correctly

---

## 🛠️ Troubleshooting

### Issue: Menu not showing
**Fix:** Check user permissions and clear cache

### Issue: Database error
**Fix:** Run the SQL installation script again

### Issue: Print not working
**Fix:** Check that company_info() returns data

### Issue: Journal entry not created
**Fix:** Ensure Cash/Bank accounts exist in Chart of Accounts

---

## 📚 Full Documentation

For complete documentation, see:
- **CASH_RECEIPT_MODULE_README.md** - Full documentation with detailed instructions

---

## 🎉 You're Ready!

Your Cash Receipt Module is now installed and ready to use!

**Next Steps:**
1. ✅ Install database (Step 1 above)
2. ✅ Set permissions (Step 2 above)
3. ✅ Clear cache (Step 3 above)
4. ✅ Create your first receipt!

---

## 📞 Need Help?

If you encounter any issues:
1. Check the full documentation (CASH_RECEIPT_MODULE_README.md)
2. Review the troubleshooting section
3. Check application logs in `application/logs/`

---

**Module Version:** 1.3.0  
**Created:** December 22, 2025  
**Updated:** February 2026 (v1.3.0: Debit/Credit line items, deletable rows; v1.2.0: Report Summary/Details)  
**Framework:** CodeIgniter 3.x  
**Compatible with:** TAPSTEMCO Accounting System

---

**Happy Accounting! 📊💰**
