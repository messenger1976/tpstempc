# Loan: One-Third Monthly Income Validation

## Overview

The system enforces a rule that **total contribution and loan repayments must not exceed one-third of the member’s monthly income**. This applies when creating a new loan and when **saving changes on the Loan Editing page** (`/en/loan/loan_editing/{loanid}`).

**Message shown when the check fails:**  
*"Total contribution and loan repayment exceed one third of your monthly income"*

---

## Where It Runs

| Page / action | Controller | When |
|---------------|------------|------|
| **Loan application** (new loan) | `loan.php` → `loan_application()` | On submit; check is currently **commented out** in code (see `loan_application` around line 238). |
| **Loan editing** (save) | `loan.php` → `loan_editing()` | **TEMPORARILY DISABLED** (as of Feb 2026). Check is commented out in both the main validation `if` and the warning `else if`; see comments in `loan_editing()`. |

---

## How the Rule Works

**Function:** `pass_monthly_income($monthly_income, $pid, $newinstall = 0, $exclude_lid = null)`  
**File:** `application/controllers/loan.php`

1. **Monthly contribution**  
   From `contribution_settings` for the member (PID + PIN), or from `contribution_global` if no member-specific setting. If neither exists, contribution is treated as 0.

2. **Open loan installments**  
   Sum of `installment_amount` for all **open** loans:  
   `loan_contract` where `PID = $pid` and `disburse = 1` and `status = 4`.

3. **New installment**  
   The installment amount of the loan being applied for or edited (`$newinstall`).

4. **Formula**
   - `total_deductions = (sum of open loan installments) + monthly_contribution + newinstall`
   - `one_third_income = (1/3) × monthly_income`
   - **Pass:** `total_deductions <= one_third_income`  
   **Fail:** otherwise → user sees the error message above.

---

## Loan Editing: Double-Counting Fix

### Problem

When **editing** an existing loan and saving:

- The loan being edited is already in the database as an open loan (`disburse=1`, `status=4`).
- The code was:
  - Summing **all** open loans (including the one being edited) into `$repay_installment`, and
  - Adding the **new** installment from the form as `$newinstall`.
- So the **same loan’s installment was counted twice**, making `total_deductions` too high and often triggering the “exceed one third” message even when the member was within the rule.

### Solution

- `pass_monthly_income()` was given an optional 4th parameter: **`$exclude_lid`**.
- When saving from **Loan Editing**, the controller calls:
  - `pass_monthly_income($createloan['monthly_income'], $pid, $installment_amount, $LID)`
  where `$LID` is the loan being edited.
- Inside `pass_monthly_income()`, when `$exclude_lid` is provided, the open-loans query **excludes** that LID:
  - Only **other** open loans are summed into `$repay_installment`.
  - The current loan’s installment is counted **once**, via `$newinstall`.

So on edit:

- **Total deductions** = (other open loans’ installments) + monthly contribution + (this loan’s new installment).
- The one-third check is correct and no longer double-counts the loan being edited.

### Code References

| Location | Change |
|----------|--------|
| `pass_monthly_income()` | Added `$exclude_lid = null`; open-loans query excludes `LID = $exclude_lid` when set. |
| `loan_editing()` — main validation | Call uses 4th argument: `pass_monthly_income(..., $installment_amount, $LID)`. |
| `loan_editing()` — warning branch | Same: `pass_monthly_income(..., $installment_amount, $LID)`. |

**New loan application** still calls `pass_monthly_income(..., $installment_amount)` without the 4th argument, so behavior for new loans is unchanged.

---

## Temporarily Disabled (Loan Editing)

The one-third check is currently **turned off** for the Loan Editing save. Affected code in `application/controllers/loan.php`:

1. **Main validation (around line 454)**  
   - Comment above the `if`: `// TEMPORARILY DISABLED: one-third monthly income check — remove comment to re-enable pass_monthly_income()`  
   - The condition no longer includes `&& $this->pass_monthly_income(...) == TRUE`; that call is on the next line as a comment.

2. **Warning branch (around lines 479–482)**  
   - The `else if` that calls `pass_monthly_income()` and sets `loan_contribution_exceed_one_third` is wrapped in `/* ... */` with a comment: `TEMPORARILY DISABLED: one-third monthly income check — uncomment to re-enable`.

To **re-enable**: uncomment the `pass_monthly_income()` call in the main `if`, add it back into the condition, and uncomment the `else if` block in the warning branch.

## Additional Hardening

- If `contribution_global` returns no row, `$contr_setup` can be `null`. The code now uses  
  `$monthly_contribution = $contr_setup ? $contr_setup->amount : 0`  
  in that branch to avoid accessing a property on null.

---

## Related

- **Language string:** `application/language/english/loan_lang.php` → `loan_contribution_exceed_one_third`
- **Loan application bypasses:** `docs/LOAN_APPLICATION_BYPASSES.md` (other validations; one-third check is separate and still active on edit)

---

## Date

February 2026
