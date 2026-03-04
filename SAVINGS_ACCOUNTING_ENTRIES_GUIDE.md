# Savings Account Transactions - Accounting Entries Guide

**Date:** March 4, 2026  
**Module:** Saving → Credit/Debit  
**Applies to:** Interest, Deposits, and Withdrawals with various payment methods

---

## Overview

This document explains how accounting entries are recorded when processing savings account transactions through the **Saving → Credit/Debit** module, particularly focusing on the **ADJUSTMENT** payment method and **Interest (INT)** transactions.

---

## Payment Methods Available

| Payment Method | Description | GL Account Used |
|---|---|---|
| CASH | Cash transactions | Cash Account (Asset) |
| CHEQUE | Cheque payments | Bank Account (Asset) |
| BANK DEPOSIT | Bank transfers | Bank Account (Asset) |
| OTHERS | Other payment sources | Suspense/Miscellaneous Account |
| **ADJUSTMENT** | Journal Voucher / GL Adjustments | Equity/Adjustment Account |

---

## Transaction Types

1. **CR** - Deposit
2. **DR** - Withdrawal
3. **INT** - Interest

---

## Accounting Entries by Transaction Type

### 1. INTEREST (INT) - Always Interest Expense

**Rule:** Interest transactions **ALWAYS** use the Interest Expense Account, regardless of payment method selected.

#### Example: Interest with ADJUSTMENT Payment Method

```
Transaction:
- Account: Member Savings Account #123
- Transaction Type: INT (Interest)
- Amount: 10,000
- Payment Method: ADJUSTMENT

General Ledger Entries:
┌────────────────────────────────────────────────────────┐
│ Debit:   Interest Expense Account        10,000.00     │
│ Credit:  Savings Liability Account       10,000.00     │
├────────────────────────────────────────────────────────┤
│ Journal: Manual Journal (5) - JV Entry                 │
│ Description: [JV] Savings Interest - Member Name       │
│              (Account: 123, Receipt: XXXX)             │
└────────────────────────────────────────────────────────┘
```

#### Example: Interest with CASH Payment Method

```
Transaction:
- Account: Member Savings Account #123
- Transaction Type: INT (Interest)
- Amount: 10,000
- Payment Method: CASH

General Ledger Entries:
┌────────────────────────────────────────────────────────┐
│ Debit:   Interest Expense Account        10,000.00     │
│ Credit:  Savings Liability Account       10,000.00     │
├────────────────────────────────────────────────────────┤
│ Journal: Savings Journal (9)                           │
│ Description: Savings Interest - Member Name            │
│              (Account: 123, Receipt: XXXX,             │
│               Payment Method: CASH)                    │
└────────────────────────────────────────────────────────┘
```

**Key Points:**
- ✓ Interest ALWAYS debits Interest Expense Account
- ✓ Payment method does NOT change the GL accounts used
- ✓ Payment method only affects which Journal is used (Manual Journal for ADJUSTMENT, Savings Journal for others)
- ✓ ADJUSTMENT entries are marked with [JV] prefix

---

### 2. DEPOSIT (CR) - Regular or Adjustment

#### Example: Deposit with CASH Payment Method

```
Transaction:
- Account: Member Savings Account #123
- Transaction Type: CR (Deposit)
- Amount: 50,000
- Payment Method: CASH

General Ledger Entries:
┌────────────────────────────────────────────────────────┐
│ Debit:   Cash Account                    50,000.00     │
│ Credit:  Savings Liability Account       50,000.00     │
├────────────────────────────────────────────────────────┤
│ Journal: Savings Journal (9)                           │
│ Description: Savings Deposit - Member Name             │
│              (Account: 123, Receipt: XXXX,             │
│               Payment Method: CASH)                    │
└────────────────────────────────────────────────────────┘
```

#### Example: Deposit with ADJUSTMENT Payment Method (Beginning Balance)

```
Transaction:
- Account: Member Savings Account #123
- Transaction Type: CR (Deposit)
- Amount: 50,000
- Payment Method: ADJUSTMENT

General Ledger Entries:
┌────────────────────────────────────────────────────────┐
│ Debit:   Equity/Adjustment Account       50,000.00     │
│ Credit:  Savings Liability Account       50,000.00     │
├────────────────────────────────────────────────────────┤
│ Journal: Manual Journal (5) - JV Entry                 │
│ Description: [JV] Savings Beginning Balance            │
│              Adjustment - Member Name                  │
│              (Account: 123, Receipt: XXXX)             │
└────────────────────────────────────────────────────────┘
```

