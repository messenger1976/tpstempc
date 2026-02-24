# Savings Account Auto-Posting to General Ledger

## Overview

The savings module automatically posts transactions to the General Ledger (GL) for account opening, deposits, withdrawals, and interest. This ensures proper double-entry bookkeeping and maintains accurate financial records.

## Features

- ✅ **Automatic GL Posting**: Creates journal entries for opening balance, beginning balance, normal deposits, withdrawals, and interest
- ✅ **Double-Entry Accounting**: Proper debit/credit treatment per transaction type
- ✅ **Deposit (incl. Opening)**: Debit cash/bank, Credit savings liability
- ✅ **Withdrawal**: Debit savings liability, Credit cash/bank
- ✅ **Interest**: Debit interest expense account, Credit savings liability
- ✅ **Payment Method Mapping**: Maps payment methods (Cash, Cheque, Bank Transfer, Mobile Money) to appropriate GL accounts
- ✅ **Interest Expense Account**: Searches for expense account with "Interest" in name (account_type 50 or 70000)
- ✅ **Duplicate Prevention**: Checks if entry already exists before posting to prevent duplicates
- ✅ **Transaction Safety**: Uses database transactions to ensure data integrity
- ✅ **Error Logging**: Comprehensive logging for troubleshooting

## Installation

### Step 1: Run Installation Script

Execute the installation script to ensure Journal ID 9 (Savings Journal) exists:

```
http://your-domain.com/install_savings_gl_posting.php
```

This script will:
- Check if Journal ID 9 exists, create it if missing
- Verify required database tables exist
- Check savings account type configuration
- Provide configuration statistics

### Step 2: Configure Savings Account Types

Each savings account type must have an `account_setup` field configured that links to a GL liability account:

1. Go to **Settings → Saving Account Type**
2. Edit each savings account type
3. Set the **Account Setup** field to the appropriate GL liability account (e.g., "Savings Liability", "Member Savings", etc.)

### Step 3: Verify Payment Method Mapping

Ensure your chart of accounts has appropriate cash/bank accounts that match payment methods:

- **Cash**: Should map to Cash accounts (typically 1010001 or similar)
- **Cheque/Bank Transfer**: Should map to Bank accounts (typically 1010003 or similar)
- **Mobile Money**: Should map to Mobile Money accounts

The system will automatically search for accounts matching these names in the Asset account type (account_type = 1 or 10000).

### Step 4: Interest Expense Account (for interest postings)

For interest transactions to post to GL, you need an **interest expense account** in your chart of accounts:

1. Create an expense account (account_type 50 or 70000) with "Interest" in the name (e.g., "Savings Interest Expense", "Interest Expense")
2. The system searches for accounts where `name` or `description` contains "Interest" and `account_type` is 50 or 70000
3. If no interest expense account is found, interest GL posting will fail (transaction still succeeds; check logs)

## How It Works

### Accounting Entries by Transaction Type

| Transaction      | Debit                    | Credit                  |
|------------------|--------------------------|-------------------------|
| **Opening/Beginning** | Cash/Bank or Adjustment | Savings Liability       |
| **Deposit**      | Cash/Bank                | Savings Liability       |
| **Withdrawal**   | Savings Liability        | Cash/Bank               |
| **Interest**     | Interest Expense         | Savings Liability       |

**Example (Deposit):**
- Member deposits $1,000 cash
- **Debit**: Cash Account - $1,000
- **Credit**: Savings Liability Account (from `account_setup`) - $1,000

**Example (Withdrawal):**
- Member withdraws $500 cash
- **Debit**: Savings Liability Account - $500
- **Credit**: Cash Account - $500

**Example (Interest):**
- Interest of $50 credited to member
- **Debit**: Interest Expense Account - $50
- **Credit**: Savings Liability Account - $50

### Implementation Flow

```
Deposits (including Opening, Beginning Balance, Interest):
  credit_debit / create_account / savings_beginning_balance
    → credit() [Model]
    → post_savings_to_gl() for OPEN ACCOUNT, BEGINNING BALANCE, NORMAL DEPOSIT, INTEREST

Withdrawals:
  credit_debit [Deposit/Withdrawal]
    → debit() [Model]
    → post_savings_to_gl() for NORMAL WITHDRAWAL
```

### Code Location

- **Main Function**: `post_savings_to_gl()` in `application/models/finance_model.php`
- **Helper Functions**: `get_cash_account_for_savings()`, `get_interest_expense_account_for_savings()` in `application/models/finance_model.php`
- **Integration Points**: `credit()` and `debit()` in `application/models/finance_model.php`

## Configuration

### Journal ID

The system uses **Journal ID 9** for "Savings Journal". If this doesn't exist, the system falls back to Journal ID 5 (Manual Journal Entry). The installation script will create Journal ID 9 automatically.

### Payment Method Mapping

The system maps payment methods to account names as follows:

| Payment Method | Account Name Searched |
|---------------|----------------------|
| Cash | Cash |
| Cheque | Bank |
| Bank Transfer | Bank |
| Mobile Money | Mobile Money |
| M-Pesa | Mobile Money |
| Airtel Money | Mobile Money |
| Tigo Pesa | Mobile Money |

The system searches for accounts in `account_chart` where:
- `PIN` matches current user's PIN
- `account_type` is 1 or 10000 (Asset accounts)
- `name` contains the mapped account name

## Database Tables

### Tables Used

