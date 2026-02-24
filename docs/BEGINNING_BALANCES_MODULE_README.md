# Beginning Balances Module

## Overview
The Beginning Balances Module allows you to create, edit, and delete beginning balance entries for General Ledger Accounts by Fiscal Year. This module helps you set up opening balances for accounts at the start of a fiscal year.

## Features
- **Add Beginning Balances**: Create beginning balance entries for any General Ledger account for a selected fiscal year
- **Edit Beginning Balances**: Modify beginning balance entries before they are posted to General Ledger
- **Delete Beginning Balances**: Remove beginning balance entries that haven't been posted yet
- **Post to General Ledger**: Post beginning balances to the General Ledger (one-time action, cannot be undone)
- **Fiscal Year Selection**: Filter and manage beginning balances by fiscal year
- **Status Tracking**: Monitor which balances have been posted and which are still pending

## Installation Steps

### 1. Create Database Table
Run the SQL script to create the `beginning_balances` table:
- Navigate to: `http://your-domain/create_beginning_balances_table.php`
- Or manually run the SQL from `create_beginning_balances_table.php` in your database

### 2. Set Up Permissions
The module uses the permission: `Manage_beginning_balance` with module ID `6` (Finance module).

**Option A: Use the Automated Script (Recommended)**
1. Navigate to: `http://your-domain/add_beginning_balance_permission.php`
2. The script will:
   - Check if the permission exists
   - Add it to the `role` table if missing
   - Optionally enable it for all user groups
3. After running, assign the permission to specific groups through **User Management → Privileges**

**Option B: Manual SQL**
Run this SQL in your database:
```sql
-- Add permission to role table
INSERT INTO role (Module_id, Name) VALUES (6, 'Manage_beginning_balance');

-- Enable for a specific group (replace GROUP_ID with actual group ID)
INSERT INTO access_level (group_id, Module, link, allow) VALUES (GROUP_ID, 6, 'Manage_beginning_balance', 1);
```

**Option C: Through User Interface**
1. Go to **User Management → Privileges**
2. Select a user group
3. The permission should appear under the Finance module
4. Check the box to enable it

### 3. Journal ID Configuration (Optional)
The module uses Journal ID `8` for beginning balance entries. If you need to use a different Journal ID:
- Edit `application/models/finance_model.php`
- Find the `beginning_balance_post_to_ledger()` function
- Change `'journalID' => 8` to your desired Journal ID
- Make sure this Journal ID exists in your `journal` table

## Usage

### Accessing the Module
1. Navigate to **Finance** menu
2. Click on **Beginning Balances**

### Creating a Beginning Balance
1. Click **Create Beginning Balance** button
2. Select a **Fiscal Year**
3. Select an **Account** from the chart of accounts
4. Enter **Debit** or **Credit** amount (at least one must be greater than zero)
5. Add optional **Description**
6. Click **Create Beginning Balance**

### Editing a Beginning Balance
1. From the beginning balances list, click **Edit** on an unposted entry
2. Modify the values as needed
3. Click **Update**

**Note**: You cannot edit beginning balances that have already been posted to General Ledger.

### Deleting a Beginning Balance
1. From the beginning balances list, click **Delete** on an unposted entry
2. Confirm the deletion

**Note**: You cannot delete beginning balances that have already been posted to General Ledger.

### Posting to General Ledger
1. From the beginning balances list, click **Post to General Ledger** on an unposted entry
2. Confirm the action
3. Once posted, the balance will appear in the General Ledger and cannot be edited or deleted

**Important**: Posting is a one-time action. Once posted, the beginning balance entry is locked and cannot be modified.

## Database Structure

### Table: `beginning_balances`
- `id`: Primary key
- `fiscal_year_id`: Reference to fiscal_year table
- `account`: Account number from account_chart
- `debit`: Beginning debit balance
- `credit`: Beginning credit balance
- `description`: Description/notes
- `posted`: 1 = Posted, 0 = Not Posted
- `posted_date`: Date when posted
- `posted_by`: User ID who posted
- `created_by`: User ID who created
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp
- `PIN`: Organization PIN

## Files Created/Modified

### New Files:
- `create_beginning_balances_table.php` - Database table creation script
- `application/views/finance/beginning_balance_list.php` - List view
- `application/views/finance/beginning_balance_form.php` - Create/Edit form

### Modified Files:
- `application/models/finance_model.php` - Added beginning balance model methods
- `application/controllers/finance.php` - Added beginning balance controller methods
- `application/views/menu.php` - Added menu item
- `application/views/newmenu.php` - Added menu item
- `application/language/english/finance_lang.php` - Added language strings

## Controller Methods

- `beginning_balance_list()` - Display list of beginning balances
- `beginning_balance_create($id)` - Create or edit beginning balance
- `beginning_balance_edit($id)` - Edit beginning balance (alias for create)
- `beginning_balance_delete($id)` - Delete beginning balance
- `beginning_balance_post($id)` - Post beginning balance to General Ledger

## Model Methods

- `beginning_balance_list($fiscal_year_id, $id)` - Get beginning balances
- `beginning_balance_create($data)` - Create beginning balance
- `beginning_balance_update($data, $id)` - Update beginning balance
- `beginning_balance_delete($id)` - Delete beginning balance
- `beginning_balance_post_to_ledger($id)` - Post to General Ledger
- `check_beginning_balance_exists($fiscal_year_id, $account)` - Check if balance exists

## Notes

1. **Unique Constraint**: Each fiscal year and account combination can only have one beginning balance entry
2. **Posting Date**: When posted, the beginning balance uses the fiscal year's start date as the transaction date
3. **Account Validation**: The system validates that the account exists in the chart of accounts before allowing creation
4. **Amount Validation**: At least one of debit or credit must be greater than zero

## Troubleshooting

### Menu Item Not Showing
- Check that you have the `Manage_beginning_balance` permission for module 6
- Verify the menu files were updated correctly

### Cannot Post Beginning Balance
- Ensure the account exists in the chart of accounts
- Verify the fiscal year exists and is valid
- Check database transaction logs for errors

### Table Creation Failed
- Verify database permissions
- Check that the table doesn't already exist
- Run the SQL manually in phpMyAdmin or your database tool

## Support

For issues or questions, please refer to your system administrator or check the application logs.
