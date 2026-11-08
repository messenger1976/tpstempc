-- Read-only preflight for the PIN 105 savings-interest repair.
-- Receipt LO43QPOD0001 / void SV00002764, amount 64.61, posting date 2022-02-28.
--
-- This file does not insert, update, or delete anything. It does not create
-- or drop routines. Sourcing it cannot post the correction.
--
-- The posting date 2022-02-28 is the prepared accounting date. Sourcing this
-- file does not approve a live run and does not reopen closed books.
-- fiscal_year.status is printed only to show it is not the close flag.
--
-- Run against the intended schema, then read every result set:
--   mysql --default-character-set=utf8 tapstemco < tools/repair_pin105_lo43qpod0001_preflight.sql
--
-- Backup is a separate command. Take it before any apply. This file does not
-- run mysqldump and cannot see whether a dump file exists.
-- Database name is the first name after --tables, then the table names:
--   mysqldump [options] --tables tapstemco account_chart fiscal_year general_journal_entry general_ledger general_ledger_entry gl_books_close journal_entry journal_entry_items savings_transaction > backup.sql

SELECT
    'PREFLIGHT' AS phase,
    DATABASE() AS schema_name,
    VERSION() AS server_version,
    @@sql_mode AS sql_mode,
    '2022-02-28' AS prepared_posting_date,
    'No financial rows are written by this file' AS write_scope;

SELECT
    TABLE_NAME,
    ENGINE,
    AUTO_INCREMENT
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'account_chart',
      'fiscal_year',
      'general_journal',
      'general_journal_entry',
      'general_ledger',
      'general_ledger_entry',
      'gl_books_close',
      'journal_entry',
      'journal_entry_items',
      'savings_transaction'
  )
ORDER BY TABLE_NAME;

SELECT
    EVENT_OBJECT_TABLE,
    TRIGGER_NAME,
    EVENT_MANIPULATION
FROM information_schema.TRIGGERS
WHERE EVENT_OBJECT_SCHEMA = DATABASE()
  AND EVENT_OBJECT_TABLE IN (
      'general_ledger',
      'general_ledger_entry',
      'journal_entry',
      'journal_entry_items',
      'general_journal_entry',
      'savings_transaction'
  );

SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'general_ledger'
  AND IS_NULLABLE = 'NO'
  AND COLUMN_DEFAULT IS NULL
  AND EXTRA NOT LIKE '%auto_increment%'
  AND COLUMN_NAME NOT IN (
      'entryid', 'refferenceID', 'journalID', 'date', 'account', 'description',
      'credit', 'debit', 'linkto', 'fromtable', 'PID', 'member_id', 'customerid',
      'supplierid', 'balance', 'account_type', 'sub_account_type', 'invoiceid',
      'LID', 'PIN'
  );

SELECT
    gl.id,
    gl.entryid,
    gl.refferenceID,
    gl.journalID,
    gl.`date`,
    gl.account,
    gl.debit,
    gl.credit,
    gl.fromtable,
    gl.linkto,
    gl.account_type,
    gl.sub_account_type,
    gl.PIN
FROM general_ledger gl
WHERE gl.PIN = 105
  AND gl.id IN (3830, 3831, 11029, 11030)
ORDER BY gl.id;

SELECT
    CASE WHEN COUNT(*) = 0 THEN 'NO_LEGACY_HEADER' ELSE 'LEGACY_HEADER_PRESENT' END AS legacy_header_check,
    COUNT(*) AS header_rows
FROM general_ledger_entry
WHERE id IN (39755, 169383);

SELECT
    st.id,
    st.receipt,
    st.account,
    st.trans_type,
    st.paymethod,
    DATE(st.trans_date) AS trans_day,
    ROUND(st.amount, 2) AS amount,
    st.PIN,
    st.system_comment
FROM savings_transaction st
WHERE st.PIN = 105
  AND st.id IN (606, 2764)
ORDER BY st.id;

SELECT
    ac.account,
    ac.name,
    ac.account_type,
    ac.sub_account_type,
    ac.is_header,
    ac.PIN
FROM account_chart ac
WHERE ac.PIN = 105
  AND ac.account IN (11100, 21110, 71100)
ORDER BY ac.account;

SELECT
    'gl_books_close latest row; NULL closed_as_of means open. fiscal_year.status is not this flag.' AS note,
    c.id,
    c.PIN,
    c.closed_as_of,
    c.action,
    c.created_at
FROM gl_books_close c
WHERE c.PIN = 105
ORDER BY c.id DESC
LIMIT 1;

SELECT
    'fiscal_year is informational only and does not close 2022-02-28' AS note,
    fy.id,
    fy.name,
    fy.start_date,
    fy.end_date,
    fy.status,
    fy.PIN
FROM fiscal_year fy
WHERE fy.PIN IN ('105', 105)
ORDER BY fy.id;

SELECT
    ROUND(COALESCE(SUM(CASE WHEN account = 71100 THEN debit - credit ELSE 0 END), 0), 2) AS net_71100,
    ROUND(COALESCE(SUM(CASE WHEN account = 11100 THEN debit - credit ELSE 0 END), 0), 2) AS net_11100,
    ROUND(COALESCE(SUM(CASE WHEN account = 21110 THEN debit - credit ELSE 0 END), 0), 2) AS net_21110
FROM general_ledger
WHERE PIN = 105
  AND id IN (3830, 3831, 11029, 11030);

SELECT
    ROUND(COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0), 2) AS window_71100_net,
    '2021-11-01' AS window_from,
    '2024-12-31' AS window_until,
    'Authorized before-repair net is 121.08. Apply refuses any other net.' AS rule
FROM general_ledger
WHERE PIN = 105
  AND account = 71100
  AND `date` >= '2021-11-01'
  AND `date` <= '2024-12-31';

SELECT
    gl.id,
    gl.account,
    gl.`date`,
    gl.debit,
    gl.credit,
    gl.fromtable,
    gl.refferenceID,
    gl.journalID,
    gl.entryid
FROM general_ledger gl
WHERE gl.PIN = 105
  AND gl.id NOT IN (3830, 3831, 11029, 11030)
  AND gl.account IN (71100, 11100)
  AND (
        ABS(gl.debit - 64.61) < 0.005
        OR ABS(gl.credit - 64.61) < 0.005
      )
ORDER BY gl.id;

SELECT
    t.AUTO_INCREMENT AS next_general_ledger_entry_id,
    (
        SELECT COUNT(*)
        FROM general_ledger gl
        WHERE TRIM(gl.entryid) = CAST(t.AUTO_INCREMENT AS CHAR)
    ) AS existing_details_with_that_entryid
FROM information_schema.TABLES t
WHERE t.TABLE_SCHEMA = DATABASE()
  AND t.TABLE_NAME = 'general_ledger_entry';

SELECT
    COUNT(*) AS repair_marker_rows
FROM journal_entry
WHERE PIN = '105'
  AND reference_type = 'savings_interest_repair'
  AND description LIKE '%REPAIR-LO43QPOD0001-SV00002764%';

SELECT
    CASE
        WHEN COUNT(*) > 0 THEN 'PRESENT'
        ELSE 'MISSING'
    END AS users_table,
    'Actor metadata required before apply: users.id, users.PIN; users_groups.user_id, users_groups.group_id; groups.id, groups.PIN; access_level.group_id, access_level.Module, access_level.link, access_level.allow. Actor must have Module 6 link Review_journal_entry allow 1 on a group whose PIN is 105.' AS actor_rule
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users';