1. **savings_transaction**: Source transaction table (referenced via `receipt` field)
2. **general_ledger_entry**: GL entry header table
3. **general_ledger**: GL line items table
4. **saving_account_type**: Contains `account_setup` field for liability account
5. **account_chart**: Chart of accounts for account validation
6. **members_account**: Savings account details

### GL Entry Structure

Each GL entry contains:

```php
array(
    'journalID' => 9, // Savings Journal ID
    'refferenceID' => $receipt, // Savings transaction receipt number
    'entryid' => $ledger_entry_id, // Links to general_ledger_entry
    'date' => $trans_date, // Transaction date
    'account' => $account_code, // GL account code
    'debit' => $amount, // Debit amount (or 0)
    'credit' => $amount, // Credit amount (or 0)
    'description' => $description, // Description
    'account_type' => $account_type, // From account_chart
    'sub_account_type' => $sub_account_type, // From account_chart
    'linkto' => 'savings_transaction.receipt', // Reference link
    'fromtable' => 'savings_transaction', // Source table
    'PID' => $pid, // Member PID
    'member_id' => $member_id, // Member ID
    'PIN' => $pin // Organization PIN
)
```

## Error Handling

The system handles various error scenarios:

1. **Missing account_setup**: Logs error and returns false (doesn't break account creation)
2. **Missing cash account**: Logs error and returns false
3. **Zero amount**: Returns true (nothing to post, not an error)
4. **Duplicate posting**: Returns true if already posted (prevents duplicates)
5. **Database errors**: Logs detailed error messages and rolls back transaction

### Log Messages

Check application logs (`application/logs/`) for:

- `INFO`: Successful GL postings
- `ERROR`: Failed GL postings with details
- `DEBUG`: Detailed processing information

Example log entries:

```
INFO: Savings account posted to GL successfully: Account 12345, Receipt ABC123, Debit: 1010001 (1000), Credit: 2001001 (1000)
ERROR: Savings account type not found or account_setup not configured for account_cat: 1001
ERROR: Cash account not found for payment method: Cash
```

## Testing

### Test Steps

1. **Create a Savings Account**:
   - Go to **Savings → Create Account**
   - Select a member
   - Choose a savings account type (ensure it has `account_setup` configured)
   - Enter opening balance (e.g., $1,000)
   - Select payment method (e.g., Cash)
   - Submit

2. **Verify GL Entry**:
   - Go to **Finance → General Ledger** (or relevant report)
   - Filter by date and search for the transaction
   - Verify:
     - Debit entry for cash account
     - Credit entry for savings liability account
     - Amount matches opening balance
     - Description contains account number and receipt

3. **Check for Errors**:
   - Review application logs
   - Verify no duplicate entries
   - Confirm transaction balance (debits = credits)

### Test Scenarios

- ✅ Create account with Cash payment
- ✅ Create account with Cheque payment
- ✅ Create account with zero opening balance (should skip GL posting)
- ✅ Create account without `account_setup` configured (should log error)
- ✅ Try creating same account twice (should prevent duplicate posting)
- ✅ Deposit via Deposit/Withdrawal (NORMAL DEPOSIT → GL)
- ✅ Withdrawal via Deposit/Withdrawal (NORMAL WITHDRAWAL → GL)
- ✅ Interest posting (requires interest expense account in chart of accounts)

## Troubleshooting

### Issue: GL entries not being created

**Possible Causes:**
1. `account_setup` not configured for savings account type
2. Cash account not found for payment method
3. Journal ID 9 doesn't exist (check installation script)
4. Database transaction errors

**Solution:**
1. Check application logs for specific error messages
2. Run installation script to verify configuration
3. Ensure savings account types have `account_setup` field populated
4. Verify cash/bank accounts exist in chart of accounts
5. For interest: create an expense account (account_type 50 or 70000) with "Interest" in the name

### Issue: Duplicate GL entries

**Possible Causes:**
1. Script being called multiple times
2. Duplicate check not working properly

**Solution:**
1. Check for existing entries before creating new ones (system does this automatically)
2. Review logs to identify duplicate creation attempts

### Issue: Wrong accounts being posted

**Possible Causes:**
1. `account_setup` pointing to wrong account
2. Payment method mapping incorrect
3. Cash account lookup finding wrong account

**Solution:**
1. Verify `account_setup` field in saving_account_type table
2. Check payment method name matches expected values (case-insensitive)
3. Review `get_cash_account_for_savings()` function logic

## Maintenance

### Regular Checks

1. **Monitor Logs**: Regularly check application logs for errors
2. **Verify Configuration**: Ensure all savings account types have `account_setup` configured
3. **Reconciliation**: Periodically reconcile GL entries with savings transactions
4. **Account Validation**: Verify cash/bank accounts in chart of accounts are correct

### Backup

Before making changes:
- Backup `general_ledger` and `general_ledger_entry` tables
- Backup `savings_transaction` table
- Document current configuration

## Related Documentation

- `HOW_TO_POST_JOURNAL_TO_GENERAL_LEDGER.md`: General GL posting guide
- `CASH_RECEIPT_MODULE_README.md`: Similar implementation for cash receipts
- `FINANCE_MODULES_COMPLETE_SUMMARY.md`: Overview of all finance modules

## Support

If you encounter issues:

1. Check application logs first
2. Run the installation script to verify configuration
3. Review this documentation
4. Contact system administrator if issue persists

## Version History

- **v1.1**: Extended GL posting to deposits, withdrawals, and interest (credit_debit transactions)
- **v1.0**: Initial implementation of auto-posting to GL for savings account creation and beginning balance
