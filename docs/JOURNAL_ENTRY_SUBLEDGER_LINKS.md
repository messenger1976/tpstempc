# Journal Entry Sub-ledger Links

Manual journal vouchers (Finance → Journal Entry) can optionally link each line to a **Customer (AR)**, **Supplier (AP)**, or **Member Loan**. After approval, those links are copied into `general_ledger` so the entry appears on the matching sub-ledger report.

## Reference # (auto)

Format: `JV-{YYYY}{######}` — e.g. `JV-2026000001`

- `JV-` prefix
- 4-digit year from the **entry date**
- 6-digit counter per organization (PIN), resets each year

Assigned automatically on save (read-only on the form).

## Document No. (manual, required)

User-entered source document / voucher number stored in `general_journal_entry.document_no`. Required on create.

## What this does

| Link type | Stored on `general_journal` | Copied to `general_ledger` on post |
|-----------|-----------------------------|-------------------------------------|
| Customer  | `customerid`                | `customerid`                        |
| Supplier  | `supplierid`                | `supplierid`                        |
| Loan      | `LID`, `PID`, `member_id`   | `LID`, `PID`, `member_id`           |

Also stores `link_type` on the journal line for display.

## What this does **not** do

- Does **not** create or update `sales_invoice` / `purchase_invoice` balances
- Does **not** create loan repayment receipts or update repayment schedules
- Does **not** replace Loan Repayment, Customer Receive Payment, or Supplier Pay Invoice

Use those modules for operational payments. Use linked journal lines for **GL adjustments** that should still show on AR / AP / loan-related GL filters (e.g. write-offs, reclassifications, beginning-balance corrections tagged to a party).

## Install (production)

Run once:

`/install_journal_entry_subledger_links.php`

(or create a journal entry — `ensure_general_journal_link_columns()` will add missing columns).

## Workflow

1. Finance → Journal Entry
2. Enter date (Reference # is auto-assigned), description
3. On each line: Account, **Link To** (optional), entity, description, debit/credit
4. Save → Review & Approve → posts to GL with sub-ledger IDs

## Related modules (preferred for day-to-day)

- Member loan payment → Loan → Loan Repayment
- Customer invoice / payment → Customer module
- Supplier bill / payment → Supplier module