**Key Points:**
- ✓ CASH/CHEQUE/BANK = Debit goes to Cash/Bank Account (Asset)
- ✓ ADJUSTMENT = Debit goes to Equity/Adjustment Account
- ✓ ADJUSTMENT entries post to Manual Journal as JV entries

---

### 3. WITHDRAWAL (DR) - Regular or Adjustment

#### Example: Withdrawal with CASH Payment Method

```
Transaction:
- Account: Member Savings Account #123
- Transaction Type: DR (Withdrawal)
- Amount: 20,000
- Payment Method: CASH

General Ledger Entries:
┌────────────────────────────────────────────────────────┐
│ Debit:   Savings Liability Account       20,000.00     │
│ Credit:  Cash Account                    20,000.00     │
├────────────────────────────────────────────────────────┤
│ Journal: Savings Journal (9)                           │
│ Description: Savings Withdrawal - Member Name          │
│              (Account: 123, Receipt: XXXX,             │
│               Payment Method: CASH)                    │
└────────────────────────────────────────────────────────┘
```

#### Example: Withdrawal with ADJUSTMENT Payment Method

```
Transaction:
- Account: Member Savings Account #123
- Transaction Type: DR (Withdrawal)
- Amount: 20,000
- Payment Method: ADJUSTMENT

General Ledger Entries:
┌────────────────────────────────────────────────────────┐
│ Debit:   Savings Liability Account       20,000.00     │
│ Credit:  Equity/Adjustment Account       20,000.00     │
├────────────────────────────────────────────────────────┤
│ Journal: Manual Journal (5) - JV Entry                 │
│ Description: [JV] Savings Withdrawal - Member Name     │
│              (Account: 123, Receipt: XXXX)             │
└────────────────────────────────────────────────────────┘
```

**Key Points:**
- ✓ CASH/CHEQUE/BANK = Credit goes to Cash/Bank Account (Asset)
- ✓ ADJUSTMENT = Credit goes to Equity/Adjustment Account
- ✓ Insufficient balance check applies to all payment methods

---

## Summary Matrix

| Trans Type | Payment Method | Debit Account | Credit Account | Journal |
|---|---|---|---|---|
| **INT** | Any (incl. ADJUSTMENT) | Interest Expense | Savings Liability | Manual (5) if ADJUSTMENT, Savings (9) otherwise |
| **CR** | CASH/CHEQUE/BANK | Cash/Bank Account | Savings Liability | Savings Journal (9) |
| **CR** | ADJUSTMENT | Equity/Adjustment | Savings Liability | Manual Journal (5) |
| **CR** | OTHERS | Suspense/Misc Account | Savings Liability | Savings Journal (9) |
| **DR** | CASH/CHEQUE/BANK | Savings Liability | Cash/Bank Account | Savings Journal (9) |
| **DR** | ADJUSTMENT | Savings Liability | Equity/Adjustment | Manual Journal (5) |
| **DR** | OTHERS | Savings Liability | Suspense/Misc Account | Savings Journal (9) |

---

## Special Handling: ADJUSTMENT Payment Method

### What is ADJUSTMENT?

The ADJUSTMENT payment method represents **Journal Voucher (JV)** entries - direct GL adjustments that don't involve physical cash or bank transactions.

### When to Use ADJUSTMENT:

1. **Beginning Balance Adjustments** - When setting up opening balances for savings accounts
2. **GL Corrections** - Correcting previous accounting errors (not recommended for interest)
3. **Inter-Account Adjustments** - Internal movements that don't involve cash
4. **Equity Transfers** - Adjustments from retained earnings or other equity accounts

### Characteristics of ADJUSTMENT Entries:

✓ **Journal:** Always posted to Manual Journal (ID: 5) instead of Savings Journal  
✓ **Account Lookup:** System searches for accounts with names containing:
   - "Adjustment"
   - "Opening Balance"
   - "Beginning Balance"
   - "Equity"
   - "Retained Earnings"  
