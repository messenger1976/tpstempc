# Loan Beginning Balances, Activate as Loan & Offset Reloan

Operational guide for TAPSTEMCO Loan Management: bringing legacy loan balances into the system, collecting on them, closing them, and offsetting old loans when a member reloans.

---

## 1. Concepts

| Term | Meaning |
|------|---------|
| **Beginning Balance (BB)** | Opening amount a member still owes from before (or outside) this system. Used for migration / fiscal opening. |
| **Post to GL** | Records the opening receivable in the General Ledger only. Does **not** create a live loan for repayment. |
| **Activate as Loan** | Converts a posted BB into a real `loan_contract` (Accepted + disbursed) with schedule and loan ledger. |
| **Offset / Reloan** | At disbursement of a **new** loan, settle one or more existing active loans from the new proceeds. Member receives **net cash** only. |
| **Closed** | Loan status `5`. No open installments left. Member can apply for a new loan. |

**Important:** Beginning Balances are for **onboarding legacy debt**. New loans after go-live use the normal Apply → Evaluate → Approve → Disburse path. Do not use BB for routine new lending.

---

## 2. Menu & Permission

**Menu:** Loan Management → **Loan Beginning Balances**

**Permission:** `Loan_beginning_balances` (Module 5 – Loan Management)

- Assign under **Manage Users → Privileges** for the user’s group.
- Helper script (optional): `add_loan_beginning_balance_permission.php`
- Note: Dashboard may use `menu.php` while other pages use `newmenu.php`; both include this item when permission is granted.

**Direct URL (example):**  
`/english/loan/loan_beginning_balance_list`

**Offset / Reloan** appears on the **Loan Disbursement** screen (no separate menu). Users need disbursement access (`Disburse_loan`).

---

## 3. Loan Beginning Balances – Day-to-Day Flow

```text
Create BB  →  Post to General Ledger  →  Activate as Loan
                                              ↓
                                    Collect repayments
                                              ↓
                                    Loan fully paid → Closed
                                              ↓
                         New Loan Application  or  Offset Reloan at Disbursement
```

### 3.1 Create

1. Open **Loan Beginning Balances**.
2. Select **Fiscal Year** (and loan product filter if needed).
3. Click **Create Loan Beginning Balance**.
4. Enter:
   - Member ID  
   - Loan product  
   - Principal (required for later activation)  
   - Interest / penalty (optional)  
   - Term and/or Monthly Amortization (needed for schedule)  
   - Optional: Loan ID, disbursement date, last date paid, description  
5. Save.

**Rules:** One BB per fiscal year + member + loan product. Editable/deletable only while **Not Posted**.

### 3.2 Post to General Ledger

1. On a **Not Posted** row, click **Post to General Ledger**.
2. Confirm.

This posts principal / interest / penalty to the product’s GL accounts (opening receivable). Status becomes **Posted**.

- Posted BBs cannot be edited or deleted.
- Posted BBs can be **Void**ed (reversing GL) **only if not yet Activated**.

### 3.3 Activate as Loan

1. On a **Posted** (not yet activated) row, click **Activate as Loan**.
2. Confirm.

The system creates:

| Record | Purpose |
|--------|---------|
| `loan_contract` | Status **Accepted (4)**, `disburse = 1` |
| `loan_contract_disburse` | Opening line only — **no cash out, no second GL post** |
| Repayment schedule | Remaining installments from principal, term, amort |
| Link | BB `loan_id` set to the new LID |

After activation:

- Status on BB list shows **Activated** with link to Details / Ledger.
- Loan appears under **Loan List** as **Accepted** (not as Beginning Balance).
- You can collect via **Loan Repayment**, view schedule and **Loan Ledger**.
- Ledger opening line shows as **Beginning Balance (Opening)**.

**Activation rules:**

- Must be **posted** first.  
- Principal must be &gt; 0.  
- Term and/or monthly amort required (or derived from product).  
- Cannot activate twice.  
- Cannot void GL after activation.

---

## 4. Closing a Loan (so the member can borrow again)

A loan becomes **Closed** when it is fully paid:

1. Go to **Loan Repayment**.
2. Pay until no open installments remain.
3. System sets status to **Closed (5)**.

There is no separate “Close” button on Beginning Balances. Closing is done through repayment on the **activated** (or normally disbursed) loan.

Until Closed, the member still has an active Accepted loan.

---

## 5. New Loan / Reloan (without offset)

After the old loan is **Closed**:

1. **New Loan Application** for the member.  
2. Evaluate → Approve → Disburse → Schedule → Repay.

This creates a **new LID**. Do **not** create another Beginning Balance for a fresh loan.

---

## 6. Offset Existing Loan(s) / Reloan (at Disbursement)

Use this when the member still has an **active unpaid loan** and the new loan should **pay it off**, with only the difference paid in cash.

### 6.1 Example

```text
New loan amount          50,000
Less: old outstanding   -12,000   ← offset
────────────────────────────────
Cash to member           38,000
```

