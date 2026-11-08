PIN 105 repair for LO43QPOD0001 / SV00002764
Prepared posting date: 2022-02-28

This package prepares a script. It does not approve running it on the live
database, and it does not reopen a closed period.

What it posts, only after every confirmation is set
  DR 11100 Cash and Cash Equivalents         64.61
  CR 71100 Interest Expense on Borrowings    64.61
  Accounting date 2022-02-28 on the journal header and both GL lines.
  created_at is the clock time of the run, not backdated.

What it does not change
  GL 3830/3831 (entryid 39755) and GL 11029/11030 (entryid 169383).
  Savings rows 606 and 2764. Member balances. Account 21110.
  The 36.24 and 20.23 interest lines. Any other PIN.
  general_journal_entry and its voucher numbers (JV-YYYY######).

Why this journal
  The source posted Dr 71100 / Cr 21110. The void posted Dr 21110 / Cr 11100.
  21110 already nets to zero. The residual is Dr 11100 / Cr 71100.
  The new lines use journal_entry + journal_entry_items + a new
  general_ledger_entry + general_ledger, the same link the application uses
  in Finance_Model::post_journal_entry_to_general_ledger (fromtable
  journal_entry, linkto journal_entry.id, journalID 5).
  reference_type is savings_interest_repair, not cash_receipt or
  cash_disbursement. Legacy entry ids 39755 and 169383 are not reused.
  If the next header id would group with an existing detail line, the script
  refuses. It does not ALTER AUTO_INCREMENT.

Files
  tools/repair_pin105_lo43qpod0001_preflight.sql   read-only
  tools/repair_pin105_lo43qpod0001_apply.sql       gated writer

1. Preflight. No tokens. No writes.

  mysql --default-character-set=utf8 tapstemco < tools/repair_pin105_lo43qpod0001_preflight.sql

  Stop if the four GL lines, the two savings rows, the three leaf accounts,
  or the 71100 window net are not exactly the authorized figures.
  The authorized window net for 2021-11-01 through 2024-12-31 is 121.08.
  After a successful post it is 56.47. There is no switch to accept another
  window net. Other 64.61 lines on 11100 or 71100 are listed without
  descriptions or member ids.

2. Backup, before apply, outside the SQL transaction. A dump is not rolled
   back by the script. The backup token does not check that a file exists.

  mysqldump --single-transaction --skip-lock-tables --no-tablespaces \
    --default-character-set=utf8 \
    --tables account_chart fiscal_year general_journal_entry general_ledger \
    general_ledger_entry gl_books_close journal_entry journal_entry_items \
    savings_transaction \
    tapstemco > tapstemco_pin105_lo43qpod0001_pre_repair.sql

3. Apply only in a session where you set every value yourself. The file
   contains none of them.

  SET @expected_database       := 'tapstemco';
  SET @actor_user_id           := <users.id>;
  SET @confirm_apply           := 'APPLY-PIN105-LO43QPOD0001';
  SET @confirm_backup          := 'BACKUP-TAKEN-TAPSTEMCO-PIN105';
  SET @confirm_historical_date := 'APPROVE-POSTING-DATE-2022-02-28';
  SET @confirm_manual_review   := 'REVIEWED-NO-OTHER-641-CORRECTION';

  The actor must be one users row with PIN 105. If users.deleted exists, it
  must be NULL or 0. The user must belong to a groups row with PIN 105 that
  has access_level Module 6, link Review_journal_entry, allow 1. The script
  does not guess an id and does not treat a group as an administrator.
  If users, users_groups, groups, or access_level is missing, it refuses.
  Required columns: users.id, users.PIN; users_groups.user_id,
  users_groups.group_id; groups.id, groups.PIN; access_level.group_id,
  access_level.Module, access_level.link, access_level.allow.

  @confirm_manual_review is required only when preflight lists other PIN 105
  lines of 64.61 on 11100 or 71100. It does not override a line that already
  names these receipts, a closed book, a changed source, or a window net
  other than 121.08.

  mysql --default-character-set=utf8 tapstemco < tools/repair_pin105_lo43qpod0001_apply.sql

  Read repair_status:
    PREFLIGHT_ONLY   tokens or actor were unset; no financial write
    APPLIED          one journal posted
    ALREADY_APPLIED  the marker was already present; no second journal
    REFUSED          a guard failed before the financial transaction
    ROLLED_BACK      a guard or statement failed after the transaction began

Closed books
  Latest gl_books_close row for PIN 105, ordered by id. A NULL closed_as_of
  means open. 2022-02-28 is refused when closed_as_of is on or after that
  date. fiscal_year.status is not consulted. Nothing in the script reopens
  the books.

Privileges and rollback
  Apply needs SELECT, INSERT, CREATE ROUTINE, and DROP ROUTINE.
  It does not need UPDATE or DELETE on financial tables.
  CREATE PROCEDURE and DROP PROCEDURE implicit-commit. If apply is sourced,
  those two DDL statements run even when the status is PREFLIGHT_ONLY, and
  a financial rollback does not undo them. The routine is dropped at the
  end of a normal source. If the client stops before that drop, remove it
  with DROP PROCEDURE IF EXISTS repair_pin105_lo43qpod0001.
  The financial inserts are one InnoDB transaction. The script refuses
  unless general_ledger, general_ledger_entry, journal_entry, and
  journal_entry_items are InnoDB, and unless those tables plus
  general_journal_entry and savings_transaction have no triggers.
  A MyISAM table would not roll back; that is why the engine check exists.
  There is no on-the-fly repair DDL.

Repeat
  A second source with the same tokens returns ALREADY_APPLIED and does not
  add another 64.61. The marker is journal_entry.reference_type
  savings_interest_repair and description REPAIR-LO43QPOD0001-SV00002764.

Disposable test
  tests/repair_pin105_lo43qpod0001_sql_test.sh
  It builds a synthetic schema from the sanitized column list and never
  opens a database named tapstemco. The machine used for that run had
  MariaDB 10.11.14. The live server in the snapshot is 10.4.27. The script
  uses procedures, transactions, and information_schema checks that both
  versions support. It was not executed on the live database.
