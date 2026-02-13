# Loan Beginning Balances in Loan List - Implementation Guide

## Overview
This document describes the implementation of displaying loan beginning balances in the main Loan List view. Previously, loan beginning balances were stored in a separate table (`loan_beginning_balances`) and were not visible in the standard loan list, making it difficult to see all loans in one place.

## Problem Statement
Loan beginning balances were not appearing in the Loan List (`/loan/loan_viewlist`) because:
- The `count_loan()` and `search_loan()` methods in `loan_model.php` only queried the `loan_contract` table
- Loan beginning balances are stored in a separate `loan_beginning_balances` table
- There was no mechanism to combine or display both types of loans together

## Solution
Modified the `loan_model.php` file to include loan beginning balances in both the count and search queries by:
1. Fetching loan beginning balances separately
2. Mapping fields to match the expected structure
3. Combining results with regular loans
4. Excluding duplicates (beginning balances that already have corresponding loan contracts)

## Files Modified

### `application/models/loan_model.php`

#### Changes Made

**1. `count_loan()` Method**
- Added query to count loan beginning balances
- Excludes beginning balances that already have corresponding `loan_contract` entries (to avoid duplicates)
- Returns combined count of regular loans and beginning balances

**2. `search_loan()` Method**
- Fetches regular loans from `loan_contract` table (existing functionality)
- Fetches loan beginning balances from `loan_beginning_balances` table
- Maps beginning balance fields to match expected structure:
  - `loan_id` → `LID` (or generates `BB-{id}` if loan_id is null)
  - `loan_amount` or `principal_balance` → `basic_amount`
  - `term` → `number_istallment`
  - `monthly_amort` → `installment_amount`
  - `interest_balance` → `total_interest_amount`
  - `total_balance` → `total_loan`
  - Status set to "Beginning Balance"
  - `interval` field retrieved from `loan_product` table
- Combines both result sets
- Sorts by application/disbursement date
- Applies pagination

## Field Mapping

### Loan Beginning Balance → Loan Contract Structure

| Beginning Balance Field | Mapped To | Notes |
|------------------------|-----------|-------|
| `loan_id` | `LID` | Uses `BB-{id}` if `loan_id` is null |
| `member_id` | `member_id` | Direct mapping |
| `loan_amount` or `principal_balance` | `basic_amount` | Uses `loan_amount` if available, otherwise `principal_balance` |
| `term` | `number_istallment` | Direct mapping |
| `monthly_amort` | `installment_amount` | Direct mapping |
| `interest_balance` | `total_interest_amount` | Direct mapping |
| `total_balance` | `total_loan` | Direct mapping |
| `loan_product.interval` | `interval` | Retrieved from `loan_product` table |
| `disbursement_date` | `applicationdate` | Used for sorting |
| Static value | `name` | Set to "Beginning Balance" |
| Static value | `edit` | Set to 1 (non-editable) |

## SQL Query Structure

### Beginning Balances Query
```sql
SELECT 
    COALESCE(loan_beginning_balances.loan_id, CONCAT('BB-', loan_beginning_balances.id)) as LID,
    members.PID,
    loan_beginning_balances.member_id,
    COALESCE(loan_beginning_balances.loan_amount, loan_beginning_balances.principal_balance) as basic_amount,
    loan_beginning_balances.term as number_istallment,
    loan_beginning_balances.monthly_amort as installment_amount,
    loan_beginning_balances.interest_balance as total_interest_amount,
    loan_beginning_balances.total_balance as total_loan,
    'Beginning Balance' as name,
    1 as edit,
    COALESCE(loan_product.`interval`, 1) as `interval`,
    loan_beginning_balances.disbursement_date as applicationdate
FROM loan_beginning_balances 
INNER JOIN members ON members.member_id=loan_beginning_balances.member_id 
LEFT JOIN loan_product ON loan_product.id=loan_beginning_balances.loan_product_id AND loan_product.PIN='{pin}'
WHERE loan_beginning_balances.PIN='{pin}' 
    AND members.PIN='{pin}' 
    AND (loan_beginning_balances.loan_id IS NULL 
         OR loan_beginning_balances.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN='{pin}'))
```

**Important Notes:**
- The `interval` field is escaped with backticks because it's a reserved keyword in MySQL/MariaDB
- Beginning balances with a `loan_id` that matches an existing `LID` in `loan_contract` are excluded to prevent duplicates
- Beginning balances without a `loan_id` are included and given a generated LID format: `BB-{id}`

## Duplicate Prevention Logic

