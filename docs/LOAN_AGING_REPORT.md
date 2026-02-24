# Loan Aging Report - Implementation Guide

## Overview
The Loan Aging Report provides a comprehensive view of loans grouped by how many days they are overdue. This helps identify delinquent loans and prioritize collection efforts based on the age of outstanding balances.

## Features

### Aging Buckets
The report groups loans into the following aging buckets:
- **Current (0-30 days)**: Loans that are current or less than 30 days overdue
- **31-60 days**: Loans overdue between 31 and 60 days
- **61-90 days**: Loans overdue between 61 and 90 days
- **91-180 days**: Loans overdue between 91 and 180 days
- **Over 180 days**: Loans overdue more than 180 days

### Report Information Displayed
For each loan, the report shows:
- Loan ID
- Member ID and Name
- Loan Type/Product
- Disbursement Date
- Oldest Unpaid Due Date
- Days Overdue (highlighted in red if overdue)
- Outstanding Principal
- Outstanding Interest
- Outstanding Penalty
- Total Outstanding Balance

### Summary Information
- Subtotal for each aging bucket (count, totals)
- Grand totals across all buckets
- Number of loans in each bucket

## Files Created/Modified

### 1. Model: `application/models/report_model.php`
**Added Method:** `loan_aging_report($as_of_date)`
- Fetches all active disbursed loans
- Calculates days overdue based on oldest unpaid installment
- Groups loans into aging buckets
- Calculates outstanding balances (principal, interest, penalty)
- Returns structured data organized by aging buckets

### 2. Controller: `application/controllers/report_loan.php`
**Added Methods:**
- `loan_aging_view($link, $id)` - Displays the aging report
- `loan_aging_print($link, $id)` - Generates PDF version

**Modified Methods:**
- `loan_report($link, $id)` - Added handling for link 7 (aging report)
- `create_loan_report_title($link, $id)` - Added form validation for aging report (uses "As of Date" instead of date range)

### 3. Views Created:
- `application/views/report/loan/loan_aging_report.php` - Main report view
- `application/views/report/loan/print/loan_aging_report_print.php` - Print/PDF view

### 4. Views Modified:
- `application/views/report/loan/create_loan_report_title.php` - Updated to handle aging report (single date instead of date range)
- `application/views/report/loan/loan_report_title.php` - Added view link for aging report
- `application/views/report/home.php` - Added menu link to access aging report

### 5. Language File: `application/language/english/loan_lang.php`
**Added:**
- `$lang['report_loan_aging'] = 'Loan Aging Report';`

## How It Works

### Calculation Logic

1. **Active Loans**: Only includes loans that are:
   - Status = 4 (Active/Disbursed)
   - Disburse = 1 (Actually disbursed)
   - Disbursed on or before the "As of Date"

2. **Days Overdue Calculation**:
   - Finds the oldest unpaid installment (status = 0) for each loan
   - Calculates the difference between the "As of Date" and the due date
   - If due date is in the future, days overdue = 0

3. **Outstanding Balance Calculation**:
   - Outstanding Principal = Basic Amount - Principal Paid
   - Outstanding Interest = Sum of unpaid interest from schedule
   - Outstanding Penalty = Sum of unpaid penalties
   - Total Outstanding = Sum of all unpaid amounts from schedule

4. **Aging Bucket Assignment**:
   - Based on days overdue, loans are assigned to appropriate buckets
   - Loans with zero or negative balances are excluded

## Usage

### Accessing the Report

1. Navigate to **Reports** → **Loans** → **Loan Aging Report**
2. Click **"New Report"** to create a new aging report
3. Enter:
   - **As of Date**: The date to calculate aging as of (defaults to current date)
   - **Description**: Optional description for the report
   - **Page Orientation**: Portrait or Landscape
4. Click **"Save Report Information"**
5. Click **"View"** to see the report
6. Click **"Print"** to generate PDF

### Report Structure

The report displays:
- **Header**: Company name, report title, as of date, description
- **Aging Buckets**: Each bucket shows:
  - Bucket header with label and loan count
  - Individual loan details
  - Subtotal row with totals for that bucket
- **Grand Total**: Summary row with totals across all buckets

## Database Queries

