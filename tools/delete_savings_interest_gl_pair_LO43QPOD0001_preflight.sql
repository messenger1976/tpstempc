-- Read-only preflight for deleting one GL pair.
-- Receipts LO43QPOD0001 and SV00002764, amount 64.61, date 2022-02-28.
-- This file does not insert, update, delete, create, or drop anything.
-- It does not approve a production run.
--
-- Set these in the SAME mysql session before sourcing this file.
-- None are assigned here. If PIN or database is unset, the counts stay empty.
--   SET @expected_database := 'YOUR_DATABASE_NAME';
--   SET @expected_pin := <PIN you intend>;
--
-- Database name is the first name after --tables. This file does not run the dump.
--   mysqldump [options] --tables YOUR_DATABASE_NAME account_chart fiscal_year general_journal_entry general_ledger general_ledger_entry gl_books_close journal_entry journal_entry_items savings_transaction > backup.sql

SELECT
    'DELETE_PREFLIGHT' AS phase,
    DATABASE() AS schema_name,
    @expected_database AS expected_database,
    @expected_pin AS expected_pin,
    CASE
        WHEN @expected_database IS NULL OR @expected_pin IS NULL THEN 'SET @expected_database AND @expected_pin IN THIS SESSION'
        WHEN @expected_database <> DATABASE() THEN 'DATABASE() DOES NOT MATCH @expected_database'
        ELSE 'FILTER ACTIVE'
    END AS filter_state,
    'No rows are deleted by this file' AS write_scope;

SELECT
    TABLE_NAME,
    ENGINE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('general_ledger', 'general_ledger_entry', 'savings_transaction', 'gl_books_close', 'journal_entry')
ORDER BY TABLE_NAME;

SELECT
    EVENT_OBJECT_TABLE,
    TRIGGER_NAME
FROM information_schema.TRIGGERS
WHERE EVENT_OBJECT_SCHEMA = DATABASE()
  AND EVENT_OBJECT_TABLE IN ('general_ledger', 'general_ledger_entry', 'savings_transaction');

SELECT
    st.id,
    st.receipt,
    st.account,
    st.trans_type,
    st.paymethod,
    DATE(st.trans_date) AS trans_day,
    ROUND(st.amount, 2) AS amount,
    st.PIN
FROM savings_transaction st
WHERE @expected_database <=> DATABASE()
  AND CAST(st.PIN AS UNSIGNED) = CAST(@expected_pin AS UNSIGNED)
  AND st.receipt IN ('LO43QPOD0001', 'SV00002764')
ORDER BY st.receipt, st.id;

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
    gl.PIN
FROM general_ledger gl
WHERE @expected_database <=> DATABASE()
  AND CAST(gl.PIN AS UNSIGNED) = CAST(@expected_pin AS UNSIGNED)
  AND gl.refferenceID IN ('LO43QPOD0001', 'SV00002764')
ORDER BY gl.refferenceID, gl.id;

SELECT
    CASE WHEN COUNT(*) = 0 THEN 'NO_HEADER_FOR_THESE_ENTRYIDS' ELSE 'HEADER_ROWS_EXIST' END AS header_check,
    COUNT(*) AS header_rows
FROM general_ledger_entry h
WHERE @expected_database <=> DATABASE()
  AND h.id IN (
      SELECT DISTINCT CAST(gl.entryid AS UNSIGNED)
      FROM general_ledger gl
      WHERE CAST(gl.PIN AS UNSIGNED) = CAST(@expected_pin AS UNSIGNED)
        AND gl.refferenceID IN ('LO43QPOD0001', 'SV00002764')
        AND gl.entryid REGEXP '^[0-9]+$'
  );

SELECT
    c.id,
    c.closed_as_of,
    c.action
FROM gl_books_close c
WHERE @expected_database <=> DATABASE()
  AND CAST(c.PIN AS UNSIGNED) = CAST(@expected_pin AS UNSIGNED)
ORDER BY c.id DESC
LIMIT 1;

SELECT
    ROUND(COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0), 2) AS expense_window_net_if_you_pass_the_account,
    'Pass the expense account you saw above as @expect_expense_account before trusting this net' AS note
FROM general_ledger
WHERE @expected_database <=> DATABASE()
  AND CAST(PIN AS UNSIGNED) = CAST(@expected_pin AS UNSIGNED)
  AND account = CAST(@expect_expense_account AS UNSIGNED)
  AND `date` >= '2021-11-01'
  AND `date` <= '2024-12-31';
