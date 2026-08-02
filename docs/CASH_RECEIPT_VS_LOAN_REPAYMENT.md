# Cash Receipt vs Loan Repayment

Guidance on when to use **Cash Receipt** (Finance) versus **Loan Repayment** (Loan Management), and why loan collections must go through Loan Repayment.

---

## 1. Short answer

**Do not use Cash Receipt to record member loan repayments.**

Use:

**Loan Management → Loan Repayment**

Cash Receipt is for other cash-in transactions. It does **not** update the loan repayment schedule, loan repayment records, or close the loan when fully paid.

---

## 2. Why Loan Repayment is required

| Capability | Loan Repayment | Cash Receipt |
|------------|----------------|--------------|
| Updates repayment schedule (`status` 0 → 1 / 2) | Yes | No |
| Creates `loan_repayment_receipt` / `loan_contract_repayment` | Yes | No |
| Appears on **Loan Ledger** as repayment | Yes | No |
| Applies overpayment / carry (`loan_balance_carry`) | Yes | No |
| Can set loan to **Closed (5)** when fully paid | Yes | No |
| Posts correct loan principal / interest / penalty GL | Yes (via loan product accounts) | Only if you build manual lines (easy to get wrong) |
| Posts cash / bank to GL | Yes | Yes |

If staff only enter a **Cash Receipt** for a loan payment:

- Cash may increase in the books, **but**
- The installment stays **open** on the schedule  
- The loan may still show as collectible / aging  
- Loan Ledger will not show a proper repayment  
- Books and loan operations **will not reconcile**

---

## 3. What each module is for

### Loan Repayment (correct for loans)

**Menu:** Loan Management → Loan Repayment  

**Use for:**

- Monthly amortizations  
- Partial payments (carry to next installment)  
- Advance / overpayments  
- Full payoff  
- Any collection against an Accepted + disbursed loan (including activated beginning-balance loans)

**Effects:**

1. Creates loan repayment receipt (uses shared `receipt_no` series with Cash Receipt)  
2. Applies amount to open schedule installments  
3. Posts GL (cash/bank debit; principal / interest / penalty credits per product)  
4. Updates Loan Ledger  
5. Closes loan when no open installments remain  

See also: `docs/LOAN_REPAYMENT_SCHEDULE_RECONCILIATION.md`

### Cash Receipt (Finance)

**Menu:** Finance → Cash Receipt List  

**Use for:**

- Miscellaneous cash collections  
- Fees / donations / other income not tied to loan installment collection  
- General cash-in with manual debit/credit lines  

**Does not:**

- Close schedule installments  
- Create loan repayment sub-ledger rows  
- Replace Loan Repayment  

Cash Receipt posts its own journal/GL from the line items you enter. That is independent of the loan schedule engine.

---

## 4. Shared receipt numbers (only link)

Loan Repayment and Cash Receipt share the same **receipt number** series (`receipt_no` / `get_next_shared_receipt_no()`).

That keeps numbering continuous across the coop (e.g. CR-style numbers used on both screens).  

**Sharing a number series does not mean Cash Receipt processes loans.** They remain separate modules.

---

## 5. Related tools that also are not Loan Repayment

| Screen | Role re: loans |
|--------|----------------|
| **Journal Entry** (even with Loan link) | GL tag / adjustment only — does **not** update schedule or loan repayment receipts |
| **Cash Receipt** | Cash-in journal only — does **not** update schedule |
| **Cash Disbursement** | Cash-out — not for receiving loan payments |
| **Loan Repayment** | Operational loan collection — **use this** |

From journal sub-ledger guidance: linked journal lines do not create loan repayment receipts or update repayment schedules. Same principle applies to Cash Receipt.

---

## 6. Correct cashier workflow

1. Member pays loan installment (cash / cheque / transfer).  
2. Open **Loan → Loan Repayment**.  
3. Select the loan → enter receipt no., date, amount, payment method.  
4. Save — schedule and ledger update.  
5. Print / give loan repayment receipt to member.  

Optional: review **Repayment Schedule** and **Loan Ledger** to confirm.

Use **Cash Receipt** only if the money is **not** a loan installment collection (or not meant to apply to the loan schedule).

---

## 7. If Cash Receipt was used by mistake

Typical symptoms:

- Cash is in GL from Cash Receipt  
- Schedule installments still open  
- Member appears to still owe that installment  

**Corrective approach (ops):**

1. Do **not** leave it as-is if the intent was a loan payment.  
2. Prefer voiding/reversing the mistaken Cash Receipt (per your Finance void process) if it should not stand alone.  
3. Re-enter the collection under **Loan Repayment** so schedule + loan ledger update.  
4. Avoid “fixing” by posting another Cash Receipt plus a separate Loan Repayment for the same cash — that can double-count cash unless the first receipt is voided.

Exact void steps depend on whether the Cash Receipt was posted and your journal review/void procedures.

---

## 8. Activated beginning balances & offset

Same rule:

- Collect on activated BB loans via **Loan Repayment**  
- Offset/reloan settlement of an old loan happens at **Loan Disbursement** (offset panel), not via Cash Receipt  

See: `docs/LOAN_BEGINNING_BALANCE_ACTIVATE_AND_OFFSET_RELOAN.md`

---

## 9. Quick reference

| Question | Answer |
|----------|--------|
| Can I receive loan repayment in Cash Receipt? | **No** (not for schedule/loan sub-ledger) |
| Where do I receive loan repayment? | **Loan → Loan Repayment** |
| Do they share receipt numbers? | **Yes** (number series only) |
| Will Cash Receipt close an installment? | **No** |
| Will Loan Repayment post to GL? | **Yes** |

---

## Related docs

- `docs/LOAN_REPAYMENT_SCHEDULE_RECONCILIATION.md` — schedule sync, overpayments, carry  
- `docs/LOAN_BEGINNING_BALANCE_ACTIVATE_AND_OFFSET_RELOAN.md` — BB activate & offset reloan  
- `docs/CASH_RECEIPT_MODULE_README.md` — Cash Receipt module overview  
- `docs/JOURNAL_ENTRY_SUBLEDGER_LINKS.md` — journal links do not replace Loan Repayment  
- `docs/HOW_TO_USE_THE_SYSTEM.md` — high-level menu usage  