### 6.2 Steps

1. Create / evaluate / **approve** the **new** loan (status Accepted, not yet disbursed).  
2. Open **Loan Disbursement** → **Disburse** for that loan.  
3. If the member has other **Accepted + disbursed** loans with outstanding balance, the panel **Offset Existing Loan(s) / Reloan** appears.  
4. Tick the old loan(s) to settle.  
5. Accounting lines update automatically:
   - **Debit** new loan principal (full new amount)  
   - **Credit** old loan principal / interest accounts (offset)  
   - **Credit** cash/bank for **net proceeds** only  
6. Review/edit lines if needed (debits must equal credits).  
7. Save disbursement.

### 6.3 What the system does

| Action | Result |
|--------|--------|
| Old loan(s) | Schedule settled; status **Closed**; repayment receipt tagged `OFFSET-…` |
| New loan | Marked disbursed; full repayment schedule created |
| Cash | Only net amount in the disbursement journal |
| GL | No double cash posting — offset is part of the disbursement entry |
| Link | New loan stores `offset_loans` (comma-separated LIDs closed) |
| Comment | Disbursement comment notes which loans were offset |

### 6.4 Rules

- Only **same member**, **status Accepted (4)**, **already disbursed**, with outstanding balance.  
- Total offset **cannot exceed** the new loan principal amount.  
- If no other active loans exist, the offset panel is hidden (normal disbursement).  
- You may still edit GL lines before save; keep them balanced.

### 6.5 Typical GL pattern (auto-built)

```text
Dr  New loan principal account     New loan amount
Cr  Old loan principal account     Old principal outstanding
Cr  Old loan interest account      Old interest outstanding (if any)
Cr  Cash / bank (payment method)   Net cash to member
```

---

## 7. Recommended Lifecycle Summary

### Legacy / migration loans

```text
Capture Beginning Balance
  → Post to General Ledger
  → Activate as Loan
  → Collect repayments (or later Offset at new disbursement)
  → Closed when paid in full
```

### Ongoing lending

```text
New Loan Application → Evaluate → Approve → Disburse
  → (Optional) Offset existing loan(s) on Disbursement screen
  → Repay → Closed
```

---

## 8. Status Quick Reference

| Status | Code | Typical meaning |
|--------|------|-----------------|
| New Loan | 0 | Application |
| Evaluated | 1 | Passed evaluation |
| Rejected | 2 | Rejected |
| Accepted | 4 | Approved; after disburse = active for repayment |
| Closed | 5 | Fully paid / settled (including by offset) |
| Beginning Balance (list filter) | `bb` | Display filter for non-activated BB rows on Loan List |

On Loan Beginning Balances list:

| Label | Meaning |
|-------|---------|
| Not Posted | Draft BB |
| Posted | On GL; ready to Activate |
| Activated | Live loan exists; use Loan Repayment / Ledger |

---

## 9. Technical Notes (for support)

| Area | Location |
|------|----------|
| BB list / CRUD / post / void / activate | `application/controllers/loan.php` |
| Activate + outstanding + offset settle | `application/models/loan_model.php` |
| Disbursement + offset UI | `application/views/loan/loan_disburse_entry.php` |
| BB list UI | `application/views/loan/loan_beginning_balance_list.php` |
| Menus | `application/views/menu.php`, `application/views/newmenu.php` |
| Language | `application/language/english/loan_lang.php` |
| BB table | `loan_beginning_balances` |
| Offset link on new loan | `loan_contract.offset_loans` (auto-added if missing) |

**Activate** does **not** post disbursement cash GL (opening was already posted from BB).  
**Offset settle** does **not** post a second cash repayment GL for the old loan; settlement is in the **new** loan’s disbursement journal.

---

## 10. Troubleshooting

| Issue | Check |
|-------|--------|
| Menu item missing on non-Dashboard pages | Permission `Loan_beginning_balances`; confirm `newmenu.php` has the item; hard-refresh |
| Cannot Post | Row already posted |
| Cannot Activate | Must post first; principal &gt; 0; term/amort present |
| Cannot Void | Already activated as a loan |
| Offset panel missing | Member has no other Accepted+disbursed loans with balance |
| Offset blocked | Total offset &gt; new loan amount — increase new loan or uncheck some loans |
| Details / Ledger fail on `BB-*` | Activate first so a real LID exists |

---

## Related docs

- `docs/CASH_RECEIPT_VS_LOAN_REPAYMENT.md` — use Loan Repayment (not Cash Receipt) for loan collections  
- `docs/LOAN_REPAYMENT_SCHEDULE_RECONCILIATION.md` — how repayments sync with the schedule; overpayments and carry/remaining balance  
- `docs/LOAN_BEGINNING_BALANCES_MODULE.md` — original BB module overview  
- `docs/LOAN_BEGINNING_BALANCES_IN_LOAN_LIST.md` — BB rows on Loan List  
- `docs/LOAN_DISBURSEMENT_GL_ENTRY.md` — disbursement GL entry design  
