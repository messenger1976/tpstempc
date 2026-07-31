# Journal Entry Sub-ledger Links

Manual journal vouchers (Finance → Journal Entry) can optionally link each line to a **Customer (AR)**, **Supplier (AP)**, **Member Loan**, or **CBU Account**. After approval, those links are copied into `general_ledger` so the entry appears on the matching sub-ledger report.

## Reference # (auto)

Format: `JV-{YYYY}{######}` — e.g. `JV-2026000001`

- `JV-` prefix
- 4-digit year from the **entry date**
- 6-digit counter per organization (PIN), resets each year

Assigned automatically on save (read-only on the form).

## Document No. (manual, required)

User-entered source document / voucher number stored in `general_journal_entry.document_no`. Required on create.

## What this does

| Link type | Stored on `general_journal` | Copied to `general_ledger` on post | Sub-ledger side effect |
|-----------|-----------------------------|-------------------------------------|-------------------------|
| Customer  | `customerid`                | `customerid`                        | None (GL tag only) |
| Supplier  | `supplierid`                | `supplierid`                        | None (GL tag only) |
| Loan      | `LID`, `PID`, `member_id`   | `LID`, `PID`, `member_id`           | None (GL tag only) |
| CBU       | `PID`, `member_id`          | `PID`, `member_id`                  | Updates `members_contribution` + inserts `contribution_transaction` |

Also stores `link_type` on the journal line for display (`customer` / `supplier` / `loan` / `cbu`).

### Capital Build Up (CBU) workflow

1. Settings → Contribution Minimum (`/en/setting/contribution_minimum`) defines **Capital Build Up Account** (`contribution_global.capital_build_up_account`).
2. On Journal Entry, when the line **Account** matches that COA, **Link To** gains a **CBU Account** option.
3. Choosing **CBU Account** populates the entity dropdown with members that have a CBU setup (`contribution_settings`).
4. On save/post, the member is stored via `PID` / `member_id` with `link_type = cbu`.
5. On **Approve / Post to GL**, each CBU-linked line also updates the member CBU sub-ledger:
   - **Credit** on CBU COA → `CR` (increases balance)
   - **Debit** on CBU COA → `DR` (decreases balance)
   - `paymethod = JOURNAL`, date = journal entry date, comment includes JV reference when available

## What this does **not** do

- Does **not** create or update `sales_invoice` / `purchase_invoice` balances
- Does **not** create loan repayment receipts or update repayment schedules
- Does **not** replace Loan Repayment, Customer Receive Payment, Supplier Pay Invoice, or day-to-day Capital Build Up payment screens

Use those modules for operational payments. Use linked journal lines for **GL adjustments** that should still show on AR / AP / loan / member CBU sub-ledgers (e.g. write-offs, reclassifications, beginning-balance corrections tagged to a party).

## Install (production)

Run once:

`/install_journal_entry_subledger_links.php`

(or create a journal entry — `ensure_general_journal_link_columns()` will add missing columns).

## Voiding across modules

Reversing-entry void (keep original GL, post offsetting VOID lines) is available for:

| Module | Where to void |
|--------|----------------|
| Manual JE / Cash Receipt / Cash Disbursement | Journal Entry Review / journal view |
| Savings deposit/withdrawal | Savings void transaction |
| CBU beginning balance | CBU Setting List (click Posted → Void) |
| Chart beginning balances | Finance → Beginning Balance list |
| Loan beginning balances | Loan → Beginning Balance list |
| Loan repayment | Loan Ledger → Void on repayment row |
| Loan disbursement | `loan/void_loan_disbursement/{LID}` (blocked if repayments exist) |
| Loan processing fee | Model: `void_loan_processing_fee` |
| Customer payment | Customer payment receipt → Void Payment |
| Supplier payment | `supplier/void_invoice_payment/{receipt}` |
| Member registration fee | Model: `void_member_registration_fee` |
| Shares buy/refund | Share receipt / transaction search → Void (sub-ledger reversing entry; no GL) |

Shares still have **no GL** posting (sub-ledger only). Void creates a reversing `share_transaction` and restores `members_share`.

## Workflow

1. Finance → Journal Entry
2. Enter date (Reference # is auto-assigned), description
3. On each line: Account, **Link To** (optional), entity, description, debit/credit
4. For CBU: select the Capital Build Up COA → Link To → CBU Account → pick member
5. Save → Review & Approve → posts to GL with sub-ledger IDs
6. To void a posted JE: open entry → **Void with Reversing Entry**

## Related modules (preferred for day-to-day)

- Member loan payment → Loan → Loan Repayment
- Customer invoice / payment → Customer module
- Supplier bill / payment → Supplier module
- Member CBU contribute → Capital Build Up module
