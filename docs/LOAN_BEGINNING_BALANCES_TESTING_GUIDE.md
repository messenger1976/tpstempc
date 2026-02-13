# Loan Beginning Balances Module - Testing Guide

## Overview
This document provides a comprehensive testing guide for the Loan Beginning Balances module.

## Prerequisites
1. Database table `loan_beginning_balances` must be created
2. User must have `Loan_beginning_balances` permission under module 5 (Loan Management)
3. At least one fiscal year must be configured in the system
4. At least one loan product must be configured
5. At least one member must exist in the system

## Test Cases

### 1. Database Table Creation
**Test**: Run the table creation script
- Navigate to: `http://your-domain.com/create_loan_beginning_balances_table.php`
- Expected: Success message showing table created
- Verify: Check database for `loan_beginning_balances` table with correct structure

### 2. Menu Access
**Test**: Verify menu item is visible
- Login as user with `Loan_beginning_balances` permission
- Navigate to Loan Management menu
- Expected: "Loan Beginning Balances" menu item should be visible
- Click the menu item
- Expected: Navigate to loan beginning balance list page

### 3. List View - Empty State
**Test**: View list with no data
- Navigate to Loan Beginning Balances page
- Expected: 
  - Fiscal year selection dropdown is displayed
  - "Create Loan Beginning Balance" button is visible
  - Warning message: "Please select a fiscal year to view loan beginning balances"

### 4. List View - With Fiscal Year Selected
**Test**: Select a fiscal year
- Select a fiscal year from dropdown
- Click "View" button
- Expected:
  - List table is displayed with columns:
    - S/No
    - Member ID
    - Member Name
    - Loan Product
    - Loan ID
    - Principal Balance
    - Interest Balance
    - Penalty Balance
    - Total Balance
    - Status
    - Action
  - If no records: "Data not found" message

### 5. Create Beginning Balance - Form Display
**Test**: Open create form
- Click "Create Loan Beginning Balance" button
- Expected: Form with fields:
  - Fiscal Year (required, dropdown)
  - Member ID (required, text input)
  - Loan Product (required, dropdown)
  - Loan ID (optional, text input)
  - Principal Balance (number, defaults to 0.00)
  - Interest Balance (number, defaults to 0.00)
  - Penalty Balance (number, defaults to 0.00)
  - Total Balance (calculated, readonly)
  - Disbursement Date (optional, date picker)
  - Description (optional, textarea)
  - Create/Cancel buttons

### 6. Create Beginning Balance - Validation Tests

#### Test 6.1: Required Fields
- Leave fiscal year empty, click Create
- Expected: Validation error for fiscal year
- Leave member ID empty, click Create
- Expected: Validation error for member ID
- Leave loan product empty, click Create
- Expected: Validation error for loan product

#### Test 6.2: Amount Validation
- Set all balances to 0.00, click Create
- Expected: Error "At least one balance amount must be greater than zero"

#### Test 6.3: Invalid Member ID
- Enter non-existent member ID
- Expected: Error "Member not found"

#### Test 6.4: Invalid Loan Product
- Select invalid/deleted loan product
- Expected: Error "Loan product not found"

#### Test 6.5: Duplicate Entry
- Create a balance for fiscal year X, member Y, product Z
- Try to create another with same combination
- Expected: Error "Loan beginning balance already exists for this fiscal year, member and loan product"

### 7. Create Beginning Balance - Success Test
**Test**: Create valid beginning balance
- Fill in:
  - Fiscal Year: Select valid year
  - Member ID: Valid member ID
  - Loan Product: Select valid product
  - Principal Balance: 10000.00
  - Interest Balance: 500.00
  - Penalty Balance: 100.00
  - Description: "Migration from old system"
- Click "Create Beginning Balance"
- Expected:
  - Success message: "Loan beginning balance created successfully"
  - Redirect to list view with the fiscal year selected
  - New record visible with:
    - Total Balance: 10600.00
    - Status: "Not Posted" (orange label)
    - Actions: Edit, Delete, Post buttons visible

### 8. Total Calculation Test
**Test**: Verify automatic total calculation
- On create/edit form
- Enter Principal: 5000.00
- Enter Interest: 250.00
- Enter Penalty: 50.00
- Expected: Total Balance automatically shows 5300.00
- Change Principal to 6000.00
- Expected: Total Balance updates to 6300.00

### 9. Edit Beginning Balance - Access
**Test**: Edit unposted balance
- Click Edit button on unposted balance
- Expected:
  - Form loads with existing data
  - Title: "Edit Loan Beginning Balance"
  - All fields pre-populated
  - Button text: "Update"

### 10. Edit Beginning Balance - Validation
**Test**: Edit with validation
- Edit an existing balance
- Change member ID to existing member with different product
- Expected: Update succeeds
- Change member ID to same member but product that already exists
- Expected: Error "Loan beginning balance already exists..."

### 11. Edit Beginning Balance - Success
**Test**: Update balance successfully
- Edit a balance
- Change Principal from 10000 to 12000
- Click Update
- Expected:
  - Success message: "Loan beginning balance updated successfully"
  - List shows updated total (12600.00)

### 12. Edit Posted Balance - Denied
**Test**: Try to edit posted balance
- Post a balance to GL (see test 16)
- Try to click Edit on that balance
- Expected: Edit link not available, shows "Already posted - Cannot edit"

### 13. Delete Beginning Balance - Unposted
**Test**: Delete unposted balance
- Click Delete button on unposted balance
- Expected: 
  - SweetAlert confirmation dialog appears
  - Message includes member ID
