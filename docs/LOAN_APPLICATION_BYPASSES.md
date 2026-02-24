# Loan Application Security Bypasses (Temporary)

## Overview

This document describes the temporary bypasses applied to the loan application validation logic. These bypasses allow members to submit loan applications without meeting the standard eligibility requirements for shares, contribution, savings, and maximum loan amount.

**Status:** TEMPORARY — These bypasses should be re-enabled when normal eligibility rules need to be enforced.

**Affected Page:** Loan Application (`/en/loan/loan_application`)

---

## Bypassed Validations

| Check | Original Behavior | Bypassed Behavior |
|-------|-------------------|-------------------|
| **Share requirement** | Member must have minimum shares (`loan_security_share_min`) | Always passes |
| **Maximum loan amount** | Loan limited by (contribution × times) minus existing loan principal | No limit enforced |
| **Contribution times** | Loan amount must be ≤ contribution balance × `loan_security_contribution_times` | Always passes |
| **Minimum contribution** | Member must have minimum contribution balance (`loan_security_contribution_min`) | Always passes |

**Note:** The savings requirement (`pass_saving_condition`) is **not** bypassed and still applies.

---

## Modified Files

### application/controllers/loan.php

All changes are in the Loan controller. Four functions were modified to return `TRUE` unconditionally:

| Function | Location (approx.) | Purpose |
|----------|--------------------|---------|
| `pass_share_condition()` | ~line 269 | Validates minimum number of shares |
| `maximum_loan_allowed()` | ~line 306 | Validates max loan based on contribution and existing loans |
| `maximum_contributions_times()` | ~line 330 | Validates loan amount vs contribution balance × times |
| `pass_contribution_condition()` | ~line 352 | Validates minimum contribution balance |

---

## Restoring Original Validation

To re-enable the security checks, restore the original logic in each function by:

1. Removing the early `return TRUE;` line.
2. Uncommenting the original block of code.
3. Removing the `// TEMPORARILY DISABLED` comment.

### Example: Restoring `pass_share_condition`

**Current (bypassed):**
```php
function pass_share_condition($product, $share) {
    // TEMPORARILY DISABLED - share requirement check removed for loan application
    return TRUE;
    /*
    if ($product->loan_security_share_min > 0) {
        ...
    }
    return TRUE;
    */
}
```

**Restored:**
```php
function pass_share_condition($product, $share) {
    if ($product->loan_security_share_min > 0) {
        if ($share) {
            if ($share->totalshare >= $product->loan_security_share_min) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
    return TRUE;
}
```

---

## Original Validation Logic Summary

### 1. pass_share_condition
- If `loan_security_share_min > 0`, member must have at least that many shares.
- If member has no share record, fails.
- Otherwise passes.

### 2. maximum_loan_allowed
- Computes: `allowed = (contribution_balance × loan_security_contribution_times) - open_loan_principal`
- Loan amount must be ≤ `allowed`.
- Uses approved loans (`approval=4`) to calculate open principal.

### 3. maximum_contributions_times
- Computes: `total_amount = contribution_balance × loan_security_contribution_times`
- If `total_amount == 0`, passes.
- Otherwise, loan amount must be ≤ `total_amount`.
- Member must have a contribution record.

### 4. pass_contribution_condition
- If `loan_security_contribution_min > 0`, member’s contribution balance must be ≥ that minimum.
- If member has no contribution record, fails.
- Otherwise passes.

---

## Date Applied

February 2026

---

## Related

- **Loan Product Settings:** `loan_product` table — `loan_security_share_min`, `loan_security_contribution_min`, `loan_security_contribution_times`, `loan_security_saving_minimum`
- **Share Settings:** `share_setting` table
- **Contribution Balance:** Computed from contribution model
- **Loan Application View:** `application/views/loan/loan_application_step1.php`