### Main Query
```sql
SELECT 
    lc.LID,
    lc.PID,
    lc.member_id,
    lc.basic_amount,
    lc.total_loan,
    lc.product_type,
    lcd.disbursedate,
    (SELECT SUM(lcr.amount) FROM loan_contract_repayment lcr WHERE lcr.LID = lc.LID) as total_repaid,
    (SELECT SUM(lcr.principle) FROM loan_contract_repayment lcr WHERE lcr.LID = lc.LID) as principle_paid,
    (SELECT SUM(lcr.interest) FROM loan_contract_repayment lcr WHERE lcr.LID = lc.LID) as interest_paid,
    (SELECT SUM(lcr.penalt) FROM loan_contract_repayment lcr WHERE lcr.LID = lc.LID) as penalty_paid,
    (SELECT MIN(lcrs.repaydate) FROM loan_contract_repayment_schedule lcrs 
     WHERE lcrs.LID = lc.LID AND lcrs.status = 0) as oldest_unpaid_due_date
FROM loan_contract lc
INNER JOIN loan_contract_disburse lcd ON lcd.LID = lc.LID
WHERE lc.PIN = '{pin}' 
    AND lc.status = 4 
    AND lc.disburse = 1
    AND lcd.disbursedate <= '{as_of_date}'
```

### Unpaid Installments Query
For each loan, calculates outstanding amounts from unpaid installments:
```sql
SELECT 
    SUM(repayamount + balance) as total_due,
    SUM(interest) as total_interest,
    SUM(balance) as total_balance
FROM loan_contract_repayment_schedule 
WHERE LID = '{LID}' AND status = 0
```

## Key Features

### Visual Indicators
- **Days Overdue**: Displayed in red if overdue, green if current
- **Bucket Headers**: Highlighted to distinguish different aging categories
- **Subtotals**: Clearly marked for each bucket
- **Grand Totals**: Highlighted summary row

### Data Accuracy
- Only includes loans with actual outstanding balances
- Excludes fully paid loans
- Uses actual unpaid installment data for accurate calculations
- Handles loans with no unpaid installments gracefully

### Performance Considerations
- Uses subqueries for efficient data retrieval
- Processes data in PHP for flexible grouping
- Optimized for reasonable loan volumes

## Limitations

1. **Beginning Balances**: Currently only includes loans from `loan_contract` table. Loan beginning balances are not included in this report.

2. **Performance**: For very large loan portfolios (thousands of loans), the report may take longer to generate.

3. **Real-time Data**: The report is based on the "As of Date" - it's a snapshot, not real-time.

## Future Enhancements

1. **Export to Excel**: Add Excel export functionality
2. **Filtering Options**: 
   - Filter by loan product
   - Filter by member
   - Filter by specific aging buckets
3. **Trend Analysis**: Compare aging reports across different dates
4. **Collection Actions**: Add ability to mark loans for collection follow-up
5. **Email Alerts**: Automatic alerts for loans in high-risk buckets
6. **Include Beginning Balances**: Add option to include loan beginning balances

## Testing

### Test Scenarios

1. **Create Report**:
   - Create a new aging report with current date
   - Verify report displays correctly
   - Check that all active loans appear

2. **Aging Buckets**:
   - Verify loans are correctly grouped into buckets
   - Check that days overdue calculation is accurate
   - Verify subtotals are correct

3. **Outstanding Balances**:
   - Verify outstanding amounts match loan details
   - Check that fully paid loans are excluded
   - Verify grand totals are accurate

4. **Print Functionality**:
   - Test PDF generation
   - Verify print layout is correct
   - Check that all data appears in print version

5. **Edge Cases**:
   - Loans with no unpaid installments
   - Loans with future due dates
   - Loans with zero balance
   - Loans with only partial payments

## Troubleshooting

### Issue: Report shows no loans
**Possible Causes:**
- No active disbursed loans exist
- All loans are fully paid
- Date filter excludes all loans

**Solution:**
- Verify loan status and disbursement status
- Check loan repayment records
- Adjust "As of Date" if needed

### Issue: Days overdue calculation incorrect
**Possible Causes:**
- Repayment schedule dates incorrect
- Timezone issues
- Date format problems

**Solution:**
- Verify repayment schedule data
- Check date formats in database
- Ensure "As of Date" is correct

### Issue: Outstanding balances don't match
**Possible Causes:**
- Repayment records not properly recorded
- Schedule status not updated
- Calculation logic issue

**Solution:**
- Verify repayment records in database
- Check schedule status values
- Review calculation logic in model

## Related Reports

- **Loan Balance Report**: Shows overall loan balances
- **Loan Transaction Report**: Shows repayment transactions
- **Loan Statement**: Individual loan details
- **Interest & Penalty Report**: Shows interest and penalty balances

## Support

For issues or questions regarding the Loan Aging Report:
1. Check this documentation
2. Review the model method `loan_aging_report()` for calculation logic
3. Verify database data integrity
4. Check repayment schedule status values

## Version History

- **2026-02-09**: Initial implementation
  - Created loan aging report feature
  - Added aging bucket grouping
  - Implemented days overdue calculation
  - Added print/PDF functionality