- Click "Yes, Delete"
- Expected:
  - Success message: "Loan beginning balance deleted successfully"
  - Record removed from list

### 14. Delete Beginning Balance - Posted (Denied)
**Test**: Try to delete posted balance
- Try to delete a posted balance
- Expected: No delete button available for posted records

### 15. Post to General Ledger - Validation
**Test**: Attempt to post
- Click "Post to General Ledger" on unposted balance
- Expected: Confirmation dialog with message:
  "Are you sure you want to post this loan beginning balance to the General Ledger? This action cannot be undone."

### 16. Post to General Ledger - Success
**Test**: Post balance successfully
- Click "Post to General Ledger" on balance with:
  - Principal: 10000.00
  - Interest: 500.00
  - Penalty: 100.00
- Confirm the action
- Expected:
  - Success message: "Loan beginning balance posted to General Ledger successfully"
  - Status changes to "Posted" (green label)
  - Posted date displayed
  - Edit and Delete buttons removed
  - Only "Already posted - Cannot edit" text shown

### 17. General Ledger Verification
**Test**: Verify GL entries created
- After posting, check General Ledger
- Expected entries created:
  1. Principal entry:
     - Date: Fiscal year start date
     - Description: "Loan Beginning Balance - Principal - [Member ID]"
     - Account: loan_principle_account from product
     - Debit: 10000.00
     - Credit: 0.00
  2. Interest entry:
     - Date: Fiscal year start date
     - Description: "Loan Beginning Balance - Interest - [Member ID]"
     - Account: loan_interest_account from product
     - Debit: 500.00
     - Credit: 0.00
  3. Penalty entry:
     - Date: Fiscal year start date
     - Description: "Loan Beginning Balance - Penalty - [Member ID]"
     - Account: loan_penalt_account from product
     - Debit: 100.00
     - Credit: 0.00

### 18. Filter by Fiscal Year
**Test**: Filter functionality
- Create balances for multiple fiscal years
- Select different fiscal years from dropdown
- Expected: Only balances for selected year displayed

### 19. Date Picker
**Test**: Disbursement date picker
- On create/edit form
- Click Disbursement Date field
- Expected: Date picker calendar appears
- Select a date
- Expected: Date formatted as MM/DD/YYYY

### 20. Number Formatting
**Test**: Amount field formatting
- On create/edit form
- Enter principal: 1234567.89
- Tab out of field
- Expected: Number formatted with 2 decimal places
- Enter invalid characters (letters)
- Expected: Non-numeric characters stripped

## Expected UI Screenshots

### Screenshot 1: List View - Empty State
- Fiscal year dropdown
- "Create Loan Beginning Balance" button
- Warning message about selecting fiscal year

### Screenshot 2: List View - With Data
- Table showing multiple loan beginning balances
- Different statuses (Posted/Not Posted)
- Action buttons (Edit, Delete, Post for unposted)
- Fiscal year info banner

### Screenshot 3: Create Form
- All form fields visible
- Dropdown selections
- Number inputs with proper formatting
- Date picker field
- Create/Cancel buttons

### Screenshot 4: Edit Form
- Same as create but with pre-populated data
- "Update" button instead of "Create"
- All fields editable (for unposted balance)

### Screenshot 5: Delete Confirmation
- SweetAlert confirmation dialog
- Warning icon
- Member ID in message
- Yes/Cancel buttons

### Screenshot 6: Post Confirmation
- Browser confirmation dialog
- Warning message about irreversibility

### Screenshot 7: Posted Balance
- Green "Posted" label
- Posted date displayed
- No edit/delete buttons
- "Already posted" message in action column

### Screenshot 8: Success Messages
- Green success notification after create
- Green success notification after update
- Green success notification after delete
- Green success notification after posting

### Screenshot 9: Error Messages
- Red error notification for validation failures
- Examples of different error messages

## Performance Tests

### Test P1: Large Dataset
- Create 100+ beginning balances for one fiscal year
- Expected: List loads within reasonable time
- Pagination may be needed in future (currently not implemented)

### Test P2: Multiple Users
- Multiple users accessing simultaneously
- Expected: No conflicts, proper data isolation by PIN

## Security Tests

### Test S1: Permission Check
- Login without `Loan_beginning_balances` permission
- Expected: Menu item not visible
- Try direct URL access
- Expected: Access denied or redirect

### Test S2: Data Isolation
- Create balance as User A (PIN X)
- Login as User B (PIN Y)
- Expected: User B cannot see User A's balances

### Test S3: Posted Balance Protection
- Posted balance should not be modifiable via:
  - Edit form
  - Delete action
  - Direct URL manipulation

## Integration Tests

### Test I1: Member Validation
- Use valid member ID from members table
- Expected: Member name displayed correctly
- Use invalid member ID
- Expected: Error message

### Test I2: Loan Product Integration
- Select loan product
- Post to GL
- Expected: Correct GL accounts used from product configuration

### Test I3: Fiscal Year Integration
- Select fiscal year
- Post to GL
- Expected: GL entries dated with fiscal year start date

## Known Limitations (To Document)
1. No pagination on list view (may be needed for large datasets)
2. No bulk operations (bulk import, bulk post)
3. No export functionality (CSV/Excel)
4. No audit trail for changes before posting
5. Cannot unpost from GL
6. No validation against actual loan contracts in system

## Recommended Enhancements (Future)
1. Add pagination to list view
2. Add search/filter by member ID
3. Add export to CSV/Excel
4. Add bulk import from spreadsheet
5. Add validation to check if member actually has loan
6. Add comparison with actual loan balances
7. Add ability to generate beginning balance report
8. Add audit trail for all changes