The implementation prevents duplicate entries by excluding beginning balances that already have corresponding loan contracts:

```sql
AND (loan_beginning_balances.loan_id IS NULL 
     OR loan_beginning_balances.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN='{pin}'))
```

This ensures:
- Beginning balances with no `loan_id` are always included
- Beginning balances with a `loan_id` are only included if that `loan_id` doesn't exist as an `LID` in `loan_contract`

## Display Behavior

### In the Loan List View
- Loan beginning balances appear alongside regular loans
- They are identified by the status "Beginning Balance"
- The LID format is either:
  - The original `loan_id` if it exists
  - `BB-{id}` format if `loan_id` is null
- Beginning balances are marked as non-editable (`edit = 1`)
- All loan information (amounts, installments, etc.) is displayed correctly

### Sorting
- Combined results are sorted by `applicationdate` (for regular loans) or `disbursement_date` (for beginning balances)
- Both types of loans are intermingled chronologically

### Pagination
- Pagination works correctly with the combined result set
- Both regular loans and beginning balances are included in the pagination count

## Testing Instructions

### 1. Verify Loan Beginning Balances Appear
1. Navigate to `/loan/loan_viewlist`
2. Verify that loan beginning balances appear in the list
3. Check that they show "Beginning Balance" as the status
4. Verify the LID format (should be `loan_id` or `BB-{id}`)

### 2. Verify No Duplicates
1. Create a loan beginning balance with a `loan_id` that matches an existing loan contract `LID`
2. Verify that this beginning balance does NOT appear in the list (to avoid duplicate)
3. Create a loan beginning balance with a null `loan_id`
4. Verify that this beginning balance DOES appear with `BB-{id}` format

### 3. Verify Search Functionality
1. Search by member ID that has both regular loans and beginning balances
2. Verify both types appear in search results
3. Search by loan ID (including `BB-{id}` format)
4. Verify beginning balances are found

### 4. Verify Pagination
1. If there are many loans, verify pagination works correctly
2. Verify the total count includes both regular loans and beginning balances
3. Navigate through pages and verify all loans appear

### 5. Verify Field Display
1. Check that all fields display correctly:
   - LID
   - Member name
   - Applied amount (basic_amount)
   - Installment information
   - Interest amount
   - Total loan amount
   - Status

## Known Limitations

1. **Edit Functionality**: Beginning balances are marked as non-editable (`edit = 1`), so the edit link won't appear for them in the list
2. **View Detail**: Clicking "View Detail" on a beginning balance with `BB-{id}` format may not work if the detail view expects a real `LID` from `loan_contract`
3. **Member Join**: The implementation assumes `members.member_id` exists and matches `loan_beginning_balances.member_id`

## Troubleshooting

### Issue: Beginning balances not appearing
**Possible Causes:**
- Member not found (INNER JOIN fails)
- PIN mismatch
- All beginning balances have `loan_id` that matches existing `LID`

**Solution:**
- Verify member exists in `members` table with matching `member_id`
- Check PIN values match
- Verify `loan_id` values in beginning balances

### Issue: SQL syntax error with `interval`
**Cause:** `interval` is a reserved keyword in MySQL/MariaDB

**Solution:** Already fixed by using backticks: `` `interval` ``

### Issue: Duplicate entries appearing
**Cause:** Logic for excluding duplicates not working correctly

**Solution:**
- Verify the subquery in the exclusion logic
- Check that `loan_id` values match `LID` values correctly

## Future Enhancements

1. **Edit Functionality**: Allow editing of beginning balances from the loan list
2. **View Detail**: Implement detail view for beginning balances
3. **Filter Options**: Add filter to show only regular loans, only beginning balances, or both
4. **Visual Distinction**: Add visual indicators (icons, colors) to distinguish beginning balances from regular loans

## Related Files

- `application/models/loan_model.php` - Main model with modified methods
- `application/views/loan/viewloanlist.php` - View that displays the loan list
- `application/controllers/loan.php` - Controller that calls the model methods
- `application/views/loan/loan_beginning_balance_list.php` - Separate view for managing beginning balances

## Version History

- **2026-02-09**: Initial implementation
  - Modified `count_loan()` and `search_loan()` methods
  - Added field mapping for beginning balances
  - Implemented duplicate prevention logic
  - Fixed SQL syntax error with reserved keyword `interval`

## Support

For issues or questions regarding this implementation, please refer to:
- The main loan beginning balances module documentation
- Database schema documentation for `loan_beginning_balances` table
- Loan contract module documentation
