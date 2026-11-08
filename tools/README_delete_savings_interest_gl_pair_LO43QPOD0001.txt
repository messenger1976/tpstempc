DELETE the GL pair for LO43QPOD0001 and SV00002764
This is not the correction package repair_pin105_lo43qpod0001_*.sql.
Do not replace those files with this one.

Prepared scope: remove exactly four balanced general_ledger rows for those
two receipts, 64.61, dated 2022-02-28, journal 5, from savings_transaction.
Nothing in this package has been run on production. Production contents
have not been verified. Local row ids are not used.

What it removes
  The source receipt's two GL lines and the void receipt's two GL lines,
  after you confirm the expense, liability, and offset accounts that
  preflight showed. The four lines must balance, the liability must net
  to zero inside the pair, and no other row may share their entry ids.

What it does not remove
  Savings rows, member rows, the chart of accounts, other GL lines,
  the 36.24 and 20.23 lines, other PINs, and general_ledger_entry headers.
  An empty header, if one exists only for these entry ids, stays.
  It has no amount and does not change a total.

What changes in reports
  Deleting the 2022-02-28 expense debit removes it from every report that
  includes that date. On the sanitized fixture the 71100 window
  2021-11-01 through 2024-12-31 moves from 121.08 to 56.47. Production is
  refused unless the confirmed expense account falls by exactly 64.61.
  If gl_books_close for the PIN is on or after 2022-02-28, the script
  refuses. fiscal_year.status is not a close flag. There is no bypass.

App limit you must accept
  is_savings_receipt_posted_to_gl() looks only for general_ledger rows
  with fromtable savings_transaction and that receipt. After this delete,
  both receipts can show as not posted. The savings rows remain, including
  the void link. Post to GL on the interest receipt inserts a new expense
  debit and liability credit. Post to GL on the void receipt can insert
  another offsetting pair. This file does not block that button and does
  not change the savings rows. Do not post these two receipts again unless
  you mean to put the expense back.

Files
  delete_savings_interest_gl_pair_LO43QPOD0001_preflight.sql
      Read-only. No routine and no delete.
  delete_savings_interest_gl_pair_LO43QPOD0001_apply.sql
      Deletes only inside one transaction, and only after the flags below.
      Sourcing it always creates and drops a routine, even as a preview.

1. One client. Stay in it.

  "C:\xampp\mysql\bin\mysql.exe" --user=YOUR_MYSQL_USER -p --default-character-set=utf8 YOUR_DATABASE_NAME

  YOUR_MYSQL_USER is the MariaDB account you already use for that database.
  -p with nothing after it prompts for the password. Do not put a password
  on the command line. Preflight needs SELECT. Apply also needs DELETE on
  general_ledger, plus CREATE ROUTINE and DROP ROUTINE. It does not need
  UPDATE, and it does not delete any table except the four matched GL rows.

  SELECT DATABASE(), CURRENT_USER();
  SET @expected_database := 'YOUR_DATABASE_NAME';
  SET @expected_pin := <PIN you intend>;
  SOURCE C:/Users/YOU/Downloads/delete_savings_interest_gl_pair_LO43QPOD0001_preflight.sql

  Stop if you do not see exactly two savings rows linked as a void pair
  and exactly four GL lines in the source/void shape. Read the account
  codes from that output. Do not invent them.

2. Backup, outside the transaction. The token does not create or check a file.
   The dump must succeed. Open it and confirm it is nonempty and contains
   CREATE TABLE and INSERT for general_ledger and savings_transaction.

  "C:\xampp\mysql\bin\mysqldump.exe" --user=YOUR_MYSQL_USER -p --single-transaction --skip-lock-tables --no-tablespaces --default-character-set=utf8 --tables YOUR_DATABASE_NAME account_chart fiscal_year general_journal_entry general_ledger general_ledger_entry gl_books_close journal_entry journal_entry_items savings_transaction > "C:\Users\YOU\Downloads\YOUR_DATABASE_NAME_gl_pair_delete_backup.sql"

  The database name is the first name after --tables.

3. Delete only in that same client, after review, a checked backup, and
   approval to change reports from 2022-02-28. A new mysql.exe loses the
   variables. SOURCE of the apply file alone deletes nothing, but it still
   creates and drops the routine.

  SET @expect_expense_account := <expense account from preflight>;
  SET @expect_liability_account := <liability account from preflight>;
  SET @expect_offset_account := <void credit account from preflight>;
  SET @confirm_source_receipt := 'LO43QPOD0001';
  SET @confirm_void_receipt := 'SV00002764';
  SET @confirm_backup := 'BACKUP-TAKEN-BEFORE-GL-PAIR-DELETE';
  SET @confirm_historical_effect := 'APPROVE-DELETE-AFFECTS-REPORTS-FROM-2022-02-28';
  SET @confirm_delete := 'DELETE-GL-PAIR-LO43QPOD0001-SV00002764';
  SOURCE C:/Users/YOU/Downloads/delete_savings_interest_gl_pair_LO43QPOD0001_apply.sql

  delete_pair_status:
    PREFLIGHT_ONLY    flags were unset; no delete
    DELETED           exactly four GL rows removed
    ALREADY_REMOVED   those receipts already have no GL rows; nothing new removed
    REFUSED           a guard failed before the transaction
    ROLLED_BACK       a guard failed after the transaction began; delete undone

Before commit
  The script rolls back unless the delete count is 4, the books stay
  balanced, the expense window falls by 64.61, and the savings rows are
  unchanged. You do not need a restore for a ROLLED_BACK result.

After commit
  Do not import the whole general_ledger dump back onto the live database.
  That would discard every GL change made after the backup. To put this
  pair back, restore the dump into an empty schema, find the four INSERTs
  for these receipts and this PIN, and review them before inserting only
  those lines into production. Keep their entryid text from the backup.
  Do not allocate new header ids for them. Do this only while those
  receipts still have no GL rows, so you do not duplicate the pair.

Not run on production. Disposable checks used MariaDB 10.11.14.
The snapshot server is 10.4.27.
