# Loan Repayment vs Repayment Schedule (Reconciliation & Overpayments)

How loan repayments stay in sync with the repayment schedule in TAPSTEMCO, and how amounts **higher than the monthly installment** are treated (advance payment and remaining/carry balance).

---

## 1. Overview

Repayment and the schedule are linked automatically. You do **not** update both by hand.

```text
Repayment Schedule (plan)          Loan Repayment (actual)
─────────────────────────          ───────────────────────
Installment #1  status = 0  ──pay──►  marked status = 1 (Paid)
Installment #2  status = 0  ──pay──►  marked status = 1
...
When no status = 0 left  ──────────►  Loan status = Closed (5)
```

| Schedule `status` | Meaning |
|-------------------|---------|
| **0** | Open / unpaid — next payment is applied here first |
| **1** | Paid by a normal repayment |
| **2** | Settled early (full payoff or **offset reloan**) |

Each payment also creates:

- A row in `loan_contract_repayment` (and a receipt)
- General Ledger entries for the collection
- Updates on the **Loan Ledger** (disbursement/opening + repayments)

---

## 2. Day-to-day reconciliation checklist

1. Open **Loan Repayment Schedule** for the loan (from Loan List).
2. Note open rows (`status = 0`): due date, principal, interest, installment amount.
3. Post payment under **Loan Repayment** (amount, date, receipt).
4. System applies cash **in order** to the next open installment(s).
5. Re-open the schedule: paid rows should match the receipt; remaining open rows = still due.
6. Optionally cross-check **Loan Ledger**: opening/disburse + repayments ≈ amounts collected.

If schedule and ledger disagree after a void, re-check voided receipts — voiding can reopen schedule rows.

---

## 3. When payment is higher than the monthly installment

If the member pays **more than one installment**, the excess is applied forward. It is not ignored and is not left disconnected from the loan.

### 3.1 Available amount to apply

```text
Amount to apply = Payment entered + Previous carry (if any)
```

Previous carry comes from `loan_balance_carry` (unapplied leftover from an earlier payment).

### 3.2 Application order

The system walks **open schedule rows** in installment order:

1. If amount ≥ one installment → pay that row fully → schedule `status = 1` → reduce remaining cash.
2. If still enough for the next installment → pay the next row → and so on.
3. If leftover is **less than** the next full installment → store leftover as **carry** for the next repayment.
4. If amount ≥ **full remaining loan balance** on the current path → **full payoff**: pay current installment, mark remaining open rows `status = 2`, set loan **Closed (5)**.

### 3.3 Example (overpayment, loan still open)

```text
Payment              ₱5,000
Monthly installment  ₱2,000
─────────────────────────────────────────
Pay installment #1   -2,000   → status = 1 (Paid)
Pay installment #2   -2,000   → status = 1 (Paid)
Leftover              1,000   → stored as Remaining / Carry
```

On the **next** repayment:

```text
Next payment ₱1,000 + Carry ₱1,000 = ₱2,000
→ Pays the next open installment in full
```

### 3.4 Summary of cases

| Situation | Treatment |
|-----------|-----------|
| Payment **&gt;** 1 installment, but **not** enough to finish the whole loan | Pays as many full installments as possible in order. Leftover (&lt; next installment) goes to **carry** (`loan_balance_carry`). |
| Payment **≥** remaining loan (installment + remaining principal path used for full payoff) | **Full payoff**: current installment paid; other open rows → `status = 2`; loan **Closed**. |
| Payment **&lt;** 1 installment | Schedule row not marked paid yet; full amount stored as **carry** and used on the next repayment. |

---

## 4. Two different “remaining” ideas

Do not confuse these:

| Term | Meaning |
|------|---------|
| **Schedule / loan remaining** | What the member still **owes** (open installments / unpaid principal & interest). |
| **Carry / remaining after overpayment** (`loan_balance_carry`) | Extra **cash already paid** that is waiting to be applied to the **next** installment(s). |

Also on each repayment line:

| Field | Role |
|-------|------|
| `iliyobaki` | Leftover amount after that application (audit on the repayment row). |
| `loan_balance_carry.balance` | Amount added back into the next payment’s “amount to apply”. |

Overpaying **advances the schedule** (multiple rows can become Paid on one receipt). Any leftover under one installment is held as **credit for next time** — not auto-refunded, and not left orphaned off the loan.

---

## 5. Activated Beginning Balance loans

Same rules after **Activate as Loan**:

- Schedule is built from remaining principal / term / amort.
- Ledger opening may show **Beginning Balance (Opening)** instead of cash disbursement.
- Repayments still close schedule rows `0 → 1` (or full payoff / offset → `2`).

Reconcile: open schedule total ≈ still collectible; ledger payments should match closed installments.

---

## 6. Offset / Reloan (old loan)

When an old loan is offset at new loan disbursement:

- Old open schedule rows → **`status = 2`**
- Old loan → **Closed**
- Receipt tagged `OFFSET-…` (settlement is not member cash against that old loan’s normal repayment cash path)
- Books for the settlement sit mainly in the **new loan’s disbursement** journal

Reconcile old loan: all schedule rows settled (1 or 2), status Closed, OFFSET receipt present.

---

## 7. Practical tips for cashiers / credit staff

1. After an overpayment, open **Repayment Schedule** — expect earlier due installments to show Paid.
2. Expect any amount **below one installment** leftover to sit as carry until the next payment.
3. On the next payment, carry is included automatically in what gets applied.
4. For full settlement in one go, enter an amount large enough to cover remaining open balance (system uses full-payoff path and closes the loan).
5. Use **Loan Ledger** + receipts to confirm cash collected vs opening.

---

## 8. Technical reference (support)

| Item | Location |
|------|----------|
| Repayment apply loop | `application/controllers/loan.php` — `loan_repayment_process` / `loan_repayment_save` |
| Open installments | `loan_model::open_repayment_installment` (`status = 0`) |
| Normal pay → schedule paid | `record_loan_repayment` → schedule `status = 1` |
| Full payoff | `record_loan_repayment_all` → current paid; other open → `status = 2` |
| Carry read/write | `get_previous_remain_balance` / `add_remain_balance` → table `loan_balance_carry` |
| Schedule table | `loan_contract_repayment_schedule` |
| Payment detail | `loan_contract_repayment` (`iliyobaki` = leftover after apply) |

---

## Related docs

- `docs/CASH_RECEIPT_VS_LOAN_REPAYMENT.md` — use Loan Repayment (not Cash Receipt) for loan collections  
- `docs/LOAN_BEGINNING_BALANCE_ACTIVATE_AND_OFFSET_RELOAN.md` — BB activate and offset reloan  
- `docs/LOAN_DISBURSEMENT_GL_ENTRY.md` — disbursement GL  