✓ **Description Prefix:** All entries marked with `[JV]` prefix  
✓ **Payment Method:** Not shown in GL description (since it's a direct ledger entry)  
✓ **Audit Trail:** Logged separately as JV entries for tracking  

### Exception: Interest Transactions with ADJUSTMENT

**Important:** Even when ADJUSTMENT is selected as payment method for Interest transactions (INT), the system will:
- Still use **Interest Expense Account** (not Adjustment/Equity account)
- Post to **Manual Journal** (as JV entry)
- Mark with **[JV]** prefix

This ensures proper accounting for interest expense regardless of how it's being recorded.

---

## Payment Method Variations

The system handles various payment method naming patterns:

- **"ADJUSTMENT"** → Uses Adjustment/Equity account
- **"OTHER - ADJUSTMENT"** → Treated as ADJUSTMENT (Adjustment account takes precedence)
- **"OTHERS - ADJUSTMENT"** → Treated as ADJUSTMENT
- **"OTHER"** or **"OTHERS"** → Uses Suspense/Miscellaneous account (not Adjustment)

Priority: If payment method contains "ADJUSTMENT", it's treated as ADJUSTMENT first.

---

## Required Chart of Accounts Setup

For the system to work properly, ensure these accounts exist:

### 1. Interest Expense Account (Required for INT transactions)
- **Account Type:** Expense (Type 50 or 70000)
- **Account Name:** Must contain the word "Interest"
- **Example:** "Interest Expense on Savings" or "Savings Interest"

### 2. Savings Liability Account (Required for all transactions)
- **Configured in:** Savings Account Type setup (`account_setup` field)
- **Account Type:** Liability (Type 2 or 20000)
- **Example:** "Members Savings Deposits" or "Savings Liability"

### 3. Adjustment/Equity Account (Required for ADJUSTMENT payment method)
- **Account Type:** Equity (Type 30 or 40) or Asset (Type 1)
- **Account Name:** Must contain one of:
  - "Adjustment"
  - "Opening Balance"
  - "Beginning Balance"
  - "Equity"
  - "Retained Earnings"
- **Example:** "Opening Balance Equity" or "GL Adjustments"

### 4. Cash/Bank Accounts (Required for CASH/CHEQUE/BANK payment methods)
- **Account Type:** Asset (Type 1 or 10000)
- **Account Name:** Contains "Cash" or "Bank"
- **Example:** "Cash in Hand" or "Bank - Current Account"

### 5. Suspense Account (Optional for OTHERS payment method)
- **Account Type:** Asset (Type 1) or Liability (Type 2)
- **Account Name:** Contains "Suspense", "Miscellaneous", "Other", "Pending", or "Clearing"
- **Example:** "Suspense Account" or "Miscellaneous Receipts"
- **Fallback:** If not found, system uses Cash account

---

## How to Use the Saving → Credit/Debit Form

### Step-by-Step Process:

1. **Navigate:** Go to Saving → Credit/Debit
2. **Select Date:** Choose transaction/posting date
3. **Account Number:** Enter or search for member's savings account
4. **Transaction Type:** Select from dropdown:
   - CR - Deposit
   - DR - Withdrawal
   - INT - Interest
5. **Amount:** Enter transaction amount
6. **Payment Method:** Select from dropdown:
   - CASH
   - CHEQUE (enter cheque number if selected)
   - BANK DEPOSIT
   - OTHERS
   - **ADJUSTMENT** (for JV entries)
7. **Customer Name:** Auto-fills from member record
8. **Comment:** Optional description
9. **Click:** Record button

### Result:
- ✓ Transaction recorded in savings_transaction table
- ✓ Member account balance updated
- ✓ Receipt number generated
- ✓ GL entries automatically posted
- ✓ Redirect to receipt view

---

## Technical Implementation Details

### Database Tables Involved:

1. **savings_transaction** - Transaction records
2. **members_account** - Account balances
3. **general_ledger_entry** - GL entry headers
4. **general_ledger** - GL line items (debit/credit)
5. **paymentmenthod** - Payment method master data
6. **account_chart** - Chart of accounts

### Functions Involved:

**Controller:** `application/controllers/saving.php`
- `credit_debit()` - Form display and validation

**Model:** `application/models/finance_model.php`
- `add_saving_transaction()` - Routes transaction by type
- `credit()` - Processes deposits and interest
- `debit()` - Processes withdrawals
- `post_savings_to_gl()` - Creates GL entries
- `get_interest_expense_account_for_savings()` - Finds interest expense account
- `get_cash_account_for_savings()` - Finds cash/bank/adjustment account based on payment method

### Automatic GL Posting:

The system automatically posts to GL for:
- ✓ Account Opening
- ✓ Beginning Balance entries
- ✓ Normal Deposits (CR)
- ✓ Interest (INT)
- ✓ Normal Withdrawals (DR)

---

## Journal Selection Logic

```
IF payment_method contains "ADJUSTMENT":
    Journal ID = 5 (Manual Journal - JV Entry)
    Description prefix = "[JV]"
ELSE:
    IF Journal ID 9 (Savings Journal) exists:
        Journal ID = 9
    ELSE:
        Journal ID = 5 (Manual Journal - Fallback)
    Description includes payment method
```

---

## Best Practices

### ✓ DO:
- Use ADJUSTMENT for beginning balance entries
- Use ADJUSTMENT for GL corrections (non-interest)
- Use proper account setup in savings account types
- Verify interest expense account exists before processing interest
- Use descriptive comments for adjustment entries

### ✗ DON'T:
- Don't use ADJUSTMENT for regular cash/bank transactions
- Don't mix payment methods (if it's cash, select CASH)
- Don't use ADJUSTMENT to avoid interest expense (system will still use interest expense account)
- Don't bypass the form for GL entries (use the form for proper audit trail)

