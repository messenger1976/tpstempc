# Loan Disbursement – Payment Method & Accounting Entries (Option A + B)

## Overview

Loan disbursement now uses a **dedicated entry screen** (like Cash Disbursement) with:

- **Option A:** **Payment method** dropdown – the **credit account** (source of funds) is taken from the selected payment method, not hardcoded.
- **Option B:** **Editable accounting lines** – you can add, edit, or remove lines (Account, Debit, Credit). Default two lines: Debit = Loan principal account, Credit = account from selected payment method. Debits must equal credits.

## Setup

### 1. Run the SQL migration

Run the script once to add the new column and table:

```sql
-- From file: sql/loan_disbursement_gl_module.sql
```

Or execute the file (e.g. in phpMyAdmin or MySQL client):

- `sql/loan_disbursement_gl_module.sql`

This will:

- Add `payment_method` to `loan_contract_disburse`.
- Create `loan_disbursement_gl_items` for storing accounting lines per disbursement.

### 2. Configure payment methods (GL account)

So that the **credit** account is correct when disbursing:

1. Go to **Settings → Payment Method** (or wherever payment methods are configured).
2. For each method (Cash, Cheque, Bank Deposit, etc.), set the **GL Account Code** to the correct cash/bank account from your chart of accounts.

If no GL account is set, the system falls back to searching the chart of accounts by payment method name (e.g. "Cash", "Bank").

## How to use

1. Go to **Loan → Loan Disbursement** (list of loans waiting to be disbursed).
2. Click **Disburse** for a loan.
3. You are taken to the **Loan Disbursement** entry form:
   - **Disbursement date** (required).
   - **Payment method** (required) – selects the source of funds; the credit account is derived from this.
   - **Comment** (required).
   - **Accounting entries** – table with Account, Description, Debit, Credit. Two default lines are pre-filled:
     - Debit: Loan principal account (from loan product), amount = loan amount.
     - Credit: Account from the selected payment method, same amount.
4. You can:
   - Change the payment method – the second line’s account updates from the payment method’s GL account.
   - Edit amounts or accounts, add rows, or remove rows. **Totals** and **balance** (debits = credits) are checked.
5. Click **Save**. The system will:
   - Create the disbursement record (with payment method).
   - Mark the loan as disbursed and create the repayment schedule.
   - Save the accounting lines to `loan_disbursement_gl_items`.
   - Post all lines to the **General Ledger** (journal ID 4).

## Files changed/added

- `sql/loan_disbursement_gl_module.sql` – migration.
- `application/models/payment_method_config_model.php` – `get_payment_method_by_id()`.
- `application/models/loan_model.php` – `get_credit_account_for_payment_method()`, `save_disbursement_gl_items()`, `get_disbursement_gl_items()`, `post_loan_disbursement_to_gl()`.
- `application/controllers/loan.php` – `loan_disburse_entry()`.
- `application/views/loan/loan_disburse_entry.php` – new form with payment method and line items.
- `application/views/loan/loan_wait_disburse.php` – Disburse link now points to `loan_disburse_entry` instead of `loan_disburse_action`.
- `application/language/english/loan_lang.php` and `swahili/loan_lang.php` – new lang keys.

The old **loan_disburse_action** (simple date + comment form with hardcoded bank account) is still in the codebase but no longer used from the menu. You can keep it for backward compatibility or remove it later.
