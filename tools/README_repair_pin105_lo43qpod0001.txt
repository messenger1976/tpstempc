PIN 105 repair for LO43QPOD0001 / SV00002764
Prepared posting date: 2022-02-28

This package does not approve a live run and does not reopen a closed period.
Only the 64.61 correction is in scope. Source and void rows stay as they are.
The 36.24 and 20.23 lines are not repair targets.

What a confirmed post writes
  DR 11100 Cash and Cash Equivalents         64.61
  CR 71100 Interest Expense on Borrowings    64.61
  Accounting date 2022-02-28. created_at is the clock time of the run.
  Account 21110 is not posted. It already nets to zero.

Files to download
  repair_pin105_lo43qpod0001_preflight.sql   read-only. No INSERT, UPDATE,
                                             DELETE, CREATE, or DROP.
  repair_pin105_lo43qpod0001_apply.sql       gated writer. Even a preview
                                             run creates and drops a routine.

Put both files in one folder you choose. The paths below use that folder
as an editable placeholder, not a detected install path.

1. Open one client and stay in it

  "C:\xampp\mysql\bin\mysql.exe" --user=YOUR_MYSQL_USER -p --default-character-set=utf8 tapstemco

  YOUR_MYSQL_USER is the MariaDB account you already use for tapstemco.
  -p with nothing after it makes the client prompt for the password.
  Do not put the password on the command line.
  Preflight needs SELECT. Apply also needs INSERT, CREATE ROUTINE, and
  DROP ROUTINE. It does not need UPDATE or DELETE on the financial tables.

  At the mysql prompt:

  SELECT DATABASE(), CURRENT_USER();

  DATABASE() must be tapstemco. Then:

  SOURCE C:/Users/YOU/Downloads/repair_pin105_lo43qpod0001_preflight.sql

  Stop if the four GL lines, the two savings rows, the three leaf accounts,
  or the 71100 window net are not the authorized figures. The window
  2021-11-01 through 2024-12-31 must be 121.08 before a post and 56.47
  after. There is no switch to accept another window net.

2. Backup, outside this client and outside the SQL transaction

  The confirmation token does not create a dump and does not check that a
  file exists. The dump must succeed, and you must open the file and see
  that it is nonempty and contains CREATE TABLE and INSERT for
  general_ledger, journal_entry, and savings_transaction before you set
  the backup token.

  One line. The database name is the first name after --tables.

  "C:\xampp\mysql\bin\mysqldump.exe" --user=YOUR_MYSQL_USER -p --single-transaction --skip-lock-tables --no-tablespaces --default-character-set=utf8 --tables tapstemco account_chart fiscal_year general_journal_entry general_ledger general_ledger_entry gl_books_close journal_entry journal_entry_items savings_transaction > "C:\Users\YOU\Downloads\tapstemco_pin105_lo43qpod0001_pre_repair.sql"

  A failed dump that names account_chart as the database is the old,
  invalid order. Do not use it.

3. Apply only in that same open client, and only after review

  Do not start a second mysql.exe. Variables set in one process are gone
  in the next, and SOURCE of the apply file alone is then a preview.
  Do not set the tokens unless you have reviewed the preflight output,
  verified the backup file, and a person with authority has approved
  posting on 2022-02-28. Naming that date in this package is not that
  approval.

  SET @expected_database       := 'tapstemco';
  SET @actor_user_id           := <users.id you verified for PIN 105>;
  SET @confirm_apply           := 'APPLY-PIN105-LO43QPOD0001';
  SET @confirm_backup          := 'BACKUP-TAKEN-TAPSTEMCO-PIN105';
  SET @confirm_historical_date := 'APPROVE-POSTING-DATE-2022-02-28';

  Set @confirm_manual_review only when preflight listed other PIN 105
  lines of 64.61 on 11100 or 71100, and you have decided they are not
  already this correction. It does not override a line that names these
  receipts, a closed book, a changed source, or a window net other than
  121.08.

  SET @confirm_manual_review := 'REVIEWED-NO-OTHER-641-CORRECTION';

  SOURCE C:/Users/YOU/Downloads/repair_pin105_lo43qpod0001_apply.sql

  The actor must be one users row with PIN 105. If users.deleted exists,
  it must be NULL or 0. That user must be in a groups row with PIN 105
  that has access_level Module 6, link Review_journal_entry, allow 1.
  This package does not supply an id.

  repair_status:
    PREFLIGHT_ONLY   tokens or actor were unset; no financial write
    APPLIED          one journal posted
    ALREADY_APPLIED  marker already present; no second journal
    REFUSED          a guard failed before the financial transaction
    ROLLED_BACK      a guard failed after the transaction began

  Sourcing the apply file always runs CREATE PROCEDURE and DROP PROCEDURE,
  including a preview. Those are DDL. MariaDB implicit-commits them, and
  a financial rollback does not undo them. They need CREATE ROUTINE and
  DROP ROUTINE. The preflight file does not create or drop a routine.
  If the client stops before the drop, run:
  DROP PROCEDURE IF EXISTS repair_pin105_lo43qpod0001;

Closed books
  Latest gl_books_close row for PIN 105, by id. NULL closed_as_of means
  open. 2022-02-28 is refused when closed_as_of is on or after that date.
  fiscal_year.status is not a close flag. Nothing here reopens the books.

Repeat
  A second SOURCE with the same tokens returns ALREADY_APPLIED.
  Marker: reference_type savings_interest_repair and description
  REPAIR-LO43QPOD0001-SV00002764.

Not run on the live database. Disposable checks used MariaDB 10.11.14.
The snapshot server is 10.4.27.