---

## Troubleshooting

### "Debit account not found" Error

**Cause:** System cannot find appropriate GL account for payment method

**Solutions:**
- For ADJUSTMENT: Create an Equity/Adjustment account with "Adjustment" or "Equity" in the name
- For CASH: Ensure a Cash account exists in chart of accounts
- For CHEQUE/BANK: Ensure a Bank account exists
- For OTHERS: Create a Suspense or Miscellaneous account

### "Interest expense account not found" Error

**Cause:** No expense account with "Interest" in the name

**Solutions:**
- Create an expense account (type 50 or 70000)
- Include "Interest" in the account name
- Example: "Interest Expense on Savings"

### "Savings liability account not found" Error

**Cause:** Savings account type not configured with account_setup value

**Solutions:**
- Go to Savings Account Type setup
- Set the `account_setup` field to a liability account code
- Ensure the liability account exists in chart of accounts

---

## Configuration Summary

### Payment Method Setup (paymentmenthod table):

```
ID: 7
Name: ADJUSTMENT
Description: Journal Voucher / GL Adjustment Entry
GL Account Code: (null - dynamically determined)
Status: Active (1)
```

### Account Requirements:

| Account Purpose | Account Type | Name Pattern | Required |
|---|---|---|---|
| Interest Expense | 50, 70000 | Contains "Interest" | Yes (for INT) |
| Savings Liability | 2, 20000 | Any | Yes (all trans) |
| Adjustment/Equity | 30, 40, 1 | Contains "Adjustment", "Equity", etc. | Yes (for ADJUSTMENT) |
| Cash | 1, 10000 | Contains "Cash" | Yes (for CASH) |
| Bank | 1, 10000 | Contains "Bank" | Yes (for CHEQUE/BANK) |
| Suspense | 1, 2 | Contains "Suspense", "Other", etc. | Optional (for OTHERS) |

---

## Audit and Reporting

### Viewing JV Entries:

JV entries (ADJUSTMENT payment method) can be identified by:
- Journal ID = 5 (Manual Journal)
- Description starts with `[JV]`
- Payment method field = "ADJUSTMENT" in savings_transaction table
- Logged in application logs with "Creating Journal Voucher (JV) entry" message

### Reports:

Filter transactions by:
- Payment method = "ADJUSTMENT" to see all JV entries
- Transaction type = "INT" to see all interest postings
- Journal ID = 5 to see all manual journal entries

---

## Version History

| Date | Version | Changes |
|---|---|---|
| March 4, 2026 | 1.0 | Initial documentation - ADJUSTMENT payment method implementation |

---

## Contact / Support

For questions or issues with savings accounting entries, contact your system administrator or development team.

---

**End of Document**
